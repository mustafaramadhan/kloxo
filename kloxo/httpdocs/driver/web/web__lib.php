<?php

class web__ extends lxDriverClass
{
	function __construct()
	{
	}

	static function uninstallMeTrue($drivertype = null)
	{
		if ($drivertype === 'none') { return; }

		$list = getWebDriverList($drivertype);

		$l = $list[0];

		$a = ($l === 'apache') ? 'httpd' : $l;

		lxshell_return("service", $a, "stop");

	/*	// MR -- no neeed 'yum remove' because all installed

		$blist = array();

		// MR -- for fixed an issue version conflict!
		// no action for hiawatha because used by Kloxo too
		if ($a === 'httpd') {
		//	if (file_exists("../etc/flag/use_apache24.flg")) {
				$blist[] = "httpd24*";
				$blist[] = "mod24*";
			//	$blist[] = "mod-*";
		//	} else {
				$blist[] = "httpd-*";
				$blist[] = "mod_*";
			//	$blist[] = "mod-*";
		//	}
		} elseif ($a === 'lighttpd') {
			$blist[] = "{$a}";
			$blist[] = "{$a}-fastcgi";
		} elseif ($a === 'nginx') {
			$blist[] = "{$a}";
		} elseif ($a === 'hiawatha') {
			// no action because also using by panel
		}

		$p = implode(" ", $blist);

		if ($a !== 'hiawatha') {
			exec("yum remove {$p} -y");
		}
	*/
		exec("chkconfig {$a} off");

		if (file_exists("/etc/init.d/{$a}")) {
			lunlink("/etc/init.d/{$a}");
		}
	}

	static function installMeTrue($drivertype = null)
	{
		if ($drivertype === 'none') { return; }

		$list = getWebDriverList($drivertype);

		setAllWebserverInstall();

		if (!isWebProxyOrApache($drivertype)) {
			self::uninstallMeTrue('apache');
		}

		foreach ($list as &$l) {
			$a = ($l === 'apache') ? 'httpd' : $l;
		/*
			$blist = array();

			if ($a === 'httpd') {
				if (file_exists("../etc/flag/use_apache24.flg")) {
					$blist[] = "{$a}24u";
					$blist[] = "{$a}24u-tools";
					$blist[] = "{$a}24u-filesystem";
					$blist[] = "mod24u_ssl";
					$blist[] = "mod24u_session";
					$blist[] = "mod24u_suphp";
					$blist[] = "mod24u_ruid2";
					$blist[] = "mod24u_fcgid";
					$blist[] = "mod24u_fastcgi";
				} else {
					$blist[] = "{$a}";
					$blist[] = "{$a}-tools";
					$blist[] = "mod_ssl";
					$blist[] = "mod_rpaf";
					$blist[] = "mod_ruid2";
					$blist[] = "mod_suphp";
					$blist[] = "mod_fastcgi";
					$blist[] = "mod_fcgid";
					$blist[] = "mod_define";
				//	$blist[] = "mod_perl";
				//	$blist[] = "perl-Taint-Runtime";
				}

			} elseif ($a === 'lighttpd') {
				$blist[] = $a;
				$blist[] = "{$a}-fastcgi";
			} elseif ($a === 'nginx') {
				$blist[] = $a;
				$blist[] = "GeoIP";
			} elseif ($a === 'hiawatha') {
				// no action
			}

			$p = implode(" ", $blist);

			exec("yum install {$p} -y");
		*/
			self::setWebserverInstall($a);
			self::setBaseWebConfig($a);

			exec("chkconfig {$a} on");

			setCopyWebConfFiles($l);
		}

		self::setInstallPhpfpm();

		exec("sh /script/fixweb --target=defaults");
	}

	static function setBaseWebConfig($drivertype = null)
	{
		setCopyWebConfFiles($drivertype);
	}

	static function setWebserverInstall($webserver)
	{
		// MR -- overwrite init

		$altname = ($webserver === 'httpd') ? 'apache' : $webserver;

		if (($altname === 'apache') && (file_exists("../etc/flag/use_apache24.flg"))) {
			$src = 'httpd24';
		} else {
			$src = $webserver;
		}

		if ($webserver === 'httpd') {
			if (file_exists("../etc/flag/use_pagespeed.flg")) {
				exec("rpm -qa|grep pagespeed", $out);

				if (count($out) < 1) {
					exec("yum -y install mod-pagespeed-stable");
				}

				lxfile_cp(getLinkCustomfile("/opt/configs/apache/etc/conf.d", "pagespeed.conf"),
					"/etc/httpd/conf.d/pagespeed.conf");
			} else {
					lxfile_rm("/etc/httpd/conf.d/pagespeed.conf");
			}
		}

		lxfile_cp(getLinkCustomfile("/opt/configs/{$altname}/etc/init.d", "{$src}.init"),
			"/etc/rc.d/init.d/{$webserver}");

		exec("chmod 755 /etc/rc.d/init.d/{$webserver}");
	}

	static function setUnnstallPhpfpm()
	{
		// MR -- change to it for speedup process
		exec("yum remove php*-fpm -y");
	}

	static function setInstallPhpfpm()
	{
		exec("'cp' -rf ../file/php-fpm /opt/configs");

		$phpbranch = getRpmBranchInstalled('php');

		$ret = isRpmInstalled("{$phpbranch}-fpm");

		if (!$ret) {
			lxshell_return("yum", "-y", "install", "{$phpbranch}-fpm");
		}

		if (version_compare(getPhpVersion(), "5.3.2", ">")) {
			lxfile_cp(getLinkCustomfile("/opt/configs/php-fpm/etc", "php53-fpm.conf"), "/etc/php-fpm.conf");

			if (file_exists("/etc/php-fpm.d")) {
				if (!file_exists("/etc/php-fpm.d/default.conf")) {
					lxfile_cp("/opt/configs/php-fpm/etc/php-fpm.d/default.conf", "/etc/php-fpm.d/default.conf");
				}

				if (!file_exists("/etc/php-fpm.d/www.conf")) {
					lxfile_cp("/opt/configs/php-fpm/etc/php-fpm.d/www.conf", "/etc/php-fpm.d/www.conf");
				}
			}
		} else {
			lxfile_cp(getLinkCustomfile("/opt/configs/php-fpm/etc", "php-fpm.conf"), "/etc/php-fpm.conf");

			// MR -- php 5.2 from centalt not create this pid but php-fpm installed!
			if (!file_exists("/var/run/php-fpm/php-fpm.pid")) {
				lxfile_mkdir("/var/run/php-fpm");
				exec("echo '2265' > /var/run/php-fpm/php-fpm.pid");
			}
		}

	//	lxshell_return("chkconfig", "php-fpm", "on");
		exec("chkconfig php-fpm on");
	}

	function createConfFile()
	{
		global $gbl;

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
		$input['dirindex'] = $this->getDirIndex();
		$input['certnamelist'] = ($this->getSslCertNameList()) ?
		$this->getSslCertNameList() : $this->getSslCertNameList('*');

		$input['stats'] = $this->getStats();
		$input['wwwredirect'] = $this->getWwwRedirect();
		$input['httpsredirect'] = $this->getHttpsRedirect();
		$input['domainredirect'] = $this->getRedirectDomains();

		$input['webselected'] = $this->getWebSelected();
		$input['phpselected'] = $this->getPhpSelected();
		$input['timeout'] = $this->getWebTimeout();

		$input['apacheextratext'] = $this->getApacheExtraText();
		$input['lighttpdextratext'] = $this->getLighttpdExtraText();
		$input['nginxextratext'] = $this->getNginxExtraText();

		$input['disabled'] = $this->getDisabled();
		$input['extrabasedir'] = $this->AddExtraBaseDir();
		$input['blockips'] = $this->getBlockIP();

		$input['webmailremote'] = $this->getWebmailInfo('remote');
		$input['webmailapp'] = $this->getWebmailInfo('app');
	//	$input['webmailappdefault'] = self::getWebmailAppDefault();

		$input['rootpath'] = $this->main->getFullDocRoot();

		$input['phpcgitype'] = $this->getPhpCgiType();
		$input['fastcgichildren'] = $this->getFastcgiChildren();
		$input['fastcgimaxprocs'] = $this->getFastcgiMaxProcs();

		$input['setdomains'] = true;

		$input['redirectionlocal'] = $this->getRedirectionLocal();
		$input['redirectionremote'] = $this->getRedirectionRemote();

		$input['phptype'] = self::getPhptype();

	//	$input['webcache'] = rl_exec_get('localhost', $this->main->__syncserver, 'slave_get_driver', array('webcache'));
		$input['webcache'] = $gbl->getSyncClass('localhost', $this->main->__syncserver, 'webcache');

		$input['enablecgi'] = $this->getEnableCGI();
		$input['enablephp'] = $this->getEnablePhp();
		$input['enablessl'] = $this->getEnableSsl();
		$input['enablestats'] = $this->getEnableStats();

		$input['kloxoportnonssl'] = get_kloxo_port('nonssl');
		$input['kloxoportssl'] = get_kloxo_port('ssl');

		$input['driverlist'] = getAllWebDriverList();
		$input['driver'] = getWebDriverList();

		self::setCreateConfFile($input);

		$this->setLogfile();
	}

// MR -- (1) related to create conf file

	function updateMainConfFile()
	{
		global $gbl;

		$input = array();

		$input['setdefaults'] = 'init';
		$input['indexorder'] = self::getIndexFileOrderDefault();
		$input['certnamelist'] = $this->getSslCertNameList('*');
		$input['certnamelistfree'] = $this->getSslCertNameList('free');
		$input['userlist'] = $this->getUserList();

		$input['phptype'] = self::getPhptype();

	//	$input['webcache'] = rl_exec_get('localhost', $this->main->__syncserver, 'slave_get_driver', array('webcache'));
		$input['webcache'] = $gbl->getSyncClass('localhost', $this->main->__syncserver, 'webcache');

		$input['stats'] = $this->getStats();

		$input['driverlist'] = getAllWebDriverList();
		$input['driver'] = getWebDriverList();

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

	static function addPhpFpmConfig($user)
	{
		// MR -- that mean 'ini' type config
		$cfgmain = getLinkCustomfile("/opt/configs/php-fpm/etc", "php53-fpm.conf");
		lxfile_cp($cfgmain, "/etc/php-fpm.conf");

		$tplsource = getLinkCustomfile("/opt/configs/php-fpm/tpl", "php53-fpm-pool.conf.tpl");
		$tpl = file_get_contents($tplsource);

		$input['user'] = $user;
		$tpltarget = "/etc/php-fpm.d/{$user}.conf";
		$tplparse = getParseInlinePhp($tpl, $input);

		if (!file_exists($tpltarget)) {
			file_put_contents($tpltarget, $tplparse);
		}
	}

	static function delPhpFpmConfig($user)
	{
		$tpltarget = "/etc/php-fpm.d/{$user}.conf";

		if (file_exists($tpltarget)) {
			exec("'rm' -rf {$tpltarget}");
		}
	}

	function createPhpFpmConfig($forwhat = null, $foruser = null)
	{
		$input = array();

		$input['userlist'] = $this->getUserList();

		if (version_compare(getPhpVersion(), "5.3.3", "<")) {
			// MR -- that mean 'xml' type config
			$tplsource = getLinkCustomfile("/opt/configs/php-fpm/tpl", "php-fpm.conf.tpl");
			$tpltarget = "/etc/php-fpm.conf";
			$tpl = file_get_contents($tplsource);
			$tplparse = getParseInlinePhp($tpl, $input);
			file_put_contents($tpltarget, $tplparse);
		} else {
			if ($forwhat === 'add') {
				self::addPhpFpmConfig($foruser);

				return;
			}

			if ($forwhat === 'delete') {
				self::delPhpFpmConfig($foruser);

				return;
			}

			// MR -- make simple, delete all .conf files first
			exec("'rm' -rf /etc/php-fpm.d/*.conf");
			// MR -- that mean 'ini' type config
			$cfgmain = getLinkCustomfile("/opt/configs/php-fpm/etc", "php53-fpm.conf");
			lxfile_cp($cfgmain, "/etc/php-fpm.conf");

			$tplsource = getLinkCustomfile("/opt/configs/php-fpm/tpl", "php53-fpm-pool.conf.tpl");
			$tpl = file_get_contents($tplsource);

			foreach ($input['userlist'] as &$user) {
				// MR - for 'real' users
				$input['user'] = $user;
				$tpltarget = "/etc/php-fpm.d/{$user}.conf";
				$tplparse = getParseInlinePhp($tpl, $input);

				file_put_contents($tpltarget, $tplparse);
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

	//	createRestartFile('restart-php-fpm');
	}


// MR -- (2) call by 'related to create conf file' (1)

	static function getPhptype()
	{
		global $login;

		$data = db_get_value("serverweb", "pserver-" . $login->syncserver, "php_type");

		return $data;
	}

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
		$clist = rl_exec_get('localhost', 'localhost', 'getAllClientList', array($this->main->__syncserver));

		$users = array();

		foreach ($clist as &$n) {
			$userinfo = posix_getpwnam($n);
			$fpmport = (50000 + $userinfo['uid']);

			if ($fpmport === 50000) { continue; }

			$users[] = $n;
		}

		return $users;
	}

	function getWebmailInfo($for)
	{
		$domainname = $this->getDomainname();

		// MR -- look like not work calling $this->main->__var_mmaillist
		// so, taken from database directly

		$string = "syncserver = '{$this->main->__syncserver}'";
		$mmaildb = new Sqlite(null, 'mmail');
		$mlist = $mmaildb->getRowsWhere($string, array('nname', 'parent_clname', 'webmailprog', 'webmail_url', 'remotelocalflag'));
	//	$mlist = $this->main->__var_mmaillist;

		// --- for the first time domain create
		$list = array('nname' => $domainname, 'parent_clname' => 'domain-'.$domainname,
			'webmailprog' => '', 'webmail_url' => '', 'remotelocalflag' => 'local');

		if ($mlist) {
			foreach ($mlist as $m) {
				if ($m['nname'] === $domainname) {
					$list = $m;
					break;
				}
			}
		}

		$r = null;

		if ($for === 'remote') {
			if ($list['remotelocalflag'] === 'remote') {
				$r = $list['webmail_url'];
			}

		} elseif ($for === 'app') {
			if ($list['remotelocalflag'] !== 'remote') {
				if (($list['webmailprog'] === '--system-default--') || ($list['webmailprog'] === '')) {
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
			$conffile = "{$input['setdefaults']}.conf";
		} else {
			if (array_key_exists('setdomains', $input)) {
				$conftype = 'domains';
				$conftpl = 'domains';
				$conffile = $input['domainname'] . '.conf';
				// MR -- change process to create client (user-level php.ini)
			//	self::setHttpdFcgid($input);
			}
		}

		$input['reverseproxy'] = isWebProxy();

	//	$list = getWebDriverList();
		$list = getAllWebDriverList();

		$input['webdriverlist'] = $list;

		foreach ($list as &$l) {
			$tplsource = getLinkCustomfile("/opt/configs/{$l}/tpl", "{$conftpl}.conf.tpl");

			// MR -- to make sure no 'old' config -
		//	lxshell_return("'rm'", "-rf", "/opt/configs/{$l}/conf/{$conftpl}/*.conf");

			if (($l === 'hiawatha') && ($conftype === 'domains')) {
				$types = array('domains' => false, 'proxies' => true);

				foreach ($types as $k => $v) {
					$tpltarget = "/opt/configs/{$l}/conf/{$k}";
					$input['reverseproxy'] = $v;

					$tpl = file_get_contents($tplsource);

					$tplparse = getParseInlinePhp($tpl, $input);

					if ($tplparse) {
						file_put_contents("{$tpltarget}/{$conffile}", $tplparse);
					}
				}
			} else {
				$tpltarget = "/opt/configs/{$l}/conf/{$conftype}";

				$tpl = file_get_contents($tplsource);

				$tplparse = getParseInlinePhp($tpl, $input);

				if ($tplparse) {
					file_put_contents("{$tpltarget}/{$conffile}", $tplparse);
				}
			}

			if ($conftype === 'domains') {
				if ($l === 'lighttpd') {
				//	self::setLighttpdPerlSuexec($input);
				}

				if ($l === 'nginx') {
				//	self::setNginxCgibinPhp();
				}
			}

			if ($conftype === 'defaults') {
				if ($l === 'hiawatha') {
					$tplsource = getLinkCustomfile("/opt/configs/{$l}/tpl", "cgi-wrapper.conf.tpl");
					$tpltarget = "/etc/hiawatha/cgi-wrapper.conf";

					$tpl = file_get_contents($tplsource);

					$tplparse = getParseInlinePhp($tpl, $input);

					if ($tplparse) {
						file_put_contents("{$tpltarget}", $tplparse);
					}
				}
			}
		}
	}
	function setRemoveAllDomainConfigs()
	{
		$list = getAllWebDriverList();

		foreach ($list as &$l) {
			// MR -- lxshell_return not work for rm; then use exec
		//	lxshell_return("rm", "-rf", "/opt/configs/{$l}/conf/domains/*.conf");
		//	lxshell_return("rm", "-rf", "/opt/configs/{$l}/conf/proxies/*.conf");
			exec("'rm' -rf /opt/configs/{$l}/conf/domains/*.conf");
			exec("'rm' -rf /opt/configs/{$l}/conf/proxies/*.conf");
		}
	}
	function AddExtraBaseDir()
	{
		$extrabasedir = ($this->main->__var_extrabasedir) ? $this->main->__var_extrabasedir : '';

		return trim($extrabasedir);
	}

	function getBlockIP()
	{
		$tblockip = $this->main->text_blockip;

		$t = trimSpaces($tblockip);
		$t = trim($t);

		if ($t) {
			$t = explode(" ", $t);
		} else {
			$t = null;
		}

		return $t;
	}

	function getPhpCgiType()
	{
		$ret = ($this->main->priv->isOn('phpfcgi_flag')) ? 'fastcgi' : 'suexec';

		return $ret;
	}

	function getFastcgiMaxProcs()
	{
		$n = ($this->main->priv->phpfcgiprocess_num) ?	$this->main->priv->phpfcgiprocess_num : "0";

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

	function getEnableCGI()
	{
		$ret = ($this->main->priv->isOn('cgi_flag')) ? true : false;

		return $ret;
	}

	function getEnablePhp()
	{
		$ret = ($this->main->priv->isOn('php_flag')) ? true : false;

		return $ret;
	}

	function getEnableSsl()
	{
		$ret = ($this->main->priv->isOn('ssl_flag')) ? true : false;

		return $ret;
	}

	function getEnableStats()
	{
		$ret = ($this->main->priv->isOn('awstats_flag')) ? true : false;

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

	function getDirIndex()
	{
		if (isset($this->main->webmisc_b->dirindex)) {
			$s = $this->main->webmisc_b;

			if ($s->dirindex === 'on') {
				$dirindex = true;
			} else {
				$dirindex = false;
			}
		} else {
			$dirindex = false;
		}

		return $dirindex;
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
		return web::getIndexOrderDefault();
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
		$serveralias = (isset($this->main->server_alias_a)) ?	$this->main->server_alias_a : null;

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

		// MR -- by default stats dir always protected
		if (!$this->main->stats_password) {
			$this->main->stats_password = randomString(8);
		}

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

	function getHttpsRedirect()
	{
		if ($this->main->isOn('force_https_redirect')) {
			$ret = true;
		} else {
			$ret = false;
		}

		return $ret;
	}

	function getWebSelected()
	{
		if (!isset($this->main->web_selected) || (!$this->main->web_selected)) {
			$ret = 'back-end';
		} else {
			$ret = $this->main->web_selected;
		}

		return $ret;
	}

	function getPhpSelected()
	{
		if ((!isset($this->main->php_selected)) || (!$this->main->php_selected) || (strtolower($this->main->php_selected) === '--default--')) {
			$ret = 'php';
		} else {
			$ret = $this->main->php_selected;
		}
	
		// MR -- convert
		if (strpos(strtolower($ret), 'php used') !== false) {
			$ret = 'php';
		}

		return $ret;
	}

	function getWebTimeout()
	{
		if ((!isset($this->main->time_out)) || (!$this->main->time_out) || (strtolower($this->main->time_out) === '300')) {
			$ret = '300';
		} else {
			$ret = $this->main->time_out;
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

		$tplsource = getLinkCustomfile("/opt/configs/lighttpd/tpl", "perlsuexec.sh.tpl");

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
		lxfile_cp(getLinkCustomfile("/opt/configs/nginx/tpl", "cgi-bin.php"), "/home/httpd/cgi-bin.php");
	}

	static function setHttpdFcgid($input)
	{
		$tplsource = getLinkCustomfile("/opt/configs/apache/tpl", "php.fcgi.tpl");

		$input['phpinipath'] = "/home/kloxo/client/{$input['user']}";

		$input['phpcginame'] = (version_compare(getPhpVersion(), "5.3.0", "<")) ? 'php-cgi_pure' : 'php-cgi';

		$tpltarget = "/home/kloxo/client/{$input['user']}/php.fcgi";

		$tpl = file_get_contents($tplsource);

		$tplparse = getParseInlinePhp($tpl, $input);

		if ($tplparse) {
			file_put_contents($tpltarget, $tplparse);

			lxfile_generic_chmod($tpltarget, "755");
		}

		$input['pathinipath'] = "/etc";

		$tpltarget = "/home/kloxo/client/php.fcgi";

		$tpl = file_get_contents($tplsource);

		$tplparse = getParseInlinePhp($tpl, $input);

		file_put_contents($tpltarget, $tplparse);

		lxfile_generic_chmod($tpltarget, "755");
	}

// MR -- (3) target to .httaccess or php.ini or log

	function setLogfile()
	{
		global $sgbl;

		$domainname = $this->getDomainname();

		$web_home = $sgbl->__path_httpd_root;
		$log_path = "{$web_home}/{$domainname}/stats";

		if (!file_exists($log_path)) {
			lxfile_mkdir("$log_path");
		}

		lxfile_unix_chown_rec("{$log_path}", "apache:apache");
	}

	function setPhpIni()
	{
		$domainname = $this->getDomainname();
		$username = $this->getUser();

		lxfile_unix_chown("/home/httpd/{$domainname}", "{$username}:apache");
		lxfile_unix_chmod("/home/httpd/{$domainname}", "0775");

		// TODO -- done
		// MR -- change to user-level php
		if (!lxfile_exists("/home/kloxo/client/{$username}/php.ini")) {
			// MR -- issue #650 - lxuser_cp does not work and change to lxfile_cp;
			// lighttpd use lxfile_cp
			lxfile_cp("/etc/php.ini", "/home/kloxo/client/{$username}/php.ini");
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
		// then the "rm - rf" command will delete all the domains.
		// So please be careful here. Must find a better way to delete stuff.
		if (!$domainname) {	return null;	}

		$list = getAllWebDriverList();

		foreach ($list as &$l) {
			$dfile = "/opt/configs/{$l}/conf/domains/{$domainname}.conf";
			$pfile = "/opt/configs/{$l}/conf/proxy/{$domainname}.conf";

			if (file_exists($dfile)) {
				unlink($dfile);
			}

			if (file_exists($pfile)) {
				unlink($pfile);
			}
		}

		$this->main->deleteDir();
	}

	function addDomain()
	{
		$this->main->createDir();
		$this->main->createPhpInfo();

		$this->createConfFile();
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
		// MR -- to make sure after domain config process
		// also update static config
		$this->dbactionUpdate("static_config_update");

		createRestartFile("restart-web");
	}

	function fixDomainSSLPath()
	{
		$uname = $this->getUser();

		$spath="/home/{$uname}/ssl";
		$dpath="/home/kloxo/ssl";

		if (file_exists($spath)) {
			exec("mv -f {$spath} {$dpath}");
		}
	}

	function fullUpdate()
	{
		global $sgbl;

		$domname = $this->getDomainname();

		$hroot = $sgbl->__path_httpd_root;

		$this->fixDomainSSLPath();

		$this->createConfFile();

		if (!file_exists("{$hroot}/{$domname}/webstats")) {
			lxfile_mkdir("{$hroot}/{$domname}/webstats");
		}

		web::createstatsConf($domname, $this->main->stats_username, $this->main->stats_password);

		$this->main->createPhpInfo();

	/*
		$uname = $this->getUser();
		$droot = $this->main->getFullDocRoot();

		// MR -- disabled because make slower; use fix-chownchmod for this purpose
		lxfile_unix_chown_rec("{$droot}/", "{$uname}:{$uname}");
		lxfile_unix_chmod("{$droot}", "0755");
		lxfile_unix_chown("{$hroot}/{$domname}", "{$uname}:apache");
	*/
	}

	function skeletonUpdate()
	{
		$droot = $this->main->getFullDocRoot();

		$this->main->getAndUnzipSkeleton($droot);
	}

	function phpUpdate()
	{
		// MR -- no need for it except .htaccess per-domain!
		$uname = $this->getUser();

		exec("sh /script/fixphp --client={$uname}");
	}

	function htaccessUpdate()
	{
		// MR -- no need for it except .htaccess per-domain!
		$domain = $this->getDomainname();
	//	$uname = $this->getUser();

		exec("sh /script/fixphp --domain={$domain}");

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
					$this->main->doStatsPageProtection();
					$this->fullUpdate();
					break;

				case "changeowner":
					$this->main->webChangeOwner();
					$this->main->ftpChangeOwner();
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
					$this->updateMainConfFile();
					$this->createConfFile();
					break;

				case "enable_php_manage_flag":
					$this->updateMainConfFile();
					$this->createConfFile();
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
					return rrd_graph_single("webtraffic (bytes)", $this->getDomainname(), $this->main->rrdtime);
					break;

				case "run_stats":
					$this->main->runStats();
					break;

				case "static_config_update":
					$this->updateMainConfFile();
					// MR -- only need if not set 'php configure'
					if (!file_exists("/etc/php-fpm.d/{$uname}.conf")) {
						$this->createPhpFpmConfig();
					}
					break;
				case "remove_all_domain_configs":
					$this->setRemoveAllDomainConfigs();
					break;
				case "skeleton_update":
					$this->skeletonUpdate();
					break;

				case "php_update":
					$this->phpUpdate();
					break;

				case "htaccess_update":
					$this->htaccessUpdate();
					break;

				case "fix_phpfpm":
					$this->createPhpFpmConfig();
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
