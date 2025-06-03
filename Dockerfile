FROM php:8.3-apache

# Install mysqli
RUN docker-php-ext-install mysqli

# Enable mod_rewrite (optional but useful for Laravel or clean URLs)
RUN a2enmod rewrite
