<?php 
include_once "lib/html/include.php";
include_once "lib/html/updatelib.php";

exit_if_another_instance_running();
debug_for_backend();
updatecleanup_main();

function updatecleanup_main()
{
	global $argc, $argv;
	global $gbl, $sgbl, $login, $ghtml; 

	$program = $sgbl->__var_program_name;
	$opt = parse_opt($argv);

	if ($opt['type'] === 'master') {
		initProgram('admin');
		$flg = "$sgbl->__path_program_start_vps_flag";

		if (!lxfile_exists($flg)) {
			set_login_skin_to_simplicity();
		}
	} else {
		$login = new Client(null, null, 'update');
	}

	log_cleanup("*** Executing Update (cleanup) - BEGIN ***");

	if ($opt['type'] === 'master') {
		$sgbl->slave = false;
		
		if (!is_secondary_master()) {
			print(">>> Execute fixDataBaseIssues() <<<\n");
			fixDataBaseIssues();
			print(">>> Execute doUpdates() <<<\n");
			doUpdates();
			print(">>> Execute driverload.php <<<\n");
			lxshell_return("$sgbl->__path_php_path", "../bin/common/driverload.php");
		}

		print(">>> Execute update_all_slave() <<<\n");
		update_all_slave();
		print(">>> Execute cp_dbfile() <<<\n");
		cp_dbfile();
	} else {
		$sgbl->slave = true;
	}

	if (!is_secondary_master()) {
		print(">>> Execute updatecleanup() <<<\n");
		updatecleanup();
	}

	if ($opt['type'] === 'master') {
		lxfile_touch("$sgbl->__path_program_start_vps_flag");
	}

	// MR -- mysql not start after kloxo slave install
	log_cleanup("- Preparing MySQL/MariaDB service");

	if (file_exists("/etc/rc.d/init.d/mysqld")) {
		log_cleanup("- MySQL activated");
		exec("chkconfig mysql off >/dev/null 2>&1");
		exec("chkconfig mysqld on");
	
	//	log_cleanup("- MySQL restarted");
	//	exec("service mysqld restart");
	} elseif (file_exists("/etc/rc.d/init.d/mysql")) {
		log_cleanup("- MariaDB activated");
		exec("chkconfig mysqld off >/dev/null 2>&1");
		exec("chkconfig mysql on");
	
	//	log_cleanup("- MariaDB restarted");
	//	exec("service mysql restart");
	}

	log_cleanup("- Updating Main services");

/*	
	$slist = array(
		"kloxomr7",
		"httpd* lighttpd* nginx* hiawatha* openlitespeed* monkey*",
		"mod_* mysql* mariadb* MariaDB* php*",
		"bind* djbdns* pdns* nsd* maradns* mydns yadifa*",
		"varnish* trafficserver* squid*",
		"pure-ftpd* *-toaster bogofilter",
		"kloxomr-webmail-*.noarch",
		"kloxomr-thirdparty-*.noarch",
		"kloxomr7-thirdparty-*.noarch",
		"kloxomr-editor-*.noarch"
	);

	setUpdateServices($slist);
*/
	## MR -- change to update all

	log_cleanup('Updating All packages - WAIT to process...');
//	exec("'rm' -f /var/run/yum.pid");
	lxshell_return("yum", "clean", "expire-cache");
	$ret = lxshell_return("yum", "update", "-y");

	if (!$ret) {
		log_cleanup("- No update found/not installed");
	} else {
		log_cleanup("- New version of packages installed");
	}
/*
	// MR -- use this trick for qmail non-daemontools based
	log_cleanup("- Preparing some services again");
	
	log_cleanup("- qmail enabled and restart queue");
	exec("chkconfig qmail on");
	createRestartFile("qmail");
*/

	if (isset($opt['without-services'])) {
		// no action
	} else {
		setInitialServices();

		log_cleanup("Fix services");
		setUpdateConfigWithVersionCheck(getListOnList('fix'), $opt['type']);
	}

	// MR -- installatron need ownership as root:root
	if (is_link("/usr/local/lxlabs/kloxo/httpdocs/installatron")) {
		unlink("/usr/local/lxlabs/kloxo/httpdocs/installatron");
		symlink("/var/installatron/frontend", "/usr/local/lxlabs/kloxo/httpdocs/installatron");
	}

	log_cleanup("Fixing Hiawatha service");
	fix_hiawatha();

	log_cleanup("*** Executing Update (cleanup) - END ***");
}

function cp_dbfile()
{
	global $gbl, $sgbl, $login, $ghtml;

	$progname = $sgbl->__var_program_name;

	lxfile_cp("../sbin/{$progname}db", "/usr/bin/{$progname}db");
	lxfile_generic_chmod("/usr/bin/{$progname}db", "0755");
}

