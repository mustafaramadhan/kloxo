#!/bin/sh
#    Kloxo, Hosting Control Panel
#
#    Copyright (C) 2000-2009	LxLabs
#    Copyright (C) 2009-2011	LxCenter
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
# LxCenter - Kloxo Packer
#
# Version: 1.0 (2011-08-02 - by mustafa.ramadhan@lxcenter.org)
#

if [ "$#" == 0 ] ; then
	echo
	echo " ----------------------------------------------------------------------"
	echo "  format: sh $0 --fork=<> --branch=<> --part=<>"
	echo " ----------------------------------------------------------------------"
	echo "  --fork - example: lxcenter or mustafaramadhan (for certain developer)"
	echo "  --branch - example: master or dev"
	echo "  --part - example: core or all (defaulting to all)"
	echo
	echo "  * Pack main kloxo package from git"
	echo "  * Thirdparty packages download directly for latest version"
	echo "  * Then run kloxo-installer.sh which the same place with local copy"
	echo
	exit;
fi

echo "Start pack..."

request1=$1
kloxo_fork=${request1#--fork\=}

request2=$2
kloxo_branch=${request2#--branch\=}

if [ "$3" == '--part=core' ] ; then
	request3=$3
	kloxo_part=${request3#--part\=}
else
	kloxo_part="all"
fi

kloxo_path=${kloxo_fork}/kloxo/zipball/${kloxo_branch}

yum install zip unzip -y

mkdir -p ./combo

if [ ! -d ./current/kloxo/httpdocs ] ; then
	echo "Download kloxo git from "${kloxo_path}
	yes | rm -rf ${kloxo_branch}* > /dev/null 2>&1
	wget https://github.com/${kloxo_path} --no-check-certificate
	mv -f ${kloxo_branch} kloxo.zip > /dev/null 2>&1
	unzip -oq kloxo.zip > /dev/null 2>&1
	mv -f ./${kloxo_fork}* ./current > /dev/null 2>&1
	yes | rm -rf kloxo.zip > /dev/null 2>&1
else
	echo "No download and use local copy - './current/kloxo/httpdocs already' exist"
fi

cp -rf ./current/* ./combo > /dev/null 2>&1

cp -rf ./patch/* ./combo > /dev/null 2>&1

if [ ! -f ./combo/kloxo-install/kloxo-installer.php ] ; then

	cd ./combo
	
	echo "Download kloxo-install.zip from http://download.lxcenter.org/download/"
	wget http://download.lxcenter.org/download/kloxo-install.zip
	unzip -oq kloxo-install.zip
	rm -rf kloxo-install.zip > /dev/null 2>&1
	
	cd ..
	
	# check again
	if [ ! -f ./combo/kloxo-install/kloxo-installer.php ] ; then		
		echo "Download kloxo-installer.php from http://download.lxcenter.org/download/kloxo/production/"
		wget http://download.lxcenter.org/download/kloxo/production/kloxo-installer.php
		mv -f kloxo-installer.php ./combo/kloxo-install/kloxo-installer.php > /dev/null 2>&1
	fi
fi

cd ./combo

zip -r9y kloxo-install.zip ./kloxo-install > /dev/null 2>&1

mv -f kloxo-install.zip ../ > /dev/null 2>&1

cd ./kloxo/src
yum -y install which cpp gcc gcc-c++ openssl-devel automake autoconf libtool make
make
cd ../

zip -r9y kloxo-current.zip ./bin ./cexe ./file ./httpdocs ./pscript ./sbin ./RELEASEINFO ./src ./etc/list -x \
	"*httpdocs/commands.php" \
	"*httpdocs/newpass" \
	"*httpdocs/.php.err" \
	"*/CVS/*" \
	"*/.svn/*" \
	"*httpdocs/thirdparty/*" \
	"*httpdocs/htmllib/extjs/*" \
	"*httpdocs/htmllib/fckeditor/*" \
	"*httpdocs/htmllib/yui-dragdrop/*"

mv -f kloxo-current.zip ../../ > /dev/null 2>&1
cd ../../

if [ ! $kloxo_part == "core" ] ; then

	thirdpartyver=$(curl -L http://download.lxcenter.org/download/thirdparty/kloxo-version.list)
	if [ ! -f kloxo-thirdparty.$thirdpartyver.zip ] ; then
		echo ${thirdpartyver} > kloxo-thirdparty-version
		wget http://download.lxcenter.org/download/kloxo-thirdparty.${thirdpartyver}.zip
	fi

	# no need kloxophp on 6.2.0
#	kloxophpver=$(curl -L http://download.lxcenter.org/download/version/kloxophp)
#	if [ ! -f kloxophp${kloxophpver}.tar.gz ] ; then
#		echo ${kloxophpver} > kloxophp-version
#		wget http://download.lxcenter.org/download/kloxophp${kloxophpver}.tar.gz
#	fi

	# no need kloxophp on 6.2.0
#	kloxophpsixfourver=$(curl -L http://download.lxcenter.org/download/version/kloxophpsixfour)
#	if [ ! -f kloxophpsixfour${kloxophpsixfourver}.tar.gz ] ; then
#		echo ${kloxophpsixfourver} > kloxophpsixfour-version
#		wget http://download.lxcenter.org/download/kloxophpsixfour${kloxophpsixfourver}.tar.gz
#	fi

	lxwebmailver=$(curl -L http://download.lxcenter.org/download/version/lxwebmail)
	if [ ! -f lxwebmail${lxwebmailver}.tar.gz ] ; then
		echo ${lxwebmailver} > lxwebmail-version
		wget http://download.lxcenter.org/download/lxwebmail${lxwebmailver}.tar.gz
	fi

	lxawstatsver=$(curl -L http://download.lxcenter.org/download/version/lxawstats)
	if [ ! -f lxawstats${lxawstatsver}.tar.gz ] ; then
		echo ${lxawstatsver} > lxawstats-version
		wget http://download.lxcenter.org/download/lxawstats${lxawstatsver}.tar.gz
	fi
fi

cp -rf ./combo/kloxo-install/kloxo-installer.sh ./ > /dev/null 2>&1

# delete temporal directory
rm -rf ./patch > /dev/null 2>&1
rm -rf ./current > /dev/null 2>&1
rm -rf ./combo > /dev/null 2>&1

echo
echo "Now you can run 'sh ./kloxo-installer.sh' for installing"
echo
echo "... the end"
