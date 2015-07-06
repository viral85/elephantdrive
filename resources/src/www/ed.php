<?PHP

$savePasswordHash = false;
require_once('lib/vars.php');

if (!function_exists('checkNASauth')) {

	function checkNASauth() {
		return true;
	}

}

if (!function_exists('getDevicename')) {

	function getDevicename() {
		return trim(`hostname`);
	}

}

if (!function_exists('getExeLdLibraryPath')) {

	function getExeLdLibraryPath() {
		return "";
	}

}

if (!function_exists('getVersion')) {

	function getVersion() {
		$LD_LIBRARY_PATH = getExeLdLibraryPath();
		if ($LD_LIBRARY_PATH != "") {
			putenv("LD_LIBRARY_PATH=$LD_LIBRARY_PATH");
		}
		return trim(shell_exec(getExeFullName() . " -V"));
	}

}

if (!function_exists('getNASLoginURL')) {

	function getNASLoginURL() {
		return "/";
	}

}

if (!function_exists('json_encode')) {

	function json_encode($a = false) {
		if (is_null($a)) {
			return 'null';
		} else if ($a === false) {
			return 'false';
		} else if ($a === true) {
			return 'true';
		} else if (is_scalar($a)) {
			if (is_float($a)) {
				// Always use "." for floats.
				return floatval(str_replace(",", ".", strval($a)));
			}

			if (is_string($a)) {
				static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
				return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
			} else {
				return $a;
			}
		}
		$isList = true;
		for ($i = 0, reset($a); $i < count($a); $i++, next($a)) {
			if (key($a) !== $i) {
				$isList = false;
				break;
			}
		}
		$result = array();
		if ($isList) {
			foreach ($a as $v) {
				$result[] = json_encode($v);
			}
			return '[' . join(',', $result) . ']';
		} else {
			foreach ($a as $k => $v) {
				$result[] = json_encode($k) . ':' . json_encode($v);
			}
			return '{' . join(',', $result) . '}';
		}
	}

}

function deQuote($value) {
	$QUOTE_TAG = "<quote>";
	$QUOTE_TAG_SIZE = strlen($QUOTE_TAG);
	$value = trim($value);
	$valSize = strlen($value);
	if ($valSize > $QUOTE_TAG_SIZE * 2 &&
					substr($value, 0, $QUOTE_TAG_SIZE) == $QUOTE_TAG &&
					substr($value, $valSize - $QUOTE_TAG_SIZE) == $QUOTE_TAG) {
		return substr($value, $QUOTE_TAG_SIZE, $valSize - $QUOTE_TAG_SIZE * 2);
	}
	return $value;
}

function parseConfig() {
	//Check if config file exists
	if (file_exists(getConfigFullName())) {
		$configStr = trim(file_get_contents(getConfigFullName()));

		if ($configStr != "") {
			$configLineArr = split("\n", $configStr); //Split config file into lines

			if (is_array($configLineArr) && count($configLineArr) > 1) {
				$config = array();

				for ($i = 0; $i < count($configLineArr); $i++) {

					if (strlen($configLineArr[$i]) > 1) {
						$configKeyValue = split(" ", $configLineArr[$i]); //Split config file lines into key/value pairs
						if (is_array($configKeyValue) && count($configKeyValue) > 1) {
							$config[strtolower($configKeyValue[0])] = deQuote($configKeyValue[1]);
						}
					}
				}
				return $config;
			}
		}
	}
}

function getInfo() {
	$info = array('version' => getVersion());
	$info['user'] = '';
	$passhash = '';
	$config = parseConfig();
	if (is_array($config)) {
		if (array_key_exists('username', $config)) {
			$info['user'] = $config['username'];
		}
		if (array_key_exists('password', $config)) {
			$passhash = getPasswordHash($config['password']);
		} else if (array_key_exists('hashedpassword', $config)) {
			$passhash = $config['hashedpassword'];
		}
		if (empty($passhash)) {
			$info['user'] = '';
		}
	}
	return $info;
}

function getAutoLoginToken() {
	$user = '';
	$passhash = '';

	$config = parseConfig();
	if (is_array($config)) {
		if (array_key_exists('username', $config)) {
			$user = $config['username'];
		}
		if (array_key_exists('password', $config)) {
			$passhash = getPasswordHash($config['password']);
		} else if (array_key_exists('hashedpassword', $config)) {
			$passhash = $config['hashedpassword'];
		}
	}

	date_default_timezone_set("UTC");

	$date = date("Y/m/d  H:i:s");
	$data = $user . "|" . $passhash . "|" . $date;

	return array(
			"user" => $user,
			"session" => strtoupper(base64_encode(hash_hmac("sha1", $data, $passhash, true))),
			"date" => $date,
			"dname" => getDevicename()
	);
}

function doLogin() {
	$user = getP('user');
	$pass = getP('pass');

//Config file contents
	$configStr = "UserName $user\n";
	if (!$savePasswordHash) {
		$configStr .= "Password <quote>$pass<quote>\n";
	} else {
		$configStr .= "HashedPassword " . getPasswordHash($pass) . "\n";
	}
	$configFile = getConfigFullName();
//Check if dir structure exists. mkdir if dir does not exist
	if (!file_exists($configFile)) {
		return '{ "success": false, "message": "Config file not found"}';
	}

//Write config file
	if (!file_put_contents($configFile, $configStr)) {
		return '{ "success": false, "message": "Can not write config file"}';
	}

	$retriesFRead = 0;
	$statusFile = getStatusFullName();
//Check few times if client writes a health file
	while (!file_exists($statusFile)) {

		$retriesFRead++;
		if ($retriesFRead >= 30) {
			break;
		}
		sleep(1);
	}

	$retriesFRead1 = 0;

	$statusFileStr = "";
//Check few times if health file status is successful login
	if (file_exists($statusFile)) {
		while (strpos($statusFileStr, "login successful") === FALSE) {

			$statusFileStr = file_get_contents($statusFile);

			$retriesFRead1++;
			if ($retriesFRead1 >= 30) {
				break;
			}
			sleep(1);
		}
	}

	if (function_exists('onLogin')) {
		onLogin();
	}
	$failureDetails = "";
	if (strpos($statusFileStr, "login successful") !== FALSE) {
		return '{ "success": true }';
	} else if (strpos($statusFileStr, "login failed") !== FALSE) {
		$statusFileArr = split("\n", $statusFileStr);
		if (is_array($statusFileArr) && count($statusFileArr) > 1) {
			$failureDetails = '. ' . ucfirst($statusFileArr[1]);
		}
	}

	return '{ "success": false, "message": "Login failed' . $failureDetails . '"}';
}

function doLogout() {
	if (function_exists('onLogout')) {
		onLogout();
	}
	return file_put_contents(getConfigFullName(), '') !== false;
}

//Input is plain password. Output is a formatted md5 hash
function getPasswordHash($pwd) {
	$hash = md5($pwd);
	$len = strlen($hash);
	for ($i = 0; $i < $len; $i = $i + 2) {
		$byte .= intval(substr($hash, $i, 2), 16);
	}
	return $byte;
}

function getSignupUrl() {
	$pwd = getP('pass');
	$len = strlen($pwd);
	if ($len < 6 || $len > 256) {
		return '{ "success": false, "message": "Password should be 6 or more charaters long"}';
	}

	return '{ "success": true, "url":"https://vault.elephantdrive.com/partners/vaultservices/genacct.aspx?a=reg&u=' . urlencode(getP('user')) . '&t=' . urlencode(getPasswordHash($pwd)) . '&c=' . getP('c') . '&format=json" }';
}

function err($code, $message = "") {
	switch ($code) {
		case 400: $text = 'Bad Request';
			$message = "Invalid request parameter $message";
			break;
		case 401: $text = 'Unauthorized';
			break;
		case 500: $text = 'Server error';
			break;
	}
	$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
	header($protocol . ' ' . $code . ' ' . $text);
	$GLOBALS['http_response_code'] = $code;
	die($message);
}

function printJSON($json) {
	header('Content-Type: application/json; charset=utf-8');
	if (is_array($json)) {
		print(json_encode($json));
	} else {
		print($json);
	}
}

function printLogFile() {
	header('Content-Type: text/plain; charset=utf-8');
	$logName = getLogFullName();
	if (file_exists($logName)) {
		print(file_get_contents($logName));
	} else {
		print('Log file not found');
	}
}

//q - query type
//  info
//  dologout
//  dologin
//  dosignup
//  log
//  authinfo
//  autologintoken
//user - user's e-mail
//pass - user's password
//c - partner id
function getP($param) {
	if ($param == 'q') {
		!empty($_REQUEST[$param]) or err(400, $param);
		in_array($_REQUEST[$param], array("info", "authinfo", "dologout", "dologin", "autologintoken", "getsignupurl", "log")) or err(400, $param);
		return $_REQUEST[$param];
	} elseif ($param == 'user') {
		!empty($_REQUEST[$param]) or err(400, $param);
		return $_REQUEST[$param];
	} elseif ($param == 'pass') {
		!empty($_REQUEST[$param]) or err(400, $param);
		return $_REQUEST[$param];
	} elseif ($param == 'c') {
		!empty($_REQUEST[$param]) or err(400, $param);
		return $_REQUEST[$param];
	}
}

function main() {
	ini_set('memory_limit', '-1');
	header('Cache-Control: no-cache, no-store, must-revalidate');
	header('Pragma: no-cache');
	header("Expires: 0");

	$query = getP('q');
	$query === 'authinfo' or checkNASauth() or err(401);
	switch ($query) {
//Get info
		case 'info':
			printJSON(getInfo());
			break;
//Get authinfo
		case 'authinfo':
			printJSON(array('url' => getNASLoginURL()));
			break;
//Login
		case 'dologin':
			printJSON(doLogin());
			break;
//Get service autologin token
		case 'autologintoken':
			printJSON(getAutoLoginToken());
			break;
//Logout
		case 'dologout':
			printJSON(array('success' => doLogout()));
			break;
//Signup
		case 'getsignupurl':
			printJSON(getSignupUrl());
			break;
//Download log     
		case 'log':
			printLogFile();
			break;
	}
}

main();
?>
