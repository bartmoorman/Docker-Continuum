### Usage
```
docker run \
--detach \
--name continuum \
--publish 6082:6082 \
--env "HTTPD_SERVERNAME=**sub.do.main**" \
--env "PUSHOVER_APP_TOKEN=azGDORePK8gMaC0QOYAMyEEuzJnyUi" \
--volume continuum-config:/config \
bmoorman/continuum:latest
```
