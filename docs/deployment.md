# Deployment Guide

This app is deployed as Docker images published to GitHub Container Registry by GitHub Actions.

## Images

The workflow at `.github/workflows/publish.yml` builds and pushes:

- `ghcr.io/piccolojnr/backthred/app:latest`
- `ghcr.io/piccolojnr/backthred/web:latest`
- SHA tags for both images, using the short commit SHA

The `app` image runs PHP-FPM and Artisan commands. The `web` image runs nginx and serves the built public assets.
Redis is included in the compose stack and is the default backend for cache, sessions, and queues in the Docker env.

## CI/CD

- `.github/workflows/lint.yml` runs PHP formatting, frontend formatting, ESLint, and TypeScript checks.
- `.github/workflows/tests.yml` runs the Laravel test suite against SQLite with array cache/session and sync queues.
- `.github/workflows/publish.yml` builds both Docker targets on pull requests and pushes GHCR images on `main` or `master`.
- `.github/dependabot.yml` keeps GitHub Actions dependencies grouped and updated weekly.

## First-Time VPS Setup

Install Docker Engine and the Docker Compose plugin on the VPS, then authenticate to GHCR:

```bash
echo "$GITHUB_TOKEN" | docker login ghcr.io -u piccolojnr --password-stdin
```

The token needs package read access for `ghcr.io/piccolojnr/backthred`.

Create the persistent volumes expected by `docker/compose.yaml`:

```bash
docker volume create docker_backthred_database
docker volume create docker_backthred_redis
docker volume create docker_backthred_storage
```

Copy the deployment files to the VPS:

```bash
mkdir -p /opt/backthred
cp docker/compose.yaml /opt/backthred/compose.yaml
cp docker/.env.example /opt/backthred/.env
```

Edit `/opt/backthred/.env` and set production values, especially:

- `APP_KEY`
- `APP_URL`
- `NGINX_SERVER_NAME`
- `DB_PASSWORD`
- `REDIS_PASSWORD` if you want Redis password protection
- `STOREFRONT_URL`
- `CORS_ALLOWED_ORIGINS`
- mail settings
- Paystack settings

Generate `APP_KEY` locally or on the server:

```bash
docker run --rm ghcr.io/piccolojnr/backthred/app:latest php artisan key:generate --show
```

## Deploy

From `/opt/backthred`:

```bash
docker compose pull
docker compose up -d
docker compose exec app php artisan migrate --force
```

To let the app container run migrations during startup, set:

```bash
BACKTHRED_RUN_MIGRATIONS=true
```

## Update

After a push to `main` completes the GitHub Actions image build:

```bash
cd /opt/backthred
docker compose pull
docker compose up -d
docker image prune -f
```

Use a SHA tag for pinned deployments:

```bash
BACKTHRED_IMAGE_TAG=<short-sha> docker compose up -d
```

## Logs

```bash
docker compose ps
docker compose logs -f app
docker compose logs -f web
docker compose logs -f queue
docker compose logs -f scheduler
```

## Reverse Proxy And TLS

The compose file exposes nginx on `APP_PORT`, defaulting to `8080`. Put Caddy, Traefik, or host nginx in front of it for TLS, forwarding traffic to `127.0.0.1:8080`.
