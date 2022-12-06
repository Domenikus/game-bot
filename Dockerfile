FROM php:8.1-cli-alpine

ARG version

RUN apk add ncurses composer libzip-dev
RUN docker-php-ext-install mysqli pdo pdo_mysql posix zip && docker-php-ext-enable mysqli pdo pdo_mysql posix zip
RUN mkdir -p /usr/src/game-bot

COPY . /usr/src/game-bot/

RUN php /usr/bin/composer.phar install --working-dir=/usr/src/game-bot --no-scripts
RUN chmod 755 /usr/src/game-bot/vendor/laravel-zero/framework/bin/box
RUN php /usr/src/game-bot/game-bot app:build --build-version=${version}
RUN php /usr/bin/composer.phar dump-autoload --working-dir=/usr/src/game-bot --classmap-authoritative --no-dev -vvv --optimize

RUN mkdir -p /app && mkdir -p /app/storage  \
    && mkidr -p /app/views \
    && cp /usr/src/game-bot/builds/game-bot /app  \
    && cp /usr/src/game-bot/entrypoint.sh /app  \
    && rm -R /usr/src/game-bot
RUN chmod +x /app/entrypoint.sh

WORKDIR /app
ENTRYPOINT ["/bin/sh", "/app/entrypoint.sh"]
