<?php

declare(strict_types=1);
$page_title = 'Customer Dashboard';
require __DIR__ . '/../includes/header.php';
require_login();
$user = current_user();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM loan_applications WHERE customer_id = ?");
$stmt->execute([$user['id']]);
$applications = (int)$stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM loans WHERE customer_id = ? AND status IN ('approved','active','overdue','defaulted')");
$stmt->execute([$user['id']]);
$activeLoans = (int)$stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COALESCE(SUM(outstanding_balance),0) FROM loans WHERE customer_id = ? AND status IN ('approved','active','overdue','defaulted')");
$stmt->execute([$user['id']]);
$outstanding = (float)$stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM repayments WHERE customer_id = ?");
$stmt->execute([$user['id']]);
$payments = (int)$stmt->fetchColumn();
?>
<h1>Welcome, <?= e($user['first_name']) ?></h1>
<p class="muted">Manage your PrimeLend Zambia account from one place.</p>
<div class="stat-grid">
    <div class="card stat"><span class="muted">Active Loans</span><strong><?= $activeLoans ?></strong></div>
    <div class="card stat"><span class="muted">Outstanding</span><strong><?= money($outstanding) ?></strong></div>
    <div class="card stat"><span class="muted">Applications</span><strong><?= $applications ?></strong></div>
    <div class="card stat"><span class="muted">Payments</span><strong><?= $payments ?></strong></div>
</div>
<div class="grid" style="margin-top:20px">
    <div class="card">
        <h3>Apply for a Loan</h3>
        <p class="muted">Start a new loan application.</p><a class="button" href="/primelend_zambia/customer/applications/index.php">Apply</a>
    </div>
    <div class="card">
        <h3>My Loans</h3>
        <p class="muted">View balances and repayment schedules.</p><a class="button" href="/primelend_zambia/customer/loans/index.php">View Loans</a>
    </div>
    <div class="card">
        <h3>My Profile</h3>
        <p class="muted">Review your account information.</p><a class="button" href="/primelend_zambia/customer/profile/index.php">Profile</a>
    </div>
</div>
<div class="grid" style="margin-top:20px">
    <div class="card">
        <h3>My Applications</h3>
        <p class="muted">Track pending, approved and rejected applications.</p><a class="button" href="/primelend_zambia/customer/applications/index.php">Applications</a>
    </div>
    <div class="card">
        <h3>Apply Now</h3>
        <p class="muted">Submit a new application for an available loan product.</p><a class="button" href="/primelend_zambia/customer/applications/apply.php">Apply Now</a>
    </div>
    <div class="card">
        <h3>My Loans</h3>
        <p class="muted">View approved loans, balances and installments.</p><a class="button" href="/primelend_zambia/customer/loans/index.php">My Loans</a>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>