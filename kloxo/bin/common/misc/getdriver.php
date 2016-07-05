<?php 

include_once "lib/html/include.php"; 

initProgram('admin');

if (!isset($argv[1])) {
	print("Format: sh /script/getdriver <class>\n");

	exit;
} else {
	$driverapp = $gbl->getSyncClass(null, 'localhost', $argv[1]);
	print("Driver for '{$argv[1]}' is '{$driverapp}'\n");

	exit;
}