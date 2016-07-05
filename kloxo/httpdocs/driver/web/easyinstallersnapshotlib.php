<?php 

class easyinstallersnapshot extends lxclass
{

	static $__desc = array("n", "",  "snapshot");
	static $__desc_nname	 = array("n", "",  "name");
	static $__desc_appname	 = array("n", "",  "appname");
	static $__desc_app_real_nname	 = array("n", "",  "real_appname");
	static $__acdesc_update_revert	 = array("n", "",  "restore_snapshot");

	function get() {}
	function write() {}

	static function defaultSort() { return 'ddate' ; }
	static function defaultSortDir() {return "desc"; }

	function createExtraVariables()
	{
		$parent = $this->getParentO();
		$path = "__path_customer_root/$parent->customer_name/__easyinstallersnapshot/$parent->nname/";
		$this->__var_snapbase = $path;
	}

	function updateRevert($param)
	{
		global $login;

		$ip = new easyinstaller(null, null, $this->app_real_nname);
		$ip->get();

		if ($ip->ddate !== $this->app_real_date) {
			throw new lxException($login->getThrow("this_is_a_snapshot_of_an_older_installation"), '', $this->app_real_nname);
		}
	
		$ip->setUpdateSubaction('revert');
		$ip->__var_snapname = $this->nname;
		$ip->was();

		return null;
	}

	function createShowPropertyList(&$alist)
	{
		$alist['property'][] = "goback=1&a=show&k[class]=all_easyinstaller&k[nname]=easyinstaller";
		$alist['property'][] = "goback=1&a=list&c=easyinstaller";
		$alist['property'][] = "goback=1&a=list&c=easyinstallersnapshot";
	}

	function updateform($subaction, $param)
	{
		$vlist['confirm_f'] = array('M', "Restore Snapshot?");
		$vlist['__v_button'] = 'Restore';

	return $vlist;
	}

	static function createListAlist($parent, $class)
	{
		return easyinstaller::createListAlist($parent, $class);
	}

	static function createListNlist($parent, $view)
	{
		$nlist['appname'] = '10%';
		$nlist['abutton_updateform_s_revert'] = '10%';
		$nlist['ddate'] = '10%';
		$nlist['nname'] = '100%';
	//	$nlist['app_real_nname'] = '100%';

		return $nlist;
	}

	static function initThisListRule($parent, $class) { return null; }

	static function initThisList($parent, $class)
	{
		$path = "__path_customer_root/$parent->customer_name/__easyinstallersnapshot/$parent->nname/";
		$res = rl_exec_in_driver($parent, 'easyinstallersnapshot', "getSnapList", array($path));

		foreach($res as &$r) {
			$r['parent_clname'] = $parent->getClName();
		}

		return $res;
	}
}
