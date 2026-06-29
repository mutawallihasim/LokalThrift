<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Akun Saya
  <?php
  session_start();
  if (!isset($_SESSION['id_pengguna'])) {
    header('Location: login.php');
    exit;
  }

  require_once 'koneksi.php';
  $id_pengguna = (int)$_SESSION['id_pengguna'];

  // Handle POST request to update profile
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profil'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $no_hp = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $provinsi = mysqli_real_escape_string($koneksi, $_POST['provinsi'] ?? '');
    $kota = mysqli_real_escape_string($koneksi, $_POST['kota'] ?? '');
    $kecamatan = mysqli_real_escape_string($koneksi, $_POST['kecamatan'] ?? '');
    $kode_pos = mysqli_real_escape_string($koneksi, $_POST['kode_pos'] ?? '');

    $fotoQuery = "";
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
      $tmp_name = $_FILES['foto_profil']['tmp_name'];
      $name = $_FILES['foto_profil']['name'];
      $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
      $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

      if (in_array($ext, $allowed)) {
        $newFileName = $id_pengguna . '_' . time() . '.' . $ext;
        $destination = 'uploads/profil/' . $newFileName;
        if (move_uploaded_file($tmp_name, $destination)) {
          $fotoQuery = ", foto_profil='$destination'";
        }
      }
    }

    mysqli_query($koneksi, "UPDATE pengguna SET nama='$nama', email='$email', no_hp='$no_hp', alamat='$alamat', provinsi='$provinsi', kota='$kota', kecamatan='$kecamatan', kode_pos='$kode_pos' $fotoQuery WHERE id_pengguna=$id_pengguna");
    header("Location: akun.php");
    exit;
  }

  // Handle POST request to update password
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $old_pwd = $_POST['old_password'] ?? '';
    $new_pwd = $_POST['new_password'] ?? '';
    $conf_pwd = $_POST['confirm_password'] ?? '';

    // Fetch user current data to verify old password
    $qUserCheck = mysqli_query($koneksi, "SELECT password FROM pengguna WHERE id_pengguna=$id_pengguna");
    $userCheck = mysqli_fetch_assoc($qUserCheck);

    if (!password_verify($old_pwd, $userCheck['password'])) {
      header("Location: akun.php?err=pwd_wrong");
      exit;
    }

    if (strlen($new_pwd) < 8) {
      header("Location: akun.php?err=pwd_short");
      exit;
    }

    if ($new_pwd !== $conf_pwd) {
      header("Location: akun.php?err=pwd_mismatch");
      exit;
    }

    $hashed = password_hash($new_pwd, PASSWORD_DEFAULT);
    mysqli_query($koneksi, "UPDATE pengguna SET password='$hashed' WHERE id_pengguna=$id_pengguna");
    header("Location: akun.php?msg=pwd_success");
    exit;
  }

  $qUser = mysqli_query($koneksi, "SELECT * FROM pengguna WHERE id_pengguna=$id_pengguna");
  $user = mysqli_fetch_assoc($qUser);

  // Unread Notifications Count
  $qUnread = mysqli_query($koneksi, "SELECT COUNT(*) as unread FROM notifikasi WHERE id_pengguna=$id_pengguna AND is_read=0");
  $unreadCount = $qUnread ? (int)mysqli_fetch_assoc($qUnread)['unread'] : 0;

  $jmlFav = 0;
  $hasFavorit = mysqli_query($koneksi, "SHOW TABLES LIKE 'favorit'");
  if ($hasFavorit && mysqli_num_rows($hasFavorit) > 0) {
    $qF = mysqli_query($koneksi, "SELECT COUNT(*) as cnt FROM favorit WHERE id_pengguna=$id_pengguna");
    if ($qF) {
      $r = mysqli_fetch_assoc($qF);
      $jmlFav = (int)$r['cnt'];
    }
  }

  $jmlPesanan = 0;
  $hasPesanan = mysqli_query($koneksi, "SHOW TABLES LIKE 'pesanan'");
  if ($hasPesanan && mysqli_num_rows($hasPesanan) > 0) {
    $qP = mysqli_query($koneksi, "SELECT COUNT(*) as cnt FROM pesanan WHERE id_pengguna=$id_pengguna");
    if ($qP) {
      $rP = mysqli_fetch_assoc($qP);
      $jmlPesanan = (int)$rP['cnt'];
    }
  }

  $ratingValue = "0";
  $hasReview = mysqli_query($koneksi, "SHOW TABLES LIKE 'review'");
  if ($hasReview && mysqli_num_rows($hasReview) > 0) {
    if (isset($user['role']) && $user['role'] === 'penjual') {
      $qToko = mysqli_query($koneksi, "SELECT id_toko FROM toko WHERE id_penjual=$id_pengguna");
      if ($qToko && mysqli_num_rows($qToko) > 0) {
        $toko = mysqli_fetch_assoc($qToko);
        $idToko = (int)$toko['id_toko'];
        $qRating = mysqli_query(
          $koneksi,
          "SELECT AVG(r.rating) as avg_rating 
FROM review r 
JOIN produk p ON r.id_produk = p.id_produk 
WHERE p.id_toko = $idToko"
        );
        if ($qRating) {
          $rRating = mysqli_fetch_assoc($qRating);
          $ratingValue = number_format((float)$rRating['avg_rating'], 1, ',', '.');
        }
      }
    } else {
      $qRating = mysqli_query($koneksi, "SELECT COUNT(*) as cnt FROM review WHERE id_pengguna=$id_pengguna");
      if ($qRating) {
        $rRating = mysqli_fetch_assoc($qRating);
        $ratingValue = (string)$rRating['cnt'];
      }
    }
  }
  ?>
<!DOCTYPE html>
    <html lang="id">

    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Akun Saya - LokalThrift</title>
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
      <style>
        * {
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

        .nav-item.active {
          color: #2a85ff;
        }

        .nav-item.nav-logout {
          color: #e53935;
        }

        .nav-item.nav-logout:hover {
          color: #c62828;
        }

        .sidebar-logo {
          display: none;
        }

        @media (min-width: 769px) {
          body {
            flex-direction: row;
          }

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
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.05);
            gap: 4px;
          }

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

          .nav-item:hover {
            background: #eef5fc;
            color: #2a85ff;
          }

          .nav-item.active {
            background: #ddeeff;
            color: #2a85ff;
          }

          .nav-item.nav-logout {
            margin-top: auto;
            color: #e53935;
          }

          .nav-item.nav-logout:hover {
            background: #fff0f0;
            color: #c62828;
          }

          .nav-item i {
            font-size: 22px;
            display: block;
            margin-bottom: 0;
            width: auto;
          }

          .page-wrapper {
            margin-left: 160px;
            width: 100%;
          }

          .main {
            padding: 30px 40px 40px;
            max-width: 860px;
          }
        }

        /* ── PAGE WRAPPER ── */
        .page-wrapper {
          display: flex;
          flex: 1;
        }

        .main {
          width: 100%;
          max-width: 720px;
          margin: 0 auto;
          padding: 24px 16px 100px;
        }

        /* ── PROFILE HEADER ── */
        .profile-card {
          background: white;
          border-radius: 20px;
          padding: 24px;
          box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
          margin-bottom: 16px;
          display: flex;
          align-items: center;
          gap: 18px;
          position: relative;
        }

        .avatar-wrap {
          width: 76px;
          height: 76px;
          border-radius: 50%;
          background: linear-gradient(135deg, #2a85ff, #74b3ff);
          display: flex;
          align-items: center;
          justify-content: center;
          flex-shrink: 0;
          position: relative;
          box-shadow: 0 4px 14px rgba(42, 133, 255, 0.3);
        }

        .avatar-wrap span {
          font-size: 28px;
          font-weight: 800;
          color: white;
        }

        .avatar-edit {
          position: absolute;
          bottom: 0;
          right: 0;
          width: 26px;
          height: 26px;
          background: white;
          border: 2px solid #e8f0fb;
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          cursor: pointer;
          font-size: 12px;
          color: #556980;
        }

        .profile-info h2 {
          font-size: 18px;
          font-weight: 800;
          color: #0d1c2e;
        }

        .profile-role {
          display: inline-block;
          margin-top: 4px;
          padding: 2px 10px;
          background: #eef5fc;
          color: #2a85ff;
          font-size: 11px;
          font-weight: 700;
          border-radius: 20px;
        }

        .profile-join {
          font-size: 12px;
          color: #8fa3b8;
          margin-top: 5px;
        }

        .profile-loc {
          font-size: 12px;
          color: #8fa3b8;
          margin-top: 3px;
        }

        .btn-edit-profile {
          position: absolute;
          top: 18px;
          right: 18px;
          background: #eef5fc;
          border: none;
          color: #2a85ff;
          font-size: 12px;
          font-weight: 700;
          padding: 7px 14px;
          border-radius: 10px;
          cursor: pointer;
        }

        .btn-edit-profile:hover {
          background: #ddeeff;
        }

        /* ── STATS ROW ── */
        .stats-row {
          display: grid;
          grid-template-columns: repeat(3, 1fr);
          gap: 12px;
          margin-bottom: 16px;
        }

        .stat-card {
          background: white;
          border-radius: 16px;
          padding: 16px;
          text-align: center;
          box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        }

        .stat-card .num {
          font-size: 22px;
          font-weight: 800;
          color: #0d1c2e;
        }

        .stat-card .lbl {
          font-size: 11px;
          color: #8fa3b8;
          margin-top: 3px;
        }

        .stat-card i {
          font-size: 18px;
          margin-bottom: 6px;
        }

        /* ── MENU LIST ── */
        .menu-card {
          background: white;
          border-radius: 16px;
          box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
          overflow: hidden;
          margin-bottom: 16px;
        }

        .menu-card-title {
          font-size: 12px;
          font-weight: 700;
          color: #8fa3b8;
          text-transform: uppercase;
          letter-spacing: 0.5px;
          padding: 14px 18px 8px;
        }

        .menu-row {
          display: flex;
          align-items: center;
          gap: 14px;
          padding: 14px 18px;
          text-decoration: none;
          color: #0d1c2e;
          transition: background 0.15s;
          border-top: 1px solid #f5f8fc;
        }

        .menu-row:first-of-type {
          border-top: none;
        }

        .menu-row:hover {
          background: #fafcff;
        }

        .menu-row .icon {
          width: 36px;
          height: 36px;
          border-radius: 10px;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 16px;
          flex-shrink: 0;
        }

        .menu-row .label {
          flex: 1;
          font-size: 14px;
          font-weight: 600;
        }

        .menu-row .sub {
          font-size: 12px;
          color: #8fa3b8;
          margin-top: 2px;
        }

        .menu-row .arrow {
          color: #c8dff5;
          font-size: 13px;
        }

        .menu-row.danger {
          color: #e53935;
        }

        .menu-row.danger .arrow {
          color: #ffc8c8;
        }

        /* ── MODAL EDIT PROFIL ── */
        .modal-overlay {
          display: none;
          position: fixed;
          inset: 0;
          background: rgba(13, 28, 46, 0.45);
          z-index: 1000;
          align-items: center;
          justify-content: center;
          padding: 16px;
        }

        .modal-overlay.open {
          display: flex;
        }

        .modal {
          background: white;
          border-radius: 20px;
          padding: 24px;
          width: 100%;
          max-width: 440px;
          animation: slideUp 0.2s ease;
        }

        @keyframes slideUp {
          from {
            transform: translateY(20px);
            opacity: 0
          }

          to {
            transform: translateY(0);
            opacity: 1
          }
        }

        .modal-title {
          font-size: 16px;
          font-weight: 800;
          color: #0d1c2e;
          margin-bottom: 18px;
          display: flex;
          justify-content: space-between;
          align-items: center;
        }

        .modal-close {
          background: none;
          border: none;
          font-size: 20px;
          color: #aaa;
          cursor: pointer;
        }

        .form-group {
          margin-bottom: 13px;
        }

        .form-label {
          font-size: 12px;
          font-weight: 700;
          color: #556980;
          margin-bottom: 5px;
          display: block;
        }

        .form-input {
          width: 100%;
          padding: 11px 14px;
          border: 1.5px solid #d4e3f3;
          border-radius: 10px;
          font-size: 13px;
          outline: none;
          transition: border-color 0.2s;
        }

        .form-input:focus {
          border-color: #2a85ff;
        }

        .form-row {
          display: flex;
          gap: 12px;
        }

        .form-row .form-group {
          flex: 1;
        }

        .btn-simpan {
          width: 100%;
          padding: 13px;
          background: #2a85ff;
          color: white;
          border: none;
          border-radius: 12px;
          font-size: 14px;
          font-weight: 700;
          cursor: pointer;
          margin-top: 4px;
        }

        .btn-simpan:hover {
          opacity: 0.9;
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
    <a href="cek_toko.php" class="nav-item">
      <i class="fa-solid fa-shop"></i><span>Toko</span>
    </a>
    <a href="akun.php" class="nav-item active">
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
    <div class="main">

      <?php if (isset($_GET['msg']) && $_GET['msg'] === 'pwd_success'): ?>
        <div style="background:#d1fae5; color:#065f46; padding:12px; border-radius:10px; margin-bottom:16px; font-size:13px; font-weight:600;">
          <i class="fa-solid fa-circle-check" style="margin-right:6px;"></i> Password berhasil diubah!
        </div>
      <?php endif; ?>
      <?php if (isset($_GET['err'])): ?>
        <div style="background:#fee2e2; color:#991b1b; padding:12px; border-radius:10px; margin-bottom:16px; font-size:13px; font-weight:600;">
          <i class="fa-solid fa-circle-exclamation" style="margin-right:6px;"></i>
          <?php
          if ($_GET['err'] === 'pwd_wrong') echo 'Password lama salah!';
          elseif ($_GET['err'] === 'pwd_short') echo 'Password baru minimal 8 karakter!';
          elseif ($_GET['err'] === 'pwd_mismatch') echo 'Konfirmasi password tidak cocok!';
          ?>
        </div>
      <?php endif; ?>


      <?php if (isset($_GET['msg']) && $_GET['msg'] === 'pwd_success'): ?>
        <div style="background:#d1fae5; color:#065f46; padding:12px; border-radius:10px; margin-bottom:16px; font-size:13px; font-weight:600;">
          <i class="fa-solid fa-circle-check" style="margin-right:6px;"></i> Password berhasil diubah!
        </div>
      <?php endif; ?>
      <?php if (isset($_GET['err'])): ?>
        <div style="background:#fee2e2; color:#991b1b; padding:12px; border-radius:10px; margin-bottom:16px; font-size:13px; font-weight:600;">
          <i class="fa-solid fa-circle-exclamation" style="margin-right:6px;"></i>
          <?php
          if ($_GET['err'] === 'pwd_wrong') echo 'Password lama salah!';
          elseif ($_GET['err'] === 'pwd_short') echo 'Password baru minimal 8 karakter!';
          elseif ($_GET['err'] === 'pwd_mismatch') echo 'Konfirmasi password tidak cocok!';
          ?>
        </div>
      <?php endif; ?>


      <!-- PROFILE HEADER -->
      <div class="profile-card">
        <div class="avatar-wrap" style="overflow:hidden;">
          <?php if (!empty($user['foto_profil'])): ?>
            <img src="<?= htmlspecialchars($user['foto_profil']) ?>" alt="Foto Profil" style="width:100%;height:100%;object-fit:cover;">
          <?php else: ?>
            <span id="avatar-initial"><?= strtoupper(substr($user['nama'], 0, 1)) ?></span>
          <?php endif; ?>
          <div class="avatar-edit" onclick="bukaModal('modal-pribadi')" style="z-index:2;"><i class="fa-solid fa-pen"></i></div>
        </div>
        <div class="profile-info">
          <h2 id="disp-nama"><?= htmlspecialchars($user['nama']) ?></h2>
          <span class="profile-role"><?= htmlspecialchars(ucfirst($user['role'] ?? 'Pembeli')) ?></span>
          <div class="profile-join"><i class="fa-regular fa-calendar" style="margin-right:4px"></i>Terdaftar</div>
          <div class="profile-loc"><i class="fa-solid fa-location-dot" style="margin-right:4px"></i><span id="disp-kota"><?= htmlspecialchars($user['alamat'] ?? '-') ?></span></div>
        </div>
        <button class="btn-edit-profile" onclick="bukaModal('modal-pribadi')"><i class="fa-solid fa-pen" style="margin-right:5px"></i>Edit</button>
      </div>

      <!-- STATS -->
      <div class="stats-row">
        <div class="stat-card">
          <i class="fa-solid fa-bag-shopping" style="color:#2a85ff"></i>
          <div class="num"><?= $jmlPesanan ?></div>
          <div class="lbl">Pesanan</div>
        </div>
        <div class="stat-card">
          <i class="fa-regular fa-heart" style="color:#e53935"></i>
          <div class="num"><?= $jmlFav ?></div>
          <div class="lbl">Favorit</div>
        </div>
        <div class="stat-card">
          <i class="fa-regular fa-star" style="color:#f5a623"></i>
          <div class="num"><?= $ratingValue ?></div>
          <div class="lbl">Rating</div>
        </div>
      </div>

      <!-- MENU: TRANSAKSI -->
      <div class="menu-card">
        <div class="menu-card-title">Transaksi</div>

        <a href="pesanan.php" class="menu-row">
          <div class="icon" style="background:#eef5fc"><i class="fa-solid fa-receipt" style="color:#2a85ff"></i></div>
          <div style="flex:1">
            <div class="label">Pesanan Saya</div>
            <div class="sub"><?= $jmlPesanan ?> pesanan</div>
          </div>
          <i class="fa-solid fa-chevron-right arrow"></i>
        </a>

        <a href="favorit.php" class="menu-row">
          <div class="icon" style="background:#fff0f0"><i class="fa-regular fa-heart" style="color:#e53935"></i></div>
          <div style="flex:1">
            <div class="label">Favorit</div>
            <div class="sub"><?= $jmlFav ?> item tersimpan</div>
          </div>
          <i class="fa-solid fa-chevron-right arrow"></i>
        </a>

        <a href="keranjang.php" class="menu-row">
          <div class="icon" style="background:#eef5fc"><i class="fa-solid fa-cart-shopping" style="color:#2a85ff"></i></div>
          <div style="flex:1">
            <div class="label">Keranjang Belanja</div>
            <div class="sub">Lihat item di keranjang</div>
          </div>
          <i class="fa-solid fa-chevron-right arrow"></i>
        </a>
      </div>

      <!-- MENU: AKUN -->
      <div class="menu-card">
        <div class="menu-card-title">Akun</div>

        <a href="#" class="menu-row" onclick="bukaModal('modal-pribadi');return false">
          <div class="icon" style="background:#eef5fc"><i class="fa-regular fa-user" style="color:#2a85ff"></i></div>
          <div style="flex:1">
            <div class="label">Informasi Pribadi</div>
            <div class="sub">Nama, email, telepon</div>
          </div>
          <i class="fa-solid fa-chevron-right arrow"></i>
        </a>

        <a href="#" class="menu-row" onclick="bukaModal('modal-alamat');return false">
          <div class="icon" style="background:#f0fff8"><i class="fa-solid fa-location-dot" style="color:#10b981"></i></div>
          <?php
          $alamat_lengkap = array_filter([
            $user['alamat'] ?? null,
            $user['kecamatan'] ?? null,
            $user['kota'] ?? null,
            $user['provinsi'] ?? null,
            $user['kode_pos'] ?? null
          ]);
          $alamat_display = !empty($alamat_lengkap) ? implode(', ', $alamat_lengkap) : 'Belum ada alamat';
          ?>
          <div style="flex:1">
            <div class="label">Alamat Pengiriman</div>
            <div class="sub" id="disp-alamat-sub"><?= htmlspecialchars($alamat_display) ?></div>
          </div>
          <i class="fa-solid fa-chevron-right arrow"></i>
        </a>

        <a href="#" class="menu-row" onclick="bukaModal('modal-keamanan');return false">
          <div class="icon" style="background:#fff8ee"><i class="fa-solid fa-shield-halved" style="color:#ffa800"></i></div>
          <div style="flex:1">
            <div class="label">Keamanan</div>
            <div class="sub">Password &amp; verifikasi</div>
          </div>
          <i class="fa-solid fa-chevron-right arrow"></i>
        </a>
      </div>

      <!-- MENU: LAINNYA -->
      <div class="menu-card">
        <div class="menu-card-title">Lainnya</div>

        <a href="notifikasi.php" class="menu-row">
          <div class="icon" style="background:#f5f0ff"><i class="fa-regular fa-bell" style="color:#8b5cf6"></i></div>
          <div style="flex:1; display:flex; align-items:center; gap:8px;">
            <div class="label">Notifikasi</div>
            <?php if (isset($unreadCount) && $unreadCount > 0): ?>
              <span style="background:#e53935;color:white;font-size:10px;font-weight:bold;padding:2px 6px;border-radius:10px;"><?= $unreadCount ?></span>
            <?php endif; ?>
          </div>
          <i class="fa-solid fa-chevron-right arrow"></i>
        </a>

        <a href="#" class="menu-row" onclick="bukaModal('modal-bantuan');return false">
          <div class="icon" style="background:#eef5fc"><i class="fa-regular fa-circle-question" style="color:#2a85ff"></i></div>
          <div style="flex:1">
            <div class="label">Bantuan</div>
            <div class="sub">Pusat bantuan & FAQ</div>
          </div>
          <i class="fa-solid fa-chevron-right arrow"></i>
        </a>

        <a href="logout.php" class="menu-row danger">
          <div class="icon" style="background:#fff0f0"><i class="fa-solid fa-right-from-bracket" style="color:#e53935"></i></div>
          <div style="flex:1">
            <div class="label">Keluar</div>
          </div>
          <i class="fa-solid fa-chevron-right arrow"></i>
        </a>
      </div>

    </div>
  </div>

  <!-- MODAL INFORMASI PRIBADI -->
  <div class="modal-overlay" id="modal-pribadi" onclick="tutupIfOverlay(event,'modal-pribadi')">
    <div class="modal">
      <form method="POST" action="akun.php" enctype="multipart/form-data">
        <input type="hidden" name="update_profil" value="1">
        <div class="modal-title">
          Informasi Pribadi
          <button type="button" class="modal-close" onclick="tutupModal('modal-pribadi')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <!-- Hidden fields for alamat -->
        <input type="hidden" name="alamat" value="<?= htmlspecialchars($user['alamat'] ?? '') ?>">
        <input type="hidden" name="provinsi" value="<?= htmlspecialchars($user['provinsi'] ?? '') ?>">
        <input type="hidden" name="kota" value="<?= htmlspecialchars($user['kota'] ?? '') ?>">
        <input type="hidden" name="kecamatan" value="<?= htmlspecialchars($user['kecamatan'] ?? '') ?>">
        <input type="hidden" name="kode_pos" value="<?= htmlspecialchars($user['kode_pos'] ?? '') ?>">

        <div class="form-group">
          <label class="form-label">Foto Profil (Opsional)</label>
          <input type="file" class="form-input" name="foto_profil" accept="image/*" style="padding: 8px;">
        </div>
        <div class="form-group">
          <label class="form-label">Nama Lengkap</label>
          <input class="form-input" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Email</label>
          <input class="form-input" name="email" type="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">No. Telepon</label>
          <input class="form-input" name="no_hp" value="<?= htmlspecialchars($user['no_hp'] ?? '') ?>" required>
        </div>
        <button type="submit" class="btn-simpan">Simpan Perubahan</button>
      </form>
    </div>
  </div>

  <!-- MODAL ALAMAT PENGIRIMAN -->
  <div class="modal-overlay" id="modal-alamat" onclick="tutupIfOverlay(event,'modal-alamat')">
    <div class="modal">
      <form method="POST" action="akun.php">
        <input type="hidden" name="update_profil" value="1">
        <div class="modal-title">
          Alamat Pengiriman
          <button type="button" class="modal-close" onclick="tutupModal('modal-alamat')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <!-- Hidden fields for profile info -->
        <input type="hidden" name="nama" value="<?= htmlspecialchars($user['nama']) ?>">
        <input type="hidden" name="email" value="<?= htmlspecialchars($user['email']) ?>">
        <input type="hidden" name="no_hp" value="<?= htmlspecialchars($user['no_hp'] ?? '') ?>">

        <div class="form-group">
          <label class="form-label">Alamat Lengkap (Jalan, RT/RW)</label>
          <input class="form-input" name="alamat" value="<?= htmlspecialchars($user['alamat'] ?? '') ?>" required>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Provinsi</label>
            <input class="form-input" name="provinsi" value="<?= htmlspecialchars($user['provinsi'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Kota/Kabupaten</label>
            <input class="form-input" name="kota" value="<?= htmlspecialchars($user['kota'] ?? '') ?>" required>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Kecamatan</label>
            <input class="form-input" name="kecamatan" value="<?= htmlspecialchars($user['kecamatan'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Kode Pos</label>
            <input class="form-input" name="kode_pos" value="<?= htmlspecialchars($user['kode_pos'] ?? '') ?>" required>
          </div>
        </div>
        <button type="submit" class="btn-simpan">Simpan Perubahan</button>
      </form>
    </div>
  </div>

  <!-- MODAL UBAH PASSWORD -->
  <div class="modal-overlay" id="modal-keamanan" onclick="tutupIfOverlay(event,'modal-keamanan')">
    <div class="modal">
      <form method="POST" action="akun.php">
        <input type="hidden" name="update_password" value="1">
        <div class="modal-title">
          Ubah Password
          <button type="button" class="modal-close" onclick="tutupModal('modal-keamanan')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="form-group">
          <label class="form-label">Password Lama</label>
          <input type="password" class="form-input" name="old_password" placeholder="Masukkan password lama" required>
        </div>
        <div class="form-group">
          <label class="form-label">Password Baru</label>
          <input type="password" class="form-input" name="new_password" placeholder="Minimal 8 karakter" required minlength="8">
        </div>
        <div class="form-group">
          <label class="form-label">Konfirmasi Password Baru</label>
          <input type="password" class="form-input" name="confirm_password" placeholder="Ulangi password baru" required minlength="8">
        </div>
        <button type="submit" class="btn-simpan">Simpan Password Baru</button>
      </form>
    </div>
  </div>

  <!-- MODAL BANTUAN -->
  <div class="modal-overlay" id="modal-bantuan" onclick="tutupIfOverlay(event,'modal-bantuan')">
    <div class="modal">
      <div class="modal-title">
        Pusat Bantuan
        <button type="button" class="modal-close" onclick="tutupModal('modal-bantuan')"><i class="fa-solid fa-xmark"></i></button>
      </div>

      <div style="margin-bottom: 20px;">
        <h4 style="font-size: 14px; font-weight: 700; color: #0d1c2e; margin-bottom: 8px;">Pertanyaan Populer</h4>

        <div style="border-bottom: 1px solid #f0f0f0; padding-bottom: 10px; margin-bottom: 10px;">
          <strong style="font-size: 13px; color: #333;">Bagaimana cara melacak pesanan saya?</strong>
          <p style="font-size: 12px; color: #666; margin-top: 4px;">Anda dapat melacak status pesanan melalui menu "Pesanan Saya" di halaman Akun atau sidebar kiri.</p>
        </div>

        <div style="border-bottom: 1px solid #f0f0f0; padding-bottom: 10px; margin-bottom: 10px;">
          <strong style="font-size: 13px; color: #333;">Bagaimana cara berjualan di LokalThrift?</strong>
          <p style="font-size: 12px; color: #666; margin-top: 4px;">Fitur berjualan saat ini khusus untuk penjual yang telah diverifikasi. Silakan hubungi admin untuk pendaftaran toko.</p>
        </div>

        <div style="padding-bottom: 5px;">
          <strong style="font-size: 13px; color: #333;">Metode pembayaran apa saja yang tersedia?</strong>
          <p style="font-size: 12px; color: #666; margin-top: 4px;">Kami mendukung berbagai metode pembayaran melalui transfer bank (BCA, Mandiri, BRI, dll) serta e-wallet seperti GoPay dan OVO.</p>
        </div>
      </div>

      <div style="text-align: center; margin-top: 24px;">
        <p style="font-size: 12px; color: #8fa3b8; margin-bottom: 12px;">Masih butuh bantuan lebih lanjut?</p>
        <a href="https://wa.me/6282238726796" target="_blank" style="display: inline-block; width: 100%; padding: 12px; background: #25D366; color: white; text-decoration: none; border-radius: 12px; font-size: 14px; font-weight: 700; text-align: center;">
          <i class="fa-brands fa-whatsapp" style="margin-right: 6px;"></i> Hubungi Customer Service
        </a>
      </div>

    </div>
  </div>

  <script>
    function bukaModal(id) {
      document.getElementById(id).classList.add('open');
    }

    function tutupModal(id) {
      document.getElementById(id).classList.remove('open');
    }

    function tutupIfOverlay(e, id) {
      if (e.target === document.getElementById(id)) tutupModal(id);
    }
  </script>
</body>

</html>
- LokalThrift</title>
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