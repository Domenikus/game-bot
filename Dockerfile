FROM php:8.1-cli-alpine

ARG version

RUN apk add ncurses composer libzip-dev
RUN docker-php-ext-install mysqli pdo pdo_mysql posix zip && docker-php-ext-enable mysqli pdo pdo_mysql posix zip
RUN mkdir -p /usr/src/game-bot

COPY . /usr/src/game-bot/

WORKDIR /usr/src/game-bot
RUN php /usr/bin/composer.phar install --no-scripts
RUN chmod 755 vendor/laravel-zero/framework/bin/box
RUN php game-bot app:build --build-version=${version}
RUN php /usr/bin/composer.phar dump-autoload --classmap-authoritative --no-dev -vvv --optimize

COPY entrypoint.sh /usr/src/game-bot/entrypoint.sh
RUN chmod +x /usr/src/game-bot/entrypoint.sh

WORKDIR /usr/src/game-bot/builds
ENTRYPOINT ["/bin/sh", "/usr/src/game-bot/entrypoint.sh"]
