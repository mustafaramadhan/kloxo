### begin - web of initial - do not remove/modify this line


<?php

if (!isset($phpselected)) {
	$phpselected = 'php';
}

if (!isset($timeout)) {
	$timeout = '300';
}

if (!file_exists("/var/run/letsencrypt/.well-known/acme-challenge")) {
	exec("mkdir -p /var/run/letsencrypt/.well-known/acme-challenge");
}

$srcconfpath = "/opt/configs/lighttpd/etc/conf";
$srcconfdpath = "/opt/configs/lighttpd/etc/conf.d";
$trgtconfpath = "/etc/lighttpd";
$trgtconfdpath = "/etc/lighttpd/conf.d";

if (file_exists("{$srcconfpath}/custom.lighttpd.conf")) {
	copy("{$srcconfpath}/custom.lighttpd.conf", "{$trgtconfpath}/lighttpd.conf");
} else {
	copy("{$srcconfpath}/lighttpd.conf", "{$trgtconfpath}/lighttpd.conf");
}

if (file_exists("{$srcconfdpath}/custom.~lxcenter.conf")) {
	copy("{$srcconfdpath}/custom.~lxcenter.conf", "{$trgtconfdpath}/~lxcenter.conf");
} else {
	copy("{$srcconfdpath}/~lxcenter.conf", "{$trgtconfdpath}/~lxcenter.conf");
}

if (($webcache === 'none') || (!$webcache)) {
	$ports[] = '80';
	$ports[] = '443';
} else {
	$ports[] = '8080';
	$ports[] = '8443';
}

$portlist = array('var.port', 'var.portssl');

$globalspath = "/opt/configs/lighttpd/conf/globals";

if ($reverseproxy) {
	$confs = array('proxy_standard' => 'switch_standard', 'stats_none' => 'stats', 
		'dirprotect_none' => 'dirprotect_stats');
} else {
	if ($stats['app'] === 'webalizer') {
		$confs = array('php-fpm_standard' => 'switch_standard', 'stats_webalizer' => 'stats',
			'dirprotect_webalizer' => 'dirprotect_stats');
	} else {
		$confs = array('php-fpm_standard' => 'switch_standard', 'stats_awstats' => 'stats',
			'dirprotect_awstats' => 'dirprotect_stats');
	}

}

foreach ($confs as $k => $v) {
	if (file_exists("{$globalspath}/custom.{$k}.conf")) {
		copy("{$globalspath}/custom.{$k}.conf", "{$globalspath}/{$v}.conf");
	} else {
		copy("{$globalspath}/{$k}.conf", "{$globalspath}/{$v}.conf");
	}
}

if (file_exists("{$globalspath}/custom.header_base.conf")) {
	$header_base = "custom.header_base";
} else {
	$header_base = "header_base";
}

foreach ($certnamelist as $ip => $certname) {
	$cert_ip = $ip;
	$cert_file = "/home/kloxo/ssl/{$certname}";
}

if ($indexorder) {
	$indexorder = implode(' ', $indexorder);
}

$indexorder = '"' . $indexorder . '"';
$indexorder = str_replace(' ', '", "', $indexorder);

// MR -- for future purpose, apache user have uid 50000
// $userinfoapache = posix_getpwnam('apache');
// $fpmportapache = (50000 + $userinfoapache['uid']);
$fpmportapache = 50000;

$count = 0;

$tabs = array("", "\t");
?>
server.port = "<?php echo $ports[0]; ?>"
<?php echo $portlist[1]; ?> = "<?php echo $ports[1]; ?>"


$HTTP["host"] =~ "^default\.*" {

	include "<?php echo $globalspath; ?>/acme-challenge.conf"

	$HTTP["scheme"] == "https" {

		include "<?php echo $globalspath; ?>/<?php echo $header_base; ?>.conf"

		ssl.engine = "enable"

		ssl.pemfile = "<?php echo $cert_file; ?>.pem"
<?php
if (file_exists("{$cert_file}.ca")) {
?>

		ssl.ca-file = "<?php echo $cert_file; ?>.ca"
<?php
}
?>
		ssl.use-sslv2 = "disable"
		ssl.use-sslv3 = "disable"

	}

	var.rootdir = "/home/kloxo/httpd/default/"
	var.user = "apache"
	var.fpmport = "<?php echo $fpmportapache; ?>"
	var.phpselected = "php"
	var.timeout = "<?php echo $timeout; ?>"

	server.document-root = var.rootdir

	index-file.names = ( <?php echo $indexorder; ?> )

	include "<?php echo $globalspath; ?>/switch_standard.conf"

}


### end - web of initial - do not remove/modify this line
