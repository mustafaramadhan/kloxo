<?php

chdir(dirname(__FILE__) . '/../httpdocs');
include_once "lib/html/include.php";

initProgram('admin');

$server = $login->getfromList('pserver', 'localhost');
if ($server) {
	$server->getandwriteipaddress();
}

// MR -- fix issue when start/restart kloxo will be delete /opt/configs/<webserver>/conf/domains contents
if (file_exists("/script/fixweb")) {
	lxshell_return("sh", "/script/fixweb", "--nolog");
}