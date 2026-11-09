<?php
chdir("..");
include_once "lib/html/displayinclude.php";

init_language();

$cgi_clientname = $ghtml->frm_clientname;
$cgi_class = $ghtml->frm_class;
$cgi_password = $ghtml->frm_password;
$cgi_forgotpwd = $ghtml->frm_forgotpwd;
$cgi_email = $ghtml->frm_email;
$cgi_token = $ghtml->frm_token;

$cgi_classname = 'client';
if ($cgi_class) {
    $cgi_classname = $cgi_class;
}

if ($sgbl->is_this_slave()) {
    print("<div class='alert alert-danger'>Slave Server: Please operate from Master server.</div>\n");
    exit;
}

if (!isset($_SESSION)) {
    session_start();
}

$_SESSION['frm_token'] = mt_rand();

// Check failed attempts timeout
$blocked_msg = "";
if ((isset($_SESSION['last_login_time'])) && (isset($_SESSION['num_login_fail']))) {
    $t = time();
    $s = $_SESSION['last_login_time'];
    $d = $t - $s;
    $n = $_SESSION['num_login_fail'];

    if ($n >= 5) {
        if ($d < 600) {
            $remaining = 600 - $d;
            $blocked_msg = "<div class='alert alert-danger'>Too many failed login attempts. Please wait <span id='countdown'>" . $remaining . "s</span> before retrying.</div>";
        } else {
            $_SESSION['num_login_fail'] = 0;
        }
    } else {
        $_SESSION['last_login_time'] = time();
    }
}

// Check error messages from URL parameter
$error_banner = "";
if (isset($_GET['frm_emessage'])) {
    $em = htmlspecialchars($_GET['frm_emessage']);
    if ($em === 'login_error') {
        $error_banner = "<div class='alert alert-danger'>Invalid username or password. Please try again.</div>";
    } elseif ($em === 'blocked') {
        $error_banner = "<div class='alert alert-danger'>Your IP or account has been temporarily blocked.</div>";
    } elseif ($em === 'session_timeout') {
        $error_banner = "<div class='alert alert-warning'>Session expired. Please sign in again.</div>";
    } else {
        $error_banner = "<div class='alert alert-danger'>Authentication failed (" . $em . ").</div>";
    }
}
if (isset($_GET['frm_smessage']) && $_GET['frm_smessage'] === 'password_sent') {
    $error_banner = "<div class='alert alert-success'>Password reset instructions have been sent to your email.</div>";
}
?>

<style>
    .form-group {
        margin-bottom: 20px;
        position: relative;
    }

    .form-label {
        display: block;
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 8px;
        letter-spacing: -0.01em;
    }

    .input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .input-icon {
        position: absolute;
        left: 14px;
        width: 18px;
        height: 18px;
        color: var(--text-muted);
        pointer-events: none;
        transition: color 0.2s ease;
    }

    .form-input {
        width: 100%;
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius-md);
        color: var(--text-primary);
        font-family: inherit;
        font-size: 0.9375rem;
        padding: 12px 14px 12px 42px;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        outline: none;
    }

    .form-input:focus {
        border-color: var(--border-focus);
        box-shadow: 0 0 0 3px var(--primary-glow);
        background: rgba(15, 23, 42, 0.9);
    }

    .form-input:focus + .input-icon,
    .form-input:focus ~ .input-icon {
        color: var(--border-focus);
    }

    .btn-toggle-pw {
        position: absolute;
        right: 12px;
        background: none;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        padding: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.15s ease;
    }

    .btn-toggle-pw:hover {
        color: var(--text-secondary);
    }

    .form-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 10px;
        margin-bottom: 24px;
        font-size: 0.8125rem;
    }

    .forgot-link {
        color: #60a5fa;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.15s ease;
        background: none;
        border: none;
        cursor: pointer;
        font-size: inherit;
        padding: 0;
    }

    .forgot-link:hover {
        color: #93c5fd;
        text-decoration: underline;
    }

    .btn-submit {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 13px 20px;
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: var(--radius-md);
        font-family: inherit;
        font-size: 0.9375rem;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 4px 14px 0 var(--primary-glow);
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .btn-submit:hover {
        background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
        box-shadow: 0 6px 20px 0 rgba(37, 99, 235, 0.5);
        transform: translateY(-1px);
    }

    .btn-submit:active {
        transform: translateY(0);
        box-shadow: 0 2px 10px 0 var(--primary-glow);
    }

    .alert {
        padding: 12px 16px;
        border-radius: var(--radius-md);
        font-size: 0.85rem;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .alert-danger {
        background: rgba(239, 68, 68, 0.12);
        border: 1px solid rgba(239, 68, 68, 0.25);
        color: #fca5a5;
    }

    .alert-warning {
        background: rgba(245, 158, 11, 0.12);
        border: 1px solid rgba(245, 158, 11, 0.25);
        color: #fde68a;
    }

    .alert-success {
        background: rgba(16, 185, 129, 0.12);
        border: 1px solid rgba(16, 185, 129, 0.25);
        color: #6ee7b7;
    }
</style>

<?= $blocked_msg; ?>
<?= $error_banner; ?>

<?php if (!$cgi_forgotpwd): ?>

<form name="loginform" id="loginForm" action="/lib/php/" method="post" onsubmit="ctrim(this.frm_clientname.value); ctrim(this.frm_password.value); return fieldcheck(this);">
    <div class="form-group">
        <label for="frm_clientname" class="form-label">Username</label>
        <div class="input-wrapper">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <input type="text" id="frm_clientname" name="frm_clientname" class="form-input" placeholder="admin" autocomplete="username" required autofocus>
        </div>
    </div>

    <div class="form-group">
        <label for="frm_password" class="form-label">Password</label>
        <div class="input-wrapper">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
            <input type="password" id="frm_password" name="frm_password" class="form-input" placeholder="••••••••" autocomplete="current-password" required>
            <button type="button" class="btn-toggle-pw" onclick="togglePasswordVisibility()" aria-label="Toggle password view">
                <svg id="eyeIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
            </button>
        </div>
    </div>

    <div class="form-actions">
        <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; color: var(--text-secondary);">
            <input type="checkbox" name="remember_me" style="accent-color: var(--primary);"> Remember me
        </label>
        <a class="forgot-link" href="javascript:document.forgotpassword.submit()">Forgot password?</a>
    </div>

    <input type="hidden" name="frm_token" value="<?php echo $_SESSION['frm_token']; ?>">
    <button type="submit" class="btn-submit" name="login" value="Login">
        <span>Sign in to Dashboard</span>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"></line>
            <polyline points="12 5 19 12 12 19"></polyline>
        </svg>
    </button>
</form>

<form name="forgotpassword" method="post" action="/login/" style="display: none;">
    <input type="hidden" name="frm_forgotpwd" value="1">
</form>

<?php else: ?>

<form name="sendmail" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
    <div style="margin-bottom: 20px;">
        <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 6px; color: var(--text-primary);">Reset Password</h2>
        <p style="font-size: 0.8125rem; color: var(--text-secondary);">Enter your account username and email to receive a recovery link.</p>
    </div>

    <div class="form-group">
        <label for="reset_clientname" class="form-label">Username</label>
        <div class="input-wrapper">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <input type="text" id="reset_clientname" name="frm_clientname" class="form-input" placeholder="admin" required autofocus>
        </div>
    </div>

    <div class="form-group">
        <label for="reset_email" class="form-label">Registered Email</label>
        <div class="input-wrapper">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
            <input type="email" id="reset_email" name="frm_email" class="form-input" placeholder="admin@example.com" required>
        </div>
    </div>

    <input type="hidden" name="frm_forgotpwd" value="2">
    <div style="display: flex; gap: 10px; margin-top: 24px;">
        <a href="/login/" class="btn-submit" style="background: rgba(255, 255, 255, 0.08); text-decoration: none; flex: 1; border-color: var(--border-subtle);">Cancel</a>
        <button type="submit" class="btn-submit" name="forgot" value="Send" style="flex: 2;">
            Send Reset Link
        </button>
    </div>
</form>

<?php endif; ?>

<script>
function togglePasswordVisibility() {
    var pw = document.getElementById('frm_password');
    var icon = document.getElementById('eyeIcon');
    if (pw.type === 'password') {
        pw.type = 'text';
        icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
    } else {
        pw.type = 'password';
        icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
    }
}
</script>
