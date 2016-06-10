<?php

class mailinglist_mod_a extends Lxaclass
{
	static $__desc = array("", "", "moderator");
	static $__desc_nname = array("", "", "moderator");

	static function createListAddForm($parent, $class)
	{
		return true;
	}

	static function createListAlist($object, $class)
	{
		$alist[] = "a=updateform&sa=update";
		$alist[] = "a=list&c=mailinglist_mod_a";
		$alist[] = "a=updateform&sa=editfile";
		$alist[] = "a=addform&c=listsubscribe";
		
		return $alist;
	}
}

class Mailinglist extends Lxdb
{
	static $__desc = array("", "", "mailinglist");
	static $__table = 'mailinglist';
	
	static $__desc_nname = array("", "", "mailinglist", URL_SHOW);
	static $__desc_adminemail = array("", "", "admin_email", URL_SHOW);
	static $__desc_listname = array("n", "", "mailinglist_name");
//	static $__desc_adminemail = array("n", "", "admin_email");
	static $__desc_post_members_only_flag = array("f", "", "only_members_can_post");
	static $__desc_post_moderated_flag = array("f", "", "only_moderated_posts");
	static $__desc_post_moderator_only_flag = array("f", "", "only_moderators_can_post");
	static $__desc_archived_flag = array("f", "", "posts_are_archived");
	static $__desc_archive_blocked_flag = array("f", "", "only_moderators_can_request_archives");
	static $__desc_archive_guarded_flag = array("f", "", "only_subscribers_can_request_archive");
	static $__desc_digest_flag = array("f", "", "digest_enables");
	static $__desc_jumpoff_flag = array("f", "", "allow_unconfirmed_sign_off");
	static $__desc_subscriberlist_flag = array("f", "", "maintain_subscriberlist");
	static $__desc_remote_admin_flag = array("f", "", "allow_moderators_remote_admin");
	static $__desc_subscription_mod_flag = array("f", "", "subscription_moderated");
	static $__desc_edit_text_flag = array("f", "", "permit_edit_text");

	static $__desc_text_prefix = array("", "", "prefix");
	static $__desc_text_trailer = array("t", "", "trailer");
	static $__desc_max_msg_size = array("", "", "maximum_message_size");
	static $__desc_min_msg_size = array("", "", "minimum_message_size");
	static $__desc_text_mimeremove = array("t", "", "mime_types_to_be_removed");
	static $__desc_lang = array("t", "", "language");

	static $__acdesc_update_update = array("", "", "switches");
	static $__acdesc_update_editfile = array("", "", "values");
	static $__acdesc_update_archive = array("", "", "browse_archive");

	static $__desc_listsubscribe_l = array("", "", "");

	static function createListAlist($parent, $class)
	{
		$alist[] = "a=list&c=$class";
	//	$alist['__v_dialog_add'] = "a=addform&c=$class";
		
		return $alist;
	}

	function createShowClist($subaction)
	{
		$uform['listsubscribe'] = null;
		
		return $uform;
	}

	function createExtraVariables()
	{
		global $gbl, $sgbl, $login, $ghtml;
		$this->__var_language = get_language();
		$this->__var_charset = get_charset();
	}

	function extraBackup()
	{
		return true;
	}

	function createShowPropertyList(&$alist)
	{
		$alist['property'][] = "a=show";
		$alist['property'][] = "a=updateform&sa=update";
		$alist['property'][] = "a=list&c=mailinglist_mod_a";
		$alist['property'][] = "a=updateform&sa=editfile";
		$alist['property'][] = "a=addform&c=listsubscribe";
		$tmpurl = "a=show&l[class]=ffile&l[nname]=/";
		$alist['property'][] = create_simpleObject(array('url' => "$tmpurl", 'purl' => "a=updateform&sa=archive", 'target' => "", '__internal' => true));
	}

	function createShowAlist(&$alist, $subaction = null)
	{
		return $alist;
	}

	function getFfileFromVirtualList($name)
	{
		global $gbl, $sgbl, $login, $ghtml;

		list($list, $domain) = explode("@", $this->nname);

		$mailpath = mmail__qmail::getDir($domain);
		$mailpath = str_replace($sgbl->__path_mail_root, $sgbl->__path_mail_data, $mailpath);

		$uid = mmail__qmail::getUserGroup($domain, true);
		$username = os_get_user_from_uid($uid);
		$root = "$mailpath/$list/archive";
		$ffile = new Ffile(null, $this->syncserver, $root, $name, $username);
		$ffile->__parent_o = $this;
		$ffile->get();
		$ffile->readonly = true;
		
		return $ffile;
	}

	function updateUpdate($param)
	{
		global $login;

		if (!validate_email($param['adminemail'])) {
		//	throw new lxException($login->getThrow('invalid_email'), '', $param['adminemail']);
		}
		
		return $param;
	}


	function updateform($subaction, $param)
	{
		switch ($subaction) {
			case 'update':
				$vlist['adminemail'] = null;
				$vlist['post_members_only_flag'] = null;
				$vlist['post_moderated_flag'] = null;
				$vlist['post_moderator_only_flag'] = null;
				$vlist['archived_flag'] = null;
				$vlist['archive_blocked_flag'] = null;
				$vlist['archive_guarded_flag'] = null;
				$vlist['digest_flag'] = null;
				$vlist['jumpoff_flag'] = null;
				$vlist['subscriberlist_flag'] = null;
				$vlist['remote_admin_flag'] = null;
				$vlist['subscription_mod_flag'] = null;
				$vlist['edit_text_flag'] = null;
				break;

			case "editfile":
				$vlist['text_prefix'] = null;
				$vlist['text_trailer'] = null;
				$vlist['max_msg_size'] = null;
				$vlist['min_msg_size'] = null;
				$vlist['text_mimeremove'] = null;
				$vlist['__v_updateall_button'] = array();
				break;
		}

		return $vlist;
	}

	function postAdd()
	{
		// If you want to set any defaults. PostAdd function is called after the object is initialized.
		// Store in database the defaults that ezmlm created 
	}

	static function add($parent, $class, $param)
	{
		global $login;

		if (!validate_email($param['adminemail'])) {
			throw new lxException($login->getThrow('invalid_email'), '', $param['adminemail']);
		}

		if (!$param['listname']) {
			throw new lxException($login->getThrow('need_listname'), '', $param['listname']);
		}

		if ($parent->isClient()) {
			$param['nname'] = "{$param['listname']}@lists.{$param['real_clparent_f']}";
			$param['syncserver'] = $parent->mmailsyncserver;
		} else {
			$param['nname'] = "{$param['listname']}@lists.$parent->nname";
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
		$nlist['adminemail'] = null;

		return $nlist;
	}

	static function createListNlist($parent, $view)
	{
		$nlist['nname'] = '70%';
		$nlist['adminemail'] = '30%';

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

	static function AddListForm($parent, $class)
	{
		return self::addform($parent, $class);
	}

	static function addform($parent, $class, $typetd = null)
	{
		if ($parent->isClient()) {
			$list = get_namelist_from_objectlist($parent->getList('domain'));
			$vv = array('var' => 'real_clparent_f', 'val' => array('s', $list));
			$vlist['listname'] = array('m', array('posttext' => "@lists.", 'postvar' => $vv));
		} else {
			$vlist['listname'] = array('m', array('posttext' => "@lists.$parent->nname"));
		}

		$vlist['adminemail'] = null;
		$ret['variable'] = $vlist;
		$ret['action'] = 'add';
		
		return $ret;
	}
}

class all_mailinglist extends mailinglist
{
	static $__desc = array("", "", "all_mailinglist");
	static $__desc_parent_name_f = array("n", "", "domain");
	static $__desc_parent_clname = array("n", "", "domain");

	function isSelect()
	{
		return false;
	}

	static function AddListForm($parent, $class)
	{
		return null;
	}

	static function createListAlist($parent, $class)
	{
	//	return all_mailaccount::createListAlist($parent, $class);
		return all_domain::createListAlist($parent, $class);
	}

	static function initThisListRule($parent, $class)
	{
		global $login;

		if (!$parent->isAdmin()) {
			throw new lxException($login->getThrow("only_admin_can_access"));
		}

		return "__v_table";
	}

	static function createListUpdateForm($object, $class)
	{
		return null;
	}

	static function createListSlist($parent)
	{
		$nlist['nname'] = null;
		$nlist['parent_clname'] = null;
		return $nlist;
	}
}
