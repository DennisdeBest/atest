# TODO

## Versions

- Upgrade to Symfony 8 once the libraries are ready.
- Upgrade to PHP 8.5 once the libraries are ready.

## CI/CD

- Add github actions for tests
- Add GHRC docker image

## Security

- Add rate limiting
- Add Bearer token authentication

## Storage

- Upload to S3 instead of local disk ([RustFS](https://rustfs.com/))
- Use [FlySystem](https://github.com/thephpleague/flysystem-bundle)

## Reliability

- Add retry and dead-letter queue for failed conversions (Messenger failure transport).

## Features

- Add job cancellation endpoint (DELETE /files/{id}) that marks a job as cancelled if it hasn’t started yet.

## Metrics

### Usage

- Upload and Download credits

### Performance

- Test file conversion performance and libraries
