<?php
	if (file_exists("/opt/djbdns/bin/axfr-get")) {
		$axfr_get = "/opt/djbdns/bin/axfr-get";
	} elseif (file_exists("/bin/axfr-get")) {
		$axfr_get = "/bin/axfr-get"; 
	} else {
		$axfr_get = null;
	}

	foreach($domains as $k => $v) {
		$t = explode(':', $v);

		$d1names[] = $t[0];
		$d1ips[] = $t[1];
	}

	$tpath = "/opt/configs/djbdns/conf/slave";

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

	foreach ($d1names as $k => $v) {
		$c = $d1ips[$k];

		touch("{$tpath}/{$v}");

		if ($axfr_get) {
			exec("tcpclient -v {$c} 53 {$axfr_get} {$v} {$tpath}/{$v} {$tpath}/{$v}.tmp 2>&1");
		}
	}

	$datadir = "/home/djbdns/tinydns/root";
	$datafile = "{$datadir}/slave";

	if (!file_exists($datadir)) { return; }

	exec("'rm' -f {$datafile}");

	foreach ($d1names as $k => $v) {
		$c = $d1ips[$k];

		$e  = "### begin - dns of '{$v}' - do not remove/modify this line\n\n";
		$e .= file_get_contents("{$tpath}/{$v}");
		$e .= "\n### en - dns of '{$v}' - do not remove/modify this line\n\n";

		exec("echo '{$e}' >> {$datafile}");
	}
