#!/usr/bin/env bash

export COMPOSE_PROJECT_NAME=${COMPOSE_PROJECT_NAME:-testpages}

#docker-compose --x-networking pull

docker-compose --x-networking up -d & wait
docker-compose --x-networking ps

docker-compose --x-networking run php sh /app/src/run.sh

docker-compose --x-networking run php codecept run -c /app/vendor/dmstr/yii2-pages-module/codeception.yml