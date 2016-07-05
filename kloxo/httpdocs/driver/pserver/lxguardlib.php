<?php

class lxguard extends lxdb 
{
	static $__desc = array("", "",  "lxguard");
	static $__desc_nname = array("", "",  "lxguard");
	static $__desc_disablehit = array("", "",  "disable_when_this_many_wrong_attempts");
	static $__desc_configure_flag = array("f", "",  "i_have_read_about_lxguard_and_understands_what_it_does");
	static $__acdesc_update_update = array("", "",  "update");
	static $__acdesc_show = array("", "",  "Lxguard");
	static $__acdesc_update_whitelist = array("", "",  "whitelist");
	static $__acdesc_update_remove = array("", "",  "remove");
	static $__desc_lxguardwhitelist_l = array("d", "",  "");
	static $__desc_rawlxguardhit_l = array("d", "",  "");
	static $__desc_lxguardhitdisplay_l = array("d", "",  "");

	static function initThisObjectRule($parent, $class, $name = null) { return $parent->nname; }

	function createShowPropertyList(&$alist)
	{
		$nalist = lxguardhitdisplay::createListAlist($this->getParentO(), 'lxguard');

		$alist['property'] = $nalist;
	}

	function createExtraVariables()
	{
		$this->setDefaultValue("disablehit", "20");
	}

	static function collect_lxguard()
	{
		$sq = new Sqlite(null, "lxguardhit");
		$ddate = time();
		// $ddate -= 24 * 3600 * 30 * 3;
		$ddate -= 60 * 60 * 24 * 30 * 3;
		$sq->rawQuery("delete from lxguardhit where (ddate + 0) < $ddate");
		$list = get_all_pserver();

		foreach($list as $l) {
			try {
				lxguardhitdisplay::getDataFromServer($l);
				self::save_current_hitlist($l);
			} catch (exception $e) {
			}
		}
	}

	function createShowUpdateform()
	{
		$uform['update'] = null;

		return $uform;
	}

	function updateWhiteList($param)
	{
		foreach($param['_accountselect'] as $p) {
			$nname = "{$p}___$this->syncserver";
			$s = new lxguardwhitelist(null, null, $nname);
			$s->ipaddress = $p;
			$s->syncserver = $this->syncserver;
			$s->parent_clname = $this->getClName();
			$s->dbaction = 'add';
			$s->write();
		}

		// You need to sync at least one so that the whitelist is properly fixed 
		// on the actual server.

		$s->dbaction = 'add';
		$s->was();
	}

	function updateRemove($param)
	{
		$server = $this->syncserver;
		$sq = new Sqlite(null, "lxguardhit");

		foreach($param['_accountselect'] as $ip) {
			$sq->rawQuery("delete from lxguardhit where syncserver = '$server' AND ipaddress = '$ip'");
		}

		self::save_current_hitlist($server);
	}

	static function save_current_hitlist($server)
	{
		$list = lxguardhitdisplay::createHitList($server);

		foreach($list as $r) {
			$hl[$r['ipaddress']] = $r['failcount'];
		}

		rl_exec_get(null, $server, "lxguard_save_hitlist", array($hl));
	}

	function updateform($subaction, $param)
	{
		$this->setDefaultValue("disablehit", "20");
		$vlist['disablehit'] = null;
		$vlist['configure_flag'] = null;

		return $vlist;
	}
}
