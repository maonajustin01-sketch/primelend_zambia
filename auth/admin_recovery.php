<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/app.php';

$page_title = 'Administrator Recovery';
$adminExists = (bool)$pdo->query("SELECT 1 FROM users WHERE role = 'admin' LIMIT 1")->fetchColumn();
require __DIR__ . '/../includes/header.php';
?>
<div class="card" style="max-width:680px;margin:auto">
    <h1>Administrator Recovery</h1>
    <p class="muted">Local development recovery tool for the PrimeLend Zambia administrator account.</p>
    <div class="alert alert-warning">Use this page only on your local XAMPP installation. After recovering the account, delete <code>auth/admin_recovery.php</code> and <code>actions/admin_recovery_process.php</code>.</div>
    <?php if ($adminExists): ?>
        <p>An administrator account exists. Enter the administrator username or email and choose a new password.</p>
    <?php else: ?>
        <p>No administrator account currently exists. You can create a new administrator here.</p>
    <?php endif; ?>
    <form action="/primelend_zambia/actions/admin_recovery_process.php" method="post" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <div class="form-grid">
            <div><label for="setup_key">Recovery key</label><input id="setup_key" type="password" name="setup_key" required></div>
            <?php if ($adminExists): ?>
                <div><label for="login">Existing admin username or email</label><input id="login" name="login" required></div>
            <?php else: ?>
                <div><label for="first_name">First name</label><input id="first_name" name="first_name" maxlength="100" required></div>
                <div><label for="last_name">Last name</label><input id="last_name" name="last_name" maxlength="100" required></div>
                <div><label for="email">Admin email</label><input id="email" type="email" name="email" maxlength="150" required></div>
                <div><label for="phone">Phone number</label><input id="phone" name="phone" maxlength="30" required></div>
            <?php endif; ?>
            <div><label for="password">New admin password</label><input id="password" type="password" name="password" minlength="12" autocomplete="new-password" required></div>
            <div><label for="password_confirm">Confirm password</label><input id="password_confirm" type="password" name="password_confirm" minlength="12" autocomplete="new-password" required></div>
        </div>
        <p><button class="button" type="submit"><?= $adminExists ? 'Reset Administrator Password' : 'Create Administrator' ?></button></p>
    </form>
    <p><a href="/primelend_zambia/auth/login.php">Back to login</a></p>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
