<?php 

class Mmail__Qmail  extends lxDriverClass {

// Core

function do_backup()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$mailpath = self::getDir($this->main->nname);
	foreach($this->main->__var_accountlist as $ac) {
		$list[] = "$ac/Maildir";
	}
	return array($mailpath, $list);
}

static function generateDKey($domain)
{

	$pfile = "/var/qmail/control/domainkeys/$domain/public.txt";
	if (!$domain) {
		return;
	}
	if(lxfile_exists("/var/qmail/control/domainkeys/$domain/public.txt")) {
		return lfile_get_contents("/var/qmail/control/domainkeys/$domain/public.txt");
	}

	lxfile_mkdir("/var/qmail/control/domainkeys/$domain");

	$oldir = getcwd();

	$ret = chdir("/var/qmail/control/domainkeys/$domain");

	if (!$ret) {
		log_error("Domain key creation failed\n");
		chdir($oldir);
		return null;
	}

	lxshell_return("openssl", "genrsa", "-out", "private", 384);

	$tfile = lx_tmp_file("rsagen");

	$out = lxshell_output("openssl", "rsa", "-in", "private", "-out", $tfile, "-pubout", "-outform", "PEM");

	$list = lfile($tfile);
	lunlink($tfile);
	$out = null;
	foreach($list as $k => $l) {
		if (!csa($l, "--")) {
			$out .= trim($l);
		}
	}
	$out = trim($out);

	lfile_put_contents($pfile, $out);

	$retval = lfile_get_contents($pfile);

	chdir($oldir);

	return $retval;

}

function do_restore($docd)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$mailpath = self::getDir($this->main->nname);
	lxshell_unzip_with_throw($mailpath, $docd);
	lxfile_unix_chown_rec($mailpath, mmail__qmail::getUserGroup($this->main->nname));
}

static function getDir($domain)
{
	global $global_shell_error, $global_shell_ret, $global_shell_out;
	global $global_dontlogshell;

	// MR -- change again with using this function instead 'Invalid domain name'
	if (!self::doesDomainExist($domain)) { return false; }

	$tmp = $global_dontlogshell;
	$global_dontlogshell = true;
	$out = trim(lxshell_output("__path_mail_root/bin/vdominfo", "-d", $domain));
	$out = explode("\n", $out);
	$out = $out[0];
	$global_dontlogshell = $tmp;
/*
	if ($out === 'Invalid domain name') {
		$out = false;
	}
*/
	return $out;
}

static function doesDomainExist($domain)
{
	global $global_shell_error, $global_shell_ret, $global_shell_out;
	global $global_dontlogshell;
	
	$tmp = $global_dontlogshell;
	$global_dontlogshell = true;
	$ret = lxshell_return("__path_mail_root/bin/vdominfo", "-d", $domain);
	$global_dontlogshell = $tmp;

	if ($ret) { return false; }

	return true;
}

static function getUserGroup($domain, $flag_useralone = false)
{
	global $global_dontlogshell;
	$tmp = $global_dontlogshell;
	$global_dontlogshell = true;
	$user = trim(lxshell_output("__path_mail_root/bin/vdominfo", "-u", $domain));
	$user = explode("\n", $user);
	$user = $user[0];

	if ($flag_useralone) { return $user; }
	
	$group = trim(lxshell_output("__path_mail_root/bin/vdominfo", "-g", $domain));
	$group = explode("\n", $group);
	$group = $group[0];
	$global_dontlogshell = $tmp;
	return "$user:$group";
}

function syncToggleDomain()
{

	global $gbl, $sgbl, $login;

	if($this->main->status === "on"){
		lxshell_return("__path_mail_root/bin/vmoduser",  "-x", $this->main->nname);
	} else {
		lxshell_return("__path_mail_root/bin/vmoduser", "-pwi", $this->main->nname);
	}

}

function convertToForward()
{
	$sys_cmd =  "__path_mail_root/bin/vdeldomain";
	$ret = lxshell_return($sys_cmd, $this->main->nname);

	if (!$ret) {
		throw new lxException("could_not_delete_domain", '');
	}

	lxshell_return("__path_mail_root/bin/vaddaliasdomain", $this->main->redirect_domain, $this->main->nname);
}

static function createAliasdomain($source, $maindomain)
{
	$sys_cmd = "__path_mail_root/bin/vaddaliasdomain";
	lxshell_return($sys_cmd, $maindomain, $source);
}

function addDomain()
{
	global $gbl, $sgbl, $login, $ghtml; 
	global $global_shell_error;
	
	//$catchall = "postmaster";

	if ($this->main->ttype === 'forward') {
		$sys_cmd =  "__path_mail_root/bin/vaddaliasdomain";
		lxshell_return($sys_cmd, $this->main->redirect_domain, $this->main->nname);
		return;
	}

	if (self::doesDomainExist($this->main->nname)) {
		return;
	}

	$sys_cmd =  "__path_mail_root/bin/vadddomain";
	//Hack hack... Read the mail password in the input.
	if (!$this->main->__var_password) {
		$password = 'something';
	} else {
		$password = $this->main->__var_password;
	}

	if (strlen($password) > 8) {
		$password = substr($password, 0, 7);
	}
	$uid = os_get_uid_from_user($this->main->systemuser);
	$gid = os_get_gid_from_user($this->main->systemuser);
	//$ret = lxshell_return($sys_cmd, '-i', $uid, '-g', $gid, $this->main->nname, "-e", $catchall, $password);
	$ret = lxshell_return($sys_cmd, '-i', $uid, '-g', $gid, $this->main->nname, "-b", $password);

	//$ret = lxshell_return($sys_cmd, $this->main->nname, "-e", $catchall, $password);

	if ($ret) {
		throw new lxException("could_not_add_mail", 'mailpserver', $global_shell_error);
	}

	/*
	$listdom = "lists.{$this->main->nname}";
	lxshell_return($sys_cmd, '-i', $uid, '-g', $gid, $listdom, $password);
	//lxshell_return($sys_cmd, $listdom, $password);
	$mailpath = self::getDir($listdom);
	$qmailfile = "$mailpath/.qmail-default";

	lxfile_unix_chown($qmailfile, mmail__qmail::getUserGroup($this->main->nname));
	*/
	
	$this->updateQmaildefault();
	//createRestartFile('courier-imap');

}

function doesListExist()
{
	return self::doesDomainExist("lists.{$this->main->nname}");
}

function filterRemoteList($qfile, $string, $liststring)
{
	$nlist = null;
	$list = lfile_trim($qfile);
	foreach($list as $l) {
		if ($l === $string || $l === $liststring) {
			continue;
		}

		if (array_search_bool($l, $nlist)) {
			continue;
		}
		$nlist[] = $l;
	}
/*
	//--- See Issue #512 for more information
//	if ($this->main->remotelocalflag === 'remote') {
//	} else {
		$nlist[] = $string;
		if ($this->doesListExist()) {
			$nlist[] = $liststring;
		}
//	}
*/
	//--- See Issue #593 for more information
	if ($this->main->remotelocalflag !== 'remote') {
		$nlist[] = $string;
	}

	//--- always exist lists.*
	if ($this->doesListExist()) {
		$nlist[] = $liststring;
	}

	$out = implode("\n", $nlist);
	lfile_put_contents($qfile, "$out\n");
}

function remoteLocalMail()
{
	$qfile = "/var/qmail/control/virtualdomains";
	$string = "{$this->main->nname}:{$this->main->nname}";
	$liststring = "lists.{$this->main->nname}:lists.{$this->main->nname}";
	$this->filterRemoteList($qfile, $string, $liststring);

	$qfile = "/var/qmail/control/rcpthosts";
	$string = "{$this->main->nname}";
	$liststring = "lists.{$this->main->nname}";
	$this->filterRemoteList($qfile, $string, $liststring);

	$qfile = "/var/qmail/control/morercpthosts";
	if (lxfile_exists($qfile) && $this->main->remotelocalflag === 'remote') {
		$this->filterRemoteList($qfile, $string, $liststring);
		lxshell_return("/var/qmail/bin/qmail-newmrh");
	}

	createRestartFile('qmail');
}

function updateQmaildefault()
{
	global $gbl, $sgbl, $login, $ghtml; 

	$spamstring = null;
	$mailpath = self::getDir($this->main->nname);

	dprint("{$this->main->catchall}\n");
	dprint("$mailpath");
	if ($this->main->catchall=="--bounce--") {
		$catchallstring = 'bounce-no-mailbox';
	} else if($this->main->catchall=="Delete") {
		$catchallstring = "delete";
	} else {
		$catchallstring = "$mailpath/{$this->main->catchall}";
	}

	$adminfile = "$mailpath/.qmail-default";
	$fdata = "| /home/lxadmin/mail/bin/vdelivermail '' $catchallstring\n";


	lfile_put_contents($adminfile, $fdata);
	//lfile_write_content($adminfile, $fdata, mmail__qmail::getUserGroup($this->main->nname));
	
}

function delDomain()
{
	global $gbl, $sgbl, $login, $ghtml; 
	lxshell_return("__path_mail_root/bin/vdeldomain", "-f", $this->main->nname);
	lxfile_rm_rec_content("/var/qmail/control/domainkeys/{$this->main->nname}");
	lxfile_rm("/var/qmail/control/domainkeys/{$this->main->nname}");
	if ($this->doesListExist()) {
		dprint("Lists exists. deleting list..\n");
		lxshell_return("__path_mail_root/bin/vdeldomain", "lists.{$this->main->nname}");
	}
}

function dbactionAdd()
{
	//throw new lxexception("Qmail screw", "");
	$this->addDomain();
	foreach((array) $this->main->__var_addonlist as $d) {
		if ($d->isOn('mail_flag')) {
			lxshell_return("__path_mail_root/bin/vaddaliasdomain", $this->main->nname, $d->nname);
		}
	}
}

function dbactionDelete()
{
	$this->delDomain();
	foreach((array) $this->main->__var_addonlist as $d) {
		if (self::doesDomainExist($d->nname)) {
			lxshell_return("__path_mail_root/bin/vdeldomain", $d->nname);
		}
	}
	lxfile_rm_rec("/home/lxadmin/mail/spamassassin/$this->nname");

}

function addAlias()
{
	lxshell_return("__path_mail_root/bin/vaddaliasdomain", $this->main->nname, $this->main->__var_aliasdomain);
}

function deleteAlias()
{
	lxshell_return("__path_mail_root/bin/vdeldomain", $this->main->__var_aliasdomain);
}

// MR -- this function is missing!
function fixRedirectDomain() {
}

function fullUpdate()
{
	if ($this->ttype === 'forward') {
		$this->fixRedirectDomain();
	} else {
		$this->updateQmaildefault();
		$this->syncToggleDomain();
		$this->remoteLocalMail();
		$dir = mmail__qmail::getDir($this->main->nname);
		if ($dir && lxfile_exists($dir)) {
			lxfile_unix_chown_rec($dir, mmail__qmail::getUserGroup($this->main->nname));
		}
	}
}

function changeOwner()
{
	$uid = os_get_uid_from_user($this->main->systemuser);
	$gid = os_get_gid_from_user($this->main->systemuser);

	$list = lfile("/var/qmail/users/assign");

	foreach($list as &$__l) {
		if ($__l === "\n") {
			$__l = "";
			continue;
		}

		if ($__l === ".") {
			continue;
		}

		$domainname = $this->main->nname;
		$path = self::getDir($domainname);

		lxfile_unix_chown_rec($path, "$uid:$gid");

		if (csb($__l, "+$domainname-")) {
			$__l = "+$domainname-:$domainname:$uid:$gid:$path:-::\n";
		}

		$domainname = "lists.{$this->main->nname}";
		$path = self::getDir($domainname);

		if (!$path) { continue; }

		lxfile_unix_chown_rec($path, "$uid:$gid");

		if (csb($__l, "+$domainname-")) {
			$__l = "+$domainname-:$domainname:$uid:$gid:$path:-::\n";
		}
	}

	lfile_put_contents("/var/qmail/users/assign", implode("", $list));
	lxshell_return("/var/qmail/bin/qmail-newu");

	foreach((array) $this->main->__var_addonlist as $d) {
		if ($d->isOn('mail_flag')) {
			lxshell_return("__path_mail_root/bin/vdeldomain", $d->nname);
			lxshell_return("__path_mail_root/bin/vaddaliasdomain", $this->main->nname, $d->nname);
		}
	}

	createRestartFile('courier-imap');

}

function dbactionUpdate($subaction)
{
	switch($subaction)
	{
		case "full_update":
			$this->fullUpdate();
			break;

		case "redirect_domain":
			$this->fixRedirectDomain();
			break;

		case "toggle_status":
			$this->syncToggleDomain();
			break;

		case "graph_mailtraffic":
			return rrd_graph_single("mailtraffic (bytes)", $this->main->nname, $this->main->rrdtime);
			break;


		case "change_preference":
		case "change_spam":
		case "catchall":
			$this->updateQmaildefault();
			break;

		case "remotelocalmail":
			$this->remotelocalMail();
			break;

		case "add_alias":
			$this->addAlias();
			break;

		case "delete_alias":
			$this->deleteAlias();
			break;

		case "changeowner":
			$this->changeOwner();
			break;

	}
}

}
