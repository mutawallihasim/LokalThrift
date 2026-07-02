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
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', 'Helvetica Neue', Arial, sans-serif;
    }

    body {
      background: #eef5fc;
      color: #0d1c2e;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    /* BOTTOM NAVBAR (sama seperti halaman lain) */
    .navbar {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      width: 100%;
      background: white;
      display: flex;
      justify-content: space-around;
      align-items: center;
      padding: 10px 0 14px 0;
      border-top-left-radius: 20px;
      border-top-right-radius: 20px;
      box-shadow: 0 -4px 15px rgba(0, 0, 0, 0.05);
      z-index: 999;
    }

    .nav-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      font-size: 11px;
      font-weight: bold;
      color: #777;
      text-decoration: none;
      flex: 1;
      gap: 4px;
    }

    .nav-item i {
      font-size: 20px;
      margin-bottom: 0;
      display: block;
    }

    .nav-item.active { color: #2a85ff; }
    .nav-item.nav-logout { color: #e53935; }
    .nav-item.nav-logout:hover { color: #c62828; }

    .sidebar-logo { display: none; }

    @media (min-width: 769px) {
      body { flex-direction: row; }

      .navbar {
        position: fixed;
        top: 0; left: 0; bottom: 0; right: auto;
        width: 160px; height: 100vh;
        flex-direction: column; justify-content: flex-start; align-items: stretch;
        padding: 20px 0 20px 0;
        border-top-left-radius: 0; border-top-right-radius: 0;
        border-right: 1px solid #e0ecf8; box-shadow: 4px 0 15px rgba(0, 0, 0, 0.05);
        gap: 4px;
      }

      .sidebar-logo {
        display: flex; align-items: center; justify-content: center; gap: 6px;
        font-size: 13px; font-weight: 800; color: #2a85ff;
        padding: 0 8px 18px 8px; border-bottom: 1px solid #e0ecf8;
        margin-bottom: 8px; text-align: center;
      }

      .nav-item {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        text-align: center; padding: 12px 8px; border-radius: 12px; margin: 2px 8px;
        font-size: 11px; font-weight: 600; flex: none;
        gap: 5px; transition: background 0.2s, color 0.2s;
      }

      .nav-item:hover { background: #eef5fc; color: #2a85ff; }
      .nav-item.active { background: #ddeeff; color: #2a85ff; }
      .nav-item.nav-logout { margin-top: auto; color: #e53935; }
      .nav-item.nav-logout:hover { background: #fff0f0; color: #c62828; }

      .nav-item i { font-size: 22px; display: block; margin-bottom: 0; width: auto; }

      .page-wrapper { margin-left: 160px; width: 100%; }
    }

    .page-wrapper {
      display: flex;
      flex: 1;
      flex-direction: column;
    }

    .btn-back-toko {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      color: #556980;
      text-decoration: none;
      font-size: 14px;
      font-weight: 600;
      margin-bottom: 4px;
    }

    .container {
      width: 90%;
      max-width: 1200px;
      margin: 30px auto;
      padding-bottom: 100px;
    }

    /* Store Header */
    .store-header {
      background: white;
      border-radius: 16px;
      padding: 30px;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
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
      color: #0d1c2e;
      margin-bottom: 6px;
    }

    .store-desc {
      color: #8fa3b8;
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
      color: #556980;
      background: #eef5fc;
      padding: 6px 12px;
      border-radius: 8px;
      font-weight: 600;
    }

    /* Product Grid */
    .section-title {
      font-size: 18px;
      font-weight: 700;
      color: #0d1c2e;
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
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
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
      color: #0d1c2e;
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
      color: #8fa3b8;
    }

    .card-kondisi {
      background: #eef5fc;
      color: #556980;
      padding: 2px 8px;
      border-radius: 4px;
      font-weight: 600;
    }

    .empty-state {
      text-align: center;
      padding: 50px 20px;
      background: white;
      border-radius: 16px;
      color: #8fa3b8;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
    }

    .empty-state i {
      font-size: 48px;
      color: #c8dff5;
      margin-bottom: 16px;
    }
  </style>
</head>

<body>

  <div class="navbar">
    <div class="sidebar-logo">
      <img src="Logo.svg" alt="LokalThrift" style="width:140px; height:auto; display:block; margin:0 auto;">
    </div>
    <a href="home.php" class="nav-item">
      <i class="fa-solid fa-house"></i><span>Beranda</span>
    </a>
    <a href="keranjang.php" class="nav-item">
      <i class="fa-solid fa-cart-shopping"></i><span>Keranjang</span>
    </a>
    <a href="pesanan.php" class="nav-item">
      <i class="fa-solid fa-bag-shopping"></i><span>Pesanan</span>
    </a>
    <a href="cek_toko.php" class="nav-item active">
      <i class="fa-solid fa-shop"></i><span>Toko</span>
    </a>
    <a href="akun.php" class="nav-item">
      <i class="fa-solid fa-user"></i><span>Akun</span>
    </a>
    <a href="chat.php" class="nav-item">
      <i class="fa-solid fa-message"></i><span>Chat</span>
    </a>
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
      <a href="admin/dashboard.php" class="nav-item">
        <i class="fa-solid fa-chart-pie"></i><span>Admin</span>
      </a>
    <?php endif; ?>
    <a href="logout.php" class="nav-item nav-logout">
      <i class="fa-solid fa-right-from-bracket"></i><span>Logout</span>
    </a>
  </div>

  <div class="page-wrapper">
  <div class="container">
    <a href="javascript:history.back()" class="btn-back-toko"><i class="fa-solid fa-arrow-left"></i> Kembali</a>

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

    <div class="section-title" style="margin-bottom:20px; font-size:18px; font-weight:800; color:#0d1c2e;"><i class="fa-solid fa-border-all" style="color:#2a85ff"></i> Produk Toko yang Tersedia</div>

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
                <span style="font-weight:600; color:#0d1c2e;"><?= htmlspecialchars($p['ukuran'] ?? 'M') ?></span>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="section-title" style="margin-top:40px; margin-bottom:20px; font-size:18px; font-weight:800; color:#0d1c2e;"><i class="fa-solid fa-check-double" style="color:#10b981"></i> Produk Toko yang Terjual</div>

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
                <span class="card-kondisi" style="background:#eef5fc; color:#556980;">Terjual</span>
                <span style="font-weight:600; color:#8fa3b8; font-size:12px; display:flex; align-items:center; gap:4px;">
                  <i class="fa-solid fa-star" style="color:#fbbf24;"></i> <?= number_format($p['avg_rating'], 1) ?> (<?= $p['total_ulasan'] ?>)
                </span>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
  </div><!-- /.page-wrapper -->

</body>

</html>