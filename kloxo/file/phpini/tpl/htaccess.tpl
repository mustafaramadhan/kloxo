<?php
	// can use $user and $domain vars

	exec("php -r 'echo phpversion();'", $out, $ret);

	$phpver = $out[0];

	if (version_compare($phpver, "5.4.0", ">=")) {
		$php54mark = '';
		$php54disable = '#';
	} else {
		$php54mark = '#';
		$php54disable = '';
	}

	if (version_compare($phpver, "5.3.0", ">=")) {
		$php53mark = '';
	} else {
		$php53mark = '#';
	}

	if ($sendmail_from) {
		$sendmailmark = '';
	} else {
		$sendmailmark = '#';
		$sendmail_from = '';
	}
?>
### MR -- Remove # in front of 'Addhandler' if running php 5.2 code on php 5.3+ system
#AddHandler x-httpd-php52 .php

<Ifmodule mod_php5.c>
	php_value upload_max_filesize <?php echo $upload_max_filesize; ?>

	php_value max_execution_time <?php echo $max_execution_time_flag; ?>

	php_value max_input_time <?php echo $max_input_time_flag; ?>

	php_value memory_limit <?php echo $memory_limit_flag; ?>

	php_value post_max_size <?php echo $post_max_size_flag; ?>

	php_flag register_globals <?php echo $register_global_flag; ?>

	php_flag display_errors <?php echo $display_error_flag; ?>

	php_flag file_uploads <?php echo $file_uploads_flag; ?>

	php_flag log_errors <?php echo $log_errors_flag; ?>

	php_flag output_buffering <?php echo $output_buffering_flag; ?>

	php_flag register_argc_argv <?php echo $register_argc_argv_flag; ?>

	php_flag magic_quotes_gpc <?php echo $magic_quotes_gpc_flag; ?>

	php_flag magic_quotes_runtime <?php echo $magic_quotes_runtime_flag; ?>

	php_flag magic_quotes_sybase <?php echo $magic_quotes_sybase_flag; ?>

	php_flag mysql.allow_persistent <?php echo $mysql_allow_persistent_flag; ?>

	<?php echo $php54disable; ?>php_flag register_long_arrays <?php echo $register_long_arrays_flag; ?>

	php_flag allow_url_fopen <?php echo $allow_url_fopen_flag; ?>

	php_flag cgi.force_redirect <?php echo $cgi_force_redirect_flag; ?>

	php_flag enable_dl <?php echo $enable_dl_flag; ?>

</Ifmodule>
