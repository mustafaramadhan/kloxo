----
-- phpLiteAdmin database dump (http://phpliteadmin.googlecode.com)
-- phpLiteAdmin version: 1.9.5
-- Exported: 8:06pm on April 30, 2015 (CEST)
-- database file: ./kloxo.sqlite
----
BEGIN TRANSACTION;

----
-- Table structure for actionlog
----
CREATE TABLE "actionlog" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "login" varchar(255) DEFAULT NULL,
  "loginclname" varchar(255) DEFAULT NULL,
  "auxiliary_id" varchar(255) DEFAULT NULL,
  "ipaddress" varchar(255) DEFAULT NULL,
  "class" varchar(255) DEFAULT NULL,
  "objectname" varchar(255) DEFAULT NULL,
  "action" varchar(255) DEFAULT NULL,
  "subaction" varchar(255) DEFAULT NULL,
  "ddate" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for actionlog, a total of 0 rows
----

----
-- Table structure for addondomain
----
CREATE TABLE "addondomain" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "ttype" varchar(255) DEFAULT NULL,
  "destinationdir" varchar(255) DEFAULT NULL,
  "mail_flag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for addondomain, a total of 0 rows
----

----
-- Table structure for allowedip
----
CREATE TABLE "allowedip" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "ipaddress" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for allowedip, a total of 0 rows
----

----
-- Table structure for anonftpipaddress
----
CREATE TABLE "anonftpipaddress" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "status" varchar(255) DEFAULT NULL,
  "syncserver" varchar(255) DEFAULT NULL,
  "ipaddr" varchar(255) DEFAULT NULL,
  "message" varchar(255) DEFAULT NULL,
  "anondomain" varchar(255) DEFAULT NULL,
  "ser_anonftpmisc_b" longtext,
  "disk_limit" varchar(255) DEFAULT NULL,
  "connection_limit" varchar(255) DEFAULT NULL,
  "download_limit" varchar(255) DEFAULT NULL,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for anonftpipaddress, a total of 0 rows
----

----
-- Table structure for aspnet
----
CREATE TABLE "aspnet" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "version" varchar(255) DEFAULT NULL,
  "syncserver" varchar(255) DEFAULT NULL,
  "session_timeout" varchar(255) DEFAULT NULL,
  "ser_globalization_b" longtext,
  "ser_aspnetmisc_b" longtext,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for aspnet, a total of 0 rows
----

----
-- Table structure for autoresponder
----
CREATE TABLE "autoresponder" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "status" varchar(255) DEFAULT NULL,
  "send_rule" varchar(255) DEFAULT NULL,
  "reply_subject" varchar(255) DEFAULT NULL,
  "text_message" longtext,
  "autores_name" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for autoresponder, a total of 0 rows
----

----
-- Table structure for auxiliary
----
CREATE TABLE "auxiliary" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "realname" varchar(255) DEFAULT NULL,
  "add_address" varchar(255) DEFAULT NULL,
  "add_city" varchar(255) DEFAULT NULL,
  "add_country" varchar(255) DEFAULT NULL,
  "add_telephone" varchar(255) DEFAULT NULL,
  "add_fax" varchar(255) DEFAULT NULL,
  "password" varchar(255) DEFAULT NULL,
  "cpstatus" varchar(255) DEFAULT NULL,
  "demo_status" varchar(255) DEFAULT NULL,
  "contactemail" varchar(255) DEFAULT NULL,
  "text_comment" longtext,
  "disable_per" varchar(255) DEFAULT NULL,
  "ser_hpfilter" longtext,
  "ddate" varchar(255) DEFAULT NULL,
  "ser_dskhistory" longtext,
  "ser_dskshortcut_a" longtext,
  "interface_template" varchar(255) DEFAULT NULL,
  "ser_boxpos" longtext,
  "dialogsize" varchar(255) DEFAULT NULL,
  "status" varchar(255) DEFAULT NULL,
  "realpass" varchar(255) DEFAULT NULL,
  "delete_flag" varchar(255) DEFAULT NULL,
  "pserver_flag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for auxiliary, a total of 0 rows
----

----
-- Table structure for blockedip
----
CREATE TABLE "blockedip" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "ipaddress" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for blockedip, a total of 0 rows
----

----
-- Table structure for client
----
CREATE TABLE "client" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "priv_q_pserver_num" varchar(255) DEFAULT NULL,
  "used_q_pserver_num" varchar(255) DEFAULT NULL,
  "priv_q_client_num" varchar(255) DEFAULT NULL,
  "used_q_client_num" varchar(255) DEFAULT NULL,
  "priv_q_maindomain_num" varchar(255) DEFAULT NULL,
  "used_q_maindomain_num" varchar(255) DEFAULT NULL,
  "priv_q_domain_num" varchar(255) DEFAULT NULL,
  "used_q_domain_num" varchar(255) DEFAULT NULL,
  "priv_q_subdomain_num" varchar(255) DEFAULT NULL,
  "used_q_subdomain_num" varchar(255) DEFAULT NULL,
  "priv_q_clientdisk_usage" varchar(255) DEFAULT NULL,
  "used_q_clientdisk_usage" varchar(255) DEFAULT NULL,
  "priv_q_domain_add_flag" varchar(255) DEFAULT NULL,
  "used_q_domain_add_flag" varchar(255) DEFAULT NULL,
  "priv_q_can_change_limit_flag" varchar(255) DEFAULT NULL,
  "used_q_can_change_limit_flag" varchar(255) DEFAULT NULL,
  "priv_q_can_set_disabled_flag" varchar(255) DEFAULT NULL,
  "used_q_can_set_disabled_flag" varchar(255) DEFAULT NULL,
  "priv_q_can_change_password_flag" varchar(255) DEFAULT NULL,
  "used_q_can_change_password_flag" varchar(255) DEFAULT NULL,
  "priv_q_document_root_flag" varchar(255) DEFAULT NULL,
  "used_q_document_root_flag" varchar(255) DEFAULT NULL,
  "priv_q_runstats_flag" varchar(255) DEFAULT NULL,
  "used_q_runstats_flag" varchar(255) DEFAULT NULL,
  "priv_q_traffic_usage" varchar(255) DEFAULT NULL,
  "used_q_traffic_usage" varchar(255) DEFAULT NULL,
  "priv_q_totaldisk_usage" varchar(255) DEFAULT NULL,
  "used_q_totaldisk_usage" varchar(255) DEFAULT NULL,
  "priv_q_totalinode_usage" varchar(255) DEFAULT NULL,
  "used_q_totalinode_usage" varchar(255) DEFAULT NULL,
  "priv_q_ssl_flag" varchar(255) DEFAULT NULL,
  "used_q_ssl_flag" varchar(255) DEFAULT NULL,
  "priv_q_rubyfcgiprocess_num" varchar(255) DEFAULT NULL,
  "used_q_rubyfcgiprocess_num" varchar(255) DEFAULT NULL,
  "priv_q_disk_usage" varchar(255) DEFAULT NULL,
  "used_q_disk_usage" varchar(255) DEFAULT NULL,
  "priv_q_logo_manage_flag" varchar(255) DEFAULT NULL,
  "used_q_logo_manage_flag" varchar(255) DEFAULT NULL,
  "priv_q_ftpuser_num" varchar(255) DEFAULT NULL,
  "used_q_ftpuser_num" varchar(255) DEFAULT NULL,
  "priv_q_totalinode_flag" varchar(255) DEFAULT NULL,
  "used_q_totalinode_flag" varchar(255) DEFAULT NULL,
  "priv_q_php_manage_flag" varchar(255) DEFAULT NULL,
  "used_q_php_manage_flag" varchar(255) DEFAULT NULL,
  "priv_q_inc_flag" varchar(255) DEFAULT NULL,
  "used_q_inc_flag" varchar(255) DEFAULT NULL,
  "priv_q_awstats_flag" varchar(255) DEFAULT NULL,
  "used_q_awstats_flag" varchar(255) DEFAULT NULL,
  "priv_q_easyinstaller_flag" varchar(255) DEFAULT NULL,
  "used_q_easyinstaller_flag" varchar(255) DEFAULT NULL,
  "priv_q_modperl_flag" varchar(255) DEFAULT NULL,
  "used_q_modperl_flag" varchar(255) DEFAULT NULL,
  "priv_q_cgi_flag" varchar(255) DEFAULT NULL,
  "used_q_cgi_flag" varchar(255) DEFAULT NULL,
  "priv_q_php_flag" varchar(255) DEFAULT NULL,
  "used_q_php_flag" varchar(255) DEFAULT NULL,
  "priv_q_phpunsafe_flag" varchar(255) DEFAULT NULL,
  "used_q_phpunsafe_flag" varchar(255) DEFAULT NULL,
  "priv_q_subweb_a_num" varchar(255) DEFAULT NULL,
  "used_q_subweb_a_num" varchar(255) DEFAULT NULL,
  "priv_q_dotnet_flag" varchar(255) DEFAULT NULL,
  "used_q_dotnet_flag" varchar(255) DEFAULT NULL,
  "priv_q_cron_num" varchar(255) DEFAULT NULL,
  "used_q_cron_num" varchar(255) DEFAULT NULL,
  "priv_q_cron_minute_flag" varchar(255) DEFAULT NULL,
  "used_q_cron_minute_flag" varchar(255) DEFAULT NULL,
  "priv_q_cron_manage_flag" varchar(255) DEFAULT NULL,
  "used_q_cron_manage_flag" varchar(255) DEFAULT NULL,
  "priv_q_phpfcgi_flag" varchar(255) DEFAULT NULL,
  "used_q_phpfcgi_flag" varchar(255) DEFAULT NULL,
  "priv_q_rubyrails_num" varchar(255) DEFAULT NULL,
  "used_q_rubyrails_num" varchar(255) DEFAULT NULL,
  "priv_q_phpfcgiprocess_num" varchar(255) DEFAULT NULL,
  "used_q_phpfcgiprocess_num" varchar(255) DEFAULT NULL,
  "priv_q_maildisk_usage" varchar(255) DEFAULT NULL,
  "used_q_maildisk_usage" varchar(255) DEFAULT NULL,
  "priv_q_autoresponder_num" varchar(255) DEFAULT NULL,
  "used_q_autoresponder_num" varchar(255) DEFAULT NULL,
  "priv_q_mailaccount_num" varchar(255) DEFAULT NULL,
  "used_q_mailaccount_num" varchar(255) DEFAULT NULL,
  "priv_q_mailinglist_num" varchar(255) DEFAULT NULL,
  "used_q_mailinglist_num" varchar(255) DEFAULT NULL,
  "priv_q_mysqldb_usage" varchar(255) DEFAULT NULL,
  "used_q_mysqldb_usage" varchar(255) DEFAULT NULL,
  "priv_q_mssqldb_usage" varchar(255) DEFAULT NULL,
  "used_q_mssqldb_usage" varchar(255) DEFAULT NULL,
  "priv_q_backupschedule_flag" varchar(255) DEFAULT NULL,
  "used_q_backupschedule_flag" varchar(255) DEFAULT NULL,
  "priv_q_traffic_last_usage" varchar(255) DEFAULT NULL,
  "used_q_traffic_last_usage" varchar(255) DEFAULT NULL,
  "priv_q_backup_flag" varchar(255) DEFAULT NULL,
  "used_q_backup_flag" varchar(255) DEFAULT NULL,
  "priv_q_dns_manage_flag" varchar(255) DEFAULT NULL,
  "used_q_dns_manage_flag" varchar(255) DEFAULT NULL,
  "priv_q_mysqldb_num" varchar(255) DEFAULT NULL,
  "used_q_mysqldb_num" varchar(255) DEFAULT NULL,
  "priv_q_mssqldb_num" varchar(255) DEFAULT NULL,
  "used_q_mssqldb_num" varchar(255) DEFAULT NULL,
  "priv_q_addondomain_num" varchar(255) DEFAULT NULL,
  "used_q_addondomain_num" varchar(255) DEFAULT NULL,
  "priv_q_webhosting_flag" varchar(255) DEFAULT NULL,
  "used_q_webhosting_flag" varchar(255) DEFAULT NULL,
  "realname" varchar(255) DEFAULT NULL,
  "add_address" varchar(255) DEFAULT NULL,
  "add_city" varchar(255) DEFAULT NULL,
  "add_country" varchar(255) DEFAULT NULL,
  "add_telephone" varchar(255) DEFAULT NULL,
  "add_fax" varchar(255) DEFAULT NULL,
  "password" varchar(255) DEFAULT NULL,
  "cpstatus" varchar(255) DEFAULT NULL,
  "demo_status" varchar(255) DEFAULT NULL,
  "contactemail" varchar(255) DEFAULT NULL,
  "text_comment" longtext,
  "disable_per" varchar(255) DEFAULT NULL,
  "ser_hpfilter" longtext,
  "ddate" varchar(255) DEFAULT NULL,
  "ser_dskhistory" longtext,
  "ser_dskshortcut_a" longtext,
  "interface_template" varchar(255) DEFAULT NULL,
  "ser_boxpos" longtext,
  "dialogsize" varchar(255) DEFAULT NULL,
  "cttype" varchar(255) DEFAULT NULL,
  "ser_listpriv" longtext,
  "skeletonarchive" varchar(255) DEFAULT NULL,
  "ser_dnstemplate_list" longtext,
  "state" varchar(255) DEFAULT NULL,
  "status" varchar(255) DEFAULT NULL,
  "disable_reason" varchar(255) DEFAULT NULL,
  "disable_url" varchar(255) DEFAULT NULL,
  "template_used" varchar(255) DEFAULT NULL,
  "username" varchar(255) DEFAULT NULL,
  "realpass" varchar(255) DEFAULT NULL,
  "shell" varchar(255) DEFAULT NULL,
  "default_domain" varchar(255) DEFAULT NULL,
  "resourceplan_used" varchar(255) DEFAULT NULL,
  "websyncserver" varchar(255) DEFAULT NULL,
  "coma_dnssyncserver_list" text,
  "mmailsyncserver" varchar(255) DEFAULT NULL,
  "mysqldbsyncserver" varchar(255) DEFAULT NULL,
  "syncserver" varchar(255) DEFAULT NULL,
  "cron_mailto" varchar(255) DEFAULT NULL,
  "dnstemplate_name" varchar(255) DEFAULT NULL,
  "corerootdir" varchar(255) DEFAULT NULL,
  "disable_system_flag" varchar(255) DEFAULT NULL,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for client, a total of 0 rows
----

----
-- Table structure for clienttemplate
----
CREATE TABLE "clienttemplate" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "ser_priv" longtext,
  "share_status" varchar(255) DEFAULT NULL,
  "disable_per" varchar(255) DEFAULT NULL,
  "skin_name" varchar(255) DEFAULT NULL,
  "description" varchar(255) DEFAULT NULL,
  "ddate" varchar(255) DEFAULT NULL,
  "ser_listpriv" longtext,
  "ttype" varchar(255) DEFAULT NULL,
  "ser_dnstemplate_list" longtext,
  PRIMARY KEY ("nname")
);

----
-- Data dump for clienttemplate, a total of 0 rows
----

----
-- Table structure for component
----
CREATE TABLE "component" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "componentname" varchar(255) DEFAULT NULL,
  "syncserver" varchar(255) DEFAULT NULL,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for component, a total of 0 rows
----

----
-- Table structure for cron
----
CREATE TABLE "cron" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "syncserver" varchar(255) DEFAULT NULL,
  "email" varchar(255) DEFAULT NULL,
  "ser_minute" longtext,
  "ser_hour" longtext,
  "ser_ddate" longtext,
  "ser_month" longtext,
  "ser_weekday" longtext,
  "jobid" varchar(255) DEFAULT NULL,
  "username" varchar(255) DEFAULT NULL,
  "command" varchar(255) DEFAULT NULL,
  "argument" varchar(255) DEFAULT NULL,
  "ttype" varchar(255) DEFAULT NULL,
  "simple_cron" varchar(255) DEFAULT NULL,
  "cron_day_hour" varchar(255) DEFAULT NULL,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for cron, a total of 0 rows
----

----
-- Table structure for customaction
----
CREATE TABLE "customaction" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "class" varchar(255) DEFAULT NULL,
  "action" varchar(255) DEFAULT NULL,
  "subaction" varchar(255) DEFAULT NULL,
  "exec" varchar(255) DEFAULT NULL,
  "where_to_exec" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for customaction, a total of 0 rows
----

----
-- Table structure for custombutton
----
CREATE TABLE "custombutton" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "status" varchar(255) DEFAULT NULL,
  "class" varchar(255) DEFAULT NULL,
  "title" varchar(255) DEFAULT NULL,
  "url" varchar(255) DEFAULT NULL,
  "description" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for custombutton, a total of 0 rows
----

----
-- Table structure for davuser
----
CREATE TABLE "davuser" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "realname" varchar(255) DEFAULT NULL,
  "add_address" varchar(255) DEFAULT NULL,
  "add_city" varchar(255) DEFAULT NULL,
  "add_country" varchar(255) DEFAULT NULL,
  "add_telephone" varchar(255) DEFAULT NULL,
  "add_fax" varchar(255) DEFAULT NULL,
  "password" varchar(255) DEFAULT NULL,
  "cpstatus" varchar(255) DEFAULT NULL,
  "demo_status" varchar(255) DEFAULT NULL,
  "contactemail" varchar(255) DEFAULT NULL,
  "text_comment" longtext,
  "disable_per" varchar(255) DEFAULT NULL,
  "ser_hpfilter" longtext,
  "ddate" varchar(255) DEFAULT NULL,
  "ser_dskhistory" longtext,
  "ser_dskshortcut_a" longtext,
  "interface_template" varchar(255) DEFAULT NULL,
  "ser_boxpos" longtext,
  "dialogsize" varchar(255) DEFAULT NULL,
  "state" varchar(255) DEFAULT NULL,
  "status" varchar(255) DEFAULT NULL,
  "disable_reason" varchar(255) DEFAULT NULL,
  "realpass" varchar(255) DEFAULT NULL,
  "username" varchar(255) DEFAULT NULL,
  "directory" varchar(255) DEFAULT NULL,
  "syncserver" varchar(255) DEFAULT NULL,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for davuser, a total of 0 rows
----

----
-- Table structure for dbadmin
----
CREATE TABLE "dbadmin" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "dbtype" varchar(255) DEFAULT NULL,
  "dbadmin_name" varchar(255) DEFAULT NULL,
  "dbpassword" varchar(255) DEFAULT NULL,
  "syncserver" varchar(255) DEFAULT NULL,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for dbadmin, a total of 0 rows
----

----
-- Table structure for dirprotect
----
CREATE TABLE "dirprotect" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "syncserver" varchar(255) DEFAULT NULL,
  "authname" varchar(255) DEFAULT NULL,
  "subweb" varchar(255) DEFAULT NULL,
  "path" varchar(255) DEFAULT NULL,
  "status" varchar(255) DEFAULT NULL,
  "ser_diruser_a" longtext,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for dirprotect, a total of 0 rows
----

----
-- Table structure for dns
----
CREATE TABLE "dns" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "ser_mx_rec_a" longtext,
  "ser_ns_rec_a" longtext,
  "ser_a_rec_a" longtext,
  "ser_cn_rec_a" longtext,
  "ser_txt_rec_a" longtext,
  "ttl" varchar(255) DEFAULT NULL,
  "soanameserver" varchar(255) DEFAULT NULL,
  "hostmaster" varchar(255) DEFAULT NULL,
  "zone_type" varchar(255) DEFAULT NULL,
  "ser_dns_record_a" longtext,
  "syncserver" varchar(255) DEFAULT NULL,
  "serial" varchar(255) DEFAULT NULL,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for dns, a total of 0 rows
----

----
-- Table structure for dnsslave
----
CREATE TABLE "dnsslave" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "master_ip" varchar(255) DEFAULT NULL,
  "syncserver" varchar(255) DEFAULT NULL,
  "serial" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for dnsslave, a total of 0 rows
----

----
-- Table structure for dnstemplate
----
CREATE TABLE "dnstemplate" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "ser_mx_rec_a" longtext,
  "ser_ns_rec_a" longtext,
  "ser_a_rec_a" longtext,
  "ser_cn_rec_a" longtext,
  "ser_txt_rec_a" longtext,
  "ttl" varchar(255) DEFAULT NULL,
  "soanameserver" varchar(255) DEFAULT NULL,
  "zone_type" varchar(255) DEFAULT NULL,
  "ser_dns_record_a" longtext,
  "webipaddress" varchar(255) DEFAULT NULL,
  "mmailipaddress" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for dnstemplate, a total of 0 rows
----

----
-- Table structure for domain
----
CREATE TABLE "domain" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "priv_q_traffic_usage" varchar(255) DEFAULT NULL,
  "used_q_traffic_usage" varchar(255) DEFAULT NULL,
  "priv_q_totaldisk_usage" varchar(255) DEFAULT NULL,
  "used_q_totaldisk_usage" varchar(255) DEFAULT NULL,
  "priv_q_ssl_flag" varchar(255) DEFAULT NULL,
  "used_q_ssl_flag" varchar(255) DEFAULT NULL,
  "priv_q_rubyfcgiprocess_num" varchar(255) DEFAULT NULL,
  "used_q_rubyfcgiprocess_num" varchar(255) DEFAULT NULL,
  "priv_q_disk_usage" varchar(255) DEFAULT NULL,
  "used_q_disk_usage" varchar(255) DEFAULT NULL,
  "priv_q_logo_manage_flag" varchar(255) DEFAULT NULL,
  "used_q_logo_manage_flag" varchar(255) DEFAULT NULL,
  "priv_q_ftpuser_num" varchar(255) DEFAULT NULL,
  "used_q_ftpuser_num" varchar(255) DEFAULT NULL,
  "priv_q_frontpage_flag" varchar(255) DEFAULT NULL,
  "used_q_frontpage_flag" varchar(255) DEFAULT NULL,
  "priv_q_php_manage_flag" varchar(255) DEFAULT NULL,
  "used_q_php_manage_flag" varchar(255) DEFAULT NULL,
  "priv_q_inc_flag" varchar(255) DEFAULT NULL,
  "used_q_inc_flag" varchar(255) DEFAULT NULL,
  "priv_q_awstats_flag" varchar(255) DEFAULT NULL,
  "used_q_awstats_flag" varchar(255) DEFAULT NULL,
  "priv_q_easyinstaller_flag" varchar(255) DEFAULT NULL,
  "used_q_easyinstaller_flag" varchar(255) DEFAULT NULL,
  "priv_q_modperl_flag" varchar(255) DEFAULT NULL,
  "used_q_modperl_flag" varchar(255) DEFAULT NULL,
  "priv_q_cgi_flag" varchar(255) DEFAULT NULL,
  "used_q_cgi_flag" varchar(255) DEFAULT NULL,
  "priv_q_php_flag" varchar(255) DEFAULT NULL,
  "used_q_php_flag" varchar(255) DEFAULT NULL,
  "priv_q_phpunsafe_flag" varchar(255) DEFAULT NULL,
  "used_q_phpunsafe_flag" varchar(255) DEFAULT NULL,
  "priv_q_subweb_a_num" varchar(255) DEFAULT NULL,
  "used_q_subweb_a_num" varchar(255) DEFAULT NULL,
  "priv_q_dotnet_flag" varchar(255) DEFAULT NULL,
  "used_q_dotnet_flag" varchar(255) DEFAULT NULL,
  "priv_q_cron_num" varchar(255) DEFAULT NULL,
  "used_q_cron_num" varchar(255) DEFAULT NULL,
  "priv_q_cron_minute_flag" varchar(255) DEFAULT NULL,
  "used_q_cron_minute_flag" varchar(255) DEFAULT NULL,
  "priv_q_cron_manage_flag" varchar(255) DEFAULT NULL,
  "used_q_cron_manage_flag" varchar(255) DEFAULT NULL,
  "priv_q_phpfcgi_flag" varchar(255) DEFAULT NULL,
  "used_q_phpfcgi_flag" varchar(255) DEFAULT NULL,
  "priv_q_rubyrails_num" varchar(255) DEFAULT NULL,
  "used_q_rubyrails_num" varchar(255) DEFAULT NULL,
  "priv_q_phpfcgiprocess_num" varchar(255) DEFAULT NULL,
  "used_q_phpfcgiprocess_num" varchar(255) DEFAULT NULL,
  "priv_q_maildisk_usage" varchar(255) DEFAULT NULL,
  "used_q_maildisk_usage" varchar(255) DEFAULT NULL,
  "priv_q_autoresponder_num" varchar(255) DEFAULT NULL,
  "used_q_autoresponder_num" varchar(255) DEFAULT NULL,
  "priv_q_mailaccount_num" varchar(255) DEFAULT NULL,
  "used_q_mailaccount_num" varchar(255) DEFAULT NULL,
  "priv_q_mailinglist_num" varchar(255) DEFAULT NULL,
  "used_q_mailinglist_num" varchar(255) DEFAULT NULL,
  "priv_q_mysqldb_usage" varchar(255) DEFAULT NULL,
  "used_q_mysqldb_usage" varchar(255) DEFAULT NULL,
  "priv_q_mssqldb_usage" varchar(255) DEFAULT NULL,
  "used_q_mssqldb_usage" varchar(255) DEFAULT NULL,
  "priv_q_backupschedule_flag" varchar(255) DEFAULT NULL,
  "used_q_backupschedule_flag" varchar(255) DEFAULT NULL,
  "priv_q_traffic_last_usage" varchar(255) DEFAULT NULL,
  "used_q_traffic_last_usage" varchar(255) DEFAULT NULL,
  "priv_q_backup_flag" varchar(255) DEFAULT NULL,
  "used_q_backup_flag" varchar(255) DEFAULT NULL,
  "priv_q_dns_manage_flag" varchar(255) DEFAULT NULL,
  "used_q_dns_manage_flag" varchar(255) DEFAULT NULL,
  "priv_q_mysqldb_num" varchar(255) DEFAULT NULL,
  "used_q_mysqldb_num" varchar(255) DEFAULT NULL,
  "priv_q_mssqldb_num" varchar(255) DEFAULT NULL,
  "used_q_mssqldb_num" varchar(255) DEFAULT NULL,
  "priv_q_addondomain_num" varchar(255) DEFAULT NULL,
  "used_q_addondomain_num" varchar(255) DEFAULT NULL,
  "realname" varchar(255) DEFAULT NULL,
  "add_address" varchar(255) DEFAULT NULL,
  "add_city" varchar(255) DEFAULT NULL,
  "add_country" varchar(255) DEFAULT NULL,
  "add_telephone" varchar(255) DEFAULT NULL,
  "add_fax" varchar(255) DEFAULT NULL,
  "password" varchar(255) DEFAULT NULL,
  "cpstatus" varchar(255) DEFAULT NULL,
  "demo_status" varchar(255) DEFAULT NULL,
  "contactemail" varchar(255) DEFAULT NULL,
  "text_comment" longtext,
  "disable_per" varchar(255) DEFAULT NULL,
  "ser_hpfilter" longtext,
  "ddate" varchar(255) DEFAULT NULL,
  "ser_dskhistory" longtext,
  "ser_dskshortcut_a" longtext,
  "interface_template" varchar(255) DEFAULT NULL,
  "ser_boxpos" longtext,
  "dialogsize" varchar(255) DEFAULT NULL,
  "state" varchar(255) DEFAULT NULL,
  "status" varchar(255) DEFAULT NULL,
  "disable_reason" varchar(255) DEFAULT NULL,
  "ser_listpriv" longtext,
  "mmailpserver" varchar(255) DEFAULT NULL,
  "webpserver" varchar(255) DEFAULT NULL,
  "dnspserver" varchar(255) DEFAULT NULL,
  "secdnspserver" varchar(255) DEFAULT NULL,
  "ttype" varchar(255) DEFAULT NULL,
  "username" varchar(255) DEFAULT NULL,
  "nameserver" varchar(255) DEFAULT NULL,
  "redirect_domain" varchar(255) DEFAULT NULL,
  "template_used" varchar(255) DEFAULT NULL,
  "realpass" varchar(255) DEFAULT NULL,
  "dtype" varchar(255) DEFAULT NULL,
  "subdomain_parent" varchar(255) DEFAULT NULL,
  "resourceplan_used" varchar(255) DEFAULT NULL,
  "previewdomain" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for domain, a total of 0 rows
----

----
-- Table structure for domaindefault
----
CREATE TABLE "domaindefault" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "remove_processed_stats" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for domaindefault, a total of 0 rows
----

----
-- Table structure for domainipaddress
----
CREATE TABLE "domainipaddress" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "domain" varchar(255) DEFAULT NULL,
  "ipaddr" varchar(255) DEFAULT NULL,
  "syncserver" varchar(255) DEFAULT NULL,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for domainipaddress, a total of 0 rows
----

----
-- Table structure for domaintemplate
----
CREATE TABLE "domaintemplate" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "ser_priv" longtext,
  "share_status" varchar(255) DEFAULT NULL,
  "disable_per" varchar(255) DEFAULT NULL,
  "skin_name" varchar(255) DEFAULT NULL,
  "description" varchar(255) DEFAULT NULL,
  "ddate" varchar(255) DEFAULT NULL,
  "ser_listpriv" longtext,
  "ttype" varchar(255) DEFAULT NULL,
  "ser_dnstemplate_list" longtext,
  "dnstemplate" varchar(255) DEFAULT NULL,
  "ipaddress" varchar(255) DEFAULT NULL,
  "redirect_domain" varchar(255) DEFAULT NULL,
  "catchall" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for domaintemplate, a total of 0 rows
----

----
-- Table structure for domaintraffic
----
CREATE TABLE "domaintraffic" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "ddate" varchar(255) DEFAULT NULL,
  "oldtimestamp" varchar(255) DEFAULT NULL,
  "timestamp" varchar(255) DEFAULT NULL,
  "webtraffic_usage" varchar(255) DEFAULT NULL,
  "mailtraffic_usage" varchar(255) DEFAULT NULL,
  "ftptraffic_usage" varchar(255) DEFAULT NULL,
  "traffic_usage" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for domaintraffic, a total of 0 rows
----

----
-- Table structure for driver
----
CREATE TABLE "driver" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "ser_driver_b" longtext,
  PRIMARY KEY ("nname")
);

----
-- Data dump for driver, a total of 0 rows
----

----
-- Table structure for firewall
----
CREATE TABLE "firewall" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "status" varchar(255) DEFAULT NULL,
  "id" varchar(255) DEFAULT NULL,
  "syncserver" varchar(255) DEFAULT NULL,
  "from_address" varchar(255) DEFAULT NULL,
  "from_port" varchar(255) DEFAULT NULL,
  "to_address" varchar(255) DEFAULT NULL,
  "to_port" varchar(255) DEFAULT NULL,
  "action" varchar(255) DEFAULT NULL,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for firewall, a total of 0 rows
----

----
-- Table structure for ftpuser
----
CREATE TABLE "ftpuser" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "priv_q_logo_manage_flag" varchar(255) DEFAULT NULL,
  "used_q_logo_manage_flag" varchar(255) DEFAULT NULL,
  "realname" varchar(255) DEFAULT NULL,
  "add_address" varchar(255) DEFAULT NULL,
  "add_city" varchar(255) DEFAULT NULL,
  "add_country" varchar(255) DEFAULT NULL,
  "add_telephone" varchar(255) DEFAULT NULL,
  "add_fax" varchar(255) DEFAULT NULL,
  "password" varchar(255) DEFAULT NULL,
  "cpstatus" varchar(255) DEFAULT NULL,
  "demo_status" varchar(255) DEFAULT NULL,
  "contactemail" varchar(255) DEFAULT NULL,
  "text_comment" longtext,
  "disable_per" varchar(255) DEFAULT NULL,
  "ser_hpfilter" longtext,
  "ddate" varchar(255) DEFAULT NULL,
  "ser_dskhistory" longtext,
  "ser_dskshortcut_a" longtext,
  "interface_template" varchar(255) DEFAULT NULL,
  "ser_boxpos" longtext,
  "dialogsize" varchar(255) DEFAULT NULL,
  "state" varchar(255) DEFAULT NULL,
  "status" varchar(255) DEFAULT NULL,
  "disable_reason" varchar(255) DEFAULT NULL,
  "realpass" varchar(255) DEFAULT NULL,
  "username" varchar(255) DEFAULT NULL,
  "directory" varchar(255) DEFAULT NULL,
  "syncserver" varchar(255) DEFAULT NULL,
  "ftp_disk_usage" varchar(255) DEFAULT NULL,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for ftpuser, a total of 0 rows
----

----
-- Table structure for general
----
CREATE TABLE "general" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "ser_generalmisc_b" longtext,
  "ser_helpdeskcategory_a" longtext,
  "ser_reversedns_b" longtext,
  "ser_selfbackupparam_b" longtext,
  "ser_hackbuttonconfig_b" longtext,
  "ser_customaction_b" longtext,
  "text_maintenance_message" longtext,
  "ser_portconfig_b" longtext,
  "ser_kloxoconfig_b" longtext,
  "ser_browsebackup_b" longtext,
  "login_pre" varchar(255) DEFAULT NULL,
  "ser_lxadminconfig_b" longtext,
  "disable_admin" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for general, a total of 0 rows
----

----
-- Table structure for genlist
----
CREATE TABLE "genlist" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "ser_dirindexlist_a" longtext,
  PRIMARY KEY ("nname")
);

----
-- Data dump for genlist, a total of 0 rows
----

----
-- Table structure for hostdeny
----
CREATE TABLE "hostdeny" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "syncserver" varchar(255) DEFAULT NULL,
  "hostname" varchar(255) DEFAULT NULL,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for hostdeny, a total of 0 rows
----

----
-- Table structure for installsoft
----
CREATE TABLE "installsoft" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "appname" varchar(255) DEFAULT NULL,
  "ddate" varchar(255) DEFAULT NULL,
  "dbprefix" varchar(255) DEFAULT NULL,
  "dbname" varchar(255) DEFAULT NULL,
  "installdir" varchar(255) DEFAULT NULL,
  "version" varchar(255) DEFAULT NULL,
  "dbhost" varchar(255) DEFAULT NULL,
  "realhost" varchar(255) DEFAULT NULL,
  "ser_installsoftmisc_b" longtext,
  "syncserver" varchar(255) DEFAULT NULL,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for installsoft, a total of 0 rows
----

----
-- Table structure for interface_template
----
CREATE TABLE "interface_template" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "ser_domain_show" text,
  "ser_client_show" text,
  "ser_vps_show" text,
  "ser_domain_show_list" text,
  "ser_client_show_list" text,
  "ser_vps_show_list" text,
  PRIMARY KEY ("nname")
);

----
-- Data dump for interface_template, a total of 0 rows
----

----
-- Table structure for ipaddress
----
CREATE TABLE "ipaddress" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "devname" varchar(255) DEFAULT NULL,
  "bproto" varchar(255) DEFAULT NULL,
  "ipaddr" varchar(255) DEFAULT NULL,
  "client_num" varchar(255) DEFAULT NULL,
  "shared" varchar(255) DEFAULT NULL,
  "netmask" varchar(255) DEFAULT NULL,
  "status" varchar(255) DEFAULT NULL,
  "userctl" varchar(255) DEFAULT NULL,
  "peerdns" varchar(255) DEFAULT NULL,
  "gateway" varchar(255) DEFAULT NULL,
  "itype" varchar(255) DEFAULT NULL,
  "ipv6init" varchar(255) DEFAULT NULL,
  "syncserver" varchar(255) DEFAULT NULL,
  "clientname" varchar(255) DEFAULT NULL,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for ipaddress, a total of 0 rows
----

----
-- Table structure for jailed
----
CREATE TABLE "jailed" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "enable_jailed" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for jailed, a total of 0 rows
----

----
-- Table structure for license
----
CREATE TABLE "license" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "ser_licensecom_b" longtext,
  "text_license_content" longtext,
  PRIMARY KEY ("nname")
);

----
-- Data dump for license, a total of 0 rows
----

----
-- Table structure for llog
----
CREATE TABLE "llog" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "syncserver" varchar(255) DEFAULT NULL,
  "email" varchar(255) DEFAULT NULL,
  "period" varchar(255) DEFAULT NULL,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for llog, a total of 0 rows
----

----
-- Table structure for loginattempt
----
CREATE TABLE "loginattempt" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "count" varchar(255) DEFAULT NULL,
  "client_name" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for loginattempt, a total of 0 rows
----

----
-- Table structure for lxbackup
----
CREATE TABLE "lxbackup" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "priv_q_backupschedule_flag" varchar(255) DEFAULT NULL,
  "used_q_backupschedule_flag" varchar(255) DEFAULT NULL,
  "ftp_server" varchar(255) DEFAULT NULL,
  "ssh_server" varchar(255) DEFAULT NULL,
  "rm_username" varchar(255) DEFAULT NULL,
  "rm_password" varchar(255) DEFAULT NULL,
  "rm_directory" varchar(255) DEFAULT NULL,
  "upload_type" varchar(255) DEFAULT NULL,
  "send_email" varchar(255) DEFAULT NULL,
  "upload_to_ftp" varchar(255) DEFAULT NULL,
  "backupstage" varchar(255) DEFAULT NULL,
  "backuptype" varchar(255) DEFAULT NULL,
  "backupschedule_type" varchar(255) DEFAULT NULL,
  "rm_last_number" varchar(255) DEFAULT NULL,
  "ser_lxbackupmisc_b" longtext,
  "restorestage" varchar(255) DEFAULT NULL,
  "no_local_copy_flag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for lxbackup, a total of 0 rows
----

----
-- Table structure for lxguard
----
CREATE TABLE "lxguard" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "configure_flag" varchar(255) DEFAULT NULL,
  "disablehit" varchar(255) DEFAULT NULL,
  "syncserver" varchar(255) DEFAULT NULL,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for lxguard, a total of 0 rows
----

----
-- Table structure for lxguardhit
----
CREATE TABLE "lxguardhit" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "access" varchar(255) DEFAULT NULL,
  "service" varchar(255) DEFAULT NULL,
  "ddate" varchar(255) DEFAULT NULL,
  "ipaddress" varchar(255) DEFAULT NULL,
  "user" varchar(255) DEFAULT NULL,
  "syncserver" varchar(255) DEFAULT NULL,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for lxguardhit, a total of 0 rows
----

----
-- Table structure for lxguardwhitelist
----
CREATE TABLE "lxguardwhitelist" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "ipaddress" varchar(255) DEFAULT NULL,
  "syncserver" varchar(255) DEFAULT NULL,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for lxguardwhitelist, a total of 0 rows
----

----
-- Table structure for lxupdate
----
CREATE TABLE "lxupdate" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "schedule" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for lxupdate, a total of 0 rows
----

----
-- Table structure for mailaccount
----
CREATE TABLE "mailaccount" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "priv_q_logo_manage_flag" varchar(255) DEFAULT NULL,
  "used_q_logo_manage_flag" varchar(255) DEFAULT NULL,
  "priv_q_maildisk_usage" varchar(255) DEFAULT NULL,
  "used_q_maildisk_usage" varchar(255) DEFAULT NULL,
  "priv_q_autoresponder_num" varchar(255) DEFAULT NULL,
  "used_q_autoresponder_num" varchar(255) DEFAULT NULL,
  "realname" varchar(255) DEFAULT NULL,
  "add_address" varchar(255) DEFAULT NULL,
  "add_city" varchar(255) DEFAULT NULL,
  "add_country" varchar(255) DEFAULT NULL,
  "add_telephone" varchar(255) DEFAULT NULL,
  "add_fax" varchar(255) DEFAULT NULL,
  "password" varchar(255) DEFAULT NULL,
  "cpstatus" varchar(255) DEFAULT NULL,
  "demo_status" varchar(255) DEFAULT NULL,
  "contactemail" varchar(255) DEFAULT NULL,
  "text_comment" longtext,
  "disable_per" varchar(255) DEFAULT NULL,
  "ser_hpfilter" longtext,
  "ddate" varchar(255) DEFAULT NULL,
  "ser_dskhistory" longtext,
  "ser_dskshortcut_a" longtext,
  "interface_template" varchar(255) DEFAULT NULL,
  "ser_boxpos" longtext,
  "dialogsize" varchar(255) DEFAULT NULL,
  "state" varchar(255) DEFAULT NULL,
  "status" varchar(255) DEFAULT NULL,
  "disable_reason" varchar(255) DEFAULT NULL,
  "realpass" varchar(255) DEFAULT NULL,
  "syncserver" varchar(255) DEFAULT NULL,
  "forward_status" varchar(255) DEFAULT NULL,
  "ser_forward_a" longtext,
  "autorespond_status" varchar(255) DEFAULT NULL,
  "autores_name" varchar(255) DEFAULT NULL,
  "filter_spam_status" varchar(255) DEFAULT NULL,
  "no_local_copy" varchar(255) DEFAULT NULL,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for mailaccount, a total of 0 rows
----

----
-- Table structure for mailfilter
----
CREATE TABLE "mailfilter" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "rule" varchar(255) DEFAULT NULL,
  "action" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for mailfilter, a total of 0 rows
----

----
-- Table structure for mailforward
----
CREATE TABLE "mailforward" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "accountname" varchar(255) DEFAULT NULL,
  "forwardaddress" varchar(255) DEFAULT NULL,
  "syncserver" varchar(255) DEFAULT NULL,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for mailforward, a total of 0 rows
----

----
-- Table structure for mailinglist
----
CREATE TABLE "mailinglist" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "syncserver" varchar(255) DEFAULT NULL,
  "listname" varchar(255) DEFAULT NULL,
  "adminemail" varchar(255) DEFAULT NULL,
  "lang" varchar(255) DEFAULT NULL,
  "post_members_only_flag" varchar(255) DEFAULT NULL,
  "post_moderated_flag" varchar(255) DEFAULT NULL,
  "post_moderator_only_flag" varchar(255) DEFAULT NULL,
  "archived_flag" varchar(255) DEFAULT NULL,
  "archive_blocked_flag" varchar(255) DEFAULT NULL,
  "archive_guarded_flag" varchar(255) DEFAULT NULL,
  "digest_flag" varchar(255) DEFAULT NULL,
  "jumpoff_flag" varchar(255) DEFAULT NULL,
  "subscriberlist_flag" varchar(255) DEFAULT NULL,
  "remote_admin_flag" varchar(255) DEFAULT NULL,
  "subscription_mod_flag" varchar(255) DEFAULT NULL,
  "edit_text_flag" varchar(255) DEFAULT NULL,
  "coma_mailinglist_mod_a" text,
  "text_trailer" longtext,
  "text_prefix" longtext,
  "max_msg_size" varchar(255) DEFAULT NULL,
  "min_msg_size" varchar(255) DEFAULT NULL,
  "text_mimeremove" longtext,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for mailinglist, a total of 0 rows
----

----
-- Table structure for mimetype
----
CREATE TABLE "mimetype" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "domainname" varchar(255) DEFAULT NULL,
  "type" varchar(255) DEFAULT NULL,
  "extension" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for mimetype, a total of 0 rows
----

----
-- Table structure for mmail
----
CREATE TABLE "mmail" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "priv_q_logo_manage_flag" varchar(255) DEFAULT NULL,
  "used_q_logo_manage_flag" varchar(255) DEFAULT NULL,
  "priv_q_maildisk_usage" varchar(255) DEFAULT NULL,
  "used_q_maildisk_usage" varchar(255) DEFAULT NULL,
  "priv_q_autoresponder_num" varchar(255) DEFAULT NULL,
  "used_q_autoresponder_num" varchar(255) DEFAULT NULL,
  "priv_q_mailaccount_num" varchar(255) DEFAULT NULL,
  "used_q_mailaccount_num" varchar(255) DEFAULT NULL,
  "priv_q_mailinglist_num" varchar(255) DEFAULT NULL,
  "used_q_mailinglist_num" varchar(255) DEFAULT NULL,
  "webmailprog" varchar(255) DEFAULT NULL,
  "catchall" varchar(255) DEFAULT NULL,
  "remotelocalflag" varchar(255) DEFAULT NULL,
  "catchall_status" varchar(255) DEFAULT NULL,
  "username" varchar(255) DEFAULT NULL,
  "status" varchar(255) DEFAULT NULL,
  "redirect_address" varchar(255) DEFAULT NULL,
  "syncserver" varchar(255) DEFAULT NULL,
  "ttype" varchar(255) DEFAULT NULL,
  "redirect_domain" varchar(255) DEFAULT NULL,
  "webmail_url" varchar(255) DEFAULT NULL,
  "systemuser" varchar(255) DEFAULT NULL,
  "enable_spf_flag" varchar(255) DEFAULT NULL,
  "text_spf_include" varchar(255) DEFAULT NULL,
  "exclude_all" varchar(255) DEFAULT NULL,
  "text_spf_domain" longtext,
  "enable_spf_autoip" varchar(255) DEFAULT NULL,
  "text_spf_ip" longtext,
  "spf_protocol" varchar(255) DEFAULT NULL,
  "enable_dmarc_flag" varchar(255) DEFAULT NULL,
  "percentage_filtering" varchar(255) DEFAULT NULL,
  "receiver_policy" varchar(255) DEFAULT NULL,
  "mail_feedback" varchar(255) DEFAULT NULL,
  "dmarc_protocol" varchar(255) DEFAULT NULL,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for mmail, a total of 0 rows
----

----
-- Table structure for module
----
CREATE TABLE "module" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "status" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for module, a total of 0 rows
----

----
-- Table structure for mssqldb
----
CREATE TABLE "mssqldb" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "priv_q_mssqldb_usage" varchar(255) DEFAULT NULL,
  "used_q_mssqldb_usage" varchar(255) DEFAULT NULL,
  "username" varchar(255) DEFAULT NULL,
  "dbname" varchar(255) DEFAULT NULL,
  "dbtype" varchar(255) DEFAULT NULL,
  "dbpassword" varchar(255) DEFAULT NULL,
  "installsoft_flag" varchar(255) DEFAULT NULL,
  "installsoft_app" varchar(255) DEFAULT NULL,
  "syncserver" varchar(255) DEFAULT NULL,
  "password" varchar(255) DEFAULT NULL,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for mssqldb, a total of 0 rows
----

----
-- Table structure for mssqldbuser
----
CREATE TABLE "mssqldbuser" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "username" varchar(255) DEFAULT NULL,
  "dbname" varchar(255) DEFAULT NULL,
  "dbpassword" varchar(255) DEFAULT NULL,
  "ser_dbpermission_b" longtext,
  "syncserver" varchar(255) DEFAULT NULL,
  "ser_dbhostlist_a" longtext,
  "password" varchar(255) DEFAULT NULL,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for mssqldbuser, a total of 0 rows
----

----
-- Table structure for mysqldb
----
CREATE TABLE "mysqldb" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "priv_q_mysqldb_usage" varchar(255) DEFAULT NULL,
  "used_q_mysqldb_usage" varchar(255) DEFAULT NULL,
  "primarydb" varchar(255) DEFAULT NULL,
  "username" varchar(255) DEFAULT NULL,
  "dbname" varchar(255) DEFAULT NULL,
  "dbtype" varchar(255) DEFAULT NULL,
  "dbpassword" varchar(255) DEFAULT NULL,
  "installsoft_flag" varchar(255) DEFAULT NULL,
  "installsoft_app" varchar(255) DEFAULT NULL,
  "syncserver" varchar(255) DEFAULT NULL,
  "password" varchar(255) DEFAULT NULL,
  "no_backup_flag" varchar(255) DEFAULT NULL,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for mysqldb, a total of 0 rows
----

----
-- Table structure for mysqldbuser
----
CREATE TABLE "mysqldbuser" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "username" varchar(255) DEFAULT NULL,
  "dbname" varchar(255) DEFAULT NULL,
  "dbpassword" varchar(255) DEFAULT NULL,
  "ser_dbpermission_b" longtext,
  "syncserver" varchar(255) DEFAULT NULL,
  "ser_dbhostlist_a" longtext,
  "password" varchar(255) DEFAULT NULL,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for mysqldbuser, a total of 0 rows
----

----
-- Table structure for ndskshortcut
----
CREATE TABLE "ndskshortcut" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "ddate" varchar(255) DEFAULT NULL,
  "ttype" varchar(255) DEFAULT NULL,
  "sortid" varchar(255) DEFAULT NULL,
  "separatorid" varchar(255) DEFAULT NULL,
  "description" varchar(255) DEFAULT NULL,
  "external" varchar(255) DEFAULT NULL,
  "vpsparent_clname" varchar(255) DEFAULT NULL,
  "url" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for ndskshortcut, a total of 0 rows
----

----
-- Table structure for ndsktoolbar
----
CREATE TABLE "ndsktoolbar" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "ddate" varchar(255) DEFAULT NULL,
  "ttype" varchar(255) DEFAULT NULL,
  "description" varchar(255) DEFAULT NULL,
  "external" varchar(255) DEFAULT NULL,
  "vpsparent_clname" varchar(255) DEFAULT NULL,
  "url" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for ndsktoolbar, a total of 0 rows
----

----
-- Table structure for notification
----
CREATE TABLE "notification" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "ser_notflag_b" longtext,
  "text_newsubject" longtext,
  "text_newaccountmessage" longtext,
  "fromaddress" varchar(255) DEFAULT NULL,
  "coma_class_list" text,
  PRIMARY KEY ("nname")
);

----
-- Data dump for notification, a total of 0 rows
----

----
-- Table structure for odbc
----
CREATE TABLE "odbc" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "odbcname" varchar(255) DEFAULT NULL,
  "description" varchar(255) DEFAULT NULL,
  "driver" varchar(255) DEFAULT NULL,
  "ser_odbcdetails_b" longtext,
  "syncserver" varchar(255) DEFAULT NULL,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for odbc, a total of 0 rows
----

----
-- Table structure for phpini
----
CREATE TABLE "phpini" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "php_manage_flag" varchar(255) DEFAULT NULL,
  "enable_zend_flag" varchar(255) DEFAULT NULL,
  "enable_ioncube_flag" varchar(255) DEFAULT NULL,
  "register_global_flag" varchar(255) DEFAULT NULL,
  "syncserver" varchar(255) DEFAULT NULL,
  "display_error_flag" varchar(255) DEFAULT NULL,
  "ser_phpini_flag_b" longtext,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for phpini, a total of 0 rows
----

----
-- Table structure for proxy
----
CREATE TABLE "proxy" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  PRIMARY KEY ("nname")
);

----
-- Data dump for proxy, a total of 0 rows
----

----
-- Table structure for proxyacl
----
CREATE TABLE "proxyacl" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "syncserver" varchar(255) DEFAULT NULL,
  "status" varchar(255) DEFAULT NULL,
  "ttype" varchar(255) DEFAULT NULL,
  "classid" varchar(255) DEFAULT NULL,
  "description" varchar(255) DEFAULT NULL,
  "password" varchar(255) DEFAULT NULL,
  "http" varchar(255) DEFAULT NULL,
  "ftp" varchar(255) DEFAULT NULL,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for proxyacl, a total of 0 rows
----

----
-- Table structure for pserver
----
CREATE TABLE "pserver" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "priv_q_logo_manage_flag" varchar(255) DEFAULT NULL,
  "used_q_logo_manage_flag" varchar(255) DEFAULT NULL,
  "realname" varchar(255) DEFAULT NULL,
  "add_address" varchar(255) DEFAULT NULL,
  "add_city" varchar(255) DEFAULT NULL,
  "add_country" varchar(255) DEFAULT NULL,
  "add_telephone" varchar(255) DEFAULT NULL,
  "add_fax" varchar(255) DEFAULT NULL,
  "password" varchar(255) DEFAULT NULL,
  "cpstatus" varchar(255) DEFAULT NULL,
  "demo_status" varchar(255) DEFAULT NULL,
  "contactemail" varchar(255) DEFAULT NULL,
  "text_comment" longtext,
  "disable_per" varchar(255) DEFAULT NULL,
  "ser_hpfilter" longtext,
  "ddate" varchar(255) DEFAULT NULL,
  "ser_dskhistory" longtext,
  "ser_dskshortcut_a" longtext,
  "interface_template" varchar(255) DEFAULT NULL,
  "ser_boxpos" longtext,
  "dialogsize" varchar(255) DEFAULT NULL,
  "ostype" varchar(255) DEFAULT NULL,
  "osversion" varchar(255) DEFAULT NULL,
  "dbadmin" varchar(255) DEFAULT NULL,
  "dbpassword" varchar(255) DEFAULT NULL,
  "realpass" varchar(255) DEFAULT NULL,
  "username" varchar(255) DEFAULT NULL,
  "ser_rolelist" longtext,
  "cron_mailto" varchar(255) DEFAULT NULL,
  "syncserver" varchar(255) DEFAULT NULL,
  "ser_pserverconf_b" longtext,
  "hostname" varchar(255) DEFAULT NULL,
  "description" varchar(255) DEFAULT NULL,
  "realhostname" varchar(255) DEFAULT NULL,
  "timezone" varchar(255) DEFAULT NULL,
  "coma_psrole_a" text,
  "load_threshold" varchar(255) DEFAULT NULL,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for pserver, a total of 0 rows
----

----
-- Table structure for rdnsrange
----
CREATE TABLE "rdnsrange" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "firstip" varchar(255) DEFAULT NULL,
  "lastip" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for rdnsrange, a total of 0 rows
----

----
-- Table structure for resourceplan
----
CREATE TABLE "resourceplan" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "realname" varchar(255) DEFAULT NULL,
  "ser_priv" longtext,
  "description" varchar(255) DEFAULT NULL,
  "ddate" varchar(255) DEFAULT NULL,
  "ser_listpriv" longtext,
  "ser_dnstemplate_list" longtext,
  "status" varchar(255) DEFAULT NULL,
  "disable_per" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for resourceplan, a total of 0 rows
----

----
-- Table structure for reversedns
----
CREATE TABLE "reversedns" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "reversename" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for reversedns, a total of 0 rows
----

----
-- Table structure for rubyrails
----
CREATE TABLE "rubyrails" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "priv_q_rubyfcgiprocess_num" varchar(255) DEFAULT NULL,
  "used_q_rubyfcgiprocess_num" varchar(255) DEFAULT NULL,
  "appname" varchar(255) DEFAULT NULL,
  "port" varchar(255) DEFAULT NULL,
  "syncserver" varchar(255) DEFAULT NULL,
  "accessible_directly" varchar(255) DEFAULT NULL,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for rubyrails, a total of 0 rows
----

----
-- Table structure for serverftp
----
CREATE TABLE "serverftp" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "maxclient" varchar(255) DEFAULT NULL,
  "highport" varchar(255) DEFAULT NULL,
  "lowport" varchar(255) DEFAULT NULL,
  "enable_anon_ftp" varchar(255) DEFAULT NULL,
  "defaultport" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for serverftp, a total of 0 rows
----

----
-- Table structure for servermail
----
CREATE TABLE "servermail" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "smtp_instance" varchar(255) DEFAULT NULL,
  "enable_maps" varchar(255) DEFAULT NULL,
  "domainkey_flag" varchar(255) DEFAULT NULL,
  "additional_smtp_port" varchar(255) DEFAULT NULL,
  "queuelifetime" varchar(255) DEFAULT NULL,
  "concurrencyremote" varchar(255) DEFAULT NULL,
  "spamdyke_flag" varchar(255) DEFAULT NULL,
  "syncserver" varchar(255) DEFAULT NULL,
  "myname" varchar(255) DEFAULT NULL,
  "virus_scan_flag" varchar(255) DEFAULT NULL,
  "max_size" varchar(255) DEFAULT NULL,
  "greet_delay" varchar(255) DEFAULT NULL,
  "graylist_flag" varchar(255) DEFAULT NULL,
  "graylist_max_secs" varchar(255) DEFAULT NULL,
  "graylist_min_secs" varchar(255) DEFAULT NULL,
  "coma_mail_graylist_wlist_a" text,
  "max_rcpnts" varchar(255) DEFAULT NULL,
  "reject_unresolvable_rdns_flag" varchar(255) DEFAULT NULL,
  "reject_missing_sender_mx_flag" varchar(255) DEFAULT NULL,
  "reject_ip_in_cc_rdns_flag" varchar(255) DEFAULT NULL,
  "reject_empty_rdns_flag" varchar(255) DEFAULT NULL,
  "dns_blacklists" varchar(255) DEFAULT NULL,
  "alt_smtp_sdyke_flag" varchar(255) DEFAULT NULL,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for servermail, a total of 0 rows
----

----
-- Table structure for serverspam
----
CREATE TABLE "serverspam" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "status" varchar(255) DEFAULT NULL,
  "spam_hit" varchar(255) DEFAULT NULL,
  "subject_tag" varchar(255) DEFAULT NULL,
  "ser_wlist_a" longtext,
  "ser_blist_a" longtext,
  "syncserver" varchar(255) DEFAULT NULL,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for serverspam, a total of 0 rows
----

----
-- Table structure for serverweb
----
CREATE TABLE "serverweb" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "php_type" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for serverweb, a total of 0 rows
----

----
-- Table structure for service
----
CREATE TABLE "service" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "servicename" varchar(255) DEFAULT NULL,
  "description" varchar(255) DEFAULT NULL,
  "grepstring" varchar(255) DEFAULT NULL,
  "syncserver" varchar(255) DEFAULT NULL,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for service, a total of 18 rows
----
INSERT INTO "service" ("nname","parent_clname","parent_cmlist","servicename","description","grepstring","syncserver","oldsyncserver","olddeleteflag") VALUES ('qmail___localhost','pserver-localhost','','qmail','Qmail-toaster Mail Server','qmail','localhost','','');
INSERT INTO "service" ("nname","parent_clname","parent_cmlist","servicename","description","grepstring","syncserver","oldsyncserver","olddeleteflag") VALUES ('named___localhost','pserver-localhost','','named','Bind Dns Server','named','localhost','','');
INSERT INTO "service" ("nname","parent_clname","parent_cmlist","servicename","description","grepstring","syncserver","oldsyncserver","olddeleteflag") VALUES ('djbdns___localhost','pserver-localhost','','djbdns','DjbDns Dns Server','tinydns','localhost','','');
INSERT INTO "service" ("nname","parent_clname","parent_cmlist","servicename","description","grepstring","syncserver","oldsyncserver","olddeleteflag") VALUES ('pdns___localhost','pserver-localhost','','pdns','PowerDNS Dns Server','pdns','localhost','','');
INSERT INTO "service" ("nname","parent_clname","parent_cmlist","servicename","description","grepstring","syncserver","oldsyncserver","olddeleteflag") VALUES ('nsd___localhost','pserver-localhost','','nsd','NSD Dns Server','nsd','localhost','','');
INSERT INTO "service" ("nname","parent_clname","parent_cmlist","servicename","description","grepstring","syncserver","oldsyncserver","olddeleteflag") VALUES ('mydns___localhost','pserver-localhost','','mydns','MyDNS Dns Server','mydns','localhost','','');
INSERT INTO "service" ("nname","parent_clname","parent_cmlist","servicename","description","grepstring","syncserver","oldsyncserver","olddeleteflag") VALUES ('yadifa___localhost','pserver-localhost','','yadifad','YADIFA Dns Server','yadifad','localhost','','');
INSERT INTO "service" ("nname","parent_clname","parent_cmlist","servicename","description","grepstring","syncserver","oldsyncserver","olddeleteflag") VALUES ('php-fpm___localhost','pserver-localhost','','php-fpm','Php Fastcgi Process Manager','php-fpm','localhost','','');
INSERT INTO "service" ("nname","parent_clname","parent_cmlist","servicename","description","grepstring","syncserver","oldsyncserver","olddeleteflag") VALUES ('httpd___localhost','pserver-localhost','','httpd','Apache Web Server','httpd','localhost','','');
INSERT INTO "service" ("nname","parent_clname","parent_cmlist","servicename","description","grepstring","syncserver","oldsyncserver","olddeleteflag") VALUES ('lighttpd___localhost','pserver-localhost','','lighttpd','Lighttpd Web Server','lighttpd','localhost','','');
INSERT INTO "service" ("nname","parent_clname","parent_cmlist","servicename","description","grepstring","syncserver","oldsyncserver","olddeleteflag") VALUES ('nginx___localhost','pserver-localhost','','nginx','Nginx Web Server','nginx','localhost','','');
INSERT INTO "service" ("nname","parent_clname","parent_cmlist","servicename","description","grepstring","syncserver","oldsyncserver","olddeleteflag") VALUES ('hiawatha___localhost','pserver-localhost','','hiawatha','Hiawatha Web Server (use by Kloxo-MR)','hiawatha','localhost','','');
INSERT INTO "service" ("nname","parent_clname","parent_cmlist","servicename","description","grepstring","syncserver","oldsyncserver","olddeleteflag") VALUES ('varnish___localhost','pserver-localhost','','varnish','Varnish Web Cache','varnish','localhost','','');
INSERT INTO "service" ("nname","parent_clname","parent_cmlist","servicename","description","grepstring","syncserver","oldsyncserver","olddeleteflag") VALUES ('squid___localhost','pserver-localhost','','squid','Squid Web Cache','squid','localhost','','');
INSERT INTO "service" ("nname","parent_clname","parent_cmlist","servicename","description","grepstring","syncserver","oldsyncserver","olddeleteflag") VALUES ('trafficserver___localhost','pserver-localhost','','trafficserver','Apache Traffic Server Web Cache','trafficserver','localhost','','');
INSERT INTO "service" ("nname","parent_clname","parent_cmlist","servicename","description","grepstring","syncserver","oldsyncserver","olddeleteflag") VALUES ('mysql___localhost','pserver-localhost','','mysqld','MySQL Database','mysqld','localhost','','');
INSERT INTO "service" ("nname","parent_clname","parent_cmlist","servicename","description","grepstring","syncserver","oldsyncserver","olddeleteflag") VALUES ('mariadb___localhost','pserver-localhost','','mysql','MariaDB Database','mysql','localhost','','');
INSERT INTO "service" ("nname","parent_clname","parent_cmlist","servicename","description","grepstring","syncserver","oldsyncserver","olddeleteflag") VALUES ('iptables___localhost','pserver-localhost','','iptables','IPTables Firewall','iptables','localhost','','');

----
-- Table structure for skipbackup
----
CREATE TABLE "skipbackup" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "clname" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for skipbackup, a total of 0 rows
----

----
-- Table structure for smessage
----
CREATE TABLE "smessage" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "made_by" varchar(255) DEFAULT NULL,
  "text_readby_cmlist" longtext,
  "text_sent_to_cmlist" longtext,
  "subject" varchar(255) DEFAULT NULL,
  "text_description" longtext,
  "category" varchar(255) DEFAULT NULL,
  "ddate" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for smessage, a total of 0 rows
----

----
-- Table structure for sp_childspecialplay
----
CREATE TABLE "sp_childspecialplay" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "ser_specialplay_b" longtext,
  PRIMARY KEY ("nname")
);

----
-- Data dump for sp_childspecialplay, a total of 6 rows
----
INSERT INTO "sp_childspecialplay" ("nname","parent_clname","parent_cmlist","ser_specialplay_b") VALUES ('client-admin','client-admin','','TzoxMzoiU3BlY2lhbFBsYXlfYiI6NjM6e3M6NToibm5hbWUiO3M6MTI6ImNsaWVudC1hZG1pbiI7czoxMToiX19saXN0X2xpc3QiO047czoxNDoiX192aXJ0dWFsX2xpc3QiO2E6MDp7fXM6MTM6Il9fb2JqZWN0X2xpc3QiO047czo5OiJzdWJhY3Rpb24iO047czo4OiJkYmFjdGlvbiI7czozOiJhZGQiO3M6MTI6Im1ldGFkYmFjdGlvbiI7czozOiJhbGwiO3M6NzoiX19jbGFzcyI7czoxMzoic3BlY2lhbHBsYXlfYiI7czo2OiJjdHR5cGUiO3M6MTM6InNwZWNpYWxwbGF5X2IiO3M6MTQ6Il9fbWFzdGVyc2VydmVyIjtOO3M6MTI6Il9fcmVhZHNlcnZlciI7czo5OiJsb2NhbGhvc3QiO3M6MTA6InN5bmNzZXJ2ZXIiO3M6OToibG9jYWxob3N0IjtzOjExOiJkZW1vX3N0YXR1cyI7TjtzOjE1OiJkZW1vX3N0YXR1c19vZmYiO047czoxNDoiZGVtb19zdGF0dXNfb24iO047czo5OiJza2luX25hbWUiO3M6MTA6InNpbXBsaWNpdHkiO3M6MTA6InNraW5fY29sb3IiO3M6NzoiZGVmYXVsdCI7czoxNToic2tpbl9iYWNrZ3JvdW5kIjtzOjE0OiJuYXR1cmVfMDA0LmpwZyI7czoxNDoic2hvd19kaXJlY3Rpb24iO3M6ODoidmVydGljYWwiO3M6MTE6ImJ1dHRvbl90eXBlIjtzOjQ6ImZvbnQiO3M6OToiaWNvbl9uYW1lIjtzOjc6ImNvbGxhZ2UiO3M6MTA6ImxvZ29faW1hZ2UiO047czoxMDoibG9naW5fcGFnZSI7TjtzOjE4OiJsb2dvX2ltYWdlX2xvYWRpbmciO047czoxNjoic2hvd19xdWlja2FjdGlvbiI7TjtzOjE5OiJkaXNhYmxlX3F1aWNrYWN0aW9uIjtOO3M6MTA6InNob3dfbmF2aWciO3M6Mjoib24iO3M6MTE6InVsdHJhX25hdmlnIjtzOjM6Im9mZiI7czoxMToic3BsaXRfZnJhbWUiO047czoxNDoiY2xvc2VfYWRkX2Zvcm0iO047czoxNDoiZGlzYWJsZWlwY2hlY2siO047czoxMjoiZGlzYWJsZV9hamF4IjtOO3M6MTE6ImVuYWJsZV9hamF4IjtOO3M6MTE6ImNwYW5lbF9za2luIjtOO3M6MTE6InNpbXBsZV9za2luIjtOO3M6MTY6InNob3dfdGhpbl9oZWFkZXIiO047czoxNToicmVzb3VyY2VfYm90dG9tIjtzOjM6Im9mZiI7czoxODoiaW50ZXJmYWNlX3RlbXBsYXRlIjtOO3M6MTg6InNob3dfYnJldGhyZW5fbGlzdCI7czozOiJ0b3AiO3M6Mjk6ImRvbnRfc2hvd19kaXNhYmxlZF9wZXJtaXNzaW9uIjtOO3M6ODoibGFuZ3VhZ2UiO3M6NToiZW4tdXMiO3M6MTE6Imxhbmd1YWdlX2VuIjtOO3M6MTE6InNob3dfbHBhbmVsIjtzOjI6Im9uIjtzOjE1OiJkaXNhYmxlX2RvY3Jvb3QiO047czoxNzoiY3VzdG9tZXJtb2RlX2ZsYWciO047czo5OiJzaG93X2hlbHAiO3M6Mjoib24iO3M6MTY6InNzZXNzaW9uX3RpbWVvdXQiO2k6MTgwMDA7czoxNjoic2hvd19hZGRfYnV0dG9ucyI7TjtzOjE2OiJscGFuZWxfc2Nyb2xsYmFyIjtzOjM6Im9mZiI7czoyMToibHBhbmVsX2dyb3VwX3Jlc291cmNlIjtzOjM6Im9mZiI7czoxMjoibHBhbmVsX2RlcHRoIjtpOjM7czo4OiJwZXJfcGFnZSI7czoyOiIxMCI7czoxODoicGFyZW50X25hbWVfY2hhbmdlIjtOO3M6MTU6ImNjZW50ZXJfY29tbWFuZCI7TjtzOjE0OiJjY2VudGVyX291dHB1dCI7TjtzOjEzOiJjY2VudGVyX2Vycm9yIjtOO3M6MTM6InBhcmVudF9jbG5hbWUiO047czo1OiJkZGF0ZSI7aToxNDE1MDE0NTI0O3M6MTc6ImZpbHRlcl92aWV3X3F1b3RhIjtOO3M6MTg6ImZpbHRlcl92aWV3X25vcm1hbCI7TjtzOjE2OiJjY2VudGVyX3N0YW5kYXJkIjtOO3M6MTU6ImNjZW50ZXJfcmVzdGFydCI7TjtzOjEwOiJfX29sZF91c2VkIjtOO30=');
INSERT INTO "sp_childspecialplay" ("nname","parent_clname","parent_cmlist","ser_specialplay_b") VALUES ('mailaccount-postmaster@ra1.mratwork.com','client-admin','','TzoxMzoiU3BlY2lhbFBsYXlfYiI6NjM6e3M6NToibm5hbWUiO3M6MTI6ImNsaWVudC1hZG1pbiI7czoxMToiX19saXN0X2xpc3QiO047czoxNDoiX192aXJ0dWFsX2xpc3QiO2E6MDp7fXM6MTM6Il9fb2JqZWN0X2xpc3QiO047czo5OiJzdWJhY3Rpb24iO047czo4OiJkYmFjdGlvbiI7czozOiJhZGQiO3M6MTI6Im1ldGFkYmFjdGlvbiI7czozOiJhbGwiO3M6NzoiX19jbGFzcyI7czoxMzoic3BlY2lhbHBsYXlfYiI7czo2OiJjdHR5cGUiO3M6MTM6InNwZWNpYWxwbGF5X2IiO3M6MTQ6Il9fbWFzdGVyc2VydmVyIjtOO3M6MTI6Il9fcmVhZHNlcnZlciI7czo5OiJsb2NhbGhvc3QiO3M6MTA6InN5bmNzZXJ2ZXIiO3M6OToibG9jYWxob3N0IjtzOjExOiJkZW1vX3N0YXR1cyI7TjtzOjE1OiJkZW1vX3N0YXR1c19vZmYiO047czoxNDoiZGVtb19zdGF0dXNfb24iO047czo5OiJza2luX25hbWUiO3M6MTA6InNpbXBsaWNpdHkiO3M6MTA6InNraW5fY29sb3IiO3M6NzoiZGVmYXVsdCI7czoxNToic2tpbl9iYWNrZ3JvdW5kIjtzOjE0OiJuYXR1cmVfMDA0LmpwZyI7czoxNDoic2hvd19kaXJlY3Rpb24iO3M6ODoidmVydGljYWwiO3M6MTE6ImJ1dHRvbl90eXBlIjtzOjQ6ImZvbnQiO3M6OToiaWNvbl9uYW1lIjtzOjc6ImNvbGxhZ2UiO3M6MTA6ImxvZ29faW1hZ2UiO047czoxMDoibG9naW5fcGFnZSI7TjtzOjE4OiJsb2dvX2ltYWdlX2xvYWRpbmciO047czoxNjoic2hvd19xdWlja2FjdGlvbiI7TjtzOjE5OiJkaXNhYmxlX3F1aWNrYWN0aW9uIjtOO3M6MTA6InNob3dfbmF2aWciO3M6Mjoib24iO3M6MTE6InVsdHJhX25hdmlnIjtzOjM6Im9mZiI7czoxMToic3BsaXRfZnJhbWUiO047czoxNDoiY2xvc2VfYWRkX2Zvcm0iO047czoxNDoiZGlzYWJsZWlwY2hlY2siO047czoxMjoiZGlzYWJsZV9hamF4IjtOO3M6MTE6ImVuYWJsZV9hamF4IjtOO3M6MTE6ImNwYW5lbF9za2luIjtOO3M6MTE6InNpbXBsZV9za2luIjtOO3M6MTY6InNob3dfdGhpbl9oZWFkZXIiO047czoxNToicmVzb3VyY2VfYm90dG9tIjtzOjM6Im9mZiI7czoxODoiaW50ZXJmYWNlX3RlbXBsYXRlIjtOO3M6MTg6InNob3dfYnJldGhyZW5fbGlzdCI7czozOiJ0b3AiO3M6Mjk6ImRvbnRfc2hvd19kaXNhYmxlZF9wZXJtaXNzaW9uIjtOO3M6ODoibGFuZ3VhZ2UiO3M6NToiZW4tdXMiO3M6MTE6Imxhbmd1YWdlX2VuIjtOO3M6MTE6InNob3dfbHBhbmVsIjtzOjI6Im9uIjtzOjE1OiJkaXNhYmxlX2RvY3Jvb3QiO047czoxNzoiY3VzdG9tZXJtb2RlX2ZsYWciO047czo5OiJzaG93X2hlbHAiO3M6Mjoib24iO3M6MTY6InNzZXNzaW9uX3RpbWVvdXQiO2k6MTgwMDA7czoxNjoic2hvd19hZGRfYnV0dG9ucyI7TjtzOjE2OiJscGFuZWxfc2Nyb2xsYmFyIjtzOjM6Im9mZiI7czoyMToibHBhbmVsX2dyb3VwX3Jlc291cmNlIjtzOjM6Im9mZiI7czoxMjoibHBhbmVsX2RlcHRoIjtpOjM7czo4OiJwZXJfcGFnZSI7czoyOiIxMCI7czoxODoicGFyZW50X25hbWVfY2hhbmdlIjtOO3M6MTU6ImNjZW50ZXJfY29tbWFuZCI7TjtzOjE0OiJjY2VudGVyX291dHB1dCI7TjtzOjEzOiJjY2VudGVyX2Vycm9yIjtOO3M6MTM6InBhcmVudF9jbG5hbWUiO047czo1OiJkZGF0ZSI7aToxNDE1MDE0NTI0O3M6MTc6ImZpbHRlcl92aWV3X3F1b3RhIjtOO3M6MTg6ImZpbHRlcl92aWV3X25vcm1hbCI7TjtzOjE2OiJjY2VudGVyX3N0YW5kYXJkIjtOO3M6MTU6ImNjZW50ZXJfcmVzdGFydCI7TjtzOjEwOiJfX29sZF91c2VkIjtOO30=');
INSERT INTO "sp_childspecialplay" ("nname","parent_clname","parent_cmlist","ser_specialplay_b") VALUES ('domain-ra1.mratwork.com','client-admin','','TzoxMzoiU3BlY2lhbFBsYXlfYiI6NjM6e3M6NToibm5hbWUiO3M6MTI6ImNsaWVudC1hZG1pbiI7czoxMToiX19saXN0X2xpc3QiO047czoxNDoiX192aXJ0dWFsX2xpc3QiO2E6MDp7fXM6MTM6Il9fb2JqZWN0X2xpc3QiO047czo5OiJzdWJhY3Rpb24iO047czo4OiJkYmFjdGlvbiI7czozOiJhZGQiO3M6MTI6Im1ldGFkYmFjdGlvbiI7czozOiJhbGwiO3M6NzoiX19jbGFzcyI7czoxMzoic3BlY2lhbHBsYXlfYiI7czo2OiJjdHR5cGUiO3M6MTM6InNwZWNpYWxwbGF5X2IiO3M6MTQ6Il9fbWFzdGVyc2VydmVyIjtOO3M6MTI6Il9fcmVhZHNlcnZlciI7czo5OiJsb2NhbGhvc3QiO3M6MTA6InN5bmNzZXJ2ZXIiO3M6OToibG9jYWxob3N0IjtzOjExOiJkZW1vX3N0YXR1cyI7TjtzOjE1OiJkZW1vX3N0YXR1c19vZmYiO047czoxNDoiZGVtb19zdGF0dXNfb24iO047czo5OiJza2luX25hbWUiO3M6MTA6InNpbXBsaWNpdHkiO3M6MTA6InNraW5fY29sb3IiO3M6NzoiZGVmYXVsdCI7czoxNToic2tpbl9iYWNrZ3JvdW5kIjtzOjE0OiJuYXR1cmVfMDA0LmpwZyI7czoxNDoic2hvd19kaXJlY3Rpb24iO3M6ODoidmVydGljYWwiO3M6MTE6ImJ1dHRvbl90eXBlIjtzOjQ6ImZvbnQiO3M6OToiaWNvbl9uYW1lIjtzOjc6ImNvbGxhZ2UiO3M6MTA6ImxvZ29faW1hZ2UiO047czoxMDoibG9naW5fcGFnZSI7TjtzOjE4OiJsb2dvX2ltYWdlX2xvYWRpbmciO047czoxNjoic2hvd19xdWlja2FjdGlvbiI7TjtzOjE5OiJkaXNhYmxlX3F1aWNrYWN0aW9uIjtOO3M6MTA6InNob3dfbmF2aWciO3M6Mjoib24iO3M6MTE6InVsdHJhX25hdmlnIjtzOjM6Im9mZiI7czoxMToic3BsaXRfZnJhbWUiO047czoxNDoiY2xvc2VfYWRkX2Zvcm0iO047czoxNDoiZGlzYWJsZWlwY2hlY2siO047czoxMjoiZGlzYWJsZV9hamF4IjtOO3M6MTE6ImVuYWJsZV9hamF4IjtOO3M6MTE6ImNwYW5lbF9za2luIjtOO3M6MTE6InNpbXBsZV9za2luIjtOO3M6MTY6InNob3dfdGhpbl9oZWFkZXIiO047czoxNToicmVzb3VyY2VfYm90dG9tIjtzOjM6Im9mZiI7czoxODoiaW50ZXJmYWNlX3RlbXBsYXRlIjtOO3M6MTg6InNob3dfYnJldGhyZW5fbGlzdCI7czozOiJ0b3AiO3M6Mjk6ImRvbnRfc2hvd19kaXNhYmxlZF9wZXJtaXNzaW9uIjtOO3M6ODoibGFuZ3VhZ2UiO3M6NToiZW4tdXMiO3M6MTE6Imxhbmd1YWdlX2VuIjtOO3M6MTE6InNob3dfbHBhbmVsIjtzOjI6Im9uIjtzOjE1OiJkaXNhYmxlX2RvY3Jvb3QiO047czoxNzoiY3VzdG9tZXJtb2RlX2ZsYWciO047czo5OiJzaG93X2hlbHAiO3M6Mjoib24iO3M6MTY6InNzZXNzaW9uX3RpbWVvdXQiO2k6MTgwMDA7czoxNjoic2hvd19hZGRfYnV0dG9ucyI7TjtzOjE2OiJscGFuZWxfc2Nyb2xsYmFyIjtzOjM6Im9mZiI7czoyMToibHBhbmVsX2dyb3VwX3Jlc291cmNlIjtzOjM6Im9mZiI7czoxMjoibHBhbmVsX2RlcHRoIjtpOjM7czo4OiJwZXJfcGFnZSI7czoyOiIxMCI7czoxODoicGFyZW50X25hbWVfY2hhbmdlIjtOO3M6MTU6ImNjZW50ZXJfY29tbWFuZCI7TjtzOjE0OiJjY2VudGVyX291dHB1dCI7TjtzOjEzOiJjY2VudGVyX2Vycm9yIjtOO3M6MTM6InBhcmVudF9jbG5hbWUiO047czo1OiJkZGF0ZSI7aToxNDE1MDE0NTI0O3M6MTc6ImZpbHRlcl92aWV3X3F1b3RhIjtOO3M6MTg6ImZpbHRlcl92aWV3X25vcm1hbCI7TjtzOjE2OiJjY2VudGVyX3N0YW5kYXJkIjtOO3M6MTU6ImNjZW50ZXJfcmVzdGFydCI7TjtzOjEwOiJfX29sZF91c2VkIjtOO30=');
INSERT INTO "sp_childspecialplay" ("nname","parent_clname","parent_cmlist","ser_specialplay_b") VALUES ('client-tester','client-admin','','TzoxMzoiU3BlY2lhbFBsYXlfYiI6NjM6e3M6NToibm5hbWUiO3M6MTI6ImNsaWVudC1hZG1pbiI7czoxMToiX19saXN0X2xpc3QiO047czoxNDoiX192aXJ0dWFsX2xpc3QiO2E6MDp7fXM6MTM6Il9fb2JqZWN0X2xpc3QiO047czo5OiJzdWJhY3Rpb24iO047czo4OiJkYmFjdGlvbiI7czozOiJhZGQiO3M6MTI6Im1ldGFkYmFjdGlvbiI7czozOiJhbGwiO3M6NzoiX19jbGFzcyI7czoxMzoic3BlY2lhbHBsYXlfYiI7czo2OiJjdHR5cGUiO3M6MTM6InNwZWNpYWxwbGF5X2IiO3M6MTQ6Il9fbWFzdGVyc2VydmVyIjtOO3M6MTI6Il9fcmVhZHNlcnZlciI7czo5OiJsb2NhbGhvc3QiO3M6MTA6InN5bmNzZXJ2ZXIiO3M6OToibG9jYWxob3N0IjtzOjExOiJkZW1vX3N0YXR1cyI7TjtzOjE1OiJkZW1vX3N0YXR1c19vZmYiO047czoxNDoiZGVtb19zdGF0dXNfb24iO047czo5OiJza2luX25hbWUiO3M6MTA6InNpbXBsaWNpdHkiO3M6MTA6InNraW5fY29sb3IiO3M6NzoiZGVmYXVsdCI7czoxNToic2tpbl9iYWNrZ3JvdW5kIjtzOjE0OiJuYXR1cmVfMDA0LmpwZyI7czoxNDoic2hvd19kaXJlY3Rpb24iO3M6ODoidmVydGljYWwiO3M6MTE6ImJ1dHRvbl90eXBlIjtzOjQ6ImZvbnQiO3M6OToiaWNvbl9uYW1lIjtzOjc6ImNvbGxhZ2UiO3M6MTA6ImxvZ29faW1hZ2UiO047czoxMDoibG9naW5fcGFnZSI7TjtzOjE4OiJsb2dvX2ltYWdlX2xvYWRpbmciO047czoxNjoic2hvd19xdWlja2FjdGlvbiI7TjtzOjE5OiJkaXNhYmxlX3F1aWNrYWN0aW9uIjtOO3M6MTA6InNob3dfbmF2aWciO3M6Mjoib24iO3M6MTE6InVsdHJhX25hdmlnIjtzOjM6Im9mZiI7czoxMToic3BsaXRfZnJhbWUiO047czoxNDoiY2xvc2VfYWRkX2Zvcm0iO047czoxNDoiZGlzYWJsZWlwY2hlY2siO047czoxMjoiZGlzYWJsZV9hamF4IjtOO3M6MTE6ImVuYWJsZV9hamF4IjtOO3M6MTE6ImNwYW5lbF9za2luIjtOO3M6MTE6InNpbXBsZV9za2luIjtOO3M6MTY6InNob3dfdGhpbl9oZWFkZXIiO047czoxNToicmVzb3VyY2VfYm90dG9tIjtzOjM6Im9mZiI7czoxODoiaW50ZXJmYWNlX3RlbXBsYXRlIjtOO3M6MTg6InNob3dfYnJldGhyZW5fbGlzdCI7czozOiJ0b3AiO3M6Mjk6ImRvbnRfc2hvd19kaXNhYmxlZF9wZXJtaXNzaW9uIjtOO3M6ODoibGFuZ3VhZ2UiO3M6NToiZW4tdXMiO3M6MTE6Imxhbmd1YWdlX2VuIjtOO3M6MTE6InNob3dfbHBhbmVsIjtzOjI6Im9uIjtzOjE1OiJkaXNhYmxlX2RvY3Jvb3QiO047czoxNzoiY3VzdG9tZXJtb2RlX2ZsYWciO047czo5OiJzaG93X2hlbHAiO3M6Mjoib24iO3M6MTY6InNzZXNzaW9uX3RpbWVvdXQiO2k6MTgwMDA7czoxNjoic2hvd19hZGRfYnV0dG9ucyI7TjtzOjE2OiJscGFuZWxfc2Nyb2xsYmFyIjtzOjM6Im9mZiI7czoyMToibHBhbmVsX2dyb3VwX3Jlc291cmNlIjtzOjM6Im9mZiI7czoxMjoibHBhbmVsX2RlcHRoIjtpOjM7czo4OiJwZXJfcGFnZSI7czoyOiIxMCI7czoxODoicGFyZW50X25hbWVfY2hhbmdlIjtOO3M6MTU6ImNjZW50ZXJfY29tbWFuZCI7TjtzOjE0OiJjY2VudGVyX291dHB1dCI7TjtzOjEzOiJjY2VudGVyX2Vycm9yIjtOO3M6MTM6InBhcmVudF9jbG5hbWUiO047czo1OiJkZGF0ZSI7aToxNDE1MDE0NTI0O3M6MTc6ImZpbHRlcl92aWV3X3F1b3RhIjtOO3M6MTg6ImZpbHRlcl92aWV3X25vcm1hbCI7TjtzOjE2OiJjY2VudGVyX3N0YW5kYXJkIjtOO3M6MTU6ImNjZW50ZXJfcmVzdGFydCI7TjtzOjEwOiJfX29sZF91c2VkIjtOO30=');
INSERT INTO "sp_childspecialplay" ("nname","parent_clname","parent_cmlist","ser_specialplay_b") VALUES ('mailaccount-postmaster@tester.id','client-admin','','TzoxMzoiU3BlY2lhbFBsYXlfYiI6NjM6e3M6NToibm5hbWUiO3M6MTI6ImNsaWVudC1hZG1pbiI7czoxMToiX19saXN0X2xpc3QiO047czoxNDoiX192aXJ0dWFsX2xpc3QiO2E6MDp7fXM6MTM6Il9fb2JqZWN0X2xpc3QiO047czo5OiJzdWJhY3Rpb24iO047czo4OiJkYmFjdGlvbiI7czozOiJhZGQiO3M6MTI6Im1ldGFkYmFjdGlvbiI7czozOiJhbGwiO3M6NzoiX19jbGFzcyI7czoxMzoic3BlY2lhbHBsYXlfYiI7czo2OiJjdHR5cGUiO3M6MTM6InNwZWNpYWxwbGF5X2IiO3M6MTQ6Il9fbWFzdGVyc2VydmVyIjtOO3M6MTI6Il9fcmVhZHNlcnZlciI7czo5OiJsb2NhbGhvc3QiO3M6MTA6InN5bmNzZXJ2ZXIiO3M6OToibG9jYWxob3N0IjtzOjExOiJkZW1vX3N0YXR1cyI7TjtzOjE1OiJkZW1vX3N0YXR1c19vZmYiO047czoxNDoiZGVtb19zdGF0dXNfb24iO047czo5OiJza2luX25hbWUiO3M6MTA6InNpbXBsaWNpdHkiO3M6MTA6InNraW5fY29sb3IiO3M6NzoiZGVmYXVsdCI7czoxNToic2tpbl9iYWNrZ3JvdW5kIjtzOjE0OiJuYXR1cmVfMDA0LmpwZyI7czoxNDoic2hvd19kaXJlY3Rpb24iO3M6ODoidmVydGljYWwiO3M6MTE6ImJ1dHRvbl90eXBlIjtzOjQ6ImZvbnQiO3M6OToiaWNvbl9uYW1lIjtzOjc6ImNvbGxhZ2UiO3M6MTA6ImxvZ29faW1hZ2UiO047czoxMDoibG9naW5fcGFnZSI7TjtzOjE4OiJsb2dvX2ltYWdlX2xvYWRpbmciO047czoxNjoic2hvd19xdWlja2FjdGlvbiI7TjtzOjE5OiJkaXNhYmxlX3F1aWNrYWN0aW9uIjtOO3M6MTA6InNob3dfbmF2aWciO3M6Mjoib24iO3M6MTE6InVsdHJhX25hdmlnIjtzOjM6Im9mZiI7czoxMToic3BsaXRfZnJhbWUiO047czoxNDoiY2xvc2VfYWRkX2Zvcm0iO047czoxNDoiZGlzYWJsZWlwY2hlY2siO047czoxMjoiZGlzYWJsZV9hamF4IjtOO3M6MTE6ImVuYWJsZV9hamF4IjtOO3M6MTE6ImNwYW5lbF9za2luIjtOO3M6MTE6InNpbXBsZV9za2luIjtOO3M6MTY6InNob3dfdGhpbl9oZWFkZXIiO047czoxNToicmVzb3VyY2VfYm90dG9tIjtzOjM6Im9mZiI7czoxODoiaW50ZXJmYWNlX3RlbXBsYXRlIjtOO3M6MTg6InNob3dfYnJldGhyZW5fbGlzdCI7czozOiJ0b3AiO3M6Mjk6ImRvbnRfc2hvd19kaXNhYmxlZF9wZXJtaXNzaW9uIjtOO3M6ODoibGFuZ3VhZ2UiO3M6NToiZW4tdXMiO3M6MTE6Imxhbmd1YWdlX2VuIjtOO3M6MTE6InNob3dfbHBhbmVsIjtzOjI6Im9uIjtzOjE1OiJkaXNhYmxlX2RvY3Jvb3QiO047czoxNzoiY3VzdG9tZXJtb2RlX2ZsYWciO047czo5OiJzaG93X2hlbHAiO3M6Mjoib24iO3M6MTY6InNzZXNzaW9uX3RpbWVvdXQiO2k6MTgwMDA7czoxNjoic2hvd19hZGRfYnV0dG9ucyI7TjtzOjE2OiJscGFuZWxfc2Nyb2xsYmFyIjtzOjM6Im9mZiI7czoyMToibHBhbmVsX2dyb3VwX3Jlc291cmNlIjtzOjM6Im9mZiI7czoxMjoibHBhbmVsX2RlcHRoIjtpOjM7czo4OiJwZXJfcGFnZSI7czoyOiIxMCI7czoxODoicGFyZW50X25hbWVfY2hhbmdlIjtOO3M6MTU6ImNjZW50ZXJfY29tbWFuZCI7TjtzOjE0OiJjY2VudGVyX291dHB1dCI7TjtzOjEzOiJjY2VudGVyX2Vycm9yIjtOO3M6MTM6InBhcmVudF9jbG5hbWUiO047czo1OiJkZGF0ZSI7aToxNDE1MDE0NTI0O3M6MTc6ImZpbHRlcl92aWV3X3F1b3RhIjtOO3M6MTg6ImZpbHRlcl92aWV3X25vcm1hbCI7TjtzOjE2OiJjY2VudGVyX3N0YW5kYXJkIjtOO3M6MTU6ImNjZW50ZXJfcmVzdGFydCI7TjtzOjEwOiJfX29sZF91c2VkIjtOO30=');
INSERT INTO "sp_childspecialplay" ("nname","parent_clname","parent_cmlist","ser_specialplay_b") VALUES ('domain-tester.id','client-admin','','TzoxMzoiU3BlY2lhbFBsYXlfYiI6NjM6e3M6NToibm5hbWUiO3M6MTI6ImNsaWVudC1hZG1pbiI7czoxMToiX19saXN0X2xpc3QiO047czoxNDoiX192aXJ0dWFsX2xpc3QiO2E6MDp7fXM6MTM6Il9fb2JqZWN0X2xpc3QiO047czo5OiJzdWJhY3Rpb24iO047czo4OiJkYmFjdGlvbiI7czozOiJhZGQiO3M6MTI6Im1ldGFkYmFjdGlvbiI7czozOiJhbGwiO3M6NzoiX19jbGFzcyI7czoxMzoic3BlY2lhbHBsYXlfYiI7czo2OiJjdHR5cGUiO3M6MTM6InNwZWNpYWxwbGF5X2IiO3M6MTQ6Il9fbWFzdGVyc2VydmVyIjtOO3M6MTI6Il9fcmVhZHNlcnZlciI7czo5OiJsb2NhbGhvc3QiO3M6MTA6InN5bmNzZXJ2ZXIiO3M6OToibG9jYWxob3N0IjtzOjExOiJkZW1vX3N0YXR1cyI7TjtzOjE1OiJkZW1vX3N0YXR1c19vZmYiO047czoxNDoiZGVtb19zdGF0dXNfb24iO047czo5OiJza2luX25hbWUiO3M6MTA6InNpbXBsaWNpdHkiO3M6MTA6InNraW5fY29sb3IiO3M6NzoiZGVmYXVsdCI7czoxNToic2tpbl9iYWNrZ3JvdW5kIjtzOjE0OiJuYXR1cmVfMDA0LmpwZyI7czoxNDoic2hvd19kaXJlY3Rpb24iO3M6ODoidmVydGljYWwiO3M6MTE6ImJ1dHRvbl90eXBlIjtzOjQ6ImZvbnQiO3M6OToiaWNvbl9uYW1lIjtzOjc6ImNvbGxhZ2UiO3M6MTA6ImxvZ29faW1hZ2UiO047czoxMDoibG9naW5fcGFnZSI7TjtzOjE4OiJsb2dvX2ltYWdlX2xvYWRpbmciO047czoxNjoic2hvd19xdWlja2FjdGlvbiI7TjtzOjE5OiJkaXNhYmxlX3F1aWNrYWN0aW9uIjtOO3M6MTA6InNob3dfbmF2aWciO3M6Mjoib24iO3M6MTE6InVsdHJhX25hdmlnIjtzOjM6Im9mZiI7czoxMToic3BsaXRfZnJhbWUiO047czoxNDoiY2xvc2VfYWRkX2Zvcm0iO047czoxNDoiZGlzYWJsZWlwY2hlY2siO047czoxMjoiZGlzYWJsZV9hamF4IjtOO3M6MTE6ImVuYWJsZV9hamF4IjtOO3M6MTE6ImNwYW5lbF9za2luIjtOO3M6MTE6InNpbXBsZV9za2luIjtOO3M6MTY6InNob3dfdGhpbl9oZWFkZXIiO047czoxNToicmVzb3VyY2VfYm90dG9tIjtzOjM6Im9mZiI7czoxODoiaW50ZXJmYWNlX3RlbXBsYXRlIjtOO3M6MTg6InNob3dfYnJldGhyZW5fbGlzdCI7czozOiJ0b3AiO3M6Mjk6ImRvbnRfc2hvd19kaXNhYmxlZF9wZXJtaXNzaW9uIjtOO3M6ODoibGFuZ3VhZ2UiO3M6NToiZW4tdXMiO3M6MTE6Imxhbmd1YWdlX2VuIjtOO3M6MTE6InNob3dfbHBhbmVsIjtzOjI6Im9uIjtzOjE1OiJkaXNhYmxlX2RvY3Jvb3QiO047czoxNzoiY3VzdG9tZXJtb2RlX2ZsYWciO047czo5OiJzaG93X2hlbHAiO3M6Mjoib24iO3M6MTY6InNzZXNzaW9uX3RpbWVvdXQiO2k6MTgwMDA7czoxNjoic2hvd19hZGRfYnV0dG9ucyI7TjtzOjE2OiJscGFuZWxfc2Nyb2xsYmFyIjtzOjM6Im9mZiI7czoyMToibHBhbmVsX2dyb3VwX3Jlc291cmNlIjtzOjM6Im9mZiI7czoxMjoibHBhbmVsX2RlcHRoIjtpOjM7czo4OiJwZXJfcGFnZSI7czoyOiIxMCI7czoxODoicGFyZW50X25hbWVfY2hhbmdlIjtOO3M6MTU6ImNjZW50ZXJfY29tbWFuZCI7TjtzOjE0OiJjY2VudGVyX291dHB1dCI7TjtzOjEzOiJjY2VudGVyX2Vycm9yIjtOO3M6MTM6InBhcmVudF9jbG5hbWUiO047czo1OiJkZGF0ZSI7aToxNDE1MDE0NTI0O3M6MTc6ImZpbHRlcl92aWV3X3F1b3RhIjtOO3M6MTg6ImZpbHRlcl92aWV3X25vcm1hbCI7TjtzOjE2OiJjY2VudGVyX3N0YW5kYXJkIjtOO3M6MTU6ImNjZW50ZXJfcmVzdGFydCI7TjtzOjEwOiJfX29sZF91c2VkIjtOO30=');

----
-- Table structure for sp_lstclass
----
CREATE TABLE "sp_lstclass" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "ser_lst_client_list" longtext,
  "ser_lst_vps_list" longtext,
  PRIMARY KEY ("nname")
);

----
-- Data dump for sp_lstclass, a total of 0 rows
----

----
-- Table structure for sp_specialplay
----
CREATE TABLE "sp_specialplay" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "ser_specialplay_b" longtext,
  PRIMARY KEY ("nname")
);

----
-- Data dump for sp_specialplay, a total of 0 rows
----

----
-- Table structure for spam
----
CREATE TABLE "spam" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "status" varchar(255) DEFAULT NULL,
  "spam_hit" varchar(255) DEFAULT NULL,
  "subject_tag" varchar(255) DEFAULT NULL,
  "ser_wlist_a" longtext,
  "ser_blist_a" longtext,
  "syncserver" varchar(255) DEFAULT NULL,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for spam, a total of 0 rows
----

----
-- Table structure for ssession
----
CREATE TABLE "ssession" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "cttype" varchar(255) DEFAULT NULL,
  "ip_address" varchar(255) DEFAULT NULL,
  "timeout" varchar(255) DEFAULT NULL,
  "last_access" varchar(255) DEFAULT NULL,
  "logintime" varchar(255) DEFAULT NULL,
  "ser_http_vars" longtext,
  "ser_ssession_vars" longtext,
  "tsessionid" varchar(255) DEFAULT NULL,
  "auxiliary_id" varchar(255) DEFAULT NULL,
  "ser_ssl_param" longtext,
  "consuming_parent" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for ssession, a total of 0 rows
----

----
-- Table structure for sshconfig
----
CREATE TABLE "sshconfig" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "ssh_port" varchar(255) DEFAULT NULL,
  "without_password_flag" varchar(255) DEFAULT NULL,
  "disable_password_flag" varchar(255) DEFAULT NULL,
  "config_flag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for sshconfig, a total of 0 rows
----

----
-- Table structure for sslcert
----
CREATE TABLE "sslcert" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "upload_status" varchar(255) DEFAULT NULL,
  "certname" varchar(255) DEFAULT NULL,
  "syncserver" varchar(255) DEFAULT NULL,
  "ser_ssl_data_b" longtext,
  "text_crt_content" longtext,
  "text_key_content" longtext,
  "text_csr_content" longtext,
  "text_ca_content" longtext,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for sslcert, a total of 0 rows
----

----
-- Table structure for sslipaddress
----
CREATE TABLE "sslipaddress" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "devname" varchar(255) DEFAULT NULL,
  "ipaddr" varchar(255) DEFAULT NULL,
  "sslclient" varchar(255) DEFAULT NULL,
  "ssldomain" varchar(255) DEFAULT NULL,
  "sslcert" varchar(255) DEFAULT NULL,
  "syncserver" varchar(255) DEFAULT NULL,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for sslipaddress, a total of 0 rows
----

----
-- Table structure for ticket
----
CREATE TABLE "ticket" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "password" varchar(255) DEFAULT NULL,
  "escalate" varchar(255) DEFAULT NULL,
  "realpass" varchar(255) DEFAULT NULL,
  "status" varchar(255) DEFAULT NULL,
  "cpstatus" varchar(255) DEFAULT NULL,
  "state" varchar(255) DEFAULT NULL,
  "priority" varchar(255) DEFAULT NULL,
  "responsible" varchar(255) DEFAULT NULL,
  "made_by" varchar(255) DEFAULT NULL,
  "sent_to" varchar(255) DEFAULT NULL,
  "date_modified" varchar(255) DEFAULT NULL,
  "unread_flag" varchar(255) DEFAULT NULL,
  "history_num" varchar(255) DEFAULT NULL,
  "subject" varchar(255) DEFAULT NULL,
  "category" varchar(255) DEFAULT NULL,
  "ddate" varchar(255) DEFAULT NULL,
  "mail_messageid" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for ticket, a total of 0 rows
----

----
-- Table structure for ticketconfig
----
CREATE TABLE "ticketconfig" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "ticketid" varchar(255) DEFAULT NULL,
  "ser_category_list_a" longtext,
  "mail_account" varchar(255) DEFAULT NULL,
  "mail_server" varchar(255) DEFAULT NULL,
  "mail_password" varchar(255) DEFAULT NULL,
  "mail_period" varchar(255) DEFAULT NULL,
  "mail_enable" varchar(255) DEFAULT NULL,
  "mail_ssl_flag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for ticketconfig, a total of 0 rows
----

----
-- Table structure for tickethistory
----
CREATE TABLE "tickethistory" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "made_by" varchar(255) DEFAULT NULL,
  "state_from" varchar(255) DEFAULT NULL,
  "state" varchar(255) DEFAULT NULL,
  "text_reason" longtext,
  "ddate" varchar(255) DEFAULT NULL,
  "from_ad" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for tickethistory, a total of 0 rows
----

----
-- Table structure for utmp
----
CREATE TABLE "utmp" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "ssession_name" varchar(255) DEFAULT NULL,
  "cttype" varchar(255) DEFAULT NULL,
  "logintime" varchar(255) DEFAULT NULL,
  "timeout" varchar(255) DEFAULT NULL,
  "logouttime" varchar(255) DEFAULT NULL,
  "ip_address" varchar(255) DEFAULT NULL,
  "logoutreason" varchar(255) DEFAULT NULL,
  "auxiliary_id" varchar(255) DEFAULT NULL,
  "consuming_parent" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for utmp, a total of 0 rows
----

----
-- Table structure for uuser
----
CREATE TABLE "uuser" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "priv_q_disk_usage" varchar(255) DEFAULT NULL,
  "used_q_disk_usage" varchar(255) DEFAULT NULL,
  "priv_q_logo_manage_flag" varchar(255) DEFAULT NULL,
  "used_q_logo_manage_flag" varchar(255) DEFAULT NULL,
  "realname" varchar(255) DEFAULT NULL,
  "add_address" varchar(255) DEFAULT NULL,
  "add_city" varchar(255) DEFAULT NULL,
  "add_country" varchar(255) DEFAULT NULL,
  "add_telephone" varchar(255) DEFAULT NULL,
  "add_fax" varchar(255) DEFAULT NULL,
  "password" varchar(255) DEFAULT NULL,
  "cpstatus" varchar(255) DEFAULT NULL,
  "demo_status" varchar(255) DEFAULT NULL,
  "contactemail" varchar(255) DEFAULT NULL,
  "text_comment" longtext,
  "disable_per" varchar(255) DEFAULT NULL,
  "ser_hpfilter" longtext,
  "ddate" varchar(255) DEFAULT NULL,
  "ser_dskhistory" longtext,
  "ser_dskshortcut_a" longtext,
  "interface_template" varchar(255) DEFAULT NULL,
  "ser_boxpos" longtext,
  "dialogsize" varchar(255) DEFAULT NULL,
  "realpass" varchar(255) DEFAULT NULL,
  "shellflag" varchar(255) DEFAULT NULL,
  "shell" varchar(255) DEFAULT NULL,
  "syncserver" varchar(255) DEFAULT NULL,
  "status" varchar(255) DEFAULT NULL,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for uuser, a total of 0 rows
----

----
-- Table structure for version
----
CREATE TABLE "version" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "major" varchar(255) DEFAULT NULL,
  "minor" varchar(255) DEFAULT NULL,
  "releasen" varchar(255) DEFAULT NULL,
  "extra" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for version, a total of 0 rows
----

----
-- Table structure for watchdog
----
CREATE TABLE "watchdog" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "servicename" varchar(255) DEFAULT NULL,
  "syncserver" varchar(255) DEFAULT NULL,
  "port" varchar(255) DEFAULT NULL,
  "action" varchar(255) DEFAULT NULL,
  "status" varchar(255) DEFAULT NULL,
  "added_by_system" varchar(255) DEFAULT NULL,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for watchdog, a total of 4 rows
----
INSERT INTO "watchdog" ("nname","parent_clname","parent_cmlist","servicename","syncserver","port","action","status","added_by_system","oldsyncserver","olddeleteflag") VALUES ('dns___localhost','pserver-localhost','','dns','localhost','53','__driver_dns','on','on','','');
INSERT INTO "watchdog" ("nname","parent_clname","parent_cmlist","servicename","syncserver","port","action","status","added_by_system","oldsyncserver","olddeleteflag") VALUES ('web___localhost','pserver-localhost','','web','localhost','80','__driver_web','on','on','','');
INSERT INTO "watchdog" ("nname","parent_clname","parent_cmlist","servicename","syncserver","port","action","status","added_by_system","oldsyncserver","olddeleteflag") VALUES ('mail___localhost','pserver-localhost','','mail','localhost','25','__driver_qmail','on','on','','');
INSERT INTO "watchdog" ("nname","parent_clname","parent_cmlist","servicename","syncserver","port","action","status","added_by_system","oldsyncserver","olddeleteflag") VALUES ('mysql___localhost','pserver-localhost','','mysql','localhost','3306','__driver_mysql','on','on','','');

----
-- Table structure for web
----
CREATE TABLE "web" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "priv_q_totaldisk_usage" varchar(255) DEFAULT NULL,
  "used_q_totaldisk_usage" varchar(255) DEFAULT NULL,
  "priv_q_ssl_flag" varchar(255) DEFAULT NULL,
  "used_q_ssl_flag" varchar(255) DEFAULT NULL,
  "priv_q_rubyfcgiprocess_num" varchar(255) DEFAULT NULL,
  "used_q_rubyfcgiprocess_num" varchar(255) DEFAULT NULL,
  "priv_q_disk_usage" varchar(255) DEFAULT NULL,
  "used_q_disk_usage" varchar(255) DEFAULT NULL,
  "priv_q_logo_manage_flag" varchar(255) DEFAULT NULL,
  "used_q_logo_manage_flag" varchar(255) DEFAULT NULL,
  "priv_q_ftpuser_num" varchar(255) DEFAULT NULL,
  "used_q_ftpuser_num" varchar(255) DEFAULT NULL,
  "priv_q_frontpage_flag" varchar(255) DEFAULT NULL,
  "used_q_frontpage_flag" varchar(255) DEFAULT NULL,
  "priv_q_php_manage_flag" varchar(255) DEFAULT NULL,
  "used_q_php_manage_flag" varchar(255) DEFAULT NULL,
  "priv_q_inc_flag" varchar(255) DEFAULT NULL,
  "used_q_inc_flag" varchar(255) DEFAULT NULL,
  "priv_q_awstats_flag" varchar(255) DEFAULT NULL,
  "used_q_awstats_flag" varchar(255) DEFAULT NULL,
  "priv_q_easyinstaller_flag" varchar(255) DEFAULT NULL,
  "used_q_easyinstaller_flag" varchar(255) DEFAULT NULL,
  "priv_q_modperl_flag" varchar(255) DEFAULT NULL,
  "used_q_modperl_flag" varchar(255) DEFAULT NULL,
  "priv_q_cgi_flag" varchar(255) DEFAULT NULL,
  "used_q_cgi_flag" varchar(255) DEFAULT NULL,
  "priv_q_php_flag" varchar(255) DEFAULT NULL,
  "used_q_php_flag" varchar(255) DEFAULT NULL,
  "priv_q_phpunsafe_flag" varchar(255) DEFAULT NULL,
  "used_q_phpunsafe_flag" varchar(255) DEFAULT NULL,
  "priv_q_subweb_a_num" varchar(255) DEFAULT NULL,
  "used_q_subweb_a_num" varchar(255) DEFAULT NULL,
  "priv_q_dotnet_flag" varchar(255) DEFAULT NULL,
  "used_q_dotnet_flag" varchar(255) DEFAULT NULL,
  "priv_q_cron_num" varchar(255) DEFAULT NULL,
  "used_q_cron_num" varchar(255) DEFAULT NULL,
  "priv_q_cron_minute_flag" varchar(255) DEFAULT NULL,
  "used_q_cron_minute_flag" varchar(255) DEFAULT NULL,
  "priv_q_cron_manage_flag" varchar(255) DEFAULT NULL,
  "used_q_cron_manage_flag" varchar(255) DEFAULT NULL,
  "priv_q_phpfcgi_flag" varchar(255) DEFAULT NULL,
  "used_q_phpfcgi_flag" varchar(255) DEFAULT NULL,
  "priv_q_rubyrails_num" varchar(255) DEFAULT NULL,
  "used_q_rubyrails_num" varchar(255) DEFAULT NULL,
  "priv_q_phpfcgiprocess_num" varchar(255) DEFAULT NULL,
  "used_q_phpfcgiprocess_num" varchar(255) DEFAULT NULL,
  "status" varchar(255) DEFAULT NULL,
  "iisid" varchar(255) DEFAULT NULL,
  "ser_server_alias_a" longtext,
  "ser_subweb_a" longtext,
  "ser_redirect_a" longtext,
  "stats_username" varchar(255) DEFAULT NULL,
  "stats_password" varchar(255) DEFAULT NULL,
  "ttype" varchar(255) DEFAULT NULL,
  "username" varchar(255) DEFAULT NULL,
  "password" varchar(255) DEFAULT NULL,
  "ipaddress" varchar(255) DEFAULT NULL,
  "ser_webmisc_b" longtext,
  "redirect_domain" varchar(255) DEFAULT NULL,
  "text_extra_tag" longtext,
  "ser_customerror_b" longtext,
  "frontpage_flag" varchar(255) DEFAULT NULL,
  "syncserver" varchar(255) DEFAULT NULL,
  "cron_mailto" varchar(255) DEFAULT NULL,
  "ser_aspnetconf_b" longtext,
  "ser_webindexdir_a" longtext,
  "webmail_url" varchar(255) DEFAULT NULL,
  "text_lighty_rewrite" longtext,
  "text_nginx_rewrite" longtext,
  "ftpusername" varchar(255) DEFAULT NULL,
  "hotlink_flag" varchar(255) DEFAULT NULL,
  "text_hotlink_allowed" longtext,
  "hotlink_redirect" varchar(255) DEFAULT NULL,
  "remove_processed_stats" varchar(255) DEFAULT NULL,
  "ser_indexfile_list" longtext,
  "fcgi_children" varchar(255) DEFAULT NULL,
  "customer_name" varchar(255) DEFAULT NULL,
  "text_blockip" longtext,
  "docroot" varchar(255) DEFAULT NULL,
  "force_www_redirect" varchar(255) DEFAULT NULL,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for web, a total of 0 rows
----

----
-- Table structure for webhandler
----
CREATE TABLE "webhandler" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "syncserver" varchar(255) DEFAULT NULL,
  "mimehandler" varchar(255) DEFAULT NULL,
  "extension" varchar(255) DEFAULT NULL,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for webhandler, a total of 0 rows
----

----
-- Table structure for webmimetype
----
CREATE TABLE "webmimetype" (
  "nname" varchar(255) NOT NULL,
  "parent_clname" varchar(255) DEFAULT NULL,
  "parent_cmlist" text,
  "syncserver" varchar(255) DEFAULT NULL,
  "mimehandler" varchar(255) DEFAULT NULL,
  "extension" varchar(255) DEFAULT NULL,
  "oldsyncserver" varchar(255) DEFAULT NULL,
  "olddeleteflag" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("nname")
);

----
-- Data dump for webmimetype, a total of 0 rows
----

----
-- structure for index sqlite_autoindex_actionlog_1 on table actionlog
----
;

----
-- structure for index sqlite_autoindex_addondomain_1 on table addondomain
----
;

----
-- structure for index sqlite_autoindex_allowedip_1 on table allowedip
----
;

----
-- structure for index sqlite_autoindex_anonftpipaddress_1 on table anonftpipaddress
----
;

----
-- structure for index sqlite_autoindex_aspnet_1 on table aspnet
----
;

----
-- structure for index sqlite_autoindex_autoresponder_1 on table autoresponder
----
;

----
-- structure for index sqlite_autoindex_auxiliary_1 on table auxiliary
----
;

----
-- structure for index sqlite_autoindex_blockedip_1 on table blockedip
----
;

----
-- structure for index sqlite_autoindex_client_1 on table client
----
;

----
-- structure for index sqlite_autoindex_clienttemplate_1 on table clienttemplate
----
;

----
-- structure for index sqlite_autoindex_component_1 on table component
----
;

----
-- structure for index sqlite_autoindex_cron_1 on table cron
----
;

----
-- structure for index sqlite_autoindex_customaction_1 on table customaction
----
;

----
-- structure for index sqlite_autoindex_custombutton_1 on table custombutton
----
;

----
-- structure for index sqlite_autoindex_davuser_1 on table davuser
----
;

----
-- structure for index sqlite_autoindex_dbadmin_1 on table dbadmin
----
;

----
-- structure for index sqlite_autoindex_dirprotect_1 on table dirprotect
----
;

----
-- structure for index sqlite_autoindex_dns_1 on table dns
----
;

----
-- structure for index sqlite_autoindex_dnsslave_1 on table dnsslave
----
;

----
-- structure for index sqlite_autoindex_dnstemplate_1 on table dnstemplate
----
;

----
-- structure for index sqlite_autoindex_domain_1 on table domain
----
;

----
-- structure for index sqlite_autoindex_domaindefault_1 on table domaindefault
----
;

----
-- structure for index sqlite_autoindex_domainipaddress_1 on table domainipaddress
----
;

----
-- structure for index sqlite_autoindex_domaintemplate_1 on table domaintemplate
----
;

----
-- structure for index sqlite_autoindex_domaintraffic_1 on table domaintraffic
----
;

----
-- structure for index sqlite_autoindex_driver_1 on table driver
----
;

----
-- structure for index sqlite_autoindex_firewall_1 on table firewall
----
;

----
-- structure for index sqlite_autoindex_ftpuser_1 on table ftpuser
----
;

----
-- structure for index sqlite_autoindex_general_1 on table general
----
;

----
-- structure for index sqlite_autoindex_genlist_1 on table genlist
----
;

----
-- structure for index sqlite_autoindex_hostdeny_1 on table hostdeny
----
;

----
-- structure for index sqlite_autoindex_installsoft_1 on table installsoft
----
;

----
-- structure for index sqlite_autoindex_interface_template_1 on table interface_template
----
;

----
-- structure for index sqlite_autoindex_ipaddress_1 on table ipaddress
----
;

----
-- structure for index sqlite_autoindex_jailed_1 on table jailed
----
;

----
-- structure for index sqlite_autoindex_license_1 on table license
----
;

----
-- structure for index sqlite_autoindex_llog_1 on table llog
----
;

----
-- structure for index sqlite_autoindex_loginattempt_1 on table loginattempt
----
;

----
-- structure for index sqlite_autoindex_lxbackup_1 on table lxbackup
----
;

----
-- structure for index sqlite_autoindex_lxguard_1 on table lxguard
----
;

----
-- structure for index sqlite_autoindex_lxguardhit_1 on table lxguardhit
----
;

----
-- structure for index sqlite_autoindex_lxguardwhitelist_1 on table lxguardwhitelist
----
;

----
-- structure for index sqlite_autoindex_lxupdate_1 on table lxupdate
----
;

----
-- structure for index sqlite_autoindex_mailaccount_1 on table mailaccount
----
;

----
-- structure for index sqlite_autoindex_mailfilter_1 on table mailfilter
----
;

----
-- structure for index sqlite_autoindex_mailforward_1 on table mailforward
----
;

----
-- structure for index sqlite_autoindex_mailinglist_1 on table mailinglist
----
;

----
-- structure for index sqlite_autoindex_mimetype_1 on table mimetype
----
;

----
-- structure for index sqlite_autoindex_mmail_1 on table mmail
----
;

----
-- structure for index sqlite_autoindex_module_1 on table module
----
;

----
-- structure for index sqlite_autoindex_mssqldb_1 on table mssqldb
----
;

----
-- structure for index sqlite_autoindex_mssqldbuser_1 on table mssqldbuser
----
;

----
-- structure for index sqlite_autoindex_mysqldb_1 on table mysqldb
----
;

----
-- structure for index sqlite_autoindex_mysqldbuser_1 on table mysqldbuser
----
;

----
-- structure for index sqlite_autoindex_ndskshortcut_1 on table ndskshortcut
----
;

----
-- structure for index sqlite_autoindex_ndsktoolbar_1 on table ndsktoolbar
----
;

----
-- structure for index sqlite_autoindex_notification_1 on table notification
----
;

----
-- structure for index sqlite_autoindex_odbc_1 on table odbc
----
;

----
-- structure for index sqlite_autoindex_phpini_1 on table phpini
----
;

----
-- structure for index sqlite_autoindex_proxy_1 on table proxy
----
;

----
-- structure for index sqlite_autoindex_proxyacl_1 on table proxyacl
----
;

----
-- structure for index sqlite_autoindex_pserver_1 on table pserver
----
;

----
-- structure for index sqlite_autoindex_rdnsrange_1 on table rdnsrange
----
;

----
-- structure for index sqlite_autoindex_resourceplan_1 on table resourceplan
----
;

----
-- structure for index sqlite_autoindex_reversedns_1 on table reversedns
----
;

----
-- structure for index sqlite_autoindex_rubyrails_1 on table rubyrails
----
;

----
-- structure for index sqlite_autoindex_serverftp_1 on table serverftp
----
;

----
-- structure for index sqlite_autoindex_servermail_1 on table servermail
----
;

----
-- structure for index sqlite_autoindex_serverspam_1 on table serverspam
----
;

----
-- structure for index sqlite_autoindex_serverweb_1 on table serverweb
----
;

----
-- structure for index sqlite_autoindex_service_1 on table service
----
;

----
-- structure for index sqlite_autoindex_skipbackup_1 on table skipbackup
----
;

----
-- structure for index sqlite_autoindex_smessage_1 on table smessage
----
;

----
-- structure for index sqlite_autoindex_sp_childspecialplay_1 on table sp_childspecialplay
----
;

----
-- structure for index sqlite_autoindex_sp_lstclass_1 on table sp_lstclass
----
;

----
-- structure for index sqlite_autoindex_sp_specialplay_1 on table sp_specialplay
----
;

----
-- structure for index sqlite_autoindex_spam_1 on table spam
----
;

----
-- structure for index sqlite_autoindex_ssession_1 on table ssession
----
;

----
-- structure for index sqlite_autoindex_sshconfig_1 on table sshconfig
----
;

----
-- structure for index sqlite_autoindex_sslcert_1 on table sslcert
----
;

----
-- structure for index sqlite_autoindex_sslipaddress_1 on table sslipaddress
----
;

----
-- structure for index sqlite_autoindex_ticket_1 on table ticket
----
;

----
-- structure for index sqlite_autoindex_ticketconfig_1 on table ticketconfig
----
;

----
-- structure for index sqlite_autoindex_tickethistory_1 on table tickethistory
----
;

----
-- structure for index sqlite_autoindex_utmp_1 on table utmp
----
;

----
-- structure for index sqlite_autoindex_uuser_1 on table uuser
----
;

----
-- structure for index sqlite_autoindex_version_1 on table version
----
;

----
-- structure for index sqlite_autoindex_watchdog_1 on table watchdog
----
;

----
-- structure for index sqlite_autoindex_web_1 on table web
----
;

----
-- structure for index sqlite_autoindex_webhandler_1 on table webhandler
----
;

----
-- structure for index sqlite_autoindex_webmimetype_1 on table webmimetype
----
;

----
-- structure for index watchdog_parent_clname_watchdog on table watchdog
----
CREATE INDEX "watchdog_parent_clname_watchdog" ON "watchdog" ("parent_clname");

----
-- structure for index jailed_parent_clname_jailed on table jailed
----
CREATE INDEX "jailed_parent_clname_jailed" ON "jailed" ("parent_clname");

----
-- structure for index blockedip_parent_clname_blockedip on table blockedip
----
CREATE INDEX "blockedip_parent_clname_blockedip" ON "blockedip" ("parent_clname");

----
-- structure for index domaintemplate_parent_clname_domaintemplate on table domaintemplate
----
CREATE INDEX "domaintemplate_parent_clname_domaintemplate" ON "domaintemplate" ("parent_clname");

----
-- structure for index ticketconfig_parent_clname_ticketconfig on table ticketconfig
----
CREATE INDEX "ticketconfig_parent_clname_ticketconfig" ON "ticketconfig" ("parent_clname");

----
-- structure for index skipbackup_parent_clname_skipbackup on table skipbackup
----
CREATE INDEX "skipbackup_parent_clname_skipbackup" ON "skipbackup" ("parent_clname");

----
-- structure for index mssqldbuser_parent_clname_mssqldbuser on table mssqldbuser
----
CREATE INDEX "mssqldbuser_parent_clname_mssqldbuser" ON "mssqldbuser" ("parent_clname");

----
-- structure for index webhandler_parent_clname_webhandler on table webhandler
----
CREATE INDEX "webhandler_parent_clname_webhandler" ON "webhandler" ("parent_clname");

----
-- structure for index ftpuser_parent_clname_ftpuser on table ftpuser
----
CREATE INDEX "ftpuser_parent_clname_ftpuser" ON "ftpuser" ("parent_clname");

----
-- structure for index webmimetype_parent_clname_webmimetype on table webmimetype
----
CREATE INDEX "webmimetype_parent_clname_webmimetype" ON "webmimetype" ("parent_clname");

----
-- structure for index lxguardhit_parent_clname_lxguardhit on table lxguardhit
----
CREATE INDEX "lxguardhit_parent_clname_lxguardhit" ON "lxguardhit" ("parent_clname");

----
-- structure for index domainipaddress_parent_clname_domainipaddress on table domainipaddress
----
CREATE INDEX "domainipaddress_parent_clname_domainipaddress" ON "domainipaddress" ("parent_clname");

----
-- structure for index lxbackup_parent_clname_lxbackup on table lxbackup
----
CREATE INDEX "lxbackup_parent_clname_lxbackup" ON "lxbackup" ("parent_clname");

----
-- structure for index firewall_parent_clname_firewall on table firewall
----
CREATE INDEX "firewall_parent_clname_firewall" ON "firewall" ("parent_clname");

----
-- structure for index addondomain_parent_clname_addondomain on table addondomain
----
CREATE INDEX "addondomain_parent_clname_addondomain" ON "addondomain" ("parent_clname");

----
-- structure for index rubyrails_parent_clname_rubyrails on table rubyrails
----
CREATE INDEX "rubyrails_parent_clname_rubyrails" ON "rubyrails" ("parent_clname");

----
-- structure for index sp_specialplay_parent_clname_sp_specialplay on table sp_specialplay
----
CREATE INDEX "sp_specialplay_parent_clname_sp_specialplay" ON "sp_specialplay" ("parent_clname");

----
-- structure for index mailforward_parent_clname_mailforward on table mailforward
----
CREATE INDEX "mailforward_parent_clname_mailforward" ON "mailforward" ("parent_clname");

----
-- structure for index mailfilter_parent_clname_mailfilter on table mailfilter
----
CREATE INDEX "mailfilter_parent_clname_mailfilter" ON "mailfilter" ("parent_clname");

----
-- structure for index mailinglist_parent_clname_mailinglist on table mailinglist
----
CREATE INDEX "mailinglist_parent_clname_mailinglist" ON "mailinglist" ("parent_clname");

----
-- structure for index serverspam_parent_clname_serverspam on table serverspam
----
CREATE INDEX "serverspam_parent_clname_serverspam" ON "serverspam" ("parent_clname");

----
-- structure for index reversedns_parent_clname_reversedns on table reversedns
----
CREATE INDEX "reversedns_parent_clname_reversedns" ON "reversedns" ("parent_clname");

----
-- structure for index service_parent_clname_service on table service
----
CREATE INDEX "service_parent_clname_service" ON "service" ("parent_clname");

----
-- structure for index license_parent_clname_license on table license
----
CREATE INDEX "license_parent_clname_license" ON "license" ("parent_clname");

----
-- structure for index dnstemplate_parent_clname_dnstemplate on table dnstemplate
----
CREATE INDEX "dnstemplate_parent_clname_dnstemplate" ON "dnstemplate" ("parent_clname");

----
-- structure for index web_parent_clname_web on table web
----
CREATE INDEX "web_parent_clname_web" ON "web" ("parent_clname");

----
-- structure for index spam_parent_clname_spam on table spam
----
CREATE INDEX "spam_parent_clname_spam" ON "spam" ("parent_clname");

----
-- structure for index sslipaddress_parent_clname_sslipaddress on table sslipaddress
----
CREATE INDEX "sslipaddress_parent_clname_sslipaddress" ON "sslipaddress" ("parent_clname");

----
-- structure for index customaction_parent_clname_customaction on table customaction
----
CREATE INDEX "customaction_parent_clname_customaction" ON "customaction" ("parent_clname");

----
-- structure for index proxyacl_parent_clname_proxyacl on table proxyacl
----
CREATE INDEX "proxyacl_parent_clname_proxyacl" ON "proxyacl" ("parent_clname");

----
-- structure for index smessage_parent_clname_smessage on table smessage
----
CREATE INDEX "smessage_parent_clname_smessage" ON "smessage" ("parent_clname");

----
-- structure for index auxiliary_parent_clname_auxiliary on table auxiliary
----
CREATE INDEX "auxiliary_parent_clname_auxiliary" ON "auxiliary" ("parent_clname");

----
-- structure for index lxguard_parent_clname_lxguard on table lxguard
----
CREATE INDEX "lxguard_parent_clname_lxguard" ON "lxguard" ("parent_clname");

----
-- structure for index dnsslave_parent_clname_dnsslave on table dnsslave
----
CREATE INDEX "dnsslave_parent_clname_dnsslave" ON "dnsslave" ("parent_clname");

----
-- structure for index ipaddress_parent_clname_ipaddress on table ipaddress
----
CREATE INDEX "ipaddress_parent_clname_ipaddress" ON "ipaddress" ("parent_clname");

----
-- structure for index sshconfig_parent_clname_sshconfig on table sshconfig
----
CREATE INDEX "sshconfig_parent_clname_sshconfig" ON "sshconfig" ("parent_clname");

----
-- structure for index utmp_parent_clname_utmp on table utmp
----
CREATE INDEX "utmp_parent_clname_utmp" ON "utmp" ("parent_clname");

----
-- structure for index dbadmin_parent_clname_dbadmin on table dbadmin
----
CREATE INDEX "dbadmin_parent_clname_dbadmin" ON "dbadmin" ("parent_clname");

----
-- structure for index domaintraffic_parent_clname_domaintraffic on table domaintraffic
----
CREATE INDEX "domaintraffic_parent_clname_domaintraffic" ON "domaintraffic" ("parent_clname");

----
-- structure for index ndskshortcut_parent_clname_ndskshortcut on table ndskshortcut
----
CREATE INDEX "ndskshortcut_parent_clname_ndskshortcut" ON "ndskshortcut" ("parent_clname");

----
-- structure for index clienttemplate_parent_clname_clienttemplate on table clienttemplate
----
CREATE INDEX "clienttemplate_parent_clname_clienttemplate" ON "clienttemplate" ("parent_clname");

----
-- structure for index domain_parent_clname_domain on table domain
----
CREATE INDEX "domain_parent_clname_domain" ON "domain" ("parent_clname");

----
-- structure for index anonftpipaddress_parent_clname_anonftpipaddress on table anonftpipaddress
----
CREATE INDEX "anonftpipaddress_parent_clname_anonftpipaddress" ON "anonftpipaddress" ("parent_clname");

----
-- structure for index dirprotect_parent_clname_dirprotect on table dirprotect
----
CREATE INDEX "dirprotect_parent_clname_dirprotect" ON "dirprotect" ("parent_clname");

----
-- structure for index tickethistory_parent_clname_tickethistory on table tickethistory
----
CREATE INDEX "tickethistory_parent_clname_tickethistory" ON "tickethistory" ("parent_clname");

----
-- structure for index proxy_parent_clname_proxy on table proxy
----
CREATE INDEX "proxy_parent_clname_proxy" ON "proxy" ("parent_clname");

----
-- structure for index aspnet_parent_clname_aspnet on table aspnet
----
CREATE INDEX "aspnet_parent_clname_aspnet" ON "aspnet" ("parent_clname");

----
-- structure for index actionlog_parent_clname_actionlog on table actionlog
----
CREATE INDEX "actionlog_parent_clname_actionlog" ON "actionlog" ("parent_clname");

----
-- structure for index driver_parent_clname_driver on table driver
----
CREATE INDEX "driver_parent_clname_driver" ON "driver" ("parent_clname");

----
-- structure for index serverweb_parent_clname_serverweb on table serverweb
----
CREATE INDEX "serverweb_parent_clname_serverweb" ON "serverweb" ("parent_clname");

----
-- structure for index ticket_parent_clname_ticket on table ticket
----
CREATE INDEX "ticket_parent_clname_ticket" ON "ticket" ("parent_clname");

----
-- structure for index davuser_parent_clname_davuser on table davuser
----
CREATE INDEX "davuser_parent_clname_davuser" ON "davuser" ("parent_clname");

----
-- structure for index custombutton_parent_clname_custombutton on table custombutton
----
CREATE INDEX "custombutton_parent_clname_custombutton" ON "custombutton" ("parent_clname");

----
-- structure for index resourceplan_parent_clname_resourceplan on table resourceplan
----
CREATE INDEX "resourceplan_parent_clname_resourceplan" ON "resourceplan" ("parent_clname");

----
-- structure for index mailaccount_parent_clname_mailaccount on table mailaccount
----
CREATE INDEX "mailaccount_parent_clname_mailaccount" ON "mailaccount" ("parent_clname");

----
-- structure for index dns_parent_clname_dns on table dns
----
CREATE INDEX "dns_parent_clname_dns" ON "dns" ("parent_clname");

----
-- structure for index component_parent_clname_component on table component
----
CREATE INDEX "component_parent_clname_component" ON "component" ("parent_clname");

----
-- structure for index uuser_parent_clname_uuser on table uuser
----
CREATE INDEX "uuser_parent_clname_uuser" ON "uuser" ("parent_clname");

----
-- structure for index rdnsrange_parent_clname_rdnsrange on table rdnsrange
----
CREATE INDEX "rdnsrange_parent_clname_rdnsrange" ON "rdnsrange" ("parent_clname");

----
-- structure for index domaindefault_parent_clname_domaindefault on table domaindefault
----
CREATE INDEX "domaindefault_parent_clname_domaindefault" ON "domaindefault" ("parent_clname");

----
-- structure for index allowedip_parent_clname_allowedip on table allowedip
----
CREATE INDEX "allowedip_parent_clname_allowedip" ON "allowedip" ("parent_clname");

----
-- structure for index hostdeny_parent_clname_hostdeny on table hostdeny
----
CREATE INDEX "hostdeny_parent_clname_hostdeny" ON "hostdeny" ("parent_clname");

----
-- structure for index serverftp_parent_clname_serverftp on table serverftp
----
CREATE INDEX "serverftp_parent_clname_serverftp" ON "serverftp" ("parent_clname");

----
-- structure for index ndsktoolbar_parent_clname_ndsktoolbar on table ndsktoolbar
----
CREATE INDEX "ndsktoolbar_parent_clname_ndsktoolbar" ON "ndsktoolbar" ("parent_clname");

----
-- structure for index client_parent_clname_client on table client
----
CREATE INDEX "client_parent_clname_client" ON "client" ("parent_clname");

----
-- structure for index odbc_parent_clname_odbc on table odbc
----
CREATE INDEX "odbc_parent_clname_odbc" ON "odbc" ("parent_clname");

----
-- structure for index llog_parent_clname_llog on table llog
----
CREATE INDEX "llog_parent_clname_llog" ON "llog" ("parent_clname");

----
-- structure for index version_parent_clname_version on table version
----
CREATE INDEX "version_parent_clname_version" ON "version" ("parent_clname");

----
-- structure for index phpini_parent_clname_phpini on table phpini
----
CREATE INDEX "phpini_parent_clname_phpini" ON "phpini" ("parent_clname");

----
-- structure for index genlist_parent_clname_genlist on table genlist
----
CREATE INDEX "genlist_parent_clname_genlist" ON "genlist" ("parent_clname");

----
-- structure for index interface_template_parent_clname_interface_template on table interface_template
----
CREATE INDEX "interface_template_parent_clname_interface_template" ON "interface_template" ("parent_clname");

----
-- structure for index installsoft_parent_clname_installsoft on table installsoft
----
CREATE INDEX "installsoft_parent_clname_installsoft" ON "installsoft" ("parent_clname");

----
-- structure for index lxupdate_parent_clname_lxupdate on table lxupdate
----
CREATE INDEX "lxupdate_parent_clname_lxupdate" ON "lxupdate" ("parent_clname");

----
-- structure for index sp_childspecialplay_parent_clname_sp_childspecialplay on table sp_childspecialplay
----
CREATE INDEX "sp_childspecialplay_parent_clname_sp_childspecialplay" ON "sp_childspecialplay" ("parent_clname");

----
-- structure for index pserver_parent_clname_pserver on table pserver
----
CREATE INDEX "pserver_parent_clname_pserver" ON "pserver" ("parent_clname");

----
-- structure for index general_parent_clname_general on table general
----
CREATE INDEX "general_parent_clname_general" ON "general" ("parent_clname");

----
-- structure for index autoresponder_parent_clname_autoresponder on table autoresponder
----
CREATE INDEX "autoresponder_parent_clname_autoresponder" ON "autoresponder" ("parent_clname");

----
-- structure for index loginattempt_parent_clname_loginattempt on table loginattempt
----
CREATE INDEX "loginattempt_parent_clname_loginattempt" ON "loginattempt" ("parent_clname");

----
-- structure for index mysqldb_parent_clname_mysqldb on table mysqldb
----
CREATE INDEX "mysqldb_parent_clname_mysqldb" ON "mysqldb" ("parent_clname");

----
-- structure for index ssession_parent_clname_ssession on table ssession
----
CREATE INDEX "ssession_parent_clname_ssession" ON "ssession" ("parent_clname");

----
-- structure for index mysqldbuser_parent_clname_mysqldbuser on table mysqldbuser
----
CREATE INDEX "mysqldbuser_parent_clname_mysqldbuser" ON "mysqldbuser" ("parent_clname");

----
-- structure for index lxguardwhitelist_parent_clname_lxguardwhitelist on table lxguardwhitelist
----
CREATE INDEX "lxguardwhitelist_parent_clname_lxguardwhitelist" ON "lxguardwhitelist" ("parent_clname");

----
-- structure for index mmail_parent_clname_mmail on table mmail
----
CREATE INDEX "mmail_parent_clname_mmail" ON "mmail" ("parent_clname");

----
-- structure for index module_parent_clname_module on table module
----
CREATE INDEX "module_parent_clname_module" ON "module" ("parent_clname");

----
-- structure for index sp_lstclass_parent_clname_sp_lstclass on table sp_lstclass
----
CREATE INDEX "sp_lstclass_parent_clname_sp_lstclass" ON "sp_lstclass" ("parent_clname");

----
-- structure for index servermail_parent_clname_servermail on table servermail
----
CREATE INDEX "servermail_parent_clname_servermail" ON "servermail" ("parent_clname");

----
-- structure for index mssqldb_parent_clname_mssqldb on table mssqldb
----
CREATE INDEX "mssqldb_parent_clname_mssqldb" ON "mssqldb" ("parent_clname");

----
-- structure for index cron_parent_clname_cron on table cron
----
CREATE INDEX "cron_parent_clname_cron" ON "cron" ("parent_clname");

----
-- structure for index sslcert_parent_clname_sslcert on table sslcert
----
CREATE INDEX "sslcert_parent_clname_sslcert" ON "sslcert" ("parent_clname");

----
-- structure for index notification_parent_clname_notification on table notification
----
CREATE INDEX "notification_parent_clname_notification" ON "notification" ("parent_clname");

----
-- structure for index mimetype_parent_clname_mimetype on table mimetype
----
CREATE INDEX "mimetype_parent_clname_mimetype" ON "mimetype" ("parent_clname");
COMMIT;
