<?php

class phpini_flag_b extends lxaclass
{
	static $__desc_display_error_flag = array("f", "", "display_errors");
	static $__desc_register_global_flag = array("f", "", "register_globals");
	static $__desc_enable_zend_flag = array("f", "", "enable_zend");
	static $__desc_enable_xcache_flag = array("f", "", "enable_xcache");
	static $__desc_enable_ioncube_flag = array("f", "", "enable_ioncube");
	static $__desc_enable_suhosin_flag = array("f", "", "enable_suhosin");
	static $__desc_upload_max_filesize = array("", "", "upload_file_max_size");
	static $__desc_log_errors_flag = array("f", "", "log_errors");
	static $__desc_file_uploads_flag = array("f", "", "file_uploads");
	static $__desc_upload_tmp_dir_flag = array("", "", "upload_tmp_dir");
	static $__desc_output_buffering_flag = array("f", "", "output_buffering");
	static $__desc_register_argc_argv_flag = array("f", "", "register_argc_argv");
	static $__desc_magic_quotes_gpc_flag = array("f", "", "magic_quotes_gpc");
	static $__desc_register_long_arrays_flag = array("f", "", "register_long_arrays");
	static $__desc_variables_order_flag = array("", "", "variables_order");
	static $__desc_output_compression_flag = array("f", "", "output_compression");
	static $__desc_post_max_size_flag = array("", "", "post_max_size");
	static $__desc_magic_quotes_runtime_flag = array("f", "", "magic_quotes_runtime");
	static $__desc_magic_quotes_sybase_flag = array("f", "", "magic_quotes_sybase");
	static $__desc_gpc_order_flag = array("", "", "gpc_order");
	static $__desc_extension_dir_flag = array("", "", "extension_dir");
	static $__desc_enable_dl_flag = array("f", "", "enable_dl");
	static $__desc_sendmail_from = array("", "", "sendmail_from");
	static $__desc_cgi_force_redirect_flag = array("f", "", "cgi_force_redirect");
	static $__desc_disable_functions = array("t", "", "disable_functions");
	static $__desc_max_execution_time_flag = array("", "", "max_execution_time");
	static $__desc_max_input_time_flag = array("", "", "max_input_time");
	static $__desc_memory_limit_flag = array("", "", "memory_limit");
	static $__desc_allow_url_fopen_flag = array("f", "", "allow_url_fopen");
	static $__desc_allow_url_include_flag = array("f", "", "allow_url_include");
	static $__desc_session_save_path_flag = array("", "", "session_save_path");
	static $__desc_session_autostart_flag = array("f", "", "session_autostart");
	static $__desc_safe_mode_flag = array("f", "", "safe_mode");

	static $__desc_max_input_vars_flag = array("", "", "max_input_vars");

	static $__desc_date_timezone_flag = array("s", "", "date_timezone");
	static $__desc_phpfpm_type_flag = array("s", "", "phpfpm_type");
	static $__desc_phpfpm_limit_extensions = array("", "", "phpfpm_extensions");
}

class phpini extends lxdb
{
	static $__desc = array("", "", "php_configuration");
	static $__desc_nname = array("", "", "php_configuration");
	static $__desc_enable_zend_flag = array("f", "", "enable_zend");
	static $__desc_enable_ioncube_flag = array("f", "", "enable_ioncube");
	static $__desc_register_global_flag = array("f", "", "register_globals");
	static $__desc_display_error_flag = array("f", "", "display_errors");
	static $__desc_php_manage_flag = array("", "", "manage_php_configuration");
	static $__acdesc_update_edit = array("", "", "PHP_config");
	static $__acdesc_update_extraedit = array("", "", "advanced_PHP_config");
	static $__acdesc_show = array("", "", "PHP_config");

	static function initThisObjectRule($parent, $class, $name = null)
	{
		return $parent->getClName();
	}

	function getInheritedList()
	{
		$list[] = 'output_compression_flag';

		return $list;
	}

	function getLocalList()
	{
		$server = $this->syncserver;
		$server_phpini = unserialize(base64_decode(db_get_value("phpini", "pserver-{$server}",
			"ser_phpini_flag_b")));

		$list[] = 'display_error_flag';
		$list[] = 'log_errors_flag';
		$list[] = 'output_compression_flag';
		$list[] = 'date_timezone_flag';
		$list[] = 'phpfpm_type_flag';
		$list[] = 'session_save_path_flag';
		$list[] = 'phpfpm_limit_extensions';

		return $list;
	}

	function getExtraList()
	{
		$list[] = 'sendmail_from';
		$list[] = 'enable_dl_flag';
		$list[] = 'output_buffering_flag';
		$list[] = 'allow_url_fopen_flag';
		$list[] = 'allow_url_include_flag';
		$list[] = 'register_argc_argv_flag';
		$list[] = 'disable_functions';
		$list[] = 'max_execution_time_flag';
		$list[] = 'max_input_time_flag';
		$list[] = 'memory_limit_flag';
		$list[] = 'post_max_size_flag';
		$list[] = "upload_max_filesize";

		$list[] = 'max_input_vars_flag';

		$list[] = 'file_uploads_flag';
		$list[] = 'cgi_force_redirect_flag';

		return $list;
	}

	function getAdminList()
	{
		global $login;

		$list = null;

		if (!$login->isAdmin()) {
			$list[] = 'disable_functions';
			$list[] = 'max_execution_time_flag';
			$list[] = 'max_input_time_flag';
			$list[] = 'memory_limit_flag';
			$list[] = 'post_max_size_flag';
			$list[] = "upload_max_filesize";
			$list[] = 'session_save_path_flag';
		}

		return $list;
	}

	function fixphpIniFlag()
	{
		if (!isset($this->phpini_flag_b) || !($this->phpini_flag_b instanceof phpini_flag_b)) {
		//if ($this->getParentO()->getClass() === 'web') { return; }
		//if ($this->getParentO()->getClass() === 'domain') { return; }
			$this->phpini_flag_b = new phpini_flag_b(null, null, $this->nname);
			$this->setUpInitialValues();
		}
	}

	function getParentClass($var = 'parent_clname')
	{
		$parent = $this->getParentO();

		if ($parent && is_object($parent) && method_exists($parent, 'getClass')) {
			return $parent->getClass();
		}

		if (isset($this->$var) && $this->$var) {
			list($pclass, ) = getParentNameAndClass($this->$var);

			if ($pclass) {
				return $pclass;
			}
		}

		return 'pserver';
	}

	function createExtraVariables()
	{
		global $gbl, $login;

		$this->fixphpIniFlag();
		$gen = $login->getObject('general')->generalmisc_b;
		$pclass = $this->getParentClass();
		$parent = $this->getParentO();

		//	if (!$this->getParentO()->is__table('pserver')) {
		if ($pclass !== 'pserver') {
			$ob = new phpini(null, $this->syncserver, createParentName('pserver', $this->syncserver));
			$ob->get();
			$ob->fixphpIniFlag();

			// MR -- trick for escape web-based php.ini
			//	if ($this->getParentO()->is__table('web')) {
			if ($pclass === 'web' && $parent && method_exists($parent, 'getFullDocRoot')) {
				$this->__var_docrootpath = $parent->getFullDocRoot();
			}

			$list = $this->getInheritedList();

			foreach ($list as $l) {
				$this->phpini_flag_b->$l = (isset($ob->phpini_flag_b) && isset($ob->phpini_flag_b->$l)) ? $ob->phpini_flag_b->$l : null;
			}

			// MR -- trick for escape web-based php.ini
			//	if ($this->getParentO()->is__table('web')) {
			if ($pclass === 'web' && $parent && isset($parent->username)) {
				$this->__var_web_user = $parent->username;
			//	$this->__var_customer_name = $this->getParentO()->customer_name;
			//	$this->__var_disable_openbasedir = (isset($this->getParentO()->webmisc_b->disable_openbasedir)) ?
			//	$this->getParentO()->webmisc_b->disable_openbasedir : null;
			} else {
				$this->__var_web_user = ($parent && isset($parent->nname)) ? $parent->nname : $this->nname;
			}
		}

		$this->__var_extrabasedir = (isset($gen->extrabasedir)) ? $gen->extrabasedir : null;
		$driverapp = $gbl->getSyncClass(null, $this->syncserver, 'web');
		$this->__var_webdriver = $driverapp;
	}

	function createShowPropertyList(&$alist)
	{
		$alist['property'][] = 'a=show';

		$alist['property'][] = 'a=updateform&sa=extraedit';
	}


	function createShowUpdateform()
	{
		$uflist['edit'] = null;

		return $uflist;
	}

	function postUpdate()
	{
		// We need to write because the fixphpini reads everything from the database.
		$this->write();

		$parent = $this->getParentO();

	//	if ($this->getParentO()->is__table('pserver')) {
		if ($this->getParentClass() === 'pserver') {
			$server = ($parent && isset($parent->nname)) ? $parent->nname : 'localhost';
			exec("sh /script/fixphp --server={$server}");

			exec("sh /script/enable-php-fpm");
		}
	}

	function setPhpModuleUpdate()
	{
		$modulelist = array('xcache', 'suhosin', 'ioncube', 'zend');

		foreach ($modulelist as &$m) {
			if ($this->phpini_flag_b->isOn("enable_{$m}_flag")) {
				$active = isPhpModuleActive($m);

				if (!$active) {
					setPhpModuleActive($m);
				}
			} else {
				setPhpModuleInactive($m);
			}
		}
	}

	function initPhpIni()
	{
		$this->fixphpIniFlag();
		$this->setUpInitialValues();
	}

	function updateform($subaction, $param)
	{
		global $login;

		$this->initPhpIni();

		$pclass = $this->getParentClass();

		if ($subaction === 'extraedit') {
			$totallist = $this->getExtraList();
		} else {
			$totallist = $this->getLocalList();
		}

		$inheritedlist = $this->getInheritedList();
		$adminList = $this->getAdminList();

		foreach ($totallist as $l) {
		//	if ((!$parent->is__table('pserver') && array_search_bool($l, $inheritedlist)) || array_search_bool($l, $adminList)) {
			if (($pclass !== 'pserver' && array_search_bool($l, $inheritedlist)) || array_search_bool($l, $adminList)) {
				$vlist["phpini_flag_b-$l"] = array('M', null);
			} else {
				$vlist["phpini_flag_b-$l"] = null;
			}
		}

		if ($subaction !== 'extraedit') {
			$this->initialValue('phpini_flag_b-date_timezone_flag', 'Europe/London');
			$vlist["phpini_flag_b-date_timezone_flag"] = array('s', getTimeZoneList());

			if ($login->isAdmin()) {
				$this->initialValue('phpini_flag_b-phpfpm_type_flag', 'ondemand');
				$vlist["phpini_flag_b-phpfpm_type_flag"] = array('s', self::getPhpfpmTypeList());
			} else {
				$type = (isset($this->phpini_flag_b) && isset($this->phpini_flag_b->phpfpm_type_flag)) ? $this->phpini_flag_b->phpfpm_type_flag : 'ondemand';
				$vlist["phpini_flag_b-phpfpm_type_flag"] = array('s', array($type));
			}

		//	$vlist["phpini_flag_b-phpfpm_limit_extensions"] = array('t', self::getPhpfpmLimitExtensions());

		}

		// MR -- still not work (like in 'appearance')
		// still something wrong with 'updateall' process!
	//	if ($parent->is__table('pserver')) {
		if ($pclass === 'pserver') {
			$vlist['__v_updateall_button'] = array();
		}

		return $vlist;
	}

	static function getPhpfpmTypeList()
	{
		return array('ondemand', 'dynamic', 'static');
	}

	function setUpInitialValues()
	{
		if (!isset($this->phpini_flag_b) || !($this->phpini_flag_b instanceof phpini_flag_b)) {
			$this->phpini_flag_b = new phpini_flag_b(null, null, $this->nname);
		}

		$pclass = $this->getParentClass();

		if ($pclass === 'pserver') {
			$this->initialValuesBasic();
		} else {
			$p = new phpini(null, $this->syncserver, createParentName('pserver', $this->syncserver));
			$p->get();
			$p->fixphpIniFlag();

			$b = $p->phpini_flag_b;

			$list = array_merge($this->getInheritedList(), $this->getLocalList(), $this->getExtraList());

			$list = array_unique($list);

			foreach ($list as $k => $v) {
				if ($v === 'session_save_path_flag') {
					if ($pclass === 'client') {
						$parent = $this->getParentO();
						$user = ($parent && isset($parent->nname)) ? $parent->nname : $this->nname;
						$path = "/home/kloxo/client/{$user}/session";
						exec("mkdir -p $path");
						$this->initialValue($v, $path);
						// MR -- fix for permissions fail
						exec("chmod 777 $path");
					}
				} else {
					$val = (isset($b) && is_object($b) && isset($b->$v)) ? $b->$v : null;
					$this->initialValue($v, $val);
				}
			}
		}
	}

	function initialValuesBasic()
	{
		if (!isset($this->phpini_flag_b) || !($this->phpini_flag_b instanceof phpini_flag_b)) {
			$this->phpini_flag_b = new phpini_flag_b(null, null, $this->nname);
		}

		$this->initialValue('output_compression_flag', 'off');

		$this->initialValue('upload_max_filesize', '16M');
		$this->initialValue('register_global_flag', 'off');

		$this->phpini_flag_b->session_save_path_flag = '/var/lib/php/session';
		$this->initialValue('session_save_path_flag', $this->phpini_flag_b->session_save_path_flag);

		$initial = 'exec,passthru,shell_exec,system,proc_open,popen,show_source';
		$this->initialValue('disable_functions', $initial);

		$this->initialValue('max_execution_time_flag', '120');
		$this->initialValue('max_input_time_flag', '120');
		$this->initialValue('memory_limit_flag', '128M');
		$this->initialValue('allow_url_fopen_flag', 'on');
		$this->initialValue('allow_url_include_flag', 'off');
		$this->initialValue('display_error_flag', 'off');
		$this->initialValue('log_errors_flag', 'off');
		$this->initialValue('session_autostart_flag', 'off');
		$this->initialValue('file_uploads_flag', 'on');
		$this->initialValue('output_buffering_flag', 'off');
		$this->initialValue('register_argc_argv_flag', 'on');
		$this->initialValue('register_long_arrays_flag', 'on');
		$this->initialValue('magic_quotes_gpc_flag', 'off');
		$this->initialValue('gpc_order_flag', 'GPC');
		$this->initialValue('variables_order_flag', 'EGPCS');
		$this->initialValue('post_max_size_flag', '32M');
		$this->initialValue('magic_quotes_runtime_flag', 'off');
		$this->initialValue('magic_quotes_sybase_flag', 'off');
		$this->initialValue('enable_dl_flag', 'on');
		$this->initialValue('cgi_force_redirect_flag', 'on');
		$this->initialValue('extension_dir_flag', '/usr/lib/php/modules');
		$this->initialValue('upload_tmp_dir_flag', '/tmp');
		$this->initialValue('safe_mode_flag', 'off');

		$this->initialValue('sendmail_from', '');


		$this->initialValue('max_input_vars_flag', '3000');

		if (is_link("/etc/localtime")) {
			$c = str_replace("/usr/share/zoneinfo/", "", readlink("/etc/localtime"));
			$this->initialValue('date_timezone_flag', $c);
		} else {
			// it's mean php in panel itself and wrong. so changed
		//	$this->initialValue('date_timezone_flag', date_default_timezone_get());
			$this->initialValue('date_timezone_flag', 'Europe/London');
		}

		$this->initialValue('phpfpm_type_flag', 'ondemand');

		$this->initialValue('phpfpm_limit_extensions', '.php .php5 .php7');
	}

	function initialValue($var, $val)
	{
		if (!isset($this->phpini_flag_b) || !($this->phpini_flag_b instanceof phpini_flag_b)) {
			$this->phpini_flag_b = new phpini_flag_b(null, null, $this->nname);
		}

		if (!isset($this->phpini_flag_b->$var) || !$this->phpini_flag_b->$var) {
			$this->phpini_flag_b->$var = $val;
		}
	}
}
