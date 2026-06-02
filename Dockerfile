FROM php:8.2-apache
RUN apt-get update && apt-get install -y libzip-dev curl \
    && docker-php-ext-install mysqli pdo pdo_mysql zip \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*
