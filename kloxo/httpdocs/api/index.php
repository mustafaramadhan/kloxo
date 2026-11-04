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

function authenticateUser(): array
{
	$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
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

	jsonResponse(['error' => 'Not Found', 'message' => "Endpoint {$method} {$path} does not exist"], 404);

} catch (Throwable $e) {
	jsonResponse([
		'error' => 'Server Error',
		'message' => $e->getMessage()
	], 500);
}
