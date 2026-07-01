<?php
session_start();
require 'koneksi.php';

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['id_pengguna'])) {
    header("location: dashboard.php");
    exit;
}

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $no_hp    = trim($_POST['no_hp'] ?? '');
    $alamat   = trim($_POST['alamat'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $agree    = $_POST['agree'] ?? '';

    // Validasi Input
    if (!$nama || !$email || !$no_hp || !$alamat || !$password || !$confirm) {
        $error = 'Semua field harus diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($password) < 8) {
        $error = 'Password minimal 8 karakter.';
    } elseif ($password !== $confirm) {
        $error = 'Konfirmasi password tidak cocok.';
    } elseif (!$agree) {
        $error = 'Anda harus menyetujui syarat & ketentuan.';
    } else {
        // Cek apakah email sudah terdaftar menggunakan Prepared Statement (Aman dari SQL Injection)
        $stmt_cek = mysqli_prepare($koneksi, "SELECT id_pengguna FROM pengguna WHERE email = ?");
        mysqli_stmt_bind_param($stmt_cek, "s", $email);
        mysqli_stmt_execute($stmt_cek);
        mysqli_stmt_store_result($stmt_cek);

        if (mysqli_stmt_num_rows($stmt_cek) > 0) {
            $error = 'Email sudah terdaftar.';
            mysqli_stmt_close($stmt_cek);
        } else {
            mysqli_stmt_close($stmt_cek);

            // Hash password dan simpan data pengguna baru
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO pengguna (nama, email, no_hp, alamat, role, password) VALUES (?, ?, ?, ?, 'pembeli', ?)";
            
            $stmt_insert = mysqli_prepare($koneksi, $sql);
            mysqli_stmt_bind_param($stmt_insert, "sssss", $nama, $email, $no_hp, $alamat, $hashed);

            if (mysqli_stmt_execute($stmt_insert)) {
                mysqli_stmt_close($stmt_insert);

                // Langsung login-kan pengguna, tidak perlu login manual lagi
                $id_pengguna_baru = mysqli_insert_id($koneksi);
                $_SESSION['id_pengguna'] = $id_pengguna_baru;
                $_SESSION['nama']        = $nama;
                $_SESSION['role']        = 'pembeli';

                $_SESSION['success'] = "Registrasi berhasil. Selamat datang, $nama!";
                header("Location: home.php");
                exit;
            } else {
                $error = 'Gagal mendaftar: ' . mysqli_error($koneksi);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LokalThrift – Daftar</title>
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* === UMUM === */
        *, *::before, *::after { 
            box-sizing: border-box; 
            margin: 0; 
            padding: 0; 
        }
        
        :root {
            --maya-blue: #8FC9FF; 
            --light-sky: #A2D2FF; 
            --uranian: #BDE0FE; 
            --columbia: #D1E8FC;
            --alice: #E5F0FA; 
            --white: #FFFFFF; 
            --primary: #2563EB; 
            --primary-hover: #1D4ED8;
            --text-dark: #1E293B; 
            --text-mid: #475569; 
            --text-light: #94A3B8; 
            --border: #CBD5E1;
            --error: #EF4444; 
            --success: #10B981; 
            --radius: 14px;
            --card-shadow: 0 20px 60px rgba(37,99,235,0.12), 0 4px 16px rgba(37,99,235,0.08);
        }

        html, body { 
            min-height: 100%; 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: var(--alice); 
            overflow-x: hidden; 
        }

        body::before, body::after { 
            content: ''; 
            position: fixed; 
            border-radius: 50%; 
            filter: blur(80px); 
            pointer-events: none; 
            z-index: 0; 
        }
        body::before { 
            width: 500px; 
            height: 500px; 
            background: radial-gradient(circle, #A2D2FF88, #8FC9FF44); 
            top: -150px; 
            left: -150px; 
            animation: blob1 12s ease-in-out infinite alternate; 
        }
        body::after { 
            width: 400px; 
            height: 400px; 
            background: radial-gradient(circle, #BDE0FE66, #D1E8FC33); 
            bottom: -120px; 
            right: -120px; 
            animation: blob2 14s ease-in-out infinite alternate; 
        }

        @keyframes blob1 { to { transform: translate(60px, 80px) scale(1.1); } }
        @keyframes blob2 { to { transform: translate(-40px, -60px) scale(1.08); } }

        .page { 
            position: relative; 
            z-index: 1; 
            min-height: 100vh; 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            justify-content: center; 
            padding: 40px 16px; 
        }
        .card { 
            background: rgba(255, 255, 255, 0.88); 
            backdrop-filter: blur(20px); 
            -webkit-backdrop-filter: blur(20px); 
            border: 1px solid rgba(255, 255, 255, 0.7); 
            border-radius: 28px; 
            box-shadow: var(--card-shadow); 
            padding: 40px 36px 36px; 
            width: 100%; 
            max-width: 420px; 
        }
        .logo-area { 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            margin-bottom: 24px; 
            gap: 12px; 
        }
        .card-title { 
            font-size: 20px; 
            font-weight: 700; 
            color: var(--text-dark); 
            text-align: center; 
            margin-bottom: 6px; 
        }
        .card-sub { 
            font-size: 13.5px; 
            color: var(--text-mid); 
            text-align: center; 
            margin-bottom: 24px; 
            line-height: 1.5; 
        }

        .alert { 
            padding: 10px 14px; 
            border-radius: 10px; 
            font-size: 13px; 
            font-weight: 500; 
            margin-bottom: 18px; 
            animation: fadeIn .3s ease; 
        }
        .alert-error { background: #FEF2F2; color: var(--error); border: 1px solid #FECACA; }
        .alert-success { background: #ECFDF5; color: var(--success); border: 1px solid #A7F3D0; }

        @keyframes fadeIn { 
            from { opacity: 0; transform: translateY(-6px); } 
            to { opacity: 1; } 
        }

        .field { margin-bottom: 16px; }
        .field label { 
            display: block; 
            font-size: 13px; 
            font-weight: 600; 
            color: var(--text-dark); 
            margin-bottom: 6px; 
        }

        .input-wrap { 
            position: relative; 
            display: flex; 
            align-items: center; 
        }
        .input-wrap .icon { 
            position: absolute; 
            left: 14px; 
            width: 18px; 
            height: 18px; 
            color: var(--text-light); 
            pointer-events: none; 
            transition: color .2s; 
        }
        .input-wrap:focus-within .icon { color: var(--primary); }
        
        .input-wrap input { 
            width: 100%; 
            height: 48px; 
            padding: 0 42px; 
            border: 1.5px solid var(--border); 
            border-radius: var(--radius); 
            font-family: inherit; 
            font-size: 14px; 
            color: var(--text-dark); 
            background: var(--white); 
            outline: none; 
            transition: border-color .2s, box-shadow .2s, background .2s; 
        }
        .input-wrap input::placeholder { color: var(--text-light); }
        .input-wrap input:focus { 
            border-color: var(--primary); 
            background: #F0F6FF; 
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); 
        }

        .toggle-pw { 
            position: absolute; 
            right: 14px; 
            background: none; 
            border: none; 
            cursor: pointer; 
            color: var(--text-light); 
            padding: 0; 
            display: flex; 
            align-items: center; 
            transition: color .2s; 
        }
        .toggle-pw:hover { color: var(--primary); }

        .btn-primary { 
            width: 100%; 
            height: 50px; 
            background: var(--primary); 
            color: #fff; 
            border: none; 
            border-radius: var(--radius); 
            font-family: inherit; 
            font-size: 15px; 
            font-weight: 700; 
            cursor: pointer; 
            position: relative; 
            overflow: hidden; 
            transition: background .2s, transform .15s, box-shadow .2s; 
            box-shadow: 0 4px 16px rgba(37,99,235,0.3); 
        }
        .btn-primary::after { 
            content: ''; 
            position: absolute; 
            inset: 0; 
            background: linear-gradient(135deg, rgba(255,255,255,0.15), transparent); 
        }
        .btn-primary:hover { 
            background: var(--primary-hover); 
            transform: translateY(-1px); 
            box-shadow: 0 8px 24px rgba(37,99,235,0.35); 
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 18px 0;
            color: var(--text-light);
            font-size: 12px;
            font-weight: 500;
        }
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .google-btn-wrap {
            width: 100%;
            display: flex;
            justify-content: center;
            margin-bottom: 4px;
        }

        .switch-link { 
            text-align: center; 
            margin-top: 18px; 
            font-size: 13.5px; 
            color: var(--text-mid); 
        }
        .switch-link a { 
            color: var(--primary); 
            font-weight: 700; 
            text-decoration: none; 
            position: relative; 
        }
        .switch-link a::after { 
            content: ''; 
            position: absolute; 
            bottom: -1px; 
            left: 0; 
            right: 0; 
            height: 2px; 
            background: var(--primary); 
            transform: scaleX(0); 
            transition: transform .2s; 
        }
        .switch-link a:hover::after { transform: scaleX(1); }

        .bottom-badge { 
            margin-top: 28px; 
            display: flex; 
            align-items: center; 
            gap: 8px; 
            font-size: 12px; 
            color: var(--text-light); 
            font-weight: 500; 
        }
        .bottom-badge svg { width: 16px; height: 16px; color: var(--maya-blue); }
        .dot { width: 3px; height: 3px; border-radius: 50%; background: var(--text-light); }

        .page-leave-left { animation: leaveLeft .42s cubic-bezier(.4,0,1,1) forwards; }
        .page-leave-right { animation: leaveRight .42s cubic-bezier(.4,0,1,1) forwards; }
        
        @keyframes leaveLeft { to { opacity: 0; transform: translateX(-60px) scale(.97); } }
        @keyframes leaveRight { to { opacity: 0; transform: translateX( 60px) scale(.97); } }
        
        .card-slide-up { animation: slideUp .6s cubic-bezier(.22,1,.36,1) both; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(40px) scale(.97); } }
        
        @media (max-width: 480px) { .card { padding: 32px 22px 28px; } }

        /* === KHUSUS REGISTER === */
        .check-row { 
            display: flex; 
            align-items: center; 
            gap: 10px; 
            margin-bottom: 20px; 
        }
        .check-row input[type="checkbox"] { 
            width: 18px; 
            height: 18px; 
            accent-color: var(--primary); 
            cursor: pointer; 
            flex-shrink: 0; 
        }
        .check-row label { font-size: 13px; color: var(--text-mid); cursor: pointer; }
        .check-row a { color: var(--primary); font-weight: 600; text-decoration: none; }
        .check-row a:hover { text-decoration: underline; }

        textarea.form-textarea { 
            width: 100%; 
            padding: 12px 14px; 
            border: 1.5px solid var(--border); 
            border-radius: var(--radius); 
            font-family: inherit; 
            font-size: 14px; 
            color: var(--text-dark); 
            background: var(--white); 
            outline: none; 
            resize: vertical; 
            min-height: 80px; 
            transition: border-color .2s, box-shadow .2s; 
        }
        textarea.form-textarea:focus { 
            border-color: var(--primary); 
            background: #F0F6FF; 
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); 
        }

        .strength-bar { display: flex; gap: 4px; margin-top: 8px; }
        .strength-bar span { 
            flex: 1; 
            height: 4px; 
            border-radius: 99px; 
            background: var(--border); 
            transition: background .3s; 
        }
        .strength-label { 
            font-size: 11.5px; 
            font-weight: 600; 
            margin-top: 5px; 
            min-height: 16px; 
            transition: color .3s; 
        }

        .s-weak .strength-bar span:nth-child(1) { background: #EF4444; }
        .s-medium .strength-bar span:nth-child(1), .s-medium .strength-bar span:nth-child(2) { background: #F59E0B; }
        .s-strong .strength-bar span:nth-child(1), .s-strong .strength-bar span:nth-child(2), .s-strong .strength-bar span:nth-child(3) { background: #10B981; }
        .s-vstrong .strength-bar span { background: #059669; }

        .s-weak .strength-label { color: #EF4444; }
        .s-medium .strength-label { color: #F59E0B; }
        .s-strong .strength-label { color: #10B981; }
        .s-vstrong .strength-label { color: #059669; }

        .pw-suggest-wrap { position: relative; }
        .pw-suggest { 
            display: none; 
            position: absolute; 
            left: 0; 
            right: 0; 
            top: calc(100% + 8px); 
            background: #fff; 
            border: 1.5px solid var(--uranian); 
            border-radius: 14px; 
            box-shadow: 0 8px 30px rgba(37,99,235,0.13); 
            padding: 14px 16px; 
            z-index: 100; 
            animation: fadeIn .22s ease; 
        }
        .pw-suggest.open { display: block; }
        .pw-suggest::before { 
            content: ''; 
            position: absolute; 
            top: -8px; 
            left: 18px; 
            width: 14px; 
            height: 14px; 
            background: #fff; 
            border-left: 1.5px solid var(--uranian); 
            border-top: 1.5px solid var(--uranian); 
            transform: rotate(45deg); 
            border-radius: 2px; 
        }
        .pw-suggest-title { 
            font-size: 11.5px; 
            font-weight: 700; 
            color: var(--primary); 
            letter-spacing: .04em; 
            text-transform: uppercase; 
            margin-bottom: 10px; 
        }
        
        .pw-rules { 
            list-style: none; 
            display: flex; 
            flex-direction: column; 
            gap: 7px; 
        }
        .pw-rules li { 
            display: flex; 
            align-items: center; 
            gap: 9px; 
            font-size: 12.5px; 
            color: var(--text-mid); 
            transition: color .2s; 
        }
        .pw-rules li .rule-icon { 
            width: 18px; 
            height: 18px; 
            border-radius: 50%; 
            border: 1.5px solid var(--border); 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            flex-shrink: 0; 
            transition: background .2s, border-color .2s; 
        }
        .pw-rules li .rule-icon svg { width: 10px; height: 10px; stroke: #fff; display: none; }
        .pw-rules li.ok { color: var(--success); }
        .pw-rules li.ok .rule-icon { background: var(--success); border-color: var(--success); }
        .pw-rules li.ok .rule-icon svg { display: block; }

        .pw-suggest-tips { 
            margin-top: 12px; 
            padding-top: 10px; 
            border-top: 1px solid var(--columbia); 
            font-size: 11.5px; 
            color: var(--text-light); 
            line-height: 1.6; 
        }
        .pw-suggest-tips strong { color: var(--text-mid); font-weight: 600; }
    </style>
</head>
<body>

<div class="page" id="page">
    <div class="card card-slide-up">

        <div class="logo-area" style="gap: 6px; margin-bottom: 16px;">
            <img src="logo.svg" alt="LokalThrift" style="width: 260px; height: 88px; object-fit: contain;">
            <h1 class="card-title" style="margin-top: 2px;">Buat Akun Baru</h1>
            <p class="card-sub" style="margin-bottom: 0;">Gabung dan temukan thrift item terbaik<br>di sekitarmu.</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="field">
                <label>Nama Lengkap</label>
                <div class="input-wrap">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                    <input type="text" name="nama" placeholder="masukkan nama lengkap" value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>">
                </div>
            </div>

            <div class="field">
                <label>Email</label>
                <div class="input-wrap">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="3"/><path d="m2 7 10 7 10-7"/></svg>
                    <input type="email" name="email" placeholder="contoh@gmail.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
            </div>

            <div class="field">
                <label>No. HP</label>
                <div class="input-wrap">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2A19.79 19.79 0 0 1 2.08 4.18 2 2 0 0 1 4.08 2h3a2 2 0 0 1 2 1.72c.13 1 .37 1.97.71 2.9a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.18-1.18a2 2 0 0 1 2.11-.45c.93.34 1.9.58 2.9.71A2 2 0 0 1 22 16.92z"/></svg>
                    <input type="text" name="no_hp" placeholder="08xxxxxxxxxx" value="<?= htmlspecialchars($_POST['no_hp'] ?? '') ?>">
                </div>
            </div>

            <div class="field">
                <label>Alamat</label>
                <textarea class="form-textarea" name="alamat" placeholder="masukkan alamat lengkap"><?= htmlspecialchars($_POST['alamat'] ?? '') ?></textarea>
            </div>

            <div class="field" id="pwField">
                <label>Password</label>
                <div class="pw-suggest-wrap">
                    <div class="input-wrap">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <input type="password" id="pw1" name="password" placeholder="minimal 8 karakter" oninput="checkStrength(this.value)" onfocus="openSuggest()" onblur="closeSuggest(event)">
                        <button type="button" class="toggle-pw" onclick="togglePw('pw1','eye1')">
                            <svg id="eye1" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                    <div class="pw-suggest" id="pwSuggest">
                        <div class="pw-suggest-title">💡 Saran Buat Password Kuat</div>
                        <ul class="pw-rules" id="pwRules">
                            <li id="r-len"><span class="rule-icon"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="1,6 4,9 11,2"/></svg></span>Minimal 8 karakter</li>
                            <li id="r-upper"><span class="rule-icon"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="1,6 4,9 11,2"/></svg></span>Huruf kapital (A–Z)</li>
                            <li id="r-lower"><span class="rule-icon"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="1,6 4,9 11,2"/></svg></span>Huruf kecil (a–z)</li>
                            <li id="r-num"><span class="rule-icon"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="1,6 4,9 11,2"/></svg></span>Angka (0–9)</li>
                            <li id="r-sym"><span class="rule-icon"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="1,6 4,9 11,2"/></svg></span>Simbol (!@#$%^&*)</li>
                        </ul>
                        <div class="pw-suggest-tips"><strong>Tips:</strong> Hindari nama atau tanggal lahir. Gunakan frasa acak seperti <strong>Kopi#Biru7!</strong></div>
                    </div>
                </div>
                <div class="strength-bar"><span></span><span></span><span></span><span></span></div>
                <div class="strength-label" id="strengthLabel"></div>
            </div>

            <div class="field">
                <label>Konfirmasi Password</label>
                <div class="input-wrap">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <input type="password" id="pw2" name="confirm_password" placeholder="ulangi password">
                    <button type="button" class="toggle-pw" onclick="togglePw('pw2','eye2')">
                        <svg id="eye2" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
            </div>

            <div class="check-row">
                <input type="checkbox" id="agree" name="agree" value="1" <?= !empty($_POST['agree']) ? 'checked' : '' ?>>
                <label for="agree">Saya setuju dengan <a href="#">syarat &amp; ketentuan</a></label>
            </div>

            <button type="submit" class="btn-primary">Daftar</button>
        </form>

        <div class="divider">atau daftar dengan</div>

        <div id="g_id_onload"
             data-client_id="931774318654-l2msu750tkhh37hq7megcc99jqgeqqh9.apps.googleusercontent.com"
             data-context="signup"
             data-ux_mode="popup"
             data-callback="handleCredentialResponse"
             data-auto_prompt="false">
        </div>

        <div class="google-btn-wrap">
            <div id="googleBtn"></div>
        </div>

        <div class="switch-link">
            Sudah punya akun? <a href="login.php" id="toLogin">Login di sini</a>
        </div>
    </div>

    <div class="bottom-badge">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        Aman <span class="dot"></span> Cepat <span class="dot"></span> Terpercaya
    </div>
</div>

<script>
function openSuggest() { 
    document.getElementById('pwSuggest').classList.add('open'); 
}

function closeSuggest(e) { 
    setTimeout(() => { 
        const pop = document.getElementById('pwSuggest'); 
        if (!pop.contains(document.activeElement)) pop.classList.remove('open'); 
    }, 150); 
}

function setRule(id, ok) { 
    const el = document.getElementById(id); 
    if (ok) el.classList.add('ok'); else el.classList.remove('ok'); 
}

function togglePw(id, eyeId) {
    const i = document.getElementById(id), e = document.getElementById(eyeId);
    i.type = i.type === 'password' ? 'text' : 'password';
    e.innerHTML = i.type === 'text'
        ? '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>'
        : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
}

function checkStrength(val) {
    const field = document.getElementById('pwField');
    const label = document.getElementById('strengthLabel');
    
    setRule('r-len',   val.length >= 8);
    setRule('r-upper', /[A-Z]/.test(val));
    setRule('r-lower', /[a-z]/.test(val));
    setRule('r-num',   /[0-9]/.test(val));
    setRule('r-sym',   /[^A-Za-z0-9]/.test(val));
    
    field.classList.remove('s-weak', 's-medium', 's-strong', 's-vstrong');
    if (!val) { label.textContent = ''; return; }
    
    let score = 0;
    if (val.length >= 8)  score++;
    if (val.length >= 12) score++;
    if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    
    if (score <= 1) { 
        field.classList.add('s-weak'); 
        label.textContent = '🔴 Lemah'; 
    } else if (score === 2) { 
        field.classList.add('s-medium'); 
        label.textContent = '🟡 Sedang'; 
    } else if (score <= 4)  { 
        field.classList.add('s-strong'); 
        label.textContent = '🟢 Kuat'; 
    } else { 
        field.classList.add('s-vstrong'); 
        label.textContent = '✅ Sangat Kuat'; 
    }
}

document.getElementById('toLogin').addEventListener('click', function(e) {
    e.preventDefault();
    const href = this.href;
    document.getElementById('page').classList.add('page-leave-left');
    setTimeout(() => location.href = href, 400);
});

window.addEventListener('load', function() {
    if (window.google && google.accounts && google.accounts.id) {
        const wrap = document.querySelector('.google-btn-wrap');
        google.accounts.id.renderButton(
            document.getElementById('googleBtn'),
            {
                theme: 'outline',
                size: 'large',
                shape: 'rectangular',
                text: 'signup_with',
                logo_alignment: 'left',
                width: wrap.offsetWidth
            }
        );
    }
});

function handleCredentialResponse(response) {
    fetch('api_google_login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ credential: response.credential })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            window.location.href = data.redirect;
        } else {
            alert('Gagal Daftar: ' + data.message);
        }
    })
    .catch(err => {
        console.error(err);
        alert('Terjadi kesalahan jaringan.');
    });
}
</script>
</body>
</html>