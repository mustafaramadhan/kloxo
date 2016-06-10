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
		$pclass = $this->main->getParentClass();

		$this->initString();

		$l1 = $this->main->getInheritedList();
		$l2 = $this->main->getLocalList();
		$l3 = $this->main->getExtraList();

		$ll = lx_array_merge(array($l1, $l2, $l3));
		$list = array_unique($ll);

		$input = array();

		foreach ($list as &$l) {
			$input[$l] = (isset($this->main->phpini_flag_b->$l)) ? $this->main->phpini_flag_b->$l : '';
		}

		$user = $input['user'] = (isset($this->main->__var_web_user)) ? $this->main->__var_web_user : 'apache';
	//	$extrabasedir = $input['extrabasedir'] = (isset($this->main->__var_extrabasedir)) ? $this->main->__var_extrabasedir : '';

		$phpini_path = "/opt/configs/phpini/tpl";
		$phpini_cont = file_get_contents(getLinkCustomfile($phpini_path, "php.ini.tpl"));

		$fcgid_path = "/opt/configs/apache/tpl";
		$fcgid_cont = file_get_contents(getLinkCustomfile($fcgid_path, "php.fcgi.tpl"));

		$prefork_path = "/opt/configs/apache/tpl";
		$prefork_cont = file_get_contents(getLinkCustomfile($prefork_path, "prefork.inc.tpl"));

		if (!file_exists("/etc/php-fpm.d")) {
			lxfile_mkdir("/etc/php-fpm.d");
		}

		$stlist[] = "###Start Kloxo PHP config Area";
		$stlist[] = "###Start Lxdmin Area";
		$stlist[] = "###Start Kloxo Area";
		$stlist[] = "###Start Lxadmin PHP config Area";

		$endlist[] = "###End Kloxo PHP config Area";
		$endlist[] = "###End Kloxo Area";
		$endlist[] = "###End Lxadmin PHP config Area";

		if ($pclass === 'pserver') {
			$input['phpinipath'] = "/etc";
			$input['phpcgipath'] = "/usr/bin/php-cgi";

			$phpini_parse = getParseInlinePhp($phpini_cont, $input);
			$phpini_target = '/etc/php.ini';
			file_put_contents($phpini_target, $phpini_parse);

			$fcgid_parse = getParseInlinePhp($fcgid_cont, $input);
			$fcgid_target = '/home/kloxo/client/php.fcgi';
			$fcgid_target_old = '/home/kloxo/client/php5.fcgi';
			file_put_contents($fcgid_target, $fcgid_parse);

			if (file_exists($fcgid_target_old)) {
				exec("'rm' -f {$fcgid_target_old}");
			}

			exec("'cp' -f /opt/configs/apache/tpl/php*.fcgi /home/kloxo/client");
			exec("chmod 0775 /home/kloxo/client/php*.fcgi");

			if (!file_exists("/opt/configs/php-fpm/sock")) {
				exec("mkdir -p /opt/configs/php-fpm/sock");
			}

			$phpfpm_path_etc = "/opt/configs/php-fpm/etc";
			$phpfpm_path = "/opt/configs/php-fpm/tpl";

			$phpmfpminit_src = getLinkCustomfile("{$phpfpm_path_etc}/init.d", "phpm-fpm.init");
			$phpmfpminit_target = "/etc/rc.d/init.d/phpm-fpm";
			exec("'cp' -f {$phpmfpminit_src} {$phpmfpminit_target}; chmod 755 {$phpmfpminit_target}");

			$phps = getMultiplePhpList();

			$phps = array_merge(array('php'), $phps);

			foreach ($phps as $k => $v) {
				$input['phpselected'] = $v;
				array_unique($input);

				$path = "/opt/configs/php-fpm/conf/{$v}";

				if (!file_exists("{$path}/php-fpm.d")) {
					exec("mkdir -p {$path}/php-fpm.d");
				}

				$phpfpm_target_default = "{$path}/php-fpm.d/default.conf";

				if ($v === 'php52m') {
					$phpfpm_global_pre = file_get_contents(getLinkCustomfile($phpfpm_path, "php52-fpm-global-pre.conf.tpl"));
					$phpfpm_global_post = file_get_contents(getLinkCustomfile($phpfpm_path, "php52-fpm-global-post.conf.tpl"));
					$phpfpm_default = file_get_contents(getLinkCustomfile($phpfpm_path, "php52-fpm-default.conf.tpl"));

					$phpfpm_target_global_pre = "{$path}/php-fpm_pre.conf";
					$phpfpm_target_global_post = "{$path}/php-fpm_post.conf";

					$phpfpm_parse_global_pre = getParseInlinePhp($phpfpm_global_pre, $input);
					file_put_contents($phpfpm_target_global_pre, $phpfpm_parse_global_pre);

					$phpfpm_parse_global_post = getParseInlinePhp($phpfpm_global_post, $input);
					file_put_contents($phpfpm_target_global_post, $phpfpm_parse_global_post);

					exec("cat {$phpfpm_target_global_pre} {$path}/php-fpm.d/*.conf {$phpfpm_target_global_post} > {$path}/php-fpm.conf");
				} else {
					$phpfpm_global = file_get_contents(getLinkCustomfile($phpfpm_path, "php53-fpm-global.conf.tpl"));
					$phpfpm_default = file_get_contents(getLinkCustomfile($phpfpm_path, "php53-fpm-default.conf.tpl"));

					$phpfpm_target_global = "{$path}/php-fpm.conf";

					$phpfpm_parse_global = getParseInlinePhp($phpfpm_global, $input);
					file_put_contents($phpfpm_target_global, $phpfpm_parse_global);

					if ($v === 'php') {
						exec("'cp' -f {$path}/php-fpm.conf /etc/php-fpm.conf");
					}
				}

				$phpfpm_parse_default = getParseInlinePhp($phpfpm_default, $input);
				file_put_contents($phpfpm_target_default, $phpfpm_parse_default);
			}
		} else {
			$input['phpinipath'] = "/home/kloxo/client/{$user}";
			$input['phpcgipath'] = "/usr/bin/php-cgi";

			if ($pclass === 'client') {
				$maxchildren = db_get_value("client", $user, "priv_q_phpfcgiprocess_num");

				if (($maxchildren === 'Unlimited') || ($maxchildren === '-')) {
					$maxchildren = '6';
				}

				$input['maxchildren'] = $maxchildren;

				$phpini_parse = getParseInlinePhp($phpini_cont, $input);
				$phpini_target = "/home/kloxo/client/{$user}/php.ini";
				file_put_contents($phpini_target, $phpini_parse);

				$fcgid_parse = getParseInlinePhp($fcgid_cont, $input);
				$fcgid_target = "/home/kloxo/client/{$user}/php.fcgi";
				$fcgid_target_old = "/home/kloxo/client/{$user}/php5.fcgi";
				file_put_contents($fcgid_target, $fcgid_parse);
				
				if (file_exists($fcgid_target_old)) {
					exec("'rm' -f {$fcgid_target_old}");
				}

				lxfile_unix_chmod($fcgid_target, "0755");

				$phps = getMultiplePhpList();
				$phps = array_merge(array('php'), $phps);

				foreach ($phps as $k => $v) {
					$phpfpm_path = "/opt/configs/php-fpm/tpl";

					if ($v === 'php52m') {
						$phpfpm_cont = file_get_contents(getLinkCustomfile($phpfpm_path, "php52-fpm-pool.conf.tpl"));
					} else {
						$phpfpm_cont = file_get_contents(getLinkCustomfile($phpfpm_path, "php53-fpm-pool.conf.tpl"));
					}

					$input['phpselected'] = $v;
					array_unique($input);

					$path = "/opt/configs/php-fpm/conf/{$v}";

					if (!file_exists("{$path}/php-fpm.d")) {
						exec("mkdir -p {$path}/php-fpm.d");
					}

					$phpfpm_target = "{$path}/php-fpm.d/{$user}.conf";

					$phpfpm_parse = getParseInlinePhp($phpfpm_cont, $input);

					file_put_contents($phpfpm_target, $phpfpm_parse);
				}

				$prefork_parse = getParseInlinePhp($prefork_cont, $input);
				$prefork_target = "/home/kloxo/client/{$user}/prefork.inc";
				file_put_contents($prefork_target, $prefork_parse);
			}
		}

		createRestartFile("restart-web");
	}

	function removeHtaccessOldPart()
	{
		$pclass = $this->main->getParentClass();

		$user = (isset($this->main->__var_web_user)) ? $this->main->__var_web_user : 'apache';

		$stlist[] = "###Start Kloxo PHP config Area";
		$stlist[] = "###Start Lxdmin Area";
		$stlist[] = "###Start Kloxo Area";
		$stlist[] = "###Start Lxadmin PHP config Area";

		$endlist[] = "###End Kloxo PHP config Area";
		$endlist[] = "###End Kloxo Area";
		$endlist[] = "###End Lxadmin PHP config Area";

		$endstring = $endlist[0];
		$startstring = $stlist[0];

		$htaccess_path = "/opt/configs/apache/tpl";

		if ($pclass === 'web') {
			$droot = $this->main->__var_docrootpath;
			$htaccess_cont = file_get_contents(getLinkCustomfile($htaccess_path, "htaccess.tpl"));
			$htaccess_parse = getParseInlinePhp($htaccess_cont, array(''));

			$htaccess_target = "{$droot}/.htaccess";

			$nowarning = true;

			file_put_between_comments("{$user}:apache", $stlist, $endlist,
				$startstring, $endstring, $htaccess_target, $htaccess_parse, $nowarning);

			lxfile_unix_chown($htaccess_target, "{$user}:apache");
		}
	}

	function dbactionAdd()
	{
		$this->createIniFile();

		// MR -- also restart php-fpm
		$phptype = db_get_value("serverweb", "pserver-" . $this->syncserver, "php-type");
		if (strpos($phptype, 'php-fpm') !== false) {
			createRestartFile('php-fpm');
		}
	}

	function updateSelected()
	{
		// MR -- simple way
		$this->createIniFile();
	}

	function dbactionUpdate($subaction)
	{
		switch ($subaction) {
			case "full_update":
				$this->createIniFile();
				break;
			case "ini_update":
				$this->createIniFile();
				break;
			case "htaccess_update":
				$this->removeHtaccessOldPart();
				break;
			default:
				$this->updateSelected();
				break;
		}
	}

}
