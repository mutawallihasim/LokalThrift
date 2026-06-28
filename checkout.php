<?php
// Mengambil data barang yang siap di-checkout dari session
$checkoutItems = session('checkout_items', []);
$totalBelanja = 0;
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Checkout Pembayaran - LokalThrift</title>
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
      color: #2a85ff;
      font-weight: 700;
      font-size: 20px;
    }

    .main-container {
      width: 90%;
      max-width: 1100px;
      margin: 40px auto;
      display: grid;
      grid-template-columns: 1.5fr 1fr;
      gap: 30px;
    }

    @media (max-width: 850px) {
      .main-container {
        grid-template-columns: 1fr;
      }
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

    .card-box {
      background: white;
      padding: 25px;
      border-radius: 16px;
      border: 1px solid #eef2f7;
      margin-bottom: 20px;
    }

    .card-title {
      font-size: 16px;
      font-weight: 700;
      margin-bottom: 15px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    /* PILIHAN METODE PEMBAYARAN */
    .payment-options {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .payment-method {
      display: flex;
      align-items: center;
      gap: 15px;
      padding: 15px;
      border: 1px solid #e2edf7;
      border-radius: 12px;
      cursor: pointer;
      transition: 0.2s;
    }

    .payment-method:hover {
      background: #f8fbfe;
      border-color: #2a85ff;
    }

    .payment-method input[type="radio"] {
      transform: scale(1.2);
      accent-color: #2a85ff;
    }

    .payment-method i {
      font-size: 20px;
      color: #556980;
      width: 25px;
    }

    /* RINCIAN PRODUK */
    .product-row {
      display: flex;
      align-items: center;
      gap: 15px;
      margin-bottom: 15px;
      border-bottom: 1px solid #f4f8fc;
      padding-bottom: 15px;
    }

    .product-row:last-child {
      border-bottom: none;
      margin-bottom: 0;
      padding-bottom: 0;
    }

    .product-img {
      width: 65px;
      height: 65px;
      object-fit: cover;
      border-radius: 10px;
    }

    .product-info {
      flex: 1;
    }

    .product-name {
      font-size: 14px;
      font-weight: 600;
    }

    .product-meta {
      font-size: 12px;
      color: #7d8c9e;
      margin-top: 2px;
    }

    .product-price {
      font-size: 14px;
      font-weight: 700;
      color: #2a85ff;
      text-align: right;
    }

    /* SUMMARY SIDEBAR */
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

    /* PERBAIKAN TOMBOL PESAN SEKARANG */
    .btn-order-now {
      width: 100%;
      padding: 14px;
      background: #2a85ff;
      color: white;
      border: none;
      border-radius: 12px;
      font-size: 15px;
      font-weight: 700;
      cursor: pointer;
      margin-top: 25px;
      box-shadow: 0 4px 12px rgba(42, 133, 255, 0.2);
    }

    .btn-order-now:hover {
      background: #1b6fd1;
    }

    .empty-state {
      text-align: center;
      padding: 40px;
      color: #7d8c9e;
    }
  </style>
</head>

<body>

  <div class="top-nav">
    <a href="/web-baru" class="brand"><i class="fa-solid fa-cloud-bolt"></i> <span>LokalThrift</span></a>
  </div>

  <div style="width: 90%; max-width: 1100px; margin: 30px auto 0 auto;">
    <a href="/keranjang" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Kembali ke Keranjang</a>
    <h2 style="font-size: 22px; font-weight: 700; margin-bottom: 20px;">Checkout Pembayaran</h2>
  </div>

  <div class="main-container">
    <div class="left-section">
      <div class="card-box">
        <h3 class="card-title"><i class="fa-solid fa-location-dot" style="color:#2a85ff;"></i> Alamat Pengiriman</h3>
        <p style="font-weight: 700; font-size: 14px; margin-bottom: 3px;">Rumah (Sabila Nur Sakila)</p>
        <p style="font-size: 13px; color: #556980; line-height: 1.5;">Jl. Mawar No.12, Kec. Coblong, Kota Bandung, Jawa Barat 40132</p>
      </div>

      <div class="card-box">
        <h3 class="card-title"><i class="fa-solid fa-credit-card" style="color:#2a85ff;"></i> Metode Pembayaran</h3>
        <div class="payment-options">

          <label class="payment-method">
            <input type="radio" name="payment_choice" value="COD" checked>
            <i class="fa-solid fa-hand-holding-dollar" style="color: #10b981;"></i>
            <div>
              <strong style="font-size:14px; display:block;">COD (Bayar di Tempat)</strong>
              <span style="font-size:12px; color:#7d8c9e;">Bayar tunai langsung saat pakaian thrift tiba di rumahmu</span>
            </div>
          </label>

          <label class="payment-method">
            <input type="radio" name="payment_choice" value="QRIS">
            <i class="fa-solid fa-qrcode" style="color: #2a85ff;"></i>
            <div>
              <strong style="font-size:14px; display:block;">QRIS / E-Wallet</strong>
              <span style="font-size:12px; color:#7d8c9e;">Scan praktis via wondr by BNI, BRImo, Dana, atau GoPay</span>
            </div>
          </label>

          <label class="payment-method">
            <input type="radio" name="payment_choice" value="Transfer">
            <i class="fa-solid fa-building-columns" style="color: #ff5252;"></i>
            <div>
              <strong style="font-size:14px; display:block;">Transfer Bank Virtual Account</strong>
              <span style="font-size:12px; color:#7d8c9e;">Bank BNI, BRI, Mandiri otomatis terverifikasi</span>
            </div>
          </label>
        </div>
      </div>
    </div>

    <div class="right-section">
      <div class="card-box">
        <h3 class="card-title"><i class="fa-solid fa-basket-shopping" style="color:#2a85ff;"></i> Rincian Item</h3>

        <?php if (empty($checkoutItems)): ?>
          <div class="empty-state">
            <p>Tidak ada item untuk dibeli.</p>
          </div>
        <?php else: ?>
          <div style="margin-bottom: 20px;">
            <?php foreach ($checkoutItems as $item):
              $totalBelanja += intval($item['harga']);
            ?>
              <div class="product-row">
                <img class="product-img" src="<?= $item['gambar'] ?>" alt="Produk">
                <div class="product-info">
                  <h5 class="product-name"><?= htmlspecialchars($item['nama']) ?></h5>
                  <p class="product-meta"><?= $item['varian'] ?></p>
                </div>
                <div class="product-price">Rp <?= number_format($item['harga'], 0, ',', '.') ?></div>
              </div>
            <?php endforeach; ?>
          </div>

          <h3 class="card-title" style="border-top: 1px solid #f4f8fc; padding-top: 15px;">Ringkasan Belanja</h3>
          <div class="summary-row"><span>Subtotal Produk</span><span>Rp <?= number_format($totalBelanja, 0, ',', '.') ?></span></div>
          <div class="summary-row"><span>Biaya Pengiriman</span><span style="color:#10b981; font-weight:600;">Gratis Ongkir</span></div>
          <div class="summary-total"><span>Total Pembayaran</span><span style="color:#2a85ff;">Rp <?= number_format($totalBelanja, 0, ',', '.') ?></span></div>

          <button class="btn-order-now" onclick="buatPesananSekarang()">Pesan Sekarang</button>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <script>
    function buatPesananSekarang() {
      const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      const metodeTerpilih = document.querySelector('input[name="payment_choice"]:checked').value;

      // Kirim data ke backend Laravel untuk memproses pembuatan riwayat pesanan dinamis
      fetch('/create-order', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token
          },
          body: JSON.stringify({
            metode: metodeTerpilih
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            alert(`Pesanan Berhasil Dibuat!\nInvoice Anda: ${data.invoice}\nMetode Pembayaran: ${metodeTerpilih}`);
            // Alihkan otomatis menuju halaman riwayat pesanan
            window.location.href = "/pesanan";
          } else {
            alert("Gagal memproses pesanan belanja.");
          }
        })
        .catch(error => console.error("Error:", error));
    }
  </script>
</body>

</html>