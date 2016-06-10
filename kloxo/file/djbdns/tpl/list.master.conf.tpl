<?php
	$datadir = "/home/djbdns/tinydns/root";

	// MR -- importance if not active
	if (!file_exists($datadir)) { return; }

	if (file_exists("/opt/djbdns/bin/tinydns-data")) {
		exec("echo 'data.cdb: data\n\t/opt/djbdns/bin/tinydns-data' > " .
			"{$datadir}/Makefile");
	} elseif (file_exists("/bin/tinydns-data")) {
		exec("echo 'data.cdb: data\n\t//bin/tinydns-data' > " .
			"{$datadir}/Makefile");
	} else {
		return;
	}

	$d1names = $domains;

	$tpath = "/opt/configs/djbdns/conf/master";
	$d2files = glob("{$tpath}/*");

	if (empty($d2files)) { return; }

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

	$datafile = "{$datadir}/master";

	exec("'rm' -f {$datafile}");

	foreach ($d1names as $k => $v) {
		$c = "{$tpath}/{$v}";
		exec("cat {$c} >> {$datafile}");
	}

