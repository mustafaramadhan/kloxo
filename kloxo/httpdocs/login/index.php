<?php
ini_set("display_errors", "0");

if (session_id() == "") {
    session_start();
}

$incfile = "./inc.php";
if (file_exists("./custom-inc.php")) {
    $incfile = "./custom-inc.php";
} elseif (file_exists("./custom.inc.php")) {
    $incfile = "./custom.inc.php";
}

$title = "Mfklixo - Cloud Control Panel Login";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-base: #0B0F19;
            --bg-surface: rgba(18, 24, 38, 0.75);
            --bg-surface-hover: rgba(26, 34, 53, 0.85);
            --border-subtle: rgba(255, 255, 255, 0.08);
            --border-focus: #3b82f6;
            --text-primary: #F8FAFC;
            --text-secondary: #94A3B8;
            --text-muted: #64748B;
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --primary-glow: rgba(37, 99, 235, 0.35);
            --success: #10B981;
            --danger: #EF4444;
            --radius-md: 10px;
            --radius-lg: 18px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--bg-base);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 24px 16px;
            position: relative;
            overflow-x: hidden;
        }

        /* Ambient glowing background */
        body::before {
            content: '';
            position: absolute;
            top: -20%;
            left: 50%;
            transform: translateX(-50%);
            width: 700px;
            height: 500px;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.18) 0%, rgba(16, 185, 129, 0.08) 45%, transparent 70%);
            filter: blur(80px);
            pointer-events: none;
            z-index: 0;
        }

        .auth-container {
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 1;
        }

        .brand-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .brand-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 58px;
            height: 58px;
            border-radius: 16px;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.2) 0%, rgba(30, 41, 59, 0.8) 100%);
            border: 1px solid rgba(255, 255, 255, 0.12);
            box-shadow: 0 10px 25px -5px var(--primary-glow);
            margin-bottom: 16px;
        }

        .brand-logo svg {
            width: 32px;
            height: 32px;
            color: #60a5fa;
        }

        .brand-title {
            font-size: 1.6rem;
            font-weight: 700;
            letter-spacing: -0.025em;
            color: var(--text-primary);
            margin-bottom: 6px;
        }

        .brand-subtitle {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .auth-card {
            background: var(--bg-surface);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-lg);
            padding: 36px 32px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.04);
        }

        .footer-note {
            text-align: center;
            margin-top: 24px;
            font-size: 0.8rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--text-secondary);
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border-subtle);
            padding: 3px 10px;
            border-radius: 9999px;
        }

        .status-dot {
            width: 7px;
            height: 7px;
            background-color: var(--success);
            border-radius: 50%;
            box-shadow: 0 0 8px var(--success);
        }
    </style>
    <script src="/theme/js/login.js"></script>
    <script src="/theme/js/lxa.js"></script>
</head>
<body>
    <div class="auth-container">
        <div class="brand-header">
            <div class="brand-logo">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect>
                    <rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect>
                    <line x1="6" y1="6" x2="6.01" y2="6"></line>
                    <line x1="6" y1="18" x2="6.01" y2="18"></line>
                </svg>
            </div>
            <h1 class="brand-title">Mfklixo Control Panel</h1>
            <p class="brand-subtitle">High-performance modern hosting management</p>
        </div>

        <div class="auth-card">
            <?php include_once $incfile; ?>
        </div>

        <div class="footer-note">
            <span class="status-badge"><span class="status-dot"></span> System Operational</span>
            <span>&bull;</span>
            <span>Mfklixo Cloud v7.0</span>
        </div>
    </div>
</body>
</html>
