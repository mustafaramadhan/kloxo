<?php

class Gbllib  extends Lxclass
{
	function write() {}

	function get() { }

	function __construct()
	{
		$this->nname = 'gbl';

		$this->__fvar_dont_redirect = null;
		$this->c_session = null;
		$this->__this_warning = null;
	}

	function setWarning($message, $var, $val)
	{
		$this->__this_warning['message'] = $message;
		$this->__this_warning['var'] = $var;
		$this->__this_warning['val'] = $val;
	}

	function getClass()
	{
		return 'gbl';
	}

	function __get($var)
	{
		return false;
	}

	function loaddriverappInfo($master)
	{
		$db = new Sqlite($master, 'driver');
		$res = $db->getTable();

		// Doing the setFromArray stuff here itself. Since that is the place from where we are called, 
		// and if we call setfromarray here, naturally it results in a loop.
		$__t_ob = null;
		
		foreach((array) $res as $row) {
			$nname = $row['nname'];
			$obj = new driver($master, null, $nname);

			foreach($row as $key => $value) {
				if (csb($key, "ser_")) {
					$key = strfrom($key, "ser_");
					$value = unserialize(base64_decode($value));
				}

				if (cse($key, "_b") && !is_object($value)) {
					$value = new $key(null, null, $nname);
				}

				if (is_numeric($key)) {
					continue;
				}

				$obj->$key = $value;
			}
			
			$__t_ob[$nname] = $obj;
		}
		
		if (!isset($this->driver)) {
			$this->driver = array();
		}

		$this->driver[$master] = $__t_ob;
	}

	function getSyncClass($master, $syncserver, $class)
	{
		global $gbl, $sgbl, $login, $ghtml;

		if (!$login) {
			return;
		}
		
		if (isLocalhost($master)) {
			$master = 'localhost';
		}

		if ($login->isSuperadmin() && $master === 'localhost') {
			return null;
		}

		if (isLocalhost($syncserver)) {
			$syncserver = 'localhost';
		}

		//Dynamically load the syncserver info....
		if (!isset($this->driver) || !isset($this->driver[$master])) {
			$this->loaddriverappInfo($master);
		}
		
		if (!isset($this->driver[$master][$syncserver])) {
			$this->loaddriverappInfo($master);
		}

		$class_var = strtolower("pg_" . $class);

		$pgm = $this->driver[$master][$syncserver]->driver_b;

		if (isset($pgm->$class_var)) {
			$str = $pgm->$class_var;

			if (csb($str, "__v_")) {
				$class_var = "pg_" . strtolower(strfrom($str, "__v_"));
			}

			return $pgm->$class_var;
		}
		
		return null;
	}

	function setHistory()
	{
		global $gbl, $sgbl, $login, $ghtml;
		
		$ac = array('addform', 'updateform', 'list', 'show');

		if (!array_search_bool(strtolower($ghtml->__http_vars['frm_action']), $ac)) {
			return ;
		}

		$histlist = $login->dskhistory;
		$buttonpath = null;

		$url = "/display.php?{$ghtml->get_get_from_current_post(array('Search', 'frm_hpfilter'))}";
		$description = $ghtml->getActionDetails($url, null, $buttonpath, $path, $post, $file, $name, $image, $__t_identity);

		if ($file === 'ffile') {
			return;
		}

		if ($file === 'dskshortcut_a') {
			return;
		}

		if (cse($file, 'easyinstaller')) {
			return;
		}

		unset($histlist[$url]);
		$histlist[$url] = time();

		while(count($histlist) > 20) {
			array_shift($histlist);
		}

		$login->dskhistory = $histlist;
		$login->setUpdateSubaction();
		$login->write();
	}

	function setSessionV($key, $value)
	{
		if (!isset($this->c_session)) {
			return 0;
		}

		if ($this->c_session->dbaction === "delete") {
			return;
		}

		if (!isset($this->c_session->ssession_vars)) {
			$this->c_session->ssession_vars = null;
		}
		
		$this->c_session->ssession_vars[$key] = $value;
		$this->c_session->setUpdateSubaction();
	}

	function isetSessionV($key)
	{
		if (isset($this->c_session->ssession_vars[$key])) {
			return true;
		}
		
		return false;
	}

	function unsetSessionV($key)
	{
		unset($this->c_session->ssession_vars[$key]);
		
		$this->c_session->dbaction = 'update';
	}

	function getSessionV($key)
	{
		if (isset($this->c_session->ssession_vars[$key])) {
			return $this->c_session->ssession_vars[$key];
		}
		
		return null;
	}

	function isOn($var)
	{
		$val = $this->getSessionV($var);
		$val = strtolower($val);
		
		return ($val === 'on');
	}

	final function getHttpReferer()
	{
		global $gbl, $sgbl, $login, $ghtml;

		$refer = $this->getSessionV("lx_http_referer");
		$current_query_string = $ghtml->get_get_from_post(array(), $ghtml->__http_vars);
		$cur_url = $_SERVER['PHP_SELF'] . "?" . $current_query_string;
		
		if ($cur_url === $refer) {
			return $this->getSessionV("lx_http_referer_parent");
		}
		
		return $refer;
	}
}

