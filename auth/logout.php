<?php
session_start();
session_destroy();
$_SESSION['logout_success'] = "Anda telah berhasil logout.";
header("Location: ../index.php"); 
exit;
?>
