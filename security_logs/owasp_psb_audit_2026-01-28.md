# 🔒 OWASP Security Audit Report - PSB Module

**Date:** January 28, 2026  
**Files Analyzed:** 9 files  
**Focus Areas:** A01, A02, A03, A04, A05, A07

---

## Summary

**Total Findings:** 7  
| Severity | Count |
|----------|-------|
| 🔴 Critical | 2 |
| 🟠 High | 3 |
| 🟡 Medium | 2 |
| 🟢 Low | 0 |

---

## 🔴 CRITICAL Findings

### 1. A01 - Broken Access Control: Document Ownership Validation Missing

**File:** `app/Http/Requests/Psb/RequestRevisionRequest.php:33`  
**OWASP Category:** A01 - Broken Access Control

**Issue:**
RequestRevisionRequest memvalidasi bahwa document ID exists di tabel `psb_documents`, tetapi tidak memverifikasi bahwa dokumen tersebut milik registration yang sedang direvisi. Ini memungkinkan admin memodifikasi dokumen dari registration lain.

**Vulnerable Code:**
```php
public function rules(): array
{
    return [
        'documents' => ['required', 'array', 'min:1'],
        'documents.*.id' => ['required', 'exists:psb_documents,id'], // ❌ Tidak cek ownership
        'documents.*.revision_note' => ['required', 'string', 'max:500'],
    ];
}
```

**Secure Fix:**
```php
public function rules(): array
{
    $registrationId = $this->route('registration')->id;
    
    return [
        'documents' => ['required', 'array', 'min:1'],
        'documents.*.id' => [
            'required',
            'exists:psb_documents,id,psb_registration_id,'.$registrationId, // ✅ Validasi ownership
        ],
        'documents.*.revision_note' => ['required', 'string', 'max:500'],
    ];
}
```

**Impact:** Admin dapat memodifikasi dokumen dari registration lain tanpa otorisasi  
**Priority:** P0 (immediate)

---

### 2. A05 - Security Misconfiguration: Exception Messages Exposed to Users

**File:** `app/Http/Controllers/Admin/AdminPsbController.php:112,136,159`  
**OWASP Category:** A05 - Security Misconfiguration

**Issue:**
Exception messages langsung ditampilkan ke user melalui flash message, yang dapat mengungkap informasi internal sistem, stack traces, atau detail implementasi.

**Vulnerable Code:**
```php
catch (\Exception $e) {
    return back()->with('error', 'Gagal menyetujui pendaftaran: '.$e->getMessage()); // ❌ Expose exception message
}
```

**Secure Fix:**
```php
catch (\Exception $e) {
    // Log error dengan detail lengkap untuk debugging
    \Log::error('Failed to approve registration', [
        'registration_id' => $registration->id,
        'user_id' => auth()->id(),
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);
    
    // Return generic error message untuk user
    return back()->with('error', 'Gagal menyetujui pendaftaran. Silakan coba lagi atau hubungi administrator.');
}
```

**Impact:** Information disclosure yang dapat membantu attacker memahami struktur sistem  
**Priority:** P0 (immediate)

---

## 🟠 HIGH Findings

### 3. A01 - Broken Access Control: Missing Explicit Authorization Check

**File:** `app/Http/Controllers/Admin/AdminPsbController.php:80`  
**OWASP Category:** A01 - Broken Access Control

**Issue:**
Method `show()` menggunakan route model binding tetapi tidak memiliki explicit authorization check. Meskipun middleware `role:SUPERADMIN,ADMIN` sudah ada, tidak ada verifikasi bahwa admin hanya dapat mengakses registrations dari academic year yang sesuai atau tidak ada business rule lain yang perlu dicek.

**Vulnerable Code:**
```php
public function show(PsbRegistration $registration): Response
{
    $detail = $this->psbService->getRegistrationDetail($registration);
    // ❌ Tidak ada explicit authorization check
    return Inertia::render('Admin/Psb/Registrations/Show', [...]);
}
```

**Secure Fix:**
```php
public function show(PsbRegistration $registration): Response
{
    // ✅ Verifikasi registration dapat diakses oleh admin
    // (Optional: tambahkan business rule jika diperlukan, misalnya hanya active year)
    $activeYear = AcademicYear::where('is_active', true)->first();
    
    if ($activeYear && $registration->academic_year_id !== $activeYear->id) {
        abort(403, 'Anda tidak memiliki akses ke pendaftaran ini.');
    }
    
    $detail = $this->psbService->getRegistrationDetail($registration);
    return Inertia::render('Admin/Psb/Registrations/Show', [...]);
}
```

**Impact:** Potensi akses ke data registration yang tidak seharusnya diakses  
**Priority:** P1 (this sprint)

---

### 4. A04 - Insecure Design: Missing Rate Limiting on Sensitive Operations

**File:** `routes/admin.php:70-72`  
**OWASP Category:** A04 - Insecure Design

**Issue:**
Endpoint approve, reject, dan requestRevision tidak memiliki rate limiting, memungkinkan brute force atau abuse terhadap operasi sensitif ini.

**Vulnerable Code:**
```php
Route::post('registrations/{registration}/approve', [AdminPsbController::class, 'approve'])
    ->name('registrations.approve'); // ❌ Tidak ada throttle middleware
Route::post('registrations/{registration}/reject', [AdminPsbController::class, 'reject'])
    ->name('registrations.reject');
Route::post('registrations/{registration}/revision', [AdminPsbController::class, 'requestRevision'])
    ->name('registrations.revision');
```

**Secure Fix:**
```php
Route::post('registrations/{registration}/approve', [AdminPsbController::class, 'approve'])
    ->middleware('throttle:10,1') // ✅ Max 10 requests per minute
    ->name('registrations.approve');
Route::post('registrations/{registration}/reject', [AdminPsbController::class, 'reject'])
    ->middleware('throttle:10,1')
    ->name('registrations.reject');
Route::post('registrations/{registration}/revision', [AdminPsbController::class, 'requestRevision'])
    ->middleware('throttle:10,1')
    ->name('registrations.revision');
```

**Impact:** Abuse terhadap operasi verifikasi, potensi DoS atau automated attacks  
**Priority:** P1 (this sprint)

---

### 5. A08 - Data Integrity Failures: Missing Document Ownership Verification in Service

**File:** `app/Services/PsbService.php:411-417`  
**OWASP Category:** A08 - Data Integrity Failures

**Issue:**
Method `requestDocumentRevision()` tidak memverifikasi bahwa semua document IDs yang diberikan benar-benar milik registration yang dimaksud sebelum melakukan update.

**Vulnerable Code:**
```php
foreach ($documents as $docData) {
    PsbDocument::where('id', $docData['id'])
        ->where('psb_registration_id', $registration->id) // ✅ Ada check, tapi...
        ->update([...]);
}
// ❌ Tidak ada verifikasi bahwa semua documents berhasil diupdate
// Jika document ID tidak milik registration, update akan silent fail
```

**Secure Fix:**
```php
return DB::transaction(function () use ($registration, $documents) {
    $registration->update(['status' => PsbRegistration::STATUS_DOCUMENT_REVIEW]);
    
    $documentIds = collect($documents)->pluck('id')->toArray();
    
    // ✅ Verifikasi semua documents milik registration ini
    $validDocuments = PsbDocument::where('psb_registration_id', $registration->id)
        ->whereIn('id', $documentIds)
        ->pluck('id')
        ->toArray();
    
    if (count($validDocuments) !== count($documentIds)) {
        throw new \Exception('Satu atau lebih dokumen tidak valid atau tidak milik pendaftaran ini.');
    }
    
    foreach ($documents as $docData) {
        PsbDocument::where('id', $docData['id'])
            ->where('psb_registration_id', $registration->id)
            ->update([
                'status' => PsbDocument::STATUS_REJECTED,
                'revision_note' => $docData['revision_note'],
            ]);
    }
    
    return true;
});
```

**Impact:** Potensi modifikasi dokumen yang tidak valid tanpa error yang jelas  
**Priority:** P1 (this sprint)

---

## 🟡 MEDIUM Findings

### 6. A07 - Authentication Failures: Role Check Without Authentication Verification

**File:** `app/Http/Requests/Psb/ApproveRegistrationRequest.php:19-22`  
**OWASP Category:** A07 - Identification and Authentication Failures

**Issue:**
Form Request melakukan role check tetapi tidak secara eksplisit memverifikasi bahwa user sudah authenticated. Meskipun middleware `auth` sudah ada di route, lebih baik defensive dengan explicit check.

**Vulnerable Code:**
```php
public function authorize(): bool
{
    return $this->user() && in_array($this->user()->role, ['SUPERADMIN', 'ADMIN']);
    // ❌ Tidak explicit check untuk authentication
}
```

**Secure Fix:**
```php
public function authorize(): bool
{
    // ✅ Explicit check untuk authentication dan authorization
    if (!$this->user()) {
        return false;
    }
    
    return in_array($this->user()->role, ['SUPERADMIN', 'ADMIN']);
}
```

**Note:** Ini adalah defensive programming. Middleware sudah handle authentication, tapi explicit check lebih aman.

**Impact:** Potensi edge case jika middleware tidak bekerja dengan benar  
**Priority:** P2 (next sprint)

---

### 7. A04 - Insecure Design: No Rate Limiting on Search/Filter Endpoint

**File:** `app/Http/Controllers/Admin/AdminPsbController.php:55`  
**OWASP Category:** A04 - Insecure Design

**Issue:**
Endpoint `registrations()` menerima user input untuk search dan filter tanpa rate limiting, memungkinkan abuse terhadap database queries.

**Vulnerable Code:**
```php
public function registrations(Request $request): Response
{
    $filters = [
        'status' => $request->input('status'),
        'search' => $request->input('search'), // ❌ Tidak ada rate limit
        'start_date' => $request->input('start_date'),
        'end_date' => $request->input('end_date'),
    ];
    // ...
}
```

**Secure Fix:**
Tambahkan throttle middleware di route:
```php
Route::get('registrations', [AdminPsbController::class, 'registrations'])
    ->middleware('throttle:60,1') // ✅ Max 60 requests per minute untuk search
    ->name('registrations.index');
```

**Impact:** Potensi DoS melalui expensive search queries  
**Priority:** P2 (next sprint)

---

## ✅ Positive Security Practices Found

1. ✅ **Form Requests digunakan** untuk validasi (A04)
2. ✅ **Authorization checks** ada di Form Requests (A01)
3. ✅ **Middleware auth dan role** diterapkan di routes (A01)
4. ✅ **Database transactions** digunakan untuk critical operations (A08)
5. ✅ **Eloquent ORM** digunakan, bukan raw queries (A03)
6. ✅ **Parameterized queries** untuk LIKE operations (A03)
7. ✅ **Mass assignment protection** dengan `$fillable` (A08)

---

## Immediate Actions Required

1. **P0 - Fix Document Ownership Validation** (RequestRevisionRequest)
2. **P0 - Fix Exception Message Exposure** (AdminPsbController)
3. **P1 - Add Rate Limiting** pada approve/reject/revision endpoints
4. **P1 - Add Document Ownership Verification** di PsbService
5. **P1 - Add Explicit Authorization Check** di show method

---

## Files with Issues

- `app/Http/Controllers/Admin/AdminPsbController.php` - 3 issues (1 Critical, 1 High, 1 Medium)
- `app/Http/Requests/Psb/RequestRevisionRequest.php` - 1 issue (Critical)
- `app/Services/PsbService.php` - 1 issue (High)
- `routes/admin.php` - 1 issue (High)
- `app/Http/Requests/Psb/ApproveRegistrationRequest.php` - 1 issue (Medium)

---

## Notes

- Semua Form Requests sudah menggunakan proper validation rules
- Tidak ada SQL injection vulnerabilities ditemukan (A03)
- Tidak ada cryptographic failures ditemukan (A02)
- Notifications tidak mengekspos sensitive data secara tidak perlu
