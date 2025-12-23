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
        .credentials {
            background-color: #f8fafc;
            border-left: 4px solid #2563eb;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .credentials-item {
            margin: 10px 0;
        }
        .credentials-label {
            font-weight: 600;
            color: #64748b;
            display: inline-block;
            width: 120px;
        }
        .credentials-value {
            font-family: 'Courier New', monospace;
            background-color: #ffffff;
            padding: 4px 8px;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
            display: inline-block;
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
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 14px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎓 Akun Anda Telah Dibuat</h1>
        </div>

        <p>Halo <strong>{{ $userName }}</strong>,</p>

        <p>Selamat datang di Sistem Informasi Sekolah! Akun Anda telah berhasil dibuat oleh administrator.</p>

        <div class="credentials">
            <div class="credentials-item">
                <span class="credentials-label">Username:</span>
                <span class="credentials-value">{{ $username }}</span>
            </div>
            <div class="credentials-item">
                <span class="credentials-label">Password:</span>
                <span class="credentials-value">{{ $password }}</span>
            </div>
        </div>

        <div class="warning">
            <strong>⚠️ Penting:</strong> Anda akan diminta untuk mengubah password ini pada saat login pertama kali. Pastikan untuk membuat password yang kuat dan mudah diingat.
        </div>

        <div style="text-align: center;">
            <a href="{{ $loginUrl }}" class="button">Login Sekarang</a>
        </div>

        <p style="margin-top: 30px;">Jika Anda memiliki pertanyaan atau mengalami kesulitan saat login, silakan hubungi administrator sistem.</p>

        <div class="footer">
            <p>Email ini dikirim secara otomatis oleh sistem. Mohon tidak membalas email ini.</p>
            <p>&copy; {{ date('Y') }} Sistem Informasi Sekolah. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

