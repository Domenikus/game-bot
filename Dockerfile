FROM php:8.0-cli-alpine3.15

RUN $(php -r '$extensionInstalled = array_map("strtolower", \get_loaded_extensions(false));$requiredExtensions = ["zlib", "phar", "openssl", "pcre", "tokenizer"];$extensionsToInstall = array_diff($requiredExtensions, $extensionInstalled);if ([] !== $extensionsToInstall) {echo \sprintf("docker-php-ext-install %s", implode(" ", $extensionsToInstall));}echo "echo \"No extensions\"";')
RUN docker-php-ext-install mysqli pdo pdo_mysql posix && docker-php-ext-enable mysqli pdo pdo_mysql posix
RUN apk add ncurses composer zlib
RUN mkdir -p /usr/src/game-bot

COPY . /usr/src/game-bot/
WORKDIR /usr/src/game-bot

RUN php /usr/bin/composer.phar install --no-scripts
RUN chmod 755 vendor/laravel-zero/framework/bin/box
RUN php game-bot app:build --build-version=1
RUN php /usr/bin/composer.phar dump-autoload --classmap-authoritative --no-dev -vvv --optimize

COPY entrypoint.sh /usr/src/game-bot/entrypoint.sh
RUN chmod +x /usr/src/game-bot/entrypoint.sh

ENTRYPOINT ["/bin/sh", "/usr/src/game-bot/entrypoint.sh"]
