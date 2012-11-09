<?php 

class lxbackupmisc_b extends lxaclass {
}


class vpsipaddress extends lxclass {

function get() {}
function write() {}

}

abstract class Lxclass {


public $nname;

public $__list_list;
public $__virtual_list;
public $__object_list;
public $subaction;

public $dbaction = "clean";
public $metadbaction = "all";


static $__desc_parent_name_change = array("", "",  "owner");
static $__acdesc_update_commandcenter = array("", "",  "command_center");
static $__desc_ccenter_command = array("", "",  "Command");
static $__desc_confirm_f = array("", "",  "Confirm");
static $__desc_ccenter_output = array("T", "",  "Output");
static $__desc_ccenter_error = array("", "",  "Error");
static $__acdesc_update_toggle_status = array("", "",  "status");
static $__acdesc_update_disable = array("", "",  "disable");
static $__acdesc_update_enable = array("", "",  "enable");
static $__acdesc_update_switchserver = array("", "",  "switch_server");
static $__acdesc_update_changeowner = array("", "",  "change_owner");
static $__acdesc_update_backup = array("", "",  "get_backup");
static $__acdesc_update_restore = array("", "",  "restore");
static $__acdesc_update_logout = array("", "",  "logout");
static $__acdesc_update_changenname =  array("","",  "Change Name"); 
static $__desc_switch_happening_f = array("", "",  "switch_status");
static $__desc_server_detail_f = array("", "",  "server_details");
static $__desc_parent_clname	 = array("", "",  "owner");
static $__desc_license_o	 = array("", "",  "owner");
static $__desc_ddate	 = array("", "",  "date");
static $__desc_filter_view_quota = array("", "",  "quota_view"); 
static $__desc_filter_view_normal = array("", "",  "normal_view"); 

/** 
* @return void 
* @param 
* @param 
* @desc  The base constructor. Also initializes the driverapp object to the right one, using the values from the gbl.
*/ 

function __construct($masterserver, $readserver, $key)
{
	$key = trim($key);
	// FIXME
	$this->__virtual_list = array();

	if ($key === "") {
		$key = "______________";
	}

	$this->__class = strtolower(get_class($this));



	// Remove the ',' character, since it is used to separate teh delete list in forms. This is a hack, but there is no other way.. If ',' is left as it is, it becomes impossible to delete this element..... (later) throw an exception. If a variable is changed silently like this, it can cause unexpected sideffects.

	if (!csb($this->__class, "ffile") && !csb($this->__class, 'mailcontent')) {
		if (char_search_a($key, ",")) {
			throw new lxexception('name_cannot_contain_comma', 'nname');
		}
		if (char_search_a($key, "'")) {
			throw new lxexception('name_cannot_contain_single_quote', 'nname');
		}
		if (char_search_a($key, ")")) {
			throw new lxexception('name_cannot_contain_bracket', 'nname');
		}

		if (char_search_a($key, "(")) {
			throw new lxexception('name_cannot_contain_bracket', 'nname');
		}
		if (char_search_a($key, "+")) {
			throw new lxexception('name_cannot_contain_plus', 'nname');
		}
		if (char_search_a($key, "&")) {
			throw new lxexception('name_cannot_contain_lessthan_greaterthan_or_and', 'nname');
		}
		if (char_search_a($key, "<")) {
			throw new lxexception('name_cannot_contain_lessthan_greaterthan_or_and', 'nname');
		}
		if (char_search_a($key, ">")) {
			throw new lxexception('name_cannot_contain_lessthan_greaterthan_or_and', 'nname');
		}
	}





	if (!$readserver) {
		$readserver = 'localhost';
	}
	$this->cttype = strtolower(get_class($this));
	$this->nname = $key;
	$this->__masterserver = $masterserver;
	$this->__readserver = $readserver;
	$this->syncserver = $readserver;

}

function createDriverAppSpecific()
{
	return false;
}


function getSyncServerForChild($class)
{
	return $this->syncserver;
}

function inheritSyncServer($parent)
{
	if ($this->inheritSynserverFromParent() && $parent) {
		if (!$this->isClass('ssession')) { 
			$this->syncserver = $parent->getSyncServerForChild($this->getClass());
			log_log("syncserveriherit", "Adding syncserver $this->syncserver to $this->nname {$this->getclass()} from {$parent->getClName()}");
		}
	}
}


static function isDatabase() { return false; }
function createSyncClass()
{
	global $gbl, $sgbl, $login, $ghtml; 

	if (!$login) {
		return;
	}


	if (csa($this->syncserver, ",")) {
		return;
	}

	if ($sgbl->__var_no_sync) {
		return;
	}

	if ($this->get__table() === 'pserver') {
		//debugBacktrace();
	}

	if (isLocalhost($this->__masterserver) && $login->isSuperClient()) {
		return;
	}

	if ($this->createDriverAppSpecific()) {
		return;
	}

	$class = $this->get__table();


	$syncclass = $gbl->getSyncClass($this->__masterserver, $this->syncserver, $class);



	if ($syncclass) {
		$this->__driverappclass = $syncclass;
		$syncclass = $class . "__" . $syncclass;
		if (class_exists($syncclass)) {
			$this->driverApp = new $syncclass(null, null, $this->nname);
			$this->driverApp->main = $this;
		} else {
			debugBacktrace();
			dprint("No driverApp class for {$class} {$syncclass} <br> ");
		}
	} else {
		$this->driverApp = new LxaClass(null, null, $this->nname);
		$this->driverApp->main = $this;
	}
	
}


abstract protected function get();

abstract protected function write();

function dosyncToSystem()
{
	if (!isset($this->driverApp) || !is_object($this->driverApp)) {
		return;
	}
	return $this->driverApp->doSyncToSystem();
}


function setUpdateSubaction($val = null) 
{
	if ($this->dbaction === 'clean') {
		$this->dbaction  = 'update';
		$this->subaction = $val;
	}

	if (!$val) {
		return;
	}

	if ($this->dbaction === 'update') {
		if (!$this->subaction) {
			//dprint("Overwriting Old NULL subaction <br> \n");
			$this->subaction = $val;
		} else  {
			//dprint("Old subaction {$this->subaction}.. Turning into array<br> \n");
			if (!is_array($this->subaction)) {
				if ($this->subaction != $val) {
					$oldval = $this->subaction;
					$newar[] = $oldval;
					$newar[] = $val;
					$this->subaction = $newar;
				}
			} else {
				$this->subaction = array_push_unique($this->subaction, $val); 
			}
		}
	}
}


function getPathFromName($var = 'nname') 
{
	return str_replace(" ", "", $this->$var);
}

function getQuotaAddList($k) { return null; }

function getTitleWithSync($class = null)
{
	global $gbl, $sgbl, $login, $ghtml; 
	if ($class) {
		$obj = $this->getObject($class);
	} else {
		$obj = $this;
		$class = $this->get__table();
	}

	$switch = null;
	if (isset($obj->olddeleteflag) && $obj->olddeleteflag === 'on') {
		$switch = "(Switching)";
	}

	$desc = get_description($class);
	$path = get_image_path();
	$img = $ghtml->get_image($path, null, $obj->__driverappclass, '.gif');
	$descr = null;
	$str = null;
	if (check_if_many_server()) {
		$descr = "on {$obj->syncserver}";
		//$str = ":{$obj->syncserver}";
	} 

	//<img src={$img} width=14 height=14> 
	// Don't need this. Ruins the appearance <b> [</b>{$obj->getShowInfo()}<b>] </b>
	return "{$desc}  <span title=\"{$desc} is Configured {$descr} on {$obj->__driverappclass}\">  {$str} {$switch}: {$obj->__driverappclass}  </span>";

}

function substr($var, $a, $b) { return substr($this->$var, $a, $b); }

function getShowInfo() { return null;}

function eeval($rule) 
{
	global $gbl, $sgbl, $login, $ghtml; 
	return eval("return {$rule};");
}

function syncToSystemCommon()
{
	global $gbl, $sgbl, $login, $ghtml; 

	if ($login->isDemo()) {
		if ($this->get__table() !== 'ssession') {
			throw new lxexception('login_is_demo', '');
		}
	}

	// Don't sync if there is no subactiion for update..
	if ($this->dbaction === 'update' && !$this->subaction) {
		dprint("No subaction for update not syncing anymore {$this->get__table()}\n <br> ");
		return false;
	}

	if (lfile_exists("__path_program_etc/.writeonly")) {
		dprintr("Global Writonly Mode... Not syncing... <br> \n");
		return  false;
	}


	if ($this->isDisabled('syncserver')) {
		dprint("syncserver disabled syncing anymore\n");
		return false;
	}

	return $this->isSync();
}


// This should work for all normal purposes., 
// If there is some multi server syncing for a single object like that what happens in dns and domainbackup, 
// you can redefine sysnctosyste. Just call the common function at the beginning.
function syncToSystem() 
{

	global $gbl, $sgbl, $login, $ghtml; 


	if ($this->syncToSystemCommon()) {
		$synclist = explode(",", $this->syncserver);
		foreach($synclist as $s) {
			$s = trim($s);
			if ($s) {
				dprint("doing the real sync this {$this->nname} on server {$s}  <br> \n");
				$this->__var_syncserver = $s;
				$res = rl_exec_set(null, $s,  $this);
			} else {
				$res = rl_exec_set(null, 'localhost',  $this);
			}
		}


		return $res;
	}

}

function getCommandResource($resource)
{
	return null;

	$list = $this->getList($resource);

	if (!$list) {
		throw new lxexception('resource_doesnt_exist', '', $resource);
	}

	$array = get_namelist_from_objectlist($list);

	return $array;
}

static function switchDriver($class, $old, $new)
{
//	exec_class_method("{$class}__$new", "installMe");
//	exec_class_method("{$class}__$old", "uninstallMe");

	//MR -- must be change because problem with template-based web config
	exec_class_method("{$class}__$old", "uninstallMe");
	exec_class_method("{$class}__$new", "installMe");
}


function doCustomAction()
{
	global $gbl, $sgbl, $login, $ghtml; 

	if ($this->isClass('ssession')) { return; }
	if ($this->isClass('general')) { return; }
	if ($this->isClass('sp_specialplay')) { return; }
	if ($this->isClass('sp_childspecialplay')) { return; }
	if ($this->isClass('notification')) { return; }
	if ($this->isClass('resourceplan')) { return; }

	$acto = $login->getObject('general')->customaction_b;

	$var = "{$this->get__table()}__{$this->dbaction}__{$this->subaction}";
	if (isset($acto->$var) && $acto->$var) {
		$action = $acto->$var;
		$action = str_replace('%contactemail%', $this->contactemail, $action);
		$action = str_replace('%nname%', $this->nname, $action);
		lxshell_direct($action);
	}

	if (!$this->isClass('vps')) {
		return;
	}

	$sq = new Sqlite(null, 'customaction');

	if ($this->dbaction === 'add') {
		$query = "action = '$this->dbaction' AND class = '{$this->getClass()}'";
	} else {
		$query = "action = '$this->dbaction' AND subaction = '$this->subaction' AND class = '{$this->getClass()}'";
	}

	$this->__var_custom_exec = null;

	dprint($query);
	$list = $sq->getRowsWhere($query);
	if (!$list) { return; }
	dprintr($list);
	foreach($list as $k => $l) {
		$ex = $l['exec'];
		$ex = str_replace('%contactemail%', $this->contactemail, $ex);
		$ex = str_replace('%nname%', $this->nname, $ex);
		$ex = str_replace('%hostname%', $this->hostname, $ex);
		$ex = str_replace('%vpsid%', $this->vpsid, $ex);
		if ($l['where_to_exec'] === 'master') {
			lxshell_direct($ex);
		} else {
			$this->__var_custom_exec = $ex;
		}
	}
	
}

function isDisabled($var)
{
	return (!$this->$var || $this->$var === '--Disabled--');
}

function displaySet($var, $val)
{

	$this->$var = $val;
}
function getUSlashP($v) { return "{$this->used->$v}/{$this->priv->$v}"; }

function moreNotification()
{
	return false;
}

function checkButton($var)
{
	if (!isset($this->priv)) {
		return true;
	}
	return $this->priv->isOn($var);
}

function isSync() 
{ 
	if ($this->dbaction === 'update' && $this->subaction === 'collectquotaupdate') {
		return false;
	}
	return true;

}
function isParentList() { return false; }

static function isHardRefresh() { return false; }
static function isdefaultHardRefresh() { return false; }

static function getDefaultValue($var) 
{
	if ($var === 'phpfcgi_flag') {
		return "Off";
	} 

	return "On";
}

function checkIfSomeParent($clname)
{
	if ($clname === 'client-admin') {
		return true;
	}
	$p = $this->getParentO();
	$pp = $p;
	while($pp) {
		if ($pp->getClName() === $clname) {
			return true;
		}
		$pp = $pp->getParentO();
	}

	return false;
}


function isLogin()
{
	global $gbl, $sgbl, $login, $ghtml; 
	if ($this === $login) {
		return true;
	}
	return false;
}


final static function calldriverappFunc($class, $func)
{
	global $gbl, $sgbl;

	print("I shouldn't get called\n");
	debugBacktrace();
	exit;

	$driverapp = $gbl->getSyncClass(null, null, $class);

	if (!$driverapp) {
		dprint(" NO driverapp class for {$class}\n <br> ");
		return;
	}
	$class = $class . "_" . $driverapp;

	$start = 2;

	eval($sgbl->arg_getting_string);


	return call_user_func_array(array($class, $func), $arglist);

}


function createShowTypeList()
{
	$list = null;
	if (isset($this->cttype)) {
		$list['cttype'] = null;
	}
	return $list;
}

function getVarDescrList($i = null)
{
	$class = lget_class($this);
	$r = new ReflectionClass($class);
	// First pass to isolate teh _v_ variable
	$ret = null;
	foreach($r->getProperties() as $s) {
		if (!csb($s->name, "__desc_"))
			continue;
		$descr = get_classvar_description($class, $s->name);
		$name = substr($s->name, 7);
		if ($i === null) {
			$ret[$name]  = $descr;
		} else {
			$ret[$name] = $descr[$i];
		}

	}

	return $ret;

}


final function initThisDef()
{

	$class = lget_class($this);
	// First pass to isolate teh _v_ variable
	$list = $this->getVarDescrList();
	$this->dbaction = 'add';
	foreach((array) $list as $nname => $desc) {
		if (!csa($nname, "_v_")) {
			continue;
		}
		$name = substr($nname, 0, strpos($nname, "_v_"));
		if (isset($value[$name]))
			continue;

		$value[$name] = substr($nname, strpos($nname, "_v_") + 3);
	}
		



	foreach((array) $list as $name => $descr) {
		if (cse($name, "_o"))
			continue;
		if (cse($name, "_l"))
			continue;
		if (cse($name, "_f"))
			continue;
		if (csa($name, "_v_")) {
			continue;
		}

		if (isset($this->$name)) {
			continue;
		}

		if (cse($name, "_b")) {
			$this->$name = new $name(null, null, $this->nname);
			$this->{$name}->initThisdef();
			continue;
		}

		if (cse($name, "_a")) {
			$this->$name = array();
			continue;
		}

		if (isset($value[$name])) {
			$this->$name = $value[$name];
		} else {
			if ($name === 'ddate') {
				$this->$name = time();
			} else {
				$des = $name;
				$desc = get_classvar_description($class, $des);


				if (csa($desc[0], 'q', 0)) {
					if (!isset($this->priv)) {
						$this->priv = new priv(null, null, $this->nname);
						$this->priv->__parent_o = $this;
					}

					if (!isset($this->used)) {
						$this->used = new Used(null, null, $this->nname);
						$this->used->__parent_o = $this;
					}
					if (cse($name, "_flag")) {
						$this->used->$name = '-';
						$this->priv->$name = 'on';
					} else {
						$this->priv->$name = 'Unlimited';
						$this->used->$name = 0;
					}
				}  else if (csa($desc[0], 'Q')) {
					if (isset($this->listpriv)) {
						$this->listpriv = new ListPriv(null, null, $this->nname);
					}
				} else {
					$this->$name = $this->defaultValue($name);
				}
			}
		}
	}

	// If it is pserver, then when it is initialized don't create the driver, since the driver system is not in place at all.
	if ($this->get__table() !== 'pserver') {
		if ($this->hasDriverClass()) {
			$this->createSyncClass();
		}
	}
	//print_time($this->get__table(), $this->get__table() . "jdflk");

}

final function isLocalhost($var = "syncserver")
{

	global $gbl, $sgbl, $login, $ghtml; 
	$cl = $this->get__table();
	if (isset($this->$var) && $this->$var && $this->$var !== "localhost") {
		return false;
	}
	return true;
}


function fixSyncServer()
{
	if (!$this->syncserver) {
		$this->syncserver = 'localhost';
	}
}

function getRealPserverList($role = null)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$list = $this->getVirtualList('pserver', $count);

	foreach($list as $k => $l) {
		$pslist = get_namelist_from_objectlist($l->psrole_a);
		if ($role === 'dns') {
			if ($role && !array_search_bool('reversedns', $pslist) && !array_search_bool('dns', $pslist)) {
				unset($list[$k]);
			}
		} else {
			if ($role && !array_search_bool($role, $pslist)) {
				unset($list[$k]);
			}
		}
	}
	return $list;
}

function syncEntireObject()
{
	global $gbl, $sgbl, $login, $ghtml; 


	$this->preSync();
	$this->doCustomAction();


	if ($this->isUnclean()) {

		$this->createExtraVariables();

		if ($this->metadbaction !== "writeonly") {
			try {
				$res = $this->syncToSystem();


			} catch (Exception $e) {
				throw $e;
			}
			// This is used in web to get the iis is which is generated only when you create the domain.
			$this->AddSyncReturn($res);
		}
	}


	// the children should only be synced AFTER the parent. 
	// This is actually self-evident now, especially since when we backup, a domain would be fully formed,
	// and thus adding spam BEFORE the mail is added is absurd.... 
	// But when deleting, it is the other way round. First the children should be deleted, 
	// and deleting the parent before the children can lead to some issues.

	foreach((array) $this->__object_list as $variable) {
		$objname = "{$variable}_o";
		$obj = $this->$objname;
		//dprint("my parent: {$this->__parent_o->nname}\n");
		// Big hack. When restoring parent_o is getting lost...
		if (!isset($obj->__parent_o) || !$obj->__parent_o) {
			$obj->__parent_o = $this;
		}
		if (is_object($obj) && $obj->metadbaction !== 'writeonly') {
			$obj->syncEntireObject();
		}
	}

	$this->postSync();


}

function postSync() {}
function preSync() {}

function getAllDrivers()
{

	$driverapp = $gbl->getSyncClass(null, $this->syncserver, $this->get__table());

	$driver = $gbl->driver['localhost'][$this->syncserver];

	$this->__var_driver_stuff = $driver;

}

// Adds the return of the synctosystem to $this...
function AddSyncReturn($res)
{
	if (!is_array($res)) {
		return;
	}

	foreach($res as $k => $v) {
		if (csb($k, "__syncv_")) {
			$rk = strfrom($k, "__syncv_");
			$this->$rk = $v;
		}
	}
}


// The "if (!$var)" was removed because it was evaled as true when $var was 0.
function setDefaultValue($var, $val)
{
	if ($this->$var === "" || $this->$var === NULL) { $this->$var = $val; }
}


function writeEntireObject()
{


	foreach((array) $this->__object_list as $variable) {
		$objname = $variable . "_o";
		$obj = $this->$objname;
		if ($obj) {
			$obj->writeEntireObject();
		}
	}

	if ($this->get__table() === 'driver') {
		dprint("<b> Driver  {$this->dbaction} <br> <br> </b>");
	}
	if ($this->isUnclean()) {
		dprint("Really Writing  {$this->get__table()} {$this->nname}...dbaction... {$this->dbaction} <br> \n");
		$this->write();
	}


	$this->writeAndSyncChildren();

	if ($this->dbaction === 'add') {
		$this->changeUsedFromParentAll();
	}

	if ($this->dbaction === 'delete' && !$this->isClass('ssession')) {
		$this->changeUsedFromParentAll(-1);
	}

	if ($this->dbaction !== "delete" && $this->dbaction !== "delete_done") {
		$this->dbaction = "clean";
	} else {
		$this->dbaction = "delete_done";
	}

}

/** 
* @return void 
* @param 
* @param 
* @desc syuncs a class and if successful writes it to the db.
*/ 
 
function doWas()
{
	$this->syncEntireObject();
	$this->writeEntireObject();
}


function isUnclean()
{
	return !($this->dbaction === "clean" || $this->dbaction === "delete_done");
}

function checkNotSame($var, $list)
{
	foreach($list as $l) {
		if ($this->$l === $var[$l]) {
			throw new lxexception('no_change', '');
		}
	}
}
final function was()
{

	global $gbl, $sgbl, $login, $ghtml; 

	if ($this->dbaction === "screwed") 
		return;


	//dprint("Master: {$this->dbaction}: {$this->nname} {$this->getParentO()->nname} {$this->get__table()} <br> ");

	try {
		$this->doWas();
	} catch (Exception $e) {
		$this->dbaction = "screwed";
		throw $e;
	}
	$this->metadbaction = 'all';




}

/** 
* @return void 
* @param 
* @param 
* @desc  stuf fucntion of html parameter verification.
*/ 


static function verify($var, $val)
{
	dprint("{$var} {$val}");
	return  $val;

}

static function defaultSortDir() { return "asc"; }

static function defaultSort() { return "nname"; }

function createExtraVariables() { }
function getSortTop($direction) { return NULL; }

static function perPage()
{
	global $gbl, $sgbl, $login;
	return "20";
}



function isClient()
{
	return (lget_class($this) === 'client');
}


function isGte($type)
{
	global $gbl, $sgbl, $login, $ghtml; 
	//dprint($this->cttype.  ' <br> ' . $type);
	if (!isset($sgbl->__var_cttype[$this->cttype]))  return true; 
	return ($sgbl->__var_cttype[$this->cttype] >= $sgbl->__var_cttype[$type]);

}

function isGt($type)
{
	global $gbl, $sgbl, $login, $ghtml; 
	if (!isset($sgbl->__var_cttype[$this->cttype]))  return true; 
	return ($sgbl->__var_cttype[$this->cttype] > $sgbl->__var_cttype[$type]);

}

// Less than or equal to Admin...

function isLteAdmin()
{
	global $gbl, $sgbl, $login, $ghtml; 
	if (!isset($sgbl->__var_cttype[$this->cttype]))  return false; 
	return ($sgbl->__var_cttype[$this->cttype] <= $sgbl->__var_cttype['admin']);
}

function isLte($type)
{
	global $gbl, $sgbl, $login, $ghtml; 
	if (!isset($sgbl->__var_cttype[$this->cttype]))  return false; 
	return ($sgbl->__var_cttype[$this->cttype] <= $sgbl->__var_cttype[$type]);

}


function isEq($type)
{
	global $gbl, $sgbl, $login, $ghtml; 
	if (!isset($sgbl->__var_cttype[$this->cttype]))  return false; 
	return ($sgbl->__var_cttype[$this->cttype] === $sgbl->__var_cttype[$type]);

}


function isWholeSale()
{
	return $this->isLte('wholesale');

}

function isSuperClient()
{
	return ($this->cttype === 'superclient');
}

function isSuperAdmin()
{
	return ($this->cttype === 'superadmin');
}

function isAdmin()
{
	return ($this->cttype === 'admin');
}

function isAdminReseller()
{
	return ($this->cttype === "wholereseller" || $this->cttype === 'admin');
}


function isNotCustomer()
{
	return $this->isLte('reseller');
}

function isCustomer()
{
	return ($this->cttype === 'customer');
}


static function searchVar()
{
	return "nname";
}

function isButton($name)
{
	return true;

}
function isSelect()
{
	return true;
}

// I am making it true for everything. That is, if an object is displayed on the system, it is selectable.
function isTreeSelect()
{
	return true;
}

final protected function initObjectIfUndef($class)
{
	$objectname = $class . "_o";

	$this->__object_list = array_push_unique($this->__object_list, $class);

	if (isset($this->$objectname) && $this->$objectname != NULL) {
		return 0;
	}

	$name = exec_class_method($class, 'initThisObjectRule', $this, $class);

	if ($name) {
		$obj = new $class($this->__masterserver, $this->__readserver, $name);
		$obj->get();
	} else {
		$obj = exec_class_method($class, 'initThisObject', $this, $class);
	}

	// If the object doesn't exist and is newly created, then assign it fully to the current guy. WIhtout this, it becomes impossible to delete the object if it doesn't exist in the db.
	if (!$obj) {
		$obj = new $class($this->__masterserver, $this->__readserver, $name);
		$obj->get();
	}


	// Just forcibly set the syncserver...
	$obj->inheritSyncServer($this);

	if ($obj->dbaction === 'add') {
		$obj->parent_clname = $this->getClName();
		if ($obj->nname !== '__tmp_lx_name__') {
			dprintr("<b> Getobject Created the {$class} {$obj->nname} object fresh in {$this->getClName()} ... </b>");
			debugBacktrace();
		}
		$obj->inheritSyncServer($this);
	}

	if ($obj) {
		$obj->__parent_o = $this;
	}
	$this->$objectname = $obj;

}

function getDbOrderLimit($filter, $count)
{
		
	$skiprows = $filter['pagesize'] * ($filter['pagenum'] - 1);
	if ($skiprows >= $count) {
		$skiprows = 0;
	}

	//$sortstr = "order by {$filter['sortby']} {$filter['sortdir']}";
	$sortby = $filter['sortby'];
	$sortdir = $filter['sortdir'];
	$pagesize = $filter['pagesize'];
	//$orderstr = "{$sortstr} limit {$skiprows},{$filter['pagesize']} ";

	$ret['sortby'] = $sortby;
	$ret['sortdir'] = $sortdir;
	$ret['revsortdir'] = ($sortdir === 'asc')? 'desc': 'asc';
	$ret['pagesize'] = $pagesize ;
	$ret['skiprows'] = $skiprows;

	return $ret;
}



static function getdbFilter($filter = null, $class)
{
	static $oplist = array('gt' => '>', 'lt' => '<', 'eq' => '===', 'ne' => '!=', 'cont' => 'LIKE');
	$total = null;
	
	foreach((array) $filter as $key => $val) {
		if ($key === 'sortby' || $key === 'searchstring' || $key === 'sortdir' || $key === 'pagenum' || $key === 'pagesize') {
			continue;
		}
		if (char_search_a($key, "_o_")) {
			$var = substr($key, 0, strpos($key, "_o_"));
			$op = substr($key, strpos($key, "_o_") + 3);
			//$op = $oplist[$op];
			$var = str_replace(array("\"", "'", ";"), "",  $var);
			$val = str_replace(array("\"", "'", ";"), "", $val);
			if ($val) {
				if ($val === '--any--') {
					continue;
				}

				if (cse($var, "_q")) {
					$var = strtil($var, "_q");
					if ($val === 'overquota') {
						$total[] = "(priv_q_$var != 'Unlimited' AND (used_q_$var + 0) > (priv_q_$var + 0))";
					} else {
						$total[] = "(priv_q_$var = 'Unlimited' OR (used_q_$var + 0) <= (priv_q_$var + 0))";
					}
					continue;
				}
				if ($op == 'cont') {
					if ($val[0] === '^') {
						$val = substr($val, 1);
						$val = "$val%";
					}  else {
						$val = "%{$val}%";
					}
				}
				$total[] = "{$var} {$oplist[$op]} '{$val}'";
			}

		} else {
			$f = "__hpfilter_{$key}_{$val}";
			$res = get_real_class_variable($class, $f);
			if (is_array($res)) {
				$total[] = implode(" ", $res);
			}
			
		}
	
	}

	if (isset($filter['searchstring']) && $filter['searchstring']) {
		$var = exec_class_method($class, 'searchVar');
		$total[] = "$var LIKE '%{$filter['searchstring']}%'";
	}

	$andstr = null;
	if ($total) {
		$andstr = implode(" AND  ", $total);
	}
	return $andstr;

}

function backupExtraVar(&$vlist) {}


static function hasViews() { return false; }

static function filterFunc($op, $oval, $val)
{
	static $oplist = array('gt' => '>', 'lt' => '<', 'eq' => '===', 'ne' => '!=');
	
	if (isset($oplist[$op])) {
		$string = "('{$oval}' {$oplist[$op]} '{$val}')";
		return eval("return {$string} ;");
	}

	if ($op === 'cont') {
		return strstr($oval, $val)? true: false;
	}
}


function isDisplay($filter = null) 
{

	global $gbl, $sgbl, $login;
	return true;
	if (!$filter)
		return 1;


	$class = lget_class($this);

	$res = 1;
	foreach($filter as $key => $val) {
		if (char_search_a($key, "_o_")) {
			$var = substr($key, 0, strpos($key, "_o_"));
			$op = substr($key, strpos($key, "_o_") + 3);
			//$op = $oplist[$op];

			if (!isset($this->$var)) {
				$oval = $a->display($var);
			} else {
				$oval = $this->$var;
			}

			$res &= self::filterFunc($op, $oval, $val);

		} else {
			$f = "__filter_{$key}_{$val}";
			$string = get_real_class_variable($class, $f);
			$res &= eval("return {$string};");
		}
	
	}
	return $res;
}


static function isTreeForDelete()
{
	return false;
}

function getDefaultQuery($class, $result)
{
	if (is_array($result)) {
		foreach($result as &$k) {
			if (is_array($k)) {
				$k = implode(" ", $k);
			}
		}
		$query = implode(' ', $result);
		$query = "where ({$query})";
	} else {
		if ($result === '__v_table') {
			$query = "";
		}
	}
	return $query;
}


final protected function initListIfUndef($class)
{

	$list = $class . "_l";
	$typevar = "__listtype_{$class}"; 
	$totalvar = "__virtualtotal_{$class}";

	//list($iclass, $mclass, $rclass) = get_composite($class);
	$rclass = $class;
	//if (isset($this->$list) && $this->$list != NULL) {

	// this is necessary. AFteare was the list is cleared. So if you want to do two wases in the same place
	$this->__list_list = array_push_unique($this->__list_list, $class);

	if (isset($this->$typevar) && ($this->$typevar === 'fullist')) {
		return;
	}
	if (!isset($this->$list)) {
		$this->$list = null;
	}


	$this->backuplist = $this->$list;

	if ($class === 'domaintraffic') {
		$rule = domaintraffic::initThisListRule($this, $class);
	} else {
		$rule = exec_class_method($rclass, 'initThisListRule', $this, $class);
	}


	if ($rule) {
		$query = $this->getDefaultQuery($class, $rule);
		$query .= "order by nname";

		$db = new Sqlite($this->__masterserver, $this->getTheTable($class));
		$res = $db->getRowsGeneric($query);

	} else {
		if ($class != "ssession")
			dprint("Calling External Function to initialize {$class} list <br> ", 3);

		$res = exec_class_method($rclass, 'initThisList', $this, $class);
	}

	$this->setListFromArray($this->__masterserver, $this->__readserver, $class, $res);

	foreach((array) $this->backuplist as $v) {
		$this->{$list}[$v->nname] = $v;
	}

	$this->$typevar = 'fullist';

	$this->$totalvar = count($this->$list);

	if (!isset($this->$list)) {
		$this->$list = NULL;
	}
}

final function getUrlIdentity()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$navig = $gbl->__navig;
	$navigmenu = $gbl->__navigmenu;


	$string = null;
	foreach((array) $navigmenu as $k => $o) {
		if ($o[0] === 'show' && $o[1] === $this) {
			if (isset($navig[$k]['frm_o_o'])) {
				$string = $ghtml->get_get_from_post(null, $navig[$k]['frm_o_o']);
			} else {
				$string = 'login';
			}
			$string = fix_nname_to_be_variable($string);
			break;
		}
	}
	return $string;

}


function inNoBackuplist()
{
	global $gbl, $sgbl, $login, $ghtml; 

}


function loadBackupAll()
{
	global $gbl, $sgbl, $login, $ghtml; 

	if ($this->inNoBackuplist()) {
		return;
	}
	$list = $this->getBackupChildList();

	//print("Loading {$this->get__table()} {$this->nname}\n");

	dprint("I am : {$this->get__table()} {$this->nname}\n");
	if ($this->extraBackup()) {
		if (!isset($gbl->__var_objectbackuplist)) {
			$gbl->__var_objectbackuplist = array();
		}
		$gbl->__var_objectbackuplist[] = $this;
	}

	foreach((array) $list as $c) {
		if (cse($c, "_l")) {
			$cn = $this->getChildNameFromDes($c);
			$clist = $this->getList($cn);
			foreach((array) $clist as $ch) {
				$ch->__parent_o = $this;
				print("Setting parent of {$ch->getClName()} to {$this->getClName()}\n");
				if ($ch->parent_clname && !$ch->isRightParent()) {
					unset($this->{$c}[$ch->nname]);
					continue;
				}
				$ch->loadBackupAll();
			}
		}
		if (cse($c, "_o")) {
			$cn = $this->getChildNameFromDes($c);
			if ($this->isRealChild($cn)) {
				$ch = $this->getObject($cn);

				if ($ch->dbaction === 'add') {
					$ch->dbaction = 'clean';
				}

				$ch->__parent_o = $this;

				if (!$ch->isRightParent()) {
					unset($this->$c);
					continue;
				}
				$ch->loadBackupAll();
			}
		}
	}

}

function isRealChild($c)
{
	return true;
}
function getFilterVariableForThis($class)
{
	$filtervar = "__filtervar_{$class}";
	if (isset($this->$filtervar)) {
		return $this->$filtervar;
	}

	$name = fix_nname_to_be_variable($this->nname);

	$id = "{$this->get__table()}_{$name}__{$class}";
	$this->$filtervar = $id;
	return $id;

}
final function getFilterForThis($class)
{
	global $gbl, $sgbl, $login, $ghtml; 

	//list($iclass, $mclass, $rclass) = get_composite($class);
	$rclass = $class;
	$filtervar = "__hfilter_{$class}";
	if (isset($this->$filtervar)) {
		return $this->$filtervar;
	}

	$filter = null;

	$filtername = $this->getFilterVariableForThis($class);
	if ($login->issetHpfilter($filtername)) {
		$filter = $login->getHPFilter($filtername);
	}
	//dprint("hello: " . $filtername);
	if (!isset($filter['sortby'])) {
		$filter['sortby'] = exec_class_method($rclass, "defaultSort");
	}

	if (!isset($filter['pagenum'])) {
		$filter['pagenum'] = '1';
	}
	if (!isset($filter['pagesize'])) {
		$filter['pagesize'] = exec_class_method($rclass, 'perPage');
	}

	if (!isset($filter['sortdir'])) {
		$filter['sortdir'] = exec_class_method($rclass, "defaultSortDir");
	}
	$this->$filtervar = $filter;
	return $filter;

}

function canSeePserver()
{
	if (!$this->isAdmin()) {
		return false;
	}

	if ($this->isAuxiliary()) {
		if ($this->__auxiliary_object->isOn('pserver_flag')) {
			return true;
		}
		return false;
	}
	return true;
}

function doGetHpf()
{
	if ($this->isAuxiliary()) {
		return $this->__auxiliary_object->hpfilter;
	}

	return $this->hpfilter;
}

function doSetHpf($hpfilter)
{
	if ($this->isAuxiliary()) {
		$this->__auxiliary_object->hpfilter = $hpfilter;
		return;
	}
	$this->hpfilter = $hpfilter;
}

function getHPFilter($fil = null, $var = null)
{
	$hpfilter = $this->doGetHpf();
	if ($var == null && $fil === null) {
		return $hpfilter;
	}

	if ($var === null) {
		return $hpfilter[$fil];
	}

	return $hpfilter[$fil][$var];
}

function setupHpFilter($filter)
{
	$hpfilter = $this->doGetHpf();
	foreach($filter as $k => $v) {
		foreach($v as $kk => $vv) {
			$hpfilter[$k][$kk] = $vv;
		}
	}
	$this->doSetHpf($hpfilter);
}

function hpfilterUnset($fil)
{
	$hpfilter = $this->doGetHpf();
	unset($hpfilter[$fil]);
	$this->doSetHpf($hpfilter);
}

function issetHpFilter($fil = null, $var = null)
{
	$hpfilter = $this->doGetHpf();

	if ($var == null && $fil === null) {
		return isset($hpfilter);
	}

	if ($var === null) {
		return isset($hpfilter[$fil]);
	}

	return isset($hpfilter[$fil][$var]);
}



final protected function initVirtualListIfUndef($class)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$list = "{$class}_l";

	//list($iclass, $mclass, $rclass) = get_composite($class);
	$rclass = $class;
	$this->__list_list = array_push_unique($this->__list_list, $class);

	$typevar = "__listtype_{$class}"; 
	$totalvar = "__virtualtotal_{$class}";

	if (!isset($this->$list)) {
		$this->$list = null;
	}


	if (isset($this->$typevar) && (($this->$typevar === 'fullist') || ($this->$typevar === 'virtuallist'))) {
		return $this->$totalvar;
	}
	

	$this->backuplist = $this->$list;


	if (($rule = exec_class_method($rclass, 'initThisListRule', $this, $class))) {
		$query = $this->getDefaultQuery($class, $rule);
		//dprint(' <br> ' .$query . "<br> \n");
		$filter = $this->getFilterForThis($class);
		$string = exec_class_method($rclass, "getdbFilter", $filter, $class); 

		if ($string) {
			if ($query) { 
				$query .= " AND {$string}";
			} else {
				$query .= " where {$string}";
			}
		}
		$db = new Sqlite($this->__masterserver, $this->getTheTable($rclass));

		$countquery = $query;

		$table = $this->getTheTable($rclass);


		print_time('count');
		//$db->rawquery("begin;");
		$countres = $db->rawquery("select count(*) from {$table} {$countquery}");
		if ($sgbl->__var_database_type === 'mysql') {
			$countres = $countres[0]['count(*)'];
		} else if ($sgbl->__var_database_type === 'mssql') {
			$countres = $countres[0]['computed'];
		} else {
			$countres = $countres[0]['count(*)'];
		}


		//print_time('count', "CountResult");

		print_time('getdb');
		$f = $this->getDbOrderLimit($filter, $countres, $class);

		$table = $this->getTheTable($rclass);
		$sortby = $f['sortby'];
		$sortdir = $f['sortdir'];
		$skiprows = $f['skiprows'];
		$pagesize = $f['pagesize'];
		$revsortdir = $f['revsortdir'];

		if ($sgbl->__var_database_type === 'mysql') {
			$desc = get_classvar_description($class, $sortby);
			if (csa($desc[0], "q")) {
				$sortby = "(used_q_{$sortby} + 0)";
			}
			$query = "select * from {$table} {$countquery} order by {$sortby} {$sortdir} limit {$skiprows}, {$pagesize}";
		} else if ($sgbl->__var_database_type === 'mssql') {
			$tot = $pagesize + $skiprows;
			$query = "select * from (select top {$pagesize} * from ( select top {$tot} * from {$table} {$countquery} order by {$sortby} {$sortdir} ) as t_{$table} order by {$sortby} {$revsortdir}) as t2_{$table} order by {$sortby} {$sortdir}";
		} else {
			$query = "select * from {$table} {$countquery} order by {$sortby} {$sortdir} limit {$skiprows}, {$pagesize}";
		}


		$res =  $db->rawQuery($query);
		//$db->rawquery("commit;");
		//print_time('getdb', 'GetResult');

	} else { 
		$res = exec_class_method($rclass, 'initThisList', $this, $class);
		$countres = count($res);
	}

	//$this->ApplyFilter($res);

	$this->setListFromArray($this->__masterserver, $this->__readserver, $class, $res);


	foreach((array) $this->backuplist as $v) {
		$this->{$list}[$v->nname] = $v;
	}

	$this->$typevar = 'virtuallist';
	$this->$totalvar = $countres;

	return $countres;

}


function getHardProperty() { }

function getParentClass($var = 'parent_clname')
{
	$tvar = "__ptc_{$var}";
	if (isset($this->$tvar)) {
		return $this->$tvar;
	}
	list($pclass, $pname) = getParentNameAndClass($this->$var);
	$this->$tvar = $pclass;
	return $pclass;

}

function getParentName($var = 'parent_clname')
{
	$tvar = "__ptn_{$var}";
	list($pclass, $pname) = getParentNameAndClass($this->$var);
	$this->$tvar = $pname;
	return $pname;
}

function getSpecialname()
{
	if (csa($this->nname, "_s_vv_p_")) {
		list($pclass, $pname) = explode("_s_vv_p_", $this->nname);
		return $pname;
	} else {
		return $this->nname;
	}
}


function getClName()
{
	//return "{$this->get__table()}_s_vv_p_{$this->nname}";
	return "{$this->get__table()}-{$this->nname}";
}

/*
function __get($var)
{
	global $gbl, $sgbl, $login, $ghtml; 

	 This is absurd. At least set also should trigger it. Otherwise variables will get overwritten after they are set. What the fuck....
	if (isset($this->__dbvar)) {
		$this->setFromArray($this->__dbvar);
		unset($this->__dbvar);
	}

	if (isset($this->$var)) {
		return $this->$var;
	}
	if ($var === 'cttype') {
		return lget_class($this);
	}

	$string = backtrace_once();
	if ($string) {
		dprint("\n\n<br> <br> <b> Non Existent: </b> {$this->get__table()}:$this->nname $var ");
		dprintr("Backtrace: $string<br><br>\n \n");
	} else {
		dprint(" Non Existent: {$this->get__table()}:$this->nname $var <br> ");
	}

	//debugBacktrace();
	return null;
}
*/
/*

function __set($var, $val)
{
	print("$var $val");
	$this->$var = $val;
}

function __get($var)
{
	if (preg_match("/_o$/i", $var)) {
		$class = preg_replace("/_o$/i", "", $var);
		dprint("Class: $class", 2);
	$this->initObjectIfUndef($class);
	return $this->$var;
}
if (preg_match("/_l$/i", $var)) {
	$class = preg_replace("/_l$/i", "", $var);
	$this->initListIfUndef($class);
	return $this->$var;
}

return $this->$var;
}

*/

/** 
* @return void 
* @param 
* @param 
* @desc creates a whole object_l list for the parent. Mostly called by the parent after reading all teh children from the db.
*/ 
 

final protected function setListFromArray($masterserver, $readserver, $class, $result, $force = false)
{
	//list($iclass, $mclass, $rclass) = get_composite($class);
	$rclass = $class;
	$list = "{$class}_l" ;

	if (!isset($this->$list)) {
		$this->$list = NULL;
	}
	if (!$result) {
		return;
	}

	foreach($result as $row) {
		$al[$row['nname']] = new $rclass($masterserver, $readserver, $row['nname']);

		if ($force) {
			$al[$row['nname']]->setFromArray($row);
		} else {
			$al[$row['nname']]->setFromArray($row);
			if($al[$row['nname']]->hasDriverClass()) {
				$al[$row['nname']]->createSyncClass();
			}
		}

	}


	$this->$list = $al;
}

function isRightParent()
{
	$v = ($this->getParentO()->getClname() === $this->parent_clname);
	$vvv = ($this->getParentO()->getClname() === $this->nname);
	//[b] hack
	$mailf = ($this->getParentO()->isClient() && $this->isClass('mailaccount'));
	$mailfo = ($this->getParentO()->isClient() && $this->isClass('mailforward'));
	$addonfo = ($this->getParentO()->isClient() && $this->isClass('addondomain'));
	$mlist = ($this->getParentO()->isClient() && $this->isClass('mailinglist'));

	return $v || $vvv || $mailf || $mailfo || $addonfo || $mlist;
}

function getLimitOrPermissionUrl(&$alist)
{
	if ($this->isLteAdmin()) {
		return;
	}
	if ($this->isLogin()) {
		//$alist[] = "a=list&c=permission";
	} else {
		if ($this->doesHaveTemplate()) {
		}
	}
}

function doesHaveTemplate()
{
	$v = $this->get__table();

	if (class_exists("{$this->get__table()}template")) {
		return true;
	}
	return false;
}


final function addEobject($obj)
{
	$class = "exx{$this->getClass()}";
	$this->addObject($class, $obj);
}

final function getEobject()
{
	$class = "exx{$this->getClass()}";
	return getObject($class);
}

final function delFromList($class, $ll)
{
	if (cse($class, "_a")) {
		$dellistvar = "__t_delete_{$class}_list";
		//Calling this buggers update... the security checks r in there....
		$this->update("delete", null);
		$this->setUpdateSubaction("delete_{$class}");
		if (!isset($this->$dellistvar)) {
			$this->$dellistvar = null;
		}
		foreach($ll as $l) {
			$this->{$dellistvar}[] = $this->{$class}[$l];
			// Hack... Temporirily creating parent_o for the quota updation in parent to take place...
			$this->{$class}[$l]->__parent_o = $this;
			$this->{$class}[$l]->delete();
			unset($this->{$class}[$l]);
		}
	} else {
		foreach($ll as $l) {
			$obj = $this->getFromList($class, $l);
			if (!$obj) {
				throw new lxexception('object_doesnt_exist', '', $l);
			}
			$obj->update("delete", null);
			$obj->delete();
		}
	}
}

function getStubUrl($name) { return null; }

final function addObject($class, $obj)
{
	$object = "{$class}_o";
	$this->$object = $obj;
	$obj->__parent_o = $this;
	if (!isset($obj->parent_clname)) {
		$obj->parent_clname = $this->getClName();
	}
	$this->__object_list = array_push_unique($this->__object_list, $class);
}

function findLeastId($listclass)
{


}

final function addToList($class, $object)
{
	if (cse($class, "_a")) {
		$this->{$class}[$object->nname] = $object;
		$newvvar = "__t_new_{$class}_list";
		$this->{$newvvar}[$object->nname] = $object;
		$this->setUpdateSubaction('add_' . $class);
		return;
	}

	$this->__list_list = array_push_unique($this->__list_list, $class);
	$object->__parent_o = $this;

	$list = "{$class}_l";

	//$this->initListIfUndef($class);

	if (!isset($this->$list))  {
		$this->$list = null;
	}

	if (isset($this->{$list}[$object->nname])) {
		throw new lxexception("{$object->nname} already exists in {$list} in {$this->nname}");
	}

	$this->{$list}[$object->nname] = $object;
	return 1;
}


final function getObject($class)
{
	$object = "{$class}_o";
	$this->checkForDescribed($class, "o");

	$this->initObjectIfUndef($class);
	if ($this->$object) {
		$this->$object->__parent_o = $this;
	}
	return $this->$object;
}

/** 
* @return void 
* @param 
* @param 
* @desc  Special getfromlist for ffile, dirprotect etc. See the getFfilefromVirtuallist function in weblib.. The concept is that the the whole directory tree is available virtually under an ffile object, thus enabling us to get any object at any level. This is different from other objects where there is only one level of children.
*/ 
final function getFromVirtualList($class, $name)
{
	$list = "{$class}_l";
	$func = "get{$class}FromVirtualList";
	if (method_exists($this, $func)) {
		$obj = $this->$func($name);
		if (!$obj->isNonExistant()) {
			$this->__list_list = array_push_unique($this->__list_list, $class);
			if (!isset($this->$list)) {
				$this->$list = null;
			}
			$this->{$list}[$name] = $obj;
		}
		return $obj;
	}
	return null;
}

function isNonExistant()
{
	return (isset($this->status) && $this->status === 'nonexistant');
}

function isContactable()
{
	return false;
}

/** 
* @return void 
* @param 
* @param 
* @desc Keeps the parent info intact, unlike the 'resolve_class_differences' which just returns the final class.
*/ 
 
static function resolve_class_heirarchy($class, $property, &$dclass, &$dproperty)
{

	$dclass = $class;
	$dproperty = $property;
	if (csa($property, "_s_") || csa($property, "-")) {
		if (csa($property, "_s_")) {
			$list = explode('_s_', $property);
		} else {
			$list = explode("-", $property);
		}
		$dproperty = $list[count($list) - 1];
		unset($list[count($list) -1]);
		$dclass = implode('_s_', $list);
	}
}

static function resolve_class_differences($class, $property, &$dclass, &$dproperty)
{

	$dclass = $class;
	$dproperty = $property;
	if (csa($property, "_s_") || csa($property, "-")) {
		if (csa($property, "_s_")) {
			$list = explode('_s_', $property);
		} else {
			$list = explode("-", $property);
		}
		$dclass = $list[count($list) - 2];
		$dproperty = $list[count($list) - 1];
	}
}
function getClientParentO()
{
	$pp = $this;

	while ($pp) {
		if ($pp->isLxclient()) {
			return $pp;
		}
		$pp = $pp->getParentO();
	}
	return null;
}

function getRealClientParentO()
{
	$pp = $this;

	while ($pp) {
		if ($pp->get__table() === 'client') {
			return $pp;
		}
		$pp = $pp->getParentO();
	}
	return null;
}

function getAllChildrenAndParents($alltag)
{
	$list = null;
	$pp = $this;
	$i = 0;
	while($pp) {
		$i++;
		$class = $pp->get__table();
		if (!$pp->isLxclient()) {
			$pp = $pp->getParentO();
			continue;
		}

		if ($pp !== $this) {
			$list[$pp->getClName()] = "{$pp->nname} ({$class})";
		}
		if ($i > 10) {
			exit;
		}

		$pp = $pp->getParentO();
	}

	if ($list) {
		$list = array_reverse($list);
	}

	$list[$alltag['key']] = $alltag['val'];

	$cl = $this->getChildListFilter('L');
	foreach($cl as &$c) {
		$c = $this->getChildNameFromDes($c);
		$child = $this->getVirtualList($c, $count);
		foreach((array) $child as $q) {
			$list[$q->getClName()] = "$q->nname ({$q->get__table()})";
		}
	}
	return $list;

}

function getTrueParentO()
{

	//dprint("Class: . " . $this->get__table() . "<br> ");

	// DOn't pointlessly load objects from the db.
	if ($this->__parent_o && is_object($this->__parent_o) && ($this->__parent_o->getClName() === $this->parent_clname)) {
		$this->__true_parent_o = $this->__parent_o;
		return $this->__true_parent_o;
	}

	if (isset($this->__true_parent_o) && $this->__true_parent_o) {
		return $this->__true_parent_o;
	}
	if (!$this->parent_clname) {
		print("Critical internal error. there is no parent_clname for {$this->getClname()} \n<br> ");
		exit;
		return $this->getParentO();
		return null;
	}

	list ($pclass, $pname) = getParentNameAndClass($this->parent_clname);

	$parent = new $pclass($this->__masterserver, $this->__readserver, $pname);
	$parent->get();
	if ($parent->dbaction === 'add') {
		$this->__true_parent_o = $this->getParentO();
		return $this->getParentO();
	}

	$this->__true_parent_o = $parent;
	return $parent;

}

static function defaultParentClass($parent) { return null; }

function getParentO()
{

	//dprint("Class: . " . $this->get__table() . "<br> ");

	if (isset($this->__parent_o) && $this->__parent_o) {
		return $this->__parent_o;
	}

	if (isset($this->cttype) && ($this->cttype === 'admin' || $this->cttype === 'superadmin')) {
		return null;
	}

	if (!$this->parent_clname) {
		dprint("$this->nname doesn't have parent_clname\n");
		//dprintr($this);
		return null;
	}

	list ($pclass, $pname) = getParentNameAndClass($this->parent_clname);

	$parent = new $pclass($this->__masterserver, $this->__readserver, $pname);
	$parent->get();
	$this->__parent_o = $parent;
	return $parent;

}

// Used mainly in gbl to get some global resources like resource plans.
final function getIt($master, $class, $name)
{
	if (!$master) {
		$master = 'localhost';
	}
	$list = $master . $class . "_lo";
	if (!isset($this->$list)) {
		$this->$list = null;
	}

	if (isset($this->{$list}[$name])) {
		return $this->{$list}[$name];
	}

	$ob = new $class($master, $master, $name);
	$ob->get();

	$this->{$list}[$name] = $ob;
	return $ob;
}

function getTheTable($class)
{
	$table = get_class_variable($class, "__table");
	if (!$table) { $table = $class; }
	return $table;
}

static function addCommand($parent, $class, $p)
{
	if (isset($p['name'])) { $param['nname'] = $p['name']; }
	foreach($p as $k => $v) {
		if (csb($k, "v-")) {
			$kk = strfrom($k, "v-");
			$param[$kk] = $v;
		}
	}
	return $param;
}

function commandUpdate($subaction, $param)
{
	return $param;
}

final function getFromList($class, $name)
{

	if (cse($class, "_a")) {
		return $this->{$class}[$name];
	}

	// A small hack.. If asked for the same object, return ourselves. This is the best way to do it.. I think this does not really break teh conceptual integrity. Every object must have itself as a virtual entity. Even if it is not there in the list, it should be gettable.... The only question is the uniqueness. BUt our model of unique nname makes sure of that too...
	if (($class === lget_class($this)) && $name === $this->nname) {
		return $this;
	}

	// A virtual list concept only implemented and need in ffile. This allows us to get an object without initializing the whole list.
	$this->__list_list = array_push_unique($this->__list_list, $class);

	$ret = $this->getFromVirtualList($class, $name);
	if ($ret) {
		$ret->__parent_o = $this;
		return $ret;
	}

	$name = str_replace("'", "", $name);


	$list = "{$class}_l";



	if (!isset($this->$list)) {
		$this->$list = null;
		//throw new lxexception ("The " . get_class($this) . ":$list is NULL ");
	}

	// Very important. If it is already set, then don't ever load it from the database.
	if (isset($this->{$list}[$name])) {
		$object = $this->{$list}[$name];
		return $object;
	}

	if (exec_class_method($class, 'canGetSingle')) {

		if (isset($this->{$list}[$name])) {
			return $this->{$list}[$name];
		}

		$rule = exec_class_method($class, 'initThisListRule', $this, $class);
		if ($rule) {
			$query = $this->getDefaultQuery($class, $rule);

			if ($query) {
				$query .= " AND nname = '{$name}'";
			} else {
				$query .= "where nname = '{$name}'";
			}
			$db = new Sqlite($this->__masterserver, $this->getTheTable($class));
			$res = $db->getRowsGeneric($query);

			if (!$res) {
				throw new lxexception ("The Element {$name} Doesnt Exist in. {$this->getClass()}:{$this->nname} {$list}");
			}

			$obj = new $class($this->__masterserver, $this->__readserver, $name);
			$obj->setFromArray($res[0]);
			$this->{$list}[$name] = $obj;
		} else {
			$this->{$list}[$name] = exec_class_method($class, 'initThisObject', $this, $class, $name);
		}
		
	} else {

		$this->initListIfUndef($class);

		if (!$this->$list) {
			$this->$list = null;
			//throw new lxexception ("The " . get_class($this) . ":$list is NULL ");
		}
	}


	if (isset($this->{$list}[$name])) {
		// The virtual objects already has a __parent_o, and so we shouldn't add our own __parent_o
		if (!$this->isVirtual($class)) {
			$this->{$list}[$name]->__parent_o = $this;
		}
		$this->{$list}[$name]->postRead();
		return $this->{$list}[$name];
	} else {
		throw new lxexception ("The Element {$name} Doesnt Exist in. {$this->getClass()}:{$this->nname} {$list}");
	}
}


final function hasExceededQuota($var)
{

	if ($this->priv->$var === "Unlimited" || $this->used->$var < $this->priv->$var) {
		return false;
	}
	return true;

}


function postRead()
{
}

final function clearList($class)
{

	$list = "{$class}_l";

	$this->$list = null;
	unset($this->$list);
}

function __sleep()
{
	/// Clearing certain variables.
	$this->__parent_o = null;
	$this->__old_used = null;
	$this->__virtual_list = array();
	unset($this->__parent_o);
	$res = get_object_vars($this);
	//unset($res[1]);
	//unset($res[0]);
	$keys =  array_keys($res);
	return $keys;
}

function getPlanList()
{
	$planlist = $this->getList('resourceplan');

	if (!$planlist) {
		throw new lxException("no_plans_found", 'parent');
	}

	$planlist = get_namelist_from_objectlist($planlist);
	return $planlist;
}


static function canGetSingle()
{
	return false;
}

function isSingleObject($class)
{
	$v = "__desc_{$class}_o";
	return get_class_variable($this->getClass(), $v);
}

final function getList($class)
{
	if (cse($class, "_a")) {
		return $this->$class;
	}


	$this->checkForDescribed($class, "l");
	$list = "{$class}_l";

	$this->initListIfUndef($class);


	//Setting the parent Forcibly.... Php screws up recursion.. This should have only been done in the initlistifundef and only the first time, but there u fucking have it.. Stupid php..
	foreach((array) $this->$list as $o) {
		$o->__parent_o = $this;
	}

	return $this->$list;
}

// This functions makes sure that all the objects are defined properly. This is necessary since these definitions are used to determine the actions on the object when the parent gets deleted, toggled added etc. This makes sure that we don't have children whose properties are not properly described.

function checkForDescribed($class, $type)
{
	$desc = "{$class}_{$type}";
	if (!get_classvar_description($this->get__table(), $desc)) {
		dprint("\n");
		dprint("<b> Trying to init a nondescribed Class {$class} as {$type} in {$this->get__table()}: {$this->nname} <br> ");
	}
}

static function getExtraParameters($parent, $list) {}
final function getVirtualList($class, &$count, $sortby = null, $sortdir = null)
{
	global $gbl, $sgbl, $login, $ghtml; 

	//list($iclass, $mclass, $rclass) = get_composite($class);
	$rclass = $class;

	if (!cse($class, "_a")) {
		$this->checkForDescribed($class, "l");
	}

	$ifdb = exec_class_method($rclass, "canGetSingle");


	$newres = null;
	if (!$ifdb) {
		if (cse($class, "_a")) {
			$res = $this->$class;
		} else {
			$res =  $this->getList($class);
		}

		$filter = $this->getFilterForThis($class);
		$filtername = $this->getFilterVariableForThis($class);

		if (isset($filter['sortby'])) {
			$sortby = $filter['sortby'];
		}

		if (isset($filter['sortdir'])) {
			$sortdir = $filter['sortdir'];
		}


		if ($sortby) {
			$__tvar = "__sortby_{$class}";
			if (!isset($this->$__tvar) || $this->__sortby_{$class} != $sortby || $this->__sortdir != $sortdir) {
				$this->__sortby = $sortby;
				$this->__sortdir = $sortdir;
				$this->$__tvar = $sortby;
				if ($res && $sortby) {
					uasort($res, array($this, "_compare"));
				}
			}
		}


		$var = exec_class_method($rclass, 'searchVar');
		foreach((array) $res as $k => $r) {
			if (!$r) {
				continue;
			}
			if (!$r->isDisplay()){
				unset($res[$k]);
				continue;
			}
			if (isset($filter['searchstring']) && $filter['searchstring']) {
				if (!csa($r->$var, $filter['searchstring'])) {
					unset($res[$k]);
				}
			}
		}




		$count = count($res);
		$n = 0;
		foreach((array) $res as $row) {
			if ($n < $filter['pagesize'] * ($filter['pagenum'] - 1)) {
				$n++;
				continue;
			}
			$n++;
			$newres[$row->nname] = $row;
			if ($n === $filter['pagesize'] * $filter['pagenum']) {
				break;
			}
		}
		return $newres;
	}


	$list = "{$class}_l";

	$count = $this->initVirtualListIfUndef($class);

		
	if (cse($class, "_a")) {
		return $this->$class;
	}

	// Setting the parent Forcibly.... Php screws up recursion.. 
	// This should have only been done in the initlistifundef and only the first time, 
	// but there u fucking have it.. Stupid php..
	foreach((array) $this->$list as $o) {
		$o->__parent_o = $this;
	}

	return $this->$list;
}

// Hack ahack hack ...

function setDbactionForRestoreChild($trulist, $real = false)
{
	global $gbl, $sgbl, $login, $ghtml; 



}

function AddToArrayObjectList($class, $objectlist)
{
	$myobjlist = $this->$class;
	$var = "__t_new_{$class}_list";
	if ($myobjlist)  {
		$__t_newobjlist = null;
		if ($this->$var) {
			$__t_newobjlist = $this->$var;
		}
		foreach((array) $objectlist as $o) {
			// This seems to happen with the old dns records a_rec_a etc, 
			// which were removed from the dns. I encountered because I restored a very old backup.
			if (!isset($o->nname)) {
				continue;
			}
			if (!isset($myobjlist[$o->nname])) {
				$myobjlist[$o->nname] = $o;
				$__t_newobjlist[$o->nname] = $o;
			}
		}
		$this->$class = $myobjlist;
		$this->$var = $__t_newobjlist;
	} else {
		$this->$class = $objectlist;
		$this->$var = $objectlist;
	}

}

function hasFileResource() { return false; }
function hasFunctions() { return false; }


function getAnyErrorMessage()
{
	global $gbl, $sgbl, $login, $ghtml; 

	if ($this->isLogin() && $this->isLxclient() && !isset($ghtml->__http_vars['frm_emessage'])) {
		if (($count = $this->checkTicketUnread())) {
			$ghtml->__http_vars['frm_smessage'] = 'you_have_unread_ticket';
			$ghtml->__http_vars['frm_m_smessage_data'] = $count;
		}
		if (($count = $this->checkMessageUnread())) {
			$ghtml->__http_vars['frm_smessage'] = 'you_have_unread_message';
			$ghtml->__http_vars['frm_m_smessage_data'] = $count;
		}

		if ($this->isContactable()) {
			if (!$this->contactemail) {
				$ghtml->__http_vars['frm_smessage'] = 'contact_not_set';
			} else if (!validate_email($this->contactemail)) {
				$ghtml->__http_vars['frm_smessage'] = 'contact_set_but_not_correct';
			}
		}

		if ($sgbl->isLxlabsClient()) {
			$invoice = $login->getLastInvoice();
			if ($invoice && $invoice->getTotalPaid() == 0) { 
				$url = "a=show&k[class]=invoice&k[nname]=$invoice->nname";
				$url = $ghtml->getFullUrl($url);
				$ghtml->__http_vars['frm_emessage'] = 'invoice_not_paid';
				$ghtml->__http_vars['frm_m_emessage_data'] = $invoice->nname;
			}
		}

	}

}

function object_print()
{
	static $tab;
	$tab .= " ";
	$cl = $this->getBackupChildList();

	foreach((array) $cl as $c) {
		if (cse($c, "_l")) {
			if ($this->$c) {
				foreach($this->$c as $ch) {
					print("{$tab} ListMember {$ch->getClName()} \n");
					$ch->object_print();
					$ch->__parent_o = $this;
				}
			}
		}
		if (cse($c, "_o")) {
			$ch = $this->$c;
			if ($ch) {
				print("{$tab} Object {$ch->getClName()} \n");
				$ch->object_print();
				$ch->__parent_o = $this;
			}
		}
	}
}


function AddMEssageOnlyIfClientDomain($message)
{
	$this->__v_message = $message;
	return;
}

function isCoreBackup() { return false; }


// Not needed now. Was used for databases. but now onwards, the entire cluster single database naming.

function changeNnameRewrite()
{
	$rewrite = get_class_variable($this->get__table(), "__rewrite_nname_const");
	if ($rewrite && array_search_bool("syncserver", $rewrite)) {
		foreach($rewrite as $n) {
			$nnamelist[] = $this->$n;
		}
		$this->nname =implode($sgbl->__var_nname_impstr, $nnamelist);
		$sql = new Sqlite(null, $this->get__table());
		$res = $sql->getRowsWhere("nname = '{$this->nname}'");
		if ($res) {
			throw new lxException("{$this->nname}_already_exists");
		}
	}
}

function fixbackupMysqlProblem()
{
	if ($this->get__table() === 'mysqldb' || $this->get__table() === 'mysqldbuser') {
		if (csa($this->nname, "___")) {
			$this->nname = $this->dbname;
		}
	}

}


function consistencyAlreadyExisting($res, $trulist, $real) 
{
	global $gbl, $sgbl, $login, $ghtml; 

	$pclname = $res[0]['parent_clname'];
	if ($pclname != $this->parent_clname) {
		list($parentclass, $parentname) = getClassAndName($pclname);
		if ($real) { $this->dbaction = 'already_present_under_different_owner'; }
		if ($login->isAdmin()) {
			$parentstring = "{$parentclass}:{$parentname}";
		} else {
			$parentstring = "Someone Else";
		}

		$this->AddMEssageOnlyIfClientDomain("<font color=red><b> (Already Present under {$parentstring}. Will Not be Restored)</font> </b> ");
		if ($this->isCoreBackup()) {
			log_restore("{$this->get__table()}:{$this->nname} is already present under another user. Won't be restored");
		}

		return false;
	}

	if (isset($res[0]['syncserver']) && ($this->syncserver !== $res[0]['syncserver'])) {
		$string = "Server for {$this->get__table()}:{$this->nname} in the backup is {$this->syncserver} but in the database is {$res[0]['syncserver']}. Using the one in Database\n";
		$this->syncserver = $res[0]['syncserver'];
		$this->createSyncClass();
		print($string);
		log_restore($string);
	}


	$class = $this->get__table();
	$existobj = new $class($this->__masterserver, $this->syncserver, $this->nname);
	$existobj->get();
	$cl = $existobj->getChildListFilter(null);
	foreach((array) $cl as $c) {
		if (cse($c, "_a")) {
			$this->AddToArrayObjectList($c, $existobj->$c);
		}
	}
	if ($real) {
		if ($gbl->__var_list_flag) {
			if ($this->isCoreBackup()) {
				print("{$this->getClName()} under {$this->__parent_o->nname} Already exists.....\n");
			}
		}  else {
			$this->setUpdateSubaction('full_update'); 
			if ($this->isCoreBackup()) {
				print("{$this->getClName()} under {$this->__parent_o->nname} Already exists... Updating.....\n");
			}
		}
	}
	$extra = null;
	if ($trulist) {
		$extra = " Will be Updated";
	}
	$this->AddMEssageOnlyIfClientDomain("<b> (Already Exists.{$extra}). </b>");

	if ($this->extraRestore()) {
		$sgbl->__var_objectrestorelist[] = $this;
	}
	$this->__var_checked = true;

	return true;
}

function consistencySwitchServer()
{
	global $gbl, $sgbl, $login, $ghtml; 

	if (csb($this->get__table(), "sp_") || csb($this->get__table(), "notification")) {
		return;
	}

	if (!$gbl->__var_serverlist) {
		return;
	}

	if (isset($this->syncserver)) {
		if (csa($this->syncserver, ",")) {
			$list = explode(",", $this->syncserver);
			foreach($list as $k) {
				if (array_search_bool($k, array_keys($gbl->__var_serverlist))) {
					print("Changing syncserver for {$this->get__table()}:{$this->nname}  from {$k} to {$gbl->__var_serverlist[$k]}\n");
					$res[] = $gbl->__var_serverlist[$k];
				} else {
					$res[] = $k;
				}
			}
			$res = array_unique($res);
			$this->syncserver = implode(",", $res);
			$this->createSyncClass();
		} else {
			if (array_search_bool($this->syncserver, array_keys($gbl->__var_serverlist))) {

				print("Changing server for {$this->get__table()}:{$this->nname}  from {$this->syncserver} to {$gbl->__var_serverlist[$this->syncserver]}\n");
				$this->syncserver = $gbl->__var_serverlist[$this->syncserver];
				$this->createSyncClass();
			}
		}
	}


	if (isset($this->websyncserver)) {
		if (array_search_bool($this->websyncserver, array_keys($gbl->__var_serverlist))) {
			print("Changing webserver for {$this->get__table()}:{$this->nname}  from {$this->websyncserver} to {$gbl->__var_serverlist[$this->websyncserver]}\n");
			$this->websyncserver = $gbl->__var_serverlist[$this->websyncserver];
		}
	}

	if (isset($this->mmailsyncserver)) {
		if (array_search_bool($this->mmailsyncserver, array_keys($gbl->__var_serverlist))) {
			print("Changing mailserver for {$this->get__table()}:{$this->nname}  from {$this->mmailsyncserver} to {$gbl->__var_serverlist[$this->mmailsyncserver]}\n");
			$this->mmailsyncserver = $gbl->__var_serverlist[$this->mmailsyncserver];
		}
	}

	if (isset($this->mysqldbsyncserver)) {
		if (array_search_bool($this->mysqldbsyncserver, array_keys($gbl->__var_serverlist))) {
			print("Changing mysqlserver for {$this->get__table()}:{$this->nname}  from {$this->mysqldbsyncserver} to {$gbl->__var_serverlist[$this->mysqldbsyncserver]}\n");
			$this->mysqldbsyncserver = $gbl->__var_serverlist[$this->mysqldbsyncserver];
		}
	}

	if (isset($this->dnssyncserver_list)) {
		$res = null;
		$slist = $this->dnssyncserver_list;
		foreach($slist as $k) {
			if (array_search_bool($k, array_keys($gbl->__var_serverlist))) {
				print("Changing dnsserver for {$this->get__table()}:{$this->nname}  from {$k} to {$gbl->__var_serverlist[$k]}\n");
				$res[] = $gbl->__var_serverlist[$k];
			} else {
				$res[] = $k;
			}
		}
		$res = array_unique($res);
		$this->dnssyncserver_list = $res;
	}
}

function consistencyNotExisting($trulist, $real)
{

	global $gbl, $sgbl, $login, $ghtml; 
	$sql = new Sqlite(null, get_table_from_class($this->getParentClass()));
	$res = $sql->getRowsWhere("nname = '{$this->getParentName()}'");

	if ($trulist && $this->__parent_o->dbaction !== 'add' && !$res) {
		$this->AddMEssageOnlyIfClientDomain("<font color=red> <b> (Parent {$this->getParentName()} Does Not Exist. Will be Not be Restored).</font> </b> ");
		return false;
	} else {

		$extra = null;
		if ($trulist) {
			$extra = " Will be Restored";
		}
		$this->AddMEssageOnlyIfClientDomain("<font color=blue> <b> (Does Not Exist.{$extra}).</font> </b> ");
	}

	if ($this->extraRestore()) {
		$sgbl->__var_objectrestorelist[] = $this;
	}

	// This is to ensure that if the syncserver of say mmail gets switched, and if one of the mailaccounts doesn't exist in the db, then the syncserver is properly got from the parent. Most of the objects directly inherit their syncservers from their parnet. But this code needs some more analysis.

	$this->inheritSyncServer($this->__parent_o);

	$this->__var_checked = true;
	$this->dbaction = 'add'; 
	if ($real) {
		if ($gbl->__var_list_flag) {
			if ($this->isCoreBackup()) {
				print("{$this->get__table()}:{$this->nname} under {$this->__parent_o->nname} Doesn't Exist....\n");
			}
		}  else {

			if ($this->isCoreBackup()) {
				print("{$this->get__table()}:{$this->nname} under {$this->__parent_o->nname} Doesn't exist... Restoring.....\n");
			}

			$this->consistencySwitchServer();

			if ($this->syncserver && $this->isSync() && !csa($this->syncserver, ",")) {
				if (!exists_in_db($this->__masterserver, 'pserver', $this->syncserver)) {
					throw new lxException("server_{$this->syncserver}_doesnt_exist", '', $this->get__table());
				}
			}

			$this->dbaction = 'add'; 
		}
	}
	return true;
}


function hasBackupFtp() { return true; }

function fixIndividualParentName()
{
	$plist = array('notification', 'serverweb', 'lxbackup');
	if (csb($this->getClass(), "sp_") || array_search_bool($this->getClass(), $plist)) {
		$v = fix_getParentNameAndClass($this->nname);
		if ($v) {
			list($pcl, $pcn) = $v;
			$this->nname = "$pcl-$pcn";
		}
	}
	$v = fix_getParentNameAndClass($this->parent_clname);
	if ($v) {
		list($pcl, $pcn) = $v;
		$this->parent_clname = "$pcl-$pcn";
	}
}
function fixClientWebhosting()
{
	if (!$this->isClient()) {
		return;
	}
	if (!$this->priv->webhosting_flag) {
		$this->priv->webhosting_flag = 'on';
	}
}

function checkForConsistency($tree, $trulist, $real = false)
{
	global $gbl, $sgbl, $login, $ghtml;

	$this->fixIndividualParentName();
	$this->fixbackupMysqlProblem();

	if ($this->isCoreBackup() && $trulist && ($trulist[0] !== 'all') && !array_search_bool($this->getClName(), $trulist)) {
		$this->AddMEssageOnlyIfClientDomain("Not Selected");
		$this->dbaction == 'clean';
		$coreflag = true;
		$return = true;
	} else {
		$coreflag = false;


		$sql = new Sqlite(null, $this->get__table());
		$res = $sql->getRowsWhere("nname = '{$this->nname}'");
		$this->consistencySwitchServer();
		if ($res) {
			$return = $this->consistencyAlreadyExisting($res, $trulist, $real);
		} else {
			$return = $this->consistencyNotExisting($trulist, $real);
		}
	}

	$ctree = null;
	if ($tree) {
		$ctree = $this->addToTree($tree);
	}

	if (!$return) {
		return;
	}

	if ($this->isClass('web') && (!isset($this->customer_name) || !$this->customer_name)) {
		if ($this->getRealClientParentO()) {
			$this->customer_name = $this->getRealClientParentO()->getPathFromName();
		}
	}

	/// If it is not real, then only go through the display part. Now there is no ther guy, so there's only one loop and saves a lot of time.

	if (!$real) {
		$cl = $this->getDisplayBackupChildList();
	} else {
		$cl =  $this->getBackupChildList();
	}



	foreach((array) $cl as $c) {
		if (cse($c, "_l")) {
			if (isset($this->$c) && $this->$c) {
				foreach($this->$c as $ch) {
					if ($coreflag && !$ch->isCoreBackup()) {
						continue;
					}
					$ch->__parent_o = $this;
					//print("Setting list parent of {$ch->getClName()} $c to {$this->getClName()}\n");
					$ch->fixIndividualParentName();
					if ($ch->parent_clname !== $this->getClName()) {
						print("Inconsistency detected... {$ch->get__table()}:{$ch->nname} under {$this->nname}... Parent {$ch->parent_clname} Hack attempt... exiting\n");
						throw new lxException("inconsistency_in_backup_detected_parent_heirarchy_not_met", '', '');
					}
					$ch->checkForConsistency($ctree, $trulist, $real);
				}
			}
		}
		if (cse($c, "_o")) {
			if (isset($this->$c) && $this->$c) {
				$ch = $this->$c;

				if ($coreflag && !$ch->isCoreBackup()) {
					continue;
				}
				$ch->__parent_o = $this;
				if ($c === 'web_o') {
					//print("Setting object parent of {$ch->getClName()} to {$this->getClName()}\n");
				}
				$ch->fixIndividualParentName();
				if (($ch->parent_clname !== $this->getClName()) && ($ch->nname !== $this->getClName()) && ($ch->nname !== $this->getClName())) {
					print("Inconsistency detected... {$ch->nname} {$this->nname} {$ch->get__table()} Hack attempt...\n");

					throw new lxException("Inconsistency detected... {$ch->nname} {$this->nname} {$ch->get__table()} Hack attempt... exiting\n");
				}
				$ch->checkForConsistency($ctree, $trulist, $real);
			}
		}
	}
}

function addToTree($tree)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$path = get_image_path() . "/button/";


	if ($this->cttype !== $this->get__table()) {
		$type = $this->cttype;
		$vtype = "cttype";
	} else if (isset($this->ttype)) {
		$type = $this->ttype;
		$vtype = "ttype";
	} else {
		$type = $this->get__table();
		$vtype = "cttype";
	}

	$img = $ghtml->get_image($path, $this->get__table(), "{$vtype}_v_{$type}", ".gif");

	if (!lxfile_exists("__path_program_htmlbase/{$img}")) {
		$img = $ghtml->get_image($path, $this->get__table(), "show", ".gif");
	}

	$name = $this->getId() ;
	$imgstr = "<img height=12 width=12 src={$img}> {$name} {$this->__v_message}";

	$showdisabledflag = $gbl->__var_tmp_disabled_flag;
	$disabled = null;
	$checked = null;
	if ($showdisabledflag) {
		$disabled = "disabled";
		if (isset($this->__var_checked) && $this->__var_checked) {
			$checked = "checked";
		} else {
			$checked = null;
		}
	}

	if (!isset($gbl->__tmp_checkbox_value)) {
		$gbl->__tmp_checkbox_value = 0;
	}

	$v = $gbl->__tmp_checkbox_value;
	$gbl->__tmp_checkbox_value++;

	$inputstr = "<input type=checkbox id=treecheckbox{$v} {$checked} {$disabled}  name=treecheckbox{$v} value={$this->getClName()} class=verysmall>";

	$img = null;
	$imgstr = $inputstr . $imgstr;
	$open = 'true';
	$alt = "{$name} is {$this->get__table()}";
	$help = $alt;
	$url = '';

	$pttr = createTreeObject($name, $img, $imgstr, $url, $open, $help, $alt);
	$tree->addToList('tree', $pttr);
	return $pttr;

}


function _compare($a, $b)
{
	$variable = $this->__sortby;

	$success = ($this->__sortdir === "desc")? -1: 1;
	$failure = -1 * $success;

	$sortdir = $this->__sortdir;

	if (!isset($a->$variable)) {
		$av = $a->display($variable);
	} else {
		$av = $a->$variable;
	}

	if (!isset($b->$variable)) {
		$bv = $b->display($variable);
	} else {
		$bv = $b->$variable;
	}

	if (is_numeric($av)) {
		$compa = createZeroString(100 - strlen($av)) . $av;
		$compb = createZeroString(100 - strlen($bv)) . $bv;
	} else {
		$compa = $av;
		$compb = $bv;
	}


	$compa = $a->getSortTop($sortdir) . $compa;
	$compb = $b->getSortTop($sortdir) . $compb;


	if ($compa === $compb)
		return 0;

	return ($compa < $compb)? $failure: $success;
}


static function getGraphType()
{
	return array("small", 35);
}

function getId()
{
	if (csa($this->nname, "___")) {
		return strtilfirst($this->nname, "___");
	}
	if (csa($this->nname, "_s_vv_p_")) {
		return strfrom($this->nname, "_s_vv_p_");
	}
	return $this->nname;
}


function defaultValue($var)
{
	return null;
}

function getVariable($var)
{
	return $this->$var;
}

static function exec_collectQuota()
{
	dprint("Execing Collect Quota");
	lxshell_return("__path_php_path", "../bin/collectquota.php", "--just-db");
}
function getMultiUpload($var)
{
	return $var;
}



function display($var)
{

	if (csb($var, "__v_priv_used_")) {
		$v = strfrom($var, "__v_priv_used_");
		return " {$this->used->$v} / {$this->priv->$v}";
	}

	if (csa($var, "_q_")) {
		$v = strfrom($var, "_q_");
		$c = strtil($var, "_q_");
		return $this->$c->$v;
	}

	if ($var === "status") {
		if (!$this->status) {
			dprint("Status not set for {$this->getClass()}:{$this->nname}");
			return "on";
		}
	}

	/*
	 It is very wrong to play with nname. Instead you shoudl just use some other variable.
	if ($var === "nname") {
		if (csa($this->nname, "_s_vv_p_")) {
			return strfrom($this->nname, "_s_vv_p_");
		} else {
			return $this->nname;
		}
	}
*/

	if ($var === "ddate" || $var === 'date_modified')
		return " " . lxgettime($this->$var) . "";


	if ($var === "validity_time") {
		if (isset($this->validity_time)) {
			return $this->validity_time;
		} else {
			return lxgettime(time());
		}
	}

	if ($var == 'parent_name') {
		return $this->getParentName();
	}

	if ($var === 'parent_name_f') {
		return $this->getParentName();
	}


	if (cse($var, "_f")) {
		return null;
	}

	if (csb($var, "abutton_")) {
		return null;
	}

	return $this->$var;

}


static function getClassId($name)
{
	if (csa($name, "___")) {
		return strtilfirst($name, "___");
	}
	if (csa($name, "_s_vv_p_")) {
		return strfrom($name, "_s_vv_p_");
	}
	return $name;
}

function isOff($var)
{
	return ($this->$var === 'off');
}

function isOn($var)
{
	if (!isset($this->$var)) {
		return false;
	}
	return (strtolower($this->$var) === 'on');
}


function isOnOff($var)
{
	if (!isset($this->$var)) { return false; }
	return ($this->$var === 'off' || $this->$var === 'on');
}


function getResourcePlanList($class, $withoutplan = true)
{

	global $gbl, $sgbl, $login, $ghtml; 
	$list[] = $login->getList($class);
	$list[] = $this->getList($class);
	$clist = lx_array_merge($list);
	//$clist = $this->clearGreaterTemplate($clist);

	$nclist = get_namelist_from_objectlist($clist, 'nname', 'description');

	foreach((array) $nclist as $k => $v) {
		$rn = $k;
		if (csa($k, "___")) {
			$rn = strtilfirst($k, "___");
		}
		$nclist[$k] = "{$rn} ({$v})";
	}

	if ($withoutplan) {
		$withoutplanlist["continue_without_plan"] = $login->getKeywordUc('continue_without_plan');
		$nclist = lx_array_merge(array($nclist, $withoutplanlist));
	}

	return $nclist;
}


function hasSameId($ob)
{
	return $ob->getClName() === $this->getClName();
}

function generateCMList()
{
	$parent = $this->getParentO();
	$plist[] = $parent;
	while (!$parent->isAdmin()) {
		$parent = $parent->getParentO();
		$plist[] = $parent;
	}

	foreach($plist as $p) {
		$string[] = $p->getClName();
	}

	$str = implode(",", $string);
	$this->parent_cmlist = ",{$str},";
}

function getTemplateList($class, $withouttemplate = true)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$list[] = $login->getList($class);
	$list[] = $this->getList($class);
	$clist = lx_array_merge($list);
	//$clist = $this->clearGreaterTemplate($clist);

	$nclist = get_namelist_from_objectlist($clist, 'nname', 'realname');

	foreach($nclist as $k => $v) {
		$nclist[$k] = "{$v} ({$clist[$k]->description})";
	}

	if ($withouttemplate) {
		$withouttemp["continue_without_plan"] = $login->getKeywordUc('continue_without_plan');
		$nclist = lx_array_merge(array($nclist, $withouttemp));
	}

	return $nclist;
}


final protected function writeAChildObject($class, $flag = NULL)
{
	$object = $class . "_o";
	$obj = $this->$object;
	// Sometimes the single object may not exist, at all as in uuser object in a forward domain.
	if (!$obj) {
		return;
	}
	$obj->was();
	if ($obj->dbaction === "delete_done") {
		$this->$object = NULL;
	}
	$desc = get_classvar_description(get_class($this), $object);

	if ($desc) {
		if (strpos($desc[0], "v") !== false) {
			dprint("{$object} in {$this->getClass()} is virtual... Removing <br> ", 2);
			$this->$object = NULL;
			return;
		}
	}
	if (!$flag) {
		$this->__object_list = array_remove($this->__object_list, $class);
	}
}

function makeVirtual($class)
{
	$this->__virtual_list = array_push_unique($this->__virtual_list, $class);
}

final function isVirtual($class) 
{
	$list = "{$class}_l";
	$desc = get_classvar_description(get_class($this), $list);
	if ($desc && csa($desc[0], "v")) {
		return true;
	}

	if (array_search_bool($class, $this->__virtual_list)) {
		return true;
	}

	return false;
}

final protected function writeAChildList($class, $flag = NULL)
{

	global $gbl, $sgbl, $login, $ghtml; 


	$list = "{$class}_l";

	$desc = "__desc_{$list}";
	$pclass = $this->get__table();
	$desc = get_real_class_variable($pclass, $desc);
	if (csa($desc[0], "r")) {
		dprint("Readonly {$class} in {$this->get__table()} {$this->nname} <br> ");
		return;
	}

	dprintr("Warning... Writing {$class} in {$this->get__table()} {$this->nname}...<br> ");
	if (!$this->$list) {
		return;
	}

	//dprint(" element {$class} \n");

	foreach((array) $this->$list as $element) {
		if (!$element)
			continue;
		//dprint("Inside: {$element->getClName()} {$element->dbaction} <br> ");
		if (! isset($element->__parent_o) || !$element->__parent_o) {
			$element->__parent_o = $this;
		}
		$element->was();
		if ($element->dbaction === "delete_done") {
			unset($this->{$list}[$element->nname]);
		}
	}

	// Virtual stuff is 'wased' anyway. This is not a problem since, the object will be written to Db only once.

	if ($this->isVirtual($class)) {
		$this->__virtual_list = array_remove($this->__virtual_list, $class);
		// Try adding this whole list to the $login... 
		dprint("{$list} in {$this->getClass()}:{$this->nname} is virtual; present in Virtual List... Removing <br> ", 2);
		$this->$list = NULL;
		return;
	}



	if (get_real_class_variable($class, "__ttype") === "transient") {
		$this->$list = NULL;
		unset($this->$list);
	}

	if (!$flag) {
		$this->__list_list = array_remove($this->__list_list, $class);
	}
}

/** 
* @return void 
* @param 
* @param 
* @desc  Removes all the children and teh __parent objects from an object. This is done before sending the object across to the other machine. An object is supposed to be self consistent and should work without the help of __parent_o or the children.
*/ 
 

static function clearChildrenAndParent($object)
{

	$object->__parent_o = null;

	foreach((array) $object->__object_list as $v) {
		$obj = $v . "_o";
		$object->$obj = null;
		//unset($object->$obj);
	}
	if (isset($obj->sp_specialplay_o)) {
		$obj->sp_specialplay_o == null;
	}
	if (isset($obj->sp_childSpecialPlay)) {
		$obj->sp_childSpecialPlay_o = null;
	}

	$object->__object_list = null;

	
	//dprint("<b> Clearing ... </b>  {$object->getClName()} {$object}<br> ");
	foreach((array) $object->__list_list as $v) {
		$list = $v . "_l";
		$object->$list = null;
		//unset($object->$list);
	}



	// Because of the cloning, the main is now pointing to the old this object. That means there will be unnecssary redundancy, but more importantly, this will result in catastrophe as there are two copies of the same object. So we set the driverapp->main back to the new $object.
	if (isset($object->driverApp)) {
		$driverApp = clone $object->driverApp;
		$object->driverApp = $driverApp;
		$object->driverApp->main = $object;
	}

	$object->__list_list = null;


}

function __clone()
{
	foreach((array) $this->__object_list as $v) {
		$obj = $v . "_o";
		if ($this->$obj) {
			$this->$obj = clone($this->$obj);
		} 
	}


}

final protected function writeAndSyncChildren()
{
	/*
	dprint("In Write Children: ", 2);
	dprint_r($this->__object_list, 2);
	dprint_r($this->__list_list, 2);
	dprint("Class: " . get_class($this) . " Object: " . $this->nname . " Dbaction:" . $this->dbaction, 2);
	dprint("<br> ", 2);
	flush();
	*/


	foreach((array) $this->__list_list as $variable) {
		$this->writeAChildList($variable, 1);
	}
	$this->__list_list = NULL;

}



final function setFromObject($obj)
{
	foreach($obj as $k => $v) {
		if ($k === '__table') {
			continue;
		}

		if ($k !== 'nname') {
			$this->$k = $v;
		}
	}
}

final function setFromArray($array)
{

	foreach($array as $key => $value) {
		if (is_numeric($key)) {
			//dprint("The Key is {$key} integer in .  {$this->get__table()}:{$this->nname} <br> ");
		}
		if ($key === '__table') {
			continue;
		}


		if ($key === 'ser_listpriv') {
			$key = strfrom($key, "ser_");
			$vv = @ unserialize(base64_decode($value));
			
			if (!$vv) {
				dprint("{$this->getClName()} $key");
				dprint(substr($value, 49146, 10));
				dprint(substr($value, 0, 10));
				dprint(" ");
				$this->setUpdateSubaction();
			}
			if (!is_object($vv)) {
				$this->$key = new $key(null, null, $this->nname);
			} else {
				$this->$key = $vv;
			}
			$this->{$key}->__parent_o = $this;
			continue;
		}
		if ($key === 'priv') {
			print("<b> Setting Priv If it is Ser. in {$this->nname} {$this->get__table()}<br> </b>");
			if (!is_object($value)) {
				$this->$key = new $key(null, null, $this->nname);
			} else {
				$this->$key = $value;
			}
			$this->{$key}->__parent_o = $this;
			continue;
		}

		if (csb($key, "ser_")) {
			$key = strfrom($key, "ser_");
			$value = unserialize(base64_decode($value));
			if ($value === false) {
				dprint("Unserialize failed: {$this->get__table()}: {$key}<br>\n", 3);
				if (cse($key, "_b") && !is_object($value)) {
					$value = new $key(null, null, $this->nname);
					$this->$key = $value;
					continue;
				}
			} else {

				if (cse($key, "_b") && !is_object($value)) {
					dprint("Unserialize failed: {$this->get__table()}: {$key}<br>\n", 3);
					$value = new $key(null, null, $this->nname);
					$this->$key = $value;
					continue;
				}
			}

			if (cse($key, "_a")) {
				if ($value) foreach($value as $k => $v) {
					$value[$k]->__parent_o = $this;
				}
			}

			$this->$key = $value;
			continue;
		}


		if (csb($key, "coma_")) {
			$key = strfrom($key, "coma_");
			if (!trim($value)) {
				$this->$key = null;
				continue;
			}
			$value = trim($value, ",");
			if (!$value) {
				$this->$key = null;
				continue;
			}

			$value = explode(",", $value);
			$list = null;
			foreach($value as $vv) {
				$vv = trim($vv);
				if (cse($key, "_list")) {
					$ob = $vv;
				} else {
					$ob = new $key(null, null, $vv);
				}
				$list[$vv] = $ob;
			}
			$this->$key = $list;
			continue;
		}
		/// Hack for listpriv. It needs the parent, so that it can find the resource details for admin.


		if (csb($key, "priv_q_") || csb($key, "used_q")) {
			$qvar = strtil($key, "_q_");
			if (!isset($this->$qvar)) {
				//dprint("Setting Priv in $this->nname {$this->get__table()}");
				$this->$qvar = new $qvar(null, null, $this->nname);
				$this->$qvar->__parent_o = $this;
			}
			$qv = strfrom($key, "{$qvar}_q_");
			$this->$qvar->$qv = $value;
			continue;
		}

		if ($key != "nname" && !is_numeric($key)) {
			$this->$key = $value;
		}
	}

	//dprintr($this);

	if ($this->hasDriverClass()) {
		$this->createSyncClass();
	}

	//print_time('do', 'do');
	if (!isset($this->dbaction)) {
		$this->dbaction = "clean";
	}

}

function hasDriverClass()
{
	return true;
}

final public function create($arr)
{
	$this->setFromArray($arr);
	$this->dbaction = "add";
}

final function isArraySame($v)
{

	if ($this->nname === $v['nname']) {
		return true;
	}

	return false;

}

final function isDeleted()
{
	if ($this->dbaction === "delete" || $this->dbaction === "delete_done" || $this->dbaction === 'syncdelete') {
		return true;
	}
	return false;
}

static function initThisListRule($parent, $class)
{
	return null;
}

static function initThisList($parent, $class)
{
	return null;
}

final public function modify($array, $subaction = null)
{

	$this->setFromArray($array);
	$this->setUpdateSubaction($subaction);
}



function updateShow($subaction, $param)
{
	return null;
}


function changeOwnerSpecific() { }
function updateChangeOwner($param)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$rparent['up'] = $this->getParentO()->getParentO();
	if ($rparent['up']) {
		$rparent['top'] = $rparent['up']->getParentO();
	}
	$rparent['down'] = $this->getParentO();
	if ($rparent['down']->isLogin()) {
		$rparent['up'] = null;
	}

	$newparent = getFromAny($rparent, 'client', $param['parent_name_change']);

	/*
	if (!$this->checkIfEnoughParentQuotaAll($newparent)) {
		throw new lxexception('not_enough_quota_in_parent', 'quota');
	}
*/
	// Get the objectlist BEFORE you change the parent.

	$this->__old_parent_name = $this->parent_clname;
	$this->parent_clname = createParentName("client", $param['parent_name_change']);
	$this->__parent_o = $newparent;
	$this->setUpdateSubaction();

	$this->changeOwnerSpecific();


	$gbl->__this_redirect = $ghtml->getFullUrl('a=resource', null);
	$gbl->__this_function = array("lxclass", "exec_collectQuota");
	$gbl->__this_functionargs = null;
	return null;

}

function updateform($subaction, $param)
{

	switch($subaction) {

		case "changeowner":
				//hacks... islogin should be changed to isabovelogin...
			$rparent['down'] = $this->getParentO();
			$rparent['up'] = null;
			if ($rparent['down']->isGt('admin')) {
				$rparent['up'] = $rparent['down']->getParentO();
			}
			$list['up'] = null;
			$list['top'] = null;
			if ($rparent['down']->isLogin()) {
				$rparent['up'] = null;
			}
			if ($rparent['up'] && !$rparent['down']->isLogin())  {
				dprintr($rparent['down']->nname);
				$list['up'] = $rparent['up']->getList('client');
				$rparent['top'] = $rparent['up']->getParentO();
				if ($rparent['top'] && !$rparent['up']->isLogin()) {
					$list['top'] = $rparent['top']->getList('client');
				}
			}


			if ($rparent['down']->isLte('reseller')) {
				$list['down'] = $rparent['down']->getList('client');
				foreach(array('up', 'top', 'down') as $v) {
					foreach((array) $list[$v] as $kk => $vv) {
						if ($this->isLte($vv->cttype)) {
							unset($list[$v][$kk]);
						}
					}
				}
			}
			$nlist['up'] = get_namelist_from_objectlist($list['up']);
			$nlist['top'] = get_namelist_from_objectlist($list['top']);
			$nlist['down'] = get_namelist_from_objectlist($list['down']);


			foreach($rparent as $k => $v) {
				if ($rparent[$k]) {
					if ($this->isGt($v->cttype)) {
						$nlist[$k][] = $rparent[$k]->nname;
					}
				}
			}
			//dprintr($nlist['down']);
			$nnlist = lx_array_merge($nlist);
			$nnlist = array_unique($nnlist);
			$this->parent_name_change = $this->getParentName();
			$vlist['parent_name_change'] = array('s', $nnlist);


			return $vlist;


		case "changenname":
			$vlist['nname'] = null;
			return $vlist;
	}

	dprintr("updateform in lxclass called. {$subaction} mostly by security_check\n <br> ");
	//debugBacktrace();

}


function getLoginTo()
{
	global $gbl, $sgbl, $login, $ghtml; 

	if ($this->isLte('reseller')) {
		$llist[] = "list-client";
	}

	if ($sgbl->isKloxo() && $this->isLte('customer')) {
		$llist[] = "list-domain";
	}
	if ($sgbl->ishyperVM() && $this->isLte('customer')) {
		$llist[] = "list-vps";
	}
	$llist[] = "desktop-";
	$llist[] = "show-home";
	return $llist;
}

function getUrlFromLoginTo()
{
	global $gbl, $sgbl, $login, $ghtml; 

	if (isset($login->getSpecialObject('sp_specialplay')->login_page)) {
		$string = $login->getSpecialObject('sp_specialplay')->login_page;
	} else {
		$string = null;
	}

	// Hack to change the old domaina forcibly to domain.
	if ($string === 'list-domaina') { $string = 'list-domain'; }

	if ($string === "list-domain") {
		if ($this->isCustomer()) {
			$string = "show-home";
		} else {
			$string = "show-home";
		}
	}

	if (!csa($string, "-")) {
		if ($this->isClass('client')) {
			if ($sgbl->isHyperVm()) {
				$url = $this->getSingleOrListclass("vps");
			} else if ($sgbl->isKloxo()) {
				if ($this->isEq('reseller')) {
					$url = $this->getSingleOrListclass('client');
				} else {
					$url = "a=show";
				}
			} else {
				$url = "a=show";
			}
		} else {
			$url = "a=show";
		}
		return $url;
	}

	$list = explode("-", $string);
	$classstring = null;
	if ($list[1] && $list[1] !== 'home') {
		$classstring = "&c={$list[1]}";
	}
	$url = "a={$list[0]}{$classstring}";

	if (!$login->isClient() && $list[1] === $login->getClass()) {
		return "a=show";
	}

	if ($list[1] === 'domain' || $list[1] === 'vps') {
		$url = $this->getSingleOrListclass($list[1]);
	}


	return $url;
}

function getSingleOrListclass($class)
{
	$table = get_table_from_class($class);
	$sq = new Sqlite(null, $table);
	$count = $sq->getCountWhere("parent_clname = '{$this->getClName()}'");
	if ($count == 1 && $this->isGte('customer')) {
		$dlist = $this->getList($class);
		$d = getFirstFromList($dlist);
		$url = "a=show&l[class]={$class}&l[nname]={$d->nname}";
	} else {
		$url = "a=list&c={$class}";
	}
	return $url;
}


function updateAccountSel($param, $subaction)
{
	$flist = $param['_accountselect'];
	foreach($flist as $ff){
		$fpathlist[] =  $ff;
	}
	$list = "{$subaction}_list";
	$this->$list = $fpathlist;
	$this->setUpdateSubaction($subaction);

}

function postUpdate() { }
function superPostAdd() { }
function postAdd() {  }

// The quota left is actually priv - used. This should work for both hard and soft quotas.

function getEffectivePriv($k, $class)
{
	if (is_unlimited($this->priv->$k)) {
		return 'Unlimited';
	} else {
		if (isHardQuotaVariableInClass($class, $k)) {
			return $this->priv->$k - $this->used->$k;
		} else {
			return $this->priv->$k;
		}
	}
}

function getSpecialParentClass()
{
	return 'client';
}
function createShowIlist() { return null; }
static function AddListForm() { return null; }

function createShowPropertyList(&$alist) { $alist['property'][] = 'a=show';}
function createShowActionList(&$alist) { }

static function add($parent, $class, $param)
{
	return $param;
}

static function continueForm($parent, $class, $param, $continueaction)
{

}

static function addform($parent, $class, $typetd = null)
{
	$vlist['nname'] = '';
	$ret['variable'] = $vlist;
	$ret['action'] = 'add';
	return $ret;

}


static function createListBlist($object, $class)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$blist[] = array("a=delete&c={$class}");

	return $blist;
}

static function createListSlist($parent)
{
	return null;
}

static function createselectListNlist($parent)
{
	$nlist['nname'] = '100%';
	return $nlist;
}
static function createListNlist($parent, $view)
{
	$nlist["nname"] = "100%";
	return $nlist;
}
function fillWelcomeMessage($txt){return $txt;}
function showRawPrint($subaction = null) { }
function createShowPlist($subaction) { return null; }
function createShowRlist($subaction) { return null; }
function createShowInfoList($subaction) { return null; }
static function getSelectList($parent, $var) { return null;}
function createShowAlist(&$alist, $subaction = null) { return null; }
function createShowNote() { return false; }
function createShowAlistConfig(&$alist, $subaction = null) { return null; }
function createShowShowlist() { return null;}
function createShowClist($subaction) { return null; }

static function createParentShowList($parent, $class) { } 
static function createListAddForm($parent, $class) { return null; } 
static function createAddformlist($parent, $class) { return false; } 
static function createListUpdateForm($parent, $class) { return null; } 


function isChildList($class)
{
	$listvar = $class . "_l";
	$list = $this->getVarDescrList(0);

	if (isset($list[$listvar])) {
		return true;
	}
	return false;
}



function getCustomButton(&$alist)
{
	$t = $this->get__table();
	$sq = new Sqlite(null, 'custombutton');
	$res = $sq->getRowsWhere("class = '{$this->get__table()}'");
	if (!$res) { return; }

	$alist['__title_custom'] = "Custom";

	foreach($res as $r) {
		$v = $r['url'];
		$v = str_replace("%nname%", $this->nname, $v);
		$v = str_replace("%realpass%", $this->realpass, $v);
		if (isset($this->default_domain)) {
			$v = str_replace("%default_domain%", $this->default_domain, $v);
		}
		$alist[] = create_simpleObject(array('custom' => true, 'target' => 'target=_blank', 'purl' => $r['url'], 'name' => $r['description'], 'bname' => $r['nname'], 'url' => $v));
	}

	return $alist;

}

function isExceptionForSelflist()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$exc_list = array("lxbackup", "web",  "mmail", "sp_specialplay", "centralbackupconfig");
	foreach($exc_list as $l) {
		if ($this->is__table($l)) {
			return true;
		}
	}
	if ($this->is__table("dns") && $sgbl->isKloxo()) {
		return true;
	}

	return false;
}

function canGetSelfList() { return true; }

function getSelfList()
{
	global $gbl, $sgbl, $login, $ghtml; 
	if ($this->isLogin()) {
		return null;
	}

	if (!$this->canGetSelfList()) {
		return null;
	}

	if ($login->getSpecialObject('sp_specialplay')->isOff('show_brethren_list')) {
		return null;;
	}

	// the variable below is created inside the navigation creation system. We have the whole parent list, so no need to find it using the reverse walk from the child.
	if ( !isset($gbl->__self_list_parent)) {
		return;
	}
	$parent = $gbl->__self_list_parent;
	$class = $gbl->__self_list_class;


	/// The below method is wrong. It goes back from the child to find the parent, and get the list of its siblings. The correct way is to get the parent from the url. The reason is that there are virtual objects, which have a different parent than the one who is holding it now.
	/*
	$object = $this;
	while (1) {
		$class = $object->get__table();
		$listvar = $class . "_l";
		$parent =  $object->getParentO();


		if (!$parent) {
			$parent = $object;
			return null;
		}

		$list = $parent->getVarDescrList(0);

		if (isset($list[$listvar])) {
			break;
		}
		if ($parent->isLogin()) {
			return null;
		}
		$object = $parent;
	}
*/

	if (!$parent) {
		return null;
	}

	 // For customer, it is best you get the full list. It is going to be small.
	if ($parent->isCustomer()) {
		$list = $parent->getList($class);
		$count = count($list);
	} else {
		$list = $parent->getVirtualList($class, $count);
	}

	$pp = $this;

	while($pp !== $parent) {
		$child = $pp;
		$pp = $pp->getParentO();
		if (!$pp) {
			break;
		}
	}

	$ret = null;
	foreach($list as $k => $ob) {
		// Big big hack... this is to prevent the installapp titles from cropping up here. NEed a better system though.
		if (csb($k, "__title_")) {
			continue;
		}
		if ($ghtml->frm_action !== 'show') {
			if (isset($child->ttype) && $child->ttype !== $ob->ttype) {
				continue;
			}
			if (isset($child->cttype) && $child->cttype !== $ob->cttype) {
				continue;
			}
		}

		if ($this->isExceptionForSelflist()) {
			$nob = $ob->getObject($this->getClass());
		} else if ($this->is__table('phpini')) {
			if ($ob->isClass('domain')) {
				$nob = $ob->getObject('web')->getObject($this->getClass());
			} else {
				$nob = $ob->getObject($this->getClass());
			}
		} else if ($this->is__table('spam')) {
			$nob = $ob->getObject('mmail')->getObject($this->getClass());
		} else {
			$nob = $ob;
		}

		$ret[$k] = $nob;
	}
	return $ret;
}

static function createAddformAlist($parent, $class, $typetd = null)
{
	return exec_class_method($class, "createListAlist", $parent, $class);
}

static function createListAlist($parent, $class)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$alist[] = "a=list&c={$class}";
	$alist[] = "a=addform&c={$class}";
	return $alist;
}

function createShowMainImageList()
{
	return null;
}

function createShowImageList()
{
	$vlist = null;
	if (!$this->isLogin())
		$vlist['cpstatus'] = 1;

	//$vlist['status'] = 1;
	//$vlist['state'] = 1;
	return $vlist;

}

function isAction($var)
{
	return true;
}

function get__table()
{
	$table =  get_class_variable(lget_class($this), "__table");
	if (!$table) {
		return lget_class($this);
	}
	return $table;
}


function getStatic($var)
{
	$v =  get_class_variable(lget_class($this), $var);
	return $v;
}

function is__table($class)
{
	return ($this->get__table() === $class);
}

function isClass($class)
{
	return (lget_class($this) === $class);
}
function getClass()
{
	return lget_class($this);

}



function update($subaction, $param)
{
	return $param;
}

function execInChildren($key, $func, $arg = null)
{
	global $gbl, $sgbl, $login, $ghtml; 

    $class = lget_class($this);



	$r = new ReflectionClass($class);

	$childo = null;
	$childl = null;
	foreach($r->getProperties() as $s) {
		if (cse($s->name, "_o")) {
			$desc = get_classvar_description($class, $s->name);
			if (csa($desc[0], $key)) {
				$childo[] = substr($s->name, 7, (strpos($s->name, "_o") - 7));
			}
		} else if (cse($s->name, "_l")) {
			$desc = get_classvar_description($class, $s->name);
			if (csa($desc[0], $key)) {
				$string = strfrom($s->name, "__desc_");
				$childl[] = strtil($string, "_l");
			}
		}
	}

	$gbl->__fvar_dont_redirect = ($childo || $childl);

	dprint("Execing {$key} {$func} <br> ");
	dprintr($childo);
	foreach((array) $childo as $co) {
		$ob = null;
		if ($this->isRealChild($co)) {
			$ob = $this->getObject($co);
		}
		if ($ob) {
			$ob->$func($arg);
			dprint("{$func} on {$co} ob dbaction {$ob->dbaction} in {$class}:{$this->get__table()}:{$this->nname} <br> ");
		}
	}

	dprintr($childl);
	foreach((array) $childl as $cl) {
		$obl = $this->getList($cl);
		foreach((array) $obl as $ob) {
			dprint("{$func} on {$cl}:{$ob->nname} in {$class }:{$this->nname}...");
			$ob->$func($arg);
		}
		dprint("<br> ");
	}

}

function checkIfEnoughParentQuotaAll($parent)
{
	$qp = $parent;
	while(1) {
		dprint(" Parent... " . $qp->nname . '<br> ');
		$this->checkIfEnoughParentQuota($qp);
		$qp = $qp->getParentO();
		if (!$qp) {
			break;
		}
	}
	return true;
}

function checkIfEnoughParentQuota($parent)
{

	$class = $this->get__table();

	$numvar = $class . "_num";

	if (isQuotaGreaterThan($parent->used->$numvar + 1, $parent->priv->$numvar)) {
		throw new lxexception('not_enough_quota_in_parent', $k);
	}

	$qlist = $this->getQuotaVariableList();
	foreach($qlist as $k => $q) {
		if (isQuotaGreaterThan($this->priv->$k, $parent->priv->$k)) {
			throw new lxexception('not_enough_quota_in_parent', $k);
		}
		if (isQuotaGreaterThan($parent->used->$k + $this->used->$k, $parent->priv->$k)) {
			throw new lxexception('not_enough_quota_in_parent', $k);
		}
	}
	return true;

}

function getSwitchServerUrl(&$alist)
{
	global $gbl, $sgbl, $login, $ghtml; 

	return;
	if ($login->isAdmin()) {
		if (check_if_many_server()) {
			$alist[] = "n={$this->getClass()}&a=updateform&sa=switchserver";
		}
	}

}

static function fixListVariable($v)
{
	if (!is_array($v)) {
		$v = trim($v);
		if ($v) {
			return explode(',', $v);
		} else {
			return null;
		}
	} else {
		return $v;
	}
}


static function consumeUnderParent() { return false; }

static function fixpserver_list(&$param)
{
	foreach($param as $k => $v) {
		if (!csb($k, "listpriv_s_")) {
			continue;
		}
		if (cse($k, "_list")) {
			$param[$k] = self::fixListVariable($v);
		}
	}
}

function changeUsedFromParentAll($flag = 1)
{

	global $gbl, $sgbl, $login, $ghtml; 
	$qp = $this;
	if (!$qp->getParentO()) {
		return;
	}
	//$qv = $this->getQuotaVariableList();
	//if (!$qv) {
		//return;
	//}
	//dprint(" <b> Before Mailaccount Num: {$login->used->mailaccount_num} </b> <br> \n");
	while(($qp = $qp->getParentO())) {
		dprint(" Changin {$this->get__table()}: {$this->nname}... {$qp->get__table()} {$qp->nname} </b><br> ");
		$this->changeUsedFromParent($qp, $flag);
	}
	//dprint(" <b> After  Mailaccount Num: {$login->used->mailaccount_num} </b> <br> \n");
}

function getResourceIdentity() { return $this->getClass() ; }
function changeUsedFromParent($qp, $flag)
{


	$class = $this->getResourceIdentity();
	$cnum = "{$class}_num";
	$pclass = lget_class($qp);
	$doupdate = false;


	if (!isset($qp->used)) {
		return;
	}

	$val = 1 * $flag;
	if ($qp->isQuotaVariable($cnum)) {
		$qp->used->$cnum += $val;
		$doupdate = true;
		dprint("<b> IN change used ... quota variable specific {$qp->getClname()} {$class} {$this->nname} <br> </b><br>\n ");
		//dprintr($qp->used);
	}


	/*
	$list = $qp->getQuotaVariableList();

	foreach($list as $l => $v) {
		if (csb($l, "{$class}_m_")) {
			$license = strtil(strfrom($l, "_n_"), "_num");
			$licvar = strtil(strfrom($l, "_m_"), "_n_");
			if ($this->$licvar === $license) {
				$qp->used->$l += $val;
				$doupdate = true;
			}
		}
	}
*/


	// This is not needed. When you add or delete something, just remove its number from the parent. Reducing the usages seem to cause lots of problem.
	/*
	$qv = $this->getQuotaVariableList();
	foreach((array) $qv as $k => $v) {
		if (cse($k, "_time") || cse($k, "_flag") || cse($k, "_num")) {
			continue;
		}
		if (isset($this->used)) {
			$rv = $flag * $this->used->$k;
			$qp->used->$k += $rv;
			$doupdate = true;
		}
	}
*/

	if ($doupdate) {
		dprint("<b> Warning Change Used From Parent... {$qp->getClname()} {$class} {$this->nname} <br> </b><br>\n ");
		$qp->setUpdateSubaction();
	}
	// Improtant. This function is called in the 'was' of a child. And thus the was of this object is already over. So we need to do a write ourselves. Be careful, WHen adding sometimes a child is 'wased' before the parent, leading to a lot unexpected quandaries. Only if the parent is in updatemode should it be written to. Else it means it was when adding, and then it should be left to the normal 'was';
	if ($qp->dbaction === 'update') {
		$qp->write();
	}
}

// Any Action to be done before deleting. For instance, in dirprotect, the parent (web) is updated when it is added/deleted.

function deleteSpecific()
{
	return true;
}

function convertClCmToNameCm($cmlist)
{
	$v = explode(",", $cmlist);
	$nv = null;
	foreach($v as $__q) {
		if ($__q) {
			//list($pclass, $pname) = explode("_s_vv_p_", $__q);
			list($pclass, $pname) = getParentNameAndClass($__q);
			$nv[] = $pname;
		}
	}
	return implode(", ", $nv);
}


function delete()
{

	global $gbl, $sgbl, $login, $ghtml; 
	// Don't delete unless the parent is the real owner. Or the parent is admin.
	if (!$this->getParentO()->isAdmin() && !$this->isRightParent()) {
		return ;
	}

	do_actionlog($login, $this, "delete", "");

	$qlist = $this->getQuotaVariableList();

	foreach((array) $qlist as $k => $v) {
		if ($this->isHardQuota($k)) {
			$this->getParentO()->used->$k -= $this->priv->$k;
		}
	}

	if ($this->get__table() === 'ticket') {

		dprint(" <b> Ticket <br> <br> ");
		dprint($this->parent_clname . "<br> ");
		dprint($this->getParentO()->nname);
	}

	if (!$this->isDeleted()) {
		$this->dbaction = "delete";
	}

	$this->deleteSpecific();
	$this->execInChildren("d", "delete");

}


function updateLimit($param)
{
	if_demo_throw_exception('limit');
	log_log("ajax", var_export($param, true));
	global $gbl, $sgbl, $login, $ghtml; 
	$gbl->__ajax_refresh = true;
	if ($this->isLogin()) {
		throw new lxException('cannot_change_own_limit', 'limit');
	}


	$mstr = '(--Mod--)';
	if (!csa($this->resourceplan_used, $mstr)) {
		$this->resourceplan_used = "$mstr {$this->resourceplan_used}";
	}

	return $param;
}


function updateDisable($param, $reason = null)
{
	global $gbl, $sgbl, $login, $ghtml; 

	if ($this->isAdmin()) {
		return;
	}

	if_demo_throw_exception();

	if (!$reason) {
		$reason = "__type:{$login->nname}:{$login->cttype}";
	}
	if (isset($this->status) && $this->status === 'on') {
		$this->disable_reason = $reason;
		$this->setStatus('off');
	}
	return null;
}

function updateEnable($param, $reason = null)
{
	global $gbl, $sgbl, $login, $ghtml; 
	// property access.... 
	if (!isset($this->disable_reason)) {
		$this->disable_reason = null;
	}
	$t = $this->disable_reason;

	if ($t && csb($t, "__type")) {
		$list = explode(":", $t);
		$cttype = $list[2];
		if ($login->isGt($cttype)) {
			throw new lxexception('locked_by_parent', '');
		}
	}


	if (isset($this->status) && $this->status === 'off') {
		if ($reason ) {
			if ($reason === $this->disable_reason) {
				$this->disable_reason = null;
				$this->setStatus('on');
			}
		} else {
			$this->disable_reason = null;
			$this->setStatus('on');
		}
	}
	return null;
}

function updateToggle_Status($param)
{
	$function = ($this->status === "on")? "Disable" : "Enable" ;
	$function = "update$function";
	$this->$function($param);
	return null;
}

final function setStatus($status)
{
	$old_status = $this->status;

	$this->status = $status;

	if (isset($this->cpstatus)) {
		$this->cpstatus = $this->status;
	}
	$this->execInChildren('t', "setStatus", $this->status);

	if ($old_status != $this->status) {
		$this->setUpdateSubaction("toggle_status");
	}
}


function collectQuota()
{

	global $gbl, $sgbl, $login, $ghtml; 
	$vlist = $this->getQuotaVariableList();
	$vlist = lx_array_merge(array($vlist, $this->getDeadQuotaVariableList()));
	foreach ($vlist as $k => $v) {
		if (!cse($k, "_flag")) {
			$this->used->$k = 0;
		}
	}

	$res = $this->collectVariableQuota($vlist);

	/*
	foreach($res as $k => $v) {
		if (!cse($k, "_flag")) {
			$this->used->$k = $res[$k];
		}
	}
*/
	//dprintr($this->used . "\n");

	$this->setUpdateSubaction('collectquotaupdate');

	$name = ucfirst($sgbl->__var_program_name);
	foreach($gbl->__tmp_var_email_list as $k => $v) {
		dprint("Sending {$v} to {$k}\n");
		lx_mail(null, $k, "{$name} Message", $v);
	}
}

function getCountLType($list, $licvar, $lic)
{
	$count = 0;
	foreach((array) $list as $l) {
		if (isset($l->$licvar) && $l->$licvar === $lic) {
			$count++;
		}
	}
	return $count;
}

function countRightParent($childlist)
{
	if (!$childlist) {
		return 0;
	}
	$count = 0;
	foreach($childlist as $c) {
		if ($c->parent_clname === $this->getClName()) {
			$count++;
		}
	}
	return $count;
}

function getQuotaNeedVar()
{
	return array('nname' => $this->nname) ; 
}

function collectVariableQuota($vlist)
{

	static $count = 0;
	global $gbl, $sgbl, $login, $ghtml; 

	$count++;


	foreach($vlist as $var => $val) {
		$fv[$var] = 0;
		$func = "getQuota{$var}";
		$tvv = "__var_{$var}";

		if ($this->isHardQuota($var)) {
			$fv[$var] = $this->priv->$var;
		} else {
			if ($this->isRealQuotaVariable($var)) {
				if (isset($sgbl->$tvv)) {
					dprint("{$tvv}\n");
					$qq = $sgbl->$tvv;
					$fv[$var] = $qq[$this->getClName()];
				} else {
					$fv[$var] = $this->used->$var;
				}
			}
		}
	}

	$cnlist = $this->getQuotaChildList();


	$totalchildlist = $this->getChildList();


	//dprintr($cnlist);

	foreach($vlist as $var => $val) {
		if (!$this->isQuotaVariable($var)) {
			continue;
		}
		if (cse($var, "_a_num")) {
			$cvar = strtil($var, "_num");
			$listvar = $cvar;
			if (array_search_bool($listvar, $totalchildlist)) {
				$childlist = $this->$cvar;
				$num = count($childlist);
				$fv[$var] = $num;
			}
		} else if (cse($var, "_num")) {
			$cvar = strtil($var, "_num");
			$listvar = $cvar . "_l";
			//dprint(" before {$this->nname} {$var} \n");
			if (array_search_bool($listvar, $totalchildlist)) {
				$childlist = $this->getList($cvar);
				if (!$this->isVirtual($cvar)) {
					$num = $this->countRightParent($childlist);
					//dprint(" {$this->nname}  {$var}  {$num} \n");
					$fv[$var] = $num;
				}
			}
		}
	}


	foreach((array) $cnlist as  $c) {
		if (cse($c, "_o")) {
			$name = $this->getChildNameFromDes($c);
			$ob = null;
			if ($this->isRealChild($name)) {
				$ob = $this->getObject($name);
			}
			if ($ob) {
				$res = $ob->collectVariableQuota($vlist);
				foreach($res as $var => $v) {
					$fv[$var] += $v;
				}
			}
		}  else if (cse($c, "_l")) {
			$name = $this->getChildNameFromDes($c);
			$list = $this->getList($name);
			if (!$this->isVirtual($name)) {
				foreach((array) $list as $l) {
					$res = $l->collectVariableQuota($vlist);
					foreach($res as $var => $v) {
						if (cse($var, "_flag")) {
							continue;
						}
						//dprint("Collected  {$this->nname}   {$var}  {$v} \n");
						$fv[$var] += $v;
					}
				}
			}
		}
	}

	foreach($vlist as $var => $val) {
		$list = $this->getQuotaAddList($var);
		dprintr($list);
		if (!$list) {
			continue;
		}
		$fv[$var] = 0;
		foreach($list as $k => $v) {
			$fv[$var] += $fv[$v];
		}
	}

	if ($this->isClass('domaina') && $this->nname === 'boxtrapper.com') {
		//dprintr($fv);
	}

	$rexceeded = false;
	foreach ($fv as $var => $v) {
		if ($this->isdeadQuotaVariable($var)) {
			print("Dead Quota: In {$this->getClName()} {$var} equals {$v}\n");
			if ($this->used->$var !== $v) {
				$this->used->$var = $v;
				$this->setUpdateSubaction('collectquotaupdate');
			}
			continue;
		}
		if (!$this->isQuotaVariable($var)) {
			continue;
		}
		/* never Happens. Only Priv is no checked for flag...
		if (cse($var, "_flag")) {
			// Thi is also done in distributeChildQuota.
			if (!$this->priv->isOn($var) && $this->used->isOn($var)) {
				print("In {$this->nname} {$var} is enabled and limit is disabled , Disabling.\n");
				$this->used->$var = 'off';
				$this->setUpdateSubaction("enable_{$var}");
			}
			continue;
		}
	*/
		print("In {$this->getClName()} {$var} equals {$v} and limit is {$this->priv->$var}\n");
		if ($this->used->$var != $v) {
			$this->used->$var = $v;
			$this->setUpdateSubaction('collectquotaupdate');
		}

		if ($this->isLxclient() && !$this->isHardQuota($var) && !is_unlimited($this->priv->$var) && cse($var, "_usage")) {
			$per = $this->used->$var/$this->priv->$var;
			$per *= 100;

			if (cse($var, "_num")) { $exce = 100; }
			else { $exce = 95; }
			//dprint("In {$this->nname} per is {$per}... {$general->dpercentage}\n");
			if ($per > $exce) {
				$this->state = 'exceed';
				$per = round($per);
				if (!$sgbl->__var_just_db) {
					$msg = "Warning: The Account {$this->nname} is using {$per}% of quota for {$var}.\n Limit: {$this->priv->$var}\nUsed: {$this->used->$var}\n";
					$this->notifyAll($msg, false);
				}
			} 
			if ($this->disable_per && !$this->isOff('disable_per') && $per > $this->disable_per) {
				dprint("In {$this->nname} {$var} rexceeded... \n");
				$rexceeded = true;
			}
		}
	}


	if ($rexceeded) {
		// Updatedisable will disable only if itsn't already disabled. Thus it will avoid unnecessary synctosystems.
		$this->updateDisable(null, 'quota');
		$msg = "The Account {$this->nname} has been disabled due to overquota";
		if (!$sgbl->__var_just_db) {
			$this->notifyAll($msg);
		}
		print("$msg\n");
	} else {
		$this->state = 'ok';
		$this->updateEnable(null, 'quota');
	}
	return $fv;


}

function getDbServerNum()
{
	$db = new Sqlite($this->__masterserver, "pserver");
	$list = $db->getTable(array('nname'));
	foreach($list as $k => $l) {
		$nlist[] = $l['nname'];
	}
	sort($nlist);

	foreach($nlist as $k => $l) {
		if ($l === $this->syncserver) {
			return $k + 1;
		}
	}
}

function notifyAll($msg, $parenttoo = true) 
{
	//DO the sending only for objects that contain the contactemail; For instance, if the mailaccount or ftpuser etc is being disabled, don't unncessarily bother all the parents about the disabling.

	global $gbl, $sgbl, $login, $ghtml; 
	$name = ucfirst($sgbl->__var_program_name);
	if (!isset($this->contactemail) || !$this->contactemail) {
		return;
	}

	if (!isset($gbl->__tmp_var_email_list)) {
		$gbl->__tmp_var_email_list = null;
	}

	$gbl->__tmp_var_email_list[$this->contactemail] .= "\n {$msg}";
	// Send Only one level up.

	if (!$parenttoo) { return; }
	$pp = $this->getParentO();
	if (isset($pp->contactemail) && $pp->contactemail) {
		$gbl->__tmp_var_email_list[$pp->contactemail] .= "\n {$msg}";
	}

}

function getChildNameFromDes($k)
{
	if (cse($k, "_l")) {
		$k = substr($k, 0, strrpos($k, "_l"));
	} else if (cse($k, "_o")) {
		$k = substr($k, 0, strrpos($k, "_o"));
	} else if (cse($k, "_a")) {
		$k = substr($k, 0, strrpos($k, "_a"));
	}

	return $k;


}

function distributeChildQuota($oldv = null)
{

	$cl = $this->getQuotaChildList();

	//dprint("hello <br> ");
	$ql = $this->getQuotaVariableList();


	foreach((array) $ql as $k => $v) {
		$list = $this->getQuotaAddList($k);
		if (!$list) {
			continue;
		}
		foreach($list as $nk => $nv) {
			$this->priv->$nv = $this->priv->$k;
		}
	}


	// Important: When the priv flags are turned off or on, you need to to do the requeistie updatesubaction. Also for hardquota variables, the main value also should be impressed upon instantly.
	if ($oldv) {
		foreach((array) $ql as $nk => $nv) {
			if (cse($nk, "_flag")) {
				// This is also done in collectquota, so should be made into a function that can be defined only once. Later...I removed it from there. NOw there is no more used variable for flags. ONly privs. Just set it and it will be done. 
				if ($this->priv->$nk !== $oldv->$nk) {
					if (!isset($this->__old_priv)) {
						$this->__old_priv = $oldv;
					}
					dprint("<br> <b> Warning. In {$this->getClname()} {$nk} is {$this->priv->$nk}: old value is {$oldv->$nk} , Updateing the object.<br> </b>");
					$this->setUpdateSubaction("enable_{$nk}");
				}
				continue;
			}


			// Necessary for hardquota.

			if ($this->isHardQuota($nk) || $this->isForceQuota($nk)) {
				if ($this->priv->$nk !== $oldv->$nk) {
					if (!isset($this->__old_priv)) {
						$this->__old_priv = $oldv;
					}
					dprint("<br> <b> Warning. In {$this->getClname()} {$nk} is {$this->priv->$nk}: old value is {$oldv->$nk} , Updateing the object.<br> </b>");
					$this->setUpdateSubaction("change_{$nk}");
				}
			}



		}
	}

	foreach((array) $cl as $k => $v) {
		if (!cse($v, "_o") && !cse($v, "_l")) {
			continue;
		}

		if (cse($v, "_o")) {
			$chn = $this->getChildNameFromDes($v);

			dprintr("<br> Distribute Child {$chn}: ");

			$cb = $this->getObject($chn);

			// The child object is not a strict necessary and in some cases it may not exist at all. For instance in a forwarded domain, the uuser object will not exist, nor will the mail object.
			if (!$cb) {
				continue;
			}

			$ql = $cb->getQuotaVariableList();

			if ($ql) {
				foreach($ql as $nk => $nv) {
					$cb->priv->$nk = $this->priv->$nk;
				}
				$cb->setUpdateSubaction();
			}


			$cb->distributeChildQuota($oldv);
			continue;
		}

		/* I am not sure if the quota should be synced ALL the list children too. The problem here is the load. But it is essential that _flag variables be properly synced throughout. But then it will take a long time. I think I will add this properly inside the collectquota and leave it here like this.

		if (cse($v, "_l")) {
			$chn = $this->getChildNameFromDes($v);

			$clist = $this->getList($chn);

			if (!$clist) {
				continue;
			}

			foreach($clist as $cb) {
				$ql = $cb->getQuotaVariableList();

				if ($ql) {
					foreach($ql as $nk => $nv) {
						//if (!cse($nk, "_flag")) { continue; }
						$cb->priv->$nk = $this->priv->$nk;
					}
					$cb->setUpdateSubaction();
				}

			}
			$cb->distributeChildQuota($oldv);
		} */
	}



}


function getQuotaChildList()
{
	$cnl = $this->getChildListFilter('q');
	$ret = null;
	if ($cnl) foreach ($cnl as $v) {
		if ($this->isChildVariableSpecific($v)) {
			$ret[] = $v;
		}
	}
	return $ret;

}

function getShowActions(&$alist, $class)
{
	global $gbl, $sgbl, $login, $ghtml; 


	$object = $this->getObject($class);

	//$ol[] = "a=show&o={$class}";
	$ol = null;
	$object->createShowAlist($onl);
	// Hack hack... Removing the '__title_main' from the alist. This is already present in the parent, and it will mix everything and screw up.
	unset($onl['__title_main']);

	unset($onl['action']);

	if (isset($nl['property'])) foreach($onl['property'] as $_tq) {
		$onl[] =  $_tq;
	}
	unset($onl['property']);
	if ($onl) foreach($onl as $k => &$_to) {
		if (is_array($_to)) {
			continue;
		}
		if (csb($k, "__title")) {
			continue;
		}
		if ($ghtml->is_special_url($_to)) {
			$_to->purl = "n={$class}&{$_to->purl}";
			if (isset($_to->__internal)) {
				$_to->url = "n={$class}&{$_to->url}";
			}
			continue;
		}
		$_to = "n={$class}&{$_to}";
	}
	$alist = lx_array_merge(array($alist, $ol, $onl));
	return $alist;

}

static function get_child_full_alist(&$alist, $class)
{
	global $gbl, $sgbl, $login, $ghtml; 


	$onl = exec_class_method($class, "get_full_alist");
	// Hack hack... Removing the '__title_main' from the alist. This is already present in the parent, and it will mix everything and screw up.
	unset($onl['__title_main']);

	unset($onl['action']);
	unset($onl['property']);

	if ($onl) foreach($onl as $k => &$_to) {
		if (is_array($_to)) {
			continue;
		}
		if (csb($k, "__title")) {
			continue;
		}
		if ($ghtml->is_special_url($_to)) {
			$_to->purl = "n={$class}&{$_to->purl}";
			if (isset($_to->__internal)) {
				$_to->url = "n={$class}&{$_to->url}";
			}
			continue;
		}
		$_to = "n={$class}&$_to";
	}
	$alist = lx_array_merge(array($alist, $onl));
	return $alist;
}



function getExtraId() { return null; }

function  getChildShowActions(&$alist)
{
	global $gl_parent_array;

	if (isset($gl_parent_array[$this->get__table()])) {
		foreach($gl_parent_array[$this->get__table()] as $k => $n) {
			$alist = lx_array_merge(array($alist, exec_class_method($k, "createParentShowList", $this, $k)));
		}
	}
	return $alist;
}


function getListActions(&$alist, $class)
{

	global $gbl, $sgbl, $login, $ghtml; 
	$l = exec_class_method($class, 'createListAlist', $this, $class);

	if (!$login->getSpecialObject('sp_specialplay')->isOn('show_add_buttons')) {
		foreach((array) $l as $k => $a) {
			if (!is_array($a) && !$ghtml->is_special_url($a) && csa($a, "addform")) {
				unset($l[$k]);
			}
		}
	}
	$alist = lx_array_merge(array($alist, $l));
	//Hack hack... Removing the addform at this moment.
	return $alist;
}

function isLxclient()
{
	if ($this->is__table('domain')) { return false; }
	return is_subclass_of($this, 'lxclient');
}

function updateCommandCenter($param)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$this->commandCenter($param);
	return null;
}

function commandCenter($param)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$this->ccenter_command = $param['ccenter_command'];

	if ($this->ccenter_command) {
		// This is needed, otherwise if a command gets stuck it will keep tyring to execute the same command.
		// We are not at all using session now. We are just passing it to the next ccenter.
		//if ($gbl) { $gbl->c_session->write(); }

		$driverapp = $gbl->getSyncClass(null, $this->syncserver, $this->get__table());
		$res = rl_exec_get($this->__masterserver, $this->syncserver,  array("{$this->get__table()}__{$driverapp}", "execCommand"), array($this->iid, $this->ccenter_command));

		$this->ccenter_output = $res['output'];
		$this->ccenter_error = $res['error'];
	}

	$vlist['ccenter_command'] = null;
	$vlist['ccenter_output'] = null;
	$vlist['ccenter_error'] = null;
	$vlist['__v_next'] = 'commandcenter';
	$vlist['__v_button'] = 'Execute';
	return $vlist;
}


function getSpecialObject($class)
{

	global $gbl, $sgbl, $login, $ghtml; 
	if (!$this->isLxclient()) {
		dprint("Special object called in nonclient {$this->get__table()}:{$this->nname}<br> \n");
		//debugBacktrace();
	}
	$objectname = $class . "_o";
	// Don't ever call get_classvar_description from here. That will create an infinite loop.
	$name = get_real_class_variable($class, "__special_class");
	if (!$name) {
		$name = strfrom($class, "sp_");
	}
	$bname = $name . "_b";
	if (isset($this->$objectname)) {
		return $this->$objectname->$bname;
	}
	$obj = new $class($this->__masterserver, null, $this->getClName());
	$obj->get();
	//$this->addObject($class, $obj);
	$this->$objectname = $obj;
	$obj->$bname->__parent_o = $this;
	return $obj->$bname;
}

function getDeleteChildListFilter()
{
	$list = $this->getChildListFilter('d');
	return $list;
}


function DeleteFromHere($newserver)
{

	$this->dbaction = 'syncdelete';
	$this->subaction = null;
	$this->syncserver = $newserver;
	$this->createSyncClass();
	$this->execInChildren('b', 'DeleteFromHere', $newserver);
}


// This function was used earlier because ddatabase had the syncserver in its nname. From now on, for the sake for transparency across clusters, main resources cannot have it.

function fix_syncserver_nname_problem()
{
	$rewrite = get_class_variable($this->get__table(), "__rewrite_nname_const");
	if ($rewrite && array_search_bool("syncserver", $rewrite)) {
		$newthis = clone $this;
		$newthis->syncserver = $newserver;
		foreach($rewrite as $n) {
			$nnamelist[] = $newthis->$n;
		}
		$newthis->nname =implode($sgbl->__var_nname_impstr, $nnamelist);

		$sql = new Sqlite($this->__masterserver, $this->get__table());
		$res = $sql->getRowsWhere("nname = '{$newthis->nname}'");
		if ($res) {
			throw new lxException("changed_name_already_exists", $newthis->nname, "syncserver");
		}

		$this->__real_nname = $this->nname;
		$this->nname = $newthis->nname;
		$parent = $this->getParentO();
		$list = $this->get__table() . "_l";
		$parent->{$list}[$this->__real_nname] = null;
		unset($parent->{$list}[$this->__real_nname]);
		$parent->{$list}[$this->nname] = $this;
	}
}

function UpdateHeirarchy()
{

	global $gbl, $sgbl, $login, $ghtml; 

	$this->dbaction = 'update';
	$this->subaction = null;
	// Sort of hack.. NEeded for the ttype to be set to the driver in case of vps
	$this->updateHeirarchySpecific();
	$this->execInChildren('b', 'UpdateHeirarchy');
}

function changePlanSpecific($plan) {}
function updateHeirarchySpecific() {}
function doDriverSpecific() {}
function doServerSpecific() {}
function AddToThere($newserver)
{

	global $gbl, $sgbl, $login, $ghtml; 

	$this->dbaction = 'syncadd';
	$this->syncserver = $newserver;
	if (isset($this->__driverappclass)) {
		$this->__old_driver = $this->__driverappclass;
	}
	$this->createSyncClass();

	if ($sgbl->isHyperVM()) {
		$driverapp = $gbl->getSyncClass($this->__masterserver, $this->syncserver, 'vps');
		$this->ttype = $driverapp;
	}

	$this->doServerSpecific();
	$this->doDriverSpecific();

	$this->execInChildren('b', 'AddToThere', $newserver);
}

function extraBackup() { return false; }
function extraRestore() { return $this->extraBackup(); }


function backMeUpThere()
{
	$this->setUpdateSubaction('top_level_network_backup');
	$this->createExtraVariables();
	$res = rl_exec_set(null, $this->getDataServer(), $this);
	return $res;
}


function restoreMeUpThere($oldserver, $backupfilepass)
{
	$this->setUpdateSubaction('top_level_network_restore');
	$this->createExtraVariables();
	if (isLocalhost($oldserver) && !isLocalhost($this->getDataServer())) {
		$oldserver = getOneIPForLocalhost($this->getDataServer());
	}
	$this->__var_machine = $oldserver;
	$this->__var_backupfilepass = $backupfilepass;
	$res = rl_exec_set(null, $this->getDataServer(),  $this);
}

function getDataServer()
{
	return $this->syncserver;
}

function backMeUp($backupdir, $id)
{
	$res = $this->backMeUpThere();
	$filename = $this->getBackupFileNameForObject($id);
	getFromFileserv($this->getDataServer(), $res, "{$backupdir}/{$filename}"); 
	return $filename;
}

function restoreMeUp($bdir, $id)
{
	$filename = $this->findBackupFileNameIntheDir($bdir, $id);
	if (!$filename) {
		dprint("Failed to get filename for {$this->get__table()}:{$this->nname} {$bdir}, {$id}\n");
		return;
	}
	dprint("Got filename for REstoremeup.... {$filename} \n\n\n");
	$res = cp_fileserv("{$bdir}/{$filename}");
	$this->restoreMeUpThere('localhost', $res);
}

// These are very complex backup/restore functions, and does not really work, 
// because the file size is too high.
function updateBackupOLD($param)
{
	$bfile = tempnam("/tmp", "backupzip.zip");
	lunlink($bfile);
	$vd = createTempDir("/tmp", "backup");
	$list = $this->doCoreBackup($vd, $param);
	lxshell_zip($vd, $bfile, $list);
	lxfile_tmp_rm_rec($vd);

	$fname = $this->getBackupFileNameForObject('out-' . time());

	ob_end_clean();
	header('Content-Type: application/octet-stream');
	header("Content-Disposition: attachment; filename={$fname}");
	slow_print($bfile);
	flush();
	@ lunlink($bfile);
	exit;
}

function updateRestoreOLD($param)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$gbl->__var_list_flag = false;

	$param['nothing'] = null;
	$fname = $_FILES['restore_file_f']['tmp_name'];
	if (!lxfile_exists($fname)) {
		throw new lxException('could_not_get_file', 'dbname', '');
	}

	$param['switchserverlist'] = null;
	$param['_accountselect'] = array('all');
	$this->doCoreRestore($fname, $param);
	return null;
}

// These functions are very simple and backups and restores only the internal content 
// doesn't restore the kloxo database structure. Useful only for database.

function updateBackup($param)
{
	$ret = $this->backMeUpThere();
	$fname = $this->getBackupFileNameForObject('out-' . time());
	while(@ob_end_clean());
	header('Content-Type: application/octet-stream');
	header("Content-Disposition: attachment; filename={$fname}");
	printFromFileServ($this->syncserver, $ret);
	flush();
	exit;
}

function updateRestore($param)
{
	$param['nothing'] = null;
	$fname = $_FILES['restore_file_f']['tmp_name'];
	if (!lxfile_exists($fname)) {
		throw new lxException('could_not_get_file', 'dbname', '');
	}

	$res = cp_fileserv($fname);
	$this->restoreMeUpThere('localhost', $res);
	$this->dbaction = 'clean';
	return null;
}

function isSimpleBackup() { return false; }

function getNotExistingList(&$vlist, $var, $childclass, $sourceclassorlist)
{
	if (is_array($sourceclassorlist)) {
		$list = $sourceclassorlist;
	} else {
		$list = get_namelist_from_objectlist($this->getList($sourceclassorlist));
	}
	$oblist = $this->getList($childclass);

	foreach((array) $oblist as $ob) {
		$key = array_search($ob->$var, $list);
		if ($key !== false) {
			unset($list[$key]);
		}
	}

	if (!$list) {
		$vlist[$var] =  array('M', 'none_free,all_have_been_assigned');
		$vlist['__v_button'] = array();
		return false;

	} else {
		$vlist[$var] = array('s', $list);
		return true;
	}
}


function doSimpleRestore($bfile, $param)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$progname = $sgbl->__var_program_name;
	$cprogname = ucfirst($progname);

	if ($this->isLocalhost('syncserver')) {
		$rem = lxbackup::getMetaData($bfile);
	} else {
		$rem = rl_exec_get($this->__masterserver, $this->syncserver, array("lxbackup", "getMetaData"), array($bfile));
	}

	$ob = $rem->bobject;

//	dprint($ob->getClName()); dprint($this->getClName());

	// Issue #671 - Fixed backup-restore issue
	// forum http://forum.lxcenter.org/index.php?t=msg&th=16875
	// TODO!

	if ($ob->getClName() !== $this->getClName()) {
		throw new lxException('objectclassname_doesnt_match', '');
	}


	if ($gbl->__var_list_flag) {
		print("Contents of the backfile: Owner: {$ob->nname}.....\n");
	} else {
		print("Restoring backup for {$ob->nname}.....\n");
	}

	$ob->checkForConsistency(null, $param['_accountselect'], true);


	// Restore the currenct client's quota. The person who is doing the restoring souldn't able to escape his new quota.
	if ($this->isLogin()) {
		$ob->priv = $this->priv;

		if (isset($this->listpriv)) {
			$ob->listpriv = $this->listpriv;
		}
	}

	$ob->__var_bc_filename = $bfile;


	if (!$gbl->__var_list_flag) {
		$ob->was();
		$ob->simpleRestoreMeUpThere();
	}


}

function doCoreRestore($bfile, $param)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$progname = $sgbl->__var_program_name;
	$cprogname = ucfirst($progname);

	$vd = lxbackup::createTmpDirIfitDoesntExist($bfile, true);

	if ($sgbl->isKloxo()) {
		try {
			$file = "{$vd}/{$progname}.file";
			$rem = getObjectFromFileWithThrow($file);
			$ob = $rem->bobject;
		} catch (Exception $e) {
			$file = "{$vd}/lxadmin.file";
			$rem = getObjectFromFileWithThrow($file);
			$ob = $rem->bobject;
		}
	} else {
		$file = "{$vd}/{$progname}.file";
		$rem = getObjectFromFileWithThrow($file);
		$ob = $rem->bobject;
	}

//	dprint($ob->getClName()); dprint($this->getClName());

	// Issue #671 - Fixed backup-restore issue
	// forum http://forum.lxcenter.org/index.php?t=msg&th=16875
	// TODO!

	if ($ob->getClName() !== $this->getClName()) {
		throw new lxException('objectclassname_doesnt_match', '');
	}


	$gbl->__var_serverlist = $param['switchserverlist'];

	if ($gbl->__var_list_flag) {
		print("Contents of the backfile: Owner: {$ob->nname}.....\n");
	} else {
		print("Restoring backup for {$ob->nname}.....\n");
	}
	
	try {
		$ob->checkForConsistency(null, $param['_accountselect'], true);
	} catch (Exception $e) {
		lxfile_tmp_rm_rec($vd);
		throw $e;
	}

	// Restore the currenct client's quota. The person who is doing the restoring 
	// souldn't able to escape his new quota.

	if ($this->isLogin()) {
		$ob->priv = $this->priv;

		if (isset($this->listpriv)) {
			$ob->listpriv = $this->listpriv;
		}
		if (isset($this->dnstemplate_list)) {
			$ob->dnstemplate_list = $this->dnstemplate_list;
		}
	}

	try {
		if (!$gbl->__var_list_flag) {
			$ob->was();
			foreach($sgbl->__var_objectrestorelist as $d) {
				$d->restoreMeUp($vd, $rem->ddate);
			}
		}
	} catch (Exception $e) {
		lxfile_tmp_rm_rec($vd);
		throw $e;
	}

	lxfile_tmp_rm_rec($vd);

}


function doCoreBackup($bfile, $param)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$progname = $sgbl->__var_program_name;

	$cprogname = ucfirst($progname);

	// Load the entire Backup
	print("Loading the Entire Database\n");
	$this->loadBackupAll();
	print("Done\n");

	$vd = createTempDir("/tmp", "backup");



	$rem = new Remote();
	$ver = $sgbl->__ver_major_minor;
	$rem->version = $ver;
	$rem->ddate = time();
	$cleanobject = clone $this;
	lxclass::clearChildrenAndParent($cleanobject);

	$rem->_clean_object = $cleanobject;
	$__var_backupid = $rem->ddate;

	print("Saving serialized\n");
	lfile_put_contents("{$vd}/{$progname}.metadata", serialize($rem));
	print("Done\n");
	$rem->_clean_object = null;
	$rem->bobject = $this;
	print("Saving serialized second\n");
	lfile_put_contents("{$vd}/{$progname}.file", serialize($rem));
	print("Done\n");

	try { 
		foreach((array) $gbl->__var_objectbackuplist as $d) {
			print("Taking backup of {$d->get__table()}:{$d->nname}\n");
			$d->backMeUp($vd, $rem->ddate);
		}
	} catch (Exception $e) {
		lxfile_tmp_rm_rec($vd);
		throw $e;
	}

	$list = lscandir_without_dot($vd);

	lxfile_mkdir(dirname($bfile));

	lxshell_tgz($vd, $bfile, $list);
	if (!lxfile_exists("/home/lx_debug_backup")) {
		lxfile_tmp_rm_rec($vd);
	}
}

function simpleBackMeupThere()
{
	$this->setUpdateSubaction('top_level_simple_backup');
	$this->createExtraVariables();
	rl_exec_set(null, $this->syncserver, $this);
}

function simpleRestoreMeUpThere()
{
	$this->setUpdateSubaction('top_level_simple_restore');
	$this->createExtraVariables();
	$res = rl_exec_set(null, $this->syncserver,  $this);
}




function doSimpleBackup($bfile, $param)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$progname = $sgbl->__var_program_name;
	$cprogname = ucfirst($progname);

	// Load the entire Backup
	$this->loadBackupAll();

	$fullrem = new Remote();
	$ver = $sgbl->__ver_major_minor;
	$fullrem->version = $ver;
	$fullrem->ddate = time();
	$cleanobject = clone $this;
	lxclass::clearChildrenAndParent($cleanobject);

	$singlerem = clone $fullrem;
	$singlerem->_clean_object = $cleanobject;

	$fullrem->bobject = $this;
	/// Do a was on the login would take a huge amount of time. Let us not do it.

	$this->__var_bc_metafile = serialize($fullrem);
	$this->__var_bc_metadata = serialize($singlerem);
	$this->__var_bc_filename = $bfile;

	foreach($param as $k => $v) {
		if (csb($k, "backupextra_")) {
			$kk = "__var_bc_{$k}";
			$this->$kk = $v;
		}
	}

	$this->simpleBackMeupThere();
}

function getZiptype()
{
	// Issue #671 - Fixed backup-restore issue
	// change to tgz for default that make less space especially temp process

	if (file_exists("/usr/local/lxlabs/kloxo/etc/flag/backup_compress_disabled.flg")) {
		return "tar";
	}
	else {
		return "tgz";
	}
}

function getBackupFileNameForObject($id)
{
	if (isset($this->__real_nname)) {
		$name = $this->__real_nname;
	} else {
		$name = $this->nname;
	}

	$end = $this->getZiptype();

	$filename = "{$name}-{$this->get__table()}-any-{$id}.{$end}";
	return $filename;
}

function findBackupFileNameIntheDir($bdir, $id)
{
	if (isset($this->__real_nname)) {
		$name = $this->__real_nname;
	} else {
		$name = $this->nname;
	}

	if (lxfile_exists("{$bdir}/{$name}-{$this->get__table()}-any-{$id}.tgz")) {
		return "{$name}-{$this->get__table()}-any-{$id}.tgz";
	} else if(lxfile_exists("{$bdir}/{$name}-{$this->get__table()}-any-{$id}.zip")) {
		return "{$name}-{$this->get__table()}-any-{$id}.zip";
	}

	$base = "^{$name}-{$this->get__table()}-[^-]*-{$id}.*";
	$list = lscandir_without_dot($bdir);

	foreach($list as $l) {
		if (preg_match("/{$base}/i", $l)) {
			return $l;
		}
	}


	dprint("Coudln't Get the filname... Most likely Mysqldb... Trying with __servername\n");

	// Only needed for mysqldb...
	$base = "{$name}___[^-]*-{$this->get__table()}-[^-]*-{$id}.*";
	$list = lscandir_without_dot($bdir);

	foreach($list as $l) {
		if (preg_match("/{$base}/i", $l)) {
			return $l;
		}
	}
}

function getBackupChildList()
{
	$list = $this->getChildListFilter('b');
	$list1 = $this->getChildListFilter('B');
	return lx_array_merge(array($list1, $list));
}

function getDisplayBackupChildList()
{
	$list = $this->getChildListFilter('B');
	return $list;
}
function getResourceChildList()
{
	$list = $this->getChildListFilter('R');
	return $list;
}

function getChildList()
{
	$list = $this->getChildListFilter('');
	return $list;
}

function getChildListFilter($s)
{

	$ret = null;
	$q = $this->getVarDescrList(0);
	foreach((array) $q as $k => $v) {
		if (!cse($k, "_o") && !cse($k, "_l") && !cse($k, "_a")) {
			continue;
		}
		if ($s && !csa($v, $s)) {
			continue;
		}
		$ret[$k] =  $k;
	}

	return $ret;

}

function fixPrivUnset()
{
	$list = $this->getQuotaVariableList();
	foreach($list as $k => $v) {
		if (!(cse($k, "_num") || cse($k, "_usage") || cse($k, "_flag"))) {
			continue;
		}
		if (isset($this->priv->$k)) continue;
		if (cse($k, "_flag")) {
			$this->priv->$k = "Off";
		} else {
			$this->priv->$k = "Unlimited";
		}
	}
}

function inheritSynserverFromParent() { return true; }
function convertToUnmodifiable(&$vlist)
{
	foreach($vlist as $k => $v) {
		$vlist[$k] = array('M', null);
	}
}


function getQuotaVariableList()
{

	$vl = getDbvariable("quotavar", $this->get__table());

	$vlist = null;
	foreach((array) $vl as $k => $v) {
		if ($this->isQuotaVariable($k)) {
			// Need to put all the flag variables, which are actually checkboxes at the end. Mixing them up seem to screw up Ie, especially when adding domains.
			if (cse($k, "_flag")) {
				$vlist_flag[$k] = array();
			} else {
				$vlist[$k] = array();
			}
		}
	}
	//dprintr($vlist);
	if (isset($vlist_flag)) {
		$vlist = lx_array_merge(array($vlist, $vlist_flag));
	}
	return $vlist;
}

function getDeadQuotaVariableList()
{

	$vl = $this->getVarDescrList(0);
	$vlist = null;
	foreach($vl as $k => $v) {
		if (cse($k, "_o") || cse($k, "_l") || cse($k, "_a")) {
			continue;
		}
		if ($this->isDeadQuotaVariable($k)) {
			if (isset($this->priv)) {
				if (!isset($this->priv->$k)) {
					$this->priv->$k = '-';
				}
				$vlist[$k] = $this->priv->$k;
			}
		}
	}
	//dprintr($vlist);
	return $vlist;
}

function getResUnit($name, $val = null)
{
	return null;
}
function createShowUpdateform()
{
	return null;
}



function createShowSclist()
{
	return null;

}

static function getTextAreaProperties($var)
{
	return array("height" => 10, "width" => "85%");
}

function getToggleUrl(&$alist)
{
	if ($this->isLogin() || $this->isAdmin()) {
		return;
	}
	if (isOn($this->status)) {
		$alist[] = "a=update&sa=disable";
	} else {
		$alist[] = "a=update&sa=enable";
	}

}

function getQuickClass() { return null; }

function createShowAddform()
{
	return null;
}

function isDeadQuotaVariable($k)
{
	$descr = get_classvar_description(get_class($this),  $k);
	if (csa($descr[0], 'D')) {
		return true;
	}
	return false;

}

function isListQuotaVariable($k)
{
	$descr = get_classvar_description(get_class($this),  $k);
	if (csa($descr[0], 'Q')) {
		return true;
	}
	return false;

}

function isForceQuota($k) { return false; }

function isHardQuota($k)
{
	$descr = get_classvar_description(get_class($this),  $k);
	if (csa($descr[0], 'h')) {
		return $this->isQuotaVariableSpecific($k);
	}
	return false;
}


function isRealQuotaVariable($k) { return false; }
function isQuotaVariable($k)
{
	$descr = get_classvar_description(get_class($this),  $k);
	if (csa($descr[0], 'q')) {
		return $this->isQuotaVariableSpecific($k);
	}
	return false;

}

static function getquotaclass($class)
{
	return $class;
}

function isChildVariableSpecific($var)
{
	return true;
}

function isQuotaVariableSpecific($var)
{
	return true;
}


function showPrivInResource()
{
	return true;
}

}


class LxaClass extends Lxclass {
function get() {}
function write() {}
static function createListAlist($parent, $class)
{
	return null;
}

}


class misc_b extends lxaclass {

}


class listpriv extends lxaclass {


// listpriv is a bit trickier than we first thought. For the admin, the quota is actually dynamic, and is equal to the list of pservers, and their corresponding ip addresses. For other clients, the listquota is static. To get this dynamic list quota for the admin, you need to access the parent (admin) object from inside listpriv, which is actually impossible, since listpriv, being an internal object, is automatically initialiazed when the database is loaded. Anyway, currently I have just added listpriv->__parent_o = $this inside the getThisFromDb->setFromArray() in the lxdb. This is actually not a hack, and should work pretty much fine, since setFromArray is a fundamental function and called by everyone to initialize the objects.
function __get($var)
{
	global $gbl, $sgbl, $login, $ghtml; 
	if (!isset($this->__parent_o)) {
		return null;
	}
	$parent = $this->__parent_o;
	if (!$parent || !$parent->isAdmin()) {
		return null;
	}


	if (cse($var, 'pserver_list')) {
		$slist = $parent->getRealPserverList(strtil($var, "pserver_list"));
		$this->$var = get_namelist_from_objectlist($slist);
		return $this->$var;
	}

	if (cse($var, 'dnstemplate_list')) {
		$this->$var = DomainBase::getDnsTemplateList($parent);
		return $this->$var;
	}

	if (cse($var, 'dbtype_list')) {
		return $sgbl->__var_dblist;
	}
	return null;
}

}



class priv extends Lxaclass {

function  display($var)
{
	if ($this->$var === null) {
		return '-';
	}
	return resource::privdisplay($var, $var, $this->$var);
}

function __get($var)
{
	if (!isset($this->__parent_o)) {
		dprint("<b> error No parent... {$this->nname} {$this->__class}\n </b>");
	}

	if (cse($var, '_flag')) {
		if (isset($this->__parent_o) && $this->__parent_o->isAdmin()) {
			$this->$var = 'on';
		} else {
			$this->$var = 'on';
		}
		return $this->$var;
	}

	return '-';
}

}

class Used extends priv {

function __get($var)
{
	if (cse($var, '_num')) {
		$this->$var = '0';
		return $this->$var;
	}
	return "-";
}


}


class LxMailClass extends Lxaclass {

static function createListAlist($parent, $class)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$alist[] = "a=list&c={$class}";
	return $alist;
}

static function createListAddForm($parent, $class)
{
	return true;
}

}



abstract class LxlClass extends Lxclass {
function get() {}
function write() {}
}

class lxDriverClass extends Lxclass {
function get() {}
function write() {}

function dosyncToSystemPre() {}
function dosyncToSystemPost() {}
static function installMe() {}
static function unInstallMe() {}

function dbactionDelete()
{
}

function dbactionAdd()
{
}

function dbactionUpdate($subaction)
{
}

function do_backup_cleanup($list) { return; }

function top_level_central_back()
{
	$bc = $this->do_backup();
	if (!isset($this->main->__save_variable)) {
		$this->main->__save_variable = null;
	}
	return array('savelist' => $this->main->__save_variable, 'back' => $bc);
}

function top_level_central_back_clean()
{
	$this->do_backup_cleanup($this->main->__save_bc);
}

function top_level_network_backup()
{
	$bc = $this->do_backup();

	if (!count($bc[1])) {
		$bc[1][] = 'blank_file';
		lxfile_touch("{$bc[0]}/blank_file");
	}
	if ($this->main->getZiptype() === 'zip') {
		$res = zip_to_fileserv($bc[0], $bc[1]);
	} else if ($this->main->getZiptype() === 'tar') {
		$res = tar_to_fileserv($bc[0], $bc[1]);
	} else {
		$res = tgz_to_fileserv($bc[0], $bc[1]);
	}

	$this->do_backup_cleanup($bc);
	return $res;
}

function top_level_simple_backup()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$progname = $sgbl->__var_program_name;

	$dir =dirname($this->main->__var_bc_filename);
	$name = basename($this->main->__var_bc_filename);

	$firstname = strtil($name, "-");
	lxfile_mkdir($dir);
	$list = lscandir_without_dot($dir);

	$bc = $this->do_backup();

	$tmpdir = createTempDir("$sgbl->__path_tmp", "backupfile");

	lfile_put_contents("$tmpdir/{$progname}.file", $this->main->__var_bc_metafile);
	lfile_put_contents("$tmpdir/{$progname}.metadata", $this->main->__var_bc_metadata);
	$newarray = lx_array_merge(array($bc[1], array("$tmpdir/{$progname}.file", "$tmpdir/{$progname}.metadata")));

	if ($this->main->getZiptype() === 'zip') {
		lxshell_zip($bc[0], $this->main->__var_bc_filename, $newarray);
	} else if ($this->main->getZiptype() === 'tar') {
		lxshell_tar($bc[0], $this->main->__var_bc_filename, $newarray);
	} else {
		lxshell_tgz($bc[0], $this->main->__var_bc_filename, $newarray);
	}

	print_time("cpzip", "Copy and Zip");
	lxfile_tmp_rm_rec("$tmpdir");
	$this->do_backup_cleanup($bc);
}



function top_level_network_restore()
{
	global $gbl, $sgbl, $login, $ghtml; 

	$machine = $this->main->__var_machine;
	$backupfilepass = $this->main->__var_backupfilepass;
	$docd = tempnam("/tmp", "network_restore");

	dprint("Top Level Restore.. Got the remote file..\n");
	dprint($machine);
	dprintr($backupfilepass);
	dprint("\n");
	getFromFileserv($machine, $backupfilepass, $docd);
	$this->do_restore($docd);
	lunlink($docd);

}

function top_level_simple_restore()
{
	$this->do_restore($this->main->__var_bc_filename);
}



function mydbactionUpdate()
{
	$totalres = null;
	if (is_array($this->main->subaction)) {
		foreach($this->main->subaction as $sub) {

			if (csb($sub, "top_level_")) {
				$res = $this->$sub();
			} else {
				$res = $this->dbactionUpdate($sub);
			}
			$totalres = lx_array_merge(array($totalres, $res));
		}
	} else {

		$sub = $this->main->subaction;

		if (csb($sub, "top_level_")) {
			$res = $this->$sub();
		} else {
			$res = $this->dbactionUpdate($sub);
		}

		$totalres = $res;
	}

	return $totalres;

}

function dosyncToSystem()
{

	$this->dosyncToSystemPre();

	switch($this->main->dbaction) {
		case "syncdelete":
		case "delete" :
			$res = $this->dbactionDelete();
			break;

		case "syncadd":
		case "add" : 
			$res = $this->dbactionAdd();
			break;

		case "update": 
			// Calling Our mydbactionupdate Which will in turn call the real dbactionupdate defined in the class.
			$res = $this->mydbactionUpdate();
			break;

	}

	$this->dosyncToSystemPost();

	return $res;

}

}

class ddatabase {
}

