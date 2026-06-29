<?php
session_start();
require 'koneksi.php';
header('Content-Type: application/json');

if (!isset($_SESSION['id_pengguna'])) {
    echo json_encode(['status' => 'error', 'message' => 'Belum login']);
    exit;
}

$id_user = (int) $_SESSION['id_pengguna'];
$role = $_SESSION['role'] ?? 'pembeli';
$action = $_GET['action'] ?? '';

// Ambil id_toko jika dia penjual
$id_toko_ku = 0;
if ($role === 'penjual') {
    $qT = mysqli_query($koneksi, "SELECT id_toko FROM toko WHERE id_penjual = $id_user LIMIT 1");
    if ($rT = mysqli_fetch_assoc($qT)) {
        $id_toko_ku = (int)$rT['id_toko'];
    }
}

if ($action === 'get_contacts') {
    $contacts = [];
    if ($role === 'penjual') {
        // Ambil daftar pembeli yang chat dengan toko ini
        // Kita juga perlu pastikan toko ini menginisialisasi list dari pesan
        $q = mysqli_query($koneksi, "
            SELECT DISTINCT c.id_pengguna as id, u.nama,
            (SELECT pesan FROM chat WHERE id_toko=$id_toko_ku AND id_pengguna=c.id_pengguna ORDER BY created_at DESC LIMIT 1) as last_msg,
            (SELECT created_at FROM chat WHERE id_toko=$id_toko_ku AND id_pengguna=c.id_pengguna ORDER BY created_at DESC LIMIT 1) as last_time,
            (SELECT COUNT(*) FROM chat WHERE id_toko=$id_toko_ku AND id_pengguna=c.id_pengguna AND sender='pembeli' AND is_read=0) as unread
            FROM chat c
            JOIN pengguna u ON c.id_pengguna = u.id_pengguna
            WHERE c.id_toko = $id_toko_ku
            ORDER BY last_time DESC
        ");
        if ($q) {
            while ($r = mysqli_fetch_assoc($q)) {
                $contacts[] = [
                    'id' => $r['id'],
                    'nama' => $r['nama'],
                    'foto' => '', // u.foto if existed
                    'last_msg' => htmlspecialchars($r['last_msg']),
                    'last_time' => date('H:i', strtotime($r['last_time'])),
                    'unread' => (int)$r['unread']
                ];
            }
        }
    } else {
        $q = mysqli_query($koneksi, "
            SELECT DISTINCT c.id_toko as id, t.nama_toko as nama, t.foto_toko as foto,
            (SELECT pesan FROM chat WHERE id_pengguna=$id_user AND id_toko=c.id_toko ORDER BY created_at DESC LIMIT 1) as last_msg,
            (SELECT created_at FROM chat WHERE id_pengguna=$id_user AND id_toko=c.id_toko ORDER BY created_at DESC LIMIT 1) as last_time,
            (SELECT COUNT(*) FROM chat WHERE id_pengguna=$id_user AND id_toko=c.id_toko AND sender='penjual' AND is_read=0) as unread
            FROM chat c
            JOIN toko t ON c.id_toko = t.id_toko
            WHERE c.id_pengguna = $id_user
            ORDER BY last_time DESC
        ");
        if ($q) {
            while ($r = mysqli_fetch_assoc($q)) {
                $contacts[] = [
                    'id' => $r['id'],
                    'nama' => $r['nama'],
                    'foto' => $r['foto'] ?? '',
                    'last_msg' => htmlspecialchars($r['last_msg']),
                    'last_time' => date('H:i', strtotime($r['last_time'])),
                    'unread' => (int)$r['unread']
                ];
            }
        }
    }
    echo json_encode(['status' => 'success', 'data' => $contacts]);
}
elseif ($action === 'get_messages') {
    $id_partner = (int)($_GET['id_partner'] ?? 0);
    if ($id_partner <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID tidak valid']); exit;
    }
    
    $messages = [];
    if ($role === 'penjual') {
        mysqli_query($koneksi, "UPDATE chat SET is_read = 1 WHERE id_toko = $id_toko_ku AND id_pengguna = $id_partner AND sender = 'pembeli' AND is_read = 0");
        $q = mysqli_query($koneksi, "SELECT * FROM chat WHERE id_toko = $id_toko_ku AND id_pengguna = $id_partner ORDER BY created_at ASC");
    } else {
        mysqli_query($koneksi, "UPDATE chat SET is_read = 1 WHERE id_pengguna = $id_user AND id_toko = $id_partner AND sender = 'penjual' AND is_read = 0");
        $q = mysqli_query($koneksi, "SELECT * FROM chat WHERE id_pengguna = $id_user AND id_toko = $id_partner ORDER BY created_at ASC");
    }
    
    if ($q) {
        while ($r = mysqli_fetch_assoc($q)) {
            $messages[] = [
                'id' => $r['id_chat'],
                'sender' => $r['sender'],
                'pesan' => htmlspecialchars($r['pesan']),
                'waktu' => date('H:i', strtotime($r['created_at'])),
                'is_read' => $r['is_read']
            ];
        }
    }
    echo json_encode(['status' => 'success', 'data' => $messages]);
}
elseif ($action === 'send_message') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id_partner = (int)($data['id_partner'] ?? 0);
    $pesan = trim($data['pesan'] ?? '');
    
    if ($id_partner <= 0 || $pesan === '') {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak valid']); exit;
    }
    $pesanDB = mysqli_real_escape_string($koneksi, $pesan);
    
    if ($role === 'penjual') {
        mysqli_query($koneksi, "INSERT INTO chat (id_toko, id_pengguna, sender, pesan) VALUES ($id_toko_ku, $id_partner, 'penjual', '$pesanDB')");
    } else {
        mysqli_query($koneksi, "INSERT INTO chat (id_toko, id_pengguna, sender, pesan) VALUES ($id_partner, $id_user, 'pembeli', '$pesanDB')");
    }
    echo json_encode(['status' => 'success']);
}
else {
    echo json_encode(['status' => 'error', 'message' => 'Action tidak dikenali']);
}
