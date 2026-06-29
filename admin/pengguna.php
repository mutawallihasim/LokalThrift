<?php
require 'guard.php';

$msg = $err = '';

// ── HAPUS PENGGUNA ──
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $did = (int)$_GET['delete'];
    if ($did !== $id_admin) {
        mysqli_query($koneksi, "DELETE FROM pengguna WHERE id_pengguna=$did");
        $msg = 'Pengguna berhasil dihapus.';
    } else {
        $err = 'Tidak bisa menghapus akun sendiri.';
    }
}

// ── GANTI ROLE ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ganti_role') {
    $uid  = (int)($_POST['uid'] ?? 0);
    $role = in_array($_POST['role'] ?? '', ['pembeli','penjual','admin']) ? $_POST['role'] : 'pembeli';
    if ($uid && $uid !== $id_admin) {
        mysqli_query($koneksi, "UPDATE pengguna SET role='$role' WHERE id_pengguna=$uid");
        $msg = 'Role pengguna berhasil diperbarui.';
    }
}

// ── AMBIL DATA ──
$search = trim($_GET['q'] ?? '');
$filter = $_GET['role'] ?? '';

$where = '1=1';
if ($search) {
    $s = mysqli_real_escape_string($koneksi, $search);
    $where .= " AND (nama LIKE '%$s%' OR email LIKE '%$s%')";
}
if ($filter) {
    $f = mysqli_real_escape_string($koneksi, $filter);
    $where .= " AND role='$f'";
}

$qUser = mysqli_query($koneksi, "SELECT * FROM pengguna WHERE $where ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Manajemen Pengguna — Admin LokalThrift</title>
</head>
<body>
<?php require 'navbar.php'; ?>

<div class="main-content">
  <div class="page-header">
    <div>
      <div class="page-title">Manajemen Pengguna</div>
      <div class="page-sub">Kelola semua akun pengguna platform</div>
    </div>
  </div>

  <?php if ($msg): ?><div class="alert alert-success"><i class="fa-solid fa-check-circle"></i> <?= $msg ?></div><?php endif; ?>
  <?php if ($err): ?><div class="alert alert-error"><i class="fa-solid fa-triangle-exclamation"></i> <?= $err ?></div><?php endif; ?>

  <div class="card">
    <!-- Filter & Search -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px;">
      <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="pengguna.php" class="btn btn-sm <?= !$filter ? 'btn-primary' : 'btn-outline' ?>">Semua</a>
        <a href="pengguna.php?role=pembeli<?= $search ? '&q='.urlencode($search) : '' ?>" class="btn btn-sm <?= $filter==='pembeli' ? 'btn-primary' : 'btn-outline' ?>">Pembeli</a>
        <a href="pengguna.php?role=penjual<?= $search ? '&q='.urlencode($search) : '' ?>" class="btn btn-sm <?= $filter==='penjual' ? 'btn-primary' : 'btn-outline' ?>">Penjual</a>
        <a href="pengguna.php?role=admin<?= $search ? '&q='.urlencode($search) : '' ?>" class="btn btn-sm <?= $filter==='admin' ? 'btn-primary' : 'btn-outline' ?>">Admin</a>
      </div>
      <form method="GET" action="pengguna.php" style="display:flex;gap:8px;">
        <?php if ($filter): ?><input type="hidden" name="role" value="<?= htmlspecialchars($filter) ?>"><?php endif; ?>
        <div class="search-wrap">
          <i class="fa-solid fa-search"></i>
          <input class="form-input search-wrap" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Cari nama / email…" style="padding-left:36px;width:240px;">
        </div>
        <button class="btn btn-primary btn-sm" type="submit"><i class="fa-solid fa-search"></i> Cari</button>
      </form>
    </div>

    <table class="tbl">
      <thead>
        <tr>
          <th>#</th>
          <th>Pengguna</th>
          <th>No. HP</th>
          <th>Role</th>
          <th>Daftar</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($qUser && mysqli_num_rows($qUser) > 0): $no = 1; ?>
          <?php while ($u = mysqli_fetch_assoc($qUser)): ?>
          <tr>
            <td style="color:#8fa3b8;font-size:12px;"><?= $no++ ?></td>
            <td>
              <div style="display:flex;align-items:center;gap:10px;">
                <div class="user-avatar"><?= strtoupper(substr($u['nama'],0,1)) ?></div>
                <div>
                  <div style="font-weight:700;"><?= htmlspecialchars($u['nama']) ?></div>
                  <div style="font-size:11px;color:#8fa3b8;"><?= htmlspecialchars($u['email']) ?></div>
                </div>
              </div>
            </td>
            <td style="font-size:12px;color:#556980;"><?= htmlspecialchars($u['no_hp'] ?? '-') ?></td>
            <td><span class="badge badge-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
            <td style="font-size:12px;color:#8fa3b8;"><?= date('d M Y', strtotime($u['created_at'] ?? 'now')) ?></td>
            <td>
              <div style="display:flex;gap:6px;align-items:center;">
                <!-- Ganti Role -->
                <form method="POST" style="display:inline;">
                  <input type="hidden" name="action" value="ganti_role">
                  <input type="hidden" name="uid" value="<?= $u['id_pengguna'] ?>">
                  <select name="role" class="form-input" style="padding:5px 8px;font-size:11px;width:auto;" onchange="this.form.submit()" <?= $u['id_pengguna'] == $id_admin ? 'disabled' : '' ?>>
                    <option value="pembeli"  <?= $u['role']==='pembeli'  ? 'selected' : '' ?>>Pembeli</option>
                    <option value="penjual"  <?= $u['role']==='penjual'  ? 'selected' : '' ?>>Penjual</option>
                    <option value="admin"    <?= $u['role']==='admin'    ? 'selected' : '' ?>>Admin</option>
                  </select>
                </form>
                <?php if ($u['id_pengguna'] != $id_admin): ?>
                <a href="pengguna.php?delete=<?= $u['id_pengguna'] ?>&<?= http_build_query(['q'=>$search,'role'=>$filter]) ?>"
                   class="btn btn-danger btn-sm"
                   onclick="return confirm('Hapus pengguna <?= htmlspecialchars(addslashes($u['nama'])) ?>?')">
                  <i class="fa-solid fa-trash"></i>
                </a>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="6" style="text-align:center;padding:40px;color:#8fa3b8;">
            <i class="fa-solid fa-users" style="font-size:32px;display:block;margin-bottom:10px;opacity:0.3;"></i>
            Tidak ada pengguna ditemukan
          </td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
