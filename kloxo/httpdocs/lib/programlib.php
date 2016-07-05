<?php 

function get_local_application_version_list()
{
	$list = all_easyinstaller__linux::getListofApps();
	$list = get_namelist_from_arraylist($list); 

	foreach($list as $k => $v) {
		if (csb($v, "__title")) {
			continue;
		}
		$info = all_easyinstaller::getAllInformation($v);
		$ret[$v] = $info['pversion'];
	}

	$loc= new Remote();
	$loc->applist = $ret;
	return $loc;
}

function easyinstaller_data_update()
{
	global $login;

	print(fill_string("Fetch current 'Easy Installer' version", 50));
	$string = file_get_contents("http://download.lxcenter.org/download/easyinstaller/version.list");
	$rmt = unserialize($string);

	if (!$rmt) {
		throw new lxException($login->getThrow("could_not_get_application_version_list"));
	}

	print(" OK ");
	$remver = $rmt->applist['easyinstaller'];
	print("version is $remver\n");

	if (lxfile_exists("/home/kloxo/httpd/easyinstallerdata")) {
		print(fill_string("Fetch local 'Easy Installer' version", 50));
		$loc = get_local_application_version_list();
		$locver = $loc->applist['easyinstaller'];
		print(" OK version is $locver\n");

		if ($remver != $locver) {
			print(fill_string("New 'Easy Installer' found", 50));
			print(" OK\n");
		}
	}

	print(fill_string("Checking for old easyinstallerdata.zip", 50));

	if (lxfile_exists("/tmp/easyinstallerdata.zip")) {
		lxfile_rm("/tmp/easyinstallerdata.zip");
	}

	print(" OK\n");
	print(fill_string("Downloading 'Easy Installer' data...", 50));
	system("cd /tmp ; wget -q http://download.lxcenter.org/download/easyinstaller/easyinstallerdata.zip");

	if (!lxfile_exists("/tmp/easyinstallerdata.zip")) {
		print(" ERROR\n");
		print("Could not download data from LxCenter.\nAborted.\n\n");
		return;
	}

	print(" OK\n");
	print(fill_string("Remove old 'Easy Installer' data", 50));

	lxfile_rm_rec("__path_kloxo_httpd_root/easyinstallerdata");
	lxfile_mkdir("__path_kloxo_httpd_root/easyinstallerdata");

	lxfile_rm_rec("/home/kloxo/httpd/easyinstallerdata");
	lxfile_mkdir("/home/kloxo/httpd/easyinstaller");
	lxfile_mkdir("/home/kloxo/httpd/easyinstallerdata");
	print(" OK\n");

	print(fill_string("Unpack new 'Easy Installer' data",50));
	lxshell_unzip("lxlabs", "__path_kloxo_httpd_root/easyinstallerdata/", "/tmp/easyinstallerdata.zip");
	system("cd /home/kloxo/httpd/easyinstallerdata ; unzip -qq /tmp/easyinstallerdata.zip");
	print(" OK\n");
 	print(fill_string("Remove downloaded 'Easy Installer' data zip file", 50));
	lxfile_rm("/tmp/easyinstallerdata.zip");
	print(" OK\n");
}
