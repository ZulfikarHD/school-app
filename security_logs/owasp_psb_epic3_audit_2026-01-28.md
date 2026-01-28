# 🔒 OWASP Top 10 Security Audit - Epic 3 PSB (Announcement & Re-registration)

**Date:** January 28, 2026  
**Scope:** Epic 3 PSB - Announcement & Re-registration Feature  
**Files Analyzed:** 12 files (4 Controllers, 4 Form Requests, 2 Notifications, 1 Service, 1 Model)

---

## Executive Summary

**Total Findings:** 8 vulnerabilities  
**Critical:** 1  
**High:** 3  
**Medium:** 2  
**Low:** 2

---

## 🔴 CRITICAL Findings

### 1. 🔴 CRITICAL - IDOR Vulnerability in Payment Verification

**File:** `app/Http/Controllers/Admin/AdminPsbPaymentController.php:63`  
**OWASP Category:** A01 - Broken Access Control

**Issue:**
Route model binding `PsbPayment $payment` does not verify that the admin has permission to verify this specific payment. An admin could potentially verify payments from different academic years or manipulate payment IDs.

**Vulnerable Code:**
```php
public function verify(VerifyPaymentRequest $request, PsbPayment $payment): RedirectResponse
{
    // Pastikan payment masih pending
    if ($payment->status !== PsbPayment::STATUS_PENDING) {
        return back()->with('error', 'Pembayaran sudah diverifikasi sebelumnya.');
    }
    // ... no check if payment belongs to active academic year or if admin can access it
}
```

**Secure Fix:**
```php
public function verify(VerifyPaymentRequest $request, PsbPayment $payment): RedirectResponse
{
    // Verify payment belongs to active academic year
    $activeYear = AcademicYear::where('is_active', true)->first();
    
    if (!$activeYear || $payment->registration->academic_year_id !== $activeYear->id) {
        abort(403, 'Pembayaran tidak dapat diakses untuk tahun ajaran ini.');
    }
    
    // Pastikan payment masih pending
    if ($payment->status !== PsbPayment::STATUS_PENDING) {
        return back()->with('error', 'Pembayaran sudah diverifikasi sebelumnya.');
    }
    
    // ... rest of code
}
```

**Impact:** Admin could verify payments from inactive academic years or manipulate payment verification process  
**Priority:** P0 (immediate)

---

## 🟠 HIGH Findings

### 2. 🟠 HIGH - Sensitive Data in Logs

**File:** `app/Http/Controllers/Admin/AdminPsbAnnouncementController.php:88`  
**OWASP Category:** A09 - Security Logging Failures

**Issue:**
Logging `registration_ids` array could expose sensitive information. While IDs themselves aren't highly sensitive, logging arrays can reveal patterns and should be sanitized.

**Vulnerable Code:**
```php
Log::error('Failed to bulk announce registrations', [
    'registration_ids' => $request->validated('registration_ids'), // Array of IDs logged
    'user_id' => auth()->id(),
    'error' => $e->getMessage(),
]);
```

**Secure Fix:**
```php
Log::error('Failed to bulk announce registrations', [
    'registration_count' => count($request->validated('registration_ids')), // Log count instead
    'user_id' => auth()->id(),
    'error' => $e->getMessage(),
]);
```

**Impact:** Information disclosure through logs, potential pattern analysis  
**Priority:** P1 (this sprint)

---

### 3. 🟠 HIGH - Missing Authorization Check in Bulk Announce

**File:** `app/Http/Controllers/Admin/AdminPsbAnnouncementController.php:66`  
**OWASP Category:** A01 - Broken Access Control

**Issue:**
The `bulkAnnounce` method queries registrations using `whereIn('id', $registrationIds)` but doesn't verify that all IDs belong to approved registrations that haven't been announced. While there's a check for `STATUS_APPROVED` and `whereNull('announced_at')`, the validation in FormRequest only checks `exists:psb_registrations,id` without status validation.

**Vulnerable Code:**
```php
$registrations = PsbRegistration::whereIn('id', $registrationIds)
    ->where('status', PsbRegistration::STATUS_APPROVED)
    ->whereNull('announced_at')
    ->get();
```

**Secure Fix:**
Add validation in `BulkAnnounceRequest` to ensure IDs belong to approved, unannounced registrations:

```php
// In BulkAnnounceRequest.php
public function rules(): array
{
    return [
        'registration_ids' => ['required', 'array', 'min:1'],
        'registration_ids.*' => [
            'required',
            'integer',
            'exists:psb_registrations,id,status,approved,announced_at,NULL',
        ],
        'send_notification' => ['boolean'],
    ];
}
```

**Impact:** Potential announcement of non-approved registrations if validation is bypassed  
**Priority:** P1 (this sprint)

---

### 4. 🟠 HIGH - Hardcoded Role Strings

**File:** Multiple files  
**OWASP Category:** A05 - Security Misconfiguration

**Issue:**
Role checks use hardcoded strings instead of constants, making the code error-prone and harder to maintain.

**Vulnerable Code:**
```php
// BulkAnnounceRequest.php:25
return in_array($this->user()->role, ['SUPERADMIN', 'ADMIN']);

// UploadPaymentRequest.php:26
if (! $user || $user->role !== 'PARENT') {

// UploadPaymentRequest.php:39
return in_array($registration->status, ['approved', 're_registration']);
```

**Secure Fix:**
Create a `UserRole` enum or constants:

```php
// app/Enums/UserRole.php
enum UserRole: string
{
    case SUPERADMIN = 'SUPERADMIN';
    case ADMIN = 'ADMIN';
    case PRINCIPAL = 'PRINCIPAL';
    case TEACHER = 'TEACHER';
    case PARENT = 'PARENT';
}

// Then use:
return in_array($this->user()->role, [UserRole::SUPERADMIN->value, UserRole::ADMIN->value]);
```

**Impact:** Risk of typos leading to authorization bypass, harder to maintain  
**Priority:** P1 (this sprint)

---

## 🟡 MEDIUM Findings

### 5. 🟡 MEDIUM - Missing Rate Limiting on File Upload

**File:** `app/Http/Controllers/Parent/ParentPsbController.php:141`  
**OWASP Category:** A04 - Insecure Design

**Issue:**
File upload endpoint doesn't have explicit rate limiting. While Laravel has default throttling, sensitive operations like payment proof uploads should have stricter limits.

**Vulnerable Code:**
```php
// routes/parent.php:50
Route::post('/payment', [ParentPsbController::class, 'uploadPayment'])->name('upload-payment');
// No throttle middleware
```

**Secure Fix:**
```php
// routes/parent.php
Route::post('/payment', [ParentPsbController::class, 'uploadPayment'])
    ->middleware('throttle:5,1') // 5 uploads per minute
    ->name('upload-payment');
```

**Impact:** Potential DoS through file upload spam, storage exhaustion  
**Priority:** P2 (next sprint)

---

### 6. 🟡 MEDIUM - Weak Password Generation Pattern

**File:** `app/Services/PsbService.php:1004`  
**OWASP Category:** A07 - Authentication Failures

**Issue:**
Password generation uses predictable pattern `Ortu{NIS}` which could be guessed if NIS is known. While this is for first-time login, it's still a weak pattern.

**Vulnerable Code:**
```php
// Create new parent account dengan password = Ortu{NIS}
$password = 'Ortu'.$student->nis;
```

**Secure Fix:**
```php
// Generate random password and require change on first login
$password = Str::random(12); // Random password
// Or use: Hash::make(Str::random(16))

$user = User::create([
    // ...
    'password' => Hash::make($password),
    'is_first_login' => true, // Force password change
]);
```

**Impact:** Predictable passwords could be exploited if NIS is leaked  
**Priority:** P2 (next sprint)

---

## 🟢 LOW Findings

### 7. 🟢 LOW - Missing Input Sanitization in Notes Fields

**File:** `app/Http/Requests/Psb/VerifyPaymentRequest.php:37`  
**OWASP Category:** A03 - Injection

**Issue:**
Notes fields accept raw strings without sanitization. While Laravel's Blade escapes by default, it's better to sanitize at input level for XSS prevention.

**Vulnerable Code:**
```php
'notes' => ['nullable', 'string', 'max:500'],
'rejection_reason' => [
    'required_if:approved,false',
    'nullable',
    'string',
    'min:10',
    'max:500',
],
```

**Secure Fix:**
```php
'notes' => ['nullable', 'string', 'max:500', 'regex:/^[\p{L}\p{N}\s.,!?()-]+$/u'], // Only allow safe characters
'rejection_reason' => [
    'required_if:approved,false',
    'nullable',
    'string',
    'min:10',
    'max:500',
    'regex:/^[\p{L}\p{N}\s.,!?()-]+$/u',
],
```

**Impact:** Low risk due to Blade auto-escaping, but defense in depth is better  
**Priority:** P3 (backlog)

---

### 8. 🟢 LOW - Missing Audit Logging for Critical Operations

**File:** `app/Services/PsbService.php:658`  
**OWASP Category:** A09 - Security Logging Failures

**Issue:**
Payment verification and student creation operations don't have explicit audit logging. While Laravel logs errors, successful critical operations should be audited.

**Vulnerable Code:**
```php
public function verifyPayment(PsbPayment $payment, User $verifier, bool $approved, ?string $notes = null): bool
{
    return DB::transaction(function () use ($payment, $verifier, $approved, $notes) {
        $payment->update([...]);
        // No audit log for successful verification
    });
}
```

**Secure Fix:**
```php
public function verifyPayment(PsbPayment $payment, User $verifier, bool $approved, ?string $notes = null): bool
{
    return DB::transaction(function () use ($payment, $verifier, $approved, $notes) {
        $payment->update([...]);
        
        // Audit log
        Log::info('Payment verified', [
            'payment_id' => $payment->id,
            'registration_id' => $payment->registration_id,
            'verifier_id' => $verifier->id,
            'approved' => $approved,
            'ip_address' => request()->ip(),
        ]);
        
        // ... rest of code
    });
}
```

**Impact:** Limited audit trail for compliance and security investigations  
**Priority:** P3 (backlog)

---

## ✅ Positive Security Practices Found

1. ✅ **Form Requests Used** - All controllers use Form Requests for validation
2. ✅ **Authorization Checks** - Form Requests include `authorize()` methods
3. ✅ **File Validation** - Upload endpoints validate file types and sizes
4. ✅ **Database Transactions** - Critical operations use transactions
5. ✅ **Rate Limiting** - Admin routes have throttle middleware
6. ✅ **Middleware Protection** - Routes protected with `auth` and `role` middleware
7. ✅ **Mass Assignment Protection** - Models use `$fillable` arrays
8. ✅ **Password Hashing** - Uses `Hash::make()` correctly

---

## 📋 Recommendations Summary

### Immediate Actions (P0):
1. ✅ Add academic year verification in `AdminPsbPaymentController::verify()`

### This Sprint (P1):
2. ✅ Sanitize logs to avoid exposing registration IDs
3. ✅ Add status validation to `BulkAnnounceRequest`
4. ✅ Create UserRole enum/constants to replace hardcoded strings

### Next Sprint (P2):
5. ✅ Add rate limiting to file upload endpoints
6. ✅ Improve password generation for parent accounts

### Backlog (P3):
7. ✅ Add input sanitization for notes fields
8. ✅ Add audit logging for critical operations

---

## Files Requiring Changes

1. `app/Http/Controllers/Admin/AdminPsbPaymentController.php` - Add academic year check
2. `app/Http/Controllers/Admin/AdminPsbAnnouncementController.php` - Sanitize logs
3. `app/Http/Requests/Psb/BulkAnnounceRequest.php` - Add status validation
4. `app/Http/Requests/Psb/UploadPaymentRequest.php` - Use constants
5. `app/Http/Requests/Psb/VerifyPaymentRequest.php` - Use constants, add sanitization
6. `routes/parent.php` - Add rate limiting
7. `app/Services/PsbService.php` - Improve password generation, add audit logs
8. `app/Enums/UserRole.php` - Create new enum file (if doesn't exist)

---

**Audit Completed:** January 28, 2026  
**Next Review:** After fixes are implemented
