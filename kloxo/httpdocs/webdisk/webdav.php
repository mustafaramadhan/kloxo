<?

ini_set("default_charset", "UTF-8");
//ini_set("error_reporting", E_ALL);

$realm = 'Restricted area Keyphrene';
$DBUSER = "ligesh";
$DBPWD = "hello";
$DB_WEBDAV = 'webdav';
$users = array($DBUSER => $DBPWD);
//AuthenticationDigestHTTP($realm, $users);
//AuthenticationBasicHTTP($realm, $users);



if (isset($_SERVER["ORIG_PATH_INFO"])) {
	$_SERVER["PATH_INFO"] = $_SERVER["ORIG_PATH_INFO"];
	//$_SERVER["PATH_INFO"] = $_SERVER["REQUEST_URI"];
} else {
	if (isset($_SERVER["REQUEST_URI"])) {
		$_SERVER["PATH_INFO"] = $_SERVER["REQUEST_URI"];
	}
}
if (isset($_SERVER['HTTP_CONTENT_LENGTH'])) {
	$_SERVER["CONTENT_LENGTH"] = $_SERVER['HTTP_CONTENT_LENGTH'];
	unset($_SERVER['HTTP_CONTENT_LENGTH']);
}

error_log(var_export($_SERVER, true));


require_once "HTTP/WebDAV/Server/Filesystem.php";
$server = new HTTP_WebDAV_Server_Filesystem();
$server->db_host = $DBHOST;
$server->db_name = $DB_WEBDAV;
$server->db_user = $DBUSER;
$server->db_passwd = $DBPWD;
//$server->ServeRequest(_SITE_PATH);
$server->ServeRequest("/home/showing/");

