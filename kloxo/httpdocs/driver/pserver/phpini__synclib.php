<?php

class phpini__sync extends Lxdriverclass
{
	function initString()
	{
		$this->main->fixphpIniFlag();

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
		$extrabasedir = $input['extrabasedir'] = (isset($this->main->__var_extrabasedir)) ? $this->main->__var_extrabasedir : '';

		$input['phpmlist'] = getMultiplePhpList();

		$phpini_path = "/opt/configs/phpini/tpl";
		$apache_path = "/opt/configs/apache/tpl";

		$phpini_cont = file_get_contents(getLinkCustomfile($phpini_path, "php.ini.tpl"));
	//	$fcgid_cont = file_get_contents(getLinkCustomfile($apache_path, "php.fcgi.tpl"));
		$prefork_cont = file_get_contents(getLinkCustomfile($apache_path, "prefork.inc.tpl"));

		$suphp_cont = file_get_contents(getLinkCustomfile($apache_path, "etc_suphp.conf.tpl"));
		$suphp2_cont = file_get_contents(getLinkCustomfile($apache_path, "suphp2.conf.tpl"));

		if (!file_exists("/etc/php-fpm.d")) {
			lxfile_mkdir("/etc/php-fpm.d");
		}

		if (!file_exists("/opt/configs/php-fpm/sock")) {
			exec("mkdir -p /opt/configs/php-fpm/sock");
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
			$phpscanpath = "/etc/php.d";
			$input['phpcgipath'] = "/usr/bin/php-cgi";

			$phpini_parse = getParseInlinePhp($phpini_cont, $input);
			$phpini_target = '/etc/php.ini';
			file_put_contents($phpini_target, $phpini_parse);

		//	$fcgid_target_old = '/home/kloxo/client/*.fcgi';
		//	@exec("'rm' -f {$fcgid_target_old}");

		//	$fcgid_parse = getParseInlinePhp($fcgid_cont, $input);
		//	$fcgid_target = '/home/kloxo/client/php.fcgi';

		//	file_put_contents($fcgid_target, $fcgid_parse);

		//	exec("'cp' -f /opt/configs/apache/tpl/php*.fcgi /home/kloxo/client");
			lxfile_unix_chmod($fcgid_target, "0755");

			$suphp_parse = getParseInlinePhp($suphp_cont, $input);
			$suphp_target = '/etc/suphp.conf';

			file_put_contents($suphp_target, $suphp_parse);
	
			$suphp2_parse = getParseInlinePhp($suphp2_cont, $input);
			$suphp2_target = '/etc/httpd/conf.d/suphp2.conf';

			if (file_exists($suphp2_target)) {
				file_put_contents($suphp2_target, $suphp2_parse);
			}

			$phpfpm_path_etc = "/opt/configs/php-fpm/etc";
			$phpfpm_path = "/opt/configs/php-fpm/tpl";

			if (!file_exists($phpfpm_path)) {
				$phpfpm_path_etc = "../file/php-fpm/etc";
				$phpfpm_path = "../file/php-fpm/tpl";
			}

			$phps = array_merge(array('php'), $input['phpmlist']);

			foreach ($phps as $k => $v) {
				$input['phpinipath'] = "/opt/{$v}/custom";
				$input['phpscanpath'] = "/opt/{$v}/etc/php.d";
				$input['phpcgipath'] = "/opt/{$v}/usr/bin/php-cgi";

				$w = str_replace('m', '', $v);

			//	$fcgid_parse = getParseInlinePhp($fcgid_cont, $input);
			//	$fcgid_target = "/home/kloxo/client/{$w}.fcgi";
			//	file_put_contents($fcgid_target, $fcgid_parse);

			//	lxfile_unix_chmod($fcgid_target, "0755");

				$input['phpselected'] = $v;
			//	array_unique($input);

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

					// MR -- don't move after 'merge' .conf
					$phpfpm_parse_default = getParseInlinePhp($phpfpm_default, $input);
					file_put_contents($phpfpm_target_default, $phpfpm_parse_default);

					exec("cat {$phpfpm_target_global_pre} {$path}/php-fpm.d/*.conf {$phpfpm_target_global_post} > {$path}/php-fpm.conf");
				} else {
					$phpfpm_global = file_get_contents(getLinkCustomfile($phpfpm_path, "php53-fpm-global.conf.tpl"));
					$phpfpm_default = file_get_contents(getLinkCustomfile($phpfpm_path, "php53-fpm-default.conf.tpl"));

					$phpfpm_target_global = "{$path}/php-fpm.conf";

					$phpfpm_parse_global = getParseInlinePhp($phpfpm_global, $input);
					file_put_contents($phpfpm_target_global, $phpfpm_parse_global);

					$phpfpm_parse_default = getParseInlinePhp($phpfpm_default, $input);
					file_put_contents($phpfpm_target_default, $phpfpm_parse_default);
				/*
					// MR -- don't need it because Kloxo-MR using special path for php-branch
					if ($v === 'php') {
						exec("'cp' -f {$path}/php-fpm.conf /etc/php-fpm.conf");
					}
				*/
				}
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

			//	$fcgid_target_old = "/home/kloxo/client/{$user}/*.fcgi";
			//	@exec("'rm' -f {$fcgid_target_old}");

			//	$fcgid_parse = getParseInlinePhp($fcgid_cont, $input);
			//	$fcgid_target = "/home/kloxo/client/{$user}/php.fcgi";
			//	file_put_contents($fcgid_target, $fcgid_parse);

			//	lxfile_unix_chmod($fcgid_target, "0755");

				$phps = array_merge(array('php'), $input['phpmlist']);

				foreach ($phps as $k => $v) {
					$phpfpm_path = "/opt/configs/php-fpm/tpl";

					if ($v === 'php52m') {
						$phpfpm_cont = file_get_contents(getLinkCustomfile($phpfpm_path, "php52-fpm-pool.conf.tpl"));
					} else {
						$phpfpm_cont = file_get_contents(getLinkCustomfile($phpfpm_path, "php53-fpm-pool.conf.tpl"));
					}

					$input['phpselected'] = $v;
				//	array_unique($input);

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

		createRestartFile("restart-php-fpm");
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

		$input['phpmlist'] = getMultiplePhpList();

		$htaccess_path = "/opt/configs/apache/tpl";
		$htaccess_cont = file_get_contents(getLinkCustomfile($htaccess_path, "htaccess.tpl"));

		if ($pclass === 'web') {
			$droot = $this->main->__var_docrootpath;

			$htaccess_parse = getParseInlinePhp($htaccess_cont, $input);

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
		$phptype = db_get_value("serverweb", "pserver-" . $this->syncserver, "php_type");
		if (strpos($phptype, 'php-fpm') !== false) {
			createRestartFile('restart-php-fpm');
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
