<?php

class MailForward extends Lxdb
{
	static $__desc = array("", "", "mail_forward_alias");
	static $__table =  'mailforward';

	static $__desc_nname = array("n", "", "Mail Account", "a=show");
	static $__desc_type = array("n", "", "Mail Account", "a=show");
	static $__desc_type_v_alias = array("n", "", "alias");
	static $__desc_type_v_forward = array("n", "", "forward");
	static $__desc_forwardaddress = array("n", "", "Forward To");
	static $__desc_accountame = array("n", "", "Forward To");

	static function AddListForm($parent, $class)
	{
	//	return self::addform($parent, $class);
	}

	static function addform($parent, $class, $typetd = null)
	{
		if ($parent->isClient()) {
			$list = get_namelist_from_objectlist($parent->getList('domain'));
			$vv = array('var' => 'real_clparent_f', 'val' => array('s', $list));
			$vlist['nname'] = array('m', array('posttext' => "@", 'postvar' => $vv));
		} else {
			$vlist['nname'] = array('m', array('posttext' => "@$parent->nname"));
		}

		if ($typetd['val'] === 'alias') {
			$mlist = get_namelist_from_objectlist($parent->getList('mailaccount'));
			$vlist['forwardaddress'] = array('s', $mlist);
		} else {
			$vlist['forwardaddress'] = null;
		}

		$ret['variable'] = $vlist;
		$ret['action'] = 'add';

		return $ret;
	}

	static function createListAlist($parent, $class)
	{
		$alist[] = "a=show";

		$alist[] = "a=list&c=$class";
		$alist[] = "a=addform&c=$class&dta[var]=type&dta[val]=forward";
		$alist[] = "a=addform&c=$class&dta[var]=type&dta[val]=alias";

		return $alist;
	}

	function createShowUpdateform()
	{
		$uflist['update'] = null;

		return $uflist;
	}

	function updateform($subaction, $param)
	{
		$vlist['nname'] = array('M', $this->nname);
		$vlist['forwardaddress'] = null;
		return $vlist;

		return null;
	}

	static function add($parent, $class, $param)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$param['forwardaddress'] = trim($param['forwardaddress'], "'");
		$param['forwardaddress'] = trim($param['forwardaddress']);
		$param['forwardaddress'] = trim($param['forwardaddress'], '"');

		if ($parent->isClient()) {
			$param['nname'] = "{$param['nname']}@{$param['real_clparent_f']}";
			$param['syncserver'] = $parent->mmailsyncserver;
		} else {
			$param['nname'] = "{$param['nname']}@$parent->nname";
			$param['syncserver'] = $parent->syncserver;
		}

		return $param;
	}

	static function defaultParentClass($parent)
	{
		return "mmail";
	}

	static function createListSlist($parent)
	{
		$nlist['nname'] = null;
		$nlist['forwardaddress'] = null;
		return $nlist;
	}

	static function createListNlist($parent, $view)
	{
		$nlist['nname'] = '50%';
		$nlist['forwardaddress'] = '50%';

		return $nlist;
	}

	static function initThisListRule($parent, $class)
	{
		if ($parent->isClient()) {
			$ret = lxdb::initThisOutOfBand($parent, 'domain', 'mmail', $class);

			return $ret;
		}

		return lxdb::initThisListRule($parent, $class);
	}
}

class all_mailforward extends mailforward
{
	static $__desc =  array("n", "",  "all_mailforward");
	static $__desc_parent_name_f =  array("n", "",  "owner");
	static $__desc_parent_clname =  array("n", "",  "domain");

	function isSelect()
	{
		return false;
	}

	static function initThisListRule($parent, $class)
	{
		global $login;

		if (!$parent->isAdmin()) {
			throw new lxException($login->getThrow("only_admin_can_access"));
		}

		return "__v_table";
	}

	static function createListSlist($parent)
	{
		$nlist['nname'] = null;
		$nlist['parent_clname'] = null;

		return $nlist;
	}

	static function AddListForm($parent, $class)
	{
		return null;
	}

	static function createListAlist($parent, $class)
	{
		return all_domain::createListAlist($parent, $class);
	}

	static function createListNlist($parent, $view)
	{
		$nlist['nname'] = '50%';
		$nlist['parent_name_f'] = '50%';

		return $nlist;
	}

	static function createListUpdateForm($object, $class)
	{
		return null;
	}
}
