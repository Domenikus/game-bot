FROM php:7.4.27-cli-alpine3.15

RUN docker-php-ext-install mysqli pdo pdo_mysql posix && docker-php-ext-enable pdo_mysql posix
RUN mkdir -p /usr/src/game-bot

COPY /builds/game-bot /usr/src/game-bot/
COPY entrypoint.sh /usr/src/game-bot/entrypoint.sh
RUN chmod +x /usr/src/game-bot/entrypoint.sh

WORKDIR /usr/src/game-bot

ENTRYPOINT ["/bin/sh", "/usr/src/game-bot/entrypoint.sh"]
