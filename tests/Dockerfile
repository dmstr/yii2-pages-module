FROM dmstr/yii2-app:0.6.2

# Install npm and lessc
RUN curl -sL https://deb.nodesource.com/setup_8.x | bash - \
 && apt-get install -y npm \
 && npm install -g less
ENV PATH /app:/repo/tests/project/vendor/bin:/usr/lib/node_modules/less/bin:$PATH

ENV COMPOSER=/repo/tests/project/composer.json

# Clean vendor from base image
RUN rm -rf /app/vendor
RUN ln -s /repo/tests/project/vendor /app/vendor