<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Katalog Produk - LokalThrift</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Helvetica Neue', Arial, sans-serif; }
    body { background: #f4f8fc; min-height: 100vh; display: flex; justify-content: center; }
    .app-container { width: 100%; max-width: 1400px; background: white; min-height: 100vh; display: flex; flex-direction: column; box-shadow: 0 4px 20px rgba(0,0,0,0.02); }
    .top-nav { display: flex; justify-content: space-between; align-items: center; padding: 20px 30px; border-bottom: 1px solid #eef3f8; }
    .brand { display: flex; align-items: center; gap: 10px; text-decoration: none; }
    .brand i, .brand span { font-size: 22px; font-weight: 800; color: #2a85ff; }
    .search-container { position: relative; width: 50%; }
    .search-container input { width: 100%; padding: 12px 20px 12px 45px; border: 1px solid #e2edf7; border-radius: 30px; background: #f8fbfe; outline: none; font-size: 14px; }
    .search-container i { position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: #8fa0b5; }
    
    .nav-actions { display: flex; align-items: center; gap: 20px; }
    .nav-icon-link { color: #556980; font-size: 20px; text-decoration: none; position: relative; }
    .nav-icon-link:hover { color: #2a85ff; }
    
    /* STYLE BUBBLE DINAMIS */
    .badge-count { position: absolute; top: -7px; right: -8px; background: #2a85ff; color: white; font-size: 10px; font-weight: 700; width: 18px; height: 18px; border-radius: 50%; display: flex; justify-content: center; align-items: center; border: 2px solid white; }

    .main-layout { display: flex; flex: 1; }
    .sidebar { width: 260px; padding: 30px 25px; border-right: 1px solid #eef3f8; display: flex; flex-direction: column; gap: 25px; background: white; }
    .sidebar-section h3 { font-size: 14px; color: #7d8c9e; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 0.5px; }
    .cat-list { display: flex; flex-direction: column; gap: 8px; }
    .cat-item { display: flex; align-items: center; gap: 12px; padding: 10px 14px; color: #556980; text-decoration: none; font-size: 14px; font-weight: 500; border-radius: 12px; transition: all 0.2s; }
    .cat-item:hover, .cat-item.active { background: #eef5fc; color: #2a85ff; }
    .cat-item i { font-size: 16px; width: 20px; }

    .content-area { flex: 1; padding: 30px; }
    .banner { background: #d4ecff; border-radius: 20px; padding: 35px 50px; margin-bottom: 35px; display: flex; justify-content: space-between; align-items: center; }
    .banner-text h2 { font-size: 18px; color: #2a85ff; margin-bottom: 8px; }
    .banner-text h1 { font-size: 32px; font-weight: 800; color: #0d1c2e; margin-bottom: 20px; }
    .btn-blue { display: inline-block; padding: 12px 28px; background: #2a85ff; color: white; font-weight: bold; font-size: 14px; border-radius: 12px; text-decoration: none; }
    .banner-img-wrapper { width: 40%; height: 180px; display: flex; justify-content: center; align-items: center; overflow: hidden; border-radius: 12px; }
    .banner-img-wrapper img { width: 100%; height: 100%; object-fit: cover; }

    .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
    .section-header h2 { font-size: 20px; font-weight: bold; color: #0d1c2e; }
    .products-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 25px; }
    .product-card { background: white; border-radius: 16px; overflow: hidden; display: flex; flex-direction: column; text-decoration: none; color: inherit; transition: transform 0.2s; }
    .product-card:hover { transform: translateY(-4px); }
    .product-img-wrapper { width: 100%; aspect-ratio: 1 / 1; background: #f8fbfe; border-radius: 16px; overflow: hidden; position: relative; }
    .product-img-wrapper img { width: 100%; height: 100%; object-fit: cover; }
    
    .wishlist-btn { position: absolute; top: 15px; right: 15px; width: 34px; height: 34px; background: white; border: none; border-radius: 50%; display: flex; justify-content: center; align-items: center; color: #8fa0b5; z-index: 10; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,0.05); transition: 0.2s; }
    .wishlist-btn.is-fav i { color: #e53e3e; font-weight: 900; }

    .product-info { padding: 12px 5px; }
    .product-title { font-size: 15px; color: #556980; margin-bottom: 4px; }
    .product-price { font-size: 16px; font-weight: bold; color: #2a85ff; }
    .no-results { grid-column: span 3; text-align: center; padding: 40px; color: #7d8c9e; display: none; }
  </style>
</head>
<body>

<div class="app-container">
  <div class="top-nav">
    <a href="/web-baru" class="brand"><i class="fa-solid fa-cloud-bolt"></i> <span>LokalThrift</span></a>
    <div class="search-container">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input type="text" id="search-input" placeholder="Cari barang thrift favoritmu..." onkeyup="cariProduk()">
    </div>
    <div class="nav-actions">
      <a href="/pesanan" class="nav-icon-link" title="Pesanan Saya"><i class="fa-solid fa-receipt"></i></a>
      
      <!-- BUBBLE SINKRON: Menghitung total jenis item unik di session cart -->
      <a href="/keranjang" class="nav-icon-link" title="Keranjang Belanja">
        <i class="fa-solid fa-cart-shopping"></i>
        <span class="badge-count" id="cart-bubble-count"><?= count(session('cart', [])) ?></span>
      </a>
      
      <a href="/akun" class="nav-icon-link" title="Profil Akun"><i class="fa-regular fa-user"></i></a>
    </div>
  </div>

  <div class="main-layout">
    <div class="sidebar">
      <div class="sidebar-section">
        <h3>Kategori</h3>
        <div class="cat-list">
          <a href="/web-baru" class="cat-item active"><i class="fa-solid fa-border-all"></i> Semua</a>
          <a href="/favorit" class="cat-item"><i class="fa-solid fa-heart" style="color: #e53e3e;"></i> Favorit</a>
          <a href="#" class="cat-item"><i class="fa-solid fa-shirt"></i> Atasan</a>
          <a href="#" class="cat-item"><i class="fa-solid fa-socks"></i> Bawahan</a>
          <a href="#" class="cat-item"><i class="fa-solid fa-user-tie"></i> Outer</a>
          <a href="#" class="cat-item"><i class="fa-solid fa-person-dress"></i> Dress</a>
        </div>
      </div>
      <div class="sidebar-section">
        <h3>Fitur Toko</h3>
        <div class="cat-list">
          <a href="/dashboard-penjual" class="cat-item"><i class="fa-solid fa-store"></i> Dashboard Toko</a>
        </div>
      </div>
    </div>

    <div class="content-area">
      <div class="banner" id="promo-banner">
        <div class="banner-text">
          <h2>Special Today 💙</h2>
          <h1>Diskon hingga 50%</h1>
          <a href="#" class="btn-blue">Belanja Sekarang</a>
        </div>
        <div class="banner-img-wrapper">
          <img src="https://images.unsplash.com/photo-1620799140408-edc6dcb6d633?w=500" alt="Special Today">
        </div>
      </div>

      <div class="section-header"><h2>Semua Produk</h2></div>

      <div class="products-grid" id="products-container">
        <div class="no-results" id="no-results-message"><i class="fa-regular fa-folder-open" style="font-size:30px; margin-bottom:10px; display:block;"></i>Produk tidak ditemukan.</div>

        <?php $favs = session('favorites', []); ?>

        <!-- Produk 1 -->
        <div class="product-card" data-id="1">
          <div class="product-img-wrapper">
            <a href="/detail?id=1"><img src="https://images.unsplash.com/photo-1576566588028-4147f3842f27?w=300" alt="Item"></a>
            <button class="wishlist-btn <?= isset($favs['1']) ? 'is-fav' : '' ?>" onclick="toggleFavorit('1', 'Vintage Denim Jacket', 125000, 'https://images.unsplash.com/photo-1576566588028-4147f3842f27?w=300', this)">
              <i class="fa-<?= isset($favs['1']) ? 'solid' : 'regular' ?> fa-heart"></i>
            </button>
          </div>
          <div class="product-info">
            <div class="product-title">Vintage Denim Jacket</div>
            <div class="product-price">Rp 125.000</div>
          </div>
        </div>

        <!-- Produk 2 -->
        <div class="product-card" data-id="2">
          <div class="product-img-wrapper">
            <a href="/detail?id=2"><img src="https://images.unsplash.com/photo-1620799140408-edc6dcb6d633?w=300" alt="Item"></a>
            <button class="wishlist-btn <?= isset($favs['2']) ? 'is-fav' : '' ?>" onclick="toggleFavorit('2', 'Crewneck Michigan State', 85000, 'https://images.unsplash.com/photo-1620799140408-edc6dcb6d633?w=300', this)">
              <i class="fa-<?= isset($favs['2']) ? 'solid' : 'regular' ?> fa-heart"></i>
            </button>
          </div>
          <div class="product-info">
            <div class="product-title">Crewneck Michigan State</div>
            <div class="product-price">Rp 85.000</div>
          </div>
        </div>

        <!-- Produk 3 -->
        <div class="product-card" data-id="3">
          <div class="product-img-wrapper">
            <a href="/detail?id=3"><img src="https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=300" alt="Item"></a>
            <button class="wishlist-btn <?= isset($favs['3']) ? 'is-fav' : '' ?>" onclick="toggleFavorit('3', 'Kemeja Flanel Casual', 75000, 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=300', this)">
              <i class="fa-<?= isset($favs['3']) ? 'solid' : 'regular' ?> fa-heart"></i>
            </button>
          </div>
          <div class="product-info">
            <div class="product-title">Kemeja Flanel Casual</div>
            <div class="product-price">Rp 75.000</div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
  function toggleFavorit(id, nama, harga, gambar, element) {
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    fetch('/toggle-favorit', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
      body: JSON.stringify({ id, nama, harga, gambar })
    })
    .then(res => res.json())
    .then(data => {
      if(data.status === 'success') {
        const icon = element.querySelector('i');
        if(data.action === 'added') {
          element.classList.add('is-fav');
          icon.className = 'fa-solid fa-heart';
        } else {
          element.classList.remove('is-fav');
          icon.className = 'fa-regular fa-heart';
        }
      }
    });
  }

  function cariProduk() {
    const kataKunci = document.getElementById('search-input').value.toLowerCase();
    const kartuProduk = document.querySelectorAll('.product-card');
    const pesanKosong = document.getElementById('no-results-message');
    const bannerPromo = document.getElementById('promo-banner');
    
    bannerPromo.style.display = kataKunci.trim() !== "" ? "none" : "flex";
    let adaProdukCocok = false;

    kartuProduk.forEach(card => {
      const namaProduk = card.querySelector('.product-title').innerText.toLowerCase();
      if (namaProduk.includes(kataKunci)) {
        card.style.display = "flex";
        adaProdukCocok = true;
      } else {
        card.style.display = "none";
      }
    });
    pesanKosong.style.display = adaProdukCocok ? "none" : "block";
  }
</script>
</body>
</html>