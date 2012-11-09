<?php 


class DomainIpaddress extends Lxdb
{
	static $__desc = array("", "", "configure_domain");
	static $__desc_nname = array("n", "", "client_template", URL_SHOW);
	static $__desc_devname = array("n", "", "device_name", URL_SHOW);
	static $__desc_syncserver = array("n", "", "server_name", URL_SHOW);
	static $__desc_ipaddr = array("", "", "ip_address", URL_SHOW);
	static $__desc_domain = array("", "", "domain");

	static $__acdesc_update_update = array("", "", "update");
	static $__acdesc_show = array("", "", "configure_domain");
	static $__rewrite_nname_const = Array("devname", "syncserver");


	function defaultValue($var)
	{
		if ($var === 'ipaddr') {
			$db = new Sqlite($this->__masterserver, 'ipaddress');
			$res = $db->getRowsWhere("nname = '$this->nname'");
			return $res[0]['ipaddr'];
		}

		return null;
	}

	function updateform($subaction, $param)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$sq = new Sqlite(null, 'web');
		$list = $sq->getRowsWhere("syncserver = '$this->syncserver'", array('nname'));
		$dlist = get_namelist_from_arraylist($list, 'nname');


		if (!$login->isAdmin()) {
			$sq = new Sqlite(null, 'domain');
			$nlist = $sq->getRowsWhere("parent_clname = '{$login->getClName()}'", array('nname'));
			$ndlist = get_namelist_from_arraylist($nlist);
			foreach ($dlist as $k => $v) {
				if (!array_search_bool($v, $ndlist)) {
					unset($dlist[$k]);
				}
			}
		}

		if ($dlist) {
			$dlist = add_disabled($dlist);
			$vlist['domain'] = array('s', $dlist);
		} 
		else {
			$vlist['domain'] = array('M', "No Domain");
			$vlist['__v_button'] = array();
		}

		return $vlist;

	}


	function updateUpdate($param)
	{
		// This is sort of a hack... The ssl configuration of the ipaddress has to be reflected properly in the domain too. 
		// So the web objects are initialized, the ssl parameters are changed on the fly and synced again. 
		// All this is because, apache will refuse to start if the ssl files are missing, which is fucking terrible. 
		// (later)... Apache is actually ok. The whole damn problem is with iis. Be careful about the createExtraVariables. 
		// As for now, the web object doesn't create objects from the parent domain object, 
		// and thus web object will work indepnednely without the help of the domain parent object. 
		// Thus the parent of the web can anything. But if in the at any time in the future the web object starts needing any parameter 
		// from the domain, then this will have to be rewritten. Then the domain objects have to initialized first, 
		// then the web objects are initialized, and the web can be created only UNDER the domain, and not directly under this object.

		global $gbl, $sgbl, $login, $ghtml;
		$driverapp = $gbl->getSyncClass($this->__masterserver, $this->__readserver, 'web');
		$olddom = $this->domain;
		$newdom = $param['domain'];

		if (!csb($newdom, "lxdummy") && !is_disabled($newdom)) {
			$ip = gethostbyname($newdom);

			if ($ip != $this->getParentO()->ipaddr) {
				throw new lxexception("this_domain_does_not_resolve_to_this_ip", 'domain', $newdom);
			}
		}

		$this->domain = $param['domain'];
		$this->ipaddr = $this->getParentO()->ipaddr;
		$this->setUpdateSubaction();
		$this->write();

		if ($olddom) {
			$odo = new Web(null, $this->syncserver, $olddom);
			$odo->get();

			// Need to get the client here itself so that it won't run into problems later. You don't need the client anymore... 
			//$odo->getParentO()->getParentO();

			if ($odo->dbaction !== 'add') {
				$odo->setUpdateSubaction('fixipdomain');
				$odo->was();
			}
		}

		if ($olddom === $newdom) {
			return;
		}

		if (is_disabled($newdom)) {
			return;
		}

		$ndo = new Web(null, $this->syncserver, $newdom);
		$ndo->get();
		//$ndo->getParentO()->getParentO(); //you don't need client anymore..
		$ndo->setUpdateSubaction('fixipdomain');
		$ndo->was();

	}

	function createShowUpdateform()
	{
		$uflist['update'] = null;
		return $uflist;
	}

	function isSync()
	{
		return false;
	}

	function createShowPropertyList(&$alist)
	{
		$alist['property'][] = 'goback=1&a=show';
		$alist['property'][] = 'goback=1&o=sslipaddress&a=show';
		$alist['property'][] = 'a=show';

		if ($this->getParentO()->is__table('ipaddress') && $this->getParentO()->getParentO()->isAdmin()) {
			$alist['property'][] = 'goback=1&a=updateform&sa=exclusive';
		}
	}

	function createShowAlist(&$alist, $subaction = null)
	{
		global $gbl, $sgbl, $login, $ghtml;
		return null;
		return $alist;
	}

}

