<?php 

class lxbackup extends Lxdb {

static $__desc = array("", "",  "backup");
static $__desc_nname =  array("n", "",  "backup");
static $__desc_ftp_server =  array("n", "",  "ftp_server");
static $__desc_ssh_server =  array("n", "",  "ssh_server");
static $__desc_rm_username =  array("n", "",  "username");
static $__desc_rm_password =  array("n", "",  "password");
static $__desc_rm_directory =  array("", "",  "directory");
static $__desc_upload_type =  array("", "",  "upload_type");
static $__desc_backupschedule_flag =  array("q", "",  "allow_schedule_backup");
static $__desc_backupschedule_type =  array("", "",  "schedule_backup");
static $__desc_backup_from_file_f    = array("n","",  "backup_from_file"); 
static $__desc_backup_ftp_file_f    = array("n","",  "filename_on_the_ftp_server"); 
static $__desc_backup_to_file_f    = array("n","",  "backup_file_initial_string"); 
static $__desc_send_email    = array("f","",  "send_email_after_backup"); 
static $__desc_backupstage    = array("","",  "last_backup_status"); 
static $__desc_restorestage    = array("","",  "last_restore_status"); 
static $__desc_rm_last_number    = array("","",  "keep_this_many_backups_on_the_server"); 
static $__desc_rm_last_number_v_5    = array("","",  "keep_this_many_backups_on_the_server"); 
static $__desc_dont_verify_ftp_f    = array("f","",  "dont_verify_ftp_credentials"); 
static $__desc_temp_f =  array("", "",  "");
static $__desc_upload_to_ftp =  array("f", "",  "upload_files_to_remote_server_");
static $__desc_no_local_copy_flag =  array("f", "",  "dont_keep_a_local_copy");
static $__desc_backupextra_stopvpsflag =  array('f', "", "stop_vps_while_backup_snapshot_(recommended)", "");

static $__acdesc_update_backup =  array("","",  "backup_now"); 
static $__acdesc_update_restore =  array("","",  "restore"); 
static $__acdesc_update_ftp_conf =  array("","",  "ftp_configuration"); 
static $__acdesc_update_schedule_conf =  array("","",  "schedule_configuration"); 
static $__acdesc_update_restore_from_file =  array("","",  "restore_from_file"); 
static $__acdesc_update_restore_from_ftp =  array("","",  "restore_from_ftp"); 


function getFfileFromVirtualList($name)
{
	//$ffile= new Ffile($this->__masterserver, $this->syncserver, "__path_httpd_root/{$this->getParentO()->nname}/{$this->getParentO()->nname}", $name, $this->getParentO()->getObject('web')->username);
	global $gbl, $sgbl, $login, $ghtml; 
	$name = coreFfile::getRealpath($name);
	$name = "/$name";
	$parent = $this->getParentO();
	$root = "__path_program_home/{$this->getParentO()->get__table()}/{$this->getParentO()->nname}/__backup/";
	$fullpath = "$root/$name";
	if ($parent->isClient()) {
		$syncserver = "localhost";
	} else {
		$syncserver = $parent->syncserver;
	}
	rl_exec_get(null, $syncserver, array("lxbackup", "MakeSureDirectoryExists"), array("$root/"));
	$ffile= new Ffile(null, $syncserver, $root, $name, 'lxlabs');
	$ffile->__parent_o = $this;
	$ffile->get();
	return $ffile;
}

static function MakeSureDirectoryExists($name)
{
	if (!lxfile_exists($name)) {
		lxfile_mkdir($name);
		lxfile_generic_chown($name, "lxlabs");
	}
}

function createShowShowlist() 
{ 

	$alist = null;
	//$alist['ffile'] = null;
	return $alist;
}

function isSync() {return false ; }


function update($subaction, $param)
{
	if (!$this->getParentO()->priv->isOn('backup_flag')) {
		throw new lxException('no_premission_to_backup', '', '');
	}
	return $param;
}

static function getMetaData($file)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$progname = $sgbl->__var_program_name;

	if (!lxfile_exists($file)) {
		throw new lxException('could_not_find_file', '', $file);
	}
	$tmpdir = lxbackup::createTmpDirIfitDoesntExist($file, false);
	print_time("create_tmp_dir", "Creating Tmp Directory");
	$filename = recursively_get_file($tmpdir, "$progname.file");

	// KLoxo has to recognize lxadmin's backup file.
	if (!$filename && $sgbl->isKloxoForRestore()) {
		$filename = recursively_get_file($tmpdir, "lxadmin.file");
	}

	$rem = unserialize(file_get_contents($filename));
	lxfile_tmp_rm_rec($tmpdir);
	if (!$rem) {
		throw new lxException('backupfile_corrupted', '');
	}
	return $rem;
}


function backupcheckForConsistency($tree, $param)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$progname = $sgbl->__var_program_name;

	$parent = $this->getParentO();

	print_time("create_tmp_dir");
	$file = $this->getFtpOrLocal($param);
	dprint($file);

	if ($parent->isClient() || $parent->isLocalhost('syncserver')) {
		$rem = self::getMetaData($file);
	} else {
		$rem = rl_exec_get($parent->__masterserver, $parent->syncserver, array("lxbackup", "getMetaData"), array($file));
	}

	print_time("check_cons");
	$trulist = null;
	if (isset($param['_accountselect'])) {
		$trulist = $param['_accountselect'];
	}

	$this->__var_backup = $rem->bobject;
	$this->__var_backup->checkForConsistency($tree, $trulist, false);
	print_time("check_cons", "Checking Consistency");
	if (csa($file, "__lx_temperoryftp_file")) { lunlink($file); }
}


function updateFtp_conf($param)
{
	$param['rm_username'] = fix_meta_character($param['rm_username']);
	if (is_star_password($param['rm_password'])) {
		$param['rm_password'] = $this->rm_password;
	}


	if (isOn($param['upload_to_ftp']) && !isOn($param['dont_verify_ftp_f'])) {
		$fn = lxftp_connect($param['ftp_server']);
		$mylogin = ftp_login($fn, $param['rm_username'], $param['rm_password']);
		if (!$mylogin) {
			$p = error_get_last();
			throw new lxException('could_not_connect_to_ftp_server', '', $p);
		}
	}
	/*
	ftp_pasv($fn, true);
	$fp = lfopen($file, "r");
	$ret = ftp_fput($fn, $uploadfilename, $fp, FTP_BINARY);
	if (!$ret) {
		throw new lxException('could_not_upload_file', '', $object->ftp_server);
	}
*/
	return $param;
}



function updateform($subaction, $param)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$progname = $sgbl->__var_program_name;

	$parent = $this->getParentO();

	$tree = createTreeObject('name', null, null, null, null, null, null);
	$gbl->__var_restore_tree = $tree;

	if ($this->rm_username) {
		$this->rm_username = fix_meta_character($this->rm_username);

	}

	switch($subaction) {


		case "backup":
			if (trim($this->ftp_server)) {
				$vlist['ftp_server'] = array('M', null);
				$vlist['rm_username'] = array('M', null);
				$vlist['rm_password'] = array('M', '***');
				$vlist['upload_to_ftp'] = array('M', null);
			} else {
				$vlist['upload_to_ftp'] = array('M', "Ftp Server Not Set");
			}

			if (!$this->getParentO()->checkIfLockedForAction('backup')) {
				if ($this->backupstage === 'doing') {
					$this->backupstage = 'program_interrupted';
				}
			}
			$this->backupstage = fix_nname_to_be_variable($this->backupstage);
			$vlist['backupstage'] = array('M', null);
			$vlist['backup_to_file_f'] = null;

			$parent->backupExtraVar($vlist);

			$vlist['__v_button'] = 'Backup Now';
			return $vlist;



		case "restore_confirm":
			$gbl->__var_tmp_disabled_flag = false;
			if ($param) {
				$this->backupcheckForConsistency($tree, $param);
			}
			$vlist['__v_childheir'] = '__var_backup';
			$vlist['__v_showcheckboxflag'] = true;

			if ($sgbl->isDebug()) {
				$vlist['__v_resourcefunc'] = "getBackupChildList";
			} else {
				$vlist['__v_resourcefunc'] = "getDisplayBackupChildList";
			}

			//$vlist['__v_resourcefunc'] = "getDisplayBackupChildList";
			$vlist['__v_param'] = $param;
			$vlist['__v_button'] = 'Restore Now';
			print_time("restore_process", "Restore Processing Took");
			return $vlist;



		case "restore_confirm_confirm":
			$gbl->__var_tmp_disabled_flag = true;
			print_time("restore_process");
			if ($param) {
				$this->backupcheckForConsistency($tree, $param);
			}
			$vlist['__v_childheir'] = '__var_backup';
			if ($sgbl->isDebug()) {
				$vlist['__v_resourcefunc'] = "getBackupChildList";
			} else {
					$vlist['__v_resourcefunc'] = "getDisplayBackupChildList";
			}

			//$vlist['__v_resourcefunc'] = "getDisplayBackupChildList";
			$vlist['__v_showcheckboxflag'] = true;
			$vlist['__v_param'] = $param;
			$vlist['__v_button'] = 'Restore Now';
			print_time("restore_process", "Restore Processing Took");
			return $vlist;


		case "schedule_conf":
			if ($parent->isSimpleBackup()) {
				$sched = array('disabled', 'weekly', 'monthly');
			} else {
				$sched = array('disabled', 'daily', 'weekly', 'monthly');
			}

			if (!$this->backupschedule_type) {
				$this->backupschedule_type = 'disabled';
			}
			if ($this->priv->isOn('backupschedule_flag')) {
				$vlist['backupschedule_type'] = array('s', $sched);
			} else {
				$vlist['backupschedule_type'] = array('M', 'Disabled');
			}
			$vlist['rm_last_number'] = null;
			$vlist['__v_updateall_button'] = array();
			break;


		case "ftp_conf":
			$vlist['ftp_server'] = null;
			//$vlist['ssh_server'] = null;
			$vlist['rm_username'] = null;
			$vlist['rm_password'] = array('m', get_star_password());
			$vlist['rm_directory'] = null;
			$vlist['upload_to_ftp'] = null;
			$vlist['upload_type'] = array('M', 'ftp');
			$vlist['no_local_copy_flag'] = null;
			$vlist['dont_verify_ftp_f'] = null;
			$vlist['__v_updateall_button'] = array();
			break;


		case "restore_from_ftp":
			if (!$this->ftp_server) {
				$vlist['ftp_server'] = array("M", "Ftp Server is Not Set");
				break;
			}

			$vlist['ftp_server'] = array('M', null);
			$vlist['rm_username'] = array('M', null);
			$vlist['rm_password'] = array('M', "****");
			$vlist['backup_ftp_file_f'] = null;
			$vlist['__v_next'] = 'restore_confirm';
			$vlist['__v_button'] = 'Continue';
			break;


		case "restore_from_file":
			if (!$this->getParentO()->checkIfLockedForAction('restore')) {
				if ($this->restorestage === 'doing') {
					$this->restorestage = 'program_interrupted';
				}
			}
			$vlist['restorestage'] = array('M', null);
			$vlist['backup_from_file_f'] = array('L', "/");
			$vlist['__v_next'] = 'restore_confirm';
			$vlist['__v_button'] = 'Start Restore Process';
			break;


	}
	return $vlist;
}

function createShowPropertyList(&$alist)
{
	$alist['property'][] = 'a=show';
	//$alist[] = 'a=updateform&sa=backup';
	$alist['property'][] = 'a=updateform&sa=ftp_conf';

	$alist['property']['__var_backupschedule_flag'] = 'a=updateform&sa=schedule_conf';
	$alist['property'][] = 'a=show&l[class]=ffile&l[nname]=/';
	$alist['property'][] = "l[class]=ffile&l[nname]=/&a=updateform&sa=upload";
	//$alist['property'][] = "l[class]=ffile&l[nname]=/&a=updateform&sa=backupftpupload";
	return $alist;
}


function createShowAlist(&$alist, $subaction = null)
{
	global $gbl, $sgbl, $login, $ghtml; 
	//$alist['__title_main'] = $login->getKeywordUc('actions');
	//$alist[] = 'a=updateform&sa=restore_from_file';
	//$alist[] = 'a=updateform&sa=restore_from_ftp';
	return $alist;
}

function createShowUpdateform()
{
	$uflist['backup'] = null;
	$uflist['restore_from_file'] = null;
	return  $uflist;
}
function getId()
{
	list($class, $name) = getClassAndName($this->nname);
	return $name;
}

function updateBackupRestore($param, $type)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$stagevar = $type . "stage";

	if ($this->getParentO()->checkIfLockedForAction($type)) {
		$message = "{$type}_is_going_on";
		throw new lxException($message, '');
	}

	$bpath = "__path_program_home/{$this->getParentO()->get__table()}/{$this->getParentO()->nname}/__backup";
	$parent = $this->getParentO();

	if ($type === 'backup') {
		if ($parent->get__table() === 'vps') {
			$num = rl_exec_get(null, $parent->syncserver, "get_total_files_in_directory", array($bpath));
			if (isQuotaGreaterThanOrEq($num, $parent->priv->backup_num)) {
				throw new lxException ("backup_number_exceeded", '', "$num greater than {$parent->priv->backup_num}");
			}
		}
	}



	// There is a timing issue here. The backup.php program should be run only AFTEr the update is fully complete.
	foreach($param as $k => $v) {
		$this->$k = $v;
	}
	$this->$stagevar = 'doing';
	$this->backuptype = $type;
	$this->metadbaction = 'writeonly';
	$this->dbaction = 'update';
	if ($this->backuptype === 'backup') {
		rl_exec_get(null, null, array("lxbackup", "execbackupphp"), array($this->getParentClass(), $this->getParentName(), $param));
		$this->write();
		throw new lxException("backup_has_been_scheduled", '');
	} else {
		$bpath = "__path_program_home/{$this->getParentO()->get__table()}/{$this->getParentO()->nname}/__backup";
		$fname = $param['backup_from_file_f'];
		$fname = str_replace("/", "", $fname);
		$fname = str_replace(";", "", $fname);
		$fname = str_replace(" ", "", $fname);
		$file = "$bpath/$fname";
		rl_exec_get(null, null, array("lxbackup", "execrestorephp"), array($this->getParentClass(), $this->getParentName(), $file, $param));
		$url = $ghtml->getFullUrl('a=show');
		$gbl->__this_redirect = "$url&frm_smessage=restore_started";
		$this->write();
		return null;
	}


}

function updateBackup($param)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$bpath = "__path_program_home/{$this->getParentO()->get__table()}/{$this->getParentO()->nname}/__backup";
	$bfile = "$bpath/{$this->createBackupFileName($param['backup_to_file_f'])}.tgz";
	if (lxfile_exists($bfile)) {
		throw new lxException ("file_already_exists", 'backup_to_file_f', $param['backup_to_file_f'] . ".tgz");
	}
	$this->updateBackupRestore($param, "backup");
}

function updateRestore_confirm_confirm($param)
{
	global $gbl, $sgbl, $login, $ghtml; 
	dprintr($param);

	$this->updateBackupRestore($param, "restore");

	//if (csa($file, "__lx_temperoryftp_file")) { unlink($file); }
}

function construct_tarfilename($name)
{
	//$date = date("Y-m-d-H-i");
	//$tim = time();
	$date = '';
	$tim = '';
	$hostname = `hostname`;
	$hostname = trim($hostname);
	$ret = "backup-$name";
	return $ret;
}

static function execrestorephp($class, $name, $file, $param)
{
	global $global_shell_error;
	$val = $param['_accountselect'] ;
	$res = implode($val, ",");
	$res = str_replace("-", ":", $res);
	//$res = str_replace("_s_vv_p_", ":", $res);
	lxshell_background("__path_php_path", "../bin/common/restore.php", "--class=$class", "--name=$name", "--restore", "--accounts=$res", "--priority=low", $file);
}

static function execbackupphp($class, $name, $param)
{
	foreach($param as $k => $v) {
		if (csb($k, "backupextra_")) {
			$string[] = "--v-$k=$v";
		}
	}
	$fname = $param['backup_to_file_f'];
	$fname = str_replace(";", "", $fname);
	$fname = str_replace("/", "", $fname);
	lxshell_background("__path_php_path", "../bin/common/backup.php", "--class=$class", "--name=$name", "--v-backup_file_name=$fname", $string[0]);
}



function createBackupFileName($name)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$ver = $sgbl->__ver_major_minor;
	$name = str_replace("/", "", $name);
	$name = str_replace(";", "", $name);
	$date = @ date('Y-M-d');
	$time = time();
	$bfile = "$name-{$ver}-{$this->getParentO()->nname}-$date-$time";
	return $bfile;
}


function doupdateBackup($param)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$progname = $sgbl->__var_program_name;
	$cprogname = ucfirst($progname);


	$parent = $this->getParentO();
	$bpath = "__path_program_home/{$parent->get__table()}/{$parent->nname}/__backup";
	$bfile = $bpath . "/" . $this->createBackupFileName($param['backup_to_file_f']) . "." . $parent->getZiptype();


	if ($parent->isSimpleBackup()) {
		$parent->doSimpleBackup($bfile, $param);
	} else {
		$parent->doCoreBackup($bfile, $param);
	}

	$object = clone($this);
	lxclass::clearChildrenAndParent($object);


	if ($object->isOn('upload_to_ftp')) {
		try {
			if ($parent->isClient() || $parent->isLocalhost()) {
				self::upload_to_server($bfile, basename($bfile), $object);
			} else {
				rl_exec_get(null, $parent->syncserver, array('lxbackup', 'upload_to_server'), array($bfile, basename($bfile), $object));
			}
		} catch (Exception $e) {
			lx_mail(null, $this->getParentO()->contactemail, "$cprogname Backup Upload Failed on " . date('Y-M-d') . " at " . date('H') ." Hours" , "$cprogname Backup upload Failed due to {$e->getMessage()}\n");  
		}
	}


	if ($parent->isClient() || $parent->isLocalhost()) {
		self::clear_extra_backups($parent->get__table(), $parent->nname, $object);
	} else {
		rl_exec_get(null, $parent->syncserver, array('lxbackup', 'clear_extra_backups'), array($parent->get__table(), $parent->nname, $object));
	}

	lx_mail(null, $this->getParentO()->contactemail, "$cprogname Backup on " . @ date('Y-M-d') . " at " . @ date('H') ." Hours" , "$cprogname Backup Succeeded for {$this->getParentO()->nname}\n");  

	//$gbl->__this_redirect = $ghtml->getFullUrl('a=show') . "&frm_smessage=backup_succeeded";
	//$ghtml->print_redirect($gbl->__this_redirect);
}


static function clear_extra_backups($class, $name, $object)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$progname = $sgbl->__var_program_name;
	$bpath = "__path_program_home/$class/$name/__backup";
	$list = lscandir_without_dot($bpath);
	$dellist = self::getDeleteList($object, $list);


	dprint("Delete list\n");
	dprintr($dellist);
	$num = $object->rm_last_number? $object->rm_last_number: 5;
	print("Deleting Old backups.... Will retain $num.\n");
	$bpath = "__path_program_home/$class/$name/__backup";
	foreach($dellist as $k => $v) {
		print("deleting $v\n");
		lunlink("$bpath/$v");
	}

	print("deleting old backups from ftp server\n");

	$fn = null;
	$mylogin = null;
	if (!$object->ftp_server) {
		return;
	}
	$fn = lxftp_connect($object->ftp_server);
	$mylogin = ftp_login($fn, $object->rm_username, $object->rm_password);

	if (!$fn) { return; }

	# Issue 366
	ftp_pasv($fn, true); 

	$list = ftp_nlist($fn, $object->rm_directory);

	print("Total list of files in ftp server\n");
	print_r($list);

	$dellist = self::getDeleteList($object, $list);

	print("Deleting these files on remote server.\n");
	print_r($dellist);

	foreach((array)$dellist as $k => $v) {

		$v = basename($v);

		if ($object->rm_directory) { $v = "{$object->rm_directory}/$v"; }

		ftp_delete($fn, $v);
	}

	ftp_close($fn);
}


static function getDeleteList($object, $list)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$progname = $sgbl->__var_program_name;
	dprint("$object->nname\n");
	$aname = strfrom($object->nname, "-");
	$aname = "-$aname";
	foreach($list as $k => &$__l) {
		$__l = basename($__l);
		if (csb($__l, "$progname-scheduled") && csa($__l, $aname)) {
		} else {
			unset($list[$k]);
		}
	}
	$newlist = null;
	foreach($list as $k => $l) {
		$v = explode('-', $l);
		$ti = getLastFromList($v);
		list($time, ) = explode(".", $ti);
		$newlist[$time] = $l;
	}

	if (!$newlist) {
		return;
	}

	ksort($newlist);

	$num = $object->rm_last_number? $object->rm_last_number: 5;
	$total = count($newlist);
	$i = 0;
	foreach($newlist as $k => $v) {
		$i++;
		if ($i > $total - $num) {
			break;
		}
		$retlist[$k] = $v;
	}


	return $retlist;
}

function getFtpOrLocal($param)
{
	if (isset($param['backup_ftp_file_f'])) {
		$file = tempnam("/tmp/", "__lx_temperoryftp_file");
		lunlink($file);
		$file .= ".zip";
		$this->download_from_server($param['backup_ftp_file_f'], $file);
		$ftp = true;
	} else {
		$bpath = "__path_program_home/{$this->getParentO()->get__table()}/{$this->getParentO()->nname}/__backup";
		$file = $param['backup_from_file_f'];
		$file = str_replace(";", "", $file);
		$file = str_replace("/", "", $file);
		$file = str_replace(" ", "", $file);
		$file = "$bpath/$file";
		$ftp = false;
	}
	return $file;
}


static function createTmpDirIfitDoesntExist($file, $real)
{

	global $gbl, $sgbl, $login, $ghtml; 
	$progname = $sgbl->__var_program_name;
	$vd = tempnam("/tmp", "backup");
	if (!$vd) {
		throw new lxException('could_not_create_tmp_dir', '');
	}
	lunlink($vd);
	mkdir($vd);
	lxfile_generic_chmod($vd, "0700");

	if ($real) {
		lxshell_unzip_with_throw($vd, $file);
	} else {
		if ($sgbl->isKloxoForRestore()) {
			try {
				lxshell_unzip_with_throw($vd, $file, array("*$progname.file", "*$progname.metadata"));
			} catch (Exception $e) {
				lxshell_unzip_with_throw($vd, $file, array("*lxadmin.file", "*lxadmin.metadata"));
			}
		} else {
			lxshell_unzip_with_throw($vd, $file, array("*$progname.file", "*$progname.metadata"));
		}
	}

	return $vd;

}

function doUpdateRestore($file, $param)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$progname = $sgbl->__var_program_name;
	$cprogname = ucfirst($progname);

	$parent = $this->getParentO();

	if ($parent->isSimpleBackup()) {
		$parent->doSimpleRestore($file, $param);
	} else {
		$parent->doCoreRestore($file, $param);
	}

	if (!$gbl->__var_list_flag) {
		lx_mail(null, $this->getParentO()->contactemail, "$cprogname Restoration on " . @ date('Y-M-d') . " at " . @ date('H') ." Hours" , "$cprogname Restoration Succeeded for {$parent->nname}\n");  
	}

	if ($sgbl->isKloxo()) {
		lxshell_return("__path_php_path", "../bin/collectquota.php", "--just-db=yes"); 
	}

}



function download_from_server($file, $localfile)
{
	$fn = lxftp_connect($this->ftp_server);
	$mylogin = ftp_login($fn, $this->rm_username, $this->rm_password);
	if (!$mylogin) {
		$p = error_get_last();
		throw new lxException('could_not_connect_to_ftp_server', '', $p);
	}

   // using a PASV connection is more likely to succeed
	ftp_pasv($fn, true);

	$fp = lfopen($localfile, "w");
	if (!ftp_fget($fn, $fp, $file, FTP_BINARY)) {
		throw new lxException('file_download_failed', '', $file);
	}
	fclose($fp);
}

static function upload_to_server($file, $uploadfilename, $object)
{
	$fn = lxftp_connect($object->ftp_server);
	$mylogin = ftp_login($fn, $object->rm_username, $object->rm_password);
	if (!$mylogin) {
		$p = error_get_last();
		throw new lxException('could_not_connect_to_ftp_server', '', $p);
	}
	ftp_pasv($fn, true);
	$fp = lfopen($file, "r");
	if ($object->rm_directory) {
		ftp_mkdir($fn, $object->rm_directory);
		ftp_chdir($fn, $object->rm_directory);
	}
	$ret = ftp_fput($fn, $uploadfilename, $fp, FTP_BINARY);
	if (!$ret) {
		$p = error_get_last();
		log_log("ftp_error", $p);
		throw new lxException('could_not_upload_file', '', $object->ftp_server);
	}
	fclose($fp);
	if ($object->isOn('no_local_copy_flag')) {
		lunlink($file);
	}

}

static function initThisObjectRule($parent, $class, $name = null)
{
	return $parent->getClName();
}

}


