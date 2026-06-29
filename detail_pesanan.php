<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['id_pengguna'])) {
    header('Location: login.php'); exit;
}

$invoice     = isset($_GET['invoice']) ? mysqli_real_escape_string($koneksi, $_GET['invoice']) : '';
$id_pengguna = (int) $_SESSION['id_pengguna'];

// Ambil data pesanan
$pesanan = null;
$items   = [];

if ($invoice) {
    $q = mysqli_query($koneksi,
        "SELECT * FROM pesanan WHERE invoice='$invoice' AND id_pengguna=$id_pengguna LIMIT 1");
    if ($q) $pesanan = mysqli_fetch_assoc($q);

    if ($pesanan) {
        $id_p  = (int) $pesanan['id_pesanan'];
        $qItem = mysqli_query($koneksi, "SELECT * FROM pesanan_item WHERE id_pesanan=$id_p");
        if ($qItem) while ($row = mysqli_fetch_assoc($qItem)) $items[] = $row;
    }
}

// Format tanggal Indonesia
function tglIndo($dt) {
    $bulan = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
    $d = date('j', strtotime($dt));
    $m = $bulan[(int)date('n', strtotime($dt))];
    $y = date('Y', strtotime($dt));
    $t = date('H:i', strtotime($dt));
    return "$d $m $y · $t";
}

$badgeClass = [
    'diproses'   => 'badge-diproses',
    'dikirim'    => 'badge-dikirim',
    'selesai'    => 'badge-selesai',
    'dibatalkan' => 'badge-dibatalkan',
];
$badgeLabel = [
    'diproses'   => 'Diproses',
    'dikirim'    => 'Dikirim',
    'selesai'    => 'Selesai',
    'dibatalkan' => 'Dibatalkan',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Detail Pesanan - LokalThrift</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    *{ margin:0; padding:0; box-sizing:border-box; font-family:'Helvetica Neue',Arial,sans-serif; }
    body { background:#eef5fc; min-height:100vh; display:flex; flex-direction:column; }

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
      .main { padding: 30px 40px 60px; max-width: 860px; }
    }

    /* ── MAIN ── */
    .main { width:100%; max-width:720px; margin:0 auto; padding:24px 16px 110px; }

    /* Back link */
    .btn-back { display:inline-flex; align-items:center; gap:7px; color:#556980;
      text-decoration:none; font-size:14px; font-weight:600; margin-bottom:18px; }
    .btn-back:hover { color:#2a85ff; }

    /* ── HEADER CARD ── */
    .header-card {
      background:white; border-radius:16px; padding:18px 20px;
      box-shadow:0 4px 16px rgba(0,0,0,0.05); margin-bottom:14px;
      display:flex; justify-content:space-between; align-items:flex-start;
    }
    .invoice-no { font-size:16px; font-weight:800; color:#0d1c2e; }
    .invoice-tgl { font-size:12px; color:#8fa3b8; margin-top:4px; }
    .badge { font-size:12px; font-weight:700; padding:5px 14px; border-radius:20px; flex-shrink:0; }
    .badge-diproses   { background:#fff4e0; color:#f5a623; }
    .badge-dikirim    { background:#e0f0ff; color:#2a85ff; }
    .badge-selesai    { background:#e6f9f0; color:#10b981; }
    .badge-dibatalkan { background:#fff0f0; color:#e53935; }

    /* ── DETAIL CARD ── */
    .detail-card {
      background:white; border-radius:16px; padding:0;
      box-shadow:0 4px 16px rgba(0,0,0,0.05); margin-bottom:14px;
      overflow:hidden;
    }

    .two-col { display:flex; flex-direction:column; }
    @media (min-width:560px) { .two-col { flex-direction:row; } }

    /* Kiri — alamat & kurir */
    .col-left {
      padding:20px; border-bottom:1px solid #f0f6fc;
      min-width:200px;
    }
    @media (min-width:560px) {
      .col-left { border-bottom:none; border-right:1px solid #f0f6fc; width:45%; }
    }

    .col-title { font-size:13px; font-weight:800; color:#0d1c2e; margin-bottom:10px; }
    .col-sub   { font-size:12px; color:#556980; line-height:1.65; margin-bottom:16px; }
    .col-sub strong { display:block; font-size:13px; color:#0d1c2e; font-weight:700; }

    .kurir-row { display:flex; justify-content:space-between; align-items:center;
      font-size:13px; color:#0d1c2e; }
    .kurir-row span { color:#2a85ff; font-weight:700; }

    /* Kanan — list produk */
    .col-right { padding:20px; flex:1; }

    .produk-row { display:flex; align-items:center; gap:12px;
      padding:10px 0; border-bottom:1px solid #f0f6fc; }
    .produk-row:last-child { border-bottom:none; }
    .produk-img { width:52px; height:52px; border-radius:10px;
      object-fit:cover; flex-shrink:0; background:#f0f6fc; }
    .produk-info { flex:1; min-width:0; }
    .produk-nama  { font-size:13px; font-weight:700; color:#0d1c2e; }
    .produk-varian{ font-size:11px; color:#8fa3b8; margin-top:2px; }
    .produk-harga { font-size:13px; font-weight:700; color:#0d1c2e; white-space:nowrap; }
    .produk-qty   { font-size:12px; color:#8fa3b8; margin-left:4px; }

    /* Total row */
    .total-row {
      display:flex; justify-content:space-between; align-items:center;
      padding:14px 20px 0;
      border-top:1px solid #f0f6fc; margin:0 0 4px;
    }
    .total-label { font-size:14px; font-weight:800; color:#0d1c2e; }
    .total-nilai  { font-size:16px; font-weight:800; color:#0d1c2e; }

    /* ── TOMBOL BELI LAGI & LACAK ── */
    .action-btns { display:flex; gap:10px; padding:14px 20px; }
    .btn-beli {
      flex:2; padding:14px;
      background:#2a85ff; color:white; border:none;
      border-radius:14px; font-size:14px; font-weight:700;
      text-align:center; text-decoration:none; cursor:pointer;
      transition:opacity 0.2s;
    }
    .btn-beli:hover { opacity:0.9; }

    .btn-lacak {
      flex:1; padding:14px;
      background:white; color:#2a85ff;
      border:1.5px solid #2a85ff;
      border-radius:14px; font-size:14px; font-weight:700;
      text-align:center; cursor:pointer;
      transition:background 0.2s;
    }
    .btn-lacak:hover { background:#eef5fc; }

    /* ── TRACKING PANEL ── */
    .tracking-card {
      background:white; border-radius:16px; padding:22px 20px;
      box-shadow:0 4px 16px rgba(0,0,0,0.05); margin-bottom:14px;
      display:none; /* toggle by JS */
    }
    .tracking-card.open { display:block; }
    .tracking-title {
      font-size:14px; font-weight:800; color:#0d1c2e;
      margin-bottom:6px;
    }
    .tracking-sub {
      font-size:12px; color:#8fa3b8; margin-bottom:24px;
    }

    /* Timeline horizontal */
    .timeline {
      display:flex; align-items:flex-start;
      justify-content:space-between;
      position:relative; padding:0 8px;
    }
    .timeline::before {
      content:''; position:absolute;
      top:18px; left:30px; right:30px;
      height:2px; background:#e0ecf8; z-index:0;
    }
    .tl-step {
      display:flex; flex-direction:column; align-items:center;
      flex:1; position:relative; z-index:1;
    }
    .tl-icon {
      width:36px; height:36px; border-radius:50%;
      border:2px solid #d4e3f3; background:white;
      display:flex; align-items:center; justify-content:center;
      font-size:15px; color:#c8dff5;
      transition:0.3s; margin-bottom:8px;
    }
    .tl-step.done .tl-icon {
      background:#2a85ff; border-color:#2a85ff; color:white;
      box-shadow:0 3px 10px rgba(42,133,255,0.3);
    }
    .tl-step.active .tl-icon {
      background:#2a85ff; border-color:#2a85ff; color:white;
      box-shadow:0 3px 10px rgba(42,133,255,0.3);
    }
    .tl-label { font-size:11px; font-weight:700; color:#c8dff5; text-align:center; }
    .tl-step.done .tl-label,
    .tl-step.active .tl-label { color:#2a85ff; }
    .tl-date { font-size:10px; color:#aab; margin-top:2px; text-align:center; }

    /* Riwayat log */
    .log-list { margin-top:20px; border-top:1px solid #f0f6fc; padding-top:16px; }
    .log-item {
      display:flex; gap:14px; padding:10px 0;
      border-bottom:1px solid #f8fbfe;
    }
    .log-item:last-child { border-bottom:none; }
    .log-dot {
      width:10px; height:10px; border-radius:50%;
      background:#c8dff5; flex-shrink:0; margin-top:4px;
    }
    .log-item.log-current .log-dot { background:#2a85ff; }
    .log-text { font-size:13px; font-weight:600; color:#0d1c2e; }
    .log-time { font-size:11px; color:#8fa3b8; margin-top:2px; }
    /* ── NOT FOUND ── */
    .not-found { text-align:center; padding:60px 20px; color:#aab; }
    .not-found i { font-size:48px; color:#c8dff5; display:block; margin-bottom:14px; }
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
  <a href="pesanan.php" class="nav-item active">
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
<div class="main">

  <a href="pesanan.php" class="btn-back">
    <i class="fa-solid fa-arrow-left"></i> Detail Pesanan
  </a>

<?php if (!$pesanan): ?>
  <div class="not-found">
    <i class="fa-solid fa-receipt"></i>
    <p>Pesanan tidak ditemukan.</p>
  </div>

<?php else:
  $status = $pesanan['status'];
  $total  = (int) $pesanan['total'];
?>

  <!-- HEADER: Invoice + Status -->
  <div class="header-card">
    <div>
      <div class="invoice-no"><?= htmlspecialchars($pesanan['invoice']) ?></div>
      <div class="invoice-tgl"><?= tglIndo($pesanan['created_at']) ?></div>
    </div>
    <span class="badge <?= $badgeClass[$status] ?? 'badge-diproses' ?>">
      <?= $badgeLabel[$status] ?? ucfirst($status) ?>
    </span>
  </div>

  <!-- DETAIL: Alamat + Produk -->
  <div class="detail-card">
    <div class="two-col">

      <!-- KIRI: Alamat & Kurir -->
      <div class="col-left">
        <div class="col-title">Alamat Pengiriman</div>
        <div class="col-sub">
          <?php
            // Parse alamat sederhana
            $alamat = $pesanan['alamat'] ?? '';
            echo nl2br(htmlspecialchars($alamat)) ?: '<em style="color:#aab">Tidak ada alamat</em>';
          ?>
        </div>

        <div class="col-title">Metode Pengiriman</div>
        <div class="kurir-row">
          <span style="color:#0d1c2e;font-weight:600">
            <?= htmlspecialchars($pesanan['kurir'] ?? 'JNE Reguler') ?>
          </span>
          <?php
            // Hitung ongkir = total - subtotal item
            $subtotalItem = array_sum(array_column($items, 'harga'));
            $ongkir = $total - $subtotalItem;
            if ($ongkir > 0):
          ?>
          <span>Rp <?= number_format($ongkir, 0, ',', '.') ?></span>
          <?php endif; ?>
        </div>
      </div>

      <!-- KANAN: List Produk -->
      <div class="col-right">
        <div class="col-title">Produk</div>

        <?php foreach ($items as $item): ?>
        <div class="produk-row">
          <img class="produk-img"
               src="<?= htmlspecialchars($item['gambar'] ?? '') ?>"
               alt="<?= htmlspecialchars($item['nama']) ?>"
               onerror="this.src='https://via.placeholder.com/52'">
          <div class="produk-info">
            <div class="produk-nama"><?= htmlspecialchars($item['nama']) ?></div>
            <div class="produk-varian"><?= htmlspecialchars($item['varian'] ?? '') ?></div>
          </div>
          <div>
            <span class="produk-harga">Rp <?= number_format($item['harga'], 0, ',', '.') ?></span>
            <span class="produk-qty">×1</span>
          </div>
        </div>
        <?php endforeach; ?>

        <?php if (empty($items)): ?>
          <p style="font-size:13px;color:#aab;padding:10px 0">Tidak ada item</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- TOTAL -->
    <div class="total-row">
      <span class="total-label">Total Pembayaran</span>
      <span class="total-nilai">Rp <?= number_format($total, 0, ',', '.') ?></span>
    </div>
    <div class="action-btns">
      <button class="btn-lacak" onclick="toggleTracking()">
        <i class="fa-solid fa-location-dot" style="margin-right:5px"></i>Lacak Pesanan
      </button>
      <a href="home.php" class="btn-beli">Beli Lagi</a>
    </div>
  </div>

  <!-- TRACKING PANEL -->
  <div class="tracking-card" id="tracking-panel">
    <div class="tracking-title">Lacak Pesanan</div>
    <div class="tracking-sub">
      <?= htmlspecialchars($pesanan['kurir'] ?? 'JNE Reguler') ?> &nbsp;·&nbsp; Invoice: <?= htmlspecialchars($pesanan['invoice']) ?>
    </div>

    <?php
      // Tentukan step aktif berdasarkan status
      $stepMap = ['diproses'=>0, 'dikirim'=>2, 'selesai'=>3, 'dibatalkan'=>-1];
      $activeStep = $stepMap[$status] ?? 0;
      $steps = [
        ['icon'=>'fa-solid fa-box',            'label'=>'Pesanan Dibuat',  'done'=>$activeStep>=0],
        ['icon'=>'fa-solid fa-store',           'label'=>'Dikemas',        'done'=>$activeStep>=1],
        ['icon'=>'fa-solid fa-truck',           'label'=>'Dikirim',        'done'=>$activeStep>=2],
        ['icon'=>'fa-solid fa-circle-check',    'label'=>'Diterima',       'done'=>$activeStep>=3],
      ];
      $created = tglIndo($pesanan['created_at']);
    ?>

    <div class="timeline">
      <?php foreach ($steps as $i => $s): ?>
        <?php $cls = $s['done'] ? ($i === $activeStep ? 'active' : 'done') : ''; ?>
        <div class="tl-step <?= $cls ?>">
          <div class="tl-icon"><i class="<?= $s['icon'] ?>"></i></div>
          <div class="tl-label"><?= $s['label'] ?></div>
          <?php if ($i === 0): ?>
            <div class="tl-date"><?= date('d M', strtotime($pesanan['created_at'])) ?></div>
          <?php else: ?>
            <div class="tl-date"><?= $s['done'] ? date('d M', strtotime($pesanan['created_at']) + $i*86400) : '' ?></div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Log riwayat -->
    <div class="log-list">
      <?php if ($status === 'selesai'): ?>
        <div class="log-item log-current">
          <div class="log-dot"></div>
          <div><div class="log-text">Paket telah diterima oleh penerima</div>
          <div class="log-time"><?= $created ?></div></div>
        </div>
      <?php endif; ?>
      <?php if (in_array($status, ['dikirim','selesai'])): ?>
        <div class="log-item <?= $status==='dikirim' ? 'log-current' : '' ?>">
          <div class="log-dot"></div>
          <div><div class="log-text">Paket sedang dalam pengiriman</div>
          <div class="log-time"><?= $created ?></div></div>
        </div>
      <?php endif; ?>
      <div class="log-item <?= $status==='diproses' ? 'log-current' : '' ?>">
        <div class="log-dot"></div>
        <div><div class="log-text">Pesanan sedang dikemas oleh penjual</div>
        <div class="log-time"><?= $created ?></div></div>
      </div>
      <div class="log-item">
        <div class="log-dot"></div>
        <div><div class="log-text">Pembayaran berhasil dikonfirmasi</div>
        <div class="log-time"><?= $created ?></div></div>
      </div>
      <div class="log-item">
        <div class="log-dot"></div>
        <div><div class="log-text">Pesanan berhasil dibuat</div>
        <div class="log-time"><?= $created ?></div></div>
      </div>
    </div>
  </div>

  <script>
    function toggleTracking() {
      const panel = document.getElementById('tracking-panel');
      const btn   = document.querySelector('.btn-lacak');
      panel.classList.toggle('open');
      if (panel.classList.contains('open')) {
        btn.innerHTML = '<i class="fa-solid fa-chevron-up" style="margin-right:5px"></i>Sembunyikan';
        panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
      } else {
        btn.innerHTML = '<i class="fa-solid fa-location-dot" style="margin-right:5px"></i>Lacak Pesanan';
      }
    }
  </script>

<?php endif; ?>

</div>
</div>
</body>
</html>
