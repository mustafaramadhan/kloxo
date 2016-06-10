<?php

ini_set('error_reporting', 'E_ALL');
ini_set('log_errors', 'On');
ini_set('error_log', '/tmp/phperror_log');

include_once "htmllib/lib/include.php";

sudo_action_main();

function sudo_action_main()
{
	global $argv;
//	sleep(100);
//	log_log("sudo_action", "Got: {$argv[1]}");
	$rmt =  unserialize(base64_decode($argv[1]));
//	log_log("sudo_action", "Got2: ". var_export($rmt, true));
//	log_log("sudo_action", "User: ". $rmt->__set_state['func']->__set_state['arglist'][0]);
	if (!$rmt) { exit; }
//	ob_start();
	
	if (isset($rmt->sleep)) {
		sleep($rmt->sleep);
	}
	if ($rmt->action === "set") {
//		log_log("sudo_action", "set");
		$object = $rmt->robject;
		$ret= $object->doSyncToSystem();
		$var = base64_encode(serialize($ret));
		ob_end_clean();
		print($var);
		exit;
		
	} else {
//		log_log("sudo_action", "get; user: ". $rmt->arglist[0]);
		// workaround for the following php bug:
		//   http://bugs.php.net/bug.php?id=47948
		//   http://bugs.php.net/bug.php?id=51329
		if (is_array($rmt->func) && count($rmt->func) > 0) {
			$class = $rmt->func[0];
			class_exists($class);
		}
		// ---
		$ret = call_user_func_array($rmt->func, $rmt->arglist);
	}
//	log_log("sudo_action", "result: ". serialize($ret));
	$var = base64_encode(serialize($ret));
	ob_end_clean();
	print($var);
	exit;
}