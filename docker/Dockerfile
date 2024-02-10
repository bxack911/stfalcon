FROM php:8.2-fpm AS php

ARG host_uid
ENV TZ=Europe/Kiev

## User configuration
ARG SYSTEM_USER_ID
ARG SYSTEM_GROUP_ID

ARG CONTAINER_USER_NAME=app-container

RUN groupadd --gid $SYSTEM_GROUP_ID -r $CONTAINER_USER_NAME
RUN useradd -u $SYSTEM_USER_ID -g $CONTAINER_USER_NAME -ms /bin/bash $CONTAINER_USER_NAME

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update && apt-get install -y vim git curl sudo sendmail; \
	chown -R app-container /var/www
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

## Install Redis
RUN pecl install -o -f redis \
&&  rm -rf /tmp/pear \
&&  docker-php-ext-enable redis

COPY php/php.ini /usr/local/etc/php/conf.d/custom-php.ini

RUN curl -Lsf 'https://storage.googleapis.com/golang/go1.8.3.linux-amd64.tar.gz' | tar -C '/usr/local' -xvzf -
ENV PATH /usr/local/go/bin:$PATH
RUN go get github.com/mailhog/mhsendmail
RUN cp /root/go/bin/mhsendmail /usr/bin/mhsendmail
RUN echo 'sendmail_path = /usr/bin/mhsendmail --smtp-addr mailhog:1025' > /usr/local/etc/php/conf.d/custom-php.ini

WORKDIR /var/www/html

FROM php AS cron

RUN if [ $(awk -F. '{print $1}' /etc/debian_version) -eq 9 ]; then \
  printf 'deb http://archive.debian.org/debian/ stretch main contrib non-free deb http://archive.debian.org/debian-security/ stretch/updates main contrib non-free deb http://archive.debian.org/debian/ stretch-backports main contrib non-free' > /etc/apt/sources.list; \
  fi

RUN apt-get update && apt-get install -y nano sudo cron;

COPY crond/crontasks /var/spool/cron/crontabs/app-container
RUN crontab -u app-container /var/spool/cron/crontabs/app-container
RUN chown app-container:app-container /usr/local/bin/php

CMD ["cron", "-f"]

USER app-container
