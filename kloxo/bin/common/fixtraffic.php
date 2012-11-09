<?php 
include_once "htmllib/lib/include.php"; 

$day = 1;

$list = parse_opt($argv);

// MR -- because only 1 class (domain)
$class = isset($list['class']) ? $list['class'] : 'domain';
/*
if (isset($list['class'])) {
	$class = $list['class'];
} else {
	print("Need --class=\n");
	exit;
}
*/

if (isset($list['day'])) {
	$day = $list['day'];
} else {
	print("Day not set... Defaulting to $day\n");
}


$oldtime = time() - $day * 24 * 3600;

$sq = new Sqlite(null, "{$class}traffic");

$res = $sq->getTable();

foreach($res as $r) {

	if (!csa($r['nname'], ":")) {
		continue;
	}

	$t = explode(":", $r['nname']);

	$ot = $t[1];
	if ($ot > $oldtime) {
		print("deleting $oldtime {$r['nname']}\n");
		$sq->rawQuery("delete from {$class}traffic where nname = '{$r['nname']}'");
	} else {
		//print("not deleting $oldtime {$r['nname']}\n");
	}
}


$c = "{$class}traffic";
$laccess = new $c(null, null, '__last_access_domain_');
$laccess->get();

if ($laccess->timestamp > $oldtime) {
	$laccess->timestamp = $oldtime;
	$laccess->setUpdateSubaction();
	$laccess->write();
}

system("lphp.exe ../bin/gettraffic.php");
system("lphp.exe ../bin/collectquota.php");




