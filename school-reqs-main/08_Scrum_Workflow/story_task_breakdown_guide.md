# Story Task Breakdown Guide

**Purpose:** Panduan untuk memecah user story menjadi technical tasks yang actionable.

---

## 🎯 Why Break Down Stories?

1. **Clarity:** Task lebih spesifik dan jelas apa yang harus dikerjakan
2. **Progress Tracking:** Mudah track progress harian (checklist task)
3. **Time Management:** Estimasi lebih akurat di level task
4. **Reduce Risk:** Identify technical challenges early

---

## 📝 Task Breakdown Template

### User Story: [Story ID & Title]

**As a** [Role]  
**I want** [Feature]  
**So that** [Benefit]

**Estimation:** X points  
**Priority:** Must Have / Should Have / Could Have

---

### Acceptance Criteria
- [ ] AC 1
- [ ] AC 2
- [ ] AC 3

---

### Technical Tasks

**Frontend Tasks:**
- [ ] **Task 1:** Create UI component - [Estimated Time]
  - Details: [Specific implementation notes]
- [ ] **Task 2:** Add form validation - [Estimated Time]
- [ ] **Task 3:** Integrate with API - [Estimated Time]
- [ ] **Task 4:** Add responsive styles - [Estimated Time]
- [ ] **Task 5:** Write unit tests - [Estimated Time]

**Backend Tasks:**
- [ ] **Task 6:** Design database schema - [Estimated Time]
- [ ] **Task 7:** Create migration - [Estimated Time]
- [ ] **Task 8:** Implement API endpoint - [Estimated Time]
- [ ] **Task 9:** Add business logic/validation - [Estimated Time]
- [ ] **Task 10:** Write API tests - [Estimated Time]

**Testing Tasks:**
- [ ] **Task 11:** Manual testing (happy path) - [Estimated Time]
- [ ] **Task 12:** Test edge cases - [Estimated Time]
- [ ] **Task 13:** Browser compatibility test - [Estimated Time]

**Documentation:**
- [ ] **Task 14:** Update API documentation - [Estimated Time]
- [ ] **Task 15:** Update user guide (if needed) - [Estimated Time]

---

## 📚 Task Breakdown Examples

### Example 1: US-AUTH-001 - Login ke Sistem

**Story:** As a user, I want to login with username and password so that I can access the system.

**Estimation:** 3 points (~1 day)

**Acceptance Criteria:**
- [x] User can input username & password
- [x] System validates credentials
- [x] System redirects to dashboard on success
- [x] System shows error message on failure
- [x] Session timeout after 30 minutes

---

**Frontend Tasks:**
1. **Create Login Page Component** - 2 jam
   - Layout: form dengan 2 input (username, password) + button "Masuk"
   - Styling: sesuai design system (warna, typography)
   - Link "Lupa Password?"

2. **Add Form Validation (Client-side)** - 1 jam
   - Username required, min 3 characters
   - Password required, min 8 characters
   - Show inline error messages

3. **Integrate with Login API** - 1.5 jam
   - POST /api/auth/login
   - Handle loading state (disable button, show spinner)
   - Handle response: success → save token → redirect
   - Handle error: show error message dari server

4. **Add Responsive Styles** - 1 jam
   - Test di mobile (< 768px)
   - Test di tablet (768-1024px)
   - Center form, adjust spacing

5. **Write Unit Tests** - 1 jam
   - Test form validation logic
   - Test API call success/error handling
   - Test redirection

**Backend Tasks:**
6. **Design Database Schema** - 0.5 jam
   - Table: users (id, username, password_hash, email, role, status, created_at)
   - Index: username (unique), email (unique)

7. **Create Migration** - 0.5 jam
   - Run migration di dev database
   - Verify schema

8. **Implement Login API Endpoint** - 2 jam
   - POST /api/auth/login
   - Input validation (username, password required)
   - Find user by username
   - Verify password (bcrypt compare)
   - Generate JWT token (expires 30 min)
   - Return: { success: true, token, user: { id, username, role } }

9. **Add Business Logic** - 1 jam
   - Rate limiting: max 5 failed attempts in 15 min (block IP/username)
   - Audit log: log every login attempt (success/failed)
   - Check user status: if inactive, return error

10. **Write API Tests** - 1 jam
    - Test: valid credentials → success response
    - Test: invalid username → error
    - Test: invalid password → error
    - Test: inactive user → error
    - Test: rate limiting after 5 failures

**Testing Tasks:**
11. **Manual Testing** - 0.5 jam
    - Happy path: login dengan valid credentials
    - Test redirection ke dashboard sesuai role (Guru, TU, Kepala Sekolah)

12. **Test Edge Cases** - 0.5 jam
    - SQL injection attempt di username field
    - XSS attempt di password field
    - Login dengan user yang sudah dihapus
    - Session timeout test (idle 30 menit)

13. **Browser Compatibility** - 0.5 jam
    - Test di Chrome, Firefox, Safari, Edge
    - Test di mobile browser (Safari iOS, Chrome Android)

**Documentation:**
14. **Update API Documentation** - 0.5 jam
    - Endpoint: POST /api/auth/login
    - Request body, response format, error codes

---

**Total Estimated Time:** ~12.5 jam  
**Estimation Check:** 3 points = ~1 day (8 jam) ✅ (estimasi agak over, tapi include buffer)

---

### Example 2: US-STD-001 - Tambah Data Siswa Baru

**Story:** As a TU, I want to add new student data so that the student is registered in the system.

**Estimation:** 3 points (~1 day)

**Acceptance Criteria:**
- [x] TU can open "Add Student" form
- [x] Form has all required fields (NIS, Name, DOB, Gender, Class, etc.)
- [x] System validates data before saving
- [x] System shows success message after saving
- [x] Duplicate NIS/NISN is rejected

---

**Frontend Tasks:**
1. **Create "Add Student" Page** - 2 jam
   - Route: /students/new
   - Form with fields: NIS, NISN, Nama Lengkap, Tempat Lahir, Tanggal Lahir, Jenis Kelamin (dropdown), Agama (dropdown), Alamat (textarea), Kelas (dropdown), Tahun Ajaran (auto-fill)
   - Button: "Simpan" dan "Batal"

2. **Add Form Validation** - 1.5 jam
   - NIS: required, auto-generate jika kosong
   - Nama: required, min 3 characters
   - Tanggal Lahir: required, format DD/MM/YYYY, validate age (4-15 tahun untuk SD)
   - Jenis Kelamin: required
   - Kelas: required, load dari master data
   - Show inline error messages

3. **Integrate with API** - 1 jam
   - POST /api/students
   - Handle success: show toast notification "Data siswa berhasil ditambahkan", redirect ke student list
   - Handle error: show error message (duplicate NIS, validation errors)

4. **Add Responsive Layout** - 0.5 jam
   - Form stacked di mobile
   - 2-column layout di desktop

5. **Write Unit Tests** - 1 jam
   - Test validation logic
   - Test API integration

**Backend Tasks:**
6. **Design Student Schema** - 0.5 jam
   - Table: students (id, nis, nisn, nama, tempat_lahir, tanggal_lahir, jenis_kelamin, agama, alamat, kelas_id, tahun_ajaran_id, status, foto_url, created_at, updated_at)
   - Foreign keys: kelas_id, tahun_ajaran_id
   - Unique index: nis, nisn

7. **Create Migration** - 0.5 jam
   - Run migration, verify schema

8. **Implement Create Student API** - 2 jam
   - POST /api/students
   - Validate input (server-side validation)
   - Check duplicate NIS/NISN (return 409 Conflict jika duplicate)
   - Auto-generate NIS jika kosong (format: [Tahun][Nomor Urut])
   - Insert to database
   - Return: { success: true, student: {...} }

9. **Add Business Logic** - 1 jam
   - Get active tahun ajaran (auto-assign)
   - Validate kelas exists (FK check)
   - Audit log: log creation (who created, when)

10. **Write API Tests** - 1 jam
    - Test: create student dengan valid data → success
    - Test: create dengan NIS duplicate → error 409
    - Test: create dengan NISN duplicate → error 409
    - Test: create dengan kelas invalid → error 400
    - Test: auto-generate NIS jika kosong

**Testing Tasks:**
11. **Manual Testing** - 0.5 jam
    - Happy path: create student, verify data tersimpan
    - Verify di student list page

12. **Test Edge Cases** - 0.5 jam
    - Input tanggal lahir invalid (29 Feb 2023, tanggal di masa depan)
    - Input nama dengan special characters (', ", <script>)
    - Input alamat sangat panjang (> 500 chars)

13. **Browser Compatibility** - 0.5 jam
    - Test di Chrome, Firefox, Safari
    - Test date picker di berbagai browser

**Documentation:**
14. **Update API Docs** - 0.5 jam
    - POST /api/students endpoint
    - Request/response format

---

**Total Estimated Time:** ~12.5 jam  
**Estimation Check:** 3 points ≈ 1 day ✅

---

## 🧮 Task Estimation Guidelines

### Time Estimates by Task Type

| Task Type | Time Range | Notes |
|-----------|------------|-------|
| Simple UI Component | 1-2 jam | Button, input, card |
| Complex UI Component | 2-4 jam | Form with multiple fields, table with pagination |
| Form Validation | 0.5-1 jam | Client-side validation |
| API Integration | 1-2 jam | Call API, handle response |
| Responsive Styling | 0.5-1 jam | Make component responsive |
| Unit Tests (Frontend) | 0.5-1 jam | Per component |
| Database Schema Design | 0.5-1 jam | Simple table |
| Migration | 0.5 jam | Simple migration |
| API Endpoint (CRUD) | 1-3 jam | Simple CRUD endpoint |
| Business Logic | 1-2 jam | Validation, calculation |
| API Tests (Backend) | 1-2 jam | Multiple test cases |
| Manual Testing | 0.5-1 jam | Happy path + edge cases |
| Browser Compatibility | 0.5 jam | Test di 3-4 browsers |

---

## ✅ Task Checklist (Definition of Done per Task)

Sebelum check ✅ task, pastikan:

- [ ] Code written dan working
- [ ] No console errors
- [ ] Linter passed (yarn run lint)
- [ ] Tested manually (works as expected)
- [ ] Unit tests written (jika applicable)
- [ ] Code committed to Git dengan descriptive message
- [ ] Progress updated di sprint backlog

---

## 📊 Tracking Progress

### Daily Task Update

Update progress setiap hari di sprint backlog:

```markdown
### Day 3 Progress - 15 Januari 2025

**US-AUTH-001: Login ke Sistem**
- [x] Task 1: Create Login Page Component (2h) - DONE
- [x] Task 2: Add Form Validation (1h) - DONE
- [🚧] Task 3: Integrate with Login API (1.5h) - IN PROGRESS (50%)
  - API endpoint sudah jadi, sedang integrate di frontend
- [ ] Task 4: Add Responsive Styles (1h) - TODO

**Blockers:** None

**Plan for tomorrow:**
- Finish Task 3 (integrate API)
- Start Task 4 (responsive styles)
```

---

## 🎯 Tips for Effective Task Breakdown

### DO ✅
- Break down menjadi tasks yang bisa selesai dalam 0.5-4 jam
- Task harus actionable (jelas apa yang harus dikerjakan)
- Estimate time untuk setiap task
- Prioritize tasks (mana yang harus dikerjakan dulu)
- Update progress daily

### DON'T ❌
- Jangan buat task terlalu besar (> 4 jam)
- Jangan buat task terlalu vague ("implement feature X")
- Jangan skip testing tasks
- Jangan lupa dokumentasi

---

## 📚 References

- [Agile Task Breakdown Techniques](https://www.atlassian.com/agile/project-management/user-stories)
- [Estimating Software Tasks](https://www.mountaingoatsoftware.com/blog/estimating-tasks-in-hours)

---

**Last Updated:** 13 Desember 2025  
**Author:** Zulfikar Hidayatullah
