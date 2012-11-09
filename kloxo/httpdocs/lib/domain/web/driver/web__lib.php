<?php

class web__ extends lxDriverClass
{
	function __construct()
	{
	}

	static function uninstallMeTrue($drivertype = null)
	{
		$list = getWebDriverList($drivertype);

		$l = $list[0];

		if ($l === 'apache') { $l = 'httpd'; }

		$blist = getBranchList($l);

		if (!$blist) { $blist = array($l); }

		// MR -- for fixed an issue version conflict!
		if ($l === 'httpd') {
			$blist[] = "{$l}-tools";
			$blist[] = "{$l}-devel";
		} elseif ($l === 'lighttpd') {
			$blist[] = "{$l}-fastcgi";
			$blist[] = "{$l}-devel";
		} elseif ($l === 'nginx') {
			$blist[] = "{$l}-devel";
		}

		lxshell_return("service", $l, "stop");

		foreach ($blist as $k => $v) {
			lxshell_return("rpm", "-e", "--nodeps", $v);
		}

		lxshell_return("chkconfig", $l, "off");

		if (file_exists("/etc/init.d/{$l}")) {
			lunlink("/etc/init.d/{$l}");
		}
	}

	static function installMeTrue($drivertype = null)
	{
		$list = getWebDriverList($drivertype);

		$isproxyorapache = isWebProxyOrApache($drivertype);

		if (!$isproxyorapache) {
			self::uninstallMeTrue('apache');
		}

		foreach ($list as &$l) {
			$a = ($l === 'apache') ? 'httpd' : $l;

			$hhcpath = '/home/httpd/conf';

			$hwpath  = "/home/{$l}";
			$hwcpath = "/home/{$l}/conf";
			$hawpath = "/home/{$l}/etc";
			$hawcpath = "/home/{$l}/etc/conf";
			$hawcdpath = "/home/{$l}/etc/conf.d";

			if ($a === 'httpd') {
				self::setWebserverInstall($a);

				$ret = setRpmInstallWithLocalFirst("mod_rpaf");

				if (!$ret) {
					throw new lxException("install_mod_rpaf_failed", 'parent');
				}

				$ret = setRpmInstallWithLocalFirst("mod_ssl");

				if (!$ret) {
					throw new lxException("install_mod_ssl_failed", 'parent');
				}

				lxfile_cp(getLinkCustomfile("/home/apache/etc/conf.d", "rpaf.conf"),
						"/etc/httpd/conf.d/rpaf.conf");
				lxfile_cp(getLinkCustomfile("/home/apache/etc/conf.d", "ssl.conf"),
						"/etc/httpd/conf.d/ssl.conf");
			} elseif ($a === 'lighttpd') {
				self::setWebserverInstall($a);

				$ret = setRpmInstallWithLocalFirst("{$a}-fastcgi");

				if (!$ret) {
					throw new lxException("install_{$a}-fastcgi_failed", 'parent');
				}

				// MR -- some rpm not create this file
				if (!file_exists("/etc/lighttpd/local.lighttpd.conf")) {
					exec("echo '' > /etc/lighttpd/local.lighttpd.conf");
				}

				// MR -- lighttpd problem if /var/log/lighttpd not apache:apache chown
				lxfile_unix_chown("/var/log/{$a}", "apache:apache");
			} elseif ($a === 'nginx') {
				self::setWebserverInstall($a);

				$ret = setRpmInstallWithLocalFirst("GeoIP");

				if (!$ret) {
					throw new lxException("install_GeoIP_failed", 'parent');
				}

				// MR -- nginx 1.3.5 from centalt also copy httpd ssl to /etc/nginx/conf.d
				// it's make nginx not able started. Because nginx for kloxo don't need this file,
				// and then this file deleted.

				$nginxsslcfgfile = '/etc/nginx/conf.d/ssl.conf';

				if (file_exists($nginxsslcfgfile)) {
					exec("rm -rf {$nginxsslcfgfile}");
				}
			}

			lxshell_return("chkconfig", $a, "on");

			setCopyWebConfFiles($l);

			createRestartFile($l);
		}

		self::setInstallPhpfpm();
	}

	static function setWebserverInstall($webserver)
	{
		$blist = getBranchList($webserver);

		if (!$blist) { $blist = array($webserver); }

		foreach ($blist as $k => $b) {
			$ret = setRpmInstallWithLocalFirst($b);

			if ($ret) { break; }
		}

		// MR -- overwrite init

		$altname = ($webserver === 'httpd') ? 'apache' : $webserver;

		lxfile_cp(getLinkCustomfile("/home/{$altname}/etc/init.d", "{$webserver}.init"),
				"/etc/rc.d/init.d/{$webserver}");

		if (!$ret) {
			throw new lxException("install_{$webserver}_failed", 'parent');
		}
	}

	static function setUnnstallPhpfpm()
	{
		$phpbranch = getPhpBranch();

		exec("yum remove {$phpbranch}-fpm -y");
	}

	static function setInstallPhpfpm()
	{
		exec("cp -rf /usr/local/lxlabs/kloxo/file/php-fpm /home");

		$phpbranch = getPhpBranch();

		$out = isRpmInstalled("{$phpbranch}-fpm");

		if (!$out) {
			$ret = lxshell_return("yum", "-y", "install", "{$phpbranch}-fpm");
		}

		if (version_compare(getPhpVersion(), "5.3.2", ">")) {
			$fpmused = "php53";
		} else {
			$fpmused = "php";

		}

		lxfile_cp(getLinkCustomfile("/home/php-fpm/tpl", "{$fpmused}-fpm.conf"), "/etc/php-fpm.conf");

		lxshell_return("chkconfig", "php-fpm", "on");
	}

	function createConfFile()
	{
		$this->clearDomainIpAddress();

		$input = array();

		$input['domainname'] = $this->getDomainname();
		$input['user'] = $this->getUser();

		if ((!$input['domainname']) || (!$input['user'])) {
			return null;
		}

		$input['wildcards'] = $this->isWildcards();
		$input['indexorder'] = $this->getIndexFileOrder();
		$input['parkdomains'] = $this->getParkDomains();
		$input['serveraliases'] = $this->getServerAliases();
		$input['dirprotect'] = $this->getDirprotect();

		$input['certnamelist'] = ($this->getSslCertNameList()) ?
				$this->getSslCertNameList() : $this->getSslCertNameList('*');

		$input['stats'] = $this->getStats();
		$input['wwwredirect'] = $this->getWwwRedirect();
		$input['domainredirect'] = $this->getRedirectDomains();

		$input['apacheextratext'] = $this->getApacheExtraText();
		$input['lighttpdextratext'] = $this->getLighttpdExtraText();
		$input['nginxextratext'] = $this->getNginxExtraText();

		$input['disabled'] = $this->getDisabled();
		$input['extrabasedir'] = $this->AddExtraBaseDir();
		$input['blockips'] = $this->getBlockIP();

		$input['webmailremote'] = $this->getWebmailInfo('remote');
		$input['webmailapp'] = $this->getWebmailInfo('app');
		$input['webmailappdefault'] = self::getWebmailAppDefault();

		$input['rootpath'] = $this->main->getFullDocRoot();
		$input['disablephp'] = $this->disablePhp();

		$input['phpcgitype'] = $this->getPhpCgiType();
		$input['fastcgichildren'] = $this->getFastcgiChildren();
		$input['fastcgimaxprocs'] = $this->getFastcgiMaxProcs();

		$input['setdomains'] = true;

		$input['redirectionlocal'] = $this->getRedirectionLocal();
		$input['redirectionremote'] = $this->getRedirectionRemote();

		self::setCreateConfFile($input);

		$this->setLogfile();
	}

// MR -- (1) related to create conf file

	function updateMainConfFile()
	{
		$input = array();

		$input['setdefaults'] = 'init';
		$input['indexorder'] = self::getIndexFileOrderDefault();
		$input['certnamelist'] = $this->getSslCertNameList('*');

		self::setCreateConfFile($input);
	}

	function createSSlConf()
	{
		$input = array();

		$input['certnamelist'] = $this->getSslCertNameList('free');
		$input['setdefaults'] = 'ssl';
		$input['indexorder'] = self::getIndexFileOrderDefault();		
		$input['userlist'] = $this->getUserList();

		self::setCreateConfFile($input);
	}

	function createCpConfig()
	{
		$list = array('default', 'cp', 'disable');

		$input = array();
		
		$input['indexorder'] = self::getIndexFileOrderDefault();
		$input['certnamelist'] = $this->getSslCertNameList('*');

		foreach ($list as &$v) {
			$input['setdefaults'] = $v;
			
			if ($v === 'default') {
				$input['userlist'] = $this->getUserList();			
			}

			self::setCreateConfFile($input);
		}
	}

	function createWebDefaultConfig()
	{
		$input = array();

		$input['setdefaults'] = 'webmail';
		$input['certnamelist'] = $this->getSslCertNameList('*');
		$input['webmailappdefault'] = self::getWebmailAppDefault();
		$input['indexorder'] = self::getIndexFileOrderDefault();

		self::setCreateConfFile($input);
	}

	static function getWebmailAppDefault()
	{
		global $login;

		$webmailapp = $login->getObject('general')->generalmisc_b->webmail_system_default;

		if (($webmailapp === '--chooser--') || (!isset($webmailapp))) {
			$ret = '';
		} else {
			$ret = $webmailapp;
		}

		return $ret;
	}

	function createPhpFpmConfig()
	{
		$input = array();
		
		$input['userlist'] = $this->getUserList();

		$phpver = getPhpVersion();

		if (version_compare($phpver, "5.3.3", "<")) {
			// MR -- that mean 'xml' type config
			$tplsource = getLinkCustomfile("/home/php-fpm/tpl", "php-fpm.conf.tpl");
			$tpltarget = "/etc/php-fpm.conf";
			$tpl = file_get_contents($tplsource);
			$tplparse = getParseInlinePhp($tpl, $input);
			file_put_contents($tpltarget, $tplparse);
		} else {
			// MR -- that mean 'ini' type config
			$cfgmain = getLinkCustomfile("/home/php-fpm/etc", "php53-fpm.conf");
			lxfile_cp($cfgmain, "/etc/php-fpm.conf");

			$tplsource = getLinkCustomfile("/home/php-fpm/tpl", "php53-fpm-pool.conf.tpl");
			$tpl = file_get_contents($tplsource);

			foreach ($input['userlist'] as &$user) {
				// MR - for 'real' users
				$input['user'] = $user;
				$tpltarget = "/etc/php-fpm.d/{$user}.conf";
				$tplparse = getParseInlinePhp($tpl, $input);
				if ($tplparse) {
					file_put_contents($tpltarget, $tplparse);	
				}
			}

			// MR - for 'default' user
			$input['user'] = 'apache';
			$tpltarget = "/etc/php-fpm.d/default.conf";
			$tplparse = getParseInlinePhp($tpl, $input);
			file_put_contents($tpltarget, $tplparse);	

			if (file_exists("/etc/php-fpm.d/www.conf")) {
				lxfile_mv("/etc/php-fpm.d/www.conf", "/etc/php-fpm.d/www.nonconf");
			}

		}

		createRestartFile('php-fpm');
	}

// MR -- (2) call by 'related to create conf file' (1)

	function getDomainname()
	{
		$ret = (isset($this->main->nname)) ? $this->main->nname : null;

		return $ret;
	}

	function getUser()
	{
		$ret = (isset($this->main->customer_name)) ? $this->main->customer_name : null;

		return $ret;
	}
	
	function getUserList()
	{
		$clist = rl_exec_get('localhost', 'localhost', 'getAllClientList', null);

		foreach ($clist as &$n) {
/*
			$userinfo = posix_getpwnam($n);
			$fpmport = (50000 + $userinfo['uid']);

			if ($fpmport === 50000) { continue; }
*/

			$users[] = $n;
		}

		return $users;
	}

	static function getAllIps()
	{
		$iplist = os_get_allips();

		$list = array();

		foreach ($iplist as $ip) {
			$list[] = $ip;
		}

		return $list;
	}

	function getWebmailInfo($for)
	{
		$domainname = $this->getDomainname();

		// MR -- look like not work calling $this->main->__var_mmaillist
		// so, taken from database directly

		$string = "syncserver = '{$this->main->__syncserver}'";
		$mmaildb = new Sqlite(null, 'mmail');
		$mlist = $mmaildb->getRowsWhere($string, array('nname', 'parent_clname', 'webmailprog', 'webmail_url', 'remotelocalflag'));

		if ($mlist) {
			foreach ($mlist as $m) {
				if ($m['nname'] === $domainname) {
					$list = $m;
					break;
				}
			}
		} else {
			// --- for the first time domain create
			if (!isset($list)) {
				$list = array('nname' => $domainname, 'parent_clname' => 'domain-'.$domainname, 
						'webmailprog' => '', 'webmail_url' => '', 'remotelocalflag' => 'local');
			}
		}

		$r = null;

		if ($for === 'remote') {
			if ($list['remotelocalflag'] === 'remote') {
				$r = $list['webmail_url'];
			}

		} elseif ($for === 'app') {
			if ($list['remotelocalflag'] !== 'remote') {
				if ($list['webmailprog'] === '--system-default--') {
					$r = self::getWebmailAppDefault();
				} else {
					if ($list['webmailprog'] === '--chooser--') {
						$r = '';
					} else {
						$r = $list['webmailprog'];
					}
				}
			}
		}

		return $r;
	}

	static function setCreateConfFile($input)
	{
		$conffile = $conftpl = $conftype = null;

		if (array_key_exists('setdefaults', $input)) {
			$conftype = $conftpl = 'defaults';

			if ($input['setdefaults'] === 'ssl') {
				$conffile = '__ssl.conf';
			} elseif ($input['setdefaults'] === 'default') {
				$conffile = '_default.conf';
			} else {
				$conffile = "{$input['setdefaults']}.conf";
			}
		} else {
			if (array_key_exists('setdomains', $input)) {
				$conftype = 'domains';

				$conftpl = 'domains';

				$conffile = $input['domainname'] . '.conf';

				self::setHttpdFcgid($input);
			}
		}

		$input['reverseproxy'] = isWebProxy();

		$list = getWebDriverList();

		foreach ($list as &$l) {
			$tplsource = getLinkCustomfile("/home/{$l}/tpl", "{$conftpl}.conf.tpl");

			if ($conftpl === 'defaults') {
				 if (file_exists("/home/{$l}/conf/{$conftpl}/cp_config.conf")) {
					lxfile_rm("/home/{$l}/conf/{$conftpl}/cp_config.conf");
				}
			}

			if ($conffile !== 'webmail.conf') {
				$tpltarget = "/home/{$l}/conf/{$conftype}";
			} else {
				$tpltarget = "/home/{$l}/conf/webmails";
			}

			if (file_exists("/home/{$l}/conf/{$conftype}/webmail.conf")) {
				lxfile_rm("/home/{$l}/conf/{$conftype}/webmail.conf");
			}

			$tpl = file_get_contents($tplsource);

			$tplparse = getParseInlinePhp($tpl, $input);

			if ($tplparse) {
				file_put_contents("{$tpltarget}/{$conffile}", $tplparse);
			}

			createRestartFile($l);

			if ($conftype === 'domains') {
				if ($l === 'lighttpd') {
					self::setLighttpdPerlSuexec($input);
				}

				if ($l === 'nginx') {
					self::setNginxCgibinPhp();
				}
			}
		}
	}

	function AddExtraBaseDir()
	{
		$extrabasedir = $this->main->__var_extrabasedir;

		return trim($extrabasedir);
	}

	function getBlockIP()
	{
		$tblockip = $this->main->text_blockip;

		$t = trimSpaces($tblockip);
		$t = trim($t);

		if ($t) {
			$t = str_replace(".*", "", $t);
			$t = explode(" ", $t);
		} else {
			$t = null;
		}

		return $t;
	}

	function disablePhp()
	{
		$this->setPhpIni();

		$ret = ($this->main->priv->isOn('php_flag')) ? false : true;

		return $ret;
	}

	function getPhpCgiType()
	{
		$ret = ($this->main->priv->isOn('phpfcgi_flag')) ? 'fastcgi' : 'suexec';

		return $ret;
	}

	function getFastcgiMaxProcs()
	{
		$n = ($this->main->priv->phpfcgiprocess_num) ?
				$this->main->priv->phpfcgiprocess_num : "0";

		if (($n === 'Unlimited') || ($n === '-')) {
			$n = '0';
		}

		$ret = ($this->main->isOn('fcgi_children')) ? "1" : $n;

		return $ret;
	}

	function getFastcgiChildren()
	{
		$n = ($this->main->priv->phpfcgiprocess_num) ?
				$this->main->priv->phpfcgiprocess_num : "0";

		if (($n === 'Unlimited') || ($n === '-')) {
			$n = '0';
		}

		$ret = ($this->main->isOn('fcgi_children')) ? $n : "1";

		return $ret;
	}

	function getDomainSslIpList()
	{
		$domainname = $this->getDomainname();

		$domainipaddress = (isset($this->main->__var_domainipaddress)) ?
				$this->main->__var_domainipaddress : null;

		if ($domainipaddress) {
			$list = array();

			foreach ($domainipaddress as $ip => $dom) {
				if ($dom === $domainname) {
					$list[] = $ip;
				}
			}

			$ret = $list;
		} else {
			$ret = null;
		}

		return $ret;
	}

	function getSslCertNameList($targetip = null)
	{
		$ipssllist = $this->main->__var_ipssllist;
		$domipssllist = $this->getDomainSslIpList();

		$ret = null;

		if ($ipssllist) {
			foreach ((array)$ipssllist as $ipssl) {
				if ($targetip) {
					if ($targetip === 'all') {
						$ret[$ipssl['ipaddr']] = sslcert::getSslCertnameFromIP($ipssl['nname']);
					} elseif ($targetip === '*') {
						$ipnonssllist = $this->getNonSslIpList();

						// MR -- use first ip of non exclusive ip
						if ($ipnonssllist[0] === $ipssl['ipaddr']) {
							$ret['*'] = sslcert::getSslCertnameFromIP($ipssl['nname']);
						}
					} elseif ($targetip === 'free') {
						$ipnonssllist = $this->getNonSslIpList();

						foreach ($ipnonssllist as &$ipnossl) {
							$ret[$ipnossl] = sslcert::getSslCertnameFromIP($ipssl['nname']);
						}
					} else {
						if ($targetip === $ipssl['ipaddr']) {
							$ret[$targetip] = sslcert::getSslCertnameFromIP($ipssl['nname']);
						}
					}
				} else {
					if ($domipssllist) {
						foreach ($domipssllist as &$ip) {
							if ($ip === $ipssl['ipaddr']) {
								$ret[$ip] = sslcert::getSslCertnameFromIP($ipssl['nname']);
							}
						}
					}
				}
			}
		}

		return $ret;
	}

	function getNonSslIpList()
	{
		$ipssllist = $this->main->__var_ipssllist;

		$domipssllist = $this->main->__var_domainipaddress;

		$ret = null;

		foreach ($ipssllist as $ipssl) {
			// MR -- it's bug when only declate 'as' without '=>'
			foreach ($domipssllist as $ip => $dom) {
				if ($ipssl['ipaddr'] !== $ip) {
					$ret[] = $ipssl['ipaddr'];
				}
			}
		}

		// MR -- usually happen when declare exclusive ip on 1 ip system
		if (!$ret) {
			foreach ($ipssllist as $ipssl) {
				$ret[] = $ipssl['ipaddr'];
				break;
			}
		}

		return $ret;
	}

	function getDirprotect()
	{
		$dirprotect = (isset($this->main->__var_dirprotect)) ?
				$this->main->__var_dirprotect : null;

		if ($dirprotect) {
			$input = array();

			foreach ((array)$dirprotect as $prot) {
				if (!$prot->isOn('status') || $prot->isDeleted()) {
					continue;
				}

				$input[] = array('authname' => $prot->authname,
						'path' => $prot->path, 'file' => $prot->getFileName());
			}

			return $input;
		} else {
			return null;
		}
	}

	function getIndexFileOrder()
	{
		if ($this->main->indexfile_list) {
			$list = $this->main->indexfile_list;
		} else {
		//	$list = $this->main->__var_index_list;
			$list = self::getIndexFileOrderDefault();
		}

		if ($list) {
			$string = $list;
		} else {
			$string = null;
		}

		return $string;
	}

	static function getIndexFileOrderDefault()
	{
    		return array('index.php', 'index.html', 'index.shtml', 'index.htm', 
			'default.htm', 'Default.aspx', 'Default.asp', 'index.pl');

	}
	function isWildcards()
	{
		$serveralias = (isset($this->main->server_alias_a)) ?
				$this->main->server_alias_a : null;

		foreach ($serveralias as $val) {
			if ($val->nname === '*') {
				return true;
			}
		}

		return false;
	}

	function getServerAliases()
	{
		$serveralias = (isset($this->main->server_alias_a)) ?
				$this->main->server_alias_a : null;

		if ($serveralias) {
			$list = array();

			foreach ($serveralias as $val) {
				if ($val->nname !== '*') {
					$list[] = $val->nname . '.' . $this->getDomainname();
				}
			}
		} else {
			$list = null;
		}

		return $list;
	}

	function getParkDomains()
	{
		$addonlist = (isset($this->main->__var_addonlist)) ?
				$this->main->__var_addonlist : null;

		if ($addonlist) {
			$list = array();

			foreach ((array)$addonlist as $val) {
				if ($val->ttype === 'redirect') {
					continue;
				}

				$list[] = array('parkdomain' => $val->nname, 'mailflag' => $val->mail_flag);
			}
		} else {
			$list = null;
		}

		return $list;
	}

	function getRedirectDomains()
	{
		$addonlist = (isset($this->main->__var_addonlist)) ? $this->main->__var_addonlist : null;

		if ($addonlist) {
			$list = array();

			foreach ((array)$addonlist as $val) {
				if ($val->ttype !== 'redirect') {
					continue;
				}

				$list[] = array('redirdomain' => $val->nname, 'redirpath' => $val->destinationdir,
						'mailflag' => $val->mail_flag);
			}
		} else {
			$list = null;
		}

		return $list;
	}

	function getStats()
	{
		$prog = ($this->main->__var_statsprog) ? $this->main->__var_statsprog : 'awstats';

		$prot = ($this->main->stats_password) ? $this->main->stats_password : null;

		web::createstatsConf($this->getDomainname(), $this->main->stats_username, $this->main->stats_password);

		return array('app' => $prog, 'protect' => $prot);
	}

	function getWwwRedirect()
	{
		if ($this->main->isOn('force_www_redirect')) {
			$ret = true;
		} else {
			$ret = false;
		}

		return $ret;
	}

	function getApacheExtraText()
	{
		if ($this->main->text_extra_tag) {
			$ret = $this->main->text_extra_tag;
		} else {
			$ret = null;
		}

		return $ret;
	}

	function getLighttpdExtraText()
	{
		$ret = null;

		if ($this->main->text_lighty_rewrite) {
			$ret .= $this->main->text_lighty_rewrite;
		}

	/*
		if ($this->main->customerror_b->url_404) {
			$ret .= $this->main->customerror_b->url_404;
		}
	*/
		return $ret;
	}

	function getNginxExtraText()
	{
		return null;
	}

	function getRedirectionLocal()
	{
		$list = array();

		foreach((array) $this->main->redirect_a as $red) {
			$rednname = remove_extra_slash("/{$red->nname}");

			if ($red->ttype === 'local') {
				$list[] = array($rednname, $red->redirect);
			}
		}

		return $list;
	}

	function getRedirectionRemote()
	{
		$list = array();

		foreach((array) $this->main->redirect_a as $red) {
			$rednname = remove_extra_slash("/{$red->nname}");

			if ($red->ttype !== 'local') {
				$list[] = array($rednname, $red->redirect, $red->httporssl);
			}
		}

		return $list;
	}

	function getDisabled()
	{
		if ($this->main->isOn('status')) {
			$ret = false;
		} else {
			$ret = true;
		}

		return $ret;
	}

	static function setLighttpdPerlSuexec($input)
	{
		$domainname = $input['domainname'];

		$tplsource = getLinkCustomfile("/home/lighttpd/tpl", "perlsuexec.sh.tpl");

		$tpltarget = "/home/httpd/{$domainname}/perlsuexec.sh";

		$tpl = file_get_contents($tplsource);

		$tplparse = getParseInlinePhp($tpl, $input);

		if ($tplparse) {
			file_put_contents($tpltarget, $tplparse);

			lxfile_unix_chmod($tpltarget, '755');
		}
	}

	static function setNginxCgibinPhp()
	{
		lxfile_cp(getLinkCustomfile("/home/nginx/tpl", "cgi-bin.php"), "/home/httpd/cgi-bin.php");
	}

	static function setHttpdFcgid($input)
	{
		$ver = getPhpVersion();

		$tplsource = getLinkCustomfile("/home/apache/tpl", "php5.fcgi.tpl");

		$input['phpinipath'] = "/home/httpd/{$input['domainname']}";

		$input['phpcginame'] = (version_compare($ver, "5.3.0", "<")) ? 'php-cgi_pure' : 'php-cgi';

		$tpltarget = "/home/httpd/{$input['domainname']}/php5.fcgi";

		$tpl = file_get_contents($tplsource);

		$tplparse = getParseInlinePhp($tpl, $input);

		if ($tplparse) {
			file_put_contents($tpltarget, $tplparse);

			lxfile_generic_chmod($tpltarget, "755");
		}

	//	if (!file_exists("/home/httpd/php5.fcgi")) {
			$input['phpinipath'] = "/etc";

			$tpltarget = "/home/httpd/php5.fcgi";

			$tpl = file_get_contents($tplsource);

			$tplparse = getParseInlinePhp($tpl, $input);

			file_put_contents($tpltarget, $tplparse);

			lxfile_generic_chmod($tpltarget, "755");
	//	}
	}

// MR -- (3) target to .httaccess or php.ini or log

	function setLogfile()
	{
		global $sgbl;

		$domainname = $this->getDomainname();

		$web_home = $sgbl->__path_httpd_root;
		$log_path = "{$web_home}/{$domainname}/stats";
		$cust_log = "{$log_path}/{$domainname}-custom_log";
		$err_log = "{$log_path}/{$domainname}-error_log";

		// MR -- this function not used but for temporary using for fix lighttpd issue
		// where lighttpd not running if ownership not apache

		lxfile_unix_chown_rec("{$log_path}", "apache:apache");

		// MR -- back to original!
/*
		if (file_exists($cust_log)) {
			lxfile_cp($cust_log, "{$log_path}/custom.log");
		}

		if (file_exists($err_log)) {
			lxfile_cp($err_log, "{$log_path}/error.log");
		}
*/
	}

	function setPhpIni()
	{
		$domainname = $this->getDomainname();
		$username = $this->main->username;

		lxfile_unix_chown("/home/httpd/{$domainname}", "{$username}:apache");
		lxfile_unix_chmod("/home/httpd/{$domainname}", "0775");

		if (!lxfile_exists("/home/httpd/{$domainname}/php.ini")) {
			// MR -- issue #650 - lxuser_cp does not work and change to lxfile_cp;
			// lighttpd use lxfile_cp
			lxfile_cp("/etc/php.ini", "/home/httpd/{$domainname}/php.ini");
		}
	}

	function createHotlinkHtaccess()
	{
		$string = $this->hotlink_protection();
		$stlist[] = "### Kloxo Hotlink Protection";
		$endlist[] = "### End Kloxo Hotlink Protection";
		$startstring = $stlist[0];
		$endstring = $endlist[0];
		$htfile = "{$this->main->getFullDocRoot()}/.htaccess";

		file_put_between_comments($this->getUser(), $stlist, $endlist,
				$startstring, $endstring, $htfile, $string);

		//	$this->norestart = 'on';
	}

	function hotlink_protection()
	{
		if (!$this->main->isOn('hotlink_flag')) {
			return null;
		}

		$allowed_domain_string = $this->main->text_hotlink_allowed;
		$allowed_domain_string = trim($allowed_domain_string);
		$allowed_domain_string = str_replace("\r", "", $allowed_domain_string);
		$allowed_domain_list = explode("\n", $allowed_domain_string);

		$string = null;
		$string .= "\tRewriteEngine on\n";
		$string .= "\tRewriteCond %{HTTP_REFERER} !^$\n";

		$ht = trim($this->main->hotlink_redirect, "/");
		$ht = "/$ht";

		foreach ($allowed_domain_list as $al) {
			$al = trim($al);

			if (!$al) {
				continue;
			}
			$string .= "\tRewriteCond %{HTTP_REFERER} !^http://.*{$al}.*$ [NC]\n";
			$string .= "\tRewriteCond %{HTTP_REFERER} !^https://.*{$al}.*$ [NC]\n";
		}

		$l = $this->getDomainname();

		$string .= "\tRewriteCond %{HTTP_REFERER} !^http://.*{$l}.*$ [NC]\n";
		$string .= "\tRewriteCond %{HTTP_REFERER} !^https://.*{$l}.*$ [NC]\n";
		$string .= "\tRewriteRule .*[JrRjP][PpdDAa][GfFgrR]$|.*[Gg][Ii][Ff]$ {$ht} [L]\n";

		return $string;
	}

// MR -- (4) call to 'related to create conf file' and others

	function clearDomainIpAddress()
	{
		$iplist = os_get_allips();

		foreach ($this->main->__var_domainipaddress as $ip => $dom) {
			if (!array_search_bool($ip, $iplist)) {
				unset($this->main->__var_domainipaddress[$ip]);
			}
		}
	}

	function delDomain()
	{
		$domainname = $this->getDomainname();

		// Very important. If the nname is null,
		// then the 'rm - rf' command will delete all the domains.
		// So please be careful here. Must find a better way to delete stuff.
		if (!$domainname) {	return null;	}

		$list = getWebDriverList();

		foreach ($list as &$l) {
			$p = "/home/{$l}/conf";

			lxfile_rm("{$p}/domains/{$domainname}.conf");

			// MR -- for large amount domains, call function make slow process
			// so, alternative use file detect
			if (!file_exists("{$p}/defaults/init.conf")) {
				$this->createSSlConf();
				$this->updateMainConfFile();
			}
		}

		$this->main->deleteDir();

		// MR -- relate to fixed ip/~client, but better on add/delete client process
		// meanwhile enough in here

		$this->createCpConfig();
	}

	function addDomain()
	{
		$this->main->createDir();

		$this->createConfFile();

		$this->main->createPhpInfo();

		$this->updateMainConfFile();
		$this->createSSlConf();

		$list = getWebDriverList();

		foreach ($list as &$l) {
			$p = "/home/{$l}/conf";

			// MR -- for large amount domains, call function make slow process
			// so, alternative use file detect
			if (!file_exists("{$p}/defaults/init.conf")) {
				$this->createSSlConf();
				$this->updateMainConfFile();
			}
		}


		// MR -- relate to fixed ip/~client, but better on add/delete client process
		// meanwhile enough in here

		$this->createCpConfig();
	}

	function dbactionAdd()
	{
		$this->addDomain();

		$this->main->doStatsPageProtection();
	}

	function dbactionDelete()
	{
		$this->delDomain();
	}

	function dosyncToSystemPost()
	{
		if (!$this->isOn('norestart')) {
			createRestartFile("apache");
		}
	}

	function fullUpdate()
	{
		global $sgbl;

		$domname = $this->getDomainname();
		$uname = $this->getUser();

		$hroot = $sgbl->__path_httpd_root;
		$droot = $this->main->getFullDocRoot();

		lxfile_mkdir("{$hroot}/{$domname}/webstats");

		$this->main->createPhpInfo();
		web::createstatsConf($domname, $this->main->stats_username, $this->main->stats_password);

		//	$this->createSSlConf();

		$this->createConfFile();

		lxfile_unix_chown_rec("{$droot}/", "{$uname}:{$uname}");
		lxfile_unix_chmod("{$droot}/", "0755");
		lxfile_unix_chmod("{$droot}", "0755");
		lxfile_unix_chown("{$hroot}/{$domname}", "{$uname}:apache");
	}

	function dbactionUpdate($subaction)
	{
		$domname = $this->getDomainname();
		$uname = $this->getUser();

		$ret = null;

		if (!$uname) {
			log_log("critical", "Lack customername for web: {$domname}");

			return $ret;
		} else {
			switch ($subaction) {
				case "full_update":
					$this->fullUpdate();
					$this->main->doStatsPageProtection();
					break;

				case "changeowner":
					$this->main->webChangeOwner();
					$this->createConfFile();
					break;

				case "create_config":
				case "addondomain":
					$this->createConfFile();
					break;

				case "add_delete_dirprotect":
				case "extra_tag" :
				case "add_dirprotect" :
				case "custom_error":
				case "dirindex":
				case "docroot":
				case "ipaddress":
				case "blockip";
				case "add_redirect_a":
				case "delete_redirect_a":
				case "delete_redirect_a":
				case "add_webindexdir_a":
				case "delete_webindexdir_a":
				case "add_server_alias_a" :
				case "delete_server_alias_a" :
				case "configure_misc":
					$this->createConfFile();
					break;

				case "redirect_domain" :
					$this->createConfFile();
					break;

				case "fixipdomain":
					$this->createConfFile();
					$this->updateMainConfFile();
					$this->createSSlConf();
					break;

				case "enable_php_manage_flag":
					$this->createConfFile();
					$this->updateMainConfFile();
					break;

				case "toggle_status" :
					$this->createConfFile();
					break;

				case "hotlink_protection":
					$this->createHotlinkHtaccess();
					break;

				case "enable_php_flag":
				case "enable_cgi_flag":
				case "enable_inc_flag":
				case "enable_ssl_flag" :
					$this->createConfFile();
					break;

				case "stats_protect":
					$this->main->doStatsPageProtection();
					$this->createConfFile();
					break;

				case "default_domain":
					$this->main->setupDefaultDomain();
					break;

				case "graph_webtraffic":
					return rrd_graph_single("webtraffic (bytes)", $this->getDomainname(),
							$this->main->rrdtime);
					break;

				case "run_stats":
					$this->main->runStats();
					break;

				case "static_config_update":
					$this->updateMainConfFile();
					$this->createSSlConf();
					$this->createCpConfig();
				//	self::createWebDefaultConfig();
					$this->createWebDefaultConfig();
					$this->createPhpFpmConfig();
					break;

			//	case "fix_phpfpm":
			//		$this->createPhpFpmConfig();
			//		break;

				case "fix_chownchmod_all":
					setFixChownChmod('all');
					break;

				case "fix_chownchmod_own":
					setFixChownChmod('chown');
					break;

				case "fix_chownchmod_mod":
					setFixChownChmod('chmod');
					break;

				case "fix_chownchmod_all_nolog":
					setFixChownChmod('all', $nolog = true);
					break;

				case "fix_chownchmod_own_nolog":
					setFixChownChmod('chown', $nolog = true);
					break;

				case "fix_chownchmod_mod_nolog":
					setFixChownChmod('chmod', $nolog = true);
					break;
			}
		}
	}

	function do_backup()
	{
		return $this->main->do_backup();
	}

	function do_restore($docd)
	{
		global $sgbl;

		$uname = $this->getUser();

		$fullpath = "{$sgbl->__path_customer_root}/{$uname}/";

		$this->main->do_restore($docd);

		lxfile_unix_chown_rec($fullpath, $uname);
	}
}
