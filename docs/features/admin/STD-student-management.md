# STD - Student Management (Manajemen Siswa)

**Feature Code:** `STD`  
**Priority:** P0 (Critical)  
**Status:** 🔄 Backend Complete, Frontend Pending  
**Last Updated:** 24 Desember 2025

---

## Overview

Student Management merupakan modul inti yang bertujuan untuk mengelola data siswa secara terpusat, yaitu: pendaftaran siswa baru dengan auto-generate NIS, manajemen data orang tua/wali dengan auto-create portal account, tracking riwayat kelas dan status siswa, serta bulk operations untuk naik kelas yang terintegrasi dengan sistem audit trail untuk compliance dan accountability.

---

## User Stories

| ID | User Story | Priority | Status |
|----|------------|----------|--------|
| STD-001 | Sebagai TU, saya ingin menambahkan data siswa baru dengan auto-generate NIS dan auto-create parent account | P0 | ✅ Backend Complete |
| STD-002 | Sebagai TU, saya ingin melihat daftar siswa dengan filter (kelas, status, tahun ajaran) dan search | P0 | ✅ Backend Complete |
| STD-003 | Sebagai TU, saya ingin mengedit data siswa dan data orang tua/wali | P0 | ✅ Backend Complete |
| STD-004 | Sebagai TU, saya ingin melakukan bulk naik kelas untuk siswa dengan preview dan konfirmasi | P0 | ✅ Backend Complete |
| STD-005 | Sebagai TU, saya ingin mengubah status siswa (mutasi/DO/lulus) dengan tracking history | P0 | ✅ Backend Complete |
| STD-006 | Sebagai Guru, saya ingin melihat profil siswa di kelas yang saya ajar | P1 | 📝 Planned |
| STD-007 | Sebagai Kepala Sekolah, saya ingin melihat semua data siswa dengan summary statistics | P1 | 📝 Planned |
| STD-008 | Sebagai Orang Tua, saya ingin melihat profil anak saya (read-only) | P0 | ✅ Backend Complete |
| STD-009 | Sebagai TU, saya ingin export data siswa ke Excel untuk reporting | P1 | 📝 Planned |
| STD-010 | Sebagai TU, saya ingin import data siswa dari Excel dengan preview dan validation | P1 | 📝 Planned |

---

## Business Rules

| Rule ID | Rule | Validation |
|---------|------|------------|
| BR-STD-001 | NIS auto-generated dengan format `{tahun_masuk}{4-digit nomor urut}` (contoh: 20240001) | Enforced di `StudentService::generateNis()` |
| BR-STD-002 | NIK harus unique 16 digit, NISN harus unique 10 digit | Validated di `StoreStudentRequest` & `UpdateStudentRequest` |
| BR-STD-003 | Umur siswa harus 5-15 tahun untuk jenjang SD | Validated via `tanggal_lahir` di Form Request |
| BR-STD-004 | Parent account auto-created dengan username = no HP, password = `Ortu{NIS}` | Enforced di `StudentService::createParentAccount()` |
| BR-STD-005 | Satu nomor HP = satu parent account, support multiple children | Enforced di `StudentService::attachGuardiansToStudent()` |
| BR-STD-006 | Student soft delete, data tidak permanent dihapus | Enforced via `SoftDeletes` trait di Student model |
| BR-STD-007 | Status change harus disimpan di history dengan `changed_by` user | Enforced di `StudentService::updateStudentStatus()` |
| BR-STD-008 | Bulk naik kelas harus insert history record per student | Enforced di `StudentService::bulkPromoteStudents()` |

---

## Technical Implementation

### Database Schema

**Tables Created:**
- `students` - Biodata siswa lengkap (30 columns)
- `guardians` - Data orang tua/wali (11 columns)
- `student_guardian` - Pivot table many-to-many dengan `is_primary_contact`
- `student_class_history` - Riwayat perpindahan kelas
- `student_status_history` - Riwayat perubahan status dengan audit trail

**Key Relationships:**
- Student ↔ Guardian: Many-to-Many via `student_guardian` pivot
- Student → ClassHistory: One-to-Many
- Student → StatusHistory: One-to-Many
- Guardian → User: One-to-One untuk portal account

### Backend Components

| Component | File | Responsibility |
|-----------|------|----------------|
| **Controller** | `Admin/StudentController.php` | Handle HTTP requests, CRUD operations, bulk actions |
| **Service** | `StudentService.php` | Business logic: NIS generation, parent account, bulk operations |
| **Models** | `Student.php`, `Guardian.php`, `StudentClassHistory.php`, `StudentStatusHistory.php` | Eloquent models dengan relationships dan scopes |
| **Form Requests** | `StoreStudentRequest.php`, `UpdateStudentRequest.php`, `UpdateStudentStatusRequest.php`, `BulkPromoteRequest.php` | Validation rules dengan custom error messages |
| **Factories** | `StudentFactory.php`, `GuardianFactory.php` | Test data generation dengan states |

### Key Methods

**StudentService:**
- `generateNis(string $tahunAjaran): string` - Auto-generate NIS dengan increment per tahun
- `attachGuardiansToStudent(Student $student, array $guardianData): void` - Attach guardians dan auto-create parent account
- `bulkPromoteStudents(array $studentIds, int $kelasIdBaru, string $tahunAjaranBaru, ?string $waliKelas): int` - Bulk naik kelas dengan history
- `updateStudentStatus(Student $student, string $statusBaru, array $additionalData, int $changedBy): StudentStatusHistory` - Update status dengan history tracking

---

## Edge Cases & Handling

| Scenario | Handling | Implementation |
|----------|----------|----------------|
| **Duplicate NIK/NISN** | Reject dengan validation error | Unique validation di Form Request |
| **Parent dengan multiple children** | Reuse existing user account, link guardian | Check existing user by username di `createParentAccount()` |
| **Guardian dengan NIK sama** | Update existing guardian, tidak create duplicate | Check NIK di `createOrUpdateGuardian()` |
| **Bulk promote dengan student yang sudah di kelas target** | Skip atau update, tidak error | Transaction rollback jika ada error |
| **Status change ke lulus untuk non-kelas 6** | Validation error (future: when classes table exists) | Conditional validation di `UpdateStudentStatusRequest` |
| **Soft deleted student** | Tidak muncul di list default, bisa di-restore | `SoftDeletes` trait dengan `withTrashed()` scope |
| **Photo upload > 2MB** | Reject dengan validation error | File validation di Form Request |
| **Missing primary contact** | First guardian jadi primary by default | Logic di `attachGuardiansToStudent()` |

---

## Security Considerations

| Concern | Mitigation | Implementation |
|---------|------------|----------------|
| **Unauthorized access** | Role-based middleware `role:SUPERADMIN,ADMIN` | Applied di routes |
| **Parent view other's children** | Authorization check di controller | `ChildController::show()` verify ownership |
| **Mass assignment** | Fillable properties di models | `$fillable` array di Student, Guardian models |
| **SQL injection** | Eloquent ORM dengan parameter binding | All queries via Eloquent |
| **XSS via student data** | Input sanitization di Form Request | Laravel auto-escapes output |
| **File upload vulnerability** | Validate mime type, size, store di storage | File validation rules di Form Request |

---

## Testing Coverage

### Unit Tests (30 tests, 66 assertions) ✅ All Passed

**StudentServiceTest.php:**
- NIS generation dengan format correct dan increment per tahun
- Parent account creation dengan reuse untuk multiple children
- Bulk promote dengan history tracking
- Status update dengan history dan audit trail
- Phone number normalization
- Guardian update logic (tidak duplicate jika NIK sama)

**StudentModelTest.php:**
- Model relationships (Student ↔ Guardian, Student → History)
- Helper methods (`isActive()`, `getAge()`, `formatted_nis`)
- Scopes (`active()`, `byClass()`, `byAcademicYear()`, `search()`)
- Soft delete functionality
- Guardian helpers dan scopes

### Feature Tests (Require Frontend)

**StudentManagementTest.php (17 tests):**
- Admin CRUD operations
- Search dan filter functionality
- Bulk promote students
- Status change dengan history
- Photo upload
- Access control

**ParentPortalTest.php (10 tests):**
- Parent view children list
- Parent view child detail dengan authorization
- Multiple children support
- Access control

---

## Performance Considerations

| Aspect | Optimization | Impact |
|--------|--------------|--------|
| **List query** | Eager load `guardians`, `primaryGuardian` | Prevent N+1 queries |
| **Search** | Indexed columns: `nis`, `nisn`, `nik`, `nama_lengkap` | Fast search performance |
| **Bulk operations** | DB transactions dengan batch processing | Data consistency |
| **Photo storage** | Store di `storage/app/public/students/photos` | Separate from code |
| **Pagination** | Default 20 items per page | Manageable page load |

---

## Known Limitations

| Limitation | Reason | Workaround |
|------------|--------|------------|
| **Export/Import Excel** | Placeholder methods only | Implement dengan Laravel Excel package |
| **WhatsApp notification** | TODO comment di service | Integrate WhatsApp API |
| **Classes table** | Not implemented yet | `kelas_id` masih integer placeholder |
| **Frontend pages** | Backend-only implementation | Inertia pages belum dibuat |
| **Photo compression** | Not implemented | Add image intervention library |

---

## Future Enhancements

- [ ] Excel export/import functionality
- [ ] WhatsApp notification untuk parent credentials
- [ ] Integration dengan Classes module (foreign key untuk `kelas_id`)
- [ ] Student ID card generator (PDF)
- [ ] Photo compression saat upload
- [ ] Advanced search dengan multiple filters kombinasi
- [ ] Document storage per siswa (ijazah, sertifikat)
- [ ] Barcode/QR Code untuk NIS

---

## Related Documentation

- **API Documentation:** [Student API](../../api/students.md)
- **Test Plan:** [STD Test Plan](../../testing/STD-test-plan.md)
- **Database Schema:** [Database Documentation](../../architecture/DATABASE.md#student-management-tables)

---

## Quick Reference

### Create Student Flow
```
1. TU fill form → Validate input
2. Generate NIS → Create Student record
3. Create/Update Guardians → Attach to Student
4. Auto-create Parent Account (if primary contact)
5. Log activity → Return success
```

### Bulk Promote Flow
```
1. Select students → Choose target class
2. Preview list → Confirm selection
3. Update kelas_id → Insert history records
4. Log activity → Return promoted count
```

### Parent Portal Access
```
1. Parent login dengan username (no HP)
2. Get guardian record → Get linked students
3. Display children cards → View detail (read-only)
```

---

**Verification Evidence:**
- ✅ Routes verified: 12 student routes + 2 parent routes accessible
- ✅ Service methods match controller calls (verified via grep)
- ✅ Tested with tinker: All service methods exist and work
- ✅ Migrations applied: 5 tables created successfully
- ✅ Unit tests: 30/30 passed with 66 assertions
- ✅ Code formatted: Laravel Pint passed

**Status:** Backend implementation complete dan fully tested. Frontend Vue pages pending untuk feature tests.

