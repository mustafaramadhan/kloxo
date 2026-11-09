<?php 

$driver['hostdeny'] = "linux";
//$driver['dns'] = array("none", "bind", "djbdns", "pdns", "nsd", "mydns", "yadifa");
$driver['dns'] = array("none", "bind", "djbdns", "pdns", "nsd", "yadifa");
$driver['pop3'] = array("none", "courier", "dovecot");
//$driver['imap4'] = array("none", "courier", "dovecot");
$driver['smtp'] = array("none", "qmail");
$driver['spam'] = array("none", "bogofilter", "spamassassin");
$driver['mailinglist'] = "ezmlm";
$driver['ffile'] = "linux";
$driver['easyinstaller'] = "linux";
$driver['all_easyinstaller'] = "linux";
$driver['pserver'] = "linux";
$driver['rubyrails'] = "linux";
$driver['uuser'] = "linux";
$driver['ftpuser'] = "pureftp";
/*
$driver['web'] = array("none", "apache", "lighttpd", "nginx", "hiawatha", "openlitespeed", "monkey", 
	"lighttpdproxy", "nginxproxy", "hiawathaproxy", "openlitespeedproxy", "monkeyproxy");
*/
$driver['web'] = array("none", "apache", "lighttpd", "nginx", "hiawatha", "lighttpdproxy", "nginxproxy", "apache_nginx", "hiawathaproxy");
$driver['webcache'] = array("none", "varnish", "trafficserver", "squid");
$driver['mmail'] = array("qmail");
$driver['cron'] = "linux";
$driver['llog'] = "linux";
$driver['davuser'] = 'lighttpd';
$driver['sshconfig'] = 'linux';

$driver['phpmodule'] = "linux";
