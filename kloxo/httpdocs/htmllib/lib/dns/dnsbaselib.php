<?php

class LxDnsClass extends Lxaclass
{
}

class dns_record_a extends LxDnsClass
{
	static $__desc = array("", "", "dns_record");
	static $__desc_nname = array("n", "", "dns_record");
	static $__desc_param = array("n", "", "value");
	static $__desc_hostname = array("n", "", "hostname", "a=updateform&sa=edit");
	static $__desc_ttype = array("", "", "type");
	static $__desc_ttype_v_mx = array("", "", "MX");
	static $__desc_ttype_v_ns = array("", "", "NS");
	static $__desc_ttype_v_a = array("", "", "A");
	static $__desc_ttype_v_aaaa = array("", "", "AAAA");
	static $__desc_ttype_v_txt = array("", "", "TXT");
	static $__desc_ttype_v_cname = array("", "", "CNAME");
	static $__desc_ttype_v_fcname = array("", "", "FCNAME");
	static $__desc_priority = array("", "", "priority");

	function isSelect()
	{
		if ($this->nname === 'a___base__') {
			return false;
		}

		if ($this->nname === 'a_mail') {
			return true;
		}

		return true;
	}

	function updateform($subaction, $param)
	{
		$vlist['hostname'] = array('M', null);
		$vlist['param'] = null;

		return $vlist;
	}

	function isAction($var)
	{
		if ($this->ttype === 'ns') {
			return false;
		}

		return true;
	}

	static function createListNlist($parent, $view)
	{
	//	$nlist['nname'] = '10%';
		$nlist['hostname'] = '10%';
		$nlist['ttype'] = '10%';
		$nlist['priority'] = '10%';
		$nlist['param'] = '100%';

		return $nlist;
	}

	static function perPage()
	{
		return 6000;
	}

	function display($var)
	{
		if (!isset($this->$var)) {
			return '-';
		}

		if ($var === 'ttype') {
			return strtoupper($this->$var);
		}

		if ($var === 'param') {
			if ($this->ttype === 'txt') {
				if (strlen($this->$var) > 30) {
					return substr($this->$var, 0, 30) . "...";
				}
			}
		}

		return $this->$var;
	}

	static function add($parent, $class, $param)
	{
		if ($param['ttype'] === 'mx') {
			// Validates domain
			if (!preg_match('/^([a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?\.)+(([a-z]{2,6})|(xn--[a-z0-9]{4,14}))$/i',
					$param['param'])) {
				throw new lxexception('invalid_domain', 'param');
			}

			$param['nname'] = "{$param['ttype']}_{$param['priority']}";
			$param['hostname'] = $parent->nname;
		} else if ($param['ttype'] === 'ns') {
			// Validates domain
			if (!preg_match('/^([a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?\.)+(([a-z]{2,6})|(xn--[a-z0-9]{4,14}))$/i',
					$param['param'])) {
				throw new lxexception('invalid_domain', 'param');
			}

			$param['nname'] = "{$param['ttype']}_{$param['param']}";
		} else if ($param['ttype'] === 'a' || $param['ttype'] === 'aaaa') {
			// Validates subdomain
			if (!preg_match("/^(([a-zA-Z]|[a-zA-Z][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z]|[A-Za-z][A-Za-z0-9\-]*[A-Za-z0-9])$/",
					$param['hostname'])) {
				throw new lxexception('invalid_subdomain', 'hostname');
			}

			// Validates both ipv4 and ipv6
			if (!preg_match('/^(?:(?>(?>([a-f0-9]{1,4})(?>:(?1)){7})|(?>(?!(?:.*[a-f0-9](?>:|$)){8,})((?1)(?>:' .
                '(?1)){0,6})?::(?2)?))|(?>(?>(?>(?1)(?>:(?1)){5}:)|(?>(?!(?:.*[a-f0-9]:){6,})((?1)(?>:(?1)){0,4})?:' .
                ':(?>(?3):)?))?(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9])(?>\.(?4)){3}))$/iD', $param['param'])) {
				throw new lxexception('invalid_ip_address', 'param');
			}
			$param['nname'] = "{$param['ttype']}_{$param['hostname']}_{$param['param']}";
		} else if ($param['ttype'] === 'cname') {
			// Validates hostname subdomain
			if (!preg_match("/^(([a-zA-Z]|[a-zA-Z][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z]|[A-Za-z][A-Za-z0-9\-]*[A-Za-z0-9])$/",
					$param['hostname'])) {
				throw new lxexception('invalid_subdomain', 'hostname');
			}

			// Validates value subdomain
			if (!preg_match("/^(([a-zA-Z]|[a-zA-Z][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z]|[A-Za-z][A-Za-z0-9\-]*[A-Za-z0-9])$/",
					$param['param']) && $param['param'] != "__base__") {
				throw new lxexception('invalid_subdomain', 'param');
			}

			$param['nname'] = "{$param['ttype']}_{$param['hostname']}";
		} else if ($param['ttype'] === 'fcname') {
			// Validates hostname subdomain
			if (!preg_match("/^(([a-zA-Z]|[a-zA-Z][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z]|[A-Za-z][A-Za-z0-9\-]*[A-Za-z0-9])$/",
					$param['hostname'])) {
				throw new lxexception('invalid_subdomain', 'hostname');
			}

			// Validates value domain
			if (!preg_match('/^([a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?\.)+(([a-z]{2,6})|(xn--[a-z0-9]{4,14}))$/i',
					$param['param'])) {
				throw new lxexception('invalid_domain', 'param');
			}

			$param['nname'] = "{$param['ttype']}_{$param['hostname']}";
		} else if ($param['ttype'] === 'txt') {
			// Validates hostname subdomain
			if (!preg_match("/[0-9a-zA-Z-._]$/", $param['hostname'])) {
				throw new lxexception('invalid_subdomain', 'hostname');
			}
			$param['nname'] = "{$param['ttype']}_{$param['hostname']}";
		} else {
			$param['nname'] = "{$param['ttype']}_{$param['hostname']}";
		}

		return $param;
	}

	static function addform($parent, $class, $typetd = null)
	{
		if ($typetd['val'] === 'ns') {
			$vlist['param'] = null;
		} else if ($typetd['val'] === 'mx') {
			$vlist['priority'] = array('s', array('5', '10', '20', '30', '40', '50', '60', '70', '80', '90', '100'));
			$vlist['param'] = null;
		} else if ($typetd['val'] === 'cname') {
			$vlist['hostname'] = array('m', array('posttext' => ".$parent->nname."));
			$vlist['param'] = array('m', array('posttext' => ".$parent->nname."));
			$vlist['__m_message_pre'] = 'vv_dns_blank_message';
		} else if ($typetd['val'] === 'fcname') {
			$vlist['hostname'] = array('m', array('posttext' => ".$parent->nname."));
			$vlist['param'] = array('m', array('posttext' => ""));
			$vlist['__m_message_pre'] = 'vv_dns_blank_message';
		} else {
			$vlist['hostname'] = array('m', array('posttext' => ".$parent->nname."));
			$vlist['param'] = null;
			$vlist['__m_message_pre'] = 'vv_dns_blank_message';
		}

		$ret['variable'] = $vlist;
		$ret['action'] = 'Add';

		return $ret;
	}
}

class Mx_rec_a extends LxDnsclass
{
}

class Ns_rec_a extends Lxdnsclass
{
}

class Txt_rec_a extends Lxdnsclass
{
}

class A_rec_a extends Lxdnsclass
{
}

class Cn_rec_a extends Lxdnsclass
{
}


abstract class Dnsbase extends Lxdb
{
	// Mysql
	static $__desc_ttl = array("", "", "ttl_(seconds)");
	static $__desc_syncserver = array("sd", "", "primary_dns");
	static $__desc_ns_rec_a = array("", "", "ns_record");
	static $__desc_a_rec_a = array("", "", "a_record");
	static $__desc_mx_rec_a = array("", "", "mx_record");
	static $__desc_cn_rec_a = array("", "", "cn_record");
	static $__desc_zone_type = array("", "", "type_of_dns_zone_file");
	static $__desc_nameserver_f = array("n", "", "primary_DNS");
	static $__desc_newdnstemplate_f = array("n", "", "new_dns_template");
	static $__desc_secnameserver_f = array("", "", "secondary_DNS");
	static $__desc_soanameserver = array("", "", "SOA_nameserver");
	static $__acdesc_update_parameter = array("", "", "general_settings");
	static $__acdesc_update_switchdnsserver = array("", "", "switch_server");
	static $__acdesc_update_rebuild = array("", "", "rebuild");

	function createDefaultTemplate($webipaddress, $mmailipaddress = "0.0.0.0.0", $nameserver = "defaultnameserver", $secnamserver = null)
	{
		$this->ttl = "86000";

		if (!preg_match('/^([a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?\.)+(([a-z]{2,6})|(xn--[a-z0-9]{4,14}))$/i',
				$nameserver)) {
			throw new lxexception('invalid_domain_in_primary_ns', 'nameserver_f');
		}

		$this->addRec('ns', $nameserver, $nameserver);

		if ($secnamserver) {
			if (!preg_match('/^([a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?\.)+(([a-z]{2,6})|(xn--[a-z0-9]{4,14}))$/i',
					$secnamserver)) {
				throw new lxexception('invalid_domain_in_secondary_ns', 'secnameserver_f');
			}

			$this->addRec('ns', $secnamserver, $secnamserver);
		}

		// Extra dot added at the end of a_rec...
		$cpip = getOneIPForServer("localhost");

		if (!$cpip) {
			$cpip = $webipaddress;
		}

		$this->addRec("a", "cp", $cpip);
		$this->addRec("a", "__base__", $webipaddress);
		$this->addRec("a", "ns", $webipaddress);
		$this->addRec("a", "ns1", $webipaddress);
		$this->addRec("a", "ns2", $webipaddress);
		$this->addRec("a", "mail", $mmailipaddress);
		$this->addRec("cn", "www", "__base__");
		$this->addRec("cn", "ftp", "__base__");
		$this->addRec("cn", "webmail", "mail");
		$this->addRec("cn", "lists", "mail");
		$this->addRec("mx", "10", "mail.$this->nname");

		// MR -- just testing!!!

	//	$this->addRec("txt", "__base__", "v=spf1 a mx ~all");

	//	$this->addRec("txt", "_domainkey", "t=y; o=-; r=postmaster@<%domain%>");
	//	$this->addRec("txt", "private._domainkey", "v=DKIM1; g=*; k=rsa; p=<%key%>");

		return;
	}

	function addRec($ttype, $name, $param)
	{
		$rname = "{$ttype}_$name";
		$__temp = new dns_record_a(null, null, $rname);

		if ($ttype === 'mx') {
			$__temp->hostname = $this->nname;
			$__temp->priority = $name;
			$__temp->param = $param;
		} else {
			$__temp->param = $param;
			$__temp->hostname = $name;
		}

		$__temp->ttype = $ttype;

		if (!isset($this->dns_record_a)) {
			$this->dns_record_a = array();
		}

		$this->dns_record_a[$rname] = $__temp;
		$this->setUpdateSubaction('subdomain');
	}

	function addDomainKey($key)
	{
		$this->addRec("txt", "_domainkey", "t=y; o=-; r=postmaster@{$this->nname}");
	//	$this->addRec("txt", "private._domainkey", "k=rsa; p=$key");
		$this->addRec("txt", "private._domainkey", "v=DKIM1; g=*; k=rsa; p=$key");
	}

	function RemoveDomainKey()
	{
		foreach ($this->dns_record_a as $k => $v) {
			if ($v->ttype === 'txt' && ($v->hostname === "_domainkey" || $v->hostname === 'private._domainkey')) {
				dprint("removing domainkey for $this->nname\n");

				unset($this->dns_record_a[$k]);
			}
		}
	}

	function getIpForBaseDomain()
	{
		foreach ($this->dns_record_a as $d) {
			if ($d->ttype === 'a' && $d->hostname === '__base__') {
				return $d->param;
			}
		}

		return '0.0.0.0';
	}

	function copyObject($dns)
	{
		//$this->ipaddress = $dns->ipaddress;
		$this->ttl = $dns->ttl;

		if ($dns->isClass('dns')) {
			$this->soanameserver = $dns->soanameserver;
		} else {
			$this->soanameserver = str_replace($dns->nname, $this->nname, $dns->soanameserver);
		}

		$this->zone_type = $dns->zone_type;
		$name = $dns->nname;

		foreach ($dns->dns_record_a as $k => $o) {

			if ($dns->isClass('dns') && $o->ttype === 'ns') {
				$hostname = $o->hostname;
				$param = $o->param;
				$nname = $o->nname;
			} else {
				$hostname = str_replace($dns->nname, $this->nname, $o->hostname);
				$param = str_replace($dns->nname, $this->nname, $o->param);
				$nname = str_replace($dns->nname, $this->nname, $o->nname);
			}

			$this->dns_record_a[$nname] = new dns_record_a(null, null, $nname);
			$this->dns_record_a[$nname]->hostname = $hostname;
			$this->dns_record_a[$nname]->ttype = $o->ttype;

			if (isset($o->priority)) {
				$this->dns_record_a[$nname]->priority = $o->priority;
			}

			$this->dns_record_a[$nname]->param = $param;
		}
	}

	function copyObjectWithSave($dnstemplate)
	{
		$saved = null;

		foreach ($this->dns_record_a as $k => $v) {
			if ($v->ttype === 'txt') {
				$saved[$k] = $v;
			}
		}

		$this->dns_record_a = null;
		$this->copyObject($dnstemplate);

		foreach ($saved as $k => $v) {
			if (!isset($this->dns_record_a[$k])) {
				$this->dns_record_a[$k] = $v;
			}
		}
	}

	function updateRebuild($param)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$dnstemplatename = $param['newdnstemplate_f'];
		$dnstemplate = new Dnstemplate($this->__masterserver, $this->__readserver, $dnstemplatename);

		// If template get the ip from the template.
		$dnstemplate->get();
		$this->copyObjectWithSave($dnstemplate);
		$gbl->__ajax_refresh = true;
		$this->rootpassword_changed = 'on';

		return $param;
	}

	function postAdd()
	{
		$this->createDefaultTemplate($this->webipaddress, $this->mmailipaddress, $this->nameserver_f, $this->secnameserver_f);
	}

	function createShowClist($subaction)
	{
		$clist["dns_record_a"] = null;

		return $clist;
	}

	function isRightParent()
	{
		return ($this->getParentO()->getClName() === $this->parent_clname);
	}

	function createShowPropertyList(&$alist)
	{
		global $gbl, $sgbl, $login, $ghtml;

		if ($this->isRightParent()) {
			$alist['property'][] = "a=show";

			if (!cse($this->get__table(), "template") && $sgbl->isKloxo()) {
				$alist['property'][] = "a=updateform&sa=rebuild";
			}

			$alist['property'][] = 'a=addform&c=dns_record_a&dta[var]=ttype&dta[val]=ns';
			$alist['property'][] = 'a=addform&c=dns_record_a&dta[var]=ttype&dta[val]=a';
			$alist['property'][] = 'a=addform&c=dns_record_a&dta[var]=ttype&dta[val]=cname';
			$alist['property'][] = 'a=addform&c=dns_record_a&dta[var]=ttype&dta[val]=fcname';
			$alist['property'][] = 'a=addform&c=dns_record_a&dta[var]=ttype&dta[val]=mx';
			$alist['property'][] = 'a=addform&c=dns_record_a&dta[var]=ttype&dta[val]=aaaa';
			$alist['property'][] = 'a=addform&c=dns_record_a&dta[var]=ttype&dta[val]=txt';
			$alist['property'][] = 'a=updateform&sa=parameter';

		//	$alist[] = 'a=updateform&sa=parameter';
		}
	}

	function fixParentClName()
	{
		foreach ($this->dns_record_a as $d) {
			if (isset($d->parent_clname)) {
				return;
			}

			$d->parent_clname = $this->getClName();
		}

		$this->setUpdateSubaction();
		$this->write();
	}

	function createShowAlist(&$alist, $subaction = null)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$this->fixParentClName();

		if (!cse($this->get__table(), "template")) {
			//$alist['__title_main'] = $this->getTitleWithSync();
			/*
			$alist['action'][] = "a=update&sa=backup";
			$alist['action'][] = "a=updateform&sa=restore";
			*/
		} else {
			//$alist['__title_main'] = $login->getKeywordUc('resource');
		}

		if ($this->get__table() === 'dnstemplate') {
			//$alist[] = 'a=updateform&sa=ipaddress';
		} else {
		}

		return $alist;
	}

	static function getIpaddressList($parent)
	{
		$db = new Sqlite($parent->__masterserver, 'ipaddress');
		$res = $db->getTable(array('ipaddr'));
		$res = get_namelist_from_arraylist($res, 'ipaddr');

		return $res;
	}

	function updateform($subaction, $param)
	{
		global $gbl, $sgbl, $login, $ghtml;

		switch ($subaction) {

			// ONly from dnstemplate
			case "ipaddress":
				$res = Dnsbase::getIpaddressList($this);
				$vlist['ipaddress'] = array('s', $res);
				return $vlist;

			case "parameter":
				foreach ($this->dns_record_a as $d) {
					if ($d->ttype === 'ns') {
						$nslist[] = $d->param;
					}
				}
				$vlist['ttl'] = null;
				$vlist['soanameserver'] = array('s', $nslist);
				return $vlist;

			case "switchdnsserver":
				$vlist['syncserver'] = array('s', $login->getServerList('syncserver'));
				return $vlist;


			case "rebuild":
				$vlist['newdnstemplate_f'] = array('s', domainbase::getDnsTemplateList($login));
				$vlist['__v_updateall_button'] = array();
				return $vlist;

		}

		return parent::updateform($subaction, $param);
	}

	function updateSwitchDnsServer($param)
	{
		global $gbl, $sgbl, $login, $ghtml;

		// Not much checking is needed now. You just add the files. Don't delete it from the old place.
		// After all it is just one single dns file. We will come up with a better logic later.

		$this->syncserver = $param['syncserver'];

		$domain = $this->getParentO();
		$domain->dnspserver = $this->syncserver;
		$domain->setUpdateSubaction();
		$domain->write();

		$this->dbaction = 'syncadd';
		$this->was();

	/*
		$this->dbaction = 'syncdelete';
		$this->was();
	*/

		$ghtml->print_redirect_back_success('dns_switched_successfuly', null);

		exit;
	}
}
