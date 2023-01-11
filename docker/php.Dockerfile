# Composer installation
FROM php:8.2-fpm AS composer

WORKDIR /app

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions @composer

COPY / /app
RUN composer install --optimize-autoloader --no-dev

# Vite.js build
FROM node:lts-alpine AS node

WORKDIR /app

# Cache layer for "npm ci"
COPY /package.json /package-lock.json /app/
RUN npm ci
# Copy JavaScript
COPY /vite.config.js /app/
COPY /resources/ /app/resources/
# Build using Vite.js
RUN npm run build

# Final Image
FROM php:8.2-fpm

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions bcmath pdo_pgsql redis

WORKDIR /app

COPY / /app
COPY --from=node /app/public/build /app/public/build
COPY --from=composer /app/vendor/ /app/vendor

RUN chown --recursive www-data:www-data /app/storage

RUN echo "php artisan optimize --no-ansi && php-fpm" >> /usr/bin/entrypoint.sh && \
    chmod +x /usr/bin/entrypoint.sh

CMD ["/usr/bin/entrypoint.sh"]