<?php 

include_once "lib/php/linuxcorelib.php";
include_once "lib/html/linuxfslib.php";

function os_isSelfSystemUser()
{
	return (posix_getuid() === 0);
}

function os_isSelfSystemOrLxlabsUser()
{
	$uid = posix_getuid();

	if ($uid === 0) {
		return true;
	}

	$pwd = posix_getpwuid($uid);

	if ($pwd['name'] === 'lxlabs') {
		return true;
	}
}

function os_getUptime()
{
	$v = trim(file_get_contents("/proc/uptime"));
	$vv = strtilfirst($v, " ");

	return trim($vv);
}

function os_getLoadAvg($flag = false)
{
	$v =  trim(file_get_contents("/proc/loadavg"));
	$v = trimSpaces($v);
	$vv = explode(" ", $v);

	if ($flag) { return $vv[2]; }

	return  "{$vv[0]} {$vv[1]} {$vv[2]}";
}

function os_fix_fstab()
{
	$list = lfile_trim("/etc/fstab");

	foreach($list as $l) {
		$nl = trimSpaces($l);

		$n = explode(" ", $nl);

		if ($n[1] !== "/home" && $n[1] !== '/') {
			$out[] = $l;
			continue;
		}

		if (!csa($n[3], "usrquota")) {
			$mount[] = $n[1];
			$n[3] = "$n[3],usrquota,grpquota";
		}

		$o = implode("\t", $n);
		$out[] = $o;
	}

	$out = implode("\n", $out);
	$out .= "\n";
	dprint($out);

	lfile_put_contents("/etc/fstab", $out);
	dprintr($mount);

	foreach($mount as $m) {
		system("mount $m -o remount");
	}
}

function os_set_quota($username, $disk, $inode = null)
{
	if (!$username) {
		return;
	}

	if (!$disk) {
		$disk = 0;
	}

	if (!$inode) {
		$inode = 0;
	}

	// Issue #680 - Too high inode count in Kloxo quota - set 0 that unlimited
//	$inode = $disk * 500;
//	$inode = 0;

	if ($disk === 0) {
		// MR -- that meaning 'unlimited'
		$totalblock = 0;
		$totalinode = 0;
	} else {
		// MR -- assume 1GB space = 1.000.000 KB / 4 KB = 250.000 blocksize
		// where blocksize = 4KB -> inode = 25.000
		// So, make set $totalblock = $inode * 10

		if ($inode === 0) {
			$totalinode = (int) (($disk / 100) + 1);
		} else {
			$totalinode = (int) $inode;
		}

		$perblock = getFSBlockSizeInKb();
	//	$totalblock = $disk / $perblock;
		$totalblock = $disk;
	}

	lxshell_return("setquota", "-u", $username, $totalblock, $totalblock, $totalinode, $totalinode, "-a");
}

function os_createUserQuota()
{
	os_fix_fstab();
	system("quotacheck -afmuvg");
	system("quotaon -auvg");
}

function os_get_home_dir($user)
{
	$pwd = posix_getpwnam($user);

	return $pwd['dir'];
}

function os_get_allips()
{
	// the trick for apache when behind adsl/modem

	if (lxfile_exists("/home/ipalloc/webiplist")) {
		// get ip from file
		// format: ipv4, ipv6, device, port1, sslport1, port2, sslport2, webserver
		// blank if no value
		// minimum data ipv4
		$out = file_get_contents("/home/ipalloc/webiplist");
		$list = explode("\n", $out);
		foreach($list as $l) {
			$l = trim($l);
			$l = str_replace("\"", "", $l);
			$l = str_replace("'", "", $l);
			$u = explode(",", $l);
			$t =  trim($u[0]);
			if (!$t) { continue; }
			$iplist[] = $t;		
		}
	}

//	$iplist = getIPs_from_ifcfg();
	$iplist = getIPs_from_ipaddr();

	// MR -- change spec from substitution to complementary
	// and then remove duplicate array value

	array_unique($iplist);

	return $iplist;
}

function getIPs_from_ipaddr($withV6 = true)
{
	exec("ip addr|grep 'inet'|grep 'scope global'|awk '{print $2}'|awk -F '/' '{print $1}'" . ($withV6 ? "" : "|grep -v ':'"), $a);

	foreach($a as $k => $v) {
		if (($v === '127.0.0.1') || ($v === '::1')) {
			unset($a[$k]);
		}
	}

	return array_values($a);
}

// Bug #797 - Failed identify ip on apache
// MR - mimic from getCurrentIps() on ipaddress__redhatlib.php
// back to use mimic because possibility trouble on slave 
function getIPs_from_ifcfg()
{
	$p = '/etc/sysconfig/network-scripts/';
	$l = lscandir($p);

	foreach($l as $t => $f) {
		if (stristr($f, "ifcfg-")) {
			if ($f === 'ifcfg-lo') { continue; }
			
			$c = file_get_contents($p.$f);
			$a = explode("\n", $c);
			
			foreach($a as $k => $v) {
				if (stristr($v, "IPADDR=")) {
					$i = explode("=", $v);

					$ip = trim($i[1]);

					if ($ip === '127.0.0.1') { 
						continue;
					} else {
						$r[] = trim($i[1]);
					}
				}
			}
		}
	}
	
	return $r;
}

function os_disable_user($username)
{
	lxshell_return("usermod", "-L", $username);
}

function os_enable_user($username)
{
	lxshell_return("usermod", "-U", $username);
}

function os_kill_process_user($username)
{
	$uid = os_get_uid_from_user($username);
	if ($uid) {
		lxshell_return("pkill", "-u", $uid);
		lxshell_return("pkill", "-9", "-u", $uid);
	}
}

function os_create_default_slave_driver_db()
{
	$a['web'] = "apache";
	$a['webcache'] = "none";
	$a['dns'] = "bind";
	$a['spam'] = "bogofilter";
	slave_save_db("driver", $a);
}

function os_fix_lxlabs_permission()
{
	global $gbl, $sgbl, $login, $ghtml; 
	lxfile_mkdir("__path_program_root/session");
	lxfile_unix_chown_rec("__path_program_root", "lxlabs");
	lxfile_unix_chmod_rec("__path_program_root/sbin/", "0755");
	lxfile_unix_chmod_rec("__path_program_root/httpdocs/theme/", "0755");
	lxfile_unix_chmod("__path_program_etc", "0700");
	lxfile_unix_chmod("__path_program_root/log", "0700");
	lxfile_unix_chmod("__path_program_root/session", "0700");
}

function os_userdel($name)
{
	lxshell_return("userdel", "-r", $name);
}

function os_create_system_user($basename, $password, $id, $shell, $dir = "/tmp")
{
	global $login;

	dprint("In Create User $basename, $id $password $shell");
	$i = null;
	$name = $basename;
	
	while (true) {
		try {
			$ret = uuser__linux::checkIfUserExists($name, $id);
			if ($ret) {
				return $name;
			} else {
				break;
			}
		} catch (exception $e) {
			$i++;
			$name = "$basename$i";
		}
	}
	
	$ret = lxshell_return("useradd", "-m", "-c", uuser::getUserDescription($id), "-d", $dir, "-s", $shell, "-p", $password, $name);

	if ($ret) {
		// --- issue #638 - installation fails if 'admin' group already exists
		if (!lxfile_real("/var/cache/kloxo/kloxo-install-firsttime.flg")) {
			throw new lxException($login->getThrow("could_not_create_user"), '', $name);
		}
	}
	
	return $name;
}

function os_service_manage($serv, $act)
{
	exec_with_all_closed("/etc/init.d/$serv $act");
}

function os_create_program_service()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$pgm = $sgbl->__var_program_name;

	$pgminit = "__path_program_root/init/$pgm.init";
	
	lxfile_unix_chmod("/etc/init.d/$pgm", "0755");
}

function os_is_arch_sixfour()
{
    if (!lxfile_exists("/proc/xen")) {
		$arch = trim(`arch`);
	
		return $arch === 'x86_64';
    } else {
		$q = lfile_get_contents("/etc/rpm/platform");

		if ($q === "i686-redhat-linux") {
		    return false;
		}

		return true;
    }
}

function os_is_php_six_four()
{
    $v = lxshell_output("rpm -q --queryformat '%{ARCH}' php");
    $v = trim($v);
	
    return ($v === "x86_64");
}

function os_restart_program()
{
	global $gbl, $sgbl, $login, $ghtml; 
	
	$pgm = $sgbl->__var_program_name;
	// We just need to kill the main server, and leave the wrapper alone.
	exec_with_all_closed("sh /script/restart --force");
}

function os_get_network_gateway()
{
	$res = lxshell_output("route", "-n");
	$re = explode("\n", $res);

	foreach($re as $r) {
		if (csb($r, "0.0.0.0")) {
			$r = trimSpaces($r);
			$o = explode(" ", $r);
			
			return trim($o[1]);
		}
	}
	
	return null;
}

function os_get_default_ethernet()
{
	$res = lxshell_output("route", "-n");
	$re = explode("\n", $res);

	foreach($re as $r) {
		if (csb($r, "0.0.0.0")) {
			$r = trimSpaces($r);
			$o = explode(" ", $r);
			
			return trim($o[7]);
		}
	}
	
	return null;
}

function os_getCpuNum()
{
	$list = lfile("/proc/cpuinfo");

	$i = 0;

	foreach($list as $l) {
		if (csa($l, "processor")) {
			$i++;
		}
	}
	
	return $i;
}
function os_get_hostname()
{
//	$v = `hostname`;
//	return trim($v);

//	exec("hostname", $out);
//	return $out[0];

//	return gethostname();
	return php_uname('n');
}

function os_get_user_from_uid($uid)
{
	$pwd = posix_getpwuid($uid);

	if ($pwd['name']) {
		return $pwd['name'];
	}

	return $uid;
}

function os_get_group_from_gid($gid)
{
	$pwd = posix_getgrgid($gid);

	if ($pwd['name']) {
		return $pwd['name'];
	}

	return $gid;
}

function os_get_uid_from_user($user)
{
	$pwd = posix_getpwnam($user);

	if ($pwd['uid']) {
		return $pwd['uid'];
	}

	// If the user doesn't exist return a very large number.
	return 10000000;
}

function os_get_gid_from_user($user)
{
	$pwd = posix_getpwnam($user);

	if ($pwd['gid']) {
		return $pwd['gid'];
	}

	// If the user doesn't exist return a very large number.
	return 10000000;
}

function os_getpid()
{
	return posix_getpid();
}

// Please note that os_get_commandname actually returns the first argument (NOt the actual command),
// since the actuall command would be 'php'
function os_get_commandname($pid)
{
	if (!lxfile_exists("/proc/$pid")) {
		return null;
	}

	$cmd = lfile_get_contents("/proc/$pid/cmdline");
	$cmd = explode("\0", $cmd);
	
	return $cmd[1];
}

// If pcntl is enabled, then sigterm is automatically defined. Silll
@ define('SIGTERM', 15);
@ define('SIGKILL', 9);

function os_killpid_by_name($name)
{
	system("pkill -f $name");
}

function os_killpid($pid)
{
	if (!$pid) {
		return;
	}
	
	if (intval($pid) < 10) {
		return;
	}
	
	posix_kill($pid, SIGTERM);
	usleep(10000);
	posix_kill($pid, SIGKILL);
}

function os_set_path()
{
	global $gbl, $sgbl, $login, $ghtml; 
	putenv("PATH=/sbin/:/usr/sbin/:/bin/:/usr/bin:/usr/local/bin/:/usr/local/sbin:$sgbl->__path_program_root/bin:$sgbl->__path_program_root/sbin:");
}
