<?php

class pserver extends pservercore {

	static $__desc_mailqueue_l = array('', '', '', '');
	static $__desc_clientmail_l = array('', '', '', '');
	static $__desc_web_driver = array('', '', 'web', '');
	static $__desc_webcache_driver = array('', '', 'webcache', '');
	static $__desc_dns_driver = array('', '', 'dns', '');
	static $__desc_spam_driver = array('', '', 'spam', '');
	static $__acdesc_update_switchprogram = array('', '', 'switch_program', '');
	static $__acdesc_update_mailqueuedelete = array('', '', 'delete', '');
	static $__acdesc_update_mailqueueflush = array('', '', 'flush', '');

	static $__desc_sshconfig_l = array('', '', '', '');
	static $__desc_phpini_o = array("db", "", "");
	static $__desc_serverweb_o = array("db", "", "");
	static $__desc_lxguard_o = array("db", "", "");

	Function display($var)
	{
		if ($var === "rolelist") {
			if(is_array($this->rolelist))
				return implode(",", $this->rolelist);
			else
				return $this->rolelist;
		}

		if ($var === 'used_f') {
			return $this->createUsed();
		}

		return parent::display($var);
	}


	function updateSwitchProgram($param)
	{
		global $gbl, $sgbl, $login, $ghtml;

		// MR -- change and add nofixconfig

		$a['web'] = $param['web_driver'];
		$a['webcache'] = $param['webcache_driver'];
		$a['dns'] = $param['dns_driver'];
		$a['spam'] = $param['spam_driver'];

		$nofixconfig = $param['no_fix_config'];

		$apachelist = getCleanRpmBranchListOnList('httpd');

		$httpd24ready = false;

		foreach ($apachelist as $k => $v) {
			if (strpos($k, 'httpd24')) {
				$httpd24ready = true;
				break;
			}
		}

		$httpd24flag = "../etc/flag/use_apache24.flg";

		// MR -- get httpd24u info
		exec("cat '../etc/list/httpd.lst'|grep httpd24", $out);

		if ($out[0] !== null) {
			$useapache24 = $param['use_apache24'];

			if ($useapache24 === 'on') {
				exec("echo '' > {$httpd24flag}");
			} else {
				exec("'rm' -f {$httpd24flag}");
			}
		} else {
			if (file_exists($httpd24flag)) {
				exec("'rm' -f {$httpd24flag}");
			}
		}

		// MR -- add 'pserver' on slavedb - read current server enough from slave_get_db
	//	$a['pserver'] = $this->nname;
	//	rl_exec_get(null, $this->nname, 'slave_save_db', array('driver', $a));

		foreach($param as $k => $v) {
		//	if (($k === 'no_fix_config') || ($k === 'use_apache24')) { continue; }
			if ($k === 'use_apache24') { continue; }

			if ($this->$k === $v) {
				dprint("No change for $k: $v\n");
			} else {
				$t = str_replace("proxy", "", $v);

				if ((!file_exists("{$sgbl->__path_program_root}/file/{$t}")) && ($k !== 'spam_driver') && ($t !== 'none')) {
					throw new lxException($login->getThrow("not_ready_to_use"), '', $v);
				} else {
					dprint("Change for $k: $v\n");

					$class = strtilfirst($k, "_");
					$drstring = "{$class}_driver";

					rl_exec_get(null, $this->nname, array($class, 'switchDriver'), array($class, $this->$drstring, $v));

					changeDriver($this->nname, $class, $v);

					$fixc = $class;

					if ($class === 'spam') { $fixc = "mmail"; }

					$a[$class] = $v;
					rl_exec_get(null, $this->nname, 'slave_save_db', array('driver', $a));

					if ($nofixconfig === 'on') { continue; }

				//	lxshell_return("sh", "/script/fix{$fixc}", "--target=defaults", "--server={$this->nname}", "--nolog");
					exec("sh /script/fix{$fixc} --target=defaults --server={$this->nname} --nolog");

					// MR -- original code not work, so change to, also must be the last process!
					if ($fixc === 'web') {
						if (isWebProxyOrApache()) {
							$php_type = db_get_value("serverweb", "pserver-" . $this->nname, "php_type");

							if (stripos($php_type, 'php-fpm') !== false) {
								exec("chkconfig php-fpm on");
							} else {
								exec("chkconfig php-fpm off");
							}
						} else {
							exec("chkconfig php-fpm on");
						}
					}
				}
			}
		}
	}

	function updatemailQueueFlush($param)
	{
		rl_exec_get(null, $this->syncserver, array("mailqueue__qmail", 'QueueFlush'), array());
		return null;
	}

	function updatemailQueueDelete($param)
	{
		$this->updateAccountSel($param, "mailqueuedelete");
		rl_exec_get(null, $this->syncserver, array("mailqueue__qmail", 'QueueDelete'),
			array($this->mailqueuedelete_list));

		return null;
	}

	function createUsed()
	{
		if (isset($this->used_f)) {
			return $this->used_f;
		}
		$res = $this->getUsed();
		if ($res) {
			$this->used_f = 'on';
		} else {
			$this->used_f = 'dull';
		}

		return $this->used_f;
	}

	function getUsed()
	{
	//	$vlist = array("mmail" => "mmail", "dns" => "dns",  "web" => "web", "mysqldb" => 'mysqldb', 'mssqldb' => 'mssqldb');

		$vlist = array("mmail" => "mmail", "dns" => "dns",  "web" => "web", "mysqldb" => 'mysqldb', "webcache" => 'webcache');

		$ret = null;

		foreach($vlist as $k => $v) {
			if (!is_array($v)) {
				$db = $v;
				$vname = "syncserver";
			} else {
				$db = $v[0];
				$vname = $v[1];
			}

			$db = new Sqlite($this->__masterserver, $db);
			$str = "$vname = '$this->nname'";
			$res = $db->getRowsWhere($str, array('nname'));

			if ($res) {
				$tmp = null;
				foreach($res as $r) {
					$tmp[] = $r['nname'];
				}
				$ret[$k] = implode(", ", $tmp);
			}
		}

		return $ret;
	}

	function createUsedDomainList()
	{
		$res = $this->getUsed();

		foreach($res as $k => $v) {
			$var = "used_domainlist_{$k}_f";
			$this->$var = $v;
		}

	//	$serlist = array("mmail" => "mmail", "dns" => "dns", "web" => "web", "mysqldb" => 'mysqldb', 'mssqldb' => 'mssqldb');
		$serlist = array("mmail" => "mmail", "dns" => "dns", "web" => "web", "mysqldb" => 'mysqldb', "webcache" => 'webcache');

		return $serlist;

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
			if (file_exists("./thirdparty/mywebsql/")) {
				$url = "/thirdparty/mywebsql/";
			} else {
				$url = "/thirdparty/phpMyAdmin/";
			}

			if (!$this->isLocalhost('nname')) {
				$fqdn = getFQDNforServer($this->nname);

				if (http_is_self_ssl()) {
					$port = get_kloxo_port('ssl');
					$schema = "https://";
				} else {
					$port = get_kloxo_port('nonssl');
					$schema = "http://";
				}

				$dbadminUrl = "{$schema}{$fqdn}:{$port}{$url}";
			} else {
				$dbadminUrl =  $url;
			}
		}

		try {
			$dbad = $this->getFromList('dbadmin', "mysql___{$this->syncserver}");
			$user = $dbad->dbadmin_name;
			$pass = $dbad->dbpassword;
		//	$pass = crypt($dbad->dbpassword);

			if (if_demo()) {
				$pass = "demopass";
			}

			if (file_exists("./thirdparty/mywebsql/")) {
				$alist[] = create_simpleObject(array('url' => "{$dbadminUrl}?auth_user={$user}&auth_pwd={$pass}",
					'purl' => "c=mysqldb&a=updateform&sa=phpmyadmin", 'target' => "target='_blank'"));
			} else {
				$alist[] = create_simpleObject(array('url' => "{$dbadminUrl}?pma_username={$user}&pma_password={$pass}",
					'purl' => "c=mysqldb&a=updateform&sa=phpmyadmin", 'target' => "target='_blank'"));
			}
		} catch (Exception $e) {}
	}


	function createShowPropertyList(&$alist)
	{
		global $gbl, $sgbl, $login, $ghtml;

		if ($ghtml->frm_subaction === 'commandcenter') {
			$alist['property'][] = "a=updateform&sa=commandcenter";
		} elseif ($ghtml->frm_subaction === 'timezone') {
			$alist['property'][] = "a=updateform&sa=timezone";
	//	} elseif ($ghtml->frm_subaction === 'update') {
	//		$alist['property'][] = "a=updateform&sa=update&n=driver";
		} elseif ($ghtml->frm_subaction === 'reboot') {
			$alist['property'][] = "a=updateform&sa=reboot";
			$alist['property'][] = "a=updateform&sa=poweroff";
		} elseif ($ghtml->frm_subaction === 'poweroff') {
			$alist['property'][] = "a=updateform&sa=reboot";
			$alist['property'][] = "a=updateform&sa=poweroff";
		} elseif ($ghtml->frm_subaction === 'mysqlpasswordreset') {
			$alist['property'][] = "a=updateform&sa=mysqlpasswordreset";
		} elseif ($ghtml->frm_subaction === 'switchprogram') {
			$alist['property'][] = "a=updateform&sa=switchprogram";
		}  else {
			$alist['property'][] = 'a=show';

		//	$alist['property'][] = "o=sp_specialplay&a=updateform&sa=skin";
			$alist['property'][] = "a=updateform&sa=information";

		//	if ($this->nname !== 'localhost') {
				$alist['property'][] = "a=updateform&sa=password";
		//	}

			if (check_if_many_server()) {
				$alist['property'][] = "a=list&c=psrole_a";
			}
		}
	}

	function createShowAlist(&$alist, $subaction = null)
	{
		global $gbl, $sgbl, $login, $ghtml;

		// LxCenter:
		// No menu structures for Domain and Advanced here?

		$alist['__title_main_pserver'] = $this->getTitleWithSync();
		$alist[] = "a=list&c=service";
		$alist[] = "a=list&c=cron";
		$alist[] = "a=list&c=process";
		$alist[] = "a=list&c=component";
		$alist[] = "a=list&c=ipaddress";
		$alist[] = "a=updateform&sa=commandcenter";
		$alist[] = "a=updateform&sa=switchprogram";
		$alist[] = "a=updateform&sa=timezone";
		$alist[] = "a=show&o=sshclient";
		$alist[] = "a=show&o=llog";
		$alist[] = "a=show&l[class]=ffile&l[nname]=";
	//	$alist['__v_dialog_driver'] = "a=updateform&sa=update&o=driver";
		$alist[] = "a=show&o=driver";
		$alist[] = "a=updateform&sa=reboot";
		$alist[] = "a=updateform&sa=poweroff";

		$alist['__title_security'] = "Security";
		$alist[] = "a=show&o=sshconfig";
		$alist[] = "a=list&c=watchdog";
		$alist[] = "a=show&o=lxguard";
		$alist[] = "a=list&c=hostdeny";
		$alist[] = "a=list&c=sshauthorizedkey";

	//	$alist[] = "a=show&o=jailed";

		$alist['__title_webmailanddb'] = $login->getKeywordUc('webmailanddb');
		$alist[] = "o=servermail&a=updateform&sa=update";
		$alist[] = 'a=list&c=mailqueue';
		$alist[] = 'a=list&c=clientmail';
		$alist[] = "a=list&c=ftpsession";

		if ($ghtml->frm_o_o['0']['class'] === 'pserver') {
			$alist[] = "a=show&o=phpini";
		}

		$alist[] = "a=show&o=serverweb";
		$alist[] = "a=show&o=serverftp";
		$this->getMysqlDbAdmin($alist);
		$alist[] = "a=updateform&sa=mysqlpasswordreset";
		$alist[] = "a=list&c=dbadmin";


	/*
		
	//	$alist['__title_nnn'] = 'Machine';
		// MR -- move to under pserver
		$alist['__v_dialog_driver'] = "a=updateform&sa=update&o=driver";
		$alist[] = "a=updateform&sa=reboot";
		$alist[] = "a=updateform&sa=poweroff";
	*/

		return $alist;
	}
}
