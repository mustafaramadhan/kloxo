<?php 

include_once "lib/html/include.php"; 


easyinstaller_update_main();


function easyinstaller_update_main()
{
	// check/install/update easyinstaller applications
	if (lxfile_exists("/home/kloxo/httpd/easyinstaller") || lxfile_exists("/home/kloxo/httpd/remote-easyinstaller")) {
		application_update();
	}

	// check/install/update easyinstaller data
	easyinstaller_data_update();
}


function application_update()
{
	global $gbl, $login;

/*
	$checkflag = $gbl->getObject('general')->generalmisc_b;
	$easyinstallerflag = $checkflag->isOn('disableeasyinstaller');

	if ($easyinstallerflag)	{
		print("'Easy Installer' is disabled.\n");
		exit;
	}
*/

	if (lxfile_exists("/usr/local/lxlabs/kloxo/etc/flag/disableeasyinstaller.flg")) {
		print("'Easy Installer' is disabled.\n");
		exit;
	}

	print(fill_string("Fetch current 'Easy Installer' version", 50));
	$string = file_get_contents("http://download.lxcenter.org/download/easyinstaller/version.list");
	$rmt = unserialize($string);

	if (!$rmt) { 
		exec_with_all_closed("sh /script/load-wrapper >/dev/null 2>&1 &");
		throw new lxException($login->getThrow("could_not_get_application_version_list"));
	}

	print(" OK ");

	$remver = $rmt->applist['easyinstaller'];
	print("version is $remver\n");

	print(fill_string("Fetch local 'Easy Installer' version", 50));
	$loc = get_local_application_version_list();
	$locver = $loc->applist['easyinstaller'];
	print(" OK version is $locver\n");

	$updatelist = null;
	$notexisting = null;

	foreach($rmt->applist as $k => $v) {
		if ($k === 'easyinstaller') { continue; }

		if (lxfile_exists("/home/kloxo/httpd/remote-easyinstaller")) {
			if (!lxfile_exists("/home/kloxo/httpd/remote-easyinstaller/$k.zip")) {
				$notexisting[$k] = true;
				continue;
			}
		} else {
			if (!lxfile_exists("/home/kloxo/httpd/easyinstaller/$k")) {
				$notexisting[$k] = true;
				continue;
			}
		}

		if (app_version_cmp($loc->applist[$k], $v) === -1) {
			$updatelist[$k] = $v;
			continue;
		}

		$string = "Checking application $k";
		$string = fill_string($string, 50);
		$string .= " ";

		print($string);
		print("Is latest version $v\n");

	}

	foreach((array) $updatelist as $k => $v) {
		$string = "Updating application $k";
		$string = fill_string($string, 50);
		print("$string From {$loc->applist[$k]} to $v");
		update_application($k);
	}

	foreach((array) $notexisting as $k => $v) {
		$string = "Downloading new application $k";
		$string = fill_string($string, 50);
		print("$string "); 
		update_application($k);
	}
}



function update_application($appname)
{
	if (lxfile_exists("/home/kloxo/httpd/remote-easyinstaller/")) {
		update_remote_application($appname);
	} else {
		do_update_application($appname);
	}
}


function do_update_application($appname)
{
	if (!$appname) { return; }

	if (lxfile_exists("/tmp/".$appname.".zip")) {
		lxfile_rm("/tmp/".$appname.".zip");
	}

	system("cd /tmp ;  wget -q http://download.lxcenter.org/download/easyinstaller/".$appname.".zip");

	if (!lxfile_real("/tmp/".$appname.".zip")) { 
		print("Could not download $appname\n");
		return; 
	}

	lxfile_rm_rec("/home/kloxo/httpd/easyinstaller/$appname");

	system("cd /home/kloxo/httpd/easyinstaller ; unzip -qq /tmp/".$appname.".zip");

	lxfile_rm("/tmp/".$appname.".zip");

	print("Download Done\n");
}

function update_remote_application($appname)
{
	if (!$appname) { return; }

	if (lxfile_exists("/tmp/".$appname.".zip")) {
		lxfile_rm("/tmp/".$appname.".zip");
	}

	system("cd /tmp ; wget -q http://download.lxcenter.org/download/easyinstaller/$appname.zip");

	if (!lxfile_real("/tmp/$appname.zip")) { 
		print("Could not download $appname\n");
		return; 
	}

	$app = "/home/kloxo/httpd/remote-easyinstaller/$appname.zip";

	lxfile_rm($app);
	lxfile_mv("/tmp/$appname.zip", $app);

	print("Download Done\n");
}

