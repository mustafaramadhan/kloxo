<?php 


class Certificate_b extends LxaClass{
	static $__desc_private_key = array("", "",  "private_key");
	static $__desc_certificate = array("", "",  "certificate");
	static $__desc_ca_certificate = array("", "",  "ca_certificates");
}

class Ssl_data_b  extends LxaClass {
	static $__desc_countryName_r =array("n", "",  "countryname");
	static $__desc_stateOrProvinceName_r =array("n", "",  "state");
	static $__desc_localityName_r =array("n", "",  "city");
	static $__desc_organizationName_r =array("n", "",  "organization");
	static $__desc_organizationalUnitName_r =array("n", "",  "department_name");
	static $__desc_commonName_r =array("n", "",  "commonname_(domain_name)");
	static $__desc_emailAddress_r =array("n", "",  "emailaddress");
}

class SslCert extends Lxdb {

static $__desc =  array("", "",  "ssl_certificate");
static $__desc_nname =  array("n", "",  "ssl_certificate_name", URL_SHOW);
static $__desc_certname =  array("n", "",  "ssl_certificate_name", URL_SHOW);
static $__desc_syncserver =  array("", "",  "");
static $__desc_slave_id =  array("", "",  "slave_ID (master_is_localhost)");
static $__desc_text_csr_content =  array("t", "",  "CSR");
static $__desc_text_key_content =  array("t", "",  "Key");
static $__desc_text_crt_content =  array("t", "",  "Certificate");
static $__desc_text_ca_content =  array("t", "",  "CACert");
static $__desc_ssl_key_file_f =  array("n:F", "",  "key_file");
static $__desc_ssl_crt_file_f =  array("n:F", "",  "certificate_file");
static $__desc_ssl_ca_file_f =  array("F", "",  "certificate_CA_file");
//static $__desc_ssl_ca_file_f =  array("F", "",  "authority_file");
static $__desc_upload =  array("", "",  "data");
static $__desc_upload_v_uploadfile =  array("", "",  "upload File");
static $__desc_upload_v_uploadtxt =  array("", "",  "upload Txt");

static $__acdesc_update_update =  array("", "",  "certificate_info");
static $__acdesc_update_ssl_kloxo =  array("", "",  "set_ssl_for_kloxo");
static $__acdesc_update_ssl_hypervm =  array("", "",  "set_ssl_for_hypervm");



function updateform($subaction, $param)
{


	if (csa($subaction, "ssl_")) {
		$this->slave_id = "localhost";
		$vlist['slave_id'] = array('s', get_all_pserver());
		return $vlist;
	}

	if ($this->isOn('upload_status')) {

		$string = null;
		$res = openssl_x509_read($this->text_crt_content);
		$ar = openssl_x509_parse($res);
		$string .= "{$ar['name']} {$ar['subject']['CN']}";
		$vlist['upload'] = array('M', $string);
		$vlist['text_crt_content'] = null;
		$vlist['text_key_content'] = null;
		$vlist['text_ca_content'] = null;
	} else {
		$vlist['nname'] = array('M', $this->certname);
		$vlist["ssl_data_b_s_commonName_r"]  = null;
		$vlist["ssl_data_b_s_countryName_r"] =  null;
		$vlist["ssl_data_b_s_stateOrProvinceName_r"] = null;
		$vlist["ssl_data_b_s_localityName_r"]  = null;
		$vlist["ssl_data_b_s_organizationName_r"]  = null;
		$vlist["ssl_data_b_s_organizationalUnitName_r"]  = null;
		$vlist["ssl_data_b_s_emailAddress_r"]  = null;
		$this->convertToUnmodifiable($vlist);
		$vlist['text_csr_content'] = null;
		$vlist['text_crt_content'] = null;
		$vlist['text_key_content'] = null;
	}

/*
	if ($this->getParentO()->isAdmin()) {
		$vlist['__m_message_pre'] = 'sslcert_updateform_update_pre_admin';
	} else {
		$vlist['__m_message_pre'] = 'sslcert_updateform_update_pre';
	}
*/


	$vlist['__v_button'] = array();
	return $vlist;


}

static function checkAndThrow($publickey, $privatekey, $throwname = null) 
{
	global $gbl, $sgbl, $login, $ghtml; 

	if (!self::checkKeyCert($publickey, $privatekey)) {
		if ($gbl->__restore_flag) {
			log_log("restore", "certificate_key_file_corrupted");
		} else {
			throw new lxException("certificate_key_file_corrupted", '', $throwname);
		}
	}

	//dprint("Succeesffully tested <br> <br> ");
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
	$nlist['nname'] = '100%';
	$nlist['certname'] = '10%';
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
	$alist[] = "a=addform&c=$class&dta[var]=upload&dta[val]=uploadfile";
	$alist[] = "a=addform&c=$class&dta[var]=upload&dta[val]=uploadtxt";
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

	$contentscer = $this->text_crt_content;
	$contentskey = $this->text_key_content;
	$contentsca = trim($this->text_ca_content);

	if (!$contentscer || !$contentskey) {
		throw new lxException("certificate_key_file_empty", '');
	}
	sslcert::checkAndThrow($contentscer, $contentskey, null);

//	$contentpem = "$contentscer\n$contentskey";
	// MR -- make the same as program.pem; like inside lighttpd.conf example inside
	$contentpem = "$contentskey\n$contentscer";

	rl_exec_get(null, $param['slave_id'], array("sslcert", "setProgramSsl"), array($contentpem, $contentsca));

}


static function setProgramSsl($contentpem, $contentsca)
{
	lfile_put_contents("../etc/program.pem", $contentpem);
	if ($contentsca) {
		lfile_put_contents("../etc/program.ca", $contentsca);
	}

	lxfile_unix_chown("../etc/program.pem", "lxlabs:lxlabs");
	lxfile_unix_chown("../etc/program.ca", "lxlabs:lxlabs");
}

function updatessl_hypervm($param)
{
	$this->updateSetProgramSSL($param);
}
function updatessl_kloxo($param)
{
	$this->updateSetProgramSSL($param);
}



static function add($parent, $class, $param)
{
	if (isset($param['upload'])) {
		if ($param['upload'] === 'uploadfile') {
			$key_file = $_FILES['ssl_key_file_f']['tmp_name'];
			$crt_file = $_FILES['ssl_crt_file_f']['tmp_name'];
			$ca_file = $_FILES['ssl_ca_file_f']['tmp_name'];

			if (!$key_file || !$crt_file) {
				throw new lxException("key_crt_files_needed");
			}

			$param['text_key_content'] = file_get_contents($key_file); 
			$param['text_crt_content'] = file_get_contents($crt_file);

			if ($ca_file && lxfile_exists($ca_file)) {
				$param['text_ca_content'] = lfile_get_contents($ca_file);
			}

		} 

		sslcert::checkAndThrow($param['text_crt_content'], $param['text_key_content']);

		$param['upload_status'] = 'on';

	} else {
		$param['upload_status'] = 'off';
	}

	$param['certname'] = $param['nname'];
	return $param;
}

function postAdd()
{
	if (!$this->isOn('upload_status')) {
		$this->createNewcertificate();
	}
}

function isSelect()
{
	return true;
	$db = new Sqlite($this->__masterserver, "sslipaddress");
	$res = $db->getRowsWhere("sslcert = '$this->certname'", array('nname'));

	return ($res? false: true);
}


static function addform($parent, $class, $typetd = null)
{

	global $gbl, $sgbl, $login, $ghtml; 
	if ($typetd['val'] === 'uploadfile') {
		$vlist['nname'] = null;
		$vlist['ssl_key_file_f'] = null;
		$vlist['ssl_crt_file_f'] = null;
		$vlist['ssl_ca_file_f'] = null;
		$sgbl->method = 'post';
	} else if ($typetd['val'] === 'uploadtxt') {
		$vlist['nname'] = null;
		$vlist['text_crt_content'] = null;
		$vlist['text_key_content'] = null;
		$vlist['text_ca_content'] = null;
	} else {
		include "htmllib/lib/countrycode.inc";

		foreach($gl_country_code as $key=>$name ){
			$temp[] = "$key:$name";
		}

		$vlist['nname'] = null;
		$vlist["ssl_data_b_s_commonName_r"]  = null;
		$vlist["ssl_data_b_s_countryName_r"] =  array("s", $temp);
		$vlist["ssl_data_b_s_stateOrProvinceName_r"] = null;
		$vlist["ssl_data_b_s_localityName_r"]  = null;
		$vlist["ssl_data_b_s_organizationName_r"]  = null;
		$vlist["ssl_data_b_s_organizationalUnitName_r"]  = null;
		$vlist["ssl_data_b_s_emailAddress_r"]  = null;
	}

	// MR -- add 'message inbox'
	$vlist['__m_message_pre'] = 'sslcert_updateform_update_pre';

	$ret['action'] = 'add';
	$ret['variable'] = $vlist;

	return $ret;
}

function createNewcertificate()
{
	global $gbl, $sgbl, $login, $ghtml; 
	
	foreach($this->ssl_data_b as $key => $value) {
		if (!cse($key, "_r")) {
			continue;
		}
		$nk = strtil($key, "_r");
		$temp[$nk]  = $value;
	}

	foreach($temp as $key=>$t) {
		if($key=== "countryName") {
			$l = explode(":", $t);

			$name = $l[0];
		} else
			$name = $t;
		$ltemp[$key] = $name;
	}

//	$config['private_key_bits'] = 1024;
	$config['private_key_bits'] = 2048;
	$privkey = openssl_pkey_new($config);
	openssl_pkey_export($privkey, $text_key_content);
	$csr = openssl_csr_new($ltemp, $privkey);
	openssl_csr_export($csr, $text_csr_content);
	$sscert = openssl_csr_sign($csr, null, $privkey, 3650);
	openssl_x509_export($sscert, $text_crt_content);


	$this->text_key_content = $text_key_content;
	$this->text_csr_content = $text_csr_content;
	$this->text_crt_content = $text_crt_content;
}



static function getSslCertnameFromIP($ipname)
{
	return fix_nname_to_be_variable($ipname);
}



// Not fucking needed
/*
function dbactionAdd()
{
	global $gbl, $sgbl, $login, $ghtml; 
	if ($this->main->isOn('upload_status')) {
		$crtfile = "$sgbl->__path_ssl_root/" . $this->main->certname. ".crt";
		$keyfile = "$sgbl->__path_ssl_root/". $this->main->certname. ".key";
		//$cafile =  "$sgbl->__path_ssl_root/". $this->main->certname. ".ca";
		lfile_put_contents($crtfile, $this->main->ssl_crt_file_f);
		lfile_put_contents($keyfile, $this->main->ssl_key_file_f);
		if (!sslcert::checkKeyCert($crtfile, $keyfile)) {
			lxfile_rm($crtfile);
			lxfile_rm($keyfile);
			throw new lxexception('certificate_key_file_corrupted', '');
		}

		//lfile_put_contents($cafile, $this->main->ssl_ca_file_f);
		lxfile_generic_chown($crtfile, "lxlabs");
		lxfile_generic_chown($keyfile, "lxlabs");
		//lxfile_generic_chown($cafile, "lxlabs");
	} else {
		$this->createNewcertificate();
	}
}

*/

function isSync () { return false; }

}

