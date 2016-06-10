<?php

class sslipaddress__sync extends lxDriverClass 
{
	function dbactionUpdate($subaction)
	{
		global $login;

		$name = sslcert::getSslCertnameFromIP($this->main->nname);

		$path = "__path_ssl_root";

		$contentscer = $this->main->text_crt_content;
		$contentskey = $this->main->text_key_content;
		$contentsca = trim($this->main->text_ca_content);

		if (!$contentscer || !$contentskey) {
			throw new lxException($login->getThrow("certificate_key_file_empty"));
		}
		
		sslcert::checkAndThrow($contentscer, $contentskey, $name);

		lfile_put_contents("$path/$name.crt", $contentscer);
		lfile_put_contents("$path/$name.key", $contentskey);

		// MR -- make the same as program.pem; like inside lighttpd.conf example inside
		$contentspem = "{$contentskey}\n{$contentscer}";

		lfile_put_contents("{$path}/{$name}.pem", $contentspem);

		if ($contentsca) {
			lfile_put_contents("{$path}/{$name}.ca", $contentsca);
		} else {
			lxfile_cp("theme/filecore/program.ca", "{$path}/{$name}.ca");
		}

	//	createRestartFile($this->main->__var_webdriver);
		createRestartFile("restart-web");
	}
}