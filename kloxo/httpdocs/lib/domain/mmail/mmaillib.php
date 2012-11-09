<?php

class Mmail extends Lxdb
{
// Core
	static $__desc = array("", "", "mail_");

//Data
	static $__desc_catchall = array("", "", "catchall_account");
//static $__desc_autoresponder_num  	 = array("q", "",  "number_of_autoresponders");
	static $__desc_mx_f = array("n", "", "MX_record");
	static $__desc_syncserver = array("sd", "", "mail_server");
	static $__desc_maildisk_usage = array("q", "", "mail_disk_usage");
	static $__desc_mailaccount_num = array("q", "", "number_of_mail_account");
	static $__desc_nname = array("", "", "domain");
	static $__desc_password = array("", "", "");
	static $__desc_redirect_domain = array("", "", "Redirect_to_Mail_Domain");
	static $__desc_status = array("e", "", "s:status");
	static $__desc_mx_record = array("e", "", "mx_record");
	static $__desc_status_v_on = array("", "", "enabled");
	static $__desc_webmailprog = array("", "", "webmail_application");
	static $__desc_exclude_all = array("", "", "exclude_all_others");
	static $__desc_domainkey_f = array("", "", "domainkeys");
	static $__desc_status_v_off = array("", "", "disabled");
	static $__desc_mailinglist_num = array("q", "", "number_of_mailing_lists");
	static $__desc_logo_manage_flag = array("q", "", "can_change_logo");
	static $__desc_remotelocalflag = array("", "", "mail_hosted_remotely");
	static $__desc_webmail_url = array("", "", "webmail_url");
	static $__desc_enable_spf_flag = array("f", "", "enable_SPF");
	static $__desc_text_spf_domain = array("t", "", "additional_domain_(one_per_line)");
	static $__desc_text_spf_ip = array("t", "", "additional_IP(one_per_line)");

// Objects
	static $__desc_spam_o = array("db", "", "");
	static $__desc_mailinglist_l = array("qdb", "", "");

// Lists
	static $__desc_mailaccount_l = array("dqb", "", "");
	static $__desc_mailforward_l = array("db", "", "");
	static $__acdesc_update_spam = array("", "", "spam_config");
	static $__acdesc_update_remotelocalmail = array("", "", "remote_mail");
	static $__acdesc_graph_mailtraffic = array("", "", "mail_traffic");
	static $__acdesc_update_catchall = array("", "", "configure_catchall");
	static $__acdesc_update_editmx = array("", "", "edit_MX");
	static $__acdesc_update_authentication = array("", "", "email_auth");
	static $__acdesc_update_webmail_select = array("", "", "webmail_application");
	static $__acdesc_update_redirect_domain = array("", "", "Redirect Mail Domain");
	static $__acdesc_show = array("", "", "mail");

	function createExtraVariables()
	{
		if ($this->ttype === 'forward') {
			return;
		}

		$this->__var_addonlist = $this->getParentO()->getList('addondomain');

		$spam = $this->getObject('spam');
		$this->__var_spam_status = $spam->status;
		$master = null;

		if ($this->dbaction === 'add' || $this->dbaction === 'syncadd') {
			try {
				$master = $this->getFromList('mailaccount', "postmaster@$this->nname");
			} catch (exception $e) {
				$this->__var_password = "hello";
			}
			if ($master) {
				$this->__var_password = $master->realpass;
			}
		}

		if (!$this->systemuser) {
			$dom = $this->getParentO();
			$web = $dom->getObject('web');
			$this->systemuser = $web->username;
		}

		if (cse($this->subaction, 'backup')) {
			$this->createMailaccountList();
		}
	}

	function createGraphList()
	{
		$alist[] = "a=graph&sa=mailtraffic";

		return $alist;
	}

	function inheritSynserverFromParent()
	{
		return false;
	}

	function extraBackup()
	{
		return true;
	}

	function createMailaccountList()
	{
		$mlist = $this->getList('mailaccount');
		$nmlist = get_namelist_from_objectlist($mlist);

		foreach ($nmlist as &$__nm) {
			$tmp = explode("@", $__nm);
			$__nm = $tmp[0];
		}

		$this->__var_accountlist = $nmlist;
	}

	function mailtemplateUpdate($result)
	{
		$this->quota = $result['quota'];
		$this->maxpopaccounts = $result['maxpopaccounts'];
		$this->dbaction = "update";
	}

	function isBounce($var)
	{
		return (strtolower($this->$var) === '--bounce--');
	}

	function createShowClist($subaction)
	{
		if ($this->ttype === 'forward') {
			//	return null;
		}

		if ($this->remotelocalflag === 'remote') {
			//	return null;
		}

		return null;

		//	$clist['mailaccount'] = null;

		//	return $clist;
	}


	function createShowRlist($subaction)
	{
		return null;

		//	$rlist['priv'] = null;
		//	return $rlist;

	}

	static function createListIlist()
	{
		$ilist["nname"] = "100%";
		$ilist["quota"] = "7%";

		return $ilist;
	}

	function getSpecialParentClass()
	{
		return 'domain';
	}

	function getQuotamail_usage()
	{
		return null;
	}

	function getShowInfo()
	{
		return "Catchall: $this->catchall;";
	}

	function toggleforwardStatus($forward_status = NULL)
	{
		if ($forward_status) {
			$this->forward_status = $forward_status;
		} else {
			$this->forward_status = ($this->forward_status === "on") ? "off" : "on";
		}

		$this->dbaction = "update";
		$this->subaction = "toggle_status";
	}

	function makeDnsChanges($newserver)
	{
		$ip = getOneIPForServer($newserver);
		$dns = $this->getParentO()->getObject('dns');

		$dns->dns_record_a['a_mail']->param = $ip;
		$dns->setUpdateSubaction('subdomain');
		$dns->was();
		$domain = $this->getParentO();

		$var = "mmailpserver";
		$domain->$var = $newserver;
		$domain->setUpdateSubaction();
		$domain->write();
	}

	function updateEditMX($param)
	{
		$dns = $this->getParentO()->getObject('dns');
		$rec = $dns->dns_record_a;

		if (!isset($rec['mx_10'])) {
			$mxrec = new dns_record_a(null, null, 'mx_10');
			$mxrec->hostname = $this->nname;
			$mxrec->ttype = 'mx';
			$mxrec->priority = '10';
		} else {
			$mxrec = $rec['mx_10'];
		}

		$mxrec->param = $param['mx_f'];
		$dns->dns_record_a['mx_10'] = $mxrec;
		$dns->setUpdateSubaction('full_update');

		// MR -- must be return null
		return null;
	}

	function updateRedirect_Domain($param)
	{
		if ($this->ttype !== 'forward') {
			$this->ttype = 'forward';
		}

		return $param;
	}

	function updateauthentication($param)
	{
		$dns = $this->getParentO()->getObject('dns');
	//	$rec = $dns->dns_record_a;
		$this->__t_var_f = $param['enable_spf_flag'];

		if ($param['exclude_all'] == 'soft') {
			$all = "~all";
		} else {
			$all = "-all";
		}

		$an = null;
		$spfdomain = trim($param['text_spf_domain']);

		if ($spfdomain) {
			$v = explode("\n", $spfdomain);
			foreach ($v as $d) {
				$d = trim($d);
				$an .= " a:$d";
			}
		}

		$spfip = trim($param['text_spf_ip']);

		if ($spfip) {
			$v = explode("\n", $spfip);
			foreach ($v as $d) {
				$d = trim($d);
				$an .= " ip4:$d";
			}
		}

		$nn = "txt__base";

		if ($this->isOn('__t_var_f')) {
			$nrc = new dns_record_a(null, null, $nn);
			$nrc->ttype = "txt";
			$nrc->hostname = "__base__";
			$nrc->param = "v=spf1 a mx $an $all";
			$dns->dns_record_a[$nn] = $nrc;
		} else {
			unset($dns->dns_record_a[$nn]);
		}

		$dns->setUpdateSubaction('full_update');

		return $param;
	}

	function updateform($subaction, $param)
	{
		switch ($subaction) {

			case "editmx":
				$v = null;

				$rec = $this->getParentO()->getObject('dns')->dns_record_a;

				foreach ($rec as $a) {
					if ($a->ttype === 'mx') {
						$v = $a->param;
						continue;
					}
				}

				$vlist['nname'] = array('M', null);
				$vlist['mx_f'] = array('m', $v);
				$vlist['__v_updateall_button'] = array();

				return $vlist;

			case "authentication":
				$domkey = db_get_value("servermail", $this->syncserver, "domainkey_flag");

				if (!$domkey) {
					$domkey = 'off';
				}

				$domkey .= " (Server Wide Value)";
				$vlist['domainkey_f'] = array('M', $domkey);
				$vlist['enable_spf_flag'] = null;
				$vlist['text_spf_domain'] = null;
				$vlist['text_spf_ip'] = null;
				$this->setDefaultValue('exclude_all', 'soft');
				$vlist['exclude_all'] = array('s', array('soft', 'hard'));
				$vlist['__v_updateall_button'] = array();

				return $vlist;

			case "webmail_select":

			//	$this->fixWebmailRedirect();

				$this->setDefaultValue('webmailprog', '--system-default--');
				$base = "/home/kloxo/httpd/webmail/";
			//	$list = lscandir_without_dot_or_underscore($base);
				$nlist[] = '--system-default--';
				$nlist[] = '--chooser--';
				$nlist = add_disabled($nlist);
				$plist = self::getWebmailProgList();
				$nlist = lx_merge_good($nlist, $plist);
				$vlist['webmailprog'] = array('s', $nlist);
				$vlist['__v_updateall_button'] = array();

				return $vlist;

			case "catchall":
				$list = $this->getList('mailaccount');
				$name[] = "--bounce--";
				$name[] = "Delete";
				$nn = get_namelist_from_objectlist($list);

				foreach ($nn as &$___t) {
					$tmp = explode("@", $___t);
					$___t = $tmp[0];
				}

				$name = lx_array_merge(array($name, $nn));
				$vlist['catchall'] = array('s', $name);
				$vlist['__v_updateall_button'] = array();

				return $vlist;

			case "remotelocalmail":

			//	$this->fixWebmailRedirect();

				$vlist['remotelocalflag'] = array('s', array('local', 'remote'));
				$vlist['webmail_url'] = null;
				$vlist['__v_updateall_button'] = array();

				return $vlist;
		}

		// MR -- this is for what?. Nothing useless?

	//	return parent::updateform($subaction, $param);

	}


	static function getWebmailProgList()
	{
		$plist = lscandir_without_dot_or_underscore("__path_kloxo_httpd_root/webmail");

		foreach ($plist as $k => $v) {
			if ($v === 'img') {
				unset($plist[$k]);
			}

			if ($v === 'images') {
				unset($plist[$k]);
			}

			if ($v === 'disabled') {
				unset($plist[$k]);
			}

			if (!lis_dir("__path_kloxo_httpd_root/webmail/$v")) {
				unset($plist[$k]);
			}
		}

		return $plist;
	}

	function createShowPropertyList(&$alist)
	{
		$this->getParentO()->getObject('web')->createShowPropertyList($alist);
	}

	function postUpdate()
	{
		// MR -- still have a problem - need click 2 times for 'update'/'update all' button
		// just single make kloxo recreate webserver config with reference to 'old' state!

		if ($this->subaction === 'remotelocalmail' || $this->subaction === 'webmail_select') {
			$this->fixWebmailRedirect();
		}
	}

	function getFfileFromVirtualList($name)
	{
		$name = coreFfile::getRealpath($name);
		$name = "/$name";
		$root = "__path_mail_root/domains/$this->nname/";

		$ffile = new Ffile($this->__masterserver, $this->syncserver, $root, $name, $this->username);
		$ffile->__parent_o = $this;
		$ffile->get();
		$ffile->readonly = 'on';
		return $ffile;
	}

	function fixWebmailRedirect()
	{
/*
	//	global $gbl, $sgbl, $login, $ghtml;

		// the same trick with createListSlist()
		// on /usr/local/lxlabs/kloxo/httpdocs/lib/domain/addondomainlib.php

		$web = $this->getParentO()->getObject('web');

		// have trouble when use addondomain, so use full_update
		$web->setUpdateSubaction('full_update');

	//	$web->setUpdateSubaction('addondomain');
*/
		$scripting = '/usr/local/lxlabs/kloxo/bin/fix/fixweb.php';

		lxshell_return("lxphp.exe", $scripting, "--nolog");

		// MR -- must be return null to prevent blank page
		return null;

	}


	function createShowAlist(&$alist, $subaction = null)
	{
		global $login;

		if ($this->ttype === 'forward') {
			return $alist;
		}

		$alist['__title_classmmail'] = $this->getTitleWithSync();

		/*
		  if ($this->remotelocalflag === 'remote') {
			  $alist[] =  "a=updateform&sa=remotelocalmail";
			  $url = $this->webmail_url;
			  $url = add_http_if_not_exist($url);
			  $alist[] = create_simpleObject(array( 'url' => $url, 'purl' => "a=updateform&sa=webmail&c=mailaccount", "target"=> 'target=_blank'));
			  return $alist;
		  } else {
		  }
	 */
		//	$alist[] =  "a=show&o=spam";

		/*
		  $alist['action'][] = "a=update&sa=backup";
		  $alist['action'][] = "a=updateform&sa=restore";
	 */
		$alist[] = "a=list&c=mailforward";
		$alist['__v_dialog_ct'] = "a=updateform&sa=catchall";
		$alist['__v_dialog_remote'] = "a=updateform&sa=remotelocalmail";
		//	$alist[] =  "a=show&l[class]=ffile&l[nname]=/";
		$alist[] = "a=list&c=mailinglist";
		$alist['__v_dialog_spam'] = "o=spam&a=updateform&sa=update";

		if ($login->isAdmin() || $login->priv->isOn('dns_manage_flag')) {
			$alist['__v_dialog_editmx'] = "a=updateform&sa=editmx";
		}

		$alist['__v_dialog_auth'] = "a=updateform&sa=authentication";
		//	$alist[] = "a=graph&sa=mailtraffic";
		//	$alist[] = create_simpleObject(array( 'url' => "http://webmail.$this->nname", 'purl' => "a=updateform&sa=webmail&c=mailaccount", "target"=> 'target=_blank'));
		$alist['__v_dialog_webm'] = "a=updateform&sa=webmail_select";
		$alist[] = "a=list&c=mailaccount";
		$alist[] = "a=addform&c=mailaccount";

		return $alist;
	}

	function isDomainVirtual()
	{
		return ($this->ttype === 'virtual');
	}
}
