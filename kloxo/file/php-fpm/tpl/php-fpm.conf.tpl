<?php
	if (!file_exists("/opt/configs/php-fpm/sock")) {
		mkdir("/opt/configs/php-fpm/sock");
	}

	$startservers = (($sts = (int)($maxchildren / 3 * 2)) < 2) ? 2 : $sts;
	$minspareservers = (($mis = (int)($maxchildren / 3)) < 2) ? 2 : $mis;
	$maxspareservers = (($mas = (int)($maxchildren / 3 * 2)) < 2) ? 2 : $mas;
	$maxchildren = (($mac = (int)($maxchildren)) < 2) ? 2 : $mac;

	if (!$maxchildren) {
		$maxchildren = '6';
	}

	$userlist[] = 'apache';

	if (!$phpselected) {
		$phpcli = 'php';
		$phpselected = 'php';
	} else {
		$phpcli = "{$phpcli}-cli";
	}

	$openbasedir = str_replace("/var/lib/php/session/", "{$session_save_path_flag}/", $openbasedir);

	// because not work for parse for inline php
	echo "<" . "?xml version=\"1.0\" ?" . ">" . "\n";
?>
<configuration>
	<section name="global_options">
		<value name="pid_file">/var/run/php-fpm.pid</value>
		<value name="error_log">/var/log/php-fpm/error.log</value>
		<value name="log_level">notice</value>
		<value name="emergency_restart_threshold">10</value>
		<value name="emergency_restart_interval">1m</value>
		<value name="process_control_timeout">10s</value>
		<value name="daemonize">yes</value>
	</section>

	<workers>
<?php
	foreach ($userlist as &$user) {
		if ($user === 'apache') {
			// MR -- for future purpose, apache user have uid 50000
			$fpmport = 50000;
			$pool = 'default';
		} else {
			$userinfo = posix_getpwnam($user);
			$fpmport = (50000 + $userinfo['uid']);
			$pool = $user;
		}
?>
		<section name="pool">
			<value name="name"><?php echo $pool; ?></value>
			<!-- <value name="listen_address">127.0.0.1:<?php echo $fpmport; ?></value> -->
			<value name="listen_address">/opt/configs/php-fpm/sock/<?php echo $phpselected; ?>-<?php echo $user; ?>.sock</value>
			<value name="listen_options">
				<value name="backlog">65536</value>
				<value name="owner"><?php echo $user; ?></value>
				<value name="group"><?php echo $user; ?></value>
				<value name="mode">0666</value>
			</value>
			<value name="user"><?php echo $user; ?></value>
			<value name="group"><?php echo $user; ?></value>
			<value name="pm">
				<!-- <value name="style">static</value> -->
				<value name="style">apache_like</value>
				<value name="max_children"><?php echo $maxchildren; ?></value>
				<value name="apache_like">
					<value name="StartServers"><?php echo $startservers; ?></value>
					<value name="MinSpareServers"><?php echo $minspareservers; ?></value>
					<value name="MaxSpareServers"><?php echo $maxspareservers; ?></value>
				</value>
			</value>
			<value name="request_terminate_timeout">120s</value>
			<value name="request_slowlog_timeout">30s</value>
			<value name="slowlog">/var/log/php-fpm/slow.log</value>
			<value name="rlimit_files">1024</value>
			<value name="rlimit_core">0</value>
			<value name="chroot"></value>
			<value name="chdir"></value>
			<value name="catch_workers_output">yes</value>
			<value name="max_requests">1000</value>
			<value name="allowed_clients">127.0.0.1</value>
			<value name="environment">
				<value name="HOSTNAME">$HOSTNAME</value>
				<value name="PATH">/bin:/usr/bin:/sbin:/usr/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin</value>
				<value name="TMP">/tmp</value>
				<value name="TMPDIR">/tmp</value>
				<value name="TEMP">/tmp</value>
				<value name="OSTYPE">$OSTYPE</value>
				<value name="MACHTYPE">$MACHTYPE</value>
				<value name="MALLOC_CHECK_">2</value>
			</value>
			<value name="php_defines">
				<value name="zlib.output_compression"><?php echo $output_compression_flag; ?></value>
				<value name="disable_functions"><?php echo $disable_functions; ?></value>
				<value name="display_errors"><?php echo $display_error_flag; ?></value>
				<value name="file_uploads"><?php echo $file_uploads_flag; ?></value>
				<value name="upload_max_filesize"><?php echo $upload_max_filesize; ?></value>
				<value name="log_errors"><?php echo $log_errors_flag; ?></value>
				<value name="output_buffering"><?php echo $output_buffering_flag; ?></value>
				<value name="register_argc_argv"><?php echo $register_argc_argv_flag; ?></value>
				<value name="mysql.allow_persistent"><?php echo $mysql_allow_persistent_flag; ?></value>
				<value name="max_execution_time"><?php echo $max_execution_time_flag; ?></value>
				<value name="max_input_time"><?php echo $max_input_time_flag; ?></value>
				<value name="memory_limit"><?php echo $memory_limit_flag; ?></value>
				<value name="post_max_size"><?php echo $post_max_size_flag; ?></value>
				<value name="allow_url_fopen"><?php echo $allow_url_fopen_flag; ?></value>
				<value name="allow_url_include"><?php echo $allow_url_include_flag; ?></value>
				<value name="session.save_path"><?php echo $session_save_path_flag; ?></value>
				<value name="cgi.force_redirect"><?php echo $cgi_force_redirect_flag; ?></value>
				<value name="enable_dl"><?php echo $enable_dl_flag; ?></value>
				<value name="open_basedir"><?php echo $openbasedir; ?></value>
				<value name="max_input_vars"><?php echo $max_input_vars_flag; ?></value>
				<value name="date.timezone"><?php echo $date_timezone_flag; ?></value>
			</value>
		</section>
<?php
	}
?>
	</workers>
</configuration>
