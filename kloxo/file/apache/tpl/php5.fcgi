#!/bin/sh
# To use your own php.ini, comment the next line and uncomment the following one
export PHPRC="/etc"
export PHP_FCGI_CHILDREN=2
export PHP_FCGI_MAX_REQUESTS=500
exec /usr/bin/php-cgi