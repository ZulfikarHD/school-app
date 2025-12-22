# Sprint 1 Backlog - Foundation Setup

**Status:** 📝 Template/Example (Copy ini untuk actual Sprint 1)

---

## Sprint Information

| Field | Value |
|-------|-------|
| **Sprint Number** | Sprint 1 |
| **Sprint Goal** | **Setup foundation: User dapat login/logout dengan aman, role-based access berfungsi, dan master data minimum viable tersedia** |
| **Duration** | 2 Januari 2025 - 15 Januari 2025 (2 minggu) |
| **Developer** | Zulfikar Hidayatullah |
| **Total Points Planned** | 20 points |
| **Velocity Target** | 18-20 points |

---

## Sprint Goal

**Goal Statement:**
> Setup project foundation: Authentication system (login/logout), Role-Based Access Control, dan master data minimum (kelas, mata pelajaran, pengaturan umum sekolah) ready untuk development selanjutnya.

**Success Criteria:**
- [x] User dapat login dengan username/password
- [x] User dapat logout dengan aman
- [x] System enforce RBAC (user hanya akses menu sesuai role)
- [x] Admin dapat create user account untuk Guru, TU, Kepala Sekolah
- [x] Master data kelas, mata pelajaran, dan pengaturan sekolah dapat di-manage
- [x] Project structure, database, dan CI/CD setup complete

---

## Sprint Backlog

### User Stories (Prioritized)

| Story ID | Story Title | Points | Status | Owner | Notes |
|----------|-------------|--------|--------|-------|-------|
| US-AUTH-001 | Login ke Sistem | 3 | ✅ Done | Zulfikar | Core functionality |
| US-AUTH-002 | Logout | 2 | ✅ Done | Zulfikar | - |
| US-AUTH-005 | RBAC | 5 | 🚧 In Progress | Zulfikar | Complex, need time |
| US-AUTH-006 | Manajemen User | 3 | ⏳ Todo | Zulfikar | Depends on RBAC |
| US-SET-001 | Master Data Kelas | 2 | ⏳ Todo | Zulfikar | - |
| US-SET-002 | Master Data Mapel | 2 | ⏳ Todo | Zulfikar | - |
| US-SET-004 | Pengaturan Umum | 3 | ⏳ Todo | Zulfikar | Low priority |

**Total Points:** 20

**Legend:**
- ⏳ Todo
- 🚧 In Progress
- 🔄 In Review
- ✅ Done
- ❌ Blocked

---

## Detailed Task Breakdown

### 📝 US-AUTH-001: Login ke Sistem (3 points)

**Priority:** Critical  
**Story:**  
As a user (semua role), I want to login dengan username dan password so that saya dapat mengakses sistem sesuai dengan hak akses saya.

**Acceptance Criteria:**
- [ ] User dapat input username & password di halaman login
- [ ] System validate credentials dan redirect ke dashboard jika valid
- [ ] System show error message jika credentials salah
- [ ] System auto-logout setelah 30 menit idle
- [ ] Login page responsive (mobile, tablet, desktop)

---

#### Frontend Tasks

**Total Estimated Time:** 7.5 jam

- [ ] **FE-001.1:** Setup project structure (React + TypeScript + Vite/CRA)
  - **Time:** 1 jam
  - **Details:**
    - Init project: `yarn create vite app --template react-ts`
    - Setup folder structure: `/src/components`, `/src/pages`, `/src/services`, `/src/utils`, `/src/types`
    - Install dependencies: axios, react-router-dom, @tanstack/react-query, tailwindcss
    - Setup Tailwind CSS
    - Create `.env` file untuk API base URL

- [ ] **FE-001.2:** Create Login Page UI Component
  - **Time:** 2 jam
  - **Details:**
    - Component: `src/pages/auth/LoginPage.tsx`
    - Layout: Centered card, logo sekolah di atas, form di tengah
    - Form fields:
      - Input username (text)
      - Input password (password type, with show/hide toggle)
      - Checkbox "Ingat Saya" (optional untuk fase 1)
      - Button "Masuk" (primary button)
      - Link "Lupa Password?" (disabled untuk fase 1, route ke /forgot-password)
    - Styling: Tailwind CSS, warna sesuai design system sekolah (primary: biru, secondary: hijau)
    - Mobile-first design

- [ ] **FE-001.3:** Implement form validation (client-side)
  - **Time:** 1 jam
  - **Details:**
    - Use `react-hook-form` + `zod` untuk validation
    - Rules:
      - Username: required, min 3 characters
      - Password: required, min 8 characters
    - Show inline error messages below each field
    - Disable submit button jika form invalid

- [ ] **FE-001.4:** Integrate dengan Login API
  - **Time:** 1.5 jam
  - **Details:**
    - Service: `src/services/authService.ts`
    - Function: `login(username, password)` → POST `/api/auth/login`
    - Handle loading state: show spinner di button, disable form
    - Handle success:
      - Save JWT token ke localStorage (`auth_token`)
      - Save user data ke localStorage (`user_data`)
      - Redirect ke dashboard sesuai role (implementasi di US-AUTH-005)
    - Handle error:
      - Show toast notification atau alert dengan error message dari server
      - Log error untuk debugging

- [ ] **FE-001.5:** Add responsive styles & polish
  - **Time:** 1 jam
  - **Details:**
    - Test di:
      - Mobile (< 768px): full width form
      - Tablet (768-1024px): centered, max-width 500px
      - Desktop (> 1024px): centered, max-width 500px
    - Add focus states untuk accessibility
    - Add loading skeleton (optional)
    - Test keyboard navigation (Tab, Enter to submit)

- [ ] **FE-001.6:** Write unit tests
  - **Time:** 1 jam
  - **Details:**
    - Test file: `src/pages/auth/LoginPage.test.tsx`
    - Test cases:
      1. Renders login form dengan semua fields
      2. Shows validation error jika username kosong
      3. Shows validation error jika password < 8 chars
      4. Calls login API dengan correct payload saat submit
      5. Shows error message jika API returns error
      6. Redirects to dashboard jika login success

---

#### Backend Tasks

**Total Estimated Time:** 6 jam

- [ ] **BE-001.1:** Setup project structure (Node.js + Express/NestJS + TypeScript)
  - **Time:** 1 jam
  - **Details:**
    - Init project: `yarn init` atau `nest new api`
    - Setup folder structure: `/src/modules`, `/src/common`, `/src/database`, `/src/config`
    - Install dependencies: express, typeorm/prisma, bcrypt, jsonwebtoken, dotenv, class-validator
    - Setup TypeScript config
    - Setup `.env` dengan: DATABASE_URL, JWT_SECRET, JWT_EXPIRES_IN

- [ ] **BE-001.2:** Design database schema untuk users table
  - **Time:** 0.5 jam
  - **Details:**
    - Table: `users`
    - Columns:
      - `id` (UUID, primary key)
      - `username` (VARCHAR 50, unique, not null)
      - `email` (VARCHAR 100, unique, nullable)
      - `password_hash` (VARCHAR 255, not null)
      - `full_name` (VARCHAR 100, not null)
      - `role` (ENUM: 'admin', 'kepala_sekolah', 'tu', 'guru', 'orang_tua', 'siswa')
      - `status` (ENUM: 'active', 'inactive', default: 'active')
      - `last_login_at` (TIMESTAMP, nullable)
      - `created_at` (TIMESTAMP, default: now())
      - `updated_at` (TIMESTAMP, default: now())
    - Indexes:
      - Unique index on `username`
      - Unique index on `email`
      - Index on `role` (for filtering)
      - Index on `status`

- [ ] **BE-001.3:** Create migration untuk users table
  - **Time:** 0.5 jam
  - **Details:**
    - Jika pakai TypeORM: `yarn typeorm migration:create -n CreateUsersTable`
    - Jika pakai Prisma: update `schema.prisma` dan run `prisma migrate dev`
    - Run migration di local database (PostgreSQL/MySQL)
    - Verify schema dengan database client (DBeaver, pgAdmin, dll)
    - Seed 1-2 dummy users untuk testing (admin, guru)

- [ ] **BE-001.4:** Implement Login API endpoint
  - **Time:** 2 jam
  - **Details:**
    - Route: `POST /api/auth/login`
    - Controller: `src/modules/auth/auth.controller.ts`
    - Service: `src/modules/auth/auth.service.ts`
    - DTO: `src/modules/auth/dto/login.dto.ts`
    - Flow:
      1. Validate input (username & password required) dengan class-validator
      2. Find user by username dari database
      3. Jika user tidak ditemukan → return 401 "Username atau password salah"
      4. Jika user status = 'inactive' → return 403 "Akun Anda tidak aktif"
      5. Compare password dengan bcrypt.compare(plainPassword, user.password_hash)
      6. Jika password salah → return 401 "Username atau password salah"
      7. Generate JWT token dengan payload: { userId, username, role, exp: 30 min }
      8. Update `last_login_at` di database
      9. Return response:
         ```json
         {
           "success": true,
           "message": "Login berhasil",
           "data": {
             "token": "eyJhbGc...",
             "user": {
               "id": "uuid",
               "username": "admin",
               "full_name": "Administrator",
               "role": "admin"
             }
           }
         }
         ```

- [ ] **BE-001.5:** Add rate limiting & audit log
  - **Time:** 1 jam
  - **Details:**
    - Install `express-rate-limit`
    - Apply rate limit middleware: max 5 requests per 15 menit per IP
    - Create `audit_logs` table (atau log ke file untuk MVP):
      - Columns: id, user_id, action, ip_address, user_agent, status (success/failed), created_at
    - Log setiap login attempt (success & failed)

- [ ] **BE-001.6:** Write API tests
  - **Time:** 1 jam
  - **Details:**
    - Test file: `src/modules/auth/auth.controller.spec.ts`
    - Use Jest + Supertest
    - Test cases:
      1. POST /api/auth/login dengan valid credentials → 200 OK, return token
      2. POST /api/auth/login dengan username salah → 401 Unauthorized
      3. POST /api/auth/login dengan password salah → 401 Unauthorized
      4. POST /api/auth/login dengan inactive user → 403 Forbidden
      5. POST /api/auth/login tanpa username → 400 Bad Request
      6. POST /api/auth/login dengan > 5 attempts → 429 Too Many Requests

---

#### Testing & QA

- [ ] **QA-001.1:** Manual testing - Happy path
  - **Time:** 0.5 jam
  - **Test scenarios:**
    1. Open login page → form renders correctly
    2. Input valid credentials (admin/password123) → click "Masuk"
    3. Success → redirected to dashboard (sementara redirect ke /dashboard placeholder)
    4. Token & user data tersimpan di localStorage
    5. Browser refresh → user tetap login (verify token di localStorage)

- [ ] **QA-001.2:** Manual testing - Error scenarios
  - **Time:** 0.5 jam
  - **Test scenarios:**
    1. Input username salah → error message "Username atau password salah"
    2. Input password salah → error message "Username atau password salah"
    3. Submit form kosong → validation error muncul
    4. Login dengan inactive user → error "Akun tidak aktif"
    5. Login 6x dengan password salah → error "Terlalu banyak percobaan, coba lagi dalam 15 menit"

- [ ] **QA-001.3:** Browser compatibility & responsive test
  - **Time:** 0.5 jam
  - **Test browsers:**
    - Chrome (latest)
    - Firefox (latest)
    - Safari (latest, jika ada Mac)
    - Edge (latest)
  - **Test devices:**
    - Mobile: iPhone 12 (375px), Samsung Galaxy S21 (360px)
    - Tablet: iPad (768px)
    - Desktop: 1920px

---

### 📝 US-AUTH-002: Logout (2 points)

**Priority:** Critical  
**Story:**  
As a user (semua role), I want to logout dari sistem dengan aman so that orang lain tidak dapat mengakses akun saya.

**Acceptance Criteria:**
- [ ] User dapat klik button "Keluar" di menu
- [ ] System logout user dan clear semua session data
- [ ] User di-redirect ke halaman login
- [ ] Setelah logout, user tidak bisa akses halaman private dengan tombol "Back" browser

---

#### Tasks (Total: ~6 jam)

**Frontend:**
- [ ] **FE-002.1:** Add Logout button di Header/Sidebar (1 jam)
  - Component: `src/components/layout/Header.tsx`
  - Button "Keluar" dengan icon (logout icon dari react-icons)
  - Onclick: call logout function

- [ ] **FE-002.2:** Implement logout logic (1 jam)
  - Function: `src/services/authService.ts` → `logout()`
  - Clear localStorage: remove `auth_token`, `user_data`
  - Clear any cached data (React Query cache clear)
  - Redirect ke `/login`

- [ ] **FE-002.3:** Implement AuthGuard/PrivateRoute (1.5 jam)
  - Component: `src/components/auth/PrivateRoute.tsx`
  - Check if `auth_token` exists di localStorage
  - Jika tidak ada → redirect ke `/login`
  - Jika ada → verify token (optional: call API `/api/auth/verify-token`)
  - Wrap semua private routes dengan `<PrivateRoute>`

**Backend:**
- [ ] **BE-002.1:** Implement token verification endpoint (optional) (1 jam)
  - Route: `POST /api/auth/verify-token`
  - Verify JWT token valid & not expired
  - Return user info jika valid

- [ ] **BE-002.2:** (Optional) Implement token blacklist untuk logout (1.5 jam)
  - Create `token_blacklist` table atau use Redis
  - POST `/api/auth/logout` → add token to blacklist
  - Middleware: check token tidak di blacklist sebelum allow request

**Testing:**
- [ ] **QA-002.1:** Manual testing (0.5 jam)
  - Login → logout → verify redirect ke login page
  - Logout → click browser back → should redirect to login (tidak bisa akses private page)
  - Logout → localStorage clear → verify

---

### 📝 US-AUTH-005: Role-Based Access Control (5 points)

**Priority:** Critical (Blocker untuk stories lain)  
**Story:**  
As a system admin, I want setiap user hanya dapat akses fitur sesuai role mereka so that data sensitif terlindungi dan tidak ada unauthorized access.

**Acceptance Criteria:**
- [ ] User dengan role "Guru" tidak bisa akses menu "Laporan Keuangan"
- [ ] User dengan role "Orang Tua" tidak bisa akses URL admin (redirect ke 403 page)
- [ ] Menu sidebar dinamis sesuai role user
- [ ] API endpoint protected dengan RBAC middleware
- [ ] Dashboard redirect otomatis sesuai role setelah login

---

#### Tasks (Total: ~15 jam)

**Planning & Design:**
- [ ] **PLAN-005.1:** Design permission matrix (1 jam)
  - Document permission matrix di spreadsheet/notion:
    - Roles: Kepala Sekolah, TU, Guru, Wali Kelas, Orang Tua
    - Modules: Authentication, Students, Attendance, Payment, Grades, Teachers, Dashboard, Reports, Settings
    - Permissions: create, read, update, delete per module
  - Example:
    - Kepala Sekolah: Read all, Approve actions
    - TU: Full access (Students, Payment, PSB, Settings)
    - Guru: Read students (limited), CRUD attendance/grades
    - Orang Tua: Read-only (own children data)

**Frontend:**
- [ ] **FE-005.1:** Create role-based menu configuration (2 jam)
  - File: `src/config/menuConfig.ts`
  - Define menu items dengan roles allowed:
    ```ts
    const menuItems = [
      {
        label: 'Dashboard',
        path: '/dashboard',
        icon: 'DashboardIcon',
        roles: ['all']
      },
      {
        label: 'Data Siswa',
        path: '/students',
        icon: 'StudentsIcon',
        roles: ['admin', 'tu', 'kepala_sekolah', 'guru']
      },
      {
        label: 'Pembayaran',
        path: '/payments',
        icon: 'PaymentIcon',
        roles: ['admin', 'tu', 'kepala_sekolah']
      },
      // ... more items
    ]
    ```

- [ ] **FE-005.2:** Implement dynamic sidebar based on role (2 jam)
  - Component: `src/components/layout/Sidebar.tsx`
  - Filter menu items berdasarkan user role
  - Hide menu items yang tidak sesuai role
  - Active state untuk current page

- [ ] **FE-005.3:** Implement RoleGuard component (2 jam)
  - Component: `src/components/auth/RoleGuard.tsx`
  - Props: `allowedRoles: string[]`, `children: ReactNode`, `fallback?: ReactNode`
  - Check if user role di dalam allowedRoles
  - Jika tidak → show fallback atau redirect ke 403 page
  - Usage: `<RoleGuard allowedRoles={['admin', 'tu']}><PaymentPage /></RoleGuard>`

- [ ] **FE-005.4:** Create 403 Forbidden page (1 jam)
  - Page: `src/pages/errors/ForbiddenPage.tsx`
  - Message: "Anda tidak memiliki akses ke halaman ini"
  - Button: "Kembali ke Dashboard"

- [ ] **FE-005.5:** Implement route protection dengan role check (1.5 jam)
  - File: `src/routes/index.tsx`
  - Wrap routes dengan `<RoleGuard>`
  - Example:
    ```tsx
    <Route path="/payments" element={
      <RoleGuard allowedRoles={['admin', 'tu', 'kepala_sekolah']}>
        <PaymentPage />
      </RoleGuard>
    } />
    ```

**Backend:**
- [ ] **BE-005.1:** Create RBAC middleware (3 jam)
  - Middleware: `src/common/middleware/rbac.middleware.ts`
  - Decorator (NestJS) atau function (Express): `@Roles('admin', 'tu')`
  - Flow:
    1. Extract JWT token dari header `Authorization: Bearer <token>`
    2. Verify token
    3. Get user role dari token payload
    4. Check if user role di dalam allowed roles
    5. Jika tidak → return 403 Forbidden
  - Usage:
    ```ts
    @Post('/students')
    @Roles('admin', 'tu')
    createStudent() { ... }
    ```

- [ ] **BE-005.2:** Apply RBAC middleware ke semua protected routes (1.5 jam)
  - Review semua routes dan tentukan roles yang allowed
  - Apply `@Roles()` decorator atau middleware
  - Document di API documentation

**Testing:**
- [ ] **QA-005.1:** Manual testing role-based menu (0.5 jam)
  - Login sebagai Guru → verify hanya menu Guru yang muncul
  - Login sebagai TU → verify menu TU (full access students, payment)
  - Login sebagai Orang Tua → verify menu limited (dashboard, data anak)

- [ ] **QA-005.2:** Manual testing route protection (0.5 jam)
  - Login sebagai Guru → coba akses `/payments` via URL → should redirect to 403
  - Login sebagai Orang Tua → coba akses `/students` → 403

- [ ] **QA-005.3:** API testing RBAC middleware (1 jam)
  - Test dengan Postman/Insomnia
  - Test cases:
    1. POST /api/students dengan token role=admin → 200 OK
    2. POST /api/students dengan token role=orang_tua → 403 Forbidden
    3. GET /api/payments dengan token role=guru → 403 Forbidden

---

### 📝 US-AUTH-006: Manajemen User Account (3 points)

*(Task breakdown similar to above stories... skipped for brevity)*

**Total Estimated Time:** ~10 jam

---

### 📝 US-SET-001: Master Data Kelas (2 points)

*(Task breakdown... skipped for brevity)*

**Total Estimated Time:** ~6 jam

---

### 📝 US-SET-002: Master Data Mata Pelajaran (2 points)

*(Task breakdown... skipped for brevity)*

**Total Estimated Time:** ~6 jam

---

### 📝 US-SET-004: Pengaturan Umum Sekolah (3 points)

*(Task breakdown... skipped for brevity)*

**Total Estimated Time:** ~10 jam

---

## Daily Progress Tracking

### Week 1

#### Day 1 - Senin, 2 Januari 2025
**What I did:**
- [x] Setup project structure (Frontend & Backend)
- [x] Setup database schema untuk users table
- [x] Create migration untuk users table
- [x] Seed dummy users (admin, guru)

**Time Spent:** 4 jam  
**Progress:** US-AUTH-001 (30%)

**Blockers:** None

**Plan for tomorrow:**
- Implement Login API endpoint
- Create Login Page UI

---

#### Day 2 - Selasa, 3 Januari 2025
**What I did:**
- [x] Implement Login API endpoint (BE-001.4)
- [x] Write API tests untuk login (BE-001.6)
- [x] Create Login Page UI (FE-001.2)

**Time Spent:** 6 jam  
**Progress:** US-AUTH-001 (70%)

**Blockers:** None

**Plan for tomorrow:**
- Integrate Login API dengan Frontend
- Add form validation
- Manual testing login flow

---

#### Day 3 - Rabu, 4 Januari 2025
**What I did:**
- [x] Integrate Login API dengan Frontend (FE-001.4)
- [x] Add form validation (FE-001.3)
- [x] Add responsive styles (FE-001.5)
- [x] Manual testing - Happy path (QA-001.1)

**Time Spent:** 5 jam  
**Progress:** US-AUTH-001 (100% DONE) ✅

**Blockers:** None

**Plan for tomorrow:**
- Start US-AUTH-002 (Logout)
- Implement logout button & logic

---

#### Day 4 - Kamis, 5 Januari 2025
**What I did:**
- [x] US-AUTH-002: Add logout button (FE-002.1)
- [x] Implement logout logic (FE-002.2)
- [x] Implement PrivateRoute component (FE-002.3)
- [x] Manual testing logout flow (QA-002.1)

**Time Spent:** 5 jam  
**Progress:** US-AUTH-002 (100% DONE) ✅

**Blockers:** None

**Plan for tomorrow:**
- Start US-AUTH-005 (RBAC)
- Design permission matrix
- Create role-based menu config

---

#### Day 5 - Jumat, 6 Januari 2025
**What I did:**
- [x] Design permission matrix (PLAN-005.1)
- [x] Create role-based menu configuration (FE-005.1)
- [x] Implement dynamic sidebar (FE-005.2)
- [x] Start RoleGuard component (FE-005.3) - 50%

**Time Spent:** 5 jam  
**Progress:** US-AUTH-005 (40%)

**Blockers:** None

**Plan for Monday:**
- Finish RoleGuard component
- Create 403 page
- Backend RBAC middleware

---

### Week 2

#### Day 6 - Senin, 9 Januari 2025
**What I did:**
- [x] Finish RoleGuard component (FE-005.3)
- [x] Create 403 Forbidden page (FE-005.4)
- [x] Implement route protection (FE-005.5)
- [x] Start RBAC middleware backend (BE-005.1) - 60%

**Time Spent:** 6 jam  
**Progress:** US-AUTH-005 (70%)

**Blockers:** None

**Plan for tomorrow:**
- Finish RBAC middleware
- Apply to all routes
- Testing RBAC

---

#### Day 7 - Selasa, 10 Januari 2025
**What I did:**
- [x] Finish RBAC middleware (BE-005.1)
- [x] Apply RBAC to all routes (BE-005.2)
- [x] Manual testing role-based menu (QA-005.1)
- [x] Manual testing route protection (QA-005.2)
- [x] API testing RBAC (QA-005.3)

**Time Spent:** 5 jam  
**Progress:** US-AUTH-005 (100% DONE) ✅

**Blockers:** None

**Plan for tomorrow:**
- Start US-AUTH-006 (User Management)

---

#### Day 8-14 - 11-15 Januari
*(Continue tracking similar format)*

---

## Sprint Burndown

| Day | Date | Points Remaining | Completed Today | Notes |
|-----|------|------------------|-----------------|-------|
| 0 | 2 Jan | 20 | - | Sprint Start |
| 1 | 2 Jan | 20 | 0 | Setup only |
| 2 | 3 Jan | 17 | 3 | US-AUTH-001 ✅ |
| 3 | 4 Jan | 15 | 2 | US-AUTH-002 ✅ |
| 4 | 5 Jan | 15 | 0 | RBAC in progress |
| 5 | 6 Jan | 15 | 0 | RBAC in progress |
| Weekend | - | 15 | - | - |
| 6 | 9 Jan | 10 | 5 | US-AUTH-005 ✅ |
| 7 | 10 Jan | 7 | 3 | US-AUTH-006 ✅ |
| 8 | 11 Jan | 5 | 2 | US-SET-001 ✅ |
| 9 | 12 Jan | 3 | 2 | US-SET-002 ✅ |
| 10 | 13 Jan | 0 | 3 | US-SET-004 ✅ Sprint Complete! 🎉 |

**Final Velocity:** 20 / 20 = 100% ✅

---

## Sprint Review (15 Januari 2025)

**Date:** 15 Januari 2025, 14:00 WIB  
**Attendees:** Zulfikar (Developer), Kepala Sekolah, TU (Bu Ani)

### Demo Agenda

1. **Login & Logout Flow** (5 menit)
   - Demo: Login sebagai admin, guru, TU
   - Demo: Logout dan verify session clear

2. **Role-Based Access Control** (7 menit)
   - Demo: Login sebagai Guru → menu terbatas (tidak ada Payment menu)
   - Demo: Login sebagai TU → full menu (ada Students, Payment, Settings)
   - Demo: Coba akses forbidden URL → 403 page

3. **User Management** (5 menit)
   - Demo: Create user baru (role: Guru)
   - Demo: Edit user data
   - Demo: Nonaktifkan user

4. **Master Data Management** (8 menit)
   - Demo: CRUD kelas (1A, 1B, 2A, dst)
   - Demo: CRUD mata pelajaran (Matematika, IPA, Bahasa Indonesia)
   - Demo: Update pengaturan sekolah (nama, alamat, logo)

5. **Q&A** (10 menit)

---

### Feedback dari Stakeholder

**Kepala Sekolah:**
- ✅ "Bagus, simple dan mudah dipahami"
- ⚠️ "Bisa tidak logo sekolah yang di login page lebih besar?" → Action: FE adjustment (1 jam)
- 💡 Suggestion: "Ke depan bisa tidak ada fitur "Lupa Username"?" → Add to backlog (Phase 2)

**TU (Bu Ani):**
- ✅ "Form tambah user sudah OK"
- ⚠️ "Di form kelas, bisa tidak tambah field "Kapasitas Maksimal"?" → Action: Add field (0.5 jam)
- 💡 Suggestion: "Bisa tidak import kelas dari Excel?" → Add to backlog (US-SET-013, Phase 2)

---

### Action Items

- [ ] Adjust logo size di login page (1 jam) - Sprint 2
- [ ] Add "Kapasitas Maksimal" field di master kelas (0.5 jam) - Sprint 2

---

## Sprint Retrospective (15 Januari 2025)

### What Went Well 🎉

1. **Velocity:** Completed semua 20 points! 100% sprint goal achieved
2. **Code Quality:** Linter passed, no major bugs
3. **Time Management:** Daily standup & time tracking help manage workload
4. **Stakeholder Communication:** Sprint review went smooth, feedback positive
5. **Foundation Solid:** Authentication & RBAC foundation sangat solid untuk sprint selanjutnya

---

### What Didn't Go Well ⚠️

1. **Estimasi Awal:** US-AUTH-005 (RBAC) ternyata lebih kompleks dari estimasi (5 points → seharusnya 6-7 points). Sempat khawatir tidak selesai.
2. **Testing Time:** Unit tests untuk RBAC agak terburu-buru, coverage hanya ~60%. Seharusnya allocate lebih banyak waktu.
3. **Documentation:** API documentation sempat tertinggal, baru update di akhir sprint.
4. **Buffer Time:** Tidak ada buffer, jadi pas stakeholder request small changes (logo size), harus squeeze time.

---

### What to Improve 🚀

1. **Better Estimation:** Add 1-2 points buffer untuk complex stories (RBAC, PDF generation, dll)
2. **Test-Driven:** Try TDD approach untuk sprint berikutnya (write tests first)
3. **Daily Documentation:** Update API docs setiap selesai endpoint, tidak tunggu akhir sprint
4. **Sprint Buffer:** Target 18-19 points instead of full 20, sisakan 1-2 points untuk bugs/changes
5. **Code Review:** Meskipun solo developer, lakukan self code review pakai checklist sebelum mark task DONE

---

### Action Items for Sprint 2

- [ ] Add 2-3 points buffer di Sprint 2 planning (target 17-18 points, bukan 20)
- [ ] Create testing checklist untuk setiap story (ensure coverage > 70%)
- [ ] Setup automation: linter + tests run di pre-commit hook (Husky)
- [ ] Daily: update API docs setiap selesai 1 endpoint

---

## Metrics & Learnings

### Time Breakdown

| Category | Hours | Percentage |
|----------|-------|------------|
| Frontend Development | 28h | 35% |
| Backend Development | 24h | 30% |
| Testing (Manual + Auto) | 12h | 15% |
| Meetings (Planning, Review, Retro) | 6h | 7.5% |
| Documentation | 4h | 5% |
| Debugging & Fixes | 6h | 7.5% |
| **Total** | **80h** | **100%** (2 weeks × 40h/week) |

---

### Velocity

- **Planned:** 20 points
- **Completed:** 20 points
- **Velocity:** 100%
- **Average per day:** 2 points

---

### Key Learnings

1. **Foundation is Critical:** Sprint 1 setup solid foundation → Sprint 2+ akan lebih smooth
2. **RBAC Early:** Implement RBAC di awal sangat tepat, karena semua fitur selanjutnya depend on this
3. **Stakeholder Engagement:** Sprint review dengan demo live sangat efektif untuk dapat feedback cepat
4. **Solo Developer Velocity:** 18-20 points per 2-week sprint adalah realistic untuk 1 developer
5. **Testing Takes Time:** Allocate 20-25% dari total time untuk testing (manual + automated)

---

## Files & Deliverables

### Code Repository
- **Branch:** `sprint-1-foundation`
- **Commits:** 47 commits
- **Pull Request:** #1 (merged to `develop`)

### Documentation
- [x] API Documentation (Postman collection + Swagger)
- [x] Database Schema (ER diagram)
- [x] Deployment Guide (README.md)
- [x] User Manual - Authentication & Settings (untuk training TU)

### Environments
- **Development:** http://localhost:3000 (frontend), http://localhost:5000 (backend)
- **Staging:** https://staging.sd-maju.sch.id (deployed for stakeholder review)
- **Production:** Not yet (akan deploy setelah Sprint 12)

---

## Next Sprint Preview

### Sprint 2: Core Data Management (17-18 Januari - 31 Januari)

**Goal:** TU dapat kelola data siswa dengan lengkap, dan user dapat reset password sendiri.

**Planned Stories:**
- US-AUTH-003: Lupa Password (3 pts)
- US-AUTH-004: Ganti Password (2 pts)
- US-SET-003: Tahun Ajaran & Semester (3 pts)
- US-SET-005: Jenis Pembayaran (2 pts)
- US-STD-001: Tambah Data Siswa (3 pts)
- US-STD-002: Edit Data Siswa (2 pts)
- US-STD-003: Nonaktifkan Siswa (2 pts)
- US-STD-004: Lihat Profil Siswa (3 pts)

**Total:** 20 points (tapi aim for 18 points, 2 points buffer)

---

**Status:** ✅ Sprint 1 COMPLETED!  
**Last Updated:** 15 Januari 2025  
**Next Sprint:** Sprint 2 starts on 17 Januari 2025

