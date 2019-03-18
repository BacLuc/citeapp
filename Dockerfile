FROM php:7.3-apache
MAINTAINER Lucius Bachmann <lucius.bachmann@gmx.ch>
LABEL Description="Docker Container to run citeapp" \
	License="Apache License 2.0" \
	Usage="docker run -d -p [HOST WWW PORT NUMBER]:80 -p [HOST DB PORT NUMBER]:3306 -v [HOST WWW DOCUMENT ROOT]:/var/www/html bacluc/citeapp" \
Version="1.0"

RUN rm /etc/apt/preferences.d/no-debian-php
RUN apt-get update
RUN apt-get install -y odbcinst dos2unix unixodbc-dev
RUN docker-php-ext-configure pdo_odbc --with-pdo-odbc=unixODBC,/usr
RUN docker-php-ext-install pdo_odbc
RUN pecl install xdebug
RUN apt-get -y install libvirtodbc0
RUN apt-get -y install iproute2

RUN cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini

COPY docker/entrypoint.sh /usr/local/bin
COPY docker/odbc.ini /etc/odbc.ini
RUN dos2unix /usr/local/bin/entrypoint.sh

VOLUME /var/www/html
VOLUME /var/log/httpd
VOLUME /var/lib/mysql
VOLUME /var/log/mysql

EXPOSE 80
EXPOSE 3306

ENTRYPOINT exec bash -v /usr/local/bin/entrypoint.sh