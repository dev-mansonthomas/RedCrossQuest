# =============================================================================
# RedCrossQuest - Docker dev environment
# Entry point: `make` (alias of `make help`)
# Full bootstrap from a clean clone: `make init`
# =============================================================================
SHELL := /usr/bin/env bash
.DEFAULT_GOAL := help

DC       := docker compose
PHP_EXEC := $(DC) exec -T php-fpm
NODE_EXEC:= $(DC) exec -T node-client

.PHONY: help init up down restart build rebuild logs ps \
        composer-install composer-update composer npm bower \
        phinx-migrate phinx-rollback phinx \
        gulp-serve gulp-build \
        shell-php shell-node \
        clean nuke doctor

help: ## Show available targets
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  \033[36m%-22s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

# ----------------------------------------------------------------------------
# Lifecycle
# ----------------------------------------------------------------------------
init: ## One-shot bootstrap: .env + build + deps + up (Phinx migrations run manually)
	@./run_local.sh

up: ## Start the stack in the background
	$(DC) up -d

down: ## Stop and remove the containers (keeps volumes)
	$(DC) down

restart: down up ## Restart the stack

build: ## Build all images
	$(DC) build

rebuild: ## Rebuild images from scratch (no cache)
	$(DC) build --no-cache --pull

logs: ## Tail logs from all services (Ctrl-C to quit)
	$(DC) logs -f --tail=100

ps: ## List running services
	$(DC) ps

# ----------------------------------------------------------------------------
# Dependencies
# ----------------------------------------------------------------------------
composer-install: ## composer install inside the php-fpm container
	$(PHP_EXEC) composer install --no-interaction

composer-update: ## composer update
	$(PHP_EXEC) composer update --no-interaction

composer: ## Arbitrary composer cmd: `make composer cmd="require foo/bar"`
	$(PHP_EXEC) composer $(cmd)

npm: ## npm in the node-client container: `make npm cmd="install"`
	$(NODE_EXEC) npm $(cmd)

bower: ## bower in the node-client container: `make bower cmd="install"`
	$(NODE_EXEC) bower --allow-root $(cmd)

# ----------------------------------------------------------------------------
# Database migrations (Phinx) — run against the host's own phinx.yml; pick the
# environment with `env=<name>` (defaults to whatever `default_environment`
# points to in your phinx.yml).
# ----------------------------------------------------------------------------
PHINX_ENV := $(if $(env),-e $(env),)

phinx-migrate: ## Run all pending phinx migrations (override env with env=<name>)
	$(PHP_EXEC) php vendor/bin/phinx migrate -c /app/server/phinx.yml $(PHINX_ENV)

phinx-rollback: ## Rollback the last migration (override env with env=<name>)
	$(PHP_EXEC) php vendor/bin/phinx rollback -c /app/server/phinx.yml $(PHINX_ENV)

phinx: ## Arbitrary phinx cmd: `make phinx cmd="status" [env=local]`
	$(PHP_EXEC) php vendor/bin/phinx $(cmd) -c /app/server/phinx.yml $(PHINX_ENV)

# ----------------------------------------------------------------------------
# Front-end
# ----------------------------------------------------------------------------
gulp-serve: ## Start `gulp serve` (attached, Ctrl-C to stop)
	$(DC) exec node-client gulp serve

gulp-build: ## Run the production build (dist/)
	$(NODE_EXEC) bash -c "./build.sh"

# ----------------------------------------------------------------------------
# Shells
# ----------------------------------------------------------------------------
shell-php: ## Open a shell in the php-fpm container
	$(DC) exec php-fpm bash

shell-node: ## Open a shell in the node-client container
	$(DC) exec node-client bash

# ----------------------------------------------------------------------------
# Maintenance
# ----------------------------------------------------------------------------
clean: ## Remove containers + named volumes (composer/npm caches only; DB is external)
	$(DC) down -v

nuke: clean ## Remove containers, volumes AND images
	$(DC) down -v --rmi local

doctor: ## Quick diagnostic of the running stack
	@echo "== docker version ==" && docker version --format '{{.Server.Version}}'
	@echo "== services =="       && $(DC) ps
	@echo "== PHP info =="       && $(PHP_EXEC) php -v || true
	@echo "== Node info =="      && $(NODE_EXEC) node -v || true
	@echo "== rcq_mysql =="      && docker ps --filter name=rcq_mysql --format '{{.Names}}\t{{.Status}}' || true
