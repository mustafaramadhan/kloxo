<?php

class Pop3__courier extends lxDriverClass 
{
	static function installMe()
	{
		$spath = '/var/qmail/supervise';

		$darray = array('authlib', 'pop3', 'pop3-ssl', 'imap4', 'imap4-ssl');

		foreach ($darray as $k => $v) {
			rename("{$spath}/{$v}/down", "{$spath}/{$v}/run");
			rename("{$spath}/{$v}/log/down", "{$spath}/{$v}/log/run");
		}
	}

	static function unInstallMe()
	{
		$spath = '/var/qmail/supervise';

		$darray = array('authlib', 'pop3', 'pop3-ssl', 'imap4', 'imap4-ssl');

		foreach ($darray as $k => $v) {
			rename("{$spath}/{$v}/run", "{$spath}/{$v}/down");
			rename("{$spath}/{$v}/log/run", "{$spath}/{$v}/log/down");
		}
	}
}
