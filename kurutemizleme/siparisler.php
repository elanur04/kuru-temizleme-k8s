<?php
require_once 'config.php';

// Müşterileri Getir
$stmt = $pdo->query("CALL MusterilerHepsi()");
$musteriler = $stmt->fetchAll();
$stmt->closeCursor(); // İlk sorgunun sonuç kümesini kapat

// Kıyafet Türlerini Getir
$stmt = $pdo->query("CALL KiyafetTurleriHepsi()");
$kiyafet_turleri = $stmt->fetchAll();
$stmt->closeCursor(); // İkinci sorgunun sonuç kümesini kapat

// Yeni Sipariş Oluşturma
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ekle'])) {
    try {
        // Debug için POST verilerini kontrol et
        error_log("Sipariş verileri: " . print_r($_POST, true));
        
        // Teslim tarihi kontrolü
        $teslim_tarihi = new DateTime($_POST['teslim_tarihi']);
        $bugun = new DateTime();
        
        if ($teslim_tarihi < $bugun) {
            throw new Exception("Teslim tarihi bugünden önce olamaz!");
        }

        $stmt = $pdo->prepare("CALL YeniSiparisOlustur(?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['musteri_id'],
            $_POST['teslim_tarihi'],
            $_POST['kiyafet_tur_id'],
            $_POST['adet'],
            $_POST['notlar']
        ]);
        $stmt->closeCursor();
        
        // Başarılı mesajı
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                Sipariş başarıyla oluşturuldu.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
              
    } catch (Exception $e) {
        // Hata mesajı
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                Hata: ' . htmlspecialchars($e->getMessage()) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
        error_log("Sipariş oluşturma hatası: " . $e->getMessage());
    }
}

// Sipariş Durumu Güncelleme
if (isset($_GET['siparis_id']) && isset($_GET['durum'])) {
    try {
        $stmt = $pdo->prepare("CALL SiparisDurumGuncelle(?, ?)");
        $stmt->execute([$_GET['siparis_id'], $_GET['durum']]);
        $stmt->closeCursor();
        
        // Başarılı mesajı
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                Sipariş durumu güncellendi.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
              
    } catch (Exception $e) {
        // Hata mesajı
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                Hata: ' . htmlspecialchars($e->getMessage()) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
        error_log("Sipariş durumu güncelleme hatası: " . $e->getMessage());
    }
}

// Siparişleri Listele
$stmt = $pdo->query("CALL TumSiparisDetaylariniListele()");
$siparisler = $stmt->fetchAll();
$stmt->closeCursor(); // Son sorgunun sonuç kümesini kapat
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siparişler - Kuru Temizleme</title>
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
        .status-badge {
            font-size: 0.8rem;
            padding: 0.3rem 0.6rem;
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
                <h2 class="mb-4">Sipariş Yönetimi</h2>

                <!-- Yeni Sipariş Formu -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Yeni Sipariş Oluştur</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Müşteri</label>
                                <select name="musteri_id" class="form-select" required>
                                    <option value="">Müşteri Seçin</option>
                                    <?php foreach ($musteriler as $musteri): ?>
                                        <option value="<?= $musteri['musteri_id'] ?>">
                                            <?= htmlspecialchars($musteri['ad'] . ' ' . $musteri['soyad']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Teslim Tarihi</label>
                                <input type="date" name="teslim_tarihi" class="form-control" 
                                       min="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Kıyafet Türü</label>
                                <select name="kiyafet_tur_id" class="form-select" required>
                                    <option value="">Kıyafet Türü Seçin</option>
                                    <?php foreach ($kiyafet_turleri as $tur): ?>
                                        <option value="<?= $tur['kiyafet_tur_id'] ?>">
                                            <?= htmlspecialchars($tur['ad']) ?> - <?= number_format($tur['fiyat'], 2) ?> ₺
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Adet</label>
                                <input type="number" name="adet" class="form-control" min="1" value="1" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Notlar</label>
                                <textarea name="notlar" class="form-control" rows="1"></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" name="ekle" class="btn btn-primary">Sipariş Oluştur</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Siparişler Listesi -->
                <div class="card">
                    <div class="card-header">
                        <h5>Siparişler</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Sipariş No</th>
                                        <th>Müşteri</th>
                                        <th>Kıyafet</th>
                                        <th>Adet</th>
                                        <th>Tutar</th>
                                        <th>Teslim Tarihi</th>
                                        <th>Durum</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($siparisler as $siparis): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($siparis['siparis_id']) ?></td>
                                        <td><?= htmlspecialchars($siparis['musteri_ad'] . ' ' . $siparis['musteri_soyad']) ?></td>
                                        <td><?= htmlspecialchars($siparis['kiyafet_adi']) ?></td>
                                        <td><?= htmlspecialchars($siparis['adet']) ?></td>
                                        <td><?= number_format($siparis['detay_tutar'], 2) ?> ₺</td>
                                        <td><?= date('d.m.Y', strtotime($siparis['teslim_tarihi'])) ?></td>
                                        <td>
                                            <?php
                                            $durum_renkleri = [
                                                'Alındı' => 'primary',
                                                'Temizleniyor' => 'info',
                                                'Hazır' => 'success',
                                                'Teslim Edildi' => 'secondary'
                                            ];
                                            $renk = $durum_renkleri[$siparis['guncel_durum']] ?? 'primary';
                                            ?>
                                            <span class="badge bg-<?= $renk ?> status-badge">
                                                <?= htmlspecialchars($siparis['guncel_durum']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    Durum Değiştir
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="?siparis_id=<?= $siparis['siparis_id'] ?>&durum=Alındı">Alındı</a></li>
                                                    <li><a class="dropdown-item" href="?siparis_id=<?= $siparis['siparis_id'] ?>&durum=Temizleniyor">Temizleniyor</a></li>
                                                    <li><a class="dropdown-item" href="?siparis_id=<?= $siparis['siparis_id'] ?>&durum=Hazır">Hazır</a></li>
                                                    <li><a class="dropdown-item" href="?siparis_id=<?= $siparis['siparis_id'] ?>&durum=Teslim Edildi">Teslim Edildi</a></li>
                                                </ul>
                                            </div>
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