<?php
// Guard: hanya admin yang boleh akses
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['id_pengguna'])) {
    header('Location: ../login.php'); exit;
}
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../home.php'); exit;
}

require_once '../koneksi.php';

$id_admin    = (int) $_SESSION['id_pengguna'];
$nama_admin  = htmlspecialchars($_SESSION['nama'] ?? 'Admin');

function rupiah($n) { return 'Rp ' . number_format($n, 0, ',', '.'); }
