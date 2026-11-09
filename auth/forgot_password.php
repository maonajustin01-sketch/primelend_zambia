<?php $page_title='Forgot Password'; require __DIR__.'/../includes/header.php'; ?>
<div class="card" style="max-width:520px;margin:auto"><h1>Forgot Password</h1><p class="muted">Enter your registered email or phone number.</p><form action="/primelend_zambia/actions/forgot_password_process.php" method="post"><label>Email or phone</label><input name="login" required><p><button class="button" type="submit">Continue</button></p></form></div>
<?php require __DIR__.'/../includes/footer.php'; ?>
