<?php
$page_title = 'Create Account';
require __DIR__ . '/../includes/header.php';
?>
<div class="card">
    <h1>Create a Customer Account</h1>
    <p class="muted">Register to apply for and manage PrimeLend Zambia loans.</p>
    <form action="/primelend_zambia/actions/register_process.php" method="post" autocomplete="on">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <div class="form-grid">
            <div><label for="first_name">First name</label><input id="first_name" name="first_name" maxlength="100" required></div>
            <div><label for="last_name">Last name</label><input id="last_name" name="last_name" maxlength="100" required></div>
            <div><label for="phone">Phone number</label><input id="phone" name="phone" maxlength="30" required></div>
            <div><label for="email">Email address</label><input id="email" type="email" name="email" maxlength="150"></div>
            <div><label for="national_id">National ID</label><input id="national_id" name="national_id" maxlength="50"></div>
            <div><label for="date_of_birth">Date of birth</label><input id="date_of_birth" type="date" name="date_of_birth"></div>
            <div><label for="password">Password</label><input id="password" type="password" name="password" minlength="8" autocomplete="new-password" required></div>
            <div><label for="password_confirm">Confirm password</label><input id="password_confirm" type="password" name="password_confirm" minlength="8" autocomplete="new-password" required></div>
        </div>
        <p><button class="button" type="submit">Create Account</button></p>
    </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
