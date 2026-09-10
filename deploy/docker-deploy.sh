#!/usr/bin/env bash
# ==============================================================================
# Docker Deployment Script for IdeaProj on AWS EC2
# ==============================================================================
set -e

echo "==> Ensuring Docker service is active..."
sudo systemctl enable --now docker
sudo usermod -aG docker "$(whoami)" 2>/dev/null || true

echo "==> Ensuring Docker Compose is available..."
sudo mkdir -p /usr/local/lib/docker/cli-plugins /usr/lib/docker/cli-plugins
if [ -f /usr/local/bin/docker-compose ]; then
    sudo cp -f /usr/local/bin/docker-compose /usr/local/lib/docker/cli-plugins/docker-compose 2>/dev/null || true
    sudo cp -f /usr/local/bin/docker-compose /usr/lib/docker/cli-plugins/docker-compose 2>/dev/null || true
    sudo chmod +x /usr/local/lib/docker/cli-plugins/docker-compose /usr/lib/docker/cli-plugins/docker-compose 2>/dev/null || true
fi

if ! docker compose version >/dev/null 2>&1 && ! command -v docker-compose >/dev/null 2>&1; then
    echo "==> Installing Docker Compose..."
    sudo curl -SL "https://github.com/docker/compose/releases/latest/download/docker-compose-linux-x86_64" -o /usr/local/lib/docker/cli-plugins/docker-compose
    sudo chmod +x /usr/local/lib/docker/cli-plugins/docker-compose
    sudo cp -f /usr/local/lib/docker/cli-plugins/docker-compose /usr/lib/docker/cli-plugins/docker-compose 2>/dev/null || true
    sudo ln -sf /usr/local/lib/docker/cli-plugins/docker-compose /usr/local/bin/docker-compose
    sudo ln -sf /usr/local/lib/docker/cli-plugins/docker-compose /usr/bin/docker-compose
fi

if docker compose version >/dev/null 2>&1; then
    COMPOSE_CMD="docker compose"
elif command -v docker-compose >/dev/null 2>&1; then
    COMPOSE_CMD="docker-compose"
else
    echo "==> Error: Docker Compose could not be initialized."
    exit 1
fi
echo "==> Using compose command: $COMPOSE_CMD"

# Ensure modern buildx is available (requires >= 0.17.0 for Compose)
BUILDX_VER=$(docker buildx version 2>/dev/null | grep -oE 'v[0-9]+\.[0-9]+\.[0-9]+' | head -n1 || echo "v0.0.0")
if [ -z "$BUILDX_VER" ] || [ "$BUILDX_VER" \< "v0.17.0" ]; then
    echo "==> Upgrading buildx to v0.21.1 (current: $BUILDX_VER)..."
    sudo mkdir -p /usr/local/lib/docker/cli-plugins /usr/lib/docker/cli-plugins
    sudo curl -SL "https://github.com/docker/buildx/releases/download/v0.21.1/buildx-v0.21.1.linux-amd64" -o /usr/local/lib/docker/cli-plugins/docker-buildx
    sudo chmod +x /usr/local/lib/docker/cli-plugins/docker-buildx
    sudo cp -f /usr/local/lib/docker/cli-plugins/docker-buildx /usr/lib/docker/cli-plugins/docker-buildx 2>/dev/null || true
fi

# Ensure $HOME/.docker exists and is owned by current user
sudo mkdir -p "$HOME/.docker"
sudo chown -R "$(whoami):$(whoami)" "$HOME/.docker"
sudo chmod -R 775 "$HOME/.docker"

echo "==> Stopping host Apache (httpd) to release port 80 for Docker..."
sudo systemctl stop httpd 2>/dev/null || true
sudo systemctl disable httpd 2>/dev/null || true

# Navigate to project directory
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_DIR"

# Ensure .env exists with proper db connection
if [ ! -f .env ]; then
    echo "==> Creating .env from .env.example..."
    cp .env.example .env
fi
if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
    if ! grep -q "^APP_KEY=" .env 2>/dev/null; then
        echo "APP_KEY=" >> .env
    fi
    HOST_KEY="base64:$(openssl rand -base64 32 2>/dev/null || php -r 'echo base64_encode(random_bytes(32));' 2>/dev/null || echo 'bGFyYXZlbGFwcGxpY2F0aW9ua2V5MTIzNDU2Nzg5MDEyMzQ=')"
    sed -i "s|^APP_KEY=.*|APP_KEY=${HOST_KEY}|" .env
fi
sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=mysql/' .env
sed -i 's/^#* *DB_HOST=.*/DB_HOST=db/' .env
sed -i 's/^#* *DB_PORT=.*/DB_PORT=3306/' .env
sed -i 's/^#* *DB_DATABASE=.*/DB_DATABASE=idea/' .env
sed -i 's/^#* *DB_USERNAME=.*/DB_USERNAME=root/' .env

echo "==> Building application image with Docker..."
docker build -t ideaproj-app:latest .

echo "==> Launching Docker containers..."
$COMPOSE_CMD down --remove-orphans 2>/dev/null || true
$COMPOSE_CMD up -d

echo "==> Waiting for database container to initialize..."
sleep 12

DB_PASS="${DB_PASSWORD:-secret_password}"
docker exec ideaproj-db mariadb -u root -proot_password -e "
  ALTER USER 'root'@'%' IDENTIFIED BY '${DB_PASS}';
  GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' IDENTIFIED BY '${DB_PASS}' WITH GRANT OPTION;
  CREATE USER IF NOT EXISTS 'idea_user'@'%' IDENTIFIED BY '${DB_PASS}';
  ALTER USER 'idea_user'@'%' IDENTIFIED BY '${DB_PASS}';
  GRANT ALL PRIVILEGES ON *.* TO 'idea_user'@'%';
  FLUSH PRIVILEGES;
" 2>/dev/null || docker exec ideaproj-db mariadb -u root -p"${DB_PASS}" -e "
  GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' IDENTIFIED BY '${DB_PASS}' WITH GRANT OPTION;
  CREATE USER IF NOT EXISTS 'idea_user'@'%' IDENTIFIED BY '${DB_PASS}';
  ALTER USER 'idea_user'@'%' IDENTIFIED BY '${DB_PASS}';
  GRANT ALL PRIVILEGES ON *.* TO 'idea_user'@'%';
  FLUSH PRIVILEGES;
" 2>/dev/null || true

# Run migrations to ensure all tables exist (users, sessions, cache, ideas, steps)
echo "==> Running Laravel database migrations inside container..."
docker exec ideaproj-app php /var/www/html/artisan migrate --force || true
docker exec ideaproj-app php /var/www/html/artisan optimize:clear || true
docker exec ideaproj-app php /var/www/html/artisan config:cache || true

echo "==> Container Status:"
docker ps --filter "name=ideaproj"

echo "==> Running Health Check..."
STATUS=$(curl -s -o /dev/null -w "%{http_code}" --max-time 5 "http://127.0.0.1/up" || echo "failed")
if [ "$STATUS" = "200" ]; then
    echo "==> [SUCCESS] Application is LIVE and healthy on port 80 (HTTP 200)!"
else
    echo "==> Health check returned: $STATUS. Checking main page:"
    curl -I http://127.0.0.1/ || true
fi

echo "==> Cleaning up build caches and temporary builder images..."
docker image rm node:22-alpine composer:2 2>/dev/null || true
docker system prune -f
docker builder prune -af --keep-storage 200MB 2>/dev/null || true

echo "==> Docker Disk Usage Summary:"
docker system df

echo "=============================================================================="
echo " Docker deployment completed successfully!"
echo " Check containers with:  docker ps"
echo " View app logs with:     docker logs -f ideaproj-app"
echo " View db logs with:      docker logs -f ideaproj-db"
echo "=============================================================================="
