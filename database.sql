-- ========================================================
-- KURU TEMİZLEME SİSTEMİ VERİTABANI İSKELETİ (MySQL)
-- ========================================================

-- Karakter seti ayarları (Türkçe karakter sorununu önlemek için)
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;


-- 1. TABLOLARIN OLUŞTURULMASI

-- Müşteriler Tablosu
CREATE TABLE IF NOT EXISTS `Musteriler` (
  `musteri_id` int(11) NOT NULL AUTO_INCREMENT,
  `ad` varchar(50) NOT NULL,
  `soyad` varchar(50) NOT NULL,
  `telefon` varchar(20) NOT NULL,
  `eposta` varchar(100) DEFAULT NULL,
  `adres` text DEFAULT NULL,
  PRIMARY KEY (`musteri_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Kıyafet Türleri Tablosu
CREATE TABLE IF NOT EXISTS `Kiyafet_Turleri` (
  `kiyafet_tur_id` int(11) NOT NULL AUTO_INCREMENT,
  `ad` varchar(100) NOT NULL,
  `fiyat` decimal(10,2) NOT NULL,
  `bakim_notlari` text DEFAULT NULL,
  PRIMARY KEY (`kiyafet_tur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Siparişler Tablosu
CREATE TABLE IF NOT EXISTS `Siparisler` (
  `siparis_id` int(11) NOT NULL AUTO_INCREMENT,
  `musteri_id` int(11) NOT NULL,
  `kiyafet_tur_id` int(11) NOT NULL,
  `adet` int(11) NOT NULL DEFAULT 1,
  `toplam_tutar` decimal(10,2) NOT NULL,
  `notlar` text DEFAULT NULL,
  `guncel_durum` enum('Alındı','Temizleniyor','Hazır','Teslim Edildi') DEFAULT 'Alındı',
  `teslim_tarihi` date NOT NULL,
  `siparis_tarihi` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`siparis_id`),
  FOREIGN KEY (`musteri_id`) REFERENCES `Musteriler`(`musteri_id`) ON DELETE CASCADE,
  FOREIGN KEY (`kiyafet_tur_id`) REFERENCES `Kiyafet_Turleri`(`kiyafet_tur_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ödemeler Tablosu
CREATE TABLE IF NOT EXISTS `Odemeler` (
  `odeme_id` int(11) NOT NULL AUTO_INCREMENT,
  `siparis_id` int(11) NOT NULL,
  `odeme_tutari` decimal(10,2) NOT NULL,
  `odeme_turu` enum('Nakit','Kredi Kartı','Banka Havalesi') NOT NULL,
  `odeme_tarihi` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`odeme_id`),
  FOREIGN KEY (`siparis_id`) REFERENCES `Siparisler`(`siparis_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Müşteri Düzenleme Log Tablosu
CREATE TABLE IF NOT EXISTS `Musteri_Duzenleme` (
  `duzenleme_id` int(11) NOT NULL AUTO_INCREMENT,
  `musteri_id` int(11) NOT NULL,
  `eski_ad` varchar(50) DEFAULT NULL,
  `yeni_ad` varchar(50) DEFAULT NULL,
  `eski_soyad` varchar(50) DEFAULT NULL,
  `yeni_soyad` varchar(50) DEFAULT NULL,
  `eski_telefon` varchar(20) DEFAULT NULL,
  `yeni_telefon` varchar(20) DEFAULT NULL,
  `eski_eposta` varchar(100) DEFAULT NULL,
  `yeni_eposta` varchar(100) DEFAULT NULL,
  `eski_adres` text DEFAULT NULL,
  `yeni_adres` text DEFAULT NULL,
  `duzenleme_tarihi` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`duzenleme_id`),
  FOREIGN KEY (`musteri_id`) REFERENCES `Musteriler`(`musteri_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Kıyafet Türü Düzenleme Log Tablosu
CREATE TABLE IF NOT EXISTS `Kiyafet_Turu_Duzenleme` (
  `duzenleme_id` int(11) NOT NULL AUTO_INCREMENT,
  `kiyafet_tur_id` int(11) NOT NULL,
  `eski_ad` varchar(100) DEFAULT NULL,
  `yeni_ad` varchar(100) DEFAULT NULL,
  `eski_fiyat` decimal(10,2) DEFAULT NULL,
  `yeni_fiyat` decimal(10,2) DEFAULT NULL,
  `eski_bakim_notlari` text DEFAULT NULL,
  `yeni_bakim_notlari` text DEFAULT NULL,
  `duzenleme_tarihi` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`duzenleme_id`),
  FOREIGN KEY (`kiyafet_tur_id`) REFERENCES `Kiyafet_Turleri`(`kiyafet_tur_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



-- 2. SAKLI YORDAMLARIN (STORED PROCEDURES) OLUŞTURULMASI

DELIMITER $$

-- Müşteri İşlemleri

CREATE PROCEDURE `MusteriEkle`(
    IN p_ad VARCHAR(50), 
    IN p_soyad VARCHAR(50), 
    IN p_telefon VARCHAR(20), 
    IN p_eposta VARCHAR(100), 
    IN p_adres TEXT
)
BEGIN
    INSERT INTO Musteriler (ad, soyad, telefon, eposta, adres) 
    VALUES (p_ad, p_soyad, p_telefon, p_eposta, p_adres);
END$$

CREATE PROCEDURE `MusteriSil`(
    IN p_musteri_id INT
)
BEGIN
    DELETE FROM Musteriler WHERE musteri_id = p_musteri_id;
END$$

CREATE PROCEDURE `MusteriGuncelle`(
    IN p_musteri_id INT,
    IN p_ad VARCHAR(50), 
    IN p_soyad VARCHAR(50), 
    IN p_telefon VARCHAR(20), 
    IN p_eposta VARCHAR(100), 
    IN p_adres TEXT
)
BEGIN
    UPDATE Musteriler 
    SET ad = p_ad, soyad = p_soyad, telefon = p_telefon, eposta = p_eposta, adres = p_adres 
    WHERE musteri_id = p_musteri_id;
END$$

CREATE PROCEDURE `MusterilerHepsi`()
BEGIN
    SELECT * FROM Musteriler ORDER BY musteri_id DESC;
END$$


-- Kıyafet İşlemleri 

CREATE PROCEDURE `KiyafetTuruEkle`(
    IN p_ad VARCHAR(100), 
    IN p_fiyat DECIMAL(10,2), 
    IN p_bakim_notlari TEXT
)
BEGIN
    INSERT INTO Kiyafet_Turleri (ad, fiyat, bakim_notlari) 
    VALUES (p_ad, p_fiyat, p_bakim_notlari);
END$$

CREATE PROCEDURE `KiyafetTuruSil`(
    IN p_kiyafet_tur_id INT
)
BEGIN
    DELETE FROM Kiyafet_Turleri WHERE kiyafet_tur_id = p_kiyafet_tur_id;
END$$

CREATE PROCEDURE `KiyafetTuruGuncelle`(
    IN p_kiyafet_tur_id INT,
    IN p_ad VARCHAR(100), 
    IN p_fiyat DECIMAL(10,2), 
    IN p_bakim_notlari TEXT
)
BEGIN
    UPDATE Kiyafet_Turleri 
    SET ad = p_ad, fiyat = p_fiyat, bakim_notlari = p_bakim_notlari 
    WHERE kiyafet_tur_id = p_kiyafet_tur_id;
END$$

CREATE PROCEDURE `KiyafetTurleriHepsi`()
BEGIN
    SELECT * FROM Kiyafet_Turleri ORDER BY kiyafet_tur_id DESC;
END$$


-- Sipariş İşlemleri

CREATE PROCEDURE `YeniSiparisOlustur`(
    IN p_musteri_id INT, 
    IN p_teslim_tarihi DATE, 
    IN p_kiyafet_tur_id INT, 
    IN p_adet INT, 
    IN p_notlar TEXT
)
BEGIN
    DECLARE v_birim_fiyat DECIMAL(10,2);
    DECLARE v_toplam_tutar DECIMAL(10,2);
    
    -- Önce kıyafetin birim fiyatını alıp toplam tutarı hesaplıyoruz
    SELECT fiyat INTO v_birim_fiyat FROM Kiyafet_Turleri WHERE kiyafet_tur_id = p_kiyafet_tur_id;
    SET v_toplam_tutar = v_birim_fiyat * p_adet;
    
    -- Siparişi ekliyoruz
    INSERT INTO Siparisler (musteri_id, kiyafet_tur_id, adet, toplam_tutar, notlar, teslim_tarihi, guncel_durum) 
    VALUES (p_musteri_id, p_kiyafet_tur_id, p_adet, v_toplam_tutar, p_notlar, p_teslim_tarihi, 'Alındı');
END$$

CREATE PROCEDURE `SiparisDurumGuncelle`(
    IN p_siparis_id INT, 
    IN p_durum VARCHAR(50)
)
BEGIN
    UPDATE Siparisler SET guncel_durum = p_durum WHERE siparis_id = p_siparis_id;
END$$

CREATE PROCEDURE `TumSiparisDetaylariniListele`()
BEGIN
    -- PHP tarafında beklenen spesifik kolonlar: siparis_id, musteri_ad, musteri_soyad, kiyafet_adi, adet, detay_tutar, teslim_tarihi, guncel_durum
    SELECT 
        s.siparis_id, 
        m.ad AS musteri_ad, 
        m.soyad AS musteri_soyad, 
        kt.ad AS kiyafet_adi, 
        s.adet, 
        s.toplam_tutar AS detay_tutar, 
        s.teslim_tarihi, 
        s.guncel_durum 
    FROM 
        Siparisler s 
    JOIN Musteriler m ON s.musteri_id = m.musteri_id 
    JOIN Kiyafet_Turleri kt ON s.kiyafet_tur_id = kt.kiyafet_tur_id 
    ORDER BY 
        s.siparis_tarihi DESC;
END$$


-- Ödeme İşlemleri --------------------------------------

CREATE PROCEDURE `OdemeEkle`(
    IN p_siparis_id INT, 
    IN p_odeme_tutari DECIMAL(10,2), 
    IN p_odeme_turu VARCHAR(50)
)
BEGIN
    INSERT INTO Odemeler (siparis_id, odeme_tutari, odeme_turu) 
    VALUES (p_siparis_id, p_odeme_tutari, p_odeme_turu);
END$$



-- 3. TETİKLEYİCİLERİN (TRIGGERS) OLUŞTURULMASI


-- Müşteri Güncelleme Tetikleyicisi
CREATE TRIGGER `AfterMusteriGuncelle` 
AFTER UPDATE ON `Musteriler`
FOR EACH ROW
BEGIN
    INSERT INTO `Musteri_Duzenleme` (
        musteri_id, 
        eski_ad, yeni_ad, 
        eski_soyad, yeni_soyad, 
        eski_telefon, yeni_telefon, 
        eski_eposta, yeni_eposta, 
        eski_adres, yeni_adres
    ) 
    VALUES (
        OLD.musteri_id, 
        OLD.ad, NEW.ad, 
        OLD.soyad, NEW.soyad, 
        OLD.telefon, NEW.telefon, 
        OLD.eposta, NEW.eposta, 
        OLD.adres, NEW.adres
    );
END$$

-- Kıyafet Türü Güncelleme Tetikleyicisi
CREATE TRIGGER `AfterKiyafetTuruGuncelle` 
AFTER UPDATE ON `Kiyafet_Turleri`
FOR EACH ROW
BEGIN
    INSERT INTO `Kiyafet_Turu_Duzenleme` (
        kiyafet_tur_id, 
        eski_ad, yeni_ad, 
        eski_fiyat, yeni_fiyat, 
        eski_bakim_notlari, yeni_bakim_notlari
    ) 
    VALUES (
        OLD.kiyafet_tur_id, 
        OLD.ad, NEW.ad, 
        OLD.fiyat, NEW.fiyat, 
        OLD.bakim_notlari, NEW.bakim_notlari
    );
END$$

DELIMITER ;

SET FOREIGN_KEY_CHECKS = 1;
