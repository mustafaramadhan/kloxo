<?php

class Spam__bogofilter extends lxDriverClass 
{
	static function installMe()
	{
		global $login;

		$ret = lxshell_return("yum", "-y", "install", "bogofilter");

		if ($ret) {
			throw new lxException($login->getThrow('install_failed'), '', 'bogofilter');
		}
	}

	static function unInstallMe()
	{
		lxshell_return("rpm", "-e", "bogofilter");
	}

	function dbactionAdd()
	{
		$this->syncSpamUserPref();
	}

	function dbactionDelete()
	{
		$wname = fix_nname_to_be_variable($this->main->nname);
		lxfile_rm("/var/bogofilter/$wname.wordlist.db");
	}

	function syncSpamUserPref()
	{
		global $gbl, $sgbl, $ghtml;

		// The parent can be either a domain or a user. CHeck for the @ sign.
		if (csa($this->main->nname, "@")) {
			list($user, $domain) = explode("@", $this->main->nname);
		} else {
			$domain = $this->main->nname;
			$user = null;
		}

		$sysuser =  mmail__qmail::getUserGroup($domain);

		// --- issue #578/#721 - missing in version 6.1.6
		$mailpath = mmail__qmail::getDir($domain);
		$mailpath = str_replace($sgbl->__path_mail_root, $sgbl->__path_mail_data, $mailpath);

		if ($user) {
			$prefpath = "{$mailpath}/{$user}/.bogopref.cf";
		} else {
			return;
		}

		$prefdir = dirname($prefpath);

		if (!lxfile_exists(dirname($prefpath))) {
			lxuser_mkdir($sysuser, dirname($prefpath));
		}

		$wname = fix_nname_to_be_variable($this->main->nname);

		$fdata = null;
		$cutoff = $this->main->spam_hit/10 + 0.2;
		$fdata .= "spam_cutoff  $cutoff\n";
		$fdata .= "spam_subject_tag={$this->main->subject_tag}\n";
		$fdata .= "wordlist R,user,$wname.wordlist.db,1\n";
		$fdata .= "wordlist R,system,wordlist.db,2\n";
		$fdata .= "wordlist R,system,kloxo.wordlist.db,3\n";
		lxuser_put_contents($sysuser, $prefpath, $fdata);

		if (!lxfile_real("/var/bogofilter/$wname.wordlist.db")) {
			new_process_cmd($sysuser, null, "bogofilter -d /var/bogofilter/ --wordlist=R,user,$wname.wordlist.db,1  -n < /etc/my.cnf");
		}

		lxfile_touch("/var/bogofilter/wordlist.db");
		// Using generic because spamassasin is used on windows too. Or at least can be used.
	//	lxfile_generic_chown("/var/bogofilter", mmail__qmail::getUserGroup($domain));
	}

	function dbactionUpdate($subaction)
	{
		switch($subaction)
		{
			case "full_update":
				$this->syncSpamUserPref();
				break;

			case "update":
			case "add_wlist_a" :
			case "add_blist_a" :
			case "delete_blist_a" :
			case "delete_wlist_a" :
				$this->syncSpamUserPref();
				break;
		}
	}
}
