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
--env "HTTPD_SERVERNAME=**sub.do.main**" \
--env "PUSHOVER_APP_TOKEN=azGDORePK8gMaC0QOYAMyEEuzJnyUi" \
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
      - HTTPD_SERVERNAME=**sub.do.main**
      - PUSHOVER_APP_TOKEN=azGDORePK8gMaC0QOYAMyEEuzJnyUi
    volumes:
      - continuum-config:/config

volumes:
  continuum-config:
```
