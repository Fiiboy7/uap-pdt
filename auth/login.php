<?php 
session_start(); 
include '../config/db.php'; 

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: ../admin/dashboard.php");
    } else {
        header("Location: ../kasir/dashboard.php");
    }
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']); 
    $password = $_POST['password']; 
    
    $query = "SELECT * FROM users WHERE username='$username' AND status='aktif'"; 
    $result = mysqli_query($conn, $query); 
    $user = mysqli_fetch_assoc($result); 
    
    if ($user && password_verify($password, $user['password'])) { 
        $_SESSION['user_id'] = $user['id_user'];
        $_SESSION['username'] = $user['username']; 
        $_SESSION['role'] = $user['role'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
        
        if ($user['role'] == 'admin') {
            header("Location: ../admin/dashboard.php");
        } else {
            header("Location: ../kasir/dashboard.php");
        }
        exit; 
    } else { 
        $_SESSION['login_error'] = "Login gagal. Username atau password salah.";
        header("Location: ../index.php"); 
        exit;
    }
} else {
    header("Location: ../index.php");
    exit;
}
?>
