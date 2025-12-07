#!/usr/bin/env just --justfile

[group: 'Docker']
up:
  docker compose up -d

[group: 'Docker']
down:
  docker compose down

[group: 'Docker']
build:
  docker compose build \
    --build-arg USER_ID=$(id -u ${USER}) \
    --build-arg GROUP_ID=$(id -g ${USER}) \

[group: 'Docker']
logs:
  docker compose logs -f

[group: 'App']
command *COMMAND:
  docker compose run --rm -it php {{ COMMAND }}

[group: 'App']
console *COMMAND:
  just command php bin/console {{ COMMAND }}

[group: 'App']
shell:
  just command bash

[group: 'App']
composer *COMMAND:
    just command composer {{ COMMAND }}

[group: 'App']
make *COMMAND:
    just console make:{{ COMMAND }}

[group: 'Test']
test:
    just command ./bin/phpunit

[group: 'Clean']
lint:
    just command php-cs-fixer fix .

[group: 'Database']
reset-db ENV='dev':
    just console doctrine:database:drop --if-exists --env={{ ENV }} --force && \
    just console doctrine:database:create --env={{ ENV }} && \
    just console doctrine:migrations:migrate --env={{ ENV }} --no-interaction

prod:
    docker compose -f compose.prod.yaml build
    docker compose -f compose.prod.yaml up
