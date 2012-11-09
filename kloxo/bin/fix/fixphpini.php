<?php 

include_once "htmllib/lib/include.php"; 
initProgram('admin');

$list = parse_opt($argv);

$server = (isset($list['server'])) ? $list['server'] : 'localhost';
$client = (isset($list['client'])) ? $list['client'] : null;
$nolog  = (isset($list['nolog'])) ? $list['nolog'] : null;

$login->loadAllObjects('client');
$list = $login->getList('client');

$plist = $login->getList('pserver');

log_cleanup("Fixing php.ini", $nolog);

foreach($plist as $s) {
	if ($client) {
		$server = 'all';
	}

	if ($server !== 'all') {
	//	if ($s->syncserver !== $server) { continue; }
		$sa = explode(",", $server);
		if (!in_array($s->syncserver, $sa)) { continue; }
	}

	$pi = $s->getObject('phpini');
	$pi->setUpdateSubaction('full_update');
	$pi->was();

	log_cleanup("- in '/etc' at '{$s->nname}'", $nolog);
}

foreach($list as $c) {
	if ($client) {
	//	if ($client !== $c->nname) { continue; }
		$ca = explode(",", $client);
		if (!in_array($c->nname, $ca)) { continue; }
		$server = 'all';
	}

	if ($server !== 'all') {
	//	if ($c->syncserver !== $server) { continue; }
		$sa = explode(",", $server);
		if (!in_array($c->syncserver, $sa)) { continue; }
	}

	$dlist = $c->getList('domaina');

	foreach((array) $dlist as $l) {
		$web = $l->getObject('web');

		$php = $web->getObject('phpini');
		$php->initPhpIni();
		$php->setUpdateSubaction('full_update');
		$php->was();

		log_cleanup("- in '/home/httpd/{$web->nname}' ('{$c->nname}') at '{$web->syncserver}'", $nolog);
	}
}

