<?php
require 'guard.php';

// ── AMBIL DATA TOKO ──
$search = trim($_GET['q'] ?? '');
$where  = '1=1';
if ($search) {
    $s = mysqli_real_escape_string($koneksi, $search);
    $where .= " AND (t.nama_toko LIKE '%$s%' OR pg.nama LIKE '%$s%')";
}

$qToko = mysqli_query($koneksi,
  "SELECT t.*, pg.nama as nama_penjual, pg.email,
          COUNT(DISTINCT pr.id_produk) as jml_produk,
          COALESCE(SUM(pi.harga), 0) as total_revenue
   FROM toko t
   JOIN pengguna pg ON pg.id_pengguna = t.id_penjual
   LEFT JOIN produk pr ON pr.id_toko = t.id_toko AND pr.aktif = 1
   LEFT JOIN pesanan_item pi ON pi.id_produk = pr.id_produk
   LEFT JOIN pesanan p ON p.id_pesanan = pi.id_pesanan AND p.status NOT IN ('dibatalkan','menunggu')
   WHERE $where
   GROUP BY t.id_toko
   ORDER BY t.created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Manajemen Toko — Admin LokalThrift</title>
</head>
<body>
<?php require 'navbar.php'; ?>

<div class="main-content">
  <div class="page-header">
    <div>
      <div class="page-title">Manajemen Toko</div>
      <div class="page-sub">Lihat semua toko penjual di platform</div>
    </div>
    <form method="GET" style="display:flex;gap:8px;">
      <div class="search-wrap">
        <i class="fa-solid fa-search"></i>
        <input class="form-input" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Cari nama toko / penjual…" style="padding-left:36px;width:260px;">
      </div>
      <button class="btn btn-primary btn-sm" type="submit"><i class="fa-solid fa-search"></i> Cari</button>
    </form>
  </div>

  <div class="card">
    <table class="tbl">
      <thead>
        <tr>
          <th>#</th>
          <th>Nama Toko</th>
          <th>Penjual</th>
          <th>Produk Aktif</th>
          <th>Total Revenue</th>
          <th>Bergabung</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($qToko && mysqli_num_rows($qToko) > 0): $no = 1; ?>
          <?php while ($t = mysqli_fetch_assoc($qToko)): ?>
          <tr>
            <td style="color:#8fa3b8;font-size:12px;"><?= $no++ ?></td>
            <td>
              <div style="display:flex;align-items:center;gap:10px;">
                <?php if (!empty($t['foto_toko'])): ?>
                  <img src="../<?= htmlspecialchars($t['foto_toko']) ?>" style="width:36px;height:36px;border-radius:10px;object-fit:cover;">
                <?php else: ?>
                  <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#e8f1ff,#ddeeff);display:flex;align-items:center;justify-content:center;font-size:16px;">
                    <i class="fa-solid fa-store" style="color:#2a85ff;"></i>
                  </div>
                <?php endif; ?>
                <div>
                  <div style="font-weight:700;"><?= htmlspecialchars($t['nama_toko']) ?></div>
                  <?php if ($t['alamat']): ?>
                  <div style="font-size:11px;color:#8fa3b8;"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($t['alamat']) ?></div>
                  <?php endif; ?>
                </div>
              </div>
            </td>
            <td>
              <div style="font-weight:600;"><?= htmlspecialchars($t['nama_penjual']) ?></div>
              <div style="font-size:11px;color:#8fa3b8;"><?= htmlspecialchars($t['email']) ?></div>
            </td>
            <td>
              <span style="font-size:18px;font-weight:800;color:#0d1c2e;"><?= $t['jml_produk'] ?></span>
              <span style="font-size:12px;color:#8fa3b8;"> produk</span>
            </td>
            <td style="font-weight:700;color:#10b981;"><?= rupiah($t['total_revenue']) ?></td>
            <td style="font-size:12px;color:#8fa3b8;"><?= date('d M Y', strtotime($t['created_at'])) ?></td>
            <td>
              <a href="produk.php?toko_id=<?= $t['id_toko'] ?>" class="btn btn-outline btn-sm">
                <i class="fa-solid fa-shirt"></i> Lihat Produk
              </a>
            </td>
          </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="7" style="text-align:center;padding:40px;color:#8fa3b8;">
            <i class="fa-solid fa-store" style="font-size:32px;display:block;margin-bottom:10px;opacity:0.3;"></i>
            Tidak ada toko ditemukan
          </td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
