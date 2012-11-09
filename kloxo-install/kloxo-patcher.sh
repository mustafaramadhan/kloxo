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
# LxCenter - Kloxo Patcher
#
# Version: 1.0 (2011-08-02 - by mustafa.ramadhan@lxcenter.org)
#

### functions - must declare before execute ###

# Reads yes|no answer from the input ; question text ; default answer, yes = 1 and no = 0
function get_yes_no () {
    local question=
    local input=
    case $2 in 
        1 ) question="$1 [Y/n]: "
            ;;
        0 ) question="$1 [y/N]: "
            ;;
        * ) question="$1 [y/n]: "
    esac

    while :
    do
        read -p "$question" input
        input=$( echo $input | tr -s '[:upper:]' '[:lower:]' )
        if [ "$input" = "" ] ; then
            if [ "$2" == "1" ] ; then
                return 1
            elif [ "$2" == "0" ] ; then
                return 0
            fi
        else
            case $input in
                y|yes) return 1
                    ;;
                n|no) return 0
                    ;;
            esac
        fi
    done
}

function kloxo_core_portion () {
	echo
	echo "Step for Kloxo Core..."

	if [ -f ./patch/kloxo-patch-version ] ; then
		patchver=`cat ./patch/kloxo-patch-version`
	else
		patchver=""
	fi

	kloxover=`sh /script/version`
	
	kloxo_check_version $patchver $kloxover
	
	if [ "$?" -eq "1" ] ; then
		echo "- Set ownership and permissions"
		chown -R lxlabs:lxlabs ./patch/kloxo/
		find ./patch/kloxo/ -type f -name \"*.php*\" -exec chmod 644 {} \;
		find ./patch/kloxo/ -type d -exec chmod 755 {} \;

		echo "- Copy patch files"
		cp -rf ./patch/kloxo/* /usr/local/lxlabs/kloxo
		cp -rf /usr/local/lxlabs/kloxo/pscript /script
		cp -rf /usr/local/lxlabs/kloxo/httpdocs/htmllib/script /script

		kloxo_symlink
	else
		echo " - No Core patches"
	fi
}

function kloxo_symlink () {
	if [ -d /usr/lib64 ] ; then
		echo "- Set symlink from 64bit links if needed"

		# no need kloxophp on 6.2.x
	#	if [ ! -h /usr/lib/kloxophp ] ; then
	#		echo "- /usr/lib/kloxophp not as symlink, deleted"
	#		rm -rf /usr/lib/kloxophp
	#	fi
	
		if [ ! -h /usr/lib/php ] ; then
			echo "- Set symlink for /usr/lib/php"
			mkdir -p /usr/lib64/php
			ln -s /usr/lib64/php /usr/lib/php
		fi

		if [ ! -h /usr/lib/httpd ] ; then
			echo "- Set symlink for /usr/lib/httpd"
			mkdir -p /usr/lib64/httpd
			ln -s /usr/lib64/httpd /usr/lib/httpd
		fi

		if [ ! -h /usr/lib/lighttpd ] ; then
			echo "- Set symlink for /usr/lib/kloxophp"
			mkdir -p /usr/lib64/lighttpd
			ln -s /usr/lib64/lighttpd /usr/lib/lighttpd
		fi

		# no need kloxophp on 6.2.x
	#	if [ ! -h /usr/lib/kloxophp ] ; then
	#		echo "- Set symlink for /usr/lib/kloxophp"
	#		mkdir -p /usr/lib64/kloxophp
	#		ln -s /usr/lib64/kloxophp /usr/lib/kloxophp
	#	fi
	else
		echo "- No extra setting for 32bit"
	fi

	# no need kloxophp on 6.2.x
	if [ -d /usr/lib/kloxophp ] ; then
		rm -rf /usr/lib/kloxophp
	fi

	if [ -d /usr/lib64/kloxophp ] ; then
		rm -rf /usr/lib64/kloxophp
	fi

}

function kloxo_thirdparty_portion () {
	echo "Step for Kloxo Thirdparty..."

	if [ -f ./patch/thirdparty-patch-version ] ; then
		patchver=`cat ./patch/thirdparty-patch-version`

		wget -q http://download.lxcenter.org/download/thirdparty/kloxo-version.list > /dev/null
		kloxover=`cat kloxo-version.list`
		rm -f kloxo-version.list
	
		kloxo_check_version $patchver $kloxover
	
		if [ "$?" -eq "1" ] ; then
			echo "- Set ownership and permissions"
			chown -R lxlabs:lxlabs ./patch/thirdparty/
			find ./patch/thirdparty/ -type f -name \"*.php*\" -exec chmod 644 {} \;
			find ./patch/thirdparty/ -type d -exec chmod 755 {} \;
	
			echo "- Copy patch files"
			cp -rf ./patch/thirdparty/* /usr/local/lxlabs/kloxo/httpdocs/thirdparty

		fi
	else
		echo " - No Thirdparty patches"
	fi
}

function kloxo_webmail_portion () {
	echo "Step for Kloxo Webmail..."

	if [ -f ./patch/webmail-patch-version ] ; then
		patchver=`cat ./patch/webmail-patch-version`

		wget -q http://download.lxcenter.org/download/version/lxwebmail
		kloxover=`cat lxwebmail`
		rm -f lxwebmail
	
		kloxo_check_version $patchver $kloxover
	
		if [ "$?" -eq "1" ] ; then
			echo "- Set ownership and permissions"
			chown -R lxlabs:lxlabs ./patch/webmail/
			find ./patch/webmail/ -type f -name \"*.php*\" -exec chmod 644 {} \;
			find ./patch/webmail/ -type d -exec chmod 755 {} \;
	
			echo "- Copy patch files"
			cp -rf ./patch/webmail/* /home/kloxo/httpd/webmail
		fi
	else
		echo " - No Webmail patches"
	fi
}

function kloxo_awstats_portion () {
	echo "Step for Kloxo Webmail..."

	if [ -f ./patch/awstats-patch-version ] ; then
		patchver=`cat ./patch/awstats-patch-version`

		wget -q http://download.lxcenter.org/download/version/lxawstats
		kloxover=`cat lxawstats`
		rm -f lxawstats
	
		kloxo_check_version $patchver $kloxover
	
		if [ "$?" -eq "1" ] ; then
			echo "- Set ownership and permissions"
			chown -R lxlabs:lxlabs ./patch/awstats/
			find ./patch/awstats/ -type f -name \"*.php*\" -exec chmod 644 {} \;
			find ./patch/awstats/ -type d -exec chmod 755 {} \;
	
			echo "- Copy patch files"
			cp -rf ./patch/awstats/* /home/kloxo/httpd/awstats
		fi
	else
		echo " - No Awstats patches"
	fi
}

function kloxo_run_script () {
	echo "Step for cleanup and fixed..."

	sh /script/cleanup
	sh /script/fixdns
	sh /script/fixweb
	sh /script/fixphp
}

function kloxo_begin () {
	echo
	echo "Begin..."
	echo
}

function kloxo_end () {
	echo
	echo "------------------------------------------"
	echo " * Better reboot your system"
	echo " * run 'sh /script/upcp' for Kloxo update"
	echo "------------------------------------------"
	echo
	echo "... the end"
	echo

	exit
}

function kloxo_exit () {
	exit
}

function kloxo_check_version () {
	if [ "$patchver" ==  "$kloxover" ] ; then
		echo "- Version '$kloxover' equal to patch version '$patchver', patch processing..."
		return 1
	else
		echo "- Version '$kloxover' but patch version '$patchver', no patch process"
		return 0
	fi
}

### execution ###

if [ "$#" == 0 ] ; then
	echo
	echo " -------------------------------------------------------------------"
	echo "  format: sh $0 --type=<master/slave>"
	echo " -------------------------------------------------------------------"
	echo "  --type - master or slave"
	echo
	echo " * Note: - patch dirs inside ./patch"
	echo " * Run kloxo-packer.sh to make kloxo packs (local copy)"
	echo

	exit;
fi

kloxo_begin

kloxo_core_portion

kloxo_thirdparty_portion

kloxo_webmail_portion

kloxo_awstats_portion

kloxo_run_script

kloxo_end