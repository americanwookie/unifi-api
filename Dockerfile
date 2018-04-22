FROM centos:7
Maintainer Scott O'Neil <scott@cpanel.net>
RUN yum -y update && yum -y install ca-certificates php curl git php-curl php-openssl php-json php-phar php-dom && yum clean all

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer 

RUN mkdir /app && cd /app && composer require art-of-wifi/unifi-api-client
COPY app/* /app/

USER nobody
WORKDIR /app
ENTRYPOINT ["/bin/php", "-S", "0:8000", "router.php"]
