<?php
declare(strict_types=1);
$page_title = 'Loan Products';
require __DIR__ . '/../../includes/header.php';
require_admin();

$products = $pdo->query('SELECT * FROM loan_products ORDER BY status DESC, name ASC')->fetchAll();
$editId = (int)($_GET['edit'] ?? 0);
$editing = null;
if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM loan_products WHERE id = ? LIMIT 1');
    $stmt->execute([$editId]);
    $editing = $stmt->fetch() ?: null;
}

$form = $editing ?: [
    'id' => 0, 'name' => '', 'description' => '', 'min_amount' => '500.00', 'max_amount' => '10000.00',
    'interest_rate' => '30.00', 'term_min_months' => 1, 'term_max_months' => 12,
    'processing_fee' => '0.00', 'late_fee' => '50.00', 'status' => 'active'
];
?>
<div class="admin-page-head">
    <div>
        <h1>Loan Products</h1>
        <p class="muted">Control the interest rate, loan limits, terms and fees used by PrimeLend Zambia.</p>
    </div>
    <a class="button button-secondary" href="/primelend_zambia/admin/index.php">Back to Dashboard</a>
</div>

<div class="product-management-grid">
    <section class="card">
        <div class="section-head">
            <div>
                <h2><?= $editing ? 'Edit Loan Product' : 'Add Loan Product' ?></h2>
                <p class="muted">Current loan calculations use flat interest.</p>
            </div>
            <?php if ($editing): ?><a class="text-link" href="/primelend_zambia/admin/settings/index.php">Cancel edit</a><?php endif; ?>
        </div>
        <form method="post" action="/primelend_zambia/actions/loan_product_process.php">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="<?= $editing ? 'update' : 'create' ?>">
            <?php if ($editing): ?><input type="hidden" name="id" value="<?= (int)$form['id'] ?>"><?php endif; ?>

            <div class="form-grid">
                <div class="form-full">
                    <label for="name">Product Name</label>
                    <input id="name" name="name" value="<?= e((string)$form['name']) ?>" required maxlength="120" placeholder="e.g. Personal Loan">
                </div>
                <div class="form-full">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3" placeholder="Short description shown to customers."><?= e((string)$form['description']) ?></textarea>
                </div>
                <div>
                    <label for="min_amount">Minimum Amount (K)</label>
                    <input id="min_amount" name="min_amount" type="number" min="0.01" step="0.01" value="<?= e((string)$form['min_amount']) ?>" required>
                </div>
                <div>
                    <label for="max_amount">Maximum Amount (K)</label>
                    <input id="max_amount" name="max_amount" type="number" min="0.01" step="0.01" value="<?= e((string)$form['max_amount']) ?>" required>
                </div>
                <div>
                    <label for="interest_rate">Interest Rate (%)</label>
                    <input id="interest_rate" name="interest_rate" type="number" min="0" max="100" step="0.01" value="<?= e((string)$form['interest_rate']) ?>" required>
                    <small class="field-help">Example: enter <strong>25</strong> for 25% flat interest.</small>
                </div>
                <div>
                    <label>Interest Type</label>
                    <input value="Flat" readonly>
                    <small class="field-help">The current approval and calculator engine uses flat interest.</small>
                </div>
                <div>
                    <label for="term_min_months">Minimum Term (months)</label>
                    <input id="term_min_months" name="term_min_months" type="number" min="1" step="1" value="<?= (int)$form['term_min_months'] ?>" required>
                </div>
                <div>
                    <label for="term_max_months">Maximum Term (months)</label>
                    <input id="term_max_months" name="term_max_months" type="number" min="1" step="1" value="<?= (int)$form['term_max_months'] ?>" required>
                </div>
                <div>
                    <label for="processing_fee">Processing Fee (K)</label>
                    <input id="processing_fee" name="processing_fee" type="number" min="0" step="0.01" value="<?= e((string)$form['processing_fee']) ?>" required>
                </div>
                <div>
                    <label for="late_fee">Late Fee (K)</label>
                    <input id="late_fee" name="late_fee" type="number" min="0" step="0.01" value="<?= e((string)$form['late_fee']) ?>" required>
                </div>
                <div>
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="active" <?= $form['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $form['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
            </div>
            <div class="form-actions">
                <button class="button" type="submit"><?= $editing ? 'Save Changes' : 'Create Product' ?></button>
            </div>
        </form>
    </section>

    <aside class="card rate-info">
        <h3>How interest works</h3>
        <p class="muted">PrimeLend currently calculates flat interest from the product rate.</p>
        <div class="formula-box">
            <strong>Interest</strong><br>
            Principal × Interest Rate ÷ 100
        </div>
        <div class="formula-box">
            <strong>Total Payable</strong><br>
            Principal + Interest + Processing Fee
        </div>
        <div class="formula-box">
            <strong>Monthly Payment</strong><br>
            Total Payable ÷ Term in Months
        </div>
        <p class="muted"><strong>Important:</strong> changing the product rate affects new calculations and new loan approvals that use that product. Existing loans keep their stored interest values.</p>
    </aside>
</div>

<section class="card" style="margin-top:20px">
    <div class="section-head">
        <div>
            <h2>Current Loan Products</h2>
            <p class="muted">Edit a product to change its interest rate or other lending rules.</p>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Product</th><th>Loan Range</th><th>Interest</th><th>Term</th><th>Fees</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><strong><?= e($product['name']) ?></strong><br><span class="muted"><?= e((string)$product['description']) ?></span></td>
                    <td><?= money((float)$product['min_amount']) ?> – <?= money((float)$product['max_amount']) ?></td>
                    <td><strong><?= number_format((float)$product['interest_rate'], 2) ?>%</strong><br><span class="muted">Flat</span></td>
                    <td><?= (int)$product['term_min_months'] ?> – <?= (int)$product['term_max_months'] ?> months</td>
                    <td>Processing: <?= money((float)$product['processing_fee']) ?><br>Late: <?= money((float)$product['late_fee']) ?></td>
                    <td><span class="status-pill status-<?= e($product['status']) ?>"><?= ucfirst(e($product['status'])) ?></span></td>
                    <td class="actions-cell">
                        <a class="small-button" href="/primelend_zambia/admin/settings/index.php?edit=<?= (int)$product['id'] ?>">Edit</a>
                        <form method="post" action="/primelend_zambia/actions/loan_product_process.php" style="display:inline">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
                            <button class="small-button small-button-muted" type="submit"><?= $product['status'] === 'active' ? 'Deactivate' : 'Activate' ?></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$products): ?><tr><td colspan="7" class="muted">No loan products found.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
