-- STORED PROCEDURE Implementation untuk WarungKu

-- Procedure untuk tambah produk
DELIMITER //
CREATE PROCEDURE tambah_produk(
    IN p_kode VARCHAR(20),
    IN p_nama VARCHAR(100),
    IN p_kategori INT,
    IN p_harga DECIMAL(10,2),
    IN p_stok INT,
    IN p_stok_min INT,
    IN p_status VARCHAR(10),
    IN p_foto VARCHAR(255)
)
BEGIN
    INSERT INTO produk (kode_produk, nama_produk, id_kategori, harga_jual, stok, stok_minimum, status, foto_produk)
    VALUES (p_kode, p_nama, p_kategori, p_harga, p_stok, p_stok_min, p_status, p_foto);
END //
DELIMITER ;

-- Procedure untuk update produk
DELIMITER //
CREATE PROCEDURE update_produk(
    IN p_id INT,
    IN p_kode VARCHAR(20),
    IN p_nama VARCHAR(100),
    IN p_kategori INT,
    IN p_harga DECIMAL(10,2),
    IN p_stok INT,
    IN p_stok_min INT,
    IN p_status VARCHAR(10),
    IN p_foto VARCHAR(255)
)
BEGIN
    IF p_foto IS NOT NULL THEN
        UPDATE produk 
        SET kode_produk = p_kode,
            nama_produk = p_nama,
            id_kategori = p_kategori,
            harga_jual = p_harga,
            stok = p_stok,
            stok_minimum = p_stok_min,
            status = p_status,
            foto_produk = p_foto
        WHERE id_produk = p_id;
    ELSE
        UPDATE produk 
        SET kode_produk = p_kode,
            nama_produk = p_nama,
            id_kategori = p_kategori,
            harga_jual = p_harga,
            stok = p_stok,
            stok_minimum = p_stok_min,
            status = p_status
        WHERE id_produk = p_id;
    END IF;
END //
DELIMITER ;

-- Procedure untuk laporan penjualan harian
DELIMITER //
CREATE PROCEDURE laporan_penjualan_harian(IN p_tanggal DATE)
BEGIN
    SELECT 
        p.nama_produk,
        SUM(dt.jumlah) as total_terjual,
        SUM(dt.subtotal) as total_pendapatan,
        p.stok as stok_tersisa
    FROM detail_transaksi dt
    JOIN produk p ON dt.id_produk = p.id_produk
    JOIN transaksi t ON dt.id_transaksi = t.id_transaksi
    WHERE DATE(t.tanggal_transaksi) = p_tanggal
      AND t.status = 'selesai'
    GROUP BY p.id_produk
    ORDER BY total_pendapatan DESC;
END //
DELIMITER ;

-- Procedure untuk get produk by kategori
DELIMITER //
CREATE PROCEDURE get_produk_by_kategori(IN p_id_kategori INT)
BEGIN
    SELECT p.*, k.nama_kategori
    FROM produk p
    JOIN kategori k ON p.id_kategori = k.id_kategori
    WHERE p.id_kategori = p_id_kategori AND p.status = 'aktif'
    ORDER BY p.nama_produk;
END //
DELIMITER ;
