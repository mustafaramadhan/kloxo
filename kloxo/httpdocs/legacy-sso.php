<?php
/**
 * Mfklixo Single Sign-On (SSO) Bridge
 * Seamlessly authenticates Next.js JWT sessions into Legacy Kloxo panel.
 */

declare(strict_types=1);

chdir(dirname(__FILE__));
include_once "lib/html/displayinclude.php";
require_once dirname(__FILE__) . '/api/jwt.php';

// 1. Extract token from Query string or Cookie
$token = (string)($_GET['token'] ?? $_COOKIE['kloxo_token'] ?? '');
$targetUrl = '/display.php?frm_action=show';

// Check if user already has an active legacy session file
$existingSession = $_COOKIE['kloxo-session-id'] ?? '';
if (!empty($existingSession) && empty($token)) {
    $sessFile = expand_real_root("__path_program_root/session/{$existingSession}");
    if (file_exists($sessFile)) {
        header("Location: {$targetUrl}");
        exit;
    }
}

// 2. Validate token
if (!empty($token)) {
    $payload = ApiToken::verify($token);
    if ($payload && !empty($payload['username'])) {
        $username = (string)$payload['username'];
        $sessId = randomString(50);
        $now = time();
        $cookietime = $now + 86400 * 30; // 30 days
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $tsess = randomString(30);

        // Ensure session directory exists and is writable
        $sessDir = expand_real_root("__path_program_root/session");
        if (!is_dir($sessDir)) {
            @mkdir($sessDir, 0777, true);
        }
        @chmod($sessDir, 0777);

        // Write file session
        $sessData = [
            'nname' => $sessId,
            'parent_clname' => 'client-' . $username,
            'cttype' => ($username === 'admin') ? 'admin' : 'client',
            'ip_address' => $ip,
            'timeout' => $now + 86400 * 30,
            'last_access' => $now,
            'logintime' => $now,
            'tsessionid' => $tsess,
            'dbaction' => 'clean',
            '__class' => 'ssession'
        ];
        @file_put_contents("{$sessDir}/{$sessId}", json_encode($sessData));
        @chmod("{$sessDir}/{$sessId}", 0666);

        // Also write to MariaDB ssession table for complete compatibility
        try {
            $db = new Sqlite(null, 'ssession');
            $escapedSess = addslashes($sessId);
            $escapedParent = addslashes('client-' . $username);
            $escapedType = ($username === 'admin') ? 'admin' : 'client';
            $escapedIp = addslashes($ip);
            $timeoutVal = $now + 86400 * 30;
            $db->rawQuery("REPLACE INTO ssession (nname, parent_clname, cttype, ip_address, timeout, last_access, logintime, tsessionid) VALUES ('{$escapedSess}', '{$escapedParent}', '{$escapedType}', '{$escapedIp}', '{$timeoutVal}', '{$now}', '{$now}', '{$tsess}');");
        } catch (Throwable $e) {
            // DB session record is optional as ssession uses file storage
        }

        // Set cookies for legacy Kloxo (SameSite Lax, accessible on all paths)
        setcookie('kloxo-clientname', $username, $cookietime, '/');
        setcookie('kloxo-classname', 'client', $cookietime, '/');
        setcookie('kloxo-session-id', $sessId, $cookietime, '/');
        setcookie('kloxo_token', $token, $cookietime, '/');

        // Redirect to Legacy Control Panel
        header("Location: {$targetUrl}");
        exit;
    }
}

// 3. Fallback: If not authenticated, redirect to login
header('Location: /login/');
exit;
