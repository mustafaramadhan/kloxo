### begin - web of initial - do not remove/modify this line

<?php

if (!file_exists("/var/run/letsencrypt/.well-known/acme-challenge")) {
	exec("mkdir -p /var/run/letsencrypt/.well-known/acme-challenge");
}

if (!isset($phpselected)) {
	$phpselected = 'php';
}

$srcpath = "/opt/configs/apache/etc";
$srccpath = "/opt/configs/apache/etc/conf";
$srccdpath = "/opt/configs/apache/etc/conf.d";
$srccmdpath = "/opt/configs/apache/etc/conf.modules.d";
$trgtpath = "/etc";
$trgtcpath = "/etc/httpd/conf";
$trgtcdpath = "/etc/httpd/conf.d";
$trgtcmdpath = "/etc/httpd/conf.modules.d";

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


if (file_exists("/usr/local/lxlabs/kloxo/etc/flag/use_apache24.flg")) {
	$httptype="httpd24";
	// MR -- enable all optional module
	// exec("sed -i 's/^#LoadModule/LoadModule/' {$trgtcmdpath}/00-optional.conf");
	// MR -- disable deflate module; auto-enable by mod-pagespeed
	exec("sed -i 's/^LoadModule deflate_module/#LoadModule deflate_module/' {$trgtcmdpath}/00-base.conf");
/*
	// MR -- disabled all first; prefork not work because need 'special' php
	exec("sed -i 's/^LoadModule mpm_/#LoadModule mpm_/' {$trgtcmdpath}/00-mpm.conf");

	foreach ($mpmlist as $k => $v) {
		if (strpos($phptype, $v) !== false) {
			exec("sed -i 's/^#LoadModule mpm_{$v}_module/LoadModule mpm_{$v}_module/' {$trgtcmdpath}/00-mpm.conf");
			break;
		}
	}
*/
	foreach ($mpmlist as $k => $v) {
		if (strpos($phptype, $v) !== false) {
			exec("echo 'LoadModule mpm_{$v}_module modules/mod_mpm_{$v}.so' > {$trgtcmdpath}/00-mpm.conf");
			break;
		}
	}
	
	// MR -- make blank content
	exec("echo '' > /etc/sysconfig/httpd");
/*
//	exec("sed -i 's/^LoadModule lbmethod_heartbeat_module/#LoadModule lbmethod_heartbeat_module/' {$trgtcmdpath}/00-proxy.conf");
	if (file_exists("{$trgtcmdpath}/00-proxy.conf")) {
		exec("'mv' -f {$trgtcmdpath}/00-proxy.conf {$trgtcmdpath}/00-proxy.nonconf");
	}
*/
} else {
	$httptype="httpd";

	// as 'httpd' as default mpm
	exec("echo 'HTTPD=/usr/sbin/httpd' > /etc/sysconfig/httpd");

	foreach ($mpmlist as $k => $v) {
		if (strpos($phptype, $v) !== false) {
			exec("echo 'HTTPD=/usr/sbin/httpd.{$v}' > /etc/sysconfig/httpd");
			break;
		}
	}
}

if (file_exists("{$srccpath}/custom.{$httptype}.conf")) {
	copy("{$srccpath}/custom.{$httptype}.conf", "{$trgtcpath}/httpd.conf");
} else {
	copy("{$srccpath}/{$httptype}.conf", "{$trgtcpath}/httpd.conf");
}

$modlist = array("~lxcenter", "ssl", "__version", "perl", "rpaf", "define", "_inactive_");

foreach ($modlist as $k => $v) {
	if (file_exists("{$srccdpath}/custom.{$v}.conf")) {
		copy("{$srccdpath}/custom.{$v}.conf", "{$trgtcdpath}/{$v}.conf");
	} else {
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
		} else {
			copy("{$srccdpath}/{$v}.conf", "{$trgtcdpath}/{$v}.conf");
		}
	} else {
		if ($v === 'proxy_fcgi') {
			if (file_exists("{$srccmdpath}/custom._inactive_.conf")) {
				copy("{$srccmdpath}/custom._inactive_.conf", "{$trgtcmdpath}/00-proxy.conf");
			} else {
				copy("{$srccmdpath}/_inactive_.conf", "{$trgtcmdpath}/00-proxy.conf");
			}

			unlink("{$trgtcdpath}/{$v}.conf");
			unlink("{$trgtcdpath}/{$v}.nonconf");
		} else {
			if (file_exists("{$srccdpath}/custom._inactive_.conf")) {
				copy("{$srccdpath}/custom._inactive_.conf", "{$trgtcdpath}/{$v}.conf");
			} else {
				copy("{$srccdpath}/_inactive_.conf", "{$trgtcdpath}/{$v}.conf");
			}
		}
	}
}

if (file_exists("{$srcpath}/custom.suphp.conf")) {
	copy("{$srcpath}/custom.suphp.conf", "{$trgtpath}/suphp.conf");
} else {
	copy("{$srcpath}/suphp.conf", "{$trgtpath}/suphp.conf");
}

foreach ($certnamelist as $ip => $certname) {
	$certnamelist[$ip] = "/home/kloxo/httpd/ssl/{$certname}";
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

	Include <?php echo $globalspath; ?>/acme-challenge.conf

	DirectoryIndex <?php echo $indexorder; ?>

<?php
		if ($count !== 0) {
?>

	<IfModule mod_ssl.c>
		SSLEngine On
		SSLProtocol ALL -SSLv2 -SSLv3
		SSLHonorCipherOrder On
		#SSLCipherSuite ECDH+AESGCM:DH+AESGCM:ECDH+AES256:DH+AES256:ECDH+AES128:DH+AES:ECDH+3DES:DH+3DES:RSA+AESGCM:RSA+AES:RSA+3DES:!aNULL:!MD5:!DSS
		SSLCipherSuite "EECDH+ECDSA+AESGCM EECDH+aRSA+AESGCM EECDH+ECDSA+SHA384 EECDH+ECDSA+SHA256 EECDH+aRSA+SHA384 EECDH+aRSA+SHA256 EECDH+aRSA+RC4 EECDH EDH+aRSA RC4 !aNULL 
		SSLCertificateFile <?php echo $certname; ?>.pem
		SSLCertificateKeyFile <?php echo $certname; ?>.key
<?php
				if (file_exists("{$certname}.ca")) {

?>
		SSLCACertificatefile <?php echo $certname; ?>.ca
<?php
				}
?>
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
			#FastCGIExternalServer "<?php echo $defaultdocroot; ?>/default.<?php echo $count; ?>fake" -host 127.0.0.1:<?php echo $fpmportapache; ?> -idle-timeout 120 -pass-header Authorization
			FastCGIExternalServer "<?php echo $defaultdocroot; ?>/default.<?php echo $count; ?>fake" -socket /opt/configs/php-fpm/sock/php-apache.sock -idle-timeout 120 -pass-header Authorization
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
				ProxySet timeout=600
				ProxySet connectiontimeout=300
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
