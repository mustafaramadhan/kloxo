<?php

class ClientBase extends ClientCore
{
	// Core
	static $__ttype = "permanent";
	static $__desc = array("S", "", "client");
	static $__desc_c_c = array("", "", "client");

	// Mysql
	static $__desc_nname = array("n", "", "client_name", "a=show");
	static $__desc_shell = array("", "", "shell", "a=show");
	static $__desc_status = array("e", "", "s:status", "a=update&sa=toggle_status");
	static $__desc_status_v_on = array("", "", "enabled");
	static $__desc_status_v_off = array("", "", "disabled");
	static $__desc_disable_reason = array("", "", "st", 'a=updateForm&sa=limit');
	static $__desc_state = array("e", "", "ST:State", 'a=updateForm&sa=limit');
	static $__desc_state_v_ok = array("", "", "alright");
	static $__desc_state_v_exceed = array("", "", "exceeded");
	static $__desc_parent_name_f = array("S", "", "parent");
	static $__desc_parent_name = array("", "", "parent");
	static $__desc_contactemail = array("", "", "email_address");
	static $__desc_disable_url = array("", "", "url_to_show_when_domain_is_disabled.");

	static $__desc_skeleton = array("", "", "skeleton");
	static $__desc_skeletonarchive = array("", "", "current_skeleton");
	static $__desc_skeletonarchive_f = array("F", "", "upload_archive_of_skeleton_(zip_file)");

	static $__desc___v_priv_used_client_num = array("S", "", "clients");
	static $__desc___v_priv_used_traffic_usage = array("S", "", "traffic");
	static $__desc___v_priv_used_totaldisk_usage = array("S", "", "totdisk");
	static $__desc___v_priv_used_totalinode_usage = array("S", "", "inode");
	static $__desc___v_priv_used_maindomain_num = array("S", "", "domains");
	static $__desc___v_priv_used_mysqldb_num = array("S", "", "mysql");

	static $__desc_client_num_per_f = array("S", "", "clients");
	static $__desc_traffic_usage_per_f = array("pS", "", "traffic");
	static $__desc_maindomain_num_per_f = array("pS", "", "domains");
	static $__desc_mysqldb_num_per_f = array("pS", "", "mysql");
	static $__desc_disk_usage_per_f = array("pS", "", "disk");
	static $__desc_totaldisk_usage_per_f = array("pS", "", "totdisk");
	static $__desc_totalinode_usage_per_f = array("pS", "", "inode");
	static $__desc_use_resourceplan_f = array("f", "", "use_template");
	static $__desc_send_welcome_f = array("f", "", "send_welcome_message");
	static $__desc_resourceplan_f = array("", "", "resource_plan_name");
	static $__desc_send_to_f = array("", "", "send_mail_to");
	static $__desc_wall_from_f = array("", "", "from");
	static $__desc_wall_subject_f = array("n", "", "subject");
	static $__desc_resourceplan_used_f = array("n", "", "Plan");
	static $__desc_wall_message_f = array("t", "", "message");
	static $__desc_easyinstaller_app = array("", "", "install_easyinstaller");

	// Objects
	static $__acdesc_update_information = array("", "", "information");
	static $__acdesc_update_wall = array("", "", "email_all");
	static $__acdesc_update_message = array("", "", "email");
	static $__acdesc_update_collectmodinfo = array("", "", "reload_module_info");
	static $__acdesc_show_config = array("", "", "Advanced");
	static $__acdesc_update_shell_access = array("", "", "shell_access");
	static $__acdesc_update_clientsendmessage = array("", "", "message");
	static $__acdesc_update_vpssendmessage = array("", "", "message");

	// Lists
	static $__desc_pserver_l = array("q", "", "");
	static $__desc_exclusiveip_l = array("", "", "");
	static $__desc_client_l = array("RqdtLB", "", "");
	static $__desc_ndskshortcut_l = array("db", "", "");
	static $__desc_ndsktoolbar_l = array("db", "", "");
	static $__desc_clienttemplate_l = array("d", "", "");
	static $__desc_custombutton_l = array("", "", "");
	static $__desc_ipaddress_l = array("", "", "");
	static $__desc_ostemplatelist_o = array("", "", "");
	static $__desc_lxbackup_o = array("bqd", "", "");
	static $__desc_license_o = array("", "", "");
	static $__desc_sshclient_o = array("", "", "");
	static $__desc_interface_template_l = array("", "", "");
	static $__desc_interface_template_o = array("", "", "");

	static $__filter_show_all = '1';
	static $__filter_show_own = '($this->parent === $login->nname)';

	static $__client_add = array("", "", "Change Password", "Password");
	static $__client_delete = array("", "", "Change Password", "Password");

	static $__acdesc_update_disable_skeleton = array("t", "", "skeleton_and_disable_");
	static $__acdesc_update_image_manager = array("", "", "image_manager");

	function isSelect()
	{
		return true;
	}

	function canHaveChild()
	{
		return $this->isLte('reseller');
	}

	function moreNotification()
	{
		return true;
	}

	function updateClientSendMessage($param)
	{
	//	dprintr($param);
		
		$flist = $param['_accountselect'];
		$this->doSendMessage('client', $flist);
	}

	function updateboxposReset($param)
	{
		$login->boxpos = null;
		$login->setUpdateSubaction();
		$login->write();
	}

	function updateCustomerMode($param)
	{
		global $gbl, $sgbl, $login, $ghtml;

		if ($this->isDomainOwnerMode()) {
			$gbl->setSessionV('customermode_flag', 'off');
		} else {
			$list = $this->getList('domain');
			if (!$list) {
				throw new lxException($login->getThrow("please_add_one_domain_for_owner_mode"));
			}
			$gbl->setSessionV('customermode_flag', 'on');
		}

	//	$gbl->c_session->was();
		$gbl->was();

	/*
		if ($this->isOn('customermode_flag')) {
			$param['customermode_flag'] = 'off';
		} else {
			$param['customermode_flag'] = 'on';
		}
	*/
		$gbl->__this_redirect = $ghtml->getFullUrl("a=show");
		$gbl->__no_debug_redirect = true;

		return $param;
	}

	static function createListBlist($parent, $class)
	{
		$blist[] = array("a=update&sa=clientsendmessage");
		$blist[] = array("a=delete&c=client");
		return $blist;
	}

	function updateVpsSendMessage($param)
	{
	//	dprintr($param);
		$flist = $param['_accountselect'];
		$this->doSendMessage('vps', $flist);
	}

	function doSendMessage($class, $aclist)
	{
		global $gbl, $sgbl, $login, $ghtml;

		foreach ($aclist as &$__a) {
			$__a = "$__a ($class)";
		}

		$v['frm_smessage_c_text_sent_to_cmlist'] = implode(",", $aclist);
		$gbl->setSessionV("__tmp_redirect_var", $v);
		$gbl->c_session->write();
		$url = $ghtml->getFullUrl("a=addform&c=smessage");
		$ghtml->print_redirect($url);

		exit;
	}

	function getAnyErrorMessage()
	{
		global $gbl, $sgbl, $login, $ghtml;

	//	if ($sgbl->isKloxo() && $this->isAdmin()) {
		if ($sgbl->isKloxo() && ($login->nname === 'admin')) {
			if (!is_unlimited($this->priv->maindomain_num) && ($this->priv->maindomain_num - $this->used->maindomain_num) < 6) {
				$ghtml->__http_vars['frm_smessage'] = 'warn_license_limit';
				$ghtml->__http_vars['frm_m_smessage_data'] = 'maindomain_num';
			}
		}

	//	if ($this->isAdmin()) {
		if ($login->nname === 'admin') {
			$v = db_get_value("sshconfig", "localhost", "without_password_flag");
			$vv = db_get_value("sshconfig", "localhost", "config_flag");

			$this->__t_check_var = $v;
			$this->__t_check_vvar = $vv;

			if (!$this->isOn('__t_check_var') && !$this->isOn('__t_check_vvar')) {
			//	$ghtml->__http_vars['frm_emessage'] = "ssh_root_password_access";
			}

			if ($sgbl->isKloxo()) {
				$v = db_get_value("servermail", "localhost", "myname");
				if ($sgbl->isKloxo() && !$v) {
					$ghtml->__http_vars['frm_emessage'] = "mail_server_name_not_set";
				}
			}

			if (!$gbl->getSessionV('__v_error_not_first_time')) {
				$v = db_get_value("lxguard", "localhost", "configure_flag");
				$this->__t_check_var = $v;
				if (!$this->isOn('__t_check_var')) {
					$ghtml->__http_vars['frm_emessage'] = "lxguard_not_configured";
				}
			}

			// MR -- need this trick to make sure driver info sync between slavedb and kloxo database
			// if going to 'switch program' sync will be processed.
			if (!$gbl->getSyncClass($this->__masterserver, $this->syncserver, 'web')) {
				$ghtml->__http_vars['frm_emessage'] = "switch_program_not_set";
			}
		}

		if ($login->sp_specialplay_o->specialplay_b->skin_name === 'simplicity') {
		//	if ($this->isAdmin()) {
			if ($login->nname === 'admin') {
				$server = $this->syncserver;
				$server_phpini = unserialize(base64_decode(db_get_value("phpini", "pserver-" . $server, "ser_phpini_flag_b")));

				// MR -- pserver must set/update php.ini
			//	if (!isset($server_phpini->session_save_path_flag)) {
				if (!$server_phpini->session_save_path_flag) {
					$ghtml->__http_vars['frm_emessage'] = "phpini_not_set_pserver";
				}

				// MR -- double check for php.ini in client (especially for admin)
				$server_phpini = unserialize(base64_decode(db_get_value("phpini", "client-" . $this->nname, "ser_phpini_flag_b")));

			//	if (!isset($server_phpini->session_save_path_flag)) {
				if (!$server_phpini->session_save_path_flag) {
					$ghtml->__http_vars['frm_emessage'] = "phpini_not_set_client";
				}
			}
		}

		parent::getAnyErrorMessage();

		$gbl->setSessionV('__v_error_not_first_time', 1);
	}

	static function createDbPass($pass)
	{
		$newp = md5($pass);
		$newp = substr($newp, 0, 10);
		
		return $newp;
	}

	function createShowNote()
	{
		return !$this->isLogin();
	}

	static function isTreeForDelete()
	{
		return true;
	}

	function isAction($var)
	{
		global $gbl, $sgbl, $login;

		if ($var === 'parent') {
			if ($this->parent === $login->nname)
				return false;
		}

		if ($var === "client_num") {
			if ($this->isCustomer()) {
				return false;
			}
		}

		if ($var === 'state') {
			if ($this->isCustomer()) {
				return false;
			}
		}
		
		return true;
	}

	function createShowIlist()
	{
		$ilist[] = "cttype";
		$ilist[] = "ddate";
		
		return $ilist;

	}

	function getResourceChildList()
	{
		$list = $this->getChildListFilter('R');
		if ($this->isAdmin() && !$this->isAuxiliary()) {
			$list[] = 'pserver_l';
		}
		if ($this->isCustomer() || !isLicensed('lic_client')) {
			array_remove_assoc($list, 'client_l');
		}

		return $list;
	}

	function isChildVariableSpecific($var)
	{
		if ($var === 'pserver_l' && !$this->isAdmin()) {
			return false;
		}

		return true;
	}

	function isQuotaVariableSpecific($var)
	{
		global $gbl, $sgbl, $login, $ghtml;

		if ($var === 'client_num' && $this->isCustomer()) {
			return false;
		}

		if ($var === 'pserver_num' && !$this->isAdmin()) {
			return false;
		}

		$s = null;

		if (isset($this->websyncserver)) {
		//	$s = $this->websyncserver;
		}

		// MR -- change this function for php-fpm purpose
		return lightyApacheLimit($s, $var);
	}

	function createGshowAlist()
	{
		$alist[] = "a=updateForm&sa=limit";

		return $alist;
	}

	function createShowAlist(&$alist, $subaction = null)
	{
		global $gbl, $sgbl, $login, $ghtml;

		// this is dud. Overridden in clientlib.

		if ($subaction === 'config') {
			return $this->createShowAlistConfig($alist);
		}

		return $alist;
	}

	function createShowAlistConfig(&$alist, $subaction = null)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$progname = $sgbl->__var_program_name;

		$alist['__title_main'] = $login->getKeywordUc('resource');

		$alist[] = "a=resource";

		if ($this->isAdmin()) {
		//	$alist['__v_dialog_tick'] = "a=updateform&sa=ticketconfig&o=ticketconfig";
		//	$alist[] = "o=general&c=helpdeskcategory_a&a=list";
			$alist['__v_dialog_sca'] = "o=general&a=updateform&sa=scavengetime";
			$alist['__v_dialog_gen'] = "o=general&a=updateform&sa=generalsetting";
			$alist['__v_dialog_main'] = "o=general&a=updateform&sa=maintenance";
			$alist['__v_dialog_self'] = "o=general&a=updateform&sa=selfbackupconfig";
		//	$alist['__v_dialog_ssh'] = "o=general&a=updateform&sa=ssh_config";
		//	$alist['__v_dialog_ipcheck'] = "o=general&a=updateform&sa=session_config";
			$alist['__v_dialog_download'] = "o=general&a=updateform&sa=download_config";
			$alist['__v_dialog_forc'] = "a=updateform&sa=forcedeletepserver&c=client";

			if ($sgbl->isHyperVm()) {
				$alist['__v_dialog_hack'] = "o=general&a=updateform&sa=hackbuttonconfig";
				$alist['__v_dialog_rev'] = "o=general&a=updateform&sa=reversedns";
				$alist['__v_dialog_cust'] = "o=general&a=updateform&sa=customaction";
				$alist['__v_dialog_orph'] = "a=updateform&sa=deleteorphanedvps";
				$alist['__v_dialog_lxc'] = "o=general&a=updateform&sa=kloxo_config";
			//	$alist[] = "a=show&o=ostemplatelist";
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

		$alist['__title_asep'] = $login->getKeywordUc('separate');

		if ($this->canHaveChild()) {
			$alist['__v_dialog_ch'] = "o=sp_childspecialplay&a=updateform&sa=skin";
		}

		$alist['__v_dialog_not'] = "a=updateform&sa=update&o=notification";
		$alist['__v_dialog_misc'] = "a=updateform&sa=miscinfo";

		if ($this->isAdmin()) {
			$alist[] = "o=general&a=updateform&sa=portconfig";
		}

		if (!$this->isLogin() && !$this->isLteAdmin() && csb($this->nname, "demo_")) {
			$alist['__v_dialog_demo'] = "o=sp_specialplay&a=updateform&sa=demo_status";
		}

		if ($sgbl->isKloxo()) {
			$alist[] = "a=list&c=allowedip";
		}

		$alist['__title_amisc'] = $login->getKeywordUc('misc');

		if (!$this->isLogin()) {
			$alist['__v_dialog_disa'] = "a=updateform&sa=disable_per";
		}

		if ($login->priv->isOn('logo_manage_flag') && $this->isLogin()) {
			$alist['__v_dialog_uplo'] = "o=sp_specialplay&a=updateform&sa=upload_logo";
		}

		if ($sgbl->isKloxo()) {
			if ($login->priv->isOn('can_set_disabled_flag')) {
				$alist[] = 'a=updateform&sa=disable_skeleton';
			}

		//	$alist[] = "a=updateform&sa=generate_csr";
		}

		if (!$this->isLogin()) {
			$alist['__v_dialog_resend'] = "a=updateform&sa=resendwelcome";
		}

		if (!$this->isLogin()) {
			$alist[] = "a=updateForm&sa=changeowner";
		}
		if ($this->isLogin()) {
			$alist['__v_dialog_login'] = "o=sp_specialplay&a=updateform&sa=login_options";
		}

		if ($this->isAdmin()) {
		//	$alist[] = "a=updateform&sa=license&o=license";
		}

		return $alist;
	}

	function isTreeSelect()
	{
		return true;
	}

	function deleteSpecific()
	{
		$this->notifyObjects('delete');
	}

	function isContactable()
	{
		return true;
	}

	function isRealQuotaVariable($var)
	{
		$list["clientdisk_usage"] = 'a';

		return isset($list[$var]);
	}

	function getQuotaAddList($k)
	{
		if ($k !== 'totaldisk_usage') {
			return null;
		}

		return array('disk_usage', 'clientdisk_usage', 'mysqldb_usage');
	}

	static function findTotalUsage($driver, $list)
	{
		foreach ($list as $k => $d) {
			$ret[$k] = 0;
			$ret[$k] += lxfile_dirsize("__path_program_home/client/{$d['nname']}/");
			$ret[$k] += lxfile_dirsize("__path_customer_root/{$d['nname']}/");
		}
		return $ret;
	}

	function clearGreaterTemplate($clist)
	{
		$rlist = null;
		foreach ($clist as $c) {
			if (!$this->checkTemplateGreaterThan($c)) {
				$rlist[$c->nname] = $c;
			}
		}

		return $rlist;

	}

	function updateSkeleton($param)
	{
		global $login;

		$key_file_tmp = $_FILES['skeletonarchive_f']['tmp_name'];
		$key_file = $_FILES['skeletonarchive_f']['name'];
		if (!cse($key_file, ".zip")) {
			throw new lxException($login->getThrow("skeleton_should_be_zip"), '', $key_file);
		}
		$this->skeletonarchive = $key_file;
		$this->__skeletion_tmp = $key_file_tmp;
		$this->setUpdateSubaction('skeleton');
		// Don't ever return the param. In the param the skeletonarchive is null and thus the value will be set to that.
		//return $param;
	}

	function updateWall($param)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$clname = $this->getClName();
		$db = new Sqlite($this->__masterserver, "client");
		$clist = $db->getRowsWhere("parent_clname = '$clname'", array("nname", "contactemail"));

		$db = new Sqlite($this->__masterserver, "domain");
		$dlist = $db->getRowsWhere("parent_clname = '$clname'", array("nname", "contactemail"));

		$nlist = lx_merge_good($clist, $dlist);

		foreach ($nlist as $client) {
			if ($client['contactemail']) {
				if ($sgbl->dbg > 0) {
					$subject = $param['wall_subject_f'] . " ({$client['nname']})";
				}

				$contactemail = $client['contactemail'];

				lx_mail(null, $contactemail, $subject, $param['wall_message_f']);
			}
		}

	//	throw new lxException($login->getThrow("success_message_successfully_sent"));
		throw new lxException($login->getThrow("message_sent"));
	}

	function updateMessage($param)
	{
		global $login;

		if (!$this->contactemail) {
			throw new lxException($login->getThrow("no_contact_email"));
		}

		lx_mail(null, $this->contactemail, $param['wall_subject_f'], $param['wall_message_f']);

	//	throw new lxException($login->getThrow("success_message_successfully_sent"), '', $this->contactemail);
		throw new lxException($login->getThrow("message_sent"), '', $this->contactemail);
	}

	function checkTemplateGreaterThan($tmp)
	{
		$pvlist = $this->getQuotaVariableList();

		foreach ($pvlist as $k => $var) {
			if (!isset($tmp->priv->$k)) {
				continue;
			}
			if (isQuotaGreaterThan($tmp->priv->$k, $this->priv->$k)) {
				return $k;
			}
		}

		return false;
	}

	static function verify($var, $val)
	{
		global $login;

		switch ($var) {
			case "nname":
			{
				if (is_numeric($val)) {
					throw new lxException($login->getThrow("invalid_client_name"), '', $val);
				}
			}
		}
		
		return $val;
	}

	function getZiptype()
	{
		// Issue #671 - Fixed backup-restore issue
		// change to tgz for default that make less space especially temp process

		if (file_exists("../etc/flag/backup_compress_disabled.flg")) {
			return "tar";
		} else {
			return "tgz";
		}
	}

	function postAdd()
	{
		global $gbl, $sgbl, $login, $ghtml;

		$parent = $this->getParentO();
	//	$parent = $parent->getClientParentO();

		if ($this->isOn('use_resourceplan_f')) {
			//$ct = $parent->getFromList("clienttemplate", $this->resourceplan_f);

			$ct = getFromAny(array($login, $parent), 'resourceplan', $this->resourceplan_f);

			if (!$ct) {
				throw new lxException($login->getThrow("no_resourceplan"), '', $this->resourceplan_f);
			}

			if (!$parent->isAdmin()) {
				$v = $parent->checkTemplateGreaterThan($ct);

				if ($v) {
					throw new lxException($login->getThrow("resource_quota_more_than_available"), '', $v);
				}
			}

			$this->resourceplan_used = $this->resourceplan_f;
			$pv = clone($ct->priv);
			$this->priv = $pv;
			$this->fixPrivUnset();
			$this->listpriv = clone($ct->listpriv);
			$this->disable_per = $ct->disable_per;
			$this->dnstemplate_list = $ct->dnstemplate_list;
		}

		$this->setClientSyncServer();

		$this->disable_url = $parent->disable_url;

		$this->lxclientpostAdd();

		$this->contactemail = trim($this->contactemail);

		if ($this->easyinstaller_app && $this->easyinstaller_app !== '--leave--') {
			if (!validate_email($this->contactemail)) {
				throw new lxException($login->getThrow("easyinstaller_needs_valid_contactemail"), '', $this->contactemail);
			}
		}

		if ($this->priv->mysqldb_num > 0) {
			$this->createDefaultDatabase();
		}

		// Please note, the parent is wassed inside the createDefaultDomain, via the __desc_cmd_add.
		// So after that, nothing will happen.
	//	$this->domain_name = trim($this->domain_name);

		if ($sgbl->isKloxo() && $this->domain_name) {
			$this->default_domain = $this->domain_name;
			$this->createDefaultDomain($this->domain_name, $this->dnstemplate_name);
		
			if ($this->easyinstaller_app && $this->easyinstaller_app !== '--leave--') {
				$this->createDefaultApplication($this->domain_name, $this->easyinstaller_app);
			}
		}

		$this->notifyObjects('add');
	}

	function getPrimaryDb()
	{
		$list = $this->getList('mysqldb');
		foreach ((array)$list as $k => $mdb) {
			if ($mdb->isOn('primarydb')) {
				return $mdb;
			}
		}
	}

	function createDefaultDatabase()
	{
		$dbname = fix_nname_to_be_variable($this->nname);
		$dbname = substr($dbname, 0, 15);
		$mysql = new Mysqldb(null, null, $dbname);
		$rl['primarydb'] = 'on';
		$rl['dbname'] = $dbname;
		$rl['dbpassword'] = $this->realpass;
		$rl['username'] = $dbname;
		$rl['dbtype'] = 'mysql';
		$rl['syncserver'] = $this->mysqldbsyncserver;
		$rl['parent_clname'] = $this->getClName();
		$mysql->create($rl);
		$this->addToList('mysqldb', $mysql);

		return $mysql;
	}

	function setClientSyncServer()
	{
		global $gbl, $sgbl, $login, $ghtml;

		if ($sgbl->isKloxo()) {
			if (!$this->websyncserver) {
				$this->websyncserver = 'localhost';
			}

			if (!$this->mmailsyncserver) {
				$this->mmailsyncserver = 'localhost';
			}

			if (!$this->mysqldbsyncserver) {
				$this->mysqldbsyncserver = 'localhost';
			}

			if ($this->websyncserver !== $this->mmailsyncserver) {
				$this->syncserver = "$this->websyncserver,$this->mmailsyncserver";
			} else {
				$this->syncserver = $this->websyncserver;
			}
		}
	}

	function getCommandResource($resource)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$ret = null;

		switch ($resource) {
			case "ostemplate_xen":
				return vps::getVpsOsimage($login, "xen");

			case "ostemplate_openvz":
				return vps::getVpsOsimage($login, "openvz");

			case "vpspserver":
				$lx = $this->getVpsServers('xen');
				$lo = $this->getVpsServers('openvz');
				$ret = lx_merge_good($lx, $lo);
				$ret = convert_to_associate($ret);
				break;

			case "vpspserver_xen":
				$ret = $this->getVpsServers("xen");
				$ret = convert_to_associate($ret);
				break;

			case "vpspserver_openvz":
				$ret = $this->getVpsServers("openvz");
				$ret = convert_to_associate($ret);
				break;

			case "dnstemplate":
				$ret = domainBase::getDnsTemplateList($this);
				$ret = convert_to_associate($ret);
				break;

			case "resourceplan":
				$list = $this->getList('resourceplan');
				$ret = get_namelist_from_objectlist($list, 'nname', 'realname');
				break;
		}

		return $ret;
	}

	function getFfileFromVirtualList($name)
	{
		$name = coreFfile::getRealpath($name);
		$name = '/' . $name;
		$ffile = new Ffile($this->__masterserver, $this->websyncserver, "__path_customer_root/{$this->getPathFromName()}", $name, "$this->username");
		$ffile->__parent_o = $this;
		$ffile->get();

		return $ffile;
	}

	function isSync()
	{
	/*
		if ($this->dbaction === 'update') {
			return false;
		}
	*/
		return parent::isSync();
	}

	function loadAllVps()
	{
		if (!$this->isAdmin()) {
			return;
		}

		$db = new Sqlite($this->__masterserver, "vps");
		$result = $db->getTable();
		$this->setListFromArray($this->__masterserver, $this->__readserver, "vps", $result, true);
	}

	function loadAllObjects($objectname)
	{
		if (!$this->isAdmin()) {
			return;
		}

		$db = new Sqlite($this->__masterserver, $objectname);
		$result = $db->getTable();
		$this->setListFromArray($this->__masterserver, $this->__readserver, $objectname, $result, true);
	}

	function loadAllDomains()
	{
		if (!$this->isAdmin()) {
			return;
		}

		$db = new Sqlite($this->__masterserver, "domain");
		$result = $db->getTable();
		$this->setListFromArray($this->__masterserver, $this->__readserver, "domain", $result, true);
	}

	function loadAllBackups()
	{
		if (!$this->isAdmin()) {
			return;
		}

		$db = new Sqlite($this->__masterserver, "lxbackup");
		$result = $db->getTable();
		$this->setListFromArray($this->__masterserver, $this->__readserver, "lxbackup", $result, true);
	}

	function loadAllDdatabase()
	{
		if (!$this->isAdmin()) {
			return;
		}

		$db = new Sqlite($this->__masterserver, "ddatabase");
		$result = $db->getTable();
		$this->setListFromArray($this->__masterserver, $this->__readserver, "ddatabase", $result, true);
	}

	static function createListSlist($parent)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$nlist['nname'] = null;
		$nlist['contactemail'] = null;

		$nlist['resourceplan_used'] = null;
		if ($sgbl->isKloxo()) {
			$nlist['default_domain'] = null;
		}

		$nlist['status'] = array('s', array('--any--', 'on', 'off'));
		$nlist['cttype'] = array('s', array('--any--', 'reseller', 'customer'));
		$nlist['traffic_usage_q'] = array('s', array('--any--', 'overquota', 'underquota'));

		if (check_if_many_server()) {
			$sql = new Sqlite($parent->__masterserver, "pserver");
			$res = $sql->getTable(array('nname'));
			$rs = get_namelist_from_arraylist($res);
			$rs = lx_merge_good(array('--any--'), $rs);
			$nlist['websyncserver'] = array('s', $rs);
			$nlist['mmailsyncserver'] = array('s', $rs);
		//	$nlist['coma_dnssyncserver_list'] = array('s', $rs);
		}

		return $nlist;
	}

	static function hasViews()
	{
		return true;
	}

	static function createListNlist($parent, $view)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$name_list["cpstatus"] = "2%";
		$name_list["status"] = "2%";
		$name_list["state"] = "2%";

		$name_list["cttype"] = "2%";
		$name_list["nname"] = "15%";

		if ($view === 'quota') {
			$name_list["__v_priv_used_client_num"] = "10%";
		//	$name_list["client_num_per_f"] = "5%";

		//	$name_list["maindomain_num"] = "5%";
		//	$name_list["__v_priv_used_maindomain_num"] = "5%";
			$name_list["maindomain_num_per_f"] = "15%";

		//	$name_list["traffic_usage"] = "5%";
		//	$name_list["__v_priv_used_traffic_usage"] = "10%";
			$name_list["traffic_usage_per_f"] = "20%";

		//	$name_list["totaldisk_usage"] = "5%";
		//	$name_list["__v_priv_used_totaldisk_usage"] = "10%";
			$name_list["totaldisk_usage_per_f"] = "20%";

			$name_list["totalinode_usage_per_f"] = "20%";

		//	$name_list["mysqldb_num"] = "10%";
		//	$name_list["__v_priv_used_mysqldb_num"] = "10%";
			$name_list["mysqldb_num_per_f"] = "20%";
		} else {
			$name_list["contactemail"] = "25%";

			$name_list["resourceplan_used_f"] = "10%";

			if ($parent->isLte('wholesale')) {
				$name_list["client_num"] = "3%";
			}

			$name_list["maindomain_num"] = "10%";

			if ($sgbl->isKloxo()) {
			//	$name_list['default_domain'] = '10%';
			}


			if ($sgbl->isKloxo() && check_if_many_server()) {
				$name_list["websyncserver"] = "10%";
			}

			$name_list["ddate"] = "20%";
			$name_list["traffic_usage"] = "5%";
			$name_list["abutton_updateform_s_information"] = "2%";
			$name_list["abutton_updateform_s_password"] = "2%";
			$name_list["abutton_list_s_ticket"] = "2%";
			$name_list["abutton_list_s_utmp"] = "2%";
			$name_list["abutton_updateform_s_limit"] = "2%";
		}

		return $name_list;
	}

	static function add($parent, $class, $param)
	{
		global $login;

		if_customer_complain_and_exit();

		if ($parent->isGt('wholesale') && $parent->isGte($param['cttype'])) {
		//	throw new lxException($login->getThrow("type_of_adding_more_than_parent"));
			throw new lxException($login->getThrow("client_type_more_than_parent"));
		}

		$param['cpstatus'] = 'on';

		if (isset($param['resourceplan_f'])) {
			// blank
		} else {
			ClientBase::fixpserver_list($param);
		//	$param['dnstemplate_list'] = domain::getDnsTemplateList($parent);
		}

		if (isset($param['dnssyncserver_list'])) {
			$param['dnssyncserver_list'] = Client::fixListVariable($param['dnssyncserver_list']);
		}
		$param['used_s_client_num'] = '-';

		$param['realpass'] = $param['password'];
		$param['password'] = crypt($param['password']);

		return $param;
	}

	function updatecollectModInfo()
	{
		$modlist = $this->getList('module');

		foreach ((array)$modlist as $m) {
			$m->delete();
			$m->metadbaction = 'writeonly';
		}

		$this->was();

		$list = lscandir_without_dot(getreal("/module/"));

		foreach ($list as $l) {
			if ($l === 'define.inc') {
				continue;
			}

			$m = new Module(null, null, $l);
			$m->dbaction = 'add';
			$m->parent_clname = $this->getClName();
			$m->write();
		}

		return null;
	}

	static function continueForm($parent, $class, $param, $continueaction)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$vlist = null;

		$param['nname'] = trim($param['nname']);
		$param['password'] = trim($param['password']);

		validate_client_name($param['nname']);
		validate_password($param['password']);

		// and issue #657 - Client user names with "__" are displayed with missing end
		if (stristr($param['nname'], '__')) {
			throw new lxException($login->getThrow("use_double_underscore"), '', $param['nname']);
		}

		// also check if /home/<client> exists --> prevent use like 'httpd' as client

		$reserved = getRpmBranchList('reserved');

		foreach ($reserved as $r) {
			if ($param['nname'] === $r) {
				throw new lxException($login->getThrow("dir_as_reserved_under_home_dir"), '', $param['nname']);
			}
		}

		if (lxfile_exists("/home/{$param['nname']}")) {
			throw new lxException($login->getThrow("dir_exists_under_home_dir"), '', $param['nname']);

		}

		$param['nname'] = trim($param['nname']);

		if ($continueaction === 'server') {
			if (isOn($param['send_welcome_f'])) {
				if (!$param['contactemail']) {
					throw new lxException($login->getThrow("sending_welcome_needs_contactemail"));
				}

				// accept to more contact mail - http://forum.lxcenter.org/index.php?t=msg&goto=89118
				$contact = implode(",", str_replace(" ", "", $param['contactemail']));

				foreach ($contact as $c) {
					if (!validate_email($c)) {
						throw new lxException($login->getThrow("contactemail_is_not_valid_email_address"), '', $c);
					}
				}
			}

		//	dprintr($param);

			if ($param['resourceplan_f'] !== 'continue_without_plan') {
				$param['use_resourceplan_f'] = 'On';
				$ret['param'] = $param;
				$ret['action'] = 'addnow';
				return $ret;
			}

			$array = client::getPserverListPriv();

			foreach ((array)$array as $a) {
				$v = "{$a}_list";

				if (!$parent->listpriv->$v) {
				//	throw new lxException($login->getThrow("no_server_pool"), '', $v);
				}

				$param["listpriv_s_{$a}_list"] = $parent->listpriv->$v;
			}

			// This is a hack... This should now only happen in kloxo and not in hypervm.
			if (isset($param['listpriv_s_webpserver_list'])) {
				$weblist = $param['listpriv_s_webpserver_list'];
				$param['listpriv_s_ipaddress_list'] = $parent->getIpaddress($weblist);

				$nlist = domain::getDnsTemplateList($parent);
				$param['dnstemplate_list'] = $nlist;
			}

			$qvlist = getQuotaListForClass('client', $param);
			$vlist = lx_merge_good($vlist, $qvlist);

			$ret['action'] = "add";
		//	$ret['continueaction'] = 'server';
			$ret['variable'] = $vlist;
			$ret['param'] = $param;
		}

		return $ret;
	}

	static function initThisObjectRule($parent, $class, $name = null)
	{
		return null;
	}

	static function initThisObject($parent, $class, $name = null)
	{
	//	if (!$parent->is__table('node')) {
		if ($parent->getClass() !== 'node') {
			print("Attempt to Hack <br> <br> ");
			exit;
		}

		$client = new Client($parent->nname, null, 'admin');
		$client->get();

		return $client;
	}

	static function addCommand($parent, $class, $p)
	{
		checkIfVariablesSet($p, array('name', 'v-type', 'v-password'));
		checkIfVariablesSetOr($p, $param, 'resourceplan_f', array('v-plan_name'));

		$param['nname'] = $p['name'];
		$param['cttype'] = $p['v-type'];
		$param['password'] = $p['v-password'];
		$param['use_resourceplan_f'] = 'on';

		return $param;
	}

	function commandUpdate($subaction, $param)
	{
		// MR - taken from https://blogs.securiteam.com/index.php/archives/2603
		// but this logic inside commandlinelib.php
	/*
		if_demo_throw_exception('info');

		if (isset($param['cttype'])) {
			if (!$this->isAdmin()) {
				if ($this->getParentO()->isGt($param['cttype'])) {
					throw new lxException("parent_doesnt_have_privileges", 'cttype', '');
				}
		}
	*/
		switch ($subaction) {
			case "change_plan":
				checkIfVariablesSetOr($param, $param, 'newresourceplan', array('resourceplan_name'));
				break;
		}

		return $param;
	}

	static function addform($parent, $class, $typetd = null)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$progname = $sgbl->__var_program_name;

		$vlist['nname'] = "";

		if ($sgbl->isKloxo()) {
			/*
				$dlist = domainbase::getDnsTemplateList($parent);

				// MR -- don't know what a problem where including domain make unfinish looping
				// so, for awhile option for customer as the same as reseller

				if ($typetd['val'] === 'customer') {
					$vlist['domain_name'] = "";
					$vlist['dnstemplate_name'] = make_hidden_if_one($dlist);
				//	$list = array('wordpress', 'mambo', 'joomla', 'dolphin');
				//	$list = lx_merge_good('--leave--', $list);
				//	$vlist['easyinstaller_app'] = array('s', $list);
				}
			*/
		}

		$vlist['password'] = "";

		$nclist = $parent->getResourcePlanList('resourceplan');

		$vlist['__c_subtitle_plan'] = "Welcome Message";
		$vlist['contactemail'] = "";
		$vlist['send_welcome_f'] = "";
		$vlist['__v_button'] = $login->getKeywordUc('add');
		$vlist['__c_subtitle_temp'] = "Choose Plan";
		$vlist['resourceplan_f'] = array('A', $nclist);

		if ($sgbl->isKloxo()) {

			if ($typetd['val'] === 'customer') {


				if (check_if_many_server()) {
					$vlist['__c_subtitle_server'] = "Servers";
					self::getDomainServerVlist($parent, null, $vlist);
				}

			}
		}

		$ret['variable'] = $vlist;

		$ret['action'] = "continue";
		$ret['continueaction'] = "server";

		return $ret;
	}

	function getAlistFromChild($rd, &$alist)
	{
		$dalist = $rd->createShowAlist($slist);

		foreach ($dalist as $k => $a) {
			if (is_string($a) && csa($a, "ffile")) {
				continue;
			}
			if (is_string($a) && csa($a, "cron")) {
				continue;
			}
			if (is_string($a) && csa($a, "ipaddress")) {
				continue;
			}

			if (csb($k, "__title")) {
				$alist[$k] = $a;
			} else {
				if (is_string($a) && csa($a, "mailforward")) {
					$alist[] = "a=list&c=mailforward";
					continue;
				}
				if (is_string($a) && csa($a, "mailaccount") && csa($a, "list")) {
					continue;
				}
				if (is_string($a) && csa($a, "mailaccount") && csa($a, "addform")) {
					$alist[] = "a=addform&c=mailaccount";
					continue;
				}
				if (is_string($a) && csa($a, "mailinglist")) {
					$alist[] = "a=list&c=mailinglist";
					continue;
				}
				if (is_string($a) && csa($a, "addondomain")) {
					$alist[] = "a=list&c=addondomain";
					continue;
				}

				if (is_object($a) && csa($a->purl, "image_manager")) {
					$tmpurl = "a=show&l[class]=ffile&l[nname]=/";
					$alist[] = create_simpleObject(array('url' => "$tmpurl", 'purl' => "a=updateform&sa=image_manager", 'target' => "", '__internal' => true));
					continue;
				}

				if (is_string($a)) {
					$alist[] = "j[class]={$rd->getClass()}&j[nname]=$rd->nname&$a";
				} else if (is_object($a)) {
					if (!csb($a->url, "http") && !csb($a->url, "/")) {
						$a->url = "j[class]={$rd->getClass()}&j[nname]=$rd->nname&{$a->url}";
					}
					$alist[] = $a;
				}

			}
		}
	}


	// Hack function... IF it returns false, the priv field will be a '-'.
	function showPrivInResource()
	{
	/*
		if ($this->isCustomer()) {
			if (!$this->priv->isOn('domain_add_flag')) {
				return false;
			}
		}
	*/
		return true;
	}

	static function createListAlist($parent, $class)
	{
		global $gbl, $sgbl, $login, $ghtml;

		if ($parent->isGte('customer')) {
			return null;
		}

		$alist[] = "a=list&c=client";

	//	if (!$sgbl->isHyperVm()) {
			if ($parent->isLte('wholesale')) {
				$alist[] = "a=addform&dta[var]=cttype&dta[val]=wholesale&c=client";
				$alist[] = "a=addform&dta[var]=cttype&dta[val]=reseller&c=client";
			}

	//	}

		if ($parent->isLte('reseller')) {
			$alist[] = "a=addform&dta[var]=cttype&dta[val]=customer&c=client";
		}

		return $alist;
	}

	function hasDriverClass()
	{
		return true;
	}
}
