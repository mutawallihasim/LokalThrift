<?php
require 'guard.php';

// ── FILTER ──
$filter = $_GET['status'] ?? '';
$search = trim($_GET['q'] ?? '');

$where = '1=1';
if ($filter) {
    $f = mysqli_real_escape_string($koneksi, $filter);
    $where .= " AND p.status='$f'";
}
if ($search) {
    $s = mysqli_real_escape_string($koneksi, $search);
    $where .= " AND (p.invoice LIKE '%$s%' OR pg.nama LIKE '%$s%')";
}

$qPes = mysqli_query($koneksi,
  "SELECT p.id_pesanan, p.invoice, p.status, p.created_at, p.total, p.metode_bayar,
          pg.nama as nama_pembeli, pg.email,
          COALESCE(SUM(pi.harga), 0) as subtotal,
          COUNT(pi.id_item) as jml_item
   FROM pesanan p
   JOIN pengguna pg ON pg.id_pengguna = p.id_pengguna
   LEFT JOIN pesanan_item pi ON pi.id_pesanan = p.id_pesanan
   WHERE $where
   GROUP BY p.id_pesanan
   ORDER BY p.created_at DESC");

$statusList = ['menunggu','diproses','dikirim','selesai','dibatalkan'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Manajemen Pesanan — Admin LokalThrift</title>
</head>
<body>
<?php require 'navbar.php'; ?>

<div class="main-content">
  <div class="page-header">
    <div>
      <div class="page-title">Manajemen Pesanan</div>
      <div class="page-sub">Semua transaksi dari seluruh platform</div>
    </div>
  </div>

  <div class="card">
    <!-- Filter -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px;">
      <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="pesanan.php" class="btn btn-sm <?= !$filter ? 'btn-primary' : 'btn-outline' ?>">Semua</a>
        <?php foreach ($statusList as $s): ?>
        <a href="pesanan.php?status=<?= $s ?><?= $search ? '&q='.urlencode($search) : '' ?>"
           class="btn btn-sm <?= $filter===$s ? 'btn-primary' : 'btn-outline' ?>">
          <?= ucfirst($s) ?>
        </a>
        <?php endforeach; ?>
      </div>
      <form method="GET" style="display:flex;gap:8px;">
        <?php if ($filter): ?><input type="hidden" name="status" value="<?= htmlspecialchars($filter) ?>"><?php endif; ?>
        <div class="search-wrap">
          <i class="fa-solid fa-search"></i>
          <input class="form-input" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Cari invoice / pembeli…" style="padding-left:36px;width:240px;">
        </div>
        <button class="btn btn-primary btn-sm" type="submit"><i class="fa-solid fa-search"></i></button>
      </form>
    </div>

    <table class="tbl">
      <thead>
        <tr>
          <th>#</th>
          <th>Invoice</th>
          <th>Pembeli</th>
          <th>Item</th>
          <th>Subtotal</th>
          <th>Ongkir</th>
          <th>Total</th>
          <th>Metode</th>
          <th>Status</th>
          <th>Tanggal</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($qPes && mysqli_num_rows($qPes) > 0): $no=1; ?>
          <?php while ($p = mysqli_fetch_assoc($qPes)): ?>
          <?php
            $subtotal = (int)$p['subtotal'];
            $total = (int)$p['total'];
            $ongkir = $total - $subtotal;
            if ($ongkir < 0) $ongkir = 0;
          ?>
          <tr>
            <td style="color:#8fa3b8;font-size:12px;"><?= $no++ ?></td>
            <td>
              <a href="../detail_pesanan.php?invoice=<?= urlencode($p['invoice']) ?>"
                 style="font-size:12px;font-weight:700;color:#2a85ff;text-decoration:none;"
                 target="_blank">
                <?= htmlspecialchars($p['invoice']) ?>
              </a>
            </td>
            <td>
              <div style="display:flex;align-items:center;gap:8px;">
                <div class="user-avatar" style="width:28px;height:28px;font-size:11px;"><?= strtoupper(substr($p['nama_pembeli'],0,1)) ?></div>
                <div>
                  <div style="font-weight:600;font-size:12px;"><?= htmlspecialchars($p['nama_pembeli']) ?></div>
                  <div style="font-size:10px;color:#8fa3b8;"><?= htmlspecialchars($p['email']) ?></div>
                </div>
              </div>
            </td>
            <td style="font-weight:600;"><?= $p['jml_item'] ?> item</td>
            <td style="font-weight:600;"><?= rupiah($p['subtotal']) ?></td>
            <td style="color:#8fa3b8;font-size:12px;"><?= rupiah($ongkir) ?></td>
            <td style="font-weight:800;color:#0d1c2e;"><?= rupiah($total) ?></td>
            <td>
              <span style="font-size:11px;background:#f0f6fc;padding:3px 8px;border-radius:20px;font-weight:600;text-transform:uppercase;letter-spacing:0.3px;color:#556980;">
                <?= htmlspecialchars($p['metode_bayar'] ?? '-') ?>
              </span>
            </td>
            <td><span class="badge badge-<?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
            <td style="font-size:11px;color:#8fa3b8;white-space:nowrap;">
              <?= date('d M Y', strtotime($p['created_at'])) ?><br>
              <span style="font-size:10px;"><?= date('H:i', strtotime($p['created_at'])) ?></span>
            </td>
          </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="10" style="text-align:center;padding:40px;color:#8fa3b8;">
            <i class="fa-solid fa-receipt" style="font-size:32px;display:block;margin-bottom:10px;opacity:0.3;"></i>
            Tidak ada pesanan ditemukan
          </td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
