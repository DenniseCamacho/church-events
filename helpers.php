<?php
// helpers.php — session, CSRF, escaping, validation, security headers

function start_session_once(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        // Safer cookie flags
        ini_set('session.cookie_httponly', '1');
        ini_set('session.use_strict_mode', '1');
        // SameSite=Lax; add Secure when you switch to HTTPS
        if (PHP_VERSION_ID >= 70300) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }
        session_start();
    }
}

function set_security_headers(): void
{
    // Call this once per request (e.g., in index.php before output)
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 0'); // modern browsers ignore; rely on CSP later
    // Minimal CSP that still lets your CSS work; tighten later if needed
    header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'");
}

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    start_session_once();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}
function verify_csrf(): bool
{
    start_session_once();
    return !empty($_POST['csrf']) && hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf']);
}

function current_user(): ?array
{
    start_session_once();
    return $_SESSION['user'] ?? null;
}
function require_login(): void
{
    if (!current_user()) {
        header('Location: /churchevents/?route=auth/login');
        exit;
    }
}

// -------- Validation helpers --------
function valid_name(string $name): bool
{
    $len = mb_strlen($name);
    return $len >= 2 && $len <= 120;
}
function valid_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($email) <= 160;
}
function valid_password(string $pwd): bool
{
    if (strlen($pwd) < 8 || strlen($pwd) > 72) return false;
    $hasUpper = preg_match('/[A-Z]/', $pwd);
    $hasLower = preg_match('/[a-z]/', $pwd);
    $hasDigit = preg_match('/\d/', $pwd);
    $hasSpec  = preg_match('/[^A-Za-z0-9]/', $pwd);
    return $hasUpper && $hasLower && $hasDigit && $hasSpec;
}
function email_key(string $email): string
{
    return hash('sha256', strtolower(trim($email)), true); // binary 32 bytes
}
function client_ip_bin(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    return inet_pton($ip) ?: inet_pton('0.0.0.0');
}
