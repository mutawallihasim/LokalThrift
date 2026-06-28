<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Detail Produk - LokalThrift</title>
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
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
    }

    .detail-container {
      width: 100%;
      max-width: 800px;
      background: white;
      border-radius: 24px;
      padding: 30px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
      display: flex;
      flex-direction: column;
      gap: 25px;
    }

    .detail-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .btn-back {
      display: flex;
      align-items: center;
      gap: 8px;
      color: #2a85ff;
      text-decoration: none;
      font-size: 14px;
      font-weight: 600;
      transition: 0.2s;
    }

    .btn-back:hover {
      color: #1b6fd1;
    }

    .main-content {
      display: flex;
      gap: 30px;
    }

    .image-gallery {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .main-img-wrapper {
      width: 100%;
      aspect-ratio: 1 / 1;
      background: #f8fbfe;
      border-radius: 20px;
      overflow: hidden;
      border: 1px solid #eef2f7;
    }

    .main-img-wrapper img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .info-section {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .product-title {
      font-size: 22px;
      font-weight: bold;
      color: #0d1c2e;
    }

    .product-price {
      font-size: 20px;
      font-weight: 800;
      color: #2a85ff;
    }

    .size-options {
      display: flex;
      gap: 10px;
    }

    .size-btn {
      width: 45px;
      height: 38px;
      border: 1px solid #e2edf7;
      background: #f8fbfe;
      border-radius: 8px;
      font-weight: bold;
      color: #556980;
      cursor: pointer;
    }

    .size-btn.active {
      border: 2px solid #2a85ff;
      background: #eef5fc;
      color: #2a85ff;
    }

    .badge-condition {
      display: inline-block;
      padding: 6px 12px;
      background: #eef5fc;
      color: #2a85ff;
      font-size: 13px;
      font-weight: 600;
      border-radius: 8px;
      align-self: flex-start;
    }

    .desc-text {
      font-size: 13px;
      color: #556980;
      line-height: 1.5;
    }

    .action-buttons {
      display: flex;
      flex-direction: column;
      gap: 10px;
      margin-top: 5px;
    }

    .btn-action {
      width: 100%;
      padding: 14px;
      font-size: 14px;
      font-weight: bold;
      border-radius: 12px;
      cursor: pointer;
      text-align: center;
    }

    .btn-primary {
      background: #2a85ff;
      color: white;
      border: none;
    }

    .btn-secondary {
      background: white;
      color: #2a85ff;
      border: 1px solid #2a85ff;
    }

    @media (max-width: 600px) {
      .main-content {
        flex-direction: column;
      }
    }
  </style>
</head>

<body>

  <div class="detail-container">
    <div class="detail-header">
      <!-- PERBAIKAN: Sekarang rute tombol kembali mengarah tepat ke halaman Dashboard Web utama (/web-baru) -->
      <a href="/web-baru" class="btn-back">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard
      </a>
    </div>

    <div class="main-content">
      <div class="image-gallery">
        <div class="main-img-wrapper">
          <img id="main-product-image" src="" alt="Produk">
        </div>
      </div>

      <div class="info-section">
        <div>
          <h1 class="product-title" id="product-title">Nama Produk</h1>
          <div class="product-price" id="product-price" style="margin-top: 5px;">Rp 0</div>
        </div>

        <div>
          <h4 style="font-size:12px; color:#7d8c9e; margin-bottom:8px;">PILIH UKURAN</h4>
          <div class="size-options">
            <button class="size-btn" onclick="pilihUkuran(this)">S</button>
            <button class="size-btn active" onclick="pilihUkuran(this)">M</button>
            <button class="size-btn" onclick="pilihUkuran(this)">L</button>
          </div>
        </div>

        <div>
          <h4 style="font-size:12px; color:#7d8c9e; margin-bottom:8px;">KONDISI</h4>
          <div class="badge-condition" id="product-condition">Very Good</div>
        </div>

        <div>
          <h4 style="font-size:12px; color:#7d8c9e; margin-bottom:8px;">DESKRIPSI</h4>
          <p class="desc-text" id="product-desc">Detail deskripsi item thrift...</p>
        </div>
      </div>
    </div>

    <div class="action-buttons">
      <button class="btn-action btn-primary" onclick="aksiKeranjang()">+ Tambah ke Keranjang</button>
      <button class="btn-action btn-secondary" onclick="aksiBeli()">Beli Sekarang</button>
    </div>
  </div>

  <script>
    const dataKatalog = {
      "1": {
        title: "Vintage Denim Jacket Original",
        price: "Rp 125.000",
        condition: "Excellent Condition",
        desc: "Jaket denim vintage tebal berkualitas tinggi. Warna pekat asli, jahitan luar-dalam super rapi tanpa minus cacat.",
        images: ["https://images.unsplash.com/photo-1576566588028-4147f3842f27?w=500"]
      },
      "2": {
        title: "Crewneck Michigan State Vintage Green",
        price: "Rp 85.000",
        condition: "Very Good",
        desc: "Crewneck katun lembut warna hijau botol vintage. Sablon karet tebal dada awet, karet pinggang kencang.",
        images: ["https://images.unsplash.com/photo-1620799140408-edc6dcb6d633?w=500"]
      },
      "3": {
        title: "Kemeja Flanel Casual Motif Kotak",
        price: "Rp 75.000",
        condition: "Like New",
        desc: "Kemeja bahan flanel adem premium. Kondisi mulus 9.8/10 jarang dipakai, warna cerah seperti baru.",
        images: ["https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=500"]
      }
    };

    window.onload = function() {
      const urlParams = new URLSearchParams(window.location.search);
      const productId = urlParams.get('id') || "1";
      const produk = dataKatalog[productId];

      if (produk) {
        document.getElementById('product-title').innerText = produk.title;
        document.getElementById('product-price').innerText = produk.price;
        document.getElementById('product-condition').innerText = produk.condition;
        document.getElementById('product-desc').innerText = produk.desc;
        document.getElementById('main-product-image').src = produk.images[0];
      }
    };

    function pilihUkuran(element) {
      document.querySelectorAll('.size-btn').forEach(btn => btn.classList.remove('active'));
      element.classList.add('active');
    }

    function aksiKeranjang() {
      const urlParams = new URLSearchParams(window.location.search);
      const productId = urlParams.get('id') || "1";
      const nama = document.getElementById('product-title').innerText;
      const harga = parseInt(document.getElementById('product-price').innerText.replace(/[^0-9]/g, ''));
      const gambar = document.getElementById('main-product-image').src;
      const btnUkuran = document.querySelector('.size-btn.active');
      const ukuran = btnUkuran ? btnUkuran.innerText : "M";
      const kondisi = document.getElementById('product-condition').innerText;
      const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

      fetch('/add-to-cart', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token
          },
          body: JSON.stringify({
            id: productId,
            nama,
            harga,
            gambar,
            ukuran,
            kondisi
          })
        })
        .then(res => res.json())
        .then(data => {
          if (data.status === 'success') alert("Produk berhasil dimasukkan ke keranjang belanja!");
        }).catch(err => console.error(err));
    }

    function aksiBeli() {
      const nama = document.getElementById('product-title').innerText;
      const harga = parseInt(document.getElementById('product-price').innerText.replace(/[^0-9]/g, ''));
      const gambar = document.getElementById('main-product-image').src;
      const btnUkuran = document.querySelector('.size-btn.active');
      const ukuran = btnUkuran ? btnUkuran.innerText : "M";
      const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

      const produkCheckout = [{
        nama,
        varian: "Size " + ukuran,
        harga,
        gambar
      }];

      fetch('/set-checkout', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token
          },
          body: JSON.stringify({
            items: produkCheckout
          })
        })
        .then(res => {
          if (res.ok) window.location.href = "/checkout";
        })
        .catch(err => console.error(err));
    }
  </script>