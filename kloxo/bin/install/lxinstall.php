<?php
include "lib/html/include.php";

lxinstall_main();

function del_dir($dir)
{
	$res = opendir($dir);
	
	if(!$res) { return; }
	
	while(($file = readdir($res)) !== false) {
		if ($file !== '.' && $file !== '..') {
			$f = $dir . '/' . $file;
			
			if(is_dir($f)) {
				del_dir($f);
			} else {
				unlink($f);
			}
		}
	}
	
	closedir($res);
	rmdir($dir);
}

function copy_dir($source, $dest){
	if (is_file($source)) {
		$c= copy($source, $dest);
		chmod($dest, 0777);
		
		return $c;
	}

	if (!is_dir($dest)) {
		$oldumask = umask(0);
		mkdir($dest, 0777);
		umask($oldumask);
	}

	$dir = dir($source);
	
	while (false !== $entry = $dir->read()) {
		if ($entry == '.' || $entry == '..') {
			continue;
		}

		if ($dest !== "$source/$entry") {
			copy_dir("$source/$entry", "$dest/$entry");
		}
	}

	$dir->close();
	
	return true;
}

function copy_file($src,$dest)
{
	$c=copy($src, $dest);
	chmod($dest, 0777);
	
	return $c;
}
function lxinstall_main()
{
	global $argv;
	
	$opt = parse_opt($argv);
	$package = $opt['package-name'];
	lxinstall_package($package);
}

function lxinstall_package($package)
{
	$oldir = getcwd();
	chdir("/usr/local/kloxo/src/$package-current");
	
	include_once  "./lxconfigure.php";

	print("Install/Upgrade $package\n");
	$pre_func = "{$package}_lxconfigure_pre";
	
	if (function_exists($pre_func)) {
		$pre_func();
	}
	
	dprintr($gl_execute_cmd);
	if ($gl_execute_cmd) foreach($gl_execute_cmd as $cmd) {
		system($cmd);
	}
	
	$post_func = "{$package}_lxconfigure_post";
	
	if (function_exists($post_func)) {
		$post_func();
	}
	
	chdir($oldir);
}
