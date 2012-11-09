<?php

class serverweb__ extends lxDriverClass
{
	function __construct()
	{
	}


	function dbactionUpdate($subaction)
	{
		global $gbl, $sgbl, $login, $ghtml;

		switch($subaction) {
			case "apache_optimize":
				$this->set_apacheoptimize();

				break;

			case "fix_chownchmod":
				$this->set_fixchownchmod();

				break;

			case "mysql_convert":
				$this->set_mysqlconvert();

				break;

			case "php_type":
				$this->set_phptype();

				break;

			case "php_branch":
				$this->set_phpbranch();

				break;
		}
	}

	function set_apacheoptimize()
	{
		global $gbl, $sgbl, $login, $ghtml;

		$nolog = '--nolog';

		$scripting = '/usr/local/lxlabs/kloxo/bin/fix/apache-optimize.php';

		switch($this->main->apache_optimize) {
			case 'default':
				lxshell_return("lxphp.exe", $scripting, "--select=default", $nolog);
				break;

			case 'optimize':
				lxshell_return("lxphp.exe", $scripting, "--select=optimize", $nolog);
				break;
		}
	}

	function set_fixchownchmod()
	{
		global $gbl, $sgbl, $login, $ghtml;

		$nolog = '--nolog';

		$scripting = '/usr/local/lxlabs/kloxo/bin/fix/fix-chownchmod.php';

		switch($this->main->fix_chownchmod) {
			case 'fix-ownership':
				lxshell_return("lxphp.exe", $scripting, "--select=chmod", $nolog);
				break;
			case 'fix-permissions':
				lxshell_return("lxphp.exe", $scripting, "--select=chown", $nolog);
				break;
			case 'fix-ALL':
				lxshell_return("lxphp.exe", $scripting, "--select=all", $nolog);
				break;
		}
	}

	function set_mysqlconvert()
	{
		global $gbl, $sgbl, $login, $ghtml;

		$nolog = '--nolog';

		$scripting = '/usr/local/lxlabs/kloxo/bin/fix/mysql-convert.php';

		switch($this->main->mysql_convert) {
			case 'to-myisam':
				$t = 'myisam';
				lxshell_return("lxphp.exe", $scripting, "--engine=myisam", $nolog);
				break;
			case 'to-innodb':
				lxshell_return("lxphp.exe", $scripting, "--engine=innodb", $nolog);
				break;
		}
	}

	function set_phptype()
	{
		global $gbl, $sgbl, $login, $ghtml;

		$nolog = '--nolog';

		$t = (isset($this->main->php_type)) ? $this->main->php_type : null;

		if (((stripos($t, '_ruid2') !== false)) || (stripos($t, '_itk') !== false)) {
			if ($this->main->secondary_php === 'on') {
				throw new lxexception('secondary_php_not_work_with_php-type_selected', 'parent');
			}
		}

		$ullkfapath  = '/usr/local/lxlabs/kloxo/file/apache';
		$ullkfpfpath = '/usr/local/lxlabs/kloxo/file/php-fpm';

		$ehcpath  = '/etc/httpd/conf';
		$ehcdpath = '/etc/httpd/conf.d';
		$ehckpath = '/etc/httpd/conf/kloxo';

		$hhcpath = '/home/httpd/conf';

		$hapath  = '/home/apache';
		$hacpath = '/home/apache/conf';
		$haepath = '/home/apache/etc';
		$haecpath = '/home/apache/etc/conf';
		$haecdpath = '/home/apache/etc/conf.d';

		$ullkbffwphp = '/usr/local/lxlabs/kloxo/bin/fix/fixweb.php';

		if (isWebProxyOrApache()) {
			//--- some vps include /etc/httpd/conf.d/swtune.conf
			lxshell_return("rm", "-f", $ehcdpath."/swtune.conf");

			exec("cp -rf {$ullkfapath} /home");

			if (!lfile_exists("{$ehcdpath}/~lxcenter.conf")) {
				lxfile_cp(getLinkCustomfile($haecdpath, "~lxcenter.conf"), $ehcdpath."/~lxcenter.conf");
				lxfile_cp(getLinkCustomfile($haecpath, "httpd.conf"), $ehcpath."/httpd.conf");
			}

			//--- don't use '=== true' but '!== false'
			if (stripos($t, 'mod_php') !== false) {
				$this->set_modphp($t);
			} elseif (stripos($t, 'suphp') !== false) {
				$this->set_suphp();
			} elseif (stripos($t, 'php-fpm') !== false) {
				$this->set_phpfpm();
			} elseif (stripos($t, 'fcgid') !== false) {
				$this->set_fcgid();
			}

			$this->set_mpm($t);
		}

		$this->set_secondary_php();
	}

	function set_modphp($type)
	{
		$ehcdpath = '/etc/httpd/conf.d';
		$haecdpath = '/home/apache/etc/conf.d';

		$this->rename_to_nonconf();

		// use > that equal to lxfile_rm + echo >>
		exec("echo 'HTTPD=/usr/sbin/httpd' >/etc/sysconfig/httpd");

		if ($type === 'mod_php') {
			// no action here
		} elseif ($type === 'mod_php_ruid2') {
			lxshell_return("yum", "-y", "install", "mod_ruid2");
			lxshell_return("yum", "-y", "update", "mod_ruid2");
			lxfile_rm("{$ehcdpath}/ruid2.nonconf");
			lxfile_cp(getLinkCustomfile($haecdpath, "ruid2.conf"), $ehcdpath."/ruid2.conf");
		} elseif ($type === 'mod_php_itk') {
			exec("echo 'HTTPD=/usr/sbin/httpd.itk' >/etc/sysconfig/httpd");
		}

		lxfile_cp(getLinkCustomfile($haecdpath, "php.conf"), $ehcdpath."/php.conf");
		lxfile_rm($ehcdpath."/php.nonconf");

		$this->remove_phpfpm();
	}

	function set_suphp()
	{
		$ehcdpath = '/etc/httpd/conf.d';
		$haepath = '/home/apache/etc';
		$haecdpath = '/home/apache/etc/conf.d';

		lxshell_return("yum", "-y", "install", "mod_suphp");
		$ret = lxshell_return("yum", "-y", "update", "mod_suphp");

		if ($ret) {
			throw new lxexception('mod_suphp_update_failed', 'parent');
		}

		$ver = getPhpVersion();

		$phpbranch = getPhpBranch();

		$this->rename_to_nonconf();

		if (version_compare($ver, "5.3.0", ">")) {
			lxfile_cp(getLinkCustomfile($haepath, "suphp.conf"), "/etc/suphp.conf");
			$this->remove_phpfpm();
		} else {
			lxfile_cp(getLinkCustomfile($haepath, "suphp_pure.conf"), "/etc/suphp.conf");
			$this->set_php_pure();
		}

		exec("sh /script/fixphp --nolog");

		lxfile_rm($ehcdpath."/suphp.nonconf");
	}

	function set_phpfpm()
	{
		$ehcdpath = '/etc/httpd/conf.d';
		$haepath = '/home/apache/etc';
		$haecdpath = '/home/apache/etc/conf.d';

		$this->rename_to_nonconf();

		$ver = getRpmVersion('httpd');

		if (version_compare($ver, "2.4.0", ">=") !== false) {
			lxfile_cp(getLinkCustomfile($haecdpath, "proxy_fcgi.conf"), $ehcdpath."/proxy_fcgi.conf");
			lxfile_rm($ehcdpath."/proxy_fcgi.nonconf");
		} else {
			$phpbranch = getPhpBranch();

			lxshell_return("yum", "-y", "install", "mod_fastcgi");
			$ret = lxshell_return("yum", "-y", "update", "mod_fastcgi");

			if ($ret) {
				throw new lxexception('mod_fastcgi_update_failed', 'parent');
			}

			lxshell_return("yum", "-y", "install", "{$phpbranch}-fpm");
			$ret = lxshell_return("yum", "-y", "update", "{$phpbranch}-fpm");

			if ($ret) {
				throw new lxexception('{$phpbranch}-fpm_update_failed', 'parent');
			}

			lxfile_cp(getLinkCustomfile($haecdpath, "fastcgi.conf"), $ehcdpath."/fastcgi.conf");
			lxfile_rm($ehcdpath."/fastcgi.nonconf");
		}

		lxshell_return("chkconfig", "php-fpm", "on");
		createRestartFile('phpfpm');
	}

	function set_fcgid()
	{
		$ehcdpath = '/etc/httpd/conf.d';
		$haepath = '/home/apache/etc';
		$haecdpath = '/home/apache/etc/conf.d';

		$ver = getPhpVersion();


		lxshell_return("yum", "-y", "install", "mod_fcgid");
		$ret = lxshell_return("yum", "-y", "update", "mod_fcgid");

		if ($ret) {
			throw new lxexception('mod_fcgid_update_failed', 'parent');
		}

		if (version_compare($ver, "5.3.0", "<")) {
			$this->set_php_pure();
		} else {
			$this->remove_phpfpm();
		}

		$this->rename_to_nonconf();

		lxfile_cp(getLinkCustomfile($haecdpath, "fcgid.conf"), $ehcdpath."/fcgid.conf");
		lxfile_rm($ehcdpath."/fcgid.nonconf");
	}

	function remove_phpfpm()
	{
		$phpbranch = getPhpBranch();

		$ret = lxshell_return("yum", "-y", "remove", "{$phpbranch}-fpm");

		if ($ret) {
			throw new lxexception('{$phpbranch}-fpm_update_failed', 'parent');
		}
	}

	function rename_to_nonconf()
	{
		$ehcdpath = '/etc/httpd/conf.d';

		lxfile_mv($ehcdpath."/php.conf", $ehcdpath."/php.nonconf");
		lxfile_mv($ehcdpath."/fastcgi.conf", $ehcdpath."/fastcgi.nonconf");
		lxfile_mv($ehcdpath."/fcgid.conf", $ehcdpath."/fcgid.nonconf");
		lxfile_mv($ehcdpath."/ruid2.conf", $ehcdpath."/ruid2.nonconf");
		lxfile_mv($ehcdpath."/suphp.conf", $ehcdpath."/suphp.nonconf");
		lxfile_mv($ehcdpath."/proxy_fcgi.conf", $ehcdpath."/proxy_fcgi.nonconf");
	}

	function set_mpm($type)
	{
		if (stripos($type, '_worker') !== false) {
			exec("echo 'HTTPD=/usr/sbin/httpd.worker' >/etc/sysconfig/httpd");
		} elseif (stripos($type, '_event') !== false) {
			exec("echo 'HTTPD=/usr/sbin/httpd.event' >/etc/sysconfig/httpd");
		} else {
			exec("echo 'HTTPD=/usr/sbin/httpd' >/etc/sysconfig/httpd");
		}

		$scripting = '/usr/local/lxlabs/kloxo/bin/fix/fixweb.php';
			
		lxshell_return("lxphp.exe", $scripting, "--select=all", "--nolog");

		$ret = lxshell_return("service", "httpd", "restart");

		if ($ret) {
			throw new lxexception('httpd_restart_failed', 'parent');
		}
	}

	function set_phpbranch($branch = null)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$ehcdpath = '/etc/httpd/conf.d';

		$installed = isRpmInstalled('yum-plugin-replace');

		if (!$installed) {
			$ret = lxshell_return("yum", "-y", "install", "yum-plugin-replace");

			if ($ret) {
				throw new lxexception('yum-plugin-replace_update_failed', 'parent');
			}
		}

		$nolog = '--nolog';

		$scripting = '/usr/local/lxlabs/kloxo/bin/fix/php-branch.php';

		if ($branch) {
			$branchselect = $branch;
		} else {
			$branchselect = $this->main->php_branch;
		}

		lxshell_return("lxphp.exe", $scripting, "--select={$branchselect}", $nolog);
/*
		// MR -- to make sure this modules convert too
		lxshell_return("yum", "install", "-y", "{$branchselect}-mbstring",
				"{$branchselect}-mysql", "{$branchselect}-imap", "{$branchselect}-pear",
				"{$branchselect}-devel", "{$branchselect}-fpm");
*/
		$scripting = '/usr/local/lxlabs/kloxo/bin/fix/fixweb.php';

		lxshell_return("lxphp.exe", $scripting, "--select=all", $nolog);

		$installed = isRpmInstalled("{$branchselect}-fpm");

		if ($installed) {
			lxshell_return("chkconfig", "php-fpm", "on");
			createRestartFile('php-fpm');
		}

		if (stripos('mod_php', $this->main->php_type) === false) {
			lxfile_mv($ehcdpath."/php.conf", $ehcdpath."/php.nonconf");
		}
	}

	function set_php_pure()
	{
		$phpbranch = getPhpBranch();

		if (!file_exists('/usr/bin/php_pure')) {
			$this->set_phpbranch('php52');

			lxfile_cp('/usr/bin/php', '/usr/bin/php_pure');
			lxfile_cp('/usr/bin/php-cgi', '/usr/bin/php-cgi_pure');

			$this->set_phpbranch($phpbranch);
		}

	}

	function set_secondary_php()
	{
		$ehcdpath = '/etc/httpd/conf.d';
		$haecdpath = '/home/apache/etc/conf.d';

		$epath = '/etc';
		$haepath = '/home/apache/etc';

		if ($this->main->secondary_php === 'on') {
			if (stripos($this->main->php_type, 'suphp') !== false) {
				lxfile_mv($ehcdpath."/suphp52.conf", $ehcdpath."/suphp52.nonconf");
			} else {
				lxfile_cp(getLinkCustomfile($haecdpath, "suphp52.conf"), $ehcdpath."/suphp52.conf");
				lxfile_rm($ehcdpath."/suphp52.nonconf");

				$ret = lxshell_return("yum", "-y", "install", "mod_suphp");

				if ($ret) {
					throw new lxexception('install_mod_suphp_failed', 'parent');
				}

				lxfile_cp(getLinkCustomfile($haecdpath, "suphp52.conf"), $ehcdpath."/suphp52.conf");

				lxfile_mv($ehcdpath."/suphp.conf", $ehcdpath."/suphp.nonconf");
			}

		} else {
			lxfile_mv($ehcdpath."/suphp52.conf", $ehcdpath."/suphp52.nonconf");
		}	
	}
}
