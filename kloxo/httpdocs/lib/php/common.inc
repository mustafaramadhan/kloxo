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

	if (csb($class, "all_")) { $class = strfrom($class, "all_"); }

	if (isset($gl_class_array[$class]) && file_exists(getreal($gl_class_array[$class]))) {
		include_once $gl_class_array[$class];
		return;
	}

	dprint("Class Not found $class <br> \n");
});
