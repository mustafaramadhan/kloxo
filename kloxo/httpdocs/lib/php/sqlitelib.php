<?php

ini_set('memory_limit', '-1');

/*
* Note #1
* kloxo relies on isset in other scripts
* if we set here vars to NULL to avoid PHP warnings, we will break other scripts
* so we should first properly get the vars then remove these warnings
*/

class Sqlite
{
	private $__sqtable;
	private $__force;
	static $__database = NULL;

	private $__column_type;

	function __construct($readserver, $table, $force = false)
	{
		global $gbl;

		$this->__sqtable = $table;
		$this->__force = $force;

		if (!empty ($readserver)) {
			$this->__readserver = 'localhost';
		} else {
			$this->__readserver = $readserver;
		}

		$this->connect();
	}

	// Moved connecting to a new function
	function connect()
	{
		global $gbl, $sgbl, $login;

		$fdbvar = "__fdb_{$this->__readserver}";

		if (!isset($gbl->$fdbvar) || $this->__force) {
			if (is_running_secondary()) {
				throw new lxException($login->getThrow("this_is_a_running_secondary_master"), '', $this->__readserver);
			}
		}

		$user = $sgbl->__var_admin_user;
		$db = $sgbl->__var_dbf;
		$pass = getAdminDbPass();

		if ($sgbl->__var_database_type === 'mysql') {
			$mysqlsrv = $this->__readserver;

			// MR -- use unix socket instead tcp/ip socket
			if ($mysqlsrv === 'localhost') {
				// MR -- not use because found weird (like not work for fixweb)
			//	$mysqlsrv = ':/var/lib/mysql/mysql.sock';
			}

			$gbl->$fdbvar = new mysqli($mysqlsrv, $user, $pass, $db) or dprint("Could not connect and select select $db MySQL database.\n");
			self::$__database = 'mysql';
		} else {
			try {
				$gbl->$fdbvar = new PDO("sqlite:$db");
				self::$__database = 'sqlite';
			} catch (PDOException $e) {
				dprint("PDO Error: " . $e->getMessage() . "\n");
			}
		}

		if (!$gbl->$fdbvar) {
			die("Could not open database connection.");
		}
	}

	function reconnect()
	{
		log_log("database_reconnect", "Reconnecting ...");

		$this->connect();
	}

	final function isLocalhost($var = "__readserver")
	{
		global $gbl, $sgbl, $login, $ghtml;

		if (isset($this->$var)) {
			if ($this->$var != "localhost") {
				return false;
			}
		}

		return true;
	}

	function rawQuery($string)
	{
		$ret = $this->rl_query($string);

		return $ret;
	}

	function setPassword($newp)
	{
		return $this->rawQuery("set password=Password('$newp');");
	}

	function database_query($res, $string)
	{
		$error_message = 'unknown';
	
		if (self::$__database == 'mysql') {
			// the old behavior was to reconnect. Not needed anymore.
			$result = $res->query($string);

			if (!$result) {
				$error_message = $res->connect_errno;
			}

			return $result;
		} else {
			$result = $res->prepare($string);

			if ($result) {
				$v = $result->execute();
			} else {
				$pdo_error_info = $res->errorInfo();
				$error_message = $pdo_error_info[2];
			}
		}
	
		if (!$result) {
			dprint("Query error: $error_message\n");
			log_database("Query failed: $string");
		}

		return $result;
	}

	function database_fetch_array($query)
	{
		if (self::$__database == 'mysql') {
			return $query->fetch_array(MYSQLI_ASSOC);
		} else {
			return $query->fetch(PDO::FETCH_ASSOC);
		}
	}

	function close()
	{
		global $gbl, $sgbl, $login, $ghtml;

		$fdbvar = "__fdb_" . $this->__readserver;

		if (self::$__database == 'mysql') {
			$gbl->$fdbvar->close();
		} else {
			//
		}

		$gbl->$fdbvar = NULL;
	}

	function rl_query($string)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$fdbvar = "__fdb_{$this->__readserver}";

		$query = $this->database_query($gbl->$fdbvar, $string);

		if (!$query) {
			return 0;
		}

		if (!(is_resource($query) || is_object($query))) {
			return 0;
		}

		$fulresult = null;

		while ($result = $this->database_fetch_array($query)) {
			if (isset($result['nname']) && $result['nname'] === '__dummy__dummy__') {
				continue;
			}

			if (isset($result['realpass'])) {
				$value = $result['realpass'];

				if (csb($value, '__lxen:')) {
					$value = base64_decode(strfrom($value, "__lxen:"));
				}

				$result['realpass'] = $value;
			}

			$fulresult[] = $result;
		}

		return $fulresult;
	}

	function getRowsGeneric($string, $list = null)
	{
		$ret = null;

		if ($list) {
			$select = implode(",", $list);
		} else {
			$select = "*";
		}

		$query = "select $select from $this->__sqtable $string;";
		$fulresult = $this->rl_query($query);

		// The ser varialbles are now handled in the setfromarray, and this saves us a lot time.

		return $fulresult;
	}

	function getClass()
	{
		return 'sqlite';
	}

	function existInTable($var, $value)
	{
		$result = $this->getRowsWhere("$var = '$value'");

		if ($result) {
			return true;
		}

		return false;
	}

	function getRowsWhere($string, $list = null)
	{
		return $this->getRowsGeneric("where " . $string, $list);
	}

	function getRowsOr($field1, $value1, $field2, $value2)
	{
		return $this->getRowsWhere("$field1 = '$value1' or $field2 = '$value2'");
	}

	function getRowAnd($field1, $value1, $field2, $value2)
	{
		return $this->getRowsWhere("$field1 = '$value1' and  $field2='$value2'");
	}

	function getRowsNot($field, $notval)
	{
		return $this->getRowsWhere("$field != '$notval'");
	}

	function getRows($field, $value)
	{
		return $this->getRowsWhere("$field = '$value'");
	}

	function getTable($list = null)
	{
		return $this->getRowsGeneric("", $list);
	}

	function getColumnTypes()
	{
		global $gbl, $sgbl, $login, $ghtml;

		$fdbvar = "__fdb_" . $this->__readserver;

		if (!$this->__column_type) {
			if ($sgbl->__var_database_type === 'mysql') {
				$query = "SHOW COLUMNS FROM $this->__sqtable";
			} else {
				$query = "select * from $this->__sqtable where nname = '__dummy__dummy__' ";
			}
		
			$result = $this->database_query($gbl->$fdbvar, $query);

			if (!$result) {
				return null;
			}

			$res = NULL;
		
			if ($sgbl->__var_database_type === 'mysql') {
				while ($row = $result->fetch_assoc()) {
					$res[$row['Field']] = $row['Field'];
				}
			} else {
				$row = $result->fetch(PDO::FETCH_ASSOC);

				if (!$row) {
					return null;
				}

				foreach ($row as $k => $v) {
					$res[$k] = $k;
				}
			}
		
			$this->__column_type = $res;
		}

		return $this->__column_type;
	}

	function escapeBack($key, $string)
	{
		if (!csb($key, "text_")) {
			return $string;
		}

		return $string;
	}

	function createQueryStringAdd($array)
	{
		$string = " ( ";
		$result = $this->getColumnTypes();

		foreach ($result as $key => $val) {
			$string = $string . " $key,";
		}

		$string = preg_replace("/,$/i", "", $string);
		$string = $string . ") values(";

		foreach ($result as $key => $val) {
			if ($key === 'realpass') {
				$rp = $array[$key];
				$rp = base64_encode($rp);
				$rp = "__lxen:$rp";
				$string = "$string '$rp',";

				continue;
			}

			$string = "$string '{$this->escapeBack($key, $array[$key])}',";

		}

		$string = preg_replace("/,$/i", "", $string);
		$string = $string . " )";

		return $string;
	}

	function createQueryStringUpdate($array)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$string = "";
		$result = $this->getColumnTypes();

		foreach ($result as $key => $val) {
			if (isset($sgbl->__var_collectquota_run) && $sgbl->__var_collectquota_run) {
				if (!csb($key, "priv_") && !csb($key, 'used_') && !csb($key, "nname") && !csb($key, "status") && !csb($key, "state") && !csb($key, "cpstatus")) {
					continue;
				}
			}

			if ($key === 'realpass') {
				$rp = $array[$key];

				if (!csb($rp, "__lxen:")) {
					$rp = base64_encode($rp);
					$rp = "__lxen:$rp";
				}
				$string[] = "$key = '$rp'";

				continue;
			}

			$string[] = "$key = '{$this->escapeBack($key, $array[$key])}'";
		}

		$string = implode(",", $string);

		return $string;
	}

	function getCountWhere($query)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$countres = $this->rawquery("select count(*) from $this->__sqtable where $query");
	
		if ($sgbl->__var_database_type === 'mysql') {
			$countres = $countres[0]['count(*)'];
		} else {
			$countres = $countres[0]['count(*)'];
		}
	
		return $countres;
	}

	function getToArray($object)
	{
		$col = $this->getColumnTypes();

		foreach ($col as $key => $val) {
			if (csb($key, "coma_")) {
				$cvar = substr($key, 5);
				$value = $object->$cvar;

				if ($value) {
					if (cse($key, "_list")) {
						$namelist = $value;
					} else {
						$namelist = get_namelist_from_objectlist($value);
					}

					$ret[$key] = implode(",", $namelist);
					dprint("in COma $key {$ret[$key]}<br> ");

					$ret[$key] = ",$ret[$key],";
				} else {
					$ret[$key] = '';
				}
			} else if (csb($key, "ser_")) {
				$cvar = substr($key, 4);

				// see note #1 on top
				// MR -- fix if not exists
				$value = (isset($object->$cvar)) ? $object->$cvar : null;

				if ($value && isset($value->driverApp)) {
					unset($value->driverApp);
				}

				if (cse($key, "_a")) {
					if ($value) foreach ($value as $kk => $vv) {
						unset($value[$kk]->__parent_o);
					}
				}

				// See note #1 on top
				$ret[$key] = base64_encode(serialize($value));
			} else if (csb($key, "priv_q_") || csb($key, "used_q_")) {
				$qob = strtil($key, "_q_");
				$qkey = strfrom($key, "_q_");

				$ret[$key] = (isset($object->$qob->$qkey)) ? $object->$qob->$qkey : null;
			} else {
				if (!isset($object->$key)) {
					$object->$key = null;
				}

				if (csb($key, "text_")) {
					$string = str_replace("\\", '\\\\', $object->$key);
				} else {
					$string = $object->$key;
				}

				$ret[$key] = str_replace("'", "\'", $string);
			}
		}

		return $ret;
	}

	function setRowObject($nname, $value, $object)
	{
		$array = $this->getToArray($object);

		$this->setRow($nname, $value, $array);
	}

	function addRowObject($object)
	{
		$array = $this->getToArray($object);

		$this->addRow($array);
	}

	function setRow($nname, $value, $array)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$fdbvar = "__fdb_" . $this->__readserver;

		if (!$this->isLocalhost()) {
			print("Major Error\n");
			exit;
		}

		$string = $this->createQueryStringUpdate($array);

		$update = "update $this->__sqtable set $string where $nname= '$value'";

		if ($array['nname'] === 'boxtrapper.com') {
			//
		}

		if (!($upd = $this->database_query($gbl->$fdbvar, $update))) {
			log_database("DbError: Update Failed for $update");
		} else {
			if ($this->__sqtable !== 'utmp') {
				dprint("Success: updated " . $this->__sqtable . " for " . $array['nname'] . "\n", 1);
			}
		}
	}

	function addRow($array)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$fdbvar = "__fdb_" . $this->__readserver;

		if (!$this->isLocalhost()) {
			print("Major Error\n");

			exit;
		}

		$string = $this->createQueryStringAdd($array);

	//	$insert = "insert into $this->__sqtable $string ;";
		$insert = "insert ignore into $this->__sqtable $string ;";

		if ($ins = $this->database_query($gbl->$fdbvar, $insert)) {
			dprint("Record inserted in $this->__sqtable for {$array['nname']}\n", 1);
		} else {
			// MR -- the problem is delete domain not delete sp_childspecialplay and sp_specialplay
			// that why this error happen... use 'insert ignore into' instead 'insert into'

			log_database("DbError: Insert Failed for {$this->__sqtable}:{$array['nname']}");
			log_bdatabase("DbError: Insert Failed for {$this->__sqtable}:{$array['nname']} $insert");

			// Not imporant... I think.. This happens mostly when they try add something twice.
			// Let us just ignore the second time, but log it properly.

			if ($sgbl->dbg > 0) {
			//	throw new lxException($login->getThrow("db_add_failed"), '', "{$this->__sqtable}:{$array['nname']}");
			}

			return true;
		}
	}

	function delRow($nname, $value)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$fdbvar = "__fdb_" . $this->__readserver;

		$delete = "delete from $this->__sqtable where $nname = '$value'";

		$delresult = $this->database_query($gbl->$fdbvar, $delete);

		if (!$delresult) {
			log_database("DbError: delete Failed for $delete");
		} else {
			dprint("Record deleted from $this->__sqtable for $nname <br>.");
		}

		return $delresult;
	}
}
