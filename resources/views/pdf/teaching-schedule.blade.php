<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #1f2937;
        }

        .container {
            padding: 20px;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #1f2937;
        }

        .header h1 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 12px;
            color: #6b7280;
        }

        /* Info Box */
        .info-box {
            background: #f3f4f6;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .info-box table {
            width: 100%;
        }

        .info-box td {
            padding: 2px 8px 2px 0;
        }

        .info-box .label {
            font-weight: bold;
            width: 120px;
        }

        /* Schedule Table */
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .schedule-table th,
        .schedule-table td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            text-align: left;
        }

        .schedule-table th {
            background: #1f2937;
            color: white;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
        }

        .schedule-table tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        .schedule-table tbody tr:hover {
            background: #e5e7eb;
        }

        /* Day Headers */
        .day-header {
            background: #3b82f6 !important;
            color: white;
            font-weight: bold;
            text-align: center;
        }

        /* Matrix View */
        .matrix-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }

        .matrix-table th,
        .matrix-table td {
            border: 1px solid #d1d5db;
            padding: 4px;
            text-align: center;
            vertical-align: top;
        }

        .matrix-table th {
            background: #1f2937;
            color: white;
            font-weight: bold;
        }

        .matrix-table .time-cell {
            background: #f3f4f6;
            font-weight: bold;
            width: 50px;
        }

        .matrix-table .schedule-cell {
            background: #dbeafe;
            min-height: 30px;
            text-align: left;
            padding: 3px 5px;
        }

        .matrix-table .empty-cell {
            background: #ffffff;
        }

        .schedule-info {
            font-size: 7px;
        }

        .schedule-info .subject {
            font-weight: bold;
            font-size: 8px;
            margin-bottom: 2px;
        }

        .schedule-info .detail {
            color: #6b7280;
        }

        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #d1d5db;
            font-size: 9px;
            color: #6b7280;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            text-align: center;
            margin-top: 40px;
        }

        .signature-line {
            border-top: 1px solid #1f2937;
            width: 150px;
            margin: 60px auto 5px;
        }

        /* Page break */
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>{{ $title }}</h1>
            <p>{{ $subtitle }}</p>
        </div>

        <!-- Entity Info -->
        <div class="info-box">
            <table>
                @if($type === 'teacher')
                    <tr>
                        <td class="label">Nama Guru</td>
                        <td>: {{ $entity->nama_lengkap }}</td>
                    </tr>
                    <tr>
                        <td class="label">NIP</td>
                        <td>: {{ $entity->nip ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Status</td>
                        <td>: {{ ucfirst($entity->status_kepegawaian ?? '-') }}</td>
                    </tr>
                @else
                    <tr>
                        <td class="label">Kelas</td>
                        <td>: {{ $entity->tingkat }}{{ $entity->nama }}</td>
                    </tr>
                    <tr>
                        <td class="label">Tahun Ajaran</td>
                        <td>: {{ $entity->tahun_ajaran }}</td>
                    </tr>
                    <tr>
                        <td class="label">Kapasitas</td>
                        <td>: {{ $entity->kapasitas }} siswa</td>
                    </tr>
                @endif
                <tr>
                    <td class="label">Total Jadwal</td>
                    <td>: {{ $schedules->count() }} jadwal</td>
                </tr>
            </table>
        </div>

        <!-- Schedule by Day -->
        @foreach($hariOptions as $hari)
            @php
                $daySchedules = $schedulesByDay[$hari->value] ?? collect();
            @endphp

            @if($daySchedules->isNotEmpty())
                <h3 style="margin-top: 15px; margin-bottom: 8px; font-size: 12px; color: #1f2937;">
                    {{ $hari->label() }}
                </h3>
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th style="width: 80px;">Waktu</th>
                            <th>Mata Pelajaran</th>
                            @if($type === 'teacher')
                                <th style="width: 100px;">Kelas</th>
                            @else
                                <th style="width: 150px;">Guru</th>
                            @endif
                            <th style="width: 80px;">Ruangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($daySchedules->sortBy('jam_mulai') as $schedule)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($schedule->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->jam_selesai)->format('H:i') }}</td>
                                <td>
                                    <strong>{{ $schedule->subject->nama_mapel }}</strong>
                                    <br>
                                    <span style="font-size: 8px; color: #6b7280;">{{ $schedule->subject->kode_mapel }}</span>
                                </td>
                                @if($type === 'teacher')
                                    <td>Kelas {{ $schedule->schoolClass->tingkat }}{{ $schedule->schoolClass->nama }}</td>
                                @else
                                    <td>{{ $schedule->teacher->nama_lengkap }}</td>
                                @endif
                                <td>{{ $schedule->ruangan ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @endforeach

        <!-- Footer -->
        <div class="footer">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 50%;">
                        Dicetak pada: {{ now()->setTimezone('Asia/Jakarta')->format('d F Y, H:i') }} WIB
                    </td>
                    <td style="width: 50%; text-align: right;">
                        <div class="signature-box">
                            <p>Mengetahui,</p>
                            <p>Kepala Sekolah</p>
                            <div class="signature-line"></div>
                            <p>NIP: ........................</p>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
