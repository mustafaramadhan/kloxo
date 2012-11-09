### begin content - please not remove this line

<?php

$ports = array('80', '443');

$statsapp = $stats['app'];
$statsprotect = ($stats['protect']) ? true : false;

$tmpdom = str_replace(".", "\.", $domainname);

$excludedomains = array(
    "cp",
    "disable",
    "default",
    "webmail"
);

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
    $webmaildocroot = "/home/kloxo/httpd/webmail/{$webmailapp}";
} else {
    $webmaildocroot = "/home/kloxo/httpd/webmail";
}

if ($indexorder) {
    $indexorder = implode(' ', $indexorder);
}

$indexorder = '"' . $indexorder . '"';
$indexorder = str_replace(' ', '", "', $indexorder);

if ($blockips) {
    $blockips = str_replace(' ', ', ', $blockips);
}

$userinfo = posix_getpwnam($user);

if ($userinfo) {
    $fpmport = (50000 + $userinfo['uid']);
} else {
    return false;
}

$userinfoapache = posix_getpwnam('apache');
$fpmportapache = (50000 + $userinfoapache['uid']);

if ($reverseproxy) {
    $lighttpdextratext = null;
}

$disablepath = "/home/kloxo/httpd/disable";

$globalspath = "/home/lighttpd/conf/globals";

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

if (file_exists("{$globalspath}/custom.suexec.conf")) {
    $suexecconf = 'custom.suexec.conf';
} else {
    $suexecconf = 'suexec.conf';
}

foreach ($certnamelist as $ip => $certname) {
    $count = 0;

    foreach ($ports as &$port) {
        if ($count === 0) {
            if ($ip !== '*') {
                $ipssl = "|" . $ip;
            } else {
                $ipssl = "";
            }

            if ($wwwredirect) {
?>

## web for '<?php echo $domainname; ?>'
$HTTP["host"] =~ "<?php echo $domainname; ?><?php echo $ipssl; ?>" {

    url.redirect = ( "^/(.*)" => "http://www.<?php echo $domainname; ?>/$1" )
}


## web for '<?php echo $domainname; ?>'
$HTTP["host"] =~ "<?php echo $serveralias; ?><?php echo $ipssl; ?>" {
<?php
            } else {
?>

## web for '<?php echo $domainname; ?>'
$HTTP["host"] =~ "<?php echo $serveralias; ?><?php echo $ipssl; ?>" {
<?php
            }
        } else {
            if ($ip !== '*') {
?>

## web for '<?php echo $domainname; ?>'
$SERVER["socket"] == "<?php echo $ip; ?>:<?php echo $port; ?>" {

    ssl.engine = "enable"

    ssl.pemfile = "/home/kloxo/httpd/ssl/<?php echo $certname; ?>.pem"
    ssl.ca-file = "/home/kloxo/httpd/ssl/<?php echo $certname; ?>.ca"
    ssl.use-sslv2 = "disable"
<?php
            }
        }

        if (($ip === '*') && ($count !== 0)) { continue; }
?>

    var.domain = "<?php echo $domainname; ?>"
<?php
        if ($disabled) {
?>

    var.rootdir = "<?php echo $disablepath; ?>/"

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

    var.user = "<?php echo $user; ?>"

    include "<?php echo $globalspath; ?>/<?php echo $genericconf; ?>"
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
                if ($rr[2] === 'both') {
?>

    url.redirect  += ( "^(<?php echo $rr[0]; ?>/|<?php echo $rr[0]; ?>$)" => "<?php echo $rr[1]; ?>" )
    #url.redirect  += ( "^(<?php echo $rr[0]; ?>/|<?php echo $rr[0]; ?>$)" => "<?php echo str_replace("http://", "https://", $rr[1]); ?>" )
<?php
                } else {
?>

    url.redirect  += ( "^(/<?php echo $rr[0]; ?>/|/<?php echo $rr[0]; ?>$)" => "<?php echo $rr[1]; ?>" )
<?php
                }
            }
        }

//        if (!$reverseproxy) {
            if ($statsapp === 'awstats') {
?>

    var.statstype = "awstats"

    include "<?php echo $globalspath; ?>/<?php echo $awstatsconf; ?>"
<?php
                if ($statsprotect) {
?>

    var.protectpath = "awstats"
    var.protectauthname = "Awstats"
    var.protectfile = "__stats"

    include "<?php echo $globalspath; ?>/<?php echo $dirprotectconf; ?>"
<?php
                }
            } elseif ($statsapp === 'webalizer') {
?>

    var.statstype = "stats"

    include "<?php echo $globalspath; ?>/<?php echo $webalizerconf; ?>"
<?php
                if ($statsprotect) {
?>

    var.protectpath = "stats"
    var.protectauthname = "stats"
    var.protectfile = "__stats"

    include "<?php echo $globalspath; ?>/<?php echo $dirprotectconf; ?>"
<?php
                }
            }
//        }

        if ($lighttpdextratext) {
?>

    # Extra Tags - begin
<?php echo $lighttpdextratext; ?>

    # Extra Tags - end
<?php
        }

        if (!$disablephp) {
            if ($reverseproxy) {
?>

    include "<?php echo $globalspath; ?>/<?php echo $proxyconf; ?>"
<?php
            } else {
                if ($phpcgitype === 'fastcgi') {
?>

    var.fpmport = "<?php echo $fpmport; ?>"

    include "<?php echo $globalspath; ?>/<?php echo $phpfpmconf; ?>"
<?php
                } elseif ($phpcgitype === 'suexec') {
?>

    include "<?php echo $globalspath; ?>/<?php echo $suexecconf; ?>"
<?php
                }
            }
        }

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

        if ($blockips) {
?>

    $HTTP["remoteip"] =~ "{<?php echo $blockips; ?>}" {
        url.access-deny = ( "" )
    }
<?php
        }
?>

}

<?php
        $count++;

    }
}

if ($disabled) {
?>

## webmail for '<?php echo $domainname; ?>'
$HTTP["host"] =~ "^webmail\.<?php echo str_replace(".", "\.", $domainname); ?>" {

    var.rootdir = "<?php echo $disablepath; ?>/"

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
<?php
    }
?>

}

<?php
} else {
    if ($webmailremote) {
?>

## webmail for '<?php echo $domainname; ?>'
    $HTTP["host"] =~ "^webmail\.<?php echo str_replace(".", "\.", $domainname); ?>" {

    url.redirect = ( "/" =>  "<?php echo $webmailremote; ?>/" )

}

<?php
    } elseif ($webmailapp) {
?>

## webmail for '<?php echo $domainname; ?>'
$HTTP["host"] =~ "^webmail\.<?php echo str_replace(".", "\.", $domainname); ?>" {

    var.rootdir = "<?php echo $webmaildocroot; ?>/"

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

if ($domainredirect) {
    foreach ($domainredirect as $domredir) {
        $redirdomainname = $domredir['redirdomain'];
        $redirpath = ($domredir['redirpath']) ? $domredir['redirpath'] : null;
        $webmailmap = ($domredir['mailflag'] === 'on') ? true : false;

        if ($redirpath) {
            $redirfullpath = str_replace('//', '/', $rootpath . '/' . $redirpath);
?>

## web for redirect '<?php echo $redirdomainname; ?>'
$HTTP["host"] =~ "^<?php echo str_replace(".", "\.", $redirdomainname); ?>" {

    var.rootdir = "<?php echo $redirfullpath; ?>/"

    server.document-root = var.rootdir

    index-file.names = ( <?php echo $indexorder; ?> )

    var.user = "<?php echo $user; ?>"
<?php
            if (!$disablephp) {
                if ($reverseproxy) {
?>

    include "<?php echo $globalspath; ?>/<?php echo $proxyconf; ?>"
<?php
                } else {
                    if ($phpcgitype === 'fastcgi') {
?>

    var.fpmport = "<?php echo $fpmport; ?>"

    include "<?php echo $globalspath; ?>/<?php echo $phpfpmconf; ?>"
<?php
                    } elseif ($phpcgitype === 'suexec') {
?>

    include "<?php echo $globalspath; ?>/<?php echo $suexecconf; ?>"
<?php
                    }
                }
            }
?>

        }

<?php
        } else {
?>

## web for redirect '<?php echo $redirdomainname; ?>'
$HTTP["host"] =~ "^<?php echo str_replace(".", "\.", $redirdomainname); ?>" {

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

    var.rootdir = "<?php echo $disablepath; ?>/"

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
<?php
            }
?>

        }

<?php
        } else {
            if ($webmailremote) {
?>

## webmail for parked '<?php echo $parkdomainname; ?>'
$HTTP["host"] =~ "^webmail\.<?php echo str_replace(".", "\.", $parkdomainname); ?>" {

    url.redirect = ( "/" =>  "<?php echo $webmailremote; ?>/" )

}

<?php

            } elseif ($webmailmap) {
                if ($webmailapp) {
?>

## webmail for parked '<?php echo $parkdomainname; ?>'
$HTTP["host"] =~ "^webmail\.<?php echo str_replace(".", "\.", $parkdomainname); ?>" {

    var.rootdir = "<?php echo $webmaildocroot; ?>/"

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
$HTTP["host"] =~ "^webmail\.<?php echo str_replace(".", "\.", $redirdomainname); ?>" {

    var.rootdir = "<?php echo $disablepath; ?>/"

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
<?php
            }
?>

        }

<?php
        } else {
            if ($webmailremote) {
?>

## webmail for redirect '<?php echo $redirdomainname; ?>'
$HTTP["host"] =~ "^webmail\.<?php echo str_replace(".", "\.", $redirdomainname); ?>" {

    url.redirect = ( "/" =>  "<?php echo $webmailremote; ?>/" )

}

<?php
            } elseif ($webmailmap) {
                if ($webmailapp) {
?>

## webmail for redirect '<?php echo $redirdomainname; ?>'
$HTTP["host"] =~ "^webmail\.<?php echo str_replace(".", "\.", $redirdomainname); ?>" {

    var.rootdir = "<?php echo $webmaildocroot; ?>/"

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
?>

### end content - please not remove this line
