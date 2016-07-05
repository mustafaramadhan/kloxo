<?php
	exec("echo '' > /opt/configs/nsd/conf/defaults/nsd.slave.conf");

	foreach($domains as $k => $v) {
		$t = explode(':', $v);

		$d1names[] = $t[0];
		$d1ips[] = $t[1];
	}

	$tpath = "/opt/configs/nsd/conf/slave";

	$d2files = glob("{$tpath}/*");

	foreach ($d2files as $k => $v) {
		$d2names[] = str_replace("{$tpath}/", '', $v);
	}

	$d2olds = array_diff($d2names, $d1names);

	// MR -- delete unwanted files
	if (!empty($d2olds)) {
		foreach ($d2olds as $k => $v) {
			unlink("{$tpath}/{$v}");
		}
	}

	$str = '';

	foreach ($d1names as $k => $v) {
		$c = $d1ips[$k];

		$zone  = "zone:\n";
		$zone .= "    name: {$v}\n";
		$zone .= "    zonefile: slave/{$v}\n";
		$zone .= "    allow-notify: {$c} NOKEY\n";
		$zone .= "    request-xfr: {$c}@53 NOKEY\n";
		$zone .= "    notify-retry: 5\n";

		$str .= $zone . "\n";
	}

	$file = "/opt/configs/nsd/conf/defaults/nsd.slave.conf";

	file_put_contents($file, $str);

	if (!file_exists("/etc/rc.d/init.d/nsd")) { return; }

	createRestartFile("restart-dns");