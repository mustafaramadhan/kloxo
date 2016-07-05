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

$sslpath = "/home/kloxo/ssl";

if (file_exists("{$srcconfpath}/custom.lighttpd.conf")) {
	copy("{$srcconfpath}/custom.lighttpd.conf", "{$trgtconfpath}/lighttpd.conf");
} else if (file_exists("{$srcconfpath}/lighttpd.conf")) {
	copy("{$srcconfpath}/lighttpd.conf", "{$trgtconfpath}/lighttpd.conf");
}

if (file_exists("{$srcconfdpath}/custom.~lxcenter.conf")) {
	copy("{$srcconfdpath}/custom.~lxcenter.conf", "{$trgtconfdpath}/~lxcenter.conf");
} else if (file_exists("{$srcconfdpath}/~lxcenter.conf")) {
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
		$confs = array('php-fpm_standard' => 'switch_standard', 'stats_webalizer' => 'stats');
	} else {
		$confs = array('php-fpm_standard' => 'switch_standard', 'stats_awstats' => 'stats');
	}

}

foreach ($confs as $k => $v) {
	if (file_exists("{$globalspath}/custom.{$k}.conf")) {
		copy("{$globalspath}/custom.{$k}.conf", "{$globalspath}/{$v}.conf");
	} else if (file_exists("{$globalspath}/{$k}.conf")) {
		copy("{$globalspath}/{$k}.conf", "{$globalspath}/{$v}.conf");
	}
}

if (file_exists("{$globalspath}/custom.header_base.conf")) {
	$header_base = "custom.header_base";
} else if (file_exists("{$globalspath}/header_base.conf")) {
	$header_base = "header_base";
}

if (file_exists("{$globalspath}/custom.header_ssl.conf")) {
	$header_ssl = "custom.header_ssl";
} else if (file_exists("{$globalspath}/header_ssl.conf")) {
	$header_ssl = "header_ssl";
}

foreach ($certnamelist as $ip => $certname) {
	$cert_ip = $ip;
	$cert_file = "{$sslpath}/{$certname}";
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


$SERVER["socket"] == ":<?php echo $ports[1]; ?>" {

	ssl.engine = "enable"

	ssl.pemfile = "<?php echo $cert_file; ?>.pem"
<?php
if (file_exists("{$cert_file}.ca")) {
?>
	ssl.ca-file = "<?php echo $cert_file; ?>.ca"
<?php
}

$dirs = glob("{$sslpath}/*.pem", GLOB_MARK);

if (count($dirs) > 0) {
	foreach($dirs as $k => $v) {
		$f = str_replace(".pem", "", $v);
		$d = str_replace("{$sslpath}/", "", $f);

		if ($certname === $d) { continue; }
?>

	$HTTP["host"] =~ "(^|www\.|cp\.|webmail\.)<?php echo str_replace(".", "\.", $d); ?>" {

		ssl.pemfile = "<?php echo $v; ?>"
<?php
		if (file_exists("{$f}.ca")) {
?>
		ssl.ca-file = "<?php echo $f; ?>.ca"
<?php
		}
?>

		ssl.use-sslv2 = "disable"
		ssl.use-sslv3 = "disable"
		sl.use-compression = "disable"
		ssl.honor-cipher-order = "enable"
		ssl.cipher-list = "ECDH+AESGCM:DH+AESGCM:ECDH+AES256:DH+AES256:ECDH+AES128:DH+AES:ECDH+3DES:DH+3DES:RSA+AESGCM:RSA+AES:RSA+3DES:!aNULL:!MD5:!DSS"

	}
<?php
	}
}
?>

}


$HTTP["host"] =~ "^default\.*" {

	include "<?php echo $globalspath; ?>/acme-challenge.conf"

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
