<?php

function lxserver_main()
{
	global $gbl, $sgbl, $login, $ghtml;
	global $argv, $argc;

	// Set time limit to indefinite execution


	if ($argv[1] === 'slave') {
		$login = new Client(null, null, 'slave');

		//Initthisdef uses the db to load the drivers. NO longer callable in slave.
	//	$login->initThisDef();
		$gbl->is_slave = true;
		$gbl->is_master = false;
		$rmt = unserialize(lfile_get_contents("__path_slave_db"));
		$login->password = $rmt->password;
		$argv[1] = "Running as Slave";
	} else if ($argv[1] === 'master') {
		$login = new Client(null, null, 'admin');
		$gbl->is_master = true;
		$gbl->is_slave = false;
		$login->get();
		$argv[1] = "Running as Master";
	} else {
		print("Wrong arguments\n");

		exit;
	}

	$login->cttype = 'admin';

//	set_error_handler("lx_error_handler");
//	set_exception_handler("lx_exception_handler");

	set_time_limit(0);

	some_server();
}

function do_server_stuff()
{
	global $gbl, $sgbl, $login, $ghtml;

//	dprint("in Do server stuff\n");

	try {
		timed_execution();

		if ($sgbl->is_this_master()) {
			$schour = null;
			$schour = $login->getObject('general')->generalmisc_b->scavengehour;
			$scminute = $login->getObject('general')->generalmisc_b->scavengeminute;

		//	dprint("Cron exec $schour, $scminute\n");

			if ($schour) {
				cron_exec($schour, $scminute, "exec_scavenge");
			} else {
				cron_exec("3", "57", "exec_scavenge");
			}
		}
	} catch (exception $e) {
		print("Caught Exception: ");
		print($e->getMessage());
		print("\n");
	}
}

function cron_exec($hour, $minute, $func)
{
	static $localvar;

//	dprint("in Cron exec\n");
//	dprintr($localvar);

	$time = mktime($hour, $minute, 0, date('n'), date('j'), date("Y"));
	$now = time();

	if (isset($localvar[$func]) && $localvar[$func]) {
	//	dprint("Already execed \n");

		if ($now > $time + 2 * 60) {
			$localvar[$func] = false;
		}
		return;

	}

	if ($now > $time && $now < $time + 2 * 60) {
		$localvar[$func] = true;
		log_log("cron_exec", "Execing $func");
		$func();
	}
}

function timed_exec($time, $func)
{
	$v = "global_v$func";
	global $$v;

	$ct = time();

	if (($ct - $$v) >= $time * 30) {
	//	dprint("Executing at $ct {$$v} rd time $func\n");
		$$v = $ct;
		$func();
	}
}

function exec_scavenge()
{
	global $gbl, $sgbl, $login, $ghtml;

	dprint("Execing collect quota\n");

	$olddir = getcwd();
	lchdir("__path_program_htmlbase");

	exec_with_all_closed("$sgbl->__path_php_path ../bin/scavenge.php");

	lchdir($olddir);
}

function checkRestart()
{
	global $gbl, $sgbl, $login, $ghtml;

	$res = lscandir_without_dot("{$sgbl->__path_program_etc}/.restart");

	if ($res === false) {
		dprint(".restart does not exist... Creating\n");
		lxfile_mkdir("__path_program_etc/.restart");
		lxfile_generic_chown("{$sgbl->__path_program_etc}/.restart", "lxlabs");
	}

	foreach ((array)$res as $r) {
		if (csb($r, "._restart_")) {
			$cmd = strfrom($r, "._restart_");
		}

		lunlink("__path_program_etc/.restart/$r");
		dprint("Restarting $cmd\n");

		// THe 3,4 etc are the tcp ports of this program, and it should be closed, else some programs will grab it.
		//exec("/etc/init.d/$cmd restart  </dev/null >/dev/null 2>&1 3</dev/null 4</dev/null 5</dev/null 6</dev/null &");
		switch ($cmd) {
			case 'lxcollectquota':
				exec_justdb_collectquota();
				break;

			case 'openvz_tc':
				exec_openvz_tc();
				break;

			default:
				// MR -- temporary solution because this process always get 'nginxproxy'
				// if nginxproxy as webserver -- look like get info from slavedb

				if (strpos($cmd, 'proxy') !== false) {
					$t = str_replace('proxy', '', $cmd);
					$cmdlist = array($t, 'httpd');
				} else {
					$cmdlist = array($cmd);
				}

				foreach ($cmdlist as $key => $cmd) {
					$l = getLinkCustomfile("{$sgbl->__path_program_etc}/process", $cmd);
					
					$c = null;
					
					if (file_exists($l)) {
						$c = file_get_contents($l);
					}

					if ($c) {
						exec_with_all_closed($c);
					//	exec($c);
					} else {
						if (strpos($cmd, 'restart-') !== false) {
							exec_with_all_closed("sh /script/{$cmd} -y");
						} else {
							exec_with_all_closed("/etc/init.d/{$cmd} restart");
						//	exec("/etc/init.d/{$cmd} restart");
						}
					}
				}

				break;
		}
	}
}

function exec_openvz_tc()
{
	lxshell_background("sh", "__path_program_etc/openvz_tc.sh");
}

function special_bind_restart($cmd)
{
	global $gbl, $sgbl, $login, $ghtml;

	if (myPcntl_fork() === 0) {
		socket_close($sgbl->__local_socket);
		exec("/etc/init.d/$cmd restart  </dev/null >/dev/null 2>&1 &");
		exit;
	} else {
		myPcntl_wait();
	}
}

function reload_lxserver_password()
{
	global $gbl, $sgbl, $login, $ghtml;

	static $time;

	$stat = llstat("__path_admin_pass");
	$cur = $stat['mtime'];

	if ($cur > $time) {
		$rmt = lfile_get_contents("__path_admin_pass");
		$login->password = $rmt;
		$time = $cur;
	}

}

function root_main($d)
{
	reload_lxserver_password();

	try {
		$res = do_root_main($d);
		$res->exception = null;
	} catch (exception $e) {
		dprint("Coaught Execption: " . $e->getMessage());
		$res = new Remote();
		$res->ret = -1;
		$res->exception = $e;
	}

	return $res;
}

function do_root_main($data)
{
	dprintr("Remote: ");
	dprintr($data);

	return do_remote($data);
}

