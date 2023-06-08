FROM php:7.4-cli

RUN  apt-get update \ 
    && apt-get install wget sudo -y 

RUN apt-get update && apt-get install -y \
    mariadb-server \
    redis-server \
    memcached \
    gnupg \
    libonig-dev \
    libicu-dev \
    libmemcached-dev \
    zlib1g-dev \
    libssl-dev \
    && rm -rf /var/lib/apt/lists/*

# Intall old version of MongoDB for testing with old PHP version.
RUN wget -qO - https://www.mongodb.org/static/pgp/server-4.4.asc | apt-key add -
RUN echo "deb [ arch=amd64,arm64 ] https://repo.mongodb.org/apt/ubuntu focal/mongodb-org/4.4 multiverse" | tee /etc/apt/sources.list.d/mongodb-org-4.4.list
RUN apt-get update && apt-get install -y mongodb-org=4.4.15 mongodb-org-server=4.4.15 mongodb-org-shell=4.4.15 mongodb-org-mongos=4.4.15 mongodb-org-tools=4.4.15

RUN docker-php-ext-install \
    mbstring \
    pdo \
    pdo_mysql \
    exif

RUN pecl install redis && docker-php-ext-enable redis
RUN pecl install apcu \
    && pecl install apcu_bc \
    && docker-php-ext-enable apcu \
    # For testing the APC backward compatibility
    && echo "apc.enabled=1" >> /usr/local/etc/php/conf.d/docker-php-ext-apcu.ini \
    && echo "apc.enable_cli=1" >> /usr/local/etc/php/conf.d/docker-php-ext-apcu.ini \
    # Load APCu before APC
    && echo "extension=apc.so" >> /usr/local/etc/php/conf.d/docker-php-ext-apcu.ini

RUN pecl install memcache-4.0.5.2 && docker-php-ext-enable memcache
RUN pecl install memcached && docker-php-ext-enable memcached
RUN pecl install mongodb && docker-php-ext-enable mongodb
RUN pecl install xdebug-3.1.5 && docker-php-ext-enable xdebug \
    && echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Well, we want to test the authentication for Redis version > 6.0.0
RUN sed -i 's/# aclfile \/etc\/redis\/users.acl/aclfile \/etc\/redis\/users.acl/' /etc/redis/redis.conf
RUN echo "requirepass world" >> /etc/redis/redis.conf
RUN echo "user hello on >world +@all ~*" >> /etc/redis/users.acl

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app
COPY . /app
COPY ./tests/Fixture/docker/mongod.sh /etc/init.d/mongod

RUN composer install --prefer-dist --no-interaction --dev
RUN chmod +x ./tests/Fixture/docker/entry-point.sh

ENTRYPOINT ["./tests/Fixture/docker/entry-point.sh"]

