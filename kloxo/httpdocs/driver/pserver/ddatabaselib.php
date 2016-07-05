<?php

class userlist_a 
{
}

class databasecore extends Lxdb
{
	static $__desc_username = array("n", "",  "user_name", "a=show");
	static $__desc_dbtype = array("", "",  "database_type");
	static $__desc_syncserver = array("", "",  "database_server");
	static $__desc_dbpassword = array("n", "",  "password");
	static $__desc_easyinstaller_flag = array("e", "",  "used");
	static $__desc_easyinstaller_flag_v_dull = array("", "",  "Not Used by Application");
	static $__desc_easyinstaller_flag_v_on = array("", "",  "Used By Application");
	static $__desc_mysqldb_usage = array("q", "",  "database_disk_usage_(mb)");
	static $__desc_mssqldb_usage = array("q", "",  "database_disk_usage_(mb)");
	static $__desc_phpmyadmin_f  = array("b", "",  "", "__stub_phpmyadmin_url");

	static $__acdesc_update_update = array("", "",  "edit_db");
	static $__acdesc_update_phpmyadmin = array("", "",  "phpmyadmin");

	static $__desc_clientname_as_prefix = array("f", "",  "clientname_as_prefix");

	function inheritSynserverFromParent()
	{ 
		return false; 
	}
	
	function isCoreBackup() 
	{ 
		return true; 
	}

	function createExtraVariables()
	{
		$pdb = $this->getTrueParentO()->getPrimaryDb();
		
		if ($pdb) {
			$this->__var_primary_user = $pdb->nname;
		}
		if ($this->dbtype === 'mysql') {
			$ret = $this->getDbAdminPass();
			$this->__var_dbadmin = $ret['dbadmin'];
			$this->__var_dbpassword = $ret['dbpassword'];
		}
		if (!isset($this->__var_enc_pass)) {
			$this->__var_enc_pass = md5($this->dbpassword);
		}
	}

	function getQuotaNeedVar()
	{
		return array('dbname' => $this->dbname);
	}

	static function add($parent, $class, $param)
	{
		global $gbl, $sgbl, $login, $ghtml;

		if ($parent->isAdmin()) {
			$param['nname'] = $param['nname'];
		} else {
			if ($param['clientname_as_prefix'] === 'on') {
				$param['nname'] = substr($parent->nname, 0, 15) . "_" . $param['nname'];
			} else {
				$param['nname'] = random_string_lcase(4) . "_" . $param['nname'];
			}
		}

		$param['nname'] = trim($param['nname']);
		$param['dbpassword'] = trim($param['dbpassword']);

		validate_database_name($param['nname']);
		validate_password($param['dbpassword']);

		$param['dbname'] = $param['nname'];

		if ($parent->isAdmin()) {
			$param['username'] = substr($param['username'], 0, 15);

			// MR -- use database name for validate of admin's database username
			validate_database_name($param['nname']);
		} else {
			$param['username'] = substr($param['nname'], 0, 15);
		}

		$param['dbtype'] = strtil($class, "db");

	/*
		if (!check_if_many_server()) {
			$param['syncserver'] = 'localhost';
		}
	*/
		return $param;
	}

	static function getDbName($parentname, $name)
	{
		$dbprefix = self::fixDbname($parentname);
		$name = $dbprefix . $name;
		$name = substr($name, 0, 63);
		
		return $name;
	}

	function postAdd()
	{
		global $login;

		$parent = $this->getParentO();
		$nname = $this->username;
		$pp = $this->getRealClientParentO();
		$this->syncserver = $pp->mysqldbsyncserver;
		$this->fixSyncServer();
		
		if (exists_in_db($parent->__masterserver, 'mysqldbuser', $nname)) {
		//	throw new lxException($login->getThrow('database_user_already_exists'), '', $nname);
		}
	}

	Function display($var)
	{
		if ($var === 'easyinstaller_flag') {
			if ($this->$var === 'on') {
				return 'on';
			} else {
				return "dull";
			}
		}
	
		return parent::display($var);
	}

	function extraBackup()
	{ 
		return true; 
	}

	static function mysql_dbase_usage($name)
	{
		return lxfile_dirsize("/var/lib/mysql/$name");
	}

	static function findDdatabaseUsage($name, $dbtype)
	{
		$func = $dbtype . "_dbase_usage";
		$val = self::$func($name);
		
		return round($val / 1024);
	}

	function getquotaDdatabase_usage()
	{
		global $gbl, $sgbl, $login, $ghtml;
		
		return $sgbl->__var_ddatabase_usage[$this->nname];
	}

	function createShowUpdateform()
	{
		$uflist['update'] = null;
		
		return $uflist;

	}

	function createShowRlist($subaction)
	{
		return null;
		$rlist['priv'] = null;
		return $rlist;

	}

	function getDbAdminUrl()
	{
		global $gbl, $sgbl, $login, $ghtml;

//		$flagfile = "../etc/flag/user_sql_manager.flg";

//		if (file_exists($flagfile)) {
//			$url = file_get_contents($flagfile);
//			$url = trim($url);
//			$url = trim($url, "\n");

//			return $url;

		$incfile = "lib/sqlmgr.php";

		if (file_exists($incfile)) {
			// MR -- logic must be declare $dbadminUrl
			include $incfile;
			
			return $dbadminUrl;
		} else {
			if ($this->dbtype === 'mysql') {
				if (file_exists("./thirdparty/mywebsql/")) {
					$url = "/thirdparty/mywebsql/";
				} else {
					$url = "/thirdparty/phpMyAdmin/";
				}
			} elseif ($this->dbtype === 'pgsql') {
					$url = "/thirdparty/phpPgAdmin/";
			}

			if ($this->isLocalhost()) {
				return $url;
			} else {
				$fqdn = getFQDNforServer($this->syncserver);

				if (http_is_self_ssl()) {
					$port = get_kloxo_port('ssl');
					$schema = "https://";
				} else {
					$port = get_kloxo_port('nonssl');
					$schema = "http://";
				}

				return "{$schema}{$fqdn}:{$port}{$url}";
			}
		}
	}

	function createShowPropertyList(&$alist)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$alist['property'][] = 'a=show';
		$this->getSwitchServerUrl($alist['property']);
		$user = $this->username;

		$pass = $this->dbpassword;

		try {
			$dbadminUrl = $this->getDbAdminUrl();
			$servernum = $this->getDbServerNum();
		//	$pass = urlencode($pass);

			if ($dbadminUrl) {
				if (strpos($dbadminUrl, 'mywebsql') !== false) {
					$alist['property'][] = create_simpleObject(array('url' => "{$dbadminUrl}?auth_user={$user}&auth_pwd={$pass}",
						'purl' => "c=mysqldb&a=updateform&sa=phpmyadmin", 'target' => "target='_blank'"));
				} else {
					$alist['property'][] = create_simpleObject(array('url' => "{$dbadminUrl}?pma_username={$user}&pma_password={$pass}", 
						'purl' => "c=mysqldb&a=updateform&sa=phpmyadmin", 'target' => "target='_blank'"));
				}
			}
		} catch (Exception $e) {}

	}

	function createShowAlist(&$alist, $subaction = null)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$user = $this->username;
		$pass = $this->dbpassword;
		$dbadminUrl = $this->getDbAdminUrl();
		$servernum = $this->getDbServerNum();

		return $alist;
	}

	static function createListAlist($parent, $class)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$alist[] = "a=list&c=$class";
	//	$alist['__v_dialog_add'] = "a=addform&c=$class";
	//	$alist[] = create_simpleObject(array('url' => "/thirdparty/phpMyAdmin/", 'purl' => "c=ddatabase&a=updateform&sa=phpmyadmin", 'target' => "target='_blank'"));

		return $alist;
	}

	function isSelect()
	{
		if ($this->isOn('primarydb')) {
			return false;
		}

		return true;

		if ($this->isOn('easyinstaller_flag')) {
			return false;
		}

		return true;
	}

	static function createListNlist($parent, $view)
	{
		$nlist['easyinstaller_flag'] = '5%';
		$nlist['phpmyadmin_f'] = '5%';
		$nlist['parent_clname'] = '5%';
		$nlist['syncserver'] = '5%';
		$nlist['username'] =  '10%';
		$nlist['nname'] =  '70%';
		$nlist['dbtype'] = '10%';
		return $nlist;
	}

	function updateform($subaction, $param)
	{
		global $gbl, $sgbl, $login, $ghtml;
		
		switch($subaction) {
			case "update":
				$vlist['nname'] = array('M', null);
				$vlist['dbtype'] = array('M', null);
				$vlist['syncserver'] = array('M', null);
				$vlist['username'] = array('M', null);
				$vlist['dbpassword'] = null;
				
				return $vlist;
		}
		
		return parent::updateform($subaction, $param);
	}

	static function fixDbname($pname)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$dbprefix = str_replace(".", "", $pname);
		$dbprefix = str_replace("-", "", $dbprefix);
		$dbprefix = substr($dbprefix, 0, 8);
		$dbprefix .= "_";
		
		return $dbprefix;
	}

	static function AddListForm($parent, $class)
	{
		return self::addform($parent, $class);
	}

	static function addform($parent, $class, $typetd = null)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$dbprefix = null;

	//	if (!$parent->isAdmin()) {
	//		$dbprefix = self::fixDbname($parent->nname);
	//	}

		if ($parent->isAdmin()) {
			$vlist['nname'] = null;
		} else {
			$vlist['nname'] = array('m', array('pretext' => 'prefix_'));
		}

	//	$vlist['dbtype'] = $class;
		
		if (0 && check_if_many_server()) {
			$var = "{$class}pserver_list";

		//	if ($parent->is__table('domain')) {
			if ($parent->getClass() === 'domain') {
				$pp = $parent->getRealClientParentO();
			} else {
				$pp = $parent;
			}

			$list = $pp->listpriv->$var;

			if (!$list) {
				throw new lxException($login->getThrow('no_database_server_pool_in_client'), '', $class);
			}

			$vlist['syncserver'] = array('s', $pp->listpriv->$var);
		}

		if ($parent->isAdmin()) {
			$vlist['username'] = null;
		} else {
			$vlist['clientname_as_prefix'] = null;
		}

	//	$vlist['username'] = array('m', array('pretext' => $dbprefix));
		$vlist['dbpassword'] = null;

		$ret['variable'] = $vlist;
		$ret['action'] = 'add';
		
		return $ret;
	}

	static function loadExtension($dbtype)
	{
		if (!extension_loaded($dbtype)) {
			dprint("Warning No $dbtype <br> ");
			exit;
			dl("$dbtype.". PHP_SHLIB_SUFFIX);
		}
	}

	function getDbAdminPass()
	{
		global $gbl, $sgbl, $login, $ghtml;
		
		$db = new Sqlite($this->__masterserver, 'dbadmin');

		$res = $db->getRowsWhere("dbtype = '$this->dbtype' AND syncserver = '$this->syncserver'");

		if (!$res) {
			dprintr("NO database admin entries... <br> ");
			
			if ($login->isAdmin()) {
				$err = 'e_no_dbadmin_entries_admin';
			} else {
				$err = 'e_no_dbadmin_entries';
			}
			
			throw new lxException($err, '', $this->syncserver);
		}

		$ret['dbadmin'] = $res[0]['dbadmin_name'];
		$ret['dbpassword'] = $res[0]['dbpassword'];

		return $ret;
	}
}

class mssqldb extends databasecore
{

	static $__table =  'mssqldb';
	static $__desc = array("", "",  "mssql_database");
	static $__desc_nname = array("n", "",  "Db Name", URL_SHOW);
	static $__desc_dbname = array("n", "",  "mssql_database_name", URL_SHOW);

//	static $__desc_mssqldbuser_l = array("db", "", "");
}

class mysqldb extends databasecore
{
	static $__table =  'mysqldb';
	static $__desc = array("", "",  "mysql_database");
	static $__desc_nname = array("n", "",  "Db Name", URL_SHOW);
	static $__desc_dbname = array("n", "",  "mysql_database_name", URL_SHOW);
	static $__desc_mysqldb_usage = array("q", "",  "mysqldisk:mysql_disk_usage");

//	static $__desc_mysqldbuser_l = array("db", "", "");

	function getStubUrl($name)
	{
		if ($name == '__stub_phpmyadmin_url') {

			$dbadminUrl = $this->getDbAdminUrl();
			$user = $this->username;
			$pass = $this->dbpassword;
		//	$pass = urlencode($pass);
			
			if ($dbadminUrl) {
				return create_simpleObject(array('url' => "{$dbadminUrl}?pma_username={$user}&pma_password={$pass}",
					'purl' => "c=mysqldb&a=updateform&sa=phpmyadmin", 'target' => "target='_blank'"));
			}
		}
	}

	static function findTotalUsage($driver, $list)
	{
		foreach($list as $k => $l) {
			$name = $l['dbname'];
			$ret[$k] = lxfile_dirsize("__path_mysql_datadir/$name");
		}

		return $ret;
	}

	function isRealQuotaVariable($k)
	{
		$list['mysqldb_usage'] = 'a';

		return isset($list[$k]);
	}

	function createShowPropertyList(&$alist)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$alist['property'][] = 'a=show';
		$this->getSwitchServerUrl($alist['property']);
		$alist['property'][] = "a=update&sa=backup";
		$alist['property'][] = "a=updateform&sa=restore";
		$alist['property'][] = "a=updateform&sa=changeowner";

		$server = $_SERVER['SERVER_NAME'];

		list($server, $port) = explode(":", $server);

		$user = $this->username;
		$pass = $this->dbpassword;

		$dbadminUrl = $this->getDbAdminUrl();
		$servernum = $this->getDbServerNum();
	//	$pass = urlencode($pass);

		if ($dbadminUrl) {
			$alist['property'][] = create_simpleObject(array('url' => "{$dbadminUrl}?pma_username={$user}&pma_password={$pass}",
				'purl' => "c=mysqldb&a=updateform&sa=phpmyadmin", 'target' => "target='_blank'"));
		}
	}

	function createShowAlist(&$alist, $subaction = null)
	{
		global $gbl, $sgbl, $login, $ghtml;
		
	//	$alist['__title_main'] = $login->getKeywordUc('resource');
	//	$alist[] = "a=list&c=mysqldbuser";
		$alist = parent::createShowAlist($alist);

		return $alist;
	}
}

class all_mysqldb extends mysqldb
{
	static $__desc = array("", "",  "all_mysql_database");
	static $__desc_parent_name_f =  array("n", "",  "owner");
	static $__desc_parent_clname =  array("n", "",  "owner");

	function isSelect()
	{
		return false;
	}

	static function initThisListRule($parent, $class)
	{
		global $login;

		if (!$parent->isAdmin()) {
			throw new lxException($login->getThrow("only_admin_can_access"));
		}

		return "__v_table";
	}

	static function createListSlist($parent)
	{
		$nlist['nname'] = null;
		$nlist['parent_clname'] = null;

		return $nlist;
	}

	static function AddListForm($parent, $class)
	{
		return null;
	}

	static function createListAlist($parent, $class)
	{
		return all_domain::createListAlist($parent, $class);
	}

	static function createListNlist($parent, $view)
	{
		$nlist['nname'] = '50%';
		$nlist['parent_name_f'] = '50%';
		
		return $nlist;
	}

	static function createListUpdateForm($object, $class)
	{
		return null;
	}
}
