# Kuru Temizleme Sistemi - K8s & CI/CD Final Projesi

Bu proje, PHP ve MySQL kullanılarak geliştirilmiş "Kuru Temizleme Sistemi"nin Docker ile container haline getirilip, Google Kubernetes Engine (GKE) üzerinde çalıştırılması ve GitHub Actions ile CI/CD süreçlerinin otomatikleştirilmesi işlemlerini kapsamaktadır.

## 📌 1. Uygulama Mimarisi
Uygulamamız temelde iki ana bileşenden oluşmaktadır:
- **Frontend/Backend (Web Katmanı):** PHP 8.2 ve Apache kullanılarak geliştirilmiştir. Kullanıcı arayüzünü sunar ve iş mantığını işletir.
- **Veritabanı Katmanı:** MySQL 8.0 kullanılarak müşteri verilerini, sipariş durumlarını ve sistem loglarını saklar.

## 📌 2. Sistem Mimarisi
Sistemimiz, fiziksel sunuculara olan bağımlılığı ortadan kaldırarak **Bulut Bilişim (Cloud Computing)** prensiplerine uygun olarak tasarlanmıştır. 
Uygulama Docker sayesinde paketlenmiş (containerize edilmiş) ve herhangi bir bilgisayarda aynı şekilde çalışması garanti altına alınmıştır. Tüm altyapı yönetimi, yük dengelemesi (Load Balancing) ve kendi kendini onarma (Self-Healing) özellikleri Google Kubernetes Engine (GKE) üzerinden sağlanmaktadır.

## 📌 3. Kubernetes Mimarisi
Projenin Kubernetes ortamındaki yerleşimi şu şekildedir:
- **Uygulama Podları:** PHP Apache imajını çalıştıran Pod'lar (Replica sayısı 3 olarak ayarlanmıştır).
- **Veritabanı Podu:** MySQL imajını çalıştıran ve kalıcı veriye sahip tekil Pod.
- **Service'ler:** Dışarıdan gelen kullanıcı isteklerini Web Pod'larına dağıtan bir **LoadBalancer Service** ve Web Pod'larının Veritabanı Pod'una güvenli bir şekilde ulaşmasını sağlayan bir **ClusterIP Service**.

## 📌 4. Kullanılan K8s Bileşenleri ve Görevleri

### Deployment
Uygulamalarımızı yönetmek için kullanılır. Çöken bir uygulamanın yerine yenisini açmak, güncellemeleri yönetmek ve kopya sayısını belirlemek onun görevidir. `web-deployment.yaml` ve `mysql-deployment.yaml` olarak iki ayrı dosya oluşturduk.

### Service
Pod'ların (uygulamaların) IP adresleri sürekli değişebildiği için onlara sabit bir isim ve adres atamak için kullanılır.
- **web-service:** `LoadBalancer` tipindedir. GKE bize gerçek bir internet IP'si verir ve bu IP'ye giren herkes sitemize ulaşır.
- **mysql-service:** `ClusterIP` tipindedir. Sadece Kubernetes içerisinden (bizim web uygulamamızdan) erişilebilir. Dışarıdan kimse veritabanımıza giremez.

### PV (Persistent Volume) ve PVC (Persistent Volume Claim)
Pod'lar (uygulamalar) silindiğinde veya çöktüğünde içlerindeki veriler de silinir. Bu durum web sitesi için sorun olmasa da **Veritabanı** için felakettir. Bu yüzden `mysql-pvc.yaml` dosyasını oluşturduk. Bu sayede MySQL çöksede, müşteri kayıtları Google Cloud'un kalıcı disklerinde (1 GB'lık alan) güvenle saklanmaya devam eder.

### ConfigMap (Veritabanı Otomatik Kurulumu)
Kubernetes üzerinde MySQL podu ilk defa çalıştırıldığında veritabanı tamamen boş başlar. Web uygulamasının çalışması için gerekli olan veritabanı tablolarının, saklı yordamların ve tetikleyicilerin otomatik kurulması amacıyla `mysql-configmap.yaml` dosyasını oluşturduk. Bu dosya, `database.sql` içeriğini Kubernetes üzerinde güvenli bir şekilde saklar ve MySQL poduna mount edilerek sistemin tamamen sıfır müdahaleyle (cloud-native) kurulmasını sağlar.

### Network Policy
Sistemimizin güvenlik duvarıdır (Firewall). `network-policy.yaml` dosyamızda şu kuralı koyduk: *"MySQL veritabanına sadece ve sadece 'kurutemizleme-web' etiketine sahip olan uygulamalar (yani bizim sitemiz) bağlanabilir."* Bu, siber güvenlik açısından kritik bir önlemdir.

## 📌 5. Ölçekleme (Scaling), Rolling Update ve Rollback

### Ölçekleme (Scaling)
Sitemize aniden binlerce müşteri girerse, sitemizin çökmemesi için kopya sayısını artırmamız gerekir. Biz Deployment dosyasında `replicas: 3` diyerek sistemi zaten baştan 3 kopya başlattık. İstenirse şu komutla anında sayı 10'a çıkarılabilir:
`kubectl scale deployment web-deployment --replicas=10`

### Rolling Update (Kesintisiz Güncelleme)
Koda yeni bir özellik eklediğimizde, siteyi kapatıp açmak yerine "Rolling Update" yaparız. Sistem, yeni sürüm podları teker teker açar ve eskileri teker teker kapatır. Böylece site 1 saniye bile kapanmaz. `web-deployment.yaml` içerisinde `strategy: RollingUpdate` ayarı ile bunu tanımladık.
*Güncellemeyi başlatmak için:*
`kubectl set image deployment/web-deployment web=ghcr.io/elanur04/kuru-temizleme-k8s:yeni_surum`

### Rollback (Geri Alma)
Eğer yeni yüklediğimiz kodda bir hata çıkarsa (sitede sayfalar açılmazsa vb.), tek bir komutla anında eski çalışan sürüme geri dönebiliriz. Sistem hatayı kendi düzeltir:
`kubectl rollout undo deployment/web-deployment`

## 📌 6. CI/CD Pipeline Akışı (GitHub Actions)
Yazılımcı olarak bizim işimiz kod yazmaktır, sunucuyla uğraşmak değil. Bu yüzden otomasyon (CI/CD) kurduk.
Süreç şöyle işler:
1. Biz bilgisayarımızda kodu yazarız ve `git push` komutuyla GitHub'a göndeririz.
2. GitHub Actions ( `main.yml` dosyamız) bu değişikliği algılar.
3. Bulutta sanal bir Ubuntu bilgisayar açar.
4. Bizim `Dockerfile` dosyamızı kullanarak uygulamamızı yeni baştan paketler (Build).
5. Oluşturduğu bu yeni paketi (Docker imajını) GitHub Container Registry'ye (ghcr.io) yükler (Push).
6. Deployment'ların uygulanmasından sonra `kubectl rollout restart deployment/web-deployment` komutu otomatik çalıştırılarak GKE'deki web podlarının yeni imajı çekmesi ve kesintisiz güncelleme yapması (Rolling Update) garanti altına alınır.

Demo videosu: 
https://youtu.be/Qqmm9rNvvak


