# Docker Setup Guide - School App

Panduan lengkap untuk menjalankan School App menggunakan Docker di Windows dan Linux.

## Prerequisites

### Windows

1. **Install Docker Desktop**
   - Download dari: https://www.docker.com/products/docker-desktop/
   - Jalankan installer dan ikuti instruksi
   - Restart komputer setelah instalasi

2. **Enable WSL 2 (Recommended)**
   - Buka PowerShell sebagai Administrator
   - Jalankan:
     ```powershell
     wsl --install
     ```
   - Restart komputer
   - Buka Docker Desktop → Settings → General → Centang "Use the WSL 2 based engine"

3. **Verify Installation**
   ```powershell
   docker --version
   docker-compose --version
   ```

### Linux (Ubuntu/Debian)

1. **Install Docker Engine**
   ```bash
   # Update package index
   sudo apt-get update

   # Install dependencies
   sudo apt-get install -y \
       ca-certificates \
       curl \
       gnupg \
       lsb-release

   # Add Docker's official GPG key
   sudo mkdir -m 0755 -p /etc/apt/keyrings
   curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg

   # Set up repository
   echo \
     "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
     $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

   # Install Docker Engine
   sudo apt-get update
   sudo apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
   ```

2. **Add User to Docker Group (Optional, untuk menjalankan tanpa sudo)**
   ```bash
   sudo usermod -aG docker $USER
   newgrp docker
   ```

3. **Verify Installation**
   ```bash
   docker --version
   docker compose version
   ```

### Linux (Fedora/RHEL/CentOS)

1. **Install Docker Engine**
   ```bash
   # Remove old versions
   sudo dnf remove docker docker-client docker-client-latest docker-common docker-latest docker-latest-logrotate docker-logrotate docker-engine

   # Install dependencies
   sudo dnf -y install dnf-plugins-core

   # Add Docker repository
   sudo dnf config-manager --add-repo https://download.docker.com/linux/fedora/docker-ce.repo

   # Install Docker
   sudo dnf install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

   # Start Docker
   sudo systemctl start docker
   sudo systemctl enable docker
   ```

2. **Add User to Docker Group**
   ```bash
   sudo usermod -aG docker $USER
   newgrp docker
   ```

---

## Quick Start

### Step 1: Clone Repository

**Windows (PowerShell/Command Prompt):**
```powershell
git clone https://github.com/your-repo/school-app.git
cd school-app
```

**Linux:**
```bash
git clone https://github.com/your-repo/school-app.git
cd school-app
```

### Step 2: Setup Environment

**Windows:**
```powershell
copy .env.docker .env
```

**Linux:**
```bash
cp .env.docker .env
```

### Step 3: Build dan Run

**Windows & Linux (sama):**
```bash
docker-compose up --build -d
```

Proses ini akan:
- Build Docker image dengan PHP 8.4, Nginx, dan semua dependencies
- Start PostgreSQL database
- Otomatis generate APP_KEY
- Jalankan database migrations
- Setup storage link

### Step 4: Akses Aplikasi

Buka browser dan akses: **http://localhost:8080**

---

## Commands Reference

### Menggunakan Makefile (Linux/WSL)

```bash
make help       # Tampilkan semua commands
make build      # Build Docker image
make up         # Start containers
make down       # Stop containers
make restart    # Restart containers
make logs       # Lihat logs
make shell      # Buka shell di container
make db-shell   # Buka PostgreSQL shell
make migrate    # Jalankan migrations
make seed       # Jalankan seeders
make fresh      # Fresh migrate + seed
make test       # Jalankan tests
```

### Menggunakan Docker Compose (Windows/Linux)

```bash
# Build image
docker-compose build

# Start containers (background)
docker-compose up -d

# Start dengan build
docker-compose up --build -d

# Stop containers
docker-compose down

# Lihat logs
docker-compose logs -f

# Lihat logs app saja
docker-compose logs -f app

# Restart containers
docker-compose restart

# Masuk ke shell container
docker-compose exec app sh

# Jalankan artisan command
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
docker-compose exec app php artisan tinker

# Masuk ke PostgreSQL
docker-compose exec postgres psql -U school_user -d school_app
```

---

## Configuration

### Environment Variables

Edit file `.env` untuk konfigurasi:

| Variable | Default | Description |
|----------|---------|-------------|
| `APP_PORT` | 8080 | Port untuk akses aplikasi |
| `DB_DATABASE` | school_app | Nama database |
| `DB_USERNAME` | school_user | Username database |
| `DB_PASSWORD` | school_password | Password database |
| `APP_DEBUG` | false | Debug mode (true untuk development) |
| `LOG_LEVEL` | info | Level logging |

### Mengubah Port

Jika port 8080 sudah digunakan, ubah di `.env`:

```env
APP_PORT=8000
```

Lalu restart:
```bash
docker-compose down
docker-compose up -d
```

### Database Port

Default PostgreSQL port adalah 5432. Untuk mengubah:

```env
DB_PORT=5433
```

---

## Development Mode

Untuk development dengan hot-reloading:

```bash
# Build development image
docker-compose -f docker-compose.dev.yml build

# Start development environment
docker-compose -f docker-compose.dev.yml up -d
```

Development mode menyediakan:
- Hot-reloading untuk Vue/TypeScript changes
- Source code mounting (perubahan langsung terlihat)
- Vite dev server di port 5173

---

## Troubleshooting

### Windows: Docker Desktop tidak start

1. Pastikan Hyper-V atau WSL 2 sudah enabled
2. Buka "Turn Windows features on or off"
3. Enable: "Virtual Machine Platform" dan "Windows Subsystem for Linux"
4. Restart komputer

### Windows: Permission denied

```powershell
# Jalankan PowerShell sebagai Administrator
# Atau gunakan Docker Desktop dengan "Run as administrator"
```

### Linux: Permission denied

```bash
# Tambahkan user ke docker group
sudo usermod -aG docker $USER

# Logout dan login kembali, atau:
newgrp docker
```

### Port already in use

```bash
# Cek proses yang menggunakan port
# Windows:
netstat -ano | findstr :8080

# Linux:
sudo lsof -i :8080

# Ubah port di .env
APP_PORT=8081
```

### Container tidak bisa connect ke database

```bash
# Cek status container
docker-compose ps

# Cek logs PostgreSQL
docker-compose logs postgres

# Restart semua containers
docker-compose down
docker-compose up -d
```

### Build error: out of memory

```bash
# Tambah memory untuk Docker
# Windows: Docker Desktop → Settings → Resources → Memory
# Linux: Edit /etc/docker/daemon.json

# Atau build dengan less parallelism
docker-compose build --parallel 1
```

### Rebuild dari awal

```bash
# Hapus semua containers dan volumes
docker-compose down -v

# Rebuild tanpa cache
docker-compose build --no-cache

# Start fresh
docker-compose up -d
```

---

## Database Management

### Backup Database

```bash
# Backup ke file
docker-compose exec postgres pg_dump -U school_user school_app > backup.sql

# Dengan timestamp
docker-compose exec postgres pg_dump -U school_user school_app > backup_$(date +%Y%m%d_%H%M%S).sql
```

### Restore Database

```bash
# Restore dari file
cat backup.sql | docker-compose exec -T postgres psql -U school_user -d school_app
```

### Reset Database

```bash
# Fresh migration (hapus semua data!)
docker-compose exec app php artisan migrate:fresh

# Fresh dengan seeder
docker-compose exec app php artisan migrate:fresh --seed
```

---

## Useful Tips

### Melihat Resource Usage

```bash
docker stats
```

### Membersihkan Docker

```bash
# Hapus unused images
docker image prune -a

# Hapus semua unused resources
docker system prune -a

# Hapus volumes yang tidak digunakan
docker volume prune
```

### Cek Logs Aplikasi

```bash
# Laravel logs
docker-compose exec app tail -f storage/logs/laravel.log

# Nginx access log
docker-compose exec app tail -f /var/log/nginx/access.log

# PHP-FPM slow log
docker-compose exec app tail -f storage/logs/php-fpm-slow.log
```

---

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│                      Docker Network                          │
│                                                              │
│  ┌─────────────────────────────────────────────────────┐    │
│  │                    App Container                     │    │
│  │  ┌───────────┐  ┌───────────┐  ┌─────────────────┐  │    │
│  │  │   Nginx   │  │  PHP-FPM  │  │  Queue Worker   │  │    │
│  │  │  (port 80)│  │ (port 9000│  │  (Supervisor)   │  │    │
│  │  └─────┬─────┘  └─────┬─────┘  └─────────────────┘  │    │
│  │        │              │                              │    │
│  │        └──────────────┘                              │    │
│  │              │                                       │    │
│  └──────────────┼───────────────────────────────────────┘    │
│                 │                                            │
│  ┌──────────────┴───────────────────────────────────────┐   │
│  │              PostgreSQL Container                     │   │
│  │                   (port 5432)                         │   │
│  └───────────────────────────────────────────────────────┘   │
│                                                              │
└──────────────────────────────────────────────────────────────┘
                            │
                    Host Machine
                    localhost:8080
```

---

## Support

Jika mengalami masalah:

1. Cek dokumentasi di folder `docs/`
2. Lihat logs dengan `docker-compose logs -f`
3. Pastikan Docker Desktop (Windows) atau Docker Engine (Linux) sudah running
4. Restart Docker service jika perlu

---

**Happy Coding! 🚀**
