# syntax=docker/dockerfile:1.7
# Nginx image reproducing Google App Engine `serve` wrapper behaviour.
# Static assets are served directly, PHP requests are forwarded to php-fpm:9000.
FROM nginx:1.27-alpine

# Remove default vhost, install our configuration
RUN rm -f /etc/nginx/conf.d/default.conf
COPY docker/nginx/nginx.conf         /etc/nginx/nginx.conf
COPY docker/nginx/rcq-backend.conf   /etc/nginx/conf.d/rcq-backend.conf

# Healthcheck endpoint must be reachable
HEALTHCHECK --interval=30s --timeout=5s --retries=3 \
    CMD wget -q -O- http://127.0.0.1:8080/healthz || exit 1

EXPOSE 8080
