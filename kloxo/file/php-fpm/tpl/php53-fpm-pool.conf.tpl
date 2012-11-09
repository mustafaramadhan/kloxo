<?php
    $userinfo = posix_getpwnam($user);

    if ($userinfo) {
        $fpmport = (50000 + $userinfo['uid']);
    } else {
        return false;
    }

    if ($user == 'apache') {
        $pool = 'default';
    } else {
        $pool = $user;
    }

    $maxchildren = '5';

?>
[<?php echo $pool; ?>]
listen = 127.0.0.1:<?php echo $fpmport; ?>

listen.backlog = -1
listen.allowed_clients = 127.0.0.1
user = <?php echo $user; ?>

group = <?php echo $user; ?>

pm = dynamic
pm.max_children = <?php echo $maxchildren; ?>

pm.start_servers = 1
pm.min_spare_servers = 1
pm.max_spare_servers = 2
pm.max_requests = 500
;pm.status_path = /status
;ping.path = /ping
;ping.response = pong
request_terminate_timeout = 0
request_slowlog_timeout = 0
slowlog = /var/log/php-fpm/slow.log
rlimit_files = 1024
rlimit_core = 0
;chroot = 
;chdir = /var/www
catch_workers_output = yes
security.limit_extensions = .php .php3 .php4 .php5

env[HOSTNAME] = $HOSTNAME
env[PATH] = /usr/local/bin:/usr/bin:/bin
env[TMP] = /tmp
env[TMPDIR] = /tmp
env[TEMP] = /tmp

;php_admin_value[sendmail_path] = /usr/sbin/sendmail -t -i -f webmaster@domain.com
;php_flag[display_errors] = off
php_admin_value[error_log] = /var/log/php-fpm/error.log
php_admin_value[session.save_path] = /var/lib/php/session
php_admin_flag[log_errors] = on
php_admin_value[memory_limit] = 64M
