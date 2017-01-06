FROM ubuntu:14.04
RUN apt-get update 
RUN apt-get install -y software-properties-common git zip unzip curl wget
RUN LC_ALL=en_US.UTF-8 add-apt-repository ppa:ondrej/php
RUN apt update
RUN apt-get install -y --force-yes php7.0 php7.0-fpm php7.0-mysql php7.0-mbstring php7.0-sqlite php-xml php7.0-xml
RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer
RUN wget https://phar.phpunit.de/phpunit.phar && chmod +x phpunit.phar && mv phpunit.phar /usr/local/bin/phpunit
RUN mkdir /opt/lotgd/
RUN git clone https://github.com/lotgd/core.git /opt/lotgd/core
RUN cd /opt/lotgd/core && composer install
ENTRYPOINT /bin/bash
