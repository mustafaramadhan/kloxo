<?php

include_once("web__lib.php");

class web__nginxproxy extends web__
{
	function __construct()
	{
		parent::__construct();
	}

	static function uninstallMe()
	{
		parent::uninstallMeTrue('nginxproxy');
	}

	static function installMe()
	{
		parent::installMeTrue('nginxproxy');
	}
}
