<?php
// Harus paling atas — tangkap semua output sebelum JSON dikirim
ob_start();
ini_set('display_errors', 0);
error_reporting(0);

session_start();
require 'koneksi.php';

// Bersihkan buffer lalu kirim header JSON
ob_end_clean();
header('Content-Type: application/json');

// ── CEK LOGIN ──
if (!isset($_SESSION['id_pengguna'])) {
    echo json_encode(['status' => 'error', 'message' => 'Belum login']);
    exit;
}

$id_pengguna = (int) $_SESSION['id_pengguna'];

// ── BUAT TABEL JIKA BELUM ADA ──
mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS `pesanan` (
    `id_pesanan`   INT AUTO_INCREMENT PRIMARY KEY,
    `id_pengguna`  INT NOT NULL,
    `invoice`      VARCHAR(30) NOT NULL UNIQUE,
    `status`       ENUM('diproses','dikirim','selesai','dibatalkan') NOT NULL DEFAULT 'diproses',
    `total`        INT NOT NULL DEFAULT 0,
    `metode_bayar` VARCHAR(50) DEFAULT NULL,
    `kurir`        VARCHAR(100) DEFAULT NULL,
    `alamat`       TEXT DEFAULT NULL,
    `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS `pesanan_item` (
    `id_item`    INT AUTO_INCREMENT PRIMARY KEY,
    `id_pesanan` INT NOT NULL,
    `id_produk`  INT DEFAULT 0,
    `id_toko`    INT DEFAULT 0,
    `nama`       VARCHAR(200) NOT NULL,
    `varian`     VARCHAR(100) DEFAULT NULL,
    `harga`      INT NOT NULL DEFAULT 0,
    `gambar`     TEXT DEFAULT NULL,
    FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan`(`id_pesanan`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Tambahkan kolom jika tabel sudah telanjur dibuat versi lama
$checkCol = mysqli_query($koneksi, "SHOW COLUMNS FROM pesanan_item LIKE 'id_produk'");
if ($checkCol && mysqli_num_rows($checkCol) == 0) {
    mysqli_query($koneksi, "ALTER TABLE pesanan_item ADD COLUMN id_produk INT DEFAULT 0 AFTER id_pesanan");
    mysqli_query($koneksi, "ALTER TABLE pesanan_item ADD COLUMN id_toko INT DEFAULT 0 AFTER id_produk");
}

// ── BACA REQUEST BODY ──
$raw  = file_get_contents('php://input');
$body = json_decode($raw, true);

if (!$body) {
    echo json_encode(['status' => 'error', 'message' => 'Request tidak valid']);
    exit;
}

$items        = $body['items']        ?? [];
$ongkir       = (int)($body['ongkir'] ?? 0);
$metode_bayar = mysqli_real_escape_string($koneksi, $body['metode_bayar'] ?? '');
$kurir        = mysqli_real_escape_string($koneksi, $body['kurir']        ?? '');
$alamat       = mysqli_real_escape_string($koneksi, $body['alamat']       ?? '');

if (empty($items)) {
    echo json_encode(['status' => 'error', 'message' => 'Tidak ada item']);
    exit;
}

// ── HITUNG TOTAL ──
$subtotal = 0;
foreach ($items as $item) {
    $subtotal += (int)($item['harga'] ?? 0);
}
$total = $subtotal + $ongkir;

// ── GENERATE INVOICE ──
$invoice = 'LT' . date('ymd') . strtoupper(substr(uniqid(), -5));

// ── SIMPAN PESANAN ──
$q = mysqli_query($koneksi,
    "INSERT INTO pesanan (id_pengguna, invoice, status, total, metode_bayar, kurir, alamat)
     VALUES ($id_pengguna, '$invoice', 'diproses', $total, '$metode_bayar', '$kurir', '$alamat')"
);

if (!$q) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Gagal simpan pesanan: ' . mysqli_error($koneksi)
    ]);
    exit;
}

$id_pesanan = (int) mysqli_insert_id($koneksi);

// ── SIMPAN ITEM ──
foreach ($items as $item) {
    $id_produk = (int)($item['id'] ?? 0);
    $nama   = mysqli_real_escape_string($koneksi, $item['nama']   ?? '');
    $varian = mysqli_real_escape_string($koneksi, $item['varian'] ?? '');
    $harga  = (int)($item['harga'] ?? 0);
    $gambar = mysqli_real_escape_string($koneksi, $item['gambar'] ?? '');
    
    // Cari id_toko
    $id_toko = 0;
    if ($id_produk > 0) {
        $qT = mysqli_query($koneksi, "SELECT id_toko FROM produk WHERE id_produk=$id_produk LIMIT 1");
        if ($qT && $rT = mysqli_fetch_assoc($qT)) {
            $id_toko = (int)$rT['id_toko'];
        }
    }

    mysqli_query($koneksi,
        "INSERT INTO pesanan_item (id_pesanan, id_produk, id_toko, nama, varian, harga, gambar)
         VALUES ($id_pesanan, $id_produk, $id_toko, '$nama', '$varian', $harga, '$gambar')"
    );
    
    // Hapus dari keranjang jika berasal dari keranjang
    if (!empty($item['cart_id'])) {
        $cart_id = (int)$item['cart_id'];
        mysqli_query($koneksi, "DELETE FROM keranjang WHERE id_keranjang = $cart_id AND id_pengguna = $id_pengguna");
    }
}

// ── KIRIM NOTIFIKASI KE PEMBELI ──
$judulNotif = "Pesanan Berhasil Dibuat";
$pesanNotif = "Hore! Pesananmu dengan invoice $invoice berhasil dibuat dan sedang menunggu diproses.";
mysqli_query($koneksi, "INSERT INTO notifikasi (id_pengguna, judul, pesan) VALUES ($id_pengguna, '$judulNotif', '$pesanNotif')");

echo json_encode([
    'status'  => 'success',
    'invoice' => $invoice,
    'total'   => $total
]);
