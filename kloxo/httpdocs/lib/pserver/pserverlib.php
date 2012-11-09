<?php 
class pserver extends pservercore {

static $__desc_mailqueue_l = array('', '', '', '');
static $__desc_clientmail_l = array('', '', '', '');
static $__desc_web_driver = array('', '', 'web', '');
static $__desc_dns_driver = array('', '', 'dns', '');
static $__desc_spam_driver = array('', '', 'spam', '');
static $__acdesc_update_switchprogram = array('', '', 'switch_program', '');
static $__acdesc_update_mailqueuedelete = array('', '', 'delete', '');
static $__acdesc_update_mailqueueflush = array('', '', 'flush', '');

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

	if_demo_throw_exception('switchprog');

	// MR -- change and add nofixconfig
/*
	$this->web_driver = $gbl->getSyncClass($this->__masterserver, $this->nname, 'web');
	$this->dns_driver = $gbl->getSyncClass($this->__masterserver, $this->nname, 'dns');
	$this->spam_driver = $gbl->getSyncClass($this->__masterserver, $this->nname, 'spam');

	$a['web'] = $this->web_driver;
	$a['dns'] = $this->dns_driver;
	$a['spam'] = $this->spam_driver;
*/
	$a['web'] = $param['web_driver'];
	$a['dns'] = $param['dns_driver'];
	$a['spam'] = $param['spam_driver'];

	$nofixconfig = $param['no_fix_config'];

	if ($nofixconfig === 'on') { return null; }

	// MR -- add 'pserver' on slavedb - read current server enough from slave_get_db
	$a['pserver'] = $this->nname;
	rl_exec_get(null, $this->nname, 'slave_save_db', array('driver', $a));

	foreach($param as $k => $v) {
		if ($this->$k === $v) {
			dprint("No change for $k: $v\n");
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

			// MR -- original code not work, so change to, also must be the last process!
		//	lxshell_return("__path_php_path", "../bin/fix/fix$fixc.php", "--server=$this->nname");
			lxshell_return("lxphp.exe", "../bin/fix/fix$fixc.php", "--server=$this->nname", "--nolog");
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
	$vlist = array("mmail" => "mmail", "dns" => "dns",  "web" => "web", 
			"mysqldb" => 'mysqldb', 'mssqldb' => 'mssqldb');

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
	$serlist = array("mmail" => "mmail", "dns" => "dns", "web" => "web", 
			"mysqldb" => 'mysqldb', 'mssqldb' => 'mssqldb');

	return $serlist;

}


function getMysqlDbAdmin(&$alist)
{

	if (!$this->isLocalhost('nname')) {
		$fqdn = getFQDNforServer($this->nname);
		//$dbadminUrl =  "http://$fqdn:7778/thirdparty/phpMyAdmin/";
		if (http_is_self_ssl()) {
			$dbadminUrl =  "https://$fqdn:7777/thirdparty/phpMyAdmin/";
		} else {
			$dbadminUrl = "http://$fqdn:7778/thirdparty/phpMyAdmin/";
		}

	} else {
		$dbadminUrl =  "/thirdparty/phpMyAdmin/";
	}
	//$pass = urlencode($pass);

	$server = $_SERVER['SERVER_NAME'];
	if (csa($server, ":")) {
		list($server, $port) = explode(":", $server);
	}


	try {
		$dbad = $this->getFromList('dbadmin', "mysql___{$this->syncserver}");
		$user = $dbad->dbadmin_name;
		$pass = $dbad->dbpassword;

		if (if_demo()) {
			$pass = "demopass";
		}

		$alist[] = create_simpleObject(array('url' => "$dbadminUrl?pma_username=$user&pma_password=$pass", 
				'purl' => "c=mysqldb&a=updateform&sa=phpmyadmin", 'target' => "target='_blank'"));
	} catch (Exception $e) {
		
	}
}


function createShowPropertyList(&$alist)
{
	//$alist['property'][] = "o=sp_specialplay&a=updateForm&sa=skin";
	$alist['property'][] = 'a=show';
	$alist['property'][] = "a=updateform&sa=information";
	if ($this->nname !== 'localhost') {
		$alist['property'][] = "a=updateform&sa=password";
	}
	if (check_if_many_server()) {
		$alist['property'][] = "a=list&c=psrole_a";
	}
}

function createShowAlist(&$alist, $subaction = null)
{
        global $gbl, $sgbl, $login, $ghtml;

// LxCenter:
// No menu structures for Domain and Advanced here?
//

        $alist['__title_security'] = "Security";
        $alist[] = "a=show&o=sshconfig";
        $alist[] = "a=list&c=watchdog";
        $alist[] = "a=show&o=lxguard";
        $alist[] = "a=list&c=hostdeny";
        $alist[] = "a=list&c=sshauthorizedkey";

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

        $alist['__title_webmailanddb'] = $login->getKeywordUc('webmailanddb');
        $alist[] = "o=servermail&a=updateform&sa=update";
        $alist[] = 'a=list&c=mailqueue';
        $alist[] = 'a=list&c=clientmail';
        $alist[] = "a=list&c=ftpsession";
        $alist[] = "a=show&o=phpini";
        $alist[] = "a=show&o=serverweb";
        $alist[] = "a=show&o=serverftp";
        $this->getMysqlDbAdmin($alist);
        $alist[] = "a=updateform&sa=mysqlpasswordreset";
        $alist[] = "a=list&c=dbadmin";


        $alist['__title_nnn'] = 'Machine';
        $alist['__v_dialog_driver'] = "a=updateform&sa=update&o=driver";
        $alist[] = "a=updateForm&sa=reboot";
        $alist[] = "a=updateForm&sa=poweroff";

	return $alist;
}


}
