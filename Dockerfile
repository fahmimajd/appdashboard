FROM php:8.2-fpm

# Arguments defined in docker-compose.yml
ARG user
ARG uid

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libaio1 \
    wget \
    build-essential \
    libldap2-dev 

# Install Oracle Instant Client
RUN mkdir /opt/oracle
RUN cd /opt/oracle && \
    wget https://download.oracle.com/otn_software/linux/instantclient/2112000/instantclient-basic-linux.x64-21.12.0.0.0dbru.zip && \
    wget https://download.oracle.com/otn_software/linux/instantclient/2112000/instantclient-sdk-linux.x64-21.12.0.0.0dbru.zip && \
    unzip instantclient-basic-linux.x64-21.12.0.0.0dbru.zip && \
    unzip instantclient-sdk-linux.x64-21.12.0.0.0dbru.zip && \
    rm -rf *.zip && \
    mv instantclient_21_12 instantclient

# Add Oracle to environment paths
ENV LD_LIBRARY_PATH=/opt/oracle/instantclient
ENV ORACLE_HOME=/opt/oracle/instantclient

# Install PHP extensions
RUN docker-php-ext-configure oci8 --with-oci8=instantclient,/opt/oracle/instantclient \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd oci8 \
    && docker-php-ext-configure pdo_oci --with-pdo-oci=instantclient,/opt/oracle/instantclient \
    && docker-php-ext-install pdo_oci

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Set working directory
WORKDIR /var/www

COPY . .
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Switch to non-root user (optional, but good practice. For now keeping root for entrypoint permissions flexibility, 
# or switching back to user at end of entrypoint. Let's stick to root for setup simplicity in entrypoint, then step down if needed, 
# but PHP-FPM usually handles user switch via conf. We will run as root in container entrypoint to fix perms, then PHP-FPM runs as www-data)
# But we created a user, let's use it for composer if we were running interactively.
# For simplicity with bind mounts, we often run as the host user.

ENTRYPOINT ["entrypoint.sh"]
