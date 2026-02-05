# Sprint 03: Honor/Salary Calculation System

**Epic:** 07 - Teacher Management (TCH)
**Sprint Duration:** Week 3
**Story Points:** 8 SP
**Sprint Goal:** Complete salary configuration, calculation engine, and slip generation

---

## Sprint Overview

Sprint ini berfokus pada sistem perhitungan honor/gaji guru, yaitu: konfigurasi tarif honor, kalkulasi otomatis berdasarkan jam mengajar dan kehadiran, approval workflow, dan generate slip gaji PDF. Fitur ini critical untuk administrasi kepegawaian.

---

## Prerequisites

- [ ] Sprint 01 completed (Teacher data available)
- [ ] Sprint 02 completed (Schedule data available for hour calculation)
- [ ] Teacher attendance data exists in system
- [ ] PDF generation package installed (laravel-dompdf)

---

## User Stories

### TCH-009: Salary Configuration Database
**Story Points:** 2 SP
**Priority:** P0 - Critical

**As a** developer
**I want** proper salary configuration schema
**So that** honor rates can be configured and calculations stored

**Acceptance Criteria:**
- [ ] Migration `honor_rates` table: id, nama_komponen, tipe (enum: per_jam, tetap, potongan), nilai, satuan, keterangan, is_active, timestamps
- [ ] Migration `salary_calculations` table: id, teacher_id, periode (month-year), total_jam_mengajar, total_jam_hadir, tarif_per_jam, komponen_lain (JSON), total_bruto, total_potongan, total_netto, status (draft/approved/paid), approved_by, approved_at, timestamps
- [ ] Migration `salary_components` pivot: calculation_id, rate_id, jumlah, subtotal
- [ ] Model relationships
- [ ] Seeders untuk default honor rates

**Tasks:**
1. [ ] Create migration `honor_rates`
2. [ ] Create migration `salary_calculations`
3. [ ] Create migration `salary_calculation_components` pivot
4. [ ] Create `HonorRate` model
5. [ ] Create `SalaryCalculation` model dengan relationships
6. [ ] Create `SalaryCalculationComponent` model
7. [ ] Create seeders dengan default rates
8. [ ] Add relationship di Teacher: hasMany salaryCalculations

---

### TCH-010: Admin Honor Rate Configuration
**Story Points:** 1 SP
**Priority:** P0 - Critical

**As an** Admin/TU
**I want** to configure honor/salary rates
**So that** calculations use correct rates

**Acceptance Criteria:**
- [ ] Route: `GET /admin/teachers/salary/settings`
- [ ] CRUD untuk honor rates dengan fields: nama, tipe, nilai, satuan, keterangan
- [ ] Default rates: Honor per jam mengajar, Tunjangan kehadiran, Potongan ketidakhadiran
- [ ] Inline edit pada table (no separate form page)
- [ ] History log perubahan tarif
- [ ] Validation: nilai harus positive number

**Tasks:**
1. [ ] Create `HonorRateController` dengan CRUD
2. [ ] Create `StoreHonorRateRequest`
3. [ ] Create `UpdateHonorRateRequest`
4. [ ] Create `Admin/Teachers/Salary/Settings.vue`
5. [ ] Create `HonorRateTable` component dengan inline edit
6. [ ] Implement audit log untuk rate changes
7. [ ] Generate Wayfinder routes

---

### TCH-011: Admin Salary Calculation
**Story Points:** 3 SP
**Priority:** P0 - Critical

**As an** Admin/TU
**I want** to calculate teacher salaries for a period
**So that** monthly payroll can be processed

**Acceptance Criteria:**
- [ ] Route: `GET /admin/teachers/salary` → Salary list page
- [ ] Route: `POST /admin/teachers/salary/calculate` → Trigger calculation
- [ ] Period picker: select month-year
- [ ] Calculate button: process all teachers for selected period
- [ ] Calculation logic:
  - Total jam mengajar dari schedule
  - Total jam hadir dari attendance
  - Apply tarif per jam
  - Add/subtract komponen lain
  - Generate total netto
- [ ] List view: Teacher, Total Jam, Total Hadir, Bruto, Potongan, Netto, Status
- [ ] Filter by: status, teacher
- [ ] Bulk approve selected calculations
- [ ] Individual approve/reject with notes

**Tasks:**
1. [ ] Create `SalaryCalculationController@index`
2. [ ] Create `SalaryCalculationController@calculate`
3. [ ] Create `SalaryCalculationService` untuk calculation logic
4. [ ] Create `SalaryCalculationResource`
5. [ ] Create `Admin/Teachers/Salary/Index.vue`
6. [ ] Create `SalaryTable` component
7. [ ] Create `CalculationModal` untuk trigger calculation
8. [ ] Implement bulk approve
9. [ ] Add approval workflow (draft → approved → paid)
10. [ ] Generate Wayfinder routes

---

### TCH-012: Salary Detail & PDF Slip
**Story Points:** 2 SP
**Priority:** P0 - Critical

**As an** Admin/TU
**I want** to view calculation detail and generate PDF slips
**So that** teachers receive proper salary documentation

**Acceptance Criteria:**
- [ ] Route: `GET /admin/teachers/salary/{calculation}` → Detail page
- [ ] Detail view showing breakdown:
  - Periode
  - Data guru (nama, NIP, status)
  - Rincian jam mengajar per hari
  - Rincian kehadiran
  - Komponen penghasilan (with subtotals)
  - Komponen potongan
  - Total netto
- [ ] Generate PDF slip button
- [ ] PDF layout: professional, printable A4
- [ ] Batch generate PDF untuk all approved calculations
- [ ] Export payroll summary ke Excel

**Tasks:**
1. [ ] Create `SalaryCalculationController@show`
2. [ ] Create `Admin/Teachers/Salary/Detail.vue`
3. [ ] Create `SalaryDetailCard` component
4. [ ] Create `SalaryCalculationController@generatePdf`
5. [ ] Create PDF Blade template `salary-slip.blade.php`
6. [ ] Implement DomPDF generation
7. [ ] Create batch PDF generation (ZIP download)
8. [ ] Create Excel export dengan Laravel Excel
9. [ ] Generate Wayfinder routes

---

## Technical Specifications

### Database Schema

```sql
-- honor_rates table
CREATE TABLE honor_rates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(20) UNIQUE NOT NULL,
    nama VARCHAR(100) NOT NULL,
    tipe ENUM('per_jam', 'tetap', 'potongan') NOT NULL,
    nilai DECIMAL(12,2) NOT NULL,
    satuan VARCHAR(20) NULL, -- 'jam', 'hari', 'bulan', null for fixed
    keterangan TEXT NULL,
    applies_to ENUM('semua', 'tetap', 'honorer') DEFAULT 'semua',
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- salary_calculations table
CREATE TABLE salary_calculations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    teacher_id BIGINT UNSIGNED NOT NULL,
    periode VARCHAR(7) NOT NULL, -- '2025-01' format
    total_jam_terjadwal INT DEFAULT 0,
    total_jam_hadir INT DEFAULT 0,
    total_jam_alpha INT DEFAULT 0,
    total_hari_kerja INT DEFAULT 0,
    total_hari_hadir INT DEFAULT 0,
    total_bruto DECIMAL(12,2) DEFAULT 0,
    total_potongan DECIMAL(12,2) DEFAULT 0,
    total_netto DECIMAL(12,2) DEFAULT 0,
    status ENUM('draft', 'approved', 'paid') DEFAULT 'draft',
    approved_by BIGINT UNSIGNED NULL,
    approved_at TIMESTAMP NULL,
    paid_at TIMESTAMP NULL,
    catatan TEXT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_teacher_periode (teacher_id, periode)
);

-- salary_calculation_components table
CREATE TABLE salary_calculation_components (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    salary_calculation_id BIGINT UNSIGNED NOT NULL,
    honor_rate_id BIGINT UNSIGNED NOT NULL,
    nama_komponen VARCHAR(100) NOT NULL,
    tipe ENUM('penghasilan', 'potongan') NOT NULL,
    jumlah DECIMAL(10,2) DEFAULT 1, -- quantity
    tarif DECIMAL(12,2) NOT NULL, -- rate at time of calculation
    subtotal DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (salary_calculation_id) REFERENCES salary_calculations(id) ON DELETE CASCADE,
    FOREIGN KEY (honor_rate_id) REFERENCES honor_rates(id) ON DELETE CASCADE
);
```

### Calculation Logic

```php
// SalaryCalculationService.php
public function calculateForPeriod(Teacher $teacher, string $periode): SalaryCalculation
{
    $year = substr($periode, 0, 4);
    $month = substr($periode, 5, 2);
    
    // Get scheduled hours untuk periode ini
    $schedules = $teacher->schedules()
        ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
        ->get();
    
    $totalJamTerjadwal = $this->calculateScheduledHours($schedules, $year, $month);
    
    // Get attendance data
    $attendance = $teacher->attendances()
        ->whereYear('tanggal', $year)
        ->whereMonth('tanggal', $month)
        ->get();
    
    $totalJamHadir = $this->calculateAttendedHours($attendance);
    $totalHariKerja = $this->countWorkingDays($year, $month);
    $totalHariHadir = $attendance->where('status', 'hadir')->count();
    
    // Get active honor rates
    $rates = HonorRate::where('is_active', true)
        ->where(function ($q) use ($teacher) {
            $q->where('applies_to', 'semua')
              ->orWhere('applies_to', $teacher->status_kepegawaian);
        })
        ->get();
    
    // Calculate components
    $components = [];
    $totalBruto = 0;
    $totalPotongan = 0;
    
    foreach ($rates as $rate) {
        $component = $this->calculateComponent($rate, $totalJamHadir, $totalHariHadir);
        $components[] = $component;
        
        if ($rate->tipe === 'potongan') {
            $totalPotongan += $component['subtotal'];
        } else {
            $totalBruto += $component['subtotal'];
        }
    }
    
    // Create or update calculation
    $calculation = SalaryCalculation::updateOrCreate(
        ['teacher_id' => $teacher->id, 'periode' => $periode],
        [
            'total_jam_terjadwal' => $totalJamTerjadwal,
            'total_jam_hadir' => $totalJamHadir,
            'total_jam_alpha' => $totalJamTerjadwal - $totalJamHadir,
            'total_hari_kerja' => $totalHariKerja,
            'total_hari_hadir' => $totalHariHadir,
            'total_bruto' => $totalBruto,
            'total_potongan' => $totalPotongan,
            'total_netto' => $totalBruto - $totalPotongan,
            'status' => 'draft',
        ]
    );
    
    // Save components
    $calculation->components()->delete();
    foreach ($components as $comp) {
        $calculation->components()->create($comp);
    }
    
    return $calculation;
}
```

### API Endpoints

| Method | Endpoint | Controller | Description |
|--------|----------|------------|-------------|
| GET | `/admin/teachers/salary` | SalaryCalculationController@index | List calculations |
| POST | `/admin/teachers/salary/calculate` | SalaryCalculationController@calculate | Run calculation |
| GET | `/admin/teachers/salary/{id}` | SalaryCalculationController@show | Detail view |
| POST | `/admin/teachers/salary/{id}/approve` | SalaryCalculationController@approve | Approve single |
| POST | `/admin/teachers/salary/bulk-approve` | SalaryCalculationController@bulkApprove | Approve multiple |
| GET | `/admin/teachers/salary/{id}/pdf` | SalaryCalculationController@generatePdf | Download PDF |
| GET | `/admin/teachers/salary/export` | SalaryCalculationController@export | Export Excel |
| GET | `/admin/teachers/salary/settings` | HonorRateController@index | Rate settings |
| POST | `/admin/teachers/salary/settings` | HonorRateController@store | Create rate |
| PUT | `/admin/teachers/salary/settings/{id}` | HonorRateController@update | Update rate |
| DELETE | `/admin/teachers/salary/settings/{id}` | HonorRateController@destroy | Delete rate |

### File Structure

```
app/
├── Http/
│   ├── Controllers/Admin/
│   │   ├── SalaryCalculationController.php
│   │   └── HonorRateController.php
│   ├── Requests/Admin/
│   │   ├── CalculateSalaryRequest.php
│   │   ├── StoreHonorRateRequest.php
│   │   └── UpdateHonorRateRequest.php
│   └── Resources/
│       ├── SalaryCalculationResource.php
│       └── HonorRateResource.php
├── Models/
│   ├── HonorRate.php
│   ├── SalaryCalculation.php
│   └── SalaryCalculationComponent.php
├── Services/
│   └── SalaryCalculationService.php
└── Exports/
    └── PayrollExport.php

resources/
├── js/Pages/Admin/Teachers/Salary/
│   ├── Index.vue
│   ├── Detail.vue
│   ├── Settings.vue
│   └── Components/
│       ├── SalaryTable.vue
│       ├── SalaryDetailCard.vue
│       ├── CalculationModal.vue
│       └── HonorRateTable.vue
└── views/pdf/
    └── salary-slip.blade.php
```

---

## PDF Slip Template

```blade
{{-- resources/views/pdf/salary-slip.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <title>Slip Gaji - {{ $calculation->teacher->nama_lengkap }}</title>
    <style>
        /* Professional A4 layout styles */
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .school-name { font-size: 18px; font-weight: bold; }
        .title { font-size: 14px; margin-top: 5px; }
        .info-table { width: 100%; margin: 15px 0; }
        .info-table td { padding: 3px 0; }
        .components-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .components-table th, .components-table td { 
            border: 1px solid #333; padding: 8px; text-align: left; 
        }
        .components-table th { background: #f0f0f0; }
        .text-right { text-align: right; }
        .total-row { font-weight: bold; background: #e0e0e0; }
        .footer { margin-top: 30px; }
        .signature { float: right; text-align: center; width: 200px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="school-name">{{ config('app.school_name') }}</div>
        <div class="title">SLIP GAJI / HONOR</div>
        <div>Periode: {{ $calculation->periode_formatted }}</div>
    </div>
    
    <table class="info-table">
        <tr><td width="120">Nama</td><td>: {{ $calculation->teacher->nama_lengkap }}</td></tr>
        <tr><td>NIP</td><td>: {{ $calculation->teacher->nip ?? '-' }}</td></tr>
        <tr><td>Status</td><td>: {{ ucfirst($calculation->teacher->status_kepegawaian) }}</td></tr>
        <tr><td>Jam Mengajar</td><td>: {{ $calculation->total_jam_hadir }} jam</td></tr>
    </table>
    
    <table class="components-table">
        <thead>
            <tr>
                <th>Komponen</th>
                <th>Jumlah</th>
                <th>Tarif</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($calculation->components->where('tipe', 'penghasilan') as $comp)
            <tr>
                <td>{{ $comp->nama_komponen }}</td>
                <td>{{ $comp->jumlah }}</td>
                <td>Rp {{ number_format($comp->tarif, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($comp->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3">Total Penghasilan</td>
                <td class="text-right">Rp {{ number_format($calculation->total_bruto, 0, ',', '.') }}</td>
            </tr>
            
            @foreach($calculation->components->where('tipe', 'potongan') as $comp)
            <tr>
                <td>{{ $comp->nama_komponen }}</td>
                <td>{{ $comp->jumlah }}</td>
                <td>Rp {{ number_format($comp->tarif, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($comp->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3">Total Potongan</td>
                <td class="text-right">Rp {{ number_format($calculation->total_potongan, 0, ',', '.') }}</td>
            </tr>
            
            <tr class="total-row" style="font-size: 14px;">
                <td colspan="3">TOTAL DITERIMA</td>
                <td class="text-right">Rp {{ number_format($calculation->total_netto, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
    
    <div class="footer">
        <div class="signature">
            <p>{{ config('app.school_city') }}, {{ now()->translatedFormat('d F Y') }}</p>
            <p>Bendahara</p>
            <br><br><br>
            <p>____________________</p>
        </div>
    </div>
</body>
</html>
```

---

## Definition of Done

- [ ] Honor rates configurable
- [ ] Calculation runs correctly for all teachers
- [ ] Calculation considers schedule and attendance
- [ ] Approval workflow functional
- [ ] PDF slip generates correctly
- [ ] Excel export works
- [ ] Currency formatting correct (Rupiah)
- [ ] Feature tests passing
- [ ] No lint errors

---

## Dependencies

**Blocked By:**
- Sprint 01: Teacher data
- Sprint 02: Schedule data (for hour calculation)
- Existing attendance system (for hadir/alpha data)

**Blocks:**
- Sprint 05: Dashboard (salary summary widget)
- Teacher self-service (view own salary)

---

## Risk & Mitigation

| Risk | Impact | Mitigation |
|------|--------|------------|
| Attendance data format mismatch | High | Verify existing attendance schema first |
| Decimal precision issues | Medium | Use DECIMAL(12,2), proper rounding |
| PDF generation memory | Medium | Batch processing, queue for bulk |
| Complex calculation logic | High | Unit tests, manual verification |

---

## Notes

- Tarif default: Rp 50.000/jam (configurable)
- Periode format: YYYY-MM (e.g., "2025-01")
- Calculation bisa di-run ulang (draft status)
- Approved calculation tidak bisa di-edit (harus reject dulu)
- PDF harus include QR code atau barcode untuk verification (phase 2)
