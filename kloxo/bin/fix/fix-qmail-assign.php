<?php

// by mustafa@bigraf.com for Kloxo-MR

include_once "lib/html/include.php";

resetQmailAssign();

function resetQmailAssign($nolog = null)
{
	$mpath = "/home/lxadmin/mail/domains";

	$pass = slave_get_db_pass();

	$con = new mysqli("localhost", "root", $pass);

	if (!$con) {
		die('Could not connect: ' . $con->connect_error);
	}

	$con->select_db("vpopmail");

	$result = $con->query("SELECT pw_name, pw_domain, pw_dir FROM vpopmail");

	if (!isset($result)) { return; }

	$n = array();

	while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
		// MR -- need this team to fix issue where prefix account as the same as prefix domain
		//       like your@yourdomain.com (the same 'your')
		$temp = str_replace($mpath . "/", '', $row['pw_dir']);
		$n[$row['pw_domain']] = $mpath . "/" . str_replace("/" . $row['pw_name'], '', $temp);
	}

	$ua = '';
	$rh = '';
	$vd = '';

	log_cleanup("Reset Qmail Assign for domains (also rcpthosts and virtualdomains)", $nolog);

	$bpath = "/var/qmail/bin";
	$cpath = "/var/qmail/control";
	$upath = "/var/qmail/users";

	exec("'rm' -f {$upath}/assign {$cpath}/rcpthosts {$cpath}/morercpthosts* {$cpath}/virtualdomains");

	foreach ($n as $k => $v) {
		log_cleanup("- assign for '{$k}'", $nolog);

		$o = fileowner($v);
		$ua .= "+{$k}-:{$k}:{$o}:{$o}:{$v}:-::\n";

		if (isRpmInstalled('qmail-toaster')) {
			// MR -- also fix /home/lxadmin/mail/bin to /home/vpopmail/bin
			$d = $v . "/.qmail-default";
			$x = file_get_contents($d);
			$x = str_replace("/home/lxadmin/mail/bin", "/home/vpopmail/bin", $x);
		}

		file_put_contents($d, $x);

		log_cleanup("- rcpthosts/morercpthosts for '{$k}'", $nolog);
		$rh .= "{$k}\n";
	}

	$ua .= ".";

	$con->select_db("kloxo");

	$result2 = $con->query("SELECT nname FROM mmail WHERE remotelocalflag = 'remote'");

	$n2 = array();

	while ($row2 = $result2->fetch_array(MYSQLI_ASSOC)) {
		$n2[$row2['nname']] = '1';
	}

	foreach ($n as $k => $v) {
		foreach ($n2 as $k2 => $v2) {
			if ($k === $k2) {
				unset($n[$k]);
			}
		}
	}

	foreach ($n as $k => $v) {
		log_cleanup("- virtualdomains for '{$k}'", $nolog);
		$vd .= "{$k}:{$k}\n";
	}

	exec("echo '{$ua}' > {$upath}/assign");
	// MR -- moving list to morercpthosts
//	exec("echo '{$rh}' > {$cpath}/rcpthosts");
	exec("echo '' > {$cpath}/rcpthosts");
	exec("echo '{$rh}' > {$cpath}/morercpthosts; {$bpath}/qmail-newmrh");
	exec("echo '{$vd}' > {$cpath}/virtualdomains; {$bpath}/qmail-newu");

	$con->close();
}
