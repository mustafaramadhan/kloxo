#!/bin/sh

progname=kloxo

__path_php_path=$(command -v php 2>/dev/null || echo "/usr/bin/php");

__path_program_root="/usr/local/lxlabs/${progname}";
__path_slave_db="${__path_program_root}/etc/conf/slave-db.db";

if [ -f ${__path_program_root}/sbin/custom.${progname}.php ] ; then
	__path_server_path="${__path_program_root}/sbin/custom.${progname}.php";
else
	__path_server_path="${__path_program_root}/sbin/${progname}.php";
fi

__path_server_exe="${__path_program_root}/sbin/${progname}.exe";
__path_low_memory_file="${__path_program_root}/etc/flag/lowmem.flag";

kill_and_save_pid() {
	name=$1;
	kill_pid ${name};
	usleep 100;
	save_pid ${name};
}

save_pid() {
	name=$1;
	echo $$ > "${__path_program_root}/pid/${name}.pid";
}

kill_pid() {
	name=$1

	if [ -f ${__path_program_root}/pid/${name}.pid ] ; then
		pid=$(cat ${__path_program_root}/pid/${name}.pid);

		kill ${pid} 2>/dev/null;
		usleep 10000;
		kill -9 ${pid} 2>/dev/null;
	fi
}

wrapper_main() {

	if [ -f ${__path_slave_db} ] ; then
		string="slave";
	else 
		string="master";
	fi

	mkdir ../log 2>/dev/null;
	mkdir ../pid 2>/dev/null;

	while : ; do

		if [ -f ${__path_low_memory_file} ] ; then
			/bin/cp ${__path_server_exe}.core ${__path_server_exe};
			chmod 755 ${__path_server_exe};
			exec ${__path_server_exe} ${string} >/dev/null 2>&1;
		else
			cd ${__path_program_root}/httpdocs
			exec ${__path_php_path} -f ${__path_server_path} ${string} >/dev/null 2>&1;
		fi

		sleep 10;
	done
}

kill_and_save_pid wrapper;
wrapper_main;

