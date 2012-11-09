<?php 

include_once "htmllib/lib/include.php"; 

initProgram('admin');

$server = $login->getfromList('pserver', 'localhost');
$server->getandwriteipaddress();

// MR -- fix issue when start/restart kloxo will be delete /home/<webserver>/conf/domains contents
lxshell_return("sh", "/script/fixweb", "--nolog");

