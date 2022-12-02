#!/bin/sh

php /app/game-bot migrate --force
php /app/game-bot run
