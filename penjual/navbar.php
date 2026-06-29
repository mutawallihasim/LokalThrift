<?php
$currentPage = basename($_SERVER['PHP_SELF']);
function navActive($file) {
    global $currentPage;
    return $currentPage === $file ? 'active' : '';
}
?>
<style>
  *{ margin:0; padding:0; box-sizing:border-box; font-family:'Helvetica Neue',Arial,sans-serif; }
  body { display:flex; min-height:100vh; background:#eef5fc; }

  .sidebar {
    position:fixed; top:0; left:0; bottom:0;
    width:200px; background:white;
    border-right:1px solid #e0ecf8;
    box-shadow:4px 0 15px rgba(0,0,0,0.04);
    display:flex; flex-direction:column;
    padding:24px 0 20px; z-index:100;
  }

  .sidebar-brand {
    font-size:15px; font-weight:800; color:#2a85ff;
    padding:0 16px 20px; border-bottom:1px solid #e0ecf8;
    margin-bottom:12px; display:flex; align-items:center; gap:8px;
  }

  .sidebar-section {
    font-size:10px; font-weight:700; color:#aab;
    text-transform:uppercase; letter-spacing:0.6px;
    padding:8px 16px 4px;
  }

  .nav-item {
    display:flex; align-items:center; gap:10px;
    font-size:13px; font-weight:600; color:#556980;
    text-decoration:none; padding:11px 16px;
    border-radius:10px; margin:2px 8px;
    transition:background 0.15s, color 0.15s;
  }
  .nav-item i { font-size:15px; width:18px; text-align:center; }
  .nav-item:hover { background:#eef5fc; color:#2a85ff; }
  .nav-item.active { background:#ddeeff; color:#2a85ff; font-weight:700; }
  .nav-item.danger { color:#e53935; }
  .nav-item.danger:hover { background:#fff0f0; }
  .nav-item .badge-nav {
    margin-left:auto; background:#2a85ff; color:white;
    font-size:10px; font-weight:700; padding:2px 7px;
    border-radius:20px;
  }

  .sidebar-bottom { margin-top:auto; }

  /* Main content */
  .main-content {
    margin-left:200px; flex:1;
    padding:28px 32px 60px;
    max-width:calc(100% - 200px);
  }

  .page-header {
    display:flex; justify-content:space-between; align-items:center;
    margin-bottom:24px;
  }
  .page-title { font-size:20px; font-weight:800; color:#0d1c2e; }
  .page-sub { font-size:13px; color:#8fa3b8; margin-top:3px; }

  /* Cards */
  .card {
    background:white; border-radius:16px; padding:20px;
    box-shadow:0 4px 16px rgba(0,0,0,0.05);
  }
  .card-title { font-size:14px; font-weight:800; color:#0d1c2e; margin-bottom:16px; }

  /* Stats grid */
  .stats-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:24px; }
  @media(max-width:900px){ .stats-grid{ grid-template-columns:repeat(2,1fr); } }
  .stat-card {
    background:white; border-radius:14px; padding:18px;
    box-shadow:0 4px 14px rgba(0,0,0,0.05);
  }
  .stat-icon { width:40px; height:40px; border-radius:10px;
    display:flex; align-items:center; justify-content:center;
    font-size:18px; margin-bottom:12px; }
  .stat-num { font-size:22px; font-weight:800; color:#0d1c2e; }
  .stat-lbl { font-size:12px; color:#8fa3b8; margin-top:3px; }

  /* Table */
  .tbl { width:100%; border-collapse:collapse; }
  .tbl th { font-size:11px; font-weight:700; color:#8fa3b8;
    text-transform:uppercase; padding:8px 12px; text-align:left;
    border-bottom:1px solid #f0f6fc; }
  .tbl td { padding:12px; font-size:13px; color:#0d1c2e;
    border-bottom:1px solid #f8fbfe; vertical-align:middle; }
  .tbl tr:last-child td { border-bottom:none; }
  .tbl tr:hover td { background:#fafcff; }

  /* Buttons */
  .btn { display:inline-flex; align-items:center; gap:6px;
    padding:9px 18px; border-radius:10px; font-size:13px;
    font-weight:700; cursor:pointer; border:none; transition:0.2s; }
  .btn-primary { background:#2a85ff; color:white; }
  .btn-primary:hover { opacity:0.9; }
  .btn-outline { background:white; color:#2a85ff; border:1.5px solid #2a85ff; }
  .btn-outline:hover { background:#eef5fc; }
  .btn-danger { background:white; color:#e53935; border:1.5px solid #fcc; }
  .btn-danger:hover { background:#fff0f0; }
  .btn-sm { padding:6px 12px; font-size:12px; }

  /* Status badge */
  .status { display:inline-block; padding:3px 10px; border-radius:20px;
    font-size:11px; font-weight:700; }
  .status-aktif { background:#e6f9f0; color:#10b981; }
  .status-nonaktif { background:#f0f0f0; color:#888; }

  /* Form */
  .form-group { margin-bottom:14px; }
  .form-label { font-size:12px; font-weight:700; color:#556980; margin-bottom:5px; display:block; }
  .form-input { width:100%; padding:11px 14px; border:1.5px solid #d4e3f3;
    border-radius:10px; font-size:13px; outline:none; transition:border-color 0.2s; }
  .form-input:focus { border-color:#2a85ff; }
  .form-row { display:flex; gap:14px; }
  .form-row .form-group { flex:1; }

  /* Alert */
  .alert { padding:12px 16px; border-radius:10px; font-size:13px;
    font-weight:600; margin-bottom:16px; }
  .alert-success { background:#e6f9f0; color:#10b981; }
  .alert-error   { background:#fff0f0; color:#e53935; }

  @media(max-width:768px){
    .sidebar { display:none; }
    .main-content { margin-left:0; padding:20px 16px 80px; max-width:100%; }
  }
</style>

<div class="sidebar">
  <div class="sidebar-brand" style="flex-direction: column; align-items: center; gap: 0; padding: 10px 16px 10px; border-bottom: none; margin-bottom: 0;">
    <div style="width: 100%; border-bottom: 1px solid #e0ecf8; padding-bottom: 15px; margin-bottom: 15px; text-align: center;">
      <img src="../Logo.svg" alt="LokalThrift" style="width:120px; height:auto;">
    </div>
    <div style="display:flex; flex-direction:column; align-items:center; gap:8px; font-size:16px; font-weight:800; color:#2a85ff; text-align:center; line-height:1.2; width:100%;">
      <?php if (!empty($toko['foto_toko'])): ?>
        <img src="../<?= htmlspecialchars($toko['foto_toko']) ?>" style="width:60px; height:60px; border-radius:12px; object-fit:cover; flex-shrink:0;">
      <?php else: ?>
        <i class="fa-solid fa-store" style="font-size:32px;"></i>
      <?php endif; ?>
      <span><?= htmlspecialchars($toko['nama_toko'] ?? 'Toko Saya') ?></span>
    </div>
  </div>

  <div class="sidebar-section">Menu</div>
  <a href="dashboard.php" class="nav-item <?= navActive('dashboard.php') ?>">
    <i class="fa-solid fa-house"></i> Dashboard
  </a>
  <a href="produk.php" class="nav-item <?= navActive('produk.php') ?>">
    <i class="fa-solid fa-shirt"></i> Produk
  </a>
  <a href="pesanan.php" class="nav-item <?= navActive('pesanan.php') ?>">
    <i class="fa-solid fa-receipt"></i> Pesanan
  </a>
  <a href="chat.php" class="nav-item <?= navActive('chat.php') ?>">
    <i class="fa-solid fa-message"></i> Chat Pelanggan
  </a>

  <div class="sidebar-section">Pengaturan</div>
  <a href="profil_toko.php" class="nav-item <?= navActive('profil_toko.php') ?>">
    <i class="fa-solid fa-gear"></i> Profil Toko
  </a>

  <div class="sidebar-bottom">
    <a href="../home.php" class="nav-item">
      <i class="fa-solid fa-arrow-left"></i> Kembali ke Pembeli
    </a>
    <a href="../logout.php" class="nav-item danger">
      <i class="fa-solid fa-right-from-bracket"></i> Logout
    </a>
  </div>
</div>
