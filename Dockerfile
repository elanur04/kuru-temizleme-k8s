FROM php:8.2-apache

# Gerekli sistem paketlerini ve PHP eklentilerini kuruyoruz
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    && docker-php-ext-install pdo pdo_mysql zip

# Apache mod_rewrite modülünü aktif ediyoruz 
RUN a2enmod rewrite

# Proje dosyalarını container içerisindeki web sunucusu dizinine kopyalıyoruz
COPY ./kurutemizleme /var/www/html/

# Dosya izinlerini ayarlıyoruz
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# 80 portunu dışarı açıyoruz
EXPOSE 80
