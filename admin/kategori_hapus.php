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
    header("Location: kategori.php");
    exit;
}

$id_kategori = intval($_GET['id']);

// Check if category has products
$check_query = "SELECT COUNT(*) as count FROM produk WHERE id_kategori = $id_kategori";
$check_result = mysqli_query($conn, $check_query);
$product_count = mysqli_fetch_assoc($check_result)['count'];

if ($product_count > 0) {
    $_SESSION['kategori_error'] = "Kategori tidak dapat dihapus karena masih memiliki $product_count produk.";
} else {
    // Delete category
    $delete_query = "DELETE FROM kategori WHERE id_kategori = $id_kategori";
    
    if (mysqli_query($conn, $delete_query)) {
        $_SESSION['kategori_success'] = "Kategori berhasil dihapus!";
    } else {
        $_SESSION['kategori_error'] = "Gagal menghapus kategori: " . mysqli_error($conn);
    }
}

header("Location: kategori.php");
exit;
?>
