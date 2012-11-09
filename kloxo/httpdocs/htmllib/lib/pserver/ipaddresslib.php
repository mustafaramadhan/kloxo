<?php

class Ipaddress extends Lxdb
{

//Core
	static $__desc = array("", "", "ipaddress");

//Data
	static $__desc_nname = Array("", "", "device_name");
	static $__desc_devname = Array("s", "", "device_name", URL_SHOW);
	static $__desc_ipaddr = Array("n", "", "ipaddress", URL_SHOW);
	static $__desc_server_name = Array("", "", "server_name");
	static $__desc_clientslist = Array("", "", "list_of_clients");
	static $__desc_clients_no = Array("", "", "no_of_clients");
	static $__desc_shared = Array("", "", "shared_ip_address");
	static $__desc_used_f = Array("e", "", "Used");
	static $__desc_used_f_v_on = Array("", "", "Used");
	static $__desc_used_f_v_dull = Array("", "", "not_used");
	static $__desc_netmask = Array("n", "", "netmask");
	static $__desc_status = Array("e", "", "s", "a=update&sa=toggle_status");
	static $__desc_status_v_on = array("", "", "enabled");
	static $__desc_status_v_off = array("", "", "disabled");
	static $__desc_usectl = Array("", "", "root_user");
	static $__desc_bproto = Array("", "", "root_user");
	static $__desc_peerdns = Array("", "", "dns_record_modify");
	static $__desc_clientname = Array("", "", "exclusive_client");
	static $__desc_gateway = Array("", "", "gateway");
	static $__desc_itype = Array("", "", "internet_type");
	static $__desc_ipv6init = Array("", "", "ipv6");
	static $__desc_syncserver = Array("", "", "syncserver");
	static $__desc_sslipaddress_o = Array("d", "", "syncserver");
	static $__desc_domainipaddress_o = Array("d", "", "syncserver");
	static $__desc_anonftpipaddress_o = Array("d", "", "syncserver");
	static $__rewrite_nname_const = Array("devname", "syncserver");

	static $__acdesc_update_update = array("", "", "edit");
	static $__acdesc_update_exclusive = array("", "", "exclusive_client");

//Objects

	function display($var)
	{
		global $gbl, $sgbl, $login, $ghtml;

		if ($var === "devname") {
			if (csa($this->$var, "-")) {
				list($name, $num) = explode("-", $this->$var);
				return "$name:$num";
			}

			return $this->$var;
		}

		if ($var === 'clientname') {
			if (!$this->$var) {
				return 'Unassigned';
			}
		}

		if ($var === 'used_f') {
			$this->createGblIfNotExist();

			if (array_search_bool($this->ipaddr, $gbl->__var_ip_domainlist)) {
				return 'on';
			} else {
				return 'dull';
			}
		}

		return parent::display($var);

	}

	function createExtraVariables()
	{
		global $gbl, $sgbl, $login, $ghtml;

		$driverapp = $gbl->getSyncClass(null, $this->syncserver, 'dns');

		$this->__var_dnsdriver = $driverapp;
	}

	static function searchVar()
	{
		return "ipaddr";
	}

	function createGblIfNotExist()
	{
		global $gbl, $sgbl, $login, $ghtml;

		if (!isset($gbl->__var_ip_domainlist)) {
			$sq = new Sqlite($this->__masterserver, 'web');
			$res = $sq->getTable(array('ipaddress'));

			$list = get_namelist_from_arraylist($res, "ipaddress");

			$gbl->__var_ip_domainlist = $list;
		}
	}

	function isSelect()
	{
		global $gbl, $sgbl, $login, $ghtml;

		if (self::checkIfBaseAddress($this->devname)) {
			return false;
		}

		return true;

		if (!$sgbl->isKloxo()) {
			return true;
		}

		$this->createGblIfNotExist();

		if (array_search_bool($this->ipaddr, $gbl->__var_ip_domainlist)) {
			return false;
		}

		return true;
	}

	function getOne()
	{
		$temp['devname'] = $this->devname;
		$temp['id'] = $this->id;
		$this->devname = implode('-', $temp);
		$temp = explode("-", $this->nname);

		$this->nname = $this->devname . "-" . $temp[1];
	}

	function getId()
	{
		return $this->ipaddr;
	}

	static function getIpList($parent)
	{
		global $gbl, $sgbl, $login, $ghtml;

		error_reporting(0);


		$ipl = $parent->getList("ipaddress");

		$i = 0;

		foreach ($ipl as $ip) {
			$list[$i] = $ip->devname;
			$i++;
		}

		$result = "";

		foreach ($list as $row) {
			list($devname, $id) = explode("-", $row);

			if (!isset($id) || $id === null || $id = "") {
				$result[] = $devname;
			}
		}

		return $result;
	}

	static function getLeastId($parent, $devname)
	{
		global $gbl, $sgbl, $login, $ghtml;

		print(" this is the devname u passed -$devname ");

		// I have removed the sorting from getlist, and here earlier, the result was sorted according to 'nname'.
		// this is needed only when you add a new device, so ignoring now.
		$list1 = $parent->getList("ipaddress");
		$llist = get_namelist_from_objectlist($list1, "devname");

		//	dprintr($llist);

		for ($i = 0; $i < 1000000; $i++) {
			$name = "$devname:$i";

			if (!array_search_bool($name, $llist)) {
				return "$devname-$i";
			}
		}


	}

	function updateRecord($result)
	{

		$this->devname = $result['devname'];
		$this->netmask = $result['netmask'];
		$this->status = $result['status'];
		$this->ipaddr = $result['ipaddr'];
		$this->gateway = $result['gateway'];

		if (!isset($result['client_num'])) {
			$this->client_num = null;
		} else {
			$this->client_num = $result['client_num'];
		}

		if (!isset($result['shared'])) {
			$this->shared = "yes";
		} else {
			$this->shared = $result['shared'];
		}

		if (!isset($result['userctl'])) {
			$this->userctl = null;
		} else {
			$this->userctl = $result['userctl'];
		}


		if (!isset($result['itype'])) {
			$this->itype = null;
		} else {
			$this->itype = $result['itype'];
		}

		if (!isset($resutl['ipv6init'])) {
			$this->ipv6init = null;
		} else {
			$this->ipv6init = $result['ipv6init'];
		}

		if (!isset($resutl['peerdns'])) {
			$this->peerdns = null;
		} else {
			$this->peerdns = $result['peerdns'];
		}

		$this->dbaction = "update";
	}


	static function isValidIpaddress($ip)
	{
		return validate_ipaddress($ip);
	}

// Temporary hack... Ipaddress doesn't contain a show at all. So just printing....
// (Later...) this is not the actual hack. This is the normal way. If there is no other stuff in 'show',
// we compeltely avoid the 'edit' link and directly do the editing in teh show page itself...

	function createShowUpdateform()
	{
		$uflist['update'] = null;

		return $uflist;
	}

	function createShowPropertyList(&$alist)
	{
		global $gbl, $sgbl, $login, $ghtml;
		$alist['property'][] = 'a=show';

		if ($sgbl->isKloxo() && !$this->getParentO()->isClass('pserver')) {
			$alist['property'][] = 'a=show&o=sslipaddress';
			$alist['property'][] = 'a=show&o=domainipaddress';

			if ($this->getParentO()->isAdmin()) {
				$alist['property'][] = "a=updateform&sa=exclusive";
			}

			//$alist = null;
		}
	}

	function createShowAlist(&$alist, $subaction = null)
	{
		global $gbl, $sgbl, $login, $ghtml;

		return $alist;
	}

	static function createListAlist($parent, $class)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$alist[] = "a=list&c=$class";

		if ($parent->isClass('pserver')) {
			$alist[] = "a=addform&c=$class";
			$alist[] = "a=update&sa=readipaddress";
		}

		return $alist;
	}

	static function createListNlist($parent, $view)
	{
		//$nlist["nname"] = "3%";
		global $gbl, $sgbl, $login, $ghtml;

		if ($sgbl->isKloxo()) {
			//$nlist["used_f"] = "5%";
		}

		$nlist["ipaddr"] = "100%";

		if (!$parent->isClass('pserver')) {
			$nlist['syncserver'] = '10%';
		}

		$nlist["devname"] = "30%";

		if ($sgbl->isKloxo() && $parent->isAdmin()) {
			$nlist["clientname"] = "30%";
		}

		return $nlist;
	}

	function update($subaction, $param)
	{
		if ($subaction === 'toggle_status') {
			return $param;
		}

		if ($subaction === 'delete') {
			return $param;
		}

		if ($subaction === 'exclusive') {
			return $param;
		}

		self::VerifyString($this->getParentO(), $param);

		return $param;

	}

	function isAction($var)
	{
		if ($var === 'status') {
			if ($this->devname === 'eth0') {
				return false;
			}
		}

		return true;
	}

	static function chekWhetherToBlock($ip)
	{

		global $gbl, $sgbl, $login, $ghtml;

		$blockipl = $gbl->getList("blockidip");

		$i = 0;

		foreach ($blockipl as $block) {
			$blockip = explode("/", $block);

			if (isset($blockip[1])) {
				$result[$i] = self::chekIsExists($block, $ip);
			} else {
				$result[$i] = self:: compareIp($blockip[0], $ip);
			}

			$i++;
		}

		foreach ($result as $res) {
			if ($res === 1) {
				return 1;
			} else {
				return 0;
			}
		}
	}

	static function copyCertificate($devname, $machinename)
	{
		global $sgbl;

		$ppath = $sgbl->__path_program_root;
		$spath = $sgbl->__path_ssl_root;

		$name = $devname . "___" . $machinename;

		$name = sslcert::getSslCertnameFromIP($name);

		if (!lxfile_exists("__path_ssl_root")) {
			lxfile_mkdir("__path_ssl_root");
		}

		if (!lxfile_exists("{$spath}/$name.crt")) {
			lxfile_cp("{$ppath}/file/default.crt", "{$spath}/$name.crt");
		}

		if (!lxfile_exists("{$spath}/$name.key")) {
			lxfile_cp("{$ppath}/file/default.key", "{$spath}/$name.key");
		}

		if (!lxfile_exists("{$spath}/$name.ca")) {
			lxfile_cp("{$ppath}/file/default.ca", "{$spath}/$name.ca");
		}

		// MR -- add for missing (lighttpd error when select because need .pem file
		if (!lxfile_exists("{$spath}/$name.pem")) {
		//	if (!lxfile_exists("{$ppath}/file/default.pem")) {
			/*
				// MR -- look like not work!
				$contentscer = lfile_get_contents("__path_program_root/file/default.crt");
				$contentskeyl = file_get_contents("__path_program_root/file/default.key");
				$contentpem = "$contentscer\n$contentskey";
				lfile_put_contents("__path_program_root/file/default.pem");
			*/
		//	}

			exec("cat {$ppath}/file/default.crt {$ppath}/file/default.key > {$ppath}/file/default.pem");

			lxfile_cp("{$ppath}/file/default.pem", "{$spath}/$name.pem");

		}
	}

	static function chekIsExists($blockip, $ip)
	{
		$string = self::checkvalidity($blockip);

		list($v, $ipaddr, $num) = explode("-", $string);

		if ($v != 1) {
			return 0;
		}

		$netmaskl = self::findbits($num);

		$netmask = implode(".", $netmaskl);

		$localNetwork = self::doAndOperation($ipaddr, $netmask);

		$remoteNetwork = self::doAndOperation($ip, $netmask);

		if (($localNetwork === $ipaddr) && ($remoteNetwork === $localNetwork)) {
			return 1;
		} else {
			if ($localNetwork === $remoteNetwork) {
				$res = self::compareIp($ip, $ipaddr);
				return $res;
			} else {
				return 0;
			}
		}
	}

	static function compareIp($ip, $ipaddr)
	{
		if ($ip === $ipaddr) {
			return 1;
		} else {
			return 0;
		}
	}

	static function doAndOperation($ipaddr, $netmask)
	{
		$ipaddrl = explode(".", $ipaddr);
		$netmaskl = explode(".", $netmask);

		$i = 0;

		foreach ($ipaddrl as $row) {
			$ipaddr_binary[$i] = str_pad(base_convert($row, 10, 2), 8, '0', STR_PAD_LEFT);
			$i++;
		}

		$i = 0;

		foreach ($netmaskl as $row) {
			$netmask_binary[$i] = str_pad(base_convert($row, 10, 2), 8, '0', STR_PAD_LEFT);
			$i++;
		}

		for ($i = 0; $i < 4; $i++) {
			$converted[$i] = ($ipaddr_binary[$i] & $netmask_binary[$i]);
			$converted1[$i] = base_convert($converted[$i], 2, 10);
		}

		$networkaddress = implode(".", $converted1);

		return $networkaddress;
	}

	static function  findbits($mask)
	{
		for ($i = 0; $i < 32; $i++) {
			if ($i < $mask) {
				$tbits[$i] = 1;
			} else {
				$tbits[$i] = 0;
			}
		}

		$bytes = array_chunk($tbits, 8, true);

		foreach ($bytes as $b) {
			$list[] = base_convert(implode("", $b), 2, 10);
		}

		return $list;
	}

	static function checkvalidity($blockip)
	{
		$blist = explode("/", $blockip);

		$ipaddr = $blist[0];

		$num = $blist[1];

		$iplist = explode(".", $ipaddr);

		$c = 0;

		if (($iplist[0] >= 1) && ($iplist[0] <= 126) && ($iplist[0] != 127) && ($num >= 8) && ($num != 9) && ($num <= 15)) {
			$v = 1;
		} else {
			if (($iplist[0] >= 128) && ($iplist[0] <= 191) && ($num >= 16) && ($num != 17) && ($num <= 23)) {
				$v = 1;
			} else {
				if (($iplist[0] >= 192) && ($iplist[0] <= 223) && ($num >= 24) && ($num != 25) && ($num <= 32)) {
					$v = 1;
				} else {
					$v = 0;
				}
			}
		}

		return $v . "-" . $ipaddr . "-" . $num;
	}


	function updateform($subaction, $param)
	{
		if ($subaction === 'update') {
			$vlist['devname'] = array("M", $this->devname);
			$vlist['ipaddr'] = array('M', $this->ipaddr);
			$vlist['netmask'] = array('M', $this->netmask);
			$vlist['gateway'] = array('M', $this->gateway);
			$vlist['__v_button'] = "";
		} else {
			if ($subaction === "exclusive") {
				$db = new Sqlite($this->__masterserver, "client");

				$list = $db->getTable(array("nname"));
				$list = get_namelist_from_arraylist($list);
				$list = lx_merge_good('--unassigned--', $list);

				$vlist['clientname'] = array('s', $list);
			}
		}

		return $vlist;
	}

	function isSync()
	{
		global $gbl, $sgbl, $login, $ghtml;

		if (!$login->isAdmin()) {
			$this->subaction = 'clean';
			return false;
		}

		if ($this->subaction === 'exclusive') {
			return false;
		}

		return true;
	}

	static function VerifyString($parent, $param)
	{
		if (!self::isValidIpaddress($param['ipaddr'])) {
			throw new lxexception("ipaddress_invalid", 'ipaddr');
		}

		if ($param['gateway']) {
			if (!self::isValidIpaddress($param['gateway'])) {
				throw new lxexception("gateway_invalid", 'gateway');
			}
		}

		if (!self::isValidIpaddress($param['netmask'])) {
			throw new lxexception("netmask_invalid", 'netmask');
		}

		$sq = new Sqlite($parent->__masterserver, "ipaddress");

		$res = $sq->getRowsWhere("syncserver = '$parent->nname'");

		$list = get_namelist_from_arraylist($res, "ipaddr");

		if (array_search_bool($param['ipaddr'], $list)) {
			throw new lxexception("ipaddress_already_configured", 'ipaddr');
		}

		$ret = lxshell_return("ping", "-n", "-c", "1", "-w", "5", $param['ipaddr']);

		if (!$ret) {
			throw new lxexception("some_other_host_uses_this_ip", 'ipaddr');
		}

	}

	function postAdd()
	{
		$domainip = new DomainIpaddress(null, $this->syncserver, $this->nname);
		$domainip->get();
		$this->addObject('domainipaddress', $domainip);
	}

	static function add($parent, $class, $param)
	{
		$dev = $param['devname'];

		if (!isset($param['netmask'])) {
			$param['netmask'] = "255.255.255.0";
		}

		self::VerifyString($parent, $param);

		$param['devname'] = self::getLeastId($parent, $param['devname']);
		$param['gateway'] = "";

		$param['syncserver'] = $parent->nname;
		$param['status'] = 'on';

		return $param;
	}

	static function addform($parent, $class, $typetd = null)
	{
		$result = self::getIpList($parent);

		$vlist['devname'] = array('s', $result);

		$vlist['ipaddr'] = "";
		$vlist['netmask'] = array('m', '255.255.255.0');

		$ret['variable'] = $vlist;
		$ret['action'] = "add";

		return $ret;
	}

	static function fixstatus($result)
	{
		$i = 0;

		$result2 = null;

		foreach ($result as $row) {
			if ($row['ipaddr'] === '127.0.0.1') {
				continue;
			}

			if ($row['devname'] === "lo") {
				continue;
			}


			if ($row['status'] === "yes") {
				$row['status'] = "on";
			} else {
				$row['status'] = "off";
			}

			$result2[] = $row;
		}

		return $result2;
	}

	static function checkIfBaseAddress($name)
	{
		return !csa($name, "-");
	}

	function setNoClients($no)
	{
		$this->clients_no = $no;
		$this->dbaction = "update";
	}

	function setClients($string)
	{
		$this->ser_clientslist = $string;
		$this->dbaction = "update";
	}

	static function initThisListRule($parent, $class)
	{
		if ($parent->isAdmin()) {
			$res = '__v_table';
		} else {
			if ($parent->isClass('pserver')) {
				$res[] = array('parent_clname', '=', "'{$parent->getClName()}'");
			} else {
				$res[] = array('clientname', '=', "'{$parent->nname}'");
			}
		}

		return $res;
	}
}



