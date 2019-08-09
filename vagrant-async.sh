#!/bin/bash

PHP_VERSION=7.3.0

sudo apt-get update
sudo apt-get install gdb git gcc make pkg-config autoconf libtool bison libxml2-dev libssl-dev curl -y

# Install PHP:
sudo mkdir /usr/local/php
cd /usr/local/php

sudo mkdir cli

sudo mkdir php-src
sudo curl -LSs https://github.com/php/php-src/archive/php-$PHP_VERSION.tar.gz | sudo tar -xz -C "php-src" --strip-components 1

pushd php-src

sudo ./buildconf --force
sudo ./configure \
    --prefix=/usr/local/php/cli \
    --with-config-file-path=/usr/local/php/cli \
    --with-openssl \
    --with-zlib \
    --without-pear \
    --enable-debug \
    --enable-mbstring \
    --enable-pcntl \
    --enable-sockets

sudo make -j4
sudo make install
popd

sudo touch /usr/local/php/cli/php.ini
sudo chmod 466 /usr/local/php/cli/php.ini

sudo ln -s /usr/local/php/cli/bin/php /usr/local/bin/php
sudo ln -s /usr/local/php/cli/bin/phpize /usr/local/bin/phpize
sudo ln -s /usr/local/php/cli/bin/php-config /usr/local/bin/php-config

sudo echo "alias phpgdb='gdb $(which php)'" >> ~/.bash_aliases

# Install async extension:
sudo mkdir ext-async
sudo curl -LSs https://github.com/concurrent-php/ext-async/archive/master.tar.gz | sudo tar -xz -C "ext-async" --strip-components 1

pushd ext-async
sudo phpize
sudo ./configure
sudo make install
sudo echo "extension=\"async.so\"" >> /usr/local/php/cli/php.ini
popd

php -v
php -m
