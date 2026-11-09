<?php
require_once __DIR__ . '/auth.php';
$page_title = $page_title ?? APP_NAME;
$error = flash('flash_error');
$success = flash('flash_success');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> | PrimeLend Zambia</title>
    <link rel="stylesheet" href="/primelend_zambia/assets/css/style.css">
</head>
<body>
<header class="site-header">
    <a class="brand" href="/primelend_zambia/index.php">
        <span class="brand-mark">P</span>
        <span>PrimeLend <small>Zambia</small></span>
    </a>
    <nav>
        <a href="/primelend_zambia/index.php">Home</a>
        <a href="/primelend_zambia/loans.php">Loan Products</a>
        <a href="/primelend_zambia/calculator.php">Calculator</a>
        <?php if (is_logged_in()): ?>
            <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                <a href="/primelend_zambia/admin/index.php">Admin</a>
            <?php else: ?>
                <a href="/primelend_zambia/customer/index.php">My Account</a>
            <?php endif; ?>
            <a href="/primelend_zambia/auth/logout.php">Logout</a>
        <?php else: ?>
            <a href="/primelend_zambia/auth/register.php">Register</a>
            <a class="nav-button" href="/primelend_zambia/auth/login.php">Login</a>
        <?php endif; ?>
    </nav>
</header>
<main class="page-container">
<?php if ($error): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>
