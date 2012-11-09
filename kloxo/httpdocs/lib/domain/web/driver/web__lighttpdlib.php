<?php

include_once("web__lib.php");

class web__lighttpd extends web__
{
	function __construct()
	{
		parent::__construct();
	}

	static function uninstallMe()
	{
		parent::uninstallMeTrue('lighttpd');
	}

	static function installMe()
	{
		parent::installMeTrue('lighttpd');
	}
}