<?php

class Domaind extends DomainBase
{
	// Core
	static $__table = "domain";

	// Mysql
	static $__desc_status = array("e", "", "s:status");
	static $__desc_status_v_on = array("", "", "enabled");
	static $__desc_status_v_off = array("", "", "disabled");
	static $__desc_disable_reason = array("", "", "st", 'a=updateform&sa=limit');

	static $__desc_ttype = array("e", "", "t:type_of_hosting", URL_SHOW);
//	static $__desc_ttype_v_dedicated  = array("", "",  "s");
	static $__desc_ttype_v_virtual = array("", "", "virtual_domain");
	static $__desc_ttype_v_forward = array("", "", "addon_domain");
	static $__desc_dtype = array("e", "", "t:type", URL_SHOW);
//	static $__desc_ttype_v_dedicated  = array("", "",  "s");
	static $__desc_dtype_v_domain = array("", "", "domain");
	static $__desc_dtype_v_maindomain = array("", "", "domain");
	static $__desc_dtype_v_subdomain = array("", "", "subdomain");
	static $__desc_state = array("e", "", "ST:State", 'a=updateform&sa=limit');
	static $__desc_state_v_ok = array("", "", "alright");
	static $__desc_state_v_exceed = array("", "", "exceeded");
	static $__desc_uuser_dummy = array("n", "", "primary_ftp_user");
	static $__desc_parent_clname = array("n", "", "parent");
	static $__desc_ftpuser_f = array("n", "", "ftp_user");

	static $__desc_contactemail = array("", "", "contact_email");
	static $__desc_parent_name_f = array("", "", "owner");
	static $__desc_username = array("", "", "ftp_user_name");
	static $__desc_redirect_domain = array("", "", "redirect_to");
	static $__desc_redirect_mail_domain = array("", "", "redirect_mail_to");
	static $__desc_nameserver = array("n", "", "name_server");
	static $__desc_redirect_to_flag = array("e", "", "Redi:Redirected To Flag");
	static $__desc_redirect_to_flag_v_on = array("", "", "there_are_addon_domains_on_[%s].");
	static $__desc_redirect_to_flag_v_dull = array("", "", "free");
	static $__desc_docroot = array("S", "", "document_root");

	static $__desc_domain_owned_flag = array("f", "", "domain_owned");

	static $__desc_webpserver = array("", "", "web_server");
	static $__desc_mmailpserver = array("", "", "mail_server");
	static $__desc_dnspserver = array("", "", "dns_server");
	static $__desc_secdnspserver = array("", "", "sec_dns_server");

	// Fake Variables
	static $__desc_send_welcome_f = array("f", "", "send_welcome_message");
	static $__desc_use_resourceplan_f = array("f", "", "use_template");
	static $__desc_resourceplan_f = array("s", "", "plan_name");
	static $__desc_dnstemplate_f = array("s", "", "dns_template");
	static $__desc_subdomain_parent = array("", "", "parent_domain");

	static $__desc_pvview_f = array("b", "", "", "__stub_domain_view_url");
	static $__desc_dnvview_f = array("b", "", "", "__stub_domain_preview_url");
	static $__desc_webmail_f = array("b", "", "", "__stub_domain_webmail");
	static $__desc_stats_f = array("b", "", "", "__stub_domain_stats");
	static $__desc_awstats_f = array("b", "", "", "__stub_domain_awstats");
	static $__desc_check_dns_f = array("b", "", "", "__stub_check_dns");

	static $__desc_webhome_f = array("b", "", "", 'o=web&a=show');
	static $__desc_ffile_f = array("b", "", "", 'o=web&l[class]=ffile&l[nname]=/&a=show');
	static $__desc_ipaddress_f = array("b", "", "", 'o=web&a=updateform&sa=ipaddress');
	static $__desc_information_f = array("b", "", "", 'a=updateform&sa=information');
	static $__desc_dnshome_f = array("b", "", "", 'o=dns&a=show');
	static $__desc_mmailhome_f = array("b", "", "", 'o=mmail&a=show');

	static $__desc_traffic_usage_per = array("pS", "", "traffic");
	static $__desc_disk_usage_per = array("pS", "", "disk");
	static $__acdesc_show = array("", "", "domain_home");
	static $__acdesc_update_show_stats = array("", "", "show_stats");

	// Objects
	static $__desc_web_o = array('qdtb', '', '', '');
	static $__desc_dns_o = array('qdb', '', '', '');
	static $__desc_mmail_o = array('qdtb', '', '', '');
//	static $__desc_lxbackup_o = array('d', '', '', '');

	static $__desc_ftpuser_o = array('d', '', '', '');
	static $__desc_phpini_o = array('d', '', '', '');

	static $__desc_pserver_o = array('', '', '', '');

	// Lists
//	static $__desc_domain_l = array("qvd", "",  "virtual_object");
	static $__desc_domaintraffic_l = array("d", "", "");
	static $__desc_addondomain_l = array("qdb", "", "");
	static $__desc_domaintraffichistory_l = array("", "", "");
	static $__desc_mysqldb_l = array("qdB", "", "");
//	static $__desc_mssqldb_l = array("qdB", "", "");

	static $__acdesc_update_information = array("", "", "information");
	static $__acdesc_update_view = array("", "", "view_site");
	static $__acdesc_update_site_preview = array("", "", "dnsless_preview");
	static $__acdesc_show_config = array("", "", "advanced");
	static $__acdesc_update_phpinfo = array("", "", "phpinfo");
	static $__acdesc_update_limit = array("", "", "domain_features");
	static $__acdesc_update_show_awstats = array("", "", "awstats");
	static $__acdesc_update_check_dns = array("", "", "check_dns");

	function getDomainRoot()
	{
		global $gbl, $sgbl, $login;

		$path = $this->nname;
		$fpathp = "__path_httpd_root/$path";

		return $fpathp;
	}

	function changeOwnerSpecific()
	{
		global $login;

		$parent = $this->getParentO();
		$newcustomer_name = $this->getParentName();

		$oldcustomer_name = $this->getParentName('__old_parent_name');

		$web = $this->getObject('web');
		$web->username = $parent->username;
		$web->setUpdateSubaction('changeowner');
		$web->__var_oldcustomer_name = $oldcustomer_name;
		$web->customer_name = $parent->getPathFromName('nname');

		// MR -- no need change phpini because user-based php.ini
	//	$phpini = $web->getObject('phpini');
	//	$phpini->setUpdateSubaction('changeowner');

		$this->generateCMList();

	//	$flist = $web->getList('ftpuser');

	//	foreach ($flist as $l) {
	//		$l->setupdateSubaction('changeowner');
	//	}
		
	//	$f = $parent->getObject('ftpuser');
	//	$f->setupdateSubaction('changeowner');

		// MR -- all above action for ftpuser not work; so, change ftuser table directly
		db_set_value('ftpuser', "parent_clname = 'client-{$parent->username}'", "nname = '{$this->nname}'"); 

		$mmail = $this->getObject('mmail');
		$mmail->systemuser = $parent->username;
		$mmail->setUpdateSubaction('changeowner');

		if ($parent->websyncserver !== $web->syncserver) {
			throw new lxException($login->getThrow("webserver_not_same"), '', "$parent->websyncserver != $web->syncserver");
		}
		
		if ($parent->mmailsyncserver !== $mmail->syncserver) {
			throw new lxException($login->getThrow("mailserver_not_same"), '', "$parent->mmailsyncserver != $mmail->syncserver");
		}
	}

	function loadallSingleChildren()
	{
		$web = $this->getObject('web');
		$mmail = $this->getObject('mmail');
		$mmail->ttype = 'virtual';
	}

	static function verify($var, $val)
	{
		switch ($var) {
			case "nname":
				if (is_numeric($val) || char_search_a($val, " ")) {
					throw new lxException("");
				}
			case "username":
				if (is_numeric($val) || char_search_a($val, " ")) {
					throw new lxException("");
				}
		}
		
		return $val;
	}

	function isRealChild($c)
	{
		if ($this->ttype === 'virtual') {
			return true;
		}

		return true;
	}


	function updatePassword($param)
	{
		$web = $this->getObject('web');
		$web->__var_sysuserpassword = null;
		$web->__var_sysuserpassword['realpass'] = $param['password'];
	/*
		$ftpuser = $web->getFromList('ftpuser', $web->ftpusername);
		$ftpuser->realpass = $param['password'];
		$ftpuser->password = crypt($param['password']);
		$ftpuser->setUpdateSubaction('password');
	*/	
		return parent::updatePassword($param);
	}

	function isQuotaVariableSpecific($var)
	{
		global $gbl, $sgbl, $login, $ghtml;

		// MR -- change this function for php-fpm purpose		
		return lightyApacheLimit($this->webpserver, $var);
	}

	function isSelect()
	{
		if (if_demo()) {
			if ($this->nname === 'example.com') {
				return false;
			}
			
			if ($this->nname === 'customer.com') {
				return false;
			}
			
			if ($this->nname === 'lxlabs.com') {
				return false;
			}
			
			if ($this->nname === 'admind.com') {
				return false;
			}

			if ($this->nname === 'mratwork.com') {
				return false;
			}
		}
		
		return true;
	}


	static function AddListForm($parent, $class)
	{
	/*
		if ($parent->isLogin() && !$parent->priv->isOn('domain_add_flag')) {
			return false;
		}

		$vlist['nname'] = null;
		$vlist['dnstemplate_f'] = make_hidden_if_one(domainBase::getDnsTemplateList($parent));
		$vlist['simple_add_f'] = array('h', 'on');
		
		return $vlist;
	*/
		return self::addform($parent, $class);
	}

	function isTreeSelect()
	{
		return true;
	}

	function getStubUrl($name)
	{
		if ($name == '__stub_domain_view_url') {
			return create_simpleObject(array('url' => "http://[%s]", 'purl' => 'c=domain&a=updateform&sa=view', 'target' => "target=_blank"));
		}

		if ($name === '__stub_domain_preview_url') {
			return create_simpleObject(array('url' => "/sitepreview/[%s]", 'purl' => 'c=domain&a=updateform&sa=site_preview', 'target' => "target=_blank"));
		}

		if ($name === '__stub_domain_webmail') {
			return create_simpleObject(array('url' => "http://webmail.[%s]", 'purl' => 'c=mailaccount&a=updateform&sa=webmail', 'target' => "target=_blank"));
		}
		
		if ($name === '__stub_domain_stats') {
			return create_simpleObject(array('url' => "http://[%s]/stats/", 'purl' => 'c=domain&a=updateform&sa=show_stats', 'target' => "target=_blank"));
		}

		if ($name === '__stub_domain_awstats') {
			return create_simpleObject(array('url' => "http://[%s]/awstats/awstats.pl?config=[%s]", 'purl' => 'c=domain&a=updateform&sa=show_awstats', 'target' => "target=_blank"));
		}
		
		if ($name === '__stub_check_dns') {
			return create_simpleObject(array('url' => "http://intodns.com/[%s]", 'purl' => 'c=domain&a=updateform&sa=check_dns', 'target' => "target=_blank"));
		}
	}

	function isButton($var)
	{
		if (cse($var, "_f")) {
			if ($this->ttype === 'forward') {
				return null;
			}
		}
		
		return true;
	}

	function display($var)
	{
		if ($var === "status_client") {
			return $this->status;
		}
	/*
		if ($var === 'parent_name_f') {
		  return "_lxspan:{$this->getParentName()}: Ftp {$this->getObject('web')->ftpusername}:";
		}
	*/
		if ($var === 'docroot') {
			return $this->getObject('web')->docroot;
		}

		return parent::display($var);
	}

	function deleteSpecific()
	{
	//	$this->notifyObjects('delete');
	//	lxfile_rm_rec("__path_program_home/domain/$this->nname/");

		$ftplist = $this->getParentO()->getList('ftpuser');

		foreach ($ftplist as $k => $v) {
			if (cse($v->nname, "@$this->nname")) {
				$v->delete();
				
				continue;
			}

			if ($v->nname === $this->nname) {
				$v->delete();
				
				continue;
			}
		}
	}

	function perDisplay($var)
	{
		$realname = strtil($var, "_per");
		
		switch ($var) {
			case "disk_usage_per":
				return array($this->priv->$realname, $this->used->$realname, "MB");

			case "traffic_usage_per":
				return array($this->priv->$realname, $this->used->$realname, "MB/Month");
		}
	}

	static function createListAlist($parent, $class)
	{
		if (!$parent->username && $parent->isAdmin()) {
			$parent->username = 'admin';
			$parent->setUpdateSubaction();
		}

		$alist[] = "a=show";

		$alist[] = "a=list&c=domain";
		$alist[] = "a=list&c=subdomain";

		$alist[] = "a=list&c=mailaccount";

		$alist[] = "o=sp_specialplay&a=updateform&sa=skin";
		
		return $alist;
	}

	function checkValidityPeriod()
	{
		if ($this->validity_period != "Unlimited") {
			$current_time = time();
			$diff_secs = $this->validity_period - $current_time;
			$diff_weeks = floor($diff_secs / 604800);
			$diff_secs = $diff_weeks * 604800;
			$diff_days = floor($diff_secs / 86400);
			
			if ($diff_days <= 0 && $diff_weeks <= 0) {
				$this->toggleStatus();
				return false;
			} else {
				return true;
			}
		} else {
			return true;
		}
	}

	function getFullPath()
	{
		$path = $this->nname;
		
		return $this->root . $path;
	}

	function isAction($var)
	{
		global $gbl, $sgbl, $login;

		if (($var === 'nname' || $var === 'dtype') && $this->getParentO()->isCustomer()) {
			return true;
		}

		return true;
	}


/*
	// This is needed only for subdomains... 
	// For subdomains, the parent would be domain while for the normal domains, the parent is client.

	static function initThisList($parent, $class)
	{
		global $gbl, $sgbl, $login;

		$db = new Sqlite($this->__masterserver, "domain");
		$result = $db->getRowsWhere("parent_name = '$this->nname' and parent_class = 'domain'");

		if ($result) {
			$this->setListFromArray("ddatabase", $result );
		}

	}
*/

/*
	static function initThisList($parent, $class)
	{
		global $gbl, $sgbl, $login;

		$db = new Sqlite($parent->__masterserver, "domain");
		$class = $parent->getClass();
		$result = $db->getRowsWhere("parent_name = '$parent->nname' and parent_class = '$class'");

	//	dprintr($result);

		return $result;
	}
*/

	static function createListSlist($parent)
	{
		return null;
		
		$sql = new Sqlite($parent->__masterserver, "pserver");
		$res = $sql->getTable(array('nname'));
		$rs = get_namelist_from_arraylist($res);
		
		if (count($rs) > 1) {
			$nlist['webpserver'] = array('s', $rs);
			$nlist['mmailpserver'] = array('s', $rs);
			$nlist['dnspserver'] = array('s', $rs);
		}
		
		$rs = lx_array_merge(array(array("--any--"), $rs));
		$nlist['nname'] = null;
	//	$nlist['status'] = array('s', array('--any--', 'on', 'off'));
		
		return $nlist;
	}

	static function createListNlist($parent, $view)
	{
		global $gbl, $sgbl, $login, $ghtml;

	//	$name_list["state"] = "3%";
		$name_list["status"] = "3%";
		$name_list["dtype"] = "3%";

		if ($login->isAdmin() || $login->priv->isON('can_manage_dns')) {
			$name_list["abutton_show_s_dns"] = "3%";
		}

		$name_list["abutton_list_s_domaintraffichistory"] = "3%";

		if ($parent->priv->isOn('webhosting_flag')) {
			$gen = $login->getObject('general')->generalmisc_b;
			$webstatsprog = $gen->webstatisticsprogram;
			
			if (!$webstatsprog) {
				$webstatsprog = "awstats";
			}

			$name_list["abutton_list_s_addondomain"] = "3%";
			$name_list["abutton_update_s_phpinfo"] = "3%";
			$name_list["pvview_f"] = "3%";
			$name_list["check_dns_f"] = "3%";
			$name_list["dnvview_f"] = "3%";
			$name_list["webmail_f"] = "3%";
			
			if ($webstatsprog === 'awstats') {
				$name_list["awstats_f"] = "3%";
			} else {
				if ($webstatsprog === 'webalizer') {
					$name_list["stats_f"] = "3%";
				}
			}
		}

		$name_list["nname"] = "30%";

		$name_list["ddate"] = "10%";
		$name_list["docroot"] = "50%";
		$name_list["traffic_usage"] = "5%";
	//	$name_list["totaldisk_usage"] = "5%";
	//	$name_list["domain_num"] = "3%";
		
		return $name_list;
	}

	function isRealQuotaVariable($k)
	{
		$list['traffic_usage'] = 'a';
		$list['traffic_last_usage'] = 'a';
		return isset($list[$k]);
	}

	function getQuotaAddList($k)
	{
		if ($k !== 'totaldisk_usage') {
			return null;
		}
		
		return array('disk_usage', 'mysqldb_usage');
	}

	function getQuotatraffic_usage()
	{
		global $gbl, $sgbl, $login, $ghtml;

		if (isset($sgbl->__var_trafficusage)) {
			return $sgbl->__var_trafficusage[$this->nname];
		} else {
			return $this->used->traffic_usage;
		}
	}

	function getQuotatraffic_last_usage()
	{
		global $gbl, $sgbl, $login, $ghtml;

		if (isset($sgbl->__var_traffic_last_usage)) {
			return $sgbl->__var_traffic_last_usage[$this->nname];
		} else {
			return $this->used->traffic_last_usage;
		}

	}

	static function createListIlist()
	{
	}

	function postUpdate()
	{
		// We need to write because reads everything from the database.
		$this->write();

		// The lxclient postupdate which checks for change of skin...
		parent::postUpdate();
	}

	function checkDnsTemplateConsistency($web, $mmail, $dnstemplate)
	{
		global $login;

		$list = getIpAddressList($web->__masterserver, $web->syncserver);

		if (!array_search_bool($dnstemplate->ipaddress, $list)) {
			throw new lxException($login->getThrow('dns_template_inconsistency'), '', $dnstemplate->ipaddress);
		}
	}

	function getMultiUpload($var)
	{
		if ($var === 'limit') {
		//	return array('limit_s', 'change_plan');
			return "limit";
		}
		
		return $var;
	}

	function postAdd()
	{
		global $gbl, $sgbl, $login, $ghtml;

		$gen = $login->getObject('general')->generalmisc_b;

		$parent = $this->getParentO();
		$domdefault = $parent->getObject('domaindefault');
		$web = $this->getObject("web");
		$mmail = $this->getObject('mmail');
		$dns = $this->getObject("dns");

		$web->remove_processed_stats = $domdefault->remove_processed_stats;

		$dname = $this->nname;

	//	$dname = self::createUusername($dname);

		$web->ftpusername = substr($dname, 0, 31);

		$gen = $login->getObject('general')->generalmisc_b;

		$web->__var_extrabasedir = $gen->extrabasedir;

		$this->cpstatus = 'on';

		$this->ttype = 'virtual';
		$web->ttype = 'virtual';
		$mmail->ttype = 'virtual';

		if ($this->isClass('subdomain')) {
			$parentdomain = $parent->getFromList('domaina', $this->subdomain_parent);
		}

		if ($this->isOn("simple_add_f") || $this->isClass('subdomain')) {
			if ($this->isClass('subdomain')) {
				$cparent = $parentdomain;
			} else {
				$cparent = $parent;
			}
			
			$this->template_used = $cparent->template_used;
			$this->resourceplan_used = $cparent->resourceplan_used;
			$this->priv = clone $cparent->priv;
			$this->listpriv = clone $cparent->listpriv;
			$this->disable_per = $cparent->disable_per;
			$this->password = $cparent->password;
			$this->realpass = $cparent->realpass;
			$this->priv->phpfcgiprocess_num = 0;
			$web->priv->phpfcgiprocess_num = 0;
			$this->priv->phpfcgi_flag = 'off';
			$web->priv->phpfcgi_flag = 'off';
		} else {
			$this->realpass = $parent->realpass;
			$this->password = crypt($this->realpass);

			if ($this->isOn('use_resourceplan_f')) {
				$template = getFromAny(array($login, $parent), 'resourceplan', $this->resourceplan_f);
				
				if (!$template) {
					throw new lxException($login->getThrow("no_resourceplan"), '', $this->resourceplan_f);
				}
				
				$this->template_used = $this->resourceplan_f;
				$this->resourceplan_used = $this->resourceplan_f;
				$this->priv = clone $template->priv;
				$this->listpriv = clone $template->listpriv;
				$this->disable_per = $template->disable_per;
				
				if (!$this->dnstemplate_f) {
					$list = domainBase::getDnsTemplateList($parent);
					$this->dnstemplate_f = $list[0];
				}
			}
		}

		if (!$this->docroot) {
			$this->docroot = $this->nname;
		}

		$this->docroot = coreFfile::getRealpath($this->docroot);

		if ($this->isClass('subdomain')) {
			$dnstemplate = $parentdomain->getObject('dns');
		} else {
			$dnstemplate = new Dnstemplate($this->__masterserver, null, $this->dnstemplate_f);
			$dnstemplate->get();
			if ($dnstemplate->dbaction === 'add') {
				throw new lxException($login->getThrow('no_dns_template'), '', $this->dnstemplate_f);
			}
		}

	//	$mmail->catchall = $domaindefault->catchall;

		if (!$mmail->catchall) {
			$mmail->catchall = 'Delete';
		}

		$web->ipaddress = $dnstemplate->getIpForBaseDomain();
		$web->docroot = $this->docroot;

		$web->docroot = trim($web->docroot);
		$web->docroot = trim($web->docroot, "/");

		validate_docroot($web->docroot);

		$dns->copyObject($dnstemplate);
		$dns->dbaction = 'add';

		$web->syncserver = $parent->websyncserver;

		// MR -- fix issue of not array
		$dns->syncserver = (is_array($parent->dnssyncserver_list)) ? 
			implode(",", $parent->dnssyncserver_list) : $parent->dnssyncserver_list;

		$mmail->syncserver = $parent->mmailsyncserver;

		$dns->zone_type = 'master';

		unset($this->cttype);

		if ($this->isClass('subdomain')) {
			$this->dtype = 'subdomain';
		} else {
			$this->dtype = 'maindomain';
		}

		$mmail->fixSyncServer();
		$web->fixSyncServer();
		$dns->fixSyncServer();

		$skelf = "__path_client_root/$parent->nname/skeleton.zip";
		
		if (!lxfile_exists($skelf)) {
			$skelf = "__path_client_root/admin/skeleton.zip";
		}

		//--- for new user-skeleton (since 6.1.7)
		if (!lxfile_exists($skelf)) {
			// MR -- must using \- for zip name
			$skelf = "__path_kloxo_httpd_root/" . "user\-skeleton.zip";
		}

		if (!lxfile_exists($skelf)) {
			$skelf = "__path_kloxo_httpd_root/skeleton.zip";
		}
		if (!lxfile_exists($skelf)) {
			$skelf = null;
		}

		if ($skelf) {
			$web->__var_skelmachine = getOneIPForLocalhost($web->syncserver);
			$ret = cp_fileserv($skelf);
			$web->__var_skelfile = $ret;
		} else {
			$web->__var_skelfile = null;
		}

		$ftpuser = new Ftpuser(null, $web->syncserver, $web->ftpusername);
		$ftpuser->initThisdef();
	//	$uuser = new Uuser(null, $web->syncserver, $dname);
	//	$uuser->initThisdef();

	//	$web->addObject('uuser', $uuser);

		$ftpuser->directory = $this->docroot;
		$parent->addObject('ftpuser', $ftpuser);
		$ftpuser->username = $parent->username;

		if ($this->getRealClientParentO()->username) {
			$web->username = $this->getRealClientParentO()->username;
		} else {
			$web->username = $this->getRealClientParentO()->nname;
		}

		$rp = $this->getRealClientParentO();
		$web->customer_name = $rp->getPathFromName('nname');

		$mmail->systemuser = $web->username;

		$this->mmailpserver = $mmail->syncserver;
		$this->webpserver = $web->syncserver;
		$this->dnspserver = $dns->syncserver;

		// hack hack convert listpriv into a differnet object.

		$this->generateCMList();

		$this->distributeChildQuota();

		$driverapp = $gbl->getSyncClass($this->__masterserver, $web->syncserver, 'web');

		////////////////////

	//	$uuser->syncserver = $web->syncserver;
		$ftpuser->syncserver = $web->syncserver;
	//	$ftpuser->directory =  "/domain/$this->nname";
		// Hack hack uuser needs driver to be redone, since the driver was created when uuser had no syncserver....
	//	$uuser->createSyncClass();
		$ftpuser->createSyncClass();
		$web->createSyncClass();
		$mmail->createSyncClass();
		$dns->createSyncClass();

	//	$uuser->realpass = $this->realpass;
	//	$uuser->password = crypt($this->realpass);
		$ftpuser->realpass = $this->realpass;
		$ftpuser->password = crypt($this->realpass);
		$mmail->remotelocalflag = 'local';

		$web->stats_username = $this->nname;
		$web->stats_password = null;

		// Gotta Add postmaster...
		$mailaccount = new Mailaccount($this->__masterserver, $this->__readserver, "postmaster@$this->nname");
		$mailaccount->initThisdef();
		$mailaccount->__parent_o = $mmail;
		$mailaccount->syncserver = $mmail->syncserver;
		$mailaccount->parent_clname = $mmail->getClName();
		$mailaccount->postAdd();
		$mailaccount->cpstatus = 'on';
		$mailaccount->password = $this->password;
		$mailaccount->realpass = $this->realpass;
	//	$mailaccount->metadbaction = 'writeonly';
		$mmail->addToList('mailaccount', $mailaccount);

		$spam = new Spam($this->__masterserver, $this->__readserver, $this->nname);
		$spam->initThisdef();
		$res['syncserver'] = $mmail->syncserver;
		$spam->create($res);
		$spam->parent_clname = $mmail->getClName();
		$mmail->addObject('spam', $spam);

	/* 
		// Not needed, instead the admin can configure this after the domain is created.
		if ($maindomain) {
			$parked = new addondomain(null, null, "{$this->nname}.$maindomain");
			$parked->initThisdef();
			$res['mail_flag'] = 'off';
			$parked->create($res);
			$parked->parent_clname = $this->getClName();
			$this->addToList('addondomain', $parked);
		}
	*/

	//	$uuser->shellflag = 'off';
	//	$uuser->shell = '--Disabled--';

	/*
		if (exists_in_db($this->__masterserver, "uuser", $uuser->nname)) {
			throw new lxException($login->getThrow('user_exists_in_db'), '', $uuser->nname);
		}
	*/


	/*
		$backup = new LxBackup($this->__masterserver, $this->__readserver, $this->getClName());
		$backup->initThisDef();
		$this->AddObject('lxbackup', $backup);
	*/

	//	lxfile_mkdir("__path_program_home/domain/$this->nname/__backup");

		$this->lxclientpostAdd();

		$this->generateDomainKey(true);

	//	exec("sh /script/fixphp --domain={$this->nname}");
	}

	function generateDomainKey($dontwasflag)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$mmail = $this->getObject('mmail');
		$dns = $this->getObject('dns');

		$domainkeyflag = db_get_value('servermail', $mmail->syncserver, 'domainkey_flag');

		if (!isOn($domainkeyflag)) {
			$dns->RemoveDomainKey();
			
			if ($dontwasflag) {
				return;
			}
			
			$dns->setUpdateSubaction('full_update');
			$dns->was();
			
			return;
		}

		$dkey = rl_exec_in_driver($mmail, 'mmail', 'generateDKey', array($this->nname));

		if (!$dkey) {
			return;
		}

		$dns->addDomainKey($dkey);
		
		if ($dontwasflag) {
			return;
		}

	//	$dns->setUpdateSubaction('full_update');
		$dns->setUpdateSubaction('domain');

		$dns->was();
	}

	function sendExtraFilesForMe($bdir, $id)
	{
		$dname = $this->nname;
		$dl = $this->getList('mysqldb');
		
		foreach ((array)$dl as $d) {
			$d->restoreMeUp($bdir, $id);
		}
		
		$web = $this->getObject('web');
		$web->restoreMeUp($bdir, $id);
		$mmail = $this->getObject('mmail');
		$mmail->restoreMeUp($bdir, $id);

		$mlist = $mmail->getList('mailinglist');

		foreach ((array)$mlist as $ml) {
			$ml->restoreMeUp($bdir, $id);
		}
	}

	function isCoreBackup()
	{
		return true;
	}

	function getExtraFilesForMe($backdir, $id)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$dl = $this->getList('mysqldb');
		$dname = $this->nname;
		
		foreach ((array)$dl as $d) {
			$d->backMeUp($backupdir, $id, 'backup');
		}

		$web = $this->getObject('web');
		$web->backMeUp($backupdir, $id, 'backup');

		$mmail = $this->getObject('mmail');
		$mmail->backMeUp($backupdir, $id, 'backup');

		$mlist = $mmail->getList('mailinglist');

		foreach ((array)$mlist as $ml) {
			$ml->backMeUp($backupdir, $id, 'backup');
		}
	}

	function postSync()
	{
	/*
		if ($this->dbaction === 'add') {
			$this->notifyObjects('add');
		}
	*/
	}

	static function add($parent, $class, $param)
	{
		global $gbl, $sgbl, $login, $ghtml;

		if ($class === 'subdomain') {
			$param['nname'] = "{$param['nname']}.{$param['subdomain_parent']}";
		}

		$param['nname'] = trim($param['nname']);

		validate_prefix_domain($param['nname']);

		validate_domain_name($param['nname']);

		// MR -- no need domain_owned with /script/add
		if (($param['domain_owned_flag'] !== 'on') && ($param['domain_owned_flag'])) {
			validate_domain_owned($param['nname']);
		}

		lxclient::fixpserver_list($param);

		$param['nname'] = strtolower($param['nname']);

		if (exists_in_db(null, 'addondomain', $param['nname'])) {
			throw new lxException($login->getThrow('domain_already_exists_as_pointer'), '', $param['nname']);
		}

		$param['web-nname'] = $param['nname'];
		$param['dns-nname'] = $param['nname'];
		$param['dns-zone_type'] = 'master';

		$param['mmail-nname'] = $param['nname'];

		// the uuser is two steps removed from the main object (domain), and thus the automatic nname creation 
		// doesn't seem to work. So we have to do it here.

	/*
		$param['realpass'] = $param['password'];
		$param['password'] = crypt($param['password']);
	*/
		return $param;
	}

	static function continueFormFinish($parent, $class, $param, $continueaction)
	{
		global $gbl, $sgbl, $login, $ghtml;
		
		// For IE.. too many variables won't work in get mode.
		$sgbl->method = "post";

		$param['nname'] = trim(strtolower($param['nname']));
		$dname = $param['nname'];
		$dname = str_replace(".", "", $dname);
		
		if (is_numeric($dname[0])) {
			$dname = "a" . $dname;
		}

		$iplist = $parent->getIpaddress(array('localhost'));

		if (!$iplist) {
			$iplist = getAllIpaddress();
		}

		/// Normal Virtual Hosting.
		Lxclient::fixpserver_list($param);

		$vlist['__c_subtitle_quota'] = 'Quota';
		$qvlist = getQuotaListForClass('domain', array('ttype' => 'virtual'));
		$vlist = lx_array_merge(array($vlist, $qvlist));
	//	$vlist['dbtype_list'] = null;

		$ret['param'] = $param;
		$ret['variable'] = $vlist;
		$ret['action'] = "Add";
		
		return $ret;
	}

	static function continueForm($parent, $class, $param, $continueaction)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$param['nname'] = strtolower($param['nname']);

		$param['nname'] = trim($param['nname']);

		validate_domain_name($param['nname']);

		if ($continueaction === 'server') {

			if ($param['resourceplan_f'] === 'continue_without_plan') {
				$ret = self::continueFormlistpriv($parent, $class, $param, $continueaction);

			} else {
				$template = getFromAny(array($login, $parent), 'resourceplan', $param['resourceplan_f']);

				$param['use_resourceplan_f'] = 'on';
				
				if (!$template) {
					throw new lxException($login->getThrow("no_resourceplan"), '', $param['resourceplan_f']);
				}

				$ret['action'] = 'addnow';

				$ret['param'] = $param;
				
				return $ret;
			}
		} else {
			$ret = self::continueFormFinish($parent, $class, $param, $continueaction);
		}

		return $ret;
	}

	static function createUusername($dname)
	{
		$dname = str_replace(".", "", $dname);

		return $dname;

		if (is_numeric($dname[0])) {
			$dname = "a" . $dname;
		}

		if (strlen($dname) > 15) {
			$dname = substr($dname, 0, 15);
		}
		$sq = new Sqlite(null, 'uuser');
		if (!$sq->getRowsWhere("nname = '$dname'")) {
			return $dname;
		}

		$dnamebase = $dname;
		$i = 0;
		
		while (true) {
			$i++;
			if ($sq->getRowsWhere("nname = '$dname'")) {
				$dname = $dnamebase . "$i";
			} else {
				break;
			}
		}

		return $dname;
	}

	static function addCommand($parent, $class, $p)
	{
		checkIfVariablesSet($p, array('name'));
		checkIfVariablesSetOr($p, $param, 'dnstemplate_f', array('v-dnstemplate_name'));
		$param['nname'] = $p['name'];

		$param['nname'] = strtolower($param['nname']);
		$param['ttype'] = 'virtual';
	//	$param['password'] = $p['v-password'];
		$param['use_resourceplan_f'] = 'on';
		$param['simple_add_f'] = 'on';
		
		return $param;
	}

	function commandUpdate($subaction, $param)
	{
		switch ($subaction) {
			case "change_plan":
				checkIfVariablesSetOr($param, $param, 'newresourceplan', array('resourceplan_name'));
				break;
		}

		return $param;
	}


	static function addform($parent, $class, $typetd = null)
	{
	/*
		if ($parent->isNotCustomer()) {
			$ret['variable'] = $vlist;
			$ret['action'] = '';
	
			return $ret;
		}
	*/

		$res = DomainBase::getDnsTemplateList($parent);
		$vlist['nname'] = "";

		$vlist['domain_owned_flag'] = array('f', 'on', 'off');
	//	$vlist['domain_owned_flag'] = null;

		$dir = "__path_customer_root/{$parent->getPathFromName()}";
		$dir = expand_real_root($dir);
		
		if ($parent->priv->isOn('document_root_flag')) {
			$vlist['docroot'] = array('m', array('pretext' => "$dir/"));
		}

	//	$templatelist = $parent->getResourcePlanList('resourceplan');
	//	$vlist['password'] = null;
		$vlist['__v_button'] = 'add';

		$vlist['dnstemplate_f'] = make_hidden_if_one($res);
	//	$vlist['resourceplan_f'] = array('A', $templatelist);

	//	$vlist['__c_subtitle_quota'] = 'Features';
		$qvlist = getQuotaListForClass('domain', array('ttype' => 'virtual', 'webpserver' => $parent->websyncserver));
		$vlist = lx_array_merge(array($vlist, $qvlist));

		$ret['variable'] = $vlist;
		$ret['action'] = "add";

		return $ret;
	}


	static function getSelectList($parent, $var)
	{
		global $gbl, $sgbl, $login, $ghtml;

		switch ($var) {
			case "ttype":
				return array("virtual", "forward");

			case "ipaddress":
				return getAllIpaddress();
		}

	}

	function getQuotadisk_usage()
	{
		global $gbl, $sgbl, $login, $ghtml;

		return lxfile_dirsize("/home/kloxo/domain/{$this->nname}");
	}

	function createShowTypeList()
	{
		$list['dtype'] = null;
		
		return $list;
	}

	function createShowInfoList($subaction)
	{
		global $gbl, $sgbl, $login, $ghtml;
		
		if ($subaction) {
			return;
		}
		
		$web = $this->getObject('web');
		$mmail = $this->getObject('mmail');
		$dns = $this->getObject('dns');
		$ilist['FTP User'] = "$web->ftpusername";
		$ilist['Home'] = "/home/{$this->getRealClientParentO()->getPathFromName()}/";
		$url = "a=show&o=web&l[class]=ffile&l[nname]=/";
		$ilist['DocRoot'] = "_lxinurl:$url:/{$web->substr('docroot', 0, 32)}:";
		$resource = strtil($this->resourceplan_used, "___");
		
		if (check_if_many_server() && $login->isLte("reseller")) {
			$ilist['Web'] = $web->syncserver;
			$ilist['Mail'] = $mmail->syncserver;
		//	$ilist['Mysql'] = $this->mysqldbsyncserver;
			$ilist['Dns'] = $dns->syncserver;
		}
		
		return $ilist;
	}

	function createShowIlist()
	{
		$ilist[] = "ddate";
		
		return $ilist;
	}

	function updatePhpInfo($param)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$ar['ip_address'] = $gbl->c_session->ip_address;
		$ar['session'] = $gbl->c_session->tsessionid;
		rl_exec_get(null, $this->getObject('web')->syncserver, array("web", "createSession"), array($ar));
		$servar = base64_encode(serialize($ar));
		$gbl->__this_window_url = "http://$this->nname/__kloxo/phpinfo.php?session=$servar";
		
		return null;
	}

	function updateShow($subaction, $param)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$out = $this->getTrafficInfo();
		$ret['out'] = $out;
		$ret['url'] = $ghtml->getFullUrl("a=show");
		
		return $ret;
	}

	function createShowPropertyList(&$alist)
	{
		global $gbl, $sgbl, $login, $ghtml;
	/*
		if ($this->isDomainVirtual()) {
			$alist['property'][] = "o=mmail&a=list&c=mailaccount";
			$alist['property'][] = 'a=show&sa=config';
		}
	*/
		if ($ghtml->frm_subaction === 'limit') {
			$alist['property'][] = "a=updateform&sa=limit";
		} elseif ($ghtml->frm_subaction === 'changeowner') {
			$alist['property'][] = "a=updateform&sa=changeowner";
		} elseif ($ghtml->frm_subaction === 'preview_config') {
			$alist['property'][] = "a=updateform&sa=preview_config";
		} else {
			$alist['property'][] = "a=show";
		}

		return $alist;
	}

	static function consumeUnderParent()
	{
		return false;
	}

	function createShowActionList(&$alist)
	{
		$this->getToggleUrl($alist);
	}

	function createShowAlist(&$alist, $subaction = null)
	{
		global $gbl, $sgbl, $login, $ghtml;

		if (!$this->getParentO()->priv->isOn('webhosting_flag')) {
			$this->getShowActions($alist, 'mmail');
			return $alist;
		}

		$gen = $login->getObject('general')->generalmisc_b;
		$webstatsprog = $gen->webstatisticsprogram;
		
		if (!$webstatsprog) {
			$webstatsprog = "awstats";
		}

		if ($subaction === 'config') {
			return $this->createShowAlistConfig($alist);
		}
	/*
		if (!$this->isLogin()) {
			$alist['__title_admin'] = $login->getKeywordUc('administrative_actions');
		}
	
	*/

		// MR -- just a trick to find out not access to 'home' because need title for domain
		if ($login->getUrlIdentity() || !$login->isAdmin()) {
			// MR -- disable because want combine 'domain' and 'domain adm'
			$alist['__title_domain_administer'] = $login->getKeywordUc('domain');
		}

	//	$this->getLxclientActions($alist);

		$alist['__v_dialog_limit'] = "a=updateform&sa=limit";

	//	$alist['__title_next'] = $login->getKeywordUc('general');


	//	$alist['__v_dialog_info'] = 'a=updateform&sa=information';
	//	$alist['__v_dialog_pass'] = 'a=updateform&sa=password';

		$web = $this->getObject('web');
		$alist[] = 'a=list&c=addondomain';

	//	$alist['__var_backup_flag'] = "a=show&o=lxbackup";
	//	$alist[] = "a=list&c=mysqldb";
	//	$this->getListActions($alist, 'mssqldb');
	/*
		if (check_if_many_server()) {
		//	if (count($this->getParentO()->listpriv->mysqldbpserver_list) > 1) {
				$alist[] = "a=updateform&sa=ddatabasepserver";
		//	}
		}
	*/
		if ($login->isLteAdmin() || $login->priv->isOn('dns_manage_flag')) {
			$alist[] = 'a=show&o=dns';
		}

	//	$alist['__title_web'] = $this->getTitleWithSync('web');

		$alist['__title_domain_log'] = $login->getKeywordUc('trafficandlog');
		$alist['__v_dialog_stat'] = "n=web&a=updateform&sa=stats_protect";
		$tmpurl = "n=web&a=show&l[class]=ffile&l[nname]=__lx_error_log";
		$alist[] = create_simpleObject(array('url' => "$tmpurl", 'purl' => "o=web&a=updateform&sa=error_log", 'target' => "", '__internal' => true));
		$tmpurl = "n=web&a=show&l[class]=ffile&l[nname]=__lx_access_log";
		$alist[] = create_simpleObject(array('url' => "$tmpurl", 'purl' => "o=web&a=updateform&sa=access_log", 'target' => "", '__internal' => true));

		$tmpurl = "n=web&a=show&l[class]=ffile&l[nname]=__lx_php_log";
		$alist[] = create_simpleObject(array('url' => "$tmpurl", 'purl' => "o=web&a=updateform&sa=php_log", 'target' => "", '__internal' => true));

		if ($webstatsprog === 'awstats') {
			$alist[] = create_simpleObject(array('url' => "http://$this->nname/awstats/awstats.pl?config=$this->nname", 'purl' => 'c=domain&a=updateform&sa=show_awstats', 'target' => "target='_blank'"));
		} else {
			if ($webstatsprog === 'webalizer') {
				$alist[] = create_simpleObject(array('url' => "http://$this->nname/stats/", 'purl' => 'c=domain&a=updateform&sa=show_stats', 'target' => "target='_blank'"));
			}
		}


	//	$alist[] = "n=web&a=graph&sa=webtraffic";
		$alist[] = 'a=list&c=domaintraffichistory';
		$alist[] = "n=web&a=list&c=weblastvisit";
		$alist[] = "n=web&a=updateform&sa=statsconfig";
		
		if ($login->priv->isOn('runstats_flag') && $this->priv->isOn('awstats_flag')) {
			$alist[] = "n=web&a=updateform&sa=run_stats";
		}
		
	//	$alist[] = "n=web&a=updateform&sa=sesubmit";


		$alist['__title_domain_classweb'] = $web->getTitleWithSync();
	//	$alist['__title_domain_classweb'] = 'web';

/*
		// MR -- not work under client except admin
		$plist = $login->getList('pserver');

		foreach ($plist as $server) {
			if ($server->nname === $this->syncserver) {
				$php = $server->getObject('phpini');
				break;
			}
		}
*/
	//	$php = $this->getParentO()->getObject('phpini');

	//	if ($php->phpini_flag_b->multiple_php_flag === 'on') {
	//	if (file_exists('../etc/flag/enablemultiplephp.flg')) {
		//	$alist['__v_dialog_phpini'] = "n=web&o=phpini&a=show";
		//	$alist['__v_dialog_webselector'] = "n=web&a=updateform&sa=webselector";
			$alist['__v_dialog_webfeatures'] = "n=web&a=updateform&sa=webfeatures";
	//	}

		$alist['__v_dialog_sslcert'] = "n=web&a=list&c=sslcert";


	//	$alist[] = "a=list&c=webindexdir_a";

		$alist[] = "n=web&a=list&c=dirprotect";
		$alist['__v_dialog_hotlink'] = "n=web&a=updateform&sa=hotlink_protection";
		$alist['__v_dialog_ipblock'] = "n=web&a=updateform&sa=blockip";
		
		if ($login->priv->isOn('document_root_flag')) {
			$alist[''] = "n=web&a=updateform&sa=docroot";
		}
		
		$alist['__v_dialog_misc'] = "n=web&a=updateform&sa=configure_misc";
		$alist['__v_dialog_dirin'] = "n=web&a=updateform&sa=dirindex";
		$alist[] = "n=web&a=show&l[class]=ffile&l[nname]=/";
		$driverapp = $gbl->getSyncClass($this->__masterserver, $this->syncserver, 'ftpuser');
	//	$alist[] = "n=web&a=list&c=ftpuser";
	//	$alist[] = 'n=web&a=list&c=ftpsession';
	//	$alist[] = "n=web&a=list&c=davuser";


	//	$web->getSwitchServerUrl($alist);

	//	$alist[] = "a=updateform&sa=ipaddress";

		// MR -- disable because want combine 'domain' and 'domain adm'
	//	$alist['__title_script'] = $login->getKeywordUc('script');


	//	$alist[] = create_simpleObject(array('url' => "http://$this->nname/__kloxo/phpinfo.php?session=$servar", 'purl' => 'n=web&a=updateform&sa=phpinfo', 'target' => "target='_blank'")); 
		
		$alist[] = "n=web&a=update&sa=phpinfo";

	/*
		// MR -- disable because change to user-level php.ini
		$alist['__v_dialog_phpini'] = "n=web&o=phpini&a=show";
		$alist['__v_dialog_phpiniadv'] = "n=web&o=phpini&a=updateform&sa=extraedit";
	*/

	/*
		// MR -- TODO: change to user-level for client! (not admin)
		$alist[] = create_simpleObject(array('url' => "/display.php?frm_action=show&" .
			"frm_o_o[0][class]=client&frm_o_o[0][nname]={$this->getParentName()}&frm_o_o[1][class]=phpini",
			'purl' => "a=show&o=phpini", 'target' => "target='_self'"));
	*/
		if ($login->isAdmin()) {
		//	if ($web->__driverappclass === 'lighttpd') {
			//	if (is_centosfive()) {
					$alist[] = "n=web&a=list&c=rubyrails";
			//	}
		//	}
		}

	/*
		// MR -- permalink and rewrite rule disabling on 6.2.x because wrong code will be make webserver
		// can't start ; just enough modified custom template by admin also fcgi_config

		if ($web->__driverappclass === 'lighttpd') {
			$alist['__v_dialog_perma'] = "n=web&a=updateform&sa=permalink";
			$alist['__v_dialog_fcgi'] = "n=web&a=updateform&sa=fcgi_config";
			if ($login->isAdmin()) {
				$alist['__v_dialog_lightyr'] = "n=web&a=updateform&sa=lighty_rewrite";
			}
		}
	*/
		$alist['__v_dialog_comp'] = "n=web&a=list&c=component";

		if (!$gen->isOn('disableinstallapp') && $this->getClientParentO()->priv->isOn('installapp_flag')) {
			$alist[] = "n=web&a=show&k[class]=allinstallapp&k[nname]=installapp";
		}
	/*
		$alist['action'][] = "a=update&sa=backup";
		$alist['action'][] = "a=updateform&sa=restore";
	*/

	//	$this->getChildShowActions($alist);

		$alist['__title_domain_extra'] = $login->getKeywordUc('extra');

		$tmpurl = "n=web&a=show&l[class]=ffile&l[nname]=/";
		$alist[] = create_simpleObject(array('url' => "$tmpurl", 'purl' => "o=web&a=updateform&sa=image_manager", 
				'target' => "", '__internal' => true));
	/*
		// MR -- no need/conflict for template-based config	
		if ($login->isAdmin()) {
			$alist[] = "n=web&a=updateform&sa=extra_tag";
		}
	*/
		$alist[] = "n=web&a=list&c=server_alias_a";
		
	//	if ($web->__driverappclass !== 'lighttpd') {
			$alist[] = "n=web&a=list&c=webhandler";
			$alist[] = "n=web&a=list&c=webmimetype";
	//	}
		
		$alist[] = "n=web&a=list&c=redirect_a";
		$alist['__v_dialog_error'] = "n=web&a=updateform&sa=custom_error";
	/*
		if ($this->getClientParentO()->priv->isOn('cron_manage_flag')) {
			if ($this->getObject('web')->__driverappclass === 'apache' || $this->getObject('web')->__driverappclass === 'lighttpd') {
				$alist[] = "n=web&a=list&c=cron";
			}
		}
	*/

		if ($login->isNotCustomer()) {
			// Disabling change owner for the present.
			$alist[] = "a=updateform&sa=changeowner";
		}
		
	//	$alist[] = "a=list&c=subweb_a";
	//	$alist[] = $this->getStubUrl('__stub_domain_view_url');

	//	if ($this->previewdomain) {
	//		$alist[] = create_simpleObject(array('url' => "http://$this->nname.$this->previewdomain", 'purl' => 'c=domain&a=updateform&sa=site_preview', 'target' => "target='_blank'"));
	//	} else {
			$alist[] = create_simpleObject(array('url' => "/sitepreview/$this->nname/", 'purl' => 'c=domain&a=updateform&sa=site_preview', 'target' => "target='_blank'"));
	//	}

		if ($login->isAdmin()) {
			$alist[] = "a=updateform&sa=preview_config";
		}

		$alist[] = create_simpleObject(array('url' => "http://intodns.com/$this->nname", 'purl' => 'c=domain&a=updateform&sa=check_dns', 'target' => "target='_blank'"));

	//	$alist['__title_ddatabase'] = "Database";

	/*
		$alist['__title_misc'] = "Misc";
		$this->getListActions($alist, 'utmp');
		$this->getListActions($alist, 'ticket');
		$alist[] = "a=list&c=smessage";
	*/

		$this->getShowActions($alist, 'mmail');

		return $alist;
	}


	static function get_full_alist()
	{
		global $gbl, $sgbl, $login, $ghtml;

		$alist['__title_main'] = $login->getKeywordUc('resource');

	//	$this->getLxclientActions($alist);

		$alist[] = "a=updateform&sa=limit";
	//	$alist[] = "a=updateform&sa=change_plan";

	//	$alist['__title_next'] = $login->getKeywordUc('general');

		$alist['__var_backup_flag'] = "a=show&o=lxbackup";

		$alist[] = 'a=updateform&sa=information';
		$alist[] = 'a=updateform&sa=password';
		$alist[] = 'a=list&c=addondomain';

	//	$alist[] = "a=list&c=mysqldb";
	//	$this->getListActions($alist, 'mssqldb');
		
		if (check_if_many_server()) {
		//	if (count($this->getParentO()->listpriv->mysqldbpserver_list) > 1) {
				$alist[] = "a=updateform&sa=ddatabasepserver";
		//	}
		}

		if ($login->isAdmin() || $login->priv->isON('can_manage_dns')) {
			$alist[] = 'a=show&o=dns';
		}

	//	$alist['__title_web'] = $this->getTitleWithSync('web');

		$alist['__title_log'] = $login->getKeywordUc('trafficandlog');
		$alist[] = "n=web&a=updateform&sa=stats_protect";
		$tmpurl = "n=web&a=show&l[class]=ffile&l[nname]=__lx_error_log";
		$alist[] = create_simpleObject(array('url' => "$tmpurl", 'purl' => "o=web&a=updateform&sa=error_log", 
				'target' => "", '__internal' => true));
		$tmpurl = "n=web&a=show&l[class]=ffile&l[nname]=__lx_access_log";
		$alist[] = create_simpleObject(array('url' => "$tmpurl", 'purl' => "o=web&a=updateform&sa=access_log", 
				'target' => "", '__internal' => true));

		$alist[] = create_simpleObject(array('url' => "http://nname/stats/", 'purl' => 'a=updateform&sa=show_stats', 
				'target' => "target='_blank'"));
		$alist[] = "n=web&a=graph&sa=webtraffic";
		$alist[] = 'a=list&c=domaintraffichistory';
		$alist[] = "n=web&a=list&c=weblastvisit";

		$alist['__title_extra'] = $login->getKeywordUc('extra');
		$alist[] = "n=web&a=list&c=server_alias_a";
		$alist[] = "n=web&a=list&c=redirect_a";
		$alist[] = "n=web&a=updateform&sa=configure_misc";
		$alist[] = "n=web&a=updateform&sa=custom_error";
		$alist[] = "n=web&a=list&c=cron";

		$alist[] = "a=list&c=subweb_a";
		$alist[] = create_simpleObject(array('url' => "http://[%s]", 'purl' => 'c=domain&a=updateform&sa=view', 'target' => "target=_blank"));
		$alist[] = create_simpleObject(array('url' => "/sitepreview/nname/", 'purl' => 'a=updateform&sa=site_preview', 'target' => "target='_blank'"));

		self::get_child_full_alist($alist, "web");
		self::get_child_full_alist($alist, "mmail");
		
		return $alist;
	}


	function createShowUpdateform()
	{
		$uflist = null;
		if (!$this->isDomainVirtual()) {
			$uflist['redirect_domain'] = null;
		}
		return $uflist;
	}

	function hasFunctions()
	{
		return true;
	}

	function createShowAlistConfig(&$alist, $subaction = null)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$alist['__title_advanced'] = $login->getKeywordUc('advanced');

		if (!$this->isLogin()) {
		//	$alist['__v_dialog_dis'] = "a=updateform&sa=disable_per";
		}


		if (!$this->isLogin()) {
			if ($login->isNotCustomer()) {
				// Disabling change owner for the present.
				$alist[] = "a=updateform&sa=changeowner";
			}
		}

		if ($this->isLogin()) {
			$alist['__v_dialog_login'] = "o=sp_specialplay&a=updateform&sa=login_options";
		}

		if ($login->isAdmin()) {
		//	$alist[] = "a=updateform&sa=fix_openbasedir";
		}

		return $alist;
	}

	function getResourceChildList()
	{
		global $gbl, $sgbl, $login, $ghtml;

		$list = $this->getChildListFilter('R');
		
		if (!$this->isDomainVirtual()) {
			return null;
		}
		
		if (!$login->priv->isOn('dns_manage_flag')) {
			unset($list['dns_o']);
		}
		
		return $list;
	}


	function isDomainVirtual()
	{
		return ($this->ttype === 'virtual');
	}

	function isSync()
	{
		return false;
	}
}


class subdomain extends Domaind
{
	static $__desc = array("", "", "subdomain");
	static $__desc_nname = array("n", "", "subdomain_name", URL_SHOW);

	static function addform($parent, $class, $typetd = null)
	{
		$dir = "__path_customer_root/{$parent->getPathFromName()}";
		$dir = expand_real_root($dir);
		$vv = array('var' => "subdomain_parent", 'val' => array('s', get_namelist_from_objectlist($parent->getList('domain'))));
		$vlist['nname'] = array('m', array('posttext' => ".", 'postvar' => $vv));

		if ($parent->priv->isOn('document_root_flag')) {
			$vlist['docroot'] = array('m', array('pretext' => "$dir/"));
		}

		$ret['variable'] = $vlist;
		$ret['action'] = 'add';
		return $ret;
	}

	static function addCommand($parent, $class, $p)
	{
		return lxclass::addCommand($parent, $class, $p);
	}

	static function AddListForm($parent, $class)
	{
	/*
		global $gbl, $sgbl, $login, $ghtml;


		$vlist['nname'] = null;
		$vlist['text'] = array("M", ".");
		$list = get_namelist_from_objectlist($parent->getVirtualList('domain', $count));
		
		if (!$list) {
			throw new lxException($login->getThrow("no_maindomain"));
		}
		
		$vlist['subdomain_parent'] = array('s', $list);
		
		if ($parent->priv->isOn('document_root_flag')) {
			$vlist['docroot'] = null;
		}
		
		return $vlist;
	*/
		return self::addform($parent, $class);
	}

	static function initThisListRule($parent, $class)
	{
		$rule[] = array("parent_clname", "=", "'{$parent->getClName()}'");
		$rule[] = "AND";
		$rule[] = array("dtype", "=", "'subdomain'");
		
		return $rule;
	}
}

// The table should be the class that encompasses all its variations. 
// This is a must. Otherwise, it leads to headaches.
class domain extends Domaind
{
	static $__desc = array("", "", "Domain");
	static $__desc_nname = array("n", "", "domain_name", URL_SHOW);

	function getResourceIdentity()
	{
		if ($this->dtype && $this->dtype === 'subdomain') {
			return 'subdomain';
		}

		return 'maindomain';
	}

	static function getquotaclass($class)
	{
		return 'maindomain';
	}

}

class domaina extends Domaind
{
	static $__desc = array("", "", "Domain");
	static $__desc_nname = array("n", "", "domain_name", URL_SHOW);

	function getResourceIdentity()
	{
		if ($this->dtype && $this->dtype === 'subdomain') {
			return 'subdomain';
		}

		return 'domain';
	}

	static function getquotaclass($class)
	{
		return 'domain';
	}
}

class maindomain extends Domaind
{
	static $__desc = array("", "", "maindomain");
	static $__desc_nname = array("n", "", "domain_name", URL_SHOW);

	static function AddListForm($parent, $class)
	{
		return null;
	}

	static function initThisListRule($parent, $class)
	{
		$rule[] = array("parent_clname", "=", "'{$parent->getClName()}'");
		$rule[] = "AND";
		$rule[] = array("dtype", "=", "'maindomain'");
		return $rule;
	}
}


class all_domain extends domaind
{
	static $__desc = array("", "", "all_domain");
	static $__desc_nname = array("n", "", "domain_name", URL_SHOW);

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

		if ($parent->isGte('customer')) {
			throw new lxException($login->getThrow("only_reseller_and_admin"));
		}

		if ($parent->isAdmin()) {
			return "__v_table";
		} else {
			return array('parent_cmlist', "LIKE", "'%,{$parent->getClName()},%'");
		}
	}

	static function createListAlist($parent, $class)
	{
	//	$alist[] = "a=show";

		if ($parent->isAdmin()) {
			$alist[] = "a=list&c=all_domain";
			$alist[] = "a=list&c=all_addondomain";
			$alist[] = "a=list&c=all_mailaccount";
			$alist[] = "a=list&c=all_mailforward";
			$alist[] = "a=list&c=all_mailinglist";
			$alist[] = "a=list&c=all_mysqldb";
			$alist[] = "a=list&c=all_cron";
			$alist[] = "a=list&c=all_ftpuser";
			$alist[] = "a=list&c=all_sslcert";
		} else {
			if ($parent->isLte('reseller')) {
				$alist[] = "a=list&c=all_domain";
			}
		}

		return $alist;
	}

	static function createListSlist($parent)
	{
		$nlist['nname'] = null;
		$nlist['parent_clname'] = null;
		$nlist['dtype'] = array('s', array('--any--', 'maindomain', 'subdomain'));

		if (check_if_many_server()) {
			$sql = new Sqlite($parent->__masterserver, "pserver");
			$res = $sql->getTable(array('nname'));
			$rs = get_namelist_from_arraylist($res);
			$rs = lx_array_merge(array(array('--any--'), $rs));
			$nlist['webpserver'] = array('s', $rs);
			$nlist['mmailpserver'] = array('s', $rs);
			$nlist['dnspserver'] = array('s', $rs);
		}
		
		return $nlist;
	}

}

class all_domaina extends all_domain
{
}

