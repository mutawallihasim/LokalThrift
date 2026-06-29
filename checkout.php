<?php
session_start();
require 'koneksi.php';
if (!isset($_SESSION['id_pengguna'])) { header('Location: login.php'); exit; }

$checkoutItemsDB = [];
$is_post_cart = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['cart_ids'])) {
    $is_post_cart = true;
    $cart_ids = array_map('intval', $_POST['cart_ids']);
    $id_list = implode(',', $cart_ids);
    $id_pengguna = (int) $_SESSION['id_pengguna'];
    
    $q_checkout = mysqli_query($koneksi, "
        SELECT k.id_keranjang as cart_id, p.id_produk as id, p.id_toko, p.nama as nama, p.gambar, k.total as harga, k.varian 
        FROM keranjang k 
        JOIN produk p ON k.id_produk = p.id_produk 
        WHERE k.id_pengguna = $id_pengguna AND k.id_keranjang IN ($id_list)
    ");
    while($r = mysqli_fetch_assoc($q_checkout)) {
        $checkoutItemsDB[] = [
            'cart_id' => $r['cart_id'],
            'id' => $r['id'],
            'id_toko' => $r['id_toko'],
            'nama' => $r['nama'],
            'varian' => $r['varian'],
            'harga' => (int)$r['harga'],
            'gambar' => $r['gambar']
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout - LokalThrift</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Helvetica Neue', Arial, sans-serif; }
    body { background: #eef5fc; min-height: 100vh; padding: 0 0 40px 0; }

    /* ── TOP BAR ── */
    .top-bar {
      background: white;
      padding: 14px 24px;
      display: flex;
      align-items: center;
      gap: 14px;
      border-bottom: 1px solid #e8f0fb;
      position: sticky; top: 0; z-index: 100;
    }
    .btn-back { color: #333; font-size: 18px; text-decoration: none; display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 16px; }
    .btn-back:hover { color: #2a85ff; }

    /* ── WRAPPER ── */
    .wrapper { max-width: 720px; margin: 28px auto; padding: 0 16px; }

    /* ── STEPPER ── */
    .stepper { display: flex; align-items: flex-start; justify-content: center; margin-bottom: 28px; position: relative; }
    .step-item { display: flex; flex-direction: column; align-items: center; flex: 1; position: relative; }
    .step-item:not(:last-child)::after {
      content: '';
      position: absolute;
      top: 18px; left: 60%; right: -40%;
      height: 2px; background: #d4e3f3; z-index: 0;
      transition: background 0.3s;
    }
    .step-item.done:not(:last-child)::after,
    .step-item.active:not(:last-child)::after { background: #2a85ff; }

    .step-circle {
      width: 36px; height: 36px; border-radius: 50%;
      background: #e8f0fb; color: #9ab; font-size: 14px;
      display: flex; align-items: center; justify-content: center;
      font-weight: 700; z-index: 1; transition: 0.3s;
      border: 2px solid #d4e3f3;
    }
    .step-item.active .step-circle { background: #2a85ff; color: white; border-color: #2a85ff; }
    .step-item.done .step-circle { background: #2a85ff; color: white; border-color: #2a85ff; }
    .step-label { font-size: 11px; font-weight: 600; color: #9ab; margin-top: 6px; text-align: center; }
    .step-item.active .step-label { color: #2a85ff; }
    .step-item.done .step-label { color: #2a85ff; }

    /* ── CARD ── */
    .card { background: white; border-radius: 16px; padding: 22px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); margin-bottom: 16px; }
    .card-title { font-size: 15px; font-weight: 800; color: #0d1c2e; margin-bottom: 16px; }

    /* ── ALAMAT ── */
    .alamat-box {
      border: 1.5px solid #d4e3f3; border-radius: 12px;
      padding: 14px 16px; margin-bottom: 12px; cursor: pointer;
      display: flex; gap: 12px; align-items: flex-start; transition: border-color 0.2s;
    }
    .alamat-box.selected { border-color: #2a85ff; background: #f5f9ff; }
    .alamat-box .check-icon { color: #2a85ff; font-size: 18px; margin-top: 2px; flex-shrink: 0; }
    .alamat-label { font-size: 14px; font-weight: 700; color: #0d1c2e; }
    .alamat-detail { font-size: 12px; color: #7d8c9e; margin-top: 4px; line-height: 1.5; }
    .badge-utama { display: inline-block; margin-top: 8px; padding: 3px 10px; background: #eef5fc; color: #2a85ff; font-size: 11px; font-weight: 700; border-radius: 20px; }
    .alamat-ubah { margin-left: auto; font-size: 12px; font-weight: 700; color: #2a85ff; flex-shrink: 0; cursor: pointer; padding: 2px 6px; border-radius: 6px; }
    .alamat-ubah:hover { background: #eef5fc; }
    .alamat-hapus { font-size: 12px; color: #e53935; cursor: pointer; padding: 2px 6px; border-radius: 6px; }
    .alamat-hapus:hover { background: #fff0f0; }
    .btn-tambah-alamat { display: flex; align-items: center; gap: 8px; color: #2a85ff; font-size: 13px; font-weight: 700; cursor: pointer; padding: 10px 0 4px 0; border-top: 1px dashed #d4e3f3; margin-top: 4px; }
    .btn-tambah-alamat:hover { opacity: 0.8; }

    /* ── MODAL ── */
    .modal-overlay {
      display: none; position: fixed; inset: 0;
      background: rgba(13,28,46,0.45); z-index: 999;
      align-items: center; justify-content: center; padding: 16px;
    }
    .modal-overlay.open { display: flex; }
    .modal {
      background: white; border-radius: 20px; padding: 24px;
      width: 100%; max-width: 460px;
      box-shadow: 0 16px 48px rgba(0,0,0,0.18);
      animation: slideUp 0.22s ease;
    }
    @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    .modal-title { font-size: 16px; font-weight: 800; color: #0d1c2e; margin-bottom: 18px; display: flex; justify-content: space-between; align-items: center; }
    .modal-close { background: none; border: none; font-size: 20px; color: #aaa; cursor: pointer; line-height: 1; }
    .modal-close:hover { color: #333; }
    .form-group { margin-bottom: 14px; }
    .form-label { font-size: 12px; font-weight: 700; color: #556980; margin-bottom: 5px; display: block; }
    .form-input {
      width: 100%; padding: 11px 14px; border: 1.5px solid #d4e3f3;
      border-radius: 10px; font-size: 13px; color: #0d1c2e;
      outline: none; transition: border-color 0.2s;
    }
    .form-input:focus { border-color: #2a85ff; }
    .form-row { display: flex; gap: 12px; }
    .form-row .form-group { flex: 1; }
    .form-check { display: flex; align-items: center; gap: 8px; margin-top: 4px; cursor: pointer; }
    .form-check input { accent-color: #2a85ff; width: 16px; height: 16px; }
    .form-check label { font-size: 13px; color: #556980; cursor: pointer; }
    .btn-simpan {
      width: 100%; padding: 13px; background: #2a85ff; color: white;
      border: none; border-radius: 12px; font-size: 14px; font-weight: 700;
      cursor: pointer; margin-top: 6px;
    }
    .btn-simpan:hover { opacity: 0.9; }

    /* ── RINGKASAN ITEM ── */
    .item-row { display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid #f0f6fc; }
    .item-row:last-child { border-bottom: none; }
    .item-row img { width: 60px; height: 60px; object-fit: cover; border-radius: 10px; flex-shrink: 0; }
    .item-row .info { flex: 1; }
    .item-row .info .nama { font-size: 13px; font-weight: 700; color: #0d1c2e; }
    .item-row .info .varian { font-size: 12px; color: #8fa3b8; margin-top: 2px; }
    .item-row .harga { font-size: 13px; font-weight: 700; color: #0d1c2e; white-space: nowrap; }
    .summary-rows { margin-top: 14px; border-top: 1px solid #f0f6fc; padding-top: 14px; }
    .summary-row { display: flex; justify-content: space-between; font-size: 13px; color: #556980; margin-bottom: 8px; }
    .summary-row.total { font-size: 15px; font-weight: 800; color: #0d1c2e; border-top: 1px solid #f0f6fc; padding-top: 12px; margin-top: 4px; }

    /* ── METODE PENGIRIMAN & PEMBAYARAN ── */
    .option-row {
      display: flex; align-items: center; gap: 12px;
      padding: 13px 16px; border: 1.5px solid #d4e3f3;
      border-radius: 12px; margin-bottom: 10px; cursor: pointer;
      transition: border-color 0.2s, background 0.2s;
    }
    .option-row:last-child { margin-bottom: 0; }
    .option-row.selected { border-color: #2a85ff; background: #f5f9ff; }
    .option-row input[type="radio"] { display: none; }
    .radio-dot {
      width: 20px; height: 20px; border-radius: 50%;
      border: 2px solid #c8dff5; flex-shrink: 0;
      display: flex; align-items: center; justify-content: center;
      transition: 0.2s;
    }
    .option-row.selected .radio-dot { border-color: #2a85ff; background: #2a85ff; }
    .option-row.selected .radio-dot::after {
      content: ''; width: 7px; height: 7px;
      background: white; border-radius: 50%;
    }
    .option-label { flex: 1; font-size: 13px; font-weight: 600; color: #0d1c2e; }
    .option-sub { font-size: 11px; color: #8fa3b8; margin-top: 2px; }
    .option-price { font-size: 13px; font-weight: 700; color: #2a85ff; white-space: nowrap; }
    .option-icon { font-size: 20px; color: #2a85ff; width: 26px; text-align: center; }

    /* ── PEMBAYARAN SUCCESS ── */
    .sukses-wrap {
      text-align: center;
      padding: 36px 20px 28px;
    }

    /* Lingkaran glow berlapis */
    .sukses-glow {
      position: relative;
      width: 160px; height: 160px;
      margin: 0 auto 28px;
    }
    .sukses-glow .ring {
      position: absolute; border-radius: 50%;
      top: 50%; left: 50%; transform: translate(-50%,-50%);
    }
    .sukses-glow .ring-1 { width: 160px; height: 160px; background: rgba(42,133,255,0.07); }
    .sukses-glow .ring-2 { width: 126px; height: 126px; background: rgba(42,133,255,0.11); }
    .sukses-glow .ring-3 { width: 96px;  height: 96px;  background: rgba(42,133,255,0.16); }
    .sukses-glow .check-circle {
      position: absolute;
      width: 72px; height: 72px;
      background: #2a85ff;
      border-radius: 50%;
      top: 50%; left: 50%; transform: translate(-50%,-50%);
      display: flex; align-items: center; justify-content: center;
      font-size: 30px; color: white;
      box-shadow: 0 8px 24px rgba(42,133,255,0.35);
    }
    /* Titik-titik dekorasi */
    .dot {
      position: absolute; border-radius: 50%;
      background: #c8dff5;
    }

    .sukses-wrap h2 {
      font-size: 20px; font-weight: 800; color: #0d1c2e;
      margin-bottom: 8px;
    }
    .sukses-wrap .sub {
      font-size: 13px; color: #8fa3b8; margin-bottom: 24px;
    }

    /* Kotak info pesanan */
    .sukses-info {
      background: #f8fbff;
      border: 1px solid #e0edf8;
      border-radius: 14px;
      padding: 16px 20px;
      margin-bottom: 22px;
      text-align: left;
    }
    .sukses-info-row {
      display: flex; justify-content: space-between;
      align-items: center; padding: 6px 0;
    }
    .sukses-info-row:not(:last-child) {
      border-bottom: 1px solid #eef4fc;
    }
    .sukses-info-row .key {
      font-size: 13px; color: #7d8c9e;
    }
    .sukses-info-row .val {
      font-size: 13px; font-weight: 700; color: #0d1c2e;
    }

    .btn-lihat-pesanan {
      width: 100%; padding: 15px;
      background: #2a85ff; color: white;
      border: none; border-radius: 14px;
      font-size: 15px; font-weight: 700;
      cursor: pointer; margin-bottom: 12px;
      transition: opacity 0.2s;
    }
    .btn-lihat-pesanan:hover { opacity: 0.9; }

    .btn-ke-beranda {
      display: block; text-align: center;
      font-size: 14px; font-weight: 700;
      color: #2a85ff; text-decoration: none;
      padding: 4px;
    }
    .btn-ke-beranda:hover { opacity: 0.8; }

    /* ── PAYMENT GRID ── */
    .pay-section-label {
      font-size: 12px; font-weight: 700; color: #8fa3b8;
      text-transform: uppercase; letter-spacing: 0.5px;
      margin-bottom: 14px;
    }
    .pay-grid {
      display: flex; flex-wrap: wrap; gap: 10px;
      margin-bottom: 6px;
    }
    .pay-item {
      display: flex; align-items: center; gap: 8px;
      padding: 10px 16px; border: 1.5px solid #e8f0fb;
      border-radius: 12px; cursor: pointer; background: white;
      transition: border-color 0.2s, box-shadow 0.2s;
      min-width: 90px;
    }
    .pay-item:hover { border-color: #a8c8f5; }
    .pay-item.selected { border-color: #2a85ff; box-shadow: 0 0 0 3px rgba(42,133,255,0.12); }
    .pay-item img { height: 22px; object-fit: contain; }
    .pay-item span { font-size: 13px; font-weight: 700; color: #0d1c2e; white-space: nowrap; }

    /* BTN BAYAR */
    .btn-bayar {
      width: 100%; padding: 16px; background: #2a85ff; color: white;
      border: none; border-radius: 14px; cursor: pointer;
      transition: opacity 0.2s, transform 0.1s;
      text-align: center;
    }
    .btn-bayar:hover { opacity: 0.9; }
    .btn-bayar:active { transform: scale(0.98); }
    .btn-bayar .bayar-label { font-size: 15px; font-weight: 800; display: block; }
    .btn-bayar .bayar-total { font-size: 13px; font-weight: 600; opacity: 0.85; display: block; margin-top: 2px; }
    .two-col { display: flex; gap: 16px; flex-direction: column; }
    @media (min-width: 640px) { .two-col { flex-direction: row; } .two-col > .card { flex: 1; margin-bottom: 0; } }

    /* ── BOTTOM BUTTON ── */
    .bottom-btn-wrap { margin-top: 4px; }
    .btn-lanjut {
      width: 100%; padding: 15px; background: #2a85ff; color: white;
      border: none; border-radius: 14px; font-size: 15px; font-weight: 700;
      cursor: pointer; transition: opacity 0.2s, transform 0.1s;
    }
    .btn-lanjut:hover { opacity: 0.9; }
    .btn-lanjut:active { transform: scale(0.98); }
  </style>
</head>
<body>

<!-- TOP BAR -->
<div class="top-bar">
  <a href="keranjang.php" class="btn-back" id="btn-back">
    <i class="fa-solid fa-arrow-left"></i> Checkout
  </a>
</div>

<div class="wrapper">

  <!-- STEPPER -->
  <div class="stepper">
    <div class="step-item active" id="step-1">
      <div class="step-circle"><i class="fa-solid fa-location-dot"></i></div>
      <div class="step-label">Alamat</div>
    </div>
    <div class="step-item" id="step-2">
      <div class="step-circle"><i class="fa-solid fa-list-check"></i></div>
      <div class="step-label">Ringkasan</div>
    </div>
    <div class="step-item" id="step-3">
      <div class="step-circle"><i class="fa-solid fa-credit-card"></i></div>
      <div class="step-label">Pembayaran</div>
    </div>
  </div>

  <!-- ══ TAHAP 1: ALAMAT ══ -->
  <div id="page-1">
    <div class="two-col">
      <div class="card">
        <div class="card-title">Alamat Pengiriman</div>
        <div id="alamat-list"><!-- diisi JS --></div>
        <div class="btn-tambah-alamat" onclick="bukaModal()">
          <i class="fa-solid fa-plus"></i> Tambah Alamat Baru
        </div>
      </div>

      <div class="card">
        <div class="card-title">Metode Pengiriman</div>

        <div class="option-row selected" onclick="pilihOpsi(this, 'kirim')">
          <div class="radio-dot"></div>
          <div style="flex:1">
            <div class="option-label">JNE Reguler (2-3 hari)</div>
          </div>
          <div class="option-price">Rp 15.000</div>
        </div>

        <div class="option-row" onclick="pilihOpsi(this, 'kirim')">
          <div class="radio-dot"></div>
          <div style="flex:1">
            <div class="option-label">JNE Express (1-2 hari)</div>
          </div>
          <div class="option-price">Rp 25.000</div>
        </div>

        <div class="option-row" onclick="pilihOpsi(this, 'kirim')">
          <div class="radio-dot"></div>
          <div style="flex:1">
            <div class="option-label">SiCepat Reguler (2-3 hari)</div>
          </div>
          <div class="option-price">Rp 13.000</div>
        </div>
      </div>
    </div>

    <div class="bottom-btn-wrap">
      <button class="btn-lanjut" onclick="goStep(2)">Lanjutkan</button>
    </div>
  </div>

  <!-- ══ TAHAP 2: RINGKASAN ══ -->
  <div id="page-2" style="display:none">
    <div class="card">
      <div class="card-title">Ringkasan Pesanan</div>
      <div id="summary-items"><!-- diisi JS --></div>
      <div class="summary-rows">
        <div class="summary-row"><span>Subtotal Produk</span><span id="s-subtotal">Rp 0</span></div>
        <div class="summary-row"><span>Ongkos Kirim</span><span id="s-ongkir">Rp 0</span></div>
        <div class="summary-row total"><span>Total Pembayaran</span><span id="s-total">Rp 0</span></div>
      </div>
    </div>

    <div class="card">
      <div class="card-title">Dikirim ke</div>
      <div style="font-size:14px;font-weight:700;color:#0d1c2e" id="s-alamat-nama">Rumah</div>
      <div style="font-size:13px;color:#7d8c9e;margin-top:4px;line-height:1.6" id="s-alamat-detail">
        Jl. Mawar No.12, Kec. Coblong, Kota Bandung, Jawa Barat 40132
      </div>
      <div style="font-size:13px;color:#556980;margin-top:8px;">
        <i class="fa-solid fa-truck" style="color:#2a85ff;margin-right:6px"></i>
        <span id="s-kurir">JNE Reguler (2-3 hari)</span>
      </div>
    </div>

    <div style="display:flex;gap:10px" class="bottom-btn-wrap">
      <button class="btn-lanjut" style="background:white;color:#2a85ff;border:1.5px solid #2a85ff;flex:1" onclick="goStep(1)">Kembali</button>
      <button class="btn-lanjut" style="flex:2" onclick="goStep(3)">Lanjutkan ke Pembayaran</button>
    </div>
  </div>

  <!-- ══ TAHAP 3: PEMBAYARAN ══ -->
  <div id="page-3" style="display:none">

    <!-- Transfer Bank -->
    <div class="card">
      <div class="pay-section-label">Transfer Bank</div>
      <div class="pay-grid" id="grid-bank">

        <div class="pay-item selected" onclick="pilihBayar(this,'BCA')">
          <img src="https://upload.wikimedia.org/wikipedia/commons/5/5c/Bank_Central_Asia.svg" alt="BCA">
          <span>BCA</span>
        </div>

        <div class="pay-item" onclick="pilihBayar(this,'Mandiri')">
          <img src="https://upload.wikimedia.org/wikipedia/commons/a/ad/Bank_Mandiri_logo_2008.svg" alt="Mandiri">
          <span>Mandiri</span>
        </div>

        <div class="pay-item" onclick="pilihBayar(this,'BRI')">
          <img src="https://upload.wikimedia.org/wikipedia/commons/6/68/BANK_BRI_logo.svg" alt="BRI">
          <span>BRI</span>
        </div>

        <div class="pay-item" onclick="pilihBayar(this,'BNI')">
          <img src="https://upload.wikimedia.org/wikipedia/id/5/55/BNI_logo.svg" alt="BNI">
          <span>BNI</span>
        </div>

        <div class="pay-item" onclick="pilihBayar(this,'BSI')">
          <img src="https://upload.wikimedia.org/wikipedia/commons/2/2d/Bank_Syariah_Indonesia.svg" alt="BSI">
          <span>BSI</span>
        </div>

      </div>
    </div>

    <!-- E-Wallet -->
    <div class="card">
      <div class="pay-section-label">E-Wallet</div>
      <div class="pay-grid" id="grid-ewallet">

        <div class="pay-item" onclick="pilihBayar(this,'OVO')">
          <img src="https://upload.wikimedia.org/wikipedia/commons/e/eb/Logo_ovo_purple.svg" alt="OVO">
          <span>OVO</span>
        </div>

        <div class="pay-item" onclick="pilihBayar(this,'DANA')">
          <img src="https://upload.wikimedia.org/wikipedia/commons/7/72/Logo_dana_blue.svg" alt="DANA">
          <span>DANA</span>
        </div>

        <div class="pay-item" onclick="pilihBayar(this,'ShopeePay')">
          <img src="https://upload.wikimedia.org/wikipedia/commons/f/fe/Shopee.svg" alt="ShopeePay">
          <span>ShopeePay</span>
        </div>

        <div class="pay-item" onclick="pilihBayar(this,'GoPay')">
          <img src="https://upload.wikimedia.org/wikipedia/commons/8/86/Gopay_logo.svg" alt="GoPay">
          <span>GoPay</span>
        </div>

      </div>
    </div>

    <!-- Bayar di Tempat (COD) -->
    <div class="card">
      <div class="pay-section-label">Lainnya</div>
      <div class="pay-grid" id="grid-cod">
        <div class="pay-item" onclick="pilihBayar(this,'COD (Bayar di Tempat)')">
          <i class="fa-solid fa-hand-holding-dollar" style="font-size:26px; color:#2a85ff; margin-bottom:6px;"></i>
          <span>COD</span>
        </div>
      </div>
    </div>

    <!-- Tombol Bayar -->
    <div class="bottom-btn-wrap">
      <button class="btn-bayar" onclick="buatPesanan()">
        <span class="bayar-label">Bayar Sekarang</span>
        <span class="bayar-total" id="p-total">Rp 0</span>
      </button>
    </div>
  </div>

  <!-- ══ SUKSES ══ -->
  <!-- ══ SUKSES ══ -->
  <div id="page-sukses" style="display:none">
    <div class="card">
      <div class="sukses-wrap">

        <!-- Lingkaran glow + ikon centang -->
        <div class="sukses-glow">
          <div class="ring ring-1"></div>
          <div class="ring ring-2"></div>
          <div class="ring ring-3"></div>
          <!-- Titik dekorasi -->
          <div class="dot" style="width:6px;height:6px;top:10px;left:28px;opacity:0.5"></div>
          <div class="dot" style="width:5px;height:5px;top:14px;right:30px;opacity:0.4"></div>
          <div class="dot" style="width:4px;height:4px;bottom:18px;left:20px;opacity:0.3"></div>
          <div class="dot" style="width:5px;height:5px;bottom:14px;right:24px;opacity:0.4"></div>
          <div class="dot" style="width:4px;height:4px;top:42px;left:8px;opacity:0.3"></div>
          <div class="dot" style="width:4px;height:4px;top:38px;right:8px;opacity:0.3"></div>
          <div class="check-circle">
            <i class="fa-solid fa-check"></i>
          </div>
        </div>

        <h2>Pembayaran Berhasil!</h2>
        <p class="sub">Terima kasih, pesananmu sedang kami proses.</p>

        <!-- Info pesanan -->
        <div class="sukses-info">
          <div class="sukses-info-row">
            <span class="key">No. Pesanan</span>
            <span class="val" id="invoice-no">LT-00000000</span>
          </div>
          <div class="sukses-info-row">
            <span class="key">Total Pembayaran</span>
            <span class="val" id="sukses-total">Rp 0</span>
          </div>
          <div class="sukses-info-row">
            <span class="key">Metode Pembayaran</span>
            <span class="val" id="sukses-metode">-</span>
          </div>
        </div>

        <button class="btn-lihat-pesanan" onclick="window.location.href='pesanan.php'">
          Lihat Pesanan Saya
        </button>
        <a href="home.php" class="btn-ke-beranda">Kembali ke Beranda</a>

      </div>
    </div>
  </div>

</div><!-- /wrapper -->

<!-- ══ MODAL TAMBAH / UBAH ALAMAT ══ -->
<div class="modal-overlay" id="modal-alamat" onclick="tutupModalIfOverlay(event)">
  <div class="modal">
    <div class="modal-title">
      <span id="modal-judul">Tambah Alamat Baru</span>
      <button class="modal-close" onclick="tutupModal()"><i class="fa-solid fa-xmark"></i></button>
    </div>

    <input type="hidden" id="edit-index" value="-1">

    <div class="form-group">
      <label class="form-label">Label Alamat (contoh: Rumah, Kos, Kantor)</label>
      <input type="text" class="form-input" id="f-label" placeholder="Rumah">
    </div>
    <div class="form-group">
      <label class="form-label">Nama Penerima</label>
      <input type="text" class="form-input" id="f-nama" placeholder="Nama lengkap">
    </div>
    <div class="form-group">
      <label class="form-label">No. Telepon</label>
      <input type="text" class="form-input" id="f-telp" placeholder="08xx-xxxx-xxxx">
    </div>
    <div class="form-group">
      <label class="form-label">Alamat Lengkap</label>
      <input type="text" class="form-input" id="f-jalan" placeholder="Nama jalan, nomor, RT/RW">
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Kecamatan</label>
        <input type="text" class="form-input" id="f-kecamatan" placeholder="Kecamatan">
      </div>
      <div class="form-group">
        <label class="form-label">Kota / Kabupaten</label>
        <input type="text" class="form-input" id="f-kota" placeholder="Kota">
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Provinsi</label>
        <input type="text" class="form-input" id="f-provinsi" placeholder="Provinsi">
      </div>
      <div class="form-group">
        <label class="form-label">Kode Pos</label>
        <input type="text" class="form-input" id="f-kodepos" placeholder="40000">
      </div>
    </div>
    <div class="form-check">
      <input type="checkbox" id="f-utama">
      <label for="f-utama">Jadikan alamat utama</label>
    </div>

    <button class="btn-simpan" onclick="simpanAlamat()">Simpan Alamat</button>
  </div>
</div>

<script>
  // ── STATE ──
  let currentStep = 1;
  const ALAMAT_KEY = 'alamat_list_<?= $_SESSION['id_pengguna'] ?? 'guest' ?>';

  // ── DATA ALAMAT (dari localStorage atau default) ──
  function getAlamatList() {
    const saved = localStorage.getItem(ALAMAT_KEY);
    if (saved) return JSON.parse(saved);
    return [];
  }

  function simpanAlamatList(list) {
    localStorage.setItem(ALAMAT_KEY, JSON.stringify(list));
  }

  let selectedAlamat  = 0;
  let selectedKurir   = { label: 'JNE Reguler (2-3 hari)', harga: 15000 };
  let selectedBayar   = 'BCA';

  // ── RENDER DAFTAR ALAMAT ──
  function renderAlamat() {
    const list = getAlamatList();
    const container = document.getElementById('alamat-list');
    if (!container) return;

    if (list.length === 0) {
      container.innerHTML = `<div style="text-align:center; padding: 20px; color: #8fa3b8; font-size: 14px;">Belum ada alamat pengiriman. Silakan tambahkan alamat baru.</div>`;
      return;
    }

    container.innerHTML = list.map((a, i) => `
      <div class="alamat-box ${i === selectedAlamat ? 'selected' : ''}" onclick="pilihAlamat(${i})">
        <i class="${i === selectedAlamat
          ? 'fa-solid fa-circle-check check-icon'
          : 'fa-regular fa-circle check-icon'}"
          style="color:${i === selectedAlamat ? '#2a85ff' : '#c8dff5'}"></i>
        <div style="flex:1;min-width:0">
          <div style="display:flex;justify-content:space-between;align-items:center;gap:8px">
            <div class="alamat-label">${a.label}</div>
            <div style="display:flex;gap:6px;flex-shrink:0">
              <span class="alamat-ubah" onclick="event.stopPropagation();bukaModal(${i})">Ubah</span>
              ${list.length > 1 ? `<span class="alamat-hapus" onclick="event.stopPropagation();hapusAlamat(${i})"><i class="fa-regular fa-trash-can"></i></span>` : ''}
            </div>
          </div>
          <div class="alamat-detail">${a.nama} · ${a.telp}</div>
          <div class="alamat-detail">${a.jalan}, ${a.kecamatan}, ${a.kota}, ${a.provinsi} ${a.kodepos}</div>
          ${a.utama ? '<span class="badge-utama">Utama</span>' : ''}
        </div>
      </div>
    `).join('');
  }

  function pilihAlamat(i) {
    selectedAlamat = i;
    renderAlamat();
  }

  function hapusAlamat(i) {
    const list = getAlamatList();
    if (list.length <= 1) return;
    list.splice(i, 1);
    if (selectedAlamat >= list.length) selectedAlamat = 0;
    simpanAlamatList(list);
    renderAlamat();
  }

  // ── MODAL ──
  function bukaModal(index = -1) {
    document.getElementById('edit-index').value = index;
    const judul = document.getElementById('modal-judul');

    if (index >= 0) {
      const a = getAlamatList()[index];
      judul.textContent = 'Ubah Alamat';
      document.getElementById('f-label').value     = a.label;
      document.getElementById('f-nama').value      = a.nama;
      document.getElementById('f-telp').value      = a.telp;
      document.getElementById('f-jalan').value     = a.jalan;
      document.getElementById('f-kecamatan').value = a.kecamatan;
      document.getElementById('f-kota').value      = a.kota;
      document.getElementById('f-provinsi').value  = a.provinsi;
      document.getElementById('f-kodepos').value   = a.kodepos;
      document.getElementById('f-utama').checked   = a.utama;
    } else {
      judul.textContent = 'Tambah Alamat Baru';
      ['f-label','f-nama','f-telp','f-jalan','f-kecamatan','f-kota','f-provinsi','f-kodepos']
        .forEach(id => document.getElementById(id).value = '');
      document.getElementById('f-utama').checked = false;
    }
    document.getElementById('modal-alamat').classList.add('open');
  }

  function tutupModal() {
    document.getElementById('modal-alamat').classList.remove('open');
  }

  function tutupModalIfOverlay(e) {
    if (e.target === document.getElementById('modal-alamat')) tutupModal();
  }

  function simpanAlamat() {
    const label     = document.getElementById('f-label').value.trim();
    const nama      = document.getElementById('f-nama').value.trim();
    const telp      = document.getElementById('f-telp').value.trim();
    const jalan     = document.getElementById('f-jalan').value.trim();
    const kecamatan = document.getElementById('f-kecamatan').value.trim();
    const kota      = document.getElementById('f-kota').value.trim();
    const provinsi  = document.getElementById('f-provinsi').value.trim();
    const kodepos   = document.getElementById('f-kodepos').value.trim();
    const utama     = document.getElementById('f-utama').checked;

    if (!label || !nama || !jalan || !kota) {
      alert('Label, nama, alamat jalan, dan kota wajib diisi.');
      return;
    }

    const list = getAlamatList();
    const data = { label, nama, telp, jalan, kecamatan, kota, provinsi, kodepos, utama };

    if (utama) list.forEach(a => a.utama = false);

    const idx = parseInt(document.getElementById('edit-index').value);
    if (idx >= 0) {
      list[idx] = data;
    } else {
      list.push(data);
      selectedAlamat = list.length - 1;
    }

    simpanAlamatList(list);
    tutupModal();
    renderAlamat();
  }

  // ── AMBIL ITEM CHECKOUT ──
  let checkoutItems = [];
  <?php if ($is_post_cart): ?>
    checkoutItems = <?= json_encode($checkoutItemsDB) ?>;
    sessionStorage.setItem('checkout_items', JSON.stringify(checkoutItems));
  <?php else: ?>
    const checkoutItemsStr = sessionStorage.getItem('checkout_items');
    checkoutItems = JSON.parse(checkoutItemsStr) || [];
  <?php endif; ?>

  if (checkoutItems.length === 0) {
    window.location.href = 'keranjang.php';
  }

  function formatRp(n) { return 'Rp ' + n.toLocaleString('id-ID'); }

  // ── NAVIGASI STEP ──
  function goStep(n) {
    if (n === 1) renderAlamat();
    if (n === 2) {
      if (getAlamatList().length === 0) {
        alert("Silakan tambahkan alamat pengiriman terlebih dahulu sebelum melanjutkan.");
        return;
      }
      buildRingkasan();
    }
    if (n === 3) buildPembayaran();

    // Sembunyikan semua page
    [1, 2, 3].forEach(i => {
      document.getElementById('page-' + i).style.display = 'none';
    });

    document.getElementById('page-' + n).style.display = 'block';
    currentStep = n;
    updateStepper(n);
    window.scrollTo({ top: 0, behavior: 'smooth' });

    // Tombol back
    const back = document.getElementById('btn-back');
    back.href = n === 1 ? 'keranjang.php' : 'javascript:void(0)';
    back.onclick = n === 1 ? null : () => goStep(n - 1);
  }

  function updateStepper(active) {
    for (let i = 1; i <= 3; i++) {
      const el = document.getElementById('step-' + i);
      el.classList.remove('active', 'done');
      if (i < active) el.classList.add('done');
      else if (i === active) el.classList.add('active');
    }
  }

  // ── PILIH ALAMAT ──
  function pilihAlamat(el) {
    document.querySelectorAll('.alamat-box').forEach((b, i) => {
      b.classList.remove('selected');
      b.querySelector('.check-icon').className = 'fa-regular fa-circle check-icon';
      b.querySelector('.check-icon').style.color = '#c8dff5';
      if (b === el) {
        b.classList.add('selected');
        b.querySelector('.check-icon').className = 'fa-solid fa-circle-check check-icon';
        b.querySelector('.check-icon').style.color = '#2a85ff';
        selectedAlamat = i;
      }
    });
  }

  // ── PILIH METODE BAYAR (grid logo) ──
  function pilihBayar(el, nama) {
    // Hapus selected dari semua grid bank, ewallet, dan cod
    document.querySelectorAll('#grid-bank .pay-item, #grid-ewallet .pay-item, #grid-cod .pay-item')
      .forEach(i => i.classList.remove('selected'));
    el.classList.add('selected');
    selectedBayar = nama;
  }

  // ── PILIH OPSI GENERIK (kurir) ──
  function pilihOpsi(el, group) {
    el.closest('.card').querySelectorAll('.option-row').forEach(r => r.classList.remove('selected'));
    el.classList.add('selected');

    if (group === 'kirim') {
      const label = el.querySelector('.option-label').innerText;
      const hargaStr = el.querySelector('.option-price')?.innerText || 'Rp 0';
      const harga = parseInt(hargaStr.replace(/[^0-9]/g, ''));
      selectedKurir = { label, harga };
    }
  }

  // ── BUILD RINGKASAN ──
  function buildRingkasan() {
    const subtotal = checkoutItems.reduce((s, i) => s + i.harga, 0);
    const total = subtotal + selectedKurir.harga;

    document.getElementById('summary-items').innerHTML = checkoutItems.map(item => `
      <div class="item-row">
        <img src="${item.gambar}" alt="${item.nama}" onerror="this.src='https://via.placeholder.com/60'">
        <div class="info">
          <div class="nama">${item.nama}</div>
          <div class="varian">${item.varian}</div>
        </div>
        <div class="harga">${formatRp(item.harga)}</div>
      </div>
    `).join('');

    document.getElementById('s-subtotal').textContent = formatRp(subtotal);
    document.getElementById('s-ongkir').textContent   = formatRp(selectedKurir.harga);
    document.getElementById('s-total').textContent    = formatRp(total);
    document.getElementById('s-kurir').textContent    = selectedKurir.label;

    const list = getAlamatList();
    const a = list[selectedAlamat] || list[0];
    document.getElementById('s-alamat-nama').textContent   = a.label + ' · ' + a.nama;
    document.getElementById('s-alamat-detail').textContent =
      a.jalan + ', ' + a.kecamatan + ', ' + a.kota + ', ' + a.provinsi + ' ' + a.kodepos;
  }

  function buildPembayaran() {
    const subtotal = checkoutItems.reduce((s, i) => s + i.harga, 0);
    document.getElementById('p-total').textContent = formatRp(subtotal + selectedKurir.harga);
  }

  // ── BUAT PESANAN ──
  function buatPesanan() {
    // Hitung total
    const subtotal = checkoutItems.reduce((s, i) => s + i.harga, 0);
    const total = subtotal + selectedKurir.harga;

    // Ambil data alamat
    const list = typeof getAlamatList === 'function' ? getAlamatList() : [];
    const alamat = list[selectedAlamat]
      ? `${list[selectedAlamat].jalan}, ${list[selectedAlamat].kecamatan}, ${list[selectedAlamat].kota}`
      : '';

    // Kirim ke server
    fetch('api_simpan_pesanan.php', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        items:        checkoutItems,
        ongkir:       selectedKurir.harga,
        metode_bayar: selectedBayar || 'BCA',
        kurir:        selectedKurir.label,
        alamat:       alamat
      })
    })
    .then(r => {
      if (!r.ok) return r.text().then(t => { throw new Error(t); });
      return r.json();
    })
    .then(data => {
      if (data.status !== 'success') {
        alert('Gagal membuat pesanan: ' + data.message);
        return;
      }

      // Hapus item dari keranjang localStorage
      const checkoutIds = checkoutItems.map(i => i.id).filter(Boolean);
      if (checkoutIds.length) {
        let k = JSON.parse(localStorage.getItem('keranjang')) || [];
        k = k.filter(item => !checkoutIds.includes(item.id));
        localStorage.setItem('keranjang', JSON.stringify(k));
      }
      sessionStorage.removeItem('checkout_items');

      // Tampilkan halaman sukses
      document.getElementById('invoice-no').textContent   = data.invoice;
      document.getElementById('sukses-total').textContent = formatRp(data.total);
      document.getElementById('sukses-metode').textContent = selectedBayar || 'BCA';

      [1, 2, 3].forEach(i => document.getElementById('page-' + i).style.display = 'none');
      document.getElementById('page-sukses').style.display = 'block';
      document.getElementById('btn-back').style.display = 'none';

      for (let i = 1; i <= 3; i++) {
        const el = document.getElementById('step-' + i);
        el.classList.remove('active');
        el.classList.add('done');
      }
      window.scrollTo({ top: 0, behavior: 'smooth' });
    })
    .catch(err => {
      console.error('Error detail:', err.message);
      alert('Error: ' + err.message.substring(0, 200));
    });
  }

  // ── INIT ──
  goStep(1);
  renderAlamat();
</script>
</body>
</html>
 