# Use an official PHP runtime as a parent image
FROM php:8.2-apache

# Set working directory in the container
WORKDIR /var/www/html

# Copy the current directory contents into the container at /var/www/html
COPY . /var/www/html

# Copy Apache configuration file
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

# Enable mod_rewrite
RUN a2enmod rewrite

# Copy composer.lock and composer.json to the working directory
COPY composer.lock composer.json /var/www/html/

# Install dependencies
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    unzip \
    libonig-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install pdo_mysql extension
RUN docker-php-ext-install pdo_mysql

# Install mbstring extension
RUN docker-php-ext-install mbstring

# Install zip extension
RUN docker-php-ext-install zip

# Install exif extension
RUN docker-php-ext-install exif

# Install pcntl extension
RUN docker-php-ext-install pcntl

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install npm
RUN apt-get update && apt-get install -y npm

# Install dependencies
RUN composer install
RUN npm install

# Migrate and seed database, and start the server
CMD php artisan migrate && php artisan db:seed && php artisan serve --host=0.0.0.0 --port=8181

# Make port 8181 available to the world outside this container
EXPOSE 8181

# Run the app when the container launches
CMD ["apache2-foreground"]
