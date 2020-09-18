FROM php:7.4-apache

RUN a2enmod rewrite

# ADD ./htdocs /var/www/html