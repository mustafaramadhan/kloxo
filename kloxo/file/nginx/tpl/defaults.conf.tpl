### begin - web of initial - do not remove/modify this line

<?php

if (file_exists("/tmp/nginx")) {
	// MR -- need change ownership because change nginx user from nginx to apache
	@exec("chown -R apache:apache /var/cache/nginx*");
	@exec("chown -R apache:apache /tmp/nginx");
}

if (!file_exists("/var/run/letsencrypt/.well-known/acme-challenge")) {
	exec("mkdir -p /var/run/letsencrypt/.well-known/acme-challenge");
}

$srcconfpath = "/opt/configs/nginx/etc/conf";
$srcconfdpath = "/opt/configs/nginx/etc/conf.d";
$trgtconfpath = "/etc/nginx";
$trgtconfdpath = "/etc/nginx/conf.d";

$defaultdocroot = "/home/kloxo/httpd/default";
$cpdocroot = "/home/kloxo/httpd/cp";

$globalspath = "/opt/configs/nginx/conf/globals";

if (file_exists("{$globalspath}/custom.gzip.conf")) {
		$gzip_base = "custom.gzip";
} else {
		$gzip_base = "gzip";
}

$confs = array('nginx.conf', 'mime.types', 'fastcgi_params');

$switches = array('', '_ssl');

foreach ($confs as $k => $v) {
	if (file_exists("{$srcconfpath}/custom.{$v}")) {
		copy("{$srcconfpath}/custom.{$v}", "{$trgtconfpath}/{$v}");
	} else {
		copy("{$srcconfpath}/{$v}", "{$trgtconfpath}/{$v}");
	}
}

$confs = array('~lxcenter.conf', 'default.conf');

foreach ($confs as $k => $v) {
	if (file_exists("{$srcconfdpath}/custom.{$v}")) {
		copy("{$srcconfdpath}/custom.{$v}", "{$trgtconfdpath}/{$v}");
	} else {
		copy("{$srcconfdpath}/{$v}", "{$trgtconfdpath}/{$v}");
	}
}

foreach ($certnamelist as $ip => $certname) {
	$certnamelist[$ip] = "/home/kloxo/httpd/ssl/{$certname}";
}

$iplist = array('*');

if ($indexorder) {
	$indexorder = implode(' ', $indexorder);
}

// MR -- for future purpose, apache user have uid 50000
// $userinfoapache = posix_getpwnam('apache');
// $fpmportapache = (50000 + $userinfoapache['uid']);
$fpmportapache = 50000;

exec("ip -6 addr show", $out);

if ($out[0]) {
	$IPv6Enable = true;
} else {
	$IPv6Enable = false;
}

if ($reverseproxy) {
	$confs = array('proxy_standard' => 'switch_standard', 'proxy_wildcards' => 'switch_wildcards',
	'proxy_standard_ssl' => 'switch_standard_ssl', 'proxy_wildcards_ssl' => 'switch_wildcards_ssl',
		'stats_none' => 'stats', 'dirprotect_none' => 'dirprotect_stats');
} else {
	if ($stats['app'] === 'webalizer') {
		$confs = array('php-fpm_standard' => 'switch_standard', 'php-fpm_wildcards' => 'switch_wildcards',
		'php-fpm_standard_ssl' => 'switch_standard_ssl', 'php-fpm_wildcards_ssl' => 'switch_wildcards_ssl',
		'stats_webalizer' => 'stats', 'dirprotect_webalizer' => 'dirprotect_stats');
	} else {
		$confs = array('php-fpm_standard' => 'switch_standard', 'php-fpm_wildcards' => 'switch_wildcards',
		'stats_awstats' => 'stats', 'dirprotect_awstats' => 'dirprotect_stats');
	}

}

foreach ($confs as $k => $v) {
	if (file_exists("{$globalspath}/custom.{$k}.conf")) {
		copy("{$globalspath}/custom.{$k}.conf", "{$globalspath}/{$v}.conf");
	} else {
		copy("{$globalspath}/{$k}.conf", "{$globalspath}/{$v}.conf");
	}
}

if (($webcache === 'none') || (!$webcache)) {
	$confs = array('listen_nonssl_front' => 'listen_nonssl', 'listen_ssl_front' => 'listen_ssl',
		'listen_nonssl_front_default' => 'listen_nonssl_default', 'listen_ssl_front_default' => 'listen_ssl_default');
} else {
	$confs = array('listen_nonssl_back' => 'listen_nonssl', 'listen_ssl_back' => 'listen_ssl',
		'listen_nonssl_back_default' => 'listen_nonssl_default', 'listen_ssl_back_default' => 'listen_ssl_default');
}

foreach ($confs as $k => $v) {
	if (file_exists("{$globalspath}/custom.{$k}.conf")) {
		copy("{$globalspath}/custom.{$k}.conf", "{$globalspath}/{$v}.conf");
	} else {
		copy("{$globalspath}/{$k}.conf", "{$globalspath}/{$v}.conf");
	}
}

if (file_exists("{$globalspath}/custom.ssl_base.conf")) {
	$ssl_base = "custom.ssl_base";
} else {
	$ssl_base = "ssl_base";
}

if (file_exists("{$globalspath}/custom.acme-challenge.conf")) {
	$acme_challenge = "custom.acme-challenge";
} else {
	$acme_challenge = "acme-challenge";
}

if (file_exists("{$globalspath}/custom.header_base.conf")) {
	$header_base = "custom.header_base";
} else {
	$header_base = "header_base";
}

$listens = array('listen_nonssl_default', 'listen_ssl_default');

foreach ($certnamelist as $ip => $certname) {
	$count = 0;

	foreach ($listens as &$listen) {
?>

## 'default' config
server {
	#disable_symlinks if_not_owner;

	include '<?php echo $globalspath; ?>/<?php echo $listen; ?>.conf';

	include '<?php echo $globalspath; ?>/<?php echo $gzip_base; ?>.conf';
<?php
		if ($count !== 0) {
?>

	include '<?php echo $globalspath; ?>/<?php echo $header_base; ?>.conf';

	ssl on;
	ssl_certificate <?php echo $certname; ?>.pem;
	ssl_certificate_key <?php echo $certname; ?>.key;
<?php
			if (file_exists("{$certname}.ca")) {
?>
	ssl_trusted_certificate <?php echo $certname; ?>.ca;
<?php
			}
?>
	include '<?php echo $globalspath; ?>/<?php echo $ssl_base; ?>.conf';
<?php
		}

?>

	server_name _;

	include '<?php echo $globalspath; ?>/<?php echo $acme_challenge; ?>.conf';

	index <?php echo $indexorder; ?>;

	set $var_domain '';
	set $var_rootdir '<?php echo $defaultdocroot; ?>';

	root $var_rootdir;

	set $var_user 'apache';
	set $var_fpmport '<?php echo $fpmportapache; ?>';
	set $var_phpselected 'php';

	include '<?php echo $globalspath; ?>/switch_standard<?php echo $switches[$count]; ?>.conf';
<?php
		$count++;
?>
}

<?php
	}
}
?>

### end - web of initial - do not remove/modify this line
