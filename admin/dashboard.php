<?php
session_start();
include '../config/db.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Get statistics
$stats_query = "
    SELECT 
        (SELECT COUNT(*) FROM produk WHERE status='aktif') as total_produk,
        (SELECT COUNT(*) FROM kategori) as total_kategori,
        (SELECT COUNT(*) FROM transaksi WHERE DATE(tanggal_transaksi) = CURDATE()) as transaksi_hari_ini,
        (SELECT COALESCE(SUM(total_bayar), 0) FROM transaksi WHERE DATE(tanggal_transaksi) = CURDATE()) as pendapatan_hari_ini,
        (SELECT COUNT(*) FROM produk WHERE stok <= stok_minimum) as produk_stok_menipis
";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Get recent transactions
$recent_transactions = mysqli_query($conn, "
    SELECT t.*, u.nama_lengkap as nama_kasir 
    FROM transaksi t 
    JOIN users u ON t.id_kasir = u.id_user 
    ORDER BY t.tanggal_transaksi DESC 
    LIMIT 5
");

// Get low stock products
$low_stock = mysqli_query($conn, "
    SELECT * FROM produk 
    WHERE stok <= stok_minimum AND status='aktif' 
    ORDER BY stok ASC 
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - WarungKu</title>
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
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Produk</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $stats['total_produk']; ?></p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-box text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Transaksi Hari Ini</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $stats['transaksi_hari_ini']; ?></p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-shopping-cart text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Pendapatan Hari Ini</p>
                        <p class="text-2xl font-bold text-gray-900">Rp <?php echo number_format($stats['pendapatan_hari_ini'], 0, ',', '.'); ?></p>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <i class="fas fa-money-bill-wave text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Stok Menipis</p>
                        <p class="text-2xl font-bold text-red-600"><?php echo $stats['produk_stok_menipis']; ?></p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Transactions -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-clock mr-2"></i>Transaksi Terbaru
                    </h2>
                </div>
                <div class="p-6">
                    <?php if (mysqli_num_rows($recent_transactions) > 0): ?>
                        <div class="space-y-4">
                            <?php while ($transaction = mysqli_fetch_assoc($recent_transactions)): ?>
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="font-medium text-gray-900"><?php echo $transaction['no_transaksi']; ?></p>
                                        <p class="text-sm text-gray-600">Kasir: <?php echo $transaction['nama_kasir']; ?></p>
                                        <p class="text-xs text-gray-500"><?php echo date('d/m/Y H:i', strtotime($transaction['tanggal_transaksi'])); ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-green-600">Rp <?php echo number_format($transaction['total_bayar'], 0, ',', '.'); ?></p>
                                        <span class="inline-block px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">
                                            <?php echo ucfirst($transaction['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-8">Belum ada transaksi hari ini</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Low Stock Alert -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-exclamation-triangle mr-2 text-red-500"></i>Peringatan Stok
                    </h2>
                </div>
                <div class="p-6">
                    <?php if (mysqli_num_rows($low_stock) > 0): ?>
                        <div class="space-y-4">
                            <?php while ($product = mysqli_fetch_assoc($low_stock)): ?>
                                <div class="flex items-center justify-between p-4 bg-red-50 rounded-lg border border-red-200">
                                    <div>
                                        <p class="font-medium text-gray-900"><?php echo $product['nama_produk']; ?></p>
                                        <p class="text-sm text-gray-600">Kode: <?php echo $product['kode_produk']; ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-red-600">Stok: <?php echo $product['stok']; ?></p>
                                        <p class="text-xs text-gray-500">Min: <?php echo $product['stok_minimum']; ?></p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-8">Semua produk stoknya aman</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-bolt mr-2"></i>Aksi Cepat
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="produk.php" class="bg-blue-500 text-white p-6 rounded-lg text-center hover:bg-blue-600 transition">
                    <i class="fas fa-box text-2xl mb-2"></i>
                    <p class="font-medium">Kelola Produk</p>
                </a>
                <a href="kategori.php" class="bg-green-500 text-white p-6 rounded-lg text-center hover:bg-green-600 transition">
                    <i class="fas fa-tags text-2xl mb-2"></i>
                    <p class="font-medium">Kelola Kategori</p>
                </a>
                <a href="laporan.php" class="bg-yellow-500 text-white p-6 rounded-lg text-center hover:bg-yellow-600 transition">
                    <i class="fas fa-chart-bar text-2xl mb-2"></i>
                    <p class="font-medium">Laporan</p>
                </a>
                <a href="users.php" class="bg-purple-500 text-white p-6 rounded-lg text-center hover:bg-purple-600 transition">
                    <i class="fas fa-users text-2xl mb-2"></i>
                    <p class="font-medium">Kelola User</p>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
