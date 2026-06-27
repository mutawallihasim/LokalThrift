<?php
// Data simulasi (bisa diganti dengan koneksi database)
$store_name = "Thrift House";
$stats = [
    'dilihat'       => 1250,
    'pesanan'       => 32,
    'penjualan'     => 2350000,
    'produk_terjual'=> 45,
];
$ringkasan = [
    ['label' => 'Menunggu Pembayaran', 'icon' => '⏰', 'count' => 8,  'color' => '#f59e0b'],
    ['label' => 'Dikemas',            'icon' => '📦', 'count' => 12, 'color' => '#3b82f6'],
    ['label' => 'Dikirim',            'icon' => '🚚', 'count' => 15, 'color' => '#8b5cf6'],
    ['label' => 'Selesai',            'icon' => '✅', 'count' => 25, 'color' => '#10b981'],
    ['label' => 'Dibatalkan',         'icon' => '❌', 'count' => 3,  'color' => '#ef4444'],
];
$produk_terbaru = [
    ['nama' => 'nama', 'harga' => 50000,  'img' => 'https://images.unsplash.com/photo-1620799140408-edc6dcb6d633?w=200&q=80'],
    ['nama' => 'nama', 'harga' => 70000,  'img' => 'https://images.unsplash.com/photo-1594938298603-c8148c4b4dbe?w=200&q=80'],
    ['nama' => 'nama', 'harga' => 55000,  'img' => 'https://images.unsplash.com/photo-1614251055880-ee96e4803393?w=200&q=80'],
    ['nama' => 'nama', 'harga' => 42000,  'img' => 'https://images.unsplash.com/photo-1551489186-cf8726f514f8?w=200&q=80'],
];
$pengumuman = [
    ['judul' => 'Update Sistem', 'isi' => 'Mulai 5 Mei 2024, biaya layanan akan berubah.'],
];
$tips = [
    ['ikon' => '📝', 'judul' => 'Lengkapi deskripsi produk',   'isi' => 'Produk dengan deskripsi lengkap lebih mudah terjual.'],
    ['ikon' => '📸', 'judul' => 'Gunakan foto yang menarik',  'isi' => 'Foto cerah dengan latar bersih meningkatkan kepercayaan pembeli.'],
    ['ikon' => '🏷️', 'judul' => 'Aktifkan Promosi Toko',     'isi' => 'Tingkatkan visibilitas tokomu agar dapat dijangkau lebih banyak pembeli.'],
];

function rupiah($n) { return 'Rp ' . number_format($n, 0, ',', '.'); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dasboard Penjual – LokalThrift</title>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --bg: #dce9f5;
    --sidebar-bg: #ffffff;
    --card-bg: #ffffff;
    --accent: #4a9fd4;
    --accent-dark: #2d7ab8;
    --text: #1e293b;
    --muted: #64748b;
    --green: #10b981;
    --red: #ef4444;
    --yellow: #f59e0b;
    --blue: #3b82f6;
    --purple: #8b5cf6;
    --border: #e2e8f0;
    --radius: 12px;
    --sidebar-w: 180px;
  }

  body {
    font-family: 'Segoe UI', system-ui, sans-serif;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
  }

  /* ── TOP BAR ── */
  .topbar {
    background: var(--bg);
    padding: 10px 24px;
    font-size: 13px;
    color: var(--muted);
    border-bottom: 1px solid #c8ddef;
  }

  /* ── LAYOUT ── */
  .layout { display: flex; flex: 1; }

  /* ── SIDEBAR ── */
  .sidebar {
    width: var(--sidebar-w);
    background: var(--sidebar-bg);
    padding: 24px 0;
    display: flex;
    flex-direction: column;
    gap: 4px;
    border-right: 1px solid var(--border);
    position: sticky;
    top: 0;
    height: 100vh;
  }
  .sidebar .logo {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 0 20px 20px;
    border-bottom: 1px solid var(--border);
    margin-bottom: 8px;
  }
  .sidebar .logo .logo-icon {
    background: var(--accent);
    color: #fff;
    border-radius: 10px;
    width: 36px; height: 36px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px;
  }
  .sidebar .logo span { font-weight: 700; font-size: 15px; color: var(--accent-dark); }
  .sidebar .logo span em { color: var(--text); font-style: normal; }

  .nav-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 20px;
    font-size: 14px;
    color: var(--text);
    cursor: pointer;
    border-radius: 8px;
    margin: 0 8px;
    text-decoration: none;
    transition: background .15s;
  }
  .nav-item:hover { background: var(--bg); }
  .nav-item.active { background: var(--bg); font-weight: 600; color: var(--accent-dark); }
  .nav-item .nav-icon { font-size: 16px; width: 20px; text-align: center; }

  /* ── MAIN ── */
  .main {
    flex: 1;
    padding: 20px 24px 80px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  /* ── SEARCH ── */
  .search-bar {
    background: var(--card-bg);
    border-radius: 30px;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 20px;
    box-shadow: 0 1px 4px rgba(0,0,0,.06);
    position: relative;
  }
  .search-bar input {
    border: none;
    outline: none;
    font-size: 14px;
    width: 100%;
    color: var(--text);
    background: transparent;
  }
  .search-bar .bell {
    position: absolute;
    right: 20px;
    font-size: 18px;
    color: var(--muted);
    cursor: pointer;
  }

  /* ── TOP ROW ── */
  .top-row { display: grid; grid-template-columns: 1fr 2fr; gap: 16px; }

  .welcome-card {
    background: var(--card-bg);
    border-radius: var(--radius);
    padding: 24px 20px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    box-shadow: 0 1px 4px rgba(0,0,0,.06);
  }
  .welcome-card h2 { font-size: 17px; font-weight: 700; margin-bottom: 8px; }
  .welcome-card p { font-size: 13px; color: var(--muted); line-height: 1.5; }

  .performa-card {
    background: var(--card-bg);
    border-radius: var(--radius);
    padding: 18px 20px;
    box-shadow: 0 1px 4px rgba(0,0,0,.06);
  }
  .performa-card .pc-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 14px;
  }
  .performa-card .pc-header h3 { font-size: 15px; font-weight: 600; }
  .performa-card .pc-header .periode {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 20px;
    font-size: 12px;
    padding: 4px 10px;
    color: var(--muted);
    cursor: pointer;
  }
  .performa-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
  .perf-item { text-align: center; }
  .perf-item .perf-label { font-size: 11px; color: var(--muted); margin-bottom: 4px; display: flex; align-items: center; justify-content: center; gap: 4px; }
  .perf-item .perf-val { font-size: 18px; font-weight: 700; }
  .perf-item .perf-change { font-size: 11px; margin-top: 2px; }
  .perf-change.up { color: var(--green); }
  .perf-change.down { color: var(--red); }

  /* ── BOTTOM ROW ── */
  .bottom-row { display: grid; grid-template-columns: 1fr 260px; gap: 16px; }
  .bottom-left { display: flex; flex-direction: column; gap: 16px; }

  /* Ringkasan */
  .ringkasan-card {
    background: var(--card-bg);
    border-radius: var(--radius);
    padding: 18px 20px;
    box-shadow: 0 1px 4px rgba(0,0,0,.06);
  }
  .ringkasan-card h3 { font-size: 15px; font-weight: 600; margin-bottom: 14px; }
  .ringkasan-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; }
  .ring-item {
    text-align: center;
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 12px 6px;
  }
  .ring-item .ring-icon { font-size: 20px; margin-bottom: 6px; }
  .ring-item .ring-count { font-size: 22px; font-weight: 700; }
  .ring-item .ring-label { font-size: 11px; color: var(--muted); margin-bottom: 8px; }
  .ring-item .ring-link { font-size: 11px; color: var(--accent); text-decoration: none; }
  .ring-item .ring-link:hover { text-decoration: underline; }

  /* Produk Terbaru */
  .produk-card {
    background: var(--card-bg);
    border-radius: var(--radius);
    padding: 18px 20px;
    box-shadow: 0 1px 4px rgba(0,0,0,.06);
  }
  .produk-card h3 { font-size: 15px; font-weight: 600; margin-bottom: 14px; }
  .produk-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
  .produk-item img {
    width: 100%;
    aspect-ratio: 1;
    object-fit: cover;
    border-radius: 10px;
    background: var(--bg);
    display: block;
    margin-bottom: 8px;
  }
  .produk-item .p-nama { font-size: 13px; font-weight: 500; }
  .produk-item .p-harga { font-size: 13px; color: var(--muted); }

  /* Right column */
  .right-col { display: flex; flex-direction: column; gap: 16px; }

  .pengumuman-card, .tips-card {
    background: var(--card-bg);
    border-radius: var(--radius);
    padding: 18px 16px;
    box-shadow: 0 1px 4px rgba(0,0,0,.06);
  }
  .pengumuman-card h3, .tips-card h3 {
    font-size: 15px; font-weight: 600; margin-bottom: 12px;
  }
  .pengumuman-item {
    background: #fef9ec;
    border: 1px solid #fde68a;
    border-radius: 8px;
    padding: 10px 12px;
    font-size: 12px;
    margin-bottom: 10px;
  }
  .pengumuman-item strong { display: block; margin-bottom: 4px; font-size: 13px; }
  .pengumuman-item a { color: var(--accent); font-size: 12px; }
  .btn-lihat-semua {
    display: block;
    text-align: center;
    font-size: 13px;
    color: var(--accent-dark);
    border: 1px solid var(--accent);
    border-radius: 8px;
    padding: 7px;
    text-decoration: none;
    transition: background .15s;
  }
  .btn-lihat-semua:hover { background: var(--bg); }

  .tips-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px solid var(--border);
    cursor: pointer;
  }
  .tips-item:last-of-type { border-bottom: none; }
  .tips-item .t-icon {
    background: var(--bg);
    border-radius: 8px;
    width: 32px; height: 32px;
    display: flex; align-items: center; justify-content: center;
    font-size: 15px;
    flex-shrink: 0;
  }
  .tips-item .t-body { flex: 1; }
  .tips-item .t-title { font-size: 13px; font-weight: 600; }
  .tips-item .t-desc { font-size: 11px; color: var(--muted); line-height: 1.4; margin-top: 2px; }
  .tips-item .t-arrow { color: var(--muted); font-size: 14px; align-self: center; }

  /* ── BOTTOM NAV ── */
  .bottom-nav {
    position: fixed;
    bottom: 0; left: 0; right: 0;
    background: var(--card-bg);
    border-top: 1px solid var(--border);
    display: flex;
    justify-content: space-around;
    padding: 10px 0 14px;
    z-index: 100;
  }
  .bnav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    color: var(--muted);
    text-decoration: none;
    cursor: pointer;
  }
  .bnav-item.active { color: var(--accent-dark); }
  .bnav-item .bn-icon { font-size: 20px; }
  .bnav-item.tambah .bn-icon {
    background: var(--accent-dark);
    color: #fff;
    border-radius: 50%;
    width: 40px; height: 40px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px;
    margin-top: -10px;
  }

  @media (max-width: 768px) {
    .sidebar { display: none; }
    .top-row { grid-template-columns: 1fr; }
    .performa-grid { grid-template-columns: repeat(2,1fr); }
    .ringkasan-grid { grid-template-columns: repeat(3,1fr); }
    .bottom-row { grid-template-columns: 1fr; }
    .produk-grid { grid-template-columns: repeat(2,1fr); }
    .right-col { display: none; }
  }
</style>
</head>
<body>

<!-- TOP BAR -->
<div class="topbar">Dasboard Penjual</div>

<div class="layout">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="logo">
      <div class="logo-icon">🏠</div>
      <span>Lokal<em>Thrift</em></span>
    </div>
    <a href="#" class="nav-item active">
      <span class="nav-icon">🏠</span> Dashboard
    </a>
    <a href="#" class="nav-item">
      <span class="nav-icon">🏷️</span> Produk
    </a>
    <a href="#" class="nav-item">
      <span class="nav-icon">📦</span> Pesanan
    </a>
    <a href="#" class="nav-item">
      <span class="nav-icon">💸</span> Diskon
    </a>
    <a href="#" class="nav-item">
      <span class="nav-icon">💬</span> Chat
    </a>
    <a href="#" class="nav-item">
      <span class="nav-icon">🚚</span> Pengiriman
    </a>
    <a href="#" class="nav-item">
      <span class="nav-icon">💰</span> Keuangan
    </a>
    <a href="#" class="nav-item">
      <span class="nav-icon">🏪</span> Toko
    </a>
    <a href="#" class="nav-item">
      <span class="nav-icon">⚙️</span> Pengaturan
    </a>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="main">

    <!-- SEARCH + BELL -->
    <div class="search-bar">
      <span>🔍</span>
      <input type="text" placeholder="Cari produk thirft...">
      <span class="bell">🔔</span>
    </div>

    <!-- WELCOME + PERFORMA -->
    <div class="top-row">
      <div class="welcome-card">
        <h2>Selamat Datang, <?= htmlspecialchars($store_name) ?>!</h2>
        <p>Kelola tokomu dengan mudah dan tingkatkan penjualan.</p>
      </div>

      <div class="performa-card">
        <div class="pc-header">
          <h3>Performa Toko</h3>
          <span class="periode">1 bulan terakhir ▾</span>
        </div>
        <div class="performa-grid">
          <div class="perf-item">
            <div class="perf-label">👁️ Dilihat</div>
            <div class="perf-val"><?= number_format($stats['dilihat'], 0, ',', '.') ?></div>
            <div class="perf-change up">▲ 9%</div>
          </div>
          <div class="perf-item">
            <div class="perf-label">📦 Pesanan</div>
            <div class="perf-val"><?= $stats['pesanan'] ?></div>
            <div class="perf-change up">▲ 12%</div>
          </div>
          <div class="perf-item">
            <div class="perf-label">💰 Penjualan</div>
            <div class="perf-val" style="font-size:14px"><?= rupiah($stats['penjualan']) ?></div>
            <div class="perf-change down">▼ 0%</div>
          </div>
          <div class="perf-item">
            <div class="perf-label">🛒 Produk Terjual</div>
            <div class="perf-val"><?= $stats['produk_terjual'] ?></div>
            <div class="perf-change up">▲ 92%</div>
          </div>
        </div>
      </div>
    </div>

    <!-- BOTTOM ROW -->
    <div class="bottom-row">
      <div class="bottom-left">

        <!-- RINGKASAN PESANAN -->
        <div class="ringkasan-card">
          <h3>Ringkas Pesanan</h3>
          <div class="ringkasan-grid">
            <?php foreach ($ringkasan as $r): ?>
            <div class="ring-item">
              <div class="ring-icon"><?= $r['icon'] ?></div>
              <div class="ring-count" style="color:<?= $r['color'] ?>"><?= $r['count'] ?></div>
              <div class="ring-label"><?= $r['label'] ?></div>
              <a href="#" class="ring-link">Lihat Pesanan</a>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- PRODUK TERBARU -->
        <div class="produk-card">
          <h3>Produk Terbaru</h3>
          <div class="produk-grid">
            <?php foreach ($produk_terbaru as $p): ?>
            <div class="produk-item">
              <img src="<?= htmlspecialchars($p['img']) ?>" alt="<?= htmlspecialchars($p['nama']) ?>" loading="lazy">
              <div class="p-nama"><?= htmlspecialchars($p['nama']) ?></div>
              <div class="p-harga"><?= rupiah($p['harga']) ?></div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

      </div><!-- /bottom-left -->

      <!-- RIGHT COLUMN -->
      <div class="right-col">

        <!-- PENGUMUMAN -->
        <div class="pengumuman-card">
          <h3>Pengumuman</h3>
          <?php foreach ($pengumuman as $peng): ?>
          <div class="pengumuman-item">
            <strong><?= htmlspecialchars($peng['judul']) ?></strong>
            <?= htmlspecialchars($peng['isi']) ?>
            <br><a href="#">Selengkapnya</a>
          </div>
          <?php endforeach; ?>
          <a href="#" class="btn-lihat-semua">Lihat semua pengumuman</a>
        </div>

        <!-- TIPS -->
        <div class="tips-card">
          <h3>Tips Untukmu</h3>
          <?php foreach ($tips as $t): ?>
          <div class="tips-item">
            <div class="t-icon"><?= $t['ikon'] ?></div>
            <div class="t-body">
              <div class="t-title"><?= htmlspecialchars($t['judul']) ?></div>
              <div class="t-desc"><?= htmlspecialchars($t['isi']) ?></div>
            </div>
            <span class="t-arrow">›</span>
          </div>
          <?php endforeach; ?>
          <br>
          <a href="#" class="btn-lihat-semua">Lihat Semua Tips</a>
        </div>

      </div><!-- /right-col -->
    </div><!-- /bottom-row -->

  </main>
</div>

<!-- BOTTOM NAVIGATION -->
<nav class="bottom-nav">
  <a href="#" class="bnav-item active">
    <span class="bn-icon">🏠</span> Beranda
  </a>
  <a href="#" class="bnav-item">
    <span class="bn-icon">📦</span> Order
  </a>
  <a href="#" class="bnav-item tambah">
    <span class="bn-icon">＋</span> Tambahkan
  </a>
  <a href="#" class="bnav-item">
    <span class="bn-icon">🛒</span> Keranjang
  </a>
  <a href="#" class="bnav-item">
    <span class="bn-icon">👤</span> Akun
  </a>
</nav>

</body>
</html>