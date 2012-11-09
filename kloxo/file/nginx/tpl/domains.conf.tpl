### begin content - please not remove this line

<?php

$ports[] = '80';
$ports[] = '443';

$statsapp = $stats['app'];
$statsprotect = ($stats['protect']) ? true : false;

$serveralias = "{$domainname} www.{$domainname}";

$excludedomains = array(
    "cp",
    "disable",
    "default",
    "webmail"
);

$excludealias = implode("|", $excludedomains);

if ($wildcards) {
    $serveralias .= "\n        *.{$domainname}";
}

if ($serveraliases) {
    foreach ($serveraliases as &$sa) {
        $serveralias .= "\n        {$sa}";
    }
}

if ($parkdomains) {
    foreach ($parkdomains as $pk) {
        $pa = $pk['parkdomain'];
        $serveralias .= "\n        {$pa} www.{$pa}";
    }
}

if ($webmailapp) {
    $webmaildocroot = "/home/kloxo/httpd/webmail/{$webmailapp}";
} else {
    $webmaildocroot = "/home/kloxo/httpd/webmail";
}

if ($indexorder) {
    $indexorder = implode(' ', $indexorder);
}

if ($blockips) {
//    $blockips = str_replace(' ', ', ', $blockips);
}

$userinfo = posix_getpwnam($user);

if ($userinfo) {
    $fpmport = (50000 + $userinfo['uid']);
} else {
    return false;
}

$userinfoapache = posix_getpwnam('apache');
$fpmportapache = (50000 + $userinfoapache['uid']);

$disablepath = "/home/kloxo/httpd/disable";

$globalspath = "/home/nginx/conf/globals";

if (file_exists("{$globalspath}/custom.proxy.conf")) {
    $proxyconf = 'custom.proxy.conf';
} else {
    $wppath = "{$rootpath}/_sitetype_/wordpress";

    if (file_exists($wppath)) {
        $proxyconf = 'wp-proxy.conf';
    } else {
        $proxyconf = 'proxy.conf';
    }
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

if (file_exists("{$globalspath}/custom.generic.conf")) {
    $genericconf = 'custom.generic.conf';
} else {
    $genericconf = 'generic.conf';
}

if (file_exists("{$globalspath}/custom.awstats.conf")) {
    $awstatsconf = 'custom.awstats.conf';
} else {
    $awstatsconf = 'awstats.conf';
}

if (file_exists("{$globalspath}/custom.dirprotect.conf")) {
    $dirprotectconf = 'custom.dirprotect.conf';
} else {
    $dirprotectconf = 'dirprotect.conf';
}

if (file_exists("{$globalspath}/custom.webalizer.conf")) {
    $webalizerconf = 'custom.webalizer.conf';
} else {
    $webalizerconf = 'webalizer.conf';
}

$count = 0;

foreach ($certnamelist as $ip => $certname) {
    $count = 0;

    foreach ($ports as &$port) {
?>

## web for '<?php echo $domainname; ?>'
server {
    listen <?php echo $ip; ?>:<?php echo $port; ?>;
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
?>

    server_name <?php echo $serveralias; ?>;

    index <?php echo $indexorder; ?>;

    set $domain '<?php echo $domainname; ?>';
<?php
        if ($wwwredirect) {
?>

    if ($host != 'www.<?php echo $domainname; ?>') {
        rewrite ^/(.*) 'http://www.<?php echo $domainname; ?>/$1' permanent;
    }
<?php
        }

        if ($disabled) {
?>

    set $rootdir '<?php echo $disablepath; ?>';
<?php
        } else {
            if ($wildcards) {
?>

    set $rootdir '<?php echo $rootpath; ?>';
<?php
                foreach ($excludedomains as &$ed) {
?>

    if ($host ~* ^(<?php echo $ed; ?>.<?php echo $domainname; ?>)$) {
<?php
                    if ($ed !== 'webmail') {
?>
        set $rootdir '/home/kloxo/httpd/<?php echo $ed; ?>';
<?php
                    } else {
                        if($webmailremote) {
?>
        rewrite ^/(.*) 'http://<?php echo $webmailremote; ?>/$1' permanent;
<?php
                        } else {
?>
        set $rootdir '<?php echo $webmaildocroot; ?>';
<?php
                        }
                    }
?>
    }
<?php
                }
            } else {
?>

    set $rootdir '<?php echo $rootpath; ?>';
<?php
            }
        }
?>

    root $rootdir;
<?php
        if ($redirectionlocal) {
            foreach ($redirectionlocal as $rl) {
?>

    location ~ ^<?php echo $rl[0]; ?>/(.*)$ {
            alias $rootdir<?php echo str_replace("//", "/", $rl[1]); ?>/$1;
    }
<?php
            }
        }

        if ($redirectionremote) {
            foreach ($redirectionremote as $rr) {
                if ($rr[2] === 'both') {
?>

    rewrite ^<?php echo $rr[0]; ?>/(.*) <?php echo $rr[1]; ?>/$1 permanent;
    #rewrite ^<?php echo $rr[0]; ?>/(.*) <?php echo str_replace("http://", "https://", $rr[1]); ?>/$1 permanent;
<?php
                } else {
?>

                rewrite ^<?php echo $rr[0]; ?>/(.*) <?php echo $rr[1]; ?>/$1 permanent;
<?php
                }
            }
        }
?>

    set $user '<?php echo $user; ?>';

    include '<?php echo $globalspath; ?>/<?php echo $genericconf; ?>';
<?php
//        if (!$reverseproxy) {
?>

    access_log /home/httpd/<?php echo $domainname; ?>/stats/<?php echo $domainname; ?>-custom_log main;
    error_log  /home/httpd/<?php echo $domainname; ?>/stats/<?php echo $domainname; ?>-error_log;
<?php
            if ($statsapp === 'awstats') {
?>

    set $statstype 'awstats';

    include '<?php echo $globalspath; ?>/<?php echo $awstatsconf; ?>';
<?php
                if ($statsprotect) {
?>

    set $protectpath     'awstats';
    set $protectauthname 'Awstats';
    set $protectfile     '__stats';

                include '<?php echo $globalspath; ?>/<?php echo $dirprotectconf; ?>';
<?php
                }
            } elseif ($statsapp === 'webalizer') {
?>

    set $statstype 'stats';

    include '<?php echo $globalspath; ?>/<?php echo $webalizerconf; ?>';
<?php
                if ($statsprotect) {
?>

    set $protectpath     'stats';
    set $protectauthname 'stats';
    set $protectfile     '__stats';

    include '<?php echo $globalspath; ?>/<?php echo $dirprotectconf; ?>';
<?php
                }
            }
//        }

        if ($nginxextratext) {
?>

    # Extra Tags - begin
<?php echo $nginxextratext; ?>

    # Extra Tags - end
<?php
        }

        if (!$disablephp) {
            if ($reverseproxy) {
?>

    include '<?php echo $globalspath; ?>/<?php echo $proxyconf; ?>';
<?php
            } else {
                if ($wildcards) {
?>

    #if ($host !~* ^((<?php echo $excludealias; ?>).<?php echo $domainname; ?>)$) {
        set $fpmport '<?php echo $fpmport; ?>';
    #}

    if ($host ~* ^((<?php echo $excludealias; ?>).<?php echo $domainname; ?>)$) {
        set $fpmport '<?php echo $fpmportapache; ?>';
    }
<?php
                } else {
?>

    set $fpmport '<?php echo $fpmport; ?>';
<?php
                }
?>

    include '<?php echo $globalspath; ?>/<?php echo $phpfpmconf; ?>';

    include '<?php echo $globalspath; ?>/<?php echo $perlconf; ?>';
<?php
            }
        }

        if ($dirprotect) {
            foreach ($dirprotect as $k) {
                $protectpath = $k['path'];
                $protectauthname = $k['authname'];
                $protectfile = str_replace('/', '_', $protectpath) . '_';
?>

    location /<?php echo $protectpath; ?>/(.*)$ {
        satisfy any;
        auth_basic '<?php echo $protectauthname; ?>';
        auth_basic_user_file '/home/httpd/<?php echo $domainname; ?>/__dirprotect/<?php echo $protectfile; ?>';
    }
<?php
            }
        }

        if ($blockips) {
?>

    location ^~ /(.*) {
<?php
            foreach ($blockips as &$bip) {
?>
        deny   <?php echo $bip; ?>;
<?php
            }
?>
        allow  all;
    }
<?php
        }
?>
}

<?php
        if (!$wildcards) {
            if ($disabled) {
?>

## webmail for '<?php echo $domainname; ?>'
server {
    listen <?php echo $ip; ?>:<?php echo $port; ?>;
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
?>

    server_name webmail.<?php echo $domainname; ?>;

    index <?php echo $indexorder; ?>;

    set $rootdir '<?php echo $disablepath; ?>';

    root $rootdir;
<?php
                if ($reverseproxy) {
?>

                include '<?php echo $globalspath; ?>/<?php echo $proxyconf; ?>';
<?php
                } else {
?>

    set $fpmport '<?php echo $fpmportapache; ?>';

    include '<?php echo $globalspath; ?>/<?php echo $phpfpmconf; ?>';

    include '<?php echo $globalspath; ?>/<?php echo $perlconf; ?>';
<?php
                }
?>
}

<?php
            } else {
                if ($webmailremote) {
?>

## webmail for '<?php echo $domainname; ?>'
server {
    listen <?php echo $ip; ?>:<?php echo $port; ?>;
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
?>

    server_name webmail.<?php echo $domainname; ?>;

    if ($host != '<?php echo $webmailremote; ?>') {
        rewrite ^/(.*) 'http://<?php echo $webmailremote; ?>/$1' permanent;
    }
}
<?php
                } elseif ($webmailapp) {
?>

## webmail for '<?php echo $domainname; ?>'
server {
    listen <?php echo $ip; ?>:<?php echo $port; ?>;
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
?>

    server_name webmail.<?php echo $domainname; ?>;

    index <?php echo $indexorder; ?>;

    set $rootdir '<?php echo $webmaildocroot; ?>';

    root $rootdir;
<?php
                    if ($reverseproxy) {
?>

    include '<?php echo $globalspath; ?>/<?php echo $proxyconf; ?>';
<?php
                    } else {
?>

    set $fpmport '<?php echo $fpmportapache; ?>';

    include '<?php echo $globalspath; ?>/<?php echo $phpfpmconf; ?>';

    include '<?php echo $globalspath; ?>/<?php echo $perlconf; ?>';
<?php
                    }
?>
}

<?php
                } else {
?>

## webmail for '<?php echo $domainname; ?>' handled by ../webmails/webmail.conf

<?php
                }
            }
        }

        if ($domainredirect) {
            foreach ($domainredirect as $domredir) {
                $redirdomainname = $domredir['redirdomain'];
                $redirpath = ($domredir['redirpath']) ? $domredir['redirpath'] : null;
                $webmailmap = ($domredir['mailflag'] === 'on') ? true : false;

                if ($redirpath) {
                    $redirfullpath = str_replace('//', '/', $rootpath . '/' . $redirpath);
?>

## web for redirect '<?php echo $redirdomainname; ?>'
server {
    listen <?php echo $ip; ?>:<?php echo $port; ?>;
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
?>

    server_name '<?php echo $redirdomainname; ?>';

    index <?php echo $indexorder; ?>;

    set $rootdir '<?php echo $redirfullpath; ?>';

    root $rootdir;
<?php
                    if ($reverseproxy) {
?>

    include '<?php echo $globalspath; ?>/<?php echo $proxyconf; ?>';
<?php
                    } else {
?>

    set $fpmport <?php echo $fpmport; ?>';

    include '<?php echo $globalspath; ?>/<?php echo $phpfpmconf; ?>';

    include '<?php echo $globalspath; ?>/<?php echo $perlconf; ?>';
<?php
                    }
?>
}
<?php
                } else {
?>

## web for redirect '<?php echo $redirdomainname; ?>'
server {
    listen <?php echo $ip; ?>:<?php echo $port; ?>;
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
?>

    server_name '<?php echo $redirdomainname; ?>';

    if ($host != '<?php echo $domainname; ?>') {
        rewrite ^/(.*) 'http://<?php echo $domainname; ?>/$1';
    }
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
server {
    listen <?php echo $ip; ?>:<?php echo $port; ?>;
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
?>

    server_name 'webmail.<?php echo $parkdomainname; ?>';

    index <?php echo $indexorder; ?>;

    set $rootdir '<?php echo $disablepath; ?>';

    root $rootdir;
<?php
                    if ($reverseproxy) {
?>

    include '<?php echo $globalspath; ?>/<?php echo $proxyconf; ?>';
<?php
                    } else {
?>

    set $fpmport '<?php echo $fpmportapache; ?>';

    include '<?php echo $globalspath; ?>/<?php echo $phpfpmconf; ?>';

    include '<?php echo $globalspath; ?>/<?php echo $perlconf; ?>';
<?php
                    }
?>
}

<?php
                } else {
                    if ($webmailremote) {
?>

## webmail for parked '<?php echo $parkdomainname; ?>'
server {
    listen <?php echo $ip; ?>:<?php echo $port; ?>;
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
?>

    server_name 'webmail.<?php echo $parkdomainname; ?>';

    if ($host != '<?php echo $webmailremote; ?>') {
        rewrite ^/(.*) 'http://<?php echo $webmailremote; ?>/$1';
    }
}

<?php

                    } elseif ($webmailmap) {
                        if ($webmailapp) {
?>

## webmail for parked '<?php echo $parkdomainname; ?>'
server {
     listen <?php echo $ip; ?>:<?php echo $port; ?>;
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
?>

    server_name 'webmail.<?php echo $parkdomainname; ?>';

    index <?php echo $indexorder; ?>;

    set $rootdir '<?php echo $webmaildocroot; ?>';

    root $rootdir;
<?php
                            if ($reverseproxy) {
?>

    include '<?php echo $globalspath; ?>/<?php echo $proxyconf; ?>';
<?php
                            } else {
?>

    set $fpmport '<?php echo $fpmportapache; ?>';

    include '<?php echo $globalspath; ?>/<?php echo $phpfpmconf; ?>';

    include '<?php echo $globalspath; ?>/<?php echo $perlconf; ?>';
<?php
                            }
?>
}

<?php
                        } else {
?>

## webmail for parked '<?php echo $parkdomainname; ?>' handled by ../webmails/webmail.conf

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
server {
    listen <?php echo $ip; ?>:<?php echo $port; ?>;
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
?>

    server_name 'webmail.<?php echo $redirdomainname; ?>';

    index <?php echo $indexorder; ?>;

    set $rootdir '<?php echo $disablepath; ?>';

    root $rootdir;
<?php
                    if ($reverseproxy) {
?>

    include '<?php echo $globalspath; ?>/<?php echo $proxyconf; ?>';
<?php
                    } else {
?>

    set $fpmport '<?php echo $fpmportapache; ?>';

    include '<?php echo $globalspath; ?>/<?php echo $phpfpmconf; ?>';

    include '<?php echo $globalspath; ?>/<?php echo $perlconf; ?>';
<?php
                    }
?>
}

<?php
                } else {
                    if ($webmailremote) {
?>

## webmail for redirect '<?php echo $redirdomainname; ?>'
server {
    listen <?php echo $ip; ?>:<?php echo $port; ?>;
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
?>

    server_name 'webmail.<?php echo $redirdomainname; ?>';

    if ($host != '<?php echo $webmailremote; ?>') {
        rewrite ^/(.*) 'http://<?php echo $webmailremote; ?>/$1';
    }
}

<?php
                    } elseif ($webmailmap) {
                        if ($webmailapp) {
?>

## webmail for redirect '<?php echo $redirdomainname; ?>'
server {
    listen <?php echo $ip; ?>:<?php echo $port; ?>;
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
?>

    server_name 'webmail.<?php echo $redirdomainname; ?>';

    index <?php echo $indexorder; ?>;

    set $rootdir '<?php echo $webmaildocroot; ?>';

    root $rootdir;
<?php
                            if ($reverseproxy) {
?>

    include '<?php echo $globalspath; ?>/<?php echo $proxyconf; ?>';
<?php
                            } else {
?>

    set $fpmport '<?php echo $fpmportapache; ?>';

    include '<?php echo $globalspath; ?>/<?php echo $phpfpmconf; ?>';

    include '<?php echo $globalspath; ?>/<?php echo $perlconf; ?>';
<?php
                            }
?>
}

<?php
                        } else {
?>

## webmail for redirect '<?php echo $redirdomainname; ?>' handled by ../webmails/webmail.conf

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

        $count++;
    }
}
?>

### end content - please not remove this line
