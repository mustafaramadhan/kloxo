<?php

include_once "lib/html/include.php"; 
initProgram('admin');

setSetupApp();

function setSetupApp()
{
	log_cleanup("*** Rainloop Webmail setup ***", $nolog);
	
	$path = "/home/kloxo/httpd/webmail/rainloop";
	
	if (!file_exists("{$path}/index.php")) {
		log_cleanup("- Application not exists. Exit", $nolog);
		return;
	}

	$path = "/home/kloxo/httpd/webmail/rainloop";

	$key = randomString(8);

	$appfiles = glob("{$path}//rainloop/v/*/app/libraries/RainLoop/Config/Application.php", GLOB_MARK);

	log_cleanup("- Change application.ini to application.ini.php", $nolog);

	if ($appfiles) {
		foreach ($appfiles as $k => $v) {
			$appcontent = lfile_get_contents($v);
			$appcontent = str_replace("'application.ini'", "'application.ini.php'", $appcontent);
			$appcontent = str_replace("; RainLoop Webmail configuration file", ";<' . '?php exit;?' . '> RainLoop Webmail configuration file", $appcontent);
			lfile_put_contents($v, $appcontent);
		}
	}

	
	$datfiles = glob("{$path}/data/*/_default_/configs/application.ini", GLOB_MARK);

	log_cleanup("- Change admin password", $nolog);

	if ($datfiles) {
		foreach ($datfiles as $k => $v) {
			exec("mv -f {$v} {$v}.php");
			$datcontent = lfile_get_contents("{$v}.php");
			$datcontent = str_replace("\"12345\"", "\"{$key}\"", $datcontent);
			lfile_put_contents("{$v}.php", $datcontent);
		}
	}

	log_cleanup("- Preparing Database", $nolog);
	log_cleanup("-- No need database", $nolog);
}

