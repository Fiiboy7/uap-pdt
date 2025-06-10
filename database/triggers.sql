-- UPDATED TRIGGERS Implementation untuk WarungKu

-- Drop existing triggers if they exist
DROP TRIGGER IF EXISTS update_stok_after_sale;
DROP TRIGGER IF EXISTS alert_stok_menipis;
DROP TRIGGER IF EXISTS update_total_item;
DROP TRIGGER IF EXISTS log_user_activity;

-- Trigger untuk update stok otomatis setelah penjualan (UPDATED)
DELIMITER //
CREATE TRIGGER update_stok_after_sale
AFTER INSERT ON detail_transaksi
FOR EACH ROW
BEGIN
    DECLARE stok_lama INT;
    DECLARE nama_produk_var VARCHAR(100);
    
    -- Ambil stok sebelumnya dan nama produk
    SELECT stok, nama_produk INTO stok_lama, nama_produk_var 
    FROM produk WHERE id_produk = NEW.id_produk;
    
    -- Update stok produk
    UPDATE produk 
    SET stok = stok - NEW.jumlah 
    WHERE id_produk = NEW.id_produk;
    
    -- Log perubahan stok
    INSERT INTO log_stok (id_produk, tipe, jumlah, stok_sebelum, stok_sesudah, keterangan)
    VALUES (
        NEW.id_produk, 
        'keluar', 
        NEW.jumlah, 
        stok_lama, 
        stok_lama - NEW.jumlah,
        CONCAT('Penjualan - Transaksi: ', NEW.id_transaksi, ' - Produk: ', nama_produk_var)
    );
END //
DELIMITER ;

-- Trigger untuk alert stok menipis (UPDATED)
DELIMITER //
CREATE TRIGGER alert_stok_menipis
AFTER UPDATE ON produk
FOR EACH ROW
BEGIN
    -- Alert jika stok mencapai batas minimum
    IF NEW.stok <= NEW.stok_minimum AND OLD.stok > NEW.stok_minimum THEN
        INSERT INTO notifikasi (pesan, tipe)
        VALUES (
            CONCAT('PERINGATAN: Stok produk "', NEW.nama_produk, '" tinggal ', NEW.stok, ' unit (minimum: ', NEW.stok_minimum, ')'),
            'warning'
        );
    END IF;
    
    -- Alert jika stok habis
    IF NEW.stok = 0 AND OLD.stok > 0 THEN
        INSERT INTO notifikasi (pesan, tipe)
        VALUES (
            CONCAT('HABIS: Stok produk "', NEW.nama_produk, '" sudah habis!'),
            'error'
        );
    END IF;
    
    -- Alert jika stok kembali tersedia setelah habis
    IF NEW.stok > 0 AND OLD.stok = 0 THEN
        INSERT INTO notifikasi (pesan, tipe)
        VALUES (
            CONCAT('INFO: Stok produk "', NEW.nama_produk, '" kembali tersedia (', NEW.stok, ' unit)'),
            'info'
        );
    END IF;
END //
DELIMITER ;

-- Trigger untuk update total item di header transaksi (UPDATED)
DELIMITER //
CREATE TRIGGER update_total_item
AFTER INSERT ON detail_transaksi
FOR EACH ROW
BEGIN
    DECLARE total_items INT;
    
    -- Hitung total item untuk transaksi ini
    SELECT SUM(jumlah) INTO total_items
    FROM detail_transaksi 
    WHERE id_transaksi = NEW.id_transaksi;
    
    -- Update header transaksi
    UPDATE transaksi 
    SET total_item = total_items
    WHERE id_transaksi = NEW.id_transaksi;
END //
DELIMITER ;

-- Trigger untuk log aktivitas user (UPDATED)
DELIMITER //
CREATE TRIGGER log_user_activity
AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    -- Log perubahan status
    IF OLD.status != NEW.status THEN
        INSERT INTO notifikasi (pesan, tipe)
        VALUES (
            CONCAT('Status user "', NEW.nama_lengkap, '" (', NEW.username, ') diubah dari "', OLD.status, '" menjadi "', NEW.status, '"'),
            'info'
        );
    END IF;
    
    -- Log perubahan role
    IF OLD.role != NEW.role THEN
        INSERT INTO notifikasi (pesan, tipe)
        VALUES (
            CONCAT('Role user "', NEW.nama_lengkap, '" (', NEW.username, ') diubah dari "', OLD.role, '" menjadi "', NEW.role, '"'),
            'warning'
        );
    END IF;
END //
DELIMITER ;

-- Trigger untuk log penambahan produk baru
DELIMITER //
CREATE TRIGGER log_produk_baru
AFTER INSERT ON produk
FOR EACH ROW
BEGIN
    INSERT INTO notifikasi (pesan, tipe)
    VALUES (
        CONCAT('Produk baru ditambahkan: "', NEW.nama_produk, '" (', NEW.kode_produk, ') dengan stok awal ', NEW.stok, ' unit'),
        'info'
    );
    
    -- Log stok awal
    INSERT INTO log_stok (id_produk, tipe, jumlah, stok_sebelum, stok_sesudah, keterangan)
    VALUES (
        NEW.id_produk, 
        'masuk', 
        NEW.stok, 
        0, 
        NEW.stok,
        'Stok awal produk baru'
    );
END //
DELIMITER ;

-- Trigger untuk log penghapusan produk
DELIMITER //
CREATE TRIGGER log_produk_hapus
BEFORE DELETE ON produk
FOR EACH ROW
BEGIN
    INSERT INTO notifikasi (pesan, tipe)
    VALUES (
        CONCAT('Produk dihapus: "', OLD.nama_produk, '" (', OLD.kode_produk, ') dengan sisa stok ', OLD.stok, ' unit'),
        'warning'
    );
END //
DELIMITER ;
