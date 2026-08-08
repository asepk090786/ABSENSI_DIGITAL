<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fitur Dinonaktifkan</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f7fb;
            color: #243b53;
        }
        .wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
            padding: 32px 28px;
            text-align: center;
            max-width: 520px;
            width: 100%;
        }
        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 999px;
            background: #fef3c7;
            color: #92400e;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 16px;
        }
        h1 {
            font-size: 24px;
            margin: 0 0 12px;
        }
        p {
            margin: 0 0 20px;
            line-height: 1.6;
            color: #475569;
        }
        a {
            display: inline-block;
            text-decoration: none;
            background: #2563eb;
            color: #fff;
            padding: 10px 16px;
            border-radius: 8px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <div class="badge">Fitur Tidak Aktif</div>
            <h1>Halaman Rencana Pembelajaran sedang dinonaktifkan</h1>
            <p>{{ $message ?? 'Modul ini tidak tersedia lagi di tampilan web saat ini.' }}</p>
            <a href="{{ $backUrl ?? url('/') }}">Kembali ke beranda</a>
        </div>
    </div>
</body>
</html>
