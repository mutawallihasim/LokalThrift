<?php
// 1. Mengambil pesanan dinamis dari session 'orders_history' yang diisi dari checkout
$dynamicOrders = session('orders_history', []);

// 2. Jika session dinamis masih kosong (belum ada transaksi baru), kita siapkan data default sebagai sampel awal
if (empty($dynamicOrders)) {
    $cartItems = [
        [
            'invoice' => 'LT240S170001',
            'date' => '17 Mei 2026 • 10:30',
            'status' => 'diproses',
            'total' => 'Rp 210.000',
            'jumlah_barang' => 3,
            'images' => [
                'https://images.unsplash.com/photo-1620799140408-edc6dcb6d633?w=150',
                'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=150',
                'https://images.unsplash.com/photo-1576566588028-4147f3842f27?w=150'
            ]
        ],
        [
            'invoice' => 'LT240S140023',
            'date' => '14 Mei 2026 • 14:15',
            'status' => 'dikirim',
            'total' => 'Rp 125.000',
            'jumlah_barang' => 1,
            'images' => [
                'https://images.unsplash.com/photo-1576566588028-4147f3842f27?w=150'
            ]
        ],
        [
            'invoice' => 'LT240S100098',
            'date' => '10 Mei 2026 • 09:15',
            'status' => 'selesai',
            'total' => 'Rp 120.000',
            'jumlah_barang' => 2,
            'images' => [
                'https://images.unsplash.com/photo-1620799140408-edc6dcb6d633?w=150',
                'https://images.unsplash.com/photo-1576566588028-4147f3842f27?w=150'
            ]
        ]
    ];
} else {
    // Jika user baru saja klik "Pesan Sekarang", gunakan data dari session terbaru
    $cartItems = $dynamicOrders;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pesanan Saya - LokalThrift</title>
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
      background: #f4f8fc;
      color: #0d1c2e;
      min-height: 100vh;
    }

    /* NAVBAR UTAMA */
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

    /* CONTAINER KONTEN */
    .container {
      width: 90%;
      max-width: 850px;
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

    /* KARTU UTAMA DAFTAR PESANAN */
    .order-card-container {
      background: white;
      border-radius: 16px;
      border: 1px solid #eef2f7;
      overflow: hidden;
      box-shadow: 0 4px 20px rgba(0,0,0,0.01);
      padding: 5px 20px 20px 20px;
    }

    /* TAB NAVIGASI UTAMA */
    .tab-navigation {
      display: flex;
      border-bottom: 1px solid #eef2f7;
      margin-bottom: 20px;
    }

    .tab-item {
      padding: 18px 25px;
      font-size: 14px;
      font-weight: 600;
      color: #7d8c9e;
      background: none;
      border: none;
      position: relative;
      cursor: pointer;
      transition: color 0.2s;
    }

    .tab-item:hover {
      color: #2a85ff;
    }

    .tab-item.active {
      color: #2a85ff;
    }

    .tab-item.active::after {
      content: '';
      position: absolute;
      bottom: -1px;
      left: 0;
      width: 100%;
      height: 3px;
      background: #2a85ff;
      border-radius: 3px 3px 0 0;
    }

    /* DAFTAR ITEM PESANAN */
    .order-list {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .order-box {
      border: 1px solid #e2edf7;
      border-radius: 16px;
      padding: 20px;
      background: white;
      display: flex;
      flex-direction: column;
      gap: 15px;
      position: relative;
    }

    /* HEADER KARTU PESANAN */
    .order-box-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
    }

    .invoice-info h3 {
      font-size: 15px;
      font-weight: 700;
      color: #0d1c2e;
      margin-bottom: 4px;
    }

    .invoice-info p {
      font-size: 13px;
      color: #7d8c9e;
    }

    /* BADGES STATUS WARNA */
    .badge-status {
      padding: 6px 16px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 700;
    }

    .status-diproses {
      background: #fff6e9;
      color: #ffa800;
    }

    .status-dikirim {
      background: #eef5fc;
      color: #2a85ff;
    }

    .status-selesai {
      background: #ebf9f1;
      color: #10b981;
    }

    /* BODY KARTU: BANJARAN FOTO PRODUK MINI */
    .order-box-body {
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
    }

    .product-images-row {
      display: flex;
      gap: 12px;
    }

    .mini-img-wrapper {
      width: 80px;
      height: 80px;
      border-radius: 12px;
      overflow: hidden;
      background: #f8fbfe;
      border: 1px solid #eef2f7;
    }

    .mini-img-wrapper img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    /* RINGKASAN HARGA */
    .price-summary {
      text-align: left;
      flex: 1;
      padding-left: 20px;
    }

    .price-summary p {
      font-size: 13px;
      color: #7d8c9e;
      margin-bottom: 2px;
    }

    .price-summary h4 {
      font-size: 15px;
      font-weight: 700;
      color: #0d1c2e;
    }

    /* BUTTON LINK DETAIL */
    .btn-detail-link {
      font-size: 13px;
      font-weight: 700;
      color: #2a85ff;
      text-decoration: none;
      transition: color 0.2s;
    }

    .btn-detail-link:hover {
      text-decoration: underline;
      color: #1b6fd1;
    }

    /* EMPTY STATE JIKA KATEGORI KOSONG */
    .empty-tab-state {
      text-align: center;
      padding: 40px 20px;
      color: #8fa0b5;
      display: none;
    }
  </style>
</head>
<body>

  <div class="top-nav">
    <a href="/web-baru" class="brand">
      <i class="fa-solid fa-cloud-bolt"></i> <span>LokalThrift</span>
    </a>
  </div>

  <div class="container">
    <a href="/web-baru" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard</a>
    
    <div class="order-card-container">
      
      <div class="tab-navigation">
        <button class="tab-item active" onclick="filterStatus('all', this)">Semua</button>
        <button class="tab-item" onclick="filterStatus('diproses', this)">Diproses</button>
        <button class="tab-item" onclick="filterStatus('dikirim', this)">Dikirim</button>
        <button class="tab-item" onclick="filterStatus('selesai', this)">Selesai</button>
        <button class="tab-item" onclick="filterStatus('dibatalkan', this)">Dibatalkan</button>
      </div>

      <div class="order-list">
        <?php foreach($cartItems as $box): ?>
          <div class="order-box" data-status="<?= $box['status'] ?>">
            <div class="order-box-header">
              <div class="invoice-info">
                <h3><?= $box['invoice'] ?></h3>
                <p><?= $box['date'] ?></p>
              </div>
              <span class="badge-status status-<?= $box['status'] ?>"><?= ucfirst($box['status']) ?></span>
            </div>
            <div class="order-box-body">
              <div class="product-images-row">
                <?php foreach($box['images'] as $imgUrl): ?>
                  <div class="mini-img-wrapper">
                    <img src="<?= $imgUrl ?>" alt="Item Thrift">
                  </div>
                <?php endforeach; ?>
              </div>
              <div class="price-summary">
                <p><?= $box['jumlah_barang'] ?> Barang</p>
                <h4>Total: <?= $box['total'] ?></h4>
              </div>
              <a href="/detail-pesanan?invoice=<?= $box['invoice'] ?>&status=<?= $box['status'] ?>" class="btn-detail-link">Lihat Detail</a>
            </div>
          </div>
        <?php endforeach; ?>

        <div class="empty-tab-state" id="empty-state">
          <i class="fa-regular fa-folder-open" style="font-size: 36px; margin-bottom: 10px; display: block;"></i>
          Belum ada riwayat pesanan di kategori ini.
        </div>

      </div>
    </div>
  </div>

  <script>
    // FUNGSI JAVASCRIPT: FILTER DATA BERDASARKAN STATUS TAB YANG DIKLIK
    function filterStatus(status, element) {
        // Reset kelas active di semua tab button
        document.querySelectorAll('.tab-item').forEach(tab => tab.classList.remove('active'));
        element.classList.add('active');

        let adaItemVisible = false;
        const listOrder = document.querySelectorAll('.order-box');

        listOrder.forEach(box => {
            const statusBox = box.getAttribute('data-status');
            
            if (status === 'all' || statusBox === status) {
                box.style.display = 'flex';
                adaItemVisible = true;
            } else {
                box.style.display = 'none';
            }
        });

        // Tampilkan/sembunyikan pesan kosong jika tidak ada pesanan yang sesuai kategori
        const emptyState = document.getElementById('empty-state');
        if (adaItemVisible) {
            emptyState.style.display = 'none';
        } else {
            emptyState.style.display = 'block';
        }
    }
  </script>
</body>
</html>