<?php
$userinfo = posix_getpwnam($user);

if ($userinfo) {
    $fpmport = (50000 + $userinfo['uid']);
} else {
    return false;
}
?>
#!/bin/sh
### Username: <?php echo $user; ?>

export MUID=<?php echo $userid; ?>

export GID=<?php echo $userid; ?>

export PHPRC=/home/httpd/<?php echo $domainname; ?>

export TARGET=/usr/bin/perl
export NON_RESIDENT=1
exec lxsuexec $*