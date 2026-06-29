<?php
session_start();
if (!isset($_SESSION['id_pengguna'])) {
    header('Location: login.php');
    exit;
}

require_once 'koneksi.php';
$id_pengguna = (int)$_SESSION['id_pengguna'];

// Mark all unread notifications as read when this page is opened
mysqli_query($koneksi, "UPDATE notifikasi SET is_read=1 WHERE id_pengguna=$id_pengguna AND is_read=0");

// Fetch notifications
$qNotif = mysqli_query($koneksi, "SELECT * FROM notifikasi WHERE id_pengguna=$id_pengguna ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Notifikasi — LokalThrift</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Helvetica Neue', Arial, sans-serif; }
    body { background: #eef5fc; color: #333; padding-bottom: 80px; }

    /* NAVBAR KIRI UNTUK DESKTOP */
    .navbar {
      display: none;
    }

    @media (min-width: 769px) {
      .navbar {
        display: flex;
        flex-direction: column;
        position: fixed;
        left: 0;
        top: 0;
        bottom: 0;
        width: 140px;
        background: #fff;
        border-right: 1px solid #d4e3f3;
        z-index: 1000;
        padding-top: 20px;
      }
      .page-wrapper {
        margin-left: 140px;
        max-width: 1000px;
        margin-right: auto;
      }
      .navbar-bottom {
        display: none !important;
      }
      .nav-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        padding: 15px 0;
        color: #8fa3b8;
        text-decoration: none;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: 0.2s;
      }
      .nav-item i { font-size: 20px; }
      .nav-item:hover { color: #2a85ff; background: #f8fbfe; }
      .nav-item.active { color: #2a85ff; border-left: 3px solid #2a85ff; background: #f0f6fc; }
      .nav-logout { margin-top: auto; color: #e53935; margin-bottom: 20px; }
      .nav-logout:hover { color: #c62828; background: #fff0f0; }
      .sidebar-logo {
        padding: 0 10px 20px;
        margin-bottom: 10px;
        border-bottom: 1px solid #f0f6fc;
      }
    }

    /* NAVBAR BAWAH MOBILE */
    .navbar-bottom {
      position: fixed;
      bottom: 0; left: 0; right: 0;
      background: white;
      display: flex;
      justify-content: space-around;
      padding: 12px 0;
      border-top: 1px solid #d4e3f3;
      z-index: 1000;
      box-shadow: 0 -2px 10px rgba(0,0,0,0.03);
    }
    .navbar-bottom .nav-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 5px;
      color: #8fa3b8;
      text-decoration: none;
      font-size: 10px;
      font-weight: 700;
      transition: 0.2s;
    }
    .navbar-bottom .nav-item i { font-size: 20px; }
    .navbar-bottom .nav-item.active { color: #2a85ff; }

    /* PAGE WRAPPER */
    .page-wrapper {
      padding: 20px;
    }

    /* HEADER */
    .header {
      display: flex;
      align-items: center;
      gap: 15px;
      margin-bottom: 25px;
      padding-top: 10px;
    }
    .btn-back {
      width: 40px; height: 40px;
      border-radius: 12px;
      background: white;
      display: flex; justify-content: center; align-items: center;
      color: #0d1c2e; text-decoration: none; font-size: 18px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .title {
      font-size: 24px;
      font-weight: 800;
      color: #0d1c2e;
    }

    /* NOTIF CARD */
    .notif-card {
      background: white;
      border-radius: 16px;
      padding: 20px;
      margin-bottom: 16px;
      box-shadow: 0 4px 16px rgba(0,0,0,0.04);
      display: flex;
      gap: 16px;
      align-items: flex-start;
      position: relative;
      overflow: hidden;
    }
    .notif-card.unread {
      background: #f8fbfe;
      border-left: 4px solid #2a85ff;
    }
    .notif-icon {
      width: 48px; height: 48px;
      border-radius: 14px;
      display: flex; align-items: center; justify-content: center;
      font-size: 20px; flex-shrink: 0;
    }
    /* Icon Themes */
    .icon-info { background: #eef5fc; color: #2a85ff; }
    .icon-success { background: #e6f9f0; color: #10b981; }
    .icon-warning { background: #fff4e0; color: #f59e0b; }
    
    .notif-content { flex: 1; }
    .notif-title { font-size: 15px; font-weight: 800; color: #0d1c2e; margin-bottom: 4px; }
    .notif-message { font-size: 13px; color: #556980; line-height: 1.5; margin-bottom: 8px; }
    .notif-time { font-size: 11px; font-weight: 600; color: #aab; }

    .empty-state {
      text-align: center;
      padding: 60px 20px;
    }
    .empty-icon {
      font-size: 60px;
      color: #c8dff5;
      margin-bottom: 16px;
    }
    .empty-text {
      font-size: 15px;
      font-weight: 700;
      color: #8fa3b8;
    }
  </style>
</head>
<body>

<!-- SIDEBAR DESKTOP -->
<div class="navbar">
  <div class="sidebar-logo">
    <img src="Logo.svg" alt="LokalThrift" style="width:100px; height:auto; display:block; margin:0 auto;">
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
  <div class="header">
    <a href="javascript:history.back()" class="btn-back"><i class="fa-solid fa-arrow-left"></i></a>
    <div class="title">Notifikasi</div>
  </div>

  <?php if ($qNotif && mysqli_num_rows($qNotif) > 0): ?>
    <?php while ($n = mysqli_fetch_assoc($qNotif)): ?>
      <?php
        $judul = strtolower($n['judul']);
        if (strpos($judul, 'berhasil') !== false || strpos($judul, 'selesai') !== false) {
            $iconClass = 'icon-success';
            $faIcon = 'fa-check-circle';
        } elseif (strpos($judul, 'gagal') !== false || strpos($judul, 'batal') !== false) {
            $iconClass = 'icon-warning';
            $faIcon = 'fa-triangle-exclamation';
        } else {
            $iconClass = 'icon-info';
            $faIcon = 'fa-bell';
        }
      ?>
      <div class="notif-card <?= $n['is_read'] == 0 ? 'unread' : '' ?>">
        <div class="notif-icon <?= $iconClass ?>">
          <i class="fa-solid <?= $faIcon ?>"></i>
        </div>
        <div class="notif-content">
          <div class="notif-title"><?= htmlspecialchars($n['judul']) ?></div>
          <div class="notif-message"><?= nl2br(htmlspecialchars($n['pesan'])) ?></div>
          <div class="notif-time"><?= date('d M Y, H:i', strtotime($n['created_at'])) ?></div>
        </div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <div class="empty-state">
      <i class="fa-solid fa-bell-slash empty-icon"></i>
      <div class="empty-text">Belum ada notifikasi untukmu</div>
    </div>
  <?php endif; ?>
</div>

<!-- NAVBAR MOBILE -->
<div class="navbar-bottom">
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
</div>

</body>
</html>
