### begin - web of initial - do not remove/modify this line

<?php

// MR -- disable cgi module
if (file_exists('/etc/httpd/conf.modules.d/01-cgi.conf')) {
	exec("'cp' -f /opt/configs/apache/etc/conf.modules.d/01-cgi.conf /etc/httpd/conf.modules.d/01-cgi.conf");
}

if (!file_exists("/var/run/letsencrypt/.well-known/acme-challenge")) {
	exec("mkdir -p /var/run/letsencrypt/.well-known/acme-challenge");
}

if (!isset($phpselected)) {
	$phpselected = 'php';
}

if (!isset($timeout)) {
	$timeout = '300';
}

$srcpath = "/opt/configs/apache/etc";
$srccpath = "/opt/configs/apache/etc/conf";
$srccdpath = "/opt/configs/apache/etc/conf.d";
$srccmdpath = "/opt/configs/apache/etc/conf.modules.d";
$trgtpath = "/etc";
$trgtcpath = "/etc/httpd/conf";
$trgtcdpath = "/etc/httpd/conf.d";
$trgtcmdpath = "/etc/httpd/conf.modules.d";

$sslpath = "/home/kloxo/ssl";
$kloxopath = "/usr/local/lxlabs/kloxo";

// MR -- fix error 'Directory / is not owned by admin' for suphp
exec("chown root.root /");

// MR -- mod_ruid2 from epel use mod_ruid2.conf
foreach (glob("{$trgtcdpath}/mod_*.conf") as $file)
{
	$newfile = str_replace('.conf', '.nonconf', $file);
	
	if (file_exists($newfile)) {
		unlink($newfile);
	}
	
	rename($file, $newfile);
}

$mpmlist = array('prefork', 'itk', 'event', 'worker');

// @exec("httpd -v|grep 'version:'|grep '/2.4.'", $out);
// @exec("rpm -qa|grep -E '^httpd24-2.4', $out);


if (file_exists("{$kloxopath}/etc/flag/use_apache24.flg")) {
	$use_httpd24 = true;

	exec("'cp' -f {$srccpath}/httpd24.conf /{$trgtcpath}/httpd.conf");

	if (file_exists("{$trgtcmdpath}/00-base.conf")) {
		exec("sed -i 's/^LoadModule deflate_module/#LoadModule deflate_module/' {$trgtcmdpath}/00-base.conf");
	}

	foreach ($mpmlist as $k => $v) {
		if (strpos($phptype, $v) !== false) {
			if (file_exists("{$trgtcmdpath}/00-mpm.conf")) {
				exec("echo 'LoadModule mpm_{$v}_module modules/mod_mpm_{$v}.so' > {$trgtcmdpath}/00-mpm.conf");
				break;
			}
		}
	}

	// MR -- disable cgi module
	if (file_exists("{$trgtcmdpath}/01-cgi.conf")) {
		exec("sed -i 's/^LoadModule cgid_module/#LoadModule cgid_module/' {$trgtcmdpath}/01-cgi.conf");
	}
	
	// MR -- make blank content
	exec("echo '' > /etc/sysconfig/httpd");
} else {
	$use_httpd24 = false;

	exec("'cp' -f {$srccpath}/httpd.conf {$trgtcpath}/httpd.conf");

	// as 'httpd' as default mpm
	exec("echo 'HTTPD=/usr/sbin/httpd' > /etc/sysconfig/httpd");

	foreach ($mpmlist as $k => $v) {
		if (strpos($phptype, $v) !== false) {
			exec("echo 'HTTPD=/usr/sbin/httpd.{$v}' > /etc/sysconfig/httpd");
			break;
		}
	}

	// MR -- disable cgi module
	exec("sed -i 's/^LoadModule cgi_module/#LoadModule cgi_module/' {$trgtcpath}/httpd.conf");
}

if ($use_httpd24) {
	if (file_exists("{$srccpath}/custom.httpd24.conf")) {
		copy("{$srccpath}/custom.httpd24.conf", "{$trgtcpath}/httpd.conf");
	} else if (file_exists("{$srccpath}/httpd24.conf")) {
		copy("{$srccpath}/httpd24.conf", "{$trgtcpath}/httpd.conf");
	}
} else {
	if (file_exists("{$srccpath}/custom.httpd.conf")) {
		copy("{$srccpath}/custom.httpd.conf", "{$trgtcpath}/httpd.conf");
	} else if (file_exists("{$srccpath}/httpd.conf")) {
		copy("{$srccpath}/httpd.conf", "{$trgtcpath}/httpd.conf");
	}
}

$modlist = array("~lxcenter", "ssl", "__version", "perl", "rpaf", "define", "_inactive_");

foreach ($modlist as $k => $v) {
	if (file_exists("{$srccdpath}/custom.{$v}.conf")) {
		copy("{$srccdpath}/custom.{$v}.conf", "{$trgtcdpath}/{$v}.conf");
	} else if (file_exists("{$srccdpath}/{$v}.conf")) {
		if ($v !== '~lxcenter') {
			copy("{$srccdpath}/{$v}.conf", "{$trgtcdpath}/{$v}.conf");
		}
	}
}

// MR -- because 'pure' mod_php disabled (security reason)
if (file_exists("{$srccdpath}/custom._inactive_.conf")) {
	copy("{$srccdpath}/custom._inactive_.conf", "{$trgtcdpath}/php.conf");
} else {
	copy("{$srccdpath}/_inactive_.conf", "{$trgtcdpath}/php.conf");
}

$typelist = array('ruid2', 'suphp', 'fcgid', 'fastcgi', 'proxy_fcgi');

foreach ($typelist as $k => $v) {
	if ($v === 'fastcgi') {
		$w = 'php-fpm';
	} else {
		$w = $v;
	}

	if (strpos($phptype, "{$w}") !== false) {
		if (file_exists("{$srccdpath}/custom.{$v}.conf")) {
			copy("{$srccdpath}/custom.{$v}.conf", "{$trgtcdpath}/{$v}.conf");
		} else if (file_exists("{$srccdpath}/{$v}.conf")) {
			copy("{$srccdpath}/{$v}.conf", "{$trgtcdpath}/{$v}.conf");
		}
	} else {
		if ($v === 'proxy_fcgi') {
			if (file_exists("{$srccmdpath}/custom._inactive_.conf")) {
				copy("{$srccmdpath}/custom._inactive_.conf", "{$trgtcmdpath}/00-proxy.conf");
			} else if (file_exists("{$srccmdpath}/_inactive_.conf")) {
				copy("{$srccmdpath}/_inactive_.conf", "{$trgtcmdpath}/00-proxy.conf");
			}

			if (file_exists("{$trgtcdpath}/{$v}.conf")) {
				unlink("{$trgtcdpath}/{$v}.conf");
				unlink("{$trgtcdpath}/{$v}.nonconf");
			}
		} else {
			if (file_exists("{$srccdpath}/custom._inactive_.conf")) {
				copy("{$srccdpath}/custom._inactive_.conf", "{$trgtcdpath}/{$v}.conf");
			} else if (file_exists("{$srccdpath}/_inactive_.conf")) {
				copy("{$srccdpath}/_inactive_.conf", "{$trgtcdpath}/{$v}.conf");
			}
		}
	}
}

if (file_exists("{$srcpath}/custom.suphp.conf")) {
	copy("{$srcpath}/custom.suphp.conf", "{$trgtpath}/suphp.conf");
} else if (file_exists("{$srcpath}/suphp.conf")) {
	copy("{$srcpath}/suphp.conf", "{$trgtpath}/suphp.conf");
}

foreach ($certnamelist as $ip => $certname) {
	$certnamelist[$ip] = "{$sslpath}/{$certname}";
}

if ($reverseproxy) {
	$tmp_ip = '127.0.0.1';

	foreach ($certnamelist as $ip => $certname) {
		$tmp_certname = $certname;
		break;
	}

	$certnamelist = null;

	$certnamelist[$tmp_ip] = $tmp_certname;
} else {
	$tmp_ip = '*';
}

if ($reverseproxy) {
	$ports[] = '30080';
	$ports[] = '30443';
} else {
	if (($webcache === 'none') || (!$webcache)) {
		$ports[] = '80';
		$ports[] = '443';
	} else {
		$ports[] = '8080';
		$ports[] = '8443';
	}
}

$portlist = array('${port}', '${portssl}');

$globalspath = "/opt/configs/apache/conf/globals";

$portnip = "Define port {$ports[0]}\nDefine portssl {$ports[1]}\nDefine ip {$tmp_ip}\n";

file_put_contents("{$globalspath}/portnip.conf", $portnip);

$defaultdocroot = "/home/kloxo/httpd/default";

if ($indexorder) {
	$indexorder = implode(' ', $indexorder);
}

if (file_exists("{$globalspath}/custom.acme-challenge.conf")) {
	$acmechallenge = "custom.acme-challenge";
} else if (file_exists("{$globalspath}/acme-challenge.conf")) {
	$acmechallenge = "acme-challenge";
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

if ($use_httpd24) {
	if (file_exists("{$globalspath}/custom.ssl_base24.conf")) {
		$ssl_base = "custom.ssl_base24";
	} else if (file_exists("{$globalspath}/ssl_base24.conf")) {
		$ssl_base = "ssl_base24";
	}
} else {
	if (file_exists("{$globalspath}/custom.ssl_base.conf")) {
		$ssl_base = "custom.ssl_base";
	} else if (file_exists("{$globalspath}/ssl_base.conf")) {
		$ssl_base = "ssl_base";
	}
}

// MR -- for future purpose, apache user have uid 50000
// $userinfoapache = posix_getpwnam('apache');
// $fpmportapache = (50000 + $userinfoapache['uid']);
$fpmportapache = 50000;

foreach ($certnamelist as $ip => $certname) {
?>

<IfVersion < 2.4>
	Define global::port <?php echo $ports[0]; ?>

	Define global::portssl <?php echo $ports[1]; ?>

	Define global::ip <?php echo $ip; ?>


	Define port ${global::port}
	Define portssl ${global::portssl}
	Define ip ${global::ip}
</IfVersion>

<IfVersion >= 2.4>
	Include <?php echo $globalspath; ?>/portnip.conf
</IfVersion>

Listen ${ip}:${port}
Listen ${ip}:${portssl}

<IfVersion < 2.4>
	NameVirtualHost ${ip}:${port}
	NameVirtualHost ${ip}:${portssl}
</IfVersion>
<?php
}
?>

## MR -- ruid2 not work dor userdir!
<Ifmodule mod_userdir.c>
	UserDir enabled
	UserDir /home/*/public_html
<?php
	foreach ($userlist as &$user) {
		$userinfo = posix_getpwnam($user);

		if (!$userinfo) {
			continue;
		}
?>
	<Location "/~<?php echo $user; ?>">
		<IfModule mod_suphp.c>
			SuPhp_UserGroup <?php echo $user; ?> <?php echo $user; ?>

		</IfModule>
	</Location>
<?php
}
?>
</Ifmodule>

<?php
foreach ($certnamelist as $ip => $certname) {
	$count = 0;

	foreach ($ports as &$port) {
?>

### 'default' config
<VirtualHost ${ip}:<?php echo $portlist[$count]; ?> >

	SetEnvIf X-Forwarded-Proto https HTTPS=1

	ServerName default

	ServerAlias default.*

	DocumentRoot "<?php echo $defaultdocroot; ?>"

	Include <?php echo $globalspath; ?>/<?php echo $acmechallenge; ?>.conf

	DirectoryIndex <?php echo $indexorder; ?>

<?php
		if ($count !== 0) {
?>

	<IfModule mod_http2.c>
		Protocols h2 http/1.1
	</IfModule>

	<IfModule mod_ssl.c>
		Include <?php echo $globalspath; ?>/<?php echo $ssl_base; ?>.conf

		SSLCertificateFile <?php echo $certname; ?>.pem
		SSLCertificateKeyFile <?php echo $certname; ?>.key
<?php
			if (file_exists("{$certname}.ca")) {

?>
		SSLCACertificatefile <?php echo $certname; ?>.ca

		Include <?php echo $globalspath; ?>/<?php echo $header_base; ?>.conf
<?php
			}
?>
	</IfModule>
<?php
		} else {
?>

	<IfModule mod_http2.c>
		Protocols h2c http/1.1
	</IfModule>
<?php
		}
?>

	<IfModule suexec.c>
		SuexecUserGroup apache apache
	</IfModule>

	<IfModule mod_suphp.c>
		SuPhp_UserGroup apache apache
	</IfModule>

	#<IfVersion < 2.4>
		<IfModule mod_ruid2.c>
			RMode config
			RUidGid apache apache
			RMinUidGid apache apache
		</IfModule>

		<IfModule itk.c>
			AssignUserId apache apache
		</IfModule>

		<IfModule mod_fastcgi.c>
			Alias /default.<?php echo $count; ?>fake "<?php echo $defaultdocroot; ?>/default.<?php echo $count; ?>fake"
			#FastCGIExternalServer "<?php echo $defaultdocroot; ?>/default.<?php echo $count; ?>fake" -host 127.0.0.1:<?php echo $fpmportapache; ?> -idle-timeout <?php echo $timeout; ?> -pass-header Authorization
			FastCGIExternalServer "<?php echo $defaultdocroot; ?>/default.<?php echo $count; ?>fake" -socket /opt/configs/php-fpm/sock/php-apache.sock -idle-timeout <?php echo $timeout; ?> -pass-header Authorization
			<FilesMatch \.php$>
				SetHandler application/x-httpd-fastphp
			</FilesMatch>
			Action application/x-httpd-fastphp /default.<?php echo $count; ?>fake
			<Files "default.<?php echo $count; ?>fake">
				RewriteCond %{REQUEST_URI} !default.<?php echo $count; ?>fake
			</Files>
		</IfModule>

		<IfModule !mod_ruid2.c>
			<IfModule !mod_itk.c>
				<IfModule !mod_fastcgi.c>
					<IfModule mod_fcgid.c>
						<Directory "<?php echo $defaultdocroot; ?>/">
							Options +ExecCGI
							<FilesMatch \.php$>
								SetHandler fcgid-script
							</FilesMatch>
							FCGIWrapper /home/kloxo/client/php.fcgi .php
						</Directory>
					</IfModule>
				</IfModule>
			</IfModule>	
		</IfModule>
	#</IfVersion>

	<IfVersion >= 2.4>
		<IfModule mod_proxy_fcgi.c>
			ProxyRequests Off
			ProxyErrorOverride On
			ProxyPass /error !
			ErrorDocument 500 /error/500.html
			<FilesMatch \.php$>
				SetHandler "proxy:unix:/opt/configs/php-fpm/sock/php-apache.sock|fcgi://localhost"
			</FilesMatch>
			<Proxy "fcgi://localhost">
				ProxySet timeout=<?php echo $timeout; ?>

				ProxySet connectiontimeout=<?php echo $timeout; ?>

				#ProxySet enablereuse=on
				ProxySet max=25
				ProxySet retry=0
			</Proxy>
		</IfModule>
	</IfVersion>

	<Location "/">
		# Options +Indexes +FollowSymlinks
		Options -Indexes -FollowSymlinks +SymLinksIfOwnerMatch
	</Location>

	<Directory "<?php echo $defaultdocroot; ?>/">
		AllowOverride All
		<IfVersion < 2.4>
			Order allow,deny
			Allow from all
		</IfVersion>
		<IfVersion >= 2.4>
			Require all granted
		</IfVersion>
	</Directory>

</VirtualHost>

<?php
		$count++;
	}
}
?>

### end - web of initial - do not remove/modify this line
