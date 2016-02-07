#!/usr/bin/env bash

export COMPOSE_PROJECT_NAME=${COMPOSE_PROJECT_NAME:-testpages}

#docker-compose pull

docker-compose up -d & wait
docker-compose ps

docker-compose run phpfpm sh /app/src/setup.sh

docker-compose run phpfpm codecept run -c /app/vendor/dmstr/yii2-pages-module/codeception.yml