<?php
session_start();
include '../config/db.php';

// Check if user is kasir
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'kasir') {
    header("Location: ../index.php");
    exit;
}

// Get products
$products = mysqli_query($conn, "
    SELECT p.*, k.nama_kategori 
    FROM produk p 
    JOIN kategori k ON p.id_kategori = k.id_kategori 
    WHERE p.status = 'aktif' AND p.stok > 0
    ORDER BY p.nama_produk
");

// Get categories
$categories = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir - WarungKu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <i class="fas fa-cash-register text-2xl text-green-600"></i>
                    <h1 class="text-xl font-bold text-gray-800">WarungKu - Kasir</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600">Kasir: <?php echo $_SESSION['nama_lengkap']; ?></span>
                    <a href="../auth/logout.php" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Product List -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">
                            <i class="fas fa-shopping-bag mr-2"></i>Daftar Produk
                        </h2>
                        <div class="mt-4">
                            <input type="text" id="searchProduct" placeholder="Cari produk..." 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        </div>
                    </div>
                    <div class="p-6">
                        <div id="productList" class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-96 overflow-y-auto">
    <?php while ($product = mysqli_fetch_assoc($products)): ?>
        <div class="product-item border border-gray-200 rounded-lg p-4 hover:shadow-md transition cursor-pointer"
             onclick="addToCart(<?php echo $product['id_produk']; ?>, '<?php echo addslashes($product['nama_produk']); ?>', <?php echo $product['harga_jual']; ?>, <?php echo $product['stok']; ?>)">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <?php if (!empty($product['foto_produk']) && file_exists("../uploads/{$product['foto_produk']}")): ?>
                        <img src="../uploads/<?php echo $product['foto_produk']; ?>" 
                             alt="<?php echo htmlspecialchars($product['nama_produk']); ?>"
                             class="w-16 h-16 object-cover rounded-lg border">
                    <?php else: ?>
                        <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                            <i class="fas fa-box text-gray-400"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="flex-1">
                    <h3 class="font-medium text-gray-900"><?php echo $product['nama_produk']; ?></h3>
                    <p class="text-sm text-gray-600"><?php echo $product['nama_kategori']; ?></p>
                    <p class="text-sm text-gray-500">Stok: <?php echo $product['stok']; ?></p>
                    <p class="font-bold text-green-600">Rp <?php echo number_format($product['harga_jual'], 0, ',', '.'); ?></p>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>
                    </div>
                </div>
            </div>

            <!-- Shopping Cart -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md sticky top-4">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">
                            <i class="fas fa-shopping-cart mr-2"></i>Keranjang Belanja
                        </h2>
                    </div>
                    <div class="p-6">
                        <div id="cartItems" class="space-y-3 mb-4 max-h-64 overflow-y-auto">
                            <p class="text-gray-500 text-center py-8">Keranjang kosong</p>
                        </div>
                        
                        <!-- Totals -->
                        <div class="border-t pt-4 space-y-2">
                            <div class="flex justify-between">
                                <span>Subtotal:</span>
                                <span id="subtotal">Rp 0</span>
                            </div>
                            <div class="flex justify-between">
    <span>Diskon:</span>
    <div class="flex items-center gap-2">
        <input type="number" id="diskonPersen" value="0" min="0" max="100" step="1"
               class="w-16 px-2 py-1 border border-gray-300 rounded text-right"
               onchange="calculateTotal()">
        <span class="text-sm">%</span>
    </div>
</div>
                            <div class="flex justify-between">
                                <span>Pajak (10%):</span>
                                <span id="pajak">Rp 0</span>
                            </div>
                            <div class="flex justify-between font-bold text-lg border-t pt-2">
                                <span>Total:</span>
                                <span id="total">Rp 0</span>
                            </div>
                        </div>

                        <!-- Payment -->
                        <div class="mt-4 space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Bayar:</label>
                                <input type="number" id="jumlahBayar" min="0" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                                       onchange="calculateChange()">
                            </div>
                            <div class="flex justify-between font-medium">
                                <span>Kembalian:</span>
                                <span id="kembalian" class="text-green-600">Rp 0</span>
                            </div>
                            <button onclick="processTransaction()" 
                                    class="w-full bg-green-600 text-white py-3 px-4 rounded-lg hover:bg-green-700 transition font-medium">
                                <i class="fas fa-credit-card mr-2"></i>Proses Transaksi
                            </button>
                            <button onclick="clearCart()" 
                                    class="w-full bg-red-500 text-white py-2 px-4 rounded-lg hover:bg-red-600 transition">
                                <i class="fas fa-trash mr-2"></i>Kosongkan Keranjang
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let cart = [];
        let subtotal = 0;
        let pajak = 0;
        let total = 0;

        function addToCart(id, nama, harga, stok) {
            const existingItem = cart.find(item => item.id === id);
            
            if (existingItem) {
                if (existingItem.qty < stok) {
                    existingItem.qty++;
                } else {
                    alert('Stok tidak mencukupi!');
                    return;
                }
            } else {
                cart.push({
                    id: id,
                    nama: nama,
                    harga: harga,
                    qty: 1,
                    stok: stok
                });
            }
            
            updateCartDisplay();
            calculateTotal();
        }

        function removeFromCart(id) {
            cart = cart.filter(item => item.id !== id);
            updateCartDisplay();
            calculateTotal();
        }

        function updateQuantity(id, qty) {
            const item = cart.find(item => item.id === id);
            if (item) {
                if (qty > 0 && qty <= item.stok) {
                    item.qty = qty;
                } else if (qty <= 0) {
                    removeFromCart(id);
                    return;
                } else {
                    alert('Stok tidak mencukupi!');
                    return;
                }
            }
            updateCartDisplay();
            calculateTotal();
        }

        function updateCartDisplay() {
            const cartItems = document.getElementById('cartItems');
            
            if (cart.length === 0) {
                cartItems.innerHTML = '<p class="text-gray-500 text-center py-8">Keranjang kosong</p>';
                return;
            }

            cartItems.innerHTML = cart.map(item => `
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex-1">
                        <h4 class="font-medium text-sm">${item.nama}</h4>
                        <p class="text-xs text-gray-600">Rp ${item.harga.toLocaleString()}</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button onclick="updateQuantity(${item.id}, ${item.qty - 1})" 
                                class="w-6 h-6 bg-red-500 text-white rounded-full text-xs hover:bg-red-600">-</button>
                        <span class="w-8 text-center text-sm">${item.qty}</span>
                        <button onclick="updateQuantity(${item.id}, ${item.qty + 1})" 
                                class="w-6 h-6 bg-green-500 text-white rounded-full text-xs hover:bg-green-600">+</button>
                        <button onclick="removeFromCart(${item.id})" 
                                class="w-6 h-6 bg-red-500 text-white rounded-full text-xs hover:bg-red-600">×</button>
                    </div>
                </div>
            `).join('');
        }

        function calculateTotal() {
    subtotal = cart.reduce((sum, item) => sum + (item.harga * item.qty), 0);
    const diskonPersen = parseFloat(document.getElementById('diskonPersen').value) || 0;
    const diskonNominal = (subtotal * diskonPersen) / 100;
    pajak = (subtotal - diskonNominal) * 0.1;
    total = subtotal - diskonNominal + pajak;

    document.getElementById('subtotal').textContent = 'Rp ' + subtotal.toLocaleString();
    document.getElementById('pajak').textContent = 'Rp ' + Math.round(pajak).toLocaleString();
    document.getElementById('total').textContent = 'Rp ' + Math.round(total).toLocaleString();
    
    calculateChange();
}

        function calculateChange() {
            const jumlahBayar = parseFloat(document.getElementById('jumlahBayar').value) || 0;
            const kembalian = jumlahBayar - total;
            document.getElementById('kembalian').textContent = 'Rp ' + Math.max(0, kembalian).toLocaleString();
        }

        function clearCart() {
            cart = [];
            updateCartDisplay();
            calculateTotal();
            document.getElementById('jumlahBayar').value = '';
            document.getElementById('diskonPersen').value = '0';
        }

        function processTransaction() {
            if (cart.length === 0) {
                alert('Keranjang masih kosong!');
                return;
            }

            const jumlahBayar = parseFloat(document.getElementById('jumlahBayar').value) || 0;
            if (jumlahBayar < total) {
                alert('Jumlah bayar kurang!');
                return;
            }

            // Send transaction data to server
            const transactionData = {
                items: cart,
                subtotal: subtotal,
                diskon_persen: parseFloat(document.getElementById('diskonPersen').value) || 0,
                diskon_nominal: (subtotal * (parseFloat(document.getElementById('diskonPersen').value) || 0)) / 100,
                pajak: Math.round(pajak),
                total: Math.round(total),
                jumlah_bayar: jumlahBayar,
                kembalian: jumlahBayar - total
            };

            fetch('proses_transaksi.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(transactionData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Transaksi berhasil!');
                    // Open receipt in new window
                    window.open('cetak_struk.php?id=' + data.transaction_id, '_blank');
                    clearCart();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memproses transaksi');
            });
        }

        // Search functionality
        document.getElementById('searchProduct').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const productItems = document.querySelectorAll('.product-item');
            
            productItems.forEach(item => {
                const productName = item.querySelector('h3').textContent.toLowerCase();
                if (productName.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
