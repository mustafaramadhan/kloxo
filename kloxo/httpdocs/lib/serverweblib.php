<?php

class serverweb extends lxdb
{
	static $__desc = array("", "", "webserver_config");
	static $__desc_nname = array("", "", "webserver_config");
	static $__desc_php_type = array("", "", "php_type");
	static $__desc_secondary_php = array("", "", "secondary_php");

	static $__acdesc_update_edit = array("", "", "config");
	static $__acdesc_show = array("", "", "webserver_config");

	static $__desc_apache_optimize = array("", "", "apache_optimize");
	static $__desc_mysql_convert = array("", "", "mysql_convert");
	static $__desc_fix_chownchmod = array("", "", "fix_chownchmod");

	static $__desc_php_branch = array("", "", "php_branch");

	function createShowUpdateform()
	{
		$uflist['edit'] = null;

		$uflist['php_branch'] = null;

		if (isWebProxyOrApache()) {
			$uflist['php_type'] = null;
			$uflist['apache_optimize'] = null;
		}

		$uflist['mysql_convert'] = null;
		$uflist['fix_chownchmod'] = null;

		return $uflist;
	}

	function updateform($subaction, $param)
	{
		global $gbl, $sgbl, $login, $ghtml;

		switch($subaction) {

			case "apache_optimize":
				$vlist['apache_optimize'] = array('s', array('--- none ---', 'default', 'optimize'));
				$this->setDefaultValue('apache_optimize', '--- none ---');

				break;

			case "mysql_convert":
				$vlist['mysql_convert'] = array('s', array('--- none ---', 'to-myisam', 'to-innodb'));

				$this->setDefaultValue('mysql_convert', '--- none ---');

				break;

			case "fix_chownchmod":
				$vlist['fix_chownchmod'] = array('s', array(
						'--- none ---', 'fix-ownership', 'fix-permissions', 'fix-ALL')
				);

				$this->setDefaultValue('fix_chownchmod', '--- none ---');

				break;

			case "php_type":
				$vlist['php_type'] = array('s', array(
						'mod_php', 'mod_php_ruid2', 'mod_php_itk',
						'suphp', 'suphp_event', 'suphp_worker',
						'php-fpm_event', 'php-fpm_worker',
						'fcgid_event', 'fcgid_worker')
				);

				$this->setDefaultValue('php_type', 'mod_php');

				$vlist['secondary_php'] = array('f', 'on', 'off');

				if (file_exists("/etc/httpd/conf.d/suphp52.conf")) {
					$this->setDefaultValue('secondary_php', 'on');
				}

				break;

			case "php_branch":
				$a = getBranchList('php');
				$vlist['php_branch'] = array('s', $a);

				$this->setDefaultValue('php_branch', getPhpBranch());

				break;

			default:
				$vlist['__m_message_pre'] = 'webserver_config';
				$vlist['__v_button'] = array();

				break;
		}

		return $vlist;
	}

	static function initThisObjectRule($parent, $class, $name = null)
	{
		return $parent->getClName();
	}

}
