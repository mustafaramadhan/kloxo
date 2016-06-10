<?php

if (!file_exists("/var/run/acme/acme-challenge")) {
	exec("mkdir -p /var/run/acme/acme-challenge");
}

$kpath = "/usr/local/lxlabs/kloxo";
$hpath = "/home/kloxo/httpd";

if (file_exists("{$kpath}/httpdocs/lib/html/include.php")) {
	include_once "{$kpath}/httpdocs/lib/html/include.php";
} else {
	include_once "{$kpath}/httpdocs/htmllib/lib/include.php";
}

initProgram('admin');

$sslport = $sgbl->__var_prog_ssl_port;
$nonsslport = $sgbl->__var_prog_port;

$gen = $login->getObject('general')->portconfig_b;

if (isset($gen)) {
	if (isset($gen->sslport) && ($gen->sslport !== '')) {
		$sslport = $gen->sslport;
	}

	if (isset($gen->nonsslport) && ($gen->nonsslport !== '')) {
		$nonsslport = $gen->nonsslport;
	}
}

$sfile = getLinkCustomfile("{$kpath}/init", "hiawatha.conf.base");

$content = file_get_contents($sfile);

$content = str_replace("__nonssl_port__", $nonsslport, $content);
$content = str_replace("__ssl_port__", $sslport, $content);

$acontent = file_get_contents(getLinkCustomfile("{$kpath}/init", "kloxo_php_active"));

$content = str_replace("__php__", str_replace("\n", "", $acontent), $content);
$content = str_replace("__php__", str_replace(" ", "", $acontent), $content);

if (file_exists("{$kpath}/init/kloxo_use_php-cgi")) {
	$content = str_replace("__fpmdisabled__", "#", $content);
} else {
	$content = str_replace("__fpmdisabled__", "", $content);
}

file_put_contents("{$kpath}/init/hiawatha.conf", $content);

file_put_contents("{$kpath}/init/port-nonssl", $nonsslport);
file_put_contents("{$kpath}/init/port-ssl", $sslport);

file_put_contents("{$hpath}/cp/.nonssl.port", $nonsslport);
file_put_contents("{$hpath}/cp/.ssl.port", $sslport);