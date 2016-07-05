<?php

class Imap4 extends LxDriverClass
{
	static $__desc = array("", "", "imap4");

	static function switchProgramPre($old, $new)
	{
		exec_class_method("imap4__{$old}", "uninstallMe");
		exec_class_method("imap4__{$new}", "installMe");
	}

	static function switchProgramPost($old, $new)
	{
		createRestartFile("restart-mail");
	}

	static function removeOtherDriver()
	{
		global $gbl, $sgbl, $login, $ghtml;

		removeOtherDrivers($class = 'imap4', $nolog = true);
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
