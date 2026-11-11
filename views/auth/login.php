<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($judulHalaman ?? 'Masuk - SIAKAD SMKN 9 Kabupaten Tangerang') ?></title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/images/logosmkn9.png">
    
    <!-- CSS Layers -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/components.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/responsive.css">

    <style>
        .login-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2.5rem 1.25rem;
            position: relative;
            background-color: #F8FAFC;
            background-image: 
                radial-gradient(at 0% 0%, rgba(17, 46, 129, 0.05) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(67, 130, 223, 0.08) 0px, transparent 50%),
                radial-gradient(at 50% 100%, rgba(70, 71, 174, 0.06) 0px, transparent 50%);
        }

        .login-card-container {
            width: 100%;
            max-width: 480px;
            margin: 0 auto;
        }

        .login-card {
            background-color: #FFFFFF;
            border-radius: var(--radius-xl);
            border: 1px solid var(--color-border);
            box-shadow: 0 20px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04);
            overflow: hidden;
            position: relative;
        }

        .login-card::before {
            content: '';
            display: block;
            height: 5px;
            background: linear-gradient(90deg, var(--color-primary) 0%, var(--color-secondary) 50%, var(--color-accent) 100%);
        }

        .login-body {
            padding: 2.5rem 2.25rem 2.25rem;
        }

        .login-brand-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-logo-container {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 72px;
            height: 72px;
            border-radius: var(--radius-lg);
            background: linear-gradient(135deg, rgba(239, 246, 255, 0.8) 0%, rgba(219, 234, 254, 0.8) 100%);
            border: 1px solid #DBEAFE;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.1);
            margin-bottom: 1rem;
            padding: 8px;
        }

        .login-logo-img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .login-brand-title {
            font-size: 1.65rem;
            font-weight: 800;
            color: var(--color-primary);
            letter-spacing: -0.025em;
            line-height: 1.15;
        }

        .login-brand-sub {
            font-size: 11px;
            font-weight: 700;
            color: var(--color-secondary);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-top: 0.25rem;
        }

        .login-brand-desc {
            font-size: 13px;
            color: var(--color-text-muted);
            margin-top: 0.4rem;
        }

        .login-section-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--color-border);
        }

        .login-section-title h2 {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--color-text-main);
        }

        .input-icon-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--color-text-subtle);
            display: flex;
            align-items: center;
            pointer-events: none;
        }

        .input-with-icon {
            padding-left: 2.75rem;
        }

        /* Password Eye Toggle Icon Only */
        .toggle-password-icon-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 6px;
            color: var(--color-text-subtle);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-sm);
            transition: color var(--transition-fast);
        }

        .toggle-password-icon-btn:hover {
            color: var(--color-primary);
        }

        /* Demo Accounts Box */
        .demo-accounts-box {
            margin-top: 1.75rem;
            padding: 1.15rem 1.25rem;
            border-radius: var(--radius-md);
            background-color: #F8FAFC;
            border: 1px solid #E2E8F0;
        }

        .demo-box-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.75rem;
        }

        .demo-box-title {
            font-size: 11.5px;
            font-weight: 700;
            color: var(--color-primary);
            display: flex;
            align-items: center;
            gap: 0.35rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .demo-chips-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .demo-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 5px 10px;
            background: #FFFFFF;
            border: 1px solid #CBD5E1;
            border-radius: var(--radius-full);
            font-size: 11.5px;
            font-weight: 600;
            color: var(--color-text-body);
            cursor: pointer;
            transition: all var(--transition-fast);
            box-shadow: var(--shadow-xs);
            user-select: none;
        }

        .demo-chip:hover {
            border-color: var(--color-accent);
            color: var(--color-primary);
            background: #EFF6FF;
            transform: translateY(-1px);
        }

        .demo-chip.selected {
            background: var(--color-primary);
            color: #FFFFFF;
            border-color: var(--color-primary);
        }

        .login-footer-text {
            text-align: center;
            margin-top: 1.75rem;
            font-size: 12px;
            color: var(--color-text-muted);
        }

        .login-security-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 11px;
            color: var(--color-text-muted);
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="login-card-container">
        <div class="login-card">
            <div class="login-body">
                <!-- Identitas Brand SMKN 9 Kabupaten Tangerang -->
                <div class="login-brand-header">
                    <div class="login-logo-container">
                        <img src="<?= BASE_URL ?>/assets/images/logosmkn9.png" alt="Logo SMKN 9 Kabupaten Tangerang" class="login-logo-img">
                    </div>
                    <h1 class="login-brand-title">SIAKAD</h1>
                    <div class="login-brand-sub">SMK NEGERI 9 KABUPATEN TANGERANG</div>
                    <p class="login-brand-desc">Sistem Informasi Akademik & Layanan Vokasi Terpadu</p>
                </div>

                <div class="login-section-title">
                    <h2>Masuk ke Akun Anda</h2>
                </div>

                <?php require PATH_VIEWS . '/components/flash.php'; ?>

                <form action="<?= BASE_URL ?>/login" method="POST" autocomplete="off" id="formLogin">
                    <?= \App\Helpers\Session::csrfField() ?>

                    <div class="form-group">
                        <label class="form-label" for="nomor_induk">NIS atau NUPTK</label>
                        <div class="input-icon-wrapper">
                            <span class="input-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                            </span>
                            <input type="text" id="nomor_induk" name="nomor_induk" class="form-control input-with-icon" placeholder="Masukkan NIS atau NUPTK" required autofocus>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Kata Sandi</label>
                        <div class="input-icon-wrapper">
                            <span class="input-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                            </span>
                            <input type="password" id="password" name="password" class="form-control input-with-icon" placeholder="Masukkan kata sandi" required style="padding-right: 3rem;">
                            <button type="button" class="toggle-password-icon-btn" id="btnTogglePassword" aria-label="Toggle kata sandi">
                                <svg id="eyeIconOpen" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg id="eyeIconClosed" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: none;">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div style="margin-top: 1.75rem;">
                        <button type="submit" class="btn btn-primary btn-block btn-lg" id="btnSubmit">
                            <span>Masuk</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </button>
                    </div>
                </form>

                <!-- Panel Akun Uji Coba Cepat -->
                <div class="demo-accounts-box">
                    <div class="demo-box-header">
                        <span class="demo-box-title">
                            ⚡ Klik Akun Uji Coba:
                        </span>
                    </div>
                    <div class="demo-chips-grid">
                        <button type="button" class="demo-chip" onclick="setDemo('197508142000031001', 'gurusmkn9#031001', this)" title="Kesiswaan (NUPTK: 197508142000031001)">
                            <span>🛡️</span>
                            <span>Kesiswaan</span>
                        </button>
                        <button type="button" class="demo-chip" onclick="setDemo('198203202008012003', 'gurusmkn9#012003', this)" title="Kaprog DKV (NUPTK: 198203202008012003)">
                            <span>🎨</span>
                            <span>Kaprog DKV</span>
                        </button>
                        <button type="button" class="demo-chip" onclick="setDemo('199011152019031009', 'gurusmkn9#031009', this)" title="Wali Kelas 10 TKJ (NUPTK: 199011152019031009)">
                            <span>👨‍🏫</span>
                            <span>Wali Kelas</span>
                        </button>
                        <button type="button" class="demo-chip" onclick="setDemo('2024101001', 'smkn9#1001', this)" title="Siswa 10 TKJ (NIS: 2024101001)">
                            <span>🎓</span>
                            <span>Siswa</span>
                        </button>
                        <button type="button" class="demo-chip" onclick="setDemo('2024101012', 'smkn9#1012', this)" title="Ketua OSIS (NIS: 2024101012)">
                            <span>📢</span>
                            <span>OSIS</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Bawah Card -->
        <div class="login-footer-text">
            <div>&copy; <?= date('Y') ?> SMK Negeri 9 Kabupaten Tangerang &bull; Pusat Keunggulan Vokasi</div>
            <div class="login-security-badge">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                </svg>
                <span>Dilindungi Enkripsi Sesi & Verifikasi Berjenjang</span>
            </div>
        </div>
    </div>
</div>

<script>
    // Eye icon toggle password
    const btnToggle = document.getElementById('btnTogglePassword');
    const inputPass = document.getElementById('password');
    const iconOpen = document.getElementById('eyeIconOpen');
    const iconClosed = document.getElementById('eyeIconClosed');

    if (btnToggle && inputPass) {
        btnToggle.addEventListener('click', () => {
            if (inputPass.type === 'password') {
                inputPass.type = 'text';
                iconOpen.style.display = 'none';
                iconClosed.style.display = 'block';
            } else {
                inputPass.type = 'password';
                iconOpen.style.display = 'block';
                iconClosed.style.display = 'none';
            }
        });
    }

    function setDemo(nomorInduk, password, element) {
        document.getElementById('nomor_induk').value = nomorInduk;
        document.getElementById('password').value = password;
        
        document.querySelectorAll('.demo-chip').forEach(c => c.classList.remove('selected'));
        if (element) {
            element.classList.add('selected');
        }

        const btnSubmit = document.getElementById('btnSubmit');
        if (btnSubmit) {
            btnSubmit.focus();
        }
    }
</script>
</body>
</html>
