<?php

class Smtp extends LxDriverClass
{
	static $__desc = array("", "", "smtp");

	static function switchProgramPre($old, $new)
	{
		exec_class_method("smtp__{$old}", "uninstallMe");
		exec_class_method("smtp__{$new}", "installMe");
	}

	static function switchProgramPost($old, $new)
	{
		createRestartFile("restart-mail");
	}

	static function removeOtherDriver()
	{
		global $gbl, $sgbl, $login, $ghtml;

		removeOtherDrivers($class = 'smtp', $nolog = true);
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
