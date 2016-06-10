<?php 

function shell_recurse_dir($dir, $func, $arglist = null)
{

	$list = lscandir_without_dot($dir);
	if (!$list) return;
	foreach($list as $file) {
		$path = $dir . "/" . $file;
		if (lis_dir($path)) {
			shell_recurse_dir($path, $func, $arglist);
		}
		/// After a successfuul recursion, you have to call the $func on the directory itself. So $func is called whether $path is both directory OR a file.
		$narglist = null;
		$narglist[] = $path;
		foreach((array) $arglist as $a) {
			$narglist[] = $a;
		}
		//dprint("calling with :");
		//dprintr($narglist);
		call_user_func_array($func, $narglist);
	}
}

function lxshell_direct($cmd)
{
	$username = '__system__';
	do_exec_system($username, null, $cmd, $out, $err, $ret, null);
}

function lxfile_disk_free_space($dir)
{
	$dir = expand_real_root($dir);
	lxfile_mkdir($dir);
	$ret = disk_free_space($dir);
	$ret = round($ret/(1024 * 1024), 1);
	log_shell("Disk Space $dir $ret");
	return $ret;
}

function lxshell_unzip_numeric_with_throw($dir, $file, $list = null)
{
	global $login;

	$ret = lxshell_unzip_numeric($dir, $file, $list);

	if ($ret) {
		// MR -- more informative error message
		exec_with_all_closed("sh /script/load-wrapper >/dev/null 2>&1 &");
		throw new lxException($login->getThrow("could_not_unzip_file"), '', "dir: {$dir}; file: {$file}");
	}
}

function lxshell_unzip_with_throw($dir, $file, $list = null)
{
	lxuser_unzip_with_throw('__system__', $dir, $file, $list);
}

function lxshell_redirect($file, $cmd)
{
	global $gbl, $sgbl, $login, $ghtml; 

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

	$cmd = getShellCommand($cmd, $arglist);

	$return = null;

	system("$cmd > $file 3</dev/null 4</dev/null 5</dev/null 6</dev/null", $return);

	return $return;
}

function lxshell_directory($dir, $cmd)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$dir = expand_real_root($dir);
	$username = '__system__';

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

	$cmd = getShellCommand($cmd, $arglist);

	do_exec_system($username, $dir, $cmd, $out, $err, $ret, null);

	return $out;

}

function lxshell_output($cmd)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$username = '__system__';

	$start = 1;
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

	$cmd = getShellCommand($cmd, $arglist);
	do_exec_system($username, null, $cmd, $out, $err, $ret, null);
	return $out;
}

function lxshell_return($cmd)
{
	global $sgbl;
	$username = '__system__';

	$start = 1;
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

	$cmd = getShellCommand($cmd, $arglist);

	do_exec_system($username, null, $cmd, $out, $err, $ret, null);
	
	return $ret;
}

function lxshell_php($cmd)
{
	lxshell_return("__path_php_path", $cmd);
}

function lxshell_input($input, $cmd)
{
	global $sgbl;
	$username = '__system__';

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

	$cmd = getShellCommand($cmd, $arglist);
	do_exec_system($username, null, $cmd, $out, $err, $ret, $input);
	
	return $ret;
}

/**
 * Unzip file to the given directory as given user
 * 
 * @param $username
 * @param $dir path to the output directory
 * @param $file file to unzip
 * @param $list
 */
function lxuser_unzip_with_throw($username, $dir, $file, $list = null)
{
	global $login;

	$ret = lxshell_unzip($username, $dir, $file, $list);

	if ($ret) {
		// MR -- more informative error message
		exec_with_all_closed("sh /script/load-wrapper >/dev/null 2>&1 &");
		throw new lxException($login->getThrow("could_not_unzip_file"), '', "dir: {$dir}; file: {$file}");
	}
}

/**
 * Makes new directory and set directory owner
 * 
 * @param string $username directory owner
 * @param string $dir path to the directory
 * @return bool TRUE on success or FALSE on failure.
 */
function lxuser_mkdir($username, $dir)
{
	if (!lxfile_mkdir($dir)) {
		return false;
	}
	
	lxfile_generic_chown($dir, $username);
	
	return true;
}

/**
 * Copies file or directory and set owner
 * 
 * @param string $username file or directory owner
 * @param string $src path to the source file or directory
 * @param string $dst path to the destination file or directory
 * @return bool TRUE on success or FALSE on failure.
 */
function lxuser_cp($username, $src, $dst)
{
	if (!lxfile_cp($src, $dst)) {
		return false;
	}
	
	if (!lxfile_generic_chown($dst, $username)) {
		lxfile_rm($dst);	
		return false;
	}
	
	return true;
}

/**
 * Moves file or directory and set owner
 * 
 * @param string $username file or directory owner
 * @param string $src path to the file or directory
 * @param string $dst new path to the file or directory
 * @return bool TRUE on success or FALSE on failure. 
 */
function lxuser_mv($username, $src, $dst)
{
	if (!lxfile_mv($src, $dst)) {
		return false;
	}
	
	if (!lxfile_generic_chown($dst, $username)) {
		lxfile_mv($dst, $src);
		return false;
	}
	
	return true;
}

/**
 * Writes a string to a file
 * 
 * @param string $username  file owner
 * @param string $file path to the file
 * @param mixed $data the data to write
 * @param int $flag
 */
function lxuser_put_contents($username, $file, $data, $flag = 0)
{
	$file = expand_real_root($file);
    dprint("Debug: filename is: ". $file . "\n");
	if (is_soft_or_hardlink($file)) {
		log_log("link_error", "$file is hard or symlink. Not writing\n");
		return false;
	}

	if (!lxuser_mkdir($username, dirname($file))) {
		return false;
	}
	
	if ($flag == FILE_APPEND) {
		$mode = 'a';
	} else {
		$mode = 'w';
	}
	
	$f = fopen($file, $mode);
	if (!$f) {
    	return false;
	} else {
		if (flock($f, LOCK_EX)) {
			$bytes = fwrite($f, $data);
			lxfile_generic_chown($file, $username);
			flock($f, LOCK_UN);
			fclose($f);
			return $bytes;
		}
		else {
			return false;
		}
	}
}

/**
 * Changes file mode
 * 
 * @param $username
 * @param $file path to the file
 * @param $mod mode of the specified file
 */
function lxuser_chmod($username, $file, $mod)
{
	// I'm not sure how we should implement this method ???
	
	lxfile_generic_chmod($file, $mod);
	//lxfile_generic_chown($file, $username); ???
}

/**
 * Executes an external program or command as given user 
 * 
 * @param $username
 * @param $cmd command to execute
 * @return depends on executed command
 */
function lxuser_return($username, $cmd) {
	global $sgbl;
	
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
	
	$cmd = getShellCommand($cmd, $arglist);
	
	$ret = new_process_cmd($username, null, $cmd);
	
	return $ret;
}

/**
 * Deletes filename or empty directory. 
 * 
 * @param $file	path to the file or to the directory
 * @return TRUE on success or FALSE on failure.
 */ 
function lxfile_rm($file)
{
	$file = expand_real_root($file);

	log_filesys("Removing $file");
	if (lxfile_exists($file)) {
		if (lis_dir($file)) {
			return rmdir("$file");
		} else {
			return unlink("$file");
		}
	}

	return false;
}

/**
 * Renames/Moves file
 * 
 * @param $src path to the source file
 * @param $dst destination path
 * @return TRUE on success or FALSE on failure.
 */
function lxfile_mv($src, $dst)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$src = expand_real_root($src);
	$dst = expand_real_root($dst);
	if (is_dir($dst)) {
		$base = basename($src);
		$dst = "{$dst}/{$base}";
	}

	if ($sgbl->dbg > 0) {
		log_filesys("Moving $src $dst");
	}

	if (lxfile_cp($src, $dst)) {
		lxfile_rm($src);
		return true;
	}
	return false;
}

function lxfile_exists($file)
{
	return lfile_exists($file);
}

function lxfile_real($file)
{
	$size = lxfile_size($file);
	return ($size != 0);
}

function lxfile_nonzero($file)
{
	return lxfile_real($file);
}

/**
 * Makes directory
 * 
 * @param $dir the directory path
 * @return TRUE on success or FALSE on failure.
 */
function lxfile_mkdir($dir)
{
	if(empty($dir)){ # Avoid "", NULL or false values
		return false; 
	}
	
	$dir = expand_real_root($dir);
	if(lxfile_exists($dir)){
		return true;
	}

	if(file_exists($dir)){ # Real check by PHP
		return true; 
	}
	
	log_filesys("Making directory $dir");
	return mkdir($dir, 0755, true);
}

/**
 * Copies file
 * 
 * @param $src path to the source file
 * @param $dst destination path
 * @return TRUE on success or FALSE on failure.
 */
function lxfile_cp($src, $dst)
{
	global $gbl, $sgbl, $login, $ghtml;

	$src = expand_real_root($src);
	$dst = expand_real_root($dst);

	if (is_dir($dst)) {
		$dst = $dst . "/" . basename($src);
	}

	log_filesys("Copying $src $dst");
	system("'cp' '-rf' '$src' '$dst'", $ret);

	return $ret == 0;
}

function lxfile_stat($file, $duflag)
{
	$file = expand_real_root($file);
	$list =  lstat($file);
	if (($duflag && is_dir($file)) || $file === ".trash") {
		$list['size'] = lxfile_dirsize($file, true);
	} else {
		$list['size'] = lxfile_size($file);
	}

	get_file_type($file, $list);

	return $list;
}

function get_file_type($file, &$stat)
{
	if (is_link($file)) {
		$dst = readlink($file);

		if (($dst[0] !== '/')) {
			$dst = dirname($file) . "/" . $dst;
		}

		if (!lxfile_exists($dst)) {
			$stat['ttype'] = "brokenlink";
			$stat['linkto'] = $dst;
			
			return;
		}

		if (is_dir($dst)) {
			$stat['ttype'] = "dirlink";
			$stat['linkto'] = $dst;

			return;
		}

		$stat['ttype'] = "filelink";
		$stat['linkto'] = $dst;

		return;
	}

	if (is_dir($file)) {
		$stat['ttype'] =  "directory";

		return;
	}

	$list = pathinfo($file);

	$ext = null;

	if (isset($list['extension']))  {
		$ext = $list['extension'];

		// MR -- exception for tar.gz/.bz2/.xz
		if (($ext === 'gz') || ($ext === 'bz2') || ($ext === 'xz')) {
			if (strrpos($list['filename'], '.tar') !== false) {
				$ext = 'tar.' . $ext;
			}
		}
	}

	if ($ext === "zip") {
		$stat['ttype'] =  "zip";
	} else if ($ext === "tgz" || $ext === "tar.gz") {
		$stat['ttype'] = "tgz";
	} else if ($ext === "gz") {
		$stat['ttype'] = "gz";
	} else if ($ext === "tbz2" || $ext === "tar.bz2") {
		$stat['ttype'] = "tbz2";
	} else if ($ext === "bz2") {
		$stat['ttype'] = "bz2";
	} else if ($ext === "txz" || $ext === "tar.xz") {
		$stat['ttype'] = "txz";
	} else if ($ext === "xz") {
		$stat['ttype'] = "xz";
	} else if ($ext === "7z") {
		$stat['ttype'] = "p7z";
	} else if ($ext === "rar") {
		$stat['ttype'] = "rar";
	} else if ($ext === "tar") {
		$stat['ttype'] = "tar";
	} else {
		$stat['ttype'] = "file";
	}
}

function lxfile_dstat($dir, $duflag)
{
	$dir = expand_real_root($dir);
	$list = lscandir_without_dot($dir);

	$ret = null;

	foreach($list as $l) {
		$stat = lstat("$dir/$l");
		get_file_type("$dir/$l", $stat);

		remove_unnecessary_stat($stat);

		if (($duflag && is_dir("$dir/$l") || $l === ".trash")) {
			$stat['size'] = lxfile_dirsize("$dir/$l", true);
		} else {
			$stat['size'] = lxfile_size("$dir/$l");
		}

		$stat['name'] = "$dir/$l";
		$ret[] = $stat;
	}

	return $ret;
}


function lxfile_getfile($file, $bytes = null)
{
	global $login;

	$file = expand_real_root($file);
	$stat = stat($file);
	if ($stat['size'] > 5* 1000 * 1000) {
		dprint("File size too high. Taking only the last 200 lines\n");
		$lines = 200;
	}

	if ($lines === 'download') {
		throw new lxException($login->getThrow('could_not_download_here'));
		$lines = null;
	}
	if (!$lines) {
		$data = file_get_contents($file);
	} else {
		$data = lxfile_tail($file, $bytes);
	}
	return $data;
}

function lxfile_tail($file, $getsize)
{
	$size = lfilesize($file);
	$fp = lfopen($file, "r");
	if (!$fp) {
		return null;
	}
	fseek($fp, $size - $getsize);

	$ret = null;
	while(!feof($fp)) {
		$ret .= fread($fp, 1024);
	}
	return $ret;
	
}

function lxfile_touch($file)
{
	$file = expand_real_root($file);
	$ret = touch($file);
	return $ret;
}

