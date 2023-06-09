FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y \
    wget \
    gnupg \
    gnupg \
    libonig-dev \
    libicu-dev \
    zlib1g-dev \
    libssl-dev \
    software-properties-common \
    curl \
    sudo \
    iptables 

RUN add-apt-repository --yes ppa:ondrej/php

RUN apt-get update && apt-get install -y \
    mariadb-server \
    redis-server \
    memcached \
    libmemcached-dev

RUN apt-get install -y \
    php8.2-fpm \
    php-pear \
    php8.2-cli \
    php8.2-common \
    php8.2-curl \
    php8.2-intl \
    php8.2-mbstring \
    php8.2-memcached \
    php8.2-mongodb \
    php8.2-mysql \
    php8.2-redis \
    php8.2-sqlite3 \
    php8.2- zip \
    php8.2-xdebug \
    php8.2-gd

RUN echo "xdebug.mode=coverage" >> /etc/php/8.2/cli/php.ini
RUN sed -i 's/bind 127.0.0.1/#bind 127.0.0.1/' /etc/redis/redis.conf

WORKDIR /app
COPY . /app

RUN rm -rf /var/lib/apt/lists/

RUN wget https://raw.githubusercontent.com/composer/getcomposer.org/76a7060ccb93902cd7576b67264ad91c8a2700e2/web/installer -O - -q | php -- --quiet
RUN mv composer.phar /usr/local/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer update --no-interaction
RUN chmod +x ./tests/Fixture/docker/entry-point.sh

ENTRYPOINT ["./tests/Fixture/docker/entry-point.sh"]

