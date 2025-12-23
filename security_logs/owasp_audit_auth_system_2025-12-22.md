# OWASP Security Audit Report
**Component**: Authentication System  
**Date**: 2025-12-22  
**Auditor**: AI Security Review  
**Scope**: All uncommitted authentication & authorization files

---

## 📊 Executive Summary

### Vulnerability Distribution
- 🔴 **CRITICAL**: 2 issues
- 🟠 **HIGH**: 3 issues  
- 🟡 **MEDIUM**: 4 issues
- 🟢 **LOW**: 2 issues

### Risk Score: **7.5/10** (HIGH RISK)

**Immediate Action Required**: 
1. Implement Form Request validation untuk login
2. Add CSRF protection verification
3. Implement password complexity validation
4. Add rate limiting ke routes
5. Disable debug mode untuk production

---

## 🗺️ Component Trace Map

```
┌─────────────────────────────────────────────────────────────┐
│                    AUTHENTICATION FLOW                       │
└─────────────────────────────────────────────────────────────┘

FRONTEND:
  Login.vue (resources/js/pages/Auth/Login.vue)
    ↓ [POST] /login
    
ROUTES (routes/web.php):
  Guest Routes: GET/POST /login
  Auth Routes: POST /logout
    ↓
    
MIDDLEWARE (bootstrap/app.php):
  - guest (Laravel built-in)
  - auth (Laravel built-in)
  - role (custom: CheckRole)
  - LogActivity (custom)
    ↓
    
CONTROLLERS:
  LoginController.php
    ├─ showLoginForm()
    ├─ login(Request)
    └─ logout(Request)
    ↓
    
MODELS:
  - User.php
  - ActivityLog.php
  - FailedLoginAttempt.php
  - PasswordHistory.php
    ↓
    
DATABASE MIGRATIONS:
  - add_auth_fields_to_users_table
  - create_activity_logs_table
  - create_failed_login_attempts_table
  - create_password_histories_table

DASHBOARD REDIRECTS:
  - AdminDashboardController
  - PrincipalDashboardController
  - TeacherDashboardController
  - ParentDashboardController
```

---

## 🔒 DETAILED SECURITY FINDINGS

---

### 🔴 CRITICAL-01: Missing Form Request Validation
**File**: `app/Http/Controllers/Auth/LoginController.php:28-34`  
**Type**: A04 - Insecure Design & A03 - Injection

**Issue**:
Login controller menggunakan inline validation di controller method. Ini tidak best practice karena:
1. Validation logic tercampur dengan business logic
2. Tidak ada custom error messages yang clear untuk user
3. Sulit untuk maintain dan test
4. Tidak ada protection terhadap mass assignment vulnerabilities

**Attack Scenario**:
Attacker bisa inject additional fields yang tidak divalidasi melalui request payload, potentially exploiting mass assignment jika ada model yang tidak properly guarded.

**Current Code**:

```php
public function login(Request $request)
{
    $request->validate([
        'identifier' => 'required|string',
        'password' => 'required|string',
        'remember' => 'boolean',
    ]);
    // ... rest of logic
}
```

**Secure Fix**:

```php
// app/Http/Requests/Auth/LoginRequest.php
<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'identifier' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'remember' => ['boolean'],
        ];
    }

    /**
     * Get custom messages untuk validation errors
     */
    public function messages(): array
    {
        return [
            'identifier.required' => 'Username atau email wajib diisi.',
            'identifier.string' => 'Username atau email harus berupa teks.',
            'identifier.max' => 'Username atau email maksimal 255 karakter.',
            'password.required' => 'Password wajib diisi.',
            'password.string' => 'Password harus berupa teks.',
            'password.min' => 'Password minimal 8 karakter.',
            'remember.boolean' => 'Remember me harus berupa boolean.',
        ];
    }

    /**
     * Prepare the data untuk validation - sanitize input
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'identifier' => trim($this->identifier ?? ''),
            'remember' => $this->boolean('remember'),
        ]);
    }
}

// Update LoginController.php
use App\Http\Requests\Auth\LoginRequest;

public function login(LoginRequest $request)
{
    // Validation sudah otomatis di-handle oleh LoginRequest
    $identifier = $request->validated('identifier');
    $password = $request->validated('password');
    $remember = $request->validated('remember');
    
    // ... rest of logic
}
```

**Impact**: Mass assignment vulnerabilities, weak input validation  
**Priority**: P0 (Implement immediately)

---

### 🔴 CRITICAL-02: No Password Complexity Validation
**File**: `app/Http/Controllers/Auth/LoginController.php` & `database/migrations/*`  
**Type**: A07 - Identification and Authentication Failures

**Issue**:
Tidak ada validation untuk password complexity saat user creation atau password change. Password hanya di-hash tanpa requirement minimal:
- Tidak ada minimum length enforcement yang strict
- Tidak ada requirement untuk kombinasi huruf, angka, dan simbol
- Tidak ada check untuk common passwords
- Tidak ada integration dengan PasswordHistory untuk prevent reuse

**Attack Scenario**:
User bisa membuat password lemah seperti "password", "12345678", atau "qwerty123" yang mudah di-brute force. Attacker bisa dengan mudah compromise account dengan dictionary attack atau common password lists.

**Current Code**:
Tidak ada validation sama sekali untuk password complexity.

**Secure Fix**:

```php
// app/Rules/StrongPassword.php
<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPassword implements ValidationRule
{
    /**
     * Run the validation rule untuk ensure password strength
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Check minimum length
        if (strlen($value) < 8) {
            $fail('Password minimal 8 karakter.');
            return;
        }

        // Check untuk huruf besar
        if (!preg_match('/[A-Z]/', $value)) {
            $fail('Password harus mengandung minimal 1 huruf besar.');
            return;
        }

        // Check untuk huruf kecil
        if (!preg_match('/[a-z]/', $value)) {
            $fail('Password harus mengandung minimal 1 huruf kecil.');
            return;
        }

        // Check untuk angka
        if (!preg_match('/[0-9]/', $value)) {
            $fail('Password harus mengandung minimal 1 angka.');
            return;
        }

        // Check untuk special character
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $value)) {
            $fail('Password harus mengandung minimal 1 karakter khusus (!@#$%^&*(),.?":{}|<>).');
            return;
        }

        // Check common passwords
        $commonPasswords = [
            'password', '12345678', 'qwerty123', 'password123', 
            'admin123', 'welcome123', 'letmein123', '11111111'
        ];
        
        if (in_array(strtolower($value), $commonPasswords)) {
            $fail('Password terlalu umum. Gunakan kombinasi yang lebih unik.');
            return;
        }
    }
}

// app/Rules/NotInPasswordHistory.php
<?php

namespace App\Rules;

use App\Models\PasswordHistory;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Hash;

class NotInPasswordHistory implements ValidationRule
{
    public function __construct(
        private int $userId,
        private int $historyCount = 3
    ) {}

    /**
     * Check apakah password pernah digunakan dalam 3 password terakhir
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $recentPasswords = PasswordHistory::where('user_id', $this->userId)
            ->latest()
            ->limit($this->historyCount)
            ->pluck('password');

        foreach ($recentPasswords as $hashedPassword) {
            if (Hash::check($value, $hashedPassword)) {
                $fail("Password tidak boleh sama dengan {$this->historyCount} password terakhir Anda.");
                return;
            }
        }
    }
}

// Usage dalam Form Request untuk password change
public function rules(): array
{
    return [
        'current_password' => ['required', 'string', 'current_password'],
        'password' => [
            'required',
            'string',
            'confirmed',
            new StrongPassword(),
            new NotInPasswordHistory(auth()->id(), 3),
        ],
    ];
}
```

**Impact**: Weak authentication, easy account compromise via brute force  
**Priority**: P0 (Implement before production launch)

---

### 🟠 HIGH-01: Missing Route Rate Limiting
**File**: `routes/web.php:21-24` & `bootstrap/app.php`  
**Type**: A04 - Insecure Design & A07 - Authentication Failures

**Issue**:
Login route tidak memiliki Laravel's built-in rate limiting middleware. Walaupun ada custom failed attempt tracking di controller, route-level rate limiting adalah defense-in-depth layer yang penting untuk:
1. Prevent distributed brute force attacks
2. Protect terhadap credential stuffing
3. Reduce server load dari automated attacks

**Attack Scenario**:
Attacker bisa melakukan automated login attempts dari multiple IP addresses atau distributed botnet. Custom failed attempt tracking hanya track per identifier+IP, sehingga attacker bisa bypass dengan rotating IPs.

**Current Code**:

```php
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});
```

**Secure Fix**:

```php
// routes/web.php
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    
    // Rate limit: 5 attempts per minute per IP
    Route::post('/login', [LoginController::class, 'login'])
        ->middleware('throttle:5,1');
});

// Atau untuk more sophisticated rate limiting, buat custom rate limiter
// di bootstrap/app.php atau AppServiceProvider

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

// Dalam boot() method di AppServiceProvider
RateLimiter::for('login', function (Request $request) {
    return Limit::perMinute(5)
        ->by($request->input('identifier').$request->ip())
        ->response(function (Request $request, array $headers) {
            return back()->withErrors([
                'identifier' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam 1 menit.'
            ])->withHeaders($headers);
        });
});

// Kemudian di routes
Route::post('/login', [LoginController::class, 'login'])
    ->middleware('throttle:login');
```

**Additional Enhancement - Global API Rate Limiting**:

```php
// bootstrap/app.php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->web(append: [
        HandleInertiaRequests::class,
        AddLinkHeadersForPreloadedAssets::class,
        LogActivity::class,
    ]);

    // Global rate limiting untuk API routes (jika ada)
    $middleware->api(prepend: [
        \Illuminate\Routing\Middleware\ThrottleRequests::class.':60,1',
    ]);

    $middleware->alias([
        'role' => CheckRole::class,
    ]);
})
```

**Impact**: Brute force attacks, credential stuffing, DDoS  
**Priority**: P0 (Implement immediately)

---

### 🟠 HIGH-02: Debug Mode Enabled
**File**: `.env` (APP_DEBUG=true)  
**Type**: A05 - Security Misconfiguration

**Issue**:
Debug mode masih enabled berdasarkan `config('app.debug')` returning `true`. Ketika deployed ke production dengan debug mode on, ini akan:
1. Expose detailed error messages dengan stack traces
2. Reveal database structure dan query details
3. Show internal file paths
4. Leak environment variables
5. Display sensitive configuration data

**Attack Scenario**:
Attacker trigger error page untuk mendapatkan informasi sensitif seperti:
- Database credentials
- Internal file structure
- Installed packages dan versions
- Environment variables
- API keys dan secrets

**Current State**:
```env
APP_DEBUG=true
```

**Secure Fix**:

```env
# .env (development)
APP_DEBUG=true

# .env.production (production)
APP_DEBUG=false
APP_ENV=production
```

```php
// bootstrap/app.php - Add custom error handling
->withExceptions(function (Exceptions $exceptions): void {
    // Untuk production, log error tapi jangan expose details
    $exceptions->renderable(function (Throwable $e, Request $request) {
        if (!config('app.debug') && !$request->expectsJson()) {
            return Inertia::render('Errors/500', [
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.'
            ])->toResponse($request)->setStatusCode(500);
        }
    });
})
```

**Additional Security Headers** (implement di middleware):

```php
// app/Http/Middleware/SecurityHeaders.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        if (config('app.env') === 'production') {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
```

**Impact**: Information disclosure, reconnaissance for further attacks  
**Priority**: P0 (Fix before production deployment)

---

### 🟠 HIGH-03: Timing Attack Vulnerability in Login
**File**: `app/Http/Controllers/Auth/LoginController.php:53-64`  
**Type**: A07 - Identification and Authentication Failures

**Issue**:
Login logic menggunakan sequential checks yang bisa di-exploit untuk user enumeration via timing attacks:
1. First query: check if user exists
2. Then: check password
3. Then: check if active

Attacker bisa measure response times untuk determine apakah username/email exists dalam database.

**Attack Scenario**:
1. Attacker submit login dengan valid username → slower response (karena password checking)
2. Attacker submit login dengan invalid username → faster response (early return)
3. Attacker bisa build list of valid usernames untuk targeted attacks

**Current Code**:

```php
// Cari user berdasarkan username atau email
$user = User::where('username', $identifier)
    ->orWhere('email', $identifier)
    ->first();

// Validasi password dan status akun
if (! $user || ! Hash::check($request->password, $user->password)) {
    $this->handleFailedLogin($identifier, $ipAddress);
    
    return back()->withErrors([
        'identifier' => 'Username/email atau password salah.',
    ]);
}

if (! $user->isActive()) {
    return back()->withErrors([
        'identifier' => 'Akun Anda telah dinonaktifkan. Hubungi administrator.',
    ]);
}
```

**Secure Fix**:

```php
public function login(LoginRequest $request)
{
    $identifier = $request->validated('identifier');
    $password = $request->validated('password');
    $ipAddress = $request->ip();

    // Check apakah account sedang dalam status locked
    $failedAttempt = FailedLoginAttempt::where('identifier', $identifier)
        ->where('ip_address', $ipAddress)
        ->first();

    if ($failedAttempt && $failedAttempt->isLocked()) {
        $remainingMinutes = now()->diffInMinutes($failedAttempt->locked_until);
        
        return back()->withErrors([
            'identifier' => "Akun terkunci karena terlalu banyak percobaan login gagal. Silakan coba lagi dalam {$remainingMinutes} menit.",
        ]);
    }

    // Cari user berdasarkan username atau email
    $user = User::where('username', $identifier)
        ->orWhere('email', $identifier)
        ->first();

    // TIMING ATTACK MITIGATION: Always hash check even if user doesn't exist
    $dummyHash = '$2y$12$dummyHashValueForTimingAttackPrevention1234567890';
    $passwordToCheck = $user ? $user->password : $dummyHash;
    $passwordValid = Hash::check($password, $passwordToCheck);

    // Single validation check untuk prevent timing leaks
    if (!$user || !$passwordValid || !$user->isActive()) {
        $this->handleFailedLogin($identifier, $ipAddress);
        
        // Generic error message - tidak specify apakah username atau password yang salah
        return back()->withErrors([
            'identifier' => 'Username/email atau password salah, atau akun tidak aktif.',
        ]);
    }

    // Login berhasil - reset failed attempts
    if ($failedAttempt) {
        $failedAttempt->delete();
    }

    // ... rest of successful login logic
}
```

**Impact**: User enumeration, targeted phishing attacks, account discovery  
**Priority**: P1 (Implement soon)

---

### 🟡 MEDIUM-01: Missing CSRF Token Verification Documentation
**File**: `resources/js/pages/Auth/Login.vue`  
**Type**: A01 - Broken Access Control

**Issue**:
Meskipun Laravel secara default protect POST requests dengan CSRF token via `VerifyCsrfToken` middleware, tidak ada explicit verification dalam code atau documentation bahwa CSRF protection is properly configured untuk Inertia.js forms.

**Attack Scenario**:
Jika CSRF middleware accidentally disabled atau misconfigured, attacker bisa craft malicious forms di external sites untuk submit login requests atas nama victim (CSRF attack).

**Verification Needed**:

```php
// Verify di bootstrap/app.php bahwa VerifyCsrfToken middleware aktif
->withMiddleware(function (Middleware $middleware): void {
    $middleware->web(append: [
        HandleInertiaRequests::class,
        AddLinkHeadersForPreloadedAssets::class,
        LogActivity::class,
    ]);
    
    // CSRF middleware sudah included di web middleware group by default
    // Pastikan tidak ada $middleware->validateCsrfTokens(except: [...]) yang exclude /login
})
```

**Secure Practice - Explicit CSRF Header untuk Inertia**:

```javascript
// resources/js/app.js atau bootstrap file
import { router } from '@inertiajs/vue3'

// Inertia secara default include CSRF token dari meta tag
// Verify meta tag exists di app layout
```

```blade
<!-- resources/views/app.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- CSRF Token - Required untuk Inertia forms -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    @vite(['resources/js/app.js'])
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
```

**Testing**:

```bash
# Test CSRF protection
curl -X POST http://localhost/login \
  -H "Content-Type: application/json" \
  -d '{"identifier":"test","password":"test"}' \
  # Should return 419 CSRF token mismatch
```

**Impact**: CSRF attacks, unauthorized actions  
**Priority**: P2 (Verify dan document)

---

### 🟡 MEDIUM-02: No Session Fixation Protection
**File**: `app/Http/Controllers/Auth/LoginController.php:95`  
**Type**: A07 - Identification and Authentication Failures

**Issue**:
Meskipun ada `$request->session()->regenerate()` setelah login, tidak ada explicit session invalidation pada logout atau failed login attempts.

**Good Practice Already Implemented**:

```php
// Login berhasil
$request->session()->regenerate(); // ✅ GOOD
```

**Enhancement Needed untuk Logout**:

```php
public function logout(Request $request)
{
    $user = Auth::user();

    if ($user) {
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'logout',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status' => 'success',
        ]);
    }

    Auth::logout();

    $request->session()->invalidate();         // ✅ Already implemented
    $request->session()->regenerateToken();    // ✅ Already implemented
    
    // ENHANCEMENT: Clear remember token juga
    if ($user) {
        $user->update(['remember_token' => null]);
    }

    return redirect()->route('login')->with('message', 'Anda telah keluar dari sistem.');
}
```

**Additional Enhancement - Session Timeout**:

```php
// config/session.php
'lifetime' => env('SESSION_LIFETIME', 120), // 2 hours default

// Untuk sensitive actions, implement re-authentication
// app/Http/Middleware/RequirePassword.php (Laravel built-in)
Route::post('/critical-action', [Controller::class, 'action'])
    ->middleware(['auth', 'password.confirm']);
```

**Impact**: Session hijacking, unauthorized access  
**Priority**: P2 (Enhancement)

---

### 🟡 MEDIUM-03: Insufficient Activity Logging
**File**: `app/Http/Middleware/LogActivity.php:41-70`  
**Type**: A09 - Security Logging and Monitoring Failures

**Issue**:
Activity logging middleware hanya log POST/PUT/PATCH/DELETE requests, tetapi tidak log:
1. Failed authorization attempts (403 errors)
2. GET requests ke sensitive endpoints
3. Password change operations secara explicit
4. Role changes atau privilege escalation attempts
5. Suspicious patterns (rapid requests, unusual times)

**Current Implementation**:

```php
protected function shouldLog(Request $request): bool
{
    return in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])
        && ! $request->is('_ignition/*');
}
```

**Enhanced Logging**:

```php
protected function shouldLog(Request $request): bool
{
    // Always log data modification
    if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
        return !$request->is('_ignition/*');
    }

    // Log access ke sensitive GET endpoints
    $sensitiveRoutes = [
        'admin/*',
        '*/export',
        '*/download',
        '*/report',
    ];

    foreach ($sensitiveRoutes as $pattern) {
        if ($request->is($pattern)) {
            return true;
        }
    }

    return false;
}

// Log 403 errors di Exception Handler
// bootstrap/app.php
->withExceptions(function (Exceptions $exceptions): void {
    $exceptions->renderable(function (AuthorizationException $e, Request $request) {
        // Log unauthorized access attempt
        if (Auth::check()) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'unauthorized_access',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'new_values' => [
                    'route' => $request->path(),
                    'method' => $request->method(),
                ],
                'status' => 'failed',
            ]);
        }

        return Inertia::render('Errors/403')->toResponse($request)->setStatusCode(403);
    });
})
```

**Impact**: Insufficient audit trail, difficulty detecting breaches  
**Priority**: P2 (Implement for compliance)

---

### 🟡 MEDIUM-04: Weak IP Address Tracking
**File**: `app/Http/Controllers/Auth/LoginController.php:37`  
**Type**: A09 - Security Logging and Monitoring Failures

**Issue**:
IP tracking menggunakan `$request->ip()` yang bisa di-spoof jika application di-deploy behind proxy (nginx, load balancer) tanpa proper configuration. Attacker bisa bypass IP-based rate limiting dengan spoofing `X-Forwarded-For` header.

**Current Code**:

```php
$ipAddress = $request->ip();
```

**Secure Fix**:

```php
// config/trustedproxy.php (Laravel 11+)
// Configure trusted proxies untuk proper IP detection

// app/Helpers/IpHelper.php
<?php

namespace App\Helpers;

use Illuminate\Http\Request;

class IpHelper
{
    /**
     * Get real client IP dengan validation dan fallback
     */
    public static function getRealIp(Request $request): string
    {
        // Laravel's getClientIp() sudah handle X-Forwarded-For jika trusted proxy configured
        $ip = $request->getClientIp();
        
        // Validate IP format
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            // Fallback ke server REMOTE_ADDR
            $ip = $request->server('REMOTE_ADDR', '0.0.0.0');
        }
        
        // Log suspicious IP patterns
        if (self::isSuspiciousIp($ip)) {
            \Log::warning('Suspicious IP detected', [
                'ip' => $ip,
                'user_agent' => $request->userAgent(),
                'forwarded_for' => $request->header('X-Forwarded-For'),
            ]);
        }
        
        return $ip;
    }

    /**
     * Check untuk suspicious IP patterns
     */
    protected static function isSuspiciousIp(string $ip): bool
    {
        // Check untuk known malicious ranges, VPN exits, Tor nodes, etc.
        // Integrate dengan service seperti IPQualityScore atau AbuseIPDB
        
        // Basic checks
        if ($ip === '0.0.0.0' || $ip === '127.0.0.1') {
            return true;
        }
        
        return false;
    }
}

// Usage di LoginController
use App\Helpers\IpHelper;

$ipAddress = IpHelper::getRealIp($request);
```

**Additional Enhancement - Geolocation Anomaly Detection**:

```php
// Detect login dari unusual locations
public function login(LoginRequest $request)
{
    // ... validation ...
    
    if ($user) {
        $currentIp = IpHelper::getRealIp($request);
        $lastIp = $user->last_login_ip;
        
        // Check jika IP berubah drastis (different country)
        if ($lastIp && $this->isAnomalousLocation($lastIp, $currentIp)) {
            // Send notification email
            // Log suspicious activity
            // Optionally require additional verification
        }
    }
}
```

**Impact**: IP spoofing, bypass rate limiting, inaccurate audit logs  
**Priority**: P2 (Implement jika behind proxy/load balancer)

---

### 🟢 LOW-01: No Account Lockout Notification
**File**: `app/Models/FailedLoginAttempt.php`  
**Type**: A09 - Security Logging and Monitoring Failures

**Issue**:
Ketika account terkunci karena failed attempts, user tidak receive email notification. Ini penting untuk:
1. Alert legitimate user bahwa ada yang trying to access their account
2. Provide instructions untuk unlock account
3. Security awareness

**Enhancement**:

```php
// app/Notifications/AccountLockedNotification.php
<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AccountLockedNotification extends Notification
{
    public function __construct(
        private string $ipAddress,
        private int $attempts,
        private \Carbon\Carbon $lockedUntil
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Akun Anda Terkunci - Aktivitas Mencurigakan')
            ->greeting('Halo '.$notifiable->name.',')
            ->line("Akun Anda telah terkunci karena {$this->attempts} percobaan login gagal dari IP {$this->ipAddress}.")
            ->line("Akun akan otomatis dibuka pada {$this->lockedUntil->format('d/m/Y H:i')} WIB.")
            ->line('Jika ini bukan Anda, segera hubungi administrator.')
            ->action('Reset Password', url('/forgot-password'))
            ->line('Terima kasih!');
    }
}

// Gunakan di LoginController
protected function handleFailedLogin(string $identifier, string $ipAddress): void
{
    $failedAttempt = FailedLoginAttempt::firstOrNew([
        'identifier' => $identifier,
        'ip_address' => $ipAddress,
    ]);

    $failedAttempt->attempts += 1;

    if ($failedAttempt->attempts >= 5) {
        $failedAttempt->locked_until = now()->addMinutes(15);
        
        // Send notification ke user (jika identifier adalah email atau user exists)
        $user = User::where('email', $identifier)
            ->orWhere('username', $identifier)
            ->first();
            
        if ($user) {
            $user->notify(new AccountLockedNotification(
                $ipAddress,
                $failedAttempt->attempts,
                $failedAttempt->locked_until
            ));
        }
    }

    $failedAttempt->save();

    // ... rest of logging
}
```

**Impact**: User tidak aware of attack attempts  
**Priority**: P3 (Nice to have)

---

### 🟢 LOW-02: No Session Device Management
**File**: `app/Models/User.php`  
**Type**: A07 - Authentication Failures (Enhancement)

**Issue**:
User tidak bisa see atau manage active sessions mereka dari different devices. Best practice adalah provide user dengan:
1. List of active sessions dengan device info
2. Ability untuk logout from specific devices
3. "Logout from all devices" feature

**Enhancement** (untuk future implementation):

```php
// Create sessions table untuk track
php artisan session:table
php artisan migrate

// config/session.php
'driver' => env('SESSION_DRIVER', 'database'),

// app/Http/Controllers/Profile/SessionController.php
public function index()
{
    $sessions = DB::table('sessions')
        ->where('user_id', auth()->id())
        ->get()
        ->map(function ($session) {
            $agent = new Agent();
            $agent->setUserAgent($session->user_agent);
            
            return [
                'id' => $session->id,
                'ip_address' => $session->ip_address,
                'device' => $agent->device(),
                'platform' => $agent->platform(),
                'browser' => $agent->browser(),
                'last_activity' => Carbon::createFromTimestamp($session->last_activity),
                'is_current' => $session->id === session()->getId(),
            ];
        });
        
    return Inertia::render('Profile/Sessions', [
        'sessions' => $sessions,
    ]);
}

public function destroy(string $sessionId)
{
    DB::table('sessions')
        ->where('id', $sessionId)
        ->where('user_id', auth()->id())
        ->delete();
        
    return back()->with('message', 'Session berhasil dihapus.');
}

public function destroyAll()
{
    DB::table('sessions')
        ->where('user_id', auth()->id())
        ->where('id', '!=', session()->getId())
        ->delete();
        
    return back()->with('message', 'Semua session lain berhasil dihapus.');
}
```

**Impact**: User tidak bisa detect unauthorized access  
**Priority**: P3 (Future enhancement)

---

## 📋 RECOMMENDED FIXES PRIORITY

### Phase 1: Critical (Before Production) - P0
1. ✅ Create LoginRequest Form Request dengan validation
2. ✅ Implement StrongPassword rule dengan complexity requirements
3. ✅ Add NotInPasswordHistory rule
4. ✅ Implement route rate limiting untuk /login
5. ✅ Disable APP_DEBUG untuk production
6. ✅ Add SecurityHeaders middleware

### Phase 2: High Priority (Within 1 Week) - P1
1. ✅ Fix timing attack vulnerability di login logic
2. ✅ Enhanced activity logging untuk 403 errors
3. ✅ Implement IP address helper dengan validation

### Phase 3: Medium Priority (Within 1 Month) - P2
1. ✅ Verify CSRF protection configuration
2. ✅ Enhance logout dengan remember token clearing
3. ✅ Implement session timeout configuration
4. ✅ Add geolocation anomaly detection

### Phase 4: Low Priority (Future) - P3
1. Account lockout email notifications
2. Session device management UI
3. Two-factor authentication (2FA)
4. Password expiration policy

---

## 🧪 TESTING CHECKLIST

### Security Tests to Implement

```php
// tests/Feature/Auth/LoginSecurityTest.php
<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginSecurityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_rate_limits_login_attempts()
    {
        $user = User::factory()->create();
        
        // Attempt 6 logins rapidly
        for ($i = 0; $i < 6; $i++) {
            $response = $this->post('/login', [
                'identifier' => $user->email,
                'password' => 'wrong-password',
            ]);
        }
        
        // 6th attempt should be rate limited
        $response->assertStatus(429); // Too Many Requests
    }

    /** @test */
    public function it_locks_account_after_5_failed_attempts()
    {
        $user = User::factory()->create();
        
        // Make 5 failed attempts
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'identifier' => $user->email,
                'password' => 'wrong-password',
            ]);
        }
        
        // 6th attempt should show locked message
        $response = $this->post('/login', [
            'identifier' => $user->email,
            'password' => 'correct-password',
        ]);
        
        $response->assertSessionHasErrors('identifier');
        $this->assertStringContainsString('terkunci', session('errors')->first('identifier'));
    }

    /** @test */
    public function it_prevents_timing_attacks()
    {
        $validUser = User::factory()->create(['email' => 'valid@example.com']);
        
        // Test dengan valid user
        $start1 = microtime(true);
        $this->post('/login', [
            'identifier' => 'valid@example.com',
            'password' => 'wrong-password',
        ]);
        $time1 = microtime(true) - $start1;
        
        // Test dengan invalid user
        $start2 = microtime(true);
        $this->post('/login', [
            'identifier' => 'invalid@example.com',
            'password' => 'wrong-password',
        ]);
        $time2 = microtime(true) - $start2;
        
        // Time difference should be minimal (< 100ms)
        $timeDiff = abs($time1 - $time2);
        $this->assertLessThan(0.1, $timeDiff);
    }

    /** @test */
    public function it_rejects_weak_passwords()
    {
        $weakPasswords = [
            'password',
            '12345678',
            'qwerty',
            'abc123',
        ];
        
        foreach ($weakPasswords as $weakPassword) {
            $response = $this->post('/register', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => $weakPassword,
                'password_confirmation' => $weakPassword,
            ]);
            
            $response->assertSessionHasErrors('password');
        }
    }

    /** @test */
    public function it_regenerates_session_on_login()
    {
        $user = User::factory()->create();
        
        $oldSessionId = session()->getId();
        
        $this->post('/login', [
            'identifier' => $user->email,
            'password' => 'password',
        ]);
        
        $newSessionId = session()->getId();
        
        $this->assertNotEquals($oldSessionId, $newSessionId);
    }

    /** @test */
    public function it_logs_failed_login_attempts()
    {
        $this->post('/login', [
            'identifier' => 'nonexistent@example.com',
            'password' => 'wrong-password',
        ]);
        
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'failed_login',
            'status' => 'failed',
        ]);
    }

    /** @test */
    public function it_prevents_login_for_inactive_users()
    {
        $user = User::factory()->create(['status' => 'inactive']);
        
        $response = $this->post('/login', [
            'identifier' => $user->email,
            'password' => 'password',
        ]);
        
        $response->assertSessionHasErrors();
        $this->assertGuest();
    }
}
```

---

## 📚 REFERENCES

### OWASP Resources
- [OWASP Top 10 2021](https://owasp.org/Top10/)
- [OWASP Authentication Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html)
- [OWASP Session Management](https://cheatsheetseries.owasp.org/cheatsheets/Session_Management_Cheat_Sheet.html)
- [OWASP Password Storage](https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html)

### Laravel Security
- [Laravel Security Best Practices](https://laravel.com/docs/12.x/security)
- [Laravel Authentication](https://laravel.com/docs/12.x/authentication)
- [Laravel Authorization](https://laravel.com/docs/12.x/authorization)
- [Laravel Rate Limiting](https://laravel.com/docs/12.x/routing#rate-limiting)

### Compliance Standards
- PCI DSS 4.0 (untuk payment processing)
- GDPR (data protection)
- ISO 27001 (information security)

---

## 📞 NEXT STEPS

1. **Review dengan Team**: Diskusikan findings dengan development team
2. **Prioritize Fixes**: Implement P0 items sebelum production launch
3. **Create Tickets**: Buat GitHub issues/Jira tickets untuk each finding
4. **Security Training**: Educate team tentang secure coding practices
5. **Regular Audits**: Schedule quarterly security reviews
6. **Penetration Testing**: Hire external security firm untuk comprehensive testing
7. **Bug Bounty**: Consider bug bounty program setelah launch

---

**Audit Completed**: 2025-12-22  
**Next Review Date**: 2026-03-22 (3 months)

---



