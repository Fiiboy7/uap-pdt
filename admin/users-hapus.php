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
    header("Location: users.php");
    exit;
}

$id_user = intval($_GET['id']);

// Prevent admin from deleting themselves
if ($id_user == $_SESSION['user_id']) {
    $_SESSION['user_error'] = "Anda tidak dapat menghapus akun sendiri.";
    header("Location: users.php");
    exit;
}

// Get user data
$user_query = "SELECT * FROM users WHERE id_user = $id_user";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

if ($user) {
    // Delete user
    $delete_query = "DELETE FROM users WHERE id_user = $id_user";
    
    if (mysqli_query($conn, $delete_query)) {
        $_SESSION['user_success'] = "User berhasil dihapus!";
    } else {
        $_SESSION['user_error'] = "Gagal menghapus user: " . mysqli_error($conn);
    }
} else {
    $_SESSION['user_error'] = "User tidak ditemukan.";
}

header("Location: users.php");
exit;
?>
