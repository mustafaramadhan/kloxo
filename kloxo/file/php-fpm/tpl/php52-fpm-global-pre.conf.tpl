<?php

// because not work for parse for inline php
echo "<" . "?xml version=\"1.0\" ?" . ">" . "\n";

?>

<configuration>

	<section name="global_options">
		<value name="pid_file">/var/run/php-fpm/php52m-fpm.pid</value>
		<value name="error_log">/var/log/php-fpm/error.log</value>
		<value name="log_level">notice</value>
		<value name="emergency_restart_threshold">10</value>
		<value name="emergency_restart_interval">1m</value>
		<value name="process_control_timeout">10s</value>
		<value name="daemonize">yes</value>
	</section>

	<workers>

