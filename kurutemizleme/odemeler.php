<?php
require_once 'config.php';

// Ödenmemiş Siparişleri Getir
$stmt = $pdo->query("
    SELECT 
        s.siparis_id,
        m.ad as musteri_ad,
        m.soyad as musteri_soyad,
        s.siparis_tarihi,
        s.teslim_tarihi,
        s.toplam_tutar as siparis_tutari,
        COALESCE(SUM(o.odeme_tutari), 0) as odenen_tutar,
        (s.toplam_tutar - COALESCE(SUM(o.odeme_tutari), 0)) as kalan_tutar
    FROM 
        Siparisler s
        JOIN Musteriler m ON s.musteri_id = m.musteri_id
        LEFT JOIN Odemeler o ON s.siparis_id = o.siparis_id
    GROUP BY 
        s.siparis_id
    HAVING 
        kalan_tutar > 0
    ORDER BY 
        s.siparis_tarihi DESC
");
$odenmemis_siparisler = $stmt->fetchAll();
$stmt->closeCursor();

// Ödeme Ekleme
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ekle'])) {
    try {
        // Ödeme tutarı kontrolü
        $stmt = $pdo->prepare("
            SELECT 
                s.toplam_tutar,
                COALESCE(SUM(o.odeme_tutari), 0) as odenen_tutar
            FROM 
                Siparisler s
                LEFT JOIN Odemeler o ON s.siparis_id = o.siparis_id
            WHERE 
                s.siparis_id = ?
            GROUP BY 
                s.siparis_id
        ");
        $stmt->execute([$_POST['siparis_id']]);
        $siparis_bilgisi = $stmt->fetch();
        $stmt->closeCursor();

        $kalan_tutar = $siparis_bilgisi['toplam_tutar'] - $siparis_bilgisi['odenen_tutar'];
        
        if ($_POST['odeme_tutari'] > $kalan_tutar) {
            throw new Exception("Ödeme tutarı kalan tutardan büyük olamaz! Kalan tutar: " . number_format($kalan_tutar, 2) . " ₺");
        }

        $stmt = $pdo->prepare("CALL OdemeEkle(?, ?, ?)");
        $stmt->execute([
            $_POST['siparis_id'],
            $_POST['odeme_tutari'],
            $_POST['odeme_turu']
        ]);
        $stmt->closeCursor();

        // Başarılı mesajı
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                Ödeme başarıyla kaydedildi.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';

    } catch (Exception $e) {
        // Hata mesajı
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                Hata: ' . htmlspecialchars($e->getMessage()) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
    }
}

// Ödemeleri Listele
$stmt = $pdo->query("
    SELECT 
        o.*,
        m.ad as musteri_ad,
        m.soyad as musteri_soyad,
        s.toplam_tutar as siparis_tutari
    FROM 
        Odemeler o
        JOIN Siparisler s ON o.siparis_id = s.siparis_id
        JOIN Musteriler m ON s.musteri_id = m.musteri_id
    ORDER BY 
        o.odeme_tarihi DESC
");
$odemeler = $stmt->fetchAll();
$stmt->closeCursor();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ödemeler - Kuru Temizleme</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            display: block;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .content {
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar p-0">
                <div class="p-3 text-white">
                    <h4>Kuru Temizleme</h4>
                </div>
                <a href="index.php"><i class="fas fa-home me-2"></i> Ana Sayfa</a>
                <a href="musteriler.php"><i class="fas fa-users me-2"></i> Müşteriler</a>
                <a href="kiyafet_turleri.php"><i class="fas fa-tshirt me-2"></i> Kıyafet Türleri</a>
                <a href="siparisler.php"><i class="fas fa-shopping-cart me-2"></i> Siparişler</a>
                <a href="odemeler.php"><i class="fas fa-money-bill me-2"></i> Ödemeler</a>
            </div>

            <div class="col-md-10 content">
                <h2 class="mb-4">Ödeme Yönetimi</h2>

                <!-- Yeni Ödeme Formu -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Yeni Ödeme Ekle</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Ödenmemiş Siparişler</label>
                                <select name="siparis_id" class="form-select" required>
                                    <option value="">Sipariş Seçin</option>
                                    <?php foreach ($odenmemis_siparisler as $siparis): ?>
                                        <option value="<?= $siparis['siparis_id'] ?>">
                                            #<?= $siparis['siparis_id'] ?> - 
                                            <?= htmlspecialchars($siparis['musteri_ad'] . ' ' . $siparis['musteri_soyad']) ?> - 
                                            Kalan: <?= number_format($siparis['kalan_tutar'], 2) ?> ₺
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Ödeme Tutarı (₺)</label>
                                <input type="number" name="odeme_tutari" class="form-control" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Ödeme Türü</label>
                                <select name="odeme_turu" class="form-select" required>
                                    <option value="">Ödeme Türü Seçin</option>
                                    <option value="Nakit">Nakit</option>
                                    <option value="Kredi Kartı">Kredi Kartı</option>
                                    <option value="Banka Havalesi">Banka Havalesi</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" name="ekle" class="btn btn-primary">Ödeme Ekle</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Ödemeler Listesi -->
                <div class="card">
                    <div class="card-header">
                        <h5>Ödemeler</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Ödeme No</th>
                                        <th>Sipariş No</th>
                                        <th>Müşteri</th>
                                        <th>Sipariş Tutarı</th>
                                        <th>Ödeme Tutarı</th>
                                        <th>Ödeme Tarihi</th>
                                        <th>Ödeme Türü</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($odemeler as $odeme): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($odeme['odeme_id']) ?></td>
                                        <td><?= htmlspecialchars($odeme['siparis_id']) ?></td>
                                        <td><?= htmlspecialchars($odeme['musteri_ad'] . ' ' . $odeme['musteri_soyad']) ?></td>
                                        <td><?= number_format($odeme['siparis_tutari'], 2) ?> ₺</td>
                                        <td><?= number_format($odeme['odeme_tutari'], 2) ?> ₺</td>
                                        <td><?= date('d.m.Y H:i', strtotime($odeme['odeme_tarihi'])) ?></td>
                                        <td>
                                            <?php
                                            $odeme_renkleri = [
                                                'Nakit' => 'success',
                                                'Kredi Kartı' => 'info',
                                                'Banka Havalesi' => 'primary'
                                            ];
                                            $renk = $odeme_renkleri[$odeme['odeme_turu']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?= $renk ?>">
                                                <?= htmlspecialchars($odeme['odeme_turu']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 