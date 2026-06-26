<?php
$pengiriman = $_POST['pengiriman'] ?? '';
?>

<h2>Ringkasan Pesanan</h2>

<p>Metode Pengiriman: <?php echo $pengiriman; ?></p>

<a href="checkout.php">Kembali</a>