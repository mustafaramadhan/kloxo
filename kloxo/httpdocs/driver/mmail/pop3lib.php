<?php

class Pop3 extends LxDriverClass
{
	static $__desc = array("", "", "pop3");

	static function switchProgramPre($old, $new)
	{
		exec_class_method("pop3__{$old}", "uninstallMe");
		exec_class_method("pop3__{$new}", "installMe");
	}

	static function switchProgramPost($old, $new)
	{
		createRestartFile("restart-mail");
	}

	static function removeOtherDriver()
	{
		global $gbl, $sgbl, $login, $ghtml;

		removeOtherDrivers($class = 'pop3', $nolog = true);
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
