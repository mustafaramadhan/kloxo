<?php
	$dir = "/home/djbdns/axfrdns";

	// MR -- importance if not active
	if (!file_exists($dir)) { return; }

	if (array_keys($ips)) {
		$file = "{$dir}/tcp";

		$i = implode(":allow,AXFR=\"\"\n", $ips);

		$text = $i . ":allow,AXFR=\"\"\n";

		file_put_contents($file, $text);

		$nameduser = "axfrdns";

		chown("{$dir}/data", $nameduser);

		exec_with_all_closed("cd {$dir}; make");
	}

	$datadir = "/home/djbdns/tinydns/root";

	if (!file_exists("{$datadir}/slave")) {
		touch("{$datadir}/slave");
	}

	if (!file_exists("{$datadir}/reverse")) {
		touch("{$datadir}/reverse");
	}

	if (!file_exists("/etc/rc.d/init.d/djbdns")) { return; }

	exec("cd {$datadir}; cat master slave reverse > data; make");

	if (file_exists("/etc/rc.d/init.d/djbdns")) {
		createRestartFile("restart-dns");

		$path = "/opt/configs/djbdns/conf/master";
		$dirs = glob("{$path}/*");

		foreach ($dirs as $d) {
			$d = str_replace("{$path}/", "", $d);

			exec_with_all_closed("sh /script/dnsnotify {$d}");
		}
	}
