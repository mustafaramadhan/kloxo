<?php
	$initpath = "../init";
	$loginpath = "../httpdocs/login";

	$state = 0;

	$host = $_SERVER["HTTP_HOST"];
	$splitter = explode(":", $host);
	$domain = $splitter[0];
	$port = ($splitter[1]) ? $splitter[1] : '80';
	$requesturi = $_SERVER["REQUEST_URI"];
	$scheme = $_SERVER["HTTP_SCHEME"];

	$domain_pure = preg_replace('/(cp\.|webmail\.|www\.|mail\.)(.*)/i', "$2", $domain);

	if ($domain_pure !== $domain) {
		$state += 1;
		$domain = $domain_pure;
	}

	if (file_exists("{$loginpath}/redirect-to-ssl")) {
		if ($_SERVER["HTTPS"] !== "on") {
			$state += 2;
			$port = str_replace("\n", "", file_get_contents("{$initpath}/port-ssl"));
			$scheme = 'https';
		}
	}

	if (file_exists("{$loginpath}/redirect-to-domain")) {
		// MR -- this domain always without ':port'
		$domain = str_replace("\n", "", file_get_contents("{$loginpath}/redirect-to-domain"));

		if ($domain.':'.$port !== $host) {
			$state += 4;
		}
	}

	if ($state !== 0) {
	/*
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: {$scheme}://{$domain}:{$port}{$requesturi}");
		exit();
	*/
		echo "<script> location.replace('{$scheme}://{$domain}:{$port}{$requesturi}'); </script>";
	}