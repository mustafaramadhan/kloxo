<?php 
include_once "htmllib/lib/include.php";
include_once "htmllib/lib/updatelib.php";

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
		$flg = "__path_program_start_vps_flag";
		if (!lxfile_exists($flg)) {
			set_login_skin_to_feather();
		}
	} else {
		$login = new Client(null, null, 'update');
	}

	log_cleanup("*** Executing Update (cleanup) - BEGIN ***");
//
// Check for lxlabs yum repo file and if exists
// Change to lxcenter repo file
//
	if (lxfile_exists("/etc/yum.repos.d/lxlabs.repo")) {
		log_cleanup("- Deleting old lxlabs yum repo");
		lxfile_mv("/etc/yum.repos.d/lxlabs.repo","/etc/yum.repos.d/lxlabs.repo.lxsave");
		exec("rm -f /etc/yum.repos.d/lxlabs.repo");
		log_cleanup("- Removed lxlabs.repo");
		log_cleanup("- Installing lxcenter.repo");
		exec("wget -O /etc/yum.repos.d/lxcenter.repo http://download.lxcenter.org/lxcenter.repo");
		log_cleanup("- Installing yum-protectbase plugin");
		exec("yum install -y -q yum-protectbase");
	}

// Fix #388 - phpMyAdmin config.inc.php permission

	$correct_perm = "0644";
	$check_perm = substr(decoct( fileperms("/usr/local/lxlabs/$program/httpdocs/thirdparty/phpMyAdmin/config.inc.php") ), 2);

	if ($check_perm != $correct_perm) {
		lxfile_unix_chmod("/usr/local/lxlabs/$program/httpdocs/thirdparty/phpMyAdmin/config.inc.php","0644");
	}

//

	if (lxfile_exists(".svn")) {
		log_cleanup("- SVN Found... Exiting");
		exit;
	}

	if ($opt['type'] === 'master') {
		$sgbl->slave = false;
		if (!is_secondary_master()) {
			updateDatabaseProperly();
			fixDataBaseIssues();
			doUpdates();
			lxshell_return("__path_php_path", "../bin/common/driverload.php");
		}
		update_all_slave();
		cp_dbfile();
	} else {
		$sgbl->slave = true;
	}

	if (!is_secondary_master()) {
		updatecleanup();
	}

	if ($opt['type'] === 'master') {
		lxfile_touch("__path_program_start_vps_flag");
	}

	// issue #716 -- [beta] Unresolved dependency on Apache version
	
	// --- remove httpd-itk rpm (from webtatic.repo or others) because may conflict with
	// httpd 2.2.21 that include mpm itk beside mpm worker and event

	// MR - better for httpd 2.4.x where httpd-itk separated from main httpd
/*	
	exec("rpm -q httpd-itk | grep -i 'not installed'", $out, $ret);

	// --- not work with !$ret
	if ($ret !== 0) {
		log_cleanup("Remove httpd-itk rpm package");
		log_cleanup("- Remove httpd-itk");
		exec("rpm -e httpd-itk --nodeps");
		exec("rpm -q httpd | grep -i 'not installed'", $out2, $ret2);
		if ($ret2 === 0) {
			log_cleanup("- Reinstall httpd");
			exec("yum reinstall httpd -y");
		}
	}
*/
	// MR -- mysql not start after kloxo slave install
	log_cleanup("Preparing MySQL service");

	log_cleanup("- MySQL activated");
	exec("chkconfig mysqld on");
	
	log_cleanup("- MySQL restarted");
	exec("service mysqld restart");
/*
	// MR -- importance for update from 6.1.6 or previous where change apache/lighttpd structure 
	// or others for next version

	// MR -- the same accurate with update one-by-one but faster
	// no need mod_fastcgi for httpd 2.4.x because using mod_proxy_fcgi
	$slist = array(
		"httpd httpd-tools", "lighttpd lighttpd-fastcgi", "nginx",
		"bind bind-chroot", "djbdns", "pure-ftpd",
		"mod_php mod_suphp mod_ruid2",
		"autorespond-toaster clamav-toaster",
		"courier-authlib-toaster courier-imap-toaster",
		"daemontools-toaster ezmlm-toaster",
		"libsrs2-toaster maildrop-toaster",
		"ripmime-toaster simscan-toaster",
		"ucspi-tcp-toaster",
		"qmail vpopmail",
		"spamassassin bogofilter",
		"lxphp lxlighttpd lxjailshell",
		"mysql mysql-server"
	);

	setUpdateServices($slist);

	// MR - specific for php variants
	// php52/php53u/php54 taken from ius repo
	// php (with 53 version) taken from atomic/centalt

	$slist = array(
		"php php52 php53u php54",
		"php-devel php52-devel php53u-devel php54-devel",
		"php-xcache php52-xcache php53u-xcache php54-xcache",
		"php-gd php52-gd php53u-gd php54-gd",
		"php-fpm php52-fpm php53u-fpm php54-fpm",
		"php-zend php-ioncube",
		"php-zend-optimizer php-ioncube-loader",
		"php-zend-guard-loader",
		"php52-zend-optimizer-loader php52-ioncube-loader",
		"php53u-zend-guard-loader php53u-ioncube-loader",
		"php54-zend-guard-loader php54-ioncube-loader",
		"php-suhosin php52-suhosin php53u-suhosin php54-suhosin"
	);

	setUpdateServices($slist);
*/
	
	$slist = array(
		"httpd* lighttpd* nginx*",
		"mod_* mysql* php* lx*",
		"bind* djbdns* pure-ftpd*",
		"*-toaster qmail* vpopmail*",
		"spamassassin* bogofilter*",
	);

	setUpdateServices($slist);
	
	// MR -- use this trick for qmail non-daemontools based
	log_cleanup("Preparing some services again");
	
	log_cleanup("- courier-imap enabled and restart queue");
	exec("chkconfig courier-imap on");
	createRestartFile("courier-imap");
	
	log_cleanup("- qmail enabled and restart queue");
	exec("chkconfig qmail on");
	createRestartFile("qmail");

	$fixapps = array("dns", "web", "php", "mail", "ftpuser");
	setUpdateConfigWithVersionCheck($fixapps, $opt['type']);

	log_cleanup("Fixing 'lxpopuser' MySQL password");
	exec("sh /script/fixvpop");
	log_cleanup("- Fixing process");

	// --- for anticipate change xinetd listing
	exec("service xinetd restart");

	log_cleanup("*** Executing Update (cleanup) - END ***");
}

function cp_dbfile()
{
	global $gbl, $sgbl, $login, $ghtml;

	$progname = $sgbl->__var_program_name;

	lxfile_cp("../sbin/{$progname}db", "/usr/bin/{$progname}db");
	lxfile_generic_chmod("/usr/bin/{$progname}db", "0755");
}

