### begin - web of '<?php echo $domainname; ?>' - do not remove/modify this line

<?php

if (!isset($phpselected)) {
	$phpselected = 'php';
}

if (!isset($timeout)) {
	$timeout = '300';
}

if (($webcache === 'none') || (!$webcache)) {
	$ports[] = '80';
	$ports[] = '443';
} else {
	$ports[] = '8080';
	$ports[] = '8443';
}

foreach ($certnamelist as $ip => $certname) {
	$cert_ip = $ip;

	$sslpath = "/home/kloxo/ssl";

	if (file_exists("{$sslpath}/{$domainname}.key")) {
		$cert_file = "{$sslpath}/{$domainname}";
	} else {
		$cert_file = "{$sslpath}/{$certname}";
	}

}

$statsapp = $stats['app'];
$statsprotect = ($stats['protect']) ? true : false;

$tmpdom = str_replace(".", "\.", $domainname);

$excludedomains = array("cp", "webmail");

$excludealias = implode("|", $excludedomains);

$serveralias = '';

if ($wildcards) {
	$serveralias .= "(?:^|\.){$tmpdom}$";
} else {
	if ($wwwredirect) {
		$serveralias .= "^(?:www\.){$tmpdom}$";
	} else {
		$serveralias .= "^(?:www\.|){$tmpdom}$";
	}
}

if ($serveraliases) {
	foreach ($serveraliases as &$sa) {
		$tmpdom = str_replace(".", "\.", $sa);
		$serveralias .= "|^(?:www\.|){$tmpdom}$";
	}
}

if ($parkdomains) {
	foreach ($parkdomains as $pk) {
		$pa = $pk['parkdomain'];
		$tmpdom = str_replace(".", "\.", $pa);
		$serveralias .= "|^(?:www\.|){$tmpdom}$";
	}
}

if ($webmailapp) {
	if ($webmailapp === '--Disabled--') {
		$webmaildocroot = "/home/kloxo/httpd/disable";
	} else {
		$webmaildocroot = "/home/kloxo/httpd/webmail/{$webmailapp}";
	}
} else {
	$webmaildocroot = "/home/kloxo/httpd/webmail";
}

$webmailremote = str_replace("http://", "", $webmailremote);
$webmailremote = str_replace("https://", "", $webmailremote);

if ($indexorder) {
	$indexorder = implode(' ', $indexorder);
}

$indexorder = '"' . $indexorder . '"';
$indexorder = str_replace(' ', '", "', $indexorder);

if ($blockips) {
	$biptemp = array();
	foreach ($blockips as &$bip) {
		if (strpos($bip, ".*.*.*") !== false) {
			$bip = str_replace(".*.*.*", ".0.0/8", $bip);
		}
		if (strpos($bip, ".*.*") !== false) {
			$bip = str_replace(".*.*", ".0.0/16", $bip);
		}
		if (strpos($bip, ".*") !== false) {
			$bip = str_replace(".*", ".0/24", $bip);
		}
		$biptemp[] = $bip;
	}
	$blockips = $biptemp;

	$blockips = implode('|', $blockips);
}

$userinfo = posix_getpwnam($user);

if ($userinfo) {
	$fpmport = (50000 + $userinfo['uid']);
} else {
	return false;
}

// MR -- for future purpose, apache user have uid 50000
// $userinfoapache = posix_getpwnam('apache');
// $fpmportapache = (50000 + $userinfoapache['uid']);
$fpmportapache = 50000;

if ($reverseproxy) {
	$lighttpdextratext = null;
}

$disabledocroot = "/home/kloxo/httpd/disable";
$cpdocroot = "/home/kloxo/httpd/cp";

$globalspath = "/opt/configs/lighttpd/conf/globals";

if (file_exists("{$globalspath}/custom.generic.conf")) {
	$generic = "custom.generic";
} else {
	$generic = "generic";
}

if (file_exists("{$globalspath}/custom.header_base.conf")) {
	$header_base = "custom.header_base";
} else {
	$header_base = "header_base";
}

if ($disabled) {
	$sockuser = 'apache';
} else {
	$sockuser = $user;
}

if ($disabled) {
?>

## cp for '<?php echo $domainname; ?>'
$HTTP["host"] =~ "^cp\.<?php echo str_replace(".", "\.", $domainname); ?>" {

	include "<?php echo $globalspath; ?>/acme-challenge.conf"

	$HTTP["scheme"] == "https" {

		ssl.engine = "enable"

		ssl.pemfile = "<?php echo $cert_file; ?>.pem"
<?php
	if (file_exists("{$cert_file}.ca")) {
?>

		ssl.ca-file = "<?php echo $cert_file; ?>.ca"

		include "<?php echo $globalspath; ?>/<?php echo $header_base; ?>.conf"
<?php
	}
?>

		ssl.use-sslv2 = "disable"
		ssl.use-sslv3 = "disable"

	}

	var.user = "apache"
	var.fpmport = "<?php echo $fpmportapache; ?>"
	var.rootdir = "<?php echo $disabledocroot; ?>/"
	var.phpselected = "php"
	var.timeout = "<?php echo $timeout; ?>"

	server.document-root = var.rootdir

	index-file.names = ( <?php echo $indexorder; ?> )

	include "<?php echo $globalspath; ?>/switch_standard.conf"

}


## webmail for '<?php echo $domainname; ?>'
$HTTP["host"] =~ "^webmail\.<?php echo str_replace(".", "\.", $domainname); ?>" {

	include "<?php echo $globalspath; ?>/acme-challenge.conf"

	$HTTP["scheme"] == "https" {

		ssl.engine = "enable"

		ssl.pemfile = "<?php echo $cert_file; ?>.pem"
<?php
	if (file_exists("{$cert_file}.ca")) {
?>

		ssl.ca-file = "<?php echo $cert_file; ?>.ca"

		include "<?php echo $globalspath; ?>/<?php echo $header_base; ?>.conf"
<?php
	}
?>

		ssl.use-sslv2 = "disable"
		ssl.use-sslv3 = "disable"

	}

	var.user = "apache"
	var.fpmport = "<?php echo $fpmportapache; ?>"
	var.rootdir = "<?php echo $disabledocroot; ?>/"
	var.phpselected = "php"
	var.timeout = "<?php echo $timeout; ?>"

	server.document-root = var.rootdir

	index-file.names = ( <?php echo $indexorder; ?> )

	include "<?php echo $globalspath; ?>/switch_standard.conf"

}

<?php
} else {
?>

## cp for '<?php echo $domainname; ?>'
$HTTP["host"] =~ "^cp\.<?php echo str_replace(".", "\.", $domainname); ?>" {

	include "<?php echo $globalspath; ?>/acme-challenge.conf"

	$HTTP["scheme"] == "https" {

		ssl.engine = "enable"

		ssl.pemfile = "<?php echo $cert_file; ?>.pem"
<?php
	if (file_exists("{$cert_file}.ca")) {
?>

		ssl.ca-file = "<?php echo $cert_file; ?>.ca"

		include "<?php echo $globalspath; ?>/<?php echo $header_base; ?>.conf"
<?php
	}
?>

		ssl.use-sslv2 = "disable"
		ssl.use-sslv3 = "disable"

	}

	var.user = "apache"
	var.fpmport = "<?php echo $fpmportapache; ?>"
	var.rootdir = "<?php echo $cpdocroot; ?>/"
	var.phpselected = "php"
	var.timeout = "<?php echo $timeout; ?>"

	server.document-root = var.rootdir

	index-file.names = ( <?php echo $indexorder; ?> )

	include "<?php echo $globalspath; ?>/switch_standard.conf"

}

<?php
	if ($webmailremote) {
?>

## webmail for '<?php echo $domainname; ?>'
$HTTP["host"] =~ "^webmail\.<?php echo str_replace(".", "\.", $domainname); ?>" {

	$HTTP["scheme"] == "https" {

		ssl.engine = "enable"

		ssl.pemfile = "<?php echo $cert_file; ?>.pem"
<?php
		if (file_exists("{$cert_file}.ca")) {
?>

		ssl.ca-file = "<?php echo $cert_file; ?>.ca"

		include "<?php echo $globalspath; ?>/<?php echo $header_base; ?>.conf"
<?php
		}
?>

		ssl.use-sslv2 = "disable"
		ssl.use-sslv3 = "disable"


		url.redirect = ( "/" =>  "https://<?php echo $webmailremote; ?>/" )

	}

	url.redirect = ( "/" =>  "http://<?php echo $webmailremote; ?>/" )

}

<?php
	} else {
?>

## webmail for '<?php echo $domainname; ?>'
$HTTP["host"] =~ "^webmail\.<?php echo str_replace(".", "\.", $domainname); ?>" {

	include "<?php echo $globalspath; ?>/acme-challenge.conf"

	$HTTP["scheme"] == "https" {

		ssl.engine = "enable"

		ssl.pemfile = "<?php echo $cert_file; ?>.pem"
<?php
		if (file_exists("{$cert_file}.ca")) {
?>

		ssl.ca-file = "<?php echo $cert_file; ?>.ca"

		include "<?php echo $globalspath; ?>/<?php echo $header_base; ?>.conf"
<?php
		}
?>

		ssl.use-sslv2 = "disable"
		ssl.use-sslv3 = "disable"

	}

	var.user = "apache"
	var.fpmport = "<?php echo $fpmportapache; ?>"
	var.rootdir = "<?php echo $webmaildocroot; ?>/"
	var.phpselected = "php"
	var.timeout = "<?php echo $timeout; ?>"

	server.document-root = var.rootdir

	index-file.names = ( <?php echo $indexorder; ?> )

	include "<?php echo $globalspath; ?>/switch_standard.conf"

}

<?php
	}
}

if ($domainredirect) {
	foreach ($domainredirect as $domredir) {
		$redirdomainname = $domredir['redirdomain'];
		$redirpath = ($domredir['redirpath']) ? $domredir['redirpath'] : null;
		$webmailmap = ($domredir['mailflag'] === 'on') ? true : false;

		if ($redirpath) {
			if ($disabled) {
			 	$$redirfullpath = $disablepath;
		 	} else {
				$redirfullpath = str_replace('//', '/', $rootpath . '/' . $redirpath);
			}
?>

## web for redirect '<?php echo $redirdomainname; ?>'
$HTTP["host"] =~ "^<?php echo str_replace(".", "\.", $redirdomainname); ?>" {

	include "<?php echo $globalspath; ?>/acme-challenge.conf"

	$HTTP["scheme"] == "https" {

		ssl.engine = "enable"

		ssl.pemfile = "<?php echo $cert_file; ?>.pem"
<?php
		if (file_exists("{$cert_file}.ca")) {
?>

	 	ssl.ca-file = "<?php echo $cert_file; ?>.ca"

		include "<?php echo $globalspath; ?>/<?php echo $header_base; ?>.conf"
<?php
		}
?>

		ssl.use-sslv2 = "disable"
		ssl.use-sslv3 = "disable"

	}

	var.user = "<?php echo $sockuser; ?>"
	var.fpmport = "<?php echo $fpmport; ?>"
	var.rootdir = "<?php echo $redirfullpath; ?>/"
	var.phpselected = "<?php echo $phpselected; ?>"
	var.timeout = "<?php echo $timeout; ?>"

	server.document-root = var.rootdir

	index-file.names = ( <?php echo $indexorder; ?> )
<?php

			if ($enablephp) {
?>

	include "<?php echo $globalspath; ?>/switch_standard.conf"
<?php
			}
?>

}

<?php
		} else {
			if ($disabled) {
				$$redirfullpath = $disablepath;
			} else {
				$redirfullpath = $rootpath;
			}
?>

## web for redirect '<?php echo $redirdomainname; ?>'
$HTTP["host"] =~ "^<?php echo str_replace(".", "\.", $redirdomainname); ?>" {

	$HTTP["scheme"] == "https" {

		ssl.engine = "enable"

		ssl.pemfile = "<?php echo $cert_file; ?>.pem"
<?php
			if (file_exists("{$cert_file}.ca")) {
?>

		ssl.ca-file = "<?php echo $cert_file; ?>.ca"

		include "<?php echo $globalspath; ?>/<?php echo $header_base; ?>.conf"
<?php
			}
?>

		ssl.use-sslv2 = "disable"
		ssl.use-sslv3 = "disable"

		url.redirect = ( "/" =>  "https://<?php echo $domainname; ?>/" )

	}

	var.rootdir = "<?php echo $redirfullpath; ?>/"

	server.document-root = var.rootdir

	url.redirect = ( "/" =>  "http://<?php echo $domainname; ?>/" )

}

<?php
		}
	}
}

if ($parkdomains) {
	foreach ($parkdomains as $dompark) {
		$parkdomainname = $dompark['parkdomain'];
		$webmailmap = ($dompark['mailflag'] === 'on') ? true : false;

		if ($disabled) {
?>

## webmail for parked '<?php echo $parkdomainname; ?>'
$HTTP["host"] =~ "^webmail\.<?php echo str_replace(".", "\.", $parkdomainname); ?>" {

	include "<?php echo $globalspath; ?>/acme-challenge.conf"

	$HTTP["scheme"] == "https" {

		ssl.engine = "enable"

		ssl.pemfile = "<?php echo $cert_file; ?>.pem"
<?php
			if (file_exists("{$cert_file}.ca")) {
?>

		ssl.ca-file = "<?php echo $cert_file; ?>.ca"

		include "<?php echo $globalspath; ?>/<?php echo $header_base; ?>.conf"
<?php
			}
?>

		ssl.use-sslv2 = "disable"
		ssl.use-sslv3 = "disable"

	}

	var.user = "apache"
	var.fpmport = "<?php echo $fpmportapache; ?>"
	var.rootdir = "<?php echo $disabledocroot; ?>/"
	var.phpselected = "php"
	var.timeout = "<?php echo $timeout; ?>"

	server.document-root = var.rootdir

	index-file.names = ( <?php echo $indexorder; ?> )

	include "<?php echo $globalspath; ?>/switch_standard.conf"

}

<?php
		} else {
			if ($webmailremote) {
?>

## webmail for parked '<?php echo $parkdomainname; ?>'
$HTTP["host"] =~ "^webmail\.<?php echo str_replace(".", "\.", $parkdomainname); ?>" {

	$HTTP["scheme"] == "https" {

		ssl.engine = "enable"

		ssl.pemfile = "<?php echo $cert_file; ?>.pem"
<?php
				if (file_exists("{$cert_file}.ca")) {
?>

		ssl.ca-file = "<?php echo $cert_file; ?>.ca"

		include "<?php echo $globalspath; ?>/<?php echo $header_base; ?>.conf"
<?php
				}
?>

		ssl.use-sslv2 = "disable"
		ssl.use-sslv3 = "disable"

		url.redirect = ( "/" =>  "https://<?php echo $webmailremote; ?>/" )

	}

	url.redirect = ( "/" =>  "http://<?php echo $webmailremote; ?>/" )

}

<?php

			} elseif ($webmailmap) {
				if ($webmailapp) {
?>

## webmail for parked '<?php echo $parkdomainname; ?>'
$HTTP["host"] =~ "^webmail\.<?php echo str_replace(".", "\.", $parkdomainname); ?>" {

	include "<?php echo $globalspath; ?>/acme-challenge.conf"

	$HTTP["scheme"] == "https" {

		ssl.engine = "enable"

		ssl.pemfile = "<?php echo $cert_file; ?>.pem"
<?php
					if (file_exists("{$cert_file}.ca")) {
?>

		ssl.ca-file = "<?php echo $cert_file; ?>.ca"

		include "<?php echo $globalspath; ?>/<?php echo $header_base; ?>.conf"
<?php
					}
?>

		ssl.use-sslv2 = "disable"
		ssl.use-sslv3 = "disable"

	}

	var.user = "apache"
	var.fpmport = "<?php echo $fpmportapache; ?>"
	var.rootdir = "<?php echo $webmaildocroot; ?>/"
	var.phpselected = "php"
	var.timeout = "<?php echo $timeout; ?>"

	server.document-root = var.rootdir

	index-file.names = ( <?php echo $indexorder; ?> )

	include "<?php echo $globalspath; ?>/switch_standard.conf"

}

<?php
	   			 }
	   		 } else {
?>

## No mail map for parked '<?php echo $parkdomainname; ?>'

<?php
			}
		}
	}
}

if ($domainredirect) {
	foreach ($domainredirect as $domredir) {
		$redirdomainname = $domredir['redirdomain'];
		$webmailmap = ($domredir['mailflag'] === 'on') ? true : false;

		if ($disabled) {
?>

## webmail for redirect '<?php echo $redirdomainname; ?>'
$HTTP["host"] =~ "^webmail\.<?php echo str_replace(".", "\.", $redirdomainname); ?>" {

	include "<?php echo $globalspath; ?>/acme-challenge.conf"

	$HTTP["scheme"] == "https" {

		ssl.engine = "enable"

		ssl.pemfile = "<?php echo $cert_file; ?>.pem"
<?php
			if (file_exists("{$cert_file}.ca")) {
?>

		ssl.ca-file = "<?php echo $cert_file; ?>.ca"

		include "<?php echo $globalspath; ?>/<?php echo $header_base; ?>.conf"
<?php
			}
?>

		ssl.use-sslv2 = "disable"
		ssl.use-sslv3 = "disable"

	}

	var.user = "apache"
	var.fpmport = "<?php echo $fpmportapache; ?>"
	var.rootdir = "<?php echo $disabledocroot; ?>/"
	var.phpselected = "php"
	var.timeout = "<?php echo $timeout; ?>"

	server.document-root = var.rootdir

	index-file.names = ( <?php echo $indexorder; ?> )

	include "<?php echo $globalspath; ?>/switch_standard.conf"

}

<?php
		} else {
			if ($webmailremote) {
?>

## webmail for redirect '<?php echo $redirdomainname; ?>'
$HTTP["host"] =~ "^webmail\.<?php echo str_replace(".", "\.", $redirdomainname); ?>" {

	$HTTP["scheme"] == "https" {

		ssl.engine = "enable"

		ssl.pemfile = "<?php echo $cert_file; ?>.pem"
<?php
				if (file_exists("{$cert_file}.ca")) {
?>

		ssl.ca-file = "<?php echo $cert_file; ?>.ca"

		include "<?php echo $globalspath; ?>/<?php echo $header_base; ?>.conf"
<?php
				}
?>

		ssl.use-sslv2 = "disable"
		ssl.use-sslv3 = "disable"

		url.redirect = ( "/" =>  "https://<?php echo $webmailremote; ?>/" )

	}

	url.redirect = ( "/" =>  "http://<?php echo $webmailremote; ?>/" )

}

<?php
			} elseif ($webmailmap) {
				if ($webmailapp) {
?>

## webmail for redirect '<?php echo $redirdomainname; ?>'
$HTTP["host"] =~ "^webmail\.<?php echo str_replace(".", "\.", $redirdomainname); ?>" {

	include "<?php echo $globalspath; ?>/acme-challenge.conf"

	$HTTP["scheme"] == "https" {

		ssl.engine = "enable"

		ssl.pemfile = "<?php echo $cert_file; ?>.pem"
<?php
					if (file_exists("{$cert_file}.ca")) {
?>

		ssl.ca-file = "<?php echo $cert_file; ?>.ca"

		include "<?php echo $globalspath; ?>/<?php echo $header_base; ?>.conf"
<?php
					}
?>

		ssl.use-sslv2 = "disable"
		ssl.use-sslv3 = "disable"

	}

	var.user = "apache"
	var.fpmport = "<?php echo $fpmportapache; ?>"
	var.rootdir = "<?php echo $webmaildocroot; ?>/"
	var.phpselected = "php"
	var.timeout = "<?php echo $timeout; ?>"

	server.document-root = var.rootdir

	index-file.names = ( <?php echo $indexorder; ?> )

	include "<?php echo $globalspath; ?>/switch_standard.conf"

}

<?php
				}
			} else {
?>

## No mail map for redirect '<?php echo $redirdomainname; ?>'

<?php
			}
		}
	}
}

if ($ip !== '*') {
	$ipssl = "|" . $ip;
} else {
	$ipssl = "";
}

if ($wwwredirect) {
?>

## web for '<?php echo $domainname; ?>'
$HTTP["host"] =~ "<?php echo $domainname; ?><?php echo $ipssl; ?>" {

	$HTTP["scheme"] == "https" {

		ssl.engine = "enable"

		ssl.pemfile = "<?php echo $cert_file; ?>.pem"
<?php
	if (file_exists("{$cert_file}.ca")) {
?>

		ssl.ca-file = "<?php echo $cert_file; ?>.ca"

		include "<?php echo $globalspath; ?>/<?php echo $header_base; ?>.conf"
<?php
	}
?>

		ssl.use-sslv2 = "disable"
		ssl.use-sslv3 = "disable"

		url.redirect = ( "^/(.*)" => "https://www.<?php echo $domainname; ?>/$1" )

	}

	url.redirect = ( "^/(.*)" => "http://www.<?php echo $domainname; ?>/$1" )
}


## web for '<?php echo $domainname; ?>'
$HTTP["host"] =~ "<?php echo $serveralias; ?><?php echo $ipssl; ?>" {
<?php
} else {
?>

## web for '<?php echo $domainname; ?>'
$HTTP["host"] =~ "<?php echo $serveralias; ?><?php echo $ipssl; ?>" {

	include "<?php echo $globalspath; ?>/acme-challenge.conf"
<?php
}
?>

	$HTTP["scheme"] == "https" {

		ssl.engine = "enable"

		ssl.pemfile = "<?php echo $cert_file; ?>.pem"
<?php
if (file_exists("{$cert_file}.ca")) {
?>

		ssl.ca-file = "<?php echo $cert_file; ?>.ca"

		include "<?php echo $globalspath; ?>/<?php echo $header_base; ?>.conf"
<?php
}
?>
		ssl.use-sslv2 = "disable"
		ssl.use-sslv3 = "disable"

	}

	var.domain = "<?php echo $domainname; ?>"
	var.user = "<?php echo $sockuser; ?>"
	var.fpmport = "<?php echo $fpmport; ?>"
	var.phpselected = "<?php echo $phpselected; ?>"
	var.timeout = "<?php echo $timeout; ?>"
<?php
if ($disabled) {
?>
	var.rootdir = "<?php echo $disabledocroot; ?>/"

	server.document-root = var.rootdir
<?php
} else {
?>
	var.rootdir = "<?php echo $rootpath; ?>/"

	server.document-root = var.rootdir
<?php
}
?>

	index-file.names = ( <?php echo $indexorder; ?> )
<?php
if ($redirectionlocal) {
	foreach ($redirectionlocal as $rl) {
?>

	alias.url  += ( "<?php echo $rl[0]; ?>" => "$rootdir<?php echo str_replace("//", "/", $rl[1]); ?>" )
<?php
	}
}

if ($redirectionremote) {
	foreach ($redirectionremote as $rr) {
		if ($rr[0] === '/') {
			$rr[0] = '';
		}

		if ($rr[2] === 'both') {
?>

	url.redirect += ( "^(<?php echo $rr[0]; ?>/|<?php echo $rr[0]; ?>$)" => "http://<?php echo $rr[1]; ?>" )
<?php
		} else {
			$protocol2 = ($rr[2] === 'https') ? "https://" : "http://";
?>

	url.redirect += ( "^(/<?php echo $rr[0]; ?>/|/<?php echo $rr[0]; ?>$)" => "<?php echo $protocol2; ?><?php echo $rr[1]; ?>" )
<?php
				if ((!$reverseproxy) || (($reverseproxy) && ($webselected === 'front-end'))) {
					if ($enablestats) {
?>

	include "<?php echo $globalspath; ?>/stats.conf"
<?php
						if ($statsprotect) {
?>

	include "<?php echo $globalspath; ?>/dirprotect_stats.conf"
<?php
						}
	   				 }
				}
			}
		}
	}

	if ($lighttpdextratext) {
?>

	# Extra Tags - begin
<?php echo $lighttpdextratext; ?>

	# Extra Tags - end
<?php
	}

	if ((!$reverseproxy) && (file_exists("{$globalspath}/{$domainname}.conf"))) {
		if ($enablephp) {
?>

	include "<?php echo $globalspath; ?>/<?php echo $domainname; ?>.conf"
<?php
		}
	} else {
		if (($reverseproxy) && ($webselected === 'front-end')) {
			if ($enablephp) {
?>

	include "<?php echo $globalspath; ?>/php-fpm_standard.conf"
<?php
			}
		} else {
?>

	include "<?php echo $globalspath; ?>/switch_standard.conf"
<?php
		}
	}

	if (!$reverseproxy) {
		if ($dirprotect) {
			foreach ($dirprotect as $k) {
				$protectpath = $k['path'];
				$protectauthname = $k['authname'];
				$protectfile = str_replace('/', '_', $protectpath) . '_';
?>

	$HTTP["url"] =~ "^/<?php echo $protectpath; ?>[/$]" {
		auth.backend = "htpasswd"
		auth.backend.htpasswd.userfile = "/home/httpd/" + var.domain + "/__dirprotect/<?php echo $protectfile; ?>"
		auth.require = ( "/<?php echo $protectpath; ?>" => (
		"method" => "basic",
		"realm" => "<?php echo $protectauthname; ?>",
		"require" => "valid-user"
		))
	}
<?php
			}
		}
	}

	if ($blockips) {
?>

	$HTTP["remoteip"] =~ "{<?php echo $blockips; ?>}" {
		url.access-deny = ( "" )
	}
<?php
	}
?>

	var.kloxoportssl = "<?php echo $kloxoportssl; ?>"
	var.kloxoportnonssl = "<?php echo $kloxoportnonssl; ?>"

	include "<?php echo $globalspath; ?>/<?php echo $generic; ?>.conf"

	alias.url += ( "/" => var.rootdir )

<?php
	if ($enablecgi) {
?>

	$HTTP["url"] =~ "^/cgi-bin" {
		#cgi.assign = ( "" => "/home/httpd/" + var.domain + "/perlsuexec.sh" )
		cgi.assign = ( "" => "/usr/bin/perl" )
	}
<?php
	}
?>

	$HTTP["url"] =~ "^/" {
<?php
	if ($enablecgi) {
?>
		#cgi.assign = ( ".pl" => "/home/httpd/" + var.domain + "/perlsuexec.sh" )
		cgi.assign = ( ".pl" => "/usr/bin/perl" )
<?php
	}

	if ($dirindex) {
?>
		dir-listing.activate = "enable"
<?php
	}
?>

		## trick using 'microcache' not work; no different performance!
		#expire.url = ( "" => "access 10 seconds" )
	}

}


### end - web of '<?php echo $domainname; ?>' - do not remove/modify this line
