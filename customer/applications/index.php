<?php
declare(strict_types=1);
$page_title = 'My Loan Applications';
require __DIR__ . '/../../includes/header.php';
require_login();
if (($_SESSION['role'] ?? '') !== 'customer') { http_response_code(403); exit('Customer access only.'); }
$user=current_user();
$stmt=$pdo->prepare("SELECT a.*, p.name product_name FROM loan_applications a JOIN loan_products p ON p.id=a.loan_product_id WHERE a.customer_id=? ORDER BY a.created_at DESC");
$stmt->execute([$user['id']]); $apps=$stmt->fetchAll();
?>
<div style="display:flex;justify-content:space-between;align-items:center;gap:15px;flex-wrap:wrap"><div><h1>My Loan Applications</h1><p class="muted">Track applications and their review status.</p></div><a class="button" href="/primelend_zambia/customer/applications/apply.php">New Application</a></div>
<div class="card table-wrap" style="margin-top:20px">
<table><thead><tr><th>Application No.</th><th>Product</th><th>Amount</th><th>Term</th><th>Date</th><th>Status</th></tr></thead><tbody>
<?php if(!$apps): ?><tr><td colspan="6">No applications yet.</td></tr><?php endif; ?>
<?php foreach($apps as $a): ?><tr><td><?=e($a['application_no'])?></td><td><?=e($a['product_name'])?></td><td><?=money((float)$a['requested_amount'])?></td><td><?=e((string)$a['requested_term_months'])?> months</td><td><?=e($a['requested_date'])?></td><td><?=e(ucwords(str_replace('_',' ',$a['status'])))?></td></tr><?php endforeach; ?>
</tbody></table></div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
