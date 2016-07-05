<?php

class Mailincoming extends LxDriverClass
{
	static $__desc = array("", "", "mailincoming");

	static function switchProgramPre($old, $new)
	{
		exec_class_method("mailincoming__{$old}", "uninstallMe");
		exec_class_method("mailincoming__{$new}", "installMe");
	}

	static function switchProgramPost($old, $new)
	{
		createRestartFile("restart-mail");
	}

	static function removeOtherDriver()
	{
		global $gbl, $sgbl, $login, $ghtml;

		removeOtherDrivers($class = 'mailincoming', $nolog = true);
	}

	function inheritSynserverFromParent() { return false; }

	function updateform($subaction, $param)
	{
	}

	function postUpdate()
	{
		// We need to write because reads everything from the database.
		$this->write();
	}

}
