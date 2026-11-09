<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once __DIR__ . '/database.php';

const APP_NAME = 'PrimeLend Zambia';
const CURRENCY = 'ZMW';
// LOCAL SETUP ONLY: change this before creating the first administrator.
// The setup page disables itself automatically after an admin exists.
const ADMIN_SETUP_KEY = 'CHANGE-ME-PRIMELEND-SETUP-2026';

function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function money(float $amount): string {
    return 'K' . number_format($amount, 2);
}

function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

function require_login(): void {
    if (!is_logged_in()) {
        header('Location: /primelend_zambia/auth/login.php');
        exit;
    }
}

function require_admin(): void {
    require_login();
    if (($_SESSION['role'] ?? '') !== 'admin') {
        http_response_code(403);
        exit('Access denied.');
    }
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(?string $token): bool {
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function redirect_with_error(string $url, string $message): never {
    $_SESSION['flash_error'] = $message;
    header('Location: ' . $url);
    exit;
}

function redirect_with_success(string $url, string $message): never {
    $_SESSION['flash_success'] = $message;
    header('Location: ' . $url);
    exit;
}

function flash(string $key): ?string {
    $message = $_SESSION[$key] ?? null;
    unset($_SESSION[$key]);
    return is_string($message) ? $message : null;
}
