# Use official lightweight PHP 8.2 Apache base image
FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Enable Apache rewrite module
RUN a2enmod rewrite

# Copy application files to Apache web root
COPY . /var/www/html/

# Set proper ownership and permissions for uploads and data directories
RUN mkdir -p /var/www/html/uploads /var/www/html/data \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/uploads /var/www/html/data

# Configure Apache port (supports Render / Railway / Cloud Run dynamic PORT variable)
ENV PORT=80
EXPOSE 80

# Configure Apache to listen on dynamic port if specified
RUN sed -i 's/Listen 80/Listen ${PORT}/' /etc/apache2/ports.conf
RUN sed -i 's/:80/:${PORT}/' /etc/apache2/sites-available/000-default.conf

# Start Apache in foreground
CMD ["apache2-foreground"]
