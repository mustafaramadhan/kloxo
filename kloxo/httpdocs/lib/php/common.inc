<?php

define('URL_SHOW', 'a=show');
define('URL_TOGGLE_STATUS', 'a=update&sa=toggle_status');
define('URL_TOGGLE_STATE', 'a=update&sa=toggle_state');
define('URL_UPDATE', 'a=updateform&sa=update');

spl_autoload_register(function ($class) {
	global $gbl, $sgbl, $login, $ghtml; 

	if (strpos($class, '_none') !== false) { return; }

	global $gl_class_array;

	$class = strtolower($class);

	if (str_starts_with($class, "all_")) { $class = substr($class, 4); }

	if (isset($gl_class_array[$class])) {
		$file = function_exists('getreal') ? getreal($gl_class_array[$class]) : $gl_class_array[$class];
		if (file_exists($file)) {
			include_once $gl_class_array[$class];
			return;
		}
	}

	if (function_exists('dprint')) {
		dprint("Class Not found $class <br> \n");
	}
});
