<?php 

include_once "htmllib/lib/include.php"; 
initProgram('admin');

$list = parse_opt($argv);

$server = (isset($list['server'])) ? $list['server'] : 'localhost';
$client = (isset($list['client'])) ? $list['client'] : null;
$nolog  = (isset($list['nolog'])) ? $list['nolog'] : null;

log_cleanup("Fixing DNS server config", $nolog);

if (isset($list['new_dnstemplate'])) {
	$dnst = new Dnstemplate(null, null, $list['new_dnstemplate']);
	$dnst->get();
	if ($dnst->dbaction === 'add') {
		log_cleanup("- DNS template doesn't exist", $nolog);
		exit;
	}
}

$login->loadAllObjects('client');
$list = $login->getList('client');

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

	if (!$dlist) { continue; }
	
	foreach($dlist as $l) {
		$dns = $l->getObject('dns');

		if ($dnst) {
			$dns->dns_record_a = null;
			$dns->copyObject($dnst);
		}

		$dns->setUpdateSubaction('full_update');
		$dns->was();

		log_cleanup("- '{$dns->nname}'('{$c->nname}') at '{$dns->syncserver}'", $nolog);
	}
}

