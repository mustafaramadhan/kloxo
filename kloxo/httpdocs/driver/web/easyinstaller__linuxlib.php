<?php

class easyinstaller__linux extends LxDriverclass
{
	function dbactionAdd()
	{
		global $gbl, $sgbl, $login, $ghtml;

	//	dprintr($this->main);

		// Syncaddd happens when website is moved from one server to another.
		// Then you don't need to do anything to the system.

		if ($this->main->installdir) {
			if (lxfile_exists("{$this->main->__var_full_documentroot}/{$this->main->installdir}")) {
			//	throw new lxException($login->getThrow('path_already_exists'), '', $this->main->installdir);
			}
		}

		$res['src'] = "$sgbl->__path_kloxo_httpd_root/easyinstaller/{$this->main->appname}";

	/*
		if (!lxfile_exists($res['src'])) {
			throw new lxException($login->getThrow('can_not_access_application_directory'), '', $res['src']);
		}
	*/

		$res['title']=$this->main->easyinstallermisc_b->title;
		$res['email']=$this->main->easyinstallermisc_b->admin_email;
		$res['company']=$this->main->easyinstallermisc_b->admin_company;
		$res['realname']=$this->main->easyinstallermisc_b->real_name;
		$res['appname'] = $this->main->appname;
		$res['customer_name'] = $this->main->customer_name;
		$res['full_document_root'] = $this->main->__var_full_documentroot;

		$res['adminname']= $this->main->easyinstallermisc_b->admin_name;
		$res['adminpass']= $this->main->easyinstallermisc_b->admin_password;
		$res['admin_email_login']= $this->main->easyinstallermisc_b->admin_email_login;

		$res['dbname'] = $this->main->dbname;
		$res['dbuser'] = $this->main->dbuser;
		$res['realhost'] = $this->main->realhost;
		$res['install_flag'] = "false";
		$res['domain']   = $this->main->getParentName();
		$res['dbpass']  = $this->main->dbpass;
		$res['dbtype']=$this->main->dbtype;
		$res['systemuser'] = $this->main->__var_username;
		$res['installdir']=  $this->main->installdir;
		$res['path'] =$this->main->__var_full_documentroot;
		$res['src'] = "$sgbl->__path_kloxo_httpd_root/easyinstaller/{$this->main->appname}";

		if ($this->main->dbname) {
			$__tmpr = new mysqli($this->main->realhost, $this->main->dbuser, $this->main->dbpass);

			if (!$__tmpr) {
				exec_with_all_closed("sh /script/load-wrapper >/dev/null 2>&1 &");
				throw new lxException($login->getThrow('could_not_connect_to_mysql_server_from_web_server'), '', $this->main->realhost);
			}
		}

		$dir=$res['installdir'];
		$dompath= "{$res['path']}/";
		$source =  $res['src'];
		dprint("Copying ... $source to $dompath/$dir...\n");
		lxfile_rm_rec("$dompath/$dir/__kloxo");

		if (lxfile_exists("../etc/remote_easyinstaller")) {
			$url = lfile_get_contents("../etc/remote_easyinstaller");
			$url = trim($url);
			$tf = lx_tmp_file("easyinstaller");
			download_file("$url/{$this->main->appname}.zip", $tf);
			$type = os_getZipType($tf);

			if ($type !== "zip") {
				lxfile_rm($tf);

				exec_with_all_closed("sh /script/load-wrapper >/dev/null 2>&1 &");
				throw new lxException($login->getThrow('could_not_download_application_archive'), '', $this->main->appname);
			}

			$vd = createTempDir("/tmp", easyinstallerdir");
			lxshell_unzip("__system__", $vd, $tf);
			lxfile_cp_content("$vd/{$this->main->appname}", "$dompath/$dir");
			$filelist = lscandir_without_dot("$vd/{$this->main->appname}");
			lxfile_tmp_rm_rec($vd);
			lxfile_rm($tf);
		} else {
			lxfile_cp_content($source, "$dompath/$dir");
			$filelist = lscandir_without_dot($source);
		}

		if (check_file_if_owned_by("$dompath/$dir/__kloxo_directory_list", $this->main->__var_username)) {
			lfile_put_serialize("$dompath/$dir/__kloxo_directory_list", $filelist);
		}

		if (!lxfile_exists("$dompath/{$res['installdir']}/__kloxo/lxinstaller.inc")) {
			if (!lxfile_exists("$dompath/{$res['installdir']}/lxinstaller.inc")) {
				exec_with_all_closed("sh /script/load-wrapper >/dev/null 2>&1 &");
				throw new lxException($login->getThrow('could_not_copy_to_domain_root'), '', $dompath);
			} else {
				$file = "$dompath/{$res['installdir']}/lxinstaller.inc";
			}
		} else {
			$file = "$dompath/{$res['installdir']}/__kloxo/lxinstaller.inc";
		}

		if (!function_exists("__lxinstaller_{$this->main->appname}")) {
			include_once  $file;
		}

		if (!function_exists("__lxinstaller_{$this->main->appname}")) {
			exec_with_all_closed("sh /script/load-wrapper >/dev/null 2>&1 &");
			throw new lxException($login->getThrow('could_not_copy_document_root'), '', $dompath);
		}

		$olddir = getcwd();
		chdir("$dompath/{$res['installdir']}");


	//	lxfile_unix_chown_rec("$dompath/$dir", $this->main->__var_username);
	//	lxfile_unix_chmod_rec("$dompath/$dir", "0775");

		$func="__lxinstaller_{$this->main->appname}";

		try {
			$func($res);
		} catch(exception $e) {
			chdir($olddir);
			throw $e;
		}

	//	lxfile_unix_chown_rec("$dompath/$dir", $this->main->__var_username);
		lxfile_unix_chown_rec("$dompath/$dir", "{$this->main->__var_username}:{$this->main->__var_username}");
	//	lxfile_unix_chmod("$dompath/$dir", "0775");
		chdir($olddir);

	}

	function dbactionDelete()
	{
		$dir = trim($this->main->installdir, "/");

		if (!$this->main->customer_name) {
			return;
		}

		$path = "{$this->main->__var_full_documentroot}/$dir";

		if ($dir) {
			lxfile_rm_rec($path);
			return;
		}

		dprint("Installed in Document root.. Getting directory content\n");
		$list = lfile_get_unserialize("$path/__kloxo_directory_list");
		lunlink("$path/__kloxo_directory_list");

		if ($list) {
			$out = implode(" ", $list);
			dprint("Got directory content $out\n");

			foreach($list as $l) {
				$l = coreFfile::getRealpath($l);

				if ($l) { lxfile_rm_rec("$path/$l"); }
			}
		} else {
			dprint("No directory content removing everything\n");
		//	lxfile_rm_rec_content($path);
		}
	}


	function do_update()
	{
		global $gbl, $sgbl, $login, $ghtml;
		
		$this->do_snapshot();

		if (!function_exists("__lxupdater_{$this->main->appname}")) {
			include_once  "$sgbl->__path_kloxo_httpd_root/easyinstaller/{$this->main->appname}/lxinstaller.inc";
		}

		if (!function_exists("__lxupdater_{$this->main->appname}")) {
			return;
		}
		$func="__lxupdater_{$this->main->appname}";
		$res['dbname'] = $this->main->dbname;
		$res['dbuser'] = $this->main->dbuser;
		$res['realhost'] = $this->main->realhost;
		$res['domain']   = $this->main->getParentName();
		$res['dbpass']  = $this->main->dbpass;
		$res['dbtype']=$this->main->dbtype;
		$res['installdir']=$this->main->installdir;
		$func="__lxupdater_{$this->main->appname}";
		$func($res);
	}

	function do_snapshot()
	{
		$path = $this->main->__var_snapbase;

		$ddate = time();
		$name = "{$this->main->appname}-$ddate";

		lxfile_mkdir("$path/$name");
		lxfile_mkdir("$path/$name/file/");

		$rmt = new Remote();
		$save['ddate'] = time();
		$save['ap_nname'] = $this->main->nname;

		if ($this->main->dbname) {
			$tfile = tempnam("/tmp", "datadump");
			mysqldb__mysql::take_dump($this->main->dbname, $this->main->__var_dbuser, $this->main->__var_dbpass, $tfile);
			lxfile_cp($tfile, "$path/$name/database.dump");
			lxfile_rm($tfile);
		}

		$object = clone ($this->main);
		lxclass::clearChildrenAndParent($object);
		$rmt = new Remote();
		$rmt->data = $object;
		lfile_put_serialize("$path/$name/metadata.data", $rmt);
		$fullpath = "{$this->main->__var_full_documentroot}/{$this->main->installdir}";
		lxfile_cp_content("$fullpath", "$path/$name/file/");
	}

	function do_revert()
	{
		$path = $this->main->__var_snapbase;
		$v = $this->main->__var_snapname;

		if ($this->main->dbname) {
			$file = "$path/$v/database.dump";
			mysqldb__mysql::restore_dump($this->main->dbname, $this->main->__var_dbuser, $this->main->__var_dbpass, $file);
		}

		$this->dbactionDelete();
		$fullpath = "{$this->main->__var_full_documentroot}/{$this->main->installdir}";
		lxfile_mkdir($fullpath);
		lxfile_cp_content("$path/$v/file/", $fullpath);
	}

	function dbactionUpdate($subaction)
	{
		switch($subaction) {
			case "snapshot":
				$this->do_snapshot();

				break;

			case "revert":
				$this->do_revert();

				break;

			case "update":
				$this->do_snapshot();
				$this->do_update();

				break;
		}
	}
}
