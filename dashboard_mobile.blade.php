<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard LokalThrift - Responsive</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    *{
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Helvetica Neue', Arial, sans-serif;
    }

    body {
      background: #eef5fc; 
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    /* CONTAINER UTAMA (Adaptif mengikuti layar) */
    .dashboard {
      width: 100%;
      max-width: 1400px; 
      margin: 0 auto;
      padding: 20px 16px 100px 16px; /* Padding bawah untuk space navbar mobile */
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
    }

    /* BANNER PROMO */
    .banner {
      background: #bce3ff;
      border-radius: 16px;
      padding: 20px;
      margin-bottom: 25px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: relative;
      overflow: hidden;
    }

    .banner-text {
      max-width: 65%;
    }

    .banner-text .tag {
      font-size: 11px;
      font-weight: bold;
      text-transform: uppercase;
      color: #333;
    }

    .banner-text h1 {
      font-size: 20px;
      font-weight: 800;
      margin: 4px 0;
      color: #000;
      line-height: 1.2;
    }

    .banner-text p {
      font-size: 12px;
      color: #444;
      margin-bottom: 10px;
    }

    .banner-text .btn-belanja {
      display: inline-block;
      padding: 8px 16px;
      background: #2a85ff;
      color: white;
      font-size: 12px;
      font-weight: bold;
      border-radius: 25px;
      text-decoration: none;
    }

    .banner-img-container {
      position: relative;
      width: 35%;
      height: 100px;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .banner-img {
      max-height: 100px;
      object-fit: contain;
    }

    .badge-sale {
      position: absolute;
      right: 0px;
      bottom: -5px;
      background: #fff000;
      color: #000;
      font-size: 10px;
      font-weight: 900;
      padding: 6px;
      border-radius: 50%;
      transform: rotate(-15deg);
      border: 1px dashed #000;
      text-align: center;
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
      padding: 12px 0;
      border-top-left-radius: 20px;
      border-top-right-radius: 20px;
      box-shadow: 0 -4px 15px rgba(0,0,0,0.05);
      z-index: 999;
    }

    .nav-item {
      text-align: center;
      font-size: 12px;
      font-weight: bold;
      color: #777; 
      text-decoration: none; 
      flex: 1;
    }

    .nav-item i {
      font-size: 20px;
      margin-bottom: 4px;
      display: inline-block;
    }

    .nav-item.active {
      color: #2a85ff; /* Menu aktif berwarna biru */
    }


    /* ==========================================================================
       MEDIA QUERY: ATURAN KHUSUS SAAT DIBUKA DI LAYAR LAPTOP / WEBSITE (MIN-WIDTH: 769px)
       ========================================================================== */
    @media (min-width: 769px) {
      .dashboard {
        padding: 30px 40px 40px 40px; /* Sisi laptop tidak butuh jarak navbar bawah yang besar */
      }

      .header .title {
        font-size: 26px;
      }

      .search-box input {
        padding: 16px 16px 16px 55px;
        font-size: 16px;
      }

      .banner {
        padding: 30px 50px;
        border-radius: 20px;
      }

      .banner-text h1 {
        font-size: 32px;
      }

      .banner-text p {
        font-size: 16px;
      }

      .banner-img-container {
        height: 140px;
      }

      .banner-img {
        max-height: 150px;
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

      .card-item .item-name, .card-item .item-price {
        font-size: 14px;
      }

      /* DI LAPTOP: Navbar bawah ditaruh proporsional mengikuti lebar website */
      .navbar {
        max-width: 1400px;
        margin: 0 auto;
        border-radius: 25px 25px 0 0;
      }
    }
  </style>
</head>
<body>

<div class="dashboard">

  <div class="header">
    <div class="title"><i class="fa-solid fa-cloud-bolt"></i> LokalThrift</div>
    <div class="notif-btn"><i class="fa-regular fa-bell"></i></div>
  </div>

  <div class="search-box">
    <i class="fa-solid fa-magnifying-glass"></i>
    <input type="text" placeholder="Cari produk thrift...">
  </div>

  <div class="kategori">
    <div class="item">Casual</div>
    <div class="item">Vintage</div>
    <div class="item">Sport</div>
    <div class="item">Denim</div>
    <div class="item">Outwear</div>
  </div>

  <div class="banner">
    <div class="banner-text">
      <p class="tag">Diskon Spesial</p>
      <h1>Thrift Favorit Harga Hemat!</h1>
      <p>Diskon hingga 50% + Gratis Ongkir</p>
      <a href="#" class="btn-belanja">Belanja Sekarang</a>
    </div>
    <div class="banner-img-container">
      <img class="banner-img" src="https://images.unsplash.com/photo-1540221652346-e5dd6b50f3e7?w=300" alt="Promo">
      <div class="badge-sale">sale<br>50%<br>off</div>
    </div>
  </div>

  <div class="section-container">
    
    <div class="block-produk">
      <div class="block-title">Produk Terbaru</div>
      <div class="grid-produk">
        
        <a href="/detail?id=1" class="card-item">
          <div class="img-wrapper">
            <img src="https://images.unsplash.com/photo-1576566588028-4147f3842f27?w=200" alt="Produk 1">
          </div>
          <div class="item-name">nama</div>
          <div class="item-price">Rp150.000</div>
        </a>

        <a href="/detail?id=2" class="card-item">
          <div class="img-wrapper">
            <img src="https://images.unsplash.com/photo-1620799140408-edc6dcb6d633?w=200" alt="Produk 2">
          </div>
          <div class="item-name">nama</div>
          <div class="item-price">Rp75.000</div>
        </a>

        <a href="/detail?id=3" class="card-item">
          <div class="img-wrapper">
            <img src="https://images.unsplash.com/photo-1603252109303-2751441dd157?w=200" alt="Produk 3">
          </div>
          <div class="item-name">nama</div>
          <div class="item-price">Rp50.000</div>
        </a>

      </div>
    </div>

    <div class="block-produk">
      <div class="block-title">Rekomendasi</div>
      <div class="grid-produk">
        
        <a href="/detail?id=4" class="card-item">
          <div class="img-wrapper">
            <img src="https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?w=200" alt="Produk 4">
          </div>
          <div class="item-name">nama</div>
          <div class="item-price">Rp120.000</div>
        </a>

        <a href="/detail?id=5" class="card-item">
          <div class="img-wrapper">
            <img src="https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=200" alt="Produk 5">
          </div>
          <div class="item-name">nama</div>
          <div class="item-price">Rp80.000</div>
        </a>

        <a href="/detail?id=6" class="card-item">
          <div class="img-wrapper">
            <img src="https://images.unsplash.com/photo-1554568218-0f1715e72254?w=200" alt="Produk 6">
          </div>
          <div class="item-name">nama</div>
          <div class="item-price">Rp40.000</div>
        </a>

      </div>
    </div>

  </div>

</div>

<div class="navbar">
  <a href="/" class="nav-item active">
    <i class="fa-solid fa-house"></i><br>Beranda
  </a>
  <a href="/keranjang" class="nav-item">
    <i class="fa-solid fa-cart-shopping"></i><br>Keranjang
  </a>
  <a href="/akun" class="nav-item">
    <i class="fa-solid fa-user"></i><br>Akun
  </a>
</div>

</body>
</html>