# —— Inspired by 💡———————————————————————————————————————————————————————————————
## https://www.strangebuzz.com/en/snippets/the-perfect-makefile-for-symfony
## https://symfony.com/doc/current/the-fast-track/en/17-tests.html

# Setup ————————————————————————————————————————————————————————————————————————
PROJECT       = guestbook
EXEC_PHP      = php
REDIS         = redis-cli
GIT           = git
GIT_AUTHOR    = Nelsonrodmar
SYMFONY       = $(EXEC_PHP) bin/console
SYMFONY_BIN   = symfony
COMPOSER      = composer
DOCKER        = docker
DOCKER_COMP   = docker-compose
BREW          = brew
.DEFAULT_GOAL = help
#.PHONY       = # Not needed for now

SHELL := /bin/bash

## —— Start in local 🚀 —————————————————————————————————————————————————————————
start:
	symfony server:start -d
	docker-compose up -d
	symfony run -d --watch=config,src,templates,vendor symfony console messenger:consume async
	cd spa; symfony server:start -d --passthru=index.html
	cd spa; API_ENDPOINT=`symfony var:export SYMFONY_PROJECT_DEFAULT_ROUTE_URL --dir=..` symfony run -d --watch=webpack.config.js yarn encore dev --watch
.PHONY: start

## —— Install ⚙️ ——————————————————————————————————————————————————————————————————
install:
	symfony composer install --no-progress --no-suggest --prefer-dist --optimize-autoloader
	cd spa; yarn install
.PHONY: install

## —— Stop in local 🛑 —————————————————————————————————————————————————————————
stop:
	symfony server:stop
	docker-compose down
	cd spa; symfony server:stop
.PHONY: stop


## —— Coding standards 🎨 ——————————————————————————————————————————————————————
cs:
	make codesniffer
	make stan
	make cs-fix
.PHONY: cs

codesniffer:
	./vendor/bin/php-cs-fixer fix src/
.PHONY: codesniffer
stan:
	./vendor/bin/phpstan analyse src/ --memory-limit 1G
.PHONY: stan
cs-fix:
	./vendor/squizlabs/php_codesniffer/bin/phpcs -n -p src/
.PHONY: fix


## —— Tests ✅ ——————————————————————————————————————————————————————————————————
tests:
	symfony console doctrine:fixtures:load -n
	symfony php bin/phpunit
.PHONY: tests



## —— Stats 📊 —————————————————————————————————————————————————————————————————
stats: ## Commits by the hour for the main author of this project
	$(info ************  List total commits by author ************)
	@git shortlog -sn
.PHONY: stats