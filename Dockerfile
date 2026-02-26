# 1. Gunakan image resmi PHP 8.4 dengan Apache
FROM php:8.4-apache

# 2. Install system dependencies & Node.js 20 (untuk Vite)
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 3. Install PHP extensions (Wajib ada pdo_pgsql untuk database Dokploy)
RUN docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

# 4. Aktifkan Apache mod_rewrite (Wajib untuk routing Laravel)
RUN a2enmod rewrite

# 5. Ubah DocumentRoot Apache ke folder /public milik Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 6. Dapatkan Composer versi terbaru
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 7. Set working directory ke folder web
WORKDIR /var/www/html

# 8. Copy SEMUA file project kamu ke dalam container
COPY . .

# 9. Install dependencies (PHP & Node.js) dan Build Vite
# Kita pakai --no-dev agar library testing/dev tidak ikut terinstall (bikin ringan)
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
RUN npm install
RUN npm run build

# 10. Set hak akses (Permissions) agar file bisa dibaca dan ditulis oleh Apache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# 11. Buka port 80
EXPOSE 80

# 12. Jalankan Apache
CMD ["apache2-foreground"]