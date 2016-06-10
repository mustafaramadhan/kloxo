<?php 

function __cmd_desc_add($p, $parent = null)
{
	global $gbl, $sgbl, $login, $ghtml; 

	// MR -- make return
	return;
	
	if (!$parent) {
		if (isset($p['parent-class']) && isset($p['parent-name'])) {
			$parent = new $p['parent-class'](null, 'localhost', $p['parent-name']);
			dprint("$parent->nname\n");
			$parent->get();
			if ($parent->dbaction === 'add') {
				throw new lxException("parent_doesnt_exist", "nname", $p['parent-name']);
			}

			if (!$parent->checkIfSomeParent($login->getClName())) {
				throw new lxException("you_are_not_the_owner_of_parent", "", $p['parent-name']);
			}

		} else {
			$parent = $login;
		}
	}

	copy_nname_to_name($p);
	$class = $p['class'];

	$var = get_variable($p);

	if (isset($p['count'])) {
		$oldname = $p['name'];
		for($i = 0; $i < $p['count']; $i++) {
			if ($class === 'domain') {
				$p['name'] = "$oldname$i.com";
			} else {
				$p['name'] = "$oldname$i";
			}
			$param = exec_class_method($class, "addCommand", $parent, $class, $p);
			unset($var['template-name']);
			$param = lx_array_merge(array($param, $var));
			do_desc_add($parent, $class, $param);
		}
		$parent->was();
		exit;
	}

	$param = exec_class_method($class, "addCommand", $parent, $class, $p);

	unset($var['template-name']);
	$param = lx_array_merge(array($param, $var));
	do_desc_add($parent, $class, $param);
	$parent->was();
}

function __cmd_desc_delete($p)
{
	global $gbl, $sgbl, $login, $ghtml; 

	// MR -- make return
	return;
/*
	if (isset($p['parent-class']) && isset($p['parent-name'])) {
		$parent = new $p['parent-class'](null, 'localhost', $p['parent-name']);
		$parent->get();
		if ($parent->dbaction === 'add') {
			throw new lxException("parent_doesnt_exist", "nname", $class);
		}
	} else {
		$parent = $login;
	}
*/
	$class = $p['class'];
	$name = $p['name'];

	$object = new $class(null, 'localhost', $name);
	$object->get();

	if ($object->dbaction === 'add') {
		throw new lxException('object_doesnt_exist', '', $name);
	}

	if (!$object->checkIfSomeParent($login->getClName())) {
		throw new lxException("the_object_doesnt_exist_under_you", "", $object->nname);
	}

	do_desc_delete_single($object);
	$object->was();
}

function __cmd_desc_simplelist($p)
{
	global $gbl, $sgbl, $login, $ghtml; 

	// MR -- make return
	return;

	ob_start();
	$resource = $p['resource'];

	$parent = null;
	if (!$parent) {
		if (isset($p['parent-class']) && isset($p['parent-name'])) {
			$parent = new $p['parent-class'](null, 'localhost', $p['parent-name']);
			dprint($parent->nname);
			$parent->get();
			if ($parent->dbaction === 'add') {
				throw new lxException("parent_doesnt_exist", "nname", $p['parent-name']);
			}

			if (!$parent->checkIfSomeParent($login->getClName())) {
				throw new lxException("you_are_not_the_owner_of_parent", "", $p['parent-name']);
			}

		} else {
			$parent = $login;
		}
	}

	$list = $parent->getCommandResource($resource);

	if (!$list) {

		// Fix for WHMCS needing pserver in client.

		if (!$parent->isAdmin() && $sgbl->isKloxo() && $resource === 'pserver') {
			$list['localhost'] = 'localhost';
			return $list;
		}

		$list = $parent->getList($resource);
		if (isset($p['v-filter'])) {
			list($var, $val) = explode(":", $p['v-filter']);
			foreach($list as $k => $l) {
				if ($l->$var !== $val) {
					unset($list[$k]);
				}
			}
		}
		if (!$list) {
			json_print("error", $p, "__error_no_resource_for_$resource");
			exit;
		}
		$list = get_namelist_from_objectlist($list, "nname", "nname");
	}
	ob_end_clean();

	return $list;
}

function copy_nname_to_name(&$p)
{
	// MR -- make return
	return;

	if (isset($p['nname']) && !isset($p['name'])) {
		$p['name'] = $p['nname'];
	}
}


function __cmd_desc_update($p)
{
	global $gbl, $sgbl, $login, $ghtml; 

	// MR -- make return
	return;

	copy_nname_to_name($p);
	$object = new $p['class'](null, 'localhost', $p['name']);
	$object->get();

	if ($object->dbaction === 'add') {
		throw new lxException("object_doesnt_exist", "name", $p['name']);
	}

	if (!$object->checkIfSomeParent($login->getClName())) {
		throw new lxException("the_object_doesnt_exist_under_you", "", $object->nname);
	}

	$tparam = get_variable($p);
	$subaction = $p['subaction'];

	$tparam = $object->commandUpdate($subaction, $tparam);

	$param = array();
	foreach($tparam as $k => $v) {
		$k = str_replace("-", "_s_", $k);
		$param[$k] = $v;
	}
	dprintr($param);
	do_desc_update($object, $subaction, $param);
	$object->was();
}

function __cmd_desc_getproperty($param)
{
	global $gbl, $sgbl, $login, $ghtml; 

	// MR -- make return
	return;

	if (isset($param['name']) && isset($param['class'])) {
		$name = $param['name'];
		$class = $param['class'];
		$object = new $class(null, 'localhost', $name);
		$object->get();
		if ($object->dbaction === 'add') {
			throw new lxException('object_doesnt_exist', 'name', $name);
		}
	} else {
		$object = $login;
	}


	$object->getHardProperty();

	$vlist = get_variable($param);

	foreach($vlist as $k => $v) {
		$nv = $k;
		if (csa($nv, "-")) {
			$cc = explode("-", $nv);
			$result["v-$k"] = $object->{$cc[0]}->$cc[1];
			continue;
		}

		if ($nv === 'priv' || $nv === 'used') {
			foreach($object->$nv as $kk => $nnv) {
				if ($object->isQuotaVariable($kk)) {
					$result["v-$nv-$kk"] =  $nnv;
				}
			}
			continue;
		}
		$result["v-$nv"] =  $object->$nv;
	}
	return $result;
}

