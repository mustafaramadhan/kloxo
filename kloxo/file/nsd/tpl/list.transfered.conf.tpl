<?php
	// MR -- don't needed because nsd running under root
//	exec("chown -R nsd:nsd /var/lib/nsd");
//	exec("chmod 0777 /opt/configs/nsd/conf/master; chmod 0777 /opt/configs/nsd/conf/slave");

	$file = "/opt/configs/nsd/conf/defaults/nsd.acl.conf";

	$text = '';

	// MR -- this is IPs from 'A record' of dns
	if (array_keys($ips)) {
		foreach ($ips as $k => $v) {
			$text .= "    notify: {$v} NOKEY\n";
			$text .= "    provide-xfr: {$v} NOKEY\n";
			$text .= "    outgoing-interface: {$v}\n";
			$text .= "\n";
		}
	} else {
		exec("echo '' > {$file}");
	}

	file_put_contents($file, $text);

	$file = "/etc/nsd/nsd.conf";

	$text = '';

	// MR -- this is IPs from 'ip addr' (current server IPs)
	foreach ($serverips as $k => $v) {
		// MR -- IPv6 still not work?
	//	if (stripos($v, ':')) { continue; }

		$text .= "    ip-address: $v@53\\n";
	}

	$text .= "    ip-address: 127.0.0.1@53\\n";

	$begin = "    ## begin ip-address";
	$end   = "    ## end ip-address";

	exec("sed '/{$begin}/,/{$end}/d' {$file} > {$file}2");
	// MR -- need without \\n in after {$end} 
	exec("sed -i 's/^server:/server:\\n{$begin}\\n{$text}{$end}/g' {$file}2");
	exec("mv -f {$file}2 {$file}");
