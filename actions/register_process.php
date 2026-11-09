<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/app.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf_token'] ?? null)) {
    redirect_with_error('/primelend_zambia/auth/register.php', 'Invalid or expired form. Please try again.');
}

$firstName = trim((string)($_POST['first_name'] ?? ''));
$lastName = trim((string)($_POST['last_name'] ?? ''));
$phone = trim((string)($_POST['phone'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$nationalId = trim((string)($_POST['national_id'] ?? ''));
$dob = trim((string)($_POST['date_of_birth'] ?? ''));
$password = (string)($_POST['password'] ?? '');
$confirm = (string)($_POST['password_confirm'] ?? '');

if ($firstName === '' || $lastName === '' || $phone === '') {
    redirect_with_error('/primelend_zambia/auth/register.php', 'First name, last name and phone number are required.');
}
if (strlen($password) < 8) {
    redirect_with_error('/primelend_zambia/auth/register.php', 'Password must be at least 8 characters long.');
}
if (!hash_equals($password, $confirm)) {
    redirect_with_error('/primelend_zambia/auth/register.php', 'Passwords do not match.');
}
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_with_error('/primelend_zambia/auth/register.php', 'Please enter a valid email address.');
}
if ($dob !== '') {
    $date = DateTime::createFromFormat('Y-m-d', $dob);
    if (!$date || $date->format('Y-m-d') !== $dob) {
        redirect_with_error('/primelend_zambia/auth/register.php', 'Please enter a valid date of birth.');
    }
}

try {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE phone = ? OR (? <> \'\' AND email = ?) LIMIT 1');
    $stmt->execute([$phone, $email, $email]);
    if ($stmt->fetch()) {
        redirect_with_error('/primelend_zambia/auth/register.php', 'An account with that phone number or email already exists.');
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (role, first_name, last_name, email, phone, password_hash, national_id, date_of_birth) VALUES (\'customer\', ?, ?, NULLIF(?, \'\'), ?, ?, NULLIF(?, \'\'), NULLIF(?, \'\'))');
    $stmt->execute([$firstName, $lastName, $email, $phone, $hash, $nationalId, $dob]);

    redirect_with_success('/primelend_zambia/auth/login.php', 'Registration successful. You can now log in.');
} catch (PDOException $e) {
    redirect_with_error('/primelend_zambia/auth/register.php', 'Registration could not be completed. Please check your details and try again.');
}
