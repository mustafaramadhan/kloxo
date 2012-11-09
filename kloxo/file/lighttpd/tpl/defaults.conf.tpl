### begin content - please not remove this line
<?php

if ($setdefaults === 'webmail') {
    if ($webmailappdefault) {
        $docroot = "/home/kloxo/httpd/webmail/{$webmailappdefault}";
    } else {
        $docroot = "/home/kloxo/httpd/webmail";
    }
} else {
    $docroot = "/home/kloxo/httpd/{$setdefaults}";
}

$ports[] = '80';
$ports[] = '443';

if ($indexorder) {
    $indexorder = implode(' ', $indexorder);
}

$indexorder = '"' . $indexorder . '"';
$indexorder = str_replace(' ', '", "', $indexorder);

$globalspath = "/home/lighttpd/conf/globals";

if (file_exists("{$globalspath}/custom.proxy.conf")) {
    $proxyconf = 'custom.proxy.conf';
} else {
    $proxyconf = 'proxy.conf';
}

if (file_exists("{$globalspath}/custom.php-fpm.conf")) {
    $phpfpmconf = 'custom.php-fpm.conf';
} else {
    $phpfpmconf = 'php-fpm.conf';
}

if (file_exists("{$globalspath}/custom.nobody.conf")) {
    $nobodyconf = 'custom.nobody.conf';
} else {
    $nobodyconf = 'nobody.conf';
}

$userinfoapache = posix_getpwnam('apache');
$fpmportapache = (50000 + $userinfoapache['uid']);

?>

<?php
if ($setdefaults === 'ssl') {
    foreach ($certnamelist as $ip => $certname) {
?>

$SERVER["socket"] == "<?php echo $ip; ?>:<?php echo $ports[1]; ?>" {

    ssl.engine = "enable"

    ssl.pemfile = "/home/kloxo/httpd/ssl/<?php echo $certname; ?>.pem"
    ssl.ca-file = "/home/kloxo/httpd/ssl/<?php echo $certname; ?>.ca"
    ssl.use-sslv2 = "disable"

}
<?php
    }
} elseif ($setdefaults === 'init') {
?>

## not needed

<?php
} else {
?>

$HTTP["host"] =~ "^<?php echo $setdefaults; ?>\.*" { 

    var.rootdir = "/home/kloxo/httpd/<?php echo $setdefaults; ?>/"

    server.document-root = var.rootdir

    index-file.names = ( <?php echo $indexorder; ?> )
<?php
    if ($reverseproxy) {
?>

    include "<?php echo $globalspath; ?>/<?php echo $proxyconf; ?>"
<?php
    } else {
?>

    var.fpmport = "<?php echo $fpmportapache; ?>"

    include "<?php echo $globalspath; ?>/<?php echo $phpfpmconf; ?>"

#    include "<?php echo $globalspath; ?>/<?php echo $nobodyconf; ?>"
<?php
    }
?>

}
<?php
}
?>


### end content - please not remove this line
