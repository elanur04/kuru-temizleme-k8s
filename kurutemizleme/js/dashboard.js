// Dashboard istatistiklerini güncelleme fonksiyonu
function guncelleIstatistikler() {
    fetch('api/istatistikler.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('aktifSiparisSayisi').textContent = data.aktif_siparis || 0;
            document.getElementById('bugunTeslimSayisi').textContent = data.bugun_teslim || 0;
            document.getElementById('bekleyenOdemeler').textContent = (data.bekleyen_odeme || 0).toFixed(2) + ' ₺';
            document.getElementById('toplamMusteri').textContent = data.toplam_musteri || 0;
        })
        .catch(error => console.error('İstatistikler yüklenirken hata oluştu:', error));
}

// Son siparişleri güncelleme fonksiyonu
function guncelleSonSiparisler() {
    fetch('api/son_siparisler.php')
        .then(response => response.json())
        .then(siparisler => {
            const tbody = document.getElementById('sonSiparislerTablosu');
            tbody.innerHTML = '';

            siparisler.forEach(siparis => {
                const durum_renkleri = {
                    'Alındı': 'primary',
                    'Temizleniyor': 'info',
                    'Hazır': 'success',
                    'Teslim Edildi': 'secondary'
                };

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${siparis.siparis_id}</td>
                    <td>${siparis.musteri_ad} ${siparis.musteri_soyad}</td>
                    <td>${new Date(siparis.teslim_tarihi).toLocaleDateString('tr-TR')}</td>
                    <td>${parseFloat(siparis.toplam_tutar).toFixed(2)} ₺</td>
                    <td>
                        <span class="badge bg-${durum_renkleri[siparis.guncel_durum] || 'primary'} status-badge">
                            ${siparis.guncel_durum}
                        </span>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        })
        .catch(error => console.error('Son siparişler yüklenirken hata oluştu:', error));
}

// Sayfa yüklendiğinde ve her 30 saniyede bir güncelle
document.addEventListener('DOMContentLoaded', () => {
    guncelleIstatistikler();
    guncelleSonSiparisler();
    setInterval(() => {
        guncelleIstatistikler();
        guncelleSonSiparisler();
    }, 30000);
}); 