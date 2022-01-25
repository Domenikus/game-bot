#!/bin/sh

php /usr/src/game-bot/builds/game-bot migrate --force
php /usr/src/game-bot/builds/game-bot run
