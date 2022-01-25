FROM php:8.0.14-cli-alpine3.15

RUN docker-php-ext-install mysqli pdo pdo_mysql posix && docker-php-ext-enable pdo_mysql posix
RUN apk add ncurses composer zlib
RUN mkdir -p /usr/src/game-bot

COPY . /usr/src/game-bot/
WORKDIR /usr/src/game-bot

RUN php /usr/bin/composer.phar install --no-scripts --no-autoloader
RUN php /usr/bin/composer.phar dump-autoload --optimize
RUN chmod 755 vendor/laravel-zero/framework/bin/box

RUN php /usr/src/game-bot app:build --build-version=1
COPY entrypoint.sh /usr/src/game-bot/entrypoint.sh
RUN chmod +x /usr/src/game-bot/entrypoint.sh

ENTRYPOINT ["/bin/sh", "/usr/src/game-bot/entrypoint.sh"]
