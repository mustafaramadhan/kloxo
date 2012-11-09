<?php 

function update_main()
{
	global $argc, $argv;
	global $gbl, $sgbl, $login, $ghtml; 

	log_cleanup("*** Executing Update (upcp) - BEGIN ***");

	debug_for_backend();
	$login = new Client(null, null, 'upgrade');
	$DoUpdate = false;

	$opt = parse_opt($argv);

	log_cleanup("Kloxo Install/Update");
	
	if (lxfile_exists("/var/cache/kloxo/kloxo-install-firsttime.flg")) {
		log_cleanup("- Installing Kloxo packages at the first time");
		$DoUpdate = true;
	} elseif (lxfile_exists("/var/cache/kloxo/kloxo-install-secondtime.flg")) {
		log_cleanup("- Installing Kloxo packages at reinstall");
		$DoUpdate = true;
	} else {
		log_cleanup("- Getting Version Info from the LxCenter download Server");
		$upversion = false;
		if ((isset($opt['till-version']) && $opt['till-version']) || lxfile_exists("__path_slave_db")) {
			$sgbl->slave = true;
			$upversion = findNextVersion($opt['till-version']);
			$type = 'slave';
		} else {
			$sgbl->slave = false;
			$upversion = findNextVersion();
			$type = 'master';
		}

		if ($upversion) {
			log_cleanup("- Connecting LxCenter download server");
			do_upgrade($upversion);
			log_cleanup("- Upgrade Done. Cleanup....");
			flush();
		} else {
			$localversion = $sgbl->__ver_major_minor_release;
			log_cleanup("- Kloxo is the latest version ($localversion)");
		}


	// Thirdparty/Webmail/AWstats checks

	$verWM = getVersionNumber(get_package_version("lxwebmail"));
	$verAW = getVersionNumber(get_package_version("lxawstats"));
	$ver = file_get_contents("http://download.lxcenter.org/download/thirdparty/kloxo-version.list");
	$verTP = getVersionNumber($ver);

	if ( !lxfile_exists("/var/cache/kloxo/lxwebmail$verWM.tar.gz" ) ) { $retWM = true; } else { $retWM = false; }
	if ( !lxfile_exists("/var/cache/kloxo/lxawstats$verAW.tar.gz" ) ) { $retAW = true; } else { $retAW = false; }
	if ( !lxfile_exists("/var/cache/kloxo/kloxo-thirdparty.$verTP.zip" ) ) { $retTP = true; } else { $retTP = false; }

	installThirdparty();
	installWebmail();
	installAwstats();

		// Run cleanups or not
		if ($retTP || $retWM || $retAW || $upversion) {
			$DoUpdate = true;
		} else {
			$DoUpdate = false;
		}

	}

	log_cleanup("*** Executing Update (upcp) - END ***");

	if ( $DoUpdate == false ) {
		log_cleanup("Run /script/cleanup if you want to fix/restore/(re)install non-working components.");
			exit;
	}

	if (is_running_secondary()) {
		log_cleanup("Not running Update cleanup, because this is running as secondary\n");
		exit;
	}
	
	//
	// Executing update/cleanup process
	//
	lxfile_cp("htmllib/filecore/php.ini", "/usr/local/lxlabs/ext/php/etc/php.ini");
	$res = pcntl_exec("/bin/sh", array("../bin/common/updatecleanup.sh", "--type=$type"));
}

function do_upgrade($upversion)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$program = $sgbl->__var_program_name;

	if (file_exists(".svn") || file_exists(".git")) {
		log_cleanup("BREAK -> Development version found");
		exit;
	}

	$programfile = "$program-" . $upversion . ".zip";

	lxfile_rm_rec("__path_program_htmlbase/help");
	lxfile_mkdir("help");
	lxfile_rm_rec("__path_program_htmlbase/htmllib/script");
	lxfile_rm_rec("__path_program_root/pscript");

	$saveddir = getcwd();
	lxfile_rm_rec("__path_program_htmlbase/download");
	lxfile_mkdir("download");
	chdir("download");
	log_cleanup("Downloading $programfile");
	download_source("/$program/$programfile");
	log_cleanup("Download Done!... Start unzip");
	exec("cd ../../ ; unzip -o httpdocs/download/$programfile");
	// issue #710 - Make sure the files are owned by lxlabs UID/GID
	exec("chown -R lxlabs:lxlabs /usr/local/lxlabs/");
	chdir($saveddir);
}

function fixDataBaseIssues()
{
	log_cleanup("Fix Database Issues");

	log_cleanup("- Fix admin account database settings");
	$sq = new Sqlite(null, 'domain');
	$sq->rawQuery("update domain set priv_q_php_flag = 'on'");
	$sq->rawQuery("update web set priv_q_php_flag = 'on'");
	$sq->rawQuery("update client set priv_q_php_flag = 'on'");
	$sq->rawQuery("update client set priv_q_addondomain_num = 'Unlimited' where nname = 'admin'");
	$sq->rawQuery("update client set priv_q_rubyrails_num = 'Unlimited' where nname = 'admin'");
	$sq->rawQuery("update client set priv_q_rubyfcgiprocess_num = 'Unlimited' where nname = 'admin'");
	$sq->rawQuery("update client set priv_q_mysqldb_usage = 'Unlimited' where nname = 'admin'");
	$sq->rawQuery("update client set priv_q_phpfcgi_flag = 'on' where nname = 'admin'");
	$sq->rawQuery("update client set priv_q_phpfcgiprocess_num = 'Unlimited' where nname = 'admin'");
	$sq->rawQuery("update client set priv_q_subdomain_num = 'Unlimited' where nname = 'admin'");
	$sq->rawQuery("update client set priv_q_totaldisk_usage = 'Unlimited' where nname = 'admin'");
	$sq->rawQuery("update client set priv_q_php_manage_flag = 'on' where nname = 'admin'");
	$sq->rawQuery("update client set priv_q_installapp_flag = 'on' where nname = 'admin'");
	$sq->rawQuery("update client set priv_q_cron_minute_flag = 'on' where nname = 'admin'");
	$sq->rawQuery("update client set priv_q_document_root_flag = 'on' where nname = 'admin'");
	$sq->rawQuery("update client set priv_q_runstats_flag = 'on' where nname = 'admin'");
	$sq->rawQuery("update client set priv_q_webhosting_flag = 'on' where nname = 'admin'");
	$sq->rawQuery("update ticket set parent_clname = 'client-admin' where subject = 'Welcome to Kloxo'");
	$sq->rawQuery("update domain set dtype = 'maindomain' where dtype = 'domain'");

	log_cleanup("- Set default database settings");
	db_set_default('mmail', 'remotelocalflag', 'local');
	db_set_default('mmail', 'syncserver', 'localhost');
	db_set_default('dns', 'syncserver', 'localhost');
	db_set_default('pserver', 'coma_psrole_a', ',web,dns,mmail,mysqldb,');
	db_set_default('web', 'syncserver', 'localhost');
	db_set_default('uuser', 'syncserver', 'localhost');
	db_set_default('client', 'syncserver', 'localhost');
	db_set_default('addondomain', 'mail_flag', 'on');
	db_set_default('client', 'priv_q_can_change_limit_flag', 'on');
	db_set_default('web', 'priv_q_installapp_flag', 'on');
	db_set_default('client', 'priv_q_installapp_flag', 'on');
	db_set_default('client', 'websyncserver', 'localhost');
	db_set_default('client', 'mmailsyncserver', 'localhost');
	db_set_default('client', 'mysqldbsyncserver', 'localhost');
	db_set_default('client', 'priv_q_can_change_password_flag', 'on');
	db_set_default('client', 'coma_dnssyncserver_list', ',localhost,');
	db_set_default('domain', 'priv_q_installapp_flag', 'on');
	db_set_default('domain', 'dtype', 'domain');
	db_set_default('domain', 'priv_q_php_manage_flag', 'on');
	db_set_default('web', 'priv_q_php_manage_flag', 'on');
	db_set_default('client', 'priv_q_php_manage_flag', 'on');
	db_set_default('client', 'priv_q_webhosting_flag', 'on');
	db_set_default_variable_diskusage('client', 'priv_q_totaldisk_usage', 'priv_q_disk_usage');
	db_set_default_variable_diskusage('domain', 'priv_q_totaldisk_usage', 'priv_q_disk_usage');
	db_set_default_variable('web', 'docroot', 'nname');
	db_set_default_variable('client', 'used_q_maindomain_num', 'used_q_domain_num');
	db_set_default_variable('client', 'priv_q_maindomain_num', 'priv_q_domain_num');
	db_set_default("servermail", "domainkey_flag", "on");

	log_cleanup("- Fix resourceplan settings in database");
	migrateResourceplan('domain');
	$sq->rawQuery("update resourceplan set realname = nname where realname = ''");
	$sq->rawQuery("update resourceplan set realname = nname where realname is null");
	lxshell_php("../bin/common/fixresourceplan.php");

	log_cleanup("- Alter some database tables to fit that of Kloxo");
	// TODO: Check if this is still longer needed!
	$sq->rawQuery("alter table sslcert change text_ca_content text_ca_content longtext");
	$sq->rawQuery("alter table sslcert change text_key_content text_key_content longtext");
	$sq->rawQuery("alter table sslcert change text_csr_content text_csr_content longtext");
	$sq->rawQuery("alter table sslcert change text_crt_content text_crt_content longtext");
	$sq->rawQuery("alter table mailaccount change ser_forward_a ser_forward_a longtext");
	$sq->rawQuery("alter table dns change ser_dns_record_a ser_dns_record_a longtext");
	$sq->rawQuery("alter table installsoft change ser_installappmisc_b ser_installappmisc_b longtext");
	$sq->rawQuery("alter table web change ser_redirect_a ser_redirect_a longtext");

	log_cleanup("- Set default welcome text at Kloxo login page");
	initDbLoginPre();

	log_cleanup("- Remove default db password if exists");
	critical_change_db_pass();
}

function doUpdates()
{
	global $gbl, $sgbl, $login, $ghtml;

	createFlagDir();

	fixIpAddress();

	fixservice();

	add_domain_backup_dir();

	createOSUserAdmin();

	call_with_flag("fix_phpini");

	call_with_flag("fix_awstats");

	call_with_flag("fix_domainkey");

	setWatchdogDefaults();

	fixMySQLRootPassword();

	save_admin_email();

	getKloxoLicenseInfo();

	createDatabaseInterfaceTemplate();

	fix_self_ssl();

}

