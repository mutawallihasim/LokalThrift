<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Akun Saya - LokalThrift</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Plus Jakarta Sans', sans-serif;
    }

    body {
      background-color: #f4f8fc;
      color: #0d1c2e;
      min-height: 100vh;
    }

    .top-nav {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 30px;
      background-color: #ffffff;
      border-bottom: 1px solid #eef2f7;
      position: sticky;
      top: 0;
      z-index: 100;
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 10px;
      text-decoration: none;
      color: #2a85ff;
      font-weight: 800;
      font-size: 22px;
    }

    .search-container {
      position: relative;
      width: 40%;
    }

    .search-container input {
      width: 100%;
      padding: 11px 20px 11px 45px;
      border: 1px solid #e2edf7;
      border-radius: 30px;
      background: #f8fbfe;
      outline: none;
      font-size: 14px;
    }

    .search-container i {
      position: absolute;
      left: 18px;
      top: 50%;
      transform: translateY(-50%);
      color: #8fa0b5;
    }

    .nav-actions {
      display: flex;
      align-items: center;
      gap: 20px;
    }

    .nav-icon-link {
      color: #556980;
      font-size: 18px;
      text-decoration: none;
      position: relative;
    }

    .nav-icon-link:hover {
      color: #2a85ff;
    }

    /* STYLE BUBBLE DINAMIS */
    .badge-count {
      position: absolute;
      top: -7px;
      right: -8px;
      background: #2a85ff;
      color: white;
      font-size: 10px;
      font-weight: 700;
      width: 16px;
      height: 16px;
      border-radius: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
      border: 2px solid white;
    }

    .user-pill {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 5px 12px;
      background: #f4f8fc;
      border-radius: 20px;
      text-decoration: none;
      color: inherit;
      font-size: 13px;
    }

    .user-avatar-mini {
      width: 28px;
      height: 28px;
      border-radius: 50%;
      background: #2a85ff;
      color: white;
      display: flex;
      justify-content: center;
      align-items: center;
      font-weight: bold;
    }

    .main-layout {
      display: flex;
      width: 100%;
      max-width: 1440px;
      margin: 0 auto;
      min-height: calc(100vh - 63px);
    }

    .sidebar {
      width: 260px;
      background: white;
      border-right: 1px solid #eef2f7;
      padding: 30px 20px;
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .menu-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 16px;
      color: #556980;
      text-decoration: none;
      font-size: 14px;
      font-weight: 500;
      border-radius: 12px;
      transition: 0.2s;
    }

    .menu-item:hover,
    .menu-item.active {
      background: #eef5fc;
      color: #2a85ff;
      font-weight: 600;
    }

    .menu-item i {
      font-size: 16px;
      width: 20px;
    }

    .content-area {
      flex: 1;
      padding: 35px 40px;
      display: flex;
      flex-direction: column;
      gap: 25px;
    }

    .breadcrumb {
      font-size: 13px;
      color: #7d8c9e;
      margin-bottom: -15px;
    }

    .breadcrumb a {
      color: #7d8c9e;
      text-decoration: none;
    }

    .page-title {
      font-size: 22px;
      font-weight: 700;
      color: #0d1c2e;
    }

    .grid-container {
      display: grid;
      grid-template-columns: 1.7fr 1fr;
      gap: 25px;
    }

    @media (max-width: 1024px) {
      .grid-container {
        grid-template-columns: 1fr;
      }
    }

    .card-panel {
      background: white;
      border-radius: 16px;
      border: 1px solid #eef2f7;
      padding: 25px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.01);
    }

    .profile-header-card {
      display: flex;
      align-items: center;
      gap: 25px;
      position: relative;
      background: linear-gradient(135deg, #ffffff 60%, #f0f7ff 100%);
    }

    .big-avatar-wrapper {
      width: 90px;
      height: 90px;
      border-radius: 50%;
      background: #eef5fc;
      border: 3px solid #2a85ff;
      display: flex;
      justify-content: center;
      align-items: center;
      position: relative;
    }

    .big-avatar-wrapper i {
      font-size: 40px;
      color: #2a85ff;
    }

    .btn-camera {
      position: absolute;
      bottom: 0;
      right: 0;
      width: 28px;
      height: 28px;
      background: white;
      border: 1px solid #e2edf7;
      border-radius: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
      color: #556980;
      font-size: 12px;
      cursor: pointer;
    }

    .profile-meta h2 {
      font-size: 20px;
      font-weight: 700;
      margin-bottom: 4px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .badge-role {
      font-size: 11px;
      font-weight: 700;
      color: #2a85ff;
      background: #eef5fc;
      padding: 2px 10px;
      border-radius: 10px;
    }

    .profile-join {
      font-size: 13px;
      color: #7d8c9e;
      margin-bottom: 12px;
    }

    .profile-bio {
      font-size: 13px;
      color: #556980;
      font-style: italic;
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .profile-location {
      font-size: 13px;
      color: #556980;
      margin-top: 12px;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .summary-card h3,
    .info-card h3 {
      font-size: 15px;
      font-weight: 700;
      margin-bottom: 20px;
      color: #0d1c2e;
    }

    .summary-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 0;
      border-bottom: 1px solid #f4f8fc;
      font-size: 13px;
    }

    .summary-row:last-child {
      border-bottom: none;
      padding-bottom: 0;
    }

    .summary-label {
      display: flex;
      align-items: center;
      gap: 10px;
      color: #556980;
    }

    .summary-value {
      font-weight: 700;
      color: #0d1c2e;
    }

    .info-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 10px;
    }

    .info-table td {
      padding: 14px 0;
      font-size: 13px;
      border-bottom: 1px solid #f4f8fc;
    }

    .info-table tr:last-child td {
      border-bottom: none;
    }

    .td-label {
      width: 30%;
      color: #7d8c9e;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .td-value {
      color: #0d1c2e;
      font-weight: 500;
    }

    .card-footer-action {
      display: flex;
      justify-content: flex-end;
      margin-top: 15px;
    }

    .btn-light {
      padding: 10px 20px;
      background: white;
      border: 1px solid #e2edf7;
      border-radius: 10px;
      color: #2a85ff;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
    }

    .right-bottom-box {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .block-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
    }

    .block-header h4 {
      font-size: 14px;
      font-weight: 700;
      color: #0d1c2e;
    }

    .link-change {
      font-size: 13px;
      font-weight: 700;
      color: #2a85ff;
      text-decoration: none;
    }

    .address-text {
      font-size: 13px;
      color: #556980;
      line-height: 1.5;
    }

    .payment-box {
      display: flex;
      align-items: center;
      justify-content: space-between;
      border: 1px solid #e2edf7;
      padding: 12px 15px;
      border-radius: 10px;
      background: #f8fbfe;
      font-size: 13px;
    }

    .bank-brand {
      display: flex;
      align-items: center;
      gap: 10px;
      font-weight: 700;
      color: #0d1c2e;
    }

    .bank-brand span {
      font-style: italic;
      color: #0056b3;
      font-size: 14px;
    }

    .badge-primary-pay {
      font-size: 11px;
      font-weight: 700;
      color: #10b981;
      background: #ebf9f1;
      padding: 2px 8px;
      border-radius: 6px;
    }

    .security-card {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .security-info {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .security-info i {
      font-size: 24px;
      color: #2a85ff;
    }

    .security-text h4 {
      font-size: 14px;
      font-weight: 700;
      color: #0d1c2e;
      margin-bottom: 4px;
    }

    .security-text p {
      font-size: 12px;
      color: #7d8c9e;
    }

    .footer-cr {
      text-align: center;
      font-size: 12px;
      color: #a0aec0;
      margin-top: 15px;
    }
  </style>
</head>

<body>

  <div class="top-nav">
    <a href="/web-baru" class="brand"><i class="fa-solid fa-cloud-bolt"></i> <span>LokalThrift</span></a>
    <div class="search-container">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input type="text" placeholder="Cari barang thrift favoritmu...">
    </div>

    <div class="nav-actions">
      <!-- BUBBLE SINKRON: Menghitung total jenis item unik di session cart -->
      <a href="/keranjang" class="nav-icon-link" title="Keranjang Belanja">
        <i class="fa-solid fa-cart-shopping"></i>
        <span class="badge-count"><?= count(session('cart', [])) ?></span>
      </a>
      <a href="#" class="nav-icon-link" title="Notifikasi"><i class="fa-regular fa-comment-dots"></i></a>
      <a href="#" class="nav-icon-link" title="Pesan Obrolan"><i class="fa-regular fa-bell"></i><span class="badge-count" style="background:#ff5252; width:8px; height:8px; top:-1px; right:-1px;"></span></a>

      <a href="/akun" class="user-pill">
        <div class="user-avatar-mini">S</div>
        <span style="font-weight: 600;">sophiaa</span>
        <i class="fa-solid fa-chevron-down" style="font-size: 10px; color:#7d8c9e;"></i>
      </a>
    </div>
  </div>

  <div class="main-layout">
    <div class="sidebar">
      <a href="/web-baru" class="menu-item"><i class="fa-solid fa-house"></i> Beranda</a>
      <a href="/pesanan" class="menu-item"><i class="fa-solid fa-receipt"></i> Pesanan Saya</a>
      <a href="/favorit" class="menu-item"><i class="fa-solid fa-heart"></i> Wishlist</a>
      <a href="#" class="menu-item"><i class="fa-solid fa-ticket"></i> Voucher Saya</a>
      <a href="/pesanan" class="menu-item"><i class="fa-regular fa-star"></i> Ulasan Saya</a>
      <a href="/akun" class="menu-item active"><i class="fa-regular fa-user"></i> Akun Saya</a>
      <a href="/login" class="menu-item" style="margin-top: 20px; color: #ff5252;"><i class="fa-solid fa-arrow-right-from-bracket"></i> Keluar</a>
    </div>

    <div class="content-area">
      <div class="breadcrumb"><a href="/web-baru">Beranda</a> &gt; <span>Akun Saya</span></div>
      <h2 class="page-title">Akun Saya</h2>

      <div class="grid-container">
        <div style="display: flex; flex-direction: column; gap: 25px;">
          <div class="card-panel profile-header-card">
            <div class="big-avatar-wrapper">
              <i class="fa-solid fa-user"></i>
              <button class="btn-camera" onclick="alert('Fitur unggah foto profil segera aktif.')"><i class="fa-solid fa-camera"></i></button>
            </div>
            <div class="profile-meta">
              <h2>sophiaa <span class="badge-role">Pembeli</span></h2>
              <p class="profile-join">Bergabung sejak 12 Januari 2024</p>
              <p class="profile-bio">Loves thrift shopping ✨💛</p>
              <p class="profile-location"><i class="fa-solid fa-location-dot"></i> Bandung, Jawa Barat</p>
            </div>
          </div>

          <div class="card-panel info-card">
            <h3>Informasi Account</h3>
            <table class="info-table">
              <tr>
                <td class="td-label"><i class="fa-regular fa-user"></i> Username</td>
                <td class="td-value">sophiaa</td>
              </tr>
              <tr>
                <td class="td-label"><i class="fa-regular fa-envelope"></i> Email</td>
                <td class="td-value">sophiaa.lokal@gmail.com</td>
              </tr>
              <tr>
                <td class="td-label"><i class="fa-solid fa-phone"></i> Nomor Telepon</td>
                <td class="td-value">+62 812 3456 7890</td>
              </tr>
              <tr>
                <td class="td-label"><i class="fa-solid fa-venus-mars"></i> Jenis Kelamin</td>
                <td class="td-value">Perempuan</td>
              </tr>
              <tr>
                <td class="td-label"><i class="fa-regular fa-calendar-days"></i> Tanggal Lahir</td>
                <td class="td-value">12 Maret 1998</td>
              </tr>
            </table>
            <div class="card-footer-action">
              <button class="btn-light" onclick="alert('Form ubah biodata akun dibuka.')">Ubah Informasi</button>
            </div>
          </div>
        </div>

        <div class="right-bottom-box">
          <div class="card-panel summary-card">
            <h3>Ringkasan Akun</h3>
            <div class="summary-row">
              <span class="summary-label"><i class="fa-solid fa-bag-shopping" style="color:#2a85ff;"></i> Total Pesanan</span>
              <span class="summary-value">28</span>
            </div>
            <div class="summary-row">
              <span class="summary-label"><i class="fa-solid fa-circle-check" style="color:#10b981;"></i> Pesanan Selesai</span>
              <span class="summary-value">24</span>
            </div>
            <div class="summary-row">
              <span class="summary-label"><i class="fa-solid fa-truck" style="color:#ffa800;"></i> Pesanan Dikirim</span>
              <span class="summary-value">3</span>
            </div>
            <div class="summary-row">
              <span class="summary-label"><i class="fa-regular fa-star" style="color:#ffc107;"></i> Total Ulasan</span>
              <span class="summary-value">15</span>
            </div>
            <div class="summary-row">
              <span class="summary-label"><i class="fa-regular fa-heart" style="color:#ff5252;"></i> Wishlist</span>
              <span class="summary-value">12</span>
            </div>
          </div>

          <div class="card-panel">
            <div class="block-header">
              <h4>Alamat Utama</h4>
              <a href="#" class="link-change">Ubah</a>
            </div>
            <p class="address-text" style="font-weight: 700; color:#0d1c2e; margin-bottom:4px;"><i class="fa-solid fa-location-dot" style="color:#7d8c9e; margin-right:4px;"></i> Rumah</p>
            <p class="address-text">Jl. Mawar No.12, Kec. Coblong,<br>Kota Bandung, Jawa Barat 40132<br>Indonesia</p>
            <p class="address-text" style="margin-top: 8px; font-size:12px;">Kode Pos: <strong>40132</strong></p>
          </div>

          <div class="card-panel">
            <div class="block-header">
              <h4>Metode Pembayaran Utama</h4>
              <a href="#" class="link-change">Ubah</a>
            </div>
            <div class="payment-box">
              <div class="bank-brand">
                <i class="fa-solid fa-credit-card" style="color:#0056b3;"></i> <span>BCA</span> BCA **** 1234
              </div>
              <span class="badge-primary-pay">Utama</span>
            </div>
          </div>
        </div>
      </div>

      <div class="card-panel security-card">
        <div class="security-info">
          <i class="fa-solid fa-shield-halved"></i>
          <div class="security-text">
            <h4>Keamanan Akun</h4>
            <p>Jaga keamanan akunmu dengan mengupdate password secara berkala.</p>
          </div>
        </div>
        <button class="btn-light">Ubah Password</button>
      </div>

      <p class="footer-cr">© 2026 LokalThrift. All rights reserved.</p>
    </div>
  </div>
</body>

</html>