<?php

class client__sync extends lxDriverClass {

	function dbactionDelete()
	{
		lxshell_return("userdel", $this->main->username);

		lxfile_rm_rec("__path_client_root/{$this->main->nname}");
		lxfile_rm_rec("__path_customer_root/{$this->main->getPathFromName()}");

		exec("'rm' -f /opt/configs/php-fpm/conf/php*/php-fpm.d/{$this->main->username}.conf");
		createRestartFile("php-fpm");
	}

	function dbactionAdd()
	{
		global $sgbl;

		$path = "{$sgbl->__path_client_root}/{$this->main->nname}";

		lxfile_mkdir($path);
		lxfile_mkdir("{$path}/__backup");
		lxfile_generic_chown($path, "lxlabs");
		lxfile_generic_chown("{$path}/__backup", "lxlabs");

		$ret = $this->createUser();
		$this->setupDefaultDomain();

		return $ret;
	}

	function dosyncToSystemPost()
	{
		$username = $this->main->username;

	}

	static function getFromRemote($user, $server, $filepass, $dt, $p)
	{
		getFromRemote($server, $filepass, $dt, $p);
		lxfile_generic_chown_rec("$dt/$p", $user);
	}

	function createUser()
	{
		global $gbl, $sgbl, $login, $ghtml;

		$spath = "{$sgbl->__path_customer_root}/{$this->main->getPathFromName()}";
		$tpath = "{$sgbl->__path_client_root}/{$this->main->nname}";

		if (!$sgbl->isKloxo()) {
			return;
		}

		$password = $this->main->password;
		$cmd = "useradd";
		$shell = fix_disabled("--Disabled--", $sgbl->__var_noaccess_shell);
		$username = $this->main->getPathFromName();

		if (is_numeric($username[0])) {
			$username = "a$username";
		}

		$username = os_create_system_user($username, $password, $this->main->nname, $shell, "{$spath}/");

//		exec("ln -sf {$tpath}/__backup /home/{$this->main->nname}/__backup");

		lxfile_unix_chown($spath, "{$username}:apache");
		lxfile_unix_chmod($spath, "751");

		lxfile_unix_chmod($tpath, "777");

		$this->main->username = $username;

		$this->setQuota();
		$ret = array("__syncv_username" => $username);

		return $ret;
	}

	function setQuota()
	{
		if (!is_unlimited($this->main->priv->totaldisk_usage)) {
			$disk = $this->main->priv->totaldisk_usage * 1024;
			os_set_quota($this->main->username, $disk);
		} else {
			os_set_quota($this->main->username, 0);
		}
	}

	function changeUserPass()
	{
		global $gbl, $sgbl, $login, $ghtml;
		
		if (!$sgbl->isKloxo()) {
			return;
		}
		
		$pass = $this->main->password;
		
		// Need to use single quotes.
		lxshell_return("usermod", "-p", $pass, $this->main->username);
	}

	function shellModify()
	{
		global $gbl, $sgbl, $login, $ghtml;
		
		$shell = fix_disabled($this->main->shell, $sgbl->__var_noaccess_shell);
		lxshell_return("usermod", "-s", $shell,  $this->main->nname);
		
		if ($this->main->isOn('disable_system_flag')) {
			lxshell_return("usermod", "-L", $this->main->nname);
		} else {
			lxshell_return("usermod", "-U", $this->main->nname);
		}
	}

	function ToggleStatus()
	{
		if ($this->main->isOn('status')) {
			os_enable_user($this->main->username);
		} else {
			os_kill_process_user($this->main->username);
			os_disable_user($this->main->username);
		}
	}

	function dbactionUpdate($subaction)
	{
		switch($subaction) {
			case "enable":
			case "disable":
			case "toggle_status":
				$this->ToggleStatus();
				break;

			case "password":
				$this->changeAdminPass();
				$this->changeUserPass();
				break;

			case "shell_access":
				$this->shellModify();
				break;

			case "change_totaldisk_usage":
				$this->setQuota();
				break;

			case "createuser":
				return $this->createuser();
				break;

			case "skeleton":
				$file = "__path_client_root/{$this->main->nname}/skeleton.zip";
				lxfile_mv($this->main->__skeletion_tmp, $file);
				lxfile_generic_chown($file, "lxlabs");
				break;

			case "default_domain":
				$this->setupDefaultDomain();
				break;

			case "ssh_authorized_keys":
				sshconfig::writeAuthorizedKeys($this->main->username, $this->main->ssh_authorized_keys_f);
				break;
		}
	}

	function setupDefaultDomain()
	{
		global $gbl, $sgbl, $login, $ghtml;

		if (!$sgbl->isKloxo()) {
			return;
		}

		if (!$this->main->default_domain) {
			return;
		}

		lunlink("/home/{$this->main->getPathFromName('nname')}/public_html");

		if ($this->main->isDisabled('default_domain')) {
			return;
		}

		dprint("linking {$this->main->__var_defdocroot}\n");

		lxfile_symlink("/home/{$this->main->getPathFromName()}/{$this->main->__var_defdocroot}/", "/home/{$this->main->getPathFromName()}/public_html");
	}

	function changeAdminPass()
	{
		global $gbl, $sgbl, $login, $ghtml;

		$username = $sgbl->__var_program_name;

		if ($this->main->nname === 'admin') {
			dprint($this->main->realpass);
			$newp = client::createDbPass($this->main->realpass);
			$oldpass = lfile_get_contents("__path_admin_pass");

			exec("echo 'set Password=Password(\"$newp\")' | mysql -u $username -p$oldpass 2>&1", $out, $return);

			if ($return) {
				$out = implode(" ", $out);
				log_log("admin_error", "mysql change password Failed $out");

				exec_with_all_closed("sh /script/load-wrapper >/dev/null 2>&1 &");
				throw new lxException($login->getThrow("could_not_change_admin_pass"), '', $out);
			}

			$return = lfile_put_contents("__path_admin_pass", $newp);

			if (!$return) {
				log_log("admin_error", "Admin pass change failed $last_error");

				exec_with_all_closed("sh /script/load-wrapper >/dev/null 2>&1 &");
				
				throw new lxException($login->getThrow("could_not_change_admin_pass"), '', $last_error);
			}
		}
	}

	function do_backup()
	{
		global $gbl, $sgbl, $login, $ghtml;
		
		$name = $this->main->getPathFromName('nname');
		$fullpath = "$sgbl->__path_customer_root/$name/";
		lxfile_mkdir($fullpath);
		$list = lscandir_without_dot_or_underscore($fullpath);
		
		return array($fullpath, $list);
	}

	function do_restore($docd)
	{
		global $gbl, $sgbl, $login, $ghtml;
		
		$name = $this->main->getPathFromName('nname');
		$fullpath = "$sgbl->__path_customer_root/$name/";
		lxuser_mkdir($this->main->username, $fullpath);
		lxfile_generic_chown($docd, $this->main->username);
	//	lxuser_unzip_with_throw($this->main->username, $fullpath, $docd);
		lxshell_unzip('__system__', $fullpath, $docd);
		lxfile_generic_chown($fullpath, "{$this->main->username}:apache");
		lxfile_generic_chmod($fullpath, "0751");
	}
}

