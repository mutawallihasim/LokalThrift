<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_pengguna'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require 'koneksi.php';

// Buat tabel jika belum ada
mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS favorit (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pengguna INT NOT NULL,
    id_produk INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$data = json_decode(file_get_contents('php://input'), true);
if (isset($_POST['id_produk'])) {
    $id_produk = (int)$_POST['id_produk'];
} else if (isset($data['id'])) {
    $id_produk = (int)$data['id'];
} else if (isset($data['id_produk'])) {
    $id_produk = (int)$data['id_produk'];
} else {
    echo json_encode(['status' => 'error', 'message' => 'No ID provided']);
    exit;
}

$id_pengguna = (int)$_SESSION['id_pengguna'];

// Cek status saat ini
$q = mysqli_query($koneksi, "SELECT id FROM favorit WHERE id_pengguna=$id_pengguna AND id_produk=$id_produk");
if (mysqli_num_rows($q) > 0) {
    // Hapus
    mysqli_query($koneksi, "DELETE FROM favorit WHERE id_pengguna=$id_pengguna AND id_produk=$id_produk");
    $action = 'removed';
} else {
    // Tambah
    mysqli_query($koneksi, "INSERT INTO favorit (id_pengguna, id_produk) VALUES ($id_pengguna, $id_produk)");
    $action = 'added';
}

$qCount = mysqli_query($koneksi, "SELECT COUNT(*) as cnt FROM favorit WHERE id_pengguna=$id_pengguna");
$row = mysqli_fetch_assoc($qCount);

echo json_encode([
    'status' => 'success',
    'action' => $action,
    'count' => (int)$row['cnt']
]);
