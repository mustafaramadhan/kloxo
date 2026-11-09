<?php

class driver_b extends Lxaclass
{

	static $__desc =  array("", "",  "database_types");
	static $__desc_nname =  array("", "",  "database_types");

	function hasDriverClass() { return false; }
}


class Driver extends Lxdb
{

	static $__desc =  array("", "",  "driver");
	static $__desc_driver_b =  array("", "",  "driver");

	static $__acdesc_update_update = array("", "",  "driver_configuration");

	static function initThisObjectRule($parent, $class, $name = null) { return null; }

	static function initThisObject($parent, $class, $name = null)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$driverapp = $gbl->getSyncClass(null, null, 'fake');
		
		if (isLocalhost($parent->__masterserver)) {
			$master = 'localhost';
		} else {
			$master = $parent->__masterserver;
		}
		
		$sync = $gbl->driver[$master][$parent->syncserver];
		
		return $sync;
	}

	function isSync()
	{
		return false;
	}

	function hasDriverClass() { return false; }
	
	function createShowUpdateform()
	{
		$uform['update'] = null;
		
		return $uform;
	}

	function createVlistDriver(&$vlist, $driver)
	{
		foreach($driver as $k => $v) {
			$descr = get_classvar_description($k);
			
			if (!$descr) {
				continue;
			}
			
			if (!is_array($v) && csb($v, "__v")) {
				continue;
			}
			
			if (is_array($driver[$k])) {
				$v = "pg_$k";
				$ar = implode(", ", $driver[$k]);
				$vlist["driver_b_s_pg_$k"] = array('M', create_simpleObject(array('descr' => $descr, 'value' => "{$this->driver_b->$v} ($ar)")));
			} else {
				$v = "pg_$k";
				$vlist["driver_b_s_pg_$k"] = array('M', create_simpleObject(array('descr' => $descr, 'value' => "{$this->driver_b->$v} ($driver[$k])")));
			}
		}

		$vlist['__v_button'] = array();
	}

	function updateform($subaction, $param)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$driver_dir = dirname(__FILE__) . "/../../file/driver";
		$os = ($this->getParentO() && isset($this->getParentO()->ostype)) ? $this->getParentO()->ostype : 'rhel';
		if ($os && file_exists("$driver_dir/$os.inc")) {
			include_once "$driver_dir/$os.inc";
		} else {
			include_once "$driver_dir/rhel.inc";
		}

		$vlist = null;

		$this->createVlistDriver($vlist, $driver);
	
		$list = module::getModuleList();

		$driver = null;
		
		foreach((array) $list as $l) {
		//	$mod = getreal("/module/") . "/$l";
		//	include_once "$mod/lib/driver.inc";
			
			$dlist = $driver[$os];
			
			if (isset($driver['all'])) {
				$dlist = lx_array_merge(array($dlist, $driver['all']));
			}
			
			$this->createVlistDriver($vlist, $dlist);
		}
	
		return $vlist;
	}
}

