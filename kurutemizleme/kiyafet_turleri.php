<?php
require_once 'config.php';

// Kıyafet Türü Ekleme
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ekle'])) {
    $stmt = $pdo->prepare("CALL KiyafetTuruEkle(?, ?, ?)");
    $stmt->execute([
        $_POST['ad'],
        $_POST['fiyat'],
        $_POST['bakim_notlari']
    ]);
    $stmt->closeCursor(); // Ekleme sorgusu sonuç kümesini kapat
}

// Kıyafet Türü Silme
$error_msg = null;
if (isset($_GET['sil'])) {
    try {
        $stmt = $pdo->prepare("CALL KiyafetTuruSil(?)");
        $stmt->execute([$_GET['sil']]);
        $stmt->closeCursor(); // Silme sorgusu sonuç kümesini kapat
        header('Location: kiyafet_turleri.php');
        exit();
    } catch (PDOException $e) {
        if ($e->getCode() == '23000') {
            $error_msg = "Bu kıyafet türüne ait sipariş kayıtları bulunmaktadır. Önce ilişkili siparişleri silmeniz veya değiştirmeniz gerekmektedir.";
        } else {
            $error_msg = "Silme işlemi sırasında bir hata oluştu: " . $e->getMessage();
        }
    }
}

// Kıyafet Türlerini Listele
$stmt = $pdo->query("CALL KiyafetTurleriHepsi()");
$kiyafet_turleri = $stmt->fetchAll();
$stmt->closeCursor(); // Listeleme sorgusu sonuç kümesini kapat
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kıyafet Türleri - Kuru Temizleme</title>
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
                <h2 class="mb-4">Kıyafet Türleri Yönetimi</h2>

                <?php if ($error_msg): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($error_msg) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Kıyafet Türü Ekleme Formu -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Yeni Kıyafet Türü Ekle</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Kıyafet Türü Adı</label>
                                <input type="text" name="ad" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fiyat (₺)</label>
                                <input type="number" name="fiyat" class="form-control" step="0.01" min="0" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Bakım Notları</label>
                                <textarea name="bakim_notlari" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" name="ekle" class="btn btn-primary">Kıyafet Türü Ekle</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Kıyafet Türleri Listesi -->
                <div class="card">
                    <div class="card-header">
                        <h5>Kıyafet Türleri Listesi</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Kıyafet Türü</th>
                                        <th>Fiyat</th>
                                        <th>Bakım Notları</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($kiyafet_turleri as $tur): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($tur['kiyafet_tur_id']) ?></td>
                                        <td><?= htmlspecialchars($tur['ad']) ?></td>
                                        <td><?= number_format($tur['fiyat'], 2) ?> ₺</td>
                                        <td><?= htmlspecialchars($tur['bakim_notlari']) ?></td>
                                        <td>
                                            <a href="kiyafet_turu_duzenle.php?id=<?= $tur['kiyafet_tur_id'] ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?sil=<?= $tur['kiyafet_tur_id'] ?>" class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Bu kıyafet türünü silmek istediğinizden emin misiniz?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
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