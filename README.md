### Docker Run
```
docker run \
--detach \
--name memcached \
memcached:latest

docker run \
--detach \
--name continuum \
--link memcached \
--publish 6082:6082 \
--env "PUSHOVER_APP_TOKEN=<token>" \
--volume continuum-config:/config \
bmoorman/continuum:latest
```

### Docker Compose
```
version: "3.7"
services:
  memcached:
    image: memcached:latest
    container_name: memcached

  continuum:
    image: bmoorman/continuum:latest
    container_name: continuum
    depends_on:
      - memcached
    ports:
      - "6082:6082"
    environment:
      - PUSHOVER_APP_TOKEN=<token>
    volumes:
      - continuum-config:/config

volumes:
  continuum-config:
```

### Environment Variables
* **TZ** Sets the timezone. Default `America/Denver`.
* **HTTPD_SERVERNAME** Sets the vhost servername. Default `localhost`.
* **HTTPD_PORT** Sets the vhost port. Default `6082`.
* **HTTPD_SSL** Set to anything other than `SSL` (e.g. `NO_SSL`) to disable SSL. Default `SSL`.
* **HTTPD_REDIRECT** Set to anything other than `REDIRECT` (e.g. `NO_REDIRECT`) to disable SSL redirect. Default `REDIRECT`.
* **PUSHOVER_APP_TOKEN** Used to retrieve sounds from the Pushover API. Default `<empty>`
