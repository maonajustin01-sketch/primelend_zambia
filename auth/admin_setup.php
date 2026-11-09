<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/app.php';

$adminExists = (bool)$pdo->query("SELECT 1 FROM users WHERE role = 'admin' LIMIT 1")->fetchColumn();
if ($adminExists) {
    http_response_code(403);
    exit('Administrator setup is disabled because an administrator account already exists.');
}

$page_title = 'Initial Admin Setup';
require __DIR__ . '/../includes/header.php';
?>
<div class="card" style="max-width:680px;margin:auto">
    <h1>Initial Administrator Setup</h1>
    <p class="muted">Create the first PrimeLend Zambia administrator. This page automatically becomes unavailable after the first admin is created.</p>
    <div class="alert alert-warning">For local development only. Change <code>ADMIN_SETUP_KEY</code> in <code>config/app.php</code> before using this page.</div>
    <form action="/primelend_zambia/actions/admin_setup_process.php" method="post" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <div class="form-grid">
            <div><label for="setup_key">Setup key</label><input id="setup_key" type="password" name="setup_key" required></div>
            <div><label for="first_name">First name</label><input id="first_name" name="first_name" maxlength="100" required></div>
            <div><label for="last_name">Last name</label><input id="last_name" name="last_name" maxlength="100" required></div>
            <div><label for="email">Admin email</label><input id="email" type="email" name="email" maxlength="150" required></div>
            <div><label for="phone">Phone number</label><input id="phone" name="phone" maxlength="30" required></div>
            <div><label for="password">Admin password</label><input id="password" type="password" name="password" minlength="12" autocomplete="new-password" required></div>
            <div><label for="password_confirm">Confirm password</label><input id="password_confirm" type="password" name="password_confirm" minlength="12" autocomplete="new-password" required></div>
        </div>
        <p><button class="button" type="submit">Create Administrator</button></p>
    </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
