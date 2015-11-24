#!/usr/bin/env bash

docker-compose --x-networking pull

docker-compose --x-networking up -d & wait
docker-compose --x-networking run php sh /app/src/init.sh

docker-compose --x-networking run php codecept run -c /app/vendor/dmstr/yii2-pages-module/codeception.yml