if echo $0|grep 'fixweb' >/dev/null 2>&1 ; then
	extra="[--domain]"
fi

if [ "$1" == "--help" ] || [ "$1" == "-h" ] ; then
	echo ""
	echo " ------------------------------------------------------------------------------"
	echo "  format: sh $0 [--server=] [--client=] [--target=] $extra"
	echo " ------------------------------------------------------------------------------"
	echo "  --server="
	echo "      localhost: master (default)"
	echo "      slaveid: slave id (ip or hostname)"
	echo "      all: all servers (master and slave)"
	echo "      - multiple servers separated by ',' (comma)"
	echo "  --client="
	echo "      - optional and 'all' for default value"
	echo "      - multiple clients separated by ',' (comma)"
	echo "      - use this option automatically set '--server=all'"
	if echo $0|grep 'fixweb' >/dev/null 2>&1 ; then
		echo "  --target="
		echo "      - defaults: for 'defaults' config"
		echo "      - domains: for 'domains' config"
		echo "  --domain="
		echo "      - optional and 'all' for default value"
		echo "      - multiple domain separated by ',' (comma)"
	elif echo $0|grep 'fixdns' >/dev/null 2>&1 ; then
		echo "  --target="
		echo "      - master: for 'master' config"
		echo "      - slave: for 'slave' config"
	fi
	echo ""
	exit
fi

echo ""
echo "- For help, type '$0 [--help|-h]'"

if [ "$#" == 0 ] ; then
	echo "- No argument supplied. Defaulting to localhost (master) servers"
fi

echo ""