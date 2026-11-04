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
	$authHeader = getAuthHeader();
	if (preg_match('/Bearer\s+(\S+)/i', $authHeader, $matches)) {
		$payload = ApiToken::verify($matches[1]);
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
		$db->rawQuery("REPLACE INTO cron (nname, parent_clname, command, simple_cron, ttype, status) VALUES ('{$id}', 'client-admin', '{$escapedCmd}', '{$schedule}', 'simple', 'on');");

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

	jsonResponse(['error' => 'Not Found', 'message' => "Endpoint {$method} {$path} does not exist"], 404);

} catch (Throwable $e) {
	jsonResponse([
		'error' => 'Server Error',
		'message' => $e->getMessage()
	], 500);
}
