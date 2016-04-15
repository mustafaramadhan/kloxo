<?php
	$req = '';

	if ($action === 'test') || ($action === 'staging')) {
		$req .= "--staging ";
	}

	$req .= "--rsa-key-size {$key_bits} ";

	if ($emailAddress === 'N/A') {
		$req .= '';
	} else {
		$req .= "--email {$emailAddress} ";
	}

	$san = str_replace(" ", ",", $subjectAltName);

	$req .= "--domains {$san} ";
?>
#!/bin/sh

letsencrypt-auto certonly --agree-tos --text --renew-by-default --hsts \
	--duplicate --webroot --webroot-path /var/run/letsencrypt \
	<?php echo $req; ?> || exit 1
