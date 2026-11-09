<?php
declare(strict_types=1);
$page_title='Manage Loans'; require __DIR__.'/../../includes/header.php'; require_admin();
$stmt=$pdo->query("SELECT l.*,CONCAT(u.first_name,' ',u.last_name) customer_name,p.name product_name FROM loans l JOIN users u ON u.id=l.customer_id JOIN loan_products p ON p.id=l.loan_product_id ORDER BY l.created_at DESC");$loans=$stmt->fetchAll();
?>
<h1>Loans</h1><p class="muted">Approved loans created from accepted applications.</p>
<div class="card table-wrap"><table><thead><tr><th>Loan No.</th><th>Customer</th><th>Product</th><th>Principal</th><th>Total Payable</th><th>Outstanding</th><th>Status</th></tr></thead><tbody>
<?php if(!$loans): ?><tr><td colspan="7">No loans created yet.</td></tr><?php endif; ?>
<?php foreach($loans as $l): ?><tr><td><?=e($l['loan_no'])?></td><td><?=e($l['customer_name'])?></td><td><?=e($l['product_name'])?></td><td><?=money((float)$l['principal_amount'])?></td><td><?=money((float)$l['total_payable'])?></td><td><?=money((float)$l['outstanding_balance'])?></td><td><?=e(ucfirst($l['status']))?></td></tr><?php endforeach; ?>
</tbody></table></div>
<?php require __DIR__.'/../../includes/footer.php'; ?>
