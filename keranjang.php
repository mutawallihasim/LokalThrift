<?php
$cartItems = session('cart', []);
$subtotal = 0;
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Keranjang Belanja - LokalThrift</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Plus Jakarta Sans', sans-serif;
    }

    body {
      background-color: #f4f8fc;
      color: #0d1c2e;
      min-height: 100vh;
    }

    .top-nav {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 18px 5%;
      background-color: #ffffff;
      border-bottom: 1px solid #eef2f7;
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 10px;
      text-decoration: none;
      color: #0d1c2e;
      font-weight: 700;
      font-size: 20px;
    }

    .brand i {
      color: #2a85ff;
    }

    .main-container {
      width: 90%;
      max-width: 1200px;
      margin: 30px auto;
    }

    .btn-back {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      color: #556980;
      text-decoration: none;
      font-size: 14px;
      font-weight: 600;
      margin-bottom: 20px;
    }

    .cart-title {
      font-size: 22px;
      font-weight: 700;
      margin-bottom: 20px;
    }

    .cart-wrapper {
      display: grid;
      grid-template-columns: 1.6fr 1fr;
      gap: 30px;
      align-items: start;
    }

    @media (max-width: 900px) {
      .cart-wrapper {
        grid-template-columns: 1fr;
      }
    }

    .item-list-container {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    /* CARD PRODUK */
    .cart-item {
      background: white;
      padding: 20px;
      border-radius: 16px;
      border: 1px solid #eef2f7;
      display: flex;
      align-items: center;
      gap: 15px;
      position: relative;
    }

    .item-checkbox {
      transform: scale(1.3);
      accent-color: #2a85ff;
      cursor: pointer;
    }

    .item-img {
      width: 90px;
      height: 90px;
      object-fit: cover;
      border-radius: 12px;
    }

    .item-details {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 4px;
    }

    .item-name {
      font-size: 16px;
      font-weight: 600;
      padding-right: 80px;
    }

    .item-price {
      font-size: 16px;
      font-weight: 700;
      color: #0d1c2e;
    }

    /* CONTROLLER FITUR HAPUS DAN EDIT DI SEBELAH KANAN */
    .item-actions {
      display: flex;
      flex-direction: column;
      align-items: flex-end;
      gap: 15px;
      margin-left: auto;
    }

    .btn-delete {
      background: none;
      border: none;
      color: #a0aec0;
      cursor: pointer;
      font-size: 16px;
      transition: 0.2s;
    }

    .btn-delete:hover {
      color: #e53e3e;
    }

    /* TOMBOL EDIT KUANTITAS */
    .quantity-control {
      display: flex;
      align-items: center;
      border: 1px solid #e2edf7;
      border-radius: 8px;
      background: #f8fbfe;
      overflow: hidden;
    }

    .btn-qty {
      width: 28px;
      height: 28px;
      background: none;
      border: none;
      font-size: 14px;
      color: #2a85ff;
      cursor: pointer;
      font-weight: bold;
    }

    .btn-qty:hover {
      background: #eef5fc;
    }

    .qty-value {
      width: 30px;
      text-align: center;
      font-size: 13px;
      font-weight: 600;
      color: #0d1c2e;
    }

    /* SIDEBAR RINGKASAN */
    .summary-card {
      background: white;
      padding: 25px;
      border-radius: 16px;
      border: 1px solid #eef2f7;
    }

    .summary-title {
      font-size: 16px;
      font-weight: 700;
      margin-bottom: 20px;
    }

    .summary-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 12px;
      font-size: 14px;
      color: #556980;
    }

    .summary-total {
      display: flex;
      justify-content: space-between;
      font-size: 18px;
      font-weight: 800;
      border-top: 1px solid #eef2f7;
      padding-top: 15px;
      margin-top: 15px;
    }

    .btn-checkout {
      width: 100%;
      padding: 14px;
      background: #2a85ff;
      color: white;
      border: none;
      border-radius: 12px;
      font-size: 15px;
      font-weight: 700;
      cursor: pointer;
      margin-top: 20px;
    }

    .empty-state {
      text-align: center;
      padding: 50px 20px;
      background: white;
      border-radius: 16px;
      border: 1px solid #eef2f7;
      color: #7d8c9e;
    }

    .empty-state i {
      font-size: 48px;
      color: #ccd6e0;
      margin-bottom: 15px;
    }
  </style>
</head>

<body>

  <div class="top-nav">
    <a href="/web-baru" class="brand"><i class="fa-solid fa-cloud-bolt"></i> <span>LokalThrift</span></a>
  </div>

  <div class="main-container">
    <a href="/web-baru" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard</a>
    <h2 class="cart-title">Keranjang Belanja</h2>

    <div class="cart-wrapper">
      <div class="item-list-container">
        <?php if (empty($cartItems)): ?>
          <div class="empty-state">
            <i class="fa-solid fa-basket-shopping"></i>
            <p>Keranjang belanjamu kosong. Yuk, tambahkan item pakaian favoritmu!</p>
          </div>
        <?php else: ?>
          <?php foreach ($cartItems as $key => $item):
            $itemTotal = $item['harga'] * $item['jumlah'];
            $subtotal += $itemTotal;
          ?>
            <div class="cart-item" id="item-row-<?= $key ?>" data-price="<?= $item['harga'] ?>" data-key="<?= $key ?>">
              <input type="checkbox" class="item-checkbox" checked onclick="hitungUlangTotal()">
              <img class="item-img" src="<?= $item['gambar'] ?>" alt="Produk">

              <div class="item-details">
                <h4 class="item-name"><?= htmlspecialchars($item['nama']) ?></h4>
                <p style="font-size:13px; color:#7d8c9e;">Ukuran: <strong><?= $item['ukuran'] ?></strong></p>
                <div class="item-price">Rp <?= number_format($item['harga'], 0, ',', '.') ?></div>
              </div>

              <!-- FITUR BARU: EDIT PESANAN & HAPUS -->
              <div class="item-actions">
                <!-- Tombol Hapus (Sampah) -->
                <button class="btn-delete" onclick="hapusItem('<?= $key ?>')" title="Hapus Produk">
                  <i class="fa-regular fa-trash-can"></i>
                </button>

                <!-- Tombol Edit Jumlah (+ / -) -->
                <div class="quantity-control">
                  <button class="btn-qty" onclick="editJumlah('<?= $key ?>', 'decrease')">-</button>
                  <span class="qty-value" id="qty-val-<?= $key ?>"><?= $item['jumlah'] ?></span>
                  <button class="btn-qty" onclick="editJumlah('<?= $key ?>', 'increase')">+</button>
                </div>
              </div>

            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <div class="cart-sidebar">
        <div class="summary-card">
          <h3 class="summary-title">Ringkasan Belanja</h3>
          <div class="summary-row"><span>Subtotal</span><span id="subtotal-display">Rp <?= number_format($subtotal, 0, ',', '.') ?></span></div>
          <div class="summary-row"><span>Pengiriman</span><span style="color: #10B981; font-weight:600;">Gratis</span></div>
          <div class="summary-total"><span>Total Belanja</span><span id="total-display" style="color:#2a85ff;">Rp <?= number_format($subtotal, 0, ',', '.') ?></span></div>

          <?php if (!empty($cartItems)): ?>
            <button class="btn-checkout" onclick="prosesCheckout()">Beli Sekarang</button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <script>
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // AJX FITUR 1: EDIT JUMLAH PESANAN
    function editJumlah(key, action) {
      fetch('/update-cart-quantity', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token
          },
          body: JSON.stringify({
            key,
            action
          })
        })
        .then(res => res.json())
        .then(data => {
          if (data.status === 'success') {
            if (data.removed) {
              document.getElementById(`item-row-${key}`).remove();
              location.reload(); // Refresh halaman jika item habis (0)
            } else {
              document.getElementById(`qty-val-${key}`).innerText = data.jumlah;
              hitungUlangTotal();
            }
          }
        });
    }

    // AJAX FITUR 2: HAPUS ITEM DARI KERANJANG
    function hapusItem(key) {
      if (confirm("Apakah kamu yakin ingin menghapus produk ini dari keranjang?")) {
        fetch('/remove-from-cart', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({
              key
            })
          })
          .then(res => res.json())
          .then(data => {
            if (data.status === 'success') {
              document.getElementById(`item-row-${key}`).remove();
              location.reload();
            }
          });
      }
    }

    function hitungUlangTotal() {
      let total = 0;
      let adaItem = false;
      document.querySelectorAll('.cart-item').forEach(item => {
        adaItem = true;
        if (item.querySelector('.item-checkbox').checked) {
          const harga = parseInt(item.getAttribute('data-price'));
          const jumlah = parseInt(item.querySelector('.qty-value').innerText);
          total += (harga * jumlah);
        }
      });
      document.getElementById('subtotal-display').innerText = "Rp " + total.toLocaleString('id-ID');
      document.getElementById('total-display').innerText = "Rp " + total.toLocaleString('id-ID');
    }

    function prosesCheckout() {
      const itemsToCheckout = [];
      document.querySelectorAll('.cart-item').forEach(item => {
        if (item.querySelector('.item-checkbox').checked) {
          const qty = item.querySelector('.qty-value').innerText;
          itemsToCheckout.push({
            nama: item.querySelector('.item-name').innerText,
            varian: "Size M | Jumlah: " + qty + "x",
            harga: parseInt(item.getAttribute('data-price')) * parseInt(qty),
            gambar: item.querySelector('.item-img').src
          });
        }
      });

      if (itemsToCheckout.length === 0) {
        alert("Silakan pilih minimal satu produk di keranjang!");
        return;
      }

      fetch('/set-checkout', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': token
        },
        body: JSON.stringify({
          items: itemsToCheckout
        })
      }).then(res => {
        if (res.ok) window.location.href = "/checkout";
      });
    }
  </script>
</body>

</html>