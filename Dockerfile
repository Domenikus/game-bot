FROM php:7.3.28-cli-alpine3.12

RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable pdo_mysql
RUN mkdir -p /usr/src/apex-bot

COPY /builds/apex-bot /usr/src/apex-bot/
COPY entrypoint.sh /usr/src/apex-bot/entrypoint.sh
RUN chmod +x /usr/src/apex-bot/entrypoint.sh

WORKDIR /usr/src/apex-bot

ENTRYPOINT ["/bin/sh", "/usr/src/apex-bot/entrypoint.sh"]
