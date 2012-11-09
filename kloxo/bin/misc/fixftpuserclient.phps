<?php 
include_once "htmllib/lib/include.php"; 


initprogram('admin');

$list = parse_opt($argv);

$server = (isset($list['server'])) ? $list['server'] : 'localhost';
$client = (isset($list['client'])) ? $list['client'] : null;
$nolog  = (isset($list['nolog']))  ? $list['nolog'] : null;

//lxfile_mv("/etc/pure-ftpd/pureftpd.passwd", "/etc/pure-ftpd/pureftpd.passwd.oldsaved");
//lunlink("/etc/pure-ftpd/pureftpd.pdb");
//lunlink("/etc/pure-ftpd/pureftpd.passwd.tmp");

$login->loadAllObjects('client');
$list = $login->getList('client');

log_cleanup("Fixing FTP User", $nolog);

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

	$flist = $c->getList('ftpuser');

	if (!$flist) { continue; }

	foreach($flist as $fl) {
		$fl->dbaction = 'syncadd';
		$fl->was();

		log_cleanup("- '{$fl->nname}' ('{$c->nname}') at '{$fl->syncserver}'", $nolog);
	}
}

