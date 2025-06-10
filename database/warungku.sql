-- Database WarungKu POS System
CREATE DATABASE IF NOT EXISTS warungku;
USE warungku;

-- Tabel Users (Login System)
CREATE TABLE users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'kasir') NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Kategori
CREATE TABLE kategori (
    id_kategori INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(50) NOT NULL,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Produk
CREATE TABLE produk (
    id_produk INT AUTO_INCREMENT PRIMARY KEY,
    kode_produk VARCHAR(20) UNIQUE NOT NULL,
    nama_produk VARCHAR(100) NOT NULL,
    id_kategori INT,
    harga_jual DECIMAL(10,2) NOT NULL,
    stok INT DEFAULT 0,
    stok_minimum INT DEFAULT 5,
    foto_produk VARCHAR(255) NULL AFTER stok_minimum,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_kategori) REFERENCES kategori(id_kategori)
);

-- Tabel Transaksi (Header)
CREATE TABLE transaksi (
    id_transaksi INT AUTO_INCREMENT PRIMARY KEY,
    no_transaksi VARCHAR(20) UNIQUE NOT NULL,
    id_kasir INT NOT NULL,
    tanggal_transaksi DATETIME DEFAULT CURRENT_TIMESTAMP,
    total_item INT DEFAULT 0,
    subtotal DECIMAL(10,2) DEFAULT 0,
    diskon DECIMAL(10,2) DEFAULT 0,
    pajak DECIMAL(10,2) DEFAULT 0,
    total_bayar DECIMAL(10,2) NOT NULL,
    jumlah_bayar DECIMAL(10,2) NOT NULL,
    kembalian DECIMAL(10,2) DEFAULT 0,
    status ENUM('pending', 'selesai', 'batal') DEFAULT 'selesai',
    FOREIGN KEY (id_kasir) REFERENCES users(id_user)
);

-- Tabel Detail Transaksi
CREATE TABLE detail_transaksi (
    id_detail INT AUTO_INCREMENT PRIMARY KEY,
    id_transaksi INT NOT NULL,
    id_produk INT NOT NULL,
    jumlah INT NOT NULL,
    harga_satuan DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_transaksi) REFERENCES transaksi(id_transaksi) ON DELETE CASCADE,
    FOREIGN KEY (id_produk) REFERENCES produk(id_produk)
);

-- Tabel Notifikasi
CREATE TABLE notifikasi (
    id_notifikasi INT AUTO_INCREMENT PRIMARY KEY,
    pesan TEXT NOT NULL,
    tipe ENUM('info', 'warning', 'error') DEFAULT 'info',
    status ENUM('baru', 'dibaca') DEFAULT 'baru',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Log Stok
CREATE TABLE log_stok (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    id_produk INT NOT NULL,
    tipe ENUM('masuk', 'keluar', 'adjust') NOT NULL,
    jumlah INT NOT NULL,
    stok_sebelum INT NOT NULL,
    stok_sesudah INT NOT NULL,
    keterangan VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_produk) REFERENCES produk(id_produk)
);

-- Insert data awal
INSERT INTO users (username, password, role, nama_lengkap) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Administrator'),
('kasir1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'kasir', 'Kasir Warung');

INSERT INTO kategori (nama_kategori, deskripsi) VALUES
('Makanan', 'Produk makanan dan snack'),
('Minuman', 'Minuman segar dan kemasan'),
('Rokok', 'Produk rokok dan tembakau'),
('Perlengkapan', 'Barang kebutuhan sehari-hari');

INSERT INTO produk (kode_produk, nama_produk, id_kategori, harga_jual, stok, stok_minimum) VALUES
('PRD001', 'Indomie Goreng', 1, 3500, 50, 10),
('PRD002', 'Aqua 600ml', 2, 3000, 30, 5),
('PRD003', 'Gudang Garam Merah', 3, 20000, 20, 3),
('PRD004', 'Beras 5kg', 4, 65000, 15, 2),
('PRD005', 'Teh Botol Sosro', 2, 4000, 25, 5);
