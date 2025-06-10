-- FUNCTION Implementation untuk WarungKu

-- Function untuk generate nomor transaksi
DELIMITER //
CREATE FUNCTION generate_no_transaksi()
RETURNS VARCHAR(20)
DETERMINISTIC
BEGIN
    DECLARE no_urut INT;
    DECLARE hasil VARCHAR(20);
    
    SELECT COALESCE(MAX(CAST(SUBSTRING(no_transaksi, 9) AS UNSIGNED)), 0) + 1 
    INTO no_urut
    FROM transaksi 
    WHERE DATE(tanggal_transaksi) = CURDATE();
    
    SET hasil = CONCAT('TRX', DATE_FORMAT(NOW(), '%y%m%d'), LPAD(no_urut, 3, '0'));
    
    RETURN hasil;
END //
DELIMITER ;

-- Function untuk hitung pajak
DELIMITER //
CREATE FUNCTION hitung_pajak(subtotal DECIMAL(10,2), diskon DECIMAL(10,2))
RETURNS DECIMAL(10,2)
DETERMINISTIC
BEGIN
    DECLARE total_setelah_diskon DECIMAL(10,2);
    DECLARE pajak DECIMAL(10,2);
    
    SET total_setelah_diskon = subtotal - diskon;
    SET pajak = total_setelah_diskon * 0.10; -- PPN 10%
    
    RETURN pajak;
END //
DELIMITER ;

-- Function untuk validasi stok
DELIMITER //
CREATE FUNCTION cek_stok_tersedia(p_id_produk INT, p_jumlah INT)
RETURNS VARCHAR(20)
DETERMINISTIC
BEGIN
    DECLARE stok_sekarang INT;
    
    SELECT stok INTO stok_sekarang 
    FROM produk 
    WHERE id_produk = p_id_produk;
    
    IF stok_sekarang >= p_jumlah THEN
        RETURN 'TERSEDIA';
    ELSE
        RETURN 'TIDAK_CUKUP';
    END IF;
END //
DELIMITER ;

-- Function untuk format rupiah
DELIMITER //
CREATE FUNCTION format_rupiah(nominal DECIMAL(10,2))
RETURNS VARCHAR(50)
DETERMINISTIC
BEGIN
    RETURN CONCAT('Rp ', FORMAT(nominal, 0));
END //
DELIMITER ;
