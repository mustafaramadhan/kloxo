#!/bin/sh
export MUID=501
export GID=501
/usr/local/lxlabs/ext/php/bin/php_cgi
export NON_RESIDENT=1
exec lxsuexec $*
