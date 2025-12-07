#syntax=docker/dockerfile:1.4

# Versions
FROM dunglas/frankenphp:1.10-php8.4.15-trixie AS frankenphp_upstream
FROM composer/composer:2.9.2-bin AS composer_upstream

# The different stages of this Dockerfile are meant to be built into separate images
# https://docs.docker.com/develop/develop-images/multistage-build/#stop-at-a-specific-build-stage
# https://docs.docker.com/compose/compose-file/#target

# Base FrankenPHP image
FROM frankenphp_upstream AS frankenphp_base

WORKDIR /app

# persistent / runtime deps
# hadolint ignore=DL3018
RUN apt-get update && apt-get install -y --no-install-recommends \
		acl \
		file \
		gettext \
		git \
        wget \
	;

RUN set -eux; \
	install-php-extensions \
		apcu \
		intl \
		opcache \
		zip \
        gd \
        pdo_pgsql \
        bcmath \
	;

COPY --link docker/php/conf.d/app.ini $PHP_INI_DIR/conf.d/
COPY --link --chmod=755 docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
COPY --link docker/php/Caddyfile /etc/caddy/Caddyfile

ENTRYPOINT ["docker-entrypoint"]

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PATH="${PATH}:/root/.composer/vendor/bin"

COPY --from=composer_upstream --link /composer /usr/bin/composer

HEALTHCHECK --start-period=60s CMD curl -f http://localhost:2019/metrics || exit 1
CMD [ "frankenphp", "run", "--config", "/etc/caddy/Caddyfile" ]

# Dev FrankenPHP image
FROM frankenphp_base AS frankenphp_dev

ENV APP_ENV=dev
VOLUME /app/var/

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

RUN set -eux; \
	install-php-extensions \
		xdebug \
	;

COPY --link docker/php/conf.d/app.dev.ini $PHP_INI_DIR/conf.d/

ARG PHP_CS_FIXER_VERSION=v3.91.2

RUN wget "https://github.com/FriendsOfPHP/PHP-CS-Fixer/releases/download/$PHP_CS_FIXER_VERSION/php-cs-fixer.phar" -O php-cs-fixer && \
    chmod a+x php-cs-fixer && \
    mv php-cs-fixer /usr/local/bin/php-cs-fixer

RUN git config --global --add safe.directory /app

ARG UID=1000
ARG GID=1000

RUN groupadd -g ${GID} appgroup \
 && useradd -m -u ${UID} -g ${GID} appuser

RUN mkdir -p /app/var/cache

RUN chown --changes --silent --no-dereference --recursive \
        ${UID}:${GID} \
        /app \
        /config \
        /data \
        /var/cache \
        /var/log \
    ;

USER appuser

CMD [ "frankenphp", "run", "--config", "/etc/caddy/Caddyfile", "--watch" ]

# Prod FrankenPHP image (final image used in compose.prod.yml)
FROM frankenphp_base AS frankenphp_prod

ENV APP_ENV=prod
ENV APP_DEBUG=0

WORKDIR /app

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY --link docker/php/conf.d/app.prod.ini $PHP_INI_DIR/conf.d/
COPY --link docker/php/Caddyfile /etc/caddy/Caddyfile

COPY --link ./app/composer.* ./app/symfony.* ./

RUN set -eux; \
  composer install \
    --no-cache \
    --prefer-dist \
    --no-dev \
    --no-scripts \
    --no-progress \
    --no-interaction

COPY --link ./app /app

RUN rm -rf docker/


RUN set -eux; \
    mkdir -p var/cache var/log var/uploads var/conversions; \
    chown -R www-data:www-data var


RUN set -eux; \
    composer dump-autoload --classmap-authoritative --no-dev; \
    composer dump-env prod; \
    composer run-script --no-dev post-install-cmd; \
    chmod +x bin/console; \
    sync
