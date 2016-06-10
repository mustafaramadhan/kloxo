<?php
	if (!$phpselected) {
		$phpselected = 'php';
		$phpinc="/etc/php-fpm.d";
	} else {
		$phpinc="/opt/configs/php-fpm/conf/{$phpselected}/php-fpm.d";
	}
?>

[global]
pid=/var/run/php-fpm/<?php echo $phpselected;?>-fpm.pid
error_log=/var/log/php-fpm/error.log
log_level=error

;emergency_restart_threshold=0
;emergency_restart_interval=0
;process_control_timeout=0

emergency_restart_threshold=10
emergency_restart_interval=1m
process_control_timeout=10s

events.mechanism=epoll

daemonize=yes

include=<?php echo $phpinc; ?>/*.conf

