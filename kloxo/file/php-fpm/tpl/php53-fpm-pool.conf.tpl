<?php
	if (!file_exists("/opt/configs/php-fpm/sock")) {
		mkdir("/opt/configs/php-fpm/sock");
	}

	if (!file_exists("/var/run/php-fpm")) {
		mkdir("/var/run/php-fpm");
	}

	$userinfo = posix_getpwnam($user);

	if ($user === 'apache') {
		// MR -- for future purpose, apache user have uid 50000
		$fpmport = 50000;
		$openbasedir = "/home/:/tmp/:/usr/share/pear/:/var/lib/php/session/";
	} else {
		$userinfo = posix_getpwnam($user);
		$fpmport = (50000 + $userinfo['uid']);
		$openbasedir = "/home/$user/:/tmp/:/usr/share/pear/:/var/lib/php/session/:".
			"/home/kloxo/httpd/script/:/home/kloxo/httpd/disable/:{$extrabasedir}";
	}

	if ($user === 'apache') {
		$pool = 'default';
	} else {
		$pool = $user;
	}

	if ($maxchildren) {
		$startservers = (($sts = (int)($maxchildren / 3 * 2)) < 2) ? 2 : $sts;
		$minspareservers = (($mis = (int)($maxchildren / 3)) < 2) ? 2 : $mis;
		$maxspareservers = (($mas = (int)($maxchildren / 3 * 2)) < 2) ? 2 : $mas;
		$maxchildren = (($mac = (int)($maxchildren)) < 2) ? 2 : $mac;
	} else {
		$startservers = '4';
		$minspareservers = '2';
		$maxspareservers = '4';
		$maxchildren = '6';
	}

	if (!$phpselected) {
		$phpcli = 'php';
		$phpselected = 'php';
	} else {
		if ($phpselected === 'php') {
			$phpcli = "php";
		} else {
			$phpcli = "{$phpselected}-cli";
		}
	}

//	exec("php -r 'echo phpversion();'", $out, $ret);
	exec("{$phpcli} -v|grep 'PHP'|grep '(built:'|awk '{print $2}'", $out, $ret);

	if ($ret) {
		$phpver = '5.4.0';
	} else {
		$phpver = $out[0];
	}

	if (version_compare($phpver, "5.4.0", ">=")) {
		$php54disable = ';';
	} else {
		$php54disable = '';
	}

	if (!$max_input_vars_flag) {
		$max_input_vars_flag = '3000';
	}

	if (!$date_timezone_flag) {
		$date_timezone_flag = 'Europe/London';
	}

	if ($user === 'apache') {
		$chroot_dir = "/home/kloxo/httpd";
		$enable_chroot = ";";
	} else {
		$chroot_dir = "/home/{$user}";
		$enable_chroot = "";
	}

	$openbasedir = str_replace("/var/lib/php/session/", "{$session_save_path_flag}/", $openbasedir);
?>
[<?php echo $pool; ?>]
;catch_workers_output = yes
;listen = 127.0.0.1:<?php echo $fpmport; ?>

listen = /opt/configs/php-fpm/sock/<?php echo $phpselected; ?>-<?php echo $user; ?>.sock
listen.backlog = 65536
listen.allowed_clients = 127.0.0.1
listen.owner = <?php echo $user; ?>

listen.group = <?php echo $user; ?>

listen.mode = 0666
user = <?php echo $user; ?>

group = <?php echo $user; ?>

;pm = dynamic
pm = ondemand
pm.max_children = <?php echo $maxchildren; ?>

pm.start_servers = <?php echo $startservers; ?>

pm.min_spare_servers = <?php echo $minspareservers; ?>

pm.max_spare_servers = <?php echo $maxspareservers; ?>

pm.max_requests = 1000

pm.process_idle_timeout = 20s

;pm.status_path = /status
;ping.path = /ping
;ping.response = pong
request_terminate_timeout = 120s
request_slowlog_timeout = 30s
slowlog = /var/log/php-fpm/slow.log
rlimit_files = 1024
rlimit_core = 0
;<?php echo $enable_chroot; ?>chroot = <?php echo $chroot_dir; ?>

;chdir = /
catch_workers_output = yes
security.limit_extensions = .php .php3 .php4 .php5

env[HOSTNAME] = $HOSTNAME
env[PATH] = /bin:/usr/bin:/sbin:/usr/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin
env[TMP] = /tmp
env[TMPDIR] = /tmp
env[TEMP] = /tmp
env[OSTYPE] = $OSTYPE
env[MACHTYPE] = $MACHTYPE
env[MALLOC_CHECK_] = 2

php_flag[zlib.output_compression] = <?php echo $output_compression_flag; ?>

php_admin_value[disable_functions] = <?php echo $disable_functions; ?>

php_flag[display_errors] = <?php echo $display_error_flag; ?>

php_flag[file_uploads] = <?php echo $file_uploads_flag; ?>

php_admin_value[upload_max_filesize] = <?php echo $upload_max_filesize; ?>

php_flag[log_errors] = <?php echo $log_errors_flag; ?>

php_flag[output_buffering] = <?php echo $output_buffering_flag; ?>

php_flag[register_argc_argv] = <?php echo $register_argc_argv_flag; ?>

php_flag[mysql.allow_persistent] = <?php echo $mysql_allow_persistent_flag; ?>

php_admin_value[max_execution_time] = <?php echo $max_execution_time_flag; ?>

php_admin_value[max_input_time] = <?php echo $max_input_time_flag; ?>

php_admin_value[memory_limit] = <?php echo $memory_limit_flag; ?>

php_admin_value[post_max_size] = <?php echo $post_max_size_flag; ?>

php_flag[allow_url_fopen] = <?php echo $allow_url_fopen_flag; ?>

php_flag[allow_url_include] = <?php echo $allow_url_include_flag; ?>

php_admin_value[session.save_path] = <?php echo $session_save_path_flag; ?>

php_flag[cgi.force_redirect] = <?php echo $cgi_force_redirect_flag; ?>

php_flag[enable_dl] = <?php echo $enable_dl_flag; ?>

php_admin_value[open_basedir] = <?php echo $openbasedir; ?>

php_admin_value[max_input_vars] = <?php echo $max_input_vars_flag; ?>

php_admin_value[date.timezone] = "<?php echo $date_timezone_flag; ?>"
