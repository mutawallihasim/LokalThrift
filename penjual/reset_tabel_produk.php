<?php
// Script untuk reset tabel produk
require_once '../koneksi.php';

echo "<h2>Reset Tabel Produk</h2>";

// 1. Cari dan drop semua foreign key yang mengarah ke tabel produk
echo "<h3>Step 1: Hapus Foreign Key Constraints</h3>";
$fkQuery = mysqli_query($koneksi, "
    SELECT CONSTRAINT_NAME, TABLE_NAME 
    FROM information_schema.KEY_COLUMN_USAGE 
    WHERE REFERENCED_TABLE_NAME = 'produk' 
    AND TABLE_SCHEMA = DATABASE()
");

if ($fkQuery) {
    $fkCount = 0;
    while ($fk = mysqli_fetch_assoc($fkQuery)) {
        $dropFK = mysqli_query($koneksi, "ALTER TABLE `{$fk['TABLE_NAME']}` DROP FOREIGN KEY `{$fk['CONSTRAINT_NAME']}`");
        if ($dropFK) {
            echo "<p style='color:green'>✓ Foreign key {$fk['CONSTRAINT_NAME']} di tabel {$fk['TABLE_NAME']} dihapus</p>";
            $fkCount++;
        } else {
            echo "<p style='color:orange'>⚠ Gagal hapus FK {$fk['CONSTRAINT_NAME']}: " . mysqli_error($koneksi) . "</p>";
        }
    }
    if ($fkCount == 0) {
        echo "<p style='color:gray'>- Tidak ada foreign key yang perlu dihapus</p>";
    }
} else {
    echo "<p style='color:orange'>⚠ Tidak bisa cek foreign key: " . mysqli_error($koneksi) . "</p>";
}

// 2. Disable foreign key checks sementara
mysqli_query($koneksi, "SET FOREIGN_KEY_CHECKS = 0");

// 3. Drop tabel produk lama
echo "<h3>Step 2: Hapus Tabel Produk Lama</h3>";
$drop = mysqli_query($koneksi, "DROP TABLE IF EXISTS `produk`");
if ($drop) {
    echo "<p style='color:green'>✓ Tabel produk lama berhasil dihapus</p>";
} else {
    echo "<p style='color:red'>✗ Gagal hapus tabel produk: " . mysqli_error($koneksi) . "</p>";
}

// 4. Enable foreign key checks kembali
mysqli_query($koneksi, "SET FOREIGN_KEY_CHECKS = 1");

// 5. Buat tabel produk baru dengan struktur lengkap
echo "<h3>Step 3: Buat Tabel Produk Baru</h3>";
$create = mysqli_query($koneksi, "CREATE TABLE `produk` (
  `id_produk`  INT AUTO_INCREMENT PRIMARY KEY,
  `id_toko`    INT NOT NULL,
  `nama`       VARCHAR(200) NOT NULL,
  `deskripsi`  TEXT,
  `harga`      INT NOT NULL DEFAULT 0,
  `stok`       INT NOT NULL DEFAULT 1,
  `kondisi`    VARCHAR(50) DEFAULT 'Very Good',
  `kategori`   VARCHAR(50) DEFAULT 'Casual',
  `gambar`     VARCHAR(255) DEFAULT NULL,
  `aktif`      TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

if ($create) {
    echo "<p style='color:green'>✓ Tabel produk baru berhasil dibuat</p>";
} else {
    echo "<p style='color:red'>✗ Gagal buat tabel produk: " . mysqli_error($koneksi) . "</p>";
}

// 6. Cek struktur tabel
echo "<h3>Step 4: Struktur Tabel Produk Baru</h3>";
$check = mysqli_query($koneksi, "DESCRIBE `produk`");
if ($check) {
    echo "<table border='1' cellpadding='5' style='border-collapse:collapse'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = mysqli_fetch_assoc($check)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red'>✗ Gagal cek struktur: " . mysqli_error($koneksi) . "</p>";
}

echo "<br><br><a href='produk.php' style='display:inline-block;padding:10px 20px;background:#2a85ff;color:white;text-decoration:none;border-radius:8px'>← Kembali ke Kelola Produk</a>";
?>
