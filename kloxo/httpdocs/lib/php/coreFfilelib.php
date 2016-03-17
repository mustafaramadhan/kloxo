<?php

class coreFfile
{
	static function get_full_stat($__username_o, $root, $path, $duflag)
	{
		self::check_for_break($root, $path);

		$ret = lxfile_dstat($path, $duflag);
		
		if ($ret) {
			foreach ($ret as &$r) {
				$r = self::createFfileVariables($r);
			}
		}

		return $ret;
	}

	static function getRealpath($_path)
	{
		$__path = $_path;

	/*
		if ( isRelative( $_path ) ) {
			$__curdir = unifyPath( realpath( "." ) . _PL_OS_SEP );
			$__path = $__curdir . $__path;
		}
	*/
		$__realparts = array();
		$__parts = explode("/", $__path);
		
		for ($i = 0; $i < count($__parts); $i++) {
			if (strlen($__parts[$i]) === 0 || $__parts[$i] === ".") {
				continue;
			}
			
			if ($__parts[$i] === "..") {
				if (count($__realparts) > 0) {
					array_pop($__realparts);
				}
			} else {
				array_push($__realparts, $__parts[$i]);
			}
		}

		return implode("/", $__realparts);
	}

	static function check_for_break($root, $path)
	{
		global $login;

		if (lis_link($path)) {
			$rpath = lreadlink($path);
		} else {
			$rpath = $path;
		}

		dprint("$rpath $root\n");
		
		if (!csb($rpath, $root)) {
			throw new lxException($login->getThrow("trying_to_go_outside_root"), '', $rpath);
		}
	}

	static function getLxStat($__username_o, $root, $path, $numlines = null, $duflag = null)
	{
		dprint("$path\n");
		self::check_for_break($root, $path);

		dprint("In getLxstat $path $duflag\n");

		$stat = lxfile_stat($path, $duflag);
		$stat = self::createFfileVariables($stat);

		if ($numlines === 'download') {
			$ret = cp_fileserv($path);
			$stat['serverfile_data'] = $ret;
		}

		$stat['duflag'] = $duflag;

		return $stat;
	}

	static function get_image_info($path)
	{
	//	self::load_gd();
		$path = expand_real_root($path);
		list($width, $height, $type, $attr) = getimagesize($path);
		$stat['image_width'] = $width;
		$stat['image_height'] = $height;
		$stat['image_type'] = $type;
		$stat['image_attr'] = $attr;
		$stat['image_content'] = lfile_get_contents($path);
	
	//	dprintr($stat);
		
		return $stat;
	}

	static function load_gd()
	{
		if (!extension_loaded("gd")) {
			dprint("Warning No gd <br> ");
			dl("gd." . PHP_SHLIB_SUFFIX);
		}
	}

	static function is_image($path)
	{
		$ext = self::getExtension($path);
		$list = array("GIF", "JPG", "PNG", "SWF", "SWC", "PSD", "TIFF", "BMP", "IFF", "JP2", "JPX", "JB2", "JPC", "XBM");
		$ext = strtoupper($ext);
		
		if (array_search_bool($ext, $list)) {
			return true;
		}
		
		return false;
	}

	static function getExtension($path)
	{
		return pathinfo($path, PATHINFO_EXTENSION);
	}

	static function getWithoutExtension($path)
	{
		$array = pathinfo($path);
		
		return "{$array['dirname']}/{$array['filename']}";
	}

	static function getContent($__username_o, $root, $path, $stat, $numlines)
	{
		if ($stat['ttype'] === "zip" || $stat['ttype'] === 'tar'
				|| $stat['ttype'] === 'tgz' || $stat['ttype'] === 'tbz2'
				|| $stat['ttype'] === 'txz' || $stat['ttype'] === 'p7z'
				|| $stat['ttype'] === 'rar') {
			$res = lxshell_getzipcontent($path);
		//	$res = str_replace(" ", "&nbsp;  ", $res);
			$list = explode("\n", $res);
			$list = preg_grep("/Archive:/", $list, PREG_GREP_INVERT);
			$stat['zipcontent'] = implode("\n", $list);
		} else if (self::is_image($path)) {
			$stat = self::get_image_info($path);
		} else if (!coreFfile::is_a_directory($stat['mode'])) {
			$stat = self::getFile($stat, $path, $numlines);
		}

		return $stat;
	}

	static function getFile($stat, $file, $lines = null)
	{
		if (!lfile_exists($file)) {
			$stat['not_full_size'] = 'off';
			$stat['content'] = null;
			
			return $stat;
		}

		$size = lxfile_size($file);

		if ($size > 1000 * 1000) {
			$lines = 20;
		}

		if ($lines) {
			$getsize = $lines * 1000;
			$stat['not_full_size'] = 'on';
			$stat['content'] = lxfile_tail($file, $getsize);
			$stat['numlines'] = $lines;
		} else {
			$stat['not_full_size'] = 'off';
			$stat['content'] = lfile_get_contents($file);
		}
		
		return $stat;
	}

	static function is_a_directory($mode)
	{
		if ($mode & 040000) {
			return true;
		}
		
		return false;
	}

	static function createFfileVariables($stat)
	{
		$user = os_get_user_from_uid($stat['uid']);
		$group = os_get_group_from_gid($stat['gid']);

		$stat['other_username'] = "{$user}:{$group}";
		
		return $stat;
	}

	static function removeLeadingSlash($f)
	{
		while ($f[0] === '/') {
			$f = substr($f, 1);
		}
		
		return $f;
	}
}
