<?php

class Mailaccount__Qmail extends lxDriverClass
{
	// Core

	static function Mailaccdisk_usage($accname)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$name = explode('@', $accname);

		$mailpath = mmail__qmail::getDir($name[1]);
		$mailpath = str_replace($sgbl->__path_mail_root, $sgbl->__path_mail_data, $mailpath);

		$path = "$mailpath/{$name[0]}";
		dprint("Path of the File is :$path\n");
		return lxfile_dirsize($path);
	}

	function syncUserAdd()
	{
		global $gbl, $sgbl, $ghtml, $login;

		global $global_shell_error;

		$password = $this->main->password;

		if (!$this->main->password) {
		//	$password = crypt('something');
			$password = crypt(randomString(8));
		}

		$quser = explode("@", $this->main->nname);
		$domain = $quser[1];

		$res = lxuser_return(mmail__qmail::getUserGroup($domain), "__path_mail_root/bin/vadduser", $this->main->nname, '-e', $password);

		// MR -- need fix chown/chmod to make it work
		if ($res) {
			// --- Issue #702 - Error 'mailaccount_add_failed' when add email account
			// REVERT -- back to previous
			if (!csb($this->main->nname, "postmaster")) {
				throw new lxException($login->getThrow("mailaccount_add_failed"), '', $this->main->nname);
			}
		}

		$this->syncQmail();
		$this->syncQuota();
	}

	function syncQmail()
	{
		global $gbl, $sgbl, $ghtml;

		$quser = explode("@", $this->main->nname);
		$domain = $quser[1];

		$mailpath = mmail__qmail::getDir($quser[1]);
		$mailpath = str_replace($sgbl->__path_mail_root, $sgbl->__path_mail_data, $mailpath);

		$sysuser = mmail__qmail::getUserGroup($domain);

		$qmailfile = "$mailpath/{$quser[0]}/.qmail";
		$maildropfile = "$mailpath/{$quser[0]}/.maildroprc";
		$user = $quser[0];

		$maildirpath = "{$mailpath}/{$user}/Maildir";

		$fdata = null;

		if ($this->main->isOn('autorespond_status')) {
			$mfile = "$mailpath/{$quser[0]}/autorespond/message";

			if (!lxfile_exists($mfile)) {
				lxuser_mkdir($sysuser, dirname($mfile));
				lxuser_put_contents($sysuser, $mfile, "Autoresponder");
			}

			if ($this->main->__var_autores_driver === 'qmail') {
				$fdata .= "| autorespond 100  100 autorespond/message autorespond 0\n";
			} else {
				$fdata .= "| $sgbl->__path_php_path $sgbl->__path_program_root/script/autorespond.php {$this->main->nname}\n";
			}
		}

		self::setMaildirFolder($sysuser, $maildirpath);

		$spamdir = "to {$maildirpath}";

		if ($this->main->filter_spam_status === 'delete') {
			$spamdir = "EXITCODE=0\nexit";
		} else if ($this->main->filter_spam_status === 'spambox') {
			$spamdir = "to {$maildirpath}/.Spam/";
		} else if ($this->main->filter_spam_status === 'mailbox') {
			$spamdir = "to {$maildirpath}/Maildir";
		}

		dprint("Spam status " . $this->main->__var_spam_status);
		$addextraspamheader = null;

		if ($this->main->isOn('__var_spam_status')) {
			if ($this->main->__var_spam_driver === 'spamassassin') {
				$maildropspam = "spamc -p 783 -u {$this->main->nname}";
				$addextraspamheader = "\nif ( /^X-Spam-status: Yes/ )\n{\n    $spamdir\n} \n";
			} else {
				$bogconf = "$mailpath/$user/.bogopref.cf";

				if (!lxfile_exists($bogconf)) {
					lxfile_touch($bogconf);
				}

				$maildropspam = "bogofilter -d /var/bogofilter/ -ep -c $bogconf";
				$addextraspamheader = "\nif ( /^X-Bogosity: Spam, tests=bogofilter/ )\n{\n    $spamdir\n}\n";
			}

			$fdata .= "| /var/qmail/bin/preline maildrop $maildropfile\n";
		} else {
			$fdata .= "|true\n";
			$fdata .= "./Maildir/\n";
		}

		$spamdirm = "$mailpath/$user/Maildir";

		$maildropdata = "SHELL=/bin/sh\n\n";

		if ($this->main->isOn('__var_spam_status')) {
			$maildropdata .= "if ( \$SIZE < 96144 )\n{\n    exception {\n        xfilter \"$maildropspam\"\n    }\n}\n $addextraspamheader\n";
		}

		$maildropdata .= "to {$maildirpath}/\n";

		if ($this->main->isOn('no_local_copy')) {
			dprint("Setting to null\n");
			$fdata = null;
		}

		if ($this->main->isOn('forward_status')) {
			foreach ($this->main->forward_a as $value) {
				$value->nname = trim($value->nname);

				if (csb($value->nname, "|")) {
					$fdata .= "{$value->nname}\n";
				} else if (csa($value->nname, "@")) {
					$fdata .= "&{$value->nname}\n";
				} else {
					$fdata .= "&$value->nname@$domain\n";
				}
			}
		}

		lxfile_rm($maildropfile);
		lfile_write_content($maildropfile, $maildropdata, $sysuser);

		lxfile_rm($qmailfile);
		lfile_write_content($qmailfile, $fdata, $sysuser);
		lxfile_unix_chmod($maildropfile, "700");
	}

	static function setMaildirFolder($sysuser, $maildirpath)
	{
		$dirsub = array('Drafts', 'Sent', 'Trash', 'Spam');

		$courierimapsub = $dovecotsub = '';

		$courierimapfile = "{$maildirpath}/courierimapsubscribed";
		$dovecotfile = "{$maildirpath}/subscriptions";

		foreach ($dirsub as $k => $v) {
			lxuser_return($sysuser, "maildirmake", "-f", $v, $maildirpath);
			$courierimapsub .= "INBOX." . $v . "\n";
			$dovecotsub .= $v . "\n";
		}

		if (!file_exists($courierimapfile)) {
			if (!file_exists($dovecotfile)) {
				lxuser_put_contents($sysuser, $courierimapfile, $courierimapsub);
				lxuser_put_contents($sysuser, $dovecotfile, $dovecotsub);
			} else {
				lxfile_cp($dovecotfile, $courierimapfile);
				$t = lxfile_get_contents($courierimapfile);
				$a = explode("\n", $t);

				foreach ($a as $k => $v) {
					$a[$k] = "INBOX." . $v;
				}

				$t = implode("\n", $a);

				lxuser_put_contents($sysuser, $courierimapfile, $t);
			}
		} else {
			if (!file_exists($dovecotfile)) {
				lxfile_cp($courierimapfile, $dovecotfile);
				$t = lxfile_get_contents($dovecotfile);
				$t = str_replace("INBOX.", "", $t);
				lxuser_put_contents($sysuser, $dovecotfile, $t);
			}
		}

		// MR -- NOTE: better using glob() for identify folders and then create subscribe
		// $d = glob("{$maildirpath}/.*", GLOB_ONLYDIR);
	}

	function syncUserDel()
	{
		global $gbl, $sgbl, $ghtml;

		$quser = explode("@", $this->main->nname);
		$mailpath = mmail__qmail::getDir($quser[1]);
		$mailpath = str_replace($sgbl->__path_mail_root, $sgbl->__path_mail_data, $mailpath);

		$domain = $quser[1];

		$sys_cmd = "__path_mail_root/bin/vdeluser";
		lxuser_return(mmail__qmail::getUserGroup($domain), $sys_cmd, $this->main->nname);
	}

	function createAutoResFile()
	{
		global $gbl, $sgbl, $ghtml;

		$quser = explode("@", $this->main->nname);

		$mailpath = mmail__qmail::getDir($quser[1]);
		$mailpath = str_replace($sgbl->__path_mail_root, $sgbl->__path_mail_data, $mailpath);

		$domain = $quser[1];

		if (csb($mailpath, "domain") && cse($mailpath, "exist")) {
			dprint("Got a non-existent Domain $mailpath\n");

			return;
		}

		$sys_path = "$mailpath/{$quser[0]}";
		$sys_fpath = "$mailpath/{$quser[0]}/autorespond/message";
		$sys_apath = "$mailpath/{$quser[0]}/autorespond";

		if (!lxfile_exists($sys_apath)) {
			lxuser_mkdir(mmail__qmail::getUserGroup($domain), $sys_apath);
			lxfile_unix_chown_rec($sys_apath, mmail__qmail::getUserGroup($domain));
		}

		$sysuser = mmail__qmail::getUserGroup($domain);
		lxuser_put_contents($sysuser, $sys_fpath, "From: {$this->main->nname}\nSubject: Response\n\n Message Received");
	}

	function syncRealPass()
	{
		global $gbl, $sgbl, $ghtml;
		$quser = explode("@", $this->main->nname);

		$mailpath = mmail__qmail::getDir($quser[1]);
		$mailpath = str_replace($sgbl->__path_mail_root, $sgbl->__path_mail_data, $mailpath);

		$domain = $quser[1];
		$sysuser = mmail__qmail::getUserGroup($domain);

		if (!$this->main->realpass) {
			$pass = "something";
		} else {
			$pass = $this->main->realpass;
		}

		lxuser_return($sysuser, "__path_mail_root/bin/vpasswd", $this->main->nname, $pass);
	}

	function syncQuota()
	{
		global $gbl, $sgbl, $ghtml;

		$quser = explode("@", $this->main->nname);

		$mailpath = mmail__qmail::getDir($quser[1]);
		$mailpath = str_replace($sgbl->__path_mail_root, $sgbl->__path_mail_data, $mailpath);

		$domain = $quser[1];
		$sysuser = mmail__qmail::getUserGroup($domain);

		if (is_unlimited($this->main->priv->maildisk_usage)) {
			$disksize = "NOQUOTA";
		} else {
			$disksize = $this->main->priv->maildisk_usage * 1024 * 1024;
		}

		$ret = lxuser_return($sysuser, "__path_mail_root/bin/vsetuserquota", $this->main->nname, $disksize);
	}

	function syncToggleUser()
	{
		global $gbl, $sgbl, $login;

		$quser = explode("@", $this->main->nname);

		$mailpath = mmail__qmail::getDir($quser[1]);
		$mailpath = str_replace($sgbl->__path_mail_root, $sgbl->__path_mail_data, $mailpath);

		$domain = $quser[1];
		$sysuser = mmail__qmail::getUserGroup($domain);

		if ($this->main->status === "on") {
			lxuser_return($sysuser, "__path_mail_root/bin/vmoduser", "-x", $this->main->nname);
		} else {
			lxuser_return($sysuser, "__path_mail_root/bin/vmoduser", "-pwsi", $this->main->nname);
		}
	}

	function syncAutoRes()
	{
		global $gbl, $sgbl, $login;

		$quser = explode("@", $this->main->nname);

		$mailpath = mmail__qmail::getDir($quser[1]);
		$mailpath = str_replace($sgbl->__path_mail_root, $sgbl->__path_mail_data, $mailpath);

		$domain = $quser[1];
		$sysuser = mmail__qmail::getUserGroup($domain);

		$autorespath = "$mailpath/{$quser[0]}/autorespond";

		if (!lxfile_exists($autorespath)) {
			lxuser_mkdir($sysuser, $autorespath);
		}

		if (isset($this->main->__var_autores_subject)) {
			$autoresfile = "$autorespath/message";
			$mess = "From: {$this->main->nname}\nSubject: {$this->main->__var_autores_subject}\n\n";
			$mess .= $this->main->__var_autores_message;

			lxuser_put_contents($sysuser, $autoresfile, $mess);
		}
	}

	function dbactionAdd()
	{
		$this->syncUserAdd();
		$this->createAutoResFile();
	}

	function dbactionDelete()
	{
		$this->syncUserDel();
	}

	function syncAutoRespond()
	{
	}

	function clearSpamDb()
	{
		global $gbl, $sgbl, $login;
                
                // only run for bogofilter
                if ($this->main->__var_spam_driver === 'bogofilter') {
                    list($user, $domain) = explode("@", $this->main->nname);

                    $mailpath = mmail__qmail::getDir($domain);
                    $mailpath = str_replace($sgbl->__path_mail_root, $sgbl->__path_mail_data, $mailpath);

                    $prefpath = "$mailpath/$user/.bogopref.cf";
                    $fname = fix_nname_to_be_variable($this->main->nname);
                    lunlink("/var/bogofilter/$fname.wordlist.db");
                    system("bogofilter -d /var/bogofilter/ --wordlist=R,user,$fname.wordlist.db,1 -n < /etc/my.cnf");
                }
	}

        
 /**
 *  Trains Spamassassin or bogofilter 
 *  (detects if spamassassin isinstalled and uses sa-learn if it is)
 */
	function trainAsSpam()
	{
		global $global_dontlogshell;

		$global_dontlogshell = true;

		$listname = "{$this->main->subaction}_list";

		$name = fix_nname_to_be_variable($this->main->nname);
                
                                
		if ($this->main->__var_spam_driver === 'spamassassin') {

		
		// For Kloxo-wp we are using site wide bayes
                //TODO remove train_as_system_ option from menu if Spamassassin is used    
                    /*if (csb($this->main->subaction, "train_as_system_")) {
                       	$optstring = null;
                    } else {
			$optstring = "--wordlist=R,user,$name.wordlist.db,1";
                    }
                    */
                    $flag = "--ham";

                    if (cse($this->main->subaction, '_spam')) {
                        $flag = "--spam";
                    }
                    
                    foreach ($this->main->$listname as $f) {
			$name = str_replace("_s_coma_s_", ",", $f);
			$name = str_replace("_s_colon_s_", ":", $name);
			$cmd = "/usr/bin/sa-learn $flag $name";
			do_exec_system("vpopmail", null, $cmd, $out, $err, $ret, null);
                    }
                    
		} else {
		
                    if (csb($this->main->subaction, "train_as_system_")) {
                       	$optstring = null;
                    } else {
			$optstring = "--wordlist=R,user,$name.wordlist.db,1";
                    }

                    $flag = "-n";

                    if (cse($this->main->subaction, '_spam')) {
                        $flag = "-s";
                    }

                    foreach ($this->main->$listname as $f) {
			$name = str_replace("_s_coma_s_", ",", $f);
			$name = str_replace("_s_colon_s_", ":", $name);
			$cmd = "bogofilter -d /var/bogofilter/ $optstring $flag < $name";
			do_exec_system("__system__", null, $cmd, $out, $err, $ret, null);
                    }
                }
	}

	function dbactionUpdate($subaction)
	{
		switch ($subaction) {
			case "full_update":
				$this->syncQmail();
				$this->syncRealPass();
				$this->syncAutoRes();
				$this->syncToggleUser();
				$this->syncQuota();
				break;

			case "toggle_status":
				$this->syncToggleUser();
				break;

			case "train_as_system_spam":
			case "train_as_system_ham":
			case "train_as_spam":
			case "train_as_ham":
				$this->trainAsSpam();
				break;

			case "clear_spam_db":
				$this->clearSpamDb();
				break;

			case "password" :
				$this->syncRealPass();
				break;

			case "limit":
				$this->syncQuota();
				break;

			case "autores":
				$this->syncAutoRes();
				break;

			case "add_forward_a":
			case "delete_forward_a":
			case "sync_forward" :
			case "sync_autorespond" :
			case "change_spam" :
			case "configuration" :
			case "filter" :
				$this->syncQmail();
				break;
		}
	}
}
