<?php
require_once 'config.php';

// Müşteri Ekleme
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ekle'])) {
    $stmt = $pdo->prepare("CALL MusteriEkle(?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['ad'],
        $_POST['soyad'],
        $_POST['telefon'],
        $_POST['eposta'],
        $_POST['adres']
    ]);
    $stmt->closeCursor(); // Ekleme sorgusu sonuç kümesini kapat
}

// Müşteri Silme
if (isset($_GET['sil'])) {
    $stmt = $pdo->prepare("CALL MusteriSil(?)");
    $stmt->execute([$_GET['sil']]);
    $stmt->closeCursor(); // Silme sorgusu sonuç kümesini kapat
}

// Müşterileri Listele
$stmt = $pdo->query("CALL MusterilerHepsi()");
$musteriler = $stmt->fetchAll();
$stmt->closeCursor(); // Listeleme sorgusu sonuç kümesini kapat
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Müşteri Yönetimi - Kuru Temizleme</title>
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
                <h2 class="mb-4">Müşteri Yönetimi</h2>

                <!-- Müşteri Ekleme Formu -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Yeni Müşteri Ekle</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Ad</label>
                                <input type="text" name="ad" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Soyad</label>
                                <input type="text" name="soyad" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Telefon</label>
                                <input type="tel" name="telefon" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">E-posta</label>
                                <input type="email" name="eposta" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Adres</label>
                                <textarea name="adres" class="form-control" rows="1"></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" name="ekle" class="btn btn-primary">Müşteri Ekle</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Müşteri Listesi -->
                <div class="card">
                    <div class="card-header">
                        <h5>Müşteri Listesi</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Ad</th>
                                        <th>Soyad</th>
                                        <th>Telefon</th>
                                        <th>E-posta</th>
                                        <th>Adres</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($musteriler as $musteri): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($musteri['musteri_id']) ?></td>
                                        <td><?= htmlspecialchars($musteri['ad']) ?></td>
                                        <td><?= htmlspecialchars($musteri['soyad']) ?></td>
                                        <td><?= htmlspecialchars($musteri['telefon']) ?></td>
                                        <td><?= htmlspecialchars($musteri['eposta']) ?></td>
                                        <td><?= htmlspecialchars($musteri['adres']) ?></td>
                                        <td>
                                            <a href="musteri_duzenle.php?id=<?= $musteri['musteri_id'] ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?sil=<?= $musteri['musteri_id'] ?>" class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Bu müşteriyi silmek istediğinizden emin misiniz?')">
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