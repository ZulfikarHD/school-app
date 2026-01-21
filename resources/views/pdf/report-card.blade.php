<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Rapor {{ $student['nama_lengkap'] }} - {{ $academic['semester_label'] }} {{ $academic['tahun_ajaran'] }}</title>
    <style>
        @page {
            margin: 15mm;
            size: A4 portrait;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #1e293b;
            line-height: 1.4;
        }
        .report-card {
            width: 100%;
            max-width: 180mm;
            margin: 0 auto;
        }

        /* Header Section */
        .header {
            text-align: center;
            margin-bottom: 5mm;
            padding-bottom: 3mm;
            border-bottom: 2px solid #1e293b;
        }
        .header-content {
            display: table;
            width: 100%;
        }
        .header-logo {
            display: table-cell;
            width: 15%;
            vertical-align: middle;
            text-align: center;
        }
        .header-logo img {
            width: 20mm;
            height: 20mm;
        }
        .header-text {
            display: table-cell;
            width: 70%;
            vertical-align: middle;
            text-align: center;
        }
        .header-right {
            display: table-cell;
            width: 15%;
            vertical-align: middle;
        }
        .school-name {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            margin-bottom: 1mm;
        }
        .school-address {
            font-size: 9px;
            color: #475569;
        }

        /* Title */
        .title {
            text-align: center;
            margin: 5mm 0;
        }
        .title h1 {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 1mm;
        }
        .title h2 {
            font-size: 12px;
            font-weight: normal;
            color: #475569;
        }

        /* Student Info Section */
        .student-info {
            margin-bottom: 5mm;
            padding: 3mm;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 1mm;
        }
        .info-row:last-child {
            margin-bottom: 0;
        }
        .info-col {
            display: table-cell;
            width: 50%;
        }
        .info-label {
            display: inline-block;
            width: 35mm;
            color: #64748b;
        }
        .info-value {
            display: inline-block;
            color: #1e293b;
            font-weight: 500;
        }
        .info-separator {
            display: inline-block;
            width: 5mm;
            text-align: center;
        }

        /* Section Title */
        .section-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            background-color: #334155;
            color: white;
            padding: 2mm 3mm;
            margin-bottom: 2mm;
        }

        /* Grades Table */
        .grades-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5mm;
        }
        .grades-table th,
        .grades-table td {
            border: 1px solid #cbd5e1;
            padding: 2mm;
            text-align: center;
        }
        .grades-table th {
            background-color: #e2e8f0;
            font-weight: bold;
            font-size: 9px;
        }
        .grades-table td {
            font-size: 10px;
        }
        .grades-table td.subject-name {
            text-align: left;
            padding-left: 3mm;
        }
        .grades-table td.number {
            width: 8mm;
        }
        .grades-table td.grade-col {
            width: 15mm;
        }
        .grades-table td.predikat-col {
            width: 12mm;
        }
        .predikat-a {
            color: #047857;
            font-weight: bold;
        }
        .predikat-b {
            color: #0369a1;
            font-weight: bold;
        }
        .predikat-c {
            color: #b45309;
            font-weight: bold;
        }
        .predikat-d {
            color: #dc2626;
            font-weight: bold;
        }

        /* Attitude Section */
        .attitude-section {
            margin-bottom: 5mm;
        }
        .attitude-table {
            width: 100%;
            border-collapse: collapse;
        }
        .attitude-table th,
        .attitude-table td {
            border: 1px solid #cbd5e1;
            padding: 2mm 3mm;
        }
        .attitude-table th {
            background-color: #e2e8f0;
            font-weight: bold;
            text-align: left;
            width: 25%;
        }
        .attitude-table td.grade {
            width: 10%;
            text-align: center;
            font-weight: bold;
        }
        .attitude-table td.description {
            width: 65%;
        }

        /* Attendance Section */
        .attendance-section {
            margin-bottom: 5mm;
        }
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
        }
        .attendance-table th,
        .attendance-table td {
            border: 1px solid #cbd5e1;
            padding: 2mm;
            text-align: center;
        }
        .attendance-table th {
            background-color: #e2e8f0;
            font-weight: bold;
        }
        .attendance-table td {
            width: 20%;
        }

        /* Summary Section */
        .summary-section {
            margin-bottom: 5mm;
        }
        .summary-table {
            width: 50%;
            border-collapse: collapse;
        }
        .summary-table th,
        .summary-table td {
            border: 1px solid #cbd5e1;
            padding: 2mm 3mm;
        }
        .summary-table th {
            background-color: #e2e8f0;
            font-weight: bold;
            text-align: left;
            width: 50%;
        }
        .summary-table td {
            text-align: center;
            font-weight: bold;
        }

        /* Homeroom Notes */
        .notes-section {
            margin-bottom: 5mm;
        }
        .notes-box {
            border: 1px solid #cbd5e1;
            padding: 3mm;
            min-height: 20mm;
            background-color: #fefce8;
        }

        /* Signature Section */
        .signature-section {
            margin-top: 10mm;
            page-break-inside: avoid;
        }
        .signature-row {
            display: table;
            width: 100%;
        }
        .signature-col {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        .signature-date {
            font-size: 9px;
            margin-bottom: 3mm;
        }
        .signature-title {
            font-size: 9px;
            color: #64748b;
            margin-bottom: 15mm;
        }
        .signature-line {
            width: 50mm;
            border-bottom: 1px solid #1e293b;
            margin: 0 auto 2mm auto;
        }
        .signature-name {
            font-size: 10px;
            font-weight: bold;
        }
        .signature-nip {
            font-size: 8px;
            color: #64748b;
        }

        /* Footer */
        .footer {
            margin-top: 5mm;
            padding-top: 3mm;
            border-top: 1px solid #cbd5e1;
            font-size: 8px;
            color: #94a3b8;
            text-align: center;
        }

        /* Page break utility */
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="report-card">
        <!-- Header -->
        <div class="header">
            <div class="header-content">
                <div class="header-logo">
                    {{-- Logo placeholder - bisa diganti dengan logo sekolah --}}
                </div>
                <div class="header-text">
                    <div class="school-name">{{ config('app.school_name', 'SD/SMP/SMA Contoh') }}</div>
                    <div class="school-address">{{ config('app.school_address', 'Jl. Pendidikan No. 123, Jakarta') }}</div>
                    <div class="school-address">Telp: {{ config('app.school_phone', '(021) 12345678') }}</div>
                </div>
                <div class="header-right"></div>
            </div>
        </div>

        <!-- Title -->
        <div class="title">
            <h1>Laporan Hasil Belajar Peserta Didik</h1>
            <h2>Semester {{ $academic['semester_label'] }} Tahun Pelajaran {{ $academic['tahun_ajaran'] }}</h2>
        </div>

        <!-- Student Info -->
        <div class="student-info">
            <div class="info-row">
                <div class="info-col">
                    <span class="info-label">Nama Peserta Didik</span>
                    <span class="info-separator">:</span>
                    <span class="info-value">{{ $student['nama_lengkap'] }}</span>
                </div>
                <div class="info-col">
                    <span class="info-label">Kelas</span>
                    <span class="info-separator">:</span>
                    <span class="info-value">{{ $class['nama_lengkap'] }}</span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-col">
                    <span class="info-label">NIS / NISN</span>
                    <span class="info-separator">:</span>
                    <span class="info-value">{{ $student['nis'] }} / {{ $student['nisn'] }}</span>
                </div>
                <div class="info-col">
                    <span class="info-label">Tahun Pelajaran</span>
                    <span class="info-separator">:</span>
                    <span class="info-value">{{ $academic['tahun_ajaran'] }}</span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-col">
                    <span class="info-label">Tempat, Tanggal Lahir</span>
                    <span class="info-separator">:</span>
                    <span class="info-value">{{ $student['tempat_lahir'] }}, {{ $student['tanggal_lahir'] }}</span>
                </div>
                <div class="info-col">
                    <span class="info-label">Semester</span>
                    <span class="info-separator">:</span>
                    <span class="info-value">{{ $academic['semester'] }} ({{ $academic['semester_label'] }})</span>
                </div>
            </div>
        </div>

        <!-- Section A: Nilai Sikap -->
        @if($attitude)
        <div class="attitude-section">
            <div class="section-title">A. Sikap</div>
            <table class="attitude-table">
                <tr>
                    <th>1. Sikap Spiritual</th>
                    <td class="grade predikat-{{ strtolower($attitude['spiritual_grade']) }}">{{ $attitude['spiritual_grade'] }}</td>
                    <td class="description">{{ $attitude['spiritual_description'] }}</td>
                </tr>
                <tr>
                    <th>2. Sikap Sosial</th>
                    <td class="grade predikat-{{ strtolower($attitude['social_grade']) }}">{{ $attitude['social_grade'] }}</td>
                    <td class="description">{{ $attitude['social_description'] }}</td>
                </tr>
            </table>
        </div>
        @endif

        <!-- Section B: Nilai Pengetahuan dan Keterampilan -->
        <div class="grades-section">
            <div class="section-title">B. Pengetahuan dan Keterampilan</div>
            <table class="grades-table">
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 8mm;">No</th>
                        <th rowspan="2">Mata Pelajaran</th>
                        <th colspan="2">Pengetahuan</th>
                        <th colspan="2">Keterampilan</th>
                    </tr>
                    <tr>
                        <th style="width: 15mm;">Nilai</th>
                        <th style="width: 12mm;">Predikat</th>
                        <th style="width: 15mm;">Nilai</th>
                        <th style="width: 12mm;">Predikat</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($grades as $index => $grade)
                    <tr>
                        <td class="number">{{ $index + 1 }}</td>
                        <td class="subject-name">{{ $grade['subject_name'] }}</td>
                        <td class="grade-col">{{ number_format($grade['final_grade'], 0) }}</td>
                        <td class="predikat-col predikat-{{ strtolower($grade['predikat']) }}">{{ $grade['predikat'] }}</td>
                        {{-- Keterampilan (untuk K13, bisa sama atau berbeda dengan pengetahuan) --}}
                        <td class="grade-col">{{ number_format($grade['breakdown']['praktik']['average'] > 0 ? $grade['breakdown']['praktik']['average'] : $grade['final_grade'], 0) }}</td>
                        <td class="predikat-col predikat-{{ strtolower($grade['predikat']) }}">{{ $grade['predikat'] }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #64748b;">Belum ada data nilai</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Predikat Legend -->
            <div style="font-size: 8px; color: #64748b; margin-top: 2mm;">
                Keterangan Predikat: A = Sangat Baik (90-100), B = Baik (80-89), C = Cukup (70-79), D = Kurang (&lt;70)
            </div>
        </div>

        <!-- Section C: Kehadiran -->
        <div class="attendance-section">
            <div class="section-title">C. Kehadiran</div>
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>Sakit</th>
                        <th>Izin</th>
                        <th>Tanpa Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $attendance['sakit'] }} hari</td>
                        <td>{{ $attendance['izin'] }} hari</td>
                        <td>{{ $attendance['alpha'] }} hari</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Summary -->
        <div class="summary-section">
            <div class="section-title">D. Ringkasan</div>
            <table class="summary-table">
                <tr>
                    <th>Rata-rata Nilai</th>
                    <td>{{ number_format($overall['average'], 2) }}</td>
                </tr>
                <tr>
                    <th>Peringkat</th>
                    <td>{{ $overall['rank'] }} dari {{ $overall['total_students'] }} siswa</td>
                </tr>
            </table>
        </div>

        <!-- Section E: Catatan Wali Kelas -->
        <div class="notes-section">
            <div class="section-title">E. Catatan Wali Kelas</div>
            <div class="notes-box">
                @if($attitude && $attitude['homeroom_notes'])
                    {{ $attitude['homeroom_notes'] }}
                @else
                    <span style="color: #94a3b8; font-style: italic;">Tidak ada catatan</span>
                @endif
            </div>
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-row">
                <div class="signature-col">
                    <div class="signature-date">Mengetahui,</div>
                    <div class="signature-title">Orang Tua / Wali</div>
                    <div class="signature-line"></div>
                    <div class="signature-name">.................................</div>
                </div>
                <div class="signature-col">
                    <div class="signature-date">{{ $generated_at }}</div>
                    <div class="signature-title">Wali Kelas</div>
                    <div class="signature-line"></div>
                    <div class="signature-name">{{ $class['wali_kelas'] }}</div>
                    <div class="signature-nip">NIP. ........................</div>
                </div>
            </div>
            <div style="text-align: center; margin-top: 10mm;">
                <div class="signature-title">Kepala Sekolah</div>
                <div style="height: 15mm;"></div>
                <div class="signature-line" style="margin: 0 auto;"></div>
                <div class="signature-name">{{ config('app.principal_name', '................................') }}</div>
                <div class="signature-nip">NIP. {{ config('app.principal_nip', '........................') }}</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            Dicetak pada {{ $generated_at }} | Dokumen ini digenerate secara elektronik
        </div>
    </div>
</body>
</html>
