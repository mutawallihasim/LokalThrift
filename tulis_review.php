<?php
session_start();
if (!isset($_SESSION['id_pengguna'])) {
  header('Location: login.php');
  exit;
}

require_once 'koneksi.php';

$id_pengguna = (int)$_SESSION['id_pengguna'];
$id_pesanan  = (int)($_GET['id_pesanan'] ?? 0);

if ($id_pesanan === 0) {
  header('Location: pesanan.php');
  exit;
}

// Cek kepemilikan pesanan
$qPesanan = mysqli_query($koneksi, "SELECT * FROM pesanan WHERE id_pesanan = $id_pesanan AND id_pengguna = $id_pengguna AND status = 'selesai'");
if (!$qPesanan || mysqli_num_rows($qPesanan) === 0) {
  header('Location: pesanan.php');
  exit;
}
$pesanan = mysqli_fetch_assoc($qPesanan);

// Cek apakah sudah pernah direview
$qCek = mysqli_query($koneksi, "SELECT id_review FROM review WHERE id_pesanan = $id_pesanan AND id_pengguna = $id_pengguna LIMIT 1");
$sudahDiulas = ($qCek && mysqli_num_rows($qCek) > 0);

// Ambil item pesanan
$qItem = mysqli_query($koneksi, "SELECT * FROM pesanan_item WHERE id_pesanan = $id_pesanan");
$items = [];
while ($row = mysqli_fetch_assoc($qItem)) {
  $items[] = $row;
}

$msg = '';
$msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$sudahDiulas) {
  $ratings = $_POST['rating'] ?? [];
  $ulasans = $_POST['ulasan'] ?? [];

  $successCount = 0;
  foreach ($items as $item) {
    $id_produk = $item['id_produk'];
    $rating = (int)($ratings[$id_produk] ?? 5);
    $ulasan = mysqli_real_escape_string($koneksi, trim($ulasans[$id_produk] ?? ''));

    if ($id_produk > 0) {
      $qInsert = "INSERT INTO review (id_produk, id_pengguna, id_pesanan, ulasan, rating, created_at)
                        VALUES ($id_produk, $id_pengguna, $id_pesanan, '$ulasan', $rating, NOW())";
      if (mysqli_query($koneksi, $qInsert)) {
        $successCount++;
      }
    }
  }

  if ($successCount > 0) {
    $sudahDiulas = true;
    $msg = "Terima kasih! Ulasan Anda berhasil disimpan.";
  } else {
    $msg = "Gagal menyimpan ulasan. Silakan coba lagi.";
    $msgType = "error";
  }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tulis Ulasan - LokalThrift</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', 'Helvetica Neue', Arial, sans-serif;
    }

    body {
      background: #eef5fc;
      color: #0d1c2e;
    }

    .header {
      background: white;
      padding: 16px 20px;
      display: flex;
      align-items: center;
      gap: 16px;
      border-bottom: 1px solid #e0ecf8;
      position: sticky;
      top: 0;
      z-index: 10;
    }

    .btn-back {
      color: #0d1c2e;
      text-decoration: none;
      font-size: 18px;
    }

    .header h1 {
      font-size: 16px;
      font-weight: 800;
    }

    .container {
      max-width: 600px;
      margin: 0 auto;
      padding: 24px 16px;
    }

    .alert {
      padding: 14px;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 600;
      margin-bottom: 20px;
    }

    .alert-success {
      background: #e6f9f0;
      color: #10b981;
      border-left: 4px solid #10b981;
    }

    .alert-error {
      background: #fff0f0;
      color: #e53935;
      border-left: 4px solid #e53935;
    }

    .alert-info {
      background: #eff6ff;
      color: #2a85ff;
      border-left: 4px solid #2a85ff;
    }

    .item-card {
      background: white;
      border-radius: 16px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .item-header {
      display: flex;
      gap: 16px;
      align-items: center;
      margin-bottom: 20px;
      padding-bottom: 16px;
      border-bottom: 1px solid #eef5fc;
    }

    .item-header img {
      width: 64px;
      height: 64px;
      border-radius: 10px;
      object-fit: cover;
      background: #eef5fc;
    }

    .item-title {
      font-size: 15px;
      font-weight: 700;
      margin-bottom: 4px;
    }

    .item-price {
      font-size: 13px;
      color: #8fa3b8;
      font-weight: 600;
    }

    .rating-wrap {
      margin-bottom: 20px;
    }

    .rating-label {
      font-size: 14px;
      font-weight: 700;
      margin-bottom: 10px;
      display: block;
    }

    .stars {
      display: flex;
      gap: 8px;
      flex-direction: row-reverse;
      justify-content: flex-end;
    }

    .stars input {
      display: none;
    }

    .stars label {
      font-size: 28px;
      color: #c8dff5;
      cursor: pointer;
      transition: 0.2s;
    }

    .stars label:hover,
    .stars label:hover~label,
    .stars input:checked~label {
      color: #fbbf24;
    }

    .form-group {
      margin-bottom: 16px;
    }

    .form-group textarea {
      width: 100%;
      padding: 14px;
      border: 1.5px solid #e0ecf8;
      border-radius: 12px;
      font-size: 14px;
      outline: none;
      resize: vertical;
      min-height: 100px;
      transition: 0.2s;
    }

    .form-group textarea:focus {
      border-color: #2a85ff;
    }

    .btn-submit {
      width: 100%;
      padding: 16px;
      background: #2a85ff;
      color: white;
      border: none;
      border-radius: 12px;
      font-size: 15px;
      font-weight: 800;
      cursor: pointer;
      transition: 0.2s;
      margin-top: 10px;
    }

    .btn-submit:hover {
      background: #1b6be0;
    }
  </style>
</head>

<body>

  <div class="header">
    <a href="pesanan.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i></a>
    <h1>Beri Ulasan Produk</h1>
  </div>

  <div class="container">
    <?php if ($msg): ?>
      <div class="alert alert-<?= $msgType ?>"><?= $msg ?></div>
    <?php endif; ?>

    <?php if ($sudahDiulas): ?>
      <div class="alert alert-info">
        <i class="fa-solid fa-circle-check" style="margin-right:8px;"></i>
        Anda sudah memberikan ulasan untuk pesanan ini. Terima kasih!
      </div>
      <a href="pesanan.php" class="btn-submit" style="display:block; text-align:center; text-decoration:none; background:#eef5fc; color:#0d1c2e;">Kembali ke Pesanan</a>
    <?php else: ?>
      <form method="POST" action="tulis_review.php?id_pesanan=<?= $id_pesanan ?>">

        <?php foreach ($items as $item): ?>
          <div class="item-card">
            <div class="item-header">
              <img src="<?= htmlspecialchars($item['gambar']) ?>" alt="<?= htmlspecialchars($item['nama']) ?>" onerror="this.src='https://via.placeholder.com/64'">
              <div>
                <div class="item-title"><?= htmlspecialchars($item['nama']) ?></div>
                <div class="item-price">Rp <?= number_format($item['harga'], 0, ',', '.') ?></div>
              </div>
            </div>

            <div class="rating-wrap">
              <label class="rating-label">Kualitas Produk</label>
              <div class="stars">
                <input type="radio" id="star5_<?= $item['id_produk'] ?>" name="rating[<?= $item['id_produk'] ?>]" value="5" checked>
                <label for="star5_<?= $item['id_produk'] ?>"><i class="fa-solid fa-star"></i></label>

                <input type="radio" id="star4_<?= $item['id_produk'] ?>" name="rating[<?= $item['id_produk'] ?>]" value="4">
                <label for="star4_<?= $item['id_produk'] ?>"><i class="fa-solid fa-star"></i></label>

                <input type="radio" id="star3_<?= $item['id_produk'] ?>" name="rating[<?= $item['id_produk'] ?>]" value="3">
                <label for="star3_<?= $item['id_produk'] ?>"><i class="fa-solid fa-star"></i></label>

                <input type="radio" id="star2_<?= $item['id_produk'] ?>" name="rating[<?= $item['id_produk'] ?>]" value="2">
                <label for="star2_<?= $item['id_produk'] ?>"><i class="fa-solid fa-star"></i></label>

                <input type="radio" id="star1_<?= $item['id_produk'] ?>" name="rating[<?= $item['id_produk'] ?>]" value="1">
                <label for="star1_<?= $item['id_produk'] ?>"><i class="fa-solid fa-star"></i></label>
              </div>
            </div>

            <div class="form-group">
              <label class="rating-label">Ulasan Lengkap (Opsional)</label>
              <textarea name="ulasan[<?= $item['id_produk'] ?>]" placeholder="Bagaimana kondisi barangnya? Apakah sesuai dengan deskripsi?"></textarea>
            </div>
          </div>
        <?php endforeach; ?>

        <button type="submit" class="btn-submit">Kirim Ulasan</button>

      </form>
    <?php endif; ?>
  </div>

</body>

</html>