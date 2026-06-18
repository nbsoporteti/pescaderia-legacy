FROM php:8.2-apache
RUN apt-get update && apt-get install -y libzip-dev curl \
    && docker-php-ext-install mysqli pdo pdo_mysql zip \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# Sesiones de 8h para el back-office. El valor por defecto (1440s = 24 min) era muy corto:
# la sesion se vencia mientras el usuario llenaba un formulario y al guardar fallaba.
RUN echo "session.gc_maxlifetime = 28800" > /usr/local/etc/php/conf.d/zz-pesc-session.ini
