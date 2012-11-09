<?php 

include_once "htmllib/lib/include.php"; 
initProgram('admin');

$login->loadAllObjects('client');
$list = $login->getList('client');

foreach($list as $c) {
	$dlist = $c->getList('domain');
	foreach($dlist as $l) {
		$web = $l->getObject('web');
		$web->setUpdateSubaction('full_update');
		$web->was();
		$dirp = $web->getList('dirprotect');

		foreach($dplist as $dp) {
			$dp->setUpdateSubaction('full_update');
			$dp->was();
		}
	}
}


