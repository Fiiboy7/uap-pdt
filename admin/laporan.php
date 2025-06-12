<?php
session_start();
include '../config/db.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Get date range from form or default to today
$tanggal_mulai = isset($_GET['tanggal_mulai']) ? $_GET['tanggal_mulai'] : date('Y-m-d');
$tanggal_selesai = isset($_GET['tanggal_selesai']) ? $_GET['tanggal_selesai'] : date('Y-m-d');

// Get transaction summary
$summary_query = "
    SELECT 
        COUNT(*) as total_transaksi,
        SUM(total_item) as total_item,
        SUM(subtotal) as total_subtotal,
        SUM(diskon) as total_diskon,
        SUM(pajak) as total_pajak,
        SUM(total_bayar) as total_pendapatan
    FROM transaksi 
    WHERE DATE(tanggal_transaksi) BETWEEN '$tanggal_mulai' AND '$tanggal_selesai'
    AND status = 'selesai'
";
$summary_result = mysqli_query($conn, $summary_query);
$summary = mysqli_fetch_assoc($summary_result);

// Get daily sales
$daily_sales = mysqli_query($conn, "
    SELECT 
        DATE(tanggal_transaksi) as tanggal,
        COUNT(*) as jumlah_transaksi,
        SUM(total_bayar) as pendapatan
    FROM transaksi 
    WHERE DATE(tanggal_transaksi) BETWEEN '$tanggal_mulai' AND '$tanggal_selesai'
    AND status = 'selesai'
    GROUP BY DATE(tanggal_transaksi)
    ORDER BY tanggal DESC
");

// Get top products
$top_products = mysqli_query($conn, "
    SELECT 
        p.nama_produk,
        SUM(dt.jumlah) as total_terjual,
        SUM(dt.subtotal) as total_pendapatan
    FROM detail_transaksi dt
    JOIN produk p ON dt.id_produk = p.id_produk
    JOIN transaksi t ON dt.id_transaksi = t.id_transaksi
    WHERE DATE(t.tanggal_transaksi) BETWEEN '$tanggal_mulai' AND '$tanggal_selesai'
    AND t.status = 'selesai'
    GROUP BY p.id_produk
    ORDER BY total_terjual DESC
    LIMIT 10
");

// Get cashier performance
$cashier_performance = mysqli_query($conn, "
    SELECT 
        u.nama_lengkap,
        COUNT(*) as jumlah_transaksi,
        SUM(t.total_bayar) as total_penjualan
    FROM transaksi t
    JOIN users u ON t.id_kasir = u.id_user
    WHERE DATE(t.tanggal_transaksi) BETWEEN '$tanggal_mulai' AND '$tanggal_selesai'
    AND t.status = 'selesai'
    GROUP BY t.id_kasir
    ORDER BY total_penjualan DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - WarungKu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-6xl mx-auto px-4">
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

    <div class="max-w-6xl mx-auto px-4 py-8">
        <!-- Breadcrumb -->
        <div class="flex items-center mb-6 text-sm">
            <a href="dashboard.php" class="text-blue-600 hover:text-blue-800">Dashboard</a>
            <span class="mx-2">/</span>
            <span class="text-gray-600">Laporan</span>
        </div>

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Laporan Penjualan</h1>
            <button onclick="window.print()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                <i class="fas fa-print mr-2"></i>Cetak Laporan
            </button>
        </div>

        <!-- Date Filter -->
        <div class="bg-white rounded-lg shadow-md p-4 mb-6">
            <form method="GET" class="flex flex-wrap items-end gap-4">
                <div>
                    <label for="tanggal_mulai" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                    <input type="date" id="tanggal_mulai" name="tanggal_mulai" 
                           value="<?php echo $tanggal_mulai; ?>"
                           class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label for="tanggal_selesai" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                    <input type="date" id="tanggal_selesai" name="tanggal_selesai" 
                           value="<?php echo $tanggal_selesai; ?>"
                           class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-search mr-2"></i>Filter
                </button>
            </form>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-md p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Transaksi</p>
                        <p class="text-xl font-bold text-gray-900"><?php echo $summary['total_transaksi'] ?? 0; ?></p>
                    </div>
                    <div class="bg-blue-100 p-2 rounded-full">
                        <i class="fas fa-shopping-cart text-blue-600 text-lg"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Item Terjual</p>
                        <p class="text-xl font-bold text-gray-900"><?php echo $summary['total_item'] ?? 0; ?></p>
                    </div>
                    <div class="bg-green-100 p-2 rounded-full">
                        <i class="fas fa-box text-green-600 text-lg"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Diskon</p>
                        <p class="text-xl font-bold text-red-600">Rp <?php echo number_format($summary['total_diskon'] ?? 0, 0, ',', '.'); ?></p>
                    </div>
                    <div class="bg-red-100 p-2 rounded-full">
                        <i class="fas fa-percentage text-red-600 text-lg"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Pendapatan</p>
                        <p class="text-xl font-bold text-green-600">Rp <?php echo number_format($summary['total_pendapatan'] ?? 0, 0, ',', '.'); ?></p>
                    </div>
                    <div class="bg-yellow-100 p-2 rounded-full">
                        <i class="fas fa-money-bill-wave text-yellow-600 text-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Daily Sales -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-chart-line mr-2"></i>Penjualan Harian
                    </h2>
                </div>
                <div class="p-4">
                    <?php if (mysqli_num_rows($daily_sales) > 0): ?>
                        <div class="space-y-3">
                            <?php while ($daily = mysqli_fetch_assoc($daily_sales)): ?>
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="font-medium text-gray-900"><?php echo date('d/m/Y', strtotime($daily['tanggal'])); ?></p>
                                        <p class="text-sm text-gray-600"><?php echo $daily['jumlah_transaksi']; ?> transaksi</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-green-600">Rp <?php echo number_format($daily['pendapatan'], 0, ',', '.'); ?></p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-6">Tidak ada data penjualan</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Top Products -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-trophy mr-2"></i>Produk Terlaris
                    </h2>
                </div>
                <div class="p-4">
                    <?php if (mysqli_num_rows($top_products) > 0): ?>
                        <div class="space-y-3">
                            <?php $rank = 1; while ($product = mysqli_fetch_assoc($top_products)): ?>
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                            <span class="text-xs font-bold text-blue-600"><?php echo $rank++; ?></span>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 truncate"><?php echo htmlspecialchars($product['nama_produk']); ?></p>
                                            <p class="text-sm text-gray-600"><?php echo $product['total_terjual']; ?> terjual</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-green-600">Rp <?php echo number_format($product['total_pendapatan'], 0, ',', '.'); ?></p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-6">Tidak ada data produk</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Cashier Performance -->
        <div class="mt-6 bg-white rounded-lg shadow-md">
            <div class="px-4 py-3 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-users mr-2"></i>Performa Kasir
                </h2>
            </div>
            <div class="p-4">
                <?php if (mysqli_num_rows($cashier_performance) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Kasir</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Transaksi</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Penjualan</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php while ($cashier = mysqli_fetch_assoc($cashier_performance)): ?>
                                <tr>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                                                    <i class="fas fa-user text-green-600 text-sm"></i>
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($cashier['nama_lengkap']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo $cashier['jumlah_transaksi']; ?></div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm font-medium text-green-600">
                                            Rp <?php echo number_format($cashier['total_penjualan'], 0, ',', '.'); ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500 text-center py-6">Tidak ada data kasir</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
            nav { display: none; }
            .breadcrumb { display: none; }
            button { display: none; }
        }
    </style>
</body>
</html>