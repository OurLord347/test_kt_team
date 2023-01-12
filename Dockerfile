# syntax=docker/dockerfile:1
FROM bitnami/symfony:1

RUN echo 'memory_limit = 1024M' >> /opt/bitnami/php/conf/php.ini
	|| echo 'post_max_size = 1024M' >> /opt/bitnami/php/conf/php.ini
	|| echo 'max_execution_time = 100' >> /opt/bitnami/php/conf/php.ini
	|| echo 'upload_max_filesize = 1024M' >> /opt/bitnami/php/conf/php.ini;

RUN echo 'memory_limit = 1024M' >> /opt/bitnami/php/conf/php.ini-development
	|| echo 'post_max_size = 1024M' >> /opt/bitnami/php/conf/php.ini-development
	|| echo 'max_execution_time = 100' >> /opt/bitnami/php/conf/php.ini-development
	|| echo 'upload_max_filesize = 1024M' >> /opt/bitnami/php/conf/php.ini-development;

RUN echo 'memory_limit = 1024M' >> /opt/bitnami/php/etc/php.ini
	|| echo 'post_max_size = 1024M' >> /opt/bitnami/php/etc/php.ini
	|| echo 'max_execution_time = 100' >> /opt/bitnami/php/etc/php.ini
	|| echo 'upload_max_filesize = 1024M' >> /opt/bitnami/php/etc/php.ini;

RUN echo 'memory_limit = 1024M' >> /opt/bitnami/php/etc/php.ini-development
	|| echo 'post_max_size = 1024M' >> /opt/bitnami/php/etc/php.ini-development
	|| echo 'max_execution_time = 100' >> /opt/bitnami/php/etc/php.ini-development
	|| echo 'upload_max_filesize = 1024M' >> /opt/bitnami/php/etc/php.ini-development;

