<?php
require 'guard.php';

// Update status pesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_pesanan'], $_POST['status'])) {
    $id_p   = (int)$_POST['id_pesanan'];
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
    $allowed = ['diproses','dikirim','selesai','dibatalkan'];
    if (in_array($status, $allowed)) {
        mysqli_query($koneksi, "UPDATE pesanan SET status='$status' WHERE id_pesanan=$id_p");
        // Kirim notifikasi ke pembeli
        $qPengguna = mysqli_query($koneksi, "SELECT id_pengguna, invoice FROM pesanan WHERE id_pesanan=$id_p");
        if ($qPengguna && mysqli_num_rows($qPengguna) > 0) {
            $pesananData = mysqli_fetch_assoc($qPengguna);
            $id_pembeli = $pesananData['id_pengguna'];
            $invoice = $pesananData['invoice'];
            $judul = "Pesanan " . ucfirst($status);
            $pesan = "Status pesanan Anda ($invoice) telah diubah menjadi: " . ucfirst($status);
            mysqli_query($koneksi, "INSERT INTO notifikasi (id_pengguna, judul, pesan) VALUES ($id_pembeli, '$judul', '$pesan')");
        }

        // Jika dibatalkan, kembalikan stok dan aktifkan kembali produk
        if ($status === 'dibatalkan') {
            mysqli_query($koneksi, "UPDATE produk p JOIN pesanan_item pi ON p.id_produk = pi.id_produk SET p.stok = 1, p.aktif = 1 WHERE pi.id_pesanan = $id_p");
        } elseif (in_array($status, ['diproses', 'dikirim', 'selesai'])) {
            // Jika diproses, tandai produk sebagai terjual (tidak tampil di beranda)
            mysqli_query($koneksi, "UPDATE produk p JOIN pesanan_item pi ON p.id_produk = pi.id_produk SET p.stok = 0, p.aktif = 0 WHERE pi.id_pesanan = $id_p");
        }
    }
}

// Tambahkan kolom jika tabel versi lama
$checkCol = mysqli_query($koneksi, "SHOW COLUMNS FROM pesanan_item LIKE 'id_toko'");
if ($checkCol && mysqli_num_rows($checkCol) == 0) {
    mysqli_query($koneksi, "ALTER TABLE pesanan_item ADD COLUMN id_produk INT DEFAULT 0 AFTER id_pesanan");
    mysqli_query($koneksi, "ALTER TABLE pesanan_item ADD COLUMN id_toko INT DEFAULT 0 AFTER id_produk");
}

$filterStatus = $_GET['status'] ?? 'semua';
$statusList   = ['semua','diproses','dikirim','selesai','dibatalkan'];

// Ambil pesanan yang mengandung produk dari toko ini (id_toko baru atau id_toko warisan)
$where = "WHERE (pi.id_toko = $id_toko OR (pi.id_toko = 0 AND pr.id_toko = $id_toko))";
if ($filterStatus !== 'semua') {
    $fs = mysqli_real_escape_string($koneksi, $filterStatus);
    $where .= " AND p.status = '$fs'";
}

$pesananList = [];
$qP = mysqli_query($koneksi,
    "SELECT p.*, GROUP_CONCAT(pi.nama SEPARATOR ', ') as produk_names,
            COUNT(pi.id_item) as jml_item
     FROM pesanan p
     JOIN pesanan_item pi ON pi.id_pesanan = p.id_pesanan
     LEFT JOIN produk pr ON pr.nama = pi.nama
     $where
     GROUP BY p.id_pesanan
     ORDER BY p.created_at DESC");
if ($qP) while ($r = mysqli_fetch_assoc($qP)) $pesananList[] = $r;

$badgeClass = ['diproses'=>'badge-diproses','dikirim'=>'badge-dikirim','selesai'=>'badge-selesai','dibatalkan'=>'badge-dibatalkan'];
$badgeLabel = ['diproses'=>'Diproses','dikirim'=>'Dikirim','selesai'=>'Selesai','dibatalkan'=>'Dibatalkan'];
?>
<!DOCTYPE html><html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Pesanan Masuk - LokalThrift</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<?php require 'navbar.php'; ?>
<style>
  .badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
  .badge-diproses   { background:#fff4e0; color:#f5a623; }
  .badge-dikirim    { background:#e0f0ff; color:#2a85ff; }
  .badge-selesai    { background:#e6f9f0; color:#10b981; }
  .badge-dibatalkan { background:#fff0f0; color:#e53935; }
  .tab-bar { display:flex; gap:4px; background:white; border-radius:12px; padding:4px; margin-bottom:20px; box-shadow:0 4px 12px rgba(0,0,0,0.05); width:fit-content; }
  .tab-btn { padding:8px 16px; border:none; background:none; border-radius:8px; font-size:13px; font-weight:600; color:#8fa3b8; cursor:pointer; }
  .tab-btn.active { background:#2a85ff; color:white; }
</style>
</head><body>

<div class="main-content">
  <div class="page-header">
    <div>
      <div class="page-title">Pesanan Masuk</div>
      <div class="page-sub"><?= count($pesananList) ?> pesanan</div>
    </div>
  </div>

  <!-- Tab filter -->
  <div class="tab-bar">
    <?php foreach ($statusList as $s): ?>
      <a href="pesanan.php?status=<?= $s ?>" class="tab-btn <?= $filterStatus===$s?'active':'' ?>">
        <?= $s === 'semua' ? 'Semua' : ucfirst($s) ?>
      </a>
    <?php endforeach; ?>
  </div>

  <div class="card">
    <?php if (empty($pesananList)): ?>
      <div style="text-align:center;padding:50px;color:#aab">
        <i class="fa-solid fa-receipt" style="font-size:40px;color:#c8dff5;display:block;margin-bottom:12px"></i>
        Belum ada pesanan.
      </div>
    <?php else: ?>
    <table class="tbl">
      <thead><tr>
        <th>Invoice</th><th>Pembeli</th><th>Produk</th><th>Total</th><th>Status</th><th>Ubah Status</th>
      </tr></thead>
      <tbody>
      <?php foreach ($pesananList as $p): ?>
        <tr>
          <td>
            <div style="font-weight:700"><?= htmlspecialchars($p['invoice']) ?></div>
            <div style="font-size:11px;color:#8fa3b8"><?= date('d M Y H:i', strtotime($p['created_at'])) ?></div>
          </td>
          <td style="font-size:13px;color:#556980">ID <?= $p['id_pengguna'] ?></td>
          <td>
            <div style="font-size:12px;color:#556980;max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
              <?= htmlspecialchars($p['produk_names']) ?>
            </div>
            <div style="font-size:11px;color:#aab"><?= $p['jml_item'] ?> item</div>
          </td>
          <td style="font-weight:700"><?= rupiah($p['total']) ?></td>
          <td><span class="badge <?= $badgeClass[$p['status']] ?? '' ?>"><?= $badgeLabel[$p['status']] ?? $p['status'] ?></span></td>
          <td>
            <form method="POST" style="display:flex;gap:6px;align-items:center">
              <input type="hidden" name="id_pesanan" value="<?= $p['id_pesanan'] ?>">
              <select name="status" class="form-input" style="padding:6px 10px;font-size:12px;width:120px">
                <option value="diproses"   <?= $p['status']==='diproses'   ?'selected':'' ?>>Diproses</option>
                <option value="dikirim"    <?= $p['status']==='dikirim'    ?'selected':'' ?>>Dikirim</option>
                <option value="selesai"    <?= $p['status']==='selesai'    ?'selected':'' ?>>Selesai</option>
                <option value="dibatalkan" <?= $p['status']==='dibatalkan' ?'selected':'' ?>>Dibatalkan</option>
              </select>
              <button class="btn btn-primary btn-sm" type="submit">Simpan</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>
</body></html>
