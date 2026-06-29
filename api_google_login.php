<?php
session_start();
require 'koneksi.php';

header('Content-Type: application/json');

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!isset($data['credential'])) {
    echo json_encode(['status' => 'error', 'message' => 'Token Google tidak ditemukan.']);
    exit;
}

$credential = $data['credential'];

// Verifikasi token ke server Google
$verifyUrl = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . $credential;
$response = @file_get_contents($verifyUrl);

if ($response === false) {
    echo json_encode(['status' => 'error', 'message' => 'Gagal memverifikasi token dengan Google.']);
    exit;
}

$googleData = json_decode($response, true);

if (!isset($googleData['email'])) {
    echo json_encode(['status' => 'error', 'message' => 'Data email tidak valid atau token kedaluwarsa.']);
    exit;
}

$email = mysqli_real_escape_string($koneksi, $googleData['email']);
$nama = mysqli_real_escape_string($koneksi, $googleData['name']);

// Cek apakah email sudah terdaftar
$qCek = mysqli_query($koneksi, "SELECT * FROM pengguna WHERE email='$email' LIMIT 1");

if (mysqli_num_rows($qCek) > 0) {
    // Pengguna sudah ada, langsung login
    $user = mysqli_fetch_assoc($qCek);
    $_SESSION['id_pengguna'] = $user['id_pengguna'];
    $_SESSION['nama'] = $user['nama'];
    $_SESSION['role'] = $user['role'];
} else {
    // Pengguna belum ada, daftar otomatis sebagai pembeli
    $randomPassword = password_hash(uniqid('gAuth_', true), PASSWORD_DEFAULT);
    $qInsert = mysqli_query($koneksi, "INSERT INTO pengguna (nama, email, password, role) VALUES ('$nama', '$email', '$randomPassword', 'pembeli')");
    
    if ($qInsert) {
        $newId = mysqli_insert_id($koneksi);
        $_SESSION['id_pengguna'] = $newId;
        $_SESSION['nama'] = $nama;
        $_SESSION['role'] = 'pembeli';
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal mendaftarkan akun secara otomatis.']);
        exit;
    }
}

// Berikan respons sukses dan kemana harus di-redirect
$role = $_SESSION['role'];
$redirectUrl = 'home.php';
if ($role == 'admin') $redirectUrl = 'admin/dashboard.php';
if ($role == 'penjual') $redirectUrl = 'penjual/dashboard.php';

echo json_encode(['status' => 'success', 'redirect' => $redirectUrl]);
exit;
?>
