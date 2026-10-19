# ============================================================================
# SPEI - Sistema de Préstamo de Equipo Institucional
# ============================================================================
# @project      SPEI (Sistema de Préstamo de Equipo Institucional)
# @module       Infraestructura
# @file         Dockerfile
# @path         docker/Dockerfile
# @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
# @date         2026-10-18
# @lastModified 2026-10-18
# @version      v1.0.0
# @license      Propietario - BigBitByte / Institución Educativa
#               Todos los derechos reservados.
# @description  Imagen del backend Laravel 12 sobre PHP 8.3-FPM. La etapa
#               "dev" agrega PCOV para medir cobertura con Pest.
# @requisitos   RS-001, RNF-009, RNF-011
# @see          Manual de Desarrollo v1.2, Tablas 14 y 16; DT-017
# ============================================================================

FROM php:8.3-fpm-alpine@sha256:454b11c8907e32878ce92e87b13c14e1bbe6e1b10f4f96d4a19c9b62ec675d41 AS base

RUN apk add --no-cache icu-dev libzip-dev oniguruma-dev git unzip \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS linux-headers \
    && docker-php-ext-install pdo_mysql bcmath intl zip pcntl opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

COPY --from=composer:2.8@sha256:5248900ab8b5f7f880c2d62180e40960cd87f60149ec9a1abfd62ac72a02577c /usr/bin/composer /usr/bin/composer
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-spei.ini

WORKDIR /var/www/html

FROM base AS dev

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS linux-headers \
    && pecl install pcov \
    && docker-php-ext-enable pcov \
    && apk del .build-deps

# Solo en desarrollo: FPM usa el mismo usuario que artisan/pest sobre el
# montaje de Windows, que no conserva permisos de www-data (DT-021).
RUN sed -i 's/^user = www-data/user = root/; s/^group = www-data/group = root/' \
    /usr/local/etc/php-fpm.d/www.conf

CMD ["php-fpm", "--allow-to-run-as-root"]

FROM base AS prod

COPY . /var/www/html
RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && chown -R www-data:www-data storage bootstrap/cache
