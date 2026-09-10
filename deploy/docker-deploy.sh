#!/usr/bin/env bash
# ==============================================================================
# Docker Deployment Script for IdeaProj on AWS EC2
# ==============================================================================
set -e

echo "==> Ensuring Docker service is active..."
sudo systemctl enable --now docker
sudo usermod -aG docker "$(whoami)" 2>/dev/null || true

echo "==> Ensuring Docker Compose is installed..."
if ! docker compose version >/dev/null 2>&1; then
    sudo dnf install -y docker-compose-plugin 2>/dev/null || {
        sudo curl -SL "https://github.com/docker/compose/releases/latest/download/docker-compose-linux-x86_64" -o /usr/local/bin/docker-compose
        sudo chmod +x /usr/local/bin/docker-compose
        sudo ln -sf /usr/local/bin/docker-compose /usr/bin/docker-compose
    }
fi

echo "==> Stopping host Apache (httpd) to release port 80 for Docker..."
sudo systemctl stop httpd 2>/dev/null || true
sudo systemctl disable httpd 2>/dev/null || true

# Navigate to project directory
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_DIR"

# Ensure .env exists
if [ ! -f .env ]; then
    echo "==> Creating .env from .env.example..."
    cp .env.example .env
fi

echo "==> Building and launching Docker containers..."
docker compose down --remove-orphans 2>/dev/null || true
docker compose build --pull
docker compose up -d

echo "==> Waiting for containers to initialize..."
sleep 10

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

echo "==> Pruning unused dangling images..."
docker image prune -f

echo "=============================================================================="
echo " Docker deployment completed successfully!"
echo " Check containers with:  docker ps"
echo " View app logs with:     docker logs -f ideaproj-app"
echo " View db logs with:      docker logs -f ideaproj-db"
echo "=============================================================================="
