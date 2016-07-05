<?php

class Component__rpm extends lxDriverClass
{
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
		global $sgbl;

/*
		$comps = array('mysql', 'MariaDB-server', 'postgresql', 'sqlite',
			'httpd', 'lighttpd', 'nginx', 'hiawatha', 'openlitespeed', 'monkey', 'h2o',
			'trafficserver', 'varnish', 'squid',
			'php', 'perl', 'mono', 'ruby', 'nodejs',
			'bind', 'djbdns', 'pdns', 'nsd', 'mydns', 'yadifa',
			'qmail-toaster', 'pure-ftpd');

		foreach ($comps as $k => $c) {
		//	$list[]['componentname'] = $c;

			$tmp = rl_exec_get('localhost', $syncserver, 'getRpmBranchInstalled', array('$c'));

			if ($tmp) {
				$list[]['componentname'] = $tmp;
			} else {
				$list[]['componentname'] = $c;
			}
		}

		foreach($list as $l) {
			$nlist[] = $l['componentname'];
		}

		$complist = implode(" ", $nlist);

		$file = fix_nname_to_be_variable("rpm -q $complist");
		$file = "$sgbl->__path_program_root/cache/$file";

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

			$ret[] = $sing;
		}

		return $ret;
*/

		$db_list = explode(",", trim(file_get_contents("../etc/list/set.mysql.lst"), "\n"));
		$db_list[] = 'sqlite';
		$db_list[] = 'postgresql';

		$db_list = array_values(array_unique($db_list));

		$out = null;
		exec('rpm -q ' . implode(" ", $db_list), $out);

		foreach ($db_list as $k => $v) {
			$nname = "{$v}___{$syncserver}";
			$type = "database";
			$componentname = $v;
			$version = $out[$k];
			$status = (strpos($out[$k], 'is not installed') !== false) ? 'off' : 'on';

			$t[] = array('nname' => $nname, 'componentname' => $componentname, 
				'version' => $version, 'status' => $status,
				'type' => $type);
		}

		$web_list = explode(",", trim(file_get_contents("../etc/list/set.web.lst"), "\n"));

		$web_list = array_values(array_unique($web_list));

		$out = null;
		exec('rpm -q ' . implode(" ", $web_list), $out);

		foreach ($web_list as $k => $v) {
			$nname = "{$v}___{$syncserver}";
			$type = "web";
			$componentname = $v;
			$version = $out[$k];
			$status = (strpos($out[$k], 'is not installed') !== false) ? 'off' : 'on';

			$t[] = array('nname' => $nname, 'componentname' => $componentname, 
				'version' => $version, 'status' => $status,
				'type' => $type);
		}

		$webcache_list = explode(",", trim(file_get_contents("../etc/list/set.webcache.lst"), "\n"));

		$webcache_list = array_values(array_unique($webcache_list));

		$out = null;
		exec('rpm -q ' . implode(" ", $webcache_list), $out);

		foreach ($webcache_list as $k => $v) {
			$nname = "{$v}___{$syncserver}";
			$type = "webcache";
			$componentname = $v;
			$version = $out[$k];
			$status = (strpos($out[$k], 'is not installed') !== false) ? 'off' : 'on';

			$t[] = array('nname' => $nname, 'componentname' => $componentname, 
				'version' => $version, 'status' => $status,
				'type' => $type);
		}

		$dns_list = explode(",", trim(file_get_contents("../etc/list/set.dns.lst"), "\n"));

		$dns_list = array_values(array_unique($dns_list));

		$out = null;
		exec('rpm -q ' . implode(" ", $dns_list), $out);

		foreach ($dns_list as $k => $v) {
			$nname = "{$v}___{$syncserver}";
			$type = "dns";
			$componentname = $v;
			$version = $out[$k];
			$status = (strpos($out[$k], 'is not installed') !== false) ? 'off' : 'on';

			$t[] = array('nname' => $nname, 'componentname' => $componentname, 
				'version' => $version, 'status' => $status,
				'type' => $type);
		}

		$smtp_list = explode(",", trim(file_get_contents("../etc/list/set.smtp.lst"), "\n"));
		$spam_list = explode(",", trim(file_get_contents("../etc/list/set.spam.lst"), "\n"));
		$pop_list = explode(",", trim(file_get_contents("../etc/list/set.pop.lst"), "\n"));
		$imap_list = explode(",", trim(file_get_contents("../etc/list/set.imap.lst"), "\n"));
		$other_list = array('qmail-toaster');

		$mail_list = array_merge($smtp_list, $spam_list, $pop_list, $imap_list, $other_list);

		$mail_list = array_values(array_unique($mail_list));

		$out = null;
		exec('rpm -q ' . implode(" ", $mail_list), $out);

		foreach ($mail_list as $k => $v) {
			$nname = "{$v}___{$syncserver}";
			$type = "mail";
			$componentname = $v;
			$version = $out[$k];
			$status = (strpos($out[$k], 'is not installed') !== false) ? 'off' : 'on';

			$t[] = array('nname' => $nname, 'componentname' => $componentname, 
				'version' => $version, 'status' => $status,
				'type' => $type);
		}

		$ftp_list = explode(",", trim(file_get_contents("../etc/list/set.ftp.lst"), "\n"));

		$ftp_list = array_values(array_unique($ftp_list));

		$out = null;
		exec('rpm -q ' . implode(" ", $ftp_list), $out);

		foreach ($ftp_list as $k => $v) {
			$nname = "{$v}___{$syncserver}";
			$type = "ftp";
			$componentname = $v;
			$version = $out[$k];
			$status = (strpos($out[$k], 'is not installed') !== false) ? 'off' : 'on';

			$t[] = array('nname' => $nname, 'componentname' => $componentname, 
				'version' => $version, 'status' => $status,
				'type' => $type);
		}

		$php_list = explode(",", trim(file_get_contents("../etc/list/set.php.lst"), "\n"));
		$php_list = array_map(function($value) { return "{$value}-cli"; }, $php_list);

		$php_list = array_values(array_unique($php_list));

		$out = null;
		exec('rpm -q ' . implode(" ", $php_list), $out);

		foreach ($php_list as $k => $v) {
			$v = str_replace('-cli', '', $v);
			$nname = "{$v}___{$syncserver}";
			$type = "php-branch";
			$componentname = $v;
			$version = $out[$k];
			$status = (strpos($out[$k], 'is not installed') !== false) ? 'off' : 'on';

			$t[] = array('nname' => $nname, 'componentname' => $componentname, 
				'version' => $version, 'status' => $status,
				'type' => $type);
		}

	//	$phpm_list = getMultiplePhpList();
		$phpm_list = getCleanRpmBranchListOnList('php');

		foreach ($phpm_list as $k => $v) {
			$nname = "{$v}___{$syncserver}";
			$type = "multiple-php";
			$componentname = $v;

			if (file_exists("/opt/{$v}/version")) {
				$version = "{$v}-" . file_get_contents("/opt/{$v}/version");
				$status = 'on';
			} else {
				$version = "package {$v} is not installed";
				$status = 'off';
			}

			$t[] = array('nname' => $nname, 'componentname' => $componentname, 
				'version' => $version, 'status' => $status,
				'type' => $type);
		}

		return $t;
	}
}
