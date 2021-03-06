FROM php:7.3-fpm

RUN apt-get update -y && apt-get install libyaml-dev -y && \
	pecl install yaml && \
	docker-php-ext-install json pdo pdo_mysql sockets && \
	docker-php-ext-enable yaml && \
	mv $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/0-php.ini

# Development extra
RUN pecl install xdebug && docker-php-ext-enable xdebug 

# Copy ini files into place
COPY docker/php-override.ini 		$PHP_INI_DIR/conf.d/1-overide.ini

COPY docker/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

COPY --from=openpolicyagent/opa:latest /opa /opa

COPY . /srv/app
WORKDIR /srv/app

ENTRYPOINT ["/srv/app/start.sh"]
CMD ["from-openapi"]
