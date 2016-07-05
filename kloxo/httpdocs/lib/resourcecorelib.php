<?php 
abstract class Resourcecore extends Lxclient
{

/// Domain REsources....

	static $__desc_traffic_last_usage = array("D", "", "Ltraffic:traffic_usage_for_last_month_(MB)");
//	static $__desc_validity_time	 = array("q", "",  "validity_period");
	static $__desc_awstats_flag = array("q", "", "enable_statistics");

//	static $__desc_autoresponder_num  	 = array("q", "",  "autores:number_of_autoresponders");
	static $__desc_logo_manage_flag = array("q", "", "can_change_logo");
	static $__desc_document_root_flag = array("q", "", "can_set_document_root");
	static $__desc_runstats_flag = array("q", "", "can_run_stats_program");
	static $__desc_traffic_usage = array("q", "", "Traffic:Traffic_(MB/Month)");
	static $__desc_totaldisk_usage = array("q", "", "TotDisk:total_disk_usage_(MB)");
	static $__desc_totalinode_usage = array("q", "", "TotInode:total_inode_usage");
//	static $__desc_disk_usage	 = array("D", "",  "Disk:web_disk_usage_(MB)");
	static $__desc_maildisk_usage = array("q", "", "MailDisk:mail_disk_usage_(MB)");
	static $__desc_mailaccount_num = array("q", "", "Mailaccount:mail_account_num");
	static $__desc_mailinglist_num = array("q", "", "mailinglist:number_of_mailing_lists");
	static $__desc_ftpuser_num = array("q", "", "ftpuser:number_of_ftp_users");

//	static $__desc_phpfcgi_flag = array("q", "", "enable_php_fastcgi");
	static $__desc_phpfcgiprocess_num = array("q", "", "phpfcgi:number_of_fastcgi_process");

//	static $__desc_subweb_a_num =  array("q", "",  "subdomain:number_of_subdomains");
	static $__desc_cron_minute_flag = array("q", "", "allow_minute_management_for_cron");
	static $__desc_mysqldb_num = array("q", "", "mysqldb:mysql_databases");
	static $__desc_addondomain_num = array("q", "", "pointer:number_of_pointer_domains");
	static $__desc_mysqldb_usage = array("D", "", "mysqldisk:mysql_disk_usage", "");
//	static $__desc_mssqldb_num = array("q", "",  "mssqldb:mssql_databases");

//	static $__desc_php_manage_flag =  array("q", "",  "enable_php_management");
	static $__desc_easyinstaller_flag = array("q", "", "enable_easyinstaller");
	static $__desc_ssl_flag = array("q", "", "enable_ssl");
	static $__desc_can_change_limit_flag = array("q", "", "can_change_limit");
	static $__desc_webhosting_flag = array("q", "", "enable_web_hosting");
//	static $__desc_modperl_flag =  array("q", "",  "enable_mod_perl");
	static $__desc_cgi_flag = array("q", "", "enable_cgi");
//	static $__desc_inc_flag =  array("q", "",  "enable_server_side_includes");
	static $__desc_php_flag = array("q", "", "enable_php");
	static $__desc_dns_manage_flag = array("q", "", "can_manage_dns");
//	static $__desc_ddatabase_usage = array("q", "",  "database_disk_usage_(MB)");
	static $__desc_cron_manage_flag = array("q", "", "allow_scheduler_management");
//	static $__desc_phpunsafe_flag = array("q", "",  "can_enable_php_unsafe_mode");
//	static $__desc_dotnet_flag = array("q", "", "enable_asp.net_(ignored_on_linux)");
	static $__desc_parent_name_change = array("", "", "owner");

	static $__desc_mysqldbpserver_list = array("Q", "", "mysql_database_server_pool");
//	static $__desc_mssqldbpserver_list = array("Q", "", "mssql_database_server_pool");
	static $__acdesc_update_ddatabasepserver = array("", "", "database_server_pool");

	static $__desc_ipaddress_list = array("Q", "", "ip_address_pool");
	static $__desc_dnspserver_list = array("Q", "", "dns_server_pool");
	static $__desc_mmailpserver_list = array("Q", "", "mail_server_pool");
	static $__desc_webpserver_list = array("Q", "", "web_server_pool");
//	static $__desc_dbtype_list =  array("Q", "",  "database_types");
	static $__desc_dnstemplate_list = array("", "", "dns_template_pool");

	static $__desc_rubyfcgiprocess_num = array("q", "", "rubyfcgi:number_of_ruby_process");
	static $__desc_rubyrails_num = array("q", "", "rails:number_of_rails_apps", "");
	static $__desc_client_num = array("q", "", "clients:number_of_clients", 'a=list&c=client');
//	static $__desc_domain_num =  array("q", "",  "domains:number_of_domains", 'a=list&c=domain');
	static $__desc_maindomain_num = array("q", "", "domains:number_of_domains", 'a=list&c=domain');
	static $__desc_subdomain_num = array("q", "", "subdomains:number_of_subdomains", 'a=list&c=domain');
	static $__desc_domain_add_flag = array("q", "", "can_add_domains", 'a=list&c=domain');
	static $__desc_can_set_disabled_flag = array("q", "", "can_set_disabled_url");
	static $__desc_can_change_password_flag = array("q", "", "can_change_password");
	static $__desc_backup_flag = array("q", "", "allow_backing_up");
	static $__desc_backupschedule_flag = array("q", "", "allow_backup_scheduling");
	static $__desc_vpspserver_list = array("Q", "", "Vps Server Pool");
	static $__desc_vmachinepserver_list = array("Q", "", "Virtual Machine Server Pool");
}

