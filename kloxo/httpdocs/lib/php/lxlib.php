<?php

include_once "lib/html/linuxlib.php";
include_once "lib/linuxproglib.php";

// Don't remove this. This is used for slave upgrade.
function remotetestfunc()
{
}

define('S_IFDIR', 00040000);
define('S_ISUID', 00004000);
define('S_ISGID', 00002000);

// This is the only function that exectues during the initialization... 
// The rest of the whle library exists as functions that can be called... 
// Nothing gets executed on their own... Execept this.. 
// So it makes this sort of special... very special..

init_global();

function init_global()
{
	global $gbl, $sgbl, $ghtml;
	global $g_demo;

	$sgbl = new Sgbl();
	$gbl = new Gbl();
	$gbl->get();

//
// Turn on demo version by putting a empty file called demo in the etc dir
//
	if (lfile_exists("__path_program_etc/demo")) {
		$g_demo = 1;
	}
//
// ### LxCenter
//

// Check for Development/Debug version
// If file not exists, Production mode (-1)
// If file exists it can have the following numbers to enable
// 1  = Debug mode 1
// 2  = Debug mode 2
// 3  = Debug mode 3
// 4  = Debug mode 4
// 5  = Debug mode 5
// -1 = Turn Off and go to production mode

	check_for_debug("/commands.php");

// Disabled by LxCenter, we are not at PHP version with number 1 at postition N ( x.N.x )
//  $v = explode(".", PHP_VERSION);
//  if ($v[1] == "1" && $sgbl->isDebug()) {
//      date_default_timezone_set("UTC");
//  }

	$sgbl->method = ($sgbl->dbg >= 1) ? "get" : "post";
}

function debug_for_backend()
{
	global $gbl, $sgbl, $login, $ghtml;
	check_for_debug("/commands.php");
	if ($sgbl->isDebug()) {
		return;
	}
	check_for_debug("/backend.php");
}

function check_for_debug($file)
{
	global $gbl, $sgbl, $login, $ghtml;
	if (file_exists(getreal($file))) {
		// MR -- fix if file have LF
		$content = file_get_contents(getreal($file));
		$sgbl->dbg = $content[0];
		if ($sgbl->dbg != "1" && $sgbl->dbg != "2" && $sgbl->dbg != "3" && $sgbl->dbg != "4" && $sgbl->dbg != "5") {
			$sgbl->dbg = -1;
		}
	} else {
		$sgbl->dbg = -1;
	}
	if ($sgbl->dbg > 0) {
		ini_set("error_reporting", E_ALL & ~E_STRICT);
		ini_set("display_errors", "On");
		ini_set("log_errors", "On");
	} else {
		ini_set("error_reporting", E_ERROR);
		ini_set("display_errors", "Off");
		ini_set("log_errors", "On");
	}
}

function isUpdating()
{
	return lx_core_lock_check_only("update.php");
}

class lxException extends Exception
{
	public $syncserver;
	public $class;
	public $variable;
	public $error;
	public $message;

	function getClass()
	{
		return lget_class($this);
	}

	function __construct($message, $variable = 'nname', $value = null)
	{
		// MR - as array if more then 1 declare in language files (example: for throw)
		if (is_array($message)) {
			$message = $message[0];
		}

		$this->message = $message;
		$this->variable = $variable;
		$this->value = $value;
		$this->__full_message = "$message: $variable: $value";
		//log_log("exception", "$message: $variable: $value");
	}

	function getlMessage()
	{
		return "$this->message: $this->variable: $this->value";
	}
}

function getAllOperatingSystemDetails()
{
	$ret = findOperatingSystem();
	$ret['loadavg'] = os_getLoadAvg();
	dprintr($ret);
	return $ret;
}

function findOperatingSystem($type = null)
{
	if (file_exists("/etc/fedora-release")) {
		$ret['os'] = 'fedora';
		$ret['version'] = file_get_contents("/etc/fedora-release");
		$ret['pointversion'] = find_os_pointversion();
	} else if (file_exists("/etc/redhat-release")) {
		$ret['os'] = 'rhel';
		$ret['version'] = file_get_contents("/etc/redhat-release");
		$ret['pointversion'] = find_os_pointversion();
	}

/* -- not work because no driver for centos inside /usr/local/lxlabs/kloxo/file/conf

	$ret['os'] = find_os_distro();
	$ret['version'] = find_os_release();
	$ret['pointversion'] = find_os_pointversion();
*/
	if (lxfile_exists("__path_program_etc/install_xen") || lxfile_exists("/proc/xen")) {
		$ret['vpstype'] = "xen";
		$ret['xenlocation'] = vg_complete();
	}

	if ($type) {
		return $ret[$type];
	}

	return $ret;
}

function find_os_distro()
{
	return find_os_selecttype('distro');
}

function find_os_release()
{
	return find_os_selecttype('release');
}

function find_os_pointversion()
{
/*
	if (file_exists("/etc/fedora-release")) {
		$release = trim(file_get_contents("/etc/fedora-release"));
		$osv = explode(" ", $release);
		if (strtolower($osv[1]) === 'core') {
			$osversion = "fedora-" . $osv[3];
		} else {
			$osversion = "fedora-" . $osv[2];
		}
		return $osversion;
	}

	if (file_exists("/etc/redhat-release")) {
		$release = trim(file_get_contents("/etc/redhat-release"));
		$osv = explode(" ", $release);
		if (isset($osv[6])) {
			$osversion = "rhel-" . $osv[6];
		} else {
			$oss = explode(".", $osv[2]);
			$osversion = "centos-" . $oss[0];
		}
		return $osversion;
	}
*/
	return find_os_selecttype('pointversion');
}

function find_os_selecttype($select)
{
	// list os support
	$ossup = array('redhat' => 'rhel', 'fedora' => 'fedora', 'centos' => 'centos');
	
	foreach(array_keys($ossup) as $k) {
		$osrel = file_get_contents("/etc/{$k}-release");
		
		if ($osrel) {
				if ($select === 'release') {
					return $osrel;
				}
				
				$osrel = strtolower(trim($osrel));
				
				break;
		}
	}
	
	// specific for 'red hat'
	$osrel = str_replace('red hat', 'redhat', $osrel);

	$osver = explode(" ", $osrel);

	$verpos = sizeof($osver) - 2;

	if (array_key_exists($osver[0], $ossup)) {
		// specific for 'red hat'
		if ($osrel === 'redhat') {
			$oss = $osver[$verpos];
		}
		else {
			$mapos = explode(".", $osver[$verpos]);
			$oss = $mapos[0];
		}

		if ($select === 'distro') {
			return $ossup[$osver[0]];
		}
		else if ($select === 'pointversion') {
			return $ossup[$osver[0]]."-".$oss;
		}
	}
}

function lscandir_without_dot($arg, $dotflag = false)
{
	$list = lscandir($arg);

	if (!$list) {
		return $list;
	}

	foreach ($list as $k => $v) {
		if ($v === ".." || $v === "." || $v === '.svn') {
			unset($list[$k]);
		}
		if ($dotflag && csb($v, '.')) {
			unset($list[$k]);
		}
	}
	return $list;
}

function lscandir_without_dot_or_underscore($arg, $dotflag = false)
{
	$list = lscandir($arg);

	if (!$list) {
		return $list;
	}

	foreach ($list as $k => $v) {
		if ($v === ".." || $v === "." || $v === '.svn') {
			unset($list[$k]);
		}
		if ($dotflag && csb($v, '.')) {
			unset($list[$k]);
		}
		if (csb($v, "__")) {
			unset($list[$k]);
		}
	}
	return $list;
}

function lscandir($arg)
{
	return lx_redefine_func("scandir", $arg);
}

function lunlink($arg)
{
	return lx_redefine_func("unlink", $arg);
}

function ltouch($arg)
{
	return lx_redefine_func("touch", $arg);
}

function lchdir($arg)
{
	return lx_redefine_func("chdir", $arg);
}

function takeToStartOfLine($fp)
{
	while (fgetc($fp) == "\n" && ftell($fp) != 1 && ftell($fp) != 0) {
		fseek($fp, -2, SEEK_CUR);
	}

	while (($c = fgetc($fp)) != "\n" && ftell($fp) != 1 && ftell($fp) != 0) {
		fseek($fp, -2, SEEK_CUR);
	}
}

function tail_func($file, $lines)
{
	$fp = fopen($file, "r");

	if (!$fp) {
		return null;
	}

	dprint("in Tail Func\n");

	fseek($fp, 0, SEEK_END);

	// Go back onece and read the line.
	takeToStartOfLine($fp);
	$arr[] = fgets($fp);

	$n = 0;
	while ($n < $lines && ftell($fp) !== 1) {
		$n++;
		//dprint($n . "\n");
		// You have to go back twice.
		print(' Before: ' . ftell($fp) . "\n");
		fseek($fp, -3, SEEK_CUR);
		takeToStartOfLine($fp);
		fseek($fp, -3, SEEK_CUR);
		print(' aaFter: ' . ftell($fp) . "\n");
		takeToStartOfLine($fp);
		print(' aaaaFter: ' . ftell($fp) . "\n");
		$arr[] = fgets($fp);
	}
	return implode("", array_reverse($arr));
}

function lfile_get_json_unserialize($file)
{
	if (!lxfile_exists($file)) {
		return null;
	}
	return json_decode(lfile_get_contents($file), true);
}

function lfile_put_json_serialize($file, $var)
{
	return lfile_put_contents($file, json_encode($var));
}

function lfile_get_unserialize($file)
{
	if (!lxfile_exists($file)) {
		return null;
	}
	return unserialize(lfile_get_contents($file));
}

function lfile_put_serialize($file, $var)
{
	return lfile_put_contents($file, serialize($var));
}

function lfile_get_contents($arg)
{
	return lx_redefine_func("file_get_contents", $arg);
}

function lrename($arg1, $arg2)
{
	return lx_redefine_func("rename", $arg1, $arg2);
}

function lfopen($arg1, $arg2)
{
	return lx_redefine_func("fopen", $arg1, $arg2);
}

function lfilesize($arg)
{
	return lx_redefine_func("filesize", $arg);
}

function ltempnam($arg1, $arg2)
{
	return lx_redefine_func("tempnam", $arg1, $arg2);
}

function lfile_write_content($file, $data, $user)
{
	if (csa($user, ":")) {
		$realuser = strtil($user, ":");
	} else {
		$realuser = $user;
	}

	if (!check_file_if_owned_by($file, $realuser)) {
		return;
	}
	lfile_put_contents($file, $data);
	lxfile_unix_chown($file, $user);
}

function check_file_if_owned_by_and_throw($filename, $username)
{
	global $login;

	if (!check_file_if_owned_by($filename, $username)) {
		throw new lxException($login->getThrow('file_exists_not_owned'), '', $filename);
	}
}

function lis_hardlink($file)
{
	$file = expand_real_root($file);
	if (is_dir($file)) {
		return false;
	}
	$stat = stat($file);
	if ($stat['nlink'] >= 2) {
		return true;
	}
	return false;
}

function is_soft_or_hardlink($file)
{
	if (!lxfile_exists($file)) {
		return false;
	}

	if (lis_link($file) || lis_hardlink($file)) {
		return true;
	}
	return false;
}

function new_process_mv_rec($user, $src, $dst)
{
	$src = expand_real_root($src);
	$dst = expand_real_root($dst);
	new_process_cmd($user, null, "'mv' -f \"$src\" \"$dst\"");
}

function new_process_chmod_rec($user, $file, $perm)
{
	$file = expand_real_root($file);
	$cmd = "chmod -R $perm \"$file\"";
	new_process_cmd($user, null, $cmd);
}

function new_process_cp_rec($user, $src, $dst)
{
	$src = expand_real_root($src);
	$dst = expand_real_root($dst);
	$cmd = "'cp' -a \"$src\" \"$dst\"";
	new_process_cmd($user, null, $cmd);
}

function new_process_cmd($user, $dir, $cmd)
{
	global $sgbl;

	if (csa($user, ':')) {
		list($user, $group) = explode(':', $user);
	} else {
		$group = $user;
	}

	if ($user === 'root') {
		$user = '__system__';
	}

	if ($dir) {
		$olddir = getcwd();
		chdir($dir);
	}
	if ($user !== '__system__') {
		$uid = is_numeric($user) ? (int) $user : os_get_uid_from_user($user);
		$gid = is_numeric($group) ? (int) $group : os_get_gid_from_user($user);
		exec("{$sgbl->__path_php_path} {$sgbl->__path_program_root}/bin/phpexec.php $uid $gid $cmd 2>&1", $output, $retval);
	} else {
		exec("$cmd 2>&1", $output, $retval);
	}

	if ($dir) {
		chdir($olddir);
	}

	$output = implode("\n", $output);
	log_log('user_cmd', "($dir) $user $cmd $output");

	return $retval;
}

function lfile_put_contents($file, $data, $flag = null)
{
	$file = expand_real_root($file);

	if (is_soft_or_hardlink($file)) {
		log_log("link_error", "$file is hard or symlink. Not writing\n");

		return;
	}

	if (char_search_a($data, "__path_")) {
		dprint("Warning : Trying to write __path into a file $file: $data <br> \n", 3);
	}

	lxfile_mkdir(dirname($file));

	if (file_exists($file)) {
		if (is_readable($file)){
			if(is_writable($file)) {
				return file_put_contents($file, $data, $flag);
			} else{
				$error_msg = 'Could not write the file \''.$file.'\' with permissions: '.substr(sprintf('%o', fileperms($file)), -4);
				dprint($error_msg);
				log_log('filesys', $error_msg);

				return false;
			}
		} else{
			$error_msg = 'Could not read the file \''.$file.'\' with permissions: '.substr(sprintf('%o', fileperms($file)), -4);
			dprint($error_msg);
			log_log('filesys', $error_msg);

			return false;
		}
	} else {
		if (file_put_contents($file, $data, $flag) === false){
			$error_msg = 'File \''.$file.'\' could not be created.';
			dprint($error_msg);
			log_log('filesys', $error_msg);

			return false;
		}

		return true;
 	}
}

/**
 * @return void
 * @param unknown
 * @param unknown
 * @desc Redefining php functions ... sort of.. Stupid php doesn't allow that. So we do the next best thing.. We add an 'l' to all system functions and then use these functions instead of the php ones... In a way, is a better idea too, since, there might always be some cases where we might want to override this crap. :-)
 */
function lmkdir($dir)
{
	return lx_redefine_func("mkdir", $dir);
}

function lis_executable($file)
{
	return lx_redefine_func("is_executable", $file);
}

function lis_readable($file)
{
	return lx_redefine_func("is_readable", $file);
}

function lreadlink($file)
{
	return lx_redefine_func("readlink", $file);
}

function lis_link($file)
{
	return lx_redefine_func("is_link", $file);
}

function lis_dir($file)
{
	return lx_redefine_func("is_dir", $file);
}

function cp_if_not_exists($src, $dst)
{
	if (lxfile_exists($dst)) {
		return;
	}
	if (!lxfile_exists($src)) {
		return;
	}
	lxfile_cp($src, $dst);
}

function cp_rec_if_not_exists($src, $dst)
{
	if (lxfile_exists($dst)) {
		return;
	}
	if (!lxfile_exists($src)) {
		return;
	}
	lxfile_cp_rec($src, $dst);
}

function mv_rec_if_not_exists($src, $dst)
{
	if (lxfile_exists($dst)) {
		return;
	}
	if (!lxfile_exists($src)) {
		return;
	}
	lxfile_mv_rec($src, $dst);
}

function llstat($file)
{
	return lx_redefine_func("lstat", $file);
}

function lx_merge_good($arg)
{
	global $gbl, $sgbl, $login, $ghtml;

	$start = 0;
	$transforming_func = null;

	if (version_compare(PHP_VERSION, '5.3.0', '<')) {
		eval($sgbl->arg_getting_string);
	} else {
	//	$arglist = get_function_arglist($start, $transforming_func);

		$arglist = array();

		for ($i = $start; $i < func_num_args(); $i++) {
			$arglist[] = func_get_arg($i);
		}

	}

	//dprintr($arglist);

	$list = $arglist;

	foreach ($list as &$l) {
		if (!$l) {
			$l = array();
		}
	}

	$ret = array();
	foreach ($list as $nl) {
		//dprintr($nl);
		if (is_array($nl)) {
			$ret = array_merge($ret, $nl);
		} else {
			$ret[] = $nl;
		}
	}
	return $ret;
}

function lx_array_merge($list)
{
	foreach ($list as &$l) {
		if (!$l) {
			$l = array();
		}
	}
	$ret = array();
	foreach ($list as $nl) {
		//dprintr($nl);
		if (is_array($nl)) {
			$ret = array_merge($ret, $nl);
		} else {
			$ret[] = $nl;
		}
	}
	return $ret;
}

function log_switch($mess, $id = 1)
{
	log_log('switch', $mess, $id);
}

function log_error($mess, $id = 1)
{
	log_log('error', $mess, $id);
}

function log_bdatabase($mess, $id = 1)
{
	log_log('bdatabase', $mess, $id);
}

function log_restore($mess, $id = 1)
{
	log_log('restore', $mess, $id);
}

function log_database($mess, $id = 1)
{
	log_log('database', $mess, $id);
}

function myPcntl_reaper()
{
	pcntl_wait($status, WNOHANG);
}

function myPcntl_wait()
{
	pcntl_wait($status);
}

function myPcntl_fork()
{
	global $gbl, $sgbl, $login, $ghtml;

	$pid = pcntl_fork();

	return $pid;
}

function log_log($file, $mess, $id = null)
{
	if (!is_string($mess)) {
		$mess = var_export($mess, true);
	}

	$mess = trim($mess);

	$rf = "__path_program_root/log/$file";

	lfile_put_contents($rf, @ date("H:i M/d/Y") . ": $mess" . PHP_EOL, FILE_APPEND);
}

function log_cleanup($mess, $nolog = null)
{
	// Function used in cleanup/upcp process
	//
	// logs to the file update and print to screen

	if (!is_string($mess)) {
		$mess = var_export($mess, true);
	}
	$mess = trim($mess);

	print( $mess . "\n" );

	if (!$nolog) {
		$rf = "__path_program_root/log/update";
		lfile_put_contents($rf, @ date("H:i M/d/Y") . ": $mess" . PHP_EOL, FILE_APPEND);
	}
}

function log_ajax($mess, $id = 1)
{
	log_log('ajax', $mess, $id);
}

function log_redirect($mess, $id = 1)
{
	log_log('redirect_error', $mess, $id);
}

function log_message($mess, $id = 1)
{
	log_log('message', $mess, $id);
}

function log_security($mess, $id = 1)
{
    // get IP
	if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
    else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

	$user_agent = $_SERVER["HTTP_USER_AGENT"];
	
	if (empty($_SERVER["HTTP_USER_AGENT"])) {
		$user_agent = "Not a browser";
	}

	log_log('security', $mess . " IP: $ip; User agent: $user_agent", $id);
}

function log_filesys_err($mess, $id = 1)
{
	log_log('filesyserr', $mess, $id);
}

function log_filesys($mess, $id = 1)
{
	global $global_dontlogshell;
	if ($global_dontlogshell) {
		log_log('nonfilesys', $mess, $id);
	} else {
		log_log('filesys', $mess, $id);
	}
}

function log_shell($mess, $id = 1)
{
	log_log('shell_exec', $mess, $id);
}

function log_shell_error($mess, $id = 1)
{
	log_log('shell_error', $mess, $id);
}

function lfile_trim($arg)
{
	$list = lfile($arg);

	foreach ($list as &$s) {
		$s = trim($s);
	}

	return $list;
}

function lcopy($src, $dst)
{
	return lx_redefine_func("copy", $src, $dst);
}

function lreadfile($file)
{
	return lx_redefine_func("readfile", $file);
}

function lmd5_file($file)
{
	return lx_redefine_func("md5_file", $file);
}

function lfile_exists($file)
{
	return lx_redefine_func("file_exists", $file);
}

function lsqlite_open($file)
{
	return lx_redefine_func("sqlite_open", $file);
}

/**
 * @return void
 * @param unknown
 * @param unknown
 * @desc This function is the core of the the path abstraction. It converts the paths of the form '__path.../dir' to '$sgbl->__path.../dir'. This is used in all the redefined functions to convert their arguments.
 */
function expand_real_root($root)
{
	global $gbl, $sgbl, $login, $ghtml;
	if (char_search_beg($root, "__path")) {
		if (char_search_a($root, "/")) {
			$var = substr($root, 0, strpos($root, "/"));
			$root = $sgbl->$var . "/" . substr($root, strpos($root, "/") + 1);
		} else {
			$root = $sgbl->$root;
		}
	}
	$root = remove_extra_slash($root);
	return $root;
}

/**
 * @return void
 * @param unknown
 * @param unknown
 * @desc the function that does the redefining of fundamental file access functions. Note the double use of 'eval'. hey hey, nothing is impossible in php. Never ever repeat the code; Make it unreadable instead. :-)
 */
function kill_and_save_pid($name)
{
	kill_pid($name);
	usleep(100);
	save_pid($name);
}

function save_pid($name)
{
	lfile_put_contents("__path_program_root/pid/$name.pid", os_getpid());
}

function kill_pid($name)
{
	$pid = lfile_get_contents("__path_program_root/pid/$name.pid");
	os_killpid($pid);
}

/************************************
 * HOW-TO GENERATE KEYPAIRS
 * //Passphrase is helloworld
 * $ openssl req -x509 -newkey rsa:1024 -keyout mykey.key -out mycert.crt
 */

function licenseDecrypt($license_content)
{
	global $gbl, $sgbl, $login, $ghtml;

	$fp = lfopen("__path_program_root/file/lprogram.crt", "r");
	$public_key = fread($fp, 8192);
	fclose($fp);

	openssl_get_publickey($public_key);

	$list = explode("\n", $license_content);

	// decrypt
	print_time('decrypt');
	$fullstring = null;
	foreach ($list as $l) {
		$l = trim($l);
		if (!$l) {
			continue;
		}
		$decrypted_string = null;
		openssl_public_decrypt(base64_decode($l), $decrypted_string, $public_key);
		//dprintr($decrypted_string . " $l<br> \n");
		$fullstring .= $decrypted_string;
	}
	print_time('decrypt', "Time Taken For decrypt:", 3);
	return $fullstring;
}

function lx_redefine_func($func)
{
	global $gbl, $sgbl, $login, $ghtml;

	$start = 1;
	$transforming_func = "expand_real_root";

	if (version_compare(PHP_VERSION, '5.3.0', '<')) {
		eval($sgbl->arg_getting_string);
	} else {
	//	$arglist = get_function_arglist($start, $transforming_func);

		$arglist = array();

		for ($i = $start; $i < func_num_args(); $i++) {
				$arglist[] = $transforming_func(func_get_arg($i));
		}
	}

	return call_user_func_array($func, $arglist);
}

function licenseEncrypt($string)
{
	global $gbl, $sgbl, $login, $ghtml;
	$file = "$sgbl->__path_program_root/file/license_privatekey.key";
	// encrypt
	$pass = "helloworld";
	$result = openssl_get_privatekey(array("file://" . $file, $pass));
	$ar = str_split($string, 100);
	$fullstring = null;
	foreach ($ar as $s) {
		openssl_private_encrypt($s, $encrypted_string, $result);
		$fullstring .= base64_encode($encrypted_string) . "\n";
	}
	print(openssl_error_string());

	return $fullstring;
}

function remove_unnecessary_stat(&$stat)
{
	foreach ($stat as $k => $v) {
		if (is_numeric($k)) {
			unset($stat[$k]);
		}
	}
}

function getShellCommand($cmd, $arglist)
{
	global $gbl, $sgbl, $login, $ghtml;
	$args = null;
	$q = $sgbl->__var_quote_char;
	$cmd = expand_real_root($cmd);
	$cmd = str_replace(";", "", $cmd);
	$cmd = "{$q}$cmd{$q}";

	foreach ($arglist as $a) {
		if ($a === "") {
			continue;
		}
		if (is_array($a)) {
			foreach ($a as $aa) {
				$aa = str_replace(";", "", $aa);
				$args .= " $q" . expand_real_root($aa) . "$q";
			}
		} else {
			$a = str_replace(";", "", $a);
			$args .= " $q" . expand_real_root($a) . "$q";
		}
	}
	$cmd .= " " . $args;
	return $cmd;
}

class Remote
{
	/*
 public $ddata;
 public $message;
 public $exception;
 */
}

function dprintoa($var, $type = 0)
{
	global $sgbl, $login, $ghtml;
	if ($type > $sgbl->dbg) {
		return;
	}
	if (!is_array($var)) {
		return;
	}
	foreach ($var as $k => $v) {
		dprint("$k =>");
		dprinto($var, $type);
		dprint("\n");
	}
}

function dprinto($var, $type = 0)
{
	global $sgbl;

	if ($type > $sgbl->dbg) {
		return;
	}

	if (!is_object($var)) {
		return;
	}

	$newob = clone($var);
	$newob->__parent_o = null;
	dprintr($newob);
}

function dprintr($var, $type = 0)
{
	global $sgbl;

	if ($type > $sgbl->dbg) {
		return;
	}

	if (is_object($var) && method_exists($var, "clearChildrenAndParent")) {
		$newvar = myclone($var);
		lxclass::clearChildrenAndParent($newvar);
		$newvar->driverApp = 'unset for printing';
		$newvar->__parent_o = 'unset for printing';

		$class = $newvar->get__table();
		
		if (csb($class, "sp_")) {
			$bclass = strfrom($class, "sp_") . "_b";
			$newvar->$bclass->__parent_o = 'unset for printing';
		}
	} else {
		$newvar = $var;
	}

	if ($sgbl->__running_in_cli) {
		print("\n");
	} else {
		print("<pre>");
	}

	print_r($newvar);

	if ($sgbl->__running_in_cli) {
		print("\n");
	} else {
		print("</pre>");
	}
}

function dprint($var, $type = 0)
{
	global $sgbl;

	$isCliMode = is_cli();
	
	if ($type <= $sgbl->dbg) {
		if (is_string($var)) {
			if ($sgbl->__running_in_cli) {
				print("\n");
			} else {
				print("<pre>");
			}

			print($var);
			
			if ($sgbl->__running_in_cli) {
				print("\n");
			} else {
				print("</pre>");
			}
		}
	}
}

function dprint_r($var, $type = 0)
{
	global $sgbl;
	
	if ($type <= $sgbl->dbg) {
		if ($sgbl->__running_in_cli) {
			print("\n");
		} else {
			print("<pre>");
		}

		print_r($var);

		if ($sgbl->__running_in_cli) {
			print("\n");
		} else {
			print("</pre>");
		}
	}
}

function xprint($var)
{
	global $sgbl;

	if ($sgbl->__running_in_cli) {
	//	print("\n---begin xprint_r---\n");
		print("\n");
	} else {
	//	print("<pre>---begin xprint_r---\n");
		print("<pre>");
	}

	if (is_object($var) && method_exists($var, "clearChildrenAndParent")) {
		$newvar = myclone($var);
		lxclass::clearChildrenAndParent($newvar);
		$newvar->driverApp = 'unset for printing';
		$newvar->__parent_o = 'unset for printing';

		$class = $newvar->get__table();
		
		if (csb($class, "sp_")) {
			$bclass = strfrom($class, "sp_") . "_b";
			$newvar->$bclass->__parent_o = 'unset for printing';
		}
		
		print_r($newvar);
	} else {
		if (is_array($var)) {
			print_r($var);
		} else {
			print($var);
		}
	}

	if ($sgbl->__running_in_cli) {
	//	print("\n---end xprint_r---\n");
		print("\n");
	} else {
	//	print("\n---end xprint_r---</pre>");
		print("</pre>");
	}
}

function get_xprint($var)
{
	ob_start();
	eval(xprint($var));
	$x = ob_get_contents();
	ob_end_clean();

	return $x;
}

function lx_local_socket_read($socket)
{
	return @ socket_read($socket, 2048);
	//$res=socket_recv($MsgSock,$buffer,1024,0);
}

function csa($haystack, $needle, $insensitive = 0)
{
	return char_search_a($haystack, $needle, $insensitive);
}

function char_search_a($haystack, $needle, $insensitive = 1)
{

	if (is_array($haystack)) {
		//dprint("Got array in Char Search ");
		//dprintr($haystack);
	}

	if (is_object($haystack)) {
		$v = debugBacktrace(true);
		log_log("error", $v);
	}
	if ($insensitive) {
		return (false !== stristr($haystack, $needle)) ? true : false;
	} else {
		return (false !== strpos($haystack, $needle)) ? true : false;
	}
}

function strtil($string, $needle)
{
	if (strrpos($string, $needle) !== false) {
		return substr($string, 0, strrpos($string, $needle));
	} else {
		return $string;
	}
}

function strtilfirst($string, $needle)
{
	if (strpos($string, $needle)) {
		return substr($string, 0, strpos($string, $needle));
	} else {
		return $string;
	}
}

function strfrom($string, $needle)
{
	if (!csa($string, $needle)) {
		return $string;
	}
	return substr($string, strpos($string, $needle) + strlen($needle));
}

function array_push_unique($array, $value)
{
	if (!$array) {
		$array = array();
	}
	foreach ($array as $var) {
		if ($var === $value) {
			return $array;
		}
	}
	$array[] = $value;
	return $array;
}

function array_remove($array, $element)
{
	$ret = null;
	foreach ($array as $value) {
		if ($value !== $element) {
			$ret[] = $value;
		}
	}
	return $ret;
}

function csb($haystack, $needle, $insensitive = 1)
{ # Char Search Begin
	return char_search_beg($haystack, $needle, $insensitive);
}

function char_search_beg($haystack, $needle)
{
	if (is_array($haystack)) {
		//debugBacktrace();
	}
	if (strpos($haystack, $needle) === 0) {
		return true;
	}
	return false;
}

function cse($haystack, $needle, $insensitive = 1)
{ # Char Search End
	return char_search_end($haystack, $needle, $insensitive);
}

function char_search_end($haystack, $needle, $insensitive)
{
	if (strpos($haystack, $needle) === false) {
		return false;
	}

	if ((strrpos($haystack, $needle) + strlen($needle)) === strlen($haystack)) {
		return true;
	} else {
		return false;
	}
}

function array_search_bool($needle, $haystack, $str=false)
{
	if (!$haystack) {
		return false;
	}

	if(!is_array($haystack)) {
		return false;
	}

	if (array_search($needle, $haystack, $str) !== false) {
		return true;
	}

	return false;
}

function isLicensed($var)
{
	global $gbl, $sgbl, $login, $ghtml;

	if ($var == 'lic_client') {
		return true;
	}

	$lic = $login->getObject('license')->licensecom_b;
	if (!isset($lic->$var)) {
		return false;
	}
	return isOn($lic->$var);
}

function is_composite($class)
{
	return false;
	return csa($class, "__");
}

function get_composite($class)
{
	return array(null, null, $class);

	$list = explode("__", $class);

	if (count($list) === 2) {
		return array($list[0], null, $list[1]);
	}

	return array($list[0], $list[1], $list[2]);
}

function setLicenseTodefault()
{
	global $gbl, $sgbl, $login, $ghtml;
	$license = $login->getObject('license');
	$license->parent_clname = $login->getClName();
	$lic = $license->licensecom_b;
	$def = array("maindomain_num" => "Unlimited", "vps_num" => "Unlimited", "pserver_num" => "Unlimited", "client_num" => "Unlimited");
	$list = get_license_resource();
	foreach ($list as $l) {
		$licv = "lic_$l";
		$lic->$licv = $def[$l];
	}
	$license->setUpdateSubaction();
	$license->write();
}

function decodeAndStoreLicense($ip, $license_content)
{
	global $gbl, $sgbl, $login, $ghtml;

	$license = $login->getObject('license');
	$license->parent_clname = $login->getClName();
	$lic = $license->licensecom_b;
	$get = licenseDecrypt($license_content);
	$license->text_license_content = $license_content;

	if (!$get) {
		throw new lxException($login->getThrow("could_not_decrypt_license"));
	}
	$get = 'licence.php?' . $get;
	$ghtml->get_post_from_get($get, $path, $post);

	if (!isset($post['maindomain_num'])) {
		$post['maindomain_num'] = $post['domain_num'];
	}

	if ($sgbl->isDebug()) {
		$post['maindomain_num'] = '1000';
	}

	foreach ($post as $k => $v) {
		$var = "lic_" . $k;
		$lic->$var = $v;
	}

	$lic->lic_ipaddress .= " ($ip)";

	$prilist = $login->getQuotaVariableList();

	foreach ($prilist as $k => $v) {
		if (cse($k, "_flag")) {
			$login->priv->$k = 'On';
		} else if (cse($k, "_usage")) {
			$login->priv->$k = 'Unlimited';
		} else if (cse($k, "_num")) {
			$login->priv->$k = 'Unlimited';
		}
	}

	$login->priv->client_num = $post['client_num'];

	if (isset($post['maindomain_num'])) {
		$login->priv->maindomain_num = $post['maindomain_num'];
	}

	if (isset($post['vps_num'])) {
		$login->priv->vps_num = $post['vps_num'];
	}

	$login->priv->pserver_num = $post['pserver_num'];
	$login->setUpdateSubaction();
	$login->write();

	$license->setUpdateSubaction();
	$license->write();
}

function remove_dot_dot($list)
{
	foreach ($list as $k => $v) {
		if ($v === "." || $v === "..") {
			unset($list[$k]);
		}
	}
	return $list;
}

function lx_tmp_file($file)
{
	global $gbl, $sgbl, $login, $ghtml;
	$file = expand_real_root($file);
	$n = preg_replace("+/+i", "_", $file);
	return tempnam("$sgbl->__path_tmp/", "lxtmp_$n");
	//return "/tmp/" . $n;
}

function lx_array_keys($list)
{
	if (!$list) {
		$list = array();
	}
	return array_keys($list);
}

function array_filter_key($full, $need)
{
	if (!$need) {
		return $full;
	}

	foreach ($full as $key => $value) {
		if (array_search_bool($key, $need)) {
			$ret[$key] = $value;
		}
	}
	return $ret;
}

function gethtmllibversion()
{
}

function isOn($var)
{
	return (($var === 'on') || ($var === 'On')) ? true : false;
}

// Function that is called to test whther the remote server is working fine or not. Used while adding.
function test_remote_func()
{
	return true;
}

function log_clicks($mess, $id = 1)
{
	global $gbl, $sgbl, $login, $ghtml;

	$ip = $gbl->c_session->ip_address;
	$mess = trim($mess);
	$file = "__path_program_root/log/clicks";
	lfile_put_contents($file, "$id: $ip: " . @date("H:i:s M/d/Y") . ": $mess\n", FILE_APPEND);
}

# Version Comparison Returns 1 if version1 is greater, and -1 if version2 is greater.
function version_cmp($version1, $version2)
{
	$l1 = explode(".", $version1);
	$l2 = explode(".", $version2);

	for ($i = 0; $i < 3; $i++) {
		if ($l2[$i] === $l1[$i]) {
			continue;
		}
		if ($l1[$i] > $l2[$i]) {
			return 1;
		}
		if ($l1[$i] < $l2[$i]) {
			return -1;
		}
	}
}

function app_version_cmp($version1, $version2)
{
	$l1 = explode(".", $version1);
	$l2 = explode(".", $version2);

	for ($i = 0; $i < 4; $i++) {
		if (!isset($l2[$i])) {
			$l2[$i] = 0;
		}
		if (!isset($l1[$i])) {
			$l1[$i] = 0;
		}
	}

	//dprintr($l1);
	//dprintr($l2);

	for ($i = 0; $i < 4; $i++) {
		if ($l2[$i] === $l1[$i]) {
			continue;
		}
		if ($l1[$i] > $l2[$i]) {
			return 1;
		}
		if ($l1[$i] < $l2[$i]) {
			return -1;
		}
	}
}

function fput_content_with_lock($file, $string)
{
	lfile_put_contents($file, $string);
}

function filter_object_list($list, $rule)
{
	$nlist = null;
	foreach ((array) $list as $o) {
		if ($o->eeval($rule)) {
			$nlist[$o->nname] = $o;
		}
	}
	return $nlist;
}

function is_assoc_array($var)
{
	if (!is_array($var)) {
		return false;
	}
	return array_keys($var) !== range(0, sizeof($var) - 1);
}

function get_namelist_from_objectlist($ol, $key = null, $val = null)
{
	if (!$ol) {
		return;
	}

	$name = array();
	if (!$key) {
		$key = "nname";
	}

	if ($val === null) {
		$val = $key;
	}

	foreach ($ol as $o) {
		if (!is_object($o)) {
			debugBacktrace();
		}
		$name[$o->$key] = $o->display($val);
	}
	return $name;
}

function convert_to_associate($ar)
{
	foreach ($ar as $k => $v)  $ret[$v] = $v;
	return $ret;
}

function get_namelist_from_arraylist($ol, $key = null, $val = null)
{
	$name = array();
	if (!$key) {
		$key = "nname";
	}

	if ($val === null) {
		$val = $key;
	}

	foreach ((array) $ol as $o) $name[$o[$key]] = $o[$val];

	return $name;
}

function isQuotaGreaterThanOrEq($used, $priv)
{
	if (is_unlimited($priv)) {
		return false;
	}
	if (is_unlimited($used)) {
		return true;
	}
	if (isOn($priv)) {
		return false;
	}
	if (isOn($used)) {
		return true;
	}
	return ($used >= $priv) ? true : false;
}

function isQuotaGreaterThan($used, $priv)
{
	if (is_unlimited($priv)) {
		return false;
	}
	if (is_unlimited($used)) {
		return true;
	}
	if (isOn($priv)) {
		return false;
	}
	if (isOn($used)) {
		return true;
	}
	return ($used > $priv) ? true : false;
}

function is_unlimited($val)
{
	if (strtolower($val) === 'unlimited' || strtolower($val) === 'na') {
		return true;
	}
	return false;
}

function if_demo_throw()
{
	global $login;

	if (if_demo()) {
		throw new lxException ($login->getThrow("demo"));
	}
}

function if_demo()
{
	global $gbl, $sgbl, $g_demo;
	return $g_demo;
}

function lx_phpdebug()
{
	global $gbl, $sgbl;

	if ($sgbl->dbg <= 0) {
		return;
	}
	if (!lfile_exists("/tmp/.php_debug")) {
		return;
	}

	$fp = lfopen("/tmp/.php_debug", "r");
	$s = fgets($fp, 1024);
	fclose($fp);

	$s = preg_replace("/--noraise /i", "", $s);
	$arr = parse_url($s);
	parse_str($arr['query'], $out);

	$ghtml->print_input("hidden", "start_debug", "1");
	$ghtml->print_input("hidden", "debug_port", $out['debug_port']);
	$ghtml->print_input("hidden", "debug_text_mode", "1");
	$ghtml->print_input("hidden", "debug_no_cache", "1095197145");
}

function create_simpleObject($arglist)
{
	$obj = new Remote();

	foreach ($arglist as $k => $v) {
		$obj->$k = $v;
	}
	return $obj;
}

function lx_sync()
{
	global $gbl, $sgbl, $login;
	$login->was();
}

function get_current_file()
{
	$n = basename(dirname($_SERVER['PHP_SELF']));
	return $n;
}

function array_flatten($a, $pref = null)
{
	$ret = array();
	foreach ($a as $i => $j) {
		if (is_array($j)) {
			$ret = array_merge($ret, array_flatten($j, "$pref$i"));
		} else {
			$ret["$pref$i"] = $j;
		}
	}
	return $ret;
}

function get_general_image_path($v = null)
{

	return add_http_host("/theme/general/$v");
}

/**
 * @return void
 * @param
 * @param
 * @desc  part of the getting-image-through-http (to enable caching) madness.
 */
function add_http_host($elem)
{
	global $gbl, $sgbl, $login, $ghtml;
	return $elem;

	$host = $_SERVER['SERVER_NAME'];
	//$port = $sgbl->__var_prog_port;
	//$host = "http://" . $host . ":" .  "$port";
	//$host = "https://" . $host . ":" .  "$port";
	return $host . $elem;
}

function get_image_path($path = null)
{
	global $gbl, $sgbl;

	global $gbl, $sgbl, $login;

	//Return path of the encrypted images in the deployment version.

	return "/theme/icon/{$login->getSpecialObject('sp_specialplay')->icon_name}/$path";
}

function randomString($length)
{
/*
	$randstr = '';

	$chars = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm',
		'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
		'0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C',
		'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P',
		'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

	for ($rand = 0; $rand <= $length; $rand++) {
		$random = rand(0, count($chars) - 1);
		$randstr .= $chars[$random];
	}

	return $randstr;
*/
	$key = '';

	$keys = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));

	for ($i = 0; $i < $length; $i++) {
			$key .= $keys[array_rand($keys)];
	}

	return $key;
}

function DBG_GetBacktrace($traceArr)
{
	if ($sgbl->dbg < 0) {
		return;
	}

	$s = '';
	$MAXSTRLEN = 64;

	$s = '<pre align=left>';
	array_shift($traceArr);
	$tabs = sizeof($traceArr) - 1;
	foreach ($traceArr as $arr) {
		for ($i = 0; $i < $tabs; $i++) {
			$s .= ' &nbsp; ';
		}

		$tabs -= 1;
		if (isset($arr['class'])) {
			$s .= $arr['class'] . '.';
		}
		$args = array();
		foreach ((array) $arr['args'] as $v) {
			if (is_null($v)) {
				$args[] = 'null';
			} else {
				if (is_array($v)) {
					$args[] = 'Array[' . sizeof($v) . ']';
				} else {
					if (is_object($v)) {
						$args[] = 'Object:' . get_class($v);
					} else {
						if (is_bool($v)) {
							$args[] = $v ? 'true' : 'false';
						} else {
							$v = (string) @$v;
							$str = htmlspecialchars(substr($v, 0, $MAXSTRLEN));
							if (strlen($v) > $MAXSTRLEN) $str .= '...';
							$args[] = "\"" . $str . "\"";
						}
					}
				}
			}
		}
		$s .= $arr['function'] . '(' . implode(',', $args) . ')';
		$Line = (isset($arr['line']) ? $arr['line'] : "unknown");
		$File = (isset($arr['file']) ? $arr['file'] : "unknown");
		$s .= sprintf("# line %4d, file: <a href=\"file:/%s\">%s</a>", $Line, $File, $File);
		$s .= "\n";
	}
	$s .= '</pre>';
	return $s;
}

function getInfo($b)
{
	if (is_object($b) && is_subclass_of($b, 'lxclass')) {
		return $b->get__table() . ':' . $b->nname;
	} else {
		return $b;
	}
}

function backtrace_once()
{
	global $gbl, $sgbl, $login, $ghtml;
	if ($sgbl->dbg < 2) {
		return;
	}

	$v = debug_backtrace();
	$count = 0;
	$string = null;
	foreach ($v as $q) {
		$count++;
		if ($count === 1) {
			continue;
		}

		if ($count > 2) break;
		if ($count === 2 && (basename($q['file']) === 'sqlitelib.php')) {
			return null;
			continue;
		}
		$string .= $q['file'] . ":" . $q['line'] . ": " . $q['function'] . '(';
		if (isset($q['args'])) {
			foreach ($q['args'] as $a) {
				if (is_array($a)) {
					foreach ($a as $b) {
						if (is_array($b)) {
							foreach ($b as $c) {
								$string .= ', ' . getInfo($c);
							}
						} else {
							$string .= ', ' . getInfo($b);
						}
					}
				} else {
					$string .= $a;
				}
			}
		}
		$string .= ")<br>\n";
	}

	return $string;
}

function debugBacktrace($flag = false)
{
	global $gbl, $sgbl, $login, $ghtml;
	$string = null;

	if ($sgbl->dbg < 2) {
		return;
	}
	$v = debug_backtrace();
	foreach ($v as $q) {
		$string .= $q['file'] . ":" . $q['line'] . ": " . $q['function'] . '(';
		if (isset($q['args'])) {
			foreach ($q['args'] as $a) {
				if (is_array($a)) {
					foreach ($a as $b) {
						if (is_array($b)) {
							foreach ($b as $c) {
								$string .= ', ' . getInfo($c);
							}
						} else {
							$string .= ', ' . getInfo($b);
						}
					}
				} else {
					if (is_string($a)) {
						$string .= $a;
					}
				}
			}
		}
		$string .= ")<br>\n";
	}
	if ($flag) {
		return $string;
	}

	dprintr($string);
}

function lx_strip_tags($str)
{
	$nstr = strip_tags($str);

	$nstr = preg_replace("/\s+/", " ", $nstr);
	return $nstr;
}

class Language_Mes
{

}

function get_language()
{
	global $gbl, $sgbl, $login, $ghtml;
	if (is_object($login) && isset($login->getSpecialObject('sp_specialplay')->language)) {
		$lan = $login->getSpecialObject('sp_specialplay')->language;
	} else {
		$lan = 'en';
	}

	// MR -- change en to en-us
	if ($lan === 'en') { $lan = 'en-us'; }

	return $lan;
}

function get_charset()
{
	$lang = get_language();
	$charset = @ lfile_get_contents("lang/$lang/charset");
	$charset = trim($charset);
	return $charset;
}

function print_meta_lan()
{
	global $gbl, $sgbl, $login, $ghtml;
	$lan = get_language();
	$charset = @ lfile_get_contents("lang/$lan/charset");
	$charset = trim($charset);
//	print("<head>");
	if ($charset) {
		print("<meta http-equiv=\"Content-Type\" content=\"text/html; charset=$charset\" />");
	} else {
		print("<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />");
	}
}

function init_language()
{
	global $gbl, $sgbl, $login, $ghtml;
	global $g_language_mes, $g_language_desc;

	$language = get_language();

	if (lxfile_exists("lang/$language/messagelib.php")) {
		include_once("lang/$language/messagelib.php");
	} else {
		include_once("lang/en-us/messagelib.php");
	}

	if (lxfile_exists("lang/$language/langfunctionlib.php")) {
		include_once("lang/$language/langfunctionlib.php");
	} else {
		include_once("lang/en-us/langfunctionlib.php");
	}

	if (lxfile_exists("lang/$language/langkeywordlib.php")) {
		include_once("lang/$language/langkeywordlib.php");
	} else {
		include_once("lang/en-us/langkeywordlib.php");
	}

	if (lxfile_exists("lang/$language/desclib.php")) {
		include_once("lang/$language/desclib.php");
	} else {
		include_once("lang/en-us/desclib.php");
	}

	$g_language_mes = new Language_Mes();
	$g_language_mes->__information = $__information;
	$g_language_mes->__emessage = $__emessage;
	$g_language_mes->__keyword = $__keyword;

/*
	$g_language_mes->__help = $__help;
	$g_language_mes->__helpvar = $__helpvar;
*/
	// __help and __helpvar until 6.1.6 doesn't exist, so make choice
	$g_language_mes->__help = (isset($__help)) ? $__help : '';
	$g_language_mes->__helpvar = (isset($__helpvar)) ? $__helpvar : '';

	$g_language_mes->__throw = $__throw;

	$g_language_desc = new Remote();
	$g_language_desc->__description = $__description;
}

function lx_error_handler($errno, $errstr, $file, $line)
{
	global $gbl, $sgbl, $login, $ghtml;
	global $last_error;

	$last_error = $errstr;

	if ($sgbl->dbg < 0) {
		return;
	}

	if (error_reporting() === 0) {
		return;
	}

	static $error = "";
	$pos = "$file:$line: $errstr";
	$error .= $pos . "\n";
	lfile_put_contents($sgbl->__var_error_file, $error);

	dprint("\n### PHP Error detected\n");
	dprint("### Notice: $errstr\n");
       dprint("### File:$file\n");
       dprint("### Line number: $line\n");
	dprint("### End PHP Error information\n\n");
}

/**
 * @return void
 * @param
 * @param
 * @desc Truly random and insane way to encrypt the image/form names so that hackers won't easily understand teh program structure, (which is clearly visible in these names...)
 */
function createEncName($name)
{
	global $gbl;

	return $name;

	if ($sgbl->dbg > 0) {
		return $name;
	}

	$name = str_replace("_", "", $name);
	$name = str_replace("php", "", $name);
	$name = str_replace("a", "r", $name);
	$name = str_replace("e", "z", $name);
	$name = str_replace("i", "q", $name);
	$name = str_replace("o", "j", $name);
	$name = str_replace("t", "y", $name);
	$name = str_replace("s", "x", $name);
	$name = str_replace("r", "p", $name);

	return $name;
}

function check_password($unenc, $enc)
{
	//Old Stuff Not reached
	if (crypt($unenc, $enc) === $enc) {
		return true;
	}

	return false;
}

function lx_exception_handler($e)
{
	global $gbl, $sgbl, $login, $ghtml;

	print("Error message: {$e->getMessage()} {$e->variable} {$e->value}\n");

	if ($sgbl->dbg <= 0) {
		return;
	}
	$tr = $e->getTrace();
	$trace = "";
	foreach ($tr as $a) {
		$trace .= $a["file"] . ":" . $a["line"] . ":";
		if (isset($a["class"])) {
			$trace .= $a["class"] . ":";
		}
		$trace .= $a["function"] . "(";
		$trace .= ")\n";
	}
	lfile_put_contents($sgbl->__var_error_file, $trace);
}

function check_raw_password($class, $client, $pass)
{
	//return true;

	if (!$class || !$client || !$pass) {
		return false;
	}

	// MR -- sanitize input
	if ((stripos($class, "'") !== false) || 
			(stripos($client, "'") !== false) || 
			(stripos($pass, "'") !== false)) {
		return false;
	}

	// MR -- sanitize input
	if ((stripos($class, "\"") !== false) || 
			(stripos($client, "\"") !== false) || 
			(stripos($pass, "\"") !== false)) {
		return false;
	}

	$rawdb = new Sqlite(null, $class);
	$password = $rawdb->rawquery("select password from $class where nname = '$client'");
	$enp = $password[0]['password'];

	if ($enp && check_password($pass, $enp)) {
		return true;
	}
	return false;
	//$rawdb->close();
}

/**
 * @return void
 * @param
 * @param
 * @desc  Checks if the client is disabled and exits immedeiately showing a message.
 */
function check_if_disabled_and_exit()
{
	global $gbl, $sgbl, $login, $ghtml;

	$contact = "administrator";

	if ($login->isAdmin()) { return; }

	if (!$login->isOn('cpstatus')) {

		Utmp::updateUtmp($gbl->c_session->nname, $login, 'disabled');
		$ghtml->print_css_source("/theme/css/common.css");

		if ($sgbl->isLxlabsClient()) {
			$ghtml->__http_vars['frm_emessage'] = "This login has been Disabled due to non-payment. Please pay the invoice below, and your account will automatically get enabled.";
			$ghtml->print_message();
			$login->print_invoice();
		} else {
			$ghtml->__http_vars['frm_emessage'] = "This login has been Disabled. Please contact the $contact";
			$ghtml->print_message();
		}

		$gbl->c_session->delete();
		$gbl->c_session->was();
		exit(0);

	}

}

function delete_expired_ssessions()
{
	global $gbl, $sgbl, $login, $ghtml;

	$s_l = $login->getList("ssessionlist");

	if(!empty($s_l)){
		foreach ($s_l as $s) {
			if (!is_object($s)) {
				continue;
			}
			$timeout = $s->last_access + $login->getSpecialObject('sp_specialplay')->ssession_timeout;
			dprint($s->nname);
			if ($timeout < time()) {
				$s->delete();
				Utmp::updateUtmp($s->nname, $login, "Session Expired");
			}
		}
	}
}

function createTreeObject($name, $img, $imgstr, $url, $open, $help, $alt)
{
	static $val;

	$imgstr = str_replace("'", "\'", $imgstr);
	$help = str_replace("'", "\'", $help);
	$alt = str_replace("'", "\'", $alt);
	$img = str_replace("'", "\'", $img);
	$val++;
	$name = $name . $val;
	$tobj = new Tree(null, null, $name);
	$tobj->img = $img;
	$tobj->imgstr = $imgstr;
	$tobj->url = $url;
	$tobj->open = $open;
	$tobj->help = $help;
	$tobj->alt = $alt;
	return $tobj;
}

/**
 * @return void
 * @param
 * @param
 * @desc A generic function, that can be used by all programs. Does all the basic login stuff.
 */
function initProgramlib($ctype = null)
{
	global $gbl, $sgbl, $login, $ghtml;

// /*
	// --- solution for some scripts running well in slave
	// REVERT - back to original
	if ($sgbl->is_this_slave()) {
		print("This is a Slave Server. Operate it at the Master server.\n");
		exit;
	}
// */
	static $var = 0;
	$var++;

	$progname = $sgbl->__var_program_name;
	lfile_put_contents($sgbl->__var_error_file, "");
	set_exception_handler("lx_exception_handler");
	//xdebug_disable();
	set_error_handler("lx_error_handler");

	//setcookie("XDEBUG_SESSION", "sess");

	if ($var >= 2) {
		dprint("initProgramlib called twice \n <br> ");
	}

	if ($ctype === 'superadmin') {
		$sgbl->__var_dbf = $sgbl->__path_supernode_db;
		$sgbl->__path_admin_pass = $sgbl->__path_super_pass;
		$sgbl->__var_admin_user = $sgbl->__var_super_user;
		$login = new SuperClient(null, null, 'superadmin', 'login', 'forced');
		$login->get();
		return;
	} else if ($ctype === "guest") {
		$login = new Client(null, null, "____________", "guest");
		$login->get();
		return;
	} else if ($ctype != "") {
		$login = new Client(null, null, $ctype, "login", "forced");
		$login->get();
		return;
	}

	$sessobj = null;
	if ($ghtml->frm_consumedlogin === 'true') {
		$clientname = $_COOKIE["$progname-consumed-clientname"];
		$classname = $_COOKIE["$progname-consumed-classname"];
		$session_id = $_COOKIE["$progname-consumed-session-id"];
		get_login($classname, $clientname);
		$login->__session_id = $session_id;
		$sessobj = $login->getObject('ssession');
	} else {
		if (isset($_COOKIE["$progname-session-id"])) {
			$clientname = $_COOKIE["$progname-clientname"];
			$classname = $_COOKIE["$progname-classname"];
			$session_id = $_COOKIE["$progname-session-id"];
			if ($classname === 'superclient') {
				$sgbl->__var_dbf = $sgbl->__path_supernode_db;
				$sgbl->__path_admin_pass = $sgbl->__path_super_pass;
				$sgbl->__var_admin_user = $sgbl->__var_super_user;
			}

			if ($classname === 'slave') {
				$sgbl->__var_dbf = $sgbl->__path_slave_db;
			}
			if ($classname) {
				get_login($classname, $clientname);
				$login->__session_id = $session_id;
				$sessobj = $login->getObject('ssession');
			}
		}
	}

	if (!$sessobj || $sessobj->dbaction === 'add') {
		if ($ghtml->frm_ssl) {
			$ssl = unserialize(base64_decode($ghtml->frm_ssl));
			$string = $ssl['string'];
			$ssl_param = $ssl['ssl_param'];
			$encrypted_string = base64_decode($ssl['encrypted_string']);
			if (!$string || !checkPublicKey($string, $encrypted_string)) {
				print("SSL Connection Failed <br> \n");
				exit;
			}
			$class = 'client';
			$clientname = 'admin';
			get_login($class, $clientname);
			do_login($class, $clientname, $ssl_param);
			$sessobj = $gbl->c_session;
			$sessobj->write();
			$sessobj->dbaction = 'clean';
		}
	}

	//get_savedlogin($classname, $clientname);
	//print_time('login_get', "Login Get");
	//dprintr($login);

   //avoid some php warnings
   if (isset($login)) {
      $gbl->client = $login->nname;
      $gbl->client_ttype = $login->cttype;
   }

	//dprintr($login->hpfilter);

	// This means the session object got created fresh.
	if (!$sessobj || $sessobj->dbaction === 'add') {
		dprint("Session id is empty. Clearing cookies and redirect to login.");
		clear_all_cookie();
		$ghtml->print_redirect_self("/login/");
	}

	$gbl->c_session = $sessobj;

	if ($login->getClName() !== $sessobj->parent_clname) {
		dprint_r($login->ssession_l);
		dprint(" <br> $session_id <br> <br> <br> ");
		print("Session error! Login again.");
		clear_all_cookie();
		$ghtml->print_redirect_self("/login/?frm_emessage=sessionname_not_client");
	}

	$gen = $login->getObject('general')->generalmisc_b;

	if (!$gen->isOn('disableipcheck') && $_SERVER['REMOTE_ADDR'] != $sessobj->ip_address) {
		$hostname = gethostbyaddr($_SERVER['REMOTE_ADDR']);
		log_message("An attempt to hack from $hostname (" . $_SERVER['REMOTE_ADDR'] . ") with sess $sessobj->nname, session ip: $sessobj->ip_address");

		if ($gen->isOn('disableipcheck')) {
		} else {
			clear_all_cookie();
			if ($gbl->c_session->ssl_param) {
				$sessobj->delete();
				$sessobj->write();
				$ghtml->print_redirect("{$gbl->c_session->ssl_param['backurl']}&frm_emessage=ipaddress_changed_amidst_session");
			} else {
				$ghtml->print_redirect_self("/login/?frm_emessage=ipaddress_changed_amidst_session");
			}
		}
	}

	if (intval($login->getSpecialObject('sp_specialplay')->ssession_timeout) <= 100) {
		$login->getSpecialObject('sp_specialplay')->ssession_timeout = 100;
		$login->setUpdateSubaction();
	}

	$timeout = $sessobj->last_access + $login->getSpecialObject('sp_specialplay')->ssession_timeout;
	$sessobj->timeout = $timeout;
	//$timeout  =  $sessobj->last_access + 4;
	$sessobj->last_access = time();
	$sessobj->setUpdateSubaction();

	if ($sessobj->auxiliary_id) {
		$aux = new Auxiliary(null, null, $sessobj->auxiliary_id);
		$aux->get();
		$login->__auxiliary_object = $aux;
	}

	if (time() > $timeout) {
		$sessobj->delete();
		$sessobj->write();
		//print("session error timeout");
		if ($gbl->c_session->ssl_param) {
			$ghtml->print_redirect("{$gbl->c_session->ssl_param['backurl']}&frm_emessage=session_timeout");
		} else {
			$ghtml->print_redirect_self("/login/?frm_emessage=session_timeout");
		}
	}

	addToUtmp($sessobj, 'update');
}

function clear_all_cookie()
{
	global $gbl, $sgbl, $login, $ghtml;
	$progname = $sgbl->__var_program_name;

	$search = $progname;
	if ($ghtml->frm_consumedlogin === 'true') {
		$search .= "-consumed";
	}

	foreach ($_COOKIE as $k => $v) {
		if (csb($k, $search)) {
			setcookie($k, "", time() - 360000000);
		}
	}
}

function checkPublicKey($string, $encrypted_string)
{
	$res = lfile_get_contents("__path_program_root/etc/authorized_keys");
	$public_key = $res;
	$pubkey_res = openssl_get_publickey($public_key);
	openssl_public_decrypt($encrypted_string, $decrypted_string, $public_key);
	if ($decrypted_string === $string) {
		return true;
	}

	return false;
}

function initSession($object, $ssl_param, $consuming_parent)
{
	global $gbl, $sgbl, $login, $ghtml;
	$progname = $sgbl->__var_program_name;

	$session = randomString(50);

	//clear_all_cookie();
	// Making cookie persistent, otherwise IE will not pass it to new windows. Needed in the file selector. Set the expiration to 10 years in the future. Needed for brain damaged ie, which cannot recognize server time.
	$cookietime = time() + 24 * 60 * 60 * 365 * 80;
	//$cookietime = 0;
	header('P3P: CP="CAO PSA OUR"');

	$ckstart = $progname;

	if ($consuming_parent) {
		$ckstart .= "-consumed";
	}

	if ($object->isAuxiliary()) {
		$name = $object->__auxiliary_object->nname;
		$class = $object->__auxiliary_object->getClass();
	} else {
		$name = $object->nname;
		$class = $object->getClass();
	}

	setcookie("$ckstart-clientname", $name, $cookietime, '/');
	setcookie("$ckstart-classname", $class, $cookietime, '/');
	setcookie("$ckstart-session-id", $session, $cookietime, '/');

	dprint("Set cookies<br/>");

	$hostname = $_SERVER['REMOTE_ADDR'];

	$sessobj = new Ssession(null, null, $session);
	$sessa['nname'] = $session;
	$sessa['ip_address'] = $_SERVER['REMOTE_ADDR'];
	$sessa['cttype'] = $object->getLoginType();
	$sessa['hostname'] = $hostname;
	$sessa['tsessionid'] = randomString(30);
	// Login time is set to null. This is set to the correct time inside display.php. This allows us to figure out the first run of display.php.
	$sessa['logintime'] = time();
	$sessa['ssession_vars'] = array();
	$sessa['http_vars'] = array();
	$sessa['parent_clname'] = $object->getClName();
	$sessa['consuming_parent'] = $consuming_parent;
	$sessa['auxiliary_id'] = $object->getAuxiliaryId();
	$sessa['ssl_param'] = $ssl_param;

	if (intval($object->getSpecialObject('sp_specialplay')->ssession_timeout) <= 100) {
		$timeout = 100;
	} else {
		$timeout = $object->getSpecialObject('sp_specialplay')->ssession_timeout;
	}

	$sessa['timeout'] = time() + $timeout;
	$sessa['last_access'] = time();
	$sessobj->create($sessa);
	return $sessobj;
}

function do_login($classname, $cgi_clientname, $ssl_param = null)
{
	global $gbl, $sgbl, $login, $ghtml;
	$url = "/display.php?frm_action=show";

	$progname = $sgbl->__var_program_name;

	if (!$classname) {
		$classname = 'client';
	}

	$sessobj = initSession($login, $ssl_param, null);

	$gbl->c_session = $sessobj;
	$login->addToList("ssession", $sessobj);
	$login->createSessionProperties();
	addToUtmp($sessobj, 'add');

	if ($ghtml->frm_extra_var) {
		$extra = unserialize(base64_decode($ghtml->frm_extra_var));
		$gbl->setSessionV('extra_var', $extra);
	}
	//This is not the way. You have to periodically scan the utmp and delete eveyrthing that had expired.
	delete_expired_ssessions();
}

function ifSplashScreen()
{
	global $gbl, $sgbl, $login, $ghtml;
	if (array_search_bool(strtolower($ghtml->frm_action), array('show', 'updateform', 'addform', 'continue'))) {
		return true;
	}
	return false;
}

function isModifyAction()
{
	global $gbl, $sgbl, $login, $ghtml;
	if (array_search_bool(strtolower($ghtml->frm_action), array('show', 'updateform', 'addform', 'continue', 'list')) || array_search_bool($ghtml->frm_subaction, array("download"))) {
		return false;
	}
	return true;
}

function lget_class($object)
{
	return strtolower(get_class($object));
}

function exit_programlib()
{
	global $gbl, $sgbl, $login, $ghtml;

	$refer = $gbl->getSessionV("lx_http_referer");
	$current_query_string = $ghtml->get_get_from_post(array('frm_ev_list'), $ghtml->__http_vars);
	$cur_url = $_SERVER['PHP_SELF'] . "?" . $current_query_string;

	if ($refer != $cur_url) {
		$gbl->setSessionV("lx_http_referer_parent", $refer);
		$gbl->setSessionV("lx_http_referer", $cur_url);
	}

	if ($ghtml->frm_hpfilter) {
		$gbl->setSessionV("lx_hpfilter", $ghtml->frm_hpfilter);
	}

	$gbl->setHistory();
	$gbl->c_session->was();
}

function getAllclients($cttype = null)
{
	$db = new Sqlite(null, 'client');

	$list = $db->getTable(array('nname', 'cttype'));

	return $list;
}

function delete_login()
{
	global $gbl, $sgbl, $login, $ghtml;
	$class = lget_class($login);
}

/**
 * @return void
 * @param
 * @param
 * @desc  Saves teh whole login info. Login is 'Was'ed; (Write and synced). Any exception is caught and the script is returned to the previous web page, with the error message string returned by the exception. Works EXceptionally well.. :-)
 */
function save_login()
{
	// Function removed by Ligesh earlier. (was saving login info)
	// Deleted the code after the first return.
	return;
}

function isLocalhost($var)
{
	if ($var && $var !== "localhost") {
		return false;
	}
	return true;
}

function get_savedlogin($classname, $clientname)
{
	global $gbl, $sgbl, $login, $ghtml;

	$path = "$sgbl->__path_program_etc/$classname:$clientname";

	if (file_exists($path)) {
		$login = unserialize(lfile_get_contents($path));
		if (!$login) {
			return 0;
		}

		$login->__parent_o = null;

		return 1;
	}
	return 0;
}

function get_login($classname, $clientname)
{
	global $gbl, $sgbl, $login, $ghtml;

	if (!$classname) {
		print("There is no class. Login Error\n");
		exit;
	}

	$object = new $classname(null, null, $clientname, 'login');

	$ret = $object->get();

	if ($classname === 'auxiliary') {
		$login = $object->getParentO();
		$login->__auxiliary_object = $object;
	} else {
		$login = $object;
	}

	$login->__parent_o = null;
	return $ret;
}

function create_name($word)
{
	$word = str_replace("_", $word);
	return $word;
}

/**
 * @return void
 * @param
 * @param
 * @desc  Remove unsavoury characters from a string so that it can be used as a variable.
 */
function fix_nname_to_be_variable($var)
{
	if (!$var) {
		return;
	}
	if (is_numeric($var[0])) {
		$var[0] = 'd';
	}
	$var = strtolower($var);
	return preg_replace("/[-\W ]/i", "_", $var);
}

function fix_nname_to_be_variable_without_lowercase($var)
{
	if (!$var) {
		return;
	}
	if (is_numeric($var[0])) {
		$var[0] = 'd';
	}
	return preg_replace("/[-\W ]/i", "_", $var);
}

function get_description($stuff)
{
	if (is_object($stuff)) {
		$class = lget_class($stuff);
	} else {
		$class = $stuff;
	}

	$descr = get_classvar_description($class);
	return $descr[2];
}

function get_classvar_description($class, $var = null)
{
	global $gbl, $sgbl, $login, $ghtml;

	global $g_language_desc;
	global $g_language_mes;

	//$var = fix_nname_to_be_variable($var);
	if (csb($var, "__")) {
		$dvar = $var;
	} else {
		if ($var) {
			$dvar = "__desc_$var";
		} else {
			$dvar = "__desc";
		}
	}

	$rvar = strfrom($dvar, "__desc_");
	$rvar = strfrom($rvar, "__acdesc_");

	$class = strtolower($class);
	$ret = get_real_class_variable($class, $dvar);

	if (!$ret) {
		return null;
	}
	$ret['help'] = $ret[2];
	if (cse($dvar, "_o") || cse($dvar, "_l")) {
		return $ret;
	}
	if (!$g_language_desc) {
		return $ret;
	}

/*
	 if ($login->getSpecialObject('sp_specialplay')->isCoreLanguage()) {
		 return $ret;
	 }
*/

	$k = trim($ret[2], "_\n ");
	if (isset($g_language_desc->__description[$k])) {
		$ret[2] = $g_language_desc->__description[$k][0];
		if (isset($g_language_mes->__helpvar[$rvar])) {
			$ret['help'] = $g_language_mes->__helpvar[$rvar];
		} else if (isset($g_language_mes->__help[$dvar])) {
			$ret['help'] = $g_language_desc->__help[$k];
		} else {
			$ret['help'] = $ret[2];
		}
	}
	return $ret;
}

function get_var_help($var)
{
	global $g_language_mes, $g_language_desc;
	$cvar = strtolower($var);
	if (isset($g_language_mes->__help[$cvar])) {
		return $g_language_mes->__help[$cvar];
	}
	return $var;
}

function get_real_class_variable($class, $var)
{
	//$var = fix_nname_to_be_variable($var);
	//list($iclass, $mclass, $rclass) = get_composite($class);
	$rclass = $class;
	$rclass = ucfirst($rclass);

	if (!class_exists($rclass)) {
		dprint("$rclass doesn't exist\n");
		return null;
	}

	$variable = "$rclass::\$" . $var;
	return eval(" if (isset($variable)) { return $variable ; }  ");
}

function get_class_variable($class, $var)
{
	//list($iclass, $mclass, $rclass) = get_composite($class);
	$rclass = $class;

	$var = fix_nname_to_be_variable($var);
	$class = ucfirst($class);
	/*
	 if (csa($class, '-')) {
		 debugBacktrace();
		 exit;
	 }
 */

	$variable = "$class::\$" . $var;
	return eval(" if (isset($variable)) { return $variable ; }  ");
}

function set_class_variable($class, $var, $val)
{
	$var = fix_nname_to_be_variable($var);
	$class = ucfirst($class);
	$variable = "$class::\$" . $var;
	return eval(" $variable = \$val ; ");
}

function createZeroString($n)
{
	$string = "";
	for ($i = 0; $i < $n; $i++) {
		$string .= "0";
	}
	return $string;
}

/**
 * @return void
 * @param
 * @param
 * @desc  Execs a method inside a class. Passes all the variables to it. See the use of 2 evals.. Check documentation for lx_redefine_func;
 */
function exec_class_method($class, $func)
{
	global $gbl, $sgbl, $login, $ghtml;

	//list($iclass, $mclass, $rclass) = get_composite($class);
	$rclass = $class;

	$class = strtolower($class);

	//Arg getting string is a function that needs $start to be set.
	$start = 2;
	$transforming_func = null;

	if (version_compare(PHP_VERSION, '5.3.0', '<')) {
		eval($sgbl->arg_getting_string);
	} else {
	//	$arglist = get_function_arglist($start, $transforming_func);

		$arglist = array();

		for ($i = $start; $i < func_num_args(); $i++) {
			$arglist[] = func_get_arg($i);
		}

	}

	// workaround for the following php bug:
	//   http://bugs.php.net/bug.php?id=47948
	//   http://bugs.php.net/bug.php?id=51329
	class_exists($class);
	// ---
	return call_user_func_array(array($class, $func), $arglist);
}

function lxgettimewithoutyear($time)
{
	$curd = @ getdate(time());
	$date = @ getdate($time);

	//$month = ($date['month'] === $curd['month'])? "this Month": $date['month'];
	$month = substr($date['month'], 0, 3);

	if ($date['hours'] > 12) {
		$sess = "pm";
		//$hour = $date['hours'] - 12;
		$hour = $date['hours'];
	} else {
		$sess = "am";
		$hour = $date['hours'];
	}

	$minutes = $date['minutes'];

	if ($minutes < 10) {
		$minutes = "0" . $minutes;
	}

	$string = "$hour:$minutes {$date['mday']} $month";

	return $string;
}

function lxgettime($time)
{
	$curd = @ getdate(time());
	$date = @ getdate($time);
/*
	$year = ($date['year'] === $curd['year']) ? "" : $date['year'];
	$year = $date['year'];
	//$month = ($date['month'] === $curd['month'])? "this Month": $date['month'];
	$month = substr($date['month'], 0, 3);

	if ($date['hours'] > 12) {
		$sess = "pm";
		//$hour = $date['hours'] - 12;
		$hour = $date['hours'];
	} else {
		$sess = "am";
		$hour = $date['hours'];
	}

	$minutes = $date['minutes'];

	if ($minutes < 10) {
		$minutes = "0" . $minutes;
	}

	$string = $hour . ":" . $minutes . " " . $date['mday'] . " " . $month . " " . $year;
*/
	$year = $date['year'];
	$month = ($date['mon'] < 10) ? "0" . $date['mon'] : $date['mon'];
	$day = ($date['mday'] < 10) ? "0" . $date['mday'] : $date['mday'];

	$hour = ($date['hours'] < 10) ? "0" . $date['hours'] : $date['hours'];
	$minute = ($date['minutes'] < 10) ? "0" . $date['minutes'] : $date['minutes'];
	$second = ($date['seconds'] < 10) ? "0" . $date['seconds'] : $date['seconds'];

	$string = "{$year}/{$month}/{$day} {$hour}:{$minute}:{$second}";

	return $string;
}

function if_search_continue($obj)
{
	global $gbl, $sgbl, $ghtml;
	if ($ghtml->iset("frm_searchstring") && !stristr($obj->{$obj->searchVar()}, $ghtml->frm_searchstring)) {
		return 1;
	} else {
		return 0;
	}
}

function add_select_one($list)
{
	$newlist[] = "--Select One--";
	$newlist = lx_array_merge(array($newlist, $list));
	return $newlist;
}

function add_disabled($list)
{
	$newlist[] = "--Disabled--";
	$newlist = lx_merge_good($newlist, $list);
	return $newlist;
}

function fix_disabled($value, $disabled_val)
{
	if ($value === "--Disabled--") {
		return $disabled_val;
	} else {
		return $value;
	}
}

function array_remove_assoc(&$array, $element)
{
	foreach ($array as $key => $value) {
		if ($value === $element)
			unset($array[$key]);
	}
}

function array_remove_object($objectlist, $variable, $value)
{
	foreach ($objectlist as $object) {
		if ($object->$variable != $value) {
			$ret[$object->nname] = $object;
		}
	}
	return $ret;
}

function add_superadmin($pass)
{
	global $gbl, $sgbl, $login, $ghtml;

	$client = new SuperClient(null, null, 'superadmin');
	$client->initThisDef();

	$ddb = new Sqlite(null, "superclient");
	if (!$ddb->existInTable("nname", 'superclient')) {
		$res['password'] = crypt($pass);
		$res['cttype'] = 'superadmin';
		$res['cpstatus'] = 'on';
		if (if_demo()) {
			$res['email'] = "admin@example.org";
		}
		$client->create($res);
		$client->write();
	}
}

function add_slave($pass)
{
	global $gbl, $sgbl, $login, $ghtml;

	$client = new Slave(null, null, 'slave');
	$client->initThisDef();

	$ddb = new Sqlite(null, "slave");
	if (!$ddb->existInTable("nname", 'slave')) {
		$res['password'] = $pass;
		$res['cttype'] = 'slave';
		if (if_demo()) {
			$res['email'] = "admin@example.org";
		}
		$client->create($res);
		$client->write();
	}
}

function init_supernode($pass)
{
	global $gbl, $sgbl, $login, $ghtml;

	sql_main();
	add_superadmin($pass);
}

function init_slave($pass)
{
	global $gbl, $sgbl, $login, $ghtml;
	$rm = new Remote();
	$rm->password = crypt($pass);
	lfile_put_contents('__path_slave_db', serialize($rm));
}

function lx_socket_read($socket)
{
	//$res=socket_recv($MsgSock,$buffer,1024,0);
}

function pad_to_length($var, $len)
{
	$times = round(strlen($var) / $len) + 1;
	$in = str_pad($var, 2048 * $times - strlen($var));
	return $in;
}

function remote_http_exec($server, $port, $rmt)
{
	$var = base64_encode(serialize($rmt));
	$res = send_to_some_http_server($server, "", $port, $var);

	$res = unserialize(base64_decode($res));

	if ($res->exception) {
		throw $res->exception;
	}
	return $res->ddata;
}

function send_to_some_http_server($raddress, $socket_type, $port, $var)
{
	global $gbl, $sgbl, $login, $ghtml;

	print_time('server');

	$ch = curl_init("http://$raddress:$port/lbin/remote.php");
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "frm_rmt=$var&");

	// MR -- possible fix download/upload issue in php 5.3
	curl_setopt($ch, CURLOPT_SSLVERSION, 3);
	curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'SSLv3');

	$totalout = curl_exec($ch);
	dprint(curl_error($ch));
	$totalout = trim($totalout);
	return $totalout;
}

function check_remote_pass($user, $pass)
{
	global $gbl, $sgbl, $login, $ghtml;
	return check_password($pass, $login->password);
}

function lfile($arg)
{
	return lx_redefine_func("file", $arg);
}

function getNthToken($string, $num, $delim = ':')
{
	if (csa($string, $delim)) {
		$_tlist = explode($delim, $string);
		return $_tlist[$num];
	}
	return $string;
}

/**
 * @return void
 * @param unknown
 * @param unknown
 * @desc Recurses a dir tree and execs the '$func' on all the files AND the directories.
 */
function recurse_dir($dir, $func, $arglist = null)
{
	$list = lscandir($dir);
	if (!$list) {
		return;
	}
	foreach ($list as $file) {
		if ($file === "." || $file === "..") {
			continue;
		}
		$path = $dir . "/" . $file;
		if (lis_dir($path)) {
			recurse_dir($path, $func, $arglist);
		}
		/// After a successfuul recursion, you have to call the $func on the directory itself. So $func is called whether $path is both directory OR a file.
		$narglist[] = $path;
		foreach ((array) $arglist as $a) {
			$narglist[] = $a;
		}
		call_user_func_array($func, $narglist);
	}
}

function do_recurse_dir($dir, $func)
{
	$list = lscandir($dir);
	if (!$list) {
		return;
	}
	foreach ($list as $file) {
		if ($file === "." || $file === "..") {
			continue;
		}
		$path = $dir . "/" . $file;
		if (lis_dir($path)) {
			do_recurse_dir($path, $func);
		}
		$func($path);
	}
}

function getFromAny($list, $class, $name)
{
	$v = null;
	foreach ($list as $ob) {
		if (!$ob) {
			continue;
		}
		try {
			$v = $ob->getFromList($class, $name);
			return $v;
		} catch (exception $e) {
		}
	}
	return $v;
}

function arrayGetFirstObject($list)
{
	foreach ($list as $k => $v) break;
	return $list[$k];
}

function array_get_object($objectlist, $variable, $value)
{
	foreach ($objectlist as $object)
		if ($object->$variable === $value) {
			return $object;
		}

	return NULL;
}

function getBasicServiceList()
{
	global $gbl, $sgbl, $login, $ghtml;
	$serv[] = $sgbl->__var_programname_web;
	$descr[$sgbl->__var_programname_web] = "Web Server";
	$serv[] = $sgbl->__var_programname_dns;
	$descr[$sgbl->__var_programname_dns] = "Name Server";
	$serv[] = "lxmail";
	$descr['lxmail'] = "Mail Server";
	return array($serv, $descr);
}

function getDbvariable($listvar, $mainvar)
{
	static $var;

	if (!$var) {
		$var = unserialize(lfile_get_contents("__path_dbschema"));
	}

	if (isset($var[$listvar][$mainvar])) {
		return $var[$listvar][$mainvar];
	} else {
		dprint("NO schema for $listvar $mainvar <br> \n");
		return null;
	}

}

function check_file_if_owned_by($file, $user)
{
	if (!lxfile_exists($file)) {
		return true;
	}

	if (csa($user, ":")) {
		$ruser = strtil($user, ":");
	} else {
		$ruser = $user;
	}
	$stat = lxfile_stat($file, false);
	$uid = $stat['uid'];
	$name = os_get_user_from_uid($uid);
	if ($name === $ruser) {
		return true;
	}
	log_log("file_check", "$file not owned by $ruser");
	return false;
}

function critical_change_db_pass()
{
	$c = trim(lfile_get_contents("__path_admin_pass"));
	if ($c == "21232f297a") {
		try {
			change_db_pass();
		} catch (exception $e) {
		}
	}
}

function change_db_pass()
{
	global $gbl, $sgbl, $login, $ghtml;

	$pass = randomString(10);
	$newp = client::createDbPass($pass);
	$oldpass = lfile_get_contents("__path_admin_pass");
	$username = $sgbl->__var_program_name;

	exec("echo 'set Password=Password(\"$newp\")' | mysql -u $username -p$oldpass 2>&1", $out, $return);

	if ($return) {
		$out = implode(" ", $out);
		log_log("admin_error", "mysql change password Failed $out");
		throw new lxException ($login->getThrow("could_not_change_admin_pass"), '', $out);
	}

	$return = lfile_put_contents("__path_admin_pass", $newp);

	if (!$return) {
		log_log("admin_error", "Admin pass change failed  $last_error");
		throw new lxException ($login->getThrow("could_not_change_admin_pass"), '', $last_error);
	}
}

function create_database()
{
	global $gbl, $sgbl, $login, $ghtml;

	// MR -- create kloxo database like horde or roundcube model
	// no override when database exist --> possible reinstall kloxo without lossing kloxo setting

	$pass = slave_get_db_pass();

	$pstring = '';

	if ($pass !== '') {
		$pstring = "-p\"$pass\"";
	}

	$dbpath = '../file/sql';

	exec("mysql -f -u root $pstring < {$dbpath}/db-structure-base.sql &");
}

function update_database()
{
	global $gbl, $sgbl, $login, $ghtml;

	$pass = slave_get_db_pass();

	$pstring = null;

	if ($pass) {
		$pstring = "-p\"$pass\"";
	}

	$dbpath = '../file/sql';

	exec("mysql -f -u root $pstring < {$dbpath}/db-structure-update.sql");
}

function get_default_fields()
{
	$fields = array("nname", "parent_clname", "parent_cmlist");
	return $fields;
}

function create_table_with_drop($tbl_name, $list)
{
	global $gbl, $sgbl, $login, $ghtml;
	$__db = new Sqlite(null, 'sqlite');
	$__db->rawQuery("drop table $tbl_name");
	create_table($__db, $tbl_name, $list);
}

function create_table($__db, $tbl_name, $list)
{
	global $gbl, $sgbl, $login, $ghtml;
	$__db = new Sqlite(null, 'sqlite');

	// For mssql you can talk of primary key initially itself nad not wait till the end of the fields.
	if ($sgbl->__var_database_type === 'mysql') {
		$primary_key = "not null";
	} else {
		$primary_key = "PRIMARY KEY";
	}

	foreach ($list as &$__tl) {
		if (csb($__tl, "nname")) {
			$__tl = "$__tl varchar(255) $primary_key";
		} else if (csb($__tl, "ser_")) {
			$__tl = "$__tl longtext";
		} else if (csb($__tl, "coma_")) {
			$__tl = "$__tl text";
		} else if (csb($__tl, "text_")) {
			$__tl = "$__tl longtext";
		} else if (csb($__tl, "parent_cmlist")) {
			$__tl = "$__tl text";
		} else {
			// Why this madness... How can the general type be varchar. What will happen to all the text messages?
			$__tl = "$__tl varchar(255)";
		}
	}

	if ($sgbl->__var_database_type === 'mysql') {
		mysql_do_create_table($__db, $tbl_name, $list);
	} else {
		sqlite_do_create_table($__db, $tbl_name, $list);
	}
}

function sqlite_do_create_table($__db, $tbl_name, $fields)
{
	$fields = implode(', ', $fields);

	$query = "create table $tbl_name ($fields);";
	print("Creating table $tbl_name....\n");
	$ret = $__db->rawQuery($query);

	$query = "insert into $tbl_name (nname) values ('__dummy__dummy__')";
	$__db->rawQuery($query);
}

function mysql_do_create_table($__db, $tbl_name, $fields)
{
	$fields = implode(', ', $fields);
	$query = "create table $tbl_name ($fields, primary key (nname (255)));";
	print("Creating table $tbl_name....\n");
	$ret = $__db->rawQuery($query);

	if (!$ret) {
		//print("\nerror: " . mysql_error() . "\n\n");
	}

	$query = "create index parent_clname_$tbl_name on $tbl_name (parent_clname (255));";
	$__db->rawQuery($query);
}

// string_main();

function getQuotaListForClass($stuff, $res = null)
{
	if (is_object($stuff)) {
		$ob = $stuff;
	} else {
		$class = $stuff;
		$name = '__dummy__dummy__';
		$ob = new $class(null, null, $name);
		if ($res) {
			$ob->create($res);
		}
		$ob->priv = new Priv(null, null, $name);
	}
	return $ob->getQuotaVariableList();
}

function lx_xdebug_break()
{
	if (function_exists('xdebug_break')) {
		xdebug_break();
	}
}

function remove_extra_slash($file)
{
	return preg_replace("/\/+/", "/", $file);
}

function if_demo_throw_exception($where = null)
{
	global $gbl, $sgbl, $login, $ghtml;
	
	if (!$where) {
		$where = $login->nname;
	}

	if ($sgbl->dbg < 0) {
		$where = null;
	}

	if (if_demo()) {
		throw new lxException($login->getThrow("not_allowed_in_demo_version"), '', $where);
	}
}

function get_package_version($name)
{
	$cont = curl_general_get("http://download.lxcenter.org/download/version/$name");
	return trim($cont);
}

function getClassFromName($cgi_clientname)
{
	$classname = "client";

	if (csa($cgi_clientname, "@")) {
		$classname = "mailaccount";
	} elseif (csa($cgi_clientname, ".vps")) {
		$classname = "vps";
	} elseif (csa($cgi_clientname, ".vm")) {
		$classname = "vps";
	} elseif (csa($cgi_clientname, ".aux")) {
		$classname = "auxiliary";
	}

	return $classname;
}
