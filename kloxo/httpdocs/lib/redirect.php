<?php
	$kloxopath = "/usr/local/lxlabs/kloxo";
	$initpath = "{$kloxopath}/init";
	$loginpath = "{$kloxopath}/httpdocs/login";

	$a = $_SERVER;

	$state = 0;

	$host = $a["HTTP_HOST"];
	$splitter = explode(":", $host);
	$domain = $splitter[0];
	$port = ($splitter[1]) ? $splitter[1] : '7778';
	$requesturi = $a["REQUEST_URI"];
	$scheme = (isset($a["HTTP_SCHEME"])) ? $a["HTTP_SCHEME"] : ((isset($a["HTTPS"]) && $a["HTTPS"] === "on") ? "https" : "http");

	$domain_pure = preg_replace('/(cp\.|webmail\.|www\.|mail\.)(.*)/i', "$2", $domain);

	if ($domain_pure !== $domain) {
		$state += 1;
		$domain = $domain_pure;
	}

	if (file_exists("{$loginpath}/redirect-to-ssl")) {
	//	if ($a["HTTPS"] === "off") {
		if ($scheme === "http") {
			$state += 2;
			$port = trim(file_get_contents("{$initpath}/port-ssl"));
			$scheme = 'https';
		}
	}

	if (file_exists("{$loginpath}/redirect-to-domain")) {
		// MR -- this domain always without ':port'
		$domain = trim(file_get_contents("{$loginpath}/redirect-to-domain"));

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
		$s = "<script> location.replace('{$scheme}://{$domain}:{$port}{$requesturi}'); </script>";
		echo $s;
	}