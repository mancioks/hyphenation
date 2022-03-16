FROM php:8.1-apache
RUN apt-get update && apt-get upgrade -y
RUN docker-php-ext-install pdo pdo_mysql mysqli
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite
EXPOSE 80