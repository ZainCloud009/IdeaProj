#!/usr/bin/env bash
# ==============================================================================
# Amazon Linux 2023 Server Setup for Laravel 12 on AWS EC2
# ==============================================================================
set -e

echo "==> Updating package repository..."
sudo dnf update -y

echo "==> Installing Apache, MariaDB, and PHP with all required Laravel extensions..."
sudo dnf install -y \
  httpd \
  mariadb105-server \
  php \
  php-fpm \
  php-mysqli \
  php-mysqlnd \
  php-xml \
  php-mbstring \
  php-curl \
  php-zip \
  php-intl \
  php-bcmath \
  php-opcache \
  php-gd \
  wget \
  unzip \
  tar

echo "==> Starting and enabling services..."
sudo systemctl enable --now httpd
sudo systemctl enable --now php-fpm
sudo systemctl enable --now mariadb

echo "==> Ensuring MariaDB database 'idea' exists..."
sudo mysql -e "CREATE DATABASE IF NOT EXISTS idea CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

echo "==> Configuring Apache Virtual Host for Laravel..."
# Configure Apache to serve from /var/www/html/public and allow .htaccess overrides
sudo tee /etc/httpd/conf.d/laravel.conf > /dev/null << 'EOF'
<VirtualHost *:80>
    DocumentRoot "/var/www/html/public"

    <Directory "/var/www/html/public">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # Alias to keep phpmyadmin accessible at http://<server-ip>/phpmyadmin
    Alias /phpmyadmin /var/www/html/phpmyadmin
    <Directory "/var/www/html/phpmyadmin">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog /var/log/httpd/laravel_error.log
    CustomLog /var/log/httpd/laravel_access.log combined
</VirtualHost>
EOF

echo "==> Setting up phpMyAdmin if not already present..."
if [ ! -d "/var/www/html/phpmyadmin" ]; then
    sudo mkdir -p /var/www/html
    cd /var/www/html
    sudo wget -q https://www.phpmyadmin.net/downloads/phpMyAdmin-latest-all-languages.tar.gz
    sudo tar -xzf phpMyAdmin-latest-all-languages.tar.gz
    sudo mv phpMyAdmin-*-all-languages phpmyadmin
    sudo rm -f phpMyAdmin-latest-all-languages.tar.gz
fi

echo "==> Configuring directory permissions for ec2-user and apache..."
sudo mkdir -p /var/www/html/public
sudo mkdir -p /var/www/html/storage
sudo mkdir -p /var/www/html/bootstrap/cache

sudo usermod -a -G apache ec2-user
sudo chown -R ec2-user:apache /var/www/html
sudo chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "==> Restarting Apache and PHP-FPM..."
sudo systemctl restart php-fpm
sudo systemctl restart httpd

echo "=============================================================================="
echo " Server setup complete! The server is ready for GitHub Actions deployments."
echo "=============================================================================="
