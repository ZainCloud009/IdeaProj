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
    
    # HTTPD, MariaDB, PHP install karna
    sudo dnf install -y httpd mariadb105-server php php-mysqli php-fpm wget unzip tar
    
    # Services start aur enable karna
    sudo systemctl start httpd
    sudo systemctl enable httpd
    sudo systemctl start mariadb
    sudo systemctl enable mariadb
    
    # phpMyAdmin download aur extract karna
    cd /var/www/html
    sudo wget https://www.phpmyadmin.net/downloads/phpMyAdmin-latest-all-languages.tar.gz
    sudo tar -xvzf phpMyAdmin-latest-all-languages.tar.gz
    sudo mv phpMyAdmin-*-all-languages phpmyadmin
    sudo rm -rf phpMyAdmin-latest-all-languages.tar.gz
  EOF

  tags = {
    Name = "MyWebServer"
  }
}
