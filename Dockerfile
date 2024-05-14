# Use an official PHP runtime as a parent image
FROM php:7.4-apache

# Set working directory in the container
WORKDIR /var/www/html

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

# Install project dependencies
RUN composer install -v
RUN npm install

# Copy the current directory contents into the container at /var/www/html
COPY . /var/www/html

# Make port 80 available to the world outside this container
EXPOSE 80

# Run the app when the container launches
CMD ["apache2-foreground"]
