# Docker Demo Setup

Setup ini dibuat untuk VM demo dengan target:

- source code dimount langsung ke container
- perubahan PHP/Blade langsung terbaca tanpa restart
- perubahan CSS/JS ikut rebuild otomatis lewat watcher Vite
- PostgreSQL dan Redis ikut tersedia di stack yang sama
- port app disamakan ke pola server lama: `8081 -> 8000`
- nama container utama disamakan ke pola server lama: `satset_*`

## File yang dipakai

- `Dockerfile`
- `docker-compose.demo.yml`
- `.env.docker.example`

## Langkah pakai

1. Copy file env demo:

```bash
cp .env.docker.example .env.docker
cp .env.docker .env
```

2. Jalankan stack:

```bash
docker compose -f docker-compose.demo.yml up -d --build
```

3. Buka aplikasi:

- App: `http://localhost:8081`

## Service

- `app`: Laravel web server
- `queue`: queue listener
- `scheduler`: scheduler loop
- `assets`: Vite build watcher
- `postgres`: database demo
- `redis`: cache / future compatibility with stack lama

## Kenapa perubahan file langsung kebaca

- Project folder dimount dengan bind mount ke `/var/www/html`
- PHP/Blade dibaca langsung dari folder host
- Asset JS/CSS direbuild otomatis oleh service `assets`

## Perintah penting

Lihat log:

```bash
docker compose -f docker-compose.demo.yml logs -f app
docker compose -f docker-compose.demo.yml logs -f assets
```

Stop stack:

```bash
docker compose -f docker-compose.demo.yml down
```

Reset database demo:

```bash
docker compose -f docker-compose.demo.yml exec app php artisan migrate:fresh --seed --force
```
