<?php
session_start();
if (!isset($_SESSION['id_pengguna'])) {
    header('Location: login.php');
    exit;
}

require_once 'koneksi.php';

// Kategori untuk filter
$kategoriList = ['Semua','Casual','Vintage','Sport','Denim','Outwear','Atasan','Bawahan','Aksesoris'];
$kategoriAktif = $_GET['kategori'] ?? 'Semua';
$search = trim($_GET['q'] ?? '');

// Notifikasi Unread Count
$id_pengguna = (int)$_SESSION['id_pengguna'];
$qUnread = mysqli_query($koneksi, "SELECT COUNT(*) as unread FROM notifikasi WHERE id_pengguna=$id_pengguna AND is_read=0");
$unreadCount = $qUnread ? (int)mysqli_fetch_assoc($qUnread)['unread'] : 0;

// ── PRODUK TERBARU (aktif, fetch up to 36 for show more)
$whereTerbaru = "WHERE p.aktif = 1";
$produkTerbaru = [];
$qPT = mysqli_query($koneksi,
    "SELECT p.*, t.nama_toko
     FROM produk p
     JOIN toko t ON t.id_toko = p.id_toko
     $whereTerbaru
     ORDER BY p.created_at DESC LIMIT 36");
if ($qPT) while ($r = mysqli_fetch_assoc($qPT)) $produkTerbaru[] = $r;

// ── PRODUK REKOMENDASI (aktif, diurutkan berdasarkan like)
$excludeStr = ''; // Tidak perlu eksklusif agar produk favorit tetap muncul di rekomendasi meskipun dia produk baru
$whereKategori = '';
if ($kategoriAktif !== 'Semua') {
    $kat = mysqli_real_escape_string($koneksi, $kategoriAktif);
    $whereKategori = "AND p.kategori = '$kat'";
}
$whereSearch = '';
if ($search !== '') {
    $sq = mysqli_real_escape_string($koneksi, $search);
    $whereSearch = "AND (p.nama LIKE '%$sq%' OR p.deskripsi LIKE '%$sq%' OR t.nama_toko LIKE '%$sq%')";
}
$hasFavorit = mysqli_query($koneksi, "SHOW TABLES LIKE 'favorit'");
$produkRekomendasi = [];
if ($hasFavorit && mysqli_num_rows($hasFavorit) > 0) {
    $qPR = mysqli_query($koneksi,
        "SELECT p.*, t.nama_toko, COUNT(f.id) as jml_like
         FROM produk p
         JOIN toko t ON t.id_toko = p.id_toko
         JOIN favorit f ON f.id_produk = p.id_produk
         WHERE p.aktif = 1 $excludeStr $whereKategori $whereSearch
         GROUP BY p.id_produk
         ORDER BY jml_like DESC, RAND() LIMIT 36");
    if ($qPR) while ($r = mysqli_fetch_assoc($qPR)) $produkRekomendasi[] = $r;
}

// Kalau ada filter/search, tampilkan semua produk yang cocok di satu section
$produkFilter = [];
if ($search !== '' || $kategoriAktif !== 'Semua') {
    $sq2 = $search !== '' ? mysqli_real_escape_string($koneksi, $search) : '';
    $searchWhere = $sq2 !== '' ? "AND (p.nama LIKE '%$sq2%' OR p.deskripsi LIKE '%$sq2%' OR t.nama_toko LIKE '%$sq2%')" : '';
    $katWhere    = $kategoriAktif !== 'Semua' ? "AND p.kategori = '" . mysqli_real_escape_string($koneksi, $kategoriAktif) . "'" : '';
    $qPF = mysqli_query($koneksi,
        "SELECT p.*, t.nama_toko
         FROM produk p
         JOIN toko t ON t.id_toko = p.id_toko
         WHERE p.aktif = 1 $searchWhere $katWhere
         ORDER BY p.created_at DESC");
    if ($qPF) while ($r = mysqli_fetch_assoc($qPF)) $produkFilter[] = $r;
}

function rupiah($n) { return 'Rp ' . number_format($n, 0, ',', '.'); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home - LokalThrift</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <style>
    *{
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', 'Helvetica Neue', Arial, sans-serif;
    }

    body {
      background: #eef5fc; 
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    /* CONTAINER UTAMA (Adaptif mengikuti layar) */
    .home {
      width: 100%;
      max-width: 1400px; 
      margin: 0 auto;
      padding: 20px 16px 100px 16px; /* Padding bawah untuk space navbar mobile */
      flex: 1;
    }

    /* WRAPPER untuk sidebar + konten di desktop */
    .page-wrapper {
      display: flex;
      flex: 1;
    }

    /* HEADER */
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }

    .header .title {
      font-size: 20px;
      font-weight: bold;
      color: #2a85ff;
    }

    .header .notif-btn {
      font-size: 22px;
      color: #000;
      cursor: pointer;
    }

    /* SEARCH BOX */
    .search-box {
      position: relative;
      margin-bottom: 20px;
      width: 100%;
    }

    .search-box input {
      width: 100%;
      padding: 12px 12px 12px 45px;
      border: 1px solid #d4e3f3;
      border-radius: 40px;
      background: white;
      outline: none;
      font-size: 14px;
      color: #333;
    }

    .search-box i {
      position: absolute;
      left: 18px;
      top: 50%;
      transform: translateY(-50%);
      color: #000;
      font-size: 16px;
    }

    /* KATEGORI (Bisa di-scroll geser kanan-kiri di HP) */
    .kategori {
      display: flex;
      gap: 10px;
      overflow-x: auto;
      margin-bottom: 25px;
      padding-bottom: 5px;
    }

    .kategori::-webkit-scrollbar {
      display: none;
    }

    .kategori .item {
      padding: 10px 24px;
      background: #a9d4f9;
      color: #000;
      border-radius: 30px;
      font-size: 14px;
      font-weight: bold;
      white-space: nowrap;
      cursor: pointer;
      text-decoration: none;
    }

    .kategori .item.item-active {
      background: #2a85ff;
      color: #fff;
    }

    /* BANNER PROMO */
    .banner {
      background: linear-gradient(115deg, #1c5fd6 0%, #2a85ff 45%, #57a6ff 100%);
      border-radius: 18px;
      padding: 0;
      margin-bottom: 25px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: relative;
      overflow: hidden;
      min-height: 150px;
      box-shadow: 0 10px 26px rgba(28, 95, 214, 0.32);
    }

    /* decorative soft circles instead of a clashing photo */
    .banner::before {
      content: "";
      position: absolute;
      top: -60px;
      right: -40px;
      width: 220px;
      height: 220px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.12);
      z-index: 1;
    }

    .banner::after {
      content: "";
      position: absolute;
      bottom: -70px;
      right: 90px;
      width: 160px;
      height: 160px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.10);
      z-index: 1;
    }

    .banner-text {
      max-width: 62%;
      position: relative;
      z-index: 4;
      padding: 26px 10px 26px 26px;
    }

    .banner-text .tag {
      display: inline-block;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.6px;
      color: #1c5fd6;
      background: #ffffff;
      padding: 4px 10px;
      border-radius: 20px;
      margin-bottom: 8px;
    }

    .banner-text h1 {
      font-size: 21px;
      font-weight: 800;
      margin: 4px 0 6px;
      color: #ffffff;
      line-height: 1.25;
    }

    .banner-text p {
      font-size: 12.5px;
      color: rgba(255, 255, 255, 0.88);
      margin-bottom: 14px;
    }

    .banner-text .btn-belanja {
      display: inline-block;
      padding: 9px 20px;
      background: #ffffff;
      color: #1c5fd6;
      font-size: 12.5px;
      font-weight: bold;
      border-radius: 25px;
      text-decoration: none;
      box-shadow: 0 6px 14px rgba(11, 31, 58, 0.25);
      transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .banner-text .btn-belanja:hover {
      transform: translateY(-1px);
      box-shadow: 0 8px 18px rgba(11, 31, 58, 0.32);
    }

    /* photo card area replacing the old flat SVG icon */
    .banner-icon-container {
      position: absolute;
      top: 0;
      right: 0;
      bottom: 0;
      width: 40%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 2;
    }

    /* the thrift-clothing photo sits inside a tilted, rounded, bordered card
       so it reads as a deliberate product highlight instead of a clashing
       full-bleed background — this is what keeps it feeling cohesive */
    .banner-photo-card {
      width: 62%;
      aspect-ratio: 1 / 1;
      border-radius: 16px;
      overflow: hidden;
      transform: rotate(-4deg);
      border: 4px solid rgba(255, 255, 255, 0.9);
      box-shadow: 0 14px 28px rgba(11, 31, 58, 0.35);
      position: relative;
    }

    .banner-photo-card img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: center 30%;
      display: block;
      /* gentle color match so the photo's natural tones sit comfortably
         inside the blue banner instead of feeling like a separate cutout */
      filter: saturate(1.05) brightness(1.03);
    }

    /* soft blue wash along the card edges to tie it visually into the
       gradient background, without flattening the photo itself */
    .banner-photo-card::after {
      content: "";
      position: absolute;
      inset: 0;
      background: linear-gradient(160deg, rgba(28, 95, 214, 0.22) 0%, rgba(28, 95, 214, 0) 45%, rgba(11, 31, 58, 0.18) 100%);
      pointer-events: none;
    }

    .banner-badge {
      position: absolute;
      top: 14%;
      right: 10%;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: #ffe066;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 6px 14px rgba(11, 31, 58, 0.3);
      z-index: 3;
    }

    .banner-badge span {
      font-size: 17px;
      font-weight: 800;
      color: #1c5fd6;
    }

    /* SECTION LAYOUT PRODUK UTAMA */
    .section-container {
      display: flex;
      flex-direction: column; /* Default Mobile: Turun ke bawah */
      gap: 20px;
    }

    .block-produk {
      background: white;
      border-radius: 16px;
      padding: 16px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.01);
    }

    .block-title {
      font-size: 16px;
      font-weight: bold;
      color: #000;
      margin-bottom: 15px;
    }

    /* GRID PRODUK (Default Mobile: 2 Kolom Menyamping) */
    .grid-produk {
      display: grid;
      grid-template-columns: repeat(2, 1fr); 
      gap: 12px;
    }

    .card-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      text-decoration: none; 
      color: inherit; 
    }

    .img-wrapper {
      width: 100%;
      background: #f7f9fa;
      border-radius: 12px;
      aspect-ratio: 1 / 1;
      overflow: hidden; 
    }

    .card-item img {
      width: 100%;
      height: 100%;
      object-fit: cover; 
    }

    .card-item .item-name {
      font-size: 13px;
      color: #333;
      margin-top: 8px;
      width: 100%;
      text-align: left;
    }

    .card-item .item-price {
      font-size: 13px;
      font-weight: bold;
      color: #000;
      width: 100%;
      text-align: left;
    }

    /* BOTTOM NAVBAR (Menempel manis di bawah layar HP) */
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

    .nav-item.active {
      color: #2a85ff;
    }

    .nav-item.nav-logout {
      color: #e53935;
    }

    .nav-item.nav-logout:hover {
      color: #c62828;
    }

    /* TOMBOL SHOW MORE */
    .btn-show-more {
      background: white;
      color: #2a85ff;
      border: 1px solid #2a85ff;
      padding: 10px 20px;
      border-radius: 20px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
    }

    .btn-show-more:hover {
      background: #eef5fc;
    }

    /* Logo area di sidebar (hanya tampil di desktop) */
    .sidebar-logo {
      display: none;
    }


    /* ==========================================================================
       MEDIA QUERY: ATURAN KHUSUS SAAT DIBUKA DI LAYAR LAPTOP / WEBSITE (MIN-WIDTH: 769px)
       ========================================================================== */
    @media (min-width: 769px) {

      body {
        flex-direction: row;
      }

      /* SIDEBAR KIRI */
      .navbar {
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        right: auto;
        width: 160px;
        height: 100vh;
        flex-direction: column;
        justify-content: flex-start;
        align-items: stretch;
        padding: 20px 0 20px 0;
        border-top-left-radius: 0;
        border-top-right-radius: 0;
        border-right: 1px solid #e0ecf8;
        box-shadow: 4px 0 15px rgba(0,0,0,0.05);
        gap: 4px;
      }

      /* Logo di sidebar */
      .sidebar-logo {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 800;
        color: #2a85ff;
        padding: 0 8px 18px 8px;
        border-bottom: 1px solid #e0ecf8;
        margin-bottom: 8px;
        text-align: center;
      }

      .nav-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 12px 8px;
        border-radius: 12px;
        margin: 2px 8px;
        font-size: 11px;
        font-weight: 600;
        flex: none;
        gap: 5px;
        transition: background 0.2s, color 0.2s;
      }

      .nav-item:hover { background: #eef5fc; color: #2a85ff; }
      .nav-item.active { background: #ddeeff; color: #2a85ff; }
      .nav-item.nav-logout { margin-top: auto; color: #e53935; }
      .nav-item.nav-logout:hover { background: #fff0f0; color: #c62828; }

      .nav-item i {
        font-size: 22px;
        display: block;
        margin-bottom: 0;
        width: auto;
      }

      /* Konten utama digeser ke kanan mengikuti lebar sidebar */
      .page-wrapper {
        margin-left: 160px;
        width: 100%;
      }

      .home {
        max-width: 1440px;
        padding: 30px 40px 40px 40px;
      }

      .header .title {
        font-size: 26px;
      }

      .search-box input {
        padding: 16px 16px 16px 55px;
        font-size: 16px;
      }

      .banner {
        border-radius: 20px;
        min-height: 220px;
      }

      .banner-text {
        padding: 40px 10px 40px 50px;
      }

      .banner-text h1 {
        font-size: 32px;
      }

      .banner-text p {
        font-size: 16px;
      }

      .banner-icon-container {
        width: 38%;
      }

      .banner-photo-card {
        width: 68%;
      }

      .banner-badge {
        width: 52px;
        height: 52px;
      }

      .banner-badge span {
        font-size: 21px;
      }

      /* DI LAPTOP: Blok produk terbaru dan rekomendasi ditaruh BERDAMPINGAN KIRI-KANAN */
      .section-container {
        flex-direction: row;
        gap: 24px;
      }

      /* DI LAPTOP: Grid di dalam blok berubah dari 2 kolom menjadi 3 kolom menyamping */
      .grid-produk {
        grid-template-columns: repeat(3, 1fr); 
        gap: 20px;
      }

      /* Grid khusus 5 kolom untuk mode filter / pencarian */
      .grid-produk.grid-5 {
        grid-template-columns: repeat(5, 1fr);
      }

      .card-item .item-name, .card-item .item-price {
        font-size: 14px;
      }
    }
  </style>
</head>
<body>

<div class="navbar">
  <div class="sidebar-logo">
    <img src="Logo.svg" alt="LokalThrift" style="width:140px; height:auto; display:block; margin:0 auto;">
  </div>
  <a href="home.php" class="nav-item active">
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
  <a href="chat.php" class="nav-item">
    <i class="fa-solid fa-message"></i><span>Chat</span>
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
<div class="home">

  <div class="header">
    <div class="title">Beranda</div>
    <a href="notifikasi.php" class="notif-btn" style="position:relative;text-decoration:none;">
      <i class="fa-regular fa-bell"></i>
      <?php if(isset($unreadCount) && $unreadCount > 0): ?>
        <span style="position:absolute;top:-2px;right:-4px;background:#e53935;color:white;font-size:9px;font-weight:bold;padding:1px 5px;border-radius:10px;"><?= $unreadCount ?></span>
      <?php endif; ?>
    </a>
  </div>

  <div class="search-box">
    <form method="GET" action="home.php" style="position:relative;width:100%">
      <?php if ($kategoriAktif !== 'Semua'): ?>
        <input type="hidden" name="kategori" value="<?= htmlspecialchars($kategoriAktif) ?>">
      <?php endif; ?>
      <i class="fa-solid fa-magnifying-glass"></i>
      <input type="text" name="q" placeholder="Cari produk thrift..."
             value="<?= htmlspecialchars($search) ?>">
    </form>
  </div>

  <div class="kategori">
    <?php foreach ($kategoriList as $k): ?>
      <a href="home.php?kategori=<?= urlencode($k) ?><?= $search ? '&q=' . urlencode($search) : '' ?>"
         class="item <?= $kategoriAktif === $k ? 'item-active' : '' ?>"><?= $k ?></a>
    <?php endforeach; ?>
  </div>

  <div class="banner">
    <div class="banner-text">
      <p class="tag">Diskon Spesial</p>
      <h1>Thrift Favorit Harga Hemat!</h1>
      <p>Diskon hingga 50% + Gratis Ongkir</p>
      <a href="#" class="btn-belanja">Belanja Sekarang</a>
    </div>
    <div class="banner-icon-container">
      <div class="banner-photo-card">
        <img src="https://images.unsplash.com/photo-1637228393246-c38a4b3d2011?w=500&q=80&auto=format&fit=crop" alt="Pakaian thrift">
      </div>
      <div class="banner-badge">
        <span>%</span>
      </div>
    </div>
  </div>

  <div class="section-container">

    <?php if ($search !== '' || $kategoriAktif !== 'Semua'): ?>
    <!-- ── MODE FILTER / SEARCH ── -->
    <div class="block-produk" style="flex:1">
      <div class="block-title">
        <?= $search ? 'Hasil pencarian "' . htmlspecialchars($search) . '"' : 'Kategori: ' . htmlspecialchars($kategoriAktif) ?>
        <span style="font-size:13px;font-weight:400;color:#8fa3b8;margin-left:8px"><?= count($produkFilter) ?> produk</span>
      </div>
      <?php if (empty($produkFilter)): ?>
        <div style="text-align:center;padding:50px;color:#aab">
          <i class="fa-solid fa-box-open" style="font-size:36px;color:#c8dff5;display:block;margin-bottom:10px"></i>
          Tidak ada produk ditemukan.
        </div>
      <?php else: ?>
      <div class="grid-produk grid-5">
        <?php foreach ($produkFilter as $p): ?>
          <a href="detail.php?id=<?= $p['id_produk'] ?>" class="card-item">
            <div class="img-wrapper">
              <img src="<?= !empty($p['gambar']) ? htmlspecialchars($p['gambar']) : '' ?>"
                   onerror="this.style.background='#ddeeff';this.removeAttribute('src')"
                   alt="<?= htmlspecialchars($p['nama']) ?>">
            </div>
            <div class="item-name"><?= htmlspecialchars($p['nama']) ?></div>
            <div class="item-price"><?= rupiah($p['harga']) ?></div>
          </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <?php else: ?>
    <!-- ── MODE NORMAL ── -->
    <div class="block-produk">
      <div class="block-title">Produk Terbaru</div>
      <?php if (empty($produkTerbaru)): ?>
        <div style="text-align:center;padding:50px;color:#aab">
          <i class="fa-solid fa-shirt" style="font-size:36px;color:#c8dff5;display:block;margin-bottom:10px"></i>
          Belum ada produk.
        </div>
      <?php else: ?>
      <div class="grid-produk" id="grid-terbaru">
        <?php $count = 0; foreach ($produkTerbaru as $p): $count++; ?>
          <a href="detail.php?id=<?= $p['id_produk'] ?>" class="card-item" <?= $count > 9 ? 'style="display:none;"' : '' ?>>
            <div class="img-wrapper">
              <img src="<?= !empty($p['gambar']) ? htmlspecialchars($p['gambar']) : '' ?>"
                   onerror="this.style.background='#ddeeff';this.removeAttribute('src')"
                   alt="<?= htmlspecialchars($p['nama']) ?>">
            </div>
            <div class="item-name"><?= htmlspecialchars($p['nama']) ?></div>
            <div class="item-price"><?= rupiah($p['harga']) ?></div>
          </a>
        <?php endforeach; ?>
      </div>
      <?php if (count($produkTerbaru) > 9): ?>
        <div style="text-align:center; margin-top:20px;">
          <button class="btn-show-more" onclick="showMore('grid-terbaru', this)">Tampilkan Lebih Banyak</button>
        </div>
      <?php endif; ?>
      <?php endif; ?>
    </div>

    <?php if (!empty($produkRekomendasi)): ?>
    <div class="block-produk">
      <div class="block-title">Rekomendasi Paling Disukai</div>
      <div class="grid-produk" id="grid-rekomendasi">
        <?php $count = 0; foreach ($produkRekomendasi as $p): $count++; ?>
          <a href="detail.php?id=<?= $p['id_produk'] ?>" class="card-item" <?= $count > 9 ? 'style="display:none;"' : '' ?>>
            <div class="img-wrapper">
              <img src="<?= !empty($p['gambar']) ? htmlspecialchars($p['gambar']) : '' ?>"
                   onerror="this.style.background='#ddeeff';this.removeAttribute('src')"
                   alt="<?= htmlspecialchars($p['nama']) ?>">
            </div>
            <div class="item-name"><?= htmlspecialchars($p['nama']) ?></div>
            <div class="item-price"><?= rupiah($p['harga']) ?></div>
          </a>
        <?php endforeach; ?>
      </div>
      <?php if (count($produkRekomendasi) > 9): ?>
        <div style="text-align:center; margin-top:20px;">
          <button class="btn-show-more" onclick="showMore('grid-rekomendasi', this)">Tampilkan Lebih Banyak</button>
        </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>

  </div>

</div>
</div>

</body>
<script>
  function showMore(gridId, btn) {
    const grid = document.getElementById(gridId);
    const items = grid.querySelectorAll('.card-item');
    
    let shownCount = 0;
    let remainingHidden = 0;
    
    items.forEach(item => {
      if (item.style.display === 'none') {
        if (shownCount < 9) {
          item.style.display = 'flex';
          shownCount++;
        } else {
          remainingHidden++;
        }
      }
    });

    if (remainingHidden === 0) {
      btn.style.display = 'none';
    }
  }
</script>
<?php if (isset($_SESSION['success'])) : ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
Swal.fire({
    icon: 'success',
    title: 'Berhasil',
    text: '<?= addslashes($_SESSION['success']); ?>',
    confirmButtonText: 'OK'
});
</script>
<?php unset($_SESSION['success']); ?>
<?php endif; ?>
</html>