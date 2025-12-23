<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #2563eb;
            margin: 0;
            font-size: 24px;
        }
        .info-box {
            background-color: #eff6ff;
            border-left: 4px solid #2563eb;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .button {
            display: inline-block;
            background-color: #2563eb;
            color: #ffffff;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 6px;
            margin: 20px 0;
            font-weight: 600;
        }
        .button:hover {
            background-color: #1d4ed8;
        }
        .warning {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            font-size: 14px;
        }
        .expiry {
            background-color: #fee2e2;
            border-left: 4px solid #ef4444;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            font-size: 14px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 14px;
            text-align: center;
        }
        .link-alternative {
            background-color: #f8fafc;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
            word-break: break-all;
            font-size: 12px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Reset Password</h1>
        </div>

        <p>Halo <strong>{{ $userName }}</strong>,</p>

        <p>Kami menerima permintaan untuk mereset password akun Anda di Sistem Informasi Sekolah. Klik tombol di bawah untuk membuat password baru:</p>

        <div style="text-align: center;">
            <a href="{{ $resetUrl }}" class="button">Reset Password Saya</a>
        </div>

        <div class="expiry">
            <strong>⏰ Link ini akan kadaluarsa dalam 1 jam.</strong><br>
            Silakan segera reset password Anda sebelum link tidak dapat digunakan.
        </div>

        <div class="warning">
            <strong>⚠️ Penting:</strong> Jika Anda tidak meminta reset password, abaikan email ini dan password Anda akan tetap aman.
        </div>

        <p>Jika tombol di atas tidak berfungsi, copy dan paste link berikut ke browser Anda:</p>

        <div class="link-alternative">
            {{ $resetUrl }}
        </div>

        <div class="info-box">
            <strong>💡 Tips Keamanan:</strong>
            <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                <li>Gunakan password minimal 8 karakter</li>
                <li>Kombinasikan huruf, angka, dan simbol</li>
                <li>Jangan gunakan password yang sama dengan website lain</li>
                <li>Simpan password Anda dengan aman</li>
            </ul>
        </div>

        <div class="footer">
            <p>Email ini dikirim secara otomatis oleh sistem. Mohon tidak membalas email ini.</p>
            <p>Jika Anda membutuhkan bantuan, silakan hubungi administrator sistem.</p>
            <p>&copy; {{ date('Y') }} Sistem Informasi Sekolah. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

