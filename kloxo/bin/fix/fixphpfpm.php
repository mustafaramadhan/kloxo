<?php 

include_once "htmllib/lib/include.php";
initProgram('admin');

$list = parse_opt($argv);

$server = (isset($list['server'])) ? $list['server'] : 'localhost';
$nolog  = (isset($list['nolog'])) ? $list['nolog'] : null;

$target = (isset($list['target'])) ? $list['target'] : 'all';

$login->loadAllObjects('client');
$list = $login->getList('client');

log_cleanup("Fixing Php-fpm config", $nolog);

$slist = array();

foreach($list as $c) {
	if ($server !== 'all') {
		$sa = explode(",", $server);
		if (!in_array($c->syncserver, $sa)) { continue; }
	}

	$dlist = $c->getList('domaina');

	foreach((array) $dlist as $l) {
		$web = $l->getObject('web');

		if (!in_array($web->syncserver, $slist)) {
			if (($target === 'all') || ($target === 'defaults')) {
				$web->setUpdateSubaction('fix_phpfpm');
				log_cleanup("- php-fpm at '{$web->syncserver}'", $nolog);
			}

			$slist[] = $web->syncserver;
			array_unique($slist);
		}

		$web->was();
	}
}
