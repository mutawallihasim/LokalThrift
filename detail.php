<?php
session_start();
if (!isset($_SESSION['id_pengguna'])) { header('Location: login.php'); exit; }

require_once 'koneksi.php';

$id_produk = (int)($_GET['id'] ?? 0);
if ($id_produk <= 0) { header('Location: home.php'); exit; }

// Ambil data produk + toko (tanpa filter aktif agar bisa debug)
$qP = mysqli_query($koneksi,
    "SELECT p.*, t.nama_toko, t.id_toko, t.foto_toko, t.alamat as alamat_toko
     FROM produk p
     JOIN toko t ON t.id_toko = p.id_toko
     WHERE p.id_produk = $id_produk
     LIMIT 1");
    $produk = $qP ? mysqli_fetch_assoc($qP) : null;

if ($produk) {
    // Tambah jumlah view
    mysqli_query($koneksi, "UPDATE produk SET dilihat = dilihat + 1 WHERE id_produk = $id_produk");
}

if (!$produk) {
    // Coba ambil produk apapun yang ada untuk debug
    $qAny = mysqli_query($koneksi, "SELECT id_produk FROM produk LIMIT 5");
    $available = [];
    if ($qAny) while ($r = mysqli_fetch_assoc($qAny)) $available[] = $r['id_produk'];

    $errMsg = "Produk dengan ID $id_produk tidak ditemukan.";
    if (!empty($available)) {
        $errMsg .= " ID produk yang tersedia: " . implode(', ', $available);
        // Auto-redirect ke produk pertama yang ada
        header('Location: detail.php?id=' . $available[0]);
        exit;
    } else {
        // Tidak ada produk sama sekali
        header('Location: home.php'); exit;
    }
}

// Cek apakah sudah di favorit
$id_pengguna = (int)$_SESSION['id_pengguna'];
$isFavorit = false;
$qFav = mysqli_query($koneksi,
    "SHOW TABLES LIKE 'favorit'");
if ($qFav && mysqli_num_rows($qFav) > 0) {
    $qF = mysqli_query($koneksi,
        "SELECT id FROM favorit WHERE id_pengguna=$id_pengguna AND id_produk=$id_produk LIMIT 1");
    $isFavorit = ($qF && mysqli_num_rows($qF) > 0);
}

// Gambar utama dan sekunder
$gambarUtama = !empty($produk['gambar']) ? $produk['gambar'] : '';
$gambarList = [];
if ($gambarUtama) $gambarList[] = $gambarUtama;
else $gambarList[] = 'https://images.unsplash.com/photo-1576566588028-4147f3842f27?w=600';

$hasGambarTable = mysqli_query($koneksi, "SHOW TABLES LIKE 'produk_gambar'");
if ($hasGambarTable && mysqli_num_rows($hasGambarTable) > 0) {
    $qGbr = mysqli_query($koneksi, "SELECT gambar FROM produk_gambar WHERE id_produk=$id_produk");
    if ($qGbr) {
        while ($rGbr = mysqli_fetch_assoc($qGbr)) {
            $gambarList[] = $rGbr['gambar'];
        }
    }
}

// Ambil Rating Toko
$id_toko_p = (int)$produk['id_toko'];
$qRating = mysqli_query($koneksi, 
    "SELECT AVG(r.rating) as avg_rating, COUNT(r.id_review) as total_review 
     FROM review r 
     JOIN produk p ON r.id_produk = p.id_produk 
     WHERE p.id_toko = $id_toko_p");
$ratingToko = 0;
$totalReview = 0;
if ($qRating && $rRating = mysqli_fetch_assoc($qRating)) {
    $ratingToko = round((float)$rRating['avg_rating'], 1);
    $totalReview = (int)$rRating['total_review'];
}

// Ambil Ulasan Produk Ini
$qUlasan = mysqli_query($koneksi, 
    "SELECT r.*, u.nama as nama_lengkap 
     FROM review r 
     JOIN pengguna u ON r.id_pengguna = u.id_pengguna 
     WHERE r.id_produk = $id_produk LIMIT 1");
$ulasanProduk = $qUlasan ? mysqli_fetch_assoc($qUlasan) : null;

function rupiah($n) { return 'Rp ' . number_format($n, 0, ',', '.'); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($produk['nama']) ?> - LokalThrift</title>
  <!-- DEBUG INFO -->
  <style>
  .debug-info {
    position: fixed;
    top: 10px;
    right: 10px;
    background: rgba(0,0,0,0.85);
    color: white;
    padding: 10px 14px;
    border-radius: 8px;
    font-size: 11px;
    z-index: 99999;
    max-width: 250px;
    font-family: monospace;
    line-height: 1.5;
  }
  .debug-info strong { color: #4ade80; }
  </style>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Helvetica Neue', Arial, sans-serif; }

    body {
      background: #eef5fc;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    /* BOTTOM NAVBAR */
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
      box-shadow: 0 -4px 15px rgba(0,0,0,0.05);
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
        width: 140px; height: 100vh;
        flex-direction: column; justify-content: flex-start; align-items: stretch;
        padding: 20px 0 20px 0;
        border-top-left-radius: 0; border-top-right-radius: 0;
        border-right: 1px solid #e0ecf8; box-shadow: 4px 0 15px rgba(0,0,0,0.05);
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

      .page-wrapper { margin-left: 140px; width: 100%; }
    }

    .page-wrapper {
      display: flex;
      flex: 1;
      justify-content: center;
      align-items: flex-start;
      padding: 20px 16px 100px 16px;
    }

    .detail-container {
      width: 100%;
      max-width: 860px;
      background: white;
      border-radius: 24px;
      padding: 24px;
      box-shadow: 0 8px 30px rgba(0,0,0,0.06);
    }

    /* ── HEADER ── */
    .detail-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 22px;
    }

    .btn-back {
      display: flex;
      align-items: center;
      gap: 7px;
      color: #333;
      text-decoration: none;
      font-size: 14px;
      font-weight: 600;
    }

    .btn-back:hover { color: #2a85ff; }

    .btn-fav {
      width: 38px;
      height: 38px;
      border: 1.5px solid #d4e3f3;
      background: white;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      font-size: 18px;
      color: #aaa;
      transition: 0.2s;
    }

    .btn-fav.active, .btn-fav:hover { color: #e53935; border-color: #e53935; }

    /* ── MAIN CONTENT LAYOUT ── */
    .main-content {
      display: flex;
      gap: 28px;
      flex-direction: column; /* mobile default */
    }

    /* ── GALLERY ── */
    .image-gallery {
      display: flex;
      flex-direction: column;
      gap: 14px;
    }

    .main-img-wrapper {
      width: 100%;
      aspect-ratio: 1 / 1;
      background: #f4f8fc;
      border-radius: 18px;
      overflow: hidden;
      position: relative;
    }

    .main-img-wrapper img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: opacity 0.25s;
    }

    /* Dot indicator */
    .img-dots {
      display: flex;
      justify-content: center;
      gap: 6px;
      position: absolute;
      bottom: 12px;
      left: 50%;
      transform: translateX(-50%);
    }

    .img-dot {
      width: 7px;
      height: 7px;
      border-radius: 50%;
      background: rgba(255,255,255,0.5);
      cursor: pointer;
      transition: background 0.2s;
    }

    .img-dot.active { background: white; }

    /* Thumbnails */
    .thumbnails {
      display: flex;
      gap: 10px;
      overflow-x: auto;
      padding-bottom: 4px;
    }

    .thumbnails::-webkit-scrollbar { display: none; }

    .thumb {
      width: 68px;
      height: 68px;
      flex-shrink: 0;
      border-radius: 12px;
      overflow: hidden;
      border: 2px solid transparent;
      cursor: pointer;
      transition: border-color 0.2s;
    }

    .thumb.active { border-color: #2a85ff; }

    .thumb img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    /* ── INFO SECTION ── */
    .info-section {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .product-title {
      font-size: 20px;
      font-weight: 800;
      color: #0d1c2e;
      line-height: 1.3;
    }

    .product-price {
      font-size: 22px;
      font-weight: 800;
      color: #2a85ff;
      margin-top: 4px;
    }

    .product-meta {
      display: flex;
      align-items: center;
      gap: 18px;
      flex-wrap: wrap;
    }

    .meta-rating {
      display: flex;
      align-items: center;
      gap: 5px;
      font-size: 13px;
      color: #555;
      font-weight: 600;
    }

    .meta-rating i { color: #f5a623; }

    .meta-sold {
      display: flex;
      align-items: center;
      gap: 5px;
      font-size: 13px;
      color: #555;
      font-weight: 600;
    }

    .meta-sold i { color: #888; }

    /* Divider */
    .divider {
      height: 1px;
      background: #f0f4f9;
    }

    /* Ukuran */
    .section-label {
      font-size: 14px;
      font-weight: 700;
      color: #0d1c2e;
      margin-bottom: 10px;
    }

    .size-options {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }

    .size-btn {
      width: 52px;
      height: 44px;
      border: 1.5px solid #d4e3f3;
      background: #f8fbfe;
      border-radius: 10px;
      font-size: 13px;
      font-weight: 700;
      color: #556980;
      cursor: pointer;
      transition: 0.2s;
    }

    .size-btn.active {
      border: 2px solid #2a85ff;
      background: #eef5fc;
      color: #2a85ff;
    }

    .size-btn:hover:not(.active) {
      border-color: #aac8f0;
    }

    /* Kondisi */
    .kondisi-badge {
      display: inline-block;
      padding: 7px 16px;
      background: #f4f8fc;
      color: #334;
      border: 1px solid #d4e3f3;
      font-size: 13px;
      font-weight: 600;
      border-radius: 10px;
    }

    /* Deskripsi */
    .desc-text {
      font-size: 13px;
      color: #556980;
      line-height: 1.65;
    }

    /* Poin keunggulan */
    .perks {
      display: flex;
      flex-direction: column;
      gap: 7px;
      margin-top: 10px;
    }

    .perk-item {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 13px;
      color: #556980;
    }

    .perk-item i { color: #2a85ff; font-size: 14px; }

    /* ── ACTION BUTTONS ── */
    .action-buttons {
      display: flex;
      flex-direction: column;
      gap: 10px;
      margin-top: 10px;
    }

    .btn-action {
      width: 100%;
      padding: 15px;
      font-size: 15px;
      font-weight: 700;
      border-radius: 14px;
      cursor: pointer;
      text-align: center;
      border: none;
      transition: opacity 0.2s, transform 0.1s;
    }

    .btn-action:active { transform: scale(0.98); }

    .btn-primary {
      background: #2a85ff;
      color: white;
    }

    .btn-primary:hover { opacity: 0.9; }

    .btn-secondary {
      background: white;
      color: #2a85ff;
      border: 1.5px solid #2a85ff;
    }

    .btn-secondary:hover { background: #eef5fc; }

    /* ── TOAST NOTIF ── */
    .toast {
      position: fixed;
      bottom: 30px;
      left: 50%;
      transform: translateX(-50%) translateY(20px);
      background: #0d1c2e;
      color: white;
      padding: 12px 24px;
      border-radius: 30px;
      font-size: 14px;
      font-weight: 600;
      opacity: 0;
      transition: opacity 0.3s, transform 0.3s;
      z-index: 9999;
      white-space: nowrap;
    }

    .toast.show {
      opacity: 1;
      transform: translateX(-50%) translateY(0);
    }

    /* ── DESKTOP LAYOUT ── */
    @media (min-width: 640px) {
      .page-wrapper { padding: 30px 24px 100px; align-items: center; }

      .detail-container { padding: 32px; }

      .main-content {
        flex-direction: row;
        gap: 36px;
      }

      .image-gallery { flex: 1; }

      .info-section { flex: 1; }

      .product-title { font-size: 22px; }
    }
  </style>
</head>
<body>

<div class="navbar">
  <div class="sidebar-logo">
    <img src="Logo.svg" alt="LokalThrift" style="width:110px; height:auto; display:block; margin:0 auto;">
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
  <a href="cek_toko.php" class="nav-item">
    <i class="fa-solid fa-shop"></i><span>Toko</span>
  </a>
  <a href="akun.php" class="nav-item">
    <i class="fa-solid fa-user"></i><span>Akun</span>
  </a>
  <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
  <a href="admin/dashboard.php" class="nav-item">
    <i class="fa-solid fa-chart-pie"></i><span>Admin</span>
  </a>
  <?php endif; ?>
  <a href="logout.php" class="nav-item nav-logout">
    <i class="fa-solid fa-right-from-bracket"></i><span>Logout</span>
  </a>
</div>

<div class="page-wrapper">
<div class="detail-container">

  <!-- HEADER -->
  <div class="detail-header">
    <a href="javascript:history.back()" class="btn-back">
      <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>
    <button class="btn-fav <?= $isFavorit ? 'active' : '' ?>" id="btn-fav" onclick="toggleFavorit()">
      <i class="<?= $isFavorit ? 'fa-solid' : 'fa-regular' ?> fa-heart"></i>
    </button>
  </div>

  <!-- MAIN CONTENT -->
  <div class="main-content">

    <!-- GALLERY -->
    <div class="image-gallery">
      <div class="main-img-wrapper">
        <img id="main-img" src="" alt="Foto Produk">
        <div class="img-dots" id="img-dots"></div>
      </div>
      <div class="thumbnails" id="thumbnails"></div>
    </div>

    <!-- INFO -->
    <div class="info-section">
      <div>
        <h1 class="product-title" id="product-title">Nama Produk</h1>
        <div style="font-size:14px; color:#64748b; margin-top:5px; font-weight:600; display:flex; align-items:center; gap:8px;">
          <?php if (!empty($produk['foto_toko'])): ?>
            <img src="<?= htmlspecialchars($produk['foto_toko']) ?>" style="width:24px; height:24px; border-radius:50%; object-fit:cover;">
          <?php else: ?>
            <i class="fa-solid fa-store"></i>
          <?php endif; ?>
          <a href="toko.php?id=<?= $produk['id_toko'] ?>" style="color:#2a85ff; text-decoration:none; margin-right:8px;"><?= htmlspecialchars($produk['nama_toko']) ?></a>
          <?php if(isset($_SESSION['id_pengguna']) && (!isset($_SESSION['role']) || $_SESSION['role'] !== 'penjual')): ?>
            <a href="chat.php?toko_id=<?= $produk['id_toko'] ?>" style="background:#eef5fc; color:#2a85ff; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:700; text-decoration:none; display:flex; align-items:center; gap:4px; transition:0.2s;" onmouseover="this.style.background='#d4e3f3'" onmouseout="this.style.background='#eef5fc'">
              <i class="fa-solid fa-message"></i> Chat
            </a>
          <?php endif; ?>
        </div>
        <div class="product-price" id="product-price" style="margin-top:12px;">Rp 0</div>
      </div>

      <div class="product-meta">
        <div class="meta-rating">
          <i class="fa-solid fa-star"></i>
          <span id="product-rating"><?= $totalReview > 0 ? $ratingToko . ' (' . $totalReview . ' Ulasan Toko)' : 'Belum ada ulasan' ?></span>
        </div>
        <div class="meta-sold">
          <i class="fa-regular fa-clock"></i>
          <span>Status <span id="product-sold" style="font-weight:700; color: <?= $produk['aktif'] == 0 ? '#e53935' : '#10b981' ?>;"><?= $produk['aktif'] == 0 ? 'Habis/Terjual' : 'Tersedia' ?></span></span>
        </div>
      </div>

      <div class="divider"></div>

      <!-- UKURAN -->
      <div>
        <div class="section-label">Ukuran</div>
        <span class="kondisi-badge" id="product-size" style="background:#f1f5f9; color:#0d1c2e; border:1px solid #e2e8f0; font-weight:700; padding:6px 14px; display:inline-block; border-radius:8px;">
          <?= htmlspecialchars($produk['ukuran'] ?? 'All Size') ?>
        </span>
      </div>

      <div class="divider"></div>

      <!-- KONDISI -->
      <div>
        <div class="section-label">Kondisi</div>
        <span class="kondisi-badge" id="product-condition">Very Good</span>
      </div>

      <!-- DESKRIPSI -->
      <div>
        <div class="section-label">Deskripsi</div>
        <p class="desc-text" id="product-desc">Deskripsi produk akan muncul di sini.</p>
        <div class="perks">
          <div class="perk-item"><i class="fa-regular fa-circle-check"></i> 100% Original</div>
          <div class="perk-item"><i class="fa-regular fa-circle-check"></i> Dicuci &amp; Steril</div>
          <div class="perk-item"><i class="fa-regular fa-circle-check"></i> Packing Aman</div>
        </div>
      </div>

      <?php if ($ulasanProduk): ?>
      <div class="divider"></div>
      <div>
        <div class="section-label">Ulasan Pembeli</div>
        <div style="background:#f8fafc; border-radius:12px; padding:16px; margin-top:10px;">
          <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
            <div style="font-weight:700; font-size:14px;"><?= htmlspecialchars($ulasanProduk['nama_lengkap'] ?? 'Pembeli') ?></div>
            <div style="color:#fbbf24; font-size:13px;">
              <?php for($i=0; $i<$ulasanProduk['rating']; $i++) echo '<i class="fa-solid fa-star"></i>'; ?>
            </div>
          </div>
          <div style="font-size:13px; color:#475569; line-height:1.5;">
            <?= nl2br(htmlspecialchars($ulasanProduk['ulasan'] ?? '')) ?>
          </div>
          <div style="font-size:11px; color:#94a3b8; margin-top:10px;">
            Diupload pada: <?= date('d M Y', strtotime($ulasanProduk['created_at'])) ?>
          </div>
        </div>
      </div>
      <?php endif; ?>

    </div>
  </div>

  <!-- ACTION BUTTONS -->
  <div class="action-buttons">
    <?php if ($produk['aktif'] == 1): ?>
    <button class="btn-action btn-primary" onclick="aksiKeranjang()">
      <i class="fa-solid fa-plus"></i> Tambah ke Keranjang
    </button>
    <button class="btn-action btn-secondary" onclick="aksiBeli()">
      Beli Sekarang
    </button>
    <?php else: ?>
    <button class="btn-action" style="background:#e2e8f0; color:#64748b; cursor:not-allowed;" disabled>
      Produk Habis/Terjual
    </button>
    <?php endif; ?>
  </div>

</div><!-- /.detail-container -->
</div><!-- /.page-wrapper -->

<!-- TOAST -->
<div class="toast" id="toast"></div>

<script>
  let currentImages = [];
  let activeIndex = 0;

  function showImage(index) {
    activeIndex = index;
    const mainImg = document.getElementById('main-img');
    mainImg.style.opacity = 0;
    setTimeout(() => {
      mainImg.src = currentImages[index];
      mainImg.style.opacity = 1;
    }, 150);

    // Update dots
    document.querySelectorAll('.img-dot').forEach((d, i) => {
      d.classList.toggle('active', i === index);
    });

    // Update thumbnails
    document.querySelectorAll('.thumb').forEach((t, i) => {
      t.classList.toggle('active', i === index);
    });
  }

  function buildGallery(images) {
    currentImages = images;

    // Thumbnails
    const thumbContainer = document.getElementById('thumbnails');
    thumbContainer.innerHTML = '';
    images.forEach((src, i) => {
      const div = document.createElement('div');
      div.className = 'thumb' + (i === 0 ? ' active' : '');
      div.onclick = () => showImage(i);
      const img = document.createElement('img');
      img.src = src;
      img.alt = 'Foto ' + (i + 1);
      div.appendChild(img);
      thumbContainer.appendChild(div);
    });

    // Dots
    const dotsContainer = document.getElementById('img-dots');
    dotsContainer.innerHTML = '';
    images.forEach((_, i) => {
      const dot = document.createElement('div');
      dot.className = 'img-dot' + (i === 0 ? ' active' : '');
      dot.onclick = () => showImage(i);
      dotsContainer.appendChild(dot);
    });

    showImage(0);
  }

  window.onload = function () {
    const p = {
      title: <?= json_encode($produk['nama']) ?>,
      price: <?= json_encode(rupiah($produk['harga'])) ?>,
      condition: <?= json_encode($produk['kondisi'] ?? 'Very Good') ?>,
      desc: <?= json_encode($produk['deskripsi'] ?? 'Deskripsi tidak tersedia.') ?>,
      images: <?= json_encode($gambarList) ?>
    };

    document.getElementById('product-title').innerText = p.title;
    document.getElementById('product-price').innerText = p.price;
    document.getElementById('product-condition').innerText = p.condition;
    document.getElementById('product-desc').innerText = p.desc;
    document.title = p.title + ' - LokalThrift';

    buildGallery(p.images);
  };

  function pilihUkuran(el) {
    document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('active'));
    el.classList.add('active');
  }

  function toggleFavorit() {
    const btn = document.getElementById('btn-fav');
    const id_produk = '<?= $id_produk ?>';
    
    fetch('api_toggle_favorit.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_produk: id_produk })
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            const icon = btn.querySelector('i');
            if (data.action === 'added') {
                btn.classList.add('active');
                icon.className = 'fa-solid fa-heart';
                showToast('Ditambahkan ke favorit ❤️');
            } else {
                btn.classList.remove('active');
                icon.className = 'fa-regular fa-heart';
                showToast('Dihapus dari favorit');
            }
        } else {
            showToast('Gagal memproses favorit');
        }
    })
    .catch(err => {
        showToast('Gagal memproses favorit');
    });
  }

  function showToast(msg) {
    const toast = document.getElementById('toast');
    toast.innerText = msg;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 2500);
  }

  function getUkuran() {
    const sizeEl = document.getElementById('product-size');
    return sizeEl ? sizeEl.innerText.trim() : 'All Size';
  }

  function aksiKeranjang() {
    <?php if (!isset($_SESSION['id_pengguna'])): ?>
      window.location.href = 'login.php';
      return;
    <?php endif; ?>
    const params = new URLSearchParams(window.location.search);
    const id = params.get('id') || '<?= $id_produk ?>';
    const ukuran = getUkuran();
    const kondisi = document.getElementById('product-condition').innerText;
    const varian = kondisi + ', ' + ukuran;

    fetch('api_keranjang.php?action=add', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id_produk: id, varian: varian })
    })
    .then(res => res.json())
    .then(res => {
      if(res.status === 'success') {
        showToast('Berhasil ditambahkan ke keranjang 🛒');
        // Bersihkan cart localStorage lama jika ada
        localStorage.removeItem('keranjang');
      } else {
        showToast(res.message || 'Gagal menambahkan ke keranjang');
      }
    })
    .catch(err => {
      showToast('Terjadi kesalahan koneksi');
    });
  }

  function aksiBeli() {
    const params = new URLSearchParams(window.location.search);
    const id = params.get('id') || '<?= $id_produk ?>';
    const nama = document.getElementById('product-title').innerText;
    const harga = parseInt(document.getElementById('product-price').innerText.replace(/[^0-9]/g, ''));
    const gambar = document.getElementById('main-img').src;
    const ukuran = getUkuran();
    const kondisi = document.getElementById('product-condition').innerText;

    // Langsung set item untuk checkout via sessionStorage lalu redirect
    const items = [{ id, nama, varian: kondisi + ', ' + ukuran, harga, gambar, checked: true }];
    sessionStorage.setItem('checkout_items', JSON.stringify(items));
    window.location.href = 'checkout.php';
  }
</script>
</body>
</html>
