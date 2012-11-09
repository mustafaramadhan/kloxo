<?php

include_once("web__lib.php");

class web__lighttpdproxy extends web__
{
	function __construct()
	{
		parent::__construct();
	}

	static function uninstallMe()
	{
		parent::uninstallMeTrue('lighttpdproxy');
	}

	static function installMe()
	{
		parent::installMeTrue('lighttpdproxy');
	}
}