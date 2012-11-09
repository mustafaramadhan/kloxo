<?php

class phpini__sync extends Lxdriverclass
{
	function initString()
	{
	//	$pclass = $this->main->getParentClass();

		$this->main->fixphpIniFlag();

	//	$this->setInitString();

	}

	function setInitString()
	{
		$modulelist = array('xcache', 'suhosin', 'ioncube', 'zend');

		foreach ($modulelist as &$m) {
			if ($this->main->phpini_flag_b->isOn("enable_{$m}_flag")) {
				$active = isPhpModuleActive($m);

				if ($active) {
					setPhpModuleActive($m);
				}
			} else {
				setPhpModuleInactive($m);
			}
		}
	}

	function enableDisableModule($flag, $mod)
	{
		// MR -- not used since 6.2.x
	}

	function createIniFile()
	{
		global $sgbl;

		$pclass = $this->main->getParentClass();

		$this->initString();

		$l1 = $this->main->getInheritedList();
		$l2 = $this->main->getLocalList();
		$l3 = $this->main->getExtraList();

		$ll = lx_array_merge(array($l1, $l2, $l3));
		$list = array_unique($ll);

		$input = array();

		foreach ($list as &$l) {
			$v = $this->main->phpini_flag_b->$l;
			$input[$l] = ($v) ? $v : '';
		}

		// MR -- also carry user and domain on $input 
		// to make .htaccess and php.ini more customizing
		$input['user'] = $this->main->__var_customer_name;
		$input['domain'] = $this->main->getParentName();

		$stlist[] = "###Start Kloxo PHP config Area";
		$stlist[] = "###Start Lxdmin Area";
		$stlist[] = "###Start Kloxo Area";
		$stlist[] = "###Start Lxadmin PHP config Area";

		$endlist[] = "###End Kloxo PHP config Area";
		$endlist[] = "###End Kloxo Area";
		$endlist[] = "###End Lxadmin PHP config Area";

		$endstring = $endlist[0];
		$startstring = $stlist[0];

		$fpath = "/usr/local/lxlabs/kloxo/file";
		$tpath = "/home/phpini/tpl";

		exec("cp -rf {$fpath}/phpini /home");

		$pcont = file_get_contents(getLinkCustomfile($tpath, "php.ini.tpl"));
		$hcont = file_get_contents(getLinkCustomfile($tpath, "htaccess.tpl"));

		$pparse = getParseInlinePhp($pcont, $input);
		$hparse = getParseInlinePhp($hcont, $input);

		if ($pclass === 'pserver') {
			$ptarget = '/etc/php.ini';
			file_put_contents($ptarget, $pparse);
		} else {
			$dname = $this->main->getParentName();
			$hroot = $sgbl->__path_httpd_root;
			$droot = $this->main->__var_docrootpath;
			$wuser = $this->main->__var_web_user;
			$cname = $this->main->__var_customer_name;

		//	$elogfile = "/home/{$cname}/__processed_stats/{$dname}.phplog";

			$ptarget = "{$hroot}/{$dname}/php.ini";
			file_put_contents($ptarget, $pparse);

			$htfile = "{$droot}/.htaccess";
			$ht1file = "/home/{$cname}/kloxoscript/.htaccess";

			file_put_between_comments("{$wuser}:apache", $stlist, $endlist, 
					$startstring, $endstring, $htfile, $hparse);
			file_put_between_comments("{$wuser}:apache", $stlist, $endlist, 
					$startstring, $endstring, $ht1file, $hparse);

			lxfile_unix_chown($htfile, "{$wuser}:apache");

		}

		// MR -- also restart php-fpm
		createRestartFile('php-fpm');

		createRestartFile($this->main->__var_webdriver);

	}

	function dbactionAdd()
	{
		$this->createIniFile();
	}

	function dbactionUpdate($subaction)
	{
	//	$this->setInitString();

		$this->createIniFile();
	}

}
