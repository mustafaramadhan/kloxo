<?php
//
//	Kloxo, Hosting Panel
//
//	Copyright (C) 2000-2009	 LxLabs
//	Copyright (C) 2009-2010	 LxCenter
//
//	This program is free software: you can redistribute it and/or modify
//	it under the terms of the GNU Affero General Public License as
//	published by the Free Software Foundation, either version 3 of the
//	License, or (at your option) any later version.
//
//	This program is distributed in the hope that it will be useful,
//	but WITHOUT ANY WARRANTY; without even the implied warranty of
//	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//	GNU Affero General Public License for more details.
//
//	You should have received a copy of the GNU Affero General Public License
//	along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
//  This is the scavenge file. It is a master cronjob that runs once every 24h (default at 3:57 AM)
//

include_once "lib/html/displayinclude.php";

exit_if_secondary_master();
exit_if_another_instance_running();
scavenge_main();
	
function scavenge_main() {
	global $gbl, $sgbl, $login, $ghtml;

	initProgramlib('admin');
	
	$starttime = date('Y/m/d H:i:s', time());

	log_shell("Scavenge: Start");
	log_shell("Scavenge: Collect Traffic");
	passthru("$sgbl->__path_php_path ../bin/gettraffic.php");
	log_shell("Scavenge: Collect Quota");
	passthru("$sgbl->__path_php_path ../bin/collectquota.php");
	log_shell("Scavenge: Schedule backups");
	passthru("$sgbl->__path_php_path ../bin/common/schedulebackup.php");
	log_shell("Scavenge: Clear Sessions");
	passthru("$sgbl->__path_php_path ../bin/common/clearsession.php");
	log_shell("Scavenge: Self backup");
	passthru("$sgbl->__path_php_path ../bin/common/mebackup.php");
	log_shell("Scavenge: Check Cluster Disk Quota");
	checkClusterDiskQuota();

	$driverapp = $gbl->getSyncClass(null, 'localhost', 'web');
/*
	if ($driverapp === 'lighttpd') {
	    log_shell("Scavenge: Restarting lighttpd");
	    system("service lighttpd restart");
	}
*/
	log_shell("Scavenge: Fix log dir");
	passthru("$sgbl->__path_php_path ../bin/common/fixlogdir.php");
	log_shell("Scavenge: 'Easy Installer' update");
//	passthru("$sgbl->__path_php_path ../bin/easyinstaller-update.php");

	log_shell("Scavenge: Watchdog checks");
	$rs = get_all_pserver();

	foreach ($rs as $r) {
	    watchdog::addDefaultWatchdog($r);
	}

	log_shell("Scavenge: Collect LxGuard info");
	lxguard::collect_lxguard();
	log_shell("Scavenge: Fix MySQL root password");
	fix_all_mysql_root_password();
	log_shell("Scavenge: Auto update Kloxo");
	auto_update();

	$endtime = date('Y/m/d H:i:s', time());

	$subj = "Scavenge has completed";

	$msg  = "Scavenge started: {$starttime}\n";
	$msg .= "Scavenge completed: {$endtime}";

	if (file_exists("/usr/local/lxlabs/kloxo/etc/flag/enablescavengesendmail.flg")) {
	//	lx_mail(null, $login->contactemail, $subj, $msg);
		callInBackground("lx_mail", array(null, $login->contactemail, $subj, $msg));
	}
}
