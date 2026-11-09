<?php
declare(strict_types=1);
require_once __DIR__.'/../config/app.php'; require_admin();
if($_SERVER['REQUEST_METHOD']!=='POST'||!verify_csrf($_POST['csrf_token']??null)) redirect_with_error('/primelend_zambia/admin/applications/','Invalid or expired request.');
$id=filter_input(INPUT_POST,'application_id',FILTER_VALIDATE_INT);$decision=(string)($_POST['decision']??'');$notes=trim((string)($_POST['notes']??''));
if(!$id||!in_array($decision,['under_review','approve','reject'],true)) redirect_with_error('/primelend_zambia/admin/applications/','Invalid review decision.');
try{
 $pdo->beginTransaction();
 $stmt=$pdo->prepare("SELECT a.*,p.interest_rate,p.interest_type,p.processing_fee FROM loan_applications a JOIN loan_products p ON p.id=a.loan_product_id WHERE a.id=? FOR UPDATE");$stmt->execute([$id]);$a=$stmt->fetch();
 if(!$a) throw new RuntimeException('Application not found.');
 if(!in_array($a['status'],['pending','under_review'],true)) throw new RuntimeException('This application has already been decided.');
 $admin=(int)$_SESSION['user_id'];
 if($decision==='under_review'){
   $stmt=$pdo->prepare("UPDATE loan_applications SET status='under_review',admin_notes=?,reviewed_by=?,reviewed_at=NOW() WHERE id=?");$stmt->execute([$notes,$admin,$id]);
   $pdo->commit(); redirect_with_success('/primelend_zambia/admin/applications/review.php?id='.$id,'Application marked under review.');
 }
 if($decision==='reject'){
   $stmt=$pdo->prepare("UPDATE loan_applications SET status='rejected',admin_notes=?,rejection_reason=?,reviewed_by=?,reviewed_at=NOW() WHERE id=?");$stmt->execute([$notes,$notes,$admin,$id]);
   $stmt=$pdo->prepare("INSERT INTO notifications(user_id,title,message,type,related_type,related_id) VALUES(?,?,?,?,?,?)");$stmt->execute([$a['customer_id'],'Loan application rejected','Your application '.$a['application_no'].' was not approved.','danger','application',$id]);
   $pdo->commit(); redirect_with_success('/primelend_zambia/admin/applications/','Application rejected.');
 }
 $principal=(float)$a['requested_amount']; $rate=(float)$a['interest_rate']; $term=(int)$a['requested_term_months']; $fee=(float)$a['processing_fee'];
 $interest=$principal*$rate/100; $total=$principal+$interest+$fee; $installment=round($total/$term,2); $maturity=(new DateTime('today'))->modify('+'.$term.' months')->format('Y-m-d'); $first=(new DateTime('today'))->modify('+1 month')->format('Y-m-d');
 $loanNo='PLN-'.date('Ymd').'-'.strtoupper(bin2hex(random_bytes(4)));
 $stmt=$pdo->prepare("INSERT INTO loans(loan_no,application_id,customer_id,loan_product_id,principal_amount,interest_rate,interest_amount,processing_fee,total_payable,amount_paid,outstanding_balance,term_months,installment_amount,disbursement_date,first_payment_date,maturity_date,status,approved_by,approved_at,notes) VALUES(?,?,?,?,?,?,?,?,?,0,?,?,?,?,?,?,?,?,?,?)");
 $stmt->execute([$loanNo,$id,$a['customer_id'],$a['loan_product_id'],$principal,$rate,$interest,$fee,$total,$total,$term,$installment,null,$first,$maturity,'approved',$admin,date('Y-m-d H:i:s'),$notes]);
 $loanId=(int)$pdo->lastInsertId();
 $baseInstallment=round($total/$term,2); $running=0.0;
 for($i=1;$i<=$term;$i++){ $due=(new DateTime('today'))->modify('+'.$i.' month')->format('Y-m-d'); $amountDue=($i===$term)?round($total-$running,2):$baseInstallment; $running+= $amountDue; $stmt=$pdo->prepare("INSERT INTO repayment_schedule(loan_id,installment_no,due_date,amount_due) VALUES(?,?,?,?)");$stmt->execute([$loanId,$i,$due,$amountDue]); }
 $stmt=$pdo->prepare("UPDATE loan_applications SET status='approved',admin_notes=?,reviewed_by=?,reviewed_at=NOW() WHERE id=?");$stmt->execute([$notes,$admin,$id]);
 $stmt=$pdo->prepare("INSERT INTO notifications(user_id,title,message,type,related_type,related_id) VALUES(?,?,?,?,?,?)");$stmt->execute([$a['customer_id'],'Loan approved','Your application '.$a['application_no'].' has been approved. Loan number: '.$loanNo,'success','loan',$loanId]);
 $pdo->commit(); redirect_with_success('/primelend_zambia/admin/loans/','Application approved and loan '.$loanNo.' created.');
}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();redirect_with_error('/primelend_zambia/admin/applications/review.php?id='.$id,'Unable to process application: '.$e->getMessage());}
