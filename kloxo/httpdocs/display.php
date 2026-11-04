<?php
if (!isset($_GET['frm_action']) && !isset($_POST['frm_action'])) {
	$_GET['frm_action'] = 'show';
	$_REQUEST['frm_action'] = 'show';
}

$v = 0;

include_once 'theme/coredisplaylib.php';

sleep($v);

//setcookie("XDEBUG_SESSION", "sess", time () +  36000);
//setcookie("XDEBUG_SESSION", "sess");
print_time("start");

display_init();

// print_time("start", "Start");