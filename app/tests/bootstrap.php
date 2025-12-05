<?php

use Symfony\Component\Dotenv\Dotenv;


require dirname(__DIR__) . '/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');
}


if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

passthru(sprintf(
    'APP_ENV=test php "%s/../bin/console" cache:clear --no-warmup',
    __DIR__
));

passthru(sprintf(
    'APP_ENV=test php "%s/../bin/console" doctrine:database:create',
    __DIR__
));

passthru(sprintf(
    'APP_ENV=test php "%s/../bin/console" doctrine:migrations:migrate --no-interaction',
    __DIR__
));
