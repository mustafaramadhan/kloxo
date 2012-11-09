<?php 

class Component__rpm extends lxDriverClass {



static function getDetailedInfo($name)
{
	$ret = lxshell_output("rpm", "-qi", $name);
	return $ret;
}


static function getVersion($list, $name)
{
	foreach($list as $v) {
		if (csb($v, $name) || csa($v, " $name ")) {
			$ret[] = $v;
		}
	}

	return implode(", ", $ret);
}

static function getListVersion($syncserver, $list)
{

	$list[]['componentname'] = 'mysql';
	$list[]['componentname'] = 'perl';
	//$list[]['componentname'] = 'postgresql';
	$list[]['componentname'] = 'httpd';
	$list[]['componentname'] = 'qmail';
	$list[]['componentname'] = 'courier-imap-toaster';
	$list[]['componentname'] = 'php';
	$list[]['componentname'] = 'lighttpd';
	$list[]['componentname'] = 'djbdns';
	$list[]['componentname'] = 'bind';
	$list[]['componentname'] = 'spamassassin';
	$list[]['componentname'] = 'pure-ftpd';
	$list[]['componentname'] = 'nginx';

	foreach($list as $l) {
		$nlist[] = $l['componentname'];
	}
	$complist = implode(" ", $nlist);
	$file = fix_nname_to_be_variable("rpm -q $complist");
	$file = "__path_program_root/cache/$file";

	$cmdlist = lx_array_merge(array(array("rpm", "-q"), $nlist));
	$val = get_with_cache($file, $cmdlist);

	$res = explode("\n", $val);

	$ret = null;
	foreach($list as $k => $l) {
		$name = $list[$k]['componentname'];
		$sing['nname'] = $name . "___" . $syncserver;
		$sing['componentname'] = $name;

		$sing['version'] = self::getVersion($res, $name);
		$status = strstr($sing['version'], "not installed");
		$sing['status'] = $status? 'off': 'on';

		/*
		if (isOn($sing['status'])) {
			$sing['full_version'] = `rpm -qi $name`; 
		} else {
			$sing['full_version'] = $sing['version'];
		}
	*/
		$ret[] = $sing;
	}

	return $ret;
}

}


