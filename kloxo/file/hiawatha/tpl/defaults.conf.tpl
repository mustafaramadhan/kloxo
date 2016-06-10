### begin - web of initial - do not remove/modify this line


## MR - NOTE:
## add 'header("X-Hiawatha-Cache: 10");' to index.php

<?php

$error_handler="Alias = /error:/home/kloxo/httpd/error
ErrorHandler = 401:/error/401.html
ErrorHandler = 403:/error/403.html
ErrorHandler = 404:/error/404.html
ErrorHandler = 501:/error/501.html
ErrorHandler = 503:/error/503.html";

if (!isset($phpselected)) {
	$phpselected = 'php';
}

if (!isset($timeout)) {
	$timeout = '300';
}

if (!file_exists("/var/run/letsencrypt/.well-known/acme-challenge")) {
	exec("mkdir -p /var/run/letsencrypt/.well-known/acme-challenge");
}

$srcconfpath = "/opt/configs/hiawatha/etc/conf";
$trgtconfpath = "/etc/hiawatha";

if ($reverseproxy) {
	if (file_exists("{$srcconfpath}/custom.hiawatha_proxy.conf")) {
		copy("{$srcconfpath}/custom.hiawatha_proxy.conf", "{$trgtconfpath}/hiawatha.conf");
	} else {
		copy("{$srcconfpath}/hiawatha_proxy.conf", "{$trgtconfpath}/hiawatha.conf");
	}
} else {
	if (file_exists("{$srcconfpath}/custom.hiawatha_standard.conf")) {
		copy("{$srcconfpath}/custom.hiawatha_standard.conf", "{$trgtconfpath}/hiawatha.conf");
	} else {
		copy("{$srcconfpath}/hiawatha_standard.conf", "{$trgtconfpath}/hiawatha.conf");
	}
}

if (($webcache === 'none') || (!$webcache)) {
	$ports[] = '80';
	$ports[] = '443';
} else {
	$ports[] = '8080';
	$ports[] = '8443';
}

$reverseports = array('30080', '30443');

$portnames = array('nonssl', 'ssl');

foreach ($certnamelist as $ip => $certname) {
	$certnamelist[$ip] = "/home/kloxo/ssl/{$certname}";
}

$defaultdocroot = "/home/kloxo/httpd/default";

// MR -- for future purpose, apache user have uid 50000
// $userinfoapache = posix_getpwnam('apache');
// $fpmportapache = (50000 + $userinfoapache['uid']);
$fpmportapache = 50000;
?>

UrlToolkit {
	ToolkitID = monitor
	RequestURI isfile Return
	Match ^/(css|files|fonts|images|js)(/|$) Return
	Match ^/(favicon.ico|robots.txt)$ Return
	Match [^?]*(\?.*)? Rewrite /index.php$1
}

UrlToolkit {
	ToolkitID = block_shellshock
	#Header * \(\)\s+\{ DenyAccess
	Header User-Agent \(\)\s*\{ DenyAccess
	Header Referer \(\)\s*\{ DenyAccess
}

UrlToolkit {
	ToolkitID = findindexfile
<?php
	$v2 = "";

	foreach ($indexorder as $k => $v) {
?>
	Match ^([^?]*)/<?php echo $v2; ?>(\?.*)?$ Rewrite $1/<?php echo $v; ?>$2 Continue
	RequestURI isfile Return
<?php
		$v2 = str_replace(".", "\.", $v);
	}
?>
	Match ^([^?]*)/<?php echo $v2; ?>(\?.*)?$ Rewrite $1/$2 Continue
}

UrlToolkit {
	ToolkitID = permalink
	RequestURI exists Return
	## process for 'special dirs' of Kloxo-MR
	Match ^/(stats|awstats|cp|error|webmail|__kloxo|kloxo|kloxononssl|cgi-bin)(/|$) Return
	Match ^/(css|files|images|js)(/|$) Return
	Match ^/(favicon.ico|robots.txt|sitemap.xml)$ Return
	Match /(.*)\?(.*) Rewrite /index.php?path=$1&$2
	Match .*\?(.*) Rewrite /index.php?$1
	Match .* Rewrite /index.php
}

FastCGIserver {
	FastCGIid = php_for_apache

	ConnectTo = /opt/configs/php-fpm/sock/php-apache.sock
	Extension = php
	SessionTimeout = <?php echo $timeout; ?>

}

FastCGIserver {
	FastCGIid = cgi_for_apache

	ConnectTo = /tmp/fcgiwrap.sock
	Extension = pl,cgi
	SessionTimeout = <?php echo $timeout; ?>

}

Directory {
	DirectoryID = well_known
	Path = /.well-known
	AccessList = allow all
}

CGIhandler = /usr/bin/perl:pl
#CGIhandler = /usr/bin/php-cgi:php
CGIhandler = /usr/bin/python:py
CGIhandler = /usr/bin/ruby:rb
CGIhandler = /usr/bin/ssi-cgi:shtml
#CGIextension = cgi
<?php
foreach ($certnamelist as $ip => $certname) {
	$count = 0;

	foreach ($ports as &$port) {
?>

Binding {
	BindingId = port_<?php echo $portnames[$count]; ?>

	Port = <?php echo $ports[$count]; ?>

	#Interface = 0.0.0.0
	MaxKeepAlive = 120
	TimeForRequest = <?php echo $timeout; ?>

	MaxRequestSize = 2096128
	MaxUploadSize = 2047
<?php
		if ($count !== 0) {
?>

	TLScertFile = <?php echo $certname; ?>.pem
<?php
			if (file_exists("{$certname}.ca")) {
?>
	#RequiredCA = <?php echo $certname; ?>.ca
<?php
			}
		}
?>
}
<?php
		$count++;
	}
}
?>


Alias = /.well-known:/var/run/letsencrypt/.well-known
UseDirectory = well_known

### 'default' config
set var_user = apache

Hostname = 0.0.0.0, ::
WebsiteRoot = <?php echo $defaultdocroot; ?>


EnablePathInfo = yes
## MR -- remove by Hiawatha 10+
#UseGZfile = yes
FollowSymlinks = no

TimeForCGI = <?php echo $timeout; ?>


<?php echo $error_handler; ?>

<?php
		if ($reverseproxy) {
?>

## MR -- change IgnoreDotHiawatha to UseLocalConfig in Hiawatha 10+
UseLocalConfig = yes
#IgnoreDotHiawatha = yes
UseToolkit = block_shellshock, findindexfile
#ReverseProxy ^/.* http://127.0.0.1:30080/ <?php echo $timeout; ?> keep-alive
ReverseProxy !\.(pl|cgi|py|rb|shmtl) http://127.0.0.1:30080/ <?php echo $timeout; ?> keep-alive
<?php
		} else {
?>

#UserDirectory = public_html
#UserWebsites = yes

UseFastCGI = php_for_var_user
UseToolkit = block_shellshock, findindexfile, permalink
<?php
		}
?>

#StartFile = index.php


### end - web of initial - do not remove/modify this line
