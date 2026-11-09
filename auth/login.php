<?php
$page_title = 'Login';
require __DIR__ . '/../includes/header.php';
?>
<div class="card" style="max-width:520px;margin:auto">
    <h1>Login</h1>
    <p class="muted">Access your PrimeLend Zambia account.</p>
    <form action="/primelend_zambia/actions/login_process.php" method="post" autocomplete="on">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <p><label for="login">Email, phone or username</label><input id="login" name="login" autocomplete="username" required></p>
        <p><label for="password">Password</label><input id="password" type="password" name="password" autocomplete="current-password" required></p>
        <button class="button" type="submit">Login</button>
    </form>
    <p class="muted">Forgot your password? <a href="/primelend_zambia/auth/forgot_password.php">Reset it</a>.</p>
    <p class="muted">Administrator having trouble logging in? <a href="/primelend_zambia/auth/admin_recovery.php">Use local admin recovery</a>.</p>
    <p class="muted">Don't have an account? <a href="/primelend_zambia/auth/register.php">Create one</a>.</p>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
