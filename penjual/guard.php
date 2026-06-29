<?php
// Guard: hanya seller yang boleh akses
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['id_pengguna'])) {
    header('Location: ../login.php'); exit;
}
if ($_SESSION['role'] !== 'penjual') {
    header('Location: ../home.php'); exit;
}

require_once '../koneksi.php';

$id_penjual = (int) $_SESSION['id_pengguna'];
$nama_penjual = htmlspecialchars($_SESSION['nama'] ?? 'Penjual');

// Auto-buat tabel jika belum ada — urutan penting: toko dulu, baru produk
mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS `toko` (
  `id_toko`    INT AUTO_INCREMENT PRIMARY KEY,
  `id_penjual` INT NOT NULL UNIQUE,
  `nama_toko`  VARCHAR(100) NOT NULL,
  `deskripsi`  TEXT,
  `foto_toko`  VARCHAR(255) DEFAULT NULL,
  `alamat`     VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Cek apakah tabel produk sudah ada
$checkProduk = mysqli_query($koneksi, "SHOW TABLES LIKE 'produk'");
if (mysqli_num_rows($checkProduk) > 0) {
    // Tabel sudah ada, cek apakah kolom id_toko ada
    $checkColumn = mysqli_query($koneksi, "SHOW COLUMNS FROM `produk` LIKE 'id_toko'");
    if (mysqli_num_rows($checkColumn) == 0) {
        // Kolom id_toko tidak ada, drop dan buat ulang tabel
        mysqli_query($koneksi, "DROP TABLE IF EXISTS `produk`");
        mysqli_query($koneksi, "CREATE TABLE `produk` (
          `id_produk`  INT AUTO_INCREMENT PRIMARY KEY,
          `id_toko`    INT NOT NULL,
          `nama`       VARCHAR(200) NOT NULL,
          `deskripsi`  TEXT,
          `harga`      INT NOT NULL DEFAULT 0,
          `stok`       INT NOT NULL DEFAULT 1,
          `kondisi`    VARCHAR(50) DEFAULT 'Very Good',
          `kategori`   VARCHAR(50) DEFAULT 'Casual',
          `gambar`     VARCHAR(255) DEFAULT NULL,
          `aktif`      TINYINT(1) DEFAULT 1,
          `dilihat`    INT DEFAULT 0,
          `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
} else {
    // Tabel belum ada, buat baru
    mysqli_query($koneksi, "CREATE TABLE `produk` (
      `id_produk`  INT AUTO_INCREMENT PRIMARY KEY,
      `id_toko`    INT NOT NULL,
      `nama`       VARCHAR(200) NOT NULL,
      `deskripsi`  TEXT,
      `harga`      INT NOT NULL DEFAULT 0,
      `stok`       INT NOT NULL DEFAULT 1,
      `kondisi`    VARCHAR(50) DEFAULT 'Very Good',
      `kategori`   VARCHAR(50) DEFAULT 'Casual',
      `gambar`     VARCHAR(255) DEFAULT NULL,
      `aktif`      TINYINT(1) DEFAULT 1,
      `dilihat`    INT DEFAULT 0,
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

// Ambil atau buat toko milik penjual ini
$qToko = mysqli_query($koneksi, "SELECT * FROM toko WHERE id_penjual=$id_penjual LIMIT 1");
$toko  = mysqli_fetch_assoc($qToko);

if (!$toko) {
    // Buat toko otomatis saat pertama kali login
    $namaToko = mysqli_real_escape_string($koneksi, $nama_penjual . "'s Thrift Store");
    mysqli_query($koneksi, "INSERT INTO toko (id_penjual, nama_toko) VALUES ($id_penjual, '$namaToko')");
    $qToko = mysqli_query($koneksi, "SELECT * FROM toko WHERE id_penjual=$id_penjual LIMIT 1");
    $toko  = mysqli_fetch_assoc($qToko);
}

$id_toko = (int) $toko['id_toko'];

function rupiah($n) { return 'Rp ' . number_format($n, 0, ',', '.'); }
