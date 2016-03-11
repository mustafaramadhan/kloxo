<?php

class pserverconf_b extends Lxaclass
{

	static $__desc = array("", "", "slave_server");
	static $__desc_usesmtp = array("f", "", "use_smtp");
	static $__desc_smtpserver = array("", "", "smtp_server");
	static $__desc_smtpport = array("", "", "smtp_port");
	static $__desc_smtpuseauth = array("f", "", "smtp_auth");
	static $__desc_smtpuser = array("", "", "smtp_auth_user");
	static $__desc_smtppass = array("", "", "smtp_auth_password");

}

class psrole_a extends LxaClass
{

	static $__desc = array("n", "", "server_role");
	static $__desc_nname = array("n", "", "server_role");

	static function createListAddForm($parent, $class)
	{
		return true;
	}

	static function createListAlist($parent, $class)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$alist[] = 'a=show';
		$alist[] = "a=updateform&sa=information";

		// MR -- look like not work but must
		// because password need slavedb.db file where only exists in slave
		if (!$parent->isLocalhost()) {
			$alist[] = "a=updateform&sa=password";
		}

	/*
		if ($sgbl->isHyperVm()) {
			$alist[] = "a=graph&sa=vpsbase";
		}
	*/
		$alist[] = "a=list&c=$class";

		return $alist;
	}

	static function addform($parent, $class, $typetd = null)
	{
		global $gbl, $sgbl, $login, $ghtml;

		if ($sgbl->isKloxo()) {
			$list = array('web', 'mmail', 'dns', 'mysqldb');
		} else {
			$list = array('vps', 'dns');
		}

		$vv = $parent->getNotExistingList($vlist, "nname", 'psrole_a', $list);
		$ret['action'] = 'add';
		$ret['variable'] = $vlist;

		return $ret;
	}

	static function add($parent, $class, $param)
	{
		return $param;
	}

}

class pservercore extends Lxclient
{

// Core
	static $__desc = array("", "", "server");
// Data
	static $__desc_nname = array("n", "", "IP_or_hostname", "a=show");
	static $__desc_username = array("", "", "user_name.");
	static $__desc_rolelist = array("", "", "roles");
	static $__desc_description = array("n", "", "verbose_description (to_identify)");
	static $__desc_tmpdir = array("", "", "tmpdir_for_backup_(/tmp)");
	static $__desc_timezone = array("n", "", "timezone");
	static $__desc_ostype = array("e", "", "t:type_of_os");
	static $__desc_ostype_v_fedora = array("", "", "fedora");
	static $__desc_ostype_v_rhel = array("", "", "rhel");
	static $__desc_ostype_v_debian = array("", "", "debian");
	static $__desc_used_domainlist_mmail_f = array("", "", "mail_servers");
	static $__desc_used_domainlist_web_f = array("", "", "domains_as_web");
	static $__desc_used_domainlist_dns_f = array("", "", "domains_as_dns");
	static $__desc_used_domainlist_secdns_f = array("", "", "domains_as_secondary_dns");
	static $__desc_used_domainlist_mysqldb_f = array("", "", "mysql_databases");
	static $__desc_used_domainlist_mssqldb_f = array("", "", "mssql_databases");
	static $__desc_newpassword_f = array("", "", "new_mysql_root_password");
	static $__desc_osversion = array("", "", "version");
	static $__desc_used_f = array("e", "", "used");
	static $__desc_server_traffic_usage = array("q", "", "server_traffic_usage");
	static $__desc_server_traffic_last_usage = array("q", "", "server_traffic_usage_for_last_month");
	static $__desc_used_f_v_on = array("", "", "used");
	static $__desc_used_f_v_dull = array("", "", "not_used");
	static $__desc_pserverconf_b = array("", "", "configuration");
	static $__desc_load_threshold = array("", "", "load_threshold_at_which_warning_is_sent");
	static $__desc_cron_mailto = array("", "", "mail_to");
	static $__desc_clientname = array("", "", "exclusive_client");
	static $__desc_internalnetworkip = array("", "", "internal_network_ip");
	static $__desc_realhostname = array("", "", "FQDN Hostname");
	static $__desc_loadavg = array("", "", "LoadAvg");
	static $__desc_ps_password = array("", "", "password");
	static $__desc_ddate = array("", "", "date");


	static $__desc_retype_admin_p_f = array("", "", "retype_admin_or_server_password");
	static $__desc_button_dbpassword_f = array("b", "", "", 'a=updateform&sa=dbpassword');
	static $__desc_button_list_process_f = array("b", "", "", 'a=list&c=process');
	static $__desc_button_password_f = array("b", "", "", 'a=updateform&sa=password');
	static $__desc_button_list_ip_f = array("b", "", "", 'a=list&c=ipaddress');
	static $__desc_button_showused_f = array("b", "", "", 'a=updateform&sa=showused');
	static $__desc_button_list_service_f = array("b", "", "", 'a=list&c=service');
	static $__desc_button_list_usage_f = array("b", "", "", 'a=list&c=diskusage');
	static $__desc_button_file_home_f = array("b", "", "", 'a=show&l[class]=ffile&l[nname]=/');

//Objects
// Lists
	static $__desc_hostdeny_l = array("d", "", "virtual");
	static $__desc_process_l = array("v", "", "virtual");
	static $__desc_ipaddress_l = array("R", "", "virtual");
	static $__desc_component_l = array("v", "", "virtual");
	static $__desc_diskusage_l = array("v", "", "virtual");
	static $__desc_cron_l = array("", "", "virtual");
	static $__desc_service_l = array("d", "", "virtual");
	static $__desc_sslcert_l = array("d", "", "virtual");
	static $__desc_uuser_l = array("v", "", "virtual");
	static $__desc_dbadmin_l = array('d', '', '', '');
	static $__desc_aspnet_l = array('d', '', '', '');
	static $__desc_odbc_l = array('d', '', '', '');
	static $__desc_firewall_l = array("", "", "virtual");
	static $__desc_watchdog_l = array("", "", "");
	static $__desc_package_l = array("", "", "virtual");
	static $__desc_ffile_l = array('v', '', '', '');
	static $__desc_ftpsession_l = array('v', '', '', '');
	static $__desc_proxy_o = array('', '', '', '');
	static $__desc_sshauthorizedkey_l = array("", "", "");
	static $__desc_serverftp_o = array('d', '', '', '');
	static $__desc_driver_o = array('d', '', '', '');
	static $__desc_lxupdate_o = array('', '', '', '');
	static $__desc_servermail_o = array('d', '', '', '');
	static $__desc_serverspam_o = array('', '', '', '');
	static $__desc_llog_o = array('d', '', '', '');
	static $__desc_sshconfig_o = array('d', '', '', '');


	static $__acdesc_update_cron_mailto = array("", "", "cron_mail");
	static $__acdesc_update_dbpassword = array("", "", "db_admin");
	static $__acdesc_update_reboot = array("", "", "reboot");
	static $__acdesc_update_poweroff = array("", "", "poweroff");
	static $__acdesc_update_readipaddress = array("", "", "re_read_ipaddress");
	static $__acdesc_update_loaddriverinfo = array("", "", "re_load_driver_info");
	static $__acdesc_update_mysqlpasswordreset = array("", "", "mysql_password_reset");
	static $__acdesc_update_showused = array("", "", "domain_list");
	static $__acdesc_update_update = array("", "", "edit");
	static $__acdesc_update_phpsmtp = array("", "", "kloxo_smtp");
	static $__acdesc_update_information = array("", "", "information");
	static $__acdesc_update_timezone = array("", "", "timezone");
	static $__acdesc_update_phpmyadmin = array("", "", "phpmyadmin");

	static $__desc_no_fix_config = array("f", "", "no_fix_config");
	static $__desc_pserver_o = array('d', '', '', '');

	static $__desc_use_apache24 = array("f", "", "use_apache24");

	function syncToSystem()
	{
		// Special for pserver... Since the whole idea of remote syncing is handled here,
		// it makes sense to have the special case of pserver when it is added here itself.

		global $gbl, $sgbl, $login, $ghtml;

		if ($this->dbaction === 'add') {
			dprint("This is ssytem not syncing anymore\n");

			return false;
		}

		if (!$this->isSync()) {
			return;
		}

		if ($this->dbaction === 'delete') {
			$gbl->pserver_password = $this->realpass;
		}

		rl_exec_set($this->__masterserver, $this->syncserver, $this);

	}

	function dosyncToSystem()
	{
		if ($this->dbaction === 'delete') {
			return;
		}
		
		if ($this->dbaction === 'add') {
			return;
		}
		
		return $this->driverApp->dosyncToSystem();
	}

	function isSync()
	{
		if ($this->subaction === 'password') {
			return true;
		}

		return true;
	}

	static function createListAlist($parent, $class)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$alist[] = "a=show";

		$alist[] = "a=list&c=$class";

		if ($parent->isAdmin()) {
		//	$alist[] = "a=addform&c=$class";
		}

		$alist[] = "a=updateform&sa=forcedeletepserver&c=client";

		return $alist;
	}

	function inheritSynserverFromParent()
	{
		return false;
	}

	function getAnyErrorMessage()
	{
		global $gbl, $sgbl, $login, $ghtml;

		if (!$this->getObject('sshconfig')->ssh_port) {
			$ghtml->__http_vars['frm_emessage'] = "ssh_port_not_configured";
		}

		if (!$this->isLocalhost() && $this->realpass === 'admin') {
			$ghtml->__http_vars['frm_emessage'] = "security_warning";
		}

		if ($sgbl->isKloxo() && !$this->getObject('servermail')->myname) {
			//$ghtml->__http_vars['frm_emessage'] = "mail_server_name_not_set";
		}


		// MR -- need this trick to make sure driver info sync between slavedb and kloxo database
		// if going to 'switch program' sync will be processed.
	//	if (!$this->getObject('pserver')->web_driver) {
	//	if (!slave_get_driver('web')) {
		if (!$gbl->getSyncClass($this->__masterserver, $this->syncserver, 'web')) {
			$ghtml->__http_vars['frm_emessage'] = "switch_program_not_set";
		}


		if (!db_get_value("serverweb", "pserver-" . $this->syncserver, "php_type")) {
			if (isWebProxyOrApache()) {
				$ghtml->__http_vars['frm_emessage'] = "phptype_not_set";
			}
		}

		if ($login->sp_specialplay_o->specialplay_b->skin_name === 'simplicity') {
			$server = $this->syncserver;
			$server_phpini = unserialize(base64_decode(db_get_value("phpini", "pserver-" . $server, "ser_phpini_flag_b")));

			// MR -- pserver must set/update php.ini
			if (!isset($server_phpini->session_save_path_flag)) {
				$ghtml->__http_vars['frm_emessage'] = "phpini_not_set_pserver";
			}
		}

		parent::getAnyErrorMessage();
	}

	function getIpPool($totalneeded)
	{
		global $login;

		if (!($totalneeded > 0)) {
			return;
		}

		$list = $this->getList('ippool');

		if (!$list) {
			throw new lxException($login->getThrow("no_ip_pool_configured_for_this_slave"), '', $this->nname);
		}

		$totallist = null;
		$newnum = $totalneeded;

		foreach ((array)$list as $l) {
			$nameserver = $l->nameserver;
			$networkgateway = $l->networkgateway;
			$netmask = $l->networknetmask;
			$iplist = $l->getFreeIp($newnum);
			$totallist = lx_array_merge(array($iplist, $totallist));

			if (count($totallist) >= $totalneeded) {
				break;
			} else {
				$newnum = $totalneeded - count($totallist);
			}
		}

		return array('nameserver' => $nameserver, 'networkgateway' => $networkgateway, 'ip' => $totallist, 'networknetmask' => $netmask);
	}

	function __construct($masterserver, $readserver, $name)
	{
		global $gbl, $sgbl, $login, $ghtml;

		parent::__construct($masterserver, $name, $name);
	}

	function getShowInfo()
	{
		$list = $this->getList('ipaddress');

		$namelist = implode(", ", get_namelist_from_objectlist($list, "ipaddr"));

		if (strlen($namelist) > 20) {
			$namelist = substr($namelist, 0, 20);
			$namelist .= " ...";
		}

		return "Ipaddress: $namelist";
	}

	static function createServerInfo($list, $class = null)
	{
		global $gbl, $sgbl, $login, $ghtml;

		if (!$list) {
			return null;
		}

		$sq = new Sqlite(null, "pserver");

		foreach ($list as $l) {
			$rl[] = "nname = '$l'";
		}

		$string = implode(" OR ", $rl);
		$res = $sq->rawQuery("select nname, description, realhostname, osversion from pserver where $string");

		if (!$res) {
			return $res;
		}

		foreach ($res as $r) {
			$driverappstring = null;
			if ($class) {
				$driverapp = $gbl->getSyncClass('localhost', $r['nname'], $class);
				$driverappstring = ": Driver is $driverapp";
			}
			$r['osversion'] = trim($r['osversion']);
			$return[] = "{$r['nname']} is {$r['description']} {$r['realhostname']} ({$r['osversion']}) $driverappstring";
		}

		$ret = "\n" . implode("\n", $return) . "\n\n";

		return $ret;
	}

	static function createListNlist($parent, $view)
	{
	//	$nlist['cpstatus'] = '3%';
		$nlist['ostype'] = '3%';
		$nlist['used_f'] = '3%';
		$nlist['nname'] = '50%';
		$nlist['osversion'] = '50%';
	//	$nlist['button_showused_f'] = '5%';
		$nlist['button_password_f'] = '5%';
		$nlist['button_list_process_f'] = '5%';
		$nlist['button_list_ip_f'] = '5%';
		$nlist['button_list_service_f'] = '5%';
		$nlist['button_list_usage_f'] = '5%';
		$nlist['button_file_home_f'] = '5%';

		return $nlist;
	}

	function createShowIlist()
	{
		return array("nname");
	}

	function createShowClist($subaction)
	{
		$clist['diskusage'] = null;
		$clist = null;

		return $clist;
	}

	function getVpsRam()
	{
		global $gbl, $sgbl, $login, $ghtml;
		$sq = new Sqlite(null, "vps");
		$driverapp = $gbl->getSyncClass('localhost', $this->nname, 'vps');

		if ($driverapp === 'xen') {
			$list = array("priv_q_realmem_usage");
		} else {
			$list = array("priv_q_guarmem_usage", "priv_q_memory_usage");
		}

		$res = $sq->getRowsWhere('syncserver = :nname', array(':nname' => $this->nname), $list);

		if (!$res) {
			return;
		}

		foreach ($res as $r) {
			foreach ($r as $k => $v) {
				if (!isset($total[$k])) {
					$total[$k] = 0;
				}
				$total[$k] += $v;
			}
		}

		foreach ($total as $k => $v) {
			$var = strfrom($k, "priv_q_");
			$descr = get_classvar_description('vps', $var);
			$ret[] = array('memory', $descr[2], $total[$k], "-");
		}

		return $ret;
	}

	static function getTimeZoneList()
	{
		global $global_list_path;

		$global_list_path = null;

		do_recurse_dir("/usr/share/zoneinfo/", "listFile", null);

		return $global_list_path;
	}

	function createShowRlist($subaction)
	{
		global $gbl, $sgbl, $login, $ghtml;

		static $rlist;

	//	$l = $this->pserverInfo();

		if ($rlist) {
			return $rlist;
		}

		if ($sgbl->isHyperVm()) {
			$rlist = $this->getVpsRam();
		}

		$driverapp = $gbl->getSyncClass($this->__masterserver, $this->__readserver, 'pserver');
		$l = rl_exec_get($this->__masterserver, $this->__readserver, array("pserver__$driverapp", "pserverInfo"));

		$osdet = $l['osdet'];

		if ($this->osversion !== $osdet['version']) {
			$this->osversion = $osdet['version'];
			$this->setUpdateSubaction();
		}

		$lvm = $l['lvm'];

		foreach ((array)$lvm as $k => $c) {
			$rlist[] = array('lvm', "{$c['nname']}", $c['used'], $c['total']);
		}

		$disk = $l['disk'];

		foreach ($disk as $k => $c) {
			$c['nname'] = str_replace(":", "_", $c['nname']);

			if (csa($c['nname'], "mapper")) {
				$c['nname'] = strfrom($c['nname'], "/dev/mapper/");
			}

		//	$rlist[] = array('disk', "{$c['mountedon']} ({$c['nname']})", $c['used'], $c['kblock']);
			$rlist[] = array('disk', "{$c['mountedon']}", $c['used'], $c['kblock']);
		}

		// Rlist takes an array...
		$rlist[] = array('memory_usage', "Total Mem:Memory Usage (MB)", $l['used_s_memory'], $l['priv_s_memory']);

		$rlist[] = array('membuffers_usage', "Buffers:Memory Buffers Usage (MB)", $l['used_s_membuffers'] . ' MB', $l['priv_s_memory'] . ' MB');
		$rlist[] = array('memcached_usage', "Cached:Memory Cached Usage (MB)", $l['used_s_memcached'] . ' MB', $l['priv_s_memory'] . ' MB');

		$rlist[] = array('real_usage', "Real Mem:Real Usage (MB)", $l['used_s_realused'] . ' MB', $l['priv_s_memory'] . ' MB');

		if (isset($l['used_s_swap'])) {
			$rlist[] = array('swap_usage', "Swap:Swap Usage (MB)", $l['used_s_swap'], $l['priv_s_swap']);
		}

		if (isset($l['used_s_virtual'])) {
			$rlist[] = array('Virtual Memory', "Virtual:Virtual Memory Usage (MB)", $l['used_s_virtual'], $l['priv_s_virtual']);
		}

		$rlist[] = array('Server Traffic', "Traffic:Server Traffic For Current Month", $this->used->server_traffic_usage, '-');
		$rlist[] = array('Server Traffic', "Last Traffic:Server Traffic For Last Month", $this->used->server_traffic_last_usage, '-');

		$cpu = $l['cpu'];

		foreach ($cpu as $k => $c) {
		//	$rlist[] = array('cpu', "CPU$k Model (speed)",  "{$c['used_s_cpumodel']} ({$c['used_s_cpuspeed']})", '-');
		//	$rlist[] = array('cpu', "CPU$k speed", "{$c['used_s_cpuspeed']}", '-');
			$rlist[] = array('cpu', "CPU$k:CPU processor/core $k", "{$c['used_s_cpuspeed']}", '-');
		}

		$loadavg = sys_getloadavg();

		$rlist[] = array('loadavg', "LoadAvg1:Load Average in 1 minutes", "{$loadavg['0']} %", '100 %');
		$rlist[] = array('loadavg', "LoadAvg5:Load Average in 5 minutes", "{$loadavg['1']} %", '100 %');
		$rlist[] = array('loadavg', "LoadAvg15:Load Average in 15 minutes", "{$loadavg['2']} %", '100 %');
		

		return $rlist;
	}

	function superPostAdd()
	{
		global $gbl, $sgbl, $login, $ghtml;

	/*
		// MR -- it's for hypervm; disabled it

		if (!$sgbl->isHyperVm()) {
			return;
		}

		if ($this->vpstype_f === 'xen') {

			$driver = new Driver(null, $this->nname, $this->nname);
			$driver->get();
			$driver->driver_b->pg_vps = 'xen';
			$driver->setUpdateSubaction();
			$driver->write();

			if ($this->xenlocation) {
				$dirlocation = new Dirlocation(null, $this->nname, $this->nname);
				$dirlocation->dbaction = 'add';

				foreach ($this->xenlocation as $k) {
					$name = "lvm:{$k['nname']}";
					$xenloc[$name] = new xen_location_a(null, $this->nname, $name);
				}

				$dirlocation->parent_clname = $this->getClName();
				$dirlocation->xen_location_a = $xenloc;
				$this->addToList('dirlocation', $dirlocation);
			}
		}
	*/
	}

	function postAdd()
	{
		global $gbl, $sgbl, $login, $ghtml;

		$this->dbaction = 'add';
		$this->findOsDetails();

		$this->username = "root";
/*
		if ($sgbl->isHyperVm()) {
			$rlist = array('vps');
		} else {
			$rlist = array('web', 'mmail', 'dns', 'mysqldb');
		}
*/
		$rlist = array('web', 'mmail', 'dns', 'mysqldb');

		foreach ($rlist as $l) {
			$role = new psrole_a(null, null, $l);
			$this->psrole_a[$l] = $role;
		}

		$this->ddate = time();
		$this->getandWriteModuleDriver();
	//	$this->fixDatabaseServers();
		$this->getandWriteService();

		$this->parent_clname = createParentName('client', 'admin');

		$this->AddMysqlDbadmin();

		// There's a problem here. If the server is added for the second time, the ipaddress would be present,
		// and this would lead to a 'was' happening inside here, which would turn the dbaction to clean and
		// then the actual was wouldn't happen.
		$this->getandwriteipaddress();

		// MR -- no need because execute on fixIpAddrss.php and execatinit.php
		// MR -- fix issue when start/restart kloxo will be delete /opt/configs/<webserver>/conf/domains contents
	//	lxshell_return("sh", "/script/fixweb", "--nolog");

	}

	function AddMysqlDbadmin()
	{
		$dbadmin = new Dbadmin($this->__masterserver, $this->nname, "mysql___{$this->nname}");

		$res['dbtype'] = 'mysql';
		$res['dbadmin_name'] = 'root';
		$res['dbpassword'] = '';
		$res['parent_clname'] = $this->getClName();
		$res['syncserver'] = $this->nname;
		$dbadmin->create($res);

		try {
			$dbadmin->Was();
		} catch (Exception $e) {
		}
	}

	static function add($parent, $class, $param)
	{
		global $login;

		if (!preg_match("/^[A-Za-z.0-9-]*$/", $param['nname'])) {
			throw new lxException($login->getThrow("only_alpha_numeric_characters_allowed"), '', $param['nname']);
		}

		if_not_admin_complain_and_exit();

		$param['nname'] = trim($param['nname']);
		$param['syncserver'] = $param['nname'];
		$param['realpass'] = $param['ps_password'];
		$param['password'] = crypt($param['ps_password']);

		return $param;
	}

	static function isHardRefresh()
	{
		return true;
	}

	static function getExtraParameters($parent, $list)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$gen = $login->getObject('general')->generalmisc_b;

		foreach ($list as $l) {
			try {
				$res = rl_exec_get(null, $l->nname, "getAllOperatingSystemDetails", array());

				if ($l->osversion !== $res['version']) {
					$l->osversion = $res['version'];
					$l->setUpdateSubaction();
				}

				$l->loadavg = $res['loadavg'];
			} catch (Exception $e) {
				$res = null;
				$failedlist[$l->nname] = $e->getMessage();
			}
		}

		if ($failedlist) {
			$emessage = null;

			foreach ($failedlist as $k => $m) {
				$emessage .= "&nbsp; Failed to get Status from $k. Server said: $m <br>  ";
			}

			$ghtml->__http_vars['frm_emessage'] = $emessage;
			$ghtml->__http_vars['frm_m_emessage_data'] = null;
			$ghtml->print_message();
		}
	}

	function findOsDetails()
	{
		global $gbl, $sgbl, $login, $ghtml;

		$rmt = new Remote();
		$rmt->action = 'get';
		$rmt->func = 'findOperatingSystem';
		$rmt->arglist = null;
		$rmt->slave_password = $this->realpass;
		$gbl->pserver_password = $this->realpass;

		$result = rl_exec($this->__masterserver, $this->nname, $rmt);

		if (!$result) {
			throw new lxException($login->getThrow("no_server"), '', $this->nname);
		}

		$this->ostype = $result['os'];
		$this->osversion = $result['version'];

		if (isset($result['vpstype'])) {
			$this->vpstype_f = $result['vpstype'];
			$this->xenlocation = $result['xenlocation'];
		}
	}

	static function AddListForm($parent, $class)
	{
		return self::addform($parent, $class);
	}

	static function addform($parent, $class, $typetd = null)
	{
		$vlist['nname'] = null;
	//	$vlist['description'] = null;
		$vlist['ps_password'] = null;
		$ret['variable'] = $vlist;
		$ret['action'] = 'add';

		return $ret;
	}

	function createExtraVariables()
	{
		if (csb($this->subaction, 'graph_')) {
			$res = vps::findVpsGraph($this->nname, strfrom($this->subaction, "graph_"));
			$this->__var_graph_list = $res;
		}
	}

	function createShowPropertyList(&$alist)
	{
	//	$alist['property'][] = "o=sp_specialplay&a=updateForm&sa=skin";
		$alist['property'][] = 'a=show';
		$alist['property'][] = "a=updateform&sa=information";

		// MR -- look like not work but must
		// because password need slavedb.db file where only exists in slave
		if (!$this->isLocalhost()) {
			$alist['property'][] = "a=updateform&sa=password";
		}

		$alist['property'][] = "a=list&c=psrole_a";
	}

	function createShowAlist(&$alist, $subaction = null)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$alist[] = "a=show";

		$alist['__title_main'] = $login->getKeywordUc('config');

		if ($this->isLocalhost('nname')) {
			$alist[] = "a=show&o=lxupdate";
		}

		$alist['property'][] = "a=updateform&sa=password";
	//	$this->getCPToggleUrl($alist);
		$alist[] = "a=updateform&sa=showused";

	//	$alist[] = "a=list&c=component";

		$cnl = array('ipaddress', 'dbadmin');

		foreach ($cnl as $cn) {
			$alist = $this->getListActions($alist, $cn);
		}

		$alist['__title_next'] = get_plural(get_description('service'));

		$cnl = array('service', 'cron', 'process', 'uuser');

		foreach ($cnl as $cn) {
			$alist = $this->getListActions($alist, $cn);
		}

		$this->driverApp->createShowAlist($alist);

	//	$alist[] = "a=updateform&sa=phpsmtp";
		$alist[] = "a=show&l[class]=ffile&l[nname]=";

	//	$alist[] = "a=list&c=firewall";
	//	$alist[] = "a=show&o=proxy";
	//	$alist[] = "a=updateform&sa=update&c=serverspam";
		$alist['__title_nnn'] = 'Machine';
		$alist[] = "a=show&n=driver";
	//	$alist[] = "a=update&sa=loaddriverinfo";
		$alist[] = "a=updateform&sa=reboot";

		$alist[] = "a=updateform&sa=poweroff";

		return $alist;
	}

	function updatePackage_doupdate($param)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$list = $param['_accountselect'];

		$driverapp = $gbl->getSyncClass($this->__masterserver, $this->__readserver, 'package');

		rl_exec_get($this->__masterserver, $this->__readserver, array("package__$driverapp", 'doUpdate'), array($list));

	}

	function updateMysqlPasswordReset($param)
	{
		global $gbl, $sgbl, $login, $ghtml;

		try {
			$p = $this->getFromList("dbadmin", "mysql___{$this->nname}");
		} catch (Exception $e) {
			$p = new dbadmin(null, null, "mysql___{$this->nname}");
			$p->dbadmin_name = 'root';
			$p->dbtype = 'mysql';
			$p->syncserver = $this->nname;
			$p->parent_clname = $this->getClname();
			$p->dbaction = 'add';
		}

		$p->dbpassword = $param['newpassword_f'];
		$p->setUpdateSubaction();
		$p->write();
		$pass = $p->dbpassword;
		rl_exec_get($this->__masterserver, $this->nname, array("pserver__linux", 'mysqlPasswordReset'), array($pass));
		$ghtml->print_redirect_back_success("Success", "");

	//	exit;
	}

	function updatePoweroff($param)
	{
		global $gbl, $sgbl, $login, $ghtml;

		// --- issue 612 - Hide password in reboot / shutdown server
	/*
		if (!check_password($param['retype_admin_p_f'], $login->password) &&
				!check_password($param['retype_admin_p_f'], $this->password)) {
			throw new lxException($login->getThrow("wrong_password"), '', $param['retype_admin_p_f']);
		}

		return $param;
	*/
		return $login->password;
	}

	function updateReboot($param)
	{
		global $gbl, $sgbl, $login, $ghtml;

		// --- issue 612 - Hide password in reboot / shutdown server
	/*
		if (check_password($param['retype_admin_p_f'], $login->password) ||
				check_password($param['retype_admin_p_f'], $this->password)) {
			return $param;
		} else {
			throw new lxException($login->getThrow("wrong_password"), '', $param['retype_admin_p_f']);
		}
	*/
		return $login->password;
	}

	function getOs()
	{
		// Sending the password here too. Since this is also called in the initial stage.

		global $gbl, $sgbl, $login, $ghtml;

		$rmt = new Remote();
		$rmt->action = 'get';
		$rmt->func = 'findOperatingSystem';
		$rmt->arglist = null;
		$rmt->slave_password = $this->realpass;
		$gbl->pserver_password = $this->realpass;

		$ret = rl_exec($this->__masterserver, $this->nname, $rmt);

		return $ret['os'];
	}

	function addToDriverObject($ob, $driver)
	{
		foreach ($driver as $k => $v) {
			if (is_array($v)) {
				$v = $v[0];
			}

			$var = "pg_" . $k;

			/// Ad only new drivers... very important.
			if (!(isset($ob->driver_b->$var) && $ob->driver_b->$var)) {
			//	dprint("Adding New Driver $var: $v <br> \n");
				$ob->driver_b->$var = $v;
			}
		}
	}

	function createDriver()
	{
		$ob = new Driver($this->__masterserver, null, $this->nname);
		$ob->get();

		$os = $this->ostype;
	//	include "../file/driver/$os.inc";
		include "../file/driver/rhel.inc";

		if (!$driver) {
			print("Error Reading Driver Config File...\n");

			exit;
		}

		$olddriver_b = $ob->driver_b;
		$ob->driver_b = new Driver_b(null, null, $this->nname);

		foreach ($driver as $k => $v) {
			if (is_array($v)) {
				$v = $v[0];
			}

			$var = "pg_" . $k;

			if (isset($olddriver_b->$var)) {
				$ob->driver_b->$var = $olddriver_b->$var;
			}
		}

		$this->addToDriverObject($ob, $driver);

		$list = module::getModuleList();

		$driver = null;

		foreach ((array)$list as $l) {
		//	$mod = getreal("/module/") . "/$l";
		//	include_once "$mod/lib/driver.inc";

			$dlist = $driver[$os];

			if (isset($driver['all'])) {
				$dlist = lx_array_merge(array($dlist, $driver['all']));
			}

			$this->addToDriverObject($ob, $dlist);
		}

		if ($ob->dbaction === 'clean') {
			$ob->dbaction = 'update';
		}
		$ob->parent_clname = $this->getClName();

		$ob->write();
	}

	function getandWriteModuleDriver()
	{
		$this->createDriver();
	}

	function fixDatabaseServers()
	{
		$db = new Sqlite($this->__masterserver, "pserver");

		$list = $db->getTable(array('nname'));

		foreach ($list as $l) {
			if ($this->isDeleted() && $l['nname'] === $this->nname) {
				continue;
			}
			$nlist[] = $l['nname'];
		}


		if ($this->dbaction === 'add') {
			$nlist[] = $this->nname;
		}

		sort($nlist);

		rl_exec_get($this->__masterserver, null, array('pserver', 'localfixDatabaseServers'), array($nlist));
	}

	static function localfixDatabaseServers($list)
	{

		$string = <<<STRIN

\$i++;
\$cfg['Servers'][\$i]['host']            = '__hostname__';
\$cfg['Servers'][\$i]['port']            = '';
\$cfg['Servers'][\$i]['socket']          = '';
\$cfg['Servers'][\$i]['connect_type']    = 'tcp';
\$cfg['Servers'][\$i]['extension']       = 'mysql';
\$cfg['Servers'][\$i]['compress']        = FALSE;
\$cfg['Servers'][\$i]['controluser']     = '';
\$cfg['Servers'][\$i]['controlpass']     = '';
\$cfg['Servers'][\$i]['auth_type']       = 'cookie';
\$cfg['Servers'][\$i]['user']            = '';
\$cfg['Servers'][\$i]['password']        = '';
\$cfg['Servers'][\$i]['only_db']         = '';
\$cfg['Servers'][\$i]['verbose']         = '';
\$cfg['Servers'][\$i]['pmadb']           = ''; // 'phpmyadmin' - see scripts/create_tables.sql
\$cfg['Servers'][\$i]['bookmarktable']   = ''; // 'pma_bookmark'
\$cfg['Servers'][\$i]['relation']        = ''; // 'pma_relation'
\$cfg['Servers'][\$i]['table_info']      = ''; // 'pma_table_info'
\$cfg['Servers'][\$i]['table_coords']    = ''; // 'pma_table_coords'
\$cfg['Servers'][\$i]['pdf_pages']       = ''; // 'pma_pdf_pages'
\$cfg['Servers'][\$i]['column_info']     = ''; // 'pma_column_info'
\$cfg['Servers'][\$i]['history']         = ''; // 'pma_history'
\$cfg['Servers'][\$i]['verbose_check']   = TRUE;
\$cfg['Servers'][\$i]['AllowRoot']       = TRUE;

\$cfg['Servers'][\$i]['AllowDeny']['order'] = '';
\$cfg['Servers'][\$i]['AllowDeny']['rules'] = array();

STRIN;

		$fstring = "<?php\n\n\$i = 0;\n";

		foreach ($list as $l) {
			$nstring = str_replace("__hostname__", $l, $string);
			$fstring .= $nstring;
		}

		$fstring .= <<<STRIN

	\$cfg['ServerDefault'] = 1;              // Default server (0 = no default server)
	\$cfg['Server']        = '';
	unset(\$cfg['Servers'][0]);

STRIN;

		$fstring .= "\n\n";

		lxfile_mkdir("__path_program_etc/thirdparty/");
		lfile_put_contents("__path_program_etc/thirdparty/phpmyadmin_servers.inc", $fstring);

	}

	function getandWriteService()
	{
		global $gbl, $sgbl, $login, $ghtml;

		$srvlist = $this->getList("service");

		if ($srvlist) {
			foreach ($srvlist as $srv) {
				$srv->delete();
				$srv->metadbaction = "writeonly";
			}

			$this->was();
		}

		$list = service__Linux::getMainServiceList();

		foreach ((array)$list as $l => $g) {
			$nname = $l . "___" . $this->nname;
			$ob = new Service($this->__masterserver, $this->__readserver, $nname);
			$res['syncserver'] = $this->nname;
			$res['servicename'] = $l;
			$res['grepstring'] = $g;
			$res['status'] = 'on';
			$res['parent_clname'] = $this->getClName();

			if (isset($sgbl->__var_service_desc[$l])) {
				$res['description'] = $sgbl->__var_service_desc[$l];
			} else {
				$res['description'] = "";
			}

			$ob->create($res);
			$this->addToList('service', $ob);
		}

	//	$this->was();
	}

	function update($subaction, $param)
	{
		global $login;

		$parent = $this->getParentO();

		if (!$parent->isAdmin() && $this->clientname !== $parent->nname) {
			throw new lxException($login->getThrow("no_permission"), '', $parent->nname);
		}

		return $param;
	}

	function commondbActionUpdate($subaction)
	{
	}

	function getandwriteipaddress()
	{
		global $gbl, $sgbl, $login, $ghtml;

		dprint($this->nname);

		$rmt = new Remote();

		$driverapp = $gbl->getSyncClass($this->__masterserver, $this->nname, 'ipaddress');

		if (!$driverapp) {
			print("NO driverapp for ipaddress\n");

		//	exit;
			return;
		}

		$rmt->func = array("Ipaddress__$driverapp", "listSystemIps");
		$rmt->arglist = array($this->nname);
		$rmt->action = 'get';
		$rmt->slave_password = $this->realpass;
		$gbl->pserver_password = $this->realpass;

		$result = rl_exec(null, $this->nname, $rmt);

		if (!$result) {
			dprint("No Result <br> <br> \n\n\n");
		}

	//	dprintr($result);

		// hack hack hack
		// Direclty call 'was' to sync the pserver. This is needed because this is an out of the way Action,
		// to remove the ipaddress from the database before the newly got ones are added in.

		$iplist = $this->getList("ipaddress");

		if ($iplist) {
			foreach ($iplist as $ip) {
				$exclusive_client[$ip->nname] = $ip->clientname;
				$ip->dbaction = 'delete';
				$ip->write();
				$ip->dbaction = 'clean';
			}

		//	$this->was();
		}

		foreach ($result as $row) {
			if (!trim($row['ipaddr'])) {
				continue;
			}

			$row['nname'] = $row['devname'] . "___" . $this->syncserver;
			$row['syncserver'] = $this->syncserver;
			$row['parent_clname'] = $this->getClName();

			if (isset($exclusive_client[$row['nname']])) {
				$row['clientname'] = $exclusive_client[$row['nname']];
			}

			$obj = New Ipaddress($this->__masterserver, $this->syncserver, $row['nname']);
			$obj->create($row);
			$obj->write();
			$obj->dbaction = 'clean';
		}
	}

	function fixInitialSsl($obj)
	{
		$sslipaddr = new SslIpaddress($this->__masterserver, $this->syncserver, $row['nname']);
		$sslipaddr->get();

		if (!$sslipaddr->sslcert) {
			$sslipaddr->sslcert = "default___{$obj->devname}";
		}

		$sslipaddr->parent_clname = $obj->getClName();
		$sslipaddr->write();
		$sslcert = new SslCert($this->__masterserver, $this->syncserver, "default___{$obj->nname}");

		$sslcert->get();
		$sslcert->upload_status = 'on';
		$sslcert->certname = "default___{$obj->devname}";
		$sslcert->parent_clname = $sslipaddr->getClName();
		$sslcert->write();
	}

	function updateReadIpAddress($param)
	{
		$this->getandwriteipaddress();

		return null;
	}

	function updateLoadDriverInfo($param)
	{
		$this->getAndWriteModuleDriver();

		return null;
	}

	function updateform($subaction, $param)
	{

		global $gbl, $sgbl, $login, $ghtml;

		switch ($subaction) {
			case "centralbackupconfig":
				$list = $login->getList('centralbackupserver');
				$list = get_namelist_from_objectlist($list);
				$vlist['centralbackupserver'] = array('s', add_disabled($list));
				$vlist['internalnetworkip'] = null;
				$vlist['tmpdir'] = null;
			//	$vlist['__v_updateall_button'] = array();

				return $vlist;

			case "commandcenter":
				return $this->commandCenter($param);
				break;

			case "timezone":
				$vlist['timezone'] = array('s', pserver::getTimeZoneList());

				return $vlist;

			case "ssl_key":

				$this->createPublicPrivate();
				$this->setUpdateSubaction();
				$vlist['text_public_key'] = array('t', null);

				return $vlist;


			case "switchprogram":
				// MR -- 'nname' = syncserver in here (because pserver class)!
				// check if slavedb/driver file first and if empty then check driver table
				$this->web_driver = rl_exec_get('localhost', $this->syncserver, 'slave_get_driver', array('web'));
				$this->webcache_driver = rl_exec_get('localhost', $this->syncserver, 'slave_get_driver', array('webcache'));
				$this->dns_driver = rl_exec_get('localhost', $this->syncserver, 'slave_get_driver', array('dns'));
				$this->spam_driver = rl_exec_get('localhost', $this->syncserver, 'slave_get_driver', array('spam'));

				if (!isset($this->web_driver)) {
					$this->web_driver = $gbl->getSyncClass($this->__masterserver, $this->syncserver, 'web');
					$this->webcache_driver = $gbl->getSyncClass($this->__masterserver, $this->syncserver, 'webcache');
					$this->dns_driver = $gbl->getSyncClass($this->__masterserver, $this->syncserver, 'dns');
					$this->spam_driver = $gbl->getSyncClass($this->__masterserver, $this->syncserver, 'spam');
				}

				$this->was();

				// MR -- always off because one-time process
				$this->no_fix_config = 'off';

			/*
				$vlist['web_driver'] = array('s', array('none', 'apache', 'lighttpd', 'nginx', 
					'hiawatha', 'openlitespeed', 'monkey',
					'lighttpdproxy', 'nginxproxy', 'hiawathaproxy', 'openlitespeedproxy',
					'monkeyproxy'));
			*/
				$vlist['web_driver'] = array('s', array('none',
					'apache', 'lighttpd', 'nginx', 'hiawatha',
					'lighttpdproxy', 'nginxproxy', 'hiawathaproxy'));

				// MR -- get httpd24u info
				exec("cat '../etc/list/httpd.lst'|grep httpd24", $out);

				if ($out[0] !== null) {
					if (file_exists("../etc/flag/use_apache24.flg")) {
						$this->use_apache24 = 'on';
					} else {
						$this->use_apache24 = 'off';
					}

					$vlist['use_apache24'] = array('f', 'on', 'off');
				}

				$vlist['webcache_driver'] = array('s', array('none', 'squid', 'trafficserver', 'varnish'));

			//	$vlist['dns_driver'] = array('s', array('none', 'bind', 'djbdns', 'maradns', 'nsd', 'pdns'));
				$vlist['dns_driver'] = array('s', array('none', 'bind', 'djbdns', 'nsd', 'pdns', 'mydns', 'yadifa'));

				$vlist['spam_driver'] = array('s', array('none', 'spamassassin', 'bogofilter'));

				// MR -- no needed under Kloxo-MR 7.0 because fix 'defaults' level
			//	$vlist['no_fix_config'] = array('f', 'on', 'off');

				$vlist['__v_updateall_button'] = array();

				return $vlist;

			case "mysqlpasswordreset":
				$vlist['newpassword_f'] = null;

				return $vlist;

			case "importvps":
				$vlist['importvps'] = array('M', 'Import Vpses on this server?');
				$vlist['__v_button'] = "Import";

				return $vlist;

			case "importhypervmvps":
				$vlist['importvps'] = array('M', 'Import HyperVM Vpses on this server?');
				$vlist['__v_button'] = "Import";

				return $vlist;

			case "savevpsdata":
				$vlist['__v_button'] = "Save";

				return $vlist;

			case "information":
				$sq = new Sqlite(null, 'client');
				$res = $sq->getRowsWhere("cttype = 'wholesale'", null, array('nname'));
				$clientlist = get_namelist_from_arraylist($res);

				$vlist['description'] = null;
				$vlist['realhostname'] = null;

				if ($sgbl->isHyperVm()) {
					$list = get_namelist_from_objectlist($login->getList('datacenter'));

					if (!$list) {
						$list[] = '--no-dc--';
						$this->datacenter = '--no-dc--';
					}

					$vlist['datacenter'] = array('s', $list);
					$newclientlist = lx_array_merge(array(array('--unassigned--'), $clientlist));

					if ($this->nname === 'localhost') {
						$vlist['clientname'] = array('M', $login->getKeyword('master_cannot_be_assigned'));
					} else {
						$vlist['clientname'] = array('s', $newclientlist);
					}
				}

				if ($sgbl->isHyperVm()) {
					$vlist['max_vps_num'] = null;
				}

				$this->setDefaultValue("load_threshold", "20");
				$vlist['load_threshold'] = null;

				return $vlist;

			case "backupconfig":
				return $vlist;

			case "phpsmtp":
				$vlist['pserverconf_b_s_usesmtp'] = null;
				$vlist['pserverconf_b_s_smtpserver'] = null;
				$vlist['pserverconf_b_s_smtpport'] = null;
				$vlist['pserverconf_b_s_smtpuseauth'] = null;
				$vlist['pserverconf_b_s_smtpuser'] = null;
				$vlist['pserverconf_b_s_smtppass'] = null;

				return $vlist;

			case "cron_mailto":
				$vlist['cron_mailto'] = null;

				return $vlist;

			case "vpslist":
				$vlist['used_vpslist_f'] = array('M', $this->getUsed());
				$vlist['__v_button'] = array();

				return $vlist;

			case "showused":
				$res = $this->createUsedDomainList();

				foreach ($res as $k => $v) {
					$var = "used_domainlist_{$k}_f";
					$vlist[$var] = array('M', $this->$var);
				}

				$vlist['__v_button'] = array();

				return $vlist;

			case "update":
				$vlist['nname'] = array('M', null);
				$vlist['password'] = null;

				return $vlist;

			case "poweroff" :
				// --- issue 612 - Hide password in reboot / shutdown server
			//	$vlist['retype_admin_p_f'] = null;
				$vlist['__v_button'] = 'Poweroff';

				return $vlist;

			case "reboot":
				// --- issue 612 - Hide password in reboot / shutdown server
			//	$vlist['retype_admin_p_f'] = null;
				$vlist['__v_button'] = 'Reboot';
				return $vlist;

			case "dbpassword":
				$vlist['dbadmin'] = null;
				$vlist['dbpassword'] = null;

				return $vlist;
		}

		return parent::updateform($subaction, $param);
	}

	function createShowMainImageList()
	{
		$vlist['ostype'] = 1;

		return $vlist;
	}

	function isSelect()
	{
		if ($this->nname === "localhost") {
			return false;
		}

		if (isOn($this->createUsed())) {
			return false;
		}

		return $this->getParentO()->isAdmin();
	}

	static function createListBlist($parent, $class)
	{
		$blist = null;

		if ($parent->isAdmin()) {
			$blist[] = array("a=delete&c=$class");
		}

		return $blist;
	}

	function getFfileFromVirtualList($name)
	{
		$name = coreFfile::getRealpath($name);
		$name = '/' . $name;
		$ffile = new Ffile($this->__masterserver, $this->__readserver, "__path_root_base", $name, $this->username);
		$ffile->__parent_o = $this;
		$ffile->get();

		return $ffile;
	}

	function deleteSpecific()
	{
		$sq = new Sqlite(null, 'ipaddress');
		$sq->rawQuery("delete from ipaddress where syncserver = '$this->nname'");

	//	$this->fixDatabaseServers();
	}

	static function initThisListRule($parent, $class)
	{
		if ($parent->isAdmin()) {
			$res = "__v_table";
		} else {
		//	if ($parent->is__table('datacenter')) {
			if ($parent->getClass() === 'datacenter') {
				$res[] = array('datacenter', '=', "'$parent->nname'");
			} else {
				$res[] = array("clientname", '=', "'$parent->nname'");
			}
		}

		return $res;
	}

	function syncPasswordCommon()
	{
		global $gbl, $sgbl, $login, $ghtml;

		$login->password = $this->password;

		if (!lfile_exists('__path_slave_db')) {
			return;
		}

		$rmt = unserialize(lfile_get_contents('__path_slave_db'));
		$rmt->password = $this->password;
	//	$rmt->realpass = $this->realpass;

		lfile_put_contents('__path_slave_db', serialize($rmt));
	}
}

