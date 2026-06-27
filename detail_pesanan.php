<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Detail Pesanan - LokalThrift</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
    body { background: #f4f8fc; color: #0d1c2e; min-height: 100vh; padding-bottom: 50px; }

    .top-nav { display: flex; justify-content: space-between; align-items: center; padding: 18px 5%; background-color: #ffffff; border-bottom: 1px solid #eef2f7; }
    .brand { display: flex; align-items: center; gap: 10px; text-decoration: none; color: #0d1c2e; font-weight: 700; font-size: 20px; }
    .brand i { color: #2a85ff; }

    .container { width: 90%; max-width: 950px; margin: 30px auto; }
    .btn-back { display: inline-flex; align-items: center; gap: 8px; color: #556980; text-decoration: none; font-size: 14px; font-weight: 600; margin-bottom: 20px; }

    /* TIMELINE TRACKER CRITICAL SECTION */
    .tracking-card { background: white; border-radius: 16px; border: 1px solid #eef2f7; padding: 30px; margin-bottom: 25px; text-align: center; }
    .tracking-title { font-size: 20px; font-weight: 700; color: #0d1c2e; display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 8px; }
    .tracking-title i { color: #2a85ff; }
    .tracking-eta { font-size: 13px; color: #7d8c9e; margin-bottom: 40px; }

    .timeline-wrapper { display: flex; justify-content: space-between; position: relative; max-width: 700px; margin: 0 auto; }
    .timeline-line { position: absolute; top: 20px; left: 40px; right: 40px; height: 3px; background: #e2edf7; z-index: 1; }
    .timeline-line-progress { position: absolute; top: 20px; left: 40px; width: 0%; height: 3px; background: #2a85ff; z-index: 2; transition: width 0.4s ease; }

    .timeline-step { position: relative; z-index: 3; display: flex; flex-direction: column; align-items: center; width: 100px; }
    .step-icon { width: 42px; height: 42px; border-radius: 50%; background: white; border: 2px solid #e2edf7; color: #a0aec0; display: flex; justify-content: center; align-items: center; font-size: 16px; margin-bottom: 12px; transition: 0.3s; }
    
    .timeline-step.active .step-icon { background: #2a85ff; border-color: #2a85ff; color: white; box-shadow: 0 4px 10px rgba(42,133,255,0.25); }
    .step-text { font-size: 13px; font-weight: 600; color: #a0aec0; margin-bottom: 4px; }
    .timeline-step.active .step-text { color: #2a85ff; }
    .step-date { font-size: 11px; color: #7d8c9e; }

    /* LAYOUT GRID */
    .main-grid { display: grid; grid-template-columns: 1fr 1.3fr; gap: 25px; }
    @media (max-width: 768px) { .main-grid { grid-template-columns: 1fr; } }

    .info-card { background: white; border-radius: 16px; border: 1px solid #eef2f7; padding: 25px; display: flex; flex-direction: column; gap: 20px; }
    .card-section-title { font-size: 15px; font-weight: 700; color: #0d1c2e; margin-bottom: 5px; }
    .sub-info-block { border-bottom: 1px solid #f4f8fc; padding-bottom: 15px; }
    .sub-info-block:last-child { border-bottom: none; padding-bottom: 0; }
    .sub-info-block h4 { font-size: 14px; font-weight: 700; color: #0d1c2e; margin-bottom: 6px; }
    .sub-info-block p { font-size: 13px; color: #556980; line-height: 1.5; }

    .shipping-box { border: 1px solid #e2edf7; background: #f8fbfe; border-radius: 12px; padding: 15px; display: flex; align-items: center; gap: 12px; margin-bottom: 15px; }
    .shipping-box i { font-size: 18px; color: #2a85ff; }
    .btn-lacak { width: 100%; padding: 12px; background: #eef5fc; color: #2a85ff; border: none; border-radius: 10px; font-size: 14px; font-weight: 700; cursor: pointer; }

    .invoice-header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid #f4f8fc; padding-bottom: 15px; }
    .badge-invoice-status { padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 700; }
    
    /* VARIASI BADGE STATUS WARNA */
    .badge-diproses { background: #fff6e9; color: #ffa800; }
    .badge-dikirim { background: #eef5fc; color: #2a85ff; }
    .badge-selesai { background: #ebf9f1; color: #10b981; }

    .product-list-wrapper { display: flex; flex-direction: column; gap: 15px; margin-top: 5px; }
    .product-row { display: flex; align-items: center; gap: 15px; }
    .product-thumb { width: 60px; height: 60px; object-fit: cover; border-radius: 10px; border: 1px solid #eef2f7; }
    .product-name-meta { flex: 1; }
    .product-name-meta h5 { font-size: 14px; font-weight: 600; color: #0d1c2e; }
    .product-name-meta p { font-size: 12px; color: #7d8c9e; margin-top: 2px; }
    .product-price-qty { text-align: right; font-size: 14px; font-weight: 700; color: #0d1c2e; }
    .product-price-qty span { font-size: 12px; color: #a0aec0; margin-left: 5px; }

    .total-payment-row { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f4f8fc; padding-top: 18px; margin-top: 10px; }
    .btn-beli-lagi { width: 100%; padding: 14px; background: #2a85ff; color: white; border: none; border-radius: 12px; font-size: 14px; font-weight: 700; cursor: pointer; margin-top: 15px; }
  </style>
</head>
<body>

  <div class="top-nav">
    <a href="/web-baru" class="brand">
      <i class="fa-solid fa-cloud-bolt"></i> <span>LokalThrift</span>
    </a>
  </div>

  <div class="container">
    <a href="/pesanan" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Kembali ke Riwayat Pesanan</a>

    <!-- BAGIAN ATAS: STATUS PELACAKAN DINAMIS -->
    <div class="tracking-card">
      <div class="tracking-title">
        <span id="txt-title-status">Pesanan Sedang Diproses</span> <i id="icon-status" class="fa-solid fa-clipboard-list"></i>
      </div>
      <p class="tracking-eta" id="txt-eta">Perkiraan tiba: -</p>

      <div class="timeline-wrapper">
        <div class="timeline-line"></div>
        <div class="timeline-line-progress" id="timeline-progress"></div>

        <!-- Step 1 -->
        <div class="timeline-step" id="step-diproses">
          <div class="step-icon"><i class="fa-solid fa-clipboard-check"></i></div>
          <span class="step-text">Diproses</span>
          <span class="step-date">17 Mei</span>
        </div>

        <!-- Step 2 -->
        <div class="timeline-step" id="step-dikirim">
          <div class="step-icon"><i class="fa-solid fa-truck-ramp-box"></i></div>
          <span class="step-text">Dikirim</span>
          <span class="step-date" id="date-dikirim">-</span>
        </div>

        <!-- Step 3 -->
        <div class="timeline-step" id="step-perjalanan">
          <div class="step-icon"><i class="fa-solid fa-route"></i></div>
          <span class="step-text">Dalam Perjalanan</span>
          <span class="step-date">-</span>
        </div>

        <!-- Step 4 -->
        <div class="timeline-step" id="step-tiba">
          <div class="step-icon"><i class="fa-solid fa-box-open"></i></div>
          <span class="step-text">Tiba</span>
          <span class="step-date">-</span>
        </div>
      </div>
    </div>

    <!-- BAGIAN BAWAH: RINCIAN GRID -->
    <div class="main-grid">
      <div class="info-card">
        <div class="sub-info-block">
          <h3 class="card-section-title">Informasi Pengiriman</h3>
          <div class="shipping-box">
            <i class="fa-solid fa-boxes-packing"></i>
            <div>
              <h5 style="font-size:13px; font-weight:700;">JNE Reguler</h5>
              <p style="font-size:12px; color:#7d8c9e; margin-top:2px;">No. Resi: JNE1234567890</p>
            </div>
          </div>
          <button class="btn-lacak" onclick="alert('Paket terdeteksi di Sortasi Center Bandung.')">Lacak Paket</button>
        </div>

        <div class="sub-info-block">
          <h4>Alamat Pengiriman</h4>
          <p style="font-weight: 700; margin-bottom: 2px; color:#0d1c2e;">Rumah</p>
          <p>Jl. Mawar No.12, Kec. Coblong,<br>Kota Bandung, Jawa Barat 40132</p>
        </div>

        <div class="sub-info-block">
          <h4>Metode Pengiriman</h4>
          <p>JNE Reguler (2-3 hari) <span style="float: right; font-weight:700; color:#0d1c2e;">Rp 15.000</span></p>
        </div>
      </div>

      <!-- KARTU RINCIAN DATA PRODUK SESUAI INVOICE -->
      <div class="info-card">
        <div>
          <div class="invoice-header">
            <div>
              <h3 id="lbl-invoice">LT240S170001</h3>
              <p id="lbl-date" style="font-size: 12px; color: #7d8c9e; margin-top: 2px;">17 Mei 2026 • 10:30</p>
            </div>
            <span class="badge-invoice-status" id="lbl-badge-status">Diproses</span>
          </div>
        </div>

        <!-- Tempat Suntik List Produk Dinamis Via JS -->
        <div class="product-list-wrapper" id="box-product-container"></div>

        <div>
          <div class="total-payment-row">
            <h4 style="font-size: 14px; font-weight: 600; color:#556980;">Total Pembayaran</h4>
            <h3 style="font-size: 18px; font-weight: 800; color:#0d1c2e;" id="lbl-total-price">Rp 210.000</h3>
          </div>
          <button class="btn-beli-lagi" onclick="alert('Produk berhasil disalin kembali ke keranjang belanja!'); window.location.href='/keranjang';">Beli Lagi</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    // DATA INVOICE SINKRON
    const databaseInvoice = {
      "LT240S170001": {
        date: "17 Mei 2026 • 10:30",
        total: "Rp 210.000",
        products: [
          { name: "Crewneck Michigan State", meta: "Hijau, M", price: "Rp 85.000", img: "https://images.unsplash.com/photo-1620799140408-edc6dcb6d633?w=100" },
          { name: "Kemeja Flanel Oversize", meta: "Merah, L", price: "Rp 75.000", img: "https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=100" },
          { name: "Totebag Canvas Thrift", meta: "Putih", price: "Rp 35.000", img: "https://images.unsplash.com/photo-1576566588028-4147f3842f27?w=100" }
        ]
      },
      "LT240S140023": {
        date: "14 Mei 2026 • 14:15",
        total: "Rp 125.000",
        products: [
          { name: "Vintage Denim Jacket Original", meta: "Biru Denim, L", price: "Rp 125.000", img: "https://images.unsplash.com/photo-1576566588028-4147f3842f27?w=100" }
        ]
      },
      "LT240S100098": {
        date: "10 Mei 2026 • 09:15",
        total: "Rp 120.000",
        products: [
          { name: "Crewneck Michigan State", meta: "Hijau, M", price: "Rp 85.000", img: "https://images.unsplash.com/photo-1620799140408-edc6dcb6d633?w=100" },
          { name: "Totebag Canvas Thrift", meta: "Putih", price: "Rp 35.000", img: "https://images.unsplash.com/photo-1576566588028-4147f3842f27?w=100" }
        ]
      }
    };

    window.onload = function() {
        const urlParams = new URLSearchParams(window.location.search);
        const currentStatus = urlParams.get('status') || 'diproses';
        const currentInvoice = urlParams.get('invoice') || 'LT240S170001';

        // 1. Render Detail Invoice & List Produk Terpilih
        const dataInvoice = databaseInvoice[currentInvoice];
        if (dataInvoice) {
            document.getElementById('lbl-invoice').innerText = currentInvoice;
            document.getElementById('lbl-date').innerText = dataInvoice.date;
            document.getElementById('lbl-total-price').innerText = dataInvoice.total;

            let htmlProduk = `<h4 class="card-section-title" style="margin-bottom:5px;">Produk</h4>`;
            dataInvoice.products.forEach(p => {
                htmlProduk += `
                  <div class="product-row">
                    <img class="product-thumb" src="${p.img}" alt="Produk">
                    <div class="product-name-meta"><h5>${p.name}</h5><p>${p.meta}</p></div>
                    <div class="product-price-qty">${p.price}<span>x1</span></div>
                  </div>`;
            });
            document.getElementById('box-product-container').innerHTML = htmlProduk;
        }

        // 2. Kontrol Logika Progress Bar Timeline Tracker (SINKRON DETAIL)
        const progressLine = document.getElementById('timeline-progress');
        const badgeStatus = document.getElementById('lbl-badge-status');
        
        badgeStatus.className = "badge-invoice-status"; // Reset

        if (currentStatus === 'diproses') {
            document.getElementById('txt-title-status').innerText = "Pesanan Sedang Diproses";
            document.getElementById('icon-status').className = "fa-solid fa-clipboard-list";
            document.getElementById('txt-eta').innerText = "Penjual sedang menyiapkan pakaian pilihanmu";
            document.getElementById('step-diproses').classList.add('active');
            progressLine.style.width = "0%";
            badgeStatus.classList.add('badge-diproses');
            badgeStatus.innerText = "Diproses";
        } 
        else if (currentStatus === 'dikirim') {
            document.getElementById('txt-title-status').innerText = "Pesanan Sedang Dikirim";
            document.getElementById('icon-status').className = "fa-solid fa-truck-fast";
            document.getElementById('txt-eta').innerText = "Perkiraan tiba: 19 Mei 2026";
            document.getElementById('date-dikirim').innerText = "18 Mei";
            
            document.getElementById('step-diproses').classList.add('active');
            document.getElementById('step-dikirim').classList.add('active');
            progressLine.style.width = "33%";
            badgeStatus.classList.add('badge-dikirim');
            badgeStatus.innerText = "Dikirim";
        } 
        else if (currentStatus === 'selesai') {
            document.getElementById('txt-title-status').innerText = "Pesanan Telah Selesai";
            document.getElementById('icon-status').className = "fa-solid fa-circle-check";
            document.getElementById('txt-eta').innerText = "Paket berhasil diserahkan kepada pembeli";
            document.getElementById('date-dikirim').innerText = "18 Mei";
            
            document.getElementById('step-diproses').classList.add('active');
            document.getElementById('step-dikirim').classList.add('active');
            document.getElementById('step-perjalanan').classList.add('active');
            document.getElementById('step-tiba').classList.add('active');
            progressLine.style.width = "100%";
            badgeStatus.classList.add('badge-selesai');
            badgeStatus.innerText = "Selesai";
        }
    };
  </script>
</body>
</html>