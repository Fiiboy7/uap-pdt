<?php
$host = "localhost";
$user = "root";
$password = "";
$db = "warungku";  

$conn = mysqli_connect($host, $user, $password, $db);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Set charset untuk mendukung karakter Indonesia
mysqli_set_charset($conn, "utf8");
?>
