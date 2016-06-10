#!/bin/sh

name=$1
pass=$2
dbuser=$3
dbpass=$4

MYSQLPR=`which mysql`

if [ ! -f "$MYSQLPR" ]; then
	echo "mysql client is not there"
	exit 1
fi

#if [ -f /var/lock/subsys/mysqld ] ;then
	if [ -z $pass ] ; then
		echo "CREATE DATABASE IF NOT EXISTS vpopmail;GRANT ALL PRIVILEGES ON vpopmail.* TO $dbuser@localhost IDENTIFIED BY '$dbpass'" | "$MYSQLPR" -u"$name"
	else
		echo "CREATE DATABASE IF NOT EXISTS vpopmail;GRANT ALL PRIVILEGES ON vpopmail.* TO $dbuser@localhost IDENTIFIED BY '$dbpass'" | "$MYSQLPR" -u"$name" -p"$pass"
	fi
#fi
 
echo "localhost|0|$dbuser|$dbpass|vpopmail">/home/vpopmail/etc/vpopmail.mysql
