<?php

class Service__Linux extends Lxlclass
{
	static function getServiceList()
	{
		global $gbl, $sgbl, $login, $ghtml;
		
	//	$val = lscandir_without_dot("{$sgbl->__path_real_etc_root}/init.d");
	/*
		$val = array_remove($val, $sgbl->__var_programname_web);
		$val = array_remove($val, $sgbl->__var_programname_dns);
		$val = array_remove($val, $sgbl->__var_programname_imap);
		$val = array_remove($val, $sgbl->__var_programname_mmail);
	*/
		// MR not use option '--type=sysv' because trouble in CentOS 5
		exec("chkconfig --list 2>/dev/null|awk '{print $1}'|grep -v ':'", $val1);

		$val2 = array();

	//	exec("command -v systemctl", $test);

	//	if (count($test) > 0) {
		if (getServiceType() === 'systemd') {
			exec("systemctl list-unit-files --type=service|awk '{print $1}'|sed 's/\.service//g'", $val2);
		}

		$val = lx_array_merge($val1, $val2);

		$nval = self::getMainServiceList();
		$nval = lx_array_merge(array($nval, $val));

		array_unique($nval);
		
		return $nval;
	}

	static function getMainServiceList()
	{
		global $gbl, $sgbl, $login, $ghtml;

		$nval['httpd'] = 'httpd';
		$nval['apache2'] = 'apache2';
		$nval['lighttpd'] = 'lighttpd';
		$nval['nginx'] = 'nginx';
		$nval['hiawatha'] = 'hiawatha';
	//	$nval['openlitespeed'] = 'lsws';
	//	$nval['monkey'] = 'monkey';

		$nval['varnish'] = 'varnish';
		$nval['squid'] = 'squid';
		$nval['trafficserver'] = 'trafficserver';

		$nval['php-fpm'] = 'php-fpm';

		$nval['named'] = 'named';
		$nval['djbdns'] = "tinydns";
	//	$nval['maradns'] = "maradns";
	//	$nval['powerdns'] = "powerdns";
		$nval['pdns'] = "pdns";
		$nval['nsd'] = "nsd";
	//	$nval['mydns'] = "mydns";
		$nval['yadifad'] = "yadifad";

		$nval['qmail'] = 'qmail';
	//	$nval['courier-imap'] = 'courier';
	//	$nval['spamassassin'] = 'spamassassin';
	//	$nval['dovecot'] = 'dovecot';

		$nval['iptables'] = "iptables";
		$nval['firewalld'] = "firewalld";

		return $nval;
	}

	static function checkService($name)
	{
		global $gbl, $sgbl, $login, $ghtml;
	/*
		if ($name === 'qmail') {
			$ret = lxshell_return("qmailctl", "stat");
		} else {
			$ret = lxshell_return("service". $name, "status");
		}
	*/
		exec("pgrep ^{$name}", $out);

	//	$state = ($ret) ? "off" : "on";
		$state = (count($out) > 0) ? "off" : "on";

		return $state;
	}

	static function getRunLevel()
	{
		$v = trim(lxshell_output("runlevel"));
		$v = explode(" ", $v);
		
		return $v[1];
	}
}
