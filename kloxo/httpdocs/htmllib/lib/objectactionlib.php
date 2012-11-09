<?php 

function webcommandline_main()
{
	global $gbl, $sgbl, $login, $ghtml; 
	global $argv;


	ob_start();

	$opt = $_REQUEST;

	// MR -- why && instead || ? (This is because its an inverted check, both need to be true to trigger)
	if ($opt['login-class'] !== 'client' && $opt['login-class'] !== 'auxiliary') { // This works.
//	if ($opt['login-class'] !== 'client' || $opt['login-class'] !== 'auxiliary') { // This is incorrect
		json_print("error", $opt, "__error_only_clients_and_auxiliary_allowed_to_login");
		log_log("web_command", "__error_only_clients_and_auxiliary_allowed_to_login");
		exit;
	}

	log_log("web_command", var_export($opt, true));
	//initProgram('admin');

	if (!check_raw_password($opt['login-class'], $opt['login-name'], $opt['login-password'])) {
		json_print("error", $opt, "_error_login_error");
		log_log("web_command", "__error_login_error");
		exit;
	}

	if (check_disable_admin($opt['login-name'])) {
		json_print("error", $opt, "_error_login_error");
		log_log("web_command", "__error_admin_is_disabled");
		exit;
	}

	$classname = $opt['login-class'];
	$lobject = new $classname(null, 'localhost', $opt['login-name']);
	$lobject->get();
	if ($lobject->dbaction === 'add') {
		json_print("error", $opt, "__error_login_error\n");
		log_log("web_command", "__error_login_error");
		exit;
	}

	if ($classname === 'auxiliary') {
		$login = $lobject->getParentO();
		$login->__auxiliary_object = $lobject;
	} else {
		$login = $lobject;
	}


	if ($opt['action'] === 'simplelist') {
		$must = array('action', 'resource');
	} else if ($opt['action'] === 'getproperty') {
		$must = array('action');
	} else {
		$must = array('action', 'class');
	}

	$pk = array_keys($opt);
	foreach($must as $m) {
		if (!array_search_bool($m, $pk)) {
			$string = implode("_", $must);
			json_print("error", $opt, "__error_need_$string\n");
			log_log("web_command", "__error_need_$string");
			exit;
		}
	}

	$func = "__cmd_desc_{$opt['action']}";

	try {
		$list = $func($opt);
	} catch (exception $e) {
		while(@ob_end_clean());
		json_print("error", $opt, "__error_{$e->getMessage()}");
		log_log("web_command", "__error_{$e->getMessage()}");
		exit;
	}

	if ($opt['action'] === 'simplelist') {
		json_print_result($opt, $list);
	} else if ($opt['action'] === 'getproperty') {
		json_print_result($opt, $list);
	} else {
		$out = "__success_{$opt['action']}_successful_on_{$opt['class']}_{$opt['name']}";
		json_print("success", $opt, $out);
	}
	log_log("web_command", "__success_{$opt['action']}");
	exit;


}

function json_print_result($opt, $result)
{
	if (isset($opt['output-type']) && $opt['output-type'] === 'json') {
		$out = array();
		$out['message'] = "success";
		$out['result'] = $result;
		$out['return'] = "success";
		$out = json_encode($out);
	} else {
		foreach($result as $k => $l) {
			$ret[] = "[$k]={$l}";
		}
		$out = implode("&", $ret);
	}
	while(@ob_end_clean());
	print($out);
}

function json_print($type, $opt, $message)
{
	if (isset($opt['output-type']) && $opt['output-type'] === 'json') {
		$out = array();
		$out['message'] = $message;
		$out['return'] = $type;
		$out = json_encode($out);
	} else {
		$out = $message;
	}
	while(@ob_end_clean());
	print($out);
}

function webc_print_and_exit($opt, $out)
{
	log_log("web_command", "__success_{$opt['action']}_successful_on_{$opt['class']}_{$opt['name']}\n");
	while(@ob_end_clean());
	print($out);
	print("\n");
}



function exists_in_db($server, $class, $nname)
{
	$db = new Sqlite($server, get_table_from_class($class));
	return $db->existInTable('nname', $nname);
}

function check_listpriv($parent, $class, $pvar, $v)
{
	foreach($v as $pk => $pv) {
		$pvar->$pk = $pv;
	}
	return;
}

function do_desc_delete_single($object)
{
	$object->delete();
}

function do_desc_delete($object, $cname, $ll)
{
	$object->delFromList($cname, $ll);
}

function isHardQuotaVariableInClass($class, $vname)
{
	$obj = new $class(null, null, 'tmpname');
	return $obj->isHardQuota($vname);
}

function check_priv($parent, $class, $pvar, $v)
{

	if (cse($class, "template")) {
		foreach($v as $pk => $pv) {
			$pvar->$pk = $pv;
		}
		return;
	}


	$parent = $parent->getClientParentO();


	foreach($v as $pk => $pv) {
		if (cse($pk, "_time")) {
			$pvar->$pk = $pv;
			continue;
		}
		if (!$parent->isQuotaVariable($pk) && !$parent->isDeadQuotaVariable($pk)) {
			continue;
		}
		if (cse($pk, "_flag")) {
			if ($parent->priv->isOn($pk)) {
				$pvar->$pk = $pv;
				continue;
			}
			if (isOn($pv)) {
				throw new lxException("Parent Doesnt Have Permission for $pk", "frm_{$class}_c_priv_s_$pk", null);
			}
			$pvar->$pk = $pv;
			continue;
		}
				
		if (cse($pk, "_num") || cse($pk, "_usage")) {
			$tmp = $pv;

			if ($tmp < 0) {
				throw new lxException('has_to_be_greater_than_zero', "priv_s_$pk");
			}
			if (is_unlimited($parent->priv->$pk)) {
				if (isHardQuotaVariableInClass($class, $pk)) {
					$parent->used->$pk -= $pvar->$pk;
					$parent->used->$pk += $pv;
					$parent->setUpdateSubaction();
				}
				$pvar->$pk = $pv;
				continue;
			}

			if (is_unlimited($pv)) {
				$desc = getNthToken(get_v_descr($parent, $pk), 2);
				if (!$desc) { $desc = $pk; }
				throw new lxException("quota_exceeded", "priv_s_$pk", $desc);
			}

			if (isHardQuotaVariableInClass($class, $pk)) {
				$parent->used->$pk -= $pvar->$pk;
			}
			dprintr($parent->used);
			if ($tmp > $parent->getEffectivePriv($pk, $class)) {
				dprint("After throw");
				$desc = getNthToken(get_v_descr($parent, $pk), 1);
				if (!$desc) { $desc = $pk; }
				throw new lxException("quota_exceeded", "priv_s_$pk", $desc);
			}
			dprint("No throw.. $tmp <br> ");

			if (isHardQuotaVariableInClass($class, $pk)) {
				$parent->used->$pk += $pv;
				$parent->setUpdateSubaction();
			}
			$pvar->$pk = $pv;
		}
	}

}

function is_license_quota_variable($v)
{
	if ($v === 'maindomain_num') {
		return true;
	}

	if ($v === 'client_num') {
		return true;
	}

	if ($v === 'vps_num') {
		return true;
	}
	return false;
}

function get_v_descr($stuff, $v = null)
{
	if (is_object($stuff)) {
		$class = lget_class($stuff);
	} else {
		$class = $stuff;
	}

	if ($v) {
		$desc = get_classvar_description($class,  $v);
	} else {
		$desc = get_classvar_description($class);
	}
	return $desc[2];
}


function createPrincipleObject()
{
	global $gbl, $sgbl, $login, $ghtml; 

	$object = $login;
	$parent = $login;
	$post = array();

	$navig = null;
	$navigmenu = null;
	$n = 0;
	// no cgi_o_o shows that the object being shown is the current object, so don't show anything, if the object is the current object.
	if ($ghtml->frm_o_o || ($ghtml->frm_action != 'show')) {
		if ($ghtml->frm_consumedlogin === 'true') {
			$navig[$n]['frm_consumedlogin'] = 'true';
		}
		$navig[$n]['frm_action'] = 'show';
		$navigmenu[$n] = array('show', $object);
		$n++;
	}
	if ($ghtml->frm_o_o) {
		$p = $ghtml->frm_o_o;
		foreach($p as $k => $v) {
			$__tparent = $object;
			if (isset($v['nname'])) {
				$object = $object->getFromList($v['class'], $v['nname']);
				$sing = false;
			} else {
				$object = $object->getObject($v['class']);
				$sing = true;
			}

			if (!$object) {
				break;
			}
			if (!$object->isLogin() && !is_object($object->getParentO())) {
				dprint("<br> <h1>... Parent got currupted for " . $object->getClass() . ":$object->nname with parent " . $__tparent->getClass() . ":$__tparent->nname $object->__parent_o <br> \n");
				dprintr($object->getParentO());
			}

			$desc = $ghtml->get_class_description($object->getClass());
			// Three conditions needed for listing. One, it should contain nname - this is the basic criteria. Second, the parent should contain the list as a child - this isn't as important actually.... (i removed the parent-containing-list-as child criteria, since it is absurdly wrong. The list is the child not of the _tparent, but a child of one of its child objects.) The third is that the child shouldn't be P object. P object is a virtual list object, where the whole list is virtually present, but you can't really list them, but can get any object as if the list was present.
			if (!$sing && isset($p[$k]['nname']) && !csa($desc[0], 'P')) {
				// Sort of a hack job... i am setting the self list parent and child here itself.
				$gbl->__self_list_parent = $__tparent;
				$gbl->__self_list_class = $object->getClass();
				if ($ghtml->frm_consumedlogin === 'true') {
					$navig[$n]['frm_consumedlogin'] = 'true';
				}
				$navig[$n]['frm_action'] = 'list';
				$navigmenu[$n] = array('list', null);
				for($i = 0; $i <= ($k - 1); $i++) {
					$navig[$n]['frm_o_o'][$i] = $p[$i];
				}
				$navig[$n]['frm_o_cname'] = $object->getClass();
				// Hack bloody hack.. This should be done the other way. getFiltervariable needs the navig to be set.
				$gbl->__navig = $navig;
				$gbl->__navigmenu = $navigmenu;
				$n++;
			}

			if (!$sing && ($object->createShowAlist($alist) || $object->createShowPropertyList($alist) || $object->createShowClist("") || $object->createShowSclist())) {
				// Skip the last one, but only if it is a 'show'. If 'show', the last object is the object that is being displayed, and shouldn't appear in history.
				if (($k === count($p) - 1) && ($ghtml->frm_action === 'show')) {
					break;
				}
				if ($ghtml->frm_consumedlogin === 'true') {
					$navig[$n]['frm_consumedlogin'] = 'true';
				}
				$navig[$n]['frm_action'] = 'show';
				$navigmenu[$n] = array('show', $object);
				for($i = 0; $i <= $k; $i++) {
					$navig[$n]['frm_o_o'][$i] = $p[$i];
				}
				$n++;
			}
		}
	}

	/*
	if ($ghtml->frm_o_cname) {
		$action = $ghtml->frm_action;
		$navig[$n]['frm_action'] = $action;
		if ($ghtml->frm_dttype) {
			$navig[$n]['frm_dttype'] = $ghtml->frm_dttype;
		}
		$navig[$n]['frm_o_cname'] = $ghtml->frm_o_cname;
		$navigmenu[$n] = array($action, $ghtml->frm_o_cname);
		if ($ghtml->frm_o_o) {
			foreach($p as $k => $v) {
				$navig[$n]['frm_o_o'][$k] = $p[$k];
			}
		}
	}
*/

	$gbl->__navigmenu = $navigmenu;
	$gbl->__navig = $navig;
	$gbl->__c_object = $object;
	$gbl->__histlist = $gbl->getSessionV("lx_history_var");

	//dprintr($ghtml->__http_vars['frm_hpfilter']);

}

function do_desc_update($object, $subaction, $param)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$class = lget_class($object);


	$parent = $object->getParentO();
	$qparent = $parent;


	$update_func = "update$subaction";


	do_actionlog($login, $object, "update", $subaction);




	
	//Calling the generic update first... If any class wide security checks to be made (as in the case of templates, u can do it there...
	$param = $object->update($subaction, $param);
	if (method_exists($object, $update_func)) {
		$param =  $object->$update_func($param);
	}


	//dprintr($param['__m_group']);

	if (!$param) {
		return false;
	}

	if (array_search_bool('--Select One--', $param)) {
		throw new lxException("Select One is not an acceptable Value", '');
	}


	$nparam[$class]['nname'] = $object->nname;
	// This code is very much suspect. Looks like I copied this from the addform and dumped it here. Should tkae a more detailed look in this. The issue is, the nnamevar is not needed, since this is inside a fully formed object, and nname need not be constructed. 
	foreach($param as $k => $v) {
		$object->resolve_class_heirarchy($class, $k, $dclass, $dk);

		$object->resolve_class_differences($class, $k, $ddclass, $ddk);
		if ($ddclass !== $class) {
			$nnamevar = get_real_class_variable($ddclass, "__rewrite_nname_const");
			if ($nnamevar) {
				$nnamelist = null;
				foreach($nnamevar as $n) {
					$nnamelist[] = $param[$n];
				}
				$nparam[$dclass]['nname'] = implode($sgbl->__var_nname_impstr, $nnamelist);
			}
		}
		$nparam[$dclass][$dk] = $v;
	}


	foreach($nparam as $k => $v) {
		if ($k === $class) {
			continue;
		}
		if ($k === 'priv') {
			$pvar = $object->priv;
			$oldpvar = clone $pvar; 
			check_priv($qparent, $class, $pvar, $v);
			$object->distributeChildQuota($oldpvar);
			continue;
		}

		if ($k === 'listpriv') {
			$pvar = $object->listpriv;
			check_listpriv($qparent, $class, $pvar, $v);
			continue;
		}
		// Checking for used too. Special case.... Copy the current used to __old_used. 
		// This is done so that the changes are trackable. For instance, in frontpage, 
		// you need to know if the previous state of frontpage. You cannot simply run 
		// the frontpage enabled command evertime any change is made. 
		// It should be run only if the previous state was disabled and the current state is enabled. 
		// Or vice versa. This has to be done distributechildquota too, 
		// where the 'used' is forcibly turned off to synchronize with the priv variable.
		if ($k === 'used') {
			$object->__old_used = clone $object->used;
		}
		if (cse($k, "_b") || $k === 'used') {
			$bvar = $object->$k;
			$bvar->modify($v);
			continue;
		}
	}

	$object->modify($nparam[$class], $subaction);

	$object->postUpdate();

	if (cse($object->get__table(), "_a")) {
		$object->getParentO()->setUpdateSubaction('update_a_child');
	}

	

	return $param;
}


function do_desc_add($object, $class, $param)
{
	global $gbl, $sgbl, $login, $ghtml; 


	

	$quotaclass = exec_class_method($class, "getquotaclass", $class);
	$numvar = "{$quotaclass}_num";

	$qobject = $object->getClientParentO();

	dprint($qobject->getClname());

	if ($qobject->isQuotaVariable($numvar)) {
		if (isQuotaGreaterThanOrEq($qobject->used->$numvar, $qobject->priv->$numvar)) {
			throw new lxException("Quota Exceeded for $class", 'nname', $numvar);
		}
	}


	/*
	$list = $qobject->getQuotaVariableList();
	foreach((array) $list as $l => $v) {
		if (csb($l, "{$class}_m_")) {
			$license = strtil(strfrom($l, "_n_"), "_num");
			$licvar = strtil(strfrom($l, "_m_"), "_n_");
			if (isset($param[$licvar]) && $param[$licvar] === $license) {
				if (isQuotaGreaterThanOrEq($qobject->used->$l, $qobject->priv->$l)) {
					throw new lxException("Quota Exceeded for $class $licvar.$license", 'nname', $numvar);
				}
			}
		}
	}
*/




	
	// Setting it here itself so that the add can override if necessary. This is done in tickets, where the parent is always the admin.
	$param['parent_clname'] = $object->getClName();

	// In the case of mailaccount, the real parent is mmail, while the object is added to client.
	if (isset($param['real_clparent_f'])) {
		$parent_class = exec_class_method($class, 'defaultParentClass', $object);
		$param['parent_clname'] = createParentName($parent_class, $param['real_clparent_f']);
	}

	$param = exec_class_method($class, 'Add', $object, $class, $param);

	// First loop to create a unique nname if applicable.... FOr the 'unique-nname-creation' to work in the second loop, the variables must be resolved before that... So this extra looping...

	foreach($param as $k => $v) {
		if (csb($k, "__v_") || csb($k, "__m_")) {
			continue;
		}
		$object->resolve_class_differences($class, $k, $dclass, $dk);
	}

	foreach($param as $k => $v) {
		if (csb($k, "__v_") || csb($k, "__m_")) {
			continue;
		}
		$object->resolve_class_heirarchy($class, $k, $dclass, $dk);

		$object->resolve_class_differences($class, $k, $ddclass, $ddk);
		$nnamevar = get_real_class_variable($ddclass, "__rewrite_nname_const");
		if ($nnamevar) {
			$nnamelist = null;
			foreach($nnamevar as $n) {
				$nnamelist[] = $param[$n];
			}
			$nparam[$dclass]['nname'] =implode($sgbl->__var_nname_impstr, $nnamelist);
		}
		$nparam[$dclass][$dk] = $v;
	}


	// First Pass
	foreach($nparam as $k => $v) {
		if (csa($k, "_s_")) {
			continue;
		}
		if ($k === 'priv') {
			$olist[$k] = new priv(null, null, $nparam[$class]['nname']);
			check_priv($object, $class, $olist[$k], $v);
			continue;
		}
		if ($k === 'used') {
			$olist[$k] = new Used(null, null, $nparam[$class]['nname']);
			$olist[$k]->create($v);
			continue;
		}
		if ($k === 'listpriv') {
			//$olist[$k] = new listpriv($object->__masterserver, null, $class . "_s_vv_p_" . $nparam[$class]['nname']);
			$olist[$k] = new listpriv($object->__masterserver, null, $class . "-" . $nparam[$class]['nname']);
			check_listpriv($object, $class, $olist[$k], $v);
			continue;
		}
		
		if (csa($k, "_b")) {
			$olist[$k] = new $k($object->__masterserver, null, $nparam[$class]['nname']);
		} else {
			$olist[$k] = new $k($object->__masterserver, null, $v['nname']);
		}
		$olist[$k]->inheritSyncServer($object);
		$olist[$k]->initThisDef();
		$olist[$k]->create($v);
		// The createsyncclass needs the syncserver variable to be set. Which may not be available. So we have to run this again.


		if ($olist[$k]->hasDriverClass()) {
			$olist[$k]->createSyncClass();
		}
	}

	// The main object has to inherit the masterserver here itself, so that its children will inherit it later when they are added through addobject.


	if (!cse($class, "_a") && exec_class_method($class, "isDatabase") && exists_in_db($object->__masterserver, $class, $olist[$class]->nname)) {
		// If the parent is getting added too, then that means we are in the client add page, and thus the variable is vps_name, domain_name rather than nname.
		if ($object->dbaction === 'add') {
			$vname = "{$class}_name";
		} else {
			$vname = "nname";
		}
		throw new lxException("{$olist[$class]->nname}+already+exists+in+$class.", $vname, $class);
	}

	//Second Pass...
	foreach($nparam as $k => $v) {
		if (!csa($k, "_s_") && !csa($k, "-")) {
			continue;
		}

		$clist = explode("_s_", $k);
		$k = $clist[1];
		$cl = $clist[0];
		$nolist[$k] = new $k($object->__masterserver, null, $v['nname']);
		$nolist[$k]->inheritSyncServer($olist[$cl]);
		$nolist[$k]->initThisDef();
		$nolist[$k]->create($v);
		// The createsyncclass needs the syncserver variable to be set. Which may not be available. So we have to run this again.
		if ($nolist[$k]->hasDriverClass()) {
			$nolist[$k]->createSyncClass();
		}

		$olist[$cl]->addObject($k, $nolist[$k]);
	}



	foreach($olist as $k => $v) {
		if (cse($k, "_b") || $k === 'used' || $k === 'priv' || $k === 'listpriv') {
			$olist[$class]->$k = $v;
			continue;
		}
		if ($k != $class) {
			$olist[$class]->addObject($k, $v);
			continue;
		}
	}

	if (isset($param['__v_priv'])) {
		$olist[$class]->priv = $param['__v_priv'];
	}

	if (isset($param['__v_listpriv'])) {
		$olist[$class]->listpriv = $param['__v_listpriv'];
	}


	//$olist[$class]->parent_clname = $object->getClName();
	$rparent = $object;

	$olist[$class]->__parent_o = $rparent;

	

	$olist[$class]->postAdd();


	$rparent->addToList($class, $olist[$class]);
	$olist[$class]->superPostAdd();
	//dprintr($object);

	notify_admin("add", $object, $olist[$class]);

	do_actionlog($login, $olist[$class], "add", "");


	//This shouldn't happen here. This should be done only after the synctosystem since, the sync can fail and the write may not happen at all.

	//$olist[$class]->changeUsedFromParentAll();

	dprint($olist[$class]->getParentO());
}

