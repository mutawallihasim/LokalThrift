<?php
require 'guard.php';

$msg = '';
$msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_toko  = mysqli_real_escape_string($koneksi, trim($_POST['nama_toko']  ?? ''));
    $deskripsi  = mysqli_real_escape_string($koneksi, trim($_POST['deskripsi']  ?? ''));
    $alamat     = mysqli_real_escape_string($koneksi, trim($_POST['alamat']     ?? ''));

    // Handle upload foto toko
    $foto_toko = mysqli_real_escape_string($koneksi, trim($_POST['foto_toko_lama'] ?? ''));
    if (!empty($_FILES['foto_toko']['name'])) {
        $ext     = strtolower(pathinfo($_FILES['foto_toko']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp'];
        if (!in_array($ext, $allowed)) {
            $msg = 'Format foto harus JPG, PNG, atau WEBP.'; $msgType = 'error';
        } elseif ($_FILES['foto_toko']['size'] > 2 * 1024 * 1024) {
            $msg = 'Ukuran foto maksimal 2MB.'; $msgType = 'error';
        } else {
            $namaFile = 'toko_' . time() . '_' . rand(100,999) . '.' . $ext;
            $tujuan   = '../uploads/toko/' . $namaFile;
            if (move_uploaded_file($_FILES['foto_toko']['tmp_name'], $tujuan)) {
                // Hapus foto lama kalau ada
                $lama = $_POST['foto_toko_lama'] ?? '';
                if ($lama && strpos($lama, 'uploads/') !== false && file_exists('../' . $lama)) {
                    @unlink('../' . $lama);
                }
                $foto_toko = mysqli_real_escape_string($koneksi, 'uploads/toko/' . $namaFile);
            } else {
                $msg = 'Gagal upload foto.'; $msgType = 'error';
            }
        }
    }

    if (empty($msg)) {
        if (empty($nama_toko)) {
            $msg = 'Nama toko wajib diisi.'; $msgType = 'error';
        } else {
            mysqli_query($koneksi,
                "UPDATE toko SET nama_toko='$nama_toko', deskripsi='$deskripsi',
                 alamat='$alamat', foto_toko='$foto_toko'
                 WHERE id_toko=$id_toko");
            $msg = 'Profil toko berhasil disimpan.';
            // Refresh data toko
            $qToko = mysqli_query($koneksi, "SELECT * FROM toko WHERE id_toko=$id_toko LIMIT 1");
            $toko  = mysqli_fetch_assoc($qToko);
        }
    }
}
?>
<!DOCTYPE html><html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Profil Toko - LokalThrift</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<?php require 'navbar.php'; ?>
</head><body>

<div class="main-content">
  <div class="page-header">
    <div>
      <div class="page-title">Profil Toko</div>
      <div class="page-sub">Kelola informasi toko kamu</div>
    </div>
  </div>

  <?php if ($msg): ?>
    <div class="alert alert-<?= $msgType ?>"><?= $msg ?></div>
  <?php endif; ?>

  <div style="display:flex;gap:20px;align-items:flex-start;flex-wrap:wrap">

    <!-- Preview toko -->
    <div class="card" style="width:220px;text-align:center;flex-shrink:0">
      <img id="foto-preview"
           src="<?= !empty($toko['foto_toko']) ? '../' . htmlspecialchars($toko['foto_toko']) : 'https://via.placeholder.com/80' ?>"
           style="width:80px;height:80px;border-radius:50%;object-fit:cover;background:#f0f6fc;margin-bottom:12px"
           onerror="this.src='https://via.placeholder.com/80'">
      <div style="font-size:15px;font-weight:800;color:#0d1c2e"><?= htmlspecialchars($toko['nama_toko']) ?></div>
      <div style="font-size:12px;color:#8fa3b8;margin-top:4px"><?= htmlspecialchars($toko['alamat'] ?? 'Belum ada alamat') ?></div>
      <div style="font-size:12px;color:#556980;margin-top:8px;line-height:1.5">
        <?= htmlspecialchars($toko['deskripsi'] ?? 'Belum ada deskripsi') ?>
      </div>
    </div>

    <!-- Form edit -->
    <div class="card" style="flex:1;min-width:280px">
      <div class="card-title">Edit Profil Toko</div>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="foto_toko_lama" value="<?= htmlspecialchars($toko['foto_toko'] ?? '') ?>">
        
        <div class="form-group">
          <label class="form-label">Nama Toko *</label>
          <input class="form-input" name="nama_toko" required
                 value="<?= htmlspecialchars($toko['nama_toko']) ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Alamat Toko</label>
          <input class="form-input" name="alamat"
                 value="<?= htmlspecialchars($toko['alamat'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Foto Toko</label>
          <input class="form-input" type="file" name="foto_toko" accept="image/jpeg,image/jpg,image/png,image/webp"
                 onchange="if(this.files[0]){const r=new FileReader();r.onload=e=>document.getElementById('foto-preview').src=e.target.result;r.readAsDataURL(this.files[0])}">
          <div style="font-size:11px;color:#8fa3b8;margin-top:4px">Format: JPG, PNG, WEBP. Maksimal 2MB.</div>
          <?php if (!empty($toko['foto_toko'])): ?>
            <div style="margin-top:8px;font-size:12px;color:#556980">
              Foto saat ini: <strong><?= basename($toko['foto_toko']) ?></strong>
            </div>
          <?php endif; ?>
        </div>
        <div class="form-group">
          <label class="form-label">Deskripsi Toko</label>
          <textarea class="form-input" name="deskripsi" rows="3"><?= htmlspecialchars($toko['deskripsi'] ?? '') ?></textarea>
        </div>
        <button class="btn btn-primary" type="submit" style="width:100%;justify-content:center;padding:13px">
          <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
        </button>
      </form>
    </div>
  </div>
</div>
</body></html>
