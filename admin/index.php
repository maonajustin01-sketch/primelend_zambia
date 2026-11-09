<?php
declare(strict_types=1);
$page_title = 'Admin Dashboard';
require __DIR__ . '/../includes/header.php';
require_admin();
$customers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$applications = (int)$pdo->query("SELECT COUNT(*) FROM loan_applications WHERE status IN ('pending','under_review')")->fetchColumn();
$activeLoans = (int)$pdo->query("SELECT COUNT(*) FROM loans WHERE status IN ('approved','active','overdue','defaulted')")->fetchColumn();
$outstanding = (float)$pdo->query("SELECT COALESCE(SUM(outstanding_balance),0) FROM loans WHERE status IN ('approved','active','overdue','defaulted')")->fetchColumn();
?>
<h1>Admin Dashboard</h1>
<p class="muted">PrimeLend Zambia management overview.</p>
<div class="stat-grid">
    <div class="card stat"><span class="muted">Customers</span><strong><?= $customers ?></strong></div>
    <div class="card stat"><span class="muted">Pending Applications</span><strong><?= $applications ?></strong></div>
    <div class="card stat"><span class="muted">Active Loans</span><strong><?= $activeLoans ?></strong></div>
    <div class="card stat"><span class="muted">Outstanding</span><strong><?= money($outstanding) ?></strong></div>
</div>
<div class="grid" style="margin-top:20px">
    <div class="card"><h3>Customers</h3><a class="button" href="/primelend_zambia/admin/customers/index.php">Manage Customers</a></div>
    <div class="card"><h3>Applications</h3><a class="button" href="/primelend_zambia/admin/applications/index.php">Review Applications</a></div>
    <div class="card"><h3>Loans</h3><a class="button" href="/primelend_zambia/admin/loans/index.php">Manage Loans</a></div>
</div>
<div class="grid" style="margin-top:20px">
<div class="card"><h3>Loan Products</h3><p class="muted">Update interest rates, loan limits, terms and fees.</p><a class="button" href="/primelend_zambia/admin/settings/index.php">Manage Loan Products</a></div>
<div class="card"><h3>Applications</h3><p class="muted">Review pending customer applications.</p><a class="button" href="/primelend_zambia/admin/applications/">Review Applications</a></div>
<div class="card"><h3>Loans</h3><p class="muted">View approved loans and balances.</p><a class="button" href="/primelend_zambia/admin/loans/">Manage Loans</a></div>
<div class="card"><h3>Customers</h3><p class="muted">Manage customer accounts.</p><a class="button" href="/primelend_zambia/admin/customers/">Customers</a></div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
