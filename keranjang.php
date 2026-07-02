<?php
session_start();
require 'koneksi.php';
if (!isset($_SESSION['id_pengguna'])) { header('Location: login.php'); exit; }

$id_pengguna = (int) $_SESSION['id_pengguna'];
$q_keranjang = mysqli_query($koneksi, "
    SELECT k.id_keranjang as cart_id, p.id_produk as id, p.nama as nama, p.gambar, k.total as harga, k.varian 
    FROM keranjang k 
    JOIN produk p ON k.id_produk = p.id_produk 
    WHERE k.id_pengguna = $id_pengguna
");

$keranjang_db = [];
while ($row = mysqli_fetch_assoc($q_keranjang)) {
    $keranjang_db[] = [
        'cart_id' => $row['cart_id'],
        'id' => $row['id'],
        'nama' => $row['nama'],
        'varian' => $row['varian'] ?? '',
        'harga' => (int)$row['harga'],
        'gambar' => $row['gambar'],
        'checked' => true
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Keranjang Belanja - LokalThrift</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', 'Helvetica Neue', Arial, sans-serif; }

    body {
      background: #eef5fc;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    /* BOTTOM NAVBAR */
    .navbar {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      width: 100%;
      background: white;
      display: flex;
      justify-content: space-around;
      align-items: center;
      padding: 10px 0 14px 0;
      border-top-left-radius: 20px;
      border-top-right-radius: 20px;
      box-shadow: 0 -4px 15px rgba(0,0,0,0.05);
      z-index: 999;
    }

    .nav-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      font-size: 11px;
      font-weight: bold;
      color: #777;
      text-decoration: none;
      flex: 1;
      gap: 4px;
    }

    .nav-item i {
      font-size: 20px;
      margin-bottom: 0;
      display: block;
    }

    .nav-item.active { color: #2a85ff; }
    .nav-item.nav-logout { color: #e53935; }
    .nav-item.nav-logout:hover { color: #c62828; }

    .sidebar-logo { display: none; }

    @media (min-width: 769px) {
      body { flex-direction: row; }

      .navbar {
        position: fixed;
        top: 0; left: 0; bottom: 0; right: auto;
        width: 160px; height: 100vh;
        flex-direction: column; justify-content: flex-start; align-items: stretch;
        padding: 20px 0 20px 0;
        border-top-left-radius: 0; border-top-right-radius: 0;
        border-right: 1px solid #e0ecf8; box-shadow: 4px 0 15px rgba(0,0,0,0.05);
        gap: 4px;
      }

      .sidebar-logo {
        display: flex; align-items: center; justify-content: center; gap: 6px;
        font-size: 13px; font-weight: 800; color: #2a85ff;
        padding: 0 8px 18px 8px; border-bottom: 1px solid #e0ecf8;
        margin-bottom: 8px; text-align: center;
      }

      .nav-item {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        text-align: center; padding: 12px 8px; border-radius: 12px; margin: 2px 8px;
        font-size: 11px; font-weight: 600; flex: none;
        gap: 5px; transition: background 0.2s, color 0.2s;
      }

      .nav-item:hover { background: #eef5fc; color: #2a85ff; }
      .nav-item.active { background: #ddeeff; color: #2a85ff; }
      .nav-item.nav-logout { margin-top: auto; color: #e53935; }
      .nav-item.nav-logout:hover { background: #fff0f0; color: #c62828; }

      .nav-item i { font-size: 22px; display: block; margin-bottom: 0; width: auto; }

      .page-wrapper { margin-left: 160px; width: 100%; }
    }

    /* ── PAGE ── */
    .page {
      width: 100%;
      max-width: 680px;
      margin: 28px auto 110px auto;
      padding: 0 16px;
    }

    .page-title {
      font-size: 18px;
      font-weight: 800;
      color: #0d1c2e;
      margin-bottom: 16px;
    }

    /* ── CART CARD ── */
    .cart-card {
      background: white;
      border-radius: 18px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.05);
      overflow: hidden;
    }

    /* ── ITEM ROW ── */
    .cart-item {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 16px 20px;
      border-bottom: 1px solid #f0f6fc;
      transition: background 0.15s;
    }

    .cart-item:last-child { border-bottom: none; }
    .cart-item:hover { background: #fafcff; }

    /* Custom checkbox */
    .item-check {
      appearance: none;
      -webkit-appearance: none;
      width: 22px;
      height: 22px;
      border: 2px solid #c8dff5;
      border-radius: 6px;
      cursor: pointer;
      flex-shrink: 0;
      position: relative;
      transition: 0.2s;
    }

    .item-check:checked {
      background: #2a85ff;
      border-color: #2a85ff;
    }

    .item-check:checked::after {
      content: '';
      position: absolute;
      left: 5px;
      top: 2px;
      width: 5px;
      height: 9px;
      border: 2.5px solid white;
      border-top: none;
      border-left: none;
      transform: rotate(45deg);
    }

    /* Gambar */
    .item-img {
      width: 74px;
      height: 74px;
      object-fit: cover;
      border-radius: 12px;
      flex-shrink: 0;
      background: #f0f6fc;
    }

    /* Info */
    .item-info {
      flex: 1;
      min-width: 0;
    }

    .item-name {
      font-size: 14px;
      font-weight: 700;
      color: #0d1c2e;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .item-variant {
      font-size: 12px;
      color: #8fa3b8;
      margin-top: 3px;
    }

    /* Harga */
    .item-price {
      font-size: 14px;
      font-weight: 700;
      color: #0d1c2e;
      white-space: nowrap;
      flex-shrink: 0;
    }

    /* Hapus */
    .btn-delete {
      background: none;
      border: none;
      color: #c8dff5;
      font-size: 17px;
      cursor: pointer;
      padding: 6px;
      border-radius: 8px;
      flex-shrink: 0;
      transition: color 0.2s, background 0.2s;
    }

    .btn-delete:hover { color: #e53935; background: #fff0f0; }

    /* ── EMPTY STATE ── */
    .empty-state {
      text-align: center;
      padding: 64px 20px;
      color: #aab;
    }

    .empty-state i {
      font-size: 52px;
      color: #c8dff5;
      margin-bottom: 14px;
      display: block;
    }

    .empty-state p { font-size: 15px; }

    .empty-state a {
      display: inline-block;
      margin-top: 16px;
      padding: 10px 24px;
      background: #2a85ff;
      color: white;
      border-radius: 12px;
      text-decoration: none;
      font-size: 14px;
      font-weight: 700;
    }

    /* ── BOTTOM BAR ── */
    .bottom-bar {
      position: fixed;
      bottom: 68px; /* sit above mobile navbar */
      left: 0;
      right: 0;
      background: white;
      border-top: 1px solid #e8f0fb;
      padding: 13px 20px;
      display: flex;
      align-items: center;
      gap: 14px;
      box-shadow: 0 -4px 20px rgba(0,0,0,0.06);
      z-index: 998;
    }

    @media (min-width: 769px) {
      .bottom-bar {
        bottom: 0;
        left: 180px; /* offset sidebar */
      }
    }

    .select-all-wrap {
      display: flex;
      align-items: center;
      gap: 8px;
      flex-shrink: 0;
    }

    .select-all-wrap label {
      font-size: 13px;
      font-weight: 600;
      color: #556980;
      cursor: pointer;
      white-space: nowrap;
    }

    .total-wrap {
      flex: 1;
      text-align: right;
    }

    .total-label {
      font-size: 11px;
      color: #8fa3b8;
    }

    .total-amount {
      font-size: 16px;
      font-weight: 800;
      color: #0d1c2e;
    }

    .btn-checkout {
      background: #2a85ff;
      color: white;
      border: none;
      border-radius: 12px;
      padding: 13px 22px;
      font-size: 14px;
      font-weight: 700;
      cursor: pointer;
      flex-shrink: 0;
      white-space: nowrap;
      transition: opacity 0.2s, transform 0.1s;
    }

    .btn-checkout:hover { opacity: 0.9; }
    .btn-checkout:active { transform: scale(0.97); }
    .btn-checkout:disabled { background: #c8dff5; cursor: not-allowed; opacity: 1; }

    /* ── TOAST ── */
    .toast {
      position: fixed;
      bottom: 150px;
      left: 50%;
      transform: translateX(-50%) translateY(10px);
      background: #0d1c2e;
      color: white;
      padding: 10px 22px;
      border-radius: 30px;
      font-size: 13px;
      font-weight: 600;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.3s, transform 0.3s;
      z-index: 9999;
      white-space: nowrap;
    }

    .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }

    @media (min-width: 640px) {
      .page { padding: 0 24px; }
    }
  </style>
</head>
<body>

<div class="navbar">
  <div class="sidebar-logo">
    <img src="Logo.svg" alt="LokalThrift" style="width:140px; height:auto; display:block; margin:0 auto;">
  </div>
  <a href="home.php" class="nav-item">
    <i class="fa-solid fa-house"></i><span>Beranda</span>
  </a>
  <a href="keranjang.php" class="nav-item active">
    <i class="fa-solid fa-cart-shopping"></i><span>Keranjang</span>
  </a>
  <a href="pesanan.php" class="nav-item">
    <i class="fa-solid fa-bag-shopping"></i><span>Pesanan</span>
  </a>
  <a href="cek_toko.php" class="nav-item">
    <i class="fa-solid fa-shop"></i><span>Toko</span>
  </a>
  <a href="akun.php" class="nav-item">
    <i class="fa-solid fa-user"></i><span>Akun</span>
  </a>
  <a href="chat.php" class="nav-item">
    <i class="fa-solid fa-message"></i><span>Chat</span>
  </a>
  <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
  <a href="admin/dashboard.php" class="nav-item">
    <i class="fa-solid fa-chart-pie"></i><span>Admin</span>
  </a>
  <?php endif; ?>
  <a href="logout.php" class="nav-item nav-logout">
    <i class="fa-solid fa-right-from-bracket"></i><span>Logout</span>
  </a>
</div>

<div class="page-wrapper">

  <!-- PAGE -->
  <div class="page">
    <div class="page-title">Keranjang Belanja</div>
    <div class="cart-card" id="cart-list"></div>
  </div>

</div>
  <div class="bottom-bar">
    <div class="select-all-wrap">
      <input type="checkbox" class="item-check" id="select-all" onchange="toggleSelectAll(this)">
      <label for="select-all">Pilih Semua</label>
    </div>
    <div class="total-wrap">
      <div class="total-label" id="total-label">Total (0 barang)</div>
      <div class="total-amount" id="total-amount">Rp 0</div>
    </div>
    <button class="btn-checkout" id="btn-checkout" onclick="prosesCheckout()" disabled>
      Checkout
    </button>
  </div>

  <div class="toast" id="toast"></div>

<script>
  // ── AMBIL DATA DARI DATABASE ──
  let keranjang = <?= json_encode($keranjang_db) ?>;

  function getKeranjang() {
    return keranjang;
  }

  function simpanKeranjang(data) {
    keranjang = data;
  }

  function formatRupiah(n) {
    return 'Rp ' + n.toLocaleString('id-ID');
  }

  // ── RENDER ──
  function render() {
    const list = document.getElementById('cart-list');

    if (keranjang.length === 0) {
      list.innerHTML = `
        <div class="empty-state">
          <i class="fa-solid fa-cart-shopping"></i>
          <p>Keranjang kamu masih kosong</p>
          <a href="home.php">Mulai Belanja</a>
        </div>`;
      updateBar([]);
      return;
    }

    list.innerHTML = keranjang.map((item, i) => `
      <div class="cart-item">
        <input type="checkbox" class="item-check item-cb" data-index="${i}"
          ${item.checked ? 'checked' : ''} onchange="toggleItem(${i}, this)">
        <a href="detail.php?id=${item.id}" style="display:block; flex-shrink:0;">
          <img class="item-img" src="${item.gambar}" alt="${item.nama}"
            onerror="this.src='https://via.placeholder.com/74x74?text=?'">
        </a>
        <div class="item-info">
          <div class="item-name">${item.nama}</div>
          <div class="item-variant">${item.varian}</div>
        </div>
        <div class="item-price">${formatRupiah(item.harga)}</div>
        <button class="btn-delete" onclick="hapus(${i})" title="Hapus">
          <i class="fa-regular fa-trash-can"></i>
        </button>
      </div>
    `).join('');

    updateBar(keranjang);
  }

  // ── UPDATE BOTTOM BAR ──
  function updateBar(k) {
    const terpilih = k.filter(i => i.checked);
    const total = terpilih.reduce((s, i) => s + i.harga, 0);

    document.getElementById('total-label').textContent = `Total (${terpilih.length} barang)`;
    document.getElementById('total-amount').textContent = formatRupiah(total);
    document.getElementById('btn-checkout').disabled = terpilih.length === 0;

    const cb = document.getElementById('select-all');
    if (k.length === 0) {
      cb.checked = false;
      cb.indeterminate = false;
    } else {
      cb.checked = k.every(i => i.checked);
      cb.indeterminate = !cb.checked && k.some(i => i.checked);
    }
  }

  // ── ACTIONS ──
  function toggleItem(index, el) {
    keranjang[index].checked = el.checked;
    updateBar(keranjang);
  }

  function toggleSelectAll(el) {
    keranjang.forEach(item => item.checked = el.checked);
    render();
  }

  function hapus(index) {
    const cart_id = keranjang[index].cart_id;
    const nama = keranjang[index].nama;
    
    fetch('api_keranjang.php?action=delete&id=' + cart_id)
      .then(r => r.json())
      .then(res => {
         if (res.status === 'success') {
            keranjang.splice(index, 1);
            render();
            showToast(`"${nama.substring(0, 22)}..." dihapus`);
         } else {
            showToast('Gagal menghapus');
         }
      })
      .catch(e => showToast('Terjadi kesalahan koneksi'));
  }

  function prosesCheckout() {
    const terpilih = keranjang.filter(i => i.checked);
    if (terpilih.length === 0) return;
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'checkout.php';
    
    terpilih.forEach(item => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'cart_ids[]';
        input.value = item.cart_id;
        form.appendChild(input);
    });
    
    document.body.appendChild(form);
    form.submit();
  }

  function showToast(msg) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2500);
  }

  // ── INIT ──
  // Bersihkan cache lama di localStorage agar tidak tersangkut
  localStorage.removeItem('keranjang');
  render();
</script>
</body>
</html>
