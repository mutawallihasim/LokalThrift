<?php
$currentPage = basename($_SERVER['PHP_SELF']);
function navAdminActive($file) {
    global $currentPage;
    return $currentPage === $file ? 'active' : '';
}
?>
<style>
  * { margin:0; padding:0; box-sizing:border-box; font-family:'Helvetica Neue',Arial,sans-serif; }
  body { display:flex; min-height:100vh; background:#f0f4fa; }

  /* ── SIDEBAR ── */
  .sidebar {
    position:fixed; top:0; left:0; bottom:0;
    width:220px; background:linear-gradient(180deg,#0d1c2e 0%,#1a2d42 100%);
    display:flex; flex-direction:column;
    padding:0 0 20px; z-index:100;
    box-shadow:4px 0 20px rgba(0,0,0,0.15);
  }

  .sidebar-brand {
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    padding:30px 18px 24px;
    border-bottom:1px solid rgba(255,255,255,0.07);
    margin-bottom:8px;
  }
  .sidebar-brand img { width:110px; height:auto; filter:brightness(0) invert(1); margin-bottom:10px; }
  .sidebar-badge {
    background:linear-gradient(135deg,#e53935,#c62828);
    color:white; font-size:10px; font-weight:800;
    padding:4px 10px; border-radius:20px; letter-spacing:0.5px;
    text-transform:uppercase;
  }

  .sidebar-section {
    font-size:10px; font-weight:700; color:rgba(255,255,255,0.3);
    text-transform:uppercase; letter-spacing:0.8px;
    padding:12px 18px 4px;
  }

  .nav-item {
    display:flex; align-items:center; gap:10px;
    font-size:13px; font-weight:600; color:rgba(255,255,255,0.55);
    text-decoration:none; padding:11px 16px;
    border-radius:10px; margin:2px 10px;
    transition:background 0.15s, color 0.15s;
  }
  .nav-item i { font-size:15px; width:18px; text-align:center; }
  .nav-item:hover { background:rgba(255,255,255,0.08); color:white; }
  .nav-item.active { background:rgba(42,133,255,0.25); color:#2a85ff; font-weight:700; }
  .nav-item.active i { color:#2a85ff; }
  .nav-item.danger { color:rgba(229,57,53,0.8); }
  .nav-item.danger:hover { background:rgba(229,57,53,0.1); color:#e53935; }

  .sidebar-bottom { margin-top:auto; border-top:1px solid rgba(255,255,255,0.07); padding-top:10px; }

  .admin-info {
    display:flex; align-items:center; gap:10px;
    padding:12px 18px;
  }
  .admin-avatar {
    width:34px; height:34px; border-radius:50%;
    background:linear-gradient(135deg,#2a85ff,#0055cc);
    display:flex; align-items:center; justify-content:center;
    font-size:14px; font-weight:800; color:white; flex-shrink:0;
  }
  .admin-name { font-size:12px; font-weight:700; color:white; }
  .admin-role { font-size:10px; color:rgba(255,255,255,0.35); margin-top:1px; }

  /* ── MAIN CONTENT ── */
  .main-content {
    margin-left:220px; flex:1;
    padding:28px 32px 60px;
    min-height:100vh;
  }

  .page-header {
    display:flex; justify-content:space-between; align-items:flex-start;
    margin-bottom:24px;
  }
  .page-title { font-size:22px; font-weight:800; color:#0d1c2e; }
  .page-sub { font-size:13px; color:#8fa3b8; margin-top:3px; }

  /* ── STAT CARDS ── */
  .stats-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:24px; }
  @media(max-width:1100px){ .stats-grid{ grid-template-columns:repeat(2,1fr); } }

  .stat-card {
    background:white; border-radius:16px; padding:20px;
    box-shadow:0 2px 12px rgba(0,0,0,0.06);
    display:flex; align-items:center; gap:16px;
    transition:transform 0.2s, box-shadow 0.2s;
  }
  .stat-card:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(0,0,0,0.1); }
  .stat-icon {
    width:50px; height:50px; border-radius:14px;
    display:flex; align-items:center; justify-content:center;
    font-size:22px; flex-shrink:0;
  }
  .stat-icon.blue { background:#e8f1ff; color:#2a85ff; }
  .stat-icon.green { background:#e6f9f0; color:#10b981; }
  .stat-icon.orange { background:#fff4e0; color:#f59e0b; }
  .stat-icon.red { background:#fff0f0; color:#e53935; }
  .stat-icon.purple { background:#f3e8ff; color:#7c3aed; }
  .stat-num { font-size:24px; font-weight:800; color:#0d1c2e; line-height:1; }
  .stat-lbl { font-size:12px; color:#8fa3b8; margin-top:4px; }
  .stat-trend { font-size:11px; font-weight:700; margin-top:4px; }
  .stat-trend.up { color:#10b981; }

  /* ── CARDS ── */
  .card {
    background:white; border-radius:16px; padding:20px;
    box-shadow:0 2px 12px rgba(0,0,0,0.06);
    margin-bottom:20px;
  }
  .card-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; }
  .card-title { font-size:15px; font-weight:800; color:#0d1c2e; }

  /* ── TABLE ── */
  .tbl { width:100%; border-collapse:collapse; }
  .tbl th {
    font-size:11px; font-weight:700; color:#8fa3b8;
    text-transform:uppercase; padding:8px 14px; text-align:left;
    border-bottom:2px solid #f0f6fc; letter-spacing:0.4px;
  }
  .tbl td {
    padding:12px 14px; font-size:13px; color:#0d1c2e;
    border-bottom:1px solid #f8fbfe; vertical-align:middle;
  }
  .tbl tr:last-child td { border-bottom:none; }
  .tbl tr:hover td { background:#fafcff; }

  /* ── BADGES ── */
  .badge {
    display:inline-block; padding:4px 10px; border-radius:20px;
    font-size:11px; font-weight:700;
  }
  .badge-admin    { background:#f3e8ff; color:#7c3aed; }
  .badge-penjual  { background:#e8f1ff; color:#2a85ff; }
  .badge-pembeli  { background:#f0f6fc; color:#556980; }
  .badge-aktif    { background:#e6f9f0; color:#10b981; }
  .badge-nonaktif { background:#f0f0f0; color:#888; }
  .badge-diproses { background:#fff4e0; color:#f59e0b; }
  .badge-dikirim  { background:#e8f1ff; color:#2a85ff; }
  .badge-selesai  { background:#e6f9f0; color:#10b981; }
  .badge-dibatalkan { background:#fff0f0; color:#e53935; }
  .badge-menunggu { background:#f5f5f5; color:#888; }

  /* ── BUTTONS ── */
  .btn {
    display:inline-flex; align-items:center; gap:6px;
    padding:9px 18px; border-radius:10px; font-size:13px;
    font-weight:700; cursor:pointer; border:none; text-decoration:none;
    transition:all 0.2s;
  }
  .btn-primary { background:#2a85ff; color:white; }
  .btn-primary:hover { opacity:0.9; transform:translateY(-1px); }
  .btn-outline { background:white; color:#2a85ff; border:1.5px solid #2a85ff; }
  .btn-outline:hover { background:#eef5fc; }
  .btn-danger { background:white; color:#e53935; border:1.5px solid #fcc; }
  .btn-danger:hover { background:#fff0f0; }
  .btn-success { background:#10b981; color:white; }
  .btn-success:hover { opacity:0.9; }
  .btn-sm { padding:5px 11px; font-size:11px; border-radius:8px; }

  /* ── FORM ── */
  .form-input {
    padding:10px 14px; border:1.5px solid #d4e3f3;
    border-radius:10px; font-size:13px; outline:none;
    transition:border-color 0.2s; background:white;
  }
  .form-input:focus { border-color:#2a85ff; }

  /* ── ALERT ── */
  .alert { padding:12px 16px; border-radius:10px; font-size:13px; font-weight:600; margin-bottom:16px; }
  .alert-success { background:#e6f9f0; color:#10b981; border-left:3px solid #10b981; }
  .alert-error   { background:#fff0f0; color:#e53935; border-left:3px solid #e53935; }

  /* ── SEARCH ── */
  .search-wrap { position:relative; }
  .search-wrap i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#8fa3b8; font-size:14px; }
  .search-wrap input { padding-left:36px; width:260px; }

  /* ── AVATAR ── */
  .user-avatar {
    width:32px; height:32px; border-radius:50%;
    background:linear-gradient(135deg,#2a85ff,#7c3aed);
    display:inline-flex; align-items:center; justify-content:center;
    font-size:13px; font-weight:800; color:white; flex-shrink:0;
  }

  @media(max-width:768px){
    .sidebar { display:none; }
    .main-content { margin-left:0; padding:20px 16px 80px; }
  }
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="sidebar">
  <div class="sidebar-brand">
    <img src="../Logo.svg" alt="LokalThrift">
    <div class="sidebar-badge">Admin Panel</div>
  </div>

  <div class="sidebar-section">Manajemen</div>
  <a href="dashboard.php" class="nav-item <?= navAdminActive('dashboard.php') ?>">
    <i class="fa-solid fa-chart-pie"></i> Dashboard
  </a>
  <a href="pengguna.php" class="nav-item <?= navAdminActive('pengguna.php') ?>">
    <i class="fa-solid fa-users"></i> Pengguna
  </a>
  <a href="toko.php" class="nav-item <?= navAdminActive('toko.php') ?>">
    <i class="fa-solid fa-store"></i> Toko
  </a>
  <a href="produk.php" class="nav-item <?= navAdminActive('produk.php') ?>">
    <i class="fa-solid fa-shirt"></i> Produk
  </a>
  <a href="pesanan.php" class="nav-item <?= navAdminActive('pesanan.php') ?>">
    <i class="fa-solid fa-receipt"></i> Pesanan
  </a>

  <div class="sidebar-bottom">
    <div class="admin-info">
      <div class="admin-avatar"><?= strtoupper(substr($_SESSION['nama'] ?? 'A', 0, 1)) ?></div>
      <div>
        <div class="admin-name"><?= htmlspecialchars($_SESSION['nama'] ?? 'Admin') ?></div>
        <div class="admin-role">Super Admin</div>
      </div>
    </div>
    <a href="../home.php" class="nav-item">
      <i class="fa-solid fa-arrow-left"></i> Kembali ke App
    </a>
    <a href="../logout.php" class="nav-item danger">
      <i class="fa-solid fa-right-from-bracket"></i> Logout
    </a>
  </div>
</div>
