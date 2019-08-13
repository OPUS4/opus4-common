FROM ubuntu:16.04

# Install PHP, Apache, Git, Composer and all other necessary packages -> extension if necessary
RUN apt-get update \
    && apt-get install -y apt-utils\
    openjdk-8-jdk\
    php\
    php-cli\
    php-common\
    php-curl\
    php-dev\
    php-gd\
    php-mcrypt\
    php-mysql\
    php-mbstring\
    php-uuid\
    php-xsl\
    php-intl\
    php-log\
    php-zip\
    libapache2-mod-php7.0\
    libxml2-utils\
    composer\
    git\
    wget\
    unzip\
    ant\
    curl\
    sudo\
    docker-compose\
    apache2\
    php-xdebug
