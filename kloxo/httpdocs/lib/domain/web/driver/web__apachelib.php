<?php

include_once("web__lib.php");

class web__apache extends web__
{
	function __construct()
	{
		parent::__construct();
	}

	static function uninstallMe()
	{
		parent::uninstallMeTrue('apache');
	}

	static function installMe()
	{
		parent::installMeTrue('apache');
	}
}