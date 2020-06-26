### Docker Run
```
docker run \
--detach \
--name memcached \
--restart unless-stopped \
memcached:latest

docker run \
--detach \
--name continuum \
--restart unless-stopped \
--link memcached \
--publish 6082:6082 \
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
    restart: unless-stopped

  continuum:
    image: bmoorman/continuum:latest
    container_name: continuum
    restart: unless-stopped
    depends_on:
      - memcached
    ports:
      - "6082:6082"
    volumes:
      - continuum-config:/config

volumes:
  continuum-config:
```

### Environment Variables
|Variable|Description|Default|
|--------|-----------|-------|
|TZ|Sets the timezone|`America/Denver`|
|HTTPD_SERVERNAME|Sets the vhost servername|`localhost`|
|HTTPD_PORT|Sets the vhost port|`6082`|
|HTTPD_SSL|Set to anything other than `SSL` (e.g. `NO_SSL`) to disable SSL|`SSL`|
|HTTPD_REDIRECT|Set to anything other than `REDIRECT` (e.g. `NO_REDIRECT`) to disable SSL redirect|`REDIRECT`|
|PUSHOVER_APP_TOKEN|Used to retrieve sounds from the Pushover API|`<empty>`|
|MEMCACHED_HOST|Sets the Memcached host|`memcached`|
|MEMCACHED_PORT|Sets the Memcached port|`11211`|
