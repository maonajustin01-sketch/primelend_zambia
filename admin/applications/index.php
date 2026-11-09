<?php
declare(strict_types=1);
$page_title='Loan Applications'; require __DIR__.'/../../includes/header.php'; require_admin();
$status=trim((string)($_GET['status']??''));
$sql="SELECT a.*, CONCAT(u.first_name,' ',u.last_name) customer_name,u.phone,p.name product_name FROM loan_applications a JOIN users u ON u.id=a.customer_id JOIN loan_products p ON p.id=a.loan_product_id";
$params=[]; if(in_array($status,['pending','under_review','approved','rejected','cancelled'],true)){ $sql.=' WHERE a.status=?'; $params[]=$status; } $sql.=' ORDER BY a.created_at DESC';
$stmt=$pdo->prepare($sql);$stmt->execute($params);$apps=$stmt->fetchAll();
?>
<div style="display:flex;justify-content:space-between;align-items:center;gap:15px;flex-wrap:wrap"><div><h1>Loan Applications</h1><p class="muted">Review customer applications and create approved loans.</p></div><div><a href="?status=pending">Pending</a> · <a href="?status=under_review">Under Review</a> · <a href="?status=approved">Approved</a> · <a href="?status=rejected">Rejected</a></div></div>
<div class="card table-wrap" style="margin-top:20px"><table><thead><tr><th>No.</th><th>Customer</th><th>Product</th><th>Amount</th><th>Term</th><th>Date</th><th>Status</th><th>Action</th></tr></thead><tbody>
<?php if(!$apps): ?><tr><td colspan="8">No applications found.</td></tr><?php endif; ?>
<?php foreach($apps as $a): ?><tr><td><?=e($a['application_no'])?></td><td><?=e($a['customer_name'])?><br><span class="muted"><?=e($a['phone'])?></span></td><td><?=e($a['product_name'])?></td><td><?=money((float)$a['requested_amount'])?></td><td><?=e((string)$a['requested_term_months'])?> mo.</td><td><?=e($a['requested_date'])?></td><td><?=e(ucwords(str_replace('_',' ',$a['status'])))?></td><td><a class="button" href="/primelend_zambia/admin/applications/review.php?id=<?= (int)$a['id'] ?>">Review</a></td></tr><?php endforeach; ?>
</tbody></table></div>
<?php require __DIR__.'/../../includes/footer.php'; ?>
