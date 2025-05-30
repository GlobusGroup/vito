# Development Dockerfile for Laravel on Alpine
FROM alpine:3.19

LABEL maintainer="globus Group"

WORKDIR /var/www/html

ENV TZ=UTC
ENV SUPERVISOR_PHP_FPM_COMMAND="/usr/sbin/php-fpm83 -F"
ENV SUPERVISOR_CADDY_COMMAND="/usr/sbin/caddy run --config /etc/caddy/Caddyfile"
ENV SUPERVISOR_PHP_USER="vito"

# Enable community repository for PHP packages
RUN echo "http://dl-cdn.alpinelinux.org/alpine/v3.19/community" >> /etc/apk/repositories

# Install development requirements (includes everything from production plus dev tools)
RUN apk add --no-cache \
    curl \
    su-exec \
    supervisor \
    cronie \
    php83 \
    php83-pdo_pgsql \
    php83-pdo_sqlite \
    php83-gd \
    php83-curl \
    php83-pecl-mongodb \
    php83-imap \
    php83-pdo_mysql \
    php83-mbstring \
    php83-xml \
    php83-zip \
    php83-bcmath \
    php83-soap \
    php83-intl \
    php83-ldap \
    php83-pecl-redis \
    php83-pecl-memcached \
    php83-phar \
    php83-openssl \
    php83-json \
    php83-dom \
    php83-tokenizer \
    php83-fileinfo \
    php83-ctype \
    php83-session \
    php83-iconv \
    php83-simplexml \
    php83-xmlwriter \
    php83-pcntl \
    php83-posix \
    php83-sockets \
    tzdata \
    libcap \
    zip \
    php83-fpm \
    caddy \
    # Development specific packages
    php83-pecl-xdebug \
    php83-dev \
    nodejs \
    npm \
    git \
    vim \
    bash \
    && ln -s /usr/bin/php83 /usr/bin/php \
    && rm -rf /var/cache/apk/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Create necessary directories
RUN mkdir -p /run/php /var/www/html/public /etc/php83/php-fpm.d /etc/caddy

# Copy configuration files
COPY ./docker/development/www.conf /etc/php83/php-fpm.d/www.conf
COPY ./docker/development/Caddyfile /etc/caddy/Caddyfile

# Set timezone
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Configure PHP capabilities
RUN setcap "cap_net_bind_service=+ep" /usr/bin/php83

# Create vito user and group
RUN addgroup vito && \
    adduser -D -G vito vito

# Set permissions
RUN chown -R vito:vito /run/php

# Copy only the files needed for dependency installation
COPY --chown=vito:vito composer.json composer.lock /var/www/html/
COPY --chown=vito:vito package.json package-lock.json /var/www/html/

# Install PHP dependencies
RUN composer install --no-scripts --no-autoloader --no-interaction

# Install Node dependencies
RUN npm ci

# Copy configuration files
COPY ./docker/development/start-container.sh /usr/local/bin/start-container
COPY ./docker/development/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY ./docker/development/php.ini /etc/php83/conf.d/99-vito.ini
RUN chmod +x /usr/local/bin/start-container

# Create supervisor log directory and other required directories
RUN mkdir -p /var/log/supervisor /var/run/cron /var/log/php83

# Create directories that will be mounted as volumes
RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache

# Set proper permissions for Laravel directories
RUN chown -R vito:vito /var/www/html

# Give vito user permission to write to necessary directories
RUN chown -R vito:vito /var/log/supervisor /var/run/cron /var/log/php83

# Create sudoers entry for vito user to run supervisord
RUN echo "vito ALL=(ALL) NOPASSWD: /usr/bin/supervisord" >> /etc/sudoers

EXPOSE 80/tcp
EXPOSE 5173/tcp

# Don't switch to vito user - we'll handle permissions in the start script
# USER vito

ENTRYPOINT ["start-container"]