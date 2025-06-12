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

// Get user data
$user_query = "SELECT * FROM users WHERE id_user = $id_user";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

if (!$user) {
    $_SESSION['user_error'] = "User tidak ditemukan.";
    header("Location: users.php");
    exit;
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $role = $_POST['role'];
    $status = $_POST['status'];
    $new_password = $_POST['new_password'];
    
    // Check if username already exists (excluding current user)
    $check_query = "SELECT * FROM users WHERE username = '$username' AND id_user != $id_user";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $_SESSION['user_error'] = "Username sudah digunakan oleh user lain.";
    } else {
        // Handle password change
        $password_update = "";
        if (!empty($new_password)) {
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $password_update = ", password = '$password_hash'";
        }
        
        $update_query = "UPDATE users SET 
                        username = '$username', 
                        nama_lengkap = '$nama_lengkap',
                        role = '$role',
                        status = '$status'
                        $password_update
                        WHERE id_user = $id_user";
        
        if (mysqli_query($conn, $update_query)) {
            $_SESSION['user_success'] = "User berhasil diupdate!";
            header("Location: users.php");
            exit;
        } else {
            $_SESSION['user_error'] = "Gagal mengupdate user: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - WarungKu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <i class="fas fa-store text-2xl text-blue-600"></i>
                    <h1 class="text-xl font-bold text-gray-800">WarungKu - Admin</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600">Selamat datang, <?php echo $_SESSION['nama_lengkap']; ?></span>
                    <a href="../auth/logout.php" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Breadcrumb -->
        <div class="flex items-center mb-6 text-sm">
            <a href="dashboard.php" class="text-blue-600 hover:text-blue-800">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="users.php" class="text-blue-600 hover:text-blue-800">Kelola User</a>
            <span class="mx-2">/</span>
            <span class="text-gray-600">Edit User</span>
        </div>

        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Edit User</h1>
            <p class="text-gray-600">Edit informasi user "<?php echo htmlspecialchars($user['username']); ?>"</p>
        </div>

        <!-- Form -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <?php
                    if (isset($_SESSION['user_error'])) {
                        echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                                <i class="fas fa-exclamation-circle mr-2"></i>' . $_SESSION['user_error'] . '
                              </div>';
                        unset($_SESSION['user_error']);
                    }
                    ?>
                    
                    <form method="POST">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username *</label>
                                <input type="text" id="username" name="username" required
                                       value="<?php echo htmlspecialchars($user['username']); ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            
                            <div>
                                <label for="nama_lengkap" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap *</label>
                                <input type="text" id="nama_lengkap" name="nama_lengkap" required
                                       value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            
                            <div>
                                <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
                                <select id="role" name="role" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                    <option value="kasir" <?php echo ($user['role'] == 'kasir') ? 'selected' : ''; ?>>Kasir</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                                <select id="status" name="status" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="aktif" <?php echo ($user['status'] == 'aktif') ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="nonaktif" <?php echo ($user['status'] == 'nonaktif') ? 'selected' : ''; ?>>Nonaktif</option>
                                </select>
                            </div>
                            
                            <div class="md:col-span-2">
                                <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                                <input type="password" id="new_password" name="new_password"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah password</p>
                            </div>
                        </div>
                        
                        <div class="mt-6 flex items-center justify-end gap-3">
                            <a href="users.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                                <i class="fas fa-arrow-left mr-2"></i>Kembali
                            </a>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                <i class="fas fa-save mr-2"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="md:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-medium text-gray-800 mb-4">Informasi User</h3>
                    
                    <div class="flex items-center justify-center mb-4">
                        <div class="h-24 w-24 rounded-full bg-<?php echo $user['role'] == 'admin' ? 'red' : 'green'; ?>-100 flex items-center justify-center">
                            <i class="fas fa-<?php echo $user['role'] == 'admin' ? 'user-shield' : 'cash-register'; ?> text-<?php echo $user['role'] == 'admin' ? 'red' : 'green'; ?>-600 text-4xl"></i>
                        </div>
                    </div>
                    
                    <div class="mb-4 text-center">
                        <h4 class="font-medium"><?php echo htmlspecialchars($user['nama_lengkap']); ?></h4>
                        <p class="text-sm text-gray-600">@<?php echo htmlspecialchars($user['username']); ?></p>
                        <span class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-<?php echo $user['role'] == 'admin' ? 'red' : 'green'; ?>-100 text-<?php echo $user['role'] == 'admin' ? 'red' : 'green'; ?>-800">
                            <?php echo ucfirst($user['role']); ?>
                        </span>
                    </div>
                    
                    <div class="border-t pt-4">
                        <div class="flex justify-between mb-2">
                            <span class="text-sm text-gray-600">Status:</span>
                            <span class="text-sm font-medium"><?php echo ucfirst($user['status']); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Tanggal Dibuat:</span>
                            <span class="text-sm font-medium"><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></span>
                        </div>
                    </div>
                    
                    <?php if ($user['id_user'] == $_SESSION['user_id']): ?>
                    <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="text-sm text-yellow-700">
                            <i class="fas fa-info-circle mr-1"></i>
                            Ini adalah akun Anda sendiri
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
