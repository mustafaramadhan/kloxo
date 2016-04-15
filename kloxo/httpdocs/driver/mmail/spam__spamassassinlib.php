<?php 

class Spam__Spamassassin extends lxDriverClass
{
	static function installMe()
	{
		global $login;

		$ret = lxshell_return("yum", "-y", "install", "spamassassin-toaster");

		if ($ret) {
			throw new lxException($login->getThrow('install_failed'), '', 'spamassassin-toaster');
		}

		$ret2 = lxshell_return("yum", "-y", "install", "simscan-toaster");

		if ($ret2) {
			throw new lxException($login->getThrow('install_failed'), '', 'simscan-toaster');
		}

		$ret3 = lxshell_return("yum", "-y", "install", "ripmime");

		if ($ret2) {
			throw new lxException($login->getThrow('install_failed'), '', 'ripmime');
		}

	//	lxshell_return("chkconfig", "spamassassin", "on");
	//	lxfile_cp("../file/sysconfig_spamassassin", "/etc/sysconfig/spamassassin");
	//	createRestartFile("spamassassin");
	}

	static function uninstallMe()
	{
	//	lxshell_return("service", "spamassassin", "stop");
		lxshell_return("rpm", "-e", "--nodeps", "spamassassin-toaster");
	//	lxshell_return("rpm", "-e", "--nodeps", "simscan-toaster");
	//	lxshell_return("rpm", "-e", "--nodeps", "ripmime");
	}

	function dbactionAdd()
	{
		//
		$this->syncSpamUserPref();
	}

	function dbactionDelete()
	{
		//
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

		// --- issue #578/#721 - missing in version 6.1.6
		$mailpath = mmail__qmail::getDir($domain);

		if ($user) {
		//	$prefpath = "$mailpath/domains/{$domain}/{$user}/user_prefs";
                /* JP add proper spamassassin path
                 * For this to work ensure that /var/qmail/supervise/spamd/run
                 * contains the correct --virtual-config-dir=/home/lxadmin/mail/spamassassin/%d/%l/.spamassassin command
                 *   
                 * eg exec /usr/bin/spamd --virtual-config-dir=/home/lxadmin/mail/spamassassin/%d/%l/.spamassassin -x -u vpopmail -s stderr -i 0.0.0.0 2>&1   
		 */
                 // 	$prefpath = "{$mailpath}/{$user}/.spamassassin/user_prefs";
                 
                        $prefpath = "/home/lxadmin/mail/spamassassin/{$domain}/{$user}/.spamassassin/user_prefs";
		} else {
			return;
		}

		if (!lxfile_exists(dirname($prefpath))) {
			lxfile_mkdir(dirname($prefpath));
			lxfile_generic_chown(dirname($prefpath), "vpopmail:vchkpw");
		}

		$fdata = null;
		$fdata .= "required_score  " . $this->main->spam_hit . "\n";
		$fdata .= "ok_locales   all\n";
		$fdata .= "rewrite_header Subject  {$this->main->subject_tag}\n";
		foreach ((array) $this->main->wlist_a as $wlist) $fdata .= "whitelist_from   " . $wlist->nname . "\n";
		$fdata .= "#***********************************\n";
		foreach ((array) $this->main->blist_a as $blist) $fdata .= "blocklist_from   " . $blist->nname . "\n";

		lxfile_rm($prefpath);
		lfile_write_content($prefpath, $fdata, "vpopmail:vchkpw");
	}

	function dbactionUpdate($subaction)
	{

		switch ($subaction) {
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
