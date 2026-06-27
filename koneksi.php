<?php
$host     = "localhost";
$username = "root";
$password = "";
$database = "lokalthrift";

$koneksi = mysqli_connect($host, $username, $password, $database);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Mengatur charset agar mendukung UTF-8
mysqli_set_charset($koneksi, "utf8");
?>