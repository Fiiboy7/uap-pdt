<?php
session_start();
include '../config/db.php';

// Cek apakah pengguna sudah login dan memiliki peran admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// mengambil data produk untuk statistik
$total_produk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM produk"))['count'];
$produk_aktif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM produk WHERE status='aktif'"))['count'];
$produk_nonaktif = $total_produk - $produk_aktif;
$stok_menipis = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM produk WHERE stok <= stok_minimum"))['count'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - WarungKu</title>
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
            <span class="text-gray-600">Kelola Produk</span>
        </div>

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Kelola Produk</h1>
            <a href="produk-tambah.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-plus mr-2"></i>Tambah Produk
            </a>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Produk</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $total_produk; ?></p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-box text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Produk Aktif</p>
                        <p class="text-2xl font-bold text-green-600"><?php echo $produk_aktif; ?></p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Stok Menipis</p>
                        <p class="text-2xl font-bold text-red-600"><?php echo $stok_menipis; ?></p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <input type="text" id="searchProduct" placeholder="Cari produk..." 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <select id="filterKategori" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Semua Kategori</option>
                        <?php
                        $kategori_query = "SELECT * FROM kategori ORDER BY nama_kategori";
                        $kategori_result = mysqli_query($conn, $kategori_query);
                        while ($kategori = mysqli_fetch_assoc($kategori_result)) {
                            echo "<option value='{$kategori['id_kategori']}'>{$kategori['nama_kategori']}</option>";
                        }
                        ?>
                    </select>
                    <select id="filterStatus" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Semua Status</option>
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                    <select id="filterStok" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Semua Stok</option>
                        <option value="menipis">Stok Menipis</option>
                        <option value="habis">Stok Habis</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Product List -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-list mr-2"></i>Daftar Produk
                </h2>
            </div>
            <div class="p-6">
                <?php
                if (isset($_SESSION['produk_success'])) {
                    echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            <i class="fas fa-check-circle mr-2"></i>' . $_SESSION['produk_success'] . '
                          </div>';
                    unset($_SESSION['produk_success']);
                }
                if (isset($_SESSION['produk_error'])) {
                    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <i class="fas fa-exclamation-circle mr-2"></i>' . $_SESSION['produk_error'] . '
                          </div>';
                    unset($_SESSION['produk_error']);
                }
                ?>
                
                <?php
                $produk_query = "SELECT p.*, k.nama_kategori 
                                FROM produk p 
                                LEFT JOIN kategori k ON p.id_kategori = k.id_kategori 
                                ORDER BY p.nama_produk";
                $produk_result = mysqli_query($conn, $produk_query);
                
                if (mysqli_num_rows($produk_result) > 0):
                ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while ($produk = mysqli_fetch_assoc($produk_result)): ?>
                            <tr data-kategori-id="<?php echo $produk['id_kategori']; ?>" data-status="<?php echo $produk['status']; ?>">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <?php if (!empty($produk['foto_produk']) && file_exists("../uploads/{$produk['foto_produk']}")): ?>
                                                <img class="h-10 w-10 rounded-full object-cover" 
                                                     src="../uploads/<?php echo $produk['foto_produk']; ?>" 
                                                     alt="<?php echo $produk['nama_produk']; ?>">
                                            <?php else: ?>
                                                <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                    <i class="fas fa-box text-gray-400"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($produk['nama_produk']); ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <?php echo htmlspecialchars($produk['kode_produk']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <?php echo htmlspecialchars($produk['nama_kategori'] ?? 'Tidak ada kategori'); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        Rp <?php echo number_format($produk['harga_jual'], 0, ',', '.'); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($produk['stok'] <= $produk['stok_minimum'] && $produk['stok'] > 0): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800" data-stok-status="menipis">
                                            <?php echo $produk['stok']; ?> (Menipis)
                                        </span>
                                    <?php elseif ($produk['stok'] == 0): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800" data-stok-status="habis">
                                            Habis
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800" data-stok-status="normal">
                                            <?php echo $produk['stok']; ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($produk['status'] == 'aktif'): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Aktif
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            Nonaktif
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="produk-edit.php?id=<?php echo $produk['id_produk']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="produk-hapus.php?id=<?php echo $produk['id_produk']; ?>" 
                                       class="text-red-600 hover:text-red-900"
                                       onclick="return confirm('Yakin ingin menghapus produk ini?')">
                                        <i class="fas fa-trash"></i> Hapus
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-8">
                    <i class="fas fa-box-open text-gray-400 text-5xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-500">Belum ada produk</h3>
                    <p class="text-gray-500 mb-4">Mulai tambahkan produk untuk inventaris Anda</p>
                    <a href="produk-tambah.php" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-plus mr-2"></i> Tambah Produk Pertama
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Search functionality
        document.getElementById('searchProduct').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const productName = row.querySelector('td:first-child .text-gray-900').textContent.toLowerCase();
                const productCode = row.querySelector('td:first-child .text-gray-500').textContent.toLowerCase();
                
                if (productName.includes(searchTerm) || productCode.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
        
        // Filter functionality
        function filterProducts() {
            const kategoriFilter = document.getElementById('filterKategori').value;
            const statusFilter = document.getElementById('filterStatus').value;
            const stokFilter = document.getElementById('filterStok').value;
            
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                // Ambil data dari atribut data-* yang sudah ditambahkan
                const kategoriId = row.getAttribute('data-kategori-id');
                const status = row.getAttribute('data-status');
                const stokStatusElement = row.querySelector('[data-stok-status]');
                const stokStatus = stokStatusElement ? stokStatusElement.getAttribute('data-stok-status') : 'normal';
                
                let showRow = true;
                
                // Filter kategori berdasarkan ID
                if (kategoriFilter && kategoriId !== kategoriFilter) {
                    showRow = false;
                }
                
                // Filter status
                if (statusFilter && status !== statusFilter) {
                    showRow = false;
                }
                
                // Filter stok
                if (stokFilter && stokStatus !== stokFilter) {
                    showRow = false;
                }
                
                row.style.display = showRow ? '' : 'none';
            });
        }
        
        // Event listeners untuk filter
        document.getElementById('filterKategori').addEventListener('change', filterProducts);
        document.getElementById('filterStatus').addEventListener('change', filterProducts);
        document.getElementById('filterStok').addEventListener('change', filterProducts);
    </script>
</body>
</html>