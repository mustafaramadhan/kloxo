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

		if ($contentsca) {
			$contentspem = "{$contentskey}\n{$contentscer}\n{$contentsca}";
			lfile_put_contents("{$path}/{$name}.ca", $contentsca);
		} else {
			$contentspem = "{$contentskey}\n{$contentscer}";
			lxfile_cp("../file/ssl/default.ca", "{$path}/{$name}.ca");
		}

		lfile_put_contents("{$path}/{$name}.pem", $contentspem);

	//	createRestartFile($this->main->__var_webdriver);
		createRestartFile("restart-web");
	}
}