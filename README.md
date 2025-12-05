# File conversion

## Setup

This project runs on Docker. 
It was developed on Linux.

It will instantiate a FrankenPHP server and a PostgreSQL database.

### Requirements

- Docker

## Usage

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

