<?php 

class sslipaddress__sync extends lxDriverClass {


function dbactionUpdate($subaction)
{
	$name = sslcert::getSslCertnameFromIP($this->main->nname);

	$path = "__path_ssl_root";

	$contentscer = $this->main->text_crt_content;
	$contentskey = $this->main->text_key_content;
	$contentsca = trim($this->main->text_ca_content);

	if (!$contentscer || !$contentskey) {
		throw new lxException("certificate_key_file_empty", '');
	}
	sslcert::checkAndThrow($contentscer, $contentskey, $name);

	lfile_put_contents("$path/$name.crt", $contentscer);
	lfile_put_contents("$path/$name.key", $contentskey);
//	$contentpem = "$contentscer\n$contentskey";
	// MR -- make the same as program.pem; like inside lighttpd.conf example inside
	$contentpem = "$contentskey\n$contentscer";

	lfile_put_contents("$path/$name.pem", $contentpem);

	if ($contentsca) {
		lfile_put_contents("$path/$name.ca", $contentsca);
	} else {
		lxfile_cp("htmllib/filecore/program.ca", "$path/$name.ca");
	}

	createRestartFile($this->main->__var_webdriver);
}


}
