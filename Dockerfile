# ServiceConnect — Docker image for Render / Railway / any container host
FROM php:8.2-apache

# ---- System deps + PHP extensions ----------------------------------
RUN apt-get update && apt-get install -y --no-install-recommends \
        unzip \
        libzip-dev \
        ca-certificates \
    && docker-php-ext-install pdo pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# ---- Enable Apache mod_rewrite (not strictly required here, but handy) ----
RUN a2enmod rewrite

# ---- Composer (for vlucas/phpdotenv, used only in local dev) ----
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# ---- Copy dependency manifests first for better Docker layer caching ----
COPY composer.json ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader || true

# ---- Copy the rest of the application ----
COPY . .

# ---- Point Apache's DocumentRoot at the project root (where index.php lives) ----
ENV APACHE_DOCUMENT_ROOT=/var/www/html
RUN sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf \
    && sed -ri -e "s!/var/www/!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# ---- Allow .htaccess overrides ----
RUN sed -ri -e 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# ---- Render/Railway assign a dynamic $PORT — Apache must listen on it ----
# The official image listens on 80 by default; this entrypoint rewrites
# the port at container start based on the platform's injected $PORT.
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
