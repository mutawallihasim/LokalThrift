<?php
require 'guard.php';
$action  = $_GET['action'] ?? 'list';
$msg     = '';
$msgType = 'success';
// Buat tabel produk_gambar jika belum ada
mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS produk_gambar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_produk INT NOT NULL,
    gambar VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Handle POST request (Tambah / Edit Produk)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_produk = isset($_POST['id_produk']) ? (int)$_POST['id_produk'] : 0;
    $nama      = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $harga     = (int)$_POST['harga'];
    $ukuran    = mysqli_real_escape_string($koneksi, $_POST['ukuran']);
    $kondisi   = mysqli_real_escape_string($koneksi, $_POST['kondisi']);
    $kategori  = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $aktif     = isset($_POST['aktif']) ? 1 : 0;
    
    // Upload Gambar Utama
    $gambar = '';
    if (!empty($_FILES['gambar']['name'])) {
        $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $gambar = 'uploads/' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['gambar']['tmp_name'], '../' . $gambar);
    }
    
    if ($id_produk === 0) {
        // Insert Produk
        if (!$gambar) {
            $msg = 'Gambar utama produk wajib diupload.'; $msgType = 'danger';
        } else {
            $q = "INSERT INTO produk (id_toko, nama, deskripsi, harga, ukuran, kondisi, kategori, gambar, aktif) 
                  VALUES ($id_toko, '$nama', '$deskripsi', $harga, '$ukuran', '$kondisi', '$kategori', '$gambar', $aktif)";
            if (mysqli_query($koneksi, $q)) {
                $id_produk = mysqli_insert_id($koneksi);
                $msg = 'Produk berhasil ditambahkan.';
                header("Location: produk.php?action=list&msg=added");
                exit;
            } else {
                $msg = 'Gagal menyimpan produk: ' . mysqli_error($koneksi); $msgType = 'danger';
            }
        }
    } else {
        // Update Produk
        $qU = mysqli_query($koneksi, "SELECT gambar FROM produk WHERE id_produk=$id_produk");
        $old = mysqli_fetch_assoc($qU);
        if (!$gambar) $gambar = $old['gambar'];
        
        $q = "UPDATE produk SET nama='$nama', deskripsi='$deskripsi', harga=$harga, 
              ukuran='$ukuran', kondisi='$kondisi', kategori='$kategori', 
              gambar='$gambar', aktif=$aktif 
              WHERE id_produk=$id_produk AND id_toko=$id_toko";
        if (mysqli_query($koneksi, $q)) {
            $msg = 'Produk berhasil diperbarui.';
            header("Location: produk.php?action=list&msg=updated");
            exit;
        } else {
            $msg = 'Gagal memperbarui produk.'; $msgType = 'danger';
        }
    }
    
    // Upload Gambar Tambahan jika berhasil
    if ($id_produk > 0 && !empty($_FILES['gambar_tambahan']['name'][0])) {
        foreach ($_FILES['gambar_tambahan']['name'] as $key => $name) {
            if ($name) {
                $ext = pathinfo($name, PATHINFO_EXTENSION);
                $gbr = 'uploads/' . uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['gambar_tambahan']['tmp_name'][$key], '../' . $gbr)) {
                    mysqli_query($koneksi, "INSERT INTO produk_gambar (id_produk, gambar) VALUES ($id_produk, '$gbr')");
                }
            }
        }
    }
}
// Handle Hapus Produk
if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    mysqli_query($koneksi, "DELETE FROM produk WHERE id_produk=$id AND id_toko=$id_toko");
    header("Location: produk.php?action=list");
    exit;
}
// Handle Hapus Gambar Tambahan
if ($action === 'delete_img' && isset($_GET['id_gbr'])) {
    $id_gbr = (int)$_GET['id_gbr'];
    $qG = mysqli_query($koneksi, "SELECT g.gambar, p.id_produk FROM produk_gambar g JOIN produk p ON g.id_produk=p.id_produk WHERE g.id=$id_gbr AND p.id_toko=$id_toko LIMIT 1");
    if ($r = mysqli_fetch_assoc($qG)) {
        if(file_exists('../'.$r['gambar'])) unlink('../'.$r['gambar']);
        mysqli_query($koneksi, "DELETE FROM produk_gambar WHERE id=$id_gbr");
        header("Location: produk.php?action=edit&id=" . $r['id_produk']);
        exit;
    }
}
// Handle Toggle Aktif
if ($action === 'toggle' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    mysqli_query($koneksi, "UPDATE produk SET aktif = IF(aktif=1,0,1) WHERE id_produk=$id AND id_toko=$id_toko");
    header("Location: produk.php?action=list");
    exit;
}
// Data Edit
$editProduk = null;
$gambarTambahan = [];
if ($action === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $qE = mysqli_query($koneksi, "SELECT * FROM produk WHERE id_produk=$id AND id_toko=$id_toko LIMIT 1");
    $editProduk = mysqli_fetch_assoc($qE);
    if (!$editProduk) { 
        $action = 'list'; 
    } elseif ($editProduk['aktif'] == 0) {
        echo "<script>alert('Produk yang sudah terjual tidak dapat diedit. Silakan ubah statusnya menjadi Tersedia terlebih dahulu.');window.location='produk.php';</script>";
        exit;
    } else {
        $qG = mysqli_query($koneksi, "SELECT * FROM produk_gambar WHERE id_produk=$id");
        while ($rG = mysqli_fetch_assoc($qG)) $gambarTambahan[] = $rG;
    }
}
// List produk
$produkList = [];
if ($action === 'list') {
    // Tampilkan pesan dari redirect
    if (isset($_GET['msg'])) {
        if ($_GET['msg'] === 'added') {
            $msg = 'Produk berhasil ditambahkan.'; $msgType = 'success';
        } elseif ($_GET['msg'] === 'updated') {
            $msg = 'Produk berhasil diperbarui.'; $msgType = 'success';
        }
    }
    
    $filter = isset($_GET['status']) ? $_GET['status'] : '';
    $search = isset($_GET['q']) ? mysqli_real_escape_string($koneksi, trim($_GET['q'])) : '';
    
    $where  = "WHERE id_toko=$id_toko";
    if ($search) $where .= " AND nama LIKE '%$search%'";
    if ($filter === 'tersedia') $where .= " AND aktif = 1";
    if ($filter === 'terjual') $where .= " AND aktif = 0";
    
    $qL = mysqli_query($koneksi, "SELECT * FROM produk $where ORDER BY created_at DESC");
    if ($qL) while ($r = mysqli_fetch_assoc($qL)) $produkList[] = $r;
}
$kategoriList = ['Casual','Vintage','Sport','Denim','Outwear','Atasan','Bawahan','Aksesoris'];
$kondisiList  = ['Like New','Excellent','Very Good','Good','Fair'];
?>
<!DOCTYPE html><html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Kelola Produk - LokalThrift</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<?php require 'navbar.php'; ?>
</head><body>
<div class="main-content">
<?php if ($msg): ?>
  <div class="alert alert-<?= $msgType ?>"><?= $msg ?></div>
<?php endif; ?>
<?php if ($action === 'list'): ?>
  <!-- ── LIST ── -->
  <div class="page-header">
    <div>
      <div class="page-title">Produk</div>
      <div class="page-sub"><?= count($produkList) ?> produk di tokomu</div>
    </div>
    <a href="produk.php?action=tambah" class="btn btn-primary">
      <i class="fa-solid fa-plus"></i> Tambah Produk
    </a>
  </div>
  <div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px;">
      <div style="display:flex;gap:8px;">
        <a href="produk.php?<?= $search ? 'q='.urlencode($search) : '' ?>" class="btn btn-sm <?= empty($filter) ? 'btn-primary' : 'btn-outline' ?>">Semua</a>
        <a href="produk.php?status=tersedia<?= $search ? '&q='.urlencode($search) : '' ?>" class="btn btn-sm <?= $filter==='tersedia' ? 'btn-primary' : 'btn-outline' ?>">Tersedia</a>
        <a href="produk.php?status=terjual<?= $search ? '&q='.urlencode($search) : '' ?>" class="btn btn-sm <?= $filter==='terjual' ? 'btn-primary' : 'btn-outline' ?>">Terjual</a>
      </div>
      <form method="GET" style="display:flex;gap:10px;">
        <?php if (!empty($filter)): ?><input type="hidden" name="status" value="<?= htmlspecialchars($filter) ?>"><?php endif; ?>
        <input name="q" class="form-input" placeholder="Cari nama produk..."
               value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" style="max-width:280px">
        <button class="btn btn-outline" type="submit">Cari</button>
      </form>
    </div>
    <?php if (empty($produkList)): ?>
      <div style="text-align:center;padding:50px;color:#aab">
        <i class="fa-solid fa-shirt" style="font-size:40px;color:#c8dff5;display:block;margin-bottom:12px"></i>
        Belum ada produk. <a href="produk.php?action=tambah" style="color:#2a85ff;font-weight:700">Tambah sekarang</a>
      </div>
    <?php else: ?>
    <table class="tbl">
      <thead><tr>
        <th>Produk</th><th>Harga</th><th>Kategori</th><th>Status</th><th>Aksi</th>
      </tr></thead>
      <tbody>
      <?php foreach ($produkList as $p): ?>
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:10px">
              <a href="../detail.php?id=<?= $p['id_produk'] ?>">
                <img src="<?= !empty($p['gambar']) ? '../' . htmlspecialchars($p['gambar']) : 'https://via.placeholder.com/48' ?>"
                     style="width:48px;height:48px;border-radius:10px;object-fit:cover;background:#f0f6fc"
                     onerror="this.src='https://via.placeholder.com/48'">
              </a>
              <div>
                <div style="font-weight:700"><?= htmlspecialchars($p['nama']) ?></div>
                <div style="font-size:11px;color:#8fa3b8"><?= htmlspecialchars($p['kondisi']) ?></div>
              </div>
            </div>
          </td>
          <td><?= rupiah($p['harga']) ?></td>
          <td><?= htmlspecialchars($p['kategori']) ?></td>
          <td>
            <a href="produk.php?action=toggle&id=<?= $p['id_produk'] ?>">
              <span class="status <?= $p['aktif'] ? 'status-aktif' : 'status-nonaktif' ?>">
                <?= $p['aktif'] ? 'Tersedia' : 'Terjual' ?>
              </span>
            </a>
          </td>
          <td>
            <div style="display:flex;gap:6px">
              <?php if ($p['aktif']): ?>
                <a href="produk.php?action=edit&id=<?= $p['id_produk'] ?>" class="btn btn-outline btn-sm" title="Edit Produk"><i class="fa-solid fa-pen"></i></a>
              <?php else: ?>
                <button class="btn btn-outline btn-sm" disabled title="Produk terjual tidak dapat diedit" style="opacity:0.5;cursor:not-allowed;"><i class="fa-solid fa-pen"></i></button>
              <?php endif; ?>
              <a href="produk.php?action=delete&id=<?= $p['id_produk'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus produk?')"><i class="fa-solid fa-trash"></i></a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
<?php else: ?>
  <!-- ── TAMBAH / EDIT ── -->
  <div class="page-header">
    <div>
      <div class="page-title"><?= $editProduk ? 'Edit Produk' : 'Tambah Produk' ?></div>
      <div class="page-sub">Isi detail produk dengan lengkap</div>
    </div>
    <a href="produk.php" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
  </div>
  <div class="card" style="max-width:800px;margin:0 auto">
    <form method="POST" enctype="multipart/form-data">
      <?php if($editProduk): ?><input type="hidden" name="id_produk" value="<?= $editProduk['id_produk'] ?>"><?php endif; ?>
      
      <div class="form-group">
        <label class="form-label">Nama Produk *</label>
        <input type="text" name="nama" class="form-input" required value="<?= $editProduk ? htmlspecialchars($editProduk['nama']) : '' ?>">
      </div>
      
      <div style="display:flex;gap:20px;flex-wrap:wrap">
        <div class="form-group" style="flex:1;min-width:200px">
          <label class="form-label">Harga (Rp) *</label>
          <input type="number" name="harga" class="form-input" required value="<?= $editProduk ? $editProduk['harga'] : '' ?>">
        </div>
        <div class="form-group" style="flex:1;min-width:200px">
          <label class="form-label">Kategori *</label>
          <select name="kategori" class="form-input" required>
            <?php foreach($kategoriList as $k): ?>
              <option <?= ($editProduk && $editProduk['kategori']==$k)?'selected':'' ?>><?= $k ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      
      <div style="display:flex;gap:20px;flex-wrap:wrap">
        <div class="form-group" style="flex:1;min-width:200px">
          <label class="form-label">Ukuran *</label>
          <input type="text" name="ukuran" class="form-input" required placeholder="S, M, L, XL" value="<?= $editProduk ? htmlspecialchars($editProduk['ukuran']) : '' ?>">
        </div>
        <div class="form-group" style="flex:1;min-width:200px">
          <label class="form-label">Kondisi *</label>
          <select name="kondisi" class="form-input" required>
            <?php foreach($kondisiList as $k): ?>
              <option <?= ($editProduk && $editProduk['kondisi']==$k)?'selected':'' ?>><?= $k ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      
      <div class="form-group">
        <label class="form-label">Deskripsi Produk *</label>
        <textarea name="deskripsi" class="form-input" rows="5" required><?= $editProduk ? htmlspecialchars($editProduk['deskripsi']) : '' ?></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">
          <input type="checkbox" name="aktif" value="1" <?= (!$editProduk || $editProduk['aktif']==1) ? 'checked' : '' ?>>
          Status Produk: Tersedia (Bisa dibeli)
        </label>
        <div style="font-size:12px;color:#8fa3b8;margin-top:4px">Jika di-uncheck, status produk akan menjadi "Terjual" (Habis).</div>
      </div>
      <div style="border-top:1px solid #e8f0fb;margin:24px 0;padding-top:24px">
        <div style="font-weight:700;font-size:15px;margin-bottom:16px;color:#0d1c2e">Foto Produk</div>
        
        <div class="form-group">
          <label class="form-label">Foto Utama * (Akan ditampilkan di katalog)</label>
          <?php if($editProduk && $editProduk['gambar']): ?>
            <img src="../<?= htmlspecialchars($editProduk['gambar']) ?>" style="height:100px;border-radius:10px;display:block;margin-bottom:10px">
          <?php endif; ?>
          <input type="file" name="gambar" class="form-input" accept="image/*" <?= $editProduk ? '' : 'required' ?>>
        </div>
        
        <div class="form-group" id="img-tambahan-container">
          <label class="form-label">Foto Tambahan (Detail produk)</label>
          <?php if(!empty($gambarTambahan)): ?>
            <div style="display:flex;gap:10px;margin-bottom:10px;flex-wrap:wrap">
              <?php foreach($gambarTambahan as $gt): ?>
                <div style="position:relative;display:inline-block">
                  <img src="../<?= htmlspecialchars($gt['gambar']) ?>" style="height:80px;border-radius:8px">
                  <a href="produk.php?action=delete_img&id_gbr=<?= $gt['id'] ?>" style="position:absolute;top:-8px;right:-8px;background:#ef4444;color:#fff;width:20px;height:20px;border-radius:50%;text-align:center;line-height:20px;font-size:10px;text-decoration:none" onclick="return confirm('Hapus foto ini?')"><i class="fa-solid fa-times"></i></a>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          <input type="file" name="gambar_tambahan[]" class="form-input" accept="image/*" style="margin-bottom:8px">
        </div>
        <button type="button" class="btn btn-outline btn-sm" onclick="addImgField()">+ Tambah Slot Foto Lain</button>
        <script>
          function addImgField() {
            var input = document.createElement('input');
            input.type = 'file';
            input.name = 'gambar_tambahan[]';
            input.className = 'form-input';
            input.accept = 'image/*';
            input.style.marginBottom = '8px';
            input.style.marginTop = '8px';
            document.getElementById('img-tambahan-container').appendChild(input);
          }
        </script>
      </div>
      <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px">
        <a href="produk.php" class="btn btn-outline">Batal</a>
        <button type="submit" class="btn btn-primary"><?= $editProduk ? 'Simpan Perubahan' : 'Tambah Produk' ?></button>
      </div>
    </form>
  </div>
<?php endif; ?>
</div>
</body></html>
