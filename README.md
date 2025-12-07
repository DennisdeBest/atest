# File conversion

## Setup

This project runs on Docker. 
It was developed on Linux.

It will instantiate a FrankenPHP server and a PostgreSQL database.

### Requirements

- Docker

## Usage

### Development

[Just](https://github.com/casey/just) is used to simplify the commands for the development environment.

Build the docker image:

```shell
just build
```

Start it with:

```shell
just up
```

By default, the server runs on port 8086.
The api documentation is available on [localhost:8086/api](http://localhost:8086/api).

check the logs with:

```shell
just logs
```

Or in one line:

```shell
just build up logs
```

#### Test

The tests are setup with [PHPUnit](https://phpunit.de/).
They can be run with the following command:

```shell
just test
```

#### Lint

The code is linted with PHP-CS-Fixer.
It can be run with the following command:

```shell
just lint
```

### Production

The development and test environments run the workers synschronously. To test the production environment, you need to run the workers asynchronously.
There is a `compose.prod.yml` file for this purpose.

It can be started with:

```shell
just prod
```

Or: 

```shell
docker compose -f compose.prod.yml up
```

It will be available on port [localhost:8087](http://localhost:8087/api).

The uploaded files will take **120** seconds to be processed.
During the progress it is possible to check the state.

Once completed the new file can be downloaded from the `download` endpoint.
