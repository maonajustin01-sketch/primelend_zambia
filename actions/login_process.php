<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/app.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf_token'] ?? null)) {
    redirect_with_error('/primelend_zambia/auth/login.php', 'Invalid or expired login form. Please try again.');
}

$login = trim((string)($_POST['login'] ?? ''));
$password = (string)($_POST['password'] ?? '');

if ($login === '' || $password === '') {
    redirect_with_error('/primelend_zambia/auth/login.php', 'Enter your login details.');
}

$stmt = $pdo->prepare('SELECT id, role, password_hash, status FROM users WHERE email = ? OR phone = ? OR username = ? LIMIT 1');
$stmt->execute([$login, $login, $login]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    redirect_with_error('/primelend_zambia/auth/login.php', 'Invalid login details.');
}

if ($user['status'] !== 'active') {
    redirect_with_error('/primelend_zambia/auth/login.php', 'This account is not active. Please contact PrimeLend Zambia.');
}

session_regenerate_id(true);
$_SESSION['user_id'] = (int)$user['id'];
$_SESSION['role'] = $user['role'];
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$update = $pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?');
$update->execute([$user['id']]);

if ($user['role'] === 'admin') {
    header('Location: /primelend_zambia/admin/index.php');
} else {
    header('Location: /primelend_zambia/customer/index.php');
}
exit;
