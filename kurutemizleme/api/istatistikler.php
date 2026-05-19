<?php
require_once '../config.php';

header('Content-Type: application/json');

try {
    // Aktif Sipariş Sayısı (Teslim Edilmemiş)
    $stmt = $pdo->query("SELECT COUNT(*) as aktif_siparis FROM Siparisler WHERE guncel_durum != 'Teslim Edildi'");
    $aktif_siparis = $stmt->fetch()['aktif_siparis'];
    $stmt->closeCursor();

    // Bugün Teslim Edilecek Sipariş Sayısı
    $stmt = $pdo->query("SELECT COUNT(*) as bugun_teslim FROM Siparisler WHERE DATE(teslim_tarihi) = CURDATE()");
    $bugun_teslim = $stmt->fetch()['bugun_teslim'];
    $stmt->closeCursor();

    // Bekleyen Ödemeler Toplamı
    $stmt = $pdo->query("
        SELECT 
            COALESCE(SUM(s.toplam_tutar) - COALESCE(SUM(o.odeme_tutari), 0), 0) as bekleyen_odeme
        FROM 
            Siparisler s
            LEFT JOIN Odemeler o ON s.siparis_id = o.siparis_id
        WHERE 
            s.guncel_durum != 'Teslim Edildi'
    ");
    $bekleyen_odeme = $stmt->fetch()['bekleyen_odeme'];
    $stmt->closeCursor();

    // Toplam Müşteri Sayısı
    $stmt = $pdo->query("SELECT COUNT(*) as toplam_musteri FROM Musteriler");
    $toplam_musteri = $stmt->fetch()['toplam_musteri'];
    $stmt->closeCursor();

    echo json_encode([
        'aktif_siparis' => (int)$aktif_siparis,
        'bugun_teslim' => (int)$bugun_teslim,
        'bekleyen_odeme' => (float)$bekleyen_odeme,
        'toplam_musteri' => (int)$toplam_musteri
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Veritabanı hatası: ' . $e->getMessage()]);
}
?> 