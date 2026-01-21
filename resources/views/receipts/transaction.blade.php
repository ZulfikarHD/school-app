<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Kwitansi {{ $transaction->transaction_number }}</title>
    <style>
        @page {
            margin: 10mm;
            size: A4 portrait;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #1e293b;
            line-height: 1.5;
        }
        .receipt {
            width: 100%;
            max-width: 180mm;
            margin: 0 auto;
            padding: 5mm;
        }
        .header {
            text-align: center;
            margin-bottom: 5mm;
            padding-bottom: 4mm;
            border-bottom: 1px dashed #64748b;
        }
        .school-name {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 2mm;
        }
        .school-address {
            font-size: 9px;
            color: #64748b;
        }
        .title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin: 5mm 0;
            padding: 3mm;
            background-color: #f1f5f9;
            border-radius: 2mm;
        }
        .section {
            margin-bottom: 4mm;
        }
        .section-title {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2mm;
            font-weight: bold;
        }
        .row {
            display: table;
            width: 100%;
            margin-bottom: 1.5mm;
        }
        .row .label {
            display: table-cell;
            width: 35%;
            color: #64748b;
            vertical-align: top;
        }
        .row .value {
            display: table-cell;
            width: 65%;
            color: #1e293b;
            font-weight: 500;
        }
        .divider {
            border-bottom: 1px dashed #cbd5e1;
            margin: 4mm 0;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 3mm 0;
        }
        .items-table th {
            background-color: #f1f5f9;
            padding: 2mm;
            font-size: 9px;
            text-align: left;
            color: #64748b;
            border-bottom: 1px solid #cbd5e1;
        }
        .items-table td {
            padding: 2mm;
            font-size: 10px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }
        .items-table .amount {
            text-align: right;
            font-weight: 500;
        }
        .total-section {
            background-color: #ecfdf5;
            padding: 4mm;
            border-radius: 2mm;
            margin: 4mm 0;
        }
        .total-row {
            display: table;
            width: 100%;
        }
        .total-row .label {
            display: table-cell;
            width: 40%;
            font-weight: bold;
            color: #047857;
            font-size: 12px;
        }
        .total-row .value {
            display: table-cell;
            width: 60%;
            text-align: right;
            font-weight: bold;
            color: #047857;
            font-size: 16px;
        }
        .footer {
            margin-top: 6mm;
            padding-top: 4mm;
            border-top: 1px dashed #cbd5e1;
        }
        .footer-row {
            display: table;
            width: 100%;
            margin-bottom: 1mm;
        }
        .footer-row .label {
            display: table-cell;
            width: 35%;
            color: #94a3b8;
            font-size: 9px;
        }
        .footer-row .value {
            display: table-cell;
            width: 65%;
            color: #64748b;
            font-size: 9px;
        }
        .signature-area {
            margin-top: 10mm;
            text-align: right;
            padding-right: 15mm;
        }
        .signature-box {
            display: inline-block;
            text-align: center;
        }
        .signature-line {
            width: 50mm;
            border-bottom: 1px solid #1e293b;
            margin-bottom: 2mm;
            height: 15mm;
        }
        .signature-name {
            font-size: 10px;
            color: #1e293b;
        }
        .signature-title {
            font-size: 9px;
            color: #64748b;
        }
        .note {
            margin-top: 6mm;
            padding: 3mm;
            background-color: #fefce8;
            border-radius: 2mm;
            font-size: 9px;
            color: #854d0e;
            text-align: center;
        }
        .receipt-number {
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 11px;
            color: #1e293b;
        }
        .status-verified {
            display: inline-block;
            padding: 1mm 3mm;
            background-color: #d1fae5;
            color: #047857;
            border-radius: 2mm;
            font-size: 9px;
            font-weight: bold;
        }
        .status-pending {
            display: inline-block;
            padding: 1mm 3mm;
            background-color: #fef3c7;
            color: #b45309;
            border-radius: 2mm;
            font-size: 9px;
            font-weight: bold;
        }
        .bill-count {
            display: inline-block;
            padding: 1mm 2mm;
            background-color: #e0e7ff;
            color: #3730a3;
            border-radius: 2mm;
            font-size: 9px;
            font-weight: bold;
            margin-left: 2mm;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- Header -->
        <div class="header">
            <div class="school-name">{{ $school['name'] }}</div>
            <div class="school-address">{{ $school['address'] }}</div>
            @if(!empty($school['phone']))
            <div class="school-address">Telp: {{ $school['phone'] }}</div>
            @endif
        </div>

        <!-- Title -->
        <div class="title">
            KWITANSI PEMBAYARAN
            <span class="bill-count">{{ $items->count() }} Tagihan</span>
        </div>

        <!-- Receipt Info -->
        <div class="section">
            <div class="row">
                <div class="label">No. Transaksi</div>
                <div class="value receipt-number">{{ $transaction->transaction_number }}</div>
            </div>
            <div class="row">
                <div class="label">Tanggal</div>
                <div class="value">{{ $transaction->payment_date?->format('d M Y') }}</div>
            </div>
            <div class="row">
                <div class="label">Waktu</div>
                <div class="value">{{ $transaction->payment_time?->format('H:i') ?? '-' }} WIB</div>
            </div>
            <div class="row">
                <div class="label">Metode</div>
                <div class="value">{{ $transaction->method_label }}</div>
            </div>
            <div class="row">
                <div class="label">Status</div>
                <div class="value">
                    @if($transaction->status === 'verified')
                    <span class="status-verified">TERVERIFIKASI</span>
                    @elseif($transaction->status === 'pending')
                    <span class="status-pending">MENUNGGU VERIFIKASI</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="divider"></div>

        <!-- Student Info (unique students from items) -->
        @php
            $students = $items->map(fn($item) => $item->student)->unique('id');
        @endphp
        <div class="section">
            <div class="section-title">Informasi Siswa</div>
            @foreach($students as $student)
            <div class="row">
                <div class="label">{{ $loop->first ? 'Nama' : '' }}</div>
                <div class="value">
                    {{ $student->nama_lengkap }}
                    <span style="color: #64748b; font-size: 10px;">
                        (NIS: {{ $student->nis }} • {{ $student->kelas?->nama_lengkap ?? '-' }})
                    </span>
                </div>
            </div>
            @endforeach
        </div>

        @if($transaction->guardian)
        <div class="row">
            <div class="label">Pembayar</div>
            <div class="value">{{ $transaction->guardian->nama_lengkap }} ({{ $transaction->guardian->hubungan_label }})</div>
        </div>
        @endif

        <div class="divider"></div>

        <!-- Items Detail -->
        <div class="section">
            <div class="section-title">Detail Tagihan yang Dibayar</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 25%;">No. Tagihan</th>
                        <th style="width: 20%;">Kategori</th>
                        <th style="width: 20%;">Periode</th>
                        <th style="width: 15%;">Siswa</th>
                        <th style="width: 15%;">Nominal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="receipt-number" style="font-size: 9px;">{{ $item->bill->nomor_tagihan }}</td>
                        <td>{{ $item->bill->paymentCategory->nama ?? '-' }}</td>
                        <td>{{ $item->bill->nama_bulan ?? '-' }} {{ $item->bill->tahun }}</td>
                        <td>{{ $item->student->nama_lengkap }}</td>
                        <td class="amount">{{ $item->formatted_amount }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Total -->
        <div class="total-section">
            <div class="total-row">
                <div class="label">TOTAL BAYAR ({{ $items->count() }} tagihan)</div>
                <div class="value">{{ $transaction->formatted_amount }}</div>
            </div>
        </div>

        @if($transaction->notes)
        <div class="section">
            <div class="section-title">Catatan</div>
            <p style="font-size: 10px; color: #475569;">{{ $transaction->notes }}</p>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div class="footer-row">
                <div class="label">Petugas</div>
                <div class="value">{{ $transaction->creator?->name ?? '-' }}</div>
            </div>
            @if($transaction->verifier)
            <div class="footer-row">
                <div class="label">Verifikator</div>
                <div class="value">{{ $transaction->verifier->name }} ({{ $transaction->verified_at?->format('d M Y H:i') }})</div>
            </div>
            @endif
            <div class="footer-row">
                <div class="label">Dicetak</div>
                <div class="value">{{ $generated_at }}</div>
            </div>
        </div>

        <!-- Signature -->
        <div class="signature-area">
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-name">{{ $transaction->verifier?->name ?? $transaction->creator?->name ?? 'Petugas' }}</div>
                <div class="signature-title">Petugas Administrasi</div>
            </div>
        </div>

        <!-- Note -->
        <div class="note">
            Kwitansi ini adalah bukti pembayaran yang sah. Simpan sebagai bukti pembayaran Anda.
        </div>
    </div>
</body>
</html>
