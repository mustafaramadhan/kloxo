<?php

abstract class ClientCore extends Resourcecore
{

	static $__desc_ddate = array("", "",  "date:date_of_registration");

	static $__desc_lic_client_num_f = array("","",  "number_of_clients");
	static $__desc_lic_pserver_num_f = array("","",  "number_of_servers");
	static $__desc_lic_domain_num_f = array("","",  "number_of_domains");
	static $__desc_lic_expiry_date_f = array("","",  "expiry_date");
	static $__desc_lic_live_support_f = array("","",  "live_support");
	static $__desc_lic_ipaddress_f = array("","",  "ip_address");
	static $__desc_lic_client_f = array("","",  "client_support");
	static $__desc_lic_node_num_f = array("", "",  "number_of_nodes");
	static $__desc_license_upload_f = array("F","",  "upload_new_license");
	static $__desc_lic_current_f = array("t","",  "current_license_string");
	static $__desc_pserver_delete_f = array("","",  "server_to_delete");
	static $__desc_vps_delete_f = array("","",  "orphaned_VM_to_delete");
	static $__desc_newdnstemplate = array("","",  "dnstemplate");
	static $__desc_websyncserver = array("","",  "web_server");
	static $__desc_mmailsyncserver = array("","",  "mail_server");
	static $__desc_mysqldbsyncserver = array("","",  "mysql_server");
	static $__desc_openvzostemplate_list = array("", "",  "openvz_template_list");
	static $__desc_xenostemplate_list = array("", "",  "xen_template_list");
	static $__desc_disable_admin = array("f", "",  "disable_admin_login");
	static $__desc_dnssyncserver_list = array("","",  "dns_servers");
	static $__desc_cron_mailto = array("", "",  "mail_to");
	static $__desc_disable_system_flag = array("f", "",  "completely_disable_system_access_to_this_user");

	static $__acdesc_update_ostemplatelist = array("","",  "ostemplate_list");

	//  Objects
	static $__acdesc_update_pserver = array("","",  "server_pool");
	static $__acdesc_update_ipaddress = array("","",  "ip_pool");
	static $__acdesc_update_changeowner = array("", "",  "change_owner");
	static $__acdesc_update_description = array("", "",  "information");
	static $__acdesc_update_license = array("","",  "license");
	static $__acdesc_update_disable_url = array("","",  "disable_url");
	static $__acdesc_update_skeleton = array("","",  "skeleton");
	static $__acdesc_update_forcedeletepserver = array("","",  "force_delete_server");
	static $__acdesc_update_generate_csr = array("","",  "generate_csr");
	static $__acdesc_update_deleteorphanedvps = array("","",  "delete_orphaned_VM");
	static $__acdesc_update_dnstemplatelist = array("","",  "dns_template_pool");
	static $__acdesc_update_domainpserver = array("","",  "Servers");
	static $__acdesc_update_pserver_s = array("","",  "server_pool");

	static $__desc_owner_f = array("ef", "",  "owner");
	static $__desc_owner_f_v_on = array("", "",  "you_are_the_owner_of_plan");
	static $__desc_owner_f_v_off = array("", "",  "you_are_the_owner_of_plan");

	function display($var)
	{
	/*
		if ($var === 'nname') {
			return ucfirst($this->$var);
		}
	*/
		if ($var === 'resourceplan_used_f') {
			return strtil($this->resourceplan_used, "___");
		}

		return parent::display($var);
	}

	static function continueFormlistpriv($parent, $class, $param, $continueaction)
	{
		global $login;

		$ret = exec_class_method('client', 'continueFormClientFinish', $parent, $class, $param, $continueaction);

		return $ret;

		$totallist = null;

		$array = client::getPserverListPriv();

		$listpriv = $parent->listpriv;
		$more = false;

		foreach($array as $a) {
			$list = $a . "_list";

			if (isset($listpriv->$list) && is_countable($listpriv->$list) && count($listpriv->$list) > 1) {
				$more = true;

				break;
			}
		}

		if ($more) {
			$vlist['server_detail_f'] = null;
			foreach($array as $a) {
				$v = "{$a}_list";

				if (!$parent->listpriv->$v) {
					throw new lxException($login->getThrow("no_server_pool"), '', $v);
				}

				$totallist = lx_merge_good($totallist, $parent->listpriv->$v);
				$vlist["{$a}_list"] = "";
			}
			$vlist['server_detail_f'] = array('M', pservercore::createServerInfo($totallist));
			$ret["param"] = $param;
			$ret["variable"] = $vlist;
			$ret["action"] = "add";
		//	$ret["continueaction"] = "clientfinish";
		} else {
			// All are $singstringle arrays, so just implode with "". the actually arrays are indexed u$singstring the name itself.
			foreach($array as $a) {
				$v = "{$a}_list";

				if (!$parent->listpriv->$v) {
					throw new lxException($login->getThrow("no_server_pool"), '', $v);
				}

				$param["listpriv_s_{$a}_list"] = implode("", $parent->listpriv->$v);
			}

		//	$param['listpriv_s_dbtype_list'] = implode($parent->listpriv->dbtype_list);
		}

		return $ret;
	}

	function updateForceDeletePserver($param)
	{
		if_not_admin_complain_and_exit();

		$servername = $param['pserver_delete_f'];
		$servo = $this->getFromList('pserver', $servername);
		$ol = $servo->getList('dbadmin');

		foreach($ol as $o) {
			$o->dbaction = 'delete';
			$o->write();
		}

		$servo->dbaction = 'delete';
		$servo->write();
		$this->pserver_l = null;
	}

	function updateDeleteOrphanedVps($param)
	{
		global $gbl, $sgbl, $login, $ghtml;

		if_not_admin_complain_and_exit();
		$this->loadAllObjects('vps');
		$v = $this->getFromList('vps', $param['vps_delete_f']);
		$v->dbaction = 'delete';
		$v->write();
		$this->vps_l = null;
	}

	function updateDomainPserver($param)
	{
		$list = $this->getList('domaina');

		$this->__old_websyncserver = $this->websyncserver;
		$this->__old_mmailsyncserver = $this->mmailsyncserver;
		$this->websyncserver = $param['websyncserver'];
		$this->mmailsyncserver = $param['mmailsyncserver'];

		$name = $this->getPathFromName();
		$this->setClientSyncServer();
		$this->setUpdateSubaction('createuser');
		$this->syncEntireObject();

		if ($this->__old_websyncserver !== $this->websyncserver) {
			$filepass = rl_exec_get(null, $this->__old_websyncserver, "cp_fileserv", array("__path_customer_root/$name"));
			rl_exec_get(null, $this->websyncserver, array("client__sync", "getFromRemote"), array($this->username,
					getFQDNforServer($this->__old_websyncserver), $filepass, "__path_customer_root", $name));
			rl_exec_get(null, $this->__old_websyncserver, "lxfile_rm_rec_content", array("__path_customer_root/$name"));
		}

		$cronlist = $this->getList('cron');

		foreach($cronlist as $cron) {
			$nparam['syncserver'] = $param['websyncserver'];

			if ($cron->syncserver === $nparam['syncserver']) { continue; }

			$cron->doupdateSwitchserver($nparam);
		}

		$ftplist = $this->getList('ftpuser');

		foreach($ftplist as $ftp) {
			$nparam['syncserver'] = $param['websyncserver'];

			if ($ftp->syncserver === $nparam['syncserver']) { continue; }

			$ftp->doupdateSwitchserver($nparam);
		}

		foreach($list as $l) {
			$web = $l->getObject('web');
			$nparam['syncserver'] = $param['websyncserver'];

			if ($web->syncserver === $nparam['syncserver']) { continue; }

			$web->doupdateSwitchserver($nparam);
		}

		foreach($list as $l) {
			$mmail = $l->getObject('mmail');
			$nparam['syncserver'] = $param['mmailsyncserver'];

			if ($mmail->syncserver === $nparam['syncserver']) { continue; }

			$mmail->doupdateSwitchserver($nparam);
		}

		$mysqldblist = $this->getList('mysqldb');
		$nparam['syncserver'] = $param['mysqldbsyncserver'];

		foreach($mysqldblist as $mysqldb) {
			if ($mysqldb->syncserver === $nparam['syncserver']) { continue; }

			$mysqldb->doupdateSwitchserver($nparam);
		}

		foreach($list as $l) {
			$mysqldblist = $l->getList('mysqldb');
			$nparam['syncserver'] = $param['mysqldbsyncserver'];

			foreach((array) $mysqldblist as $mysqldb) {
				if ($mysqldb->syncserver === $nparam['syncserver']) { continue; }

				$mysqldb->doupdateSwitchserver($nparam);
			}
		}

		$param['dnssyncserver_list'] = Client::fixListVariable($param['dnssyncserver_list']);

		foreach($list as $l) {
			$dns = $l->getObject('dns');

			if ($param['newdnstemplate'] !== '--leave--') {
				$dnstemplatename = $param['newdnstemplate'];
				$dnstemplate = new Dnstemplate(null, null, $dnstemplatename);
				$dnstemplate->get();
				$dns->copyObjectWithSave($dnstemplate);
			}

			$dns->syncserver = implode(",", $param['dnssyncserver_list']);
			$l->dnspserver = $dns->syncserver;
			$l->setUpdateSubaction();
			$l->write();
			$dns->setUpdateSubaction('syncadd');
			$dns->was();
		}

		return $param;
	}

	function updategenerate_csr($param)
	{
		global $login;

		$s = new sslcert(null, null, null);
	//	dprintr($param);

		$dn = array(
			"countryName" => $param['ssl_data_b_s_countryName_r'],
			"stateOrProvinceName" => $param['ssl_data_b_s_stateOrProvinceName_r'],
			"localityName" => $param['ssl_data_b_s_localityName_r'],
			"organizationName" => $param['ssl_data_b_s_organizationName_r'],
			"organizationalUnitName" => $param['ssl_data_b_s_organizationalUnitName_r'],
			"commonName" => $param['ssl_data_b_s_commonName_r'],
			"emailAddress" => $param['ssl_data_b_s_emailAddress_r']
		);

	//	$fp=@fopen("/home/root/nag.txt","w");

		$privkey = openssl_pkey_new();
		$csr = openssl_csr_new($dn, $privkey);
		$sscert = openssl_csr_sign($csr, null, $privkey, 365);
		openssl_csr_export($csr, $csrout);
		mail($this->contactemail, "Kloxo CSR", $csrout);
		openssl_x509_export($sscert, $certout);
		mail($this->contactemail, "cert", $certout);
		openssl_pkey_export($privkey, $pkeyout, null);
		mail($this->contactemail, "public-key", $pkeyout);

		throw new lxException($login->getThrow("csr_sent_to_email"));
	}

	static function getDomainServerVlist($parent, $obj, &$vlist)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$vlist['server_detail_f'] = null;
		$wlist = $parent->getServerList('web');
		$mlist = $parent->getServerList('mmail');
		$mylist = $parent->getServerList('mysqldb');
		$dnslist = $parent->getServerList('dns');

		if ($obj) { // This means we are switching as opposed to creating a new client.
			$obj->newdnstemplate = '--leave--';
			$dtlist = domainbase::getDnsTemplateList($login);
			$dtlist = lx_merge_good("--leave--", $dtlist);
			$vlist['newdnstemplate'] = array('s', $dtlist);
		}

		$vlist['websyncserver'] = array('s', $wlist);
		$vlist['mmailsyncserver'] = array('s', $mlist);
		$vlist['mysqldbsyncserver'] = array('s',$mylist);
		$vlist['dnssyncserver_list'] = array('U', $dnslist);
		$list = lx_merge_good($wlist, $mlist, $mylist, $dnslist);
		$sinfo = pservercore::createServerInfo($list);
		$sinfo = get_warning_for_server_info($parent, $sinfo);
		$vlist['server_detail_f'] = array('M', $sinfo);
	}

	function updateSearch_engine($param)
	{

	}

	function updateform($subaction, $param)
	{
		global $gbl, $sgbl, $login, $ghtml;

		switch($subaction) {
			case "installatron":
				$vlist['__v_button'] = array();

				return $vlist;

			case "ostemplatelist":
				getResourceOstemplate($vlist, 'all');
				$vlist['__v_updateall_button'] = array();

				return $vlist;

			case "createinvoice_m":
				$vlist['month'] = null;

				return $vlist;

			case "createinvoice_s":
				$vlist['month'] = array('s', $this->getInvoiceMonthList());

				return $vlist;

			case "search_engine":
				$vlist['se_url'] = array('M', "http://");
				$vlist['se_email'] = null;

				return $vlist;

			case "cron_mailto":
				$vlist['cron_mailto'] = null;

				return $vlist;

			case "default_domain":
				$vlist['default_domain'] = array('s', add_disabled(get_namelist_from_objectlist($this->getList('domain'))));

				return $vlist;

			case "shell_access":
				$res[] = "/bin/bash";
				$res[] = "/usr/bin/lxjailshell";
				$res = add_disabled($res);

				$vlist['nname'] = array('M', null);

				$this->setDefaultValue('shell', $res[0]);
				$this->setDefaultValue('disable_system_flag', 'off');

				if ($login->isAdmin()) {
					$vlist['disable_system_flag'] = null;

					$vlist['shell'] = array('s', $res);

					$vlist['__v_updateall_button'] = array();
				} else {
					$vlist['disable_system_flag'] = array('M', null);

					$vlist['shell'] = array('M', null);
				}

				return $vlist;

			case "generate_csr":
				include "lib/countrycode.inc";

				foreach($gl_country_code as $key=>$name ){
					$temp[$key] = $name;
				}

				$s = new sslcert(null, null, null);
				$this->ssl_data_b = new ssl_data_b(null, null, null);
				$vlist['contactemail'] = array('M', null);
				$vlist["ssl_data_b_s_commonName_r"]  = null;
				$vlist["ssl_data_b_s_emailAddress_r"]  = null;
				$vlist["ssl_data_b_s_countryName_r"] =  array('A', $temp);
				$vlist["ssl_data_b_s_stateOrProvinceName_r"] = null;
				$vlist["ssl_data_b_s_localityName_r"]  = null;
				$vlist["ssl_data_b_s_organizationName_r"]  = null;
				$vlist["ssl_data_b_s_organizationalUnitName_r"]  = null;

				return $vlist;

			case "domainpserver":
				if ($this->isAdmin()) {
					$parent = $this;
				} else {
					$parent = $this->getParentO();
				}

				self::getDomainServerVlist($parent, $this, $vlist);

				return $vlist;

			case "forcedeletepserver":
				if_not_admin_complain_and_exit();

				$list = get_namelist_from_objectlist($this->getList('pserver'));
				$vlist['pserver_delete_f'] = array('s', array_remove($list, "localhost"));

				return $vlist;

			case "deleteorphanedvps":
				$sq = new Sqlite(null, 'vps');
				$slist = get_namelist_from_objectlist($this->getList('pserver'));
				$res = $sq->getTable(array('nname', 'syncserver', 'parent_clname'));

				$list = null;

				foreach($res as $r) {
					if (!array_search_bool($r['syncserver'], $slist)) {
						$list[$r['nname']] = "{$r['nname']} ({$r['syncserver']}) (orphaned)";
					}
				}

				if ($list) {
					$vlist['vps_delete_f'] = array('A', $list);
				} else {
					$vlist['vps_delete_f'] = array('M', 'No Orphaned vm');
				}

				return $vlist;

			case "multivpscreate":
				$vlist['vps_basename_f'] = null;
				$vlist['vps_admin_password_f'] = null;
				$vlist['vps_count_f'] = null;
				$vlist['vps_template_name_f'] = array('s', get_namelist_from_objectlist($this->getList('vpstemplate')));

				return $vlist;

			case "disable_url":
				$vlist['disable_url'] = array('m', array('pretext' => 'http://'));

				return $vlist;

			case "message":
				$vlist['wall_from_f'] = array('M', $login->nname);
				$vlist['send_to_f'] = array('M', $this->nname);
				$vlist['wall_subject_f'] = null;
				$vlist['wall_message_f'] = null;
				$vlist['__v_button'] = 'Send';

				return $vlist;

			case "skeleton":
				$vlist['skeletonarchive'] = array('M', null);
				$vlist['skeletonarchive_f'] = null;

				return $vlist;

			case "wall":
				$vlist['wall_from_f'] = array('M', $this->nname);

				// Can't do this. If he has 10000 client, this itelf will hang the machine.
			//	$vlist['send_to_f'] = array('M', $namlist);
				$vlist['wall_subject_f'] = null;
				$vlist['wall_message_f'] = null;
				$vlist['__v_button'] = 'Send';

				return $vlist;

			case "dnstemplatelist":
				$parent = $this->getParentO();
				$nlist = domain::getDnsTemplateList($parent);
				$vlist['dnstemplate_list'] = array('U', $nlist);
				$vlist['__v_updateall_button'] = array();

				return $vlist;

			case "information":
				$vlist['nname'] = array('M', $this->nname);

				if ($this->isAdmin()) {
					$gen = $login->getObject('general');
					$this->disable_admin = $gen->disable_admin;
					$vlist['disable_admin'] = null;
				}

				if ($this->isLogin()) {
					$vlist['cttype']= array('M', $this->cttype);
				} else {
					$parent = $this->getParentO();
					$res = null;

					if (check_if_many_server()) {
						$ctlist = array('reseller', 'customer', 'wholesale');
					} else {
						$ctlist = array('reseller', 'customer');
					}

					foreach($ctlist as $v) {
						if ($parent->isGte($v)) {
							continue;
						}

						$res[] = $v;
					}

					if ($res) {
						$vlist['cttype']= array('s', $res);
					} else {
						$vlist['cttype']= array('M', $this->cttype);
					}
				}
				$vlist['ddate']= array('M', @ date('d-m-Y', $this->ddate));

				if (!$this->isAdmin()) {
					$vlist['parent_name_f'] = array('M', $this->getParentName());
				}

				$vlist['contactemail']= "";

				if (!$this->isLogin()) {
					$vlist['text_comment'] = null;
				}

				return $vlist;

			case "license":

				$lic = $login->getObject('license')->licensecom_b;

				if ($login->isAdmin()) {
					$vlist['lic_pserver_num_f'] = array('M', $lic->lic_pserver_num);
					$vlist['lic_client_num_f'] = array('M', $lic->lic_client_num);
					$vlist['lic_maindomain_num_f'] = array('M', $lic->lic_maindomain_num);
				} else {
					$vlist['lic_node_num_f'] = array('M', $lic->node_num);
				}

				$vlist['lic_live_support_f'] = array('M', $lic->lic_live_support);
			//	$vlist['lic_ipaddress_f'] = array('M', $lic->lic_ipaddress);
				$vlist['lic_client_f'] = array('M', $lic->lic_client);
			//	$vlist['lic_current_f'] = array('t', lfile_get_contents('__path_program_etc/license.txt'));
				$vlist['license_upload_f'] = null;

				return $vlist;

			case "ipaddress":
				$parent = $this->getParentO();

				if ($this->isLogin() || !$this->isRightParent()) {
					$vlist['ipaddress_list'] = array('M', $this->getIpaddress($this->listpriv->webpserver_list));
					$vlist['__v_button'] = array();
				} else {
					if (check_if_many_server()) {
					//	dprintr($this->listpriv->webpserver_list);
						$iplist = $parent->getIpaddress($this->listpriv->webpserver_list);
					} else {
						$iplist = $parent->getIpaddress(array('localhost'));
					}

				//	dprintr($iplist);
					$vlist['ipaddress_list'] = array('Q', $iplist);
				}

				return $vlist;

			case "pserver_s":
				$parent = $this->getParentO();
				$list = null;
				$serverlist = client::getPserverListPriv();

				if ($this->isLogin() || !$this->isRightParent()) {
					foreach($serverlist as $s) {
						$slist = "{$s}_list";
						$vlist["{$s}_list"] = array('M', $this->listpriv->$slist);
					}

					$vlist['__v_button'] = array();
					//$vlist['dbtype_list'] = array('M', $this->listpriv->dbtype_list);

					return $vlist;
				} else {
					$vlist['server_detail_f'] = null;

					foreach($serverlist as $s) {
						$slist = "{$s}_list";

						// Hack.. Actually, admin's listpriv should be empty so that the __get inside the listpriv will get automatically called.
						if ($parent->isAdmin()) {
							unset($parent->listpriv->$slist);
						}

						$vlist["{$s}_list"] = null;
						$list = lx_array_merge(array($list, $parent->getServerList(strtilfirst($s, "pserver"))));
					}

					$sinfo = pservercore::createServerInfo($list);
					$sinfo = get_warning_for_server_info($parent, $sinfo);
					$vlist['server_detail_f'] = array('M', $sinfo);
					//$vlist['dbtype_list'] = null;

					return $vlist;
				}

			case "description":
				$vlist['description'] = null;
			//	$vlist['share_status'] = null;

				if (!$this->isRightParent()) {
					$this->convertToUnmodifiable($vlist);
				}

				return $vlist;
		}

		return parent::updateform($subaction, $param);
	}

	function updateInformation($param)
	{
		global $gbl, $sgbl, $login, $ghtml;

		if (isset($param['cttype'])) {
			if (!$this->isAdmin()) {
				if ($this->getParentO()->isGt($param['cttype'])) {
					throw new lxException($login->getThrow("parent_doesnt_have_privileges"), '', $param['cttype']);
				}
			}
		}

		if ($login->isAdmin()) {
			$gen = $login->getObject('general');
			$gen->disable_admin = $param['disable_admin'];
			if ($gen->isOn('disable_admin')) {
				$list = $login->getList('auxiliary');
				if (count($list) == 0) {
					throw new lxException($login->getThrow("should_create_auxiliary_id_before_disabling_admin"));
				}
			}
			$gen->setUpdateSubaction();
			$gen->write();
		}

		return $param;
	}

	function updateDnstemplatelist($param)
	{
		$param['dnstemplate_list'] = lxclass::fixListVariable($param['dnstemplate_list']);

		return $param;
	}

	function updateLicense($param)
	{
		global $gbl, $sgbl, $login, $ghtml;

		if (!$login->isLteAdmin()) {
			throw new lxException($login->getThrow("not_admin"));
		}

	//	$this->license_upload_f =  $param['license_upload_f'];
		$fname = $_FILES["license_upload_f"]["tmp_name"];
	//	$val = str_replace(" ", "", $this->license_upload_f);
	//	lfile_put_contents("__path_program_etc/license.txt", $val);

		if (!lcopy($fname, "__path_program_etc/license.txt")) {
			throw new lxException ($login->getThrow("failed_to_copy_license_file_permission_error"), 'licence');
		}

		decodeAndStoreLicense();

		// This is set so that the license alone feature - happens when the license expires - 
		// will properly redirect back to the original page. 
		$gbl->__this_redirect = '/display.php?frm_action=show';

		return null;
	}

	final function updatepserver_s($param)
	{
		$this->fixpserver_list($param);

		return $param;
	}

	final function updateIpaddress($param)
	{
		$this->fixpserver_list($param);

		return $param;
	}

	function createShowRlist($subaction)
	{
		global $gbl, $sgbl, $login, $ghtml;
	/*
		if ($this->isCustomer()) {
			if (!$this->priv->isOn('domain_add_flag')) {
				return null;
			}
		}
	*/
		if ($sgbl->isKloxo() && !$this->priv->isOn('webhosting_flag')) {
			return null;
		}

	//	dprint($subaction);
		$rlist = null;

		if (!$subaction) {
			$rlist['priv'] = null;
		}

		return $rlist;
	}

	function createShowPlist($subaction)
	{
		if ($this->isLteAdmin()) {
			return null;
		}

		$rlist = null;

		if (!$subaction) {
			$rlist['priv'] = null;
		}

		return $rlist;
	}

	function isRightParent()
	{
		return ($this->getParentO()->getClName() === $this->parent_clname) ;
	}

	function getVariable($var)
	{
		return parent::getVariable($var);

	}

	// This function is supposed to return the ipaddress of the client.
	function getIpaddress($list = null)
	{
		$retlist = null;

		if ($list && !is_array($list)) {
			$list = null;
		}

		// If the list is null then return nothing. The list is supposed to be the quota of 
		// the web servers configured. So if it doesn't exist, then we need to return nothing. 
		// Make sure we don't call it without anything.
		if (!$list) {
			return null;
		}

		if ($this->isAdmin()) {
			$iplist = $this->getList('ipaddress');

		//	dprintoa($iplist);

			foreach($iplist as $ip) {
				$ipaddr = trim($ip->ipaddr);

				if (!$ipaddr) {
					continue;
				}

				if ($list) {
					$syncserver = $ip->syncserver? $ip->syncserver: 'localhost';

					if (array_search_bool($syncserver, $list)) {
						$retlist[] = $ipaddr;
					}
				} else {
					$retlist[] = $ipaddr;
				}
			}

			return $retlist;
		}

		$sql = new Sqlite($this->__masterserver, "ipaddress");

		if ($this->listpriv->ipaddress_list)  {
			foreach($this->listpriv->ipaddress_list as $ip) {
				$res = $sql->getRowsWhere("ipaddr = '$ip'", array('syncserver'));

				foreach($res as $a) {
					$serv[] = $a['syncserver']? $a['syncserver']: 'localhost';
				}

				foreach($serv as $s) {
					if (array_search_bool($s, $list)) {
						$retlist[] = $ip;
					}
				}
			}
		}

		if ($retlist) {
			$retlist = array_unique($retlist);
		}

		return $retlist;
	}

	static function getSelectList($parent, $var)
	{
		global $gbl, $sgbl, $login, $ghtml;

		switch($var) {
			case "cttype":
				return array("customer", "reseller");

			case "ipaddresslist":
				$iplist = $parent->getIpaddress();
				$iplist = null;

				if (!$iplist) {
				//	dprintr($parent->__parent_o);
					throw new lxException($login->getThrow("no_ipaddress"), 'ipaddresslist');
				}

				return $iplist;

			case "template":
				$ol = $login->getList("clienttemplate");

				if (!$ol) {
					throw new lxException($login->getThrow("no_template"), 'template');
				}

				$onl = get_namelist_from_objectlist($ol);

				return $onl;
		}
	}
}
