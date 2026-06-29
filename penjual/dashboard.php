<?php
require 'guard.php';

// ── PERFORMA TOKO (1 bulan terakhir vs bulan sebelumnya) ──
$bulanIni  = date('Y-m-01');
$bulanLalu = date('Y-m-01', strtotime('-1 month'));

// Total dilihat
$qViews = mysqli_query($koneksi, "SELECT COALESCE(SUM(dilihat),0) as total FROM produk WHERE id_toko = $id_toko");
$views = ($qViews && ($r = mysqli_fetch_assoc($qViews))) ? (int)$r['total'] : 0;

// Untuk viewsPrev, kita biarkan 0 dulu karena belum ada history bulanan
$viewsPrev = 0;

// Pesanan bulan ini
$qPes = mysqli_query($koneksi,
    "SELECT COUNT(DISTINCT p.id_pesanan) as total
     FROM pesanan p
     JOIN pesanan_item pi ON pi.id_pesanan = p.id_pesanan
     JOIN produk pr ON pr.id_produk = pi.id_produk
     WHERE pr.id_toko = $id_toko
       AND p.status != 'dibatalkan'
       AND p.created_at >= '$bulanIni'");
$pesananBulanIni = ($qPes && ($r = mysqli_fetch_assoc($qPes))) ? (int)$r['total'] : 0;

$qPesL = mysqli_query($koneksi,
    "SELECT COUNT(DISTINCT p.id_pesanan) as total
     FROM pesanan p
     JOIN pesanan_item pi ON pi.id_pesanan = p.id_pesanan
     JOIN produk pr ON pr.id_produk = pi.id_produk
     WHERE pr.id_toko = $id_toko
       AND p.status != 'dibatalkan'
       AND p.created_at >= '$bulanLalu' AND p.created_at < '$bulanIni'");
$pesananBulanLalu = ($qPesL && ($r = mysqli_fetch_assoc($qPesL))) ? (int)$r['total'] : 0;

// Penjualan (revenue) bulan ini
$qRev = mysqli_query($koneksi,
    "SELECT COALESCE(SUM(pi.harga),0) as total
     FROM pesanan p
     JOIN pesanan_item pi ON pi.id_pesanan = p.id_pesanan
     JOIN produk pr ON pr.id_produk = pi.id_produk
     WHERE pr.id_toko = $id_toko
       AND p.status NOT IN ('dibatalkan','menunggu')
       AND p.created_at >= '$bulanIni'");
$revBulanIni = ($qRev && ($r = mysqli_fetch_assoc($qRev))) ? (int)$r['total'] : 0;

$qRevL = mysqli_query($koneksi,
    "SELECT COALESCE(SUM(pi.harga),0) as total
     FROM pesanan p
     JOIN pesanan_item pi ON pi.id_pesanan = p.id_pesanan
     JOIN produk pr ON pr.id_produk = pi.id_produk
     WHERE pr.id_toko = $id_toko
       AND p.status NOT IN ('dibatalkan','menunggu')
       AND p.created_at >= '$bulanLalu' AND p.created_at < '$bulanIni'");
$revBulanLalu = ($qRevL && ($r = mysqli_fetch_assoc($qRevL))) ? (int)$r['total'] : 0;

// Produk terjual bulan ini
$qTerjual = mysqli_query($koneksi,
    "SELECT COUNT(pi.id_item) as total
     FROM pesanan p
     JOIN pesanan_item pi ON pi.id_pesanan = p.id_pesanan
     JOIN produk pr ON pr.id_produk = pi.id_produk
     WHERE pr.id_toko = $id_toko
       AND p.status NOT IN ('dibatalkan')
       AND p.created_at >= '$bulanIni'");
$terjualBulanIni = ($qTerjual && ($r = mysqli_fetch_assoc($qTerjual))) ? (int)$r['total'] : 0;

$qTerjualL = mysqli_query($koneksi,
    "SELECT COUNT(pi.id_item) as total
     FROM pesanan p
     JOIN pesanan_item pi ON pi.id_pesanan = p.id_pesanan
     JOIN produk pr ON pr.id_produk = pi.id_produk
     WHERE pr.id_toko = $id_toko
       AND p.status NOT IN ('dibatalkan')
       AND p.created_at >= '$bulanLalu' AND p.created_at < '$bulanIni'");
$terjualBulanLalu = ($qTerjualL && ($r = mysqli_fetch_assoc($qTerjualL))) ? (int)$r['total'] : 0;

// Rata-rata Rating
$qRating = mysqli_query($koneksi, 
    "SELECT AVG(r.rating) as avg_rating 
     FROM review r 
     JOIN produk p ON r.id_produk = p.id_produk 
     WHERE p.id_toko = $id_toko");
$avgRating = ($qRating && ($r = mysqli_fetch_assoc($qRating))) ? (float)$r['avg_rating'] : 0;

// Hitung persentase perubahan
function pct($now, $prev) {
    if ($prev == 0) return $now > 0 ? 100 : 0;
    return round((($now - $prev) / $prev) * 100);
}
$pctViews   = pct($views, $viewsPrev);
$pctPesanan = pct($pesananBulanIni, $pesananBulanLalu);
$pctRev     = pct($revBulanIni, $revBulanLalu);
$pctTerjual = pct($terjualBulanIni, $terjualBulanLalu);

// ── RINGKAS PESANAN ──
function hitungStatus($koneksi, $id_toko, $status) {
    $s = mysqli_real_escape_string($koneksi, $status);
    $q = mysqli_query($koneksi,
        "SELECT COUNT(DISTINCT p.id_pesanan) as total
         FROM pesanan p
         JOIN pesanan_item pi ON pi.id_pesanan = p.id_pesanan
         JOIN produk pr ON pr.id_produk = pi.id_produk
         WHERE pr.id_toko = $id_toko AND p.status = '$s'");
    return ($q && ($r = mysqli_fetch_assoc($q))) ? (int)$r['total'] : 0;
}
$cMenunggu   = hitungStatus($koneksi, $id_toko, 'menunggu');
$cDikemas    = hitungStatus($koneksi, $id_toko, 'dikemas');
$cDikirim    = hitungStatus($koneksi, $id_toko, 'dikirim');
$cSelesai    = hitungStatus($koneksi, $id_toko, 'selesai');
$cDibatalkan = hitungStatus($koneksi, $id_toko, 'dibatalkan');

// ── PRODUK TERSEDIA ──
$produkTerbaru = [];
$qPT = mysqli_query($koneksi, "SELECT * FROM produk WHERE id_toko=$id_toko AND aktif = 1 ORDER BY created_at DESC LIMIT 8");
if ($qPT) while ($r = mysqli_fetch_assoc($qPT)) $produkTerbaru[] = $r;

// ── PRODUK TERJUAL & REVIEW ──
$produkTerjual = [];
$qTerjual = mysqli_query($koneksi, 
    "SELECT p.*, r.rating, r.ulasan, r.created_at as review_date, u.nama as nama_pembeli 
     FROM produk p 
     LEFT JOIN review r ON p.id_produk = r.id_produk 
     LEFT JOIN pengguna u ON r.id_pengguna = u.id_pengguna 
     WHERE p.id_toko=$id_toko AND p.aktif = 0 
     ORDER BY p.id_produk DESC LIMIT 8");
if ($qTerjual) while ($r = mysqli_fetch_assoc($qTerjual)) $produkTerjual[] = $r;
?>
<!DOCTYPE html><html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Dashboard - LokalThrift</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<?php require 'navbar.php'; ?>
<style>
/* ── Performa Toko ── */
.performa-card {
  background:#fff;
  border-radius:14px;
  border:1px solid #e8f0fb;
  padding:18px 22px;
  margin-bottom:20px;
}
.performa-header {
  display:flex;
  align-items:center;
  justify-content:space-between;
  margin-bottom:18px;
  flex-wrap:wrap;
  gap:10px;
}
.performa-header .title {
  font-size:15px;
  font-weight:700;
  color:#0d1c2e;
}
.period-select {
  background:#f5f8ff;
  border:1px solid #dde8f8;
  border-radius:8px;
  padding:5px 14px;
  font-size:13px;
  color:#556980;
  cursor:pointer;
  outline:none;
}
.performa-grid {
  display:grid;
  grid-template-columns:repeat(5,1fr);
  gap:0;
}
.performa-item {
  text-align:center;
  padding:8px 10px;
  border-right:1px solid #f0f5fc;
}
.performa-item:last-child { border-right:none; }
.performa-item .label {
  font-size:12px;
  color:#8fa3b8;
  margin-bottom:6px;
  display:flex;
  align-items:center;
  justify-content:center;
  gap:5px;
}
.performa-item .value {
  font-size:22px;
  font-weight:800;
  color:#0d1c2e;
  line-height:1.2;
}
.performa-item .change {
  font-size:12px;
  margin-top:4px;
  font-weight:600;
  display:flex;
  align-items:center;
  justify-content:center;
  gap:3px;
}
.change.up   { color:#10b981; }
.change.down { color:#ef4444; }

/* ── Ringkas Pesanan ── */
.ringkas-card {
  background:#fff;
  border-radius:14px;
  border:1px solid #e8f0fb;
  padding:18px 22px;
  margin-bottom:20px;
}
.ringkas-card .title {
  font-size:15px;
  font-weight:700;
  color:#0d1c2e;
  margin-bottom:16px;
}
.ringkas-grid {
  display:grid;
  grid-template-columns:repeat(5,1fr);
  gap:12px;
}
.ringkas-item {
  background:#fff;
  border:1px solid #e8f0fb;
  border-radius:12px;
  padding:18px 10px;
  text-align:center;
}
.ringkas-icon { font-size:26px; margin-bottom:8px; line-height:1; }
.ringkas-num  { font-size:26px; font-weight:800; color:#0d1c2e; line-height:1.2; }
.ringkas-lbl  { font-size:11px; color:#8fa3b8; margin:4px 0 8px; }
.ringkas-link { font-size:12px; color:#2a85ff; text-decoration:none; font-weight:600; }
.ringkas-link:hover { text-decoration:underline; }

/* ── Produk Tersedia ── */
.produk-card {
  background:#fff;
  border-radius:14px;
  border:1px solid #e8f0fb;
  padding:18px 22px;
  margin-bottom:20px;
}
.produk-card-header {
  display:flex;
  align-items:center;
  justify-content:space-between;
  margin-bottom:16px;
}
.produk-card-header .title {
  font-size:15px;
  font-weight:700;
  color:#0d1c2e;
}
.produk-grid {
  display:grid;
  grid-template-columns:repeat(4,1fr);
  gap:14px;
}
.produk-item {
  border-radius:12px;
  overflow:hidden;
  cursor:pointer;
  transition:transform .15s;
}
.produk-item:hover { transform:translateY(-3px); }
.produk-item img {
  width:100%;
  aspect-ratio:1/1;
  object-fit:cover;
  background:#ddeeff;
  border-radius:12px;
  display:block;
}
.produk-item .info { padding:8px 2px 4px; }
.produk-item .info .nama { font-size:13px; font-weight:600; color:#0d1c2e; }
.produk-item .info .harga { font-size:12px; color:#556980; margin-top:2px; }

@media(max-width:768px){
  .performa-grid  { grid-template-columns:repeat(2,1fr); }
  .ringkas-grid   { grid-template-columns:repeat(3,1fr); }
  .produk-grid    { grid-template-columns:repeat(2,1fr); }
}
@media(max-width:480px){
  .ringkas-grid   { grid-template-columns:repeat(2,1fr); }
  .produk-grid    { grid-template-columns:repeat(2,1fr); }
}
</style>
</head><body>

<div class="main-content">

  <!-- Greeting -->
  <div class="page-header" style="margin-bottom:20px">
    <div>
      <div class="page-title">Dashboard Toko</div>
      <div class="page-sub">Selamat datang, <?= $nama_penjual ?> 👋</div>
    </div>
    <a href="produk.php?action=tambah" class="btn btn-primary">
      <i class="fa-solid fa-plus"></i> Tambah Produk
    </a>
  </div>

  <!-- ── PERFORMA TOKO ── -->
  <div class="performa-card">
    <div class="performa-header">
      <span class="title">Performa Toko</span>
      <select class="period-select" onchange="location.href='dashboard.php?period='+this.value">
        <option value="1">1 bulan terakhir</option>
        <option value="3">3 bulan terakhir</option>
        <option value="6">6 bulan terakhir</option>
      </select>
    </div>
    <div class="performa-grid">

      <div class="performa-item">
        <div class="label">
          <span style="color:#10b981;font-size:9px">●</span> Dilihat
        </div>
        <div class="value"><?= number_format($views, 0, ',', '.') ?></div>
        <div class="change <?= $pctViews >= 0 ? 'up' : 'down' ?>">
          <i class="fa-solid fa-caret-<?= $pctViews >= 0 ? 'up' : 'down' ?>"></i>
          <?= abs($pctViews) ?>%
        </div>
      </div>

      <div class="performa-item">
        <div class="label">
          <span style="color:#f59e0b;font-size:9px">●</span> Pesanan
        </div>
        <div class="value"><?= $pesananBulanIni ?></div>
        <div class="change <?= $pctPesanan >= 0 ? 'up' : 'down' ?>">
          <i class="fa-solid fa-caret-<?= $pctPesanan >= 0 ? 'up' : 'down' ?>"></i>
          <?= abs($pctPesanan) ?>%
        </div>
      </div>

      <div class="performa-item">
        <div class="label">
          <span style="color:#ef4444;font-size:9px">●</span> Penjualan
        </div>
        <div class="value" style="font-size:17px"><?= rupiah($revBulanIni) ?></div>
        <div class="change <?= $pctRev >= 0 ? 'up' : 'down' ?>">
          <i class="fa-solid fa-caret-<?= $pctRev >= 0 ? 'up' : 'down' ?>"></i>
          <?= abs($pctRev) ?>%
        </div>
      </div>

      <div class="performa-item">
        <div class="label">
          <i class="fa-solid fa-table-cells" style="color:#8fa3b8;font-size:11px"></i> Produk Terjual
        </div>
        <div class="value"><?= $terjualBulanIni ?></div>
        <div class="change <?= $pctTerjual >= 0 ? 'up' : 'down' ?>">
          <i class="fa-solid fa-caret-<?= $pctTerjual >= 0 ? 'up' : 'down' ?>"></i>
          <?= abs($pctTerjual) ?>%
        </div>
      </div>

      <div class="performa-item">
        <div class="label">
          <span style="color:#eab308;font-size:12px">★</span> Rata-rata Rating
        </div>
        <div class="value"><?= number_format($avgRating, 1, ',', '.') ?></div>
        <div class="change up" style="color:#eab308;">
          Total dari semua review
        </div>
      </div>

    </div>
  </div>

  <!-- ── RINGKAS PESANAN ── -->
  <div class="ringkas-card">
    <div class="title">Ringkas Pesanan</div>
    <div class="ringkas-grid">

      <div class="ringkas-item">
        <div class="ringkas-icon">⏰</div>
        <div class="ringkas-num"><?= $cMenunggu ?></div>
        <div class="ringkas-lbl">Menunggu Pembayaran</div>
        <a href="pesanan.php?status=menunggu" class="ringkas-link">Lihat Pesanan</a>
      </div>

      <div class="ringkas-item">
        <div class="ringkas-icon">📦</div>
        <div class="ringkas-num"><?= $cDikemas ?></div>
        <div class="ringkas-lbl">Dikemas</div>
        <a href="pesanan.php?status=dikemas" class="ringkas-link">Lihat Pesanan</a>
      </div>

      <div class="ringkas-item">
        <div class="ringkas-icon">🚚</div>
        <div class="ringkas-num"><?= $cDikirim ?></div>
        <div class="ringkas-lbl">Dikirim</div>
        <a href="pesanan.php?status=dikirim" class="ringkas-link">Lihat Pesanan</a>
      </div>

      <div class="ringkas-item">
        <div class="ringkas-icon" style="color:#10b981">✅</div>
        <div class="ringkas-num"><?= $cSelesai ?></div>
        <div class="ringkas-lbl">Selesai</div>
        <a href="pesanan.php?status=selesai" class="ringkas-link">Lihat Pesanan</a>
      </div>

      <div class="ringkas-item">
        <div class="ringkas-icon" style="color:#ef4444">✖</div>
        <div class="ringkas-num"><?= $cDibatalkan ?></div>
        <div class="ringkas-lbl">Dibatalkan</div>
        <a href="pesanan.php?status=dibatalkan" class="ringkas-link">Lihat Pesanan</a>
      </div>

    </div>
  </div>

  <!-- ── PRODUK TERSEDIA ── -->
  <div class="produk-card">
    <div class="produk-card-header">
      <span class="title">Produk Tersedia</span>
      <a href="produk.php?status=tersedia" style="font-size:13px;color:#2a85ff;font-weight:600;text-decoration:none">Lihat Semua</a>
    </div>

    <?php if (empty($produkTerbaru)): ?>
      <div style="text-align:center;padding:50px 20px;color:#aab">
        <i class="fa-solid fa-shirt" style="font-size:40px;color:#c8dff5;display:block;margin-bottom:12px"></i>
        Belum ada produk.
        <a href="produk.php?action=tambah" style="color:#2a85ff;font-weight:700">Tambah sekarang</a>
      </div>
    <?php else: ?>
      <div class="produk-grid">
        <?php foreach ($produkTerbaru as $p): ?>
          <a href="produk.php?action=edit&id=<?= $p['id_produk'] ?>" class="produk-item" style="text-decoration:none">
            <img src="<?= !empty($p['gambar']) ? '../' . htmlspecialchars($p['gambar']) : '' ?>"
                 onerror="this.style.background='#ddeeff';this.removeAttribute('src')"
                 alt="<?= htmlspecialchars($p['nama']) ?>">
            <div class="info">
              <div class="nama"><?= htmlspecialchars($p['nama']) ?></div>
              <div class="harga"><?= rupiah($p['harga']) ?></div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- ── PRODUK TERJUAL & REVIEW ── -->
  <div class="produk-card" style="margin-top:20px;">
    <div class="produk-card-header">
      <span class="title">Produk Terjual & Ulasan</span>
      <a href="produk.php?status=terjual" style="font-size:13px;color:#2a85ff;font-weight:600;text-decoration:none">Lihat Semua</a>
    </div>

    <?php if (empty($produkTerjual)): ?>
      <div style="text-align:center;padding:50px 20px;color:#aab">
        <i class="fa-solid fa-box-open" style="font-size:40px;color:#c8dff5;display:block;margin-bottom:12px"></i>
        Belum ada produk yang terjual.
      </div>
    <?php else: ?>
      <div class="produk-grid">
        <?php foreach ($produkTerjual as $p): ?>
          <a href="../detail.php?id=<?= $p['id_produk'] ?>" class="produk-item" style="text-decoration:none">
            <img src="<?= !empty($p['gambar']) ? '../' . htmlspecialchars($p['gambar']) : '' ?>"
                 onerror="this.style.background='#ddeeff';this.removeAttribute('src')"
                 alt="<?= htmlspecialchars($p['nama']) ?>">
            <div class="info">
              <div class="nama"><?= htmlspecialchars($p['nama']) ?></div>
              <div class="harga" style="color:#10b981;">Terjual</div>
              <?php if ($p['rating']): ?>
                <div style="margin-top:8px; padding-top:8px; border-top:1px dashed #e2e8f0;">
                  <div style="color:#fbbf24; font-size:11px; margin-bottom:4px;">
                    <?php for($i=0; $i<$p['rating']; $i++) echo '<i class="fa-solid fa-star"></i>'; ?>
                  </div>
                  <div style="font-size:11px; color:#475569; font-style:italic;">
                    "<?= htmlspecialchars(strlen($p['ulasan']) > 30 ? substr($p['ulasan'],0,30).'...' : $p['ulasan']) ?>"
                  </div>
                </div>
              <?php else: ?>
                <div style="margin-top:8px; padding-top:8px; border-top:1px dashed #e2e8f0; font-size:11px; color:#94a3b8;">
                  Belum diulas
                </div>
              <?php endif; ?>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

</div>
</body></html>
