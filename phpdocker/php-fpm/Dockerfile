FROM phpdockerio/php73-fpm:latest
WORKDIR "/application"

# Fix debconf warnings upon build
ARG DEBIAN_FRONTEND=noninteractive

# Install selected extensions and other stuff
RUN apt-get update \
    && apt-get -y --no-install-recommends install \
    php-memcached \
# php7.3-dev contains phpize, will be needed for compiling php-extensions
    php7.3-dev \
    php7.3-mysql \
    php-redis \
    php-xdebug \
    php7.3-gd \
    php7.3-imap \
    php7.3-intl \
    php7.3-ldap \
    php-mongodb \
    php7.3-phpdbg \
    php-ssh2 \
#    php-tideways \
    php7.3-xsl \
    php-yaml \
# php-pear provides pecl for installing sqlsrv and pdo_sqlsrv
    php-pear \
    apt-transport-https \
    apt-utils \
# libcurl4 is needed for curl and curl is needed to use microsoft-repository for msodbcsql17 and mssql-tools
# will be removed later by installing libcurl3, which is needed by msodbcsql17
    libcurl4 \
    curl 

# Is Already installed
# RUN pecl install xdebug-2.6.0RC2 && docker-php-ext-enable xdebug

RUN APT_KEY_DONT_WARN_ON_DANGEROUS_USAGE=Y \
    && curl https://packages.microsoft.com/keys/microsoft.asc | apt-key add - \
    && curl https://packages.microsoft.com/config/ubuntu/16.04/prod.list > /etc/apt/sources.list.d/mssql-release.list \
    && apt-get update \
    && apt-get install -y --no-install-recommends libcurl3

# install msodbcsql and associated tools
RUN ACCEPT_EULA=Y apt-get install -y msodbcsql17 
RUN ACCEPT_EULA=Y apt-get install -y mssql-tools 

# add msssql-tools to path 
RUN echo 'export PATH="$PATH:/opt/mssql-tools/bin"' >> ~/.bash_profile
RUN echo 'export PATH="$PATH:/opt/mssql-tools/bin"' >> ~/.bashrc

RUN apt-get install -y unixodbc-dev

# add extension info to ini files
#RUN echo extension=pdo_sqlsrv.so >> `php --ini | grep "Scan for additional .ini files" | sed -e "s|.*:\s*||"`/30-pdo_sqlsrv.ini
#RUN echo extension=sqlsrv.so >> `php --ini | grep "Scan for additional .ini files" | sed -e "s|.*:\s*||"`/20-sqlsrv.ini

# install compile-tools
RUN apt-get install -y g++
RUN apt-get install -y build-essential

# install sqlsrv and pdo_sqlsrv
RUN pecl install sqlsrv 
RUN pecl install pdo_sqlsrv

# add extension info to ini files
RUN echo extension=pdo_sqlsrv.so >> `php --ini | grep "Scan for additional .ini files" | sed -e "s|.*:\s*||"`/30-pdo_sqlsrv.ini
RUN echo extension=sqlsrv.so >> `php --ini | grep "Scan for additional .ini files" | sed -e "s|.*:\s*||"`/20-sqlsrv.ini

# Enable Extensions sqlsrv and pdo_sqlsrv for php-fpm
RUN cp /etc/php/7.3/cli/conf.d/30-pdo_sqlsrv.ini /etc/php/7.3/fpm/conf.d/
RUN cp /etc/php/7.3/cli/conf.d/20-sqlsrv.ini /etc/php/7.3/fpm/conf.d/

# We simply append it to the end of php.ini
RUN echo [sqlsrv] >> /etc/php/7.3/fpm/php.ini
RUN echo extension=sqlsrv >> /etc/php/7.3/fpm/php.ini
RUN echo extension=pdo_sqlsrv >> /etc/php/7.3/fpm/php.ini

# Some Cleanup of stuff we just need for build the image, but not for production-mode
RUN apt-get remove -y g++
RUN apt-get remove -y build-essential
RUN apt-get remove -y php7.3-dev
RUN apt-get remove -y php-pear
RUN apt-get remove -y apt-utils

RUN apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

# Finally: Setup some simple helper tools, including lumen
RUN apt-get update \
    && apt-get install -y less \
    && apt-get install -y vim \
    && apt-get install -y sudo \
    && adduser --disabled-password --gecos "" loviki \
    && usermod -aG sudo loviki

RUN sudo -H -u loviki composer global require "laravel/lumen-installer"

RUN echo "export PATH=\"$PATH:/home/loviki/.composer/vendor/bin\"" >> ~/.bashrc \
    && echo "export PATH=\"$PATH:/home/loviki/.composer/vendor/bin\"" >> /etc/.profile
