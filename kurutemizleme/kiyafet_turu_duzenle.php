<?php
require_once 'config.php';

if (!isset($_GET['id'])) {
    header('Location: kiyafet_turleri.php');
    exit();
}

$kiyafet_tur_id = $_GET['id'];

// Kıyafet Türü Güncelleme
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['guncelle'])) {
    $stmt = $pdo->prepare("CALL KiyafetTuruGuncelle(?, ?, ?, ?)");
    $stmt->execute([
        $kiyafet_tur_id,
        $_POST['ad'],
        $_POST['fiyat'],
        $_POST['bakim_notlari']
    ]);
    header('Location: kiyafet_turleri.php');
    exit();
}

// Kıyafet Türü Bilgilerini Getir
$stmt = $pdo->prepare("SELECT * FROM Kiyafet_Turleri WHERE kiyafet_tur_id = ?");
$stmt->execute([$kiyafet_tur_id]);
$kiyafet_turu = $stmt->fetch();

if (!$kiyafet_turu) {
    header('Location: kiyafet_turleri.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kıyafet Türü Düzenle - Kuru Temizleme</title>
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
            <!-- Sidebar -->
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

            <!-- Main Content -->
            <div class="col-md-10 content">
                <div class="card">
                    <div class="card-header">
                        <h5>Kıyafet Türü Düzenle</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Kıyafet Türü Adı</label>
                                <input type="text" name="ad" class="form-control" value="<?= htmlspecialchars($kiyafet_turu['ad']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fiyat (₺)</label>
                                <input type="number" name="fiyat" class="form-control" step="0.01" min="0" value="<?= htmlspecialchars($kiyafet_turu['fiyat']) ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Bakım Notları</label>
                                <textarea name="bakim_notlari" class="form-control" rows="2"><?= htmlspecialchars($kiyafet_turu['bakim_notlari']) ?></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" name="guncelle" class="btn btn-primary">Değişiklikleri Kaydet</button>
                                <a href="kiyafet_turleri.php" class="btn btn-secondary">İptal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 