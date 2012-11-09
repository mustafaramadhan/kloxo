<?php 

include_once "htmllib/lib/include.php"; 
initProgram('admin');

$list = parse_opt($argv);

$server = (isset($list['server'])) ? $list['server'] : 'localhost';
$client = (isset($list['client'])) ? $list['client'] : null;
$nolog  = (isset($list['nolog'])) ? $list['nolog'] : null;

$login->loadAllObjects('client');
$list = $login->getList('client');

log_cleanup("Fixing Mail accounts", $nolog);

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

	foreach((array)$dlist as $l) {
		$mmail = $l->getObject('mmail');

		$mmail->setUpdateSubaction('full_update');
		$mmail->was();

		$mlist = $mmail->getList('mailaccount');
		foreach($mlist as $ml) {
			$spam = $ml->getObject('spam');
			log_cleanup("- '{$ml->nname}' ('{$c->nname}') at '{$mmail->syncserver}'", $nolog);
			$spam->setUpdateSubaction('full_update');
			$spam->was();
			$ml->setUpdateSubaction('full_update');
			$ml->was();
		}
	}
}
