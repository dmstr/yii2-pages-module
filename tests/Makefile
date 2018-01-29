.PHONY: build run-tests

DOCKER_COMPOSE  ?= docker-compose
PHP_SERVICE ?= php
WEB_SERVICE ?= php
CODECEPT_SERVICE ?= forrest

UNAME_S := $(shell uname -s)
ifeq ($(UNAME_S), Darwin)
	OPEN_CMD        ?= open
	DOCKER_HOST_IP  ?= $(shell echo $(DOCKER_HOST) | sed 's/tcp:\/\///' | sed 's/:[0-9.]*//')
else
	OPEN_CMD        ?= xdg-open
	DOCKER_HOST_IP  ?= 127.0.0.1
endif

# Targets
# -------

default: help

all: build dev up setup

dev:    ##@development install composer package (enable host-volume in docker-compose config)
dev:
	#
	# Running composer installation in development environment
	# This may take a while on your first install...
	#
	$(DOCKER_COMPOSE) run -w /repo/tests/project --rm php composer install

up: ##@docker start application stack
	$(DOCKER_COMPOSE) up -d
	$(DOCKER_COMPOSE) ps

build: ##@docker build application image
	$(DOCKER_COMPOSE) build --pull

clean: ##@docker cleanup application stack
	$(DOCKER_COMPOSE) kill
	$(DOCKER_COMPOSE) down -v --remove-orphans

open:	 ##@docker open application web service in browser
	$(OPEN_CMD) http://$(DOCKER_HOST_IP):$(shell $(DOCKER_COMPOSE) port $(WEB_SERVICE) 80 | sed 's/[0-9.]*://')

open-db:	 ##@docker open application web service in browser
	$(OPEN_CMD) mysql://root:secretadmin@$(DOCKER_HOST_IP):$(shell $(DOCKER_COMPOSE) port db 3306 | sed 's/[0-9.]*://')

open-vnc:	 ##@test open application database service in browser
	$(OPEN_CMD) vnc://x:secret@$(DOCKER_HOST_IP):$(shell $(DOCKER_COMPOSE) port firefox 5900 | sed 's/[0-9.]*://')

bash:	 ##@docker open application development bash
	$(DOCKER_COMPOSE) run --rm -e YII_ENV=test $(PHP_SERVICE) bash

setup:	 ##@docker wait for database and setup application stack
	$(DOCKER_COMPOSE) run --rm $(PHP_SERVICE) yii db/wait-for-connection mysql:host=db pages pages
	$(DOCKER_COMPOSE) run --rm $(PHP_SERVICE) yii migrate --interactive=0

clean-tests: ##@test clean codeception output
	$(DOCKER_COMPOSE) run --rm $(PHP_SERVICE) codecept clean

run-tests: ##@test run codeception tests
	$(DOCKER_COMPOSE) run --rm -e YII_ENV=test $(PHP_SERVICE) /repo/tests/project/vendor/bin/codecept run e2e,cli,unit -x optional

lint: ##@test run php cs-fixer
	$(DOCKER_COMPOSE) run --rm $(PHP_SERVICE) /app/vendor/bin/php-cs-fixer fix --format=txt -v --dry-run .

# Help based on https://gist.github.com/prwhite/8168133 thanks to @nowox and @prwhite
# And add help text after each target name starting with '\#\#'
# A category can be added with @category

HELP_FUN = \
		%help; \
		while(<>) { push @{$$help{$$2 // 'options'}}, [$$1, $$3] if /^([\w-]+)\s*:.*\#\#(?:@([\w-]+))?\s(.*)$$/ }; \
		print "\nusage: make [target ...]\n\n"; \
	for (keys %help) { \
		print "$$_:\n"; \
		for (@{$$help{$$_}}) { \
			$$sep = "." x (25 - length $$_->[0]); \
			print "  $$_->[0]$$sep$$_->[1]\n"; \
		} \
		print "\n"; }

help:				##@system show this help
	#
	# General targets
	#
	@perl -e '$(HELP_FUN)' $(MAKEFILE_LIST)