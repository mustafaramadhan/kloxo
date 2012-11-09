### begin content - please not remove this line
<?php

$ports[] = '80';
$ports[] = '443';

$iplist = array('*');

if ($setdefaults === 'webmail') {
    if ($webmailappdefault) {
        $rootpath = "/home/kloxo/httpd/webmail/{$webmailappdeffault}";
    } else {
        $rootpath = "/home/kloxo/httpd/webmail";
    }
} else {
    $rootpath = "/home/kloxo/httpd/{$setdefaults}";
}

if ($indexorder) {
    $indexorder = implode(' ', $indexorder);
}

$disablepath = "/home/kloxo/httpd/disable";

$globalspath = "/home/nginx/conf/globals";

if (file_exists("{$globalspath}/custom.proxy.conf")) {
    $proxyconf = 'custom.proxy.conf';
} else {
    $proxyconf = 'proxy.conf';
}

if (file_exists("{$globalspath}/custom.phpfpm.conf")) {
    $phpfpmconf = 'custom.php-fpm.conf';
} else {
    $phpfpmconf = 'php-fpm.conf';
}

if (file_exists("{$globalspath}/custom.perl.conf")) {
    $perlconf = 'custom.perl.conf';
} else {
    $perlconf = 'perl.conf';
}

$userinfoapache = posix_getpwnam('apache');
$fpmportapache = (50000 + $userinfoapache['uid']);

?>

<?php
if ($setdefaults === 'ssl') {
/*
    foreach ($certnamelist as $ip => $certname) {
?>

## '<?php echo $setdefaults; ?>' for '<?php echo $ip; ?>' config
server {
    listen <?php echo $ip; ?>:<?php echo $ports[1]; ?>;

    ssl on;
    ssl_certificate /home/kloxo/httpd/ssl/<?php echo $certname; ?>.crt;
    ssl_certificate_key /home/kloxo/httpd/ssl/<?php echo $certname; ?>.key;
    ssl_protocols SSLv3 TLSv1 TLSv1.1 TLSv1.2;
    ssl_ciphers HIGH:!aNULL:!MD5;
}

<?php
    }
*/
?>

## No needed declare here because certfile directly write to defaults and domains configs

<?php
} elseif ($setdefaults === 'init') {
?>

## No needed declare here because certfile directly write to defaults and domains configs

<?php
} else {
    foreach ($certnamelist as $ip => $certname) {
        $count = 0;

        foreach ($ports as &$port) {
?>

## '<?php echo $setdefaults; ?>' config
server {
<?php

            if ($setdefaults === 'default') {
                $asdefault = ' default';
            } else {
                $asdefault = '';
            }
?>
    listen <?php echo $ip; ?>:<?php echo $port; ?><?php echo $asdefault; ?>;
<?php
            if ($count !== 0) {
?>

    ssl on;
    ssl_certificate /home/kloxo/httpd/ssl/<?php echo $certname; ?>.crt;
    ssl_certificate_key /home/kloxo/httpd/ssl/<?php echo $certname; ?>.key;
    ssl_protocols SSLv3 TLSv1 TLSv1.1 TLSv1.2;
    ssl_ciphers HIGH:!aNULL:!MD5;
<?php
            }


            if ($setdefaults === 'default') {
?>

    server_name _;

    set $domain '';

    index <?php echo $indexorder; ?>;
<?php
            } else {
?>

    server_name <?php echo $setdefaults; ?>.*;

    index <?php echo $indexorder; ?>;

    set $rootdir '<?php echo $rootpath; ?>';

    root $rootdir;

    set $domain '';

    set $user 'apache';
<?php
            }

            if ($reverseproxy) {
?>

    include '<?php echo $globalspath; ?>/<?php echo $proxyconf; ?>';
<?php
            } else {
?>

    include '<?php echo $globalspath; ?>/<?php echo $perlconf; ?>';

    set $fpmport '<?php echo $fpmportapache; ?>';

    include '<?php echo $globalspath; ?>/<?php echo $phpfpmconf; ?>';
<?php

            }
            $count++;
?>
}

<?php
        }
    }
}
?>

### end content - please not remove this line
