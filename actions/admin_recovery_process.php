<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/app.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf_token'] ?? null)) {
    redirect_with_error('/primelend_zambia/auth/admin_recovery.php', 'Invalid or expired recovery form. Please try again.');
}

$setupKey = (string)($_POST['setup_key'] ?? '');
$password = (string)($_POST['password'] ?? '');
$confirm = (string)($_POST['password_confirm'] ?? '');

if (!hash_equals(ADMIN_SETUP_KEY, $setupKey)) {
    redirect_with_error('/primelend_zambia/auth/admin_recovery.php', 'Invalid recovery key.');
}
if (strlen($password) < 12) {
    redirect_with_error('/primelend_zambia/auth/admin_recovery.php', 'Administrator password must be at least 12 characters long.');
}
if (!hash_equals($password, $confirm)) {
    redirect_with_error('/primelend_zambia/auth/admin_recovery.php', 'Passwords do not match.');
}

try {
    $adminExists = (bool)$pdo->query("SELECT 1 FROM users WHERE role = 'admin' LIMIT 1")->fetchColumn();
    $hash = password_hash($password, PASSWORD_DEFAULT);

    if ($adminExists) {
        $login = trim((string)($_POST['login'] ?? ''));
        if ($login === '') {
            redirect_with_error('/primelend_zambia/auth/admin_recovery.php', 'Enter the existing administrator username or email.');
        }

        $stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'admin' AND (username = ? OR email = ?) LIMIT 1");
        $stmt->execute([$login, $login]);
        $admin = $stmt->fetch();
        if (!$admin) {
            redirect_with_error('/primelend_zambia/auth/admin_recovery.php', 'Administrator account not found. Check the username or email.');
        }

        $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, status = 'active' WHERE id = ? AND role = 'admin'");
        $stmt->execute([$hash, $admin['id']]);
        redirect_with_success('/primelend_zambia/auth/login.php', 'Administrator password reset successfully. You can now log in.');
    }

    $firstName = trim((string)($_POST['first_name'] ?? ''));
    $lastName = trim((string)($_POST['last_name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));

    if ($firstName === '' || $lastName === '' || $email === '' || $phone === '') {
        redirect_with_error('/primelend_zambia/auth/admin_recovery.php', 'All administrator details are required.');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirect_with_error('/primelend_zambia/auth/admin_recovery.php', 'Enter a valid administrator email address.');
    }

    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? OR phone = ? LIMIT 1');
    $stmt->execute([$email, $phone]);
    if ($stmt->fetch()) {
        redirect_with_error('/primelend_zambia/auth/admin_recovery.php', 'That email or phone number is already registered.');
    }

    $stmt = $pdo->prepare("INSERT INTO users (role, first_name, last_name, email, phone, password_hash, status) VALUES ('admin', ?, ?, ?, ?, ?, 'active')");
    $stmt->execute([$firstName, $lastName, $email, $phone, $hash]);
    redirect_with_success('/primelend_zambia/auth/login.php', 'Administrator account created successfully. You can now log in.');
} catch (Throwable $e) {
    redirect_with_error('/primelend_zambia/auth/admin_recovery.php', 'Administrator recovery failed. Check that your database is available and the users table matches database.sql.');
}
