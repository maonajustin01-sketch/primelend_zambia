<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/app.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf_token'] ?? null)) {
    redirect_with_error('/primelend_zambia/auth/admin_setup.php', 'Invalid or expired setup form.');
}

if ((bool)$pdo->query("SELECT 1 FROM users WHERE role = 'admin' LIMIT 1")->fetchColumn()) {
    http_response_code(403);
    exit('Administrator setup is already disabled.');
}

$setupKey = (string)($_POST['setup_key'] ?? '');
$firstName = trim((string)($_POST['first_name'] ?? ''));
$lastName = trim((string)($_POST['last_name'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$phone = trim((string)($_POST['phone'] ?? ''));
$password = (string)($_POST['password'] ?? '');
$confirm = (string)($_POST['password_confirm'] ?? '');

if (!hash_equals(ADMIN_SETUP_KEY, $setupKey)) {
    redirect_with_error('/primelend_zambia/auth/admin_setup.php', 'Invalid setup key.');
}
if ($firstName === '' || $lastName === '' || $email === '' || $phone === '') {
    redirect_with_error('/primelend_zambia/auth/admin_setup.php', 'All administrator details are required.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_with_error('/primelend_zambia/auth/admin_setup.php', 'Enter a valid administrator email address.');
}
if (strlen($password) < 12) {
    redirect_with_error('/primelend_zambia/auth/admin_setup.php', 'Administrator password must be at least 12 characters long.');
}
if (!hash_equals($password, $confirm)) {
    redirect_with_error('/primelend_zambia/auth/admin_setup.php', 'Passwords do not match.');
}

try {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? OR phone = ? LIMIT 1');
    $stmt->execute([$email, $phone]);
    if ($stmt->fetch()) {
        redirect_with_error('/primelend_zambia/auth/admin_setup.php', 'That email or phone number is already registered.');
    }

    $pdo->beginTransaction();
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (role, first_name, last_name, email, phone, password_hash, status) VALUES ('admin', ?, ?, ?, ?, ?, 'active')");
    $stmt->execute([$firstName, $lastName, $email, $phone, $hash]);
    $pdo->commit();

    redirect_with_success('/primelend_zambia/auth/login.php', 'Administrator account created successfully. Please log in.');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    redirect_with_error('/primelend_zambia/auth/admin_setup.php', 'Administrator account could not be created.');
}
