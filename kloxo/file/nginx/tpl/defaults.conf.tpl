### begin - web of initial - do not remove/modify this line

<?php

$srcpath = "/opt/configs/nginx";

if (file_exists("{$srcpath}/etc/sysconfig/custom.spawn-fcgi")) {
	exec("'cp' -f {$srcpath}/etc/sysconfig/custom.spawn-fcgi /etc/sysconfig/spawn-fcgi");
} else if (file_exists("{$srcpath}/etc/sysconfig/spawn-fcgi")) {
	exec("'cp' -f {$srcpath}/etc/sysconfig/spawn-fcgi /etc/sysconfig/spawn-fcgi");
}

if (!isset($phpselected)) {
	$phpselected = 'php';
}

if (!isset($timeout)) {
	$timeout = '300';
}

if (file_exists("/tmp/nginx")) {
	// MR -- need change ownership because change nginx user from nginx to apache
	@exec("chown -R apache:apache /var/cache/nginx*");
	@exec("chown -R apache:apache /tmp/nginx");
}

if (!file_exists("/var/run/letsencrypt/.well-known/acme-challenge")) {
	exec("mkdir -p /var/run/letsencrypt/.well-known/acme-challenge");
}

$srcconfpath = "{$srcpath}/etc/conf";
$srcconfdpath = "{$srcpath}/etc/conf.d";
$trgtconfpath = "/etc/nginx";
$trgtconfdpath = "{$trgtconfpath}/conf.d";

$defaultdocroot = "/home/kloxo/httpd/default";
$cpdocroot = "/home/kloxo/httpd/cp";

$globalspath = "{$srcpath}/conf/globals";

if (file_exists("{$globalspath}/custom.gzip.conf")) {
		$gzip_base = "custom.gzip";
} else if (file_exists("{$globalspath}/gzip.conf")) {
		$gzip_base = "gzip";
}

$confs = array('nginx.conf', 'mime.types', 'fastcgi_params');

$switches = array('', '_ssl');

foreach ($confs as $k => $v) {
	if (file_exists("{$srcconfpath}/custom.{$v}")) {
		copy("{$srcconfpath}/custom.{$v}", "{$trgtconfpath}/{$v}");
	} else if (file_exists("{$srcconfpath}/{$v}")) {
		copy("{$srcconfpath}/{$v}", "{$trgtconfpath}/{$v}");
	}
}

$confs = array('~lxcenter.conf', 'default.conf');

foreach ($confs as $k => $v) {
	if (file_exists("{$srcconfdpath}/custom.{$v}")) {
		copy("{$srcconfdpath}/custom.{$v}", "{$trgtconfdpath}/{$v}");
	} else if (file_exists("{$srcconfdpath}/{$v}")) {
		copy("{$srcconfdpath}/{$v}", "{$trgtconfdpath}/{$v}");
	}
}

foreach ($certnamelist as $ip => $certname) {
	$certnamelist[$ip] = "/home/kloxo/ssl/{$certname}";
}

$iplist = array('*');

if ($indexorder) {
	$indexorder = implode(' ', $indexorder);
}

// MR -- for future purpose, apache user have uid 50000
// $userinfoapache = posix_getpwnam('apache');
// $fpmportapache = (50000 + $userinfoapache['uid']);
$fpmportapache = 50000;

$out = null;
exec("ip -6 addr show", $out);

if (count($out) > 0) {
	$IPv6Enable = true;
} else {
	$IPv6Enable = false;
}

if ($reverseproxy) {
	$confs = array('proxy_standard' => 'switch_standard', 'proxy_wildcards' => 'switch_wildcards',
		'proxy_standard_ssl' => 'switch_standard_ssl', 'proxy_wildcards_ssl' => 'switch_wildcards_ssl');
} else {
	$confs = array('php-fpm_standard' => 'switch_standard', 'php-fpm_wildcards' => 'switch_wildcards',
		'php-fpm_standard_ssl' => 'switch_standard_ssl', 'php-fpm_wildcards_ssl' => 'switch_wildcards_ssl');
}

if ($stats['app'] === 'webalizer') {
	$confs = array_merge($confs, array('stats_webalizer' => 'stats'));
} else {
	$confs = array_merge($confs, array('stats_awstats' => 'stats'));
}

if (($webcache === 'none') || (!$webcache)) {
	$out = null;
	exec("echo $(2>&1 nginx -V | tr -- - '\n' | grep _module)|tr ' ' '\n'|grep 'http_v2_module'", $out);

	if (count($out) > 0) {
		$confs = array_merge($confs, array('listen_nonssl_front' => 'listen_nonssl', 'listen_ssl_front_h2' => 'listen_ssl',
			'listen_nonssl_front_default' => 'listen_nonssl_default', 'listen_ssl_front_default_h2' => 'listen_ssl_default'));
	} else {
		$confs = array_merge($confs, array('listen_nonssl_front' => 'listen_nonssl', 'listen_ssl_front' => 'listen_ssl',
			'listen_nonssl_front_default' => 'listen_nonssl_default', 'listen_ssl_front_default' => 'listen_ssl_default'));
	}
} else {
	$confs = array_merge($confs, array('listen_nonssl_back' => 'listen_nonssl', 'listen_ssl_back' => 'listen_ssl',
		'listen_nonssl_back_default' => 'listen_nonssl_default', 'listen_ssl_back_default' => 'listen_ssl_default'));
}

foreach ($confs as $k => $v) {
	if (file_exists("{$globalspath}/custom.{$k}.conf")) {
		copy("{$globalspath}/custom.{$k}.conf", "{$globalspath}/{$v}.conf");
	} else if (file_exists("{$globalspath}/{$k}.conf")) {
		copy("{$globalspath}/{$k}.conf", "{$globalspath}/{$v}.conf");
	}
}

if (file_exists("{$globalspath}/custom.ssl_base.conf")) {
	$ssl_base = "custom.ssl_base";
} else if (file_exists("{$globalspath}/ssl_base.conf")) {
	$ssl_base = "ssl_base";
}

if (file_exists("{$globalspath}/custom.acme-challenge.conf")) {
	$acme_challenge = "custom.acme-challenge";
} else if (file_exists("{$globalspath}/acme-challenge.conf")) {
	$acme_challenge = "acme-challenge";
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

	include '<?php echo $globalspath; ?>/<?php echo $ssl_base; ?>.conf';
	ssl_certificate <?php echo $certname; ?>.pem;
	ssl_certificate_key <?php echo $certname; ?>.key;
<?php
			if (file_exists("{$certname}.ca")) {
?>
	ssl_trusted_certificate <?php echo $certname; ?>.ca;
<?php
			}
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
<?php
		//if ((!$reverseproxy) || (($reverseproxy) && ($webselected === 'front-end'))) {
?>

	proxy_connect_timeout <?php echo $timeout; ?>s;
	proxy_send_timeout <?php echo $timeout; ?>s;
	proxy_read_timeout <?php echo $timeout; ?>s;
<?php
		//} else {
?>

	fastcgi_connect_timeout <?php echo $timeout; ?>s;
	fastcgi_send_timeout <?php echo $timeout; ?>s;
	fastcgi_read_timeout <?php echo $timeout; ?>s;
<?php
		//}
?>

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
