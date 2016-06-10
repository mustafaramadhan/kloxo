		<section name="pool">
			<value name="name">default</value>
			<!-- <value name="listen_address">127.0.0.1:50000</value> -->
			<value name="listen_address">/opt/configs/php-fpm/sock/php52m-apache.sock</value>
			<value name="listen_options">
				<value name="backlog">65536</value>
				<value name="owner">apache</value>
				<value name="group">apache</value>
				<value name="mode">0666</value>
			</value>
			<value name="user">apache</value>
			<value name="group">apache</value>
			<value name="pm">
				<value name="style">static</value>
				<!-- <value name="style">apache_like</value> -->
				<value name="max_children">3</value>
				<value name="apache_like">
					<value name="StartServers">1</value>
					<value name="MinSpareServers">1</value>
					<value name="MaxSpareServers">2</value>
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
				<value name="display_errors">off</value>
				<value name="error_log">/var/log/php-fpm/error.log</value>
				<value name="log_errors">on</value>
				<value name="memory_limit">128M</value>
			</value>
		</section>

