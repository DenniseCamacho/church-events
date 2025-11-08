<?php
// helpers.php
// Shared utility functions for sessions, security, validation, and routing

require_once __DIR__ . '/db.php';

// SESSION HELPERS
function start_session_once(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// CURRENT USER
function current_user(): ?array
{
    start_session_once();
    return $_SESSION['user'] ?? null;
}

// URL HELPERS
function base_url(): string
{
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if (strpos($host, 'localhost') !== false) {
        return '/churchevents';
    }
    return '';
}

// SECURITY HELPERS
function csrf_token(): string
{
    start_session_once();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function verify_csrf(): bool
{
    start_session_once();
    if (!isset($_POST['csrf_token'], $_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

// OUTPUT HELPER
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// VALIDATION HELPERS
function valid_email(string $email): bool
{
    if (strlen($email) < 3 || strlen($email) > 160) {
        return false;
    }
    return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
}

function valid_name(string $name): bool
{
    $len = strlen($name);
    return $len >= 2 && $len <= 120;
}

function valid_password(string $password): bool
{
    if (strlen($password) < 8) {
        return false;
    }
    $hasUpper = preg_match('/[A-Z]/', $password);
    $hasLower = preg_match('/[a-z]/', $password);
    $hasDigit = preg_match('/[0-9]/', $password);
    $hasSymbol = preg_match('/[^A-Za-z0-9]/', $password);

    return $hasUpper && $hasLower && $hasDigit && $hasSymbol;
}

// NETWORK HELPERS
function client_ip_bin(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $bin = @inet_pton($ip);
    if ($bin === false) {
        $bin = @inet_pton('0.0.0.0');
    }
    return $bin;
}

function email_key(string $email): string
{
    $norm = strtolower(trim($email));
    return hash('sha256', $norm, true);
}
