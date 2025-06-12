<?php
session_start();
include '../config/db.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Get statistics
$total_kategori = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM kategori"))['count'];
$produk_per_kategori = mysqli_query($conn, "
    SELECT k.id_kategori, k.nama_kategori, COUNT(p.id_produk) as jumlah_produk 
    FROM kategori k 
    LEFT JOIN produk p ON k.id_kategori = p.id_kategori 
    GROUP BY k.id_kategori 
    ORDER BY jumlah_produk DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori - WarungKu</title>
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
            <span class="text-gray-600">Kelola Kategori</span>
        </div>

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Kelola Kategori</h1>
            <a href="kategori-tambah.php" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                <i class="fas fa-plus mr-2"></i>Tambah Kategori
            </a>
        </div>

        <!-- Statistics Card -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Statistik Kategori</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Kategori</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo $total_kategori; ?></p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-full">
                            <i class="fas fa-tags text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-2">Distribusi Produk per Kategori</p>
                    <div class="space-y-2">
                        <?php 
                        $max_produk = 0;
                        $kategori_data = [];
                        
                        while ($row = mysqli_fetch_assoc($produk_per_kategori)) {
                            $kategori_data[] = $row;
                            if ($row['jumlah_produk'] > $max_produk) {
                                $max_produk = $row['jumlah_produk'];
                            }
                        }
                        
                        foreach ($kategori_data as $kategori): 
                            $percentage = ($max_produk > 0) ? ($kategori['jumlah_produk'] / $max_produk) * 100 : 0;
                        ?>
                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span><?php echo htmlspecialchars($kategori['nama_kategori']); ?></span>
                                    <span><?php echo $kategori['jumlah_produk']; ?> produk</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category List -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-list mr-2"></i>Daftar Kategori
                </h2>
            </div>
            <div class="p-6">
                <?php
                if (isset($_SESSION['kategori_success'])) {
                    echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            <i class="fas fa-check-circle mr-2"></i>' . $_SESSION['kategori_success'] . '
                          </div>';
                    unset($_SESSION['kategori_success']);
                }
                if (isset($_SESSION['kategori_error'])) {
                    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <i class="fas fa-exclamation-circle mr-2"></i>' . $_SESSION['kategori_error'] . '
                          </div>';
                    unset($_SESSION['kategori_error']);
                }
                ?>
                
                <?php
                $kategori_query = "SELECT k.*, COUNT(p.id_produk) as jumlah_produk 
                                  FROM kategori k 
                                  LEFT JOIN produk p ON k.id_kategori = p.id_kategori 
                                  GROUP BY k.id_kategori 
                                  ORDER BY k.nama_kategori";
                $kategori_result = mysqli_query($conn, $kategori_query);
                
                if (mysqli_num_rows($kategori_result) > 0):
                ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Kategori</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Produk</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Dibuat</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while ($kategori = mysqli_fetch_assoc($kategori_result)): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                                                <i class="fas fa-tag text-green-600"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($kategori['nama_kategori']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        <?php echo htmlspecialchars($kategori['deskripsi'] ?? 'Tidak ada deskripsi'); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        <?php echo $kategori['jumlah_produk']; ?> produk
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('d/m/Y', strtotime($kategori['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="kategori-edit.php?id=<?php echo $kategori['id_kategori']; ?>" 
                                       class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <?php if ($kategori['jumlah_produk'] == 0): ?>
                                        <a href="kategori-hapus.php?id=<?php echo $kategori['id_kategori']; ?>" 
                                           class="text-red-600 hover:text-red-900"
                                           onclick="return confirm('Yakin ingin menghapus kategori ini?')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    <?php else: ?>
                                        <span class="text-gray-400" title="Tidak dapat dihapus karena masih ada produk">
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
                    <i class="fas fa-tags text-gray-400 text-5xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-500">Belum ada kategori</h3>
                    <p class="text-gray-500 mb-4">Mulai tambahkan kategori untuk mengorganisir produk Anda</p>
                    <a href="kategori-tambah.php" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <i class="fas fa-plus mr-2"></i> Tambah Kategori Pertama
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
