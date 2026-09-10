# ==========================================
# 1. PROVIDER
# ==========================================

provider "aws" {
  region = "eu-north-1"
}

# ==========================================
# 2. DATA SOURCES (Existing Resources)
# ==========================================

data "aws_vpc" "default" {
  default = true
}

data "aws_ami" "amazon_linux" {
  most_recent = true
  owners      = ["amazon"]

  filter {
    name   = "name"
    values = ["al2023-ami-2023.*-x86_64"]
  }
}

# ==========================================
# 3. SECURITY GROUP
# ==========================================

resource "aws_security_group" "ec2_sg" {
  name        = "ec2-security-group"
  description = "EC2 SG allowing SSH, HTTP, and HTTPS"
  vpc_id      = data.aws_vpc.default.id

  ingress {
    from_port   = 22
    to_port     = 22
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  ingress {
    from_port   = 80
    to_port     = 80
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  ingress {
    from_port   = 443
    to_port     = 443
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }
}

# ==========================================
# 4. EC2 INSTANCE (With inline bash script)
# ==========================================

resource "aws_instance" "web_server" {
  ami           = data.aws_ami.amazon_linux.id
  instance_type = "t3.small"

  key_name = "testingserver"

  vpc_security_group_ids = [aws_security_group.ec2_sg.id]

  # Jab bhi user_data change ho, instance recreate hoga
  user_data_replace_on_change = true

  # Shebang (#!/bin/bash) bilkul 0-indentation (left margin) par honi chahiye
  user_data = <<-EOF
#!/bin/bash
set -e

# System update
dnf update -y

# Apache, MariaDB, Docker, aur PHP extensions install karna
dnf install -y httpd mariadb105-server docker git wget unzip tar
dnf install -y php php-fpm php-mysqli php-mysqlnd php-xml php-mbstring php-curl php-zip php-intl php-bcmath php-opcache php-gd

# Node.js install karna
dnf install -y nodejs22 || dnf install -y nodejs20 || dnf install -y nodejs

# Composer install karna
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
    ln -sf /usr/local/bin/composer /usr/bin/composer
fi

# Services enable aur start karna
systemctl enable --now httpd
systemctl enable --now php-fpm
systemctl enable --now mariadb
systemctl enable --now docker

# ec2-user ko Docker aur Apache group me add karna
usermod -aG docker ec2-user
usermod -aG apache ec2-user

# MariaDB database ensure karna
mysql -u root -e "CREATE DATABASE IF NOT EXISTS idea CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" || true

# Laravel Directory structure aur permissions
mkdir -p /var/www/html/IdeaProj/shared/storage /var/www/html/IdeaProj/releases
chown -R ec2-user:apache /var/www/html/IdeaProj
chmod -R 775 /var/www/html/IdeaProj/shared/storage

# Apache Virtual Host configure karna
cat << 'VHOST' > /etc/httpd/conf.d/laravel.conf
<VirtualHost *:80>
    ServerName localhost
    ServerAlias *
    DocumentRoot "/var/www/html/IdeaProj/current/public"
    <Directory "/var/www/html/IdeaProj/current/public">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    Alias /IdeaProj/public /var/www/html/IdeaProj/current/public
    Alias /IdeaProj /var/www/html/IdeaProj/current/public
    Alias /phpmyadmin /var/www/html/phpmyadmin
    <Directory "/var/www/html/phpmyadmin">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
VHOST

# phpMyAdmin download aur extract karna
cd /var/www/html
wget -q https://www.phpmyadmin.net/downloads/phpMyAdmin-latest-all-languages.tar.gz
tar -xzf phpMyAdmin-latest-all-languages.tar.gz
rm -rf phpmyadmin phpMyAdmin-latest-all-languages.tar.gz
mv phpMyAdmin-*-all-languages phpmyadmin
chown -R apache:apache /var/www/html/phpmyadmin

# Web server restart karna changes apply karne ke liye
systemctl restart php-fpm httpd
EOF

  tags = {
    Name = "MyWebServer"
  }
}

# ==========================================
# 5. ELASTIC IP
# ==========================================

resource "aws_eip" "web_eip" {
  instance = aws_instance.web_server.id
  domain   = "vpc"

  tags = {
    Name = "WebServerEIP"
  }
}

output "elastic_ip" {
  description = "Elastic IP address of the EC2 Web Server"
  value       = aws_eip.web_eip.public_ip
}