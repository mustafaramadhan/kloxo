<?php 

include_once "htmllib/lib/include.php"; 
initProgram('admin');

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

	foreach($dlist as $l) {
		$mmail = $l->getObject('mmail');
		$mclist = $mmail->getList('mailaccount');
		foreach($mclist as  $mc) {
			$spam = $mc->getObject('spam');
			$mc->setUpdateSubaction('full_update');
			$spam->setUpdateSubaction('full_update');
			$mc->was();
			$spam->was();
		}
		log_cleanup("- '{$mmail->nname}' ('{$c->nname}') at '{$mmail->syncserver}'", $nolog);
	}
}


