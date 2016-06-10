#!/bin/sh

#    Kloxo-MR - Hosting Control Panel
#
#    Copyright (C) 2013 - MRatWork
#
#    This program is free software: you can redistribute it and/or modify
#    it under the terms of the GNU Affero General Public License as
#    published by the Free Software Foundation, either version 3 of the
#    License, or (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU Affero General Public License for more details.
#
#    You should have received a copy of the GNU Affero General Public License
#    along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
#
# MRatWork - Kloxo-MR dev Installer
#
# Version: 1.0 (2013-01-11 - by Mustafa Ramadhan <mustafa@bigraf.com>)
#

if [ "$1" == "--help" ] || [ "$1" == "-h" ] ; then
	echo
	echo " ----------------------------------------------------------------------"
	echo "  format: sh $0 --fork=<> --branch=<>"
	echo " ----------------------------------------------------------------------"
	echo "  --fork - example: lxcenter or mustafaramadhan (for certain developer)"
	echo "  --branch - example: master or dev"
	echo
	echo "  * Pack main kloxo package from git"
	echo "  * Thirdparty packages download directly for latest version"
	echo "  * Then run kloxo-installer.sh which the same place with local copy"
	echo
	echo " - If detect './kloxo/httpdocs' and then used it as source" 
	exit
fi

echo "Start pack..."

if [ "$1" == "" ] ; then
	kloxo_fork="mustafaramadhan"
else
	kloxo_fork=${1#--fork\=}
fi

if [ "$2" == "" ] ; then
	kloxo_branch="dev"
else
	kloxo_branch=${2#--branch\=}
fi

if [ "$(rpm -qa|grep unzip)" == "" ] ; then
	yum install zip unzip -y
fi

if [ ! -d ./kloxo/httpdocs ] ; then
	echo "Download git"
	'rm' -rf ${kloxo_branch}* > /dev/null 2>&1
	wget https://github.com/${kloxo_fork}/kloxo/archive/${kloxo_branch}.zip -O kloxo-mr-${kloxo_branch}.zip

	unzip -oq kloxo-mr-${kloxo_branch}.zip > /dev/null 2>&1
	'rm' -rf kloxo-mr-${kloxo_branch}.zip > /dev/null 2>&1
	'mv' -f ./kloxo*-${kloxo_branch}/kloxo ./
	'rm' -rf ./kloxo*-${kloxo_branch}
else
	echo "No download and use local copy already exist"
fi

'cp' -rf ./kloxo/install/installer.sh ./

ver=`cat ./kloxo/bin/kloxoversion`

'mv' ./kloxo ./kloxomr7-$ver

# delete dirs except en-us
find ./kloxomr7-$ver/httpdocs/lang/* -type d ! -name 'en-us' -exec rm -R {} \;

### 4. zipped process
tar -czf kloxomr7-$ver.tar.gz "./kloxomr7-$ver/bin" "./kloxomr7-$ver/cexe" "./kloxomr7-$ver/file" \
	"./kloxomr7-$ver/httpdocs" "./kloxomr7-$ver/pscript" "./kloxomr7-$ver/sbin" \
	"./kloxomr7-$ver/RELEASEINFO" "./kloxomr7-$ver/etc/process" \
	"./kloxomr7-$ver/etc/config.ini" \
	"./kloxomr7-$ver/install" "./kloxomr7-$ver/init" \
	"./kloxomr7-$ver/etc/list" \
	--exclude "./kloxomr7-$ver/httpdocs/commands.php" \
	--exclude "./kloxomr7-$ver/httpdocs/newpass" \
	--exclude "./kloxomr7-$ver/httpdocs/.php.err" \
	--exclude "./kloxomr7-$ver/httpdocs/thirdparty" \
	--exclude "./kloxomr7-$ver/httpdocs/editor" \
	--exclude "./kloxomr7-$ver/file/cache" \
	--exclude "./kloxomr7-$ver/file/*.repo" \
	--exclude "./kloxomr7-$ver/serverfile" \
	--exclude "./kloxomr7-$ver/session" \
	--exclude "./kloxomr7-$ver/etc/.restart" \
	--exclude "./kloxomr7-$ver/etc/conf/*" \
	--exclude "./kloxomr7-$ver/etc/flag/*" \
	--exclude "./kloxomr7-$ver/etc/slavedb/*" \
	--exclude "./kloxomr7-$ver/etc/last_sisinfoc" \
	--exclude "./kloxomr7-$ver/etc/program.*" \
	--exclude "./kloxomr7-$ver/etc/watchdog.conf" \
	--exclude "./kloxomr7-$ver/install/*.log" \
	--exclude "./kloxomr7-$ver/log" \
	--exclude "./kloxomr7-$ver/pid" \
	--exclude "./kloxomr7-$ver/init/kloxo_php_active" \
	--exclude "./kloxomr7-$ver/init/*.sock" \
	--exclude "./kloxomr7-$ver/init/*.pid" \
	--exclude "./kloxomr7-$ver/init/kloxo-hiawatha" \
	--exclude "./kloxomr7-$ver/init/kloxo-phpcgi" \
	--exclude "./kloxomr7-$ver/*.old" \
	--exclude "./kloxomr7-$ver/*.bck" \
	--exclude "./kloxomr7-$ver/*.pyo" \
	--exclude "./kloxomr7-$ver/*.pyc" \
	--exclude "./kloxomr7-$ver/init/php_active" \
	--exclude "./kloxomr7-$ver/httpdocs/login/*.php" \
	--exclude "./kloxomr7-$ver/httpdocs/login/*.html" \
	--exclude "./kloxomr7-$ver/httpdocs/login/.norandomimage" \
	--exclude "./kloxomr7-$ver/httpdocs/login/images" \
	--exclude "./kloxomr7-$ver/*/user-logo.png"


'rm' -rf ./kloxomr7-$ver > /dev/null 2>&1
'rm' -rf ./kloxo-install > /dev/null 2>&1
'rm' -rf ./install > /dev/null 2>&1

echo
echo "Now you can run 'sh ./installer.sh' for installing"
echo
echo "Run '$0 --help' for helping"
echo
echo "... the end"
