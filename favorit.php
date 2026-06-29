<?php
session_start();
if (!isset($_SESSION['id_pengguna'])) { header('Location: login.php'); exit; }
require 'koneksi.php';

$id_pengguna = (int)$_SESSION['id_pengguna'];
$favoriteItems = [];

// Buat tabel jika belum ada
mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS favorit (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pengguna INT NOT NULL,
    id_produk INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$qF = mysqli_query($koneksi, "SELECT p.* FROM favorit f JOIN produk p ON f.id_produk = p.id_produk WHERE f.id_pengguna=$id_pengguna ORDER BY f.created_at DESC");
if ($qF) {
    while ($r = mysqli_fetch_assoc($qF)) {
        $favoriteItems[$r['id_produk']] = $r;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Favorit Saya - LokalThrift</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2 family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
    body { background-color: #f4f8fc; color: #0d1c2e; min-height: 100vh; display: flex; flex-direction: column; }

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
        width: 160px; height: 100vh;
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

      .page-wrapper { margin-left: 160px; width: 100%; }
    }

    .page-wrapper { display: flex; flex: 1; flex-direction: column; }
    .main-container { width: 90%; max-width: 1200px; margin: 30px auto; padding-bottom: 100px; }
    .btn-back { display: inline-flex; align-items: center; gap: 8px; color: #556980; text-decoration: none; font-size: 14px; font-weight: 600; margin-bottom: 20px; }
    .page-title { font-size: 22px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
    .page-title i { color: #e53e3e; }

    .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 25px; }
    .product-card { background: white; border-radius: 16px; overflow: hidden; display: flex; flex-direction: column; text-decoration: none; color: inherit; border: 1px solid #eef2f7; position: relative; }
    .product-img-wrapper { width: 100%; aspect-ratio: 1 / 1; background: #f8fbfe; overflow: hidden; position: relative; }
    .product-img-wrapper img { width: 100%; height: 100%; object-fit: cover; }
    
    .btn-remove-fav { position: absolute; top: 15px; right: 15px; width: 34px; height: 34px; background: white; border: none; border-radius: 50%; display: flex; justify-content: center; align-items: center; color: #e53e3e; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }

    .product-info { padding: 15px; }
    .product-title { font-size: 15px; color: #556980; margin-bottom: 6px; font-weight: 500; }
    .product-price { font-size: 16px; font-weight: bold; color: #2a85ff; }

    .empty-state { text-align: center; padding: 60px 20px; background: white; border-radius: 16px; border: 1px solid #eef2f7; color: #7d8c9e; width: 100%; grid-column: 1 / -1; }
    .empty-state i { font-size: 48px; color: #ccd6e0; margin-bottom: 15px; }
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
  <a href="cek_toko.php" class="nav-item">
    <i class="fa-solid fa-shop"></i><span>Toko</span>
  </a>
  <a href="akun.php" class="nav-item active">
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
  <div class="main-container">
    <a href="akun.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Kembali ke Katalog</a>
    <h2 class="page-title"><i class="fa-solid fa-heart"></i> Produk Favorit Saya</h2>

    <div class="products-grid">
      <?php if(empty($favoriteItems)): ?>
        <div class="empty-state">
          <i class="fa-solid fa-heart-crack"></i>
          <p>Belum ada produk favorit. Klik ikon hati pada barang thrift yang kamu suka!</p>
        </div>
      <?php else: ?>
        <?php foreach($favoriteItems as $id => $item): ?>
          <div class="product-card" id="fav-card-<?= $id ?>">
            <div class="product-img-wrapper">
              <a href="detail.php?id=<?= $id ?>"><img src="<?= $item['gambar'] ?>" alt="Produk"></a>
              <button class="btn-remove-fav" onclick="hapusFavorit('<?= $id ?>')" title="Hapus dari Favorit">
                <i class="fa-solid fa-heart"></i>
              </button>
            </div>
            <div class="product-info">
              <h4 class="product-title"><?= htmlspecialchars($item['nama']) ?></h4>
              <div class="product-price">Rp <?= number_format($item['harga'], 0, ',', '.') ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div><!-- /.main-container -->
</div><!-- /.page-wrapper -->

  <script>
    function hapusFavorit(id) {
      fetch('api_toggle_favorit.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
      })
      .then(res => res.json())
      .then(data => {
        if(data.status === 'success') {
          // Hapus card dari layar tanpa reload
          const card = document.getElementById(`fav-card-${id}`);
          if (card) card.remove();
          // Jika sudah habis, reload biar memicu tampilan empty state
          if(data.count === 0) {
              location.reload();
          }
        }
      });
    }
  </script>
</body>
</html>