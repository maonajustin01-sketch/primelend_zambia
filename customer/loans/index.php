<?php
declare(strict_types=1);
$page_title='My Loans'; require __DIR__.'/../../includes/header.php'; require_login();
if(($_SESSION['role']??'')!=='customer'){http_response_code(403);exit('Customer access only.');}
$stmt=$pdo->prepare("SELECT l.*,p.name product_name FROM loans l JOIN loan_products p ON p.id=l.loan_product_id WHERE l.customer_id=? ORDER BY l.created_at DESC");$stmt->execute([(int)$_SESSION['user_id']]);$loans=$stmt->fetchAll();
?>
<h1>My Loans</h1><p class="muted">View approved loans, balances and repayment schedules.</p>
<div class="card table-wrap"><table><thead><tr><th>Loan No.</th><th>Product</th><th>Principal</th><th>Total Payable</th><th>Outstanding</th><th>Installment</th><th>Status</th></tr></thead><tbody>
<?php if(!$loans): ?><tr><td colspan="7">No loans yet.</td></tr><?php endif; ?>
<?php foreach($loans as $l): ?><tr><td><?=e($l['loan_no'])?></td><td><?=e($l['product_name'])?></td><td><?=money((float)$l['principal_amount'])?></td><td><?=money((float)$l['total_payable'])?></td><td><?=money((float)$l['outstanding_balance'])?></td><td><?=money((float)$l['installment_amount'])?></td><td><?=e(ucfirst($l['status']))?></td></tr><?php endforeach; ?>
</tbody></table></div>
<?php require __DIR__.'/../../includes/footer.php'; ?>
