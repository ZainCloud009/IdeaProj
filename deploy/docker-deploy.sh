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
$COMPOSE_CMD down --remove-orphans 2>/dev/null || true
$COMPOSE_CMD build
$COMPOSE_CMD up -d

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
