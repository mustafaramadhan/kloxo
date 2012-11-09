-- phpMyAdmin SQL Dump
-- version 3.4.10
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Mar 09, 2012 at 04:10 AM
-- Server version: 5.0.95
-- PHP Version: 5.2.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `kloxo`
--

CREATE DATABASE IF NOT EXISTS kloxo;

USE kloxo;

-- --------------------------------------------------------

--
-- Table structure for table `actionlog`
--

CREATE TABLE IF NOT EXISTS `actionlog` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `login` varchar(255) default NULL,
  `loginclname` varchar(255) default NULL,
  `auxiliary_id` varchar(255) default NULL,
  `ipaddress` varchar(255) default NULL,
  `class` varchar(255) default NULL,
  `objectname` varchar(255) default NULL,
  `action` varchar(255) default NULL,
  `subaction` varchar(255) default NULL,
  `ddate` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_actionlog` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `addondomain`
--

CREATE TABLE IF NOT EXISTS `addondomain` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `ttype` varchar(255) default NULL,
  `destinationdir` varchar(255) default NULL,
  `mail_flag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_addondomain` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `allowedip`
--

CREATE TABLE IF NOT EXISTS `allowedip` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `ipaddress` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_allowedip` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `anonftpipaddress`
--

CREATE TABLE IF NOT EXISTS `anonftpipaddress` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `status` varchar(255) default NULL,
  `syncserver` varchar(255) default NULL,
  `ipaddr` varchar(255) default NULL,
  `message` varchar(255) default NULL,
  `anondomain` varchar(255) default NULL,
  `ser_anonftpmisc_b` longtext,
  `disk_limit` varchar(255) default NULL,
  `connection_limit` varchar(255) default NULL,
  `download_limit` varchar(255) default NULL,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_anonftpipaddress` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `aspnet`
--

CREATE TABLE IF NOT EXISTS `aspnet` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `version` varchar(255) default NULL,
  `syncserver` varchar(255) default NULL,
  `session_timeout` varchar(255) default NULL,
  `ser_globalization_b` longtext,
  `ser_aspnetmisc_b` longtext,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_aspnet` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `autoresponder`
--

CREATE TABLE IF NOT EXISTS `autoresponder` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `status` varchar(255) default NULL,
  `send_rule` varchar(255) default NULL,
  `reply_subject` varchar(255) default NULL,
  `text_message` longtext,
  `autores_name` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_autoresponder` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `auxiliary`
--

CREATE TABLE IF NOT EXISTS `auxiliary` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `realname` varchar(255) default NULL,
  `add_address` varchar(255) default NULL,
  `add_city` varchar(255) default NULL,
  `add_country` varchar(255) default NULL,
  `add_telephone` varchar(255) default NULL,
  `add_fax` varchar(255) default NULL,
  `password` varchar(255) default NULL,
  `cpstatus` varchar(255) default NULL,
  `demo_status` varchar(255) default NULL,
  `contactemail` varchar(255) default NULL,
  `text_comment` longtext,
  `disable_per` varchar(255) default NULL,
  `ser_hpfilter` longtext,
  `ddate` varchar(255) default NULL,
  `ser_dskhistory` longtext,
  `ser_dskshortcut_a` longtext,
  `interface_template` varchar(255) default NULL,
  `ser_boxpos` longtext,
  `dialogsize` varchar(255) default NULL,
  `status` varchar(255) default NULL,
  `realpass` varchar(255) default NULL,
  `delete_flag` varchar(255) default NULL,
  `pserver_flag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_auxiliary` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `blockedip`
--

CREATE TABLE IF NOT EXISTS `blockedip` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `ipaddress` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_blockedip` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `client`
--

CREATE TABLE IF NOT EXISTS `client` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `priv_q_pserver_num` varchar(255) default NULL,
  `used_q_pserver_num` varchar(255) default NULL,
  `priv_q_client_num` varchar(255) default NULL,
  `used_q_client_num` varchar(255) default NULL,
  `priv_q_maindomain_num` varchar(255) default NULL,
  `used_q_maindomain_num` varchar(255) default NULL,
  `priv_q_domain_num` varchar(255) default NULL,
  `used_q_domain_num` varchar(255) default NULL,
  `priv_q_subdomain_num` varchar(255) default NULL,
  `used_q_subdomain_num` varchar(255) default NULL,
  `priv_q_clientdisk_usage` varchar(255) default NULL,
  `used_q_clientdisk_usage` varchar(255) default NULL,
  `priv_q_domain_add_flag` varchar(255) default NULL,
  `used_q_domain_add_flag` varchar(255) default NULL,
  `priv_q_can_change_limit_flag` varchar(255) default NULL,
  `used_q_can_change_limit_flag` varchar(255) default NULL,
  `priv_q_can_set_disabled_flag` varchar(255) default NULL,
  `used_q_can_set_disabled_flag` varchar(255) default NULL,
  `priv_q_can_change_password_flag` varchar(255) default NULL,
  `used_q_can_change_password_flag` varchar(255) default NULL,
  `priv_q_document_root_flag` varchar(255) default NULL,
  `used_q_document_root_flag` varchar(255) default NULL,
  `priv_q_runstats_flag` varchar(255) default NULL,
  `used_q_runstats_flag` varchar(255) default NULL,
  `priv_q_traffic_usage` varchar(255) default NULL,
  `used_q_traffic_usage` varchar(255) default NULL,
  `priv_q_totaldisk_usage` varchar(255) default NULL,
  `used_q_totaldisk_usage` varchar(255) default NULL,
  `priv_q_ssl_flag` varchar(255) default NULL,
  `used_q_ssl_flag` varchar(255) default NULL,
  `priv_q_rubyfcgiprocess_num` varchar(255) default NULL,
  `used_q_rubyfcgiprocess_num` varchar(255) default NULL,
  `priv_q_disk_usage` varchar(255) default NULL,
  `used_q_disk_usage` varchar(255) default NULL,
  `priv_q_logo_manage_flag` varchar(255) default NULL,
  `used_q_logo_manage_flag` varchar(255) default NULL,
  `priv_q_ftpuser_num` varchar(255) default NULL,
  `used_q_ftpuser_num` varchar(255) default NULL,
  `priv_q_frontpage_flag` varchar(255) default NULL,
  `used_q_frontpage_flag` varchar(255) default NULL,
  `priv_q_php_manage_flag` varchar(255) default NULL,
  `used_q_php_manage_flag` varchar(255) default NULL,
  `priv_q_inc_flag` varchar(255) default NULL,
  `used_q_inc_flag` varchar(255) default NULL,
  `priv_q_awstats_flag` varchar(255) default NULL,
  `used_q_awstats_flag` varchar(255) default NULL,
  `priv_q_installapp_flag` varchar(255) default NULL,
  `used_q_installapp_flag` varchar(255) default NULL,
  `priv_q_modperl_flag` varchar(255) default NULL,
  `used_q_modperl_flag` varchar(255) default NULL,
  `priv_q_cgi_flag` varchar(255) default NULL,
  `used_q_cgi_flag` varchar(255) default NULL,
  `priv_q_php_flag` varchar(255) default NULL,
  `used_q_php_flag` varchar(255) default NULL,
  `priv_q_phpunsafe_flag` varchar(255) default NULL,
  `used_q_phpunsafe_flag` varchar(255) default NULL,
  `priv_q_subweb_a_num` varchar(255) default NULL,
  `used_q_subweb_a_num` varchar(255) default NULL,
  `priv_q_dotnet_flag` varchar(255) default NULL,
  `used_q_dotnet_flag` varchar(255) default NULL,
  `priv_q_cron_num` varchar(255) default NULL,
  `used_q_cron_num` varchar(255) default NULL,
  `priv_q_cron_minute_flag` varchar(255) default NULL,
  `used_q_cron_minute_flag` varchar(255) default NULL,
  `priv_q_cron_manage_flag` varchar(255) default NULL,
  `used_q_cron_manage_flag` varchar(255) default NULL,
  `priv_q_phpfcgi_flag` varchar(255) default NULL,
  `used_q_phpfcgi_flag` varchar(255) default NULL,
  `priv_q_rubyrails_num` varchar(255) default NULL,
  `used_q_rubyrails_num` varchar(255) default NULL,
  `priv_q_phpfcgiprocess_num` varchar(255) default NULL,
  `used_q_phpfcgiprocess_num` varchar(255) default NULL,
  `priv_q_maildisk_usage` varchar(255) default NULL,
  `used_q_maildisk_usage` varchar(255) default NULL,
  `priv_q_autoresponder_num` varchar(255) default NULL,
  `used_q_autoresponder_num` varchar(255) default NULL,
  `priv_q_mailaccount_num` varchar(255) default NULL,
  `used_q_mailaccount_num` varchar(255) default NULL,
  `priv_q_mailinglist_num` varchar(255) default NULL,
  `used_q_mailinglist_num` varchar(255) default NULL,
  `priv_q_mysqldb_usage` varchar(255) default NULL,
  `used_q_mysqldb_usage` varchar(255) default NULL,
  `priv_q_mssqldb_usage` varchar(255) default NULL,
  `used_q_mssqldb_usage` varchar(255) default NULL,
  `priv_q_backupschedule_flag` varchar(255) default NULL,
  `used_q_backupschedule_flag` varchar(255) default NULL,
  `priv_q_traffic_last_usage` varchar(255) default NULL,
  `used_q_traffic_last_usage` varchar(255) default NULL,
  `priv_q_backup_flag` varchar(255) default NULL,
  `used_q_backup_flag` varchar(255) default NULL,
  `priv_q_dns_manage_flag` varchar(255) default NULL,
  `used_q_dns_manage_flag` varchar(255) default NULL,
  `priv_q_mysqldb_num` varchar(255) default NULL,
  `used_q_mysqldb_num` varchar(255) default NULL,
  `priv_q_mssqldb_num` varchar(255) default NULL,
  `used_q_mssqldb_num` varchar(255) default NULL,
  `priv_q_addondomain_num` varchar(255) default NULL,
  `used_q_addondomain_num` varchar(255) default NULL,
  `priv_q_webhosting_flag` varchar(255) default NULL,
  `used_q_webhosting_flag` varchar(255) default NULL,
  `realname` varchar(255) default NULL,
  `add_address` varchar(255) default NULL,
  `add_city` varchar(255) default NULL,
  `add_country` varchar(255) default NULL,
  `add_telephone` varchar(255) default NULL,
  `add_fax` varchar(255) default NULL,
  `password` varchar(255) default NULL,
  `cpstatus` varchar(255) default NULL,
  `demo_status` varchar(255) default NULL,
  `contactemail` varchar(255) default NULL,
  `text_comment` longtext,
  `disable_per` varchar(255) default NULL,
  `ser_hpfilter` longtext,
  `ddate` varchar(255) default NULL,
  `ser_dskhistory` longtext,
  `ser_dskshortcut_a` longtext,
  `interface_template` varchar(255) default NULL,
  `ser_boxpos` longtext,
  `dialogsize` varchar(255) default NULL,
  `cttype` varchar(255) default NULL,
  `ser_listpriv` longtext,
  `skeletonarchive` varchar(255) default NULL,
  `ser_dnstemplate_list` longtext,
  `state` varchar(255) default NULL,
  `status` varchar(255) default NULL,
  `disable_reason` varchar(255) default NULL,
  `disable_url` varchar(255) default NULL,
  `template_used` varchar(255) default NULL,
  `username` varchar(255) default NULL,
  `realpass` varchar(255) default NULL,
  `shell` varchar(255) default NULL,
  `default_domain` varchar(255) default NULL,
  `resourceplan_used` varchar(255) default NULL,
  `websyncserver` varchar(255) default NULL,
  `coma_dnssyncserver_list` text,
  `mmailsyncserver` varchar(255) default NULL,
  `mysqldbsyncserver` varchar(255) default NULL,
  `syncserver` varchar(255) default NULL,
  `cron_mailto` varchar(255) default NULL,
  `dnstemplate_name` varchar(255) default NULL,
  `corerootdir` varchar(255) default NULL,
  `disable_system_flag` varchar(255) default NULL,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_client` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `clienttemplate`
--

CREATE TABLE IF NOT EXISTS `clienttemplate` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `ser_priv` longtext,
  `share_status` varchar(255) default NULL,
  `disable_per` varchar(255) default NULL,
  `skin_name` varchar(255) default NULL,
  `description` varchar(255) default NULL,
  `ddate` varchar(255) default NULL,
  `ser_listpriv` longtext,
  `ttype` varchar(255) default NULL,
  `ser_dnstemplate_list` longtext,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_clienttemplate` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `component`
--

CREATE TABLE IF NOT EXISTS `component` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `componentname` varchar(255) default NULL,
  `syncserver` varchar(255) default NULL,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_component` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cron`
--

CREATE TABLE IF NOT EXISTS `cron` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `syncserver` varchar(255) default NULL,
  `email` varchar(255) default NULL,
  `ser_minute` longtext,
  `ser_hour` longtext,
  `ser_ddate` longtext,
  `ser_month` longtext,
  `ser_weekday` longtext,
  `jobid` varchar(255) default NULL,
  `username` varchar(255) default NULL,
  `command` varchar(255) default NULL,
  `argument` varchar(255) default NULL,
  `ttype` varchar(255) default NULL,
  `simple_cron` varchar(255) default NULL,
  `cron_day_hour` varchar(255) default NULL,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_cron` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `customaction`
--

CREATE TABLE IF NOT EXISTS `customaction` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `class` varchar(255) default NULL,
  `action` varchar(255) default NULL,
  `subaction` varchar(255) default NULL,
  `exec` varchar(255) default NULL,
  `where_to_exec` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_customaction` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `custombutton`
--

CREATE TABLE IF NOT EXISTS `custombutton` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `status` varchar(255) default NULL,
  `class` varchar(255) default NULL,
  `title` varchar(255) default NULL,
  `url` varchar(255) default NULL,
  `description` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_custombutton` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `davuser`
--

CREATE TABLE IF NOT EXISTS `davuser` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `realname` varchar(255) default NULL,
  `add_address` varchar(255) default NULL,
  `add_city` varchar(255) default NULL,
  `add_country` varchar(255) default NULL,
  `add_telephone` varchar(255) default NULL,
  `add_fax` varchar(255) default NULL,
  `password` varchar(255) default NULL,
  `cpstatus` varchar(255) default NULL,
  `demo_status` varchar(255) default NULL,
  `contactemail` varchar(255) default NULL,
  `text_comment` longtext,
  `disable_per` varchar(255) default NULL,
  `ser_hpfilter` longtext,
  `ddate` varchar(255) default NULL,
  `ser_dskhistory` longtext,
  `ser_dskshortcut_a` longtext,
  `interface_template` varchar(255) default NULL,
  `ser_boxpos` longtext,
  `dialogsize` varchar(255) default NULL,
  `state` varchar(255) default NULL,
  `status` varchar(255) default NULL,
  `disable_reason` varchar(255) default NULL,
  `realpass` varchar(255) default NULL,
  `username` varchar(255) default NULL,
  `directory` varchar(255) default NULL,
  `syncserver` varchar(255) default NULL,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_davuser` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `dbadmin`
--

CREATE TABLE IF NOT EXISTS `dbadmin` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `dbtype` varchar(255) default NULL,
  `dbadmin_name` varchar(255) default NULL,
  `dbpassword` varchar(255) default NULL,
  `syncserver` varchar(255) default NULL,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_dbadmin` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `dirprotect`
--

CREATE TABLE IF NOT EXISTS `dirprotect` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `syncserver` varchar(255) default NULL,
  `authname` varchar(255) default NULL,
  `subweb` varchar(255) default NULL,
  `path` varchar(255) default NULL,
  `status` varchar(255) default NULL,
  `ser_diruser_a` longtext,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_dirprotect` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `dns`
--

CREATE TABLE IF NOT EXISTS `dns` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `ser_mx_rec_a` longtext,
  `ser_ns_rec_a` longtext,
  `ser_a_rec_a` longtext,
  `ser_cn_rec_a` longtext,
  `ser_txt_rec_a` longtext,
  `ttl` varchar(255) default NULL,
  `soanameserver` varchar(255) default NULL,
  `zone_type` varchar(255) default NULL,
  `ser_dns_record_a` longtext,
  `syncserver` varchar(255) default NULL,
  `serial` varchar(255) default NULL,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_dns` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `dnstemplate`
--

CREATE TABLE IF NOT EXISTS `dnstemplate` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `ser_mx_rec_a` longtext,
  `ser_ns_rec_a` longtext,
  `ser_a_rec_a` longtext,
  `ser_cn_rec_a` longtext,
  `ser_txt_rec_a` longtext,
  `ttl` varchar(255) default NULL,
  `soanameserver` varchar(255) default NULL,
  `zone_type` varchar(255) default NULL,
  `ser_dns_record_a` longtext,
  `webipaddress` varchar(255) default NULL,
  `mmailipaddress` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_dnstemplate` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `domain`
--

CREATE TABLE IF NOT EXISTS `domain` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `priv_q_traffic_usage` varchar(255) default NULL,
  `used_q_traffic_usage` varchar(255) default NULL,
  `priv_q_totaldisk_usage` varchar(255) default NULL,
  `used_q_totaldisk_usage` varchar(255) default NULL,
  `priv_q_ssl_flag` varchar(255) default NULL,
  `used_q_ssl_flag` varchar(255) default NULL,
  `priv_q_rubyfcgiprocess_num` varchar(255) default NULL,
  `used_q_rubyfcgiprocess_num` varchar(255) default NULL,
  `priv_q_disk_usage` varchar(255) default NULL,
  `used_q_disk_usage` varchar(255) default NULL,
  `priv_q_logo_manage_flag` varchar(255) default NULL,
  `used_q_logo_manage_flag` varchar(255) default NULL,
  `priv_q_ftpuser_num` varchar(255) default NULL,
  `used_q_ftpuser_num` varchar(255) default NULL,
  `priv_q_frontpage_flag` varchar(255) default NULL,
  `used_q_frontpage_flag` varchar(255) default NULL,
  `priv_q_php_manage_flag` varchar(255) default NULL,
  `used_q_php_manage_flag` varchar(255) default NULL,
  `priv_q_inc_flag` varchar(255) default NULL,
  `used_q_inc_flag` varchar(255) default NULL,
  `priv_q_awstats_flag` varchar(255) default NULL,
  `used_q_awstats_flag` varchar(255) default NULL,
  `priv_q_installapp_flag` varchar(255) default NULL,
  `used_q_installapp_flag` varchar(255) default NULL,
  `priv_q_modperl_flag` varchar(255) default NULL,
  `used_q_modperl_flag` varchar(255) default NULL,
  `priv_q_cgi_flag` varchar(255) default NULL,
  `used_q_cgi_flag` varchar(255) default NULL,
  `priv_q_php_flag` varchar(255) default NULL,
  `used_q_php_flag` varchar(255) default NULL,
  `priv_q_phpunsafe_flag` varchar(255) default NULL,
  `used_q_phpunsafe_flag` varchar(255) default NULL,
  `priv_q_subweb_a_num` varchar(255) default NULL,
  `used_q_subweb_a_num` varchar(255) default NULL,
  `priv_q_dotnet_flag` varchar(255) default NULL,
  `used_q_dotnet_flag` varchar(255) default NULL,
  `priv_q_cron_num` varchar(255) default NULL,
  `used_q_cron_num` varchar(255) default NULL,
  `priv_q_cron_minute_flag` varchar(255) default NULL,
  `used_q_cron_minute_flag` varchar(255) default NULL,
  `priv_q_cron_manage_flag` varchar(255) default NULL,
  `used_q_cron_manage_flag` varchar(255) default NULL,
  `priv_q_phpfcgi_flag` varchar(255) default NULL,
  `used_q_phpfcgi_flag` varchar(255) default NULL,
  `priv_q_rubyrails_num` varchar(255) default NULL,
  `used_q_rubyrails_num` varchar(255) default NULL,
  `priv_q_phpfcgiprocess_num` varchar(255) default NULL,
  `used_q_phpfcgiprocess_num` varchar(255) default NULL,
  `priv_q_maildisk_usage` varchar(255) default NULL,
  `used_q_maildisk_usage` varchar(255) default NULL,
  `priv_q_autoresponder_num` varchar(255) default NULL,
  `used_q_autoresponder_num` varchar(255) default NULL,
  `priv_q_mailaccount_num` varchar(255) default NULL,
  `used_q_mailaccount_num` varchar(255) default NULL,
  `priv_q_mailinglist_num` varchar(255) default NULL,
  `used_q_mailinglist_num` varchar(255) default NULL,
  `priv_q_mysqldb_usage` varchar(255) default NULL,
  `used_q_mysqldb_usage` varchar(255) default NULL,
  `priv_q_mssqldb_usage` varchar(255) default NULL,
  `used_q_mssqldb_usage` varchar(255) default NULL,
  `priv_q_backupschedule_flag` varchar(255) default NULL,
  `used_q_backupschedule_flag` varchar(255) default NULL,
  `priv_q_traffic_last_usage` varchar(255) default NULL,
  `used_q_traffic_last_usage` varchar(255) default NULL,
  `priv_q_backup_flag` varchar(255) default NULL,
  `used_q_backup_flag` varchar(255) default NULL,
  `priv_q_dns_manage_flag` varchar(255) default NULL,
  `used_q_dns_manage_flag` varchar(255) default NULL,
  `priv_q_mysqldb_num` varchar(255) default NULL,
  `used_q_mysqldb_num` varchar(255) default NULL,
  `priv_q_mssqldb_num` varchar(255) default NULL,
  `used_q_mssqldb_num` varchar(255) default NULL,
  `priv_q_addondomain_num` varchar(255) default NULL,
  `used_q_addondomain_num` varchar(255) default NULL,
  `realname` varchar(255) default NULL,
  `add_address` varchar(255) default NULL,
  `add_city` varchar(255) default NULL,
  `add_country` varchar(255) default NULL,
  `add_telephone` varchar(255) default NULL,
  `add_fax` varchar(255) default NULL,
  `password` varchar(255) default NULL,
  `cpstatus` varchar(255) default NULL,
  `demo_status` varchar(255) default NULL,
  `contactemail` varchar(255) default NULL,
  `text_comment` longtext,
  `disable_per` varchar(255) default NULL,
  `ser_hpfilter` longtext,
  `ddate` varchar(255) default NULL,
  `ser_dskhistory` longtext,
  `ser_dskshortcut_a` longtext,
  `interface_template` varchar(255) default NULL,
  `ser_boxpos` longtext,
  `dialogsize` varchar(255) default NULL,
  `state` varchar(255) default NULL,
  `status` varchar(255) default NULL,
  `disable_reason` varchar(255) default NULL,
  `ser_listpriv` longtext,
  `mmailpserver` varchar(255) default NULL,
  `webpserver` varchar(255) default NULL,
  `dnspserver` varchar(255) default NULL,
  `secdnspserver` varchar(255) default NULL,
  `ttype` varchar(255) default NULL,
  `username` varchar(255) default NULL,
  `nameserver` varchar(255) default NULL,
  `redirect_domain` varchar(255) default NULL,
  `template_used` varchar(255) default NULL,
  `realpass` varchar(255) default NULL,
  `dtype` varchar(255) default NULL,
  `subdomain_parent` varchar(255) default NULL,
  `resourceplan_used` varchar(255) default NULL,
  `previewdomain` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_domain` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `domaindefault`
--

CREATE TABLE IF NOT EXISTS `domaindefault` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `remove_processed_stats` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_domaindefault` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `domainipaddress`
--

CREATE TABLE IF NOT EXISTS `domainipaddress` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `domain` varchar(255) default NULL,
  `ipaddr` varchar(255) default NULL,
  `syncserver` varchar(255) default NULL,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_domainipaddress` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `domaintemplate`
--

CREATE TABLE IF NOT EXISTS `domaintemplate` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `ser_priv` longtext,
  `share_status` varchar(255) default NULL,
  `disable_per` varchar(255) default NULL,
  `skin_name` varchar(255) default NULL,
  `description` varchar(255) default NULL,
  `ddate` varchar(255) default NULL,
  `ser_listpriv` longtext,
  `ttype` varchar(255) default NULL,
  `ser_dnstemplate_list` longtext,
  `dnstemplate` varchar(255) default NULL,
  `ipaddress` varchar(255) default NULL,
  `redirect_domain` varchar(255) default NULL,
  `catchall` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_domaintemplate` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `domaintraffic`
--

CREATE TABLE IF NOT EXISTS `domaintraffic` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `ddate` varchar(255) default NULL,
  `oldtimestamp` varchar(255) default NULL,
  `timestamp` varchar(255) default NULL,
  `webtraffic_usage` varchar(255) default NULL,
  `mailtraffic_usage` varchar(255) default NULL,
  `ftptraffic_usage` varchar(255) default NULL,
  `traffic_usage` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_domaintraffic` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `driver`
--

CREATE TABLE IF NOT EXISTS `driver` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `ser_driver_b` longtext,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_driver` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `firewall`
--

CREATE TABLE IF NOT EXISTS `firewall` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `status` varchar(255) default NULL,
  `id` varchar(255) default NULL,
  `syncserver` varchar(255) default NULL,
  `from_address` varchar(255) default NULL,
  `from_port` varchar(255) default NULL,
  `to_address` varchar(255) default NULL,
  `to_port` varchar(255) default NULL,
  `action` varchar(255) default NULL,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_firewall` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ftpuser`
--

CREATE TABLE IF NOT EXISTS `ftpuser` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `priv_q_logo_manage_flag` varchar(255) default NULL,
  `used_q_logo_manage_flag` varchar(255) default NULL,
  `realname` varchar(255) default NULL,
  `add_address` varchar(255) default NULL,
  `add_city` varchar(255) default NULL,
  `add_country` varchar(255) default NULL,
  `add_telephone` varchar(255) default NULL,
  `add_fax` varchar(255) default NULL,
  `password` varchar(255) default NULL,
  `cpstatus` varchar(255) default NULL,
  `demo_status` varchar(255) default NULL,
  `contactemail` varchar(255) default NULL,
  `text_comment` longtext,
  `disable_per` varchar(255) default NULL,
  `ser_hpfilter` longtext,
  `ddate` varchar(255) default NULL,
  `ser_dskhistory` longtext,
  `ser_dskshortcut_a` longtext,
  `interface_template` varchar(255) default NULL,
  `ser_boxpos` longtext,
  `dialogsize` varchar(255) default NULL,
  `state` varchar(255) default NULL,
  `status` varchar(255) default NULL,
  `disable_reason` varchar(255) default NULL,
  `realpass` varchar(255) default NULL,
  `username` varchar(255) default NULL,
  `directory` varchar(255) default NULL,
  `syncserver` varchar(255) default NULL,
  `ftp_disk_usage` varchar(255) default NULL,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_ftpuser` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `general`
--

CREATE TABLE IF NOT EXISTS `general` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `ser_generalmisc_b` longtext,
  `ser_helpdeskcategory_a` longtext,
  `ser_reversedns_b` longtext,
  `ser_selfbackupparam_b` longtext,
  `ser_hackbuttonconfig_b` longtext,
  `ser_customaction_b` longtext,
  `text_maintenance_message` longtext,
  `ser_portconfig_b` longtext,
  `ser_kloxoconfig_b` longtext,
  `ser_browsebackup_b` longtext,
  `login_pre` varchar(255) default NULL,
  `ser_lxadminconfig_b` longtext,
  `disable_admin` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_general` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `genlist`
--

CREATE TABLE IF NOT EXISTS `genlist` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `ser_dirindexlist_a` longtext,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_genlist` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `hostdeny`
--

CREATE TABLE IF NOT EXISTS `hostdeny` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `syncserver` varchar(255) default NULL,
  `hostname` varchar(255) default NULL,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_hostdeny` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `installsoft`
--

CREATE TABLE IF NOT EXISTS `installsoft` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `appname` varchar(255) default NULL,
  `ddate` varchar(255) default NULL,
  `dbprefix` varchar(255) default NULL,
  `dbname` varchar(255) default NULL,
  `installdir` varchar(255) default NULL,
  `version` varchar(255) default NULL,
  `dbhost` varchar(255) default NULL,
  `realhost` varchar(255) default NULL,
  `ser_installsoftmisc_b` longtext,
  `syncserver` varchar(255) default NULL,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_installsoft` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `interface_template`
--

CREATE TABLE IF NOT EXISTS `interface_template` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `ser_domain_show` text,
  `ser_client_show` text,
  `ser_vps_show` text,
  `ser_domain_show_list` text,
  `ser_client_show_list` text,
  `ser_vps_show_list` text,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_interface_template` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ipaddress`
--

CREATE TABLE IF NOT EXISTS `ipaddress` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `devname` varchar(255) default NULL,
  `bproto` varchar(255) default NULL,
  `ipaddr` varchar(255) default NULL,
  `client_num` varchar(255) default NULL,
  `shared` varchar(255) default NULL,
  `netmask` varchar(255) default NULL,
  `status` varchar(255) default NULL,
  `userctl` varchar(255) default NULL,
  `peerdns` varchar(255) default NULL,
  `gateway` varchar(255) default NULL,
  `itype` varchar(255) default NULL,
  `ipv6init` varchar(255) default NULL,
  `syncserver` varchar(255) default NULL,
  `clientname` varchar(255) default NULL,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_ipaddress` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `license`
--

CREATE TABLE IF NOT EXISTS `license` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `ser_licensecom_b` longtext,
  `text_license_content` longtext,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_license` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `llog`
--

CREATE TABLE IF NOT EXISTS `llog` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `syncserver` varchar(255) default NULL,
  `email` varchar(255) default NULL,
  `period` varchar(255) default NULL,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_llog` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `loginattempt`
--

CREATE TABLE IF NOT EXISTS `loginattempt` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `count` varchar(255) default NULL,
  `client_name` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_loginattempt` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lxbackup`
--

CREATE TABLE IF NOT EXISTS `lxbackup` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `priv_q_backupschedule_flag` varchar(255) default NULL,
  `used_q_backupschedule_flag` varchar(255) default NULL,
  `ftp_server` varchar(255) default NULL,
  `ssh_server` varchar(255) default NULL,
  `rm_username` varchar(255) default NULL,
  `rm_password` varchar(255) default NULL,
  `rm_directory` varchar(255) default NULL,
  `upload_type` varchar(255) default NULL,
  `send_email` varchar(255) default NULL,
  `upload_to_ftp` varchar(255) default NULL,
  `backupstage` varchar(255) default NULL,
  `backuptype` varchar(255) default NULL,
  `backupschedule_type` varchar(255) default NULL,
  `rm_last_number` varchar(255) default NULL,
  `ser_lxbackupmisc_b` longtext,
  `restorestage` varchar(255) default NULL,
  `no_local_copy_flag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_lxbackup` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lxguard`
--

CREATE TABLE IF NOT EXISTS `lxguard` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `configure_flag` varchar(255) default NULL,
  `disablehit` varchar(255) default NULL,
  `syncserver` varchar(255) default NULL,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_lxguard` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lxguardhit`
--

CREATE TABLE IF NOT EXISTS `lxguardhit` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `access` varchar(255) default NULL,
  `service` varchar(255) default NULL,
  `ddate` varchar(255) default NULL,
  `ipaddress` varchar(255) default NULL,
  `user` varchar(255) default NULL,
  `syncserver` varchar(255) default NULL,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_lxguardhit` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lxguardwhitelist`
--

CREATE TABLE IF NOT EXISTS `lxguardwhitelist` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `ipaddress` varchar(255) default NULL,
  `syncserver` varchar(255) default NULL,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_lxguardwhitelist` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lxupdate`
--

CREATE TABLE IF NOT EXISTS `lxupdate` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `schedule` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_lxupdate` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mailaccount`
--

CREATE TABLE IF NOT EXISTS `mailaccount` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `priv_q_logo_manage_flag` varchar(255) default NULL,
  `used_q_logo_manage_flag` varchar(255) default NULL,
  `priv_q_maildisk_usage` varchar(255) default NULL,
  `used_q_maildisk_usage` varchar(255) default NULL,
  `priv_q_autoresponder_num` varchar(255) default NULL,
  `used_q_autoresponder_num` varchar(255) default NULL,
  `realname` varchar(255) default NULL,
  `add_address` varchar(255) default NULL,
  `add_city` varchar(255) default NULL,
  `add_country` varchar(255) default NULL,
  `add_telephone` varchar(255) default NULL,
  `add_fax` varchar(255) default NULL,
  `password` varchar(255) default NULL,
  `cpstatus` varchar(255) default NULL,
  `demo_status` varchar(255) default NULL,
  `contactemail` varchar(255) default NULL,
  `text_comment` longtext,
  `disable_per` varchar(255) default NULL,
  `ser_hpfilter` longtext,
  `ddate` varchar(255) default NULL,
  `ser_dskhistory` longtext,
  `ser_dskshortcut_a` longtext,
  `interface_template` varchar(255) default NULL,
  `ser_boxpos` longtext,
  `dialogsize` varchar(255) default NULL,
  `state` varchar(255) default NULL,
  `status` varchar(255) default NULL,
  `disable_reason` varchar(255) default NULL,
  `realpass` varchar(255) default NULL,
  `syncserver` varchar(255) default NULL,
  `forward_status` varchar(255) default NULL,
  `ser_forward_a` longtext,
  `autorespond_status` varchar(255) default NULL,
  `autores_name` varchar(255) default NULL,
  `filter_spam_status` varchar(255) default NULL,
  `no_local_copy` varchar(255) default NULL,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_mailaccount` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mailfilter`
--

CREATE TABLE IF NOT EXISTS `mailfilter` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `rule` varchar(255) default NULL,
  `action` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_mailfilter` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mailforward`
--

CREATE TABLE IF NOT EXISTS `mailforward` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `accountname` varchar(255) default NULL,
  `forwardaddress` varchar(255) default NULL,
  `syncserver` varchar(255) default NULL,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_mailforward` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mailinglist`
--

CREATE TABLE IF NOT EXISTS `mailinglist` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `syncserver` varchar(255) default NULL,
  `listname` varchar(255) default NULL,
  `adminemail` varchar(255) default NULL,
  `lang` varchar(255) default NULL,
  `post_members_only_flag` varchar(255) default NULL,
  `post_moderated_flag` varchar(255) default NULL,
  `post_moderator_only_flag` varchar(255) default NULL,
  `archived_flag` varchar(255) default NULL,
  `archive_blocked_flag` varchar(255) default NULL,
  `archive_guarded_flag` varchar(255) default NULL,
  `digest_flag` varchar(255) default NULL,
  `jumpoff_flag` varchar(255) default NULL,
  `subscriberlist_flag` varchar(255) default NULL,
  `remote_admin_flag` varchar(255) default NULL,
  `subscription_mod_flag` varchar(255) default NULL,
  `edit_text_flag` varchar(255) default NULL,
  `coma_mailinglist_mod_a` text,
  `text_trailer` longtext,
  `text_prefix` longtext,
  `max_msg_size` varchar(255) default NULL,
  `min_msg_size` varchar(255) default NULL,
  `text_mimeremove` longtext,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_mailinglist` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mimetype`
--

CREATE TABLE IF NOT EXISTS `mimetype` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `domainname` varchar(255) default NULL,
  `type` varchar(255) default NULL,
  `extension` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_mimetype` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mmail`
--

CREATE TABLE IF NOT EXISTS `mmail` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `priv_q_logo_manage_flag` varchar(255) default NULL,
  `used_q_logo_manage_flag` varchar(255) default NULL,
  `priv_q_maildisk_usage` varchar(255) default NULL,
  `used_q_maildisk_usage` varchar(255) default NULL,
  `priv_q_autoresponder_num` varchar(255) default NULL,
  `used_q_autoresponder_num` varchar(255) default NULL,
  `priv_q_mailaccount_num` varchar(255) default NULL,
  `used_q_mailaccount_num` varchar(255) default NULL,
  `priv_q_mailinglist_num` varchar(255) default NULL,
  `used_q_mailinglist_num` varchar(255) default NULL,
  `webmailprog` varchar(255) default NULL,
  `catchall` varchar(255) default NULL,
  `remotelocalflag` varchar(255) default NULL,
  `catchall_status` varchar(255) default NULL,
  `username` varchar(255) default NULL,
  `status` varchar(255) default NULL,
  `redirect_address` varchar(255) default NULL,
  `syncserver` varchar(255) default NULL,
  `ttype` varchar(255) default NULL,
  `redirect_domain` varchar(255) default NULL,
  `webmail_url` varchar(255) default NULL,
  `systemuser` varchar(255) default NULL,
  `enable_spf_flag` varchar(255) default NULL,
  `exclude_all` varchar(255) default NULL,
  `text_spf_domain` longtext,
  `text_spf_ip` longtext,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_mmail` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `module`
--

CREATE TABLE IF NOT EXISTS `module` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `status` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_module` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mssqldb`
--

CREATE TABLE IF NOT EXISTS `mssqldb` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `priv_q_mssqldb_usage` varchar(255) default NULL,
  `used_q_mssqldb_usage` varchar(255) default NULL,
  `username` varchar(255) default NULL,
  `dbname` varchar(255) default NULL,
  `dbtype` varchar(255) default NULL,
  `dbpassword` varchar(255) default NULL,
  `installsoft_flag` varchar(255) default NULL,
  `installsoft_app` varchar(255) default NULL,
  `syncserver` varchar(255) default NULL,
  `password` varchar(255) default NULL,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_mssqldb` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mssqldbuser`
--

CREATE TABLE IF NOT EXISTS `mssqldbuser` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `username` varchar(255) default NULL,
  `dbname` varchar(255) default NULL,
  `dbpassword` varchar(255) default NULL,
  `ser_dbpermission_b` longtext,
  `syncserver` varchar(255) default NULL,
  `ser_dbhostlist_a` longtext,
  `password` varchar(255) default NULL,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_mssqldbuser` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mysqldb`
--

CREATE TABLE IF NOT EXISTS `mysqldb` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `priv_q_mysqldb_usage` varchar(255) default NULL,
  `used_q_mysqldb_usage` varchar(255) default NULL,
  `primarydb` varchar(255) default NULL,
  `username` varchar(255) default NULL,
  `dbname` varchar(255) default NULL,
  `dbtype` varchar(255) default NULL,
  `dbpassword` varchar(255) default NULL,
  `installsoft_flag` varchar(255) default NULL,
  `installsoft_app` varchar(255) default NULL,
  `syncserver` varchar(255) default NULL,
  `password` varchar(255) default NULL,
  `no_backup_flag` varchar(255) default NULL,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_mysqldb` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mysqldbuser`
--

CREATE TABLE IF NOT EXISTS `mysqldbuser` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `username` varchar(255) default NULL,
  `dbname` varchar(255) default NULL,
  `dbpassword` varchar(255) default NULL,
  `ser_dbpermission_b` longtext,
  `syncserver` varchar(255) default NULL,
  `ser_dbhostlist_a` longtext,
  `password` varchar(255) default NULL,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_mysqldbuser` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ndskshortcut`
--

CREATE TABLE IF NOT EXISTS `ndskshortcut` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `ddate` varchar(255) default NULL,
  `ttype` varchar(255) default NULL,
  `sortid` varchar(255) default NULL,
  `separatorid` varchar(255) default NULL,
  `description` varchar(255) default NULL,
  `external` varchar(255) default NULL,
  `vpsparent_clname` varchar(255) default NULL,
  `url` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_ndskshortcut` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ndsktoolbar`
--

CREATE TABLE IF NOT EXISTS `ndsktoolbar` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `ddate` varchar(255) default NULL,
  `ttype` varchar(255) default NULL,
  `description` varchar(255) default NULL,
  `external` varchar(255) default NULL,
  `vpsparent_clname` varchar(255) default NULL,
  `url` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_ndsktoolbar` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE IF NOT EXISTS `notification` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `ser_notflag_b` longtext,
  `text_newsubject` longtext,
  `text_newaccountmessage` longtext,
  `fromaddress` varchar(255) default NULL,
  `coma_class_list` text,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_notification` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `odbc`
--

CREATE TABLE IF NOT EXISTS `odbc` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `odbcname` varchar(255) default NULL,
  `description` varchar(255) default NULL,
  `driver` varchar(255) default NULL,
  `ser_odbcdetails_b` longtext,
  `syncserver` varchar(255) default NULL,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_odbc` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `phpini`
--

CREATE TABLE IF NOT EXISTS `phpini` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `php_manage_flag` varchar(255) default NULL,
  `enable_zend_flag` varchar(255) default NULL,
  `enable_ioncube_flag` varchar(255) default NULL,
  `register_global_flag` varchar(255) default NULL,
  `syncserver` varchar(255) default NULL,
  `display_error_flag` varchar(255) default NULL,
  `ser_phpini_flag_b` longtext,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_phpini` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `proxy`
--

CREATE TABLE IF NOT EXISTS `proxy` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_proxy` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `proxyacl`
--

CREATE TABLE IF NOT EXISTS `proxyacl` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `syncserver` varchar(255) default NULL,
  `status` varchar(255) default NULL,
  `ttype` varchar(255) default NULL,
  `classid` varchar(255) default NULL,
  `description` varchar(255) default NULL,
  `password` varchar(255) default NULL,
  `http` varchar(255) default NULL,
  `ftp` varchar(255) default NULL,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_proxyacl` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pserver`
--

CREATE TABLE IF NOT EXISTS `pserver` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `priv_q_logo_manage_flag` varchar(255) default NULL,
  `used_q_logo_manage_flag` varchar(255) default NULL,
  `realname` varchar(255) default NULL,
  `add_address` varchar(255) default NULL,
  `add_city` varchar(255) default NULL,
  `add_country` varchar(255) default NULL,
  `add_telephone` varchar(255) default NULL,
  `add_fax` varchar(255) default NULL,
  `password` varchar(255) default NULL,
  `cpstatus` varchar(255) default NULL,
  `demo_status` varchar(255) default NULL,
  `contactemail` varchar(255) default NULL,
  `text_comment` longtext,
  `disable_per` varchar(255) default NULL,
  `ser_hpfilter` longtext,
  `ddate` varchar(255) default NULL,
  `ser_dskhistory` longtext,
  `ser_dskshortcut_a` longtext,
  `interface_template` varchar(255) default NULL,
  `ser_boxpos` longtext,
  `dialogsize` varchar(255) default NULL,
  `ostype` varchar(255) default NULL,
  `osversion` varchar(255) default NULL,
  `dbadmin` varchar(255) default NULL,
  `dbpassword` varchar(255) default NULL,
  `realpass` varchar(255) default NULL,
  `username` varchar(255) default NULL,
  `ser_rolelist` longtext,
  `cron_mailto` varchar(255) default NULL,
  `syncserver` varchar(255) default NULL,
  `ser_pserverconf_b` longtext,
  `hostname` varchar(255) default NULL,
  `description` varchar(255) default NULL,
  `realhostname` varchar(255) default NULL,
  `timezone` varchar(255) default NULL,
  `coma_psrole_a` text,
  `load_threshold` varchar(255) default NULL,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_pserver` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `rdnsrange`
--

CREATE TABLE IF NOT EXISTS `rdnsrange` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `firstip` varchar(255) default NULL,
  `lastip` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_rdnsrange` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `resourceplan`
--

CREATE TABLE IF NOT EXISTS `resourceplan` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `realname` varchar(255) default NULL,
  `ser_priv` longtext,
  `description` varchar(255) default NULL,
  `ddate` varchar(255) default NULL,
  `ser_listpriv` longtext,
  `ser_dnstemplate_list` longtext,
  `status` varchar(255) default NULL,
  `disable_per` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_resourceplan` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `reversedns`
--

CREATE TABLE IF NOT EXISTS `reversedns` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `reversename` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_reversedns` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `rubyrails`
--

CREATE TABLE IF NOT EXISTS `rubyrails` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `priv_q_rubyfcgiprocess_num` varchar(255) default NULL,
  `used_q_rubyfcgiprocess_num` varchar(255) default NULL,
  `appname` varchar(255) default NULL,
  `port` varchar(255) default NULL,
  `syncserver` varchar(255) default NULL,
  `accessible_directly` varchar(255) default NULL,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_rubyrails` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `serverftp`
--

CREATE TABLE IF NOT EXISTS `serverftp` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `maxclient` varchar(255) default NULL,
  `highport` varchar(255) default NULL,
  `lowport` varchar(255) default NULL,
  `enable_anon_ftp` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_serverftp` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `servermail`
--

CREATE TABLE IF NOT EXISTS `servermail` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `smtp_instance` varchar(255) default NULL,
  `enable_maps` varchar(255) default NULL,
  `domainkey_flag` varchar(255) default NULL,
  `additional_smtp_port` varchar(255) default NULL,
  `queuelifetime` varchar(255) default NULL,
  `concurrencyremote` varchar(255) default NULL,
  `spamdyke_flag` varchar(255) default NULL,
  `syncserver` varchar(255) default NULL,
  `myname` varchar(255) default NULL,
  `virus_scan_flag` varchar(255) default NULL,
  `max_size` varchar(255) default NULL,
  `greet_delay` varchar(255) default NULL,
  `graylist_flag` varchar(255) default NULL,
  `graylist_max_secs` varchar(255) default NULL,
  `graylist_min_secs` varchar(255) default NULL,
  `coma_mail_graylist_wlist_a` text,
  `max_rcpnts` varchar(255) default NULL,
  `reject_unresolvable_rdns_flag` varchar(255) default NULL,
  `reject_missing_sender_mx_flag` varchar(255) default NULL,
  `reject_ip_in_cc_rdns_flag` varchar(255) default NULL,
  `reject_empty_rdns_flag` varchar(255) default NULL,
  `dns_blacklists` varchar(255) default NULL,
  `alt_smtp_sdyke_flag` varchar(255) default NULL,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_servermail` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `serverspam`
--

CREATE TABLE IF NOT EXISTS `serverspam` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `status` varchar(255) default NULL,
  `spam_hit` varchar(255) default NULL,
  `subject_tag` varchar(255) default NULL,
  `ser_wlist_a` longtext,
  `ser_blist_a` longtext,
  `syncserver` varchar(255) default NULL,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_serverspam` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `serverweb`
--

CREATE TABLE IF NOT EXISTS `serverweb` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `php_type` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_serverweb` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `service`
--

CREATE TABLE IF NOT EXISTS `service` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `servicename` varchar(255) default NULL,
  `description` varchar(255) default NULL,
  `grepstring` varchar(255) default NULL,
  `syncserver` varchar(255) default NULL,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_service` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `skipbackup`
--

CREATE TABLE IF NOT EXISTS `skipbackup` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `clname` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_skipbackup` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `smessage`
--

CREATE TABLE IF NOT EXISTS `smessage` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `made_by` varchar(255) default NULL,
  `text_readby_cmlist` longtext,
  `text_sent_to_cmlist` longtext,
  `subject` varchar(255) default NULL,
  `text_description` longtext,
  `category` varchar(255) default NULL,
  `ddate` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_smessage` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `spam`
--

CREATE TABLE IF NOT EXISTS `spam` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `status` varchar(255) default NULL,
  `spam_hit` varchar(255) default NULL,
  `subject_tag` varchar(255) default NULL,
  `ser_wlist_a` longtext,
  `ser_blist_a` longtext,
  `syncserver` varchar(255) default NULL,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_spam` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `sp_childspecialplay`
--

CREATE TABLE IF NOT EXISTS `sp_childspecialplay` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `ser_specialplay_b` longtext,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_sp_childspecialplay` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `sp_lstclass`
--

CREATE TABLE IF NOT EXISTS `sp_lstclass` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `ser_lst_client_list` longtext,
  `ser_lst_vps_list` longtext,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_sp_lstclass` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `sp_specialplay`
--

CREATE TABLE IF NOT EXISTS `sp_specialplay` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `ser_specialplay_b` longtext,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_sp_specialplay` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ssession`
--

CREATE TABLE IF NOT EXISTS `ssession` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `cttype` varchar(255) default NULL,
  `ip_address` varchar(255) default NULL,
  `timeout` varchar(255) default NULL,
  `last_access` varchar(255) default NULL,
  `logintime` varchar(255) default NULL,
  `ser_http_vars` longtext,
  `ser_ssession_vars` longtext,
  `tsessionid` varchar(255) default NULL,
  `auxiliary_id` varchar(255) default NULL,
  `ser_ssl_param` longtext,
  `consuming_parent` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_ssession` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `sshconfig`
--

CREATE TABLE IF NOT EXISTS `sshconfig` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `ssh_port` varchar(255) default NULL,
  `without_password_flag` varchar(255) default NULL,
  `disable_password_flag` varchar(255) default NULL,
  `config_flag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_sshconfig` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `sslcert`
--

CREATE TABLE IF NOT EXISTS `sslcert` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `upload_status` varchar(255) default NULL,
  `certname` varchar(255) default NULL,
  `syncserver` varchar(255) default NULL,
  `ser_ssl_data_b` longtext,
  `text_crt_content` longtext,
  `text_key_content` longtext,
  `text_csr_content` longtext,
  `text_ca_content` longtext,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_sslcert` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `sslipaddress`
--

CREATE TABLE IF NOT EXISTS `sslipaddress` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `devname` varchar(255) default NULL,
  `ipaddr` varchar(255) default NULL,
  `sslclient` varchar(255) default NULL,
  `ssldomain` varchar(255) default NULL,
  `sslcert` varchar(255) default NULL,
  `syncserver` varchar(255) default NULL,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_sslipaddress` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ticket`
--

CREATE TABLE IF NOT EXISTS `ticket` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `password` varchar(255) default NULL,
  `escalate` varchar(255) default NULL,
  `realpass` varchar(255) default NULL,
  `status` varchar(255) default NULL,
  `cpstatus` varchar(255) default NULL,
  `state` varchar(255) default NULL,
  `priority` varchar(255) default NULL,
  `responsible` varchar(255) default NULL,
  `made_by` varchar(255) default NULL,
  `sent_to` varchar(255) default NULL,
  `date_modified` varchar(255) default NULL,
  `unread_flag` varchar(255) default NULL,
  `history_num` varchar(255) default NULL,
  `subject` varchar(255) default NULL,
  `category` varchar(255) default NULL,
  `ddate` varchar(255) default NULL,
  `mail_messageid` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_ticket` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ticketconfig`
--

CREATE TABLE IF NOT EXISTS `ticketconfig` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `ticketid` varchar(255) default NULL,
  `ser_category_list_a` longtext,
  `mail_account` varchar(255) default NULL,
  `mail_server` varchar(255) default NULL,
  `mail_password` varchar(255) default NULL,
  `mail_period` varchar(255) default NULL,
  `mail_enable` varchar(255) default NULL,
  `mail_ssl_flag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_ticketconfig` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tickethistory`
--

CREATE TABLE IF NOT EXISTS `tickethistory` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `made_by` varchar(255) default NULL,
  `state_from` varchar(255) default NULL,
  `state` varchar(255) default NULL,
  `text_reason` longtext,
  `ddate` varchar(255) default NULL,
  `from_ad` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_tickethistory` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `utmp`
--

CREATE TABLE IF NOT EXISTS `utmp` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `ssession_name` varchar(255) default NULL,
  `cttype` varchar(255) default NULL,
  `logintime` varchar(255) default NULL,
  `timeout` varchar(255) default NULL,
  `logouttime` varchar(255) default NULL,
  `ip_address` varchar(255) default NULL,
  `logoutreason` varchar(255) default NULL,
  `auxiliary_id` varchar(255) default NULL,
  `consuming_parent` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_utmp` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `uuser`
--

CREATE TABLE IF NOT EXISTS `uuser` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `priv_q_disk_usage` varchar(255) default NULL,
  `used_q_disk_usage` varchar(255) default NULL,
  `priv_q_logo_manage_flag` varchar(255) default NULL,
  `used_q_logo_manage_flag` varchar(255) default NULL,
  `realname` varchar(255) default NULL,
  `add_address` varchar(255) default NULL,
  `add_city` varchar(255) default NULL,
  `add_country` varchar(255) default NULL,
  `add_telephone` varchar(255) default NULL,
  `add_fax` varchar(255) default NULL,
  `password` varchar(255) default NULL,
  `cpstatus` varchar(255) default NULL,
  `demo_status` varchar(255) default NULL,
  `contactemail` varchar(255) default NULL,
  `text_comment` longtext,
  `disable_per` varchar(255) default NULL,
  `ser_hpfilter` longtext,
  `ddate` varchar(255) default NULL,
  `ser_dskhistory` longtext,
  `ser_dskshortcut_a` longtext,
  `interface_template` varchar(255) default NULL,
  `ser_boxpos` longtext,
  `dialogsize` varchar(255) default NULL,
  `realpass` varchar(255) default NULL,
  `shellflag` varchar(255) default NULL,
  `shell` varchar(255) default NULL,
  `syncserver` varchar(255) default NULL,
  `status` varchar(255) default NULL,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_uuser` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `version`
--

CREATE TABLE IF NOT EXISTS `version` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `major` varchar(255) default NULL,
  `minor` varchar(255) default NULL,
  `releasen` varchar(255) default NULL,
  `extra` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_version` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `watchdog`
--

CREATE TABLE IF NOT EXISTS `watchdog` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `servicename` varchar(255) default NULL,
  `syncserver` varchar(255) default NULL,
  `port` varchar(255) default NULL,
  `action` varchar(255) default NULL,
  `status` varchar(255) default NULL,
  `added_by_system` varchar(255) default NULL,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_watchdog` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `web`
--

CREATE TABLE IF NOT EXISTS `web` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `priv_q_totaldisk_usage` varchar(255) default NULL,
  `used_q_totaldisk_usage` varchar(255) default NULL,
  `priv_q_ssl_flag` varchar(255) default NULL,
  `used_q_ssl_flag` varchar(255) default NULL,
  `priv_q_rubyfcgiprocess_num` varchar(255) default NULL,
  `used_q_rubyfcgiprocess_num` varchar(255) default NULL,
  `priv_q_disk_usage` varchar(255) default NULL,
  `used_q_disk_usage` varchar(255) default NULL,
  `priv_q_logo_manage_flag` varchar(255) default NULL,
  `used_q_logo_manage_flag` varchar(255) default NULL,
  `priv_q_ftpuser_num` varchar(255) default NULL,
  `used_q_ftpuser_num` varchar(255) default NULL,
  `priv_q_frontpage_flag` varchar(255) default NULL,
  `used_q_frontpage_flag` varchar(255) default NULL,
  `priv_q_php_manage_flag` varchar(255) default NULL,
  `used_q_php_manage_flag` varchar(255) default NULL,
  `priv_q_inc_flag` varchar(255) default NULL,
  `used_q_inc_flag` varchar(255) default NULL,
  `priv_q_awstats_flag` varchar(255) default NULL,
  `used_q_awstats_flag` varchar(255) default NULL,
  `priv_q_installapp_flag` varchar(255) default NULL,
  `used_q_installapp_flag` varchar(255) default NULL,
  `priv_q_modperl_flag` varchar(255) default NULL,
  `used_q_modperl_flag` varchar(255) default NULL,
  `priv_q_cgi_flag` varchar(255) default NULL,
  `used_q_cgi_flag` varchar(255) default NULL,
  `priv_q_php_flag` varchar(255) default NULL,
  `used_q_php_flag` varchar(255) default NULL,
  `priv_q_phpunsafe_flag` varchar(255) default NULL,
  `used_q_phpunsafe_flag` varchar(255) default NULL,
  `priv_q_subweb_a_num` varchar(255) default NULL,
  `used_q_subweb_a_num` varchar(255) default NULL,
  `priv_q_dotnet_flag` varchar(255) default NULL,
  `used_q_dotnet_flag` varchar(255) default NULL,
  `priv_q_cron_num` varchar(255) default NULL,
  `used_q_cron_num` varchar(255) default NULL,
  `priv_q_cron_minute_flag` varchar(255) default NULL,
  `used_q_cron_minute_flag` varchar(255) default NULL,
  `priv_q_cron_manage_flag` varchar(255) default NULL,
  `used_q_cron_manage_flag` varchar(255) default NULL,
  `priv_q_phpfcgi_flag` varchar(255) default NULL,
  `used_q_phpfcgi_flag` varchar(255) default NULL,
  `priv_q_rubyrails_num` varchar(255) default NULL,
  `used_q_rubyrails_num` varchar(255) default NULL,
  `priv_q_phpfcgiprocess_num` varchar(255) default NULL,
  `used_q_phpfcgiprocess_num` varchar(255) default NULL,
  `status` varchar(255) default NULL,
  `iisid` varchar(255) default NULL,
  `ser_server_alias_a` longtext,
  `ser_subweb_a` longtext,
  `ser_redirect_a` longtext,
  `stats_username` varchar(255) default NULL,
  `stats_password` varchar(255) default NULL,
  `ttype` varchar(255) default NULL,
  `username` varchar(255) default NULL,
  `password` varchar(255) default NULL,
  `ipaddress` varchar(255) default NULL,
  `ser_webmisc_b` longtext,
  `redirect_domain` varchar(255) default NULL,
  `text_extra_tag` longtext,
  `ser_customerror_b` longtext,
  `frontpage_flag` varchar(255) default NULL,
  `syncserver` varchar(255) default NULL,
  `cron_mailto` varchar(255) default NULL,
  `ser_aspnetconf_b` longtext,
  `ser_webindexdir_a` longtext,
  `webmail_url` varchar(255) default NULL,
  `text_lighty_rewrite` longtext,
  `text_nginx_rewrite` longtext,
  `ftpusername` varchar(255) default NULL,
  `hotlink_flag` varchar(255) default NULL,
  `text_hotlink_allowed` longtext,
  `hotlink_redirect` varchar(255) default NULL,
  `remove_processed_stats` varchar(255) default NULL,
  `ser_indexfile_list` longtext,
  `fcgi_children` varchar(255) default NULL,
  `customer_name` varchar(255) default NULL,
  `text_blockip` longtext,
  `docroot` varchar(255) default NULL,
  `force_www_redirect` varchar(255) default NULL,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_web` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `webhandler`
--

CREATE TABLE IF NOT EXISTS `webhandler` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `syncserver` varchar(255) default NULL,
  `mimehandler` varchar(255) default NULL,
  `extension` varchar(255) default NULL,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_webhandler` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `webmimetype`
--

CREATE TABLE IF NOT EXISTS `webmimetype` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `syncserver` varchar(255) default NULL,
  `mimehandler` varchar(255) default NULL,
  `extension` varchar(255) default NULL,
  `oldsyncserver` varchar(255) default NULL,
  `olddeleteflag` varchar(255) default NULL,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_webmimetype` (`parent_clname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
