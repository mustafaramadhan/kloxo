<?


// function to parse the http auth header
function http_digest_parse($txt)
{
    // protect against missing data
    $needed_parts = array('nonce'=>1, 'nc'=>1, 'cnonce'=>1, 'qop'=>1, 'username'=>1, 'uri'=>1, 'response'=>1);
    $data = array();

    preg_match_all('@(\w+)=([\'"]?)([a-zA-Z0-9=./\_-]+)\2@', $txt, $matches, PREG_SET_ORDER);

    foreach ($matches as $m) {
        $data[$m[1]] = $m[3];
        unset($needed_parts[$m[1]]);
    }

    return $needed_parts ? false : $data;
}

function AuthenticationDigestHTTP($realm, $users) {
	error_log(var_export($_SERVER, true));
	if (empty($_SERVER['PHP_AUTH_DIGEST'])) {
		header('HTTP/1.1 401 Unauthorized');
		header('WWW-Authenticate: Digest realm="'.$realm.'" qop="auth" nonce="'.uniqid().'" opaque="'.md5($realm).'"');
		die('401 Unauthorized');
	}

	
	// analyze the PHP_AUTH_DIGEST variable
	$data = http_digest_parse($_SERVER['PHP_AUTH_DIGEST']);
	if (!array_key_exists($data['username'], $users)) {
		header('HTTP/1.1 401 Unauthorized');
		die('401 Unauthorized');
	}
		
	// generate the valid response
	$A1 = md5($data['username'] . ':' . $realm . ':' . $users[$data['username']]);
	$A2 = md5($_SERVER['REQUEST_METHOD'].':'.$data['uri']);
	$valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);
	
	if ($data['response'] != $valid_response) {
		header('HTTP/1.1 401 Unauthorized');
		die('401 Unauthorized');
	}
	return TRUE;
}


function AuthenticationBasicHTTP($realm, $users) {
	if (empty($_SERVER['PHP_AUTH_USER'])) {
		header('WWW-Authenticate: Basic realm="'.$realm.'"');
		header('HTTP/1.0 401 Unauthorized');
		die('401 Unauthorized');
	}
	
	$user = $_SERVER['PHP_AUTH_USER'];
	if (array_key_exists($user, $users) && $users[$user] == $_SERVER['PHP_AUTH_PW'] ){
		return TRUE;
	}

	header('WWW-Authenticate: Basic realm="'.$realm.'"');
	header('HTTP/1.0 401 Unauthorized');
	die('401 Unauthorized');
	return FALSE;
}


?>
