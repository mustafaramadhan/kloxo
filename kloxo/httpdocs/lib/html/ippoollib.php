<?php

class ippserver_a extends Lxaclass
{
}

class ippoolextraip_a extends Lxaclass
{
	static $__desc = array("", "", "extra_ip");
	static $__desc_nname = array("n", "", "ipaddress");

	static function createListAlist($parent, $class)
	{
		$alist[] = 'a=show';
		$alist[] = 'a=list&c=ippoolip';
		$alist[] = 'a=list&c=ippoolextraip_a';
		$alist[] = 'a=list&c=ippoolexceptionip_a';
		$alist[] = 'a=list&c=ippoolpingip_a';
		
		return $alist;
	}

	static function createListAddForm($parent, $class) { return true;}
}

class ippoolexceptionip_a extends Lxaclass
{
	static $__desc = array("", "", "exception_ip");
	static $__desc_nname = array("n", "", "ipaddress");

	static function createListAlist($parent, $class)
	{
		return ippoolextraip_a::createListAlist($parent, $class);
		
		return $alist;
	}
	static function createListAddForm($parent, $class) { return true;}
}


class ippoolpingip_a extends Lxaclass
{
	static $__desc = array("", "", "pinged_ip");
	static $__desc_nname = array("n", "", "ipaddress");

	static function createListAlist($parent, $class)
	{
		return ippoolextraip_a::createListAlist($parent, $class);
		
		return $alist;
	}
}

class ippoolip extends Lxaclass
{
	static $__desc = array("", "", "individual_ip");
	static $__desc_nname = array("n", "", "ipaddress");
	static $__desc_assigned = array("n", "", "assigned");

	static function initThisList($parent, $class)
	{
		$re = $parent->getIndividualIpList();
		
		foreach($re as $r) {
			$ass = ippool::checkIfAlreadyAssigned('vps', $r);
			$result[] = array('nname' => $r, 'assigned' => $ass);
		}
		
		$parent->setListFromArray($parent->__masterserver, $parent->__readserver, 'ippoolip', $result);
	}

	function isSelect() { return false ; }
	
	static function createListNlist($parent, $view)
	{
		$nlist['nname'] = '100%';
		$nlist['assigned'] = '40%';
		
		return $nlist;
	}
	static function createListAlist($parent, $class)
	{
		return ippoolextraip_a::createListAlist($parent, $class);
	}
}

class ippool extends Lxdb 
{
	static $__desc = array("", "", "ip_pool");
	static $__desc_nname = array("n", "", "name_of_the_ip_pool", "a=show");
	static $__desc_freeflag = array("e", "", "S:IP_Free");
	static $__desc_freeflag_v_on = array("e", "", "IP_Free");
	static $__desc_freeflag_v_off = array("e", "", "All_assigned");
	static $__desc_freeflag_v_dull = array("e", "", "All_assigned");
	static $__desc_firstip = array("n", "", "first_ip_address");
	static $__desc_lastip = array("n", "", "last_ip_address");
	static $__desc_stat_f = array("S", "", "assigned/Total");
	static $__desc_pserver_list = array("n", "", "servers_this_is_applicable_to");
	static $__desc_coma_ippserver_a = array("n", "", "nodes_this_is_applicable_to");
	static $__desc_ippoolip_l = array("", "", "");
	static $__desc_networkgateway =  array("", "",  "gateway");
	static $__desc_networknetmask =  array("", "",  "NetMask");
	static $__desc_nameserver	 = array("", "",  "resolv_entries");

	function createExtraVariables()
	{
		$this->recalibrate();
	}

	static function add($parent, $class, $param)
	{
		global $login;

		$param['ttype'] = 'vps';

		$param['firstip'] = trim($param['firstip']);
		$param['lastip'] = trim($param['lastip']);

		validate_ipaddress($param['firstip']);
		validate_ipaddress($param['lastip']);

		if (!$param['pserver_list']) {
			throw new lxException($login->getThrow("need_to_select_pserver"));
		}

		$param['pserver_list'] = explode(',', $param['pserver_list']);

		$first = strtil($param['firstip'], ".");
		$last = strtil($param['lastip'], ".");

		if ($first !== $last) {
			throw new lxException($login->getThrow("first_and_last_should_be_same_network"), ''. "{$first} - {$last}");
		}

		return $param;
	}

	static function createListAlist($parent, $class)
	{
		$alist[] = "a=list&c=ippool";
		$alist[] = "a=addform&c=ippool";
		$alist[] = "a=updateform&sa=ippool";
		return $alist;
	}

	function createShowPropertyList(&$alist)
	{

		$alist['property'][] = 'a=show';
		$alist['property'][] = 'a=list&c=ippoolip';
		$alist['property'][] = 'a=list&c=ippoolextraip_a';
		$alist['property'][] = 'a=list&c=ippoolexceptionip_a';
		$alist['property'][] = 'a=list&c=ippoolpingip_a';
	}

	function createShowUpdateform()
	{
		$ulist['update'] = null;
		
		return $ulist;
	}

	function updateUpdate($param)
	{
		global $login;

		$param['firstip'] = trim($param['firstip']);
		$param['lastip'] = trim($param['lastip']);

		validate_ipaddress($param['firstip']);
		validate_ipaddress($param['lastip']);

		$first = strtil($param['firstip'], ".");
		$last = strtil($param['lastip'], ".");

		if ($first !== $last) {
			throw new lxException($login->getThrow("first_and_last_should_be_same_network"), ''. "{$first} - {$last}");
		}

		$param['pserver_list'] = explode(',', $param['pserver_list']);
		
		return $param;
	}

	function postUpdate()
	{
		// We need to write because reads everything from the database.
		$this->write();

		$this->convertPserverlist();
	}

	function recalibrate()
	{
		$this->freeflag = 'on';
		$fip = $this->getFreeIp(10000);
		
		if ($fip) {
			$this->freeflag = 'on';
		} else {
			$this->freeflag = 'dull';
		}
	}

	function updateform($subaction, $param)
	{
		$vlist['nname'] = array('M', null);
		$vlist['stat_f'] = array('M', $this->getStat_f());
		$vlist['firstip'] = null;
		$vlist['lastip'] = null;
		$vlist['nameserver'] = null;
		$vlist['networkgateway'] = null;
		if (!$this->networknetmask) { $this->networknetmask = "255.255.255.0"; }
		$vlist['networknetmask'] = null;
		$pslist = get_namelist_from_objectlist($this->getParentO()->getRealPserverList('vps'));


		$this->pserver_list = get_namelist_from_objectlist($this->ippserver_a);
		$vlist['server_detail_f'] = array('M', pserver::createServerInfo($pslist, "vps"));
		$vlist['pserver_list'] = array('U', $pslist);
		
		return $vlist;
	}

	function getStat_f()
	{
	//  return "$this->assigned/$this->total";

		$list = $this->getIndividualIpList();
		$assigned = 0;
		
		foreach($list as $l) {
			if (self::checkIfAlreadyAssigned("vps", $l)) {
				$assigned++;
			}
		}
		
		$tot = count($list);
		
		return "$assigned/$tot";
	}

	static function createListNlist($parent, $view)
	{
		$nlist['freeflag'] = '10%';
		$nlist['nname'] = '100%';
		$nlist['firstip'] = '40%';
		$nlist['lastip'] = '40%';
		$nlist['stat_f'] = '40%';
		$nlist['pserver_list'] = '40%';

		return $nlist;
	}

	function display($var)
	{
		if ($var === 'pserver_list') {
			$string = implode(',', get_namelist_from_objectlist($this->ippserver_a));
			if (strlen($string) > 21) {
				$string = substr($string, 0, 21) . "...";
			}
			$this->$var = $string;
		}

		if ($var === 'stat_f') {
			return $this->getStat_f();
		}

		return parent::display($var);
	}

	function getFreeIp($num)
	{
		if (!$num) { return; }

		if (!$this->isOn('freeflag')) { return null; }

		$res = null;
		$list = $this->getIndividualIpList();
		$sq = new Sqlite(null, 'tmpipassign');

		$ctime = time();
		$ctime -= 100;
		$sq->rawQuery("delete from tmpipassign where (ddate + 0) < $ctime");

		$pingip = null;
		
		foreach($list as $l) {
			$l = trim($l);

			$p = $sq->getRowsWhere("nname = '$l'");

			if ($p) { continue; }

			if (ippool::checkIfAlreadyAssigned('vps', $l)) {
			//  log_log("ip_pool", "$l is already assigned skipping...\n");
				continue;
			}

			if ($num <= 100) {
				try {
					full_validate_ipaddress($l);
				} catch (exception $e) {
					log_log("ip_pool", "Can ping $l... Skipping...\n");
					$pingip[] = $l;
					
					continue;
				}
			}

			$res[] = $l;
			
			if (count($res) >= $num) {
				return $res;
			}
		}

		$writeflag = false;

		if (!$res) {
			$this->freeflag = 'dull';
			$writeflag = true;
		}

		if ($pingip) {
			$writeflag = true;
			
			foreach($pingip as $p) {
				$op = new ippoolpingip_a(null, null, $p);
				$this->ippoolpingip_a[$p] = $op;
			}
		}

		if ($writeflag) {
			$this->setUpdateSubaction();
			$this->write();
		}

		return $res;
	}

	static function addToTmpIpAssign($l)
	{
		$sq = new Sqlite(null, 'tmpipassign');
		$date = time();
		$sq->rawQuery("insert into tmpipassign (nname, ddate) values ('$l', '$date');");
	}

	function getIndividualIpList()
	{
		$base = explode(".", $this->lastip);
		$end = array_pop($base);

		$base = explode(".", $this->firstip);
		$start = array_pop($base);
		$base = implode(".", $base);
		
		for($i = $start ; $i <= $end ; $i++) {
			$out[] = "$base.$i";
		}

		$ex = get_namelist_from_objectlist($this->ippoolextraip_a);
		$out = lx_merge_good($out, $ex);

		$exception = get_namelist_from_objectlist($this->ippoolexceptionip_a);
		
	//  dprintr($exception);
		
		foreach($out as $k => $v) {
			if (isset($exception[$v])) {
				unset($out[$k]);
			}
		}

		$exception = get_namelist_from_objectlist($this->ippoolpingip_a);
		
	//  dprintr($exception);
		
		foreach($out as $k => $v) {
			if (isset($exception[$v])) {
				unset($out[$k]);
			}
		}

		return $out;
	}

	static function checkIfAlreadyAssigned($class, $ipaddr)
	{
		$sq = new Sqlite(null, 'ipaddress');
		$res = $sq->getRowsWhere("ipaddr = '$ipaddr'", array('nname'));

		if ($res) { return $res[0]['nname']; }

		$sq = new Sqlite(null, 'vps');
		$res = $sq->getRowsWhere("coma_vmipaddress_a LIKE '%,$ipaddr,%'", array('nname'));

		if ($res) { return $res[0]['nname']; }
		
		return false;
	}

	function convertPserverlist()
	{
		foreach($this->pserver_list as $n) {
			$ippserver_a[] = new ippserver_a(null, null, $n);
		}
		
		$this->ippserver_a = $ippserver_a;
	}

	function postAdd()
	{
		$this->convertPserverlist();
	}

	function isSync() { return false; }

	static function addform($parent, $class, $typetd = null)
	{

		$vlist['nname'] = null;
		$vlist['firstip'] = null;
		$vlist['lastip'] = null;
		$vlist['nameserver'] = null;
		$vlist['networkgateway'] = null;

		$pslist = get_namelist_from_objectlist($parent->getRealPserverList('vps'));
		$vlist['server_detail_f'] = array('M', pserver::createServerInfo($pslist, "vps"));
		$vlist['pserver_list'] = array('U', $pslist);

		$ret['action'] = 'add';
		$ret['variable'] = $vlist;
		
		return $ret;
	}

	static function initThisListRule($parent, $class)
	{
		if ($parent->isAdmin()) {
			return "__v_table";
		}

		if ($parent->get__table() === 'pserver') {
			return array("coma_ippserver_a", "LIKE", "'%,$parent->nname,%'");
		}
	}
}
