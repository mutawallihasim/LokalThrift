<?php
session_start();
require 'koneksi.php';

// Cek apakah sudah login
if (!isset($_SESSION['id_pengguna'])) {
    header("Location: login.php");
    exit;
}

// Jika role sudah penjual, langsung ke dashboard penjual
if (isset($_SESSION['role']) && $_SESSION['role'] === 'penjual') {
    header("Location: penjual/dashboard.php");
    exit;
}

$error = "";

if (isset($_POST['buat_toko'])) {
    $id_pengguna = $_SESSION['id_pengguna'];
    $nama_toko = mysqli_real_escape_string($koneksi, trim($_POST['nama_toko']));
    $deskripsi = mysqli_real_escape_string($koneksi, trim($_POST['deskripsi']));
    $alamat = mysqli_real_escape_string($koneksi, trim($_POST['alamat']));

    if (empty($nama_toko) || empty($alamat)) {
        $error = "Nama Toko dan Alamat wajib diisi!";
    } else {
        // Buat record toko
        $queryToko = mysqli_query($koneksi, "INSERT INTO toko (id_penjual, nama_toko, deskripsi, alamat) VALUES ('$id_pengguna', '$nama_toko', '$deskripsi', '$alamat')");
        
        if ($queryToko) {
            // Update role pengguna menjadi penjual
            mysqli_query($koneksi, "UPDATE pengguna SET role='penjual' WHERE id_pengguna='$id_pengguna'");
            $_SESSION['role'] = 'penjual';
            
            // Redirect ke dashboard penjual
            header("Location: penjual/dashboard.php");
            exit;
        } else {
            $error = "Gagal membuat toko. Silakan coba lagi.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buka Toko - LokalThrift</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Helvetica Neue', Arial, sans-serif; }
        body { background: #eef5fc; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 20px; }
        .card { background: white; width: 100%; max-width: 400px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); padding: 30px 20px; }
        .icon-top { font-size: 48px; color: #2a85ff; text-align: center; margin-bottom: 16px; }
        .title { font-size: 20px; font-weight: bold; color: #0d1c2e; text-align: center; margin-bottom: 8px; }
        .sub { font-size: 13px; color: #8fa3b8; text-align: center; margin-bottom: 24px; line-height: 1.5; }
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 13px; font-weight: bold; color: #0d1c2e; margin-bottom: 8px; }
        .form-input { width: 100%; padding: 12px 14px; border: 1.5px solid #d4e3f3; border-radius: 10px; font-size: 14px; outline: none; transition: 0.2s; }
        .form-input:focus { border-color: #2a85ff; }
        textarea.form-input { resize: vertical; min-height: 80px; }
        .btn-submit { width: 100%; background: #2a85ff; color: white; padding: 14px; border: none; border-radius: 12px; font-size: 15px; font-weight: bold; cursor: pointer; margin-top: 10px; transition: 0.2s; }
        .btn-submit:hover { background: #1b6be0; }
        .btn-back { display: block; text-align: center; font-size: 14px; color: #8fa3b8; margin-top: 16px; text-decoration: none; }
        .alert { background: #fee2e2; color: #ef4444; padding: 12px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; text-align: center; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-top"><i class="fa-solid fa-store"></i></div>
        <div class="title">Buka Toko Thrift Anda</div>
        <div class="sub">Mulai hasilkan uang dengan menjual barang preloved terbaik Anda hari ini. Gratis dan mudah!</div>

        <?php if($error !== ""): ?>
            <div class="alert"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="cek_toko.php">
            <div class="form-group">
                <label class="form-label">Nama Toko</label>
                <input type="text" name="nama_toko" class="form-input" placeholder="Contoh: Thrift Jaya" required>
            </div>
            <div class="form-group">
                <label class="form-label">Deskripsi Toko (Opsional)</label>
                <textarea name="deskripsi" class="form-input" placeholder="Jelaskan sedikit tentang produk yang Anda jual..."></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Alamat Pengiriman</label>
                <textarea name="alamat" class="form-input" placeholder="Alamat lengkap dari mana pesanan akan dikirim" required></textarea>
            </div>
            <button type="submit" name="buat_toko" class="btn-submit">Buka Toko Sekarang</button>
            <a href="home.php" class="btn-back">Batal dan kembali</a>
        </form>
    </div>
</body>
</html>
