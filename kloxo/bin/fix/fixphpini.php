<?php 

include_once "lib/html/include.php"; 
initProgram('admin');

$list = parse_opt($argv);

$server = (isset($list['server'])) ? $list['server'] : 'localhost';
$client = (isset($list['client'])) ? $list['client'] : null;
$domain = (isset($list['domain'])) ? $list['domain'] : null;
$nolog  = (isset($list['nolog'])) ? $list['nolog'] : null;

$login->loadAllObjects('client');
$list = $login->getList('client');

log_cleanup("Fixing php.ini/php-fpm.conf/php.fcgi/.htaccess", $nolog);

$clist = array();

$plist = $login->getList('pserver');

foreach($plist as $s) {

	foreach($list as $c) {
		if ($client) {
			$ca = explode(",", $client);

			if (!in_array($c->nname, $ca)) { continue; }
		}

		if ($server !== 'all') {
			$sa = explode(",", $server);

			if (!in_array($s->syncserver, $sa)) { continue; }
		}

		$dlist = $c->getList('domaina');

		foreach((array) $dlist as $l) {
			$web = $l->getObject('web');

			if ($domain) {
				$da = explode(",", $domain);
				if (!in_array($web->nname, $da)) { continue; }
			}

			$php = $web->getObject('phpini');
			$php->initPhpIni();
			$php->setUpdateSubaction('htaccess_update');

			log_cleanup("- '/home/{$c->nname}/{$web->docroot}/.htaccess' ('{$c->nname}') at '{$php->syncserver}'", $nolog);

			if (!in_array($c->nname, $clist)) {
				$php = $c->getObject('phpini');
				$php->initPhpIni();
				$php->setUpdateSubaction('ini_update');

				log_cleanup("- '/home/kloxo/client/{$c->nname}/php.ini' at '{$php->syncserver}'", $nolog);
				log_cleanup("- '/home/kloxo/client/{$c->nname}/php.fcgi' at '{$php->syncserver}'", $nolog);
				log_cleanup("- '/home/kloxo/client/{$c->nname}/prefork.inc' at '{$php->syncserver}'", $nolog);
				log_cleanup("- '/etc/php-fpm.d/{$c->nname}.conf' (also for 'multiple php') at '{$php->syncserver}'", $nolog);

				$php->was();

				$clist[] = $c->nname;
				array_unique($clist);
			}

			$php->was();

			$web->was();
		}
	}

	if ($client !== null) { continue; }
	if ($domain !== null) { continue; }

	if ($server !== 'all') {
		$sa = explode(",", $server);
		if (!in_array($s->syncserver, $sa)) { continue; }
	}

	$php = $s->getObject('phpini');

	$php->fixphpIniFlag();

	$php->setUpdateSubaction('ini_update');

	log_cleanup("- '/etc/php.ini' at '{$php->syncserver}'", $nolog);

	log_cleanup("- Fix 'extension_dir' path in php.ini at '{$php->syncserver}'", $nolog);

	log_cleanup("- '/etc/php-fpm.d/default.conf' at '{$php->syncserver}'", $nolog);
	log_cleanup("- '/home/kloxo/client/php.fcgi' at '{$php->syncserver}'", $nolog);

	$php->was();

	// MR - fix for php-fpm and fastcgi session issue
	if (!file_exists("/var/log/php-fpm")) {
		mkdir("/var/log/php-fpm",0755);
	}

	if (!file_exists("/var/lib/php/session")) {
		mkdir("/var/lib/php/session");
	}

	chmod("/var/lib/php/session", 0777);
	chown("/var/lib/php/session", "apache");
}



