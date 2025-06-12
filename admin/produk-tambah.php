<?php
session_start();
include '../config/db.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $kode_produk = mysqli_real_escape_string($conn, $_POST['kode_produk']);
    $nama_produk = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $id_kategori = intval($_POST['id_kategori']);
    $harga_jual = floatval($_POST['harga_jual']);
    $stok = intval($_POST['stok']);
    $stok_minimum = intval($_POST['stok_minimum']);
    $status = $_POST['status'];
    
    // Check if product code already exists
    $check_query = "SELECT * FROM produk WHERE kode_produk = '$kode_produk'";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $_SESSION['produk_error'] = "Kode produk sudah digunakan.";
    } else {
        // Handle file upload
        $foto_produk = null;
        if (!empty($_FILES['foto_produk']['name'])) {
            $upload_dir = '../uploads/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['foto_produk']['name'], PATHINFO_EXTENSION);
            $foto_produk = time() . '_' . uniqid() . '.' . $file_extension;
            
            if (move_uploaded_file($_FILES['foto_produk']['tmp_name'], $upload_dir . $foto_produk)) {
                // File uploaded successfully
            } else {
                $foto_produk = null;
                $_SESSION['produk_error'] = "Gagal mengupload foto produk.";
            }
        }
        
        // Use stored procedure to add product
        $query = "CALL tambah_produk('$kode_produk', '$nama_produk', $id_kategori, $harga_jual, $stok, $stok_minimum, '$status', " . ($foto_produk ? "'$foto_produk'" : "NULL") . ")";
        
        if (mysqli_query($conn, $query)) {
            $_SESSION['produk_success'] = "Produk berhasil ditambahkan!";
            header("Location: produk.php");
            exit;
        } else {
            $_SESSION['produk_error'] = "Gagal menambahkan produk: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk - WarungKu</title>
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
            <a href="produk.php" class="text-blue-600 hover:text-blue-800">Kelola Produk</a>
            <span class="mx-2">/</span>
            <span class="text-gray-600">Tambah Produk</span>
        </div>

        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Tambah Produk Baru</h1>
            <p class="text-gray-600">Isi form berikut untuk menambahkan produk baru ke inventaris</p>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <?php
            if (isset($_SESSION['produk_error'])) {
                echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <i class="fas fa-exclamation-circle mr-2"></i>' . $_SESSION['produk_error'] . '
                      </div>';
                unset($_SESSION['produk_error']);
            }
            ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="kode_produk" class="block text-sm font-medium text-gray-700 mb-1">Kode Produk *</label>
                        <input type="text" id="kode_produk" name="kode_produk" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Kode unik untuk produk (contoh: PRD001)</p>
                    </div>
                    
                    <div>
                        <label for="nama_produk" class="block text-sm font-medium text-gray-700 mb-1">Nama Produk *</label>
                        <input type="text" id="nama_produk" name="nama_produk" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label for="id_kategori" class="block text-sm font-medium text-gray-700 mb-1">Kategori *</label>
                        <select id="id_kategori" name="id_kategori" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Pilih Kategori</option>
                            <?php
                            $kategori_query = "SELECT * FROM kategori ORDER BY nama_kategori";
                            $kategori_result = mysqli_query($conn, $kategori_query);
                            while ($kategori = mysqli_fetch_assoc($kategori_result)) {
                                echo "<option value='{$kategori['id_kategori']}'>{$kategori['nama_kategori']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="harga_jual" class="block text-sm font-medium text-gray-700 mb-1">Harga Jual (Rp) *</label>
                        <input type="number" id="harga_jual" name="harga_jual" min="0" step="100" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label for="stok" class="block text-sm font-medium text-gray-700 mb-1">Stok Awal *</label>
                        <input type="number" id="stok" name="stok" min="0" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label for="stok_minimum" class="block text-sm font-medium text-gray-700 mb-1">Stok Minimum *</label>
                        <input type="number" id="stok_minimum" name="stok_minimum" min="0" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Batas minimum stok sebelum notifikasi peringatan</p>
                    </div>
                    
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                        <select id="status" name="status" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="foto_produk" class="block text-sm font-medium text-gray-700 mb-1">Foto Produk</label>
                        <input type="file" id="foto_produk" name="foto_produk" accept="image/*"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, GIF (Max: 2MB)</p>
                    </div>
                    
                    <div class="md:col-span-2">
                        <div id="imagePreview" class="hidden mt-2">
                            <p class="text-sm font-medium text-gray-700 mb-1">Preview:</p>
                            <img id="preview" src="#" alt="Preview" class="h-40 object-contain border rounded-lg">
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 flex items-center justify-end gap-3">
                    <a href="produk.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        <i class="fas fa-times mr-2"></i>Batal
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-save mr-2"></i>Simpan Produk
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Image preview
        document.getElementById('foto_produk').addEventListener('change', function(e) {
            const preview = document.getElementById('preview');
            const imagePreview = document.getElementById('imagePreview');
            
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    imagePreview.classList.remove('hidden');
                }
                
                reader.readAsDataURL(e.target.files[0]);
            } else {
                imagePreview.classList.add('hidden');
            }
        });
        
        // Auto generate product code
        document.getElementById('nama_produk').addEventListener('blur', function() {
            const kodeInput = document.getElementById('kode_produk');
            if (kodeInput.value === '') {
                const namaProduk = this.value.trim();
                if (namaProduk) {
                    // Generate simple code from first 3 chars + timestamp
                    const prefix = namaProduk.substring(0, 3).toUpperCase();
                    const timestamp = new Date().getTime().toString().substring(9, 13);
                    kodeInput.value = `${prefix}${timestamp}`;
                }
            }
        });
    </script>
</body>
</html>
