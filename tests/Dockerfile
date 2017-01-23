FROM dmstr/yii2-app:0.4.0

# Missing directories
RUN mkdir -p /app/src/migrations

# Composer
COPY composer.* /app/
RUN composer install --prefer-dist -o
