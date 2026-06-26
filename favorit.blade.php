<?php
// Mengambil produk favorit dari session
$favoriteItems = session('favorites', []);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Favorit Saya - LokalThrift</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2 family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
    body { background-color: #f4f8fc; color: #0d1c2e; min-height: 100vh; }
    .top-nav { display: flex; justify-content: space-between; align-items: center; padding: 18px 5%; background-color: #ffffff; border-bottom: 1px solid #eef2f7; }
    .brand { display: flex; align-items: center; gap: 10px; text-decoration: none; color: #2a85ff; font-weight: 700; font-size: 20px; }
    
    .main-container { width: 90%; max-width: 1200px; margin: 30px auto; }
    .btn-back { display: inline-flex; align-items: center; gap: 8px; color: #556980; text-decoration: none; font-size: 14px; font-weight: 600; margin-bottom: 20px; }
    .page-title { font-size: 22px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
    .page-title i { color: #e53e3e; }

    .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 25px; }
    .product-card { background: white; border-radius: 16px; overflow: hidden; display: flex; flex-direction: column; text-decoration: none; color: inherit; border: 1px solid #eef2f7; position: relative; }
    .product-img-wrapper { width: 100%; aspect-ratio: 1 / 1; background: #f8fbfe; overflow: hidden; position: relative; }
    .product-img-wrapper img { width: 100%; height: 100%; object-fit: cover; }
    
    .btn-remove-fav { position: absolute; top: 15px; right: 15px; width: 34px; height: 34px; background: white; border: none; border-radius: 50%; display: flex; justify-content: center; align-items: center; color: #e53e3e; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }

    .product-info { padding: 15px; }
    .product-title { font-size: 15px; color: #556980; margin-bottom: 6px; font-weight: 500; }
    .product-price { font-size: 16px; font-weight: bold; color: #2a85ff; }

    .empty-state { text-align: center; padding: 60px 20px; background: white; border-radius: 16px; border: 1px solid #eef2f7; color: #7d8c9e; width: 100%; grid-column: 1 / -1; }
    .empty-state i { font-size: 48px; color: #ccd6e0; margin-bottom: 15px; }
  </style>
</head>
<body>

  <div class="top-nav">
    <a href="/web-baru" class="brand"><i class="fa-solid fa-cloud-bolt"></i> <span>LokalThrift</span></a>
  </div>

  <div class="main-container">
    <a href="/web-baru" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Kembali ke Katalog</a>
    <h2 class="page-title"><i class="fa-solid fa-heart"></i> Produk Favorit Saya</h2>

    <div class="products-grid">
      <?php if(empty($favoriteItems)): ?>
        <div class="empty-state">
          <i class="fa-solid fa-heart-crack"></i>
          <p>Belum ada produk favorit. Klik ikon hati pada barang thrift yang kamu suka!</p>
        </div>
      <?php else: ?>
        <?php foreach($favoriteItems as $id => $item): ?>
          <div class="product-card" id="fav-card-<?= $id ?>">
            <div class="product-img-wrapper">
              <a href="/detail?id=<?= $id ?>"><img src="<?= $item['gambar'] ?>" alt="Produk"></a>
              <button class="btn-remove-fav" onclick="hapusFavorit('<?= $id ?>')" title="Hapus dari Favorit">
                <i class="fa-solid fa-heart"></i>
              </button>
            </div>
            <div class="product-info">
              <h4 class="product-title"><?= htmlspecialchars($item['nama']) ?></h4>
              <div class="product-price">Rp <?= number_format($item['harga'], 0, ',', '.') ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <script>
    function hapusFavorit(id) {
      const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      
      fetch('/toggle-favorit', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
        body: JSON.stringify({ id: id })
      })
      .then(res => res.json())
      .then(data => {
        if(data.status === 'success') {
          // Hapus card dari layar tanpa reload
          document.getElementById(`fav-card-${id}`).remove();
          // Jika sudah habis, reload biar memicu tampilan empty state
          if(data.count === 0) {
              location.reload();
          }
        }
      });
    }
  </script>
</body>
</html>