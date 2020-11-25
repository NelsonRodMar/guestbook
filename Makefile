SHELL := /bin/bash

tests:
	symfony console doctrine:fixtures:load -n
	symfony php bin/phpunit
.PHONY: tests

start:
	symfony server:start -d
	docker-compose up -d
	symfony run -d --watch=config,src,templates,vendor symfony console messenger:consume async
.PHONY: start

stop:
	symfony server:stop
	docker-compose down
.PHONY: stop