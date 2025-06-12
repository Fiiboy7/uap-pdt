<?php
session_start();
include '../config/db.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Get statistics
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'];
$total_admins = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'admin'"))['count'];
$total_kasir = $total_users - $total_admins;
$recent_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"))['count'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - WarungKu</title>
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
            <span class="text-gray-600">Kelola User</span>
        </div>

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Kelola User</h1>
            <a href="users-tambah.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-user-plus mr-2"></i>Tambah User
            </a>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total User</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $total_users; ?></p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-users text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Admin</p>
                        <p class="text-2xl font-bold text-red-600"><?php echo $total_admins; ?></p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <i class="fas fa-user-shield text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Kasir</p>
                        <p class="text-2xl font-bold text-green-600"><?php echo $total_kasir; ?></p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-cash-register text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">User Baru (7 Hari)</p>
                        <p class="text-2xl font-bold text-purple-600"><?php echo $recent_users; ?></p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <i class="fas fa-user-plus text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- User List -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-list mr-2"></i>Daftar User
                </h2>
            </div>
            <div class="p-6">
                <?php
                if (isset($_SESSION['user_success'])) {
                    echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            <i class="fas fa-check-circle mr-2"></i>' . $_SESSION['user_success'] . '
                          </div>';
                    unset($_SESSION['user_success']);
                }
                if (isset($_SESSION['user_error'])) {
                    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <i class="fas fa-exclamation-circle mr-2"></i>' . $_SESSION['user_error'] . '
                          </div>';
                    unset($_SESSION['user_error']);
                }
                ?>
                
                <?php
                $users_query = "SELECT * FROM users ORDER BY created_at DESC";
                $users_result = mysqli_query($conn, $users_query);
                
                if (mysqli_num_rows($users_result) > 0):
                ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Lengkap</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Dibuat</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-<?php echo $user['role'] == 'admin' ? 'red' : 'green'; ?>-100 flex items-center justify-center">
                                                <i class="fas fa-<?php echo $user['role'] == 'admin' ? 'user-shield' : 'cash-register'; ?> text-<?php echo $user['role'] == 'admin' ? 'red' : 'green'; ?>-600"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($user['username']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <?php echo htmlspecialchars($user['nama_lengkap']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-<?php echo $user['role'] == 'admin' ? 'red' : 'green'; ?>-100 text-<?php echo $user['role'] == 'admin' ? 'red' : 'green'; ?>-800">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-<?php echo $user['status'] == 'aktif' ? 'green' : 'gray'; ?>-100 text-<?php echo $user['status'] == 'aktif' ? 'green' : 'gray'; ?>-800">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="users-edit.php?id=<?php echo $user['id_user']; ?>" 
                                       class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <?php if ($user['id_user'] != $_SESSION['user_id']): ?>
                                        <a href="users-hapus.php?id=<?php echo $user['id_user']; ?>" 
                                           class="text-red-600 hover:text-red-900"
                                           onclick="return confirm('Yakin ingin menghapus user ini?')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    <?php else: ?>
                                        <span class="text-gray-400" title="Tidak dapat menghapus akun sendiri">
                                            <i class="fas fa-lock"></i> Terkunci
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-8">
                    <i class="fas fa-users text-gray-400 text-5xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-500">Belum ada user</h3>
                    <p class="text-gray-500 mb-4">Mulai tambahkan user untuk sistem WarungKu</p>
                    <a href="users-tambah.php" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-user-plus mr-2"></i> Tambah User Pertama
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
