FROM php:7.3.28-cli-alpine3.12
RUN mkdir -p /usr/src/apex-bot

COPY /builds/apex-bot /usr/src/apex-bot/

WORKDIR /usr/src/apex-bot

ENTRYPOINT ["php", "apex-bot", "run"]
