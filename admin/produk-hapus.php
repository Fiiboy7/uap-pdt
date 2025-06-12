<?php
session_start();
include '../config/db.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: produk.php");
    exit;
}

$id_produk = intval($_GET['id']);

// Get product data to delete photo
$produk_query = "SELECT foto_produk FROM produk WHERE id_produk = $id_produk";
$produk_result = mysqli_query($conn, $produk_query);
$produk = mysqli_fetch_assoc($produk_result);

if ($produk) {
    // Delete photo if exists
    if (!empty($produk['foto_produk']) && file_exists("../uploads/{$produk['foto_produk']}")) {
        unlink("../uploads/{$produk['foto_produk']}");
    }
    
    // Delete product
    $delete_query = "DELETE FROM produk WHERE id_produk = $id_produk";
    
    if (mysqli_query($conn, $delete_query)) {
        $_SESSION['produk_success'] = "Produk berhasil dihapus!";
    } else {
        $_SESSION['produk_error'] = "Gagal menghapus produk: " . mysqli_error($conn);
    }
} else {
    $_SESSION['produk_error'] = "Produk tidak ditemukan.";
}

header("Location: produk.php");
exit;
?>
