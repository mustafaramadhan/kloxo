<?php

abstract class DomainCore extends Lxclient
{
	static $__desc_ssl_flag = array("q", "", "enable_ssl");
//	static $__desc_modperl_flag =  array("q", "",  "enable_mod_perl");
	static $__desc_cgi_flag = array("q", "", "enable_cgi");
//	static $__desc_inc_flag =  array("q", "",  "enable_server_side_includes");
	static $__desc_php_flag = array("q", "", "enable_php");
//	static $__desc_dns_manage_flag =  array("q", "",  "can_manage_dns");
	static $__desc_awstats_flag = array("q", "", "enable_statistics");
//	static $__desc_ddatabase_usage = array("q", "",  "database_disk_usage_(mb)");
//	static $__desc_cron_manage_flag = array("q", "",  "allow_scheduler_management");
//	static $__desc_phpunsafe_flag = array("q", "",  "can_enable_php_unsafe_mode");
	static $__desc_dotnet_flag = array("q", "", "enable_asp.net_(ignored_on_linux)");
//	static $__desc_phpfcgi_flag = array("q", "", "enable_php_fastcgi");
//	static $__desc_phpfcgiprocess_num = array("qh", "", "phpfcgi:number_of_fastcgi_process");
	static $__desc_rubyfcgiprocess_num = array("D", "", "rubyfcgi:number_of_ruby_process");
	static $__desc_parent_name_change = array("", "", "owner");

	static $__desc_traffic_last_usage = array("D", "", "LTraffic:traffic_usage_for_last_month_(MB)");

	static $__desc_easyinstaller_flag = array("q", "", "enable_easyinstaller");
//	static $__desc_php_manage_flag = array("q", "", "php_manage_flag");
//	static $__desc_autoresponder_num = array("q", "", "autores:number_of_autoresponders");
	static $__desc_traffic_usage = array("DS", "", "Traffic:Traffic_(MB/month)");
//	static $__desc_disk_usage = array("D", "",  "Disk:web_disk_usage_(MB)");
	static $__desc_totaldisk_usage = array("D", "", "totDisk:total_disk_usage_(MB)");
	static $__desc_totalinode_usage = array("D", "", "totInode:total_inode_usage");
	static $__desc_maildisk_usage = array("D", "", "MailDisk:mail_disk_usage_(MB)");
	static $__desc_rubyrails_num = array("D", "", "rails:number_of_rails_apps", "");
	static $__desc_mailaccount_num = array("D", "", "Mailaccount:mail_account_num");
	static $__desc_mailinglist_num = array("D", "", "mailinglist:number_of_mailing_lists");
	static $__desc_addondomain_num = array("D", "", "pointer:number_of_pointer_domains");

//	static $__desc_subweb_a_num =  array("q", "", "subdomain:number_of_subdomains");
//	static $__desc_cron_minute_flag = array("q", "", "allow_minute_management_for_cron");
//	static $__desc_mssqldb_num = array("q", "", "mssqldb:mssql_databases");

	function updateform($subaction, $param)
	{
		switch ($subaction) {
			case "ddatabasepserver":
				if ($this->isLogin()) {
					$vlist['mysqldbpserver_list'] = array('M', $this->listpriv->mysqldbpserver_list);
				//	$vlist['mssqldbpserver_list'] = array('M', $this->listpriv->mssqldbpserver_list);
				//	$vlist['dbtype_list'] = array('M', $this->listpriv->dbtype_list);
					$vlist['__v_button'] = array();

					return $vlist;
				}

				$parent = $this->getParentO();
				$vlist['mysqldbpserver_list'] = array('Q', $parent->listpriv->mysqldbpserver_list);
			//	$vlist['mssqldbpserver_list'] = array('Q', $parent->listpriv->mssqldbpserver_list);
			//	$vlist['dbtype_list'] = array('Q', $parent->listpriv->dbtype_list);

				if (!$this->isRightParent()) {
					$this->convertToUnmodifiable($vlist);
				}

				return $vlist;
		}

		return parent::updateform($subaction, $param);
	}

	static function continueFormlistpriv($parent, $class, $param, $continueaction)
	{
		$ret = exec_class_method($class, 'continueFormFinish', $parent, $class, $param, $continueaction);

		return $ret;
	}

	function updateDdatabasepserver($param)
	{
		$this->fixpserver_list($param);

		return $param;
	}
}
