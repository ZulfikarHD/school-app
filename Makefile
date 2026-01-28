# ==============================================================================
# Makefile untuk Laravel School App Docker Commands
# Memudahkan developer menjalankan common tasks
# ==============================================================================

.PHONY: help build up down restart logs shell db-shell migrate seed fresh test

# Default target
help:
	@echo "=================================================="
	@echo "  Laravel School App - Docker Commands"
	@echo "=================================================="
	@echo ""
	@echo "Production Commands:"
	@echo "  make build      - Build Docker image"
	@echo "  make up         - Start all containers"
	@echo "  make down       - Stop all containers"
	@echo "  make restart    - Restart all containers"
	@echo "  make logs       - View container logs"
	@echo ""
	@echo "Development Commands:"
	@echo "  make dev-build  - Build development image"
	@echo "  make dev-up     - Start development environment"
	@echo "  make dev-down   - Stop development environment"
	@echo ""
	@echo "Utility Commands:"
	@echo "  make shell      - Open shell in app container"
	@echo "  make db-shell   - Open PostgreSQL shell"
	@echo "  make migrate    - Run database migrations"
	@echo "  make seed       - Run database seeders"
	@echo "  make fresh      - Fresh migrate with seed"
	@echo "  make test       - Run tests"
	@echo "  make artisan    - Run artisan command (usage: make artisan cmd='migrate')"
	@echo ""

# ==============================================================================
# Production Commands
# ==============================================================================

build:
	docker-compose build --no-cache

up:
	docker-compose up -d

down:
	docker-compose down

restart:
	docker-compose restart

logs:
	docker-compose logs -f

# ==============================================================================
# Development Commands
# ==============================================================================

dev-build:
	docker-compose -f docker-compose.dev.yml build --no-cache

dev-up:
	docker-compose -f docker-compose.dev.yml up -d

dev-down:
	docker-compose -f docker-compose.dev.yml down

dev-logs:
	docker-compose -f docker-compose.dev.yml logs -f

dev-restart:
	docker-compose -f docker-compose.dev.yml restart

# ==============================================================================
# Utility Commands
# ==============================================================================

shell:
	docker-compose exec app sh

db-shell:
	docker-compose exec postgres psql -U school_user -d school_app

migrate:
	docker-compose exec app php artisan migrate

seed:
	docker-compose exec app php artisan db:seed

fresh:
	docker-compose exec app php artisan migrate:fresh --seed

test:
	docker-compose exec app php artisan test

artisan:
	docker-compose exec app php artisan $(cmd)

# ==============================================================================
# Cleanup Commands
# ==============================================================================

clean:
	docker-compose down -v --remove-orphans
	docker system prune -f

clean-all:
	docker-compose down -v --remove-orphans
	docker-compose -f docker-compose.dev.yml down -v --remove-orphans
	docker system prune -af
