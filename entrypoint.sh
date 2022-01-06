#!/bin/sh

php /usr/src/game-bot/game-bot migrate --force
php /usr/src/game-bot/game-bot run
