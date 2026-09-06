# ==========================================
# 1. PROVIDER
# ==========================================

# AWS Provider configure kar rahe hain
provider "aws" {
  region = "eu-north-1" 
}

# ==========================================
# 2. DATA SOURCES (Existing Resources)
# ==========================================

# By default banay gaye VPC ko fetch kar raha hai
data "aws_vpc" "default" {
  default = true
}

# Latest Amazon Linux 2023 AMI dhoondh raha hai
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

# EC2 ka Security Group (Port 22, 80, aur 443 sab ke liye open)
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
  
  # Inline script jo instance start hote hi packages install karegi
  user_data = <<-EOF
    #!/bin/bash
    sudo dnf update -y
    
    # Apache, MariaDB, and PHP with all required Laravel extensions
    sudo dnf install -y httpd mariadb105-server php php-fpm php-mysqli php-mysqlnd php-xml php-mbstring php-curl php-zip php-intl php-bcmath php-opcache php-gd git wget unzip tar
    sudo dnf install -y nodejs22 || sudo dnf install -y nodejs20 || sudo dnf install -y nodejs
    which composer >/dev/null 2>&1 || (curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer)
    sudo ln -sf /usr/local/bin/composer /usr/bin/composer 2>/dev/null || true
    
    # Services start aur enable karna
    sudo systemctl enable --now httpd
    sudo systemctl enable --now php-fpm
    sudo systemctl enable --now mariadb
    
    # MariaDB 'idea' database ensure karna
    sudo mysql -u root -e "CREATE DATABASE IF NOT EXISTS idea CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" || true
    
    # Apache Virtual Host configure karna for Laravel
    sudo tee /etc/httpd/conf.d/laravel.conf > /dev/null << 'VHOST'
    <VirtualHost *:80>
        DocumentRoot "/var/www/html/IdeaProj/current/public"
        <Directory "/var/www/html/IdeaProj/current/public">
            Options Indexes FollowSymLinks
            AllowOverride All
            Require all granted
        </Directory>
        Alias /phpmyadmin /var/www/html/phpmyadmin
        <Directory "/var/www/html/phpmyadmin">
            Options Indexes FollowSymLinks
            AllowOverride All
            Require all granted
        </Directory>
    </VirtualHost>
VHOST

    # Permissions setup for ec2-user and apache
    sudo mkdir -p /var/www/html/IdeaProj/shared/storage /var/www/html/IdeaProj/releases
    sudo usermod -a -G apache ec2-user
    sudo chown -R ec2-user:apache /var/www/html/IdeaProj
    sudo chmod -R 775 /var/www/html/IdeaProj/shared/storage

    # phpMyAdmin download aur extract karna
    cd /var/www/html
    sudo wget https://www.phpmyadmin.net/downloads/phpMyAdmin-latest-all-languages.tar.gz
    sudo tar -xvzf phpMyAdmin-latest-all-languages.tar.gz
    sudo mv phpMyAdmin-*-all-languages phpmyadmin
    sudo rm -rf phpMyAdmin-latest-all-languages.tar.gz

    sudo systemctl restart php-fpm httpd
  EOF

  tags = {
    Name = "MyWebServer"
  }
}
