<?php 

include_once "htmllib/lib/include.php"; 

$list = lscandir_without_dot_or_underscore("/home/kloxo/httpd/ssl/");

foreach($list as $l) {
	if (cse($l, ".crt")) {
		$newlist[] = basename($l, ".crt");
	} else {
		continue;
	}
}

$kloxo_file_path = "/usr/local/lxlabs/kloxo/file";
$httpd_ssl_path = "/home/kloxo/httpd/ssl/";

// MR -- using exec because lxshell_return not work!
exec("cat {$kloxo_file_path}/default.crt {$kloxo_file_path}/default.key > {$kloxo_file_path}/default.pem");


foreach($newlist as $n) {
	lxfile_cp("{$kloxo_file_path}/default.crt", "{$httpd_ssl_path}/$n.crt");
	lxfile_cp("{$kloxo_file_path}/default.key", "{$httpd_ssl_path}/$n.key");
	lxfile_cp("{$kloxo_file_path}/default.ca", "{$httpd_ssl_path}/$n.ca");

	lxfile_cp("{$kloxo_file_path}/default.pem", "{$httpd_ssl_path}/$n.pem");
}
