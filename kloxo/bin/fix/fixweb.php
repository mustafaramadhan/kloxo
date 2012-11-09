<?php 

include_once "htmllib/lib/include.php";
initProgram('admin');

$list = parse_opt($argv);

$server = (isset($list['server'])) ? $list['server'] : 'localhost';
$client = (isset($list['client'])) ? $list['client'] : null;
$nolog  = (isset($list['nolog'])) ? $list['nolog'] : null;

$target = (isset($list['target'])) ? $list['target'] : 'all';

$login->loadAllObjects('client');
$list = $login->getList('client');

log_cleanup("Fixing Web server config", $nolog);

$webdrvs = array('apache', 'lighttpd', 'nginx');

foreach ($webdrvs as &$w) {
	// MR -- delete all contents first for domains;
	// for handling garbage files!
	exec("rm -rf /home/{$w}/conf/domains/*.conf");
}

// MR -- also delete all contents first for php-fpm;
exec("rm -rf /etc/php-fpm.d/*.conf");

$slist = array();

foreach($list as $c) {
	if ($client) {
		$ca = explode(",", $client);
		if (!in_array($c->nname, $ca)) { continue; }
		$server = 'all';
	}

	if ($server !== 'all') {
		$sa = explode(",", $server);
		if (!in_array($c->syncserver, $sa)) { continue; }
	}

	$dlist = $c->getList('domaina');

	foreach((array) $dlist as $l) {
		$web = $l->getObject('web');

		if (!in_array($web->syncserver, $slist)) {
			if (($target === 'all') || ($target === 'defaults')) {
				log_cleanup("- 'defaults' and 'php-fpm' at '{$web->syncserver}'", $nolog);
				$web->setUpdateSubaction('static_config_update');
			}

			$slist[] = $web->syncserver;
			array_unique($slist);
		}

		if (($target === 'all') || ($target === 'domains')) {
			log_cleanup("- '{$web->nname}' ('{$c->nname}') at '{$web->syncserver}'", $nolog);
			$web->setUpdateSubaction('full_update');
		}

		$web->was();
	}
}

// MR - fix for php-fpm and fastcgi session issue
if (!file_exists("/var/log/php-fpm")) {
	mkdir("/var/log/php-fpm",0755);
}
chmod("/var/lib/php/session", 0777);
chown("/var/lib/php/session", "apache");

// MR - also fix for lighttpd
if (!file_exists("/var/log/lighttpd")) {
	mkdir("/var/log/lighttpd",0777);
}
chmod("/var/log/lighttpd", 0777);

