<?php 

function update_main()
{
	global $argc, $argv;
	global $gbl, $sgbl, $login, $ghtml; 

	log_cleanup("*** Executing Install/Update (upcp) - BEGIN ***");

	debug_for_backend();

	$login = new Client(null, null, 'upgrade');

	$DoUpdate = false;

	$opt = parse_opt($argv);

//	log_cleanup("- Kloxo Install/Update");
	
	$type = getKloxoType();

	if ($type === '') {
		log_cleanup("- Installing Kloxo packages at the first time");
		$DoUpdate = true;
	} else {
		$DoUpdate = false;
	}

	if (is_running_secondary()) {
		log_cleanup("- Not running Update cleanup, because this is running as secondary\n");
	}

	if ( $DoUpdate === false ) {
		log_cleanup("- Run 'sh /script/cleanup' if you want to fix/restore non-working components.");
	} else {
		system("cd /usr/local/lxlabs/kloxo/httpdocs; lxphp.exe ../bin/common/tmpupdatecleanup.php --type={$type}");
	}

	log_cleanup("*** Executing Install/Update (upcp) - END ***");
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
	$sq->rawQuery("update client set priv_q_easyinstaller_flag = 'on' where nname = 'admin'");
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
	db_set_default('web', 'priv_q_easyinstaller_flag', 'on');
	db_set_default('client', 'priv_q_easyinstaller_flag', 'on');
	db_set_default('client', 'websyncserver', 'localhost');
	db_set_default('client', 'mmailsyncserver', 'localhost');
	db_set_default('client', 'mysqldbsyncserver', 'localhost');
	db_set_default('client', 'priv_q_can_change_password_flag', 'on');
	db_set_default('client', 'coma_dnssyncserver_list', ',localhost,');
	db_set_default('domain', 'priv_q_easyinstaller_flag', 'on');
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
	$sq->rawQuery("alter table installsoft change ser_easyinstallermisc_b ser_easyinstallermisc_b longtext");
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

	// MR -- disabled it because trouble set 'chkconfig on' for also inactive all services
//	fixservice();

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
}
