.PHONY: help build up down restart logs shell clean install migrate seed test

help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Available targets:'
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

build: ## Build all Docker images
	docker-compose build

up: ## Start all services
	docker-compose up -d

down: ## Stop all services
	docker-compose down

restart: ## Restart all services
	docker-compose restart

logs: ## View logs for all services
	docker-compose logs -f

logs-backend: ## View backend logs
	docker-compose logs -f backend

logs-flutter: ## View flutter logs
	docker-compose logs -f flutter

shell-backend: ## Access backend container shell
	docker-compose exec backend bash

shell-flutter: ## Access flutter container shell
	docker-compose exec flutter bash

shell-mysql: ## Access MySQL shell
	docker-compose exec mysql mysql -u root -proot_secret status_creator

clean: ## Clean up volumes and containers
	docker-compose down -v
	docker system prune -f

install: ## Initial setup - build and install dependencies
	make build
	make up
	sleep 10
	docker-compose exec backend composer install
	docker-compose exec backend cp .env.example .env
	docker-compose exec backend php artisan key:generate
	docker-compose exec backend php artisan storage:link
	docker-compose exec flutter flutter pub get

migrate: ## Run Laravel migrations
	docker-compose exec backend php artisan migrate

seed: ## Seed the database
	docker-compose exec backend php artisan db:seed

fresh: ## Fresh migration with seeding
	docker-compose exec backend php artisan migrate:fresh --seed

test-backend: ## Run backend tests
	docker-compose exec backend php artisan test

cache-clear: ## Clear all Laravel caches
	docker-compose exec backend php artisan cache:clear
	docker-compose exec backend php artisan config:clear
	docker-compose exec backend php artisan route:clear
	docker-compose exec backend php artisan view:clear

queue-restart: ## Restart queue workers
	docker-compose exec backend php artisan queue:restart

npm-install: ## Install npm packages for backend
	docker-compose exec backend npm install

npm-dev: ## Run npm dev for backend
	docker-compose exec backend npm run dev

npm-build: ## Build production assets for backend
	docker-compose exec backend npm run build

flutter-clean: ## Clean Flutter project
	docker-compose exec flutter flutter clean

flutter-build-web: ## Build Flutter web
	docker-compose exec flutter flutter build web

flutter-build-apk: ## Build Flutter APK
	docker-compose exec flutter flutter build apk

status: ## Show status of all services
	docker-compose ps

backup-db: ## Backup MySQL database
	docker-compose exec mysql mysqldump -u root -proot_secret status_creator > backup_$(shell date +%Y%m%d_%H%M%S).sql

restore-db: ## Restore MySQL database from backup (usage: make restore-db file=backup.sql)
	docker-compose exec -T mysql mysql -u root -proot_secret status_creator < $(file)