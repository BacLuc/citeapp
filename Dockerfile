FROM fauria/lamp
MAINTAINER Lucius Bachmann <lucius.bachmann@gmx.ch>
LABEL Description="Docker Container to run citeapp" \
	License="Apache License 2.0" \
	Usage="docker run -d -p [HOST WWW PORT NUMBER]:80 -p [HOST DB PORT NUMBER]:3306 -v [HOST WWW DOCUMENT ROOT]:/var/www/html bacluc/citeapp" \
Version="1.0"

#RUN sed -i 's/archive.ubuntu/old-releases.ubuntu/' /etc/apt/sources.list
RUN apt-get update
RUN apt-get install -y php-zip php-mbstring dos2unix php-xdebug php-odbc

COPY docker/entrypoint.sh /usr/local/bin

RUN dos2unix /usr/local/bin/entrypoint.sh

VOLUME /var/www/html
VOLUME /var/log/httpd
VOLUME /var/lib/mysql
VOLUME /var/log/mysql

EXPOSE 80
EXPOSE 3306

ENTRYPOINT exec bash -v /usr/local/bin/entrypoint.sh