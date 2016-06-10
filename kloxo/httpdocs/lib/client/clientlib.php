<?php

class Client extends ClientBase
{
	static $__table = "client";
	static $__desc_mysqldb_l = array("qdB", "", "");
	static $__desc_domain_l = array("RqdtB", "", "");
	static $__desc_domaina_l = array("", "", "");
	static $__desc_maindomain_l = array("", "", "");
	static $__desc_auxiliary_l = array("db", "", "");
	static $__desc_subdomain_l = array("", "", "");
	static $__desc_dnstemplate_l = array("b", "", "");
	static $__desc_all_domaina_l = array("", "", "");
	static $__desc_all_domain_l = array("", "", "");
	static $__desc_all_addondomain_l = array("", "", "");
	static $__desc_sshauthorizedkey_l = array("", "", "");
	static $__desc_all_mailaccount_l = array("", "", "");
	static $__desc_dns_l = array("", "", "");
	static $__desc_traceroute_l = array("", "", "");
	static $__desc_web_l = array("", "", "");
	static $__desc_ftpuser_l = array("qdtb", "", "");
	static $__desc_sslcert_l = array("db", "", "");
	static $__desc_domaintemplate_l = array("d", "", "");
	static $__desc_resourceplan_l = array("db", "", "");
	static $__desc_cron_l = array("db", "", "");
	static $__acdesc_update_cron_mailto = array("", "", "cron_mail");
	static $__desc_mailaccount_l = array("R", "", "");
	static $__desc_mailforward_l = array("", "", "");
	static $__desc_mailinglist_l = array("", "", "");
	static $__desc_domaindefault_o = array("db", "", "");
	static $__desc_domain_name = array("", "", "domain_name");
	static $__desc_dnstemplate_name = array("", "", "dnstemplate");
	static $__acdesc_show_resource = array("", "", "resources");
	static $__desc_clientdisk_usage = array("D", "", "cdisk:client_disk_usage");
	static $__desc_sp_specialplay_o = array("db", "", "");
	static $__desc_sp_childspecialplay_o = array("db", "", "");
	static $__desc_notification_o = array("db", "", "");
	static $__desc_traffic_usage_q = array("", "", "Traffic");

	static $__desc_default_domain = array("", "", "default_domain");
	static $__acdesc_update_default_domain = array("", "", "default_domain");
	static $__acdesc_update_installatron = array("", "", "installatron");
	static $__acdesc_update_all_resource = array("", "", "all");
	static $__acdesc_show = array("", "", "home");

	// prevent for 'Trying to init a nondescribed Class'
	static $__desc_all_mailforward_l = array("", "", "");
	static $__desc_all_mysqldb_l = array("", "", "");
	static $__desc_all_cron_l = array("", "", "");
	static $__desc_all_ftpuser_l = array("", "", "");
	static $__desc_all_mailinglist_l = array("", "", "");
	static $__desc_all_client_l = array("", "", "");
	static $__desc_addondomain_l = array("", "", "");
	static $__desc_reversedns_l = array("", "", "");
	static $__desc_ftpsession_l = array("", "", "");
	static $__desc_dbadmin_l = array("", "", "");
	static $__desc_phpini_o = array("db", "", "");
//	static $__desc_pserver_o = array("db", "", "");

	static $__desc_dnsslave_l = array("", "", "");

	static $__desc_all_sslcert_l = array("", "", "");

	function isSync()
	{
		if ($this->subaction === 'boxpos') {
			return false;
		}

		return true;
	}

	function createShowMainImageList()
	{
		$vlist['status'] = null;
		$vlist['cttype'] = 1;

		return $vlist;
	}

	function extraBackup() { return true; }

	function getDataServer()
	{
		if ($this->websyncserver) {
			return $this->websyncserver;
		}

		return "localhost";
	}

	function createExtraVariables()
	{
		$this->__var_defdocroot = $this->default_domain;
		$sq = new Sqlite(null, 'web');
		$res = $sq->getRowsWhere("nname = '$this->default_domain'", array('docroot'));
		
		if ($res) {
			$this->__var_defdocroot = $res[0]['docroot'];
		}
	}

	function createShowClist($subaction)
	{
		return null;
		
		$clist = null;
		
		if ($subaction === null) {
			$clist['domain'] = null;
		}

		return $clist;
	}

	function getQuickClass()
	{
		if ($this->isCustomer()) {
			return 'domain';
		} else {
			return null;
		}
	}

	function createShowPropertyList(&$alist)
	{
		global $gbl, $sgbl, $login, $ghtml;

		if ($ghtml->frm_subaction === 'forcedeletepserver') {
			$alist['property'] = pserver::createListAlist($this, 'pserver');

			return;
		}

		if ($ghtml->frm_subaction === 'password') {
			$alist['property'][] = "a=updateform&sa=password";
		} elseif ($ghtml->frm_subaction === 'information') {
			$alist['property'][] = "a=updateform&sa=information";
		} elseif (($ghtml->frm_subaction === 'update') && ($ghtml->frm_o_o['0']['class'] === 'domaindefault')) {
			$alist['property'][] = "a=updateform&sa=update&n=domaindefault";
		} elseif ($ghtml->frm_subaction === 'shell_access') {
			$alist['property'][] = "a=updateform&sa=shell_access";
		} elseif ($ghtml->frm_subaction === 'default_domain') {
			$alist['property'][] = "a=updateform&sa=default_domain";
		} elseif ($ghtml->frm_subaction === 'scavengetime') {
			$alist['property'][] = "a=updateform&sa=scavengetime&o=general";
		} elseif ($ghtml->frm_subaction === 'generalsetting') {
			$alist['property'][] = "a=updateform&sa=generalsetting&o=general";
		} elseif ($ghtml->frm_subaction === 'maintenance') {
			$alist['property'][] = "a=updateform&sa=maintenance&o=general";
		} elseif ($ghtml->frm_subaction === 'selfbackupconfig') {
			$alist['property'][] = "a=updateform&sa=selfbackupconfig&o=general";
		} elseif ($ghtml->frm_subaction === 'download_config') {
			$alist['property'][] = "a=updateform&sa=download_config&o=general";
		} elseif ($ghtml->frm_subaction === 'miscinfo') {
			$alist['property'][] = "a=updateform&sa=miscinfo";
		} elseif ($ghtml->frm_subaction === 'upload_logo') {
			$alist['property'][] = "a=updateform&sa=upload_logo&o=sp_specialplay";
		} elseif ($ghtml->frm_subaction === 'portconfig') {
			$alist['property'][] = "a=updateform&sa=portconfig&o=general";
		} elseif ($ghtml->frm_subaction === 'disable_skeleton') {
			$alist['property'][] = "a=updateform&sa=disable_skeleton";
		} elseif ($ghtml->frm_subaction === 'login_options') {
			$alist['property'][] = "a=updateform&sa=login_options&o=sp_specialplay";
		} elseif ($ghtml->frm_subaction === 'limit') {
			$alist['property'][] = "a=updateform&sa=limit";
		} elseif ($ghtml->frm_subaction === 'dnstemplatelist') {
			$alist['property'][] = "a=updateform&sa=dnstemplatelist";
		} elseif ($ghtml->frm_subaction === 'custombutton') {
			$alist['property'][] = "a=list&sa=custombutton";
		} else {
			$alist['property'][] = "a=show";

			$alist['property'][] = "a=list&c=domain";
		
			if ($this->priv->subdomain_num) {
				$alist['property'][] = "a=list&c=subdomain";
			}
		
			$alist['property'][] = "a=list&c=mailaccount";
			$alist['property'][] = "o=sp_specialplay&a=updateform&sa=skin";
		}
	}

	function createShowTypeList()
	{
		$list = null;
		
		if (isset($this->cttype)) {
			$list['cttype'] = null;
		}

		return $list;
	}

	function getSyncServerForChild($class)
	{
		return $this->websyncserver;
	}

	function changePlanSpecific($plan)
	{
		$this->dnstemplate_list = $plan->dnstemplate_list;
		$this->disable_per = $plan->disable_per;
	}

	function createShowInfoList($subaction)
	{
		global $gbl, $sgbl, $login, $ghtml;
		
		if ($subaction) {
			return;
		}
		
		$resource = db_get_value('resourceplan', $this->resourceplan_used, 'realname');
		
		if (!$this->isAdmin()) {
			if ($this->isLogin()) {
			//	$ilist['Resource Plan'] = $resource;
				$ilist[$login->getKeywordUc('info_resourceplan')] = $resource;
			} else {
			//	$ilist['Resource Plan'] = "_lxinurl:a=updateform&sa=change_plan:$resource:";
				$ilist[$login->getKeywordUc('info_resourceplan')] = "_lxinurl:a=updateform&sa=change_plan:$resource:";
			}
		}

		if ($this->priv->isOn('webhosting_flag')) {
			$url = "a=show&l[class]=ffile&l[nname]=/";
		//	$ilist['Home'] = "_lxinurl:{$url}:/home/{$this->getPathFromName()}/:";
			$ilist[$login->getKeywordUc('info_home')] = "_lxinurl:{$url}:/home/{$this->getPathFromName()}/:";
		//	$ilist['Username'] = "_lxspan:{$this->username}:{$this->username}:";
			$ilist[$login->getKeywordUc('info_username')] = "_lxspan:{$this->username}:{$this->username}:";
			$url = "&a=updateform&sa=default_domain";
		//	$ilist['Default Domain'] = "_lxinurl:{$url}:{$this->default_domain}:";
			$ilist[$login->getKeywordUc('info_defaultdomain')] = "_lxinurl:{$url}:{$this->default_domain}:";
		}
		
		$this->getLastLogin($ilist);

		$skin = $this->getSpecialObject('sp_specialplay')->skin_name;
		$skin = ucfirst($skin);
		$url = "o=sp_specialplay&a=updateform&sa=skin";
	//	$ilist['Skin'] = "_lxinurl:$url:$skin:";
		$ilist[$login->getKeywordUc('info_skin')] = "_lxinurl:$url:$skin:";

		if ($this->isNotCustomer()) {
			return $ilist;
		}

		if (check_if_many_server() && !$this->isLogin()) {
			$ilist['Web Server'] = $this->websyncserver;
			$ilist['Mail Server'] = $this->mmailsyncserver;
			$ilist['Mysql Server'] = $this->mysqldbsyncserver;
			
			if ($this->dnstemplate_list) {
				$ilist['Dns Servers'] = implode(",", $this->dnssyncserver_list);
			}
		}

		return $ilist;
	}

	function isForceQuota($k) 
	{ 
		return ($k === 'totaldisk_usage'); 
	}

	static function createListAlist($parent, $class)
	{
		global $gbl, $sgbl, $login, $ghtml;

		if ($parent->isGte('customer')) {
			return null;
		}

		$alist[] = "a=list&c=client";

		if ($parent->isLte('wholesale')) {
			if (check_if_many_server()) {
				$alist[] = "a=addform&dta[var]=cttype&dta[val]=wholesale&c=client";
			}

			$alist[] = "a=addform&dta[var]=cttype&dta[val]=reseller&c=client";
		}

		if ($parent->isLte('reseller')) {
			$alist[] = "a=addform&dta[var]=cttype&dta[val]=customer&c=client";
		}

		if ($parent->isAdmin()) {
			$alist[] = "a=list&c=all_client";
		}

		return $alist;
	}

	function hasFileResource() { return false; }

	function hasFunctions() { return true; }

	function createDefaultDomain($name, $dnstemplate)
	{
		$p['class'] = 'domain';
		$p['name'] = $name;
		$p['v-dnstemplate_name'] = $dnstemplate;
		$p['v-password'] = $this->realpass;
		__cmd_desc_add($p, $this);
	}

	function createDefaultApplication($dname, $appname)
	{
		$p['class'] = 'installapp';
		$p['parent-class'] = "web";
		$p['parent-name'] = $dname;
		$p['v-appname'] = $appname;
		$p['v-installdir'] = null;
		$p['v-installappmisc_b_s_admin_email'] = $this->contactemail;
		$p['v-installappmisc_b_s_admin_name'] = 'admin';
		$p['v-installappmisc_b_s_admin_password'] = 'admin';
		
		try {
			__cmd_desc_add($p, null);
		} catch (exception $e) {
		}
	}

	function updateCron_mailto($param)
	{
		$cronlist = $this->getList('cron');
		
		if ($cronlist) {
			$cron = arrayGetFirstObject($cronlist);
			$cron->setUpdateSubaction('update');
			$cron->syncToSystem();
		}

		return $param;
	}

	function createShowActionList(&$alist)
	{
		$this->getToggleUrl($alist);
		$this->getCPToggleUrl($alist);

		return $alist;
	}

	function getMysqlDbAdmin(&$alist)
	{
	//	$flagfile = "../etc/flag/user_sql_manager.flg";

	//	if (file_exists($flagfile)) {
	//		$url = file_get_contents($flagfile);
	//		$url = trim($url);
	//		$url = trim($url, "\n");

	//		$dbadminUrl = $url;

		$incfile = "lib/sqlmgr.php";

		if (file_exists($incfile)) {
			// MR -- logic must be declare $dbadminUrl
			include $incfile;
		} else {
			if ($this->isLocalhost('mysqldbsyncserver')) {
				$dbadminUrl = "/thirdparty/phpMyAdmin/";
			} else {
				$fqdn = getFQDNforServer($this->mysqldbsyncserver);

				if (http_is_self_ssl()) {
					$port = get_kloxo_port('ssl');
					$schema = "https://";

				} else {
					$port = get_kloxo_port('nonssl');
					$schema = "https://";
				}

				$dbadminUrl = "{$schema}{$fqdn}:{$port}/thirdparty/phpMyAdmin/";
			}
		}

		try {
			$dbad = $this->getPrimaryDb();
			
			if (!$dbad) {
				return;
			}
			
			$user = $dbad->nname;
			$pass = $dbad->dbpassword;
			
			$alist[] = create_simpleObject(array('url' => "{$dbadminUrl}?pma_username={$user}&pma_password={$pass}", 
				'purl' => "c=mysqldb&a=updateform&sa=phpmyadmin", 'target' => "target='_blank'"));
		} catch (Exception $e) {}
	}

	function createShowAlist(&$alist, $subaction = null)
	{
		global $gbl, $sgbl, $login, $ghtml;

		if ($subaction === 'config') {
			return $this->createShowAlistConfig($alist);
		}

		$server = null;

		if ($this->isAdmin()) {
			$server = "Servers: {$this->getUSlashP("pserver_num")}";
		}

		$alist['__title_administer'] = $login->getKeywordUc('administration');

		if ($this->isLte('reseller')) {
			$alist[] = create_simpleObject(array('url' => "a=list&c=all_domain", 'purl' => "a=updateform&sa=all_resource", '__internal' => true, 'target' => ""));
		}

		$alist[] = "a=list&c=actionlog";

		if ($this->isAdmin()) {
			$alist[] = 'a=list&c=pserver';
		}
		
		if ($this->isLte('reseller')) {
			$alist[] = "a=list&c=client";
		}
		
		if ($this->isLte('reseller')) {
			$alist[] = "a=list&c=resourceplan";
		}

		$this->getTicketMessageUrl($alist);

		if ($login->priv->isOn('can_change_password_flag')) {
			if ($this->isLogin() && $login->isAuxiliary()) {
				$alist['__v_dialog_pass'] = "o=auxiliary&a=updateform&sa=password";
			} else {
				$alist['__v_dialog_pass'] = "a=updateform&sa=password";
			}
		}

		if ($this->isAdmin()) {
			$alist[] = "a=list&c=custombutton";
		}

		$alist['__v_dialog_info'] = "a=updateform&sa=information";

	
		if ($this->priv->isOn('webhosting_flag')) {
			if ($this->priv->isOn('cron_manage_flag') && $this->isCustomer()) {
				if (file_exists("../etc/flag/enablecronforall.flg")) {
					$alist[] = "a=list&c=cron";
				}
			}
		}

		if (!$this->isLogin()) {
			$alist['__v_dialog_limit'] = "a=updateform&sa=limit";
			$alist['__v_dialog_plan'] = "a=updateform&sa=change_plan";
		}

		// MR -- don't care vps or dedi, reversedns always appear
		// just need 'message box' warning
	//	if ($this->isAdmin() && !lxfile_exists("/proc/user_beancounters") && !lxfile_exists("/proc/xen")) {
	/*
		if ($this->isAdmin()) {
			$alist[] = "a=list&c=reversedns";
		}
	*/

		if (!$this->isAdmin()) {
			if (!$this->isLogin()) {
				$alist['__v_dialog_dnstem'] = "a=updateform&sa=dnstemplatelist";
			}

			if (check_if_many_server()) {
				if ($this->isLte('reseller')) {
					$alist[] = "a=updateform&sa=pserver_s";
				}
			}
		}

		if ($this->isAdmin()) {
			$alist[] = 'o=lxupdate&a=show';
		}

		if (!$this->isLogin()) {
			$alist[] = "a=update&sa=dologin";
		}

		if ($this->priv->isOn('webhosting_flag')) {
			$alist['__title_resource'] = $login->getKeywordUc('resource');
		}


		$alist[] = "a=show&o=phpini";

		$alist[] = "a=updateform&sa=update&o=domaindefault";
		$alist[] = "a=list&c=auxiliary";

		$alist[] = "a=list&c=utmp";
		if ($login->isAdmin()) {
			$alist['__v_dialog_shell'] = "a=updateform&sa=shell_access";
		}

		if (check_if_many_server()) {
			if (!$this->isLogin() && !$this->isAdmin()) {
				$alist[] = "a=updateform&sa=domainpserver";
			}
		}

		if ($this->isAdmin()) {
			if ($this->priv->isOn("dns_manage_flag")) {
				$alist[] = "c=dnstemplate&a=list";
			}
		}

		if ($this->isAdmin()) {
			if (lxfile_exists("/var/installatron")) {
				$alist[] = create_simpleObject(array('url' => "/installatron/", 'purl' => 'a=updateform&sa=installatron', 'target' => "target='_blank'"));
			}
		}

		if ($this->priv->isOn('webhosting_flag')) {

			if (lxfile_exists("/var/installatron")) {
				if (!$this->isAdmin()) {
					if ($this->isLogin()) {
						$alist[] = create_simpleObject(array('url' => "/installatron/", 'purl' => 'a=updateform&sa=installatron', 'target' => "target='_blank'"));
					} else {
						$alist[] = "a=updateform&sa=installatron";
					}
				}
			}

			if ($login->priv->isOn('backup_flag')) {
				$alist[] = "a=show&o=lxbackup";
			}

			$alist[] = "a=list&c=ipaddress";
			
		//	if ($this->getList('ipaddress')) {
				$alist[] = "a=list&c=sslcert";
		//	}

			if ($this->isCustomer()) {
				$alist[] = "a=list&c=ftpuser";
				$alist[] = 'a=list&c=ftpsession';
				$alist[] = "a=show&l[class]=ffile&l[nname]=/";
				$alist['__v_dialog_defd'] = "a=updateform&sa=default_domain";
				$alist[] = "a=show&o=sshclient";
				$alist[] = "a=list&c=traceroute";
				$this->getListActions($alist, 'mysqldb');
				$this->getMysqlDbAdmin($alist);
			}
			
			if ($login->priv->isOn('domain_add_flag')) {
			//	$alist[] = "a=addform&c=domain";
			}
		}

	//	if ($this->isNotCustomer()) {
			$alist['__title_domain_rec'] = $login->getKeywordUc('domain');
			$alist[] = "a=list&c=ftpuser";
			$this->getListActions($alist, 'mysqldb');
			$this->getMysqlDbAdmin($alist);
			$alist[] = "a=show&l[class]=ffile&l[nname]=/";
			$alist['__v_dialog_defd'] = "a=updateform&sa=default_domain";

			if (file_exists("../etc/flag/enablecronforall.flg")) {
				$alist[] = "a=list&c=cron";
			}

			$alist[] = "a=list&c=traceroute";
	//	}

		if (!$this->isAdmin() && !$this->isDisabled("shell")) {
			$alist[] = "a=list&c=sshauthorizedkey";
		}
	/*
		if ($this->isCustomer()) {
			$this->getDomainAlist($alist);
		}
	*/
		if ($this->isAdmin()) {
			if ($this->isDomainOwnerMode()) {
				$this->getDomainAlist($alist);
			} else {
				$so = $this->getFromList('pserver', 'localhost');
				$this->getAlistFromChild($so, $alist);
			}
	/*
		} else {

			if ($this->isLte('reseller') && $this->isDomainOwnerMode()) {
				$this->getDomainAlist($alist);
			}
	*/
		}

		$alist['__title_advanced'] = $login->getKeywordUc('advanced');
		if ($this->isAdmin()) {
			$alist['__v_dialog_sca'] = "o=general&a=updateform&sa=scavengetime";
			$alist['__v_dialog_gen'] = "o=general&a=updateform&sa=generalsetting";
			$alist['__v_dialog_main'] = "o=general&a=updateform&sa=maintenance";
			$alist['__v_dialog_self'] = "o=general&a=updateform&sa=selfbackupconfig";
			$alist['__v_dialog_download'] = "o=general&a=updateform&sa=download_config";
			$alist['__v_dialog_forc'] = "a=updateform&sa=forcedeletepserver&c=client";

			if ($sgbl->isHyperVm()) {
				$alist['__v_dialog_hack'] = "o=general&a=updateform&sa=hackbuttonconfig";
				$alist['__v_dialog_rev'] = "o=general&a=updateform&sa=reversedns";
				$alist['__v_dialog_cust'] = "o=general&a=updateform&sa=customaction";
				$alist['__v_dialog_orph'] = "a=updateform&sa=deleteorphanedvps";
				$alist['__v_dialog_lxc'] = "o=general&a=updateform&sa=kloxo_config";
				$alist[] = "a=list&c=customaction";
			} else {
				$alist[] = "o=genlist&c=dirindexlist_a&a=list";
			}
		}

		if ($sgbl->isHyperVm()) {
			if (!$this->isAdmin()) {
				$alist[] = "a=updateform&sa=ostemplatelist";
			}
		}

		$alist['__v_dialog_misc'] = "a=updateform&sa=miscinfo";
		
		// temporary, only for admin - on 6.1.7
		if ($this->isAdmin()) {
			if ($login->priv->isOn('logo_manage_flag') && $this->isLogin()) {
				$alist['__v_dialog_uplo'] = "o=sp_specialplay&a=updateform&sa=upload_logo";
			}

			if ($this->canHaveChild()) {
				$alist['__v_dialog_ch'] = "o=sp_childspecialplay&a=updateform&sa=skin";
			}
		}

		if ($this->isAdmin()) {
			$alist[] = "o=general&a=updateform&sa=portconfig";
		}

		if (!$this->isLogin() && !$this->isLteAdmin() && csb($this->nname, "demo_")) {
			$alist['__v_dialog_demo'] = "o=sp_specialplay&a=updateform&sa=demo_status";
		}

		// temporary, only for admin - on 6.1.7
		if ($this->isAdmin()) {
			if ($login->priv->isOn('can_set_disabled_flag')) {
				$alist[] = 'a=updateform&sa=disable_skeleton';
			}
		}

		$alist[] = "a=list&c=blockedip";
		$alist[] = "a=show&o=notification";

		if (!$this->isLogin()) {
			$alist['__v_dialog_disa'] = "a=updateform&sa=disable_per";
		}

		// temporary, only for admin
		if ($this->isAdmin()) {
			if ($login->priv->isOn('logo_manage_flag') && $this->isLogin()) {
				$alist['__v_dialog_uplo'] = "o=sp_specialplay&a=updateform&sa=upload_logo";
			}
		}

		if (!$this->isLogin()) {
			$alist['__v_dialog_resend'] = "a=updateform&sa=resendwelcome";
		}

		if (!$this->isLogin()) {
			$alist[] = "a=updateform&sa=changeowner";
		}
		if ($this->isLogin()) {
			$alist['__v_dialog_login'] = "o=sp_specialplay&a=updateform&sa=login_options";
		}

		if ($this->isAdmin()) {
		//	$alist[] = "a=updateform&sa=license&o=license";
		}

		$alist[] = "a=list&c=dnsslave";

		$this->getCustomButton($alist);

		return $alist;
	}

	function isDomainOwnerMode()
	{
		global $gbl, $sgbl, $login, $ghtml;

		if ($gbl->isetSessionV('customermode_flag')) {
			return $gbl->isOn('customermode_flag');
		}

		return $this->getSpecialObject('sp_specialplay')->isOn('customermode_flag');
	}

	function getDomainAlist(&$alist)
	{
		$rd = null;

		if ($this->default_domain && !$this->isDisabled('default_domain')) {
			$d = new Domain(null, null, $this->default_domain);
			$d->get();
			if ($d->dbaction === 'clean' && $d->parent_clname === $this->getClName()) {
				$rd = $d;
			}
		}

		if (!$rd) {
			$sq = new Sqlite(null, 'domain');
			$list = $sq->getRowsWhere("parent_clname = '{$this->getClName()}'", array('nname'));
			
			if ($list) {
				$list = get_namelist_from_arraylist($list);
				$dname = getFirstFromList($list);
				$d = new Domain(null, null, $dname);
				$d->get();
				$rd = $d;
			}
		}

		if (!$rd) {
			return;
		}

		$this->getAlistFromChild($rd, $alist);

		$alist[] = "a=list&c=mailaccount";

	/*
		try {
			$m = $this->getFromList('mailaccount', "postmaster@{$rd->nname}");
		} catch (exception $e) {
			return;
		}

	//	$alist['__title_mailaccount'] = "Mailaccount &#x00bb; $m->nname";
		// MR -- no include mail account because only postmaster for first domain!
		$alist['__title_mailaccount'] = "Mailaccount";

		$malist = $m->createShowAlist($rslist);
		
		foreach ($malist as $k => $a) {
			if (csb($k, "__title")) {
				//$alist[$k] = $a;
			} else {
				if (is_string($a)) {
					$alist[] = "j[class]=mailaccount&j[nname]=$m->nname&$a";
				} else {
					if (!csb($a->url, "http")) {
						$a->url = "j[class]=mailaccount&j[nname]=$m->nname&{$a->url}";
					}
					$alist[] = $a;
				}
			}
		}
	*/

	}

	function isCoreBackup() 
	{ 
		return true; 
	}

	function getMultiUpload($var)
	{
		if ($var === 'pserver') {
			return array("ipaddress", "pserver_s");
		}
		if ($var === 'disable_skeleton') {
			return array("disable_url", "skeleton");
		}

		return $var;
	}

	static function getPserverListPriv()
	{
		$array = array("webpserver", "mmailpserver", "dnspserver", "mysqldbpserver");

		return $array;
	}

	static function continueFormClientFinish($parent, $class, $param, $continueaction)
	{
		$weblist = explode(',', $param['listpriv_s_webpserver_list']);
		$vlist['ipaddress_list'] = array('Q', $parent->getIpaddress($weblist));
		
		if (!isOn($param['priv_s_dns_manage_flag'])) {
			$dlist = $parent->getList('dnstemplate');
			$nlist = get_namelist_from_objectlist($dlist);
			$vlist['dnstemplate_list'] = array('U', $nlist);
		}
		
		$ret['action'] = 'add';
		$ret['variable'] = $vlist;
		$ret['param'] = $param;

		return $ret;
	}
}

class all_client extends client
{
	static $__desc = array("", "", "all_client");
	static $__desc_nname = array("n", "", "client_name", URL_SHOW);

	static function AddListForm($parent, $class)
	{
		return null;
	}

	static function createListBlist($parent, $class)
	{
		return null;
	}

	function isSelect()
	{
		return false;
	}

	static function initThisListRule($parent, $class)
	{
		global $login;

		if ($parent->isAdmin()) {
			return "__v_table";
		//	return array('parent_cmlist', "LIKE", "'%,{$parent->getClName()},%'");
		} else {
			throw new lxException($login->getThrow("only_reseller_and_admin"), '', $parent->getClName());
		//	return array('parent_cmlist', "LIKE", "'%,{$parent->getClName()},%'");
		}
	}
}

