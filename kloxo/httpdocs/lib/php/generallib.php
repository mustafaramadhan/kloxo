<?php

class helpdeskcategory_a extends Lxaclass
{
	static $__desc = array("", "", "helpdesk_category");
	static $__desc_nname = array("", "", "category");

	static function createListAlist($parent, $class)
	{
		$nalist = ticket::createListAlist($parent, 'ticket');

		foreach ($nalist as $a) {
			$alist[] = "goback=1&$a";
		}

		return $alist;

	}

	static function createListAddForm($parent, $class)
	{
		return true;
	}
}

class browsebackup_b extends lxaclass
{
	static $__desc_browsebackup_flag = array("f", "", "enable_browse_backup");
	static $__desc_backupslave = array("", "", "backup_slave");
	static $__desc_rootdir = array("", "", "rootdir");
}

class selfbackupparam_b extends lxaclass
{
	static $__desc_selfbackupflag = array("f", "", "do_remote_backup");
	static $__desc_ftp_server = array("n", "", "ftp_server");
	static $__desc_ssh_server = array("n", "", "ssh_server");
	static $__desc_rm_username = array("n", "", "username");
	static $__desc_rm_directory = array("", "", "directory");
	static $__desc_rm_password = array("n", "", "password");
	static $__desc_rm_last_number = array("", "", "keep_this_many_backups_on_the_server");
}

class portconfig_b extends lxaclass
{
	static $__desc_sslport = array("", "", "ssl_port");
	static $__desc_nonsslport = array("", "", "plain_port");
	static $__desc_nonsslportdisable_flag = array("f", "", "disable_plainport");
	static $__desc_redirectnonssl_flag = array("f", "", "redirect_non_ssl_to_ssl");
	static $__desc_redirecttodomain = array("", "", "redirect_to_domain");
}

class kloxoconfig_b extends lxaclass
{
	static $__desc_remoteinstall_flag = array("f", "", "host_easyinstaller_remotely");
	static $__desc_easyinstaller_url = array("", "", "Url_for_remote_easyinstaller");
}

class lxadminconfig_b extends lxaclass
{
	static $__desc_remoteinstall_flag = array("f", "", "host_easyinstaller_remotely");
	static $__desc_easyinstaller_url = array("", "", "Url_for_remote_easyinstaller");
}

class customaction_b extends lxaclass
{
	static $__desc_vps__update__rebuild = array('', '', "rebuild_vps", "");
}

class hackbuttonconfig_b extends lxaclass
{
	static $__desc_nomonitor = array('f', '', 'dont_show_monitor_server', '');
	static $__desc_nobackup = array('f', '', 'dont_show_backup', '');
}

class reversednsconf_b extends lxaclass
{
}

class reversedns_b extends lxaclass
{
	static $__desc_enableflag = array("f", "", "enable_reverse_dns");
	static $__desc_forwardenableflag = array("f", "", "enable_forward_dns");
	static $__desc_primarydns = array("n", "", "primary_dns");
	static $__desc_secondarydns = array("", "", "secondary_dns");
	static $__desc_dns_slave_list = array("", "", "slaves_the_dns_entries_are_synced_on");

}

class generalmisc_b extends Lxaclass
{
	static $__desc_attempts = array("", "", "no_of_attempts");
	static $__desc_loginhistory_time = array("", "", "clear_login_history_after_this_many_months.");
	static $__desc_traffichistory_time = array("", "", "clear_traffic_history_after_this_many_months.");
	static $__desc_security = array("", "", "security_policy");
	static $__desc_multi = array("f", "", "multiple_servers");
	static $__desc_npercentage = array("", "", "notify_policy");
	static $__desc_extrabasedir = array("", "", "extra basedir");
	static $__desc_disableipcheck = array("f", "", "disable_ip_check");
	static $__desc_usenmapforping = array("f", "", "use_nmap_for_ping");
	static $__desc_masterdownload = array("f", "", "download_via_master");
	static $__desc_disable_hostname_change = array("f", "", "disable_vps_owners_ability_to_change_hostname");
	static $__desc_no_console_user = array("f", "", "dont_show_console_user");
	static $__desc_sshport = array("", "", "ssh_port");
	static $__desc_installkloxo = array("f", "", "show_install_kloxo_button");
	static $__desc_webstatisticsprogram = array("", "", "web_statistics_program");
	static $__desc_initialopenvzid = array("", "", "initial_openvz_id");
	static $__desc_helpurl = array("", "", "help_url");
	static $__desc_openvzincrement = array("", "", "openvz_increment");
	static $__desc_maintenance_flag = array("f", "", "system_under_maintenance");
	static $__desc_xenimportdriver = array("", "", "xen_import_driver");
	static $__desc_webmail_system_default = array("", "", "webmail_system_default");
	static $__desc_disableeasyinstaller = array("f", "", "disable_easyinstaller");
	static $__desc_htmltitle = array("", "", "html_title");
	static $__desc_xeninitrd_flag = array("f", "", "xen_initrd_flag");
	static $__desc_dont_get_live_status = array("f", "", "dont_get_vps_live_status");
	static $__desc_autoupdate = array("f", "", "auto_update");
	static $__desc_rebuild_time_limit = array("", "", "rebuild_limit_time(minutes)");
	static $__desc_forumurl = array("", "", "community_url");
	static $__desc_ticket_url = array("", "", "helpdesk_url");
	static $__desc_message_url = array("", "", "message_url");
	static $__desc_scavengehour = array("", "", "Hour_to_run_scavenge");
	static $__desc_scavengeminute = array("", "", "Minute");
	static $__desc_dpercentage = array("s", "", "disable_percentage");
	static $__desc_dpercentage_v_110 = array("s", "", "disable_percentage");

	static $__desc_sendmailflag = array("f", "", "scavenge_sendmail");
}

class General extends Lxdb
{

//Core

//Data
	static $__desc = array("", "", "general");
	static $__desc_nname = array("", "", "general");
	static $__desc_login_pre = array("t", "", "login_message");
	static $__desc_generalmisc_b = array("", "", "general");
	static $__desc_text_maintenance_message = array("", "", "Message");

	static $__desc_enable_cronforall = array("", "", "enable_cronforall");

	static $__acdesc_update_multi = array("", "", "multiple_servers");
	static $__acdesc_update_ssh_config = array("", "", "ssh_config");
	static $__acdesc_update_npercentage = array("", "", "notify_policy");
	static $__acdesc_update_disableper = array("", "", "disable_policy");
	static $__acdesc_update_attempts = array("", "", "security_policy");
	static $__acdesc_update_historytime = array("", "", "history_clearing_policy");
	static $__acdesc_update_scavengetime = array("", "", "scavenge_time");
	static $__acdesc_update_generalsetting = array("", "", "General Settings");
	static $__acdesc_update_maintenance = array("", "", "system_under_maintenance");
	static $__acdesc_update_reversedns = array("", "", "dns_config");
	static $__acdesc_update_kloxo_config = array("", "", "kloxo_config");
	static $__acdesc_update_selfbackupconfig = array("", "", "config_self_backup");
	static $__acdesc_update_hackbuttonconfig = array("", "", "config_buttons");
	static $__acdesc_update_customaction = array("", "", "deprecated");
	static $__acdesc_update_session_config = array("", "", "session_config");
	static $__acdesc_update_download_config = array("", "", "download_config");
	static $__acdesc_update_portconfig = array("", "", "port_config");
	static $__acdesc_update_browsebackup = array("", "", "browse_backup_config");

	static $__acdesc_show = array("", "", "Configuration");
	static $__desc_dns_slave_list = array("", "", "slaves_the_dns_entries_are_synced_on");

//Lists

	function createExtraVariables()
	{
		global $gbl, $sgbl, $login, $ghtml;

		if (!$login->isAdmin() && $this->dbaction !== 'clean') {
			throw new lxException($login->getThrow('only_admin_can_modify_general'));
		}
	}

	function updateMaintenance($param)
	{
		global $login;

		return $param;
	}

	function isSync()
	{
		return false;
	}

	function updateScavengeTime($param)
	{
		global $login;

		$ret = lfile_put_contents("../etc/conf/scavenge_time.conf", "{$param['generalmisc_b-scavengehour']} {$param['generalmisc_b-scavengeminute']}");
		
		if (!$ret) {
			exec_with_all_closed("sh /script/load-wrapper >/dev/null 2>&1 &");
			throw new lxException($login->getThrow("could_not_save_file"), '', '../etc/conf/scavenge_time.conf');
		}

		$f = "../etc/flag/enablescavengesendmail.flg";

		if ($param['generalmisc_b-sendmailflag'] === 'on') {
			touch($f);
		} else {
			if (file_exists($f)) {
				unlink($f);
			}
		}

		return $param;
	}

	function createShowPropertyList(&$alist)
	{
		global $gbl, $sgbl, $login, $ghtml;

		if ($ghtml->frm_subaction === 'browsebackup') {
			$alist['property'][] = 'goback=1&a=list&c=centralbackupserver';
			$alist['property'][] = 'goback=1&a=addform&c=centralbackupserver';
			$alist['property'][] = 'a=updateform&sa=browsebackup';
		}
	/*
		if ($ghtml->frm_subaction === 'reversedns') {
			$alist['property'][] = 'goback=1&a=list&c=reversedns';
			$alist['property'][] = 'a=updateform&sa=reversedns';

			if ($sgbl->isHyperVM()) {
				$alist['property'][] = 'goback=1&a=list&c=all_dns';
				$alist['property'][] = 'goback=1&a=list&c=all_reversedns';
			}
		}
	*/
		return $alist;
	}

	// MR -- still not work!
	static function createListAlist($parent, $class)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$alist[] = "o=general&a=updateform&sa=portconfig";
		$alist[] = "o=general&a=updateform&sa=scavengetime";

		return $alist;
	}

	function updateselfbackupconfig($param)
	{
		global $login;

		if (isOn($param['selfbackupparam_b-selfbackupflag'])) {
			$fn = lxftp_connect($param['selfbackupparam_b-ftp_server']);
			$mylogin = ftp_login($fn, $param['selfbackupparam_b-rm_username'], $param['selfbackupparam_b-rm_password']);

			if (!$mylogin) {
				$p = error_get_last();
				throw new lxException($login->getThrow('could_not_connect_to_ftp_server'), '', $p);
			}
		}
		return $param;
	}

	function updatePortConfig($param)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$sslport = $param['portconfig_b-sslport'];
		$nonsslport = $param['portconfig_b-nonsslport'];

		exec("echo '$sslport' > /home/kloxo/httpd/cp/.ssl.port");
		exec("echo '$nonsslport' > /home/kloxo/httpd/cp/.nonssl.port");

		$loginpath = "../httpdocs/login";

		if ($param['portconfig_b-redirectnonssl_flag'] === 'on') {
			touch("{$loginpath}/redirect-to-ssl");
		} else {
			unlink("{$loginpath}/redirect-to-ssl");
		}

		if ($param['portconfig_b-redirecttodomain'] !== '') {
			$dom = $param['portconfig_b-redirecttodomain'];

			validate_domain_name($dom);

			exec("echo '$dom' > {$loginpath}/redirect-to-domain");
		} else {
			exec("rm -f {$loginpath}/redirect-to-domain");
		}

		return $param;
	}

	function postUpdate()
	{
		// We need to write because reads everything from the database.
		$this->write();

		if ($this->subaction === 'generalsetting') {
			exec("sh /script/fixphp --server=all; sh /script/fixweb --server=all");

			$this->generalmisc_b->disableeasyinstaller = 'on';

			touch("../etc/flag/disableeasyinstaller.flg");
		}
	}

	function updateGeneralsetting($param)
	{
		$f = "../etc/flag/enablecronforall.flg";

		if ($param['enable_cronforall'] === 'on') {
			touch($f);
		} else {
			if (file_exists($f)) {
				unlink($f);
			}
		}

		return $param;
	}

	function updateform($subaction, $param)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$progname = $sgbl->__var_program_name;

		switch ($subaction) {
			case "multi" :
				$vlist['multi'] = null;

				break;

			case "browsebackup":
				$vlist['browsebackup_b-browsebackup_flag'] = null;
				//$vlist['browsebackup_b-backupslave'] = array('s', get_all_pserver());
				//$vlist['browsebackup_b-rootdir'] = null;

				break;

			case "historytime":
				$vlist['generalmisc_b-traffichistory_time'] = null;
				$vlist['generalmisc_b-loginhistory_time'] = null;

				break;

			case "disableper" :
				$vlist['generalmisc_b-dpercentage'] = array('s', array('90', '95', '100', '105', '110', '120'));

				break;

			case "npercentage" :
				$vlist['generalmisc_b-npercentage'] = null;

				break;

			case "ssh_config":
				$vlist['generalmisc_b-sshport'] = null;

				break;

			case "kloxo_config":
				$vlist['kloxoconfig_b-remoteinstall_flag'] = null;
				$vlist['kloxoconfig_b-easyinstaller_url'] = null;

				break;


			case "portconfig":
				$this->portconfig_b->setDefaultValue('sslport', $sgbl->__var_prog_ssl_port);
				$this->portconfig_b->setDefaultValue('nonsslport', $sgbl->__var_prog_port);
				$vlist['portconfig_b-sslport'] = null;
				$vlist['portconfig_b-nonsslport'] = null;
			//	$vlist['portconfig_b-nonsslportdisable_flag'] = null;
				$vlist['portconfig_b-redirectnonssl_flag'] = null;
				$vlist['portconfig_b-redirecttodomain'] = null;

				break;

			case "download_config":
				$vlist['generalmisc_b-masterdownload'] = null;

				break;

			case "attempts" :
				$vlist['generalmisc_b-attempts'] = null;

				break;

			case "maintenance":
				$vlist['generalmisc_b-maintenance_flag'] = null;
				$vlist['text_maintenance_message'] = array('t', null);

				break;

			case "generalsetting":
				$vlist['generalmisc_b-autoupdate'] = null;

				if ($sgbl->isHyperVM()) {
					if (!isset($this->generalmisc_b->installkloxo)) {
						$this->generalmisc_b->installkloxo = 'on';
					}

					$vlist['generalmisc_b-installkloxo'] = null;
					$vlist['generalmisc_b-openvzincrement'] = null;
					$vlist['generalmisc_b-xenimportdriver'] = null;
					$vlist['generalmisc_b-rebuild_time_limit'] = null;
					$vlist['generalmisc_b-no_console_user'] = null;
					$vlist['generalmisc_b-disable_hostname_change'] = null;
				}

				if ($sgbl->isKloxo()) {
					$vlist['generalmisc_b-extrabasedir'] = null;
					$list = array("awstats", "webalizer");
					$list = add_disabled($list);
					$this->generalmisc_b->setDefaultValue('webstatisticsprogram', 'awstats');
					$vlist['generalmisc_b-webstatisticsprogram'] = array('s', $list);

					$this->generalmisc_b->disableeasyinstaller = 'on';
					touch("../etc/flag/disableeasyinstaller.flg");
				//	$vlist['generalmisc_b-disableeasyinstaller'] = 'on';

					$list = lx_merge_good('--chooser--', mmail::getWebmailProgList());
					$this->generalmisc_b->setDefaultValue('webmail_system_default', '--chooser--');
					$vlist['generalmisc_b-webmail_system_default'] = array('s', $list);
				}

				$vlist['generalmisc_b-htmltitle'] = null;
				$vlist['generalmisc_b-ticket_url'] = null;
				$vlist['login_pre'] = null;

				$this->enable_cronforall = null;

				$vlist['enable_cronforall'] = array('f', array('on', 'off'));

				if (file_exists("../etc/flag/enablecronforall.flg")) {
					$this->setDefaultValue('enable_cronforall', 'on');
				}

				break;

			case "hostdiscovery":
				$vlist['generalmisc_b-usenmapforping'] = null;

				break;

			case "reversedns":
				if (!$this->reversedns_b) {
					$this->reversedns_b = new reversedns_b(null, null, 'general');
				}

				$vlist['reversedns_b-enableflag'] = (isset($this->reversedns_b->enableflag)) ?
					array('', '', $this->reversedns_b->enableflag) : null;

			//	$vlist['reversedns_b-forwardenableflag'] =(isset($this->reversedns_b->forwardenableflag)) ?
			//		array('', '', $this->reversedns_b->forwardenableflag) : null;

				$this->dns_slave_list = (isset($this->reversedns_b->dns_slave_list)) ?
					$this->reversedns_b->dns_slave_list : null;

				$vlist['reversedns_b-primarydns'] = (isset($this->reversedns_b->primarydns)) ?
					array('', '', $this->reversedns_b->primarydns) : null;

				$vlist['reversedns_b-secondarydns'] = (isset($this->reversedns_b->secondarydns)) ?
					array('', '', $this->reversedns_b->secondarydns) : null;

				$serverlist = get_namelist_from_objectlist($login->getRealPserverList('dns'));
				$vlist['dns_slave_list'] = array('U', $serverlist);

				break;

			case "scavengetime":
				$tcron = new Cron(null, null, 'test');
				$v = cron::$hourlist;
				unset($v[0]);
				$vlist['generalmisc_b-scavengehour'] = array('s', $v);
				$vlist['generalmisc_b-scavengeminute'] = array('s', array("0", "15", "30", "45"));
				$vlist['generalmisc_b-sendmailflag'] = null;

				break;

			case "selfbackupconfig":
				$vlist['selfbackupparam_b-selfbackupflag'] = null;
				$vlist['selfbackupparam_b-ftp_server'] = null;
				$vlist['selfbackupparam_b-rm_directory'] = null;
				$vlist['selfbackupparam_b-rm_username'] = null;
				$vlist['selfbackupparam_b-rm_password'] = array('m', '***');
			//	$vlist['selfbackupparam_b-rm_last_number'] = null;

				break;

			case "hackbuttonconfig":
				$vlist['hackbuttonconfig_b-nobackup'] = null;
				$vlist['hackbuttonconfig_b-nomonitor'] = null;

				break;

			case "session_config":
				$vlist['generalmisc_b-disableipcheck'] = null;

				break;

			case "customaction":
				$vlist['customaction_b-vps__update__rebuild'] = null;

				break;
		}

		return $vlist;
	}

	function updateReversedns($param)
	{
		$param['reversedns_b-dns_slave_list'] = explode(",", $param['dns_slave_list']);

		return $param;
	}

	function createShowAlist(&$alist, $subaction = null)
	{
		// MR --- process sync before enter to page -- related to easyinstaller issue
		if (lxfile_exists("../etc/flag/disableeasyinstaller.flg")) {
			$this->generalmisc_b->disableeasyinstaller = 'on';
		} else {
			$this->generalmisc_b->disableeasyinstaller = 'off';
		}
	}

	static function initThisObjectRule($parent, $class, $name = null)
	{
		return 'admin';
	}
}
