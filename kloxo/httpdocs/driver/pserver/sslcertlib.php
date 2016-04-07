<?php

class Certificate_b extends LxaClass
{
	static $__desc_private_key = array("", "", "private_key");
	static $__desc_certificate = array("", "", "certificate");
	static $__desc_ca_certificate = array("", "", "ca_certificates");
}

class Ssl_data_b extends LxaClass
{
	static $__desc_countryName_r = array("", "", "countryname");
	static $__desc_stateOrProvinceName_r = array("", "", "state");
	static $__desc_localityName_r = array("", "", "city");
	static $__desc_organizationName_r = array("", "", "organization");
	static $__desc_organizationalUnitName_r = array("", "", "department_name");
	static $__desc_commonName_r = array("n", "", "common_name");
	static $__desc_emailAddress_r = array("n", "", "email_address");
	static $__desc_subjectAltName_r = array("n", "", "subject_alt_name");
}

class SslCert extends Lxdb
{

	static $__desc = array("", "", "ssl_certificate");

	static $__desc_key_bits = array("", "", "ssl_key_bits");

	static $__desc_nname = array("n", "", "ssl_certificate_name", URL_SHOW);
	static $__desc_certname = array("n", "", "ssl_certificate_name", URL_SHOW);
	static $__desc_parent_domain = array("n", "", "ssl_parent_domain", URL_SHOW);
	static $__desc_syncserver = array("", "", "");
	static $__desc_slave_id = array("", "", "slave_ID (master_is_localhost)");
	static $__desc_text_csr_content = array("t", "", "CSR");
	static $__desc_text_key_content = array("t", "", "Key");
	static $__desc_text_crt_content = array("t", "", "Certificate");
	static $__desc_text_ca_content = array("t", "", "CACert");
	static $__desc_ssl_key_file_f = array("n:F", "", "key_file");
	static $__desc_ssl_crt_file_f = array("n:F", "", "certificate_file");
	static $__desc_ssl_ca_file_f = array("F", "", "certificate_CA_file");
//	static $__desc_ssl_ca_file_f =  array("F", "",  "authority_file");
	static $__desc_upload = array("", "", "data");

	static $__desc_ssl_action = array("", "", "ssl_action");
	static $__desc_ssl_parent = array("s", "", "ssl_parent");
	static $__desc_upload_v_file = array("", "", "ssl_file");
	static $__desc_upload_v_text = array("", "", "ssl_text");
	static $__desc_upload_v_letsencrypt = array("", "", "ssl_letsencrypt");
	static $__desc_upload_v_link = array("", "", "ssl_link");

	static $__acdesc_update_update = array("", "", "certificate_info");
	static $__acdesc_update_ssl_kloxo = array("", "", "set_ssl_for_kloxo");
	static $__acdesc_update_ssl_hypervm = array("", "", "set_ssl_for_hypervm");

	function updateform($subaction, $param)
	{
		if (csa($subaction, "ssl_")) {
			$this->slave_id = "localhost";
			$vlist['slave_id'] = array('s', get_all_pserver());

			return $vlist;
		}

		$vlist['nname'] = $this->certname;

		$this->convertToUnmodifiable($vlist);

	//	if ($this->isOn('upload_status')) {
		if (($this->add_type === 'text') || ($this->add_type === 'file')
				|| ($this->add_type === 'on')) {

			$string = $this->getCRTParse();
			$vlist['upload'] = array('M', $string);
			$vlist['text_key_content'] = null;
			$vlist['text_crt_content'] = null;
			$vlist['text_ca_content'] = null;
		} elseif ($this->add_type === 'letsencrypt') {
			$string = $this->getCRTParse($validonly = true);
			$vlist['upload'] = array('M', $string);
			$vlist["ssl_data_b_s_subjectAltName_r"] = null;
			$vlist['text_key_content'] = null;
			$vlist['text_crt_content'] = null;
			$vlist['text_ca_content'] = null;
		} elseif ($this->add_type === 'link') {
			$ssllist = self::getSSLParentList();
			$vlist['ssl_parent'] = array("s", $ssllist);
		} else {
			$string = $this->getCRTParse($validonly = true);
			$vlist['upload'] = array('M', $string);

			$vlist["ssl_data_b_s_commonName_r"] = array('t', null);

			$temp = self::getCountryList();
			$vlist["ssl_data_b_s_countryName_r"] = array("s", $temp);

			$vlist["ssl_data_b_s_stateOrProvinceName_r"] = null;
			$vlist["ssl_data_b_s_localityName_r"] = null;
			$vlist["ssl_data_b_s_organizationName_r"] = null;
			$vlist["ssl_data_b_s_organizationalUnitName_r"] = null;
			$vlist["ssl_data_b_s_emailAddress_r"] = null;
		//	$vlist["ssl_data_b_s_subjectAltName_r"] = null;

			$x['text_key_content'] = null;
			$x['text_crt_content'] = null;
			$x['text_csr_content'] = null;

			$this->convertToUnmodifiable($x);

			$vlist['text_key_content'] = $x['text_key_content'];
			$vlist['text_crt_content'] = $x['text_crt_content'];
			$vlist['text_csr_content'] = $x['text_csr_content'];
		}

		$vlist['__v_button'] = array();

		return $vlist;
	}

	function getCRTParse($validonly = null)
	{
		$res = openssl_x509_read($this->text_crt_content);
		$ar = openssl_x509_parse($res);

		$validfrom = date('Y-m-d', $ar['validFrom_time_t']);
		$validto = date('Y-m-d', $ar['validTo_time_t']);

		if ($validonly) {
			$string = "- Valid: {$validfrom} - {$validto}";
		} else {
			$string = "- Common Name: {$ar['subject']['CN']}\n" .
				"- Subject Alt Name: {$ar['extensions']['subjectAltName']}\n" .
				"- Valid: {$validfrom} - {$validto}";
		}

		return $string;
	}

	static function checkAndThrow($publickey, $privatekey, $throwname = null)
	{
		global $gbl, $sgbl, $login, $ghtml;

		if (!self::checkKeyCert($publickey, $privatekey)) {
			if ($gbl->__restore_flag) {
				log_log("restore", "certificate_key_file_corrupted");
			} else {
				throw new lxException($login->getThrow("certificate_key_file_corrupted"), '', $throwname);
			}
		}

	//	dprint("Succeesffully tested <br> <br> ");
	}

	static function checkKeyCert($public_key, $privatekey)
	{
		$pubkey_res = openssl_get_publickey($public_key);

		$s = "mystring";
		$priv_key_res = openssl_get_privatekey($privatekey, "");
		openssl_private_encrypt($s, $encrypted_string, $priv_key_res);
		openssl_public_decrypt($encrypted_string, $decrypted_string, $public_key);

		return ($decrypted_string === $s);
	}

	static function createListNlist($parent, $view)
	{
		$nlist['nname'] = '60%';
		$nlist['parent_domain'] = '20%';
		$nlist['syncserver'] = '20%';

		return $nlist;
	}

	function createShowUpdateform()
	{
		$uflist['update'] = null;

		return $uflist;
	}

	static function createListAlist($parent, $class)
	{
		$alist[] = "a=list&c=$class";
		$alist[] = "a=addform&c=$class";
		$alist[] = "a=addform&c=$class&dta[var]=upload&dta[val]=file";
		$alist[] = "a=addform&c=$class&dta[var]=upload&dta[val]=text";

		if ($parent->getClass() === 'web') {
			$alist[] = "a=addform&c=$class&dta[var]=upload&dta[val]=letsencrypt";
			$alist[] = "a=addform&c=$class&dta[var]=upload&dta[val]=link";
		}

		return $alist;
	}

	function createShowPropertyList(&$alist)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$prgm = $sgbl->__var_program_name;

		$alist['property'][] = 'a=show';

		if ($login->isAdmin()) {
			$alist['property'][] = "a=updateform&sa=ssl_$prgm";
		}
	}

	function updateSetProgramSSL($param)
	{
		global $login;

		$contentscer = $this->text_crt_content;
		$contentskey = $this->text_key_content;
		$contentsca = trim($this->text_ca_content);

		if (!$contentscer || !$contentskey) {
			throw new lxException($login->getThrow("certificate_key_file_empty"));
		}

		self::checkAndThrow($contentscer, $contentskey, null);

		// MR -- make the same as program.pem; like inside lighttpd.conf example inside
		$contentspem = "$contentskey\n$contentscer";

		rl_exec_get(null, $param['slave_id'], array("sslcert", "setProgramSsl"), array($contentspem, $contentsca, $contentscer, $contentskey));
	}

	static function setProgramSsl($contentspem, $contentsca, $contentscrt, $contentskey)
	{
		lfile_put_contents("../etc/program.pem", $contentspem);
		lfile_put_contents("../etc/program.crt", $contentscrt);
		lfile_put_contents("../etc/program.key", $contentskey);

		lxfile_unix_chown("../etc/program.pem", "lxlabs:lxlabs");
		lxfile_unix_chown("../etc/program.crt", "lxlabs:lxlabs");
		lxfile_unix_chown("../etc/program.key", "lxlabs:lxlabs");

		if ($contentsca) {
			lfile_put_contents("../etc/program.ca", $contentsca);
			lxfile_unix_chown("../etc/program.ca", "lxlabs:lxlabs");
		}
	}

	function updatessl_hypervm($param)
	{
		$this->updateSetProgramSSL($param);
	}

	function updatessl_kloxo($param)
	{
		$this->updateSetProgramSSL($param);
	}

	function deleteSpecific()
	{
		global $gbl;

		$parent = $this->getParentO();

		if ($parent->getClass() === 'web') {
			$name = $parent->nname;
			$user = $parent->customer_name;

		//	$path = "/home/{$user}/ssl";
			$path = "/home/kloxo/client/{$user}/ssl";

			exec("'rm' -rf {$path}/{$name}.*");

			lxshell_return("sh", "/script/fixweb", "--domain={$name}");
		//	createRestartFile($gbl->getSyncClass(null, $this->syncserver, 'web'));
			createRestartFile("restart-web");
		}
	}

	static function add($parent, $class, $param)
	{
		global $login;

		foreach ($param as $k => $v) {
			if (strtoupper(trim($v)) === 'N/A') {
			//	$param[$k] = '.';
			}
		}

		if ($parent->getClass() === 'web') {
			$param['nname'] = $parent->nname;
		}

		if (isset($param['upload'])) {
			if ($param['upload'] === 'file') {
				$param['add_type'] = 'file';

				$key_file = $_FILES['ssl_key_file_f']['tmp_name'];
				$crt_file = $_FILES['ssl_crt_file_f']['tmp_name'];
				$ca_file = $_FILES['ssl_ca_file_f']['tmp_name'];

				if (!$key_file || !$crt_file) {
					throw new lxException($login->getThrow("key_crt_files_needed"));
				}

				$param['text_key_content'] = lfile_get_contents($key_file);
				$param['text_crt_content'] = lfile_get_contents($crt_file);

				if ($ca_file && lxfile_exists($ca_file)) {
					$param['text_ca_content'] = lfile_get_contents($ca_file);
				}
			} elseif ($param['upload'] === 'text') {
				$param['add_type'] = 'text';

				self::checkAndThrow($param['text_crt_content'], $param['text_key_content']);
			} elseif ($param['upload'] === 'letsencrypt') {
				$param['add_type'] = 'letencrypt';

				$param['ssl_data_b_s_subjectAltName_r'] = replace_to_space($param['ssl_data_b_s_subjectAltName_r']);
			} elseif ($param['upload'] === 'link') {
				$param['add_type'] = 'link';

				// MR -- in progress
			}

		//	$param['upload_status'] = 'on';
		} else {
		//	$param['upload_status'] = 'off';

			$param['add_type'] = 'self';

			$param['ssl_data_b_s_commonName_r'] = replace_to_space($param['ssl_data_b_s_commonName_r']);

			if ($parent->getClass() === 'web') {
			//	$param['ssl_data_b_s_commonName_r'] = '*.' . $parent->nname;
			//	$param['ssl_data_b_s_commonName_r'] = $parent->nname;
			//	$param['ssl_data_b_s_subjectAltName_r'] = $parent->nname;
			}
		}

		$param['certname'] = $param['nname'];

		return $param;
	}

	function postAdd()
	{
		$parent = $this->getParentO();

	//	if (!$this->isOn('upload_status')) {
		if ($this->add_type === 'self') {
			$this->createNewcertificate();
		}

		if ($parent->getClass() === 'web') {
			$this->createDomainSSL();
		}
	}

	function createDomainSSL()
	{
		global $gbl, $login;

		$parent = $this->getParentO();
		$name = $parent->nname;
		$user = $parent->customer_name;

		$path = "/home/kloxo/client/{$user}/ssl";

		$contentscrt = $this->text_crt_content;
		$contentskey = $this->text_key_content;
		$contentscsr = $this->text_csr_content;
		$contentsca = trim($this->text_ca_content);

		if (!$contentscrt || !$contentskey) {
			throw new lxException($login->getThrow("certificate_key_file_empty"));
		}

		self::checkAndThrow($contentscrt, $contentskey, $name);

		lfile_put_contents("{$path}/{$name}.crt", $contentscrt);
		lfile_put_contents("{$path}/{$name}.key", $contentskey);
		lfile_put_contents("{$path}/{$name}.csr", $contentscsr);

		$contentspem = "{$contentskey}\n{$contentscrt}";

		lfile_put_contents("{$path}/{$name}.pem", $contentspem);

		if ($contentsca) {
			lfile_put_contents("{$path}/{$name}.ca", $contentsca);
		}

		exec("sh /script/fixweb --domain={$name}");
	//	createRestartFile($gbl->getSyncClass(null, $this->syncserver, 'web'));
		createRestartFile("restart-web");
	}

	function isSelect()
	{
		return true;

		$db = new Sqlite($this->__masterserver, "sslipaddress");
		$res = $db->getRowsWhere("sslcert = '$this->certname'", array('nname'));

		return ($res ? false : true);
	}

	static function addform($parent, $class, $typetd = null)
	{
		global $gbl, $sgbl, $login, $ghtml;

		if ($parent->getClass() === 'web') {
			$nname = array('M', $parent->nname);
			$cname = array('t', $parent->nname);
			$saname = array('M', $parent->nname);
			$email = array("m", "admin@{$parent->nname}");
		} else {
			$nname = null;
			$cname = null;
			$saname = null;
			$email = null;
		}

		if ($typetd['val'] === 'file') {
			$vlist['nname'] = $nname;
			$vlist['ssl_key_file_f'] = null;
			$vlist['ssl_crt_file_f'] = null;
			$vlist['ssl_ca_file_f'] = null;
			$sgbl->method = 'post';
		} else if ($typetd['val'] === 'text') {
			$vlist['nname'] = $nname;
			$vlist['text_key_content'] = null;
			$vlist['text_crt_content'] = null;
			$vlist['text_ca_content'] = null;
		} else if ($typetd['val'] === 'letsencrypt') {
			$vlist['nname'] = $nname;
			$vlist['ssl_action'] = array("s", array("test", "add", "renew", "revoke"));
			$vlist['key_bits'] = array("s", array("2048", "4096"));
			$vlist["ssl_data_b_s_subjectAltName_r"] = array('t', "{$parent->nname}\nwww.{$parent->nname}\ncp.{$parent->nname}\nwebmail.{$parent->nname}");
			$vlist["ssl_data_b_s_emailAddress_r"] = array("m", "admin@{$parent->nname}");
		} else if ($typetd['val'] === 'link') {
			$vlist['nname'] = $nname;

			$ssllist = self::getSSLParentList();

			$vlist['ssl_parent'] = array("s", $ssllist);
		} else {
			$vlist['nname'] = $nname;

			// MR -- add key_bits options
			$vlist['key_bits'] = array("s", array("2048", "1024", "512", "4096"));


			$vlist["ssl_data_b_s_commonName_r"] = $cname;

		//	$vlist["ssl_data_b_s_subjectAltName_r"] = $saname;

			$temp = self::getCountryList();

			$vlist["ssl_data_b_s_countryName_r"] = array("s", $temp);
			$vlist["ssl_data_b_s_stateOrProvinceName_r"] = array("m", "N/A");
			$vlist["ssl_data_b_s_localityName_r"] = array("m", "N/A");
			$vlist["ssl_data_b_s_organizationName_r"] = array("m", "N/A");
			$vlist["ssl_data_b_s_organizationalUnitName_r"] = array("m", "N/A");
			$vlist["ssl_data_b_s_emailAddress_r"] = $email;
		}

		// MR -- TODO: letsencrypt and link still disabled
		if (($typetd['val'] !== 'link') && ($typetd['val'] !== 'letsencrypt')) {
			$ret['action'] = 'add';
		}

		$ret['variable'] = $vlist;

		return $ret;
	}

	static function getSSLParentList()
	{
		global $login;

		if ($login->nname === 'admin') {
			$filesearch = "/home/kloxo/client/*/ssl/*.key";
		} else {
			$filesearch = "/home/kloxo/client/{$login->nname}/ssl/*.key";
		}

		foreach (glob($filesearch, GLOB_MARK) as $filename) {
		//	if (!is_link($filename)) {
				$dom = str_replace(".key", "", basename($filename));
				$subdom = str_replace($dom, "", $parent->nname);

			//	if (strpos($subdom, ".", 1) !== false) {
					$ssllist[] = $dom;
			//	}
		//	}
		}

		return $ssllist;
	}

	static function getCountryList()
	{
		include "lib/html/countrycode.inc";
		$a[] = "N/A";

		foreach ($gl_country_code as $key => $name) {
			$a[] = "$key:$name";
		}

		return $a;
	}

	function createNewcertificate()
	{
		global $gbl, $sgbl, $login, $ghtml;

		foreach ($this->ssl_data_b as $key => $value) {
			if (!cse($key, "_r")) {
				continue;
			}

			$nk = strtil($key, "_r");
			$temp[$nk] = $value;
		}

		$parent = $this->getParentO();

		if ($parent->getClass() === 'web') {
			$name = $temp['name'] = $parent->nname;
			$user = $parent->customer_name;
		} else {
		//	$name = $temp['name'] = $parent->nname;
			$name = $temp['name'] = 'openssl';
		}

		foreach ($temp as $key => $t) {
			if ($key === "countryName") {
				$l = explode(":", $t);

				$val = $l[0];
			} else {
				if ($key === 'commonName') {
					$val = replace_to_space($t);
				} else {
					$val = $t;
				}
			}

			$input[$key] = $val;
		}

		$input['key_bits'] = $this->key_bits;

	/*
		if ($parent->getClass() === 'web') {
		//	$cnffile = "/home/{$user}/ssl/{$name}.cnf";
			$cnffile = "/home/kloxo/client/{$user}/ssl/{$name}.cnf";
		} else {
			$cnffile = '/tmp/openssl.cnf';
		}

		$dn = $input;

	//	unset($dn['nname']);

		$config = array('config' => $cnffile,
			'digest_alg' => 'sha256',
			'private_key_bits' => (int)$this->key_bits,
			'private_key_type' => OPENSSL_KEYTYPE_RSA,
			'encrypt_key' => true,
			'encrypt_key_cipher' => OPENSSL_CIPHER_3DES
		);

		$tplsource = getLinkCustomfile("/opt/configs/openssl/tpl", "openssl.cnf.tpl");

		$tpltarget = $cnffile;

		$tpl = lfile_get_contents($tplsource);

		$tplparse = getParseInlinePhp($tpl, $input);

		if ($parent->getClass() === 'web') {
		//	if (!file_exists("/home/{$user}/ssl")) {
			if (!file_exists("/home/kloxo/client/{$user}/ssl")) {
				mkdir("/home/kloxo/client/{$user}/ssl");
			}
		}

		if ($tplparse) {
			lfile_put_contents($tpltarget, $tplparse);
		}

		$privkey = openssl_pkey_new($config);
		openssl_pkey_export($privkey, $text_key_content);
		$csr = openssl_csr_new($dn, $privkey, $config);
		openssl_csr_export($csr, $text_csr_content);
		$sscert = openssl_csr_sign($csr, null, $privkey, 3650);
		openssl_x509_export($sscert, $text_crt_content);

		// MR -- not using openssl error message because so many warning but process still work.
		while (($e = openssl_error_string()) !== false) {
		//	throw new lxException(str_replace(' ', '_', $e), '', $parent->nname);
		//	throw new lxException($e, '', $parent->nname);
		}

		$this->text_key_content = $text_key_content;
		$this->text_csr_content = $text_csr_content;
		$this->text_crt_content = $text_crt_content;
	*/

		if ($parent->getClass() === 'web') {
			$shpath = "/home/kloxo/client/{$user}/ssl";
		} else {
			$shpath = "/tmp";
		}

		$tplsource = getLinkCustomfile("/opt/configs/openssl/tpl", "openssl.sh.tpl");

		$tpltarget = "{$shpath}/openssl.sh";

		$tpl = lfile_get_contents($tplsource);

		$tplparse = getParseInlinePhp($tpl, $input);

		if ($parent->getClass() === 'web') {
			if (!file_exists($shpath)) {
				mkdir($shpath);
			}
		}

		if ($tplparse) {
			lfile_put_contents($tpltarget, $tplparse);
		}

		exec("cd {$shpath}; sh openssl.sh", $out, $ret);

		if ($ret !== 0) {
			throw new lxException($login->getThrow("create_certificate_failed"), '', $parent->nname);
		}

		$this->text_key_content = lfile_get_contents("{$shpath}/{$name}.key");
		$this->text_csr_content = lfile_get_contents("{$shpath}/{$name}.csr");
		$this->text_crt_content = lfile_get_contents("{$shpath}/{$name}.crt");

	}

	static function getSslCertnameFromIP($ipname)
	{
		return fix_nname_to_be_variable($ipname);
	}

	function isSync() { return false; }
}

