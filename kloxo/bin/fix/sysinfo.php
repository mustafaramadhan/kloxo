<?php

// by mustafa@bigraf.com for Kloxo-MR

include_once "lib/html/include.php";
initProgram('admin');

$kloxopath="/usr/local/lxlabs/kloxo";

exec("sh /script/version --vertype=full", $kloxomrver);
$kloxomrver = $kloxomrver[0];

exec("cat /etc/*release", $osrelease);
exec("uname -m", $osplateform);

$mysqlbranch = getRpmBranchInstalled('mysql');
if ($mysqlbranch) {
	exec("rpm -q {$mysqlbranch}", $out);
	$appmysql = trim($out[0]);
} else {
	$appmysql = '--uninstalled--';
}

$out = null;

$phpbranch = getRpmBranchInstalled('php');
if ($phpbranch) {
	exec("rpm -q {$phpbranch}-cli", $out);
	$appphp = trim($out[0]);
} else {
	$appphp = '--uninstalled--';
}

$out = null;

$phpmdirs = glob("/opt/php*m", GLOB_MARK);

$phpsbranch = str_replace("\n", "", file_get_contents("{$kloxopath}/init/kloxo_php_active"));
$phpsver = str_replace("\n", "", file_get_contents("/opt/{$phpsbranch}/version"));

if (file_exists("{$kloxopath}/init/kloxo_use_php-cgi")) {
	$phpsver = $phpsver . " (cgi mode)";
} else {
	$phpsver = $phpsver . " (fpm mode)";
}

$httpdbranch = getRpmBranchInstalled('httpd');
if ($httpdbranch) {
	exec("rpm -q {$httpdbranch}", $out);
	$apphttpd = trim($out[0]);
} else {
	$apphttpd = '--uninstalled--';
}

$out = null;

$lighttpdbranch = getRpmBranchInstalled('lighttpd');
if ($lighttpdbranch) {
	exec("rpm -q {$lighttpdbranch}", $out);
	$applighttpd = trim($out[0]);
} else {
	$applighttpd = '--uninstalled--';
}

$out = null;

$nginxbranch = getRpmBranchInstalled('nginx');
if ($nginxbranch) {
	exec("rpm -q {$nginxbranch}", $out);
	$appnginx = trim($out[0]);
} else {
	$appnginx = '--uninstalled--';
}

$out = null;

$hiawathabranch = getRpmBranchInstalled('hiawatha');
if ($hiawathabranch) {
	exec("rpm -q {$hiawathabranch}", $out);
	$apphiawatha = trim($out[0]);
	$kloxohiawatha = $apphiawatha;

	$out = null;

	exec("chkconfig --list|grep 'hiawatha'|grep ':on'", $out);

	if ($out[0] !== null) {
		$apphiawatha = "--used--";
	} else {
	//	$apphiawatha .= " (also as webserver)";
		$apphiawatha = "--unused--";
	}
} else {
	$apphiawatha = '--uninstalled--';
}

$out = null;

$atsbranch = getRpmBranchInstalled('trafficserver');
if ($atsbranch) {
	exec("rpm -q {$atsbranch}", $out);
	$appats = trim($out[0]);
} else {
	$appats = '--uninstalled--';
}

$out = null;

$squidbranch = getRpmBranchInstalled('squid');
if ($squidbranch) {
	exec("rpm -q {$squidbranch}", $out);
	$appsquid = trim($out[0]);
} else {
	$appsquid = '--uninstalled--';
}

$out = null;

$varnishbranch = getRpmBranchInstalled('varnish');
if ($varnishbranch) {
	exec("rpm -q {$varnishbranch}", $out);
	$appvarnish = trim($out[0]);
} else {
	$appvarnish = '--uninstalled--';
}

$out = null;

$bindbranch = getRpmBranchInstalled('bind');
if ($bindbranch) {
	exec("rpm -q {$bindbranch}", $out);
	$appbind = trim($out[0]);
} else {
	$appbind = '--uninstalled--';
}

$out = null;

$djbdnsbranch = getRpmBranchInstalled('djbdns');
if ($djbdnsbranch) {
	exec("rpm -q {$djbdnsbranch}", $out);
	$appdjbdns = trim($out[0]);
} else {
	$appdjbdns = '--uninstalled--';
}

$out = null;

$nsdbranch = getRpmBranchInstalled('nsd');
if ($nsdbranch) {
	exec("rpm -q {$nsdbranch}", $out);
	$appnsd = trim($out[0]);
} else {
	$appnsd = '--uninstalled--';
}

$out = null;

$pdnsbranch = getRpmBranchInstalled('pdns');
if ($pdnsbranch) {
	exec("rpm -q {$pdnsbranch}", $out);
	$apppdns = trim($out[0]);
} else {
	$apppdns = '--uninstalled--';
}

$out = null;

$yadifabranch = getRpmBranchInstalled('yadifa');
if ($yadifabranch) {
	exec("rpm -q {$yadifabranch}", $out);
	$appyadifa = trim($out[0]);
} else {
	$appyadifa = '--uninstalled--';
}

$out = null;

$qmailbranch = getRpmBranchInstalled('qmail-toaster');
if ($qmailbranch) {
	exec("rpm -q {$qmailbranch}", $out);
	$appqmail = trim($out[0]);
} else {
	$appqmail = '--uninstalled--';
}

$out = null;

$dovecotbranch = getRpmBranchInstalled('dovecot');
if ($dovecotbranch) {
	exec("rpm -q {$dovecotbranch}", $out);
	$appdovecot = trim($out[0]);
} else {
	$appdovecot = '--uninstalled--';
}

$out = null;

$courierimapbranch = 'courier-imap-toaster';
$isinstalled = isRpmInstalled($courierimapbranch);
if ($isinstalled) {
	exec("rpm -q {$courierimapbranch}", $out);
	$appcourierimap = trim($out[0]);
} else {
	$appcourierimap = '--uninstalled--';
}

$out = null;

$a = get_namelist_from_objectlist($login->getList('pserver'), 'syncserver');
$b = implode("", $a);

$phptype = db_get_value('serverweb', "pserver-{$b}", 'php_type');

if (!isset($phptype)) {
	$phptype = '[unknown]';
}

$seddata = 's/^custom_name=\"\(.*\)\"/\1/';
exec("cat /etc/rc.d/init.d/php-fpm|grep 'custom_name='|sed -e '" . $seddata . "'", $out);

if ($out[0] !== null) {
	$phpused = $out[0];
} else {
	$phpused = '--Use PHP Branch--';
}

$out = null;

exec("free -m", $meminfo);

exec("df -h /", $diskinfo);

echo "\n";
echo "A. Control Panel:" .
	"               \n"; // need more space because overwrite waiting line
echo "   - Kloxo-MR: " . $kloxomrver . "\n";
echo "   - Web: " . $kloxohiawatha . "\n";
echo "   - PHP: " . $phpsbranch . "-" . $phpsver . "\n";
//echo "\n";
echo "B. Plateform:\n";
echo "   - OS: " . $osrelease[0] . " " . $osplateform[0] . "\n";
echo "   - Hostname: " . gethostname() . "\n";
//echo "\n";
echo "C. Services:\n";
echo "   1. MySQL: " .  $appmysql . "\n";
echo "   2. PHP: \n";
echo "      - Branch: " .  $appphp . "\n";
if ($phpmdirs) {
	echo "      - Multiple: \n";
	foreach ($phpmdirs as $k => $v) {
		$v1 = str_replace("/", "", str_replace("/opt/", "", $v));
		$v2  = file_get_contents($v . "/version");
		echo "        * " . $v1 . "-" . str_replace("\n", "", $v2) . "\n";
	}
}
echo "      - Used: " . $phpused . "\n";
echo "   3. Web Used: " . slave_get_driver('web') . "\n";
echo "     - Hiawatha: " .  $apphiawatha . "\n";
echo "     - Lighttpd: " .  $applighttpd . "\n";
echo "     - Nginx: " .  $appnginx . "\n";
echo "     - Httpd: " .  $apphttpd . "\n";
echo "       - PHP Type: " . $phptype . "\n";
echo "   4. WebCache: " .  slave_get_driver('webcache') . "\n";
echo "     - ATS: " .  $appats . "\n";
echo "     - Squid: " .  $appsquid . "\n";
echo "     - Varnish: " .  $appvarnish . "\n";
echo "   5. Dns: " .  slave_get_driver('dns') . "\n";
echo "     - Bind: " .  $appbind . "\n";
echo "     - DJBDns: " .  $appdjbdns . "\n";
echo "     - NSD: " .  $appnsd . "\n";
echo "     - PowerDNS: " .  $apppdns . "\n";
echo "     - Yadifa: " .  $appyadifa . "\n";
echo "   6. Mail: " .  $appqmail . "\n";

if ($appdovecot !== '--uninstalled--') {
	echo "      - with: " . $appdovecot  . "\n";
}
if ($appcourierimap !== '--uninstalled--') {
	echo "      - pop3/imap4: " . $appcourierimap  . "\n";
}

echo "      - spam: " . slave_get_driver('spam')  . "\n";

//echo "\n";
echo "D. Memory:\n";
foreach ($meminfo as $k => $v) {
	echo "   " . $v . "\n";
}
//echo "\n";
echo "E. Disk Space:\n";
foreach ($diskinfo as $k => $v) {
	echo "   " . $v . "\n";
}
echo "\n";

