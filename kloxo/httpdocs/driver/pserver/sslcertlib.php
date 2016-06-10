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
	static $__desc_key_bits_r = array("", "", "ssl_key_bits");

}

class SslCert extends Lxdb
{
	static $__table = 'sslcert';

	static $__desc = array("", "", "ssl_certificate");

	static $__desc_nname = array("n", "", "ssl_certificate_name", URL_SHOW);
	static $__desc_certname = array("n", "", "ssl_certificate_name", URL_SHOW);
	static $__desc_add_type = array("n", "", "ssl_add_type", URL_SHOW);
	static $__desc_parent_domain = array("n", "", "parent_domain", URL_SHOW);
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
	static $__desc_username = array("", "", "user_name");

	static $__desc_ssl_action = array("", "", "ssl_action");
	static $__desc_upload_v_file = array("", "", "ssl_file");
	static $__desc_upload_v_text = array("", "", "ssl_text");
	static $__desc_upload_v_letsencrypt = array("", "", "ssl_letsencrypt");
	static $__desc_upload_v_link = array("", "", "ssl_link");

	static $__acdesc_update_update = array("", "", "certificate_info");
	static $__acdesc_update_ssl_kloxo = array("", "", "set_ssl_for_kloxo");
	static $__acdesc_update_ssl_hypervm = array("", "", "set_ssl_for_hypervm");

	function updateform($subaction, $param)
	{
		$parent = $this->getParentO();

		if (csa($subaction, "ssl_")) {
			$this->slave_id = "localhost";
			$vlist['slave_id'] = array('s', get_all_pserver());

			return $vlist;
		}

		$vlist['nname'] = $this->certname;

		$this->convertToUnmodifiable($vlist);

		if ($this->add_type !== 'link') {
			$string = $this->getCRTParse();

			$vlist['upload'] = array('M', $string);
		}

		$vlist['username'] = array("s", $this->getUserList());

		if ($parent->getClass() === 'web') {
			$this->setDefaultValue('username', $parent->getParentO()->__parent_o->nname);
		} else {
			$this->setDefaultValue('username', $parent->nname);
		}

		if ($parent->getClass() === 'web') {
		//	if ($this->isOn('upload_status')) {
			if (($this->add_type === 'text') || ($this->add_type === 'file')
					|| ($this->add_type === 'on')) {
			} elseif ($this->add_type === 'letsencrypt') {
				$vlist['ssl_data_b_s_key_bits_r'] = array("s", array("2048", "4096", "ec-256", "ec-384"));
				$vlist["ssl_data_b_s_subjectAltName_r"] = array("t", null);
			} elseif ($this->add_type === 'link') {
				$ssllist = self::getSSLParentList($param['nname']);
				$vlist['parent_domain'] = array("s", $ssllist);
			}
		} else {
			$vlist['ssl_data_b_s_key_bits_r'] = array("s", array("2048", "4096"));

			$vlist["ssl_data_b_s_commonName_r"] = null;

			$temp = self::getCountryList();
			$vlist["ssl_data_b_s_countryName_r"] = array("s", $temp);

			$vlist["ssl_data_b_s_stateOrProvinceName_r"] = null;
			$vlist["ssl_data_b_s_localityName_r"] = null;
			$vlist["ssl_data_b_s_organizationName_r"] = null;
			$vlist["ssl_data_b_s_organizationalUnitName_r"] = null;
			$vlist["ssl_data_b_s_emailAddress_r"] = null;
		//	$vlist["ssl_data_b_s_subjectAltName_r"] = null;

		}

		if ($this->add_type !== 'link') {
			if (($this->add_type === 'text') || ($this->add_type === 'file')
				|| ($this->add_type === 'on')) {
				$vlist['text_key_content'] = null;
				$vlist['text_crt_content'] = null;
				$vlist['text_ca_content'] = null;
			} else {
				$x['text_key_content'] = null;
				$x['text_crt_content'] = null;

				if ($this->add_type === 'self') {
					$x['text_csr_content'] = null;
				} else {
					$x['text_ca_content'] = null;
				}

				$this->convertToUnmodifiable($x);

				$vlist['text_key_content'] = $x['text_key_content'];
				$vlist['text_crt_content'] = $x['text_crt_content'];

				if ($this->add_type === 'self') {
					$vlist['text_csr_content'] = $x['text_csr_content'];
				} else {
					$vlist['text_ca_content'] = $x['text_ca_content'];
				}
			}
		}

		// MR -- disable it for enable 'update' button

		$vlist['__v_button'] = array();

		return $vlist;
	}

	function getUserList()
	{
		$clist = rl_exec_get('localhost', 'localhost', 'getAllClientList', array($this->main->__syncserver));

		$users = array();

		foreach ($clist as &$n) {
			$userinfo = posix_getpwnam($n);
			$fpmport = (50000 + $userinfo['uid']);

			if ($fpmport === 50000) { continue; }

			$users[] = $n;
		}

		return $users;
	}

	function getCRTParse()
	{
		$res = openssl_x509_read($this->text_crt_content);
		$ar = openssl_x509_parse($res);

		$validfrom = date('Y-m-d', $ar['validFrom_time_t']);
		$validto = date('Y-m-d', $ar['validTo_time_t']);

		$san = $ar['extensions']['subjectAltName'];

		if (!isset($san)) {
			$san = '-';
		} else {
			$san = str_replace("DNS:", "", $san);
		}
	
		$string = "- Common Name: {$ar['subject']['CN']}\n" .
			"- Subject Alt Name: {$san}\n" .
			"- Valid: {$validfrom} - {$validto}";

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
		$nlist['username'] = '15%';
		$nlist['nname'] = '40%';
		$nlist['add_type'] = '15%';
		$nlist['parent_domain'] = '15%';
		$nlist['syncserver'] = '15%';

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

		if ($parent->getClass() === 'web') {
			$alist[] = "a=addform&c=$class&dta[var]=upload&dta[val]=file";
			$alist[] = "a=addform&c=$class&dta[var]=upload&dta[val]=text";
			$alist[] = "a=addform&c=$class&dta[var]=upload&dta[val]=letsencrypt";
			$alist[] = "a=addform&c=$class&dta[var]=upload&dta[val]=link";
		} else {
			$alist[] = "a=addform&c=$class";
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

		$contentscrt = $this->text_crt_content;
		$contentskey = $this->text_key_content;
		$contentsca = trim($this->text_ca_content);

		if (!$contentscrt || !$contentskey) {
			throw new lxException($login->getThrow("certificate_key_file_empty"));
		}

		self::checkAndThrow($contentscrt, $contentskey, null);

		// MR -- make the same as program.pem; like inside lighttpd.conf example inside
		$contentspem = "$contentskey\n$contentscrt";

		rl_exec_get(null, $param['slave_id'], array("sslcert", "setProgramSsl"),
			array($contentspem, $contentsca, $contentscrt, $contentskey));
	}

	static function setProgramSsl($contentspem, $contentsca, $contentscrt, $contentskey)
	{
		$list = array('key' => $contentskey, 'crt' => $contentscrt, 'pem' => $contentspem, 'ca' => $contentsca);

		foreach ($list as $k => $v) {
			if ($v) {
				lfile_put_contents("../etc/program.{$k}", $v);
				lxfile_unix_chown("../etc/program.{$k}", "lxlabs:lxlabs");
			}
		}
	}

	function updatessl_hypervm($param)
	{
		$this->updateSetProgramSSL($param);
	}

	function updatessl_kloxo($param)
	{
		global $login, $sgbl;

		$parent = $this->getParentO();

		if ($parent->getClass() === 'web') {
			$spath = "/home/kloxo/ssl";
			$tpath = $sgbl->__path_program_etc;
			$dom = $this->nname;

			$list = array('key', 'crt', 'ca', 'pem');
			
			foreach ($list as $k => $v) {
				if (file_exists("{$spath}/{$dom}.{$v}")) {
					exec("ln -sf {$spath}/{$dom}.{$v} {$tpath}/program.{$v}");
				}
			}
		} else {
			$this->updateSetProgramSSL($param);
		}
	}

	function deleteSpecific()
	{
		global $gbl;

		$parent = $this->getParentO();

		if ($parent->getClass() === 'web') {
			$name = $parent->nname;
			$user = $parent->customer_name;

		//	$path = "/home/{$user}/ssl";
		//	$path = "/home/kloxo/client/{$user}/ssl";
			$spath = "/home/kloxo/ssl";
			$cpath = "/root/.acme.sh";
			$lpath = "/etc/letsencrypt";

			exec("'rm' -rf {$cpath}/{$name}*");
			exec("'rm' -rf {$lpath}/{live,archive,renewal}/{$name}* {$spath}/{$name}*");

			lxshell_return("sh", "/script/fixweb", "--domain={$name}");
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
				$param['add_type'] = 'letsencrypt';

				$param['ssl_data_b_s_subjectAltName_r'] = replace_to_space($param['ssl_data_b_s_subjectAltName_r']);
			} elseif ($param['upload'] === 'link') {
				$param['add_type'] = 'link';
			}
		} else {
			$param['add_type'] = 'self';

			$param['ssl_data_b_s_commonName_r'] = replace_to_space($param['ssl_data_b_s_commonName_r']);
		}

		$param['certname'] = $param['nname'];

		return $param;
	}

	function postAdd()
	{
		switch ($this->add_type) {
			case 'self':
				$this->createSelfCertificate();
				break;
			case 'letsencrypt':
				$this->createLetsencrypt();
				break;
			case 'link':
				$this->createLink();
				break;
			default:
				$parent = $this->getParentO();

				if ($parent->getClass() === 'web') {
					$this->createDomainSSL();
				}
		}
	}

	function createDomainSSL()
	{
		global $gbl, $login;

		$parent = $this->getParentO();
		$name = $parent->nname;
	//	$user = $parent->customer_name;

	//	$path = "/home/kloxo/client/{$user}/ssl";
		$path = "/home/kloxo/ssl";

		$contentscrt = $this->text_crt_content;
		$contentskey = $this->text_key_content;

		if (isset($this->text_csr_content)) {
			$contentscsr = $this->text_csr_content;
		}

		if (isset($this->text_ca_content)) {
			$contentsca = trim($this->text_ca_content);
		}

		if (!$contentscrt || !$contentskey) {
			throw new lxException($login->getThrow("certificate_key_file_empty"));
		}

		self::checkAndThrow($contentscrt, $contentskey, $name);

		$list = array('key' => $contentskey, 'crt' => $contentscrt, 'csr' => $contentscsr, 'ca' => $contentsca);

		foreach ($list as $k => $v) {
			if ($v) {
				lfile_put_contents("{$path}/{$name}.{$k}", $v);
			}
		}

		if (isset($this->text_ca_content)) {
			$contentspem = "{$contentskey}\n{$contentscrt}\n{$contentsca}";
		} else {
			$contentspem = "{$contentskey}\n{$contentscrt}";
		}

		lfile_put_contents("{$path}/{$name}.pem", $contentspem);

		exec("sh /script/fixweb --domain={$name}");
	//	createRestartFile($gbl->getSyncClass(null, $this->syncserver, 'web'));
		createRestartFile("restart-web");
	}

	function isSelect()
	{
		return true;

	//	$db = new Sqlite($this->__masterserver, "sslipaddress");
	//	$res = $db->getRowsWhere("sslcert = '$this->certname'", array('nname'));

	//	return ($res ? false : true);
	}

	static function addform($parent, $class, $typetd = null)
	{
		global $gbl, $sgbl, $login, $ghtml;

		if ($parent->getClass() === 'web') {
			$nname = array('M', $parent->nname);
			$vlist['username'] = array("h", $parent->getParentO()->__parent_o->nname);

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
			//	$vlist['ssl_action'] = array("s", array("test", "add", "renew", "revoke"));
				$vlist['ssl_data_b_s_key_bits_r'] = array("s", array("2048", "4096", "ec-256", "ec-384"));
				$vlist["ssl_data_b_s_subjectAltName_r"] =
					array('t', "{$parent->nname} www.{$parent->nname} cp.{$parent->nname} webmail.{$parent->nname}");
				$vlist["ssl_data_b_s_emailAddress_r"] = array("m", "admin@{$parent->nname}");
			} else if ($typetd['val'] === 'link') {
				$vlist['nname'] = $nname;

				$ssllist = self::getSSLParentList($parent->nname);

				if ($ssllist[0] !== null) {
					$vlist['parent_domain'] = array("s", $ssllist);
				}
			}
		} else {
			$nname = null;
			$cname = null;
			$saname = null;
			$email = null;
			$vlist['username'] = array("h", $parent->nname);

			$vlist['nname'] = $nname;

			// MR -- add key_bits options
			$vlist['ssl_data_b_s_key_bits_r'] = array("s", array("2048", "4096"));

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

		// MR -- TODO: link still disabled
	//	if ($typetd['val'] !== 'link') {
			$ret['action'] = 'add';
	//	}

		$ret['variable'] = $vlist;

		return $ret;
	}

	static function getSSLParentList($nname)
	{
		global $login;

		if ($login->nname === 'admin') {
		//	$filesearch = "/home/kloxo/client/*/ssl/*.ca";
		} else {
		//	$filesearch = "/home/kloxo/client/{$login->nname}/ssl/*.ca";
		}

		$filesearch = "/home/kloxo/ssl/*.ca";
	
		foreach (glob($filesearch, GLOB_MARK) as $filename) {
			if (!is_link($filename)) {
				$x = str_replace(".ca", "", basename($filename));

				if ($x !== $nname) {
					$ssllist[] = $x;
				} else {
					$ssllist[] = null;
				}
			}
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

	function createSelfCertificate()
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

		// MR -- disable because use ssl_data_b for key_bits
	//	$input['key_bits'] = $this->key_bits;

		if ($parent->getClass() === 'web') {
		//	$shpath = "/home/kloxo/client/{$user}/ssl";
			$shpath = "/home/kloxo/ssl";
		} else {
			$shpath = "/tmp";
		}

		$tplsource = getLinkCustomfile("/opt/configs/openssl/tpl", "openssl.sh.tpl");

		$tpltarget = "{$shpath}/{$name}_openssl.sh";

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

		exec("cd {$shpath}; sh {$name}_openssl.sh", $out, $ret);

		if ($ret !== 0) {
			throw new lxException($login->getThrow("create_certificate_failed"), '', $parent->nname);
		}

		$this->text_key_content = lfile_get_contents("{$shpath}/{$name}.key");
		$this->text_crt_content = lfile_get_contents("{$shpath}/{$name}.crt");
		$this->text_csr_content = lfile_get_contents("{$shpath}/{$name}.csr");

		if ($parent->getClass() === 'web') {
			$this->createDomainSSL();
		} 

	}

	function createLetsencrypt()
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
	//	$user = $parent->customer_name;

		$name = $temp['name'] = $parent->nname;

	//	$shpath = "/home/kloxo/client/{$user}/ssl";
		$shpath = "/home/kloxo/ssl";

		foreach ($temp as $key => $t) {
			if ($key === 'subjectAltName') {
				$val = replace_to_space($t);
			} else {
				$val = $t;
			}

			$input[$key] = $val;
		}

		// MR -- disable because use ssl_data_b for key_bits
	//	$input['key_bits'] = $this->key_bits;

		if (file_exists("/root/.acme.sh/acme.sh")) {
			$use_acmesh = true;
		} else {
			$use_acmesh = false;
		}

		if ($use_acmesh) {
			$tplsource = getLinkCustomfile("/opt/configs/acme.sh/tpl", "acme.sh.tpl");
			$tpltarget = "{$shpath}/{$name}_acme.sh";
		} else {
			$tplsource = getLinkCustomfile("/opt/configs/letsencrypt/tpl", "letsencrypt.sh.tpl");
			$tpltarget = "{$shpath}/{$name}_letsencrypt.sh";
		}

		$tpl = lfile_get_contents($tplsource);

		$tplparse = getParseInlinePhp($tpl, $input);

		if (!file_exists($shpath)) {
			mkdir($shpath);
		}

		if ($tplparse) {
			lfile_put_contents($tpltarget, $tplparse);
		}

		if ($use_acmesh) {
			exec("cd {$shpath}; sh {$name}_acme.sh", $out, $ret);
		} else {
			exec("cd {$shpath}; sh {$name}_letsencrypt.sh", $out, $ret);
		}

		if ($ret !== 0) {
			throw new lxException($login->getThrow("create_certificate_failed"), '', $parent->nname);
		}

		if ($use_acmesh) {
			$acpath = "/root/.acme.sh/{$name}";

			$this->text_key_content = lfile_get_contents("{$acpath}/{$name}.key");
			$this->text_crt_content = lfile_get_contents("{$acpath}/{$name}.cer");
			$this->text_ca_content = lfile_get_contents("{$acpath}/ca.cer");
		} else {
			$lepath = "/etc/letsencrypt/live/{$name}";

			$this->text_key_content = lfile_get_contents("{$lepath}/privkey.pem");
			$this->text_crt_content = lfile_get_contents("{$lepath}/cert.pem");
			$this->text_ca_content = lfile_get_contents("{$lepath}/chain.pem");
		}

		// MR -- no need because include in [domain]_letsencrypt.sh
	//	exec("sh /script/fixweb --domain={$name}");
	//	createRestartFile($gbl->getSyncClass(null, $this->syncserver, 'web'));
	//	createRestartFile("restart-web");
	}

	function createLink()
	{
		$parent = $this->getParentO();
	//	$user = $parent->customer_name;

	//	$filesearch = "/home/kloxo/client/*/ssl/{$this->parent_domain}.ca";
		$filesearch = "/home/kloxo/ssl/{$this->parent_domain}.ca";
	
		foreach (glob($filesearch, GLOB_MARK) as $filename) {
			$sslparent = str_replace(".ca", "", $filename);
		}

	//	$targetpath = "/home/kloxo/client/{$user}/ssl";
		$targetpath = "/home/kloxo/ssl";

		if (!file_exists($targetpath)) {
			mkdir($targetpath);
		}

		$list = array('key', 'crt', 'ca', 'pem');

		foreach ($list as $k => $v) {
			if (file_exists("{$sslparent}.{$v}")) {
				exec("ln -sf {$sslparent}.{$v} {$targetpath}/{$parent->nname}.{$v}");
			}
		}
	}

	static function getSslCertnameFromIP($ipname)
	{
		return fix_nname_to_be_variable($ipname);
	}

	function isSync() { return false; }
}

class all_sslcert extends SslCert
{
	static $__desc = array("n", "",  "all_sslcert");
	static $__desc_parent_name_f =  array("n", "",  "owner");

	function isSelect()
	{
		return false;
	}

	static function initThisListRule($parent, $class)
	{
		global $login;

		if (!$parent->isAdmin()) {
			throw new lxException($login->getThrow("only_admin_can_access"));
		}

		return "__v_table";
	}

	static function createListSlist($parent)
	{
		$nlist['nname'] = null;

		return $nlist;
	}

	static function AddListForm($parent, $class)
	{
		return null;
	}

	static function createListAlist($parent, $class)
	{
		return all_domain::createListAlist($parent, $class);
	}

	static function createListNlist($parent, $view)
	{
		$nlist['nname'] = '50%';
		$nlist['parent_name_f'] = '50%';
		
		return $nlist;
	}

	static function createListUpdateForm($object, $class)
	{
		return null;
	}
}

