<?php
require 'guard.php';

// ── STATISTIK GLOBAL ──
$totalUser    = (int)(mysqli_fetch_assoc(mysqli_query($koneksi,"SELECT COUNT(*) as c FROM pengguna"))['c'] ?? 0);
$totalPenjual = (int)(mysqli_fetch_assoc(mysqli_query($koneksi,"SELECT COUNT(*) as c FROM pengguna WHERE role='penjual'"))['c'] ?? 0);
$totalProduk  = (int)(mysqli_fetch_assoc(mysqli_query($koneksi,"SELECT COUNT(*) as c FROM produk WHERE aktif=1"))['c'] ?? 0);

$qPes = mysqli_query($koneksi,"SELECT COUNT(*) as c FROM pesanan");
$totalPesanan = $qPes ? (int)(mysqli_fetch_assoc($qPes)['c'] ?? 0) : 0;

$qRev = mysqli_query($koneksi,"SELECT COALESCE(SUM(pi.harga),0) as total FROM pesanan p JOIN pesanan_item pi ON pi.id_pesanan=p.id_pesanan WHERE p.status NOT IN ('dibatalkan','menunggu')");
$totalRevenue = $qRev ? (int)(mysqli_fetch_assoc($qRev)['total'] ?? 0) : 0;

// ── PESANAN TERBARU ──
$qRecentPes = mysqli_query($koneksi,
  "SELECT p.invoice, p.status, p.created_at, pg.nama as nama_pembeli,
          COALESCE(SUM(pi.harga),0) as total
   FROM pesanan p
   JOIN pengguna pg ON pg.id_pengguna=p.id_pengguna
   JOIN pesanan_item pi ON pi.id_pesanan=p.id_pesanan
   GROUP BY p.id_pesanan
   ORDER BY p.created_at DESC LIMIT 8");

// ── PENGGUNA TERBARU ──
$qRecentUser = mysqli_query($koneksi,
  "SELECT nama, email, role, created_at FROM pengguna ORDER BY created_at DESC LIMIT 6");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Dashboard — LokalThrift</title>
</head>
<body>
<?php require 'navbar.php'; ?>

<div class="main-content">
  <div class="page-header">
    <div>
      <div class="page-title">Dashboard Admin</div>
      <div class="page-sub">Selamat datang, <?= $nama_admin ?> 👋 — <?= date('d F Y') ?></div>
    </div>
  </div>

  <!-- STAT CARDS -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon blue"><i class="fa-solid fa-users"></i></div>
      <div>
        <div class="stat-num"><?= number_format($totalUser) ?></div>
        <div class="stat-lbl">Total Pengguna</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon orange"><i class="fa-solid fa-store"></i></div>
      <div>
        <div class="stat-num"><?= number_format($totalPenjual) ?></div>
        <div class="stat-lbl">Penjual Aktif</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon purple"><i class="fa-solid fa-shirt"></i></div>
      <div>
        <div class="stat-num"><?= number_format($totalProduk) ?></div>
        <div class="stat-lbl">Produk Tersedia</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon red"><i class="fa-solid fa-receipt"></i></div>
      <div>
        <div class="stat-num"><?= number_format($totalPesanan) ?></div>
        <div class="stat-lbl">Total Pesanan</div>
      </div>
    </div>
  </div>

  <!-- REVENUE CARD -->
  <div class="card" style="background:linear-gradient(135deg,#1a2d42,#0d3b6e);margin-bottom:20px;">
    <div style="display:flex;align-items:center;gap:20px;">
      <div style="width:60px;height:60px;border-radius:18px;background:rgba(42,133,255,0.25);display:flex;align-items:center;justify-content:center;font-size:28px;color:#2a85ff;">
        <i class="fa-solid fa-sack-dollar"></i>
      </div>
      <div>
        <div style="font-size:13px;color:rgba(255,255,255,0.5);margin-bottom:4px;">Total Revenue Platform</div>
        <div style="font-size:32px;font-weight:800;color:white;"><?= rupiah($totalRevenue) ?></div>
        <div style="font-size:12px;color:rgba(42,133,255,0.8);margin-top:4px;">Dari semua transaksi berhasil</div>
      </div>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
    <!-- PESANAN TERBARU -->
    <div class="card">
      <div class="card-header">
        <div class="card-title"><i class="fa-solid fa-receipt" style="color:#2a85ff;margin-right:6px;"></i>Pesanan Terbaru</div>
        <a href="pesanan.php" class="btn btn-outline btn-sm">Lihat Semua</a>
      </div>
      <table class="tbl">
        <thead>
          <tr>
            <th>Invoice</th>
            <th>Pembeli</th>
            <th>Total</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($qRecentPes && mysqli_num_rows($qRecentPes) > 0): ?>
            <?php while($r = mysqli_fetch_assoc($qRecentPes)): ?>
            <tr>
              <td style="font-size:11px;font-weight:700;color:#2a85ff;"><?= htmlspecialchars($r['invoice']) ?></td>
              <td><?= htmlspecialchars($r['nama_pembeli']) ?></td>
              <td style="font-weight:700;"><?= rupiah($r['total']) ?></td>
              <td><span class="badge badge-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
            </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="4" style="text-align:center;color:#8fa3b8;padding:30px;">Belum ada pesanan</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- PENGGUNA TERBARU -->
    <div class="card">
      <div class="card-header">
        <div class="card-title"><i class="fa-solid fa-users" style="color:#10b981;margin-right:6px;"></i>Pengguna Terbaru</div>
        <a href="pengguna.php" class="btn btn-outline btn-sm">Lihat Semua</a>
      </div>
      <table class="tbl">
        <thead>
          <tr>
            <th>Nama</th>
            <th>Email</th>
            <th>Role</th>
            <th>Daftar</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($qRecentUser && mysqli_num_rows($qRecentUser) > 0): ?>
            <?php while($r = mysqli_fetch_assoc($qRecentUser)): ?>
            <tr>
              <td>
                <div style="display:flex;align-items:center;gap:8px;">
                  <div class="user-avatar"><?= strtoupper(substr($r['nama'],0,1)) ?></div>
                  <?= htmlspecialchars($r['nama']) ?>
                </div>
              </td>
              <td style="font-size:12px;color:#8fa3b8;"><?= htmlspecialchars($r['email']) ?></td>
              <td><span class="badge badge-<?= $r['role'] ?>"><?= ucfirst($r['role']) ?></span></td>
              <td style="font-size:11px;color:#8fa3b8;"><?= date('d M', strtotime($r['created_at'])) ?></td>
            </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="4" style="text-align:center;color:#8fa3b8;padding:30px;">Tidak ada data</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>
