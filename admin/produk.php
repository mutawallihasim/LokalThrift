<?php
require 'guard.php';

$msg = $err = '';

// ── HAPUS PRODUK ──
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $did = (int)$_GET['delete'];
    mysqli_query($koneksi, "DELETE FROM produk WHERE id_produk=$did");
    $msg = 'Produk berhasil dihapus.';
}

// ── TOGGLE AKTIF ──
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $tid = (int)$_GET['toggle'];
    mysqli_query($koneksi, "UPDATE produk SET aktif = IF(aktif=1,0,1) WHERE id_produk=$tid");
    $msg = 'Status produk berhasil diperbarui.';
}

// ── FILTER & SEARCH ──
$search  = trim($_GET['q'] ?? '');
$filter  = $_GET['status'] ?? '';
$toko_id = isset($_GET['toko_id']) ? (int)$_GET['toko_id'] : 0;

$where = '1=1';
if ($toko_id > 0) {
    $where .= " AND pr.id_toko = $toko_id";
}
if ($search) {
    $s = mysqli_real_escape_string($koneksi, $search);
    $where .= " AND (pr.nama LIKE '%$s%' OR t.nama_toko LIKE '%$s%')";
}
if ($filter === 'aktif')    $where .= " AND pr.aktif=1";
if ($filter === 'nonaktif') $where .= " AND pr.aktif=0";

$qProduk = mysqli_query($koneksi,
  "SELECT pr.*, t.nama_toko, pg.nama as nama_penjual
   FROM produk pr
   JOIN toko t ON t.id_toko = pr.id_toko
   JOIN pengguna pg ON pg.id_pengguna = t.id_penjual
   WHERE $where
   ORDER BY pr.created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Manajemen Produk — Admin LokalThrift</title>
</head>
<body>
<?php require 'navbar.php'; ?>

<div class="main-content">
  <div class="page-header">
    <div>
      <div class="page-title">Manajemen Produk</div>
      <div class="page-sub">Semua produk dari seluruh toko</div>
    </div>
  </div>

  <?php if ($msg): ?><div class="alert alert-success"><i class="fa-solid fa-check-circle"></i> <?= $msg ?></div><?php endif; ?>

  <div class="card">
    <!-- Filter -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px;">
      <div style="display:flex;gap:8px;">
        <?php $tidParam = $toko_id > 0 ? '&toko_id='.$toko_id : ''; ?>
        <a href="produk.php?<?= $search ? 'q='.urlencode($search).$tidParam : ltrim($tidParam, '&') ?>" class="btn btn-sm <?= !$filter ? 'btn-primary' : 'btn-outline' ?>">Semua</a>
        <a href="produk.php?status=aktif<?= $search ? '&q='.urlencode($search) : '' ?><?= $tidParam ?>" class="btn btn-sm <?= $filter==='aktif' ? 'btn-primary' : 'btn-outline' ?>">Tersedia</a>
        <a href="produk.php?status=nonaktif<?= $search ? '&q='.urlencode($search) : '' ?><?= $tidParam ?>" class="btn btn-sm <?= $filter==='nonaktif' ? 'btn-primary' : 'btn-outline' ?>">Terjual</a>
      </div>
      <form method="GET" style="display:flex;gap:8px;">
        <?php if ($filter): ?><input type="hidden" name="status" value="<?= htmlspecialchars($filter) ?>"><?php endif; ?>
        <?php if ($toko_id > 0): ?><input type="hidden" name="toko_id" value="<?= $toko_id ?>"><?php endif; ?>
        <div class="search-wrap">
          <i class="fa-solid fa-search"></i>
          <input class="form-input" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Cari nama produk / toko…" style="padding-left:36px;width:240px;">
        </div>
        <button class="btn btn-primary btn-sm" type="submit"><i class="fa-solid fa-search"></i></button>
      </form>
    </div>

    <table class="tbl">
      <thead>
        <tr>
          <th>#</th>
          <th>Produk</th>
          <th>Toko</th>
          <th>Harga</th>
          <th>Kondisi</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($qProduk && mysqli_num_rows($qProduk) > 0): $no=1; ?>
          <?php while ($p = mysqli_fetch_assoc($qProduk)): ?>
          <tr>
            <td style="color:#8fa3b8;font-size:12px;"><?= $no++ ?></td>
            <td>
              <div style="display:flex;align-items:center;gap:10px;">
                <?php if (!empty($p['gambar'])): ?>
                  <img src="../<?= htmlspecialchars($p['gambar']) ?>" style="width:40px;height:40px;border-radius:10px;object-fit:cover;">
                <?php else: ?>
                  <div style="width:40px;height:40px;border-radius:10px;background:#f0f6fc;display:flex;align-items:center;justify-content:center;">
                    <i class="fa-solid fa-shirt" style="color:#c8dff5;"></i>
                  </div>
                <?php endif; ?>
                <div>
                  <div style="font-weight:700;max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($p['nama']) ?></div>
                  <div style="font-size:11px;color:#8fa3b8;"><?= htmlspecialchars($p['kategori']) ?></div>
                </div>
              </div>
            </td>
            <td>
              <div style="font-weight:600;font-size:12px;"><?= htmlspecialchars($p['nama_toko']) ?></div>
              <div style="font-size:11px;color:#8fa3b8;"><?= htmlspecialchars($p['nama_penjual']) ?></div>
            </td>
            <td style="font-weight:700;"><?= rupiah($p['harga']) ?></td>
            <td style="font-size:12px;color:#556980;"><?= htmlspecialchars($p['kondisi']) ?></td>
            <td>
              <span class="badge <?= $p['aktif'] ? 'badge-aktif' : 'badge-nonaktif' ?>">
                <?= $p['aktif'] ? 'Tersedia' : 'Terjual' ?>
              </span>
            </td>
            <td>
              <div style="display:flex;gap:6px;">
                <a href="produk.php?toggle=<?= $p['id_produk'] ?>&<?= http_build_query(['q'=>$search,'status'=>$filter,'toko_id'=>$toko_id>0?$toko_id:'']) ?>"
                   class="btn btn-outline btn-sm" title="Toggle aktif/nonaktif">
                  <i class="fa-solid fa-<?= $p['aktif'] ? 'eye-slash' : 'eye' ?>"></i>
                </a>
                <a href="produk.php?delete=<?= $p['id_produk'] ?>&<?= http_build_query(['q'=>$search,'status'=>$filter,'toko_id'=>$toko_id>0?$toko_id:'']) ?>"
                   class="btn btn-danger btn-sm"
                   onclick="return confirm('Hapus produk ini?')">
                  <i class="fa-solid fa-trash"></i>
                </a>
              </div>
            </td>
          </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="8" style="text-align:center;padding:40px;color:#8fa3b8;">
            <i class="fa-solid fa-shirt" style="font-size:32px;display:block;margin-bottom:10px;opacity:0.3;"></i>
            Tidak ada produk ditemukan
          </td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
