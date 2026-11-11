<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Halaman Tidak Ditemukan | SIAKAD SMKN 9</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/images/logosmkn9.png">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/components.css">
    <style>
        .error-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            text-align: center;
            padding: 2rem 1.5rem;
            background-color: var(--color-bg);
            background-image: radial-gradient(circle at 50% 50%, rgba(37, 99, 235, 0.05) 0%, transparent 60%);
        }
        .error-card {
            background: #FFFFFF;
            padding: 3rem 2.5rem;
            border-radius: var(--radius-xl);
            border: 1px solid var(--color-border);
            max-width: 480px;
            width: 100%;
            box-shadow: 0 20px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04);
        }
        .error-icon-box {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #EFF6FF;
            color: var(--color-primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            border: 1px solid #DBEAFE;
        }
        .error-code {
            font-size: 3rem;
            font-weight: 800;
            color: var(--color-primary);
            line-height: 1;
            margin-bottom: 0.5rem;
            letter-spacing: -0.03em;
        }
    </style>
</head>
<body>
<div class="error-container">
    <div class="error-card">
        <div class="error-icon-box">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
        </div>
        <div class="error-code">404</div>
        <h2 style="font-weight: 800; margin-bottom: 0.65rem; color: var(--color-text-main); font-size: 1.35rem;">Halaman Tidak Ditemukan</h2>
        <p style="color: var(--color-text-muted); font-size: 13.5px; margin-bottom: 2rem; line-height: 1.6;">
            Tautan yang Anda tuju mungkin sudah dipindahkan, dihapus, atau Anda salah mengetikkan alamat URL di peramban.
        </p>
        <a href="<?= BASE_URL ?>/login" class="btn btn-primary btn-block btn-lg">Kembali ke Beranda</a>
    </div>
</div>
</body>
</html>
