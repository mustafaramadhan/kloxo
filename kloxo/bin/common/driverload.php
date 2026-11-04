<?php 

chdir(dirname(__FILE__) . '/../../httpdocs');
include_once "lib/html/include.php";

driverload_main();

function driverload_main()
{

	global $argv, $gbl, $sgbl, $login, $ghtml; 
	initProgram('admin');
	$p = parse_opt($argv);

	if (isset($p['clear-existing']))  {
		$sq = new Sqlite(null, "driver");
		$sq->rawQuery("delete from driver");
	}

	$list = $login->getList('pserver');

	foreach($list as $l) {
		$l->getandWriteModuleDriver();
	}

}
