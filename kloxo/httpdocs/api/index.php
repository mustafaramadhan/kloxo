<?php
/**
 * Mfklixo High-Performance JSON REST API Gateway
 * Designed with Ponytail minimal code & zero unnecessary dependencies.
 */

declare(strict_types=1);

chdir(dirname(__FILE__) . '/..');
include_once "lib/html/include.php";
require_once dirname(__FILE__) . '/jwt.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type, Accept');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
	http_response_code(200);
	exit;
}

function jsonResponse(array $data, int $status = 200): void
{
	http_response_code($status);
	echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	exit;
}

function getJsonInput(): array
{
	$raw = file_get_contents('php://input');
	if (empty($raw)) {
		return [];
	}
	$decoded = json_decode($raw, true);
	return is_array($decoded) ? $decoded : [];
}

function getAuthHeader(): string
{
	if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
		return $_SERVER['HTTP_AUTHORIZATION'];
	}
	if (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
		return $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
	}
	if (function_exists('apache_request_headers')) {
		$headers = (array)apache_request_headers();
		foreach ($headers as $k => $v) {
			if (strcasecmp($k, 'Authorization') === 0) {
				return (string)$v;
			}
		}
	}
	if (!empty($_GET['token'])) {
		return 'Bearer ' . $_GET['token'];
	}
	return '';
}

function authenticateUser(): array
{
	$token = null;
	$authHeader = getAuthHeader();
	if (preg_match('/Bearer\s+(\S+)/i', $authHeader, $matches)) {
		$token = $matches[1];
	} elseif (!empty($_GET['token'])) {
		$token = (string)$_GET['token'];
	}

	if ($token) {
		$payload = ApiToken::verify($token);
		if ($payload && isset($payload['username'])) {
			return $payload;
		}
	}
	jsonResponse(['error' => 'Unauthorized', 'message' => 'Valid Bearer token required'], 401);
	return [];
}

// Simple REST routing based on request URI
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Normalize URI prefix
$prefix = '/api/v1';
if (strpos($uri, $prefix) !== 0) {
	jsonResponse(['error' => 'Not Found', 'message' => "API endpoint must start with {$prefix}"], 404);
}

$path = substr($uri, strlen($prefix));
if ($path === '' || $path === '/') {
	jsonResponse([
		'name' => 'Mfklixo REST API',
		'version' => '1.0.0',
		'status' => 'operational'
	]);
}

try {
	// ==========================================
	// 1. Authentication Endpoints
	// ==========================================
	if ($path === '/auth/login' && $method === 'POST') {
		$input = getJsonInput();
		$username = trim((string)($input['username'] ?? ''));
		$password = (string)($input['password'] ?? '');

		if ($username === '' || $password === '') {
			jsonResponse(['error' => 'Bad Request', 'message' => 'Username and password required'], 400);
		}

		if (check_raw_password('client', $username, $password)) {
			$token = ApiToken::generate([
				'username' => $username,
				'role' => ($username === 'admin') ? 'superadmin' : 'client'
			], 86400 * 7); // 7 days

			jsonResponse([
				'success' => true,
				'token' => $token,
				'user' => [
					'username' => $username,
					'role' => ($username === 'admin') ? 'superadmin' : 'client'
				]
			]);
		} else {
			jsonResponse(['error' => 'Unauthorized', 'message' => 'Invalid username or password'], 401);
		}
	}

	if ($path === '/auth/me' && $method === 'GET') {
		$user = authenticateUser();
		jsonResponse(['success' => true, 'user' => $user]);
	}

	// ==========================================
	// 2. System & Server Monitoring
	// ==========================================
	if ($path === '/system/status' && $method === 'GET') {
		authenticateUser();

		// System Load & Uptime
		$loads = sys_getloadavg() ?: [0, 0, 0];
		$uptimeSec = 0;
		if (file_exists('/proc/uptime')) {
			$uptimeData = explode(' ', (string)file_get_contents('/proc/uptime'));
			$uptimeSec = (int)($uptimeData[0] ?? 0);
		}

		// Memory info
		$memTotal = 0;
		$memFree = 0;
		$memAvailable = 0;
		if (file_exists('/proc/meminfo')) {
			$memLines = file('/proc/meminfo', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
			foreach ($memLines as $line) {
				if (preg_match('/^MemTotal:\s+(\d+)\s+kB/i', $line, $m)) $memTotal = (int)$m[1] * 1024;
				if (preg_match('/^MemFree:\s+(\d+)\s+kB/i', $line, $m)) $memFree = (int)$m[1] * 1024;
				if (preg_match('/^MemAvailable:\s+(\d+)\s+kB/i', $line, $m)) $memAvailable = (int)$m[1] * 1024;
			}
		}
		$memUsed = $memTotal - ($memAvailable ?: $memFree);
		$memPercent = ($memTotal > 0) ? round(($memUsed / $memTotal) * 100, 1) : 0;

		// Disk info
		$diskTotal = disk_total_space('/') ?: 1;
		$diskFree = disk_free_space('/') ?: 0;
		$diskUsed = $diskTotal - $diskFree;
		$diskPercent = round(($diskUsed / $diskTotal) * 100, 1);

		// CPU Cores & Estimation
		$cpuCores = 1;
		if (file_exists('/proc/cpuinfo')) {
			$cpuCores = substr_count((string)file_get_contents('/proc/cpuinfo'), 'processor');
			if ($cpuCores < 1) $cpuCores = 1;
		}
		$cpuPercent = round(min(100, ($loads[0] / $cpuCores) * 100), 1);

		jsonResponse([
			'success' => true,
			'system' => [
				'hostname' => gethostname() ?: 'localhost',
				'os' => php_uname('s') . ' ' . php_uname('r'),
				'uptime_seconds' => $uptimeSec,
				'load_average' => [
					'1m' => round($loads[0], 2),
					'5m' => round($loads[1], 2),
					'15m' => round($loads[2], 2)
				],
				'cpu' => [
					'cores' => $cpuCores,
					'percent' => $cpuPercent
				],
				'memory' => [
					'total_bytes' => $memTotal,
					'used_bytes' => $memUsed,
					'free_bytes' => $memAvailable ?: $memFree,
					'percent' => $memPercent
				],
				'disk' => [
					'total_bytes' => (int)$diskTotal,
					'used_bytes' => (int)$diskUsed,
					'free_bytes' => (int)$diskFree,
					'percent' => $diskPercent
				]
			]
		]);
	}

	if ($path === '/system/services' && $method === 'GET') {
		authenticateUser();

		$monitored = [
			'nginx' => 'Nginx Web Server',
			'apache2' => 'Apache Web Server',
			'mariadb' => 'MariaDB SQL Server',
			'php8.5-fpm' => 'PHP FastCGI Daemon',
			'kloxo-wrap' => 'Kloxo Core Daemon',
			'named' => 'BIND9 DNS Server',
			'ssh' => 'SSH Server'
		];

		$services = [];
		foreach ($monitored as $name => $title) {
			$statusCmd = "systemctl is-active {$name} 2>/dev/null";
			$state = trim((string)shell_exec($statusCmd));
			$services[] = [
				'service' => $name,
				'title' => $title,
				'active' => ($state === 'active'),
				'state' => $state ?: 'unknown'
			];
		}

		jsonResponse(['success' => true, 'services' => $services]);
	}

	if ($path === '/system/services/action' && $method === 'POST') {
		$user = authenticateUser();
		if (($user['role'] ?? '') !== 'superadmin') {
			jsonResponse(['error' => 'Forbidden', 'message' => 'Only superadmin can manage services'], 403);
		}

		$input = getJsonInput();
		$svc = trim((string)($input['service'] ?? ''));
		$action = trim((string)($input['action'] ?? 'restart'));

		$allowedServices = ['nginx', 'apache2', 'mariadb', 'php8.5-fpm', 'kloxo-wrap', 'kloxo-web', 'named'];
		$allowedActions = ['restart', 'start', 'stop'];

		if (!in_array($svc, $allowedServices, true) || !in_array($action, $allowedActions, true)) {
			jsonResponse(['error' => 'Bad Request', 'message' => 'Invalid service or action'], 400);
		}

		exec("sudo systemctl {$action} {$svc} 2>&1", $out, $retCode);
		jsonResponse([
			'success' => ($retCode === 0),
			'service' => $svc,
			'action' => $action,
			'output' => implode("\n", $out)
		]);
	}

	// ==========================================
	// 3. Domains Management
	// ==========================================
	if ($path === '/domains' && $method === 'GET') {
		authenticateUser();

		initProgram('admin');
		$db = new Sqlite(null, 'domain');
		$rows = $db->getTable(['nname', 'parent_clname', 'status', 'webpserver', 'dnspserver', 'mmailpserver']);

		$domains = [];
		foreach ((array)$rows as $r) {
			$domains[] = [
				'domain' => $r['nname'] ?? '',
				'owner' => $r['parent_clname'] ?? 'admin',
				'status' => ($r['status'] ?? 'on') === 'on',
				'web_server' => $r['webpserver'] ?? 'localhost',
				'dns_server' => $r['dnspserver'] ?? 'localhost',
				'mail_server' => $r['mmailpserver'] ?? 'localhost'
			];
		}

		jsonResponse(['success' => true, 'domains' => $domains]);
	}

	if ($path === '/domains' && $method === 'POST') {
		authenticateUser();
		$input = getJsonInput();
		$domain = strtolower(trim((string)($input['domain'] ?? '')));

		if (!preg_match('/^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?(\.[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?)+$/i', $domain)) {
			jsonResponse(['error' => 'Bad Request', 'message' => 'Invalid domain format'], 400);
		}

		$docroot = "/home/admin/{$domain}/public_html";
		if (!is_dir($docroot)) {
			@mkdir($docroot, 0755, true);
			@file_put_contents("{$docroot}/index.html", "<!DOCTYPE html><html><head><title>{$domain}</title></head><body style='font-family:sans-serif;text-align:center;padding:50px'><h1>{$domain}</h1><p>Website created successfully with Mfklixo Next-Gen Panel.</p></body></html>");
		}

		$vhostDir = "/home/kloxo/httpd";
		if (!is_dir($vhostDir)) @mkdir($vhostDir, 0755, true);
		$vhost = "server {\n    listen 80;\n    server_name {$domain} www.{$domain};\n    root {$docroot};\n    index index.php index.html;\n    location ~ \\.php$ {\n        include /etc/nginx/fastcgi_params;\n        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;\n        fastcgi_pass unix:/usr/local/lxlabs/kloxo/init/kloxo-phpcgi.sock;\n    }\n}\n";
		@file_put_contents("{$vhostDir}/{$domain}.conf", $vhost);
		@shell_exec("sudo systemctl reload nginx 2>/dev/null");

		initProgram('admin');
		$db = new Sqlite(null, 'domain');
		$db->rawQuery("REPLACE INTO domain (nname, parent_clname, status, webpserver, dnspserver, mmailpserver) VALUES ('{$domain}', 'client-admin', 'on', 'localhost', 'localhost', 'localhost');");

		jsonResponse(['success' => true, 'message' => "Domain {$domain} created successfully", 'domain' => $domain]);
	}

	if ($path === '/domains' && $method === 'DELETE') {
		authenticateUser();
		$input = getJsonInput();
		$domain = strtolower(trim((string)($input['domain'] ?? $_GET['domain'] ?? '')));

		if (empty($domain)) {
			jsonResponse(['error' => 'Bad Request', 'message' => 'Domain parameter required'], 400);
		}

		initProgram('admin');
		$db = new Sqlite(null, 'domain');
		$db->delRow('nname', $domain);

		@unlink("/home/kloxo/httpd/{$domain}.conf");
		@shell_exec("sudo systemctl reload nginx 2>/dev/null");

		jsonResponse(['success' => true, 'message' => "Domain {$domain} deleted successfully"]);
	}

	// ==========================================
	// 4. Databases Management
	// ==========================================
	if ($path === '/databases' && $method === 'GET') {
		authenticateUser();

		initProgram('admin');
		$db = new Sqlite(null, 'mysqldb');
		$rows = $db->getTable(['nname', 'dbname', 'username', 'parent_clname', 'used_q_mysqldb_usage']);

		$dbs = [];
		foreach ((array)$rows as $r) {
			$dbs[] = [
				'name' => $r['dbname'] ?: $r['nname'],
				'username' => $r['username'] ?? '',
				'owner' => $r['parent_clname'] ?? 'admin',
				'usage_mb' => round(((float)($r['used_q_mysqldb_usage'] ?? 0)) / 1024, 2)
			];
		}

		jsonResponse(['success' => true, 'databases' => $dbs]);
	}

	if ($path === '/databases' && $method === 'POST') {
		authenticateUser();
		$input = getJsonInput();
		$name = preg_replace('/[^a-zA-Z0-9_]/', '', (string)($input['name'] ?? ''));
		$dbuser = preg_replace('/[^a-zA-Z0-9_]/', '', (string)($input['username'] ?? $name));
		$password = (string)($input['password'] ?? '');

		if (empty($name) || empty($password)) {
			jsonResponse(['error' => 'Bad Request', 'message' => 'Database name and password required'], 400);
		}

		initProgram('admin');
		$db = new Sqlite(null, 'mysqldb');
		$escapedPass = addslashes($password);

		$db->rawQuery("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
		$db->rawQuery("CREATE USER IF NOT EXISTS '{$dbuser}'@'localhost' IDENTIFIED BY '{$escapedPass}';");
		$db->rawQuery("ALTER USER '{$dbuser}'@'localhost' IDENTIFIED BY '{$escapedPass}';");
		$db->rawQuery("GRANT ALL PRIVILEGES ON `{$name}`.* TO '{$dbuser}'@'localhost';");
		$db->rawQuery("FLUSH PRIVILEGES;");

		$db->rawQuery("REPLACE INTO mysqldb (nname, dbname, username, parent_clname, syncserver, dbtype) VALUES ('{$name}', '{$name}', '{$dbuser}', 'client-admin', 'localhost', 'mysql');");

		jsonResponse(['success' => true, 'message' => "Database {$name} created successfully", 'database' => $name]);
	}

	if ($path === '/databases' && $method === 'DELETE') {
		authenticateUser();
		$input = getJsonInput();
		$name = preg_replace('/[^a-zA-Z0-9_]/', '', (string)($input['name'] ?? $_GET['name'] ?? ''));

		if (empty($name)) {
			jsonResponse(['error' => 'Bad Request', 'message' => 'Database name required'], 400);
		}

		if (in_array(strtolower($name), ['kloxo', 'mysql', 'information_schema', 'performance_schema', 'sys'], true)) {
			jsonResponse(['error' => 'Forbidden', 'message' => 'System databases cannot be deleted'], 403);
		}

		initProgram('admin');
		$db = new Sqlite(null, 'mysqldb');
		$rows = $db->getRows('nname', $name);
		$u = $rows[0]['username'] ?? '';

		$db->rawQuery("DROP DATABASE IF EXISTS `{$name}`;");
		if ($u && !in_array($u, ['root', 'kloxo'], true)) {
			$db->rawQuery("DROP USER IF EXISTS '{$u}'@'localhost';");
		}
		$db->delRow('nname', $name);

		jsonResponse(['success' => true, 'message' => "Database {$name} deleted successfully"]);
	}

	// ==========================================
	// 5. Network & IP Addresses
	// ==========================================
	if ($path === '/network/ips' && $method === 'GET') {
		authenticateUser();

		initProgram('admin');
		$db = new Sqlite(null, 'ipaddress');
		$rows = $db->getTable(['nname', 'devname', 'ipaddr', 'netmask', 'status']);

		$ips = [];
		foreach ((array)$rows as $r) {
			$ips[] = [
				'interface' => $r['devname'] ?? '',
				'ip' => $r['ipaddr'] ?? '',
				'netmask' => $r['netmask'] ?? '',
				'status' => ($r['status'] ?? 'on') === 'on'
			];
		}

		jsonResponse(['success' => true, 'ips' => $ips]);
	}

	// ==========================================
	// 6. Mail Accounts Management
	// ==========================================
	if ($path === '/mail/accounts' && $method === 'GET') {
		authenticateUser();

		initProgram('admin');
		$db = new Sqlite(null, 'mailaccount');
		$rows = $db->getTable(['nname', 'parent_clname', 'status', 'used_q_maildisk_usage', 'priv_q_maildisk_usage']);

		$accounts = [];
		foreach ((array)$rows as $r) {
			$accounts[] = [
				'email' => $r['nname'] ?? '',
				'owner' => $r['parent_clname'] ?? 'admin',
				'status' => ($r['status'] ?? 'on') === 'on',
				'used_mb' => round(((float)($r['used_q_maildisk_usage'] ?? 0)) / 1024, 2),
				'quota_mb' => ((float)($r['priv_q_maildisk_usage'] ?? 0)) > 0 ? round(((float)$r['priv_q_maildisk_usage']) / 1024, 2) : 'unlimited'
			];
		}

		jsonResponse(['success' => true, 'accounts' => $accounts]);
	}

	if ($path === '/mail/accounts' && $method === 'POST') {
		authenticateUser();
		$input = getJsonInput();
		$email = strtolower(trim((string)($input['email'] ?? '')));
		$password = (string)($input['password'] ?? '');
		$quota = (int)($input['quota_mb'] ?? 500);

		if (!filter_var($email, FILTER_VALIDATE_EMAIL) || empty($password)) {
			jsonResponse(['error' => 'Bad Request', 'message' => 'Valid email address and password required'], 400);
		}

		initProgram('admin');
		$db = new Sqlite(null, 'mailaccount');
		$escapedPass = addslashes($password);
		$db->rawQuery("REPLACE INTO mailaccount (nname, parent_clname, status, realpass, password, priv_q_maildisk_usage, syncserver) VALUES ('{$email}', 'client-admin', 'on', '{$escapedPass}', md5('{$escapedPass}'), '{$quota}', 'localhost');");

		jsonResponse(['success' => true, 'message' => "Mailbox {$email} created successfully", 'email' => $email]);
	}

	if ($path === '/mail/accounts' && $method === 'DELETE') {
		authenticateUser();
		$input = getJsonInput();
		$email = strtolower(trim((string)($input['email'] ?? $_GET['email'] ?? '')));

		if (empty($email)) {
			jsonResponse(['error' => 'Bad Request', 'message' => 'Email address required'], 400);
		}

		initProgram('admin');
		$db = new Sqlite(null, 'mailaccount');
		$db->delRow('nname', $email);

		jsonResponse(['success' => true, 'message' => "Mailbox {$email} deleted successfully"]);
	}

	// ==========================================
	// 7. FTP Accounts Management
	// ==========================================
	if ($path === '/ftp/users' && $method === 'GET') {
		authenticateUser();

		initProgram('admin');
		$db = new Sqlite(null, 'ftpuser');
		$rows = $db->getTable(['nname', 'directory', 'status', 'parent_clname', 'ftp_disk_usage']);

		$users = [];
		foreach ((array)$rows as $r) {
			$users[] = [
				'username' => $r['nname'] ?? '',
				'directory' => $r['directory'] ?? '/home/admin',
				'owner' => $r['parent_clname'] ?? 'admin',
				'status' => ($r['status'] ?? 'on') === 'on',
				'disk_usage_mb' => round(((float)($r['ftp_disk_usage'] ?? 0)) / 1024, 2)
			];
		}

		jsonResponse(['success' => true, 'users' => $users]);
	}

	if ($path === '/ftp/users' && $method === 'POST') {
		authenticateUser();
		$input = getJsonInput();
		$username = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($input['username'] ?? ''));
		$password = (string)($input['password'] ?? '');
		$directory = (string)($input['directory'] ?? '/home/admin');

		if (empty($username) || empty($password)) {
			jsonResponse(['error' => 'Bad Request', 'message' => 'Username and password required'], 400);
		}

		initProgram('admin');
		$db = new Sqlite(null, 'ftpuser');
		$escapedPass = addslashes($password);
		$escapedDir = addslashes($directory);
		$db->rawQuery("REPLACE INTO ftpuser (nname, parent_clname, status, realpass, directory, syncserver) VALUES ('{$username}', 'client-admin', 'on', '{$escapedPass}', '{$escapedDir}', 'localhost');");

		jsonResponse(['success' => true, 'message' => "FTP user {$username} created successfully", 'username' => $username]);
	}

	if ($path === '/ftp/users' && $method === 'DELETE') {
		authenticateUser();
		$input = getJsonInput();
		$username = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($input['username'] ?? $_GET['username'] ?? ''));

		if (empty($username)) {
			jsonResponse(['error' => 'Bad Request', 'message' => 'Username required'], 400);
		}

		initProgram('admin');
		$db = new Sqlite(null, 'ftpuser');
		$db->delRow('nname', $username);

		jsonResponse(['success' => true, 'message' => "FTP user {$username} deleted successfully"]);
	}

	// ==========================================
	// 8. Clients & Resellers Management
	// ==========================================
	if ($path === '/clients' && $method === 'GET') {
		authenticateUser();

		initProgram('admin');
		$db = new Sqlite(null, 'client');
		$rows = $db->getTable(['nname', 'cttype', 'contactemail', 'status', 'used_q_domain_num', 'priv_q_domain_num', 'used_q_clientdisk_usage']);

		$clients = [];
		foreach ((array)$rows as $r) {
			$clients[] = [
				'username' => $r['nname'] ?? '',
				'type' => $r['cttype'] ?? 'customer',
				'email' => $r['contactemail'] ?? '',
				'status' => ($r['status'] ?? 'on') === 'on',
				'domains_used' => (int)($r['used_q_domain_num'] ?? 0),
				'domains_quota' => ($r['priv_q_domain_num'] ?? '') === 'Infinity' || empty($r['priv_q_domain_num']) ? 'Unlimited' : $r['priv_q_domain_num'],
				'disk_used_mb' => round(((float)($r['used_q_clientdisk_usage'] ?? 0)) / 1024, 2)
			];
		}

		jsonResponse(['success' => true, 'clients' => $clients]);
	}

	if ($path === '/clients' && $method === 'POST') {
		authenticateUser();
		$input = getJsonInput();
		$username = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($input['username'] ?? ''));
		$password = (string)($input['password'] ?? '');
		$email = (string)($input['email'] ?? '');
		$type = in_array($input['type'] ?? '', ['reseller', 'customer']) ? $input['type'] : 'customer';

		if (empty($username) || empty($password)) {
			jsonResponse(['error' => 'Bad Request', 'message' => 'Username and password required'], 400);
		}

		initProgram('admin');
		$db = new Sqlite(null, 'client');
		$escapedPass = addslashes($password);
		$db->rawQuery("REPLACE INTO client (nname, parent_clname, cttype, contactemail, status, realpass, password) VALUES ('{$username}', 'client-admin', '{$type}', '{$email}', 'on', '{$escapedPass}', md5('{$escapedPass}'));");

		jsonResponse(['success' => true, 'message' => "Client {$username} created successfully", 'username' => $username]);
	}

	if ($path === '/clients' && $method === 'DELETE') {
		authenticateUser();
		$input = getJsonInput();
		$username = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($input['username'] ?? $_GET['username'] ?? ''));

		if ($username === 'admin') {
			jsonResponse(['error' => 'Forbidden', 'message' => 'Cannot delete master admin account'], 403);
		}

		initProgram('admin');
		$db = new Sqlite(null, 'client');
		$db->delRow('nname', $username);

		jsonResponse(['success' => true, 'message' => "Client {$username} deleted successfully"]);
	}

	// ==========================================
	// 9. Scheduled Tasks (Cron Jobs)
	// ==========================================
	if ($path === '/cron/jobs' && $method === 'GET') {
		authenticateUser();

		initProgram('admin');
		$db = new Sqlite(null, 'cron');
		$rows = $db->getTable(['nname', 'parent_clname', 'command', 'argument', 'ttype', 'simple_cron', 'cron_day_hour']);

		$jobs = [];
		foreach ((array)$rows as $r) {
			$jobs[] = [
				'id' => $r['nname'] ?? '',
				'owner' => $r['parent_clname'] ?? 'admin',
				'command' => trim(($r['command'] ?? '') . ' ' . ($r['argument'] ?? '')),
				'type' => $r['ttype'] ?? 'simple',
				'schedule' => $r['simple_cron'] ?? ($r['cron_day_hour'] ?? 'daily')
			];
		}

		jsonResponse(['success' => true, 'jobs' => $jobs]);
	}

	if ($path === '/cron/jobs' && $method === 'POST') {
		authenticateUser();
		$input = getJsonInput();
		$command = trim((string)($input['command'] ?? ''));
		$schedule = trim((string)($input['schedule'] ?? 'daily'));

		if (empty($command)) {
			jsonResponse(['error' => 'Bad Request', 'message' => 'Command required'], 400);
		}

		$id = 'cron_' . substr(md5($command . time()), 0, 8);
		initProgram('admin');
		$db = new Sqlite(null, 'cron');
		$escapedCmd = addslashes($command);
		$db->rawQuery("REPLACE INTO cron (nname, parent_clname, command, simple_cron, ttype, username) VALUES ('{$id}', 'client-admin', '{$escapedCmd}', '{$schedule}', 'simple', 'admin');");

		jsonResponse(['success' => true, 'message' => 'Cron job created successfully', 'id' => $id]);
	}

	if ($path === '/cron/jobs' && $method === 'DELETE') {
		authenticateUser();
		$input = getJsonInput();
		$id = trim((string)($input['id'] ?? $_GET['id'] ?? ''));

		if (empty($id)) {
			jsonResponse(['error' => 'Bad Request', 'message' => 'Cron ID required'], 400);
		}

		initProgram('admin');
		$db = new Sqlite(null, 'cron');
		$db->delRow('nname', $id);

		jsonResponse(['success' => true, 'message' => "Cron task {$id} deleted successfully"]);
	}

	// ==========================================
	// 10. File Manager Endpoints
	// ==========================================
	if ($path === '/files' && $method === 'GET') {
		authenticateUser();

		$requestedPath = (string)($_GET['path'] ?? '/home/admin');
		$baseRoot = '/home';
		$realPath = realpath($requestedPath);

		if (!$realPath || strpos($realPath, $baseRoot) !== 0) {
			$realPath = '/home/admin';
			if (!is_dir($realPath)) {
				@mkdir($realPath, 0755, true);
			}
		}

		$items = [];
		$entries = @scandir($realPath) ?: [];

		foreach ($entries as $entry) {
			if ($entry === '.') continue;
			$full = $realPath . '/' . $entry;
			$isDir = is_dir($full);
			$items[] = [
				'name' => $entry,
				'path' => $full,
				'is_directory' => $isDir,
				'size_bytes' => $isDir ? 0 : (@filesize($full) ?: 0),
				'permissions' => substr(sprintf('%o', @fileperms($full) ?: 0), -4),
				'modified' => @filemtime($full) ? date('Y-m-d H:i:s', filemtime($full)) : ''
			];
		}

		// Sort directories first, then alphabetical
		usort($items, function ($a, $b) {
			if ($a['is_directory'] === $b['is_directory']) {
				return strcasecmp($a['name'], $b['name']);
			}
			return $a['is_directory'] ? -1 : 1;
		});

		jsonResponse([
			'success' => true,
			'current_path' => $realPath,
			'parent_path' => ($realPath !== '/home' && dirname($realPath) !== $realPath) ? dirname($realPath) : null,
			'items' => $items
		]);
	}

	if ($path === '/files/create' && $method === 'POST') {
		authenticateUser();
		$input = getJsonInput();
		$targetPath = (string)($input['path'] ?? '/home/admin');
		$name = basename(trim((string)($input['name'] ?? '')));
		$type = (string)($input['type'] ?? 'file');

		$real = realpath($targetPath);
		if (!$real || strpos($real, '/home') !== 0) {
			jsonResponse(['error' => 'Forbidden', 'message' => 'Invalid destination directory'], 403);
		}

		$dest = $real . '/' . $name;
		if ($type === 'directory') {
			$res = @mkdir($dest, 0755, true);
		} else {
			$res = @touch($dest);
		}

		jsonResponse([
			'success' => ($res !== false),
			'message' => ($type === 'directory' ? 'Folder' : 'File') . " {$name} created successfully",
			'path' => $dest
		]);
	}

	if ($path === '/files' && $method === 'DELETE') {
		authenticateUser();
		$input = getJsonInput();
		$filePath = (string)($input['path'] ?? $_GET['path'] ?? '');
		$real = realpath($filePath);

		if (!$real || strpos($real, '/home') !== 0 || in_array($real, ['/home', '/home/admin', '/home/kloxo'], true)) {
			jsonResponse(['error' => 'Forbidden', 'message' => 'Cannot delete system-critical directory'], 403);
		}

		if (is_dir($real)) {
			$res = @rmdir($real);
			if (!$res) {
				@shell_exec("rm -rf " . escapeshellarg($real));
				$res = !file_exists($real);
			}
		} else {
			$res = @unlink($real);
		}

		jsonResponse(['success' => $res, 'message' => "Item deleted successfully"]);
	}

	if ($path === '/files/content' && $method === 'POST') {
		authenticateUser();
		$input = getJsonInput();
		$filePath = (string)($input['path'] ?? '');
		$real = realpath($filePath);

		if (!$real || strpos($real, '/home') !== 0 || !is_file($real)) {
			jsonResponse(['error' => 'Not Found', 'message' => 'File does not exist or access denied'], 404);
		}

		// Avoid reading huge binary files
		$size = filesize($real);
		if ($size > 5 * 1024 * 1024) {
			jsonResponse(['error' => 'File Too Large', 'message' => 'Files larger than 5MB cannot be opened in editor'], 400);
		}

		$content = file_get_contents($real);
		jsonResponse([
			'success' => true,
			'path' => $real,
			'size' => $size,
			'content' => $content
		]);
	}

	if ($path === '/files/save' && $method === 'POST') {
		$user = authenticateUser();
		$input = getJsonInput();
		$filePath = (string)($input['path'] ?? '');
		$content = (string)($input['content'] ?? '');
		$real = realpath(dirname($filePath));

		if (!$real || strpos($real, '/home') !== 0) {
			jsonResponse(['error' => 'Forbidden', 'message' => 'Invalid path destination'], 403);
		}

		$dest = $real . '/' . basename($filePath);
		$res = file_put_contents($dest, $content);

		jsonResponse([
			'success' => ($res !== false),
			'path' => $dest,
			'bytes_written' => $res ?: 0
		]);
	}

	if ($path === '/files/upload' && $method === 'POST') {
		authenticateUser();
		$targetDir = (string)($_POST['target_dir'] ?? '/home/admin');
		$real = realpath($targetDir);
		if (!$real || strpos($real, '/home') !== 0 || !is_dir($real)) {
			jsonResponse(['error' => 'Forbidden', 'message' => 'Invalid destination directory'], 403);
		}

		if (!empty($_FILES['file']['tmp_name'])) {
			$name = basename($_FILES['file']['name']);
			$dest = $real . '/' . $name;
			if (move_uploaded_file($_FILES['file']['tmp_name'], $dest)) {
				@chown($dest, 'www-data');
				jsonResponse(['success' => true, 'message' => "File {$name} uploaded successfully", 'path' => $dest]);
			}
		}

		$input = getJsonInput();
		if (!empty($input['filename']) && isset($input['content_base64'])) {
			$name = basename($input['filename']);
			$dest = $real . '/' . $name;
			$decoded = base64_decode((string)$input['content_base64']);
			if (file_put_contents($dest, $decoded) !== false) {
				@chown($dest, 'www-data');
				jsonResponse(['success' => true, 'message' => "File {$name} uploaded successfully", 'path' => $dest]);
			}
		}

		jsonResponse(['error' => 'Bad Request', 'message' => 'No valid file uploaded'], 400);
	}

	if ($path === '/files/download' && $method === 'GET') {
		authenticateUser();
		$filePath = (string)($_GET['path'] ?? '');
		$real = realpath($filePath);
		if (!$real || strpos($real, '/home') !== 0 || !is_file($real)) {
			jsonResponse(['error' => 'Not Found', 'message' => 'File not found or access denied'], 404);
		}

		$filename = basename($real);
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Content-Length: ' . filesize($real));
		header('Cache-Control: no-cache, must-revalidate');
		readfile($real);
		exit;
	}

	if ($path === '/files/extract' && $method === 'POST') {
		authenticateUser();
		$input = getJsonInput();
		$archivePath = (string)($input['path'] ?? '');
		$destDir = (string)($input['destination'] ?? dirname($archivePath));

		$realArchive = realpath($archivePath);
		$realDest = realpath($destDir);

		if (!$realArchive || strpos($realArchive, '/home') !== 0 || !is_file($realArchive)) {
			jsonResponse(['error' => 'Not Found', 'message' => 'Archive file not found'], 404);
		}
		if (!$realDest || strpos($realDest, '/home') !== 0 || !is_dir($realDest)) {
			jsonResponse(['error' => 'Forbidden', 'message' => 'Invalid extract destination'], 403);
		}

		$escapedArchive = escapeshellarg($realArchive);
		$escapedDest = escapeshellarg($realDest);

		if (preg_match('/\.zip$/i', $realArchive)) {
			$cmd = "unzip -o {$escapedArchive} -d {$escapedDest} 2>&1";
		} elseif (preg_match('/\.(tar\.gz|tgz)$/i', $realArchive)) {
			$cmd = "tar -xzf {$escapedArchive} -C {$escapedDest} 2>&1";
		} elseif (preg_match('/\.tar$/i', $realArchive)) {
			$cmd = "tar -xf {$escapedArchive} -C {$escapedDest} 2>&1";
		} else {
			jsonResponse(['error' => 'Unsupported', 'message' => 'Format not supported. Must be .zip, .tar.gz, or .tar'], 400);
		}

		$output = shell_exec($cmd);
		jsonResponse(['success' => true, 'message' => 'Archive extracted successfully', 'output' => $output]);
	}

	if ($path === '/files/compress' && $method === 'POST') {
		authenticateUser();
		$input = getJsonInput();
		$files = (array)($input['files'] ?? []);
		$archiveName = basename((string)($input['archive_name'] ?? 'archive'));
		$destDir = (string)($input['destination'] ?? '/home/admin');
		$format = strtolower((string)($input['format'] ?? 'zip'));

		$realDest = realpath($destDir);
		if (!$realDest || strpos($realDest, '/home') !== 0 || !is_dir($realDest)) {
			jsonResponse(['error' => 'Forbidden', 'message' => 'Invalid destination directory'], 403);
		}

		$escapedFiles = [];
		foreach ($files as $f) {
			$rf = realpath($f);
			if ($rf && strpos($rf, '/home') === 0) {
				$escapedFiles[] = escapeshellarg(basename($rf));
			}
		}

		if (empty($escapedFiles)) {
			jsonResponse(['error' => 'Bad Request', 'message' => 'No valid files selected'], 400);
		}

		$fileList = implode(' ', $escapedFiles);
		$firstFileDir = dirname(realpath($files[0]));

		if ($format === 'tar.gz') {
			$finalName = preg_replace('/(\.tar\.gz|\.zip)?$/i', '', $archiveName) . '.tar.gz';
			$targetFile = $realDest . '/' . $finalName;
			$cmd = "cd " . escapeshellarg($firstFileDir) . " && tar -czf " . escapeshellarg($targetFile) . " {$fileList} 2>&1";
		} else {
			$finalName = preg_replace('/(\.tar\.gz|\.zip)?$/i', '', $archiveName) . '.zip';
			$targetFile = $realDest . '/' . $finalName;
			$cmd = "cd " . escapeshellarg($firstFileDir) . " && zip -r " . escapeshellarg($targetFile) . " {$fileList} 2>&1";
		}

		shell_exec($cmd);
		jsonResponse(['success' => file_exists($targetFile), 'message' => "Archive {$finalName} created", 'path' => $targetFile]);
	}

	if ($path === '/files/rename' && $method === 'POST') {
		authenticateUser();
		$input = getJsonInput();
		$targetPath = (string)($input['path'] ?? '');
		$newName = basename((string)($input['new_name'] ?? ''));

		$real = realpath($targetPath);
		if (!$real || strpos($real, '/home') !== 0 || empty($newName)) {
			jsonResponse(['error' => 'Forbidden', 'message' => 'Invalid target path'], 403);
		}

		$dest = dirname($real) . '/' . $newName;
		$res = @rename($real, $dest);
		jsonResponse(['success' => $res, 'message' => "Renamed to {$newName}", 'new_path' => $dest]);
	}

	if ($path === '/files/move' && $method === 'POST') {
		authenticateUser();
		$input = getJsonInput();
		$source = (string)($input['source'] ?? '');
		$targetDir = (string)($input['destination'] ?? '');

		$realSource = realpath($source);
		$realDestDir = realpath($targetDir);

		if (!$realSource || strpos($realSource, '/home') !== 0) {
			jsonResponse(['error' => 'Forbidden', 'message' => 'Invalid source path'], 403);
		}
		if (!$realDestDir || strpos($realDestDir, '/home') !== 0 || !is_dir($realDestDir)) {
			jsonResponse(['error' => 'Forbidden', 'message' => 'Invalid destination directory'], 403);
		}

		$finalDest = $realDestDir . '/' . basename($realSource);
		$res = @rename($realSource, $finalDest);
		jsonResponse(['success' => $res, 'message' => 'Item moved successfully', 'new_path' => $finalDest]);
	}

	if ($path === '/files/copy' && $method === 'POST') {
		authenticateUser();
		$input = getJsonInput();
		$source = (string)($input['source'] ?? '');
		$targetDir = (string)($input['destination'] ?? '');

		$realSource = realpath($source);
		$realDestDir = realpath($targetDir);

		if (!$realSource || strpos($realSource, '/home') !== 0) {
			jsonResponse(['error' => 'Forbidden', 'message' => 'Invalid source path'], 403);
		}
		if (!$realDestDir || strpos($realDestDir, '/home') !== 0 || !is_dir($realDestDir)) {
			jsonResponse(['error' => 'Forbidden', 'message' => 'Invalid destination directory'], 403);
		}

		$finalDest = $realDestDir . '/' . basename($realSource);
		if (is_dir($realSource)) {
			@shell_exec("cp -r " . escapeshellarg($realSource) . " " . escapeshellarg($finalDest));
			$res = is_dir($finalDest);
		} else {
			$res = @copy($realSource, $finalDest);
		}

		jsonResponse(['success' => (bool)$res, 'message' => 'Item copied successfully', 'new_path' => $finalDest]);
	}

	if ($path === '/files/permissions' && $method === 'POST') {
		authenticateUser();
		$input = getJsonInput();
		$filePath = (string)($input['path'] ?? '');
		$perms = (string)($input['permissions'] ?? '0755');

		$real = realpath($filePath);
		if (!$real || strpos($real, '/home') !== 0) {
			jsonResponse(['error' => 'Forbidden', 'message' => 'Invalid path'], 403);
		}

		$mode = octdec(str_pad(ltrim($perms, '0'), 3, '0', STR_PAD_LEFT));
		$res = @chmod($real, $mode);

		jsonResponse(['success' => (bool)$res, 'message' => "Permissions updated to {$perms}"]);
	}

	// ==========================================
	// 11. System Logs & PHP Configuration
	// ==========================================
	if ($path === '/system/logs' && $method === 'GET') {
		authenticateUser();

		$service = (string)($_GET['service'] ?? 'nginx');
		$logMap = [
			'nginx' => '/var/log/nginx/error.log',
			'php' => '/var/log/php8.5-fpm.log',
			'kloxo' => '/usr/local/lxlabs/kloxo/log/kloxo-panel-error.log'
		];

		$targetFile = $logMap[$service] ?? $logMap['nginx'];
		$lines = [];

		if (file_exists($targetFile) && is_readable($targetFile)) {
			$raw = shell_exec("tail -n 100 " . escapeshellarg($targetFile)) ?: '';
			$lines = explode("\n", trim($raw));
		}

		jsonResponse([
			'success' => true,
			'service' => $service,
			'file' => $targetFile,
			'lines' => $lines
		]);
	}

	if ($path === '/system/phpinfo' && $method === 'GET') {
		authenticateUser();

		jsonResponse([
			'success' => true,
			'php' => [
				'version' => PHP_VERSION,
				'sapi' => PHP_SAPI,
				'memory_limit' => ini_get('memory_limit'),
				'max_execution_time' => ini_get('max_execution_time'),
				'upload_max_filesize' => ini_get('upload_max_filesize'),
				'post_max_size' => ini_get('post_max_size'),
				'opcache_enabled' => function_exists('opcache_get_status') && (bool)opcache_get_status(),
				'extensions' => get_loaded_extensions()
			]
		]);
	}

	// ==========================================
	// 12. DNS Management (Zones & Records)
	// ==========================================
	if ($path === '/dns/zones' && $method === 'GET') {
		authenticateUser();
		initProgram('admin');
		$db = new Sqlite(null, 'dns');
		$dnsRows = (array)$db->getTable(['nname', 'soanameserver', 'ttl', 'zone_type']);

		$domDb = new Sqlite(null, 'domain');
		$domains = (array)$domDb->getTable(['nname']);

		$zones = [];
		$seen = [];
		foreach ($dnsRows as $r) {
			$name = $r['nname'] ?? '';
			if (!$name || isset($seen[$name])) continue;
			$seen[$name] = true;
			$zones[] = [
				'domain' => $name,
				'soanameserver' => $r['soanameserver'] ?: "ns1.{$name}",
				'ttl' => (int)($r['ttl'] ?: 3600),
				'zone_type' => $r['zone_type'] ?: 'master'
			];
		}
		foreach ($domains as $d) {
			$name = $d['nname'] ?? '';
			if (!$name || isset($seen[$name])) continue;
			$seen[$name] = true;
			$zones[] = [
				'domain' => $name,
				'soanameserver' => "ns1.{$name}",
				'ttl' => 3600,
				'zone_type' => 'master'
			];
		}

		jsonResponse(['success' => true, 'zones' => $zones]);
	}

	if ($path === '/dns/zones' && $method === 'POST') {
		authenticateUser();
		$input = getJsonInput();
		$domain = preg_replace('/[^a-zA-Z0-9_.-]/', '', (string)($input['domain'] ?? ''));
		$ttl = (int)($input['ttl'] ?? 3600);
		if (empty($domain)) {
			jsonResponse(['error' => 'Bad Request', 'message' => 'Domain name required'], 400);
		}

		initProgram('admin');
		$db = new Sqlite(null, 'dns');
		$soa = "ns1.{$domain}";
		$db->rawQuery("REPLACE INTO dns (nname, parent_clname, soanameserver, ttl, zone_type, syncserver) VALUES ('{$domain}', 'client-admin', '{$soa}', '{$ttl}', 'master', 'localhost');");

		jsonResponse(['success' => true, 'message' => "DNS zone {$domain} created successfully", 'domain' => $domain]);
	}

	if ($path === '/dns/zones' && $method === 'DELETE') {
		authenticateUser();
		$input = getJsonInput();
		$domain = preg_replace('/[^a-zA-Z0-9_.-]/', '', (string)($input['domain'] ?? $_GET['domain'] ?? ''));
		if (empty($domain)) {
			jsonResponse(['error' => 'Bad Request', 'message' => 'Domain required'], 400);
		}

		initProgram('admin');
		$db = new Sqlite(null, 'dns');
		$db->delRow('nname', $domain);
		@unlink("/home/kloxo/dns/{$domain}.json");

		jsonResponse(['success' => true, 'message' => "DNS zone {$domain} deleted successfully"]);
	}

	if ($path === '/dns/records' && $method === 'GET') {
		authenticateUser();
		$zone = preg_replace('/[^a-zA-Z0-9_.-]/', '', (string)($_GET['zone'] ?? ''));
		if (empty($zone)) {
			jsonResponse(['error' => 'Bad Request', 'message' => 'Zone required'], 400);
		}

		@mkdir('/home/kloxo/dns', 0755, true);
		$recFile = "/home/kloxo/dns/{$zone}.json";
		if (file_exists($recFile)) {
			$records = json_decode((string)file_get_contents($recFile), true) ?: [];
		} else {
			$records = [
				['id' => '1', 'type' => 'A', 'hostname' => '@', 'value' => '18.222.232.63', 'ttl' => 3600],
				['id' => '2', 'type' => 'A', 'hostname' => 'www', 'value' => '18.222.232.63', 'ttl' => 3600],
				['id' => '3', 'type' => 'A', 'hostname' => 'mail', 'value' => '18.222.232.63', 'ttl' => 3600],
				['id' => '4', 'type' => 'CNAME', 'hostname' => 'ftp', 'value' => '@', 'ttl' => 3600],
				['id' => '5', 'type' => 'MX', 'hostname' => '@', 'value' => "mail.{$zone}", 'priority' => 10, 'ttl' => 3600],
				['id' => '6', 'type' => 'TXT', 'hostname' => '@', 'value' => 'v=spf1 a mx ~all', 'ttl' => 3600]
			];
			@file_put_contents($recFile, json_encode($records, JSON_PRETTY_PRINT));
		}

		jsonResponse(['success' => true, 'zone' => $zone, 'records' => $records]);
	}

	if ($path === '/dns/records' && $method === 'POST') {
		authenticateUser();
		$input = getJsonInput();
		$zone = preg_replace('/[^a-zA-Z0-9_.-]/', '', (string)($input['zone'] ?? ''));
		$type = strtoupper(preg_replace('/[^a-zA-Z]/', '', (string)($input['type'] ?? 'A')));
		$hostname = trim((string)($input['hostname'] ?? '@'));
		$value = trim((string)($input['value'] ?? ''));
		$priority = (int)($input['priority'] ?? 10);
		$ttl = (int)($input['ttl'] ?? 3600);

		if (empty($zone) || empty($value)) {
			jsonResponse(['error' => 'Bad Request', 'message' => 'Zone and value required'], 400);
		}

		@mkdir('/home/kloxo/dns', 0755, true);
		$recFile = "/home/kloxo/dns/{$zone}.json";
		$records = file_exists($recFile) ? (json_decode((string)file_get_contents($recFile), true) ?: []) : [];

		$newRec = [
			'id' => (string)time() . '_' . rand(100, 999),
			'type' => $type,
			'hostname' => $hostname,
			'value' => $value,
			'priority' => $priority,
			'ttl' => $ttl
		];
		$records[] = $newRec;
		@file_put_contents($recFile, json_encode($records, JSON_PRETTY_PRINT));

		jsonResponse(['success' => true, 'message' => 'Record added successfully', 'record' => $newRec]);
	}

	if ($path === '/dns/records' && $method === 'DELETE') {
		authenticateUser();
		$input = getJsonInput();
		$zone = preg_replace('/[^a-zA-Z0-9_.-]/', '', (string)($input['zone'] ?? ''));
		$id = (string)($input['id'] ?? '');

		$recFile = "/home/kloxo/dns/{$zone}.json";
		if (file_exists($recFile)) {
			$records = json_decode((string)file_get_contents($recFile), true) ?: [];
			$records = array_values(array_filter($records, fn($r) => ($r['id'] ?? '') !== $id));
			@file_put_contents($recFile, json_encode($records, JSON_PRETTY_PRINT));
		}

		jsonResponse(['success' => true, 'message' => 'Record deleted successfully']);
	}

	// ==========================================
	// 13. SSL / TLS Certificate Management
	// ==========================================
	if ($path === '/ssl/certificates' && $method === 'GET') {
		authenticateUser();
		initProgram('admin');
		$db = new Sqlite(null, 'sslcert');
		$rows = (array)$db->getTable(['nname', 'certname', 'parent_domain', 'upload_status']);

		$certs = [];
		foreach ($rows as $r) {
			$domain = $r['parent_domain'] ?: $r['nname'];
			$certs[] = [
				'id' => $r['nname'],
				'certname' => $r['certname'] ?: $r['nname'],
				'domain' => $domain,
				'upload_status' => $r['upload_status'] ?: 'active',
				'has_crt' => file_exists("/home/kloxo/ssl/{$domain}.crt"),
				'has_key' => file_exists("/home/kloxo/ssl/{$domain}.key")
			];
		}

		jsonResponse(['success' => true, 'certificates' => $certs]);
	}

	if ($path === '/ssl/certificates' && $method === 'POST') {
		authenticateUser();
		$input = getJsonInput();
		$domain = preg_replace('/[^a-zA-Z0-9_.-]/', '', (string)($input['domain'] ?? ''));
		$certname = preg_replace('/[^a-zA-Z0-9_.-]/', '', (string)($input['certname'] ?? $domain));
		$isSelfSigned = !empty($input['generate_self_signed']);
		$crt = (string)($input['crt'] ?? '');
		$key = (string)($input['key'] ?? '');

		if (empty($domain)) {
			jsonResponse(['error' => 'Bad Request', 'message' => 'Domain name required'], 400);
		}

		@mkdir('/home/kloxo/ssl', 0755, true);
		$crtPath = "/home/kloxo/ssl/{$domain}.crt";
		$keyPath = "/home/kloxo/ssl/{$domain}.key";

		if ($isSelfSigned) {
			$cmd = "openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout " . escapeshellarg($keyPath) . " -out " . escapeshellarg($crtPath) . " -subj '/CN={$domain}/O=Mfklixo' 2>&1";
			@shell_exec($cmd);
		} else {
			@file_put_contents($crtPath, $crt);
			@file_put_contents($keyPath, $key);
		}

		initProgram('admin');
		$db = new Sqlite(null, 'sslcert');
		$escapedCertname = addslashes($certname);
		$escapedDomain = addslashes($domain);
		$db->rawQuery("REPLACE INTO sslcert (nname, certname, parent_clname, parent_domain, upload_status, syncserver) VALUES ('{$escapedCertname}', '{$escapedCertname}', 'client-admin', '{$escapedDomain}', 'active', 'localhost');");

		jsonResponse(['success' => true, 'message' => "Certificate for {$domain} configured successfully", 'domain' => $domain]);
	}

	if ($path === '/ssl/certificates' && $method === 'DELETE') {
		authenticateUser();
		$input = getJsonInput();
		$id = preg_replace('/[^a-zA-Z0-9_.-]/', '', (string)($input['id'] ?? $input['certname'] ?? ''));

		if (empty($id)) {
			jsonResponse(['error' => 'Bad Request', 'message' => 'Certificate identifier required'], 400);
		}

		initProgram('admin');
		$db = new Sqlite(null, 'sslcert');
		$db->delRow('nname', $id);
		@unlink("/home/kloxo/ssl/{$id}.crt");
		@unlink("/home/kloxo/ssl/{$id}.key");

		jsonResponse(['success' => true, 'message' => "Certificate {$id} deleted successfully"]);
	}

	// ==========================================
	// 14. Firewall & Blocked IPs
	// ==========================================
	if ($path === '/security/firewall' && $method === 'GET') {
		authenticateUser();
		initProgram('admin');
		$db = new Sqlite(null, 'firewall');
		$rows = (array)$db->getTable(['nname', 'from_port', 'to_port', 'action', 'status']);

		$rules = [];
		if (empty($rows)) {
			$defaults = [
				['id' => 'rule_80', 'port' => 80, 'action' => 'allow', 'protocol' => 'TCP', 'description' => 'HTTP Web Server'],
				['id' => 'rule_443', 'port' => 443, 'action' => 'allow', 'protocol' => 'TCP', 'description' => 'HTTPS Secure Web'],
				['id' => 'rule_22', 'port' => 22, 'action' => 'allow', 'protocol' => 'TCP', 'description' => 'SSH Remote Access'],
				['id' => 'rule_21', 'port' => 21, 'action' => 'allow', 'protocol' => 'TCP', 'description' => 'FTP File Transfer'],
				['id' => 'rule_7777', 'port' => 7777, 'action' => 'allow', 'protocol' => 'TCP', 'description' => 'Kloxo Modern Dashboard (SSL)'],
				['id' => 'rule_7778', 'port' => 7778, 'action' => 'allow', 'protocol' => 'TCP', 'description' => 'Kloxo Dashboard (HTTP)'],
				['id' => 'rule_3306', 'port' => 3306, 'action' => 'allow', 'protocol' => 'TCP', 'description' => 'MariaDB / MySQL Server']
			];
			foreach ($defaults as $d) {
				$db->rawQuery("REPLACE INTO firewall (nname, parent_clname, from_port, to_port, action, status, syncserver) VALUES ('{$d['id']}', 'client-admin', '{$d['port']}', '{$d['port']}', '{$d['action']}', 'on', 'localhost');");
				$rules[] = [
					'id' => $d['id'],
					'port' => $d['port'],
					'action' => $d['action'],
					'protocol' => $d['protocol'],
					'status' => true,
					'description' => $d['description']
				];
			}
		} else {
			foreach ($rows as $r) {
				$rules[] = [
					'id' => $r['nname'],
					'port' => (int)($r['from_port'] ?? 0),
					'action' => $r['action'] ?? 'allow',
					'protocol' => 'TCP',
					'status' => ($r['status'] ?? 'on') === 'on',
					'description' => "Port " . ($r['from_port'] ?? '') . " traffic"
				];
			}
		}

		jsonResponse(['success' => true, 'rules' => $rules]);
	}

	if ($path === '/security/firewall' && $method === 'POST') {
		authenticateUser();
		$input = getJsonInput();
		$port = (int)($input['port'] ?? 0);
		$action = ($input['action'] ?? 'allow') === 'deny' ? 'deny' : 'allow';
		$protocol = strtoupper(trim((string)($input['protocol'] ?? 'TCP')));
		$desc = trim((string)($input['description'] ?? "Port {$port}"));

		if ($port <= 0 || $port > 65535) {
			jsonResponse(['error' => 'Bad Request', 'message' => 'Valid port number (1-65535) required'], 400);
		}

		$id = "rule_{$port}_" . strtolower($protocol);
		initProgram('admin');
		$db = new Sqlite(null, 'firewall');
		$db->rawQuery("REPLACE INTO firewall (nname, parent_clname, from_port, to_port, action, status, syncserver) VALUES ('{$id}', 'client-admin', '{$port}', '{$port}', '{$action}', 'on', 'localhost');");

		$ruleFlag = $action === 'deny' ? '-j DROP' : '-j ACCEPT';
		@shell_exec("sudo iptables -I INPUT -p " . strtolower($protocol) . " --dport {$port} {$ruleFlag} 2>/dev/null");

		jsonResponse(['success' => true, 'message' => "Firewall rule for port {$port} created successfully", 'id' => $id]);
	}

	if ($path === '/security/firewall' && $method === 'DELETE') {
		authenticateUser();
		$input = getJsonInput();
		$id = trim((string)($input['id'] ?? ''));

		if (empty($id)) {
			jsonResponse(['error' => 'Bad Request', 'message' => 'Rule ID required'], 400);
		}

		initProgram('admin');
		$db = new Sqlite(null, 'firewall');
		$db->delRow('nname', $id);

		jsonResponse(['success' => true, 'message' => "Firewall rule {$id} deleted successfully"]);
	}

	if ($path === '/security/blocked-ips' && $method === 'GET') {
		authenticateUser();
		initProgram('admin');
		$db = new Sqlite(null, 'blockedip');
		$rows = (array)$db->getTable(['nname', 'ipaddress']);

		$ips = [];
		foreach ($rows as $r) {
			$ip = $r['ipaddress'] ?: $r['nname'];
			if ($ip) {
				$ips[] = ['id' => $r['nname'], 'ip' => $ip];
			}
		}

		jsonResponse(['success' => true, 'blocked_ips' => $ips]);
	}

	if ($path === '/security/blocked-ips' && $method === 'POST') {
		authenticateUser();
		$input = getJsonInput();
		$ip = trim((string)($input['ip'] ?? ''));

		if (!filter_var($ip, FILTER_VALIDATE_IP)) {
			jsonResponse(['error' => 'Bad Request', 'message' => 'Valid IPv4 or IPv6 required'], 400);
		}

		$id = "block_" . md5($ip);
		initProgram('admin');
		$db = new Sqlite(null, 'blockedip');
		$db->rawQuery("REPLACE INTO blockedip (nname, parent_clname, ipaddress) VALUES ('{$id}', 'client-admin', '{$ip}');");
		@shell_exec("sudo iptables -A INPUT -s " . escapeshellarg($ip) . " -j DROP 2>/dev/null");

		jsonResponse(['success' => true, 'message' => "IP {$ip} blocked successfully", 'ip' => $ip]);
	}

	if ($path === '/security/blocked-ips' && $method === 'DELETE') {
		authenticateUser();
		$input = getJsonInput();
		$ip = trim((string)($input['ip'] ?? ''));

		initProgram('admin');
		$db = new Sqlite(null, 'blockedip');
		$db->delRow('ipaddress', $ip);
		$db->delRow('nname', "block_" . md5($ip));
		@shell_exec("sudo iptables -D INPUT -s " . escapeshellarg($ip) . " -j DROP 2>/dev/null");

		jsonResponse(['success' => true, 'message' => "IP {$ip} unblocked successfully"]);
	}

	// ==========================================
	// 15. Server Backups & Snapshots
	// ==========================================
	if ($path === '/backups' && $method === 'GET') {
		authenticateUser();
		@mkdir('/home/kloxo/backups', 0755, true);
		$files = glob('/home/kloxo/backups/*.tar.gz') ?: [];

		$backups = [];
		foreach ($files as $f) {
			$backups[] = [
				'filename' => basename($f),
				'path' => $f,
				'size_bytes' => filesize($f),
				'size_formatted' => round(filesize($f) / (1024 * 1024), 2) . ' MB',
				'created_at' => date('Y-m-d H:i:s', filemtime($f))
			];
		}

		usort($backups, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));

		jsonResponse(['success' => true, 'backups' => $backups]);
	}

	if ($path === '/backups/create' && $method === 'POST') {
		authenticateUser();
		$input = getJsonInput();
		$type = (string)($input['type'] ?? 'all');
		$timestamp = date('Y-m-d_His');
		$filename = "backup_{$type}_{$timestamp}.tar.gz";
		$dest = "/home/kloxo/backups/{$filename}";

		@mkdir('/home/kloxo/backups', 0755, true);

		if ($type === 'databases') {
			$cmd = "sudo mysqldump -u root -p'Daya@2024' --all-databases 2>/dev/null | gzip > " . escapeshellarg($dest);
		} else {
			$cmd = "sudo tar -czf " . escapeshellarg($dest) . " /home/admin /home/kloxo/httpd 2>/dev/null";
		}

		@shell_exec($cmd);
		@chown($dest, 'www-data');

		$size = file_exists($dest) ? filesize($dest) : 0;

		jsonResponse([
			'success' => true,
			'message' => "Backup archive {$filename} created successfully",
			'filename' => $filename,
			'size_bytes' => $size,
			'size_formatted' => round($size / (1024 * 1024), 2) . ' MB'
		]);
	}

	if ($path === '/backups' && $method === 'DELETE') {
		authenticateUser();
		$input = getJsonInput();
		$filename = basename((string)($input['filename'] ?? ''));

		if (empty($filename)) {
			jsonResponse(['error' => 'Bad Request', 'message' => 'Filename required'], 400);
		}

		$target = "/home/kloxo/backups/{$filename}";
		if (file_exists($target)) {
			@unlink($target);
		}

		jsonResponse(['success' => true, 'message' => "Backup {$filename} deleted successfully"]);
	}

	// ==========================================
	// 16. PHP Configuration Management
	// ==========================================
	if ($path === '/system/php-config' && $method === 'GET') {
		authenticateUser();

		jsonResponse([
			'success' => true,
			'php_version' => PHP_VERSION,
			'directives' => [
				'memory_limit' => ini_get('memory_limit') ?: '256M',
				'upload_max_filesize' => ini_get('upload_max_filesize') ?: '64M',
				'post_max_size' => ini_get('post_max_size') ?: '64M',
				'max_execution_time' => ini_get('max_execution_time') ?: '300',
				'max_input_time' => ini_get('max_input_time') ?: '300',
				'display_errors' => ini_get('display_errors') ? 'On' : 'Off'
			],
			'extensions' => get_loaded_extensions()
		]);
	}

	if ($path === '/system/php-config' && $method === 'POST') {
		authenticateUser();
		$input = getJsonInput();
		$mem = preg_replace('/[^0-9MGmg]/', '', (string)($input['memory_limit'] ?? '256M'));
		$upload = preg_replace('/[^0-9MGmg]/', '', (string)($input['upload_max_filesize'] ?? '64M'));
		$post = preg_replace('/[^0-9MGmg]/', '', (string)($input['post_max_size'] ?? '64M'));
		$exec = (int)($input['max_execution_time'] ?? 300);
		$display = ($input['display_errors'] ?? 'Off') === 'On' ? 'On' : 'Off';

		$iniContent = <<<INI
; Mfklixo Panel Custom PHP Configuration
memory_limit = {$mem}
upload_max_filesize = {$upload}
post_max_size = {$post}
max_execution_time = {$exec}
max_input_time = {$exec}
display_errors = {$display}
INI;

		@file_put_contents('/etc/php/8.5/fpm/conf.d/99-kloxo-custom.ini', $iniContent);
		@file_put_contents('/etc/php.ini', $iniContent);
		@shell_exec("sudo systemctl restart kloxo-php 2>/dev/null");

		jsonResponse(['success' => true, 'message' => 'PHP configuration updated and applied successfully']);
	}

	// ==========================================
	// 17. Subdomains Management
	// ==========================================
	if ($path === '/subdomains' && $method === 'GET') {
		authenticateUser();
		initProgram('admin');
		$db = new Sqlite(null, 'domain');
		$rows = (array)$db->getTable(['nname', 'parent_clname', 'status', 'subdomain_parent']);

		$subdomains = [];
		foreach ($rows as $r) {
			$name = $r['nname'] ?? '';
			if (!empty($r['subdomain_parent']) || substr_count($name, '.') >= 2) {
				$subdomains[] = [
					'subdomain' => $name,
					'parent_domain' => $r['subdomain_parent'] ?: preg_replace('/^[^\.]+\./', '', $name),
					'docroot' => "/home/admin/{$name}/public_html",
					'status' => ($r['status'] ?? 'on') === 'on'
				];
			}
		}

		jsonResponse(['success' => true, 'subdomains' => $subdomains]);
	}

	if ($path === '/subdomains' && $method === 'POST') {
		authenticateUser();
		$input = getJsonInput();
		$prefix = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($input['prefix'] ?? ''));
		$parent = preg_replace('/[^a-zA-Z0-9_.-]/', '', (string)($input['parent_domain'] ?? ''));

		if (empty($prefix) || empty($parent)) {
			jsonResponse(['error' => 'Bad Request', 'message' => 'Subdomain prefix and parent domain required'], 400);
		}

		$fullSubdomain = "{$prefix}.{$parent}";
		$docroot = "/home/admin/{$fullSubdomain}/public_html";
		@mkdir($docroot, 0755, true);
		@file_put_contents("{$docroot}/index.html", "<!DOCTYPE html><html><head><title>{$fullSubdomain}</title></head><body style='font-family:sans-serif;text-align:center;padding:50px;'><h1>Subdomain {$fullSubdomain} is Active</h1><p>Hosted by Mfklixo Next-Gen Control Panel</p></body></html>");
		@chown("/home/admin/{$fullSubdomain}", 'www-data');

		$vhost = "server {
	listen 80;
	server_name {$fullSubdomain};
	root {$docroot};
	index index.html index.php;

	location / {
		try_files \$uri \$uri/ /index.php?\$args;
	}

	location ~ \.php$ {
		fastcgi_pass unix:/var/run/kloxo-php-fpm.sock;
		fastcgi_index index.php;
		include fastcgi_params;
		fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
	}
}";
		@file_put_contents("/home/kloxo/httpd/{$fullSubdomain}.conf", $vhost);
		@shell_exec("sudo systemctl reload nginx 2>/dev/null");

		initProgram('admin');
		$db = new Sqlite(null, 'domain');
		$escapedSub = addslashes($fullSubdomain);
		$escapedParent = addslashes($parent);
		$db->rawQuery("REPLACE INTO domain (nname, parent_clname, status, subdomain_parent, webpserver) VALUES ('{$escapedSub}', 'client-admin', 'on', '{$escapedParent}', 'localhost');");

		jsonResponse(['success' => true, 'message' => "Subdomain {$fullSubdomain} created successfully", 'subdomain' => $fullSubdomain]);
	}

	if ($path === '/subdomains' && $method === 'DELETE') {
		authenticateUser();
		$input = getJsonInput();
		$subdomain = preg_replace('/[^a-zA-Z0-9_.-]/', '', (string)($input['subdomain'] ?? $_GET['subdomain'] ?? ''));

		initProgram('admin');
		$db = new Sqlite(null, 'domain');
		$db->delRow('nname', $subdomain);
		@unlink("/home/kloxo/httpd/{$subdomain}.conf");
		@shell_exec("sudo systemctl reload nginx 2>/dev/null");

		jsonResponse(['success' => true, 'message' => "Subdomain {$subdomain} deleted successfully"]);
	}

	// ==========================================
	// 18. Web Database SQL Runner
	// ==========================================
	if ($path === '/databases/query' && $method === 'POST') {
		authenticateUser();
		$input = getJsonInput();
		$dbName = preg_replace('/[^a-zA-Z0-9_]/', '', (string)($input['database'] ?? ''));
		$sql = trim((string)($input['query'] ?? ''));

		if (empty($dbName) || empty($sql)) {
			jsonResponse(['error' => 'Bad Request', 'message' => 'Database name and SQL query required'], 400);
		}

		$startTime = microtime(true);
		$passFile = '/usr/local/lxlabs/kloxo/etc/conf/kloxo.pass';
		$dbPass = file_exists($passFile) ? trim((string)file_get_contents($passFile)) : 'FCayldtdUInsugVe';
		$conn = @new mysqli('localhost', 'kloxo', $dbPass, $dbName);
		if ($conn->connect_error) {
			jsonResponse(['error' => 'Connection Failed', 'message' => $conn->connect_error], 500);
		}

		$result = $conn->query($sql);
		$duration = round((microtime(true) - $startTime) * 1000, 2);

		if ($result === false) {
			$errMsg = $conn->error;
			$conn->close();
			jsonResponse(['error' => 'SQL Error', 'message' => $errMsg], 400);
		}

		if ($result === true) {
			$affected = $conn->affected_rows;
			$conn->close();
			jsonResponse([
				'success' => true,
				'type' => 'mutation',
				'affected_rows' => $affected,
				'duration_ms' => $duration,
				'message' => "Query executed successfully. ({$affected} rows affected in {$duration} ms)"
			]);
		}

		$columns = [];
		$fields = $result->fetch_fields();
		foreach ($fields as $f) {
			$columns[] = $f->name;
		}

		$rows = [];
		$count = 0;
		while ($row = $result->fetch_assoc()) {
			$rows[] = $row;
			$count++;
			if ($count >= 200) break;
		}
		$conn->close();

		jsonResponse([
			'success' => true,
			'type' => 'select',
			'columns' => $columns,
			'rows' => $rows,
			'total_rows' => $count,
			'duration_ms' => $duration
		]);
	}

	// ==========================================
	// 19. Email Forwarders
	// ==========================================
	if ($path === '/mail/forwarders' && $method === 'GET') {
		authenticateUser();
		initProgram('admin');
		$db = new Sqlite(null, 'mailforward');
		$rows = (array)$db->getTable(['nname', 'parent_clname', 'forwardaddress']);

		$forwarders = [];
		foreach ($rows as $r) {
			$forwarders[] = [
				'id' => $r['nname'],
				'source' => $r['nname'],
				'destination' => $r['forwardaddress'] ?? ''
			];
		}
		jsonResponse(['success' => true, 'forwarders' => $forwarders]);
	}

	if ($path === '/mail/forwarders' && $method === 'POST') {
		authenticateUser();
		$input = getJsonInput();
		$source = trim((string)($input['source'] ?? ''));
		$destination = trim((string)($input['destination'] ?? ''));

		if (empty($source) || empty($destination)) {
			jsonResponse(['error' => 'Bad Request', 'message' => 'Source and destination email required'], 400);
		}

		initProgram('admin');
		$db = new Sqlite(null, 'mailforward');
		$escapedDest = addslashes($destination);
		$db->rawQuery("REPLACE INTO mailforward (nname, parent_clname, forwardaddress, syncserver) VALUES ('{$source}', 'client-admin', '{$escapedDest}', 'localhost');");

		jsonResponse(['success' => true, 'message' => "Forwarder from {$source} to {$destination} added successfully"]);
	}

	if ($path === '/mail/forwarders' && $method === 'DELETE') {
		authenticateUser();
		$input = getJsonInput();
		$id = trim((string)($input['id'] ?? $input['source'] ?? ''));

		initProgram('admin');
		$db = new Sqlite(null, 'mailforward');
		$db->delRow('nname', $id);

		jsonResponse(['success' => true, 'message' => "Forwarder deleted successfully"]);
	}

	jsonResponse(['error' => 'Not Found', 'message' => "Endpoint {$method} {$path} does not exist"], 404);

} catch (Throwable $e) {
	jsonResponse([
		'error' => 'Server Error',
		'message' => $e->getMessage()
	], 500);
}
