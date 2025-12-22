# Database Schema Design
## Complete ERD & Table Specifications

---

## Database Overview

**Database Engine:** MySQL 8.0+ / PostgreSQL 14+  
**Charset:** utf8mb4_unicode_ci  
**Total Tables:** ~30 tables  
**Estimated Size:** ~5GB for 200 students, 5 years data

---

## Entity Relationship Diagram (Text Format)

```
┌──────────────┐           ┌──────────────┐
│    users     │◄─────────►│  teachers    │
└──────┬───────┘           └──────────────┘
       │
       │ parent_account_id
       ▼
┌──────────────┐           ┌──────────────┐
│   students   │───────────│   classes    │
└──────┬───────┘           └──────┬───────┘
       │                          │
       ├──────────────────────────┼────────────┐
       │                          │            │
       ▼                          ▼            ▼
┌──────────────┐           ┌──────────────┐   │
│ attendances  │           │   schedules  │   │
└──────────────┘           └──────────────┘   │
       │                                       │
       ▼                                       ▼
┌──────────────┐           ┌──────────────┐
│leave_requests│           │   subjects   │
└──────────────┘           └──────┬───────┘
                                  │
       ┌──────────────────────────┤
       ▼                          ▼
┌──────────────┐           ┌──────────────┐
│    grades    │           │teaching_assgn│
└──────────────┘           └──────────────┘
       │
       ▼
┌──────────────┐
│ report_cards │
└──────────────┘

       ┌──────────────┐
       │   students   │
       └──────┬───────┘
              │
       ┌──────┼──────┐
       ▼      ▼      ▼
┌──────────┐ ┌───────┐ ┌──────────┐
│   bills  │ │payments│ │ categories│
└──────────┘ └───────┘ └──────────┘

┌──────────────┐           ┌──────────────┐
│psb_registrat.│───────────│ psb_documents│
└──────────────┘           └──────────────┘
```

---

## Core Tables

### 1. users

**Purpose:** Authentication & user accounts for all roles

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    
    role ENUM('superadmin', 'principal', 'admin', 'teacher', 'parent', 'student') NOT NULL,
    
    is_first_login BOOLEAN DEFAULT TRUE,
    is_active BOOLEAN DEFAULT TRUE,
    
    remember_token VARCHAR(100),
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Sample Data:**
```sql
INSERT INTO users (name, username, email, password, role) VALUES
('Super Admin', 'superadmin', 'admin@sekolah.com', '$2y$10$...', 'superadmin'),
('Kepala Sekolah', 'kepalasekolah', 'kepsek@sekolah.com', '$2y$10$...', 'principal'),
('TU Staff', 'tu_staff', 'tu@sekolah.com', '$2y$10$...', 'admin'),
('Guru Kelas 1A', 'guru1a', 'guru1a@sekolah.com', '$2y$10$...', 'teacher');
```

---

### 2. academic_years

**Purpose:** Master data tahun ajaran & semester

```sql
CREATE TABLE academic_years (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(20) NOT NULL COMMENT '2024/2025',
    semester TINYINT NOT NULL COMMENT '1 or 2',
    
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    
    is_active BOOLEAN DEFAULT FALSE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_year_semester (name, semester),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Business Rule:** Hanya 1 academic_year yang boleh `is_active = TRUE`

---

### 3. classes

**Purpose:** Kelas (1A, 1B, 2A, etc)

```sql
CREATE TABLE classes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(10) NOT NULL COMMENT 'e.g., 1A, 2B',
    level TINYINT NOT NULL COMMENT '1-6 for SD',
    
    homeroom_teacher_id BIGINT UNSIGNED,
    academic_year_id BIGINT UNSIGNED NOT NULL,
    
    capacity INT DEFAULT 30,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (homeroom_teacher_id) REFERENCES teachers(id) ON DELETE SET NULL,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_class_year (name, academic_year_id),
    INDEX idx_level (level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 4. students

**Purpose:** Data siswa

```sql
CREATE TABLE students (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Identifiers
    nis VARCHAR(20) UNIQUE NOT NULL COMMENT 'Auto-generated',
    nisn VARCHAR(10) UNIQUE COMMENT 'National student number',
    nik VARCHAR(16) UNIQUE NOT NULL COMMENT 'National ID',
    
    -- Personal Info
    name VARCHAR(100) NOT NULL,
    nickname VARCHAR(50),
    gender ENUM('L', 'P') NOT NULL,
    birth_place VARCHAR(100) NOT NULL,
    birth_date DATE NOT NULL,
    religion ENUM('Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu') NOT NULL,
    
    -- Family Info
    child_number TINYINT COMMENT 'Anak ke-',
    siblings_count TINYINT COMMENT 'Jumlah saudara',
    
    -- Contact & Address
    address TEXT NOT NULL,
    rt_rw VARCHAR(10),
    village VARCHAR(100) NOT NULL COMMENT 'Kelurahan/Desa',
    district VARCHAR(100) NOT NULL COMMENT 'Kecamatan',
    city VARCHAR(100) NOT NULL COMMENT 'Kota/Kabupaten',
    province VARCHAR(100) NOT NULL,
    postal_code VARCHAR(10),
    phone VARCHAR(15),
    email VARCHAR(100),
    
    -- Photo
    photo_path VARCHAR(255),
    
    -- Academic
    class_id BIGINT UNSIGNED NOT NULL,
    academic_year_id BIGINT UNSIGNED NOT NULL,
    status ENUM('Aktif', 'Mutasi', 'DO', 'Lulus') DEFAULT 'Aktif',
    enrollment_date DATE NOT NULL,
    
    -- Parent Link
    parent_account_id BIGINT UNSIGNED,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE RESTRICT,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE RESTRICT,
    FOREIGN KEY (parent_account_id) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_nis (nis),
    INDEX idx_name (name),
    INDEX idx_class_status (class_id, status),
    INDEX idx_status (status),
    FULLTEXT idx_fulltext_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 5. parent_data

**Purpose:** Data orang tua/wali (relasi many-to-one ke students)

```sql
CREATE TABLE parent_data (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id BIGINT UNSIGNED NOT NULL,
    
    -- Father
    father_name VARCHAR(100) NOT NULL,
    father_nik VARCHAR(16) NOT NULL,
    father_occupation VARCHAR(100),
    father_education ENUM('SD', 'SMP', 'SMA', 'D3', 'S1', 'S2', 'S3'),
    father_income_range ENUM('<1jt', '1-3jt', '3-5jt', '>5jt'),
    father_phone VARCHAR(15) NOT NULL,
    father_email VARCHAR(100),
    
    -- Mother
    mother_name VARCHAR(100) NOT NULL,
    mother_nik VARCHAR(16) NOT NULL,
    mother_occupation VARCHAR(100),
    mother_education ENUM('SD', 'SMP', 'SMA', 'D3', 'S1', 'S2', 'S3'),
    mother_income_range ENUM('<1jt', '1-3jt', '3-5jt', '>5jt'),
    mother_phone VARCHAR(15),
    mother_email VARCHAR(100),
    
    -- Guardian (Optional)
    guardian_name VARCHAR(100),
    guardian_nik VARCHAR(16),
    guardian_relation VARCHAR(50),
    guardian_occupation VARCHAR(100),
    guardian_phone VARCHAR(15),
    
    -- Primary Contact
    primary_contact ENUM('father', 'mother', 'guardian') DEFAULT 'father',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    
    INDEX idx_student (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 6. teachers

**Purpose:** Data guru & staff

```sql
CREATE TABLE teachers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED UNIQUE NOT NULL,
    
    nip VARCHAR(20) UNIQUE COMMENT 'Nomor Induk Pegawai',
    nik VARCHAR(16) UNIQUE NOT NULL,
    
    name VARCHAR(100) NOT NULL,
    title VARCHAR(20) COMMENT 'S.Pd, M.Pd, etc',
    
    gender ENUM('L', 'P') NOT NULL,
    birth_place VARCHAR(100),
    birth_date DATE,
    
    address TEXT,
    phone VARCHAR(15) NOT NULL,
    email VARCHAR(100),
    
    photo_path VARCHAR(255),
    
    -- Employment
    status ENUM('Tetap', 'Honorer') NOT NULL,
    join_date DATE NOT NULL,
    
    -- Salary
    base_salary DECIMAL(12,2) DEFAULT 0 COMMENT 'For Tetap',
    hourly_rate DECIMAL(10,2) DEFAULT 0 COMMENT 'For Honorer',
    
    -- Subjects (JSON array of subject IDs)
    subjects JSON COMMENT '["MTK", "IPA"]',
    
    is_active BOOLEAN DEFAULT TRUE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_nip (nip),
    INDEX idx_status (status),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 7. subjects

**Purpose:** Master data mata pelajaran

```sql
CREATE TABLE subjects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) UNIQUE NOT NULL COMMENT 'MTK, IPA, IPS',
    name VARCHAR(100) NOT NULL COMMENT 'Matematika, IPA',
    
    category ENUM('Wajib', 'Muatan Lokal', 'Ekstra') DEFAULT 'Wajib',
    
    kkm TINYINT DEFAULT 70 COMMENT 'Kriteria Ketuntasan Minimal',
    
    -- Applicable for levels (JSON array)
    levels JSON COMMENT '[1,2,3,4,5,6] or specific levels',
    
    is_active BOOLEAN DEFAULT TRUE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    INDEX idx_code (code),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Attendance Module Tables

### 8. attendances

**Purpose:** Absensi harian siswa

```sql
CREATE TABLE attendances (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    student_id BIGINT UNSIGNED NOT NULL,
    class_id BIGINT UNSIGNED NOT NULL,
    date DATE NOT NULL,
    
    status ENUM('H', 'I', 'S', 'A') NOT NULL COMMENT 'Hadir, Izin, Sakit, Alpha',
    notes TEXT,
    
    created_by BIGINT UNSIGNED NOT NULL COMMENT 'Teacher user_id',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    
    UNIQUE KEY unique_attendance (student_id, class_id, date),
    INDEX idx_date (date),
    INDEX idx_class_date (class_id, date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 9. subject_attendances

**Purpose:** Absensi per mata pelajaran

```sql
CREATE TABLE subject_attendances (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    student_id BIGINT UNSIGNED NOT NULL,
    class_id BIGINT UNSIGNED NOT NULL,
    subject_id BIGINT UNSIGNED NOT NULL,
    teacher_id BIGINT UNSIGNED NOT NULL,
    
    date DATE NOT NULL,
    session TINYINT COMMENT 'Jam ke- (1-8)',
    
    status ENUM('H', 'I', 'S', 'A') NOT NULL,
    notes TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_subject_attendance (student_id, subject_id, date, session),
    INDEX idx_date (date),
    INDEX idx_class_subject (class_id, subject_id, date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 10. leave_requests

**Purpose:** Pengajuan izin/sakit dari orang tua

```sql
CREATE TABLE leave_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    student_id BIGINT UNSIGNED NOT NULL,
    submitted_by BIGINT UNSIGNED NOT NULL COMMENT 'Parent user_id',
    
    type ENUM('Izin', 'Sakit') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    
    reason TEXT NOT NULL,
    document_path VARCHAR(255) COMMENT 'Surat dokter, etc',
    
    status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    reviewed_by BIGINT UNSIGNED COMMENT 'Teacher/Admin user_id',
    reviewed_at TIMESTAMP NULL,
    rejection_reason TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (submitted_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_student (student_id),
    INDEX idx_status (status),
    INDEX idx_date_range (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 11. teacher_attendances

**Purpose:** Presensi guru (clock in/out)

```sql
CREATE TABLE teacher_attendances (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    teacher_id BIGINT UNSIGNED NOT NULL,
    date DATE NOT NULL,
    
    clock_in_time TIME,
    clock_out_time TIME,
    
    total_hours DECIMAL(4,2) COMMENT 'Auto-calculated',
    
    status ENUM('Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpha') DEFAULT 'Alpha',
    
    notes TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_teacher_date (teacher_id, date),
    INDEX idx_date (date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Payment Module Tables

### 12. payment_categories

**Purpose:** Kategori pembayaran (SPP, Uang Gedung, etc)

```sql
CREATE TABLE payment_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    code VARCHAR(20) UNIQUE NOT NULL COMMENT 'SPP, GEDUNG, SERAGAM',
    name VARCHAR(100) NOT NULL,
    description TEXT,
    
    default_amount DECIMAL(12,2) DEFAULT 0,
    
    frequency ENUM('Monthly', 'Yearly', 'OneTime', 'AdHoc') NOT NULL,
    is_mandatory BOOLEAN DEFAULT FALSE,
    
    is_active BOOLEAN DEFAULT TRUE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    INDEX idx_code (code),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 13. bills

**Purpose:** Tagihan pembayaran

```sql
CREATE TABLE bills (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    student_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    
    amount DECIMAL(12,2) NOT NULL,
    due_date DATE NOT NULL,
    
    period VARCHAR(20) COMMENT '2025-01 for monthly bills',
    description TEXT,
    
    status ENUM('Unpaid', 'Partial', 'Paid') DEFAULT 'Unpaid',
    paid_amount DECIMAL(12,2) DEFAULT 0,
    remaining_amount DECIMAL(12,2) GENERATED ALWAYS AS (amount - paid_amount) STORED,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES payment_categories(id) ON DELETE RESTRICT,
    
    INDEX idx_student (student_id),
    INDEX idx_due_status (due_date, status),
    INDEX idx_period (period),
    UNIQUE KEY unique_student_category_period (student_id, category_id, period)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 14. payments

**Purpose:** Pembayaran yang sudah dilakukan

```sql
CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    bill_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    
    receipt_number VARCHAR(50) UNIQUE NOT NULL COMMENT 'KWT/2025/01/0001',
    
    amount DECIMAL(12,2) NOT NULL,
    method ENUM('Tunai', 'Transfer', 'EDC') NOT NULL,
    
    reference_number VARCHAR(100) COMMENT 'Bank transfer ref',
    notes TEXT,
    
    paid_at DATE NOT NULL,
    
    receipt_pdf_path VARCHAR(255),
    
    is_verified BOOLEAN DEFAULT FALSE,
    verified_by BIGINT UNSIGNED,
    verified_at TIMESTAMP NULL,
    
    created_by BIGINT UNSIGNED NOT NULL COMMENT 'TU user_id',
    
    status ENUM('Active', 'Cancelled') DEFAULT 'Active',
    cancelled_reason TEXT,
    cancelled_by BIGINT UNSIGNED,
    cancelled_at TIMESTAMP NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (bill_id) REFERENCES bills(id) ON DELETE RESTRICT,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE RESTRICT,
    FOREIGN KEY (category_id) REFERENCES payment_categories(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_receipt (receipt_number),
    INDEX idx_student (student_id),
    INDEX idx_paid_at (paid_at),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Grades Module Tables

### 15. grades

**Purpose:** Nilai siswa per komponen

```sql
CREATE TABLE grades (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    student_id BIGINT UNSIGNED NOT NULL,
    subject_id BIGINT UNSIGNED NOT NULL,
    academic_year_id BIGINT UNSIGNED NOT NULL,
    semester TINYINT NOT NULL COMMENT '1 or 2',
    
    assessment_type ENUM('UH', 'UTS', 'UAS', 'Praktik', 'Proyek', 'Sikap') NOT NULL,
    assessment_title VARCHAR(100) COMMENT 'UH 1: Perkalian',
    
    score DECIMAL(5,2) NOT NULL COMMENT '0-100',
    
    teacher_id BIGINT UNSIGNED NOT NULL,
    assessment_date DATE,
    
    notes TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE RESTRICT,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE RESTRICT,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE RESTRICT,
    
    INDEX idx_student_semester (student_id, academic_year_id, semester),
    INDEX idx_subject (subject_id),
    INDEX idx_type (assessment_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 16. attitude_grades

**Purpose:** Nilai sikap (KI-1, KI-2)

```sql
CREATE TABLE attitude_grades (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    student_id BIGINT UNSIGNED NOT NULL,
    academic_year_id BIGINT UNSIGNED NOT NULL,
    semester TINYINT NOT NULL,
    
    spiritual_grade ENUM('A', 'B', 'C', 'D') NOT NULL COMMENT 'KI-1',
    spiritual_description TEXT,
    
    social_grade ENUM('A', 'B', 'C', 'D') NOT NULL COMMENT 'KI-2',
    social_description TEXT,
    
    homeroom_teacher_id BIGINT UNSIGNED NOT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE RESTRICT,
    FOREIGN KEY (homeroom_teacher_id) REFERENCES teachers(id) ON DELETE RESTRICT,
    
    UNIQUE KEY unique_student_semester (student_id, academic_year_id, semester)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 17. report_cards

**Purpose:** Rapor siswa

```sql
CREATE TABLE report_cards (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    student_id BIGINT UNSIGNED NOT NULL,
    academic_year_id BIGINT UNSIGNED NOT NULL,
    semester TINYINT NOT NULL,
    
    class_id BIGINT UNSIGNED NOT NULL,
    homeroom_teacher_id BIGINT UNSIGNED NOT NULL,
    
    status ENUM('Draft', 'Finalized') DEFAULT 'Draft',
    finalized_at TIMESTAMP NULL,
    finalized_by BIGINT UNSIGNED,
    
    pdf_path VARCHAR(255),
    
    average_score DECIMAL(5,2),
    class_rank INT,
    
    attendance_summary JSON COMMENT '{"hadir": 100, "izin": 5, "sakit": 2, "alpha": 0}',
    
    homeroom_notes TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE RESTRICT,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE RESTRICT,
    FOREIGN KEY (homeroom_teacher_id) REFERENCES teachers(id) ON DELETE RESTRICT,
    FOREIGN KEY (finalized_by) REFERENCES users(id) ON DELETE SET NULL,
    
    UNIQUE KEY unique_report (student_id, academic_year_id, semester),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## PSB Module Tables

### 18. psb_registrations

**Purpose:** Pendaftaran siswa baru

```sql
CREATE TABLE psb_registrations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    registration_number VARCHAR(50) UNIQUE NOT NULL COMMENT 'PSB/2025/0001',
    
    -- Student Info
    full_name VARCHAR(100) NOT NULL,
    nik VARCHAR(16) UNIQUE NOT NULL,
    nisn VARCHAR(10) UNIQUE,
    gender ENUM('L', 'P') NOT NULL,
    birth_place VARCHAR(100) NOT NULL,
    birth_date DATE NOT NULL,
    religion ENUM('Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu') NOT NULL,
    
    address TEXT NOT NULL,
    
    -- Parent Info
    father_name VARCHAR(100) NOT NULL,
    father_nik VARCHAR(16) NOT NULL,
    father_phone VARCHAR(15) NOT NULL,
    father_occupation VARCHAR(100),
    
    mother_name VARCHAR(100) NOT NULL,
    mother_nik VARCHAR(16) NOT NULL,
    mother_phone VARCHAR(15),
    mother_occupation VARCHAR(100),
    
    primary_contact_email VARCHAR(100),
    primary_contact_phone VARCHAR(15) NOT NULL,
    
    -- Registration Status
    status ENUM('Pending', 'Approved', 'Rejected', 'Accepted', 'Not Accepted', 'Registered', 'Expired') DEFAULT 'Pending',
    
    verified_by BIGINT UNSIGNED,
    verified_at TIMESTAMP NULL,
    rejection_reason TEXT,
    
    payment_confirmed_by BIGINT UNSIGNED,
    payment_confirmed_at TIMESTAMP NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (payment_confirmed_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_registration_number (registration_number),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 19. psb_documents

**Purpose:** Dokumen PSB (akte, KK, etc)

```sql
CREATE TABLE psb_documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    registration_id BIGINT UNSIGNED NOT NULL,
    
    document_type ENUM('Akte', 'KK', 'KTP_Ayah', 'KTP_Ibu', 'Foto', 'Surat_Pindah', 'Rapor', 'Other') NOT NULL,
    
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size INT COMMENT 'in bytes',
    mime_type VARCHAR(50),
    
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (registration_id) REFERENCES psb_registrations(id) ON DELETE CASCADE,
    
    INDEX idx_registration (registration_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Notification Tables

### 20. notifications

**Purpose:** In-app notifications

```sql
CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    user_id BIGINT UNSIGNED NOT NULL,
    
    type VARCHAR(50) NOT NULL COMMENT 'payment_reminder, attendance_alert, etc',
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    
    data JSON COMMENT 'Additional context data',
    
    read_at TIMESTAMP NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_user_read (user_id, read_at),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 21. notification_logs

**Purpose:** Log semua notifikasi yang dikirim (WhatsApp, Email)

```sql
CREATE TABLE notification_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    user_id BIGINT UNSIGNED,
    
    type VARCHAR(50) NOT NULL COMMENT 'payment_reminder, attendance_alert',
    channel ENUM('WhatsApp', 'Email', 'SMS', 'InApp') NOT NULL,
    
    recipient VARCHAR(100) NOT NULL COMMENT 'Phone number or email',
    message TEXT NOT NULL,
    
    status ENUM('Pending', 'Sent', 'Delivered', 'Read', 'Failed') DEFAULT 'Pending',
    
    sent_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    read_at TIMESTAMP NULL,
    
    error_message TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_recipient (recipient),
    INDEX idx_status (status),
    INDEX idx_sent_at (sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Settings Tables

### 22. school_settings

**Purpose:** Konfigurasi sekolah

```sql
CREATE TABLE school_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    school_name VARCHAR(100) NOT NULL,
    npsn VARCHAR(8) NOT NULL COMMENT 'Nomor Pokok Sekolah Nasional',
    nss VARCHAR(12) COMMENT 'Nomor Statistik Sekolah',
    
    address TEXT NOT NULL,
    phone VARCHAR(15),
    email VARCHAR(100),
    website VARCHAR(100),
    
    logo_path VARCHAR(255),
    
    accreditation ENUM('A', 'B', 'C', 'Belum Terakreditasi'),
    
    principal_name VARCHAR(100),
    principal_nip VARCHAR(20),
    
    vision TEXT,
    mission TEXT,
    
    timezone VARCHAR(50) DEFAULT 'Asia/Jakarta',
    currency VARCHAR(10) DEFAULT 'IDR',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Audit Trail Tables

### 23. activity_log (Spatie Laravel Activity Log)

**Purpose:** Audit trail untuk semua aktivitas penting

```sql
CREATE TABLE activity_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    log_name VARCHAR(255),
    description TEXT NOT NULL,
    
    subject_type VARCHAR(255),
    subject_id BIGINT UNSIGNED,
    
    causer_type VARCHAR(255),
    causer_id BIGINT UNSIGNED,
    
    properties JSON,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_subject (subject_type, subject_id),
    INDEX idx_causer (causer_type, causer_id),
    INDEX idx_log_name (log_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Summary Statistics

**Total Tables:** 23+ core tables  
**Additional Tables:** 
- `failed_jobs` (Laravel queue)
- `password_resets` (Laravel auth)
- `sessions` (if session driver = database)
- `jobs` (Laravel queue)
- `media` (Spatie Media Library)

**Estimated Row Counts (200 students, 5 years):**
- students: ~300 (including graduated)
- attendances: ~180,000 (200 students × 180 days × 5 years)
- grades: ~24,000 (200 students × 12 subjects × 10 assessments × 5 years)
- payments: ~12,000 (200 students × 12 months × 5 years)
- bills: ~12,000

**Database Size Estimate:** 3-5GB for 5 years data

---

**Document Version:** 1.0  
**Last Updated:** 13 Desember 2025  
**Prepared By:** Zulfikar Hidayatullah
