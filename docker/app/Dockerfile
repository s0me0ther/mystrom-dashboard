#############################################################################
#                                 BASE STAGE                                #
#############################################################################
FROM php:8.3-cli-alpine AS base

ARG UID=1000
ARG GID=1000

RUN apk add --no-cache shadow

RUN addgroup -g ${GID} app && \
    adduser -D -u ${UID} -G app app

RUN mkdir -p /opt/mystrom-collector && \
    chown app:app /opt/mystrom-collector -R

USER app

WORKDIR /opt/mystrom-collector

# integrate composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# integrate mystrom collector
COPY --from=collector /mystrom-collector/target/release/mystrom-collector /usr/local/bin/mystrom-collector

#############################################################################
#                                 FETCH STAGE                               #
#############################################################################
FROM base AS fetch

CMD ["php", "fetch.php"]
