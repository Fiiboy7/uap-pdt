<?php
session_start();
include '../config/db.php';

// Check if user is kasir
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'kasir') {
    header("Location: ../index.php");
    exit;
}

$transaction_id = intval($_GET['id']);

// Get transaction data
$transaction_query = "
    SELECT t.*, u.nama_lengkap as nama_kasir 
    FROM transaksi t 
    JOIN users u ON t.id_kasir = u.id_user 
    WHERE t.id_transaksi = $transaction_id
";
$transaction_result = mysqli_query($conn, $transaction_query);
$transaction = mysqli_fetch_assoc($transaction_result);

if (!$transaction) {
    echo "Transaksi tidak ditemukan";
    exit;
}

// Get transaction details
$details_query = "
    SELECT dt.*, p.nama_produk, p.kode_produk 
    FROM detail_transaksi dt 
    JOIN produk p ON dt.id_produk = p.id_produk 
    WHERE dt.id_transaksi = $transaction_id
";
$details_result = mysqli_query($conn, $details_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk - <?php echo $transaction['no_transaksi']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body class="bg-white">
    <div class="max-w-sm mx-auto p-4">
        <!-- Header -->
        <div class="text-center mb-4">
            <h1 class="text-xl font-bold">WARUNGKU</h1>
            <p class="text-sm">Sistem Kasir Digital</p>
            <p class="text-xs">Jl. Contoh No. 123, Kota</p>
            <p class="text-xs">Telp: 0812-3456-7890</p>
        </div>

        <div class="border-t border-b border-dashed py-2 mb-4">
            <div class="flex justify-between text-sm">
                <span>No. Transaksi:</span>
                <span><?php echo $transaction['no_transaksi']; ?></span>
            </div>
            <div class="flex justify-between text-sm">
                <span>Tanggal:</span>
                <span><?php echo date('d/m/Y H:i', strtotime($transaction['tanggal_transaksi'])); ?></span>
            </div>
            <div class="flex justify-between text-sm">
                <span>Kasir:</span>
                <span><?php echo $transaction['nama_kasir']; ?></span>
            </div>
        </div>

        <!-- Items -->
        <div class="mb-4">
            <?php while ($detail = mysqli_fetch_assoc($details_result)): ?>
                <div class="flex justify-between text-sm mb-1">
                    <div class="flex-1">
                        <div><?php echo $detail['nama_produk']; ?></div>
                        <div class="text-xs text-gray-600">
                            <?php echo $detail['jumlah']; ?> x Rp <?php echo number_format($detail['harga_satuan'], 0, ',', '.'); ?>
                        </div>
                    </div>
                    <div class="text-right">
                        Rp <?php echo number_format($detail['subtotal'], 0, ',', '.'); ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Totals -->
        <div class="border-t border-dashed pt-2 mb-4">
            <div class="flex justify-between text-sm">
                <span>Subtotal:</span>
                <span>Rp <?php echo number_format($transaction['subtotal'], 0, ',', '.'); ?></span>
            </div>
            <?php if ($transaction['diskon'] > 0): ?>
                <div class="flex justify-between text-sm">
                    <span>Diskon:</span>
                    <span>Rp <?php echo number_format($transaction['diskon'], 0, ',', '.'); ?></span>
                </div>
            <?php endif; ?>
            <div class="flex justify-between text-sm">
                <span>Pajak (10%):</span>
                <span>Rp <?php echo number_format($transaction['pajak'], 0, ',', '.'); ?></span>
            </div>
            <div class="flex justify-between font-bold border-t mt-1 pt-1">
                <span>TOTAL:</span>
                <span>Rp <?php echo number_format($transaction['total_bayar'], 0, ',', '.'); ?></span>
            </div>
            <div class="flex justify-between text-sm">
                <span>Bayar:</span>
                <span>Rp <?php echo number_format($transaction['jumlah_bayar'], 0, ',', '.'); ?></span>
            </div>
            <div class="flex justify-between text-sm">
                <span>Kembalian:</span>
                <span>Rp <?php echo number_format($transaction['kembalian'], 0, ',', '.'); ?></span>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center text-xs">
            <p>Terima kasih atas kunjungan Anda!</p>
            <p>Barang yang sudah dibeli tidak dapat dikembalikan</p>
        </div>

        <!-- Print Button -->
        <div class="no-print mt-4 text-center">
            <button onclick="window.print()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Cetak Struk
            </button>
            <button onclick="window.close()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 ml-2">
                Tutup
            </button>
        </div>
    </div>

    <script>
        // Auto print when page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>
</body>
</html>
