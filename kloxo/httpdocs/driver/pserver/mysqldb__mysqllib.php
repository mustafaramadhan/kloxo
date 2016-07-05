<?php

class Mysqldb__mysql extends lxDriverClass
{
	function lx_mysql_connect($server, $dbadmin, $dbpass)
	{
		global $login;

		$rdb = new mysqli('localhost', $dbadmin, $dbpass);

		if (!$rdb) {
			log_error($rdb->connect_error);

			exec_with_all_closed("sh /script/load-wrapper >/dev/null 2>&1 &");
			throw new lxException($login->getThrow('could_not_connect_to_db'), '', $rdb->connect_error);
		}

		return $rdb;
	}

	function createDatabase()
	{
		global $login;

		$rdb = $this->lx_mysql_connect('localhost', $this->main->__var_dbadmin, $this->main->__var_dbpassword);

		$rdb->query("use mysql");
		$res = $rdb->query("select * from user where User = '{$this->main->username}'");

		$ret = null;

		if ($res) {
			$ret = $res->fetch_row();
		}

		if ($ret) {
		//	throw new lxException($login->getThrow("database_user_already_exists"), '', $this->main->username);
		}

		$rdb->query("create database {$this->main->dbname};");
		$this->log_error_messages();

		$rdb->query("grant all on {$this->main->dbname}.* to '{$this->main->username}'@'%' identified by '{$this->main->dbpassword}';");
		$rdb->query("grant all on {$this->main->dbname}.* to '{$this->main->username}'@'localhost' identified by '{$this->main->dbpassword}';");

		if (isset($this->main->__var_primary_user)) {
			$parentname = $this->main->__var_primary_user;

			$rdb->query("grant all on {$this->main->dbname}.* to '{$parentname}'@'localhost';");
			$rdb->query("grant all on {$this->main->dbname}.* to '{$parentname}'@'%';");
		}

		$this->log_error_messages(false);

		$rdb->query("flush privileges;");
	}

	function extraGrant()
	{
		$rdb = $this->lx_mysql_connect('localhost', $this->main->__var_dbadmin, $this->main->__var_dbpassword);

		$rdb->query("revoke show databases on *.* from '{$this->main->username}'@'%' identified by '{$this->main->dbpassword}';");

		$this->log_error_messages(false);

		$rdb->query("grant SELECT,INSERT,UPDATE,DELETE,CREATE,DROP,ALTER on {$this->main->dbname}.* to '{$this->main->username}'@'localhost' identified by '{$this->main->dbpassword}';");

		$this->log_error_messages(false);

		$rdb->query("revoke show databases on *.* from '{$this->main->username}'@'localhost' identified by '{$this->main->dbpassword}';");

		$this->log_error_messages(false);
	}

	function deleteDatabase()
	{
		$rdb = $this->lx_mysql_connect('localhost', $this->main->__var_dbadmin, $this->main->__var_dbpassword);

		$rdb->query("drop database {$this->main->dbname};");

		$this->log_error_messages(false);

		// MR -- fix delete database username
	//	$rdb->query("delete from mysql.user where user = '{$this->main->username}';");
		$rdb->query("drop user '{$this->main->username}'@'%';");
		$rdb->query("drop user '{$this->main->username}'@'localhost';");

		$this->log_error_messages(false);

		$rdb->query("flush privileges;");
	}

	function updateDatabase()
	{
		$rdb = $this->lx_mysql_connect('localhost', $this->main->__var_dbadmin, $this->main->__var_dbpassword);

		$rdb->query("update mysql.user set password = PASSWORD('{$this->main->dbpassword}') where user = '{$this->main->username}';");

		$this->log_error_messages();

		$rdb->query("flush privileges;");
	}

	function log_error_messages($throwflag = true)
	{
		global $login;

		$rdb = $this->lx_mysql_connect('localhost', $this->main->__var_dbadmin, $this->main->__var_dbpassword);

		if ($rdb->connect_errno) {
			dprint($rdb->connect_error);

			log_error($rdb->connect_error);

			if ($throwflag) {
				throw new lxException($login->getThrow('could_not_connect_to_db'), '', $rdb->connect_error);
			}
		}
	}

	static function take_dump($dbname, $dbuser, $dbpass, $docf)
	{
		// Issue #671 - Fixed backup-restore issue

		global $gbl, $sgbl, $login, $ghtml;

		$arg[0] = $sgbl->__path_mysqldump_path;
		$arg[1] = "--add-drop-table";
		$arg[2] = "-u";
		$arg[3] = $dbuser;
		$arg[4] = $dbname;

		if ($dbpass) {
			$arg[5] = "-p'{$dbpass}'";
		} else {
			$arg[5] = "";
		}

		$cmd = implode(" ", $arg);
	/*
		$output = null;
		$ret = null;

		exec("exec $cmd > $docf", $output, $ret);
	*/

		$link = new mysqli('localhost', $dbadmin, $dbpass);
		$result = $link->query("CREATE DATABASE IF NOT EXISTS {$dbname}");

		try {
			system("{$cmd} > {$docf}");
		} catch (Exception $e) {
			throw new lxException('Error: ' . $e->getMessage(), $dbname);
		}

	}

	static function drop_all_table($dbname, $dbuser, $dbpass)
	{
		$con = new mysqli("localhost", $dbuser, $dbpass, $dbname);
		$query = $con->query($con, "show tables");

		while($res = $query->fetch_array(MYSQLI_ASSOC)) {
			$total[] = getFirstFromList($res);
		}

		foreach($total as $k => $v) {
			$con->query("drop table $v");
		}

		$con->close();
	}

	static function restore_dump($dbname, $dbuser, $dbpass, $docf)
	{
		// Issue #671 - Fixed backup-restore issue

		global $gbl, $sgbl, $login, $ghtml;

		self::drop_all_table($dbname, $dbuser, $dbpass);

		$arg[0] = $sgbl->__path_mysqlclient_path;
		$arg[1] = "-u";
		$arg[2] = $dbuser;

		if ($dbpass) {
			$arg[3] = "-p'{$dbpass}'";
		} else {
			$arg[3] = "";
		}

		$arg[4] = $dbname;

		// MR -- missing this!
		$cmd = implode(" ", $arg);

		try {
			// MR -- remove 'engine=' to make portable
			system("sed -i 's/engine=\([a-zA-z0-9]*\) //gi' {$docf}");
			system("{$cmd} < {$docf}");
		} catch (Exception $e) {
			throw new lxException('Error: ' . $e->getMessage(), $dbname);
		}
	}

	function do_backup()
	{
		// Issue #671 - Fixed backup-restore issue

		global $gbl, $sgbl, $login, $ghtml;

		$dbadmin = $this->main->__var_dbadmin;
		$dbpass = $this->main->__var_dbpassword;
		$dbname = $this->main->dbname;

		$vd = tempnam("/tmp", "mysqldump");
		lunlink($vd);
		mkdir($vd);

		$docf = "{$vd}/mysql-{$dbname}.dump";

		$arg[0] = $sgbl->__path_mysqldump_path;
		$arg[1] = "--add-drop-table";
		$arg[2] = "-u";
		$arg[3] = $dbadmin;

		if ($dbpass) {
			$arg[4] = "-p'{$dbpass}'";
		} else {
			$arg[4] = "";
		}

		$arg[5] = $this->main->dbname;

		$cmd = implode(" ", $arg);

		$link = new mysqli('localhost', $dbadmin, $dbpass);
		$result = $link->query("CREATE DATABASE IF NOT EXISTS {$dbname}");

		try {
			// MR -- remove 'engine=' to make portable
			system("{$cmd} > {$docf}");
			system("sed -i 's/engine=\([a-zA-z0-9]*\) //gi' {$docf}");
		} catch (Exception $e) {
			lxfile_tmp_rm_rec($vd);

		//	throw new lxException('Error: ' . $e->getMessage(), $dbname);
			log_log("backup", "- Error '{$e->getMessage()}' for '{$dbname}'");
		}

		return array($vd, array(basename($docf)));
	}

	function do_backup_cleanup($list)
	{
		lxfile_tmp_rm_rec($list[0]);
	}

	function fix_grant_all()
	{
		$rdb = $this->lx_mysql_connect('localhost', $this->main->__var_dbadmin, $this->main->__var_dbpassword);

		$rdb->query("grant all on {$this->main->dbname}.* to '{$this->main->username}'@'%'");
		$rdb->query("grant all on {$this->main->dbname}.* to '{$this->main->username}'@'localhost'");
	}

	function do_restore($docd)
	{
		// Issue #671 - Fixed backup-restore issue

		global $gbl, $sgbl, $login, $ghtml;

		$dbadmin = $this->main->__var_dbadmin;
		$dbpass = $this->main->__var_dbpassword;
		$dbname = $this->main->dbname;

		$vd = tempnam("/tmp", "mysqldump");

		lunlink($vd);
		mkdir($vd);

	//	$docf = "$vd/mysql-{$this->main->dbname}.dump";
		$docf = "$vd/mysql-{$dbname}.dump";

	//	$ret = lxshell_unzip_with_throw($vd, $docd);
		$ret = lxshell_unzip('__system__', $vd, $docd);

		if (!lxfile_exists($docf)) {
		//	throw new lxException($login->getThrow('could_not_find_matching_dumpfile_for_db'), '', $docf);
			log_log("restore", "- Not match $docf file for database");
		}

		$arg[0] = $sgbl->__path_mysqlclient_path;
		$arg[1] = "-u";
		$arg[2] = $dbadmin;

		if ($dbpass) {
			$arg[3] = "-p'{$dbpass}'";
		} else {
			$arg[3] = "";
		}

		$arg[4] = $dbname;

		$cmd = implode(" ", $arg);

		$link = new mysqli('localhost', $dbadmin, $dbpass);
		$result = $link->query("CREATE DATABASE IF NOT EXISTS {$dbname}");

		try {
			system("{$cmd} < {$docf}");

			lunlink($docf);
			lxfile_tmp_rm_rec($vd);
		} catch (Exception $e) {
			throw new lxException('Error: ' . $e->getMessage(), $dbname);
		}
	}

	function doSyncToSystemPre()
	{
		global $gbl, $sgbl, $login, $ghtml;

		databasecore::loadExtension('mysql');
	}

	function dbactionAdd()
	{
		$this->createDatabase();
	}

	function dbactionDelete()
	{
		$this->deleteDatabase();
	}

	function dbactionUpdate($subaction)
	{
		$this->fix_grant_all();
		$this->updateDatabase();
	}
}

