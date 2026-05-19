<?php
require_once '../config.php';

header('Content-Type: application/json');

try {
    // Son 10 Siparişi Getir
    $stmt = $pdo->query("
        SELECT 
            s.siparis_id,
            m.ad as musteri_ad,
            m.soyad as musteri_soyad,
            s.teslim_tarihi,
            s.toplam_tutar,
            s.guncel_durum
        FROM 
            Siparisler s
            JOIN Musteriler m ON s.musteri_id = m.musteri_id
        ORDER BY 
            s.siparis_tarihi DESC
        LIMIT 10
    ");
    
    $siparisler = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor(); // Sorgu sonuç kümesini kapat
    
    echo json_encode($siparisler);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Veritabanı hatası: ' . $e->getMessage()]);
}
?> 