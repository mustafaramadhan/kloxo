<?php

class addondomain extends Lxdb
{
	static $__desc = array("n", "", "parked / redirected_domain");
	static $__table = 'addondomain';

	static $__desc_nname = array("n", "", "pointer_domain");
	static $__desc_parent_name_f = array("n", "", "owner");
	static $__desc_parent_clname = array("n", "", "destination");
	static $__desc_real_clparent_f = array("", "", "redirect_to");
	static $__desc_mail_flag = array("f", "", "map_mail");
	static $__desc_ttype = array("", "", "type");
	static $__desc_ttype_v_parked = array("n", "", "parked");
	static $__desc_ttype_v_redirect = array("n", "", "redirected");
	static $__desc_destinationdir = array("", "", "destination_directory");

	static function createListAlist($parent, $class)
	{
		$alist[] = "a=list&c=$class";
		$alist['__v_dialog_park'] = "a=addform&c=addondomain&dta[var]=ttype&dta[val]=parked";
		$alist['__v_dialog_red'] = "a=addform&c=addondomain&dta[var]=ttype&dta[val]=redirect";
		return $alist;
	}

	function display($var)
	{
		if ($var === 'destinationdir') {
			return "http://{$this->getTrueParentO()->nname}/$this->destinationdir";
		}

		return parent::display($var);
	}

	static function createListNlist($parent, $view)
	{
		$nlist['ttype'] = '5%';
		$nlist['nname'] = '100%';
		$nlist['mail_flag'] = '10%';
		$nlist['destinationdir'] = '10%';
		return $nlist;
	}

	static function add($parent, $class, $param)
	{
		$param['nname'] = strtolower($param['nname']);

		if (exists_in_db(null, 'domain', $param['nname'])) {
			throw new lxException('domain_already_exists_as_virtual', 'nname', $param['nname']);
		}

		validate_domain_name($param['nname']);

		if ($parent->isClient()) {
		} else {
			$param['real_clparent_f'] = $parent->nname;
		}

		return $param;
	}


	static function initThisListRule($parent, $class)
	{
		if ($parent->isClient()) {
			$ret = lxdb::initThisOutOfBand($parent, 'domain', 'domain', $class);
			return $ret;

		}

		return lxdb::initThisListRule($parent, $class);

	}

	function isSync()
	{
		global $gbl, $sgbl, $login, $ghtml;

		// Don't do anything if it is syncadd or if it is restore... 
		// When restoring, addondomain is handled by the domain itself, and then the web backup.
		if ($this->dbaction === 'syncadd') {
			return false;
		}

		if ($this->dbaction === 'syncdelete') {
			return false;
		}

		if (isset($gbl->__restore_flag) && $gbl->__restore_flag) {
			return false;
		}

		return false;
	}

	function postAdd()
	{
		$parent = $this->getParentO();

		if ($parent->isClient()) {
			// You have to load the domain here. Otherwise, the synctosystem won't get executed on the domain.
			dprint("yes\n");
			$domain = $parent->getFromList('domain', $this->getTrueParentO()->nname);
			$domain->addToList('addondomain', $this);
		} else {
			$domain = $parent;
		}

		$web = $domain->getObject('web');
		$web->setUpdateSubaction('addondomain');

		if ($this->isOn('mail_flag')) {
			$mmail = $domain->getObject('mmail');
			$mmail->__var_aliasdomain = $this->nname;
			$mmail->setUpdateSubaction('add_alias');
		}

		$dns = $domain->getObject('dns');
		$dns->setUpdateSubaction('addondomain');
	}

	function deleteSpecific()
	{
		$parent = $this->getParentO();

		if ($parent->isClient()) {
			// You have to load the domain here. Otherwise, the synctosystem won't get executed on the domain.
			dprint("yes\n");
			$domain = $parent->getFromList('domain', $this->getTrueParentO()->nname);
		} else {
			$domain = $parent;
		}

		$web = $domain->getObject('web');
		$web->setUpdateSubaction('addondomain');

		$mmail = $domain->getObject('mmail');
		$mmail->__var_aliasdomain = $this->nname;
		$mmail->setUpdateSubaction('delete_alias');

		$dns = $domain->getObject('dns');
		$dns->setUpdateSubaction('addondomain');
	}

	static function defaultParentClass($parent)
	{
		return "domain";
	}

	static function addform($parent, $class, $typetd = null)
	{
		$vlist['nname'] = null;

		if ($parent->isClient()) {
			$list = get_namelist_from_objectlist($parent->getList('domain'));
			$vv = array('var' => 'real_clparent_f', 'val' => array('s', $list));
			$vlist['nname'] = array('m', array('posttext' => "=>", 'postvar' => $vv));
		} else {
			$vlist['nname'] = array('m', array('posttext' => "=>$parent->nname"));
		}

		if ($typetd['val'] === 'redirect') {
			$vlist['destinationdir'] = array('m', null);
		}

		$vlist['mail_flag'] = null;
		$ret['variable'] = $vlist;
		$ret['action'] = 'add';

		return $ret;
	}

	static function createListSlist($parent)
	{
		// relate to bug #50 - trick for process add/delete parked/redirect domains
		// because after add/delete always back to list!
		
		// Bug #863 - MR - but look like not need!

		// $web = $parent->getObject('web');
		// have trouble when use addondomain, so use full_update
		//	$web->setUpdateSubaction('full_update');
		//	$web->setUpdateSubaction('addondomain');

		// original code...

		//$sq = new Sqlite(null, 'domain');
		//$s = $sq->getTable(array('nname'));
		//$s = get_namelist_from_arraylist($s);
		//$s = lx_array_merge(array(array('--any--'), $s));
		$nlist['nname'] = null;
		//$nlist['parent_clname'] = array('s', $s);
		$nlist['ttype'] = array('s', array('--any--', 'parked', 'redirect'));
		$nlist['parent_clname'] = null;

		return $nlist;
	}
}


class all_addondomain extends addondomain
{
	static $__desc = array("n", "", "all_pointer_domain");

	function isSelect()
	{
		return false;
	}

	static function initThisListRule($parent, $class)
	{
		if (!$parent->isAdmin()) {
			throw new lxexception("only_admin_can_access", '', "");
		}

		return "__v_table";
	}


	static function createListAlist($parent, $class)
	{
		return all_domain::createListAlist($parent, $class);
	}

}
