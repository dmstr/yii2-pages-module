.PHONY: build test

DOCKER_COMPOSE  ?= docker-compose

UNAME_S := $(shell uname -s)
ifeq ($(UNAME_S), Darwin)
	OPEN_CMD        ?= open
	DOCKER_HOST_IP  ?= $(shell echo $(DOCKER_HOST) | sed 's/tcp:\/\///' | sed 's/:[0-9.]*//')
else
	OPEN_CMD        ?= xdg-open
	DOCKER_HOST_IP  ?= 127.0.0.1
endif

up:
	$(DOCKER_COMPOSE) up -d

clean:
	$(DOCKER_COMPOSE) kill
	$(DOCKER_COMPOSE) rm -fv --all
	$(DOCKER_COMPOSE) down

open:	 ##@docker open application web service in browser
	$(OPEN_CMD) http://$(DOCKER_HOST_IP):$(shell $(DOCKER_COMPOSE) port nginx 80 | sed 's/[0-9.]*://')

open-db:	 ##@docker open application web service in browser
	$(OPEN_CMD) mysql://root:secret@$(DOCKER_HOST_IP):$(shell $(DOCKER_COMPOSE) port db 3306/ | sed 's/[0-9.]*://')

bash:	 ##@docker open application development bash
	$(DOCKER_COMPOSE) run phpfpm bash

setup:	 ##@docker open application development bash
	$(DOCKER_COMPOSE) run phpfpm yii db/create
	$(DOCKER_COMPOSE) run phpfpm yii migrate
	$(DOCKER_COMPOSE) run phpfpm yii user/create admin@example.com admin
	$(DOCKER_COMPOSE) run phpfpm yii user/password admin admin

test:
	$(DOCKER_COMPOSE) run -e YII_ENV=test phpfpm codecept run