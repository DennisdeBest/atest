# File Conversion API

A simple long-running job API: upload a file, wait for processing, then download the converted output.

Supported input formats: CSV, JSON, XLSX, ODS  
Supported output formats: JSON, XML

Processing is intentionally slow (simulated with a sleep) to demonstrate asynchronous job execution.

## Architecture

This project uses:

- Symfony (API + Messenger)
- FrankenPHP web server
- PostgreSQL for job tracking
- Messenger workers
    - synchronous in development and tests
    - asynchronous in production mode (`compose.prod.yml`)
- Local disk storage for uploaded and converted files

### Workflow

1. `POST /files`  
   Uploads a source file and selects an output format. Returns a `File` resource with a `uid`, initial `status` (`queued`), and HATEOAS-style `links` (including the status and download URLs).

2. A worker processes the job (synchronously in dev/tests, asynchronously in production), sleeps to simulate a long-running task, and generates a dummy converted file.

3. `GET /files/{uid}`  
   Returns the current `File` resource:
    - `uid` (UUID)
    - `status` (`queued`, `processing`, `finished`, `failed`)
    - `format` (`json` or `xml`)
   - a `links` object that at minimum contains a `status` link. It will contain the download link when the conversion is finished.


4. `GET /files/{uid}/download`  
   Streams the converted output file (JSON or XML) when the `status` is `finished`.
   Returns `404` if the file does not exist or the conversion is not finished.


## Setup

This project runs on Docker.  
It was developed on Linux but works anywhere Docker is available.

Running the stack will start:

- a FrankenPHP server
- a PostgreSQL database

### Requirements

- Docker
- (Optional) Just (https://github.com/casey/just) for command shortcuts

## Development

Build the Docker image:

```sh
just build
```

Start the environment:

```sh
just up
```

The API will be available at:

[http://localhost:8086/api](http://localhost:8086/api)

View logs:

```sh
just logs
```

Or combined:

```sh
just build up logs
```

## Example Workflow

```sh
# 1. Create a job by uploading a file
curl -F "file=@app/tests/fixtures/valid.csv" -F "outputFormat=json" http://localhost:8086/api/files

# This returns: {"@context":"\/api\/contexts\/File","@id":"\/api\/files\/76a1b868-4247-4d50-97f1-423bd0ff2b93","@type":"File","uid":"76a1b868-4247-4d50-97f1-423bd0ff2b93","status":"finished","links":{"status":"\/api\/files\/76a1b868-4247-4d50-97f1-423bd0ff2b93","download":"\/api\/files\/76a1b868-4247-4d50-97f1-423bd0ff2b93\/download"},"format":"json"}%

# 2. Poll job status
curl http://localhost:8086/api/files/76a1b868-4247-4d50-97f1-423bd0ff2b93

# 3. Download the converted file when done
curl -OJ http://localhost:8086/api/files/curl http://localhost:8086/api/files/76a1b868-4247-4d50-97f1-423bd0ff2b93/download
```

## Tests

Tests use PHPUnit and cover the full workflow (upload → job creation → simulated processing → status → download).

Run the test suite:

```sh
just test
```

## Linting

The codebase is formatted with PHP-CS-Fixer:

```sh
just lint
```

## Production Mode (asynchronous workers)

In development and test environments, workers run synchronously.

To test asynchronous job execution, use the production compose file:

```sh
just prod
```

or:

```sh
docker compose -f compose.prod.yml up
```

The API will be available at:

```
http://localhost:8087/api
```

Uploaded files will take 120 seconds to process.  
During that time the job status can be queried, and once complete, the file is available at the download endpoint.

## Notes

- The conversion step is simulated and does not perform real format transformation.
- The implementation is intentionally minimal.
- Additional ideas and next steps are documented in [todo.md](docs/99_todo.md).
