<?php
declare(strict_types=1);
$page_title = 'Apply for a Loan';
require __DIR__ . '/../../includes/header.php';
require_login();
if (($_SESSION['role'] ?? '') !== 'customer') { http_response_code(403); exit('Customer access only.'); }
$products = $pdo->query("SELECT * FROM loan_products WHERE status='active' ORDER BY name")->fetchAll();
$user = current_user();
?>
<div class="card">
    <h1>Apply for a Loan</h1>
    <p class="muted">Complete the application below. Submission does not guarantee approval.</p>
    <form action="/primelend_zambia/actions/loan_application_process.php" method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <div class="form-grid">
            <div>
                <label>Loan product</label>
                <select name="loan_product_id" id="loan_product_id" required>
                    <option value="">Select a loan product</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= (int)$p['id'] ?>" data-min="<?= e((string)$p['min_amount']) ?>" data-max="<?= e((string)$p['max_amount']) ?>" data-rate="<?= e((string)$p['interest_rate']) ?>" data-term-min="<?= (int)$p['term_min_months'] ?>" data-term-max="<?= (int)$p['term_max_months'] ?>">
                            <?= e($p['name']) ?> — <?= money((float)$p['min_amount']) ?> to <?= money((float)$p['max_amount']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div><label>Requested amount (ZMW)</label><input type="number" name="requested_amount" id="requested_amount" min="1" step="0.01" required></div>
            <div><label>Term (months)</label><input type="number" name="requested_term_months" id="requested_term_months" min="1" required></div>
            <div><label>Monthly income (ZMW)</label><input type="number" name="monthly_income" min="0" step="0.01" value="<?= e((string)($user['monthly_income'] ?? 0)) ?>" required></div>
        </div>
        <p><label>Loan purpose</label><textarea name="purpose" maxlength="255" required placeholder="Tell us what the loan will be used for."></textarea></p>
        <p><label>Employment / income details</label><textarea name="employment_details" maxlength="2000" placeholder="Employer or business details and any useful income information."></textarea></p>
        <div class="card" style="background:#f8fafc;box-shadow:none;margin:20px 0">
            <strong>Product limits:</strong> <span id="limits">Select a product to view its limits.</span>
        </div>
        <button class="button" type="submit">Submit Application</button>
    </form>
</div>
<script>
const productSelect=document.getElementById('loan_product_id');
const amount=document.getElementById('requested_amount');
const term=document.getElementById('requested_term_months');
const limits=document.getElementById('limits');
function updateLimits(){
 const o=productSelect.options[productSelect.selectedIndex];
 if(!o||!o.value){limits.textContent='Select a product to view its limits.'; return;}
 amount.min=o.dataset.min; amount.max=o.dataset.max; term.min=o.dataset.termMin; term.max=o.dataset.termMax;
 limits.textContent=`Amount: K${Number(o.dataset.min).toLocaleString()} – K${Number(o.dataset.max).toLocaleString()} | Interest: ${o.dataset.rate}% | Term: ${o.dataset.termMin}–${o.dataset.termMax} months`;
}
productSelect.addEventListener('change',updateLimits); updateLimits();
</script>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
