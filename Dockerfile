FROM ubuntu:16.04

# First update Ubuntu
RUN apt-get update

# Install System-Packages
RUN apt-get install -y composer\
    git\
    wget\
    unzip\
    curl

# Install PHP with necessary packages for Opus4
RUN apt-get install -y php\
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
    php-xdebug