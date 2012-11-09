<?php 

class Lxupdate extends lxClass {

static $__ttype = "permanent";
static $__desc = array("S", "",  "update");

// Mysql
static $__desc_nname =     array("n", "",  "_version", "a=show");
static $__desc_state =     array("e", "",  "state", "a=show");
static $__desc_schedule =     array("n", "",  "_schedule_updation_later", "a=show");

static $__desc_current_version_f = array("", "",  "current_version");
static $__desc_latest_version_f = array("", "",  "latest_version");
static $__desc_buglist_f = array("T", "",  "bugs_in_this_version");

// MR -- add new var
static $__desc_stamp_f = array("", "",  "stamp");
static $__desc_step_f = array("", "",  "step");
static $__desc_name_f = array("", "",  "Name");
static $__desc_note_f = array("", "",  "Note");

static $__acdesc_update_lxupdateinfo = array("", "",  "update");
static $__acdesc_update_bugs = array("", "",  "bugs");

static $__desc_releasenote_l = array("", "",  "");

function get(){}
function write(){}



function createShowPropertyList(&$alist)
{
	$alist['property'][] = 'a=show';
	$alist['property'][] = "a=list&c=releasenote";
}

function createShowAlist(&$alist, $subaction = null)
{
	global $gbl, $sgbl, $login, $ghtml; 
	return $alist;
	if (checkIfLatest() && !if_demo()) {
		return null;
	} 
}

function createShowUpdateform()
{
	$uflist['lxupdateinfo'] = null;
	return $uflist;
}

function updateform($subaction, $param)
{

	global $gbl, $sgbl, $login, $ghtml; 
	$maj = $sgbl->__ver_major;

	switch($subaction) {

		case "lxupdateinfo":
			{
				$vlist['name_f'] = array('M', $sgbl->__ver_name);
				$vlist['note_f'] = array('M', $sgbl->__ver_note);

				$vlist['current_version_f'] = array('M', $sgbl->__ver_full);

			//	$vlist['current_version_f'] = array('M', $sgbl->__ver_major_minor_release);
			//	$vlist['latest_version_f'] = array('M', getLatestVersion());
			//	$vlist['stamp_f'] = array('M', $sgbl->__ver_stamp);
			//	$vlist['step_f'] = array('M', $sgbl->__ver_extra);

				if ($sgbl->__ver_major_minor_release === getLatestVersion()) {
					$vlist['__v_button'] = array();
				} else {
					$vlist['__v_button'] = "Update Now";
				}
				
				return $vlist;
			}

		case "bugs":
			{
				$file = "bugs/bugs-{$sgbl->__ver_major_minor_release}.txt";
				$content = curl_get_file_contents($file);
				$content = trim($content);
				if (!$content) {
					$content = "There are no Bugs Reported for this Version";
				}
				$vlist['buglist_f'] = array('t', $content);
				return $vlist;
			}

	}
}

function updateLxupdateInfo()
{
	if_demo_throw_exception();
	if (isUpdating()) {
		throw new lxException("program_is_already_updating");
	} else {
		rl_exec_get($this->__masterserver, 'localhost', array('lxupdate', 'execUpdate'), null);
		throw new lxException("update_scheduled");
	}
}

static function execUpdate()
{
	lxshell_background("__path_php_path", "../bin/update.php");
}

static function initThisObjectRule($parent, $class, $name = null)
{
	global $gbl, $sgbl, $login, $ghtml; 
	/*
	if (!$parent->isLocalhost('nname')) {
		throw new lxException("slave_is_automatically_updated", $parent->nname);
	}
*/
	$thisversion = $sgbl->__ver_major_minor_release;
	$upversion = getLatestVersion();
	return $upversion;
}

}

