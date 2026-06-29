<?php
session_start();
require 'koneksi.php';

// Format Rupiah
function rupiah($angka)
{
  return "Rp " . number_format($angka, 0, ',', '.');
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
  header("Location: home.php");
  exit;
}

$id_toko = (int)$_GET['id'];

// Ambil info toko
$qToko = mysqli_query($koneksi, "SELECT t.*, u.nama as nama_pemilik, u.created_at as bergabung 
    FROM toko t 
    JOIN pengguna u ON t.id_penjual = u.id_pengguna 
    WHERE t.id_toko = $id_toko LIMIT 1");
$toko = mysqli_fetch_assoc($qToko);

if (!$toko) {
  header("Location: home.php");
  exit;
}

// Hitung rating toko
$qRating = mysqli_query(
  $koneksi,
  "SELECT AVG(r.rating) as rata_rata, COUNT(r.id_review) as total_review 
     FROM review r 
     JOIN produk p ON r.id_produk = p.id_produk 
     WHERE p.id_toko = $id_toko"
);
$ratingData = mysqli_fetch_assoc($qRating);
$rataRating = $ratingData['rata_rata'] ? number_format($ratingData['rata_rata'], 1) : 0;
$totalReview = $ratingData['total_review'];

// Ambil produk toko
$produkList = [];
$qProduk = mysqli_query($koneksi, "SELECT * FROM produk WHERE id_toko = $id_toko AND aktif = 1 AND stok > 0 ORDER BY created_at DESC");
if ($qProduk) {
  while ($r = mysqli_fetch_assoc($qProduk)) {
    $produkList[] = $r;
  }
}

// Ambil produk terjual (stok = 0)
$produkTerjual = [];
$qTerjual = mysqli_query($koneksi, "
    SELECT p.*, COALESCE(AVG(r.rating), 0) as avg_rating, COUNT(r.id_review) as total_ulasan
    FROM produk p 
    LEFT JOIN review r ON p.id_produk = r.id_produk
    WHERE p.id_toko = $id_toko AND p.stok = 0 
    GROUP BY p.id_produk
    ORDER BY p.created_at DESC
");
if ($qTerjual) {
  while ($r = mysqli_fetch_assoc($qTerjual)) {
    $produkTerjual[] = $r;
  }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($toko['nama_toko']) ?> - LokalThrift</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      background: #f8fafc;
      color: #1e293b;
    }

    /* Navbar */
    .navbar {
      background: white;
      padding: 16px 5%;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: sticky;
      top: 0;
      z-index: 100;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
    }

    .brand {
      font-size: 24px;
      font-weight: 800;
      color: #2a85ff;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .nav-links a {
      text-decoration: none;
      color: #475569;
      font-weight: 600;
      margin-left: 20px;
      transition: 0.2s;
    }

    .nav-links a:hover {
      color: #2a85ff;
    }

    .container {
      max-width: 1200px;
      margin: 30px auto;
      padding: 0 20px;
    }

    /* Store Header */
    .store-header {
      background: white;
      border-radius: 16px;
      padding: 30px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
      display: flex;
      gap: 24px;
      margin-bottom: 30px;
      align-items: flex-start;
    }

    .store-avatar {
      width: 80px;
      height: 80px;
      background: #e0ecf8;
      color: #2a85ff;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 32px;
      flex-shrink: 0;
    }

    .store-info {
      flex: 1;
    }

    .store-name {
      font-size: 24px;
      font-weight: 800;
      color: #0f172a;
      margin-bottom: 6px;
    }

    .store-desc {
      color: #64748b;
      font-size: 14px;
      margin-bottom: 16px;
      line-height: 1.5;
    }

    .store-stats {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
    }

    .stat-item {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 13px;
      color: #475569;
      background: #f1f5f9;
      padding: 6px 12px;
      border-radius: 8px;
      font-weight: 600;
    }

    /* Product Grid */
    .section-title {
      font-size: 18px;
      font-weight: 700;
      color: #0f172a;
      margin-bottom: 16px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 20px;
    }

    .card {
      background: white;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
      transition: transform 0.2s, box-shadow 0.2s;
      text-decoration: none;
      color: inherit;
      display: block;
    }

    .card:hover {
      transform: translateY(-4px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    }

    .card img {
      width: 100%;
      height: 200px;
      object-fit: cover;
    }

    .card-body {
      padding: 16px;
    }

    .card-title {
      font-weight: 600;
      font-size: 14px;
      margin-bottom: 8px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      color: #1e293b;
    }

    .card-price {
      font-weight: 700;
      color: #2a85ff;
      font-size: 16px;
      margin-bottom: 8px;
    }

    .card-meta {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 12px;
      color: #64748b;
    }

    .card-kondisi {
      background: #f1f5f9;
      padding: 2px 8px;
      border-radius: 4px;
      font-weight: 600;
    }

    .empty-state {
      text-align: center;
      padding: 50px 20px;
      background: white;
      border-radius: 16px;
      color: #64748b;
    }

    .empty-state i {
      font-size: 48px;
      color: #cbd5e1;
      margin-bottom: 16px;
    }
  </style>
</head>

<body>

  <!-- NAVBAR -->
  <nav class="navbar">
    <a href="home.php" class="brand"><img src="Logo.svg" alt="LokalThrift" style="height:50px;"></a>
    <div class="nav-links">
      <a href="javascript:history.back()"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
    </div>
  </nav>

  <div class="container">

    <div class="store-header">
      <div class="store-avatar">
        <?php if (!empty($toko['foto_toko'])): ?>
          <img src="<?= htmlspecialchars($toko['foto_toko']) ?>" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
        <?php else: ?>
          <i class="fa-solid fa-store"></i>
        <?php endif; ?>
      </div>
      <div class="store-info">
        <div style="display:flex; align-items:center; gap:16px; margin-bottom:16px;">
          <h1 class="store-name" style="margin-bottom:0;"><?= htmlspecialchars($toko['nama_toko']) ?></h1>
          <?php if (isset($_SESSION['id_pengguna']) && (!isset($_SESSION['role']) || $_SESSION['role'] !== 'penjual')): ?>
            <a href="chat.php?toko_id=<?= $id_toko ?>" style="background:#2a85ff; color:white; padding:6px 16px; border-radius:20px; font-size:13px; font-weight:700; text-decoration:none; display:flex; align-items:center; gap:6px; transition:0.2s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
              <i class="fa-solid fa-message"></i> Chat Penjual
            </a>
          <?php endif; ?>
        </div>
        <?php if (!empty($toko['deskripsi'])): ?>
          <div class="store-desc"><?= nl2br(htmlspecialchars($toko['deskripsi'])) ?></div>
        <?php endif; ?>
        <div class="store-stats">
          <div class="stat-item">
            <i class="fa-solid fa-box" style="color: #3b82f6;"></i> <?= count($produkList) ?> Produk
          </div>
          <div class="stat-item">
            <i class="fa-solid fa-star" style="color: #fbbf24;"></i> <?= $rataRating ?> / 5 (<?= $totalReview ?> Ulasan)
          </div>
          <div class="stat-item">
            <i class="fa-solid fa-location-dot" style="color: #ef4444;"></i> <?= !empty($toko['alamat']) ? htmlspecialchars($toko['alamat']) : 'Alamat belum diatur' ?>
          </div>
          <div class="stat-item">
            <i class="fa-solid fa-calendar-check" style="color: #10b981;"></i> Bergabung sejak <?= date('M Y', strtotime($toko['bergabung'])) ?>
          </div>
        </div>
      </div>
    </div>

    <div class="section-title" style="margin-bottom:20px; font-size:18px; font-weight:800; color:#0f172a;"><i class="fa-solid fa-border-all" style="color:#2a85ff"></i> Produk Toko yang Tersedia</div>

    <?php if (empty($produkList)): ?>
      <div class="empty-state">
        <i class="fa-solid fa-box-open"></i>
        <h3>Belum Ada Produk Tersedia</h3>
        <p style="margin-top:8px;">Toko ini belum menambahkan produk atau produk sedang habis.</p>
      </div>
    <?php else: ?>
      <div class="grid">
        <?php foreach ($produkList as $p): ?>
          <a href="detail.php?id=<?= $p['id_produk'] ?>" class="card">
            <img src="<?= !empty($p['gambar']) ? htmlspecialchars($p['gambar']) : 'https://via.placeholder.com/300' ?>"
              onerror="this.src='https://via.placeholder.com/300'">
            <div class="card-body">
              <div class="card-title"><?= htmlspecialchars($p['nama']) ?></div>
              <div class="card-price"><?= rupiah($p['harga']) ?></div>
              <div class="card-meta">
                <span class="card-kondisi"><?= htmlspecialchars($p['kondisi']) ?></span>
                <span style="font-weight:600; color:#0f172a;"><?= htmlspecialchars($p['ukuran'] ?? 'M') ?></span>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="section-title" style="margin-top:40px; margin-bottom:20px; font-size:18px; font-weight:800; color:#0f172a;"><i class="fa-solid fa-check-double" style="color:#10b981"></i> Produk Toko yang Terjual</div>

    <?php if (empty($produkTerjual)): ?>
      <div class="empty-state">
        <i class="fa-solid fa-bag-shopping"></i>
        <h3>Belum Ada Produk Terjual</h3>
        <p style="margin-top:8px;">Belum ada produk yang laku terjual dari toko ini.</p>
      </div>
    <?php else: ?>
      <div class="grid" style="opacity: 0.8;">
        <?php foreach ($produkTerjual as $p): ?>
          <a href="detail.php?id=<?= $p['id_produk'] ?>" class="card">
            <img src="<?= !empty($p['gambar']) ? htmlspecialchars($p['gambar']) : 'https://via.placeholder.com/300' ?>"
              onerror="this.src='https://via.placeholder.com/300'">
            <div class="card-body">
              <div class="card-title"><?= htmlspecialchars($p['nama']) ?></div>
              <div class="card-price"><?= rupiah($p['harga']) ?></div>
              <div class="card-meta">
                <span class="card-kondisi" style="background:#f1f5f9; color:#64748b;">Terjual</span>
                <span style="font-weight:600; color:#64748b; font-size:12px; display:flex; align-items:center; gap:4px;">
                  <i class="fa-solid fa-star" style="color:#fbbf24;"></i> <?= number_format($p['avg_rating'], 1) ?> (<?= $p['total_ulasan'] ?>)
                </span>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>

</body>

</html>