<?php

class Mailoutgoing__qmail extends lxDriverClass 
{
	static function installMe()
	{
		$spath = '/var/qmail/supervise';

		$darray = array('smtp', 'smtp-ssl', 'submission');

		foreach ($darray as $k => $v) {
			rename("{$spath}/{$v}/down", "{$spath}/{$v}/run");
			rename("{$spath}/{$v}/log/down", "{$spath}/{$v}/log/run");
		}
	}

	static function unInstallMe()
	{
		$spath = '/var/qmail/supervise';

		$darray = array('smtp', 'smtp-ssl', 'submission');

		foreach ($darray as $k => $v) {
			rename("{$spath}/{$v}/run", "{$spath}/{$v}/down");
			rename("{$spath}/{$v}/log/run", "{$spath}/{$v}/log/down");
		}
	}
}
