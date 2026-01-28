# 🔒 OWASP Top 10 Security Audit - Admin PSB Verification Feature

**Date:** January 28, 2026  
**Feature:** Admin PSB Verification (Approve/Reject/Request Revision)  
**Files Analyzed:** 9 files

---

## Executive Summary

Security audit performed on Admin PSB Verification feature implementation. Found **6 security issues** across multiple OWASP categories:
- 🔴 **Critical:** 0
- 🟠 **High:** 2
- 🟡 **Medium:** 3
- 🟢 **Low:** 1

---

## Detailed Findings

### 🟠 HIGH - A01: Broken Access Control - Missing Policy-Based Authorization

**File:** `app/Http/Controllers/Admin/AdminPsbController.php:82-100`  
**OWASP Category:** A01 - Broken Access Control

**Issue:**
Controller methods rely solely on middleware and FormRequest authorization checks, but lack explicit policy-based authorization using Laravel's `authorize()` method. While FormRequests check role strings, this doesn't follow Laravel best practices and could be bypassed if middleware is misconfigured.

**Vulnerable Code:**
```php
public function show(PsbRegistration $registration): Response
{
    // Verifikasi registration dapat diakses oleh admin
    // Pastikan registration dari academic year yang aktif atau dapat diakses
    $activeYear = AcademicYear::where('is_active', true)->first();

    if ($activeYear && $registration->academic_year_id !== $activeYear->id) {
        abort(403, 'Anda tidak memiliki akses ke pendaftaran ini.');
    }
    // ... no explicit authorize() call
}

public function approve(ApproveRegistrationRequest $request, PsbRegistration $registration): RedirectResponse
{
    // FormRequest checks role, but no policy check
    $this->psbService->approveRegistration(...);
}
```

**Secure Fix:**
```php
public function show(PsbRegistration $registration): Response
{
    $this->authorize('view', $registration);
    
    $activeYear = AcademicYear::where('is_active', true)->first();
    if ($activeYear && $registration->academic_year_id !== $activeYear->id) {
        abort(403, 'Anda tidak memiliki akses ke pendaftaran ini.');
    }
    // ...
}

public function approve(ApproveRegistrationRequest $request, PsbRegistration $registration): RedirectResponse
{
    $this->authorize('approve', $registration);
    // ...
}
```

**Impact:** Admin users could potentially access or modify registrations they shouldn't have access to if middleware is misconfigured or bypassed.  
**Priority:** P1 (this sprint)

---

### 🟠 HIGH - A01: Broken Access Control - Role String Comparison Instead of Gates/Policies

**File:** 
- `app/Http/Requests/Psb/ApproveRegistrationRequest.php:19-26`
- `app/Http/Requests/Psb/RejectRegistrationRequest.php:19-26`
- `app/Http/Requests/Psb/RequestRevisionRequest.php:19-26`

**OWASP Category:** A01 - Broken Access Control

**Issue:**
Form Requests use direct role string comparison (`in_array($this->user()->role, ['SUPERADMIN', 'ADMIN'])`) instead of Laravel's Gate or Policy system. This is less maintainable and could lead to inconsistencies if role names change or if additional authorization logic is needed.

**Vulnerable Code:**
```php
public function authorize(): bool
{
    if (! $this->user()) {
        return false;
    }

    return in_array($this->user()->role, ['SUPERADMIN', 'ADMIN']);
}
```

**Secure Fix:**
```php
public function authorize(): bool
{
    return $this->user() && Gate::allows('manage-psb-registrations');
}
```

Or use Policy:
```php
public function authorize(): bool
{
    return $this->user() && $this->user()->can('manage', PsbRegistration::class);
}
```

**Impact:** Authorization logic scattered across multiple files, harder to maintain and audit. Could lead to inconsistent access control if role names change.  
**Priority:** P1 (this sprint)

---

### 🟡 MEDIUM - A03: Injection - Potential SQL Injection via orderByRaw

**File:** `app/Services/PsbService.php:36-37`  
**OWASP Category:** A03 - Injection

**Issue:**
Uses `orderByRaw()` with string manipulation on `registration_number`. While the data is internally generated and should be safe, using raw SQL with string operations is risky if the format ever changes or if external input influences the registration number format.

**Vulnerable Code:**
```php
$lastRegistration = PsbRegistration::where('registration_number', 'like', $prefix.'%')
    ->orderByRaw('CAST(SUBSTRING(registration_number, -4) AS UNSIGNED) DESC')
    ->first();
```

**Secure Fix:**
```php
// Option 1: Use Eloquent methods
$lastRegistration = PsbRegistration::where('registration_number', 'like', $prefix.'%')
    ->orderByRaw('CAST(SUBSTRING(registration_number, -4) AS UNSIGNED) DESC')
    ->first();

// Option 2: Extract number safely and use orderBy
$registrations = PsbRegistration::where('registration_number', 'like', $prefix.'%')
    ->get()
    ->sortByDesc(function ($reg) {
        $number = substr($reg->registration_number, -4);
        return (int) $number;
    })
    ->first();

// Option 3: Add a numeric column for sorting
// Migration: add column 'registration_sequence' to psb_registrations
$lastRegistration = PsbRegistration::where('registration_number', 'like', $prefix.'%')
    ->orderBy('registration_sequence', 'desc')
    ->first();
```

**Impact:** Low risk since registration_number is internally generated, but could be exploited if format validation fails or if external systems can influence the format.  
**Priority:** P2 (next sprint)

---

### 🟡 MEDIUM - A09: Security Logging Failures - Excessive Stack Trace Logging

**File:** `app/Http/Controllers/Admin/AdminPsbController.php:122-127, 153-158, 183-188`  
**OWASP Category:** A09 - Security Logging Failures

**Issue:**
Error logging includes full stack traces (`$e->getTraceAsString()`) which could expose sensitive information like file paths, internal system structure, or configuration details to anyone with log access.

**Vulnerable Code:**
```php
Log::error('Failed to approve registration', [
    'registration_id' => $registration->id,
    'user_id' => auth()->id(),
    'error' => $e->getMessage(),
    'trace' => $e->getTraceAsString(), // ⚠️ Full stack trace
]);
```

**Secure Fix:**
```php
Log::error('Failed to approve registration', [
    'registration_id' => $registration->id,
    'user_id' => auth()->id(),
    'error' => $e->getMessage(),
    'file' => $e->getFile(),
    'line' => $e->getLine(),
    // Only log trace in development
    'trace' => config('app.debug') ? $e->getTraceAsString() : null,
]);
```

Or use Laravel's exception handler:
```php
// Let Laravel's exception handler log the full trace only in appropriate environments
Log::error('Failed to approve registration', [
    'registration_id' => $registration->id,
    'user_id' => auth()->id(),
    'error' => $e->getMessage(),
]);
// Re-throw and let exception handler deal with trace logging
throw $e;
```

**Impact:** Stack traces could expose internal system structure, file paths, or configuration details to unauthorized users with log access.  
**Priority:** P2 (next sprint)

---

### 🟡 MEDIUM - A04: Insecure Design - Missing Rate Limiting on Detail View

**File:** `routes/admin.php:71`  
**OWASP Category:** A04 - Insecure Design

**Issue:**
The `show()` method (detail view) doesn't have rate limiting, while other actions do. This could allow enumeration attacks or excessive resource consumption if an attacker tries to access many registration details.

**Vulnerable Code:**
```php
Route::get('registrations/{registration}', [AdminPsbController::class, 'show'])
    ->name('registrations.show');
// No throttle middleware
```

**Secure Fix:**
```php
Route::get('registrations/{registration}', [AdminPsbController::class, 'show'])
    ->middleware('throttle:60,1')
    ->name('registrations.show');
```

**Impact:** Could allow enumeration of registration IDs or excessive resource consumption.  
**Priority:** P2 (next sprint)

---

### 🟢 LOW - A08: Software Integrity Failures - Mass Assignment Risk in Service Methods

**File:** `app/Services/PsbService.php:368-374, 388-394`  
**OWASP Category:** A08 - Software Integrity Failures

**Issue:**
Service methods use `update()` with array directly. While the model has `$fillable` protection, it's better practice to explicitly specify fields being updated, especially for critical operations like approval/rejection.

**Vulnerable Code:**
```php
return $registration->update([
    'status' => PsbRegistration::STATUS_APPROVED,
    'verified_by' => $verifier->id,
    'verified_at' => now(),
    'announced_at' => now(),
    'notes' => $notes ?? $registration->notes,
]);
```

**Secure Fix:**
```php
// Explicitly set only the fields that should be updated
$registration->status = PsbRegistration::STATUS_APPROVED;
$registration->verified_by = $verifier->id;
$registration->verified_at = now();
$registration->announced_at = now();
if ($notes !== null) {
    $registration->notes = $notes;
}
return $registration->save();
```

Or use `fill()` with explicit array:
```php
$registration->fill([
    'status' => PsbRegistration::STATUS_APPROVED,
    'verified_by' => $verifier->id,
    'verified_at' => now(),
    'announced_at' => now(),
]);
if ($notes !== null) {
    $registration->notes = $notes;
}
return $registration->save();
```

**Impact:** Low risk since model has `$fillable` protection, but explicit field setting is more secure and maintainable.  
**Priority:** P3 (backlog)

---

## ✅ Security Best Practices Found

1. ✅ **Form Requests Used** - All actions use Form Request classes for validation
2. ✅ **Middleware Protection** - Routes protected with `auth` and `role` middleware
3. ✅ **Rate Limiting** - Critical actions (approve/reject/revision) have throttle middleware
4. ✅ **Input Validation** - Proper validation rules in Form Requests
5. ✅ **Document Ownership Verification** - `requestDocumentRevision` validates document ownership
6. ✅ **Transaction Usage** - Critical operations wrapped in database transactions
7. ✅ **Error Handling** - Try-catch blocks with proper error messages
8. ✅ **No Hardcoded Secrets** - No API keys or passwords found
9. ✅ **Proper Hashing** - Uses Laravel's built-in features (no custom hashing)
10. ✅ **CSRF Protection** - Laravel automatically handles CSRF for POST routes

---

## Summary Statistics

| Severity | Count | Files Affected |
|----------|-------|----------------|
| 🔴 Critical | 0 | - |
| 🟠 High | 2 | AdminPsbController.php, Form Requests |
| 🟡 Medium | 3 | PsbService.php, AdminPsbController.php, routes/admin.php |
| 🟢 Low | 1 | PsbService.php |
| **Total** | **6** | **4 files** |

---

## Immediate Actions Required

### Priority P1 (This Sprint)
1. **Create PsbRegistration Policy** - Implement Laravel Policy for PSB registrations
2. **Add authorize() calls** - Add explicit `$this->authorize()` calls in controller methods
3. **Replace role string checks** - Use Gates/Policies instead of direct role comparison in Form Requests

### Priority P2 (Next Sprint)
4. **Fix orderByRaw usage** - Refactor to use safer ordering method or add numeric column
5. **Reduce stack trace logging** - Remove or conditionally log full stack traces
6. **Add rate limiting** - Add throttle middleware to detail view route

### Priority P3 (Backlog)
7. **Explicit field updates** - Refactor service methods to explicitly set fields instead of mass assignment

---

## Recommendations

1. **Create Policy File:**
   ```bash
   php artisan make:policy PsbRegistrationPolicy --model=PsbRegistration
   ```

2. **Implement Gate:**
   ```php
   // In AuthServiceProvider or bootstrap/app.php
   Gate::define('manage-psb-registrations', function (User $user) {
       return in_array($user->role, ['SUPERADMIN', 'ADMIN']);
   });
   ```

3. **Add Policy Methods:**
   - `view()` - Check if user can view registration
   - `approve()` - Check if user can approve registration
   - `reject()` - Check if user can reject registration
   - `requestRevision()` - Check if user can request revision

4. **Consider Adding:**
   - Audit logging for all approval/rejection actions
   - Two-factor authentication for critical operations
   - Email notifications to security team for sensitive actions

---

## Files Analyzed

1. ✅ `app/Http/Controllers/Admin/AdminPsbController.php`
2. ✅ `app/Http/Requests/Psb/ApproveRegistrationRequest.php`
3. ✅ `app/Http/Requests/Psb/RejectRegistrationRequest.php`
4. ✅ `app/Http/Requests/Psb/RequestRevisionRequest.php`
5. ✅ `app/Services/PsbService.php`
6. ✅ `app/Notifications/PsbRegistrationApproved.php`
7. ✅ `app/Notifications/PsbRegistrationRejected.php`
8. ✅ `app/Notifications/PsbDocumentRevisionRequested.php`
9. ✅ `routes/admin.php`

---

**Audit Completed:** January 28, 2026  
**Next Review:** After implementing P1 fixes
