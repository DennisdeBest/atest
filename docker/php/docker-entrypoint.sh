#!/bin/sh
set -e


echo "Starting PHP with user $(whoami) $(id -u):$(id -g)"


mkdir -p /app/var/cache

if [ "$1" = 'frankenphp' ]; then
  if [ "$APP_ENV" = 'prod' ]; then
    php bin/console doctrine:migrations:migrate --no-interaction
    php bin/console cache:clear
    php bin/console cache:warmup
  else
    composer install --no-interaction
  fi
fi

if [ "$1" = 'build' ]; then
  composer install --no-interaction
  exit 0
fi

exec "$@"
