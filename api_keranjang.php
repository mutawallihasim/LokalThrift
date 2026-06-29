<?php
session_start();
require 'koneksi.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_pengguna'])) {
    echo json_encode(['status' => 'error', 'message' => 'Belum login']);
    exit;
}

$id_pengguna = (int) $_SESSION['id_pengguna'];
$action = $_GET['action'] ?? '';

if ($action === 'add') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id_produk = (int) ($data['id_produk'] ?? 0);
    $varian = mysqli_real_escape_string($koneksi, $data['varian'] ?? '');
    
    // Cek produk
    $q = mysqli_query($koneksi, "SELECT harga FROM produk WHERE id_produk = $id_produk");
    if ($row = mysqli_fetch_assoc($q)) {
        $harga = $row['harga'];
        // Cek apakah item dengan varian yang sama sudah ada di keranjang
        $q_cek = mysqli_query($koneksi, "SELECT id_keranjang, jumlah_produk, total FROM keranjang WHERE id_pengguna = $id_pengguna AND id_produk = $id_produk AND varian = '$varian'");
        if ($cek = mysqli_fetch_assoc($q_cek)) {
            // Update qty
            $id_keranjang = $cek['id_keranjang'];
            $qty = $cek['jumlah_produk'] + 1;
            $tot = $qty * $harga;
            mysqli_query($koneksi, "UPDATE keranjang SET jumlah_produk = $qty, total = $tot WHERE id_keranjang = $id_keranjang");
        } else {
            // Insert
            mysqli_query($koneksi, "INSERT INTO keranjang (id_pengguna, id_produk, jumlah_produk, total, varian) VALUES ($id_pengguna, $id_produk, 1, $harga, '$varian')");
        }
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Produk tidak ditemukan']);
    }
} elseif ($action === 'delete') {
    $id_keranjang = (int) ($_GET['id'] ?? 0);
    mysqli_query($koneksi, "DELETE FROM keranjang WHERE id_keranjang = $id_keranjang AND id_pengguna = $id_pengguna");
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
