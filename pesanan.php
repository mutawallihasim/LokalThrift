<?php
session_start();
require 'koneksi.php';

// Redirect ke login jika belum masuk
if (!isset($_SESSION['id_pengguna'])) {
    header('Location: login.php');
    exit;
}

$id_pengguna = (int) $_SESSION['id_pengguna'];

// Buat tabel jika belum ada
mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS `pesanan` (
  `id_pesanan`   INT AUTO_INCREMENT PRIMARY KEY,
  `id_pengguna`  INT NOT NULL,
  `invoice`      VARCHAR(30) NOT NULL UNIQUE,
  `status`       ENUM('diproses','dikirim','selesai','dibatalkan') NOT NULL DEFAULT 'diproses',
  `total`        INT NOT NULL DEFAULT 0,
  `metode_bayar` VARCHAR(50) DEFAULT NULL,
  `kurir`        VARCHAR(100) DEFAULT NULL,
  `alamat`       TEXT DEFAULT NULL,
  `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS `pesanan_item` (
  `id_item`    INT AUTO_INCREMENT PRIMARY KEY,
  `id_pesanan` INT NOT NULL,
  `nama`       VARCHAR(200) NOT NULL,
  `varian`     VARCHAR(100) DEFAULT NULL,
  `harga`      INT NOT NULL DEFAULT 0,
  `gambar`     TEXT DEFAULT NULL,
  FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan`(`id_pesanan`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Ambil semua pesanan milik user beserta item-nya
$pesananList = [];
$qPesanan = mysqli_query($koneksi,
    "SELECT * FROM pesanan WHERE id_pengguna = $id_pengguna ORDER BY created_at DESC"
);

if ($qPesanan) {
    while ($row = mysqli_fetch_assoc($qPesanan)) {
        $id_p  = (int) $row['id_pesanan'];
        $items = [];
        $qItem = mysqli_query($koneksi,
            "SELECT * FROM pesanan_item WHERE id_pesanan = $id_p"
        );
        if ($qItem) {
            while ($item = mysqli_fetch_assoc($qItem)) {
                $items[] = $item;
            }
        }
        $row['items'] = $items;
        $pesananList[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pesanan Saya - LokalThrift</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins','Helvetica Neue',Arial,sans-serif; }
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
      .main { padding: 30px 40px 40px; max-width: 860px; }
      .page-title { text-align: left; }
    }

    /* ── TAB FILTER ── */
    .tabs {
      display:flex; gap:0;
      background:white; border-radius:14px;
      padding:4px; margin-bottom:20px;
      box-shadow:0 4px 16px rgba(0,0,0,0.05);
      overflow-x:auto;
    }
    .tabs::-webkit-scrollbar { display:none; }
    .tab-btn {
      flex:1; padding:10px 8px; border:none; background:none;
      font-size:13px; font-weight:600; color:#8fa3b8;
      border-radius:10px; cursor:pointer; white-space:nowrap;
      transition:background 0.2s, color 0.2s;
      min-width:60px;
    }
    .tab-btn.active {
      background:#2a85ff; color:white;
    }
    .tab-btn:hover:not(.active) { color:#2a85ff; }

    /* ── PESANAN CARD ── */
    .pesanan-card {
      background:white; border-radius:16px; padding:18px 20px;
      margin-bottom:14px; box-shadow:0 4px 16px rgba(0,0,0,0.05);
    }

    .card-header {
      display:flex; justify-content:space-between;
      align-items:flex-start; margin-bottom:14px;
    }
    .invoice { font-size:15px; font-weight:800; color:#0d1c2e; }
    .tanggal { font-size:12px; color:#8fa3b8; margin-top:3px; }

    /* Status badge */
    .badge {
      font-size:12px; font-weight:700; padding:5px 12px;
      border-radius:20px; flex-shrink:0;
    }
    .badge-diproses  { background:#fff4e0; color:#f5a623; }
    .badge-dikirim   { background:#e0f0ff; color:#2a85ff; }
    .badge-selesai   { background:#e6f9f0; color:#10b981; }
    .badge-dibatalkan{ background:#fff0f0; color:#e53935; }

    /* Foto produk */
    .foto-row {
      display:flex; gap:8px; margin-bottom:14px;
      overflow-x:auto;
    }
    .foto-row::-webkit-scrollbar { display:none; }
    .foto-item {
      width:72px; height:72px; border-radius:12px;
      overflow:hidden; flex-shrink:0; background:#f0f6fc;
    }
    .foto-item img {
      width:100%; height:100%; object-fit:cover;
    }
    .foto-more {
      width:72px; height:72px; border-radius:12px;
      background:#eef5fc; display:flex; align-items:center;
      justify-content:center; font-size:13px; font-weight:700;
      color:#2a85ff; flex-shrink:0;
    }

    /* Footer card */
    .card-footer {
      display:flex; justify-content:space-between;
      align-items:center; padding-top:12px;
      border-top:1px solid #f0f6fc;
    }
    .jumlah { font-size:13px; color:#8fa3b8; }
    .total  { font-size:14px; font-weight:800; color:#0d1c2e; margin-top:2px; }
    .btn-detail {
      font-size:13px; font-weight:700; color:#2a85ff;
      text-decoration:none; padding:6px 14px;
      border:1.5px solid #c8dff5; border-radius:10px;
      transition:background 0.2s;
    }
    .btn-detail:hover { background:#eef5fc; }

    /* ── EMPTY STATE ── */
    .empty {
      text-align:center; padding:60px 20px; color:#aab;
    }
    .empty i { font-size:48px; color:#c8dff5; display:block; margin-bottom:14px; }
    .empty p  { font-size:14px; }

    /* ── PAGE WRAPPER ── */
    .page-wrapper { display:flex; flex:1; }

    /* ── MAIN ── */
    .main {
      width:100%; max-width:720px;
      margin:0 auto;
      padding:24px 16px 110px;
    }

    .page-title {
      font-size:20px; font-weight:800; color:#0d1c2e;
      text-align:center; margin-bottom:20px;
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
  <a href="pesanan.php" class="nav-item active">
    <i class="fa-solid fa-bag-shopping"></i><span>Pesanan</span>
  </a>
  <a href="cek_toko.php" class="nav-item">
    <i class="fa-solid fa-shop"></i><span>Toko</span>
  </a>
  <a href="akun.php" class="nav-item">
    <i class="fa-solid fa-user"></i><span>Akun</span>
  </a>
  <a href="chat.php" class="nav-item">
    <i class="fa-solid fa-message"></i><span>Chat</span>
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

  <div class="page-title">Pesanan Saya</div>

  <!-- TAB FILTER -->
  <div class="tabs">
    <button class="tab-btn active" onclick="filterTab(this,'semua')">Semua</button>
    <button class="tab-btn" onclick="filterTab(this,'diproses')">Diproses</button>
    <button class="tab-btn" onclick="filterTab(this,'dikirim')">Dikirim</button>
    <button class="tab-btn" onclick="filterTab(this,'selesai')">Selesai</button>
    <button class="tab-btn" onclick="filterTab(this,'dibatalkan')">Dibatalkan</button>
  </div>

  <!-- LIST PESANAN -->
  <div id="pesanan-list"></div>

</div>
</div>

<script>
  // ── DATA DARI PHP (realtime dari database) ──
  const semuaPesanan = <?= json_encode(array_map(function($p) {
    return [
      'id_pesanan' => $p['id_pesanan'],
      'invoice' => $p['invoice'],
      'tanggal' => date('d M Y · H:i', strtotime($p['created_at'])),
      'status'  => $p['status'],
      'total'   => (int) $p['total'],
      'items'   => array_map(function($item) {
        return ['gambar' => $item['gambar'], 'nama' => $item['nama']];
      }, $p['items'])
    ];
  }, $pesananList), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

  const badgeMap = {
    diproses:   { cls: 'badge-diproses',   label: 'Diproses'   },
    dikirim:    { cls: 'badge-dikirim',    label: 'Dikirim'    },
    selesai:    { cls: 'badge-selesai',    label: 'Selesai'    },
    dibatalkan: { cls: 'badge-dibatalkan', label: 'Dibatalkan' }
  };

  function formatRp(n) { return 'Rp ' + Number(n).toLocaleString('id-ID'); }

  function renderFoto(items) {
    const max = 3;
    let html = '';
    items.slice(0, max).forEach(item => {
      html += `<div class="foto-item"><img src="${item.gambar}" alt="${item.nama}" onerror="this.src='https://via.placeholder.com/72'"></div>`;
    });
    if (items.length > max) {
      html += `<div class="foto-more">+${items.length - max}</div>`;
    }
    return html;
  }

  function renderPesanan(filter = 'semua') {
    const list = document.getElementById('pesanan-list');
    const data = filter === 'semua'
      ? semuaPesanan
      : semuaPesanan.filter(p => p.status === filter);

    if (data.length === 0) {
      list.innerHTML = `
        <div class="empty">
          <i class="fa-solid fa-receipt"></i>
          <p>Belum ada pesanan${filter !== 'semua' ? ' dengan status ' + filter : ''}</p>
        </div>`;
      return;
    }

    list.innerHTML = data.map(p => {
      const b = badgeMap[p.status] || badgeMap.diproses;
      return `
        <div class="pesanan-card">
          <div class="card-header">
            <div>
              <div class="invoice">${p.invoice}</div>
              <div class="tanggal">${p.tanggal}</div>
            </div>
            <span class="badge ${b.cls}">${b.label}</span>
          </div>
          <div class="foto-row">${renderFoto(p.items)}</div>
          <div class="card-footer">
            <div>
              <div class="jumlah">${p.items.length} Barang</div>
              <div class="total">Total: ${formatRp(p.total)}</div>
            </div>
            <div style="display:flex; gap:10px;">
              <a href="detail_pesanan.php?invoice=${p.invoice}" class="btn-detail">Lihat Detail</a>
              ${p.status === 'selesai' ? `<a href="tulis_review.php?id_pesanan=${p.id_pesanan}" class="btn-detail" style="background:#2a85ff; color:white; border-color:#2a85ff;">Beri Ulasan</a>` : ''}
            </div>
          </div>
        </div>`;
    }).join('');
  }

  function filterTab(el, status) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    el.classList.add('active');
    renderPesanan(status);
  }

  renderPesanan('semua');
</script>
</body>
</html>
