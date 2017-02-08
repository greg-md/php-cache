#!/bin/bash

mkdir /tmp/build-phpredis && cd /tmp/build-phpredis \
    && git clone --depth=50 https://github.com/phpredis/phpredis \
    && cd phpredis \
    && phpize \
    && echo "Configuring phpredis..." \
    && ./configure > /dev/null \
    && echo "Building phpredis..." \
    && make -j4 > /dev/null \
    && echo "Installing phpredis..." \
    && sudo make install > /dev/null

echo 'extension = redis.so' >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini

#mkdir /tmp/build-phpredis && cd /tmp/build-phpredis || exit 1
#
#if [ "$TRAVIS_PHP_VERSION" == "7.0" ]; then
#	PHP_REDIS_BRANCH="php7"
#else
#	PHP_REDIS_BRANCH="master"
#fi
#
#wget -O phpredis.zip "https://github.com/phpredis/phpredis/archive/$PHP_REDIS_BRANCH.zip" && unzip phpredis.zip && cd phpredis-*/ || exit 1
#echo
#
#phpize && ./configure && make && make install || exit 1
#echo
#
#echo 'extension = redis.so' >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
#
#cd $TRAVIS_BUILD_DIR