<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/app.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf_token'] ?? null)) {
    redirect_with_error('/primelend_zambia/admin/settings/index.php', 'Invalid request or security token.');
}

$action = $_POST['action'] ?? '';
$id = (int)($_POST['id'] ?? 0);

try {
    if ($action === 'toggle') {
        if ($id < 1) {
            redirect_with_error('/primelend_zambia/admin/settings/index.php', 'Invalid loan product.');
        }
        $stmt = $pdo->prepare("UPDATE loan_products SET status = CASE WHEN status = 'active' THEN 'inactive' ELSE 'active' END WHERE id = ?");
        $stmt->execute([$id]);
        redirect_with_success('/primelend_zambia/admin/settings/index.php', 'Loan product status updated successfully.');
    }

    if (!in_array($action, ['create', 'update'], true)) {
        redirect_with_error('/primelend_zambia/admin/settings/index.php', 'Unsupported action.');
    }

    $name = trim((string)($_POST['name'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    $minAmount = (float)($_POST['min_amount'] ?? 0);
    $maxAmount = (float)($_POST['max_amount'] ?? 0);
    $interestRate = (float)($_POST['interest_rate'] ?? 0);
    $termMin = (int)($_POST['term_min_months'] ?? 0);
    $termMax = (int)($_POST['term_max_months'] ?? 0);
    $processingFee = (float)($_POST['processing_fee'] ?? 0);
    $lateFee = (float)($_POST['late_fee'] ?? 0);
    $status = ($_POST['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';

    if ($name === '') redirect_with_error('/primelend_zambia/admin/settings/index.php', 'Product name is required.');
    if ($minAmount <= 0 || $maxAmount <= 0 || $minAmount > $maxAmount) redirect_with_error('/primelend_zambia/admin/settings/index.php', 'Check the minimum and maximum loan amounts.');
    if ($interestRate < 0 || $interestRate > 100) redirect_with_error('/primelend_zambia/admin/settings/index.php', 'Interest rate must be between 0% and 100%.');
    if ($termMin < 1 || $termMax < $termMin) redirect_with_error('/primelend_zambia/admin/settings/index.php', 'Check the minimum and maximum loan terms.');
    if ($processingFee < 0 || $lateFee < 0) redirect_with_error('/primelend_zambia/admin/settings/index.php', 'Fees cannot be negative.');

    if ($action === 'create') {
        $stmt = $pdo->prepare("INSERT INTO loan_products (name, description, min_amount, max_amount, interest_rate, interest_type, term_min_months, term_max_months, processing_fee, late_fee, status) VALUES (?, ?, ?, ?, ?, 'flat', ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description ?: null, $minAmount, $maxAmount, $interestRate, $termMin, $termMax, $processingFee, $lateFee, $status]);
        redirect_with_success('/primelend_zambia/admin/settings/index.php', 'Loan product created successfully.');
    }

    if ($id < 1) redirect_with_error('/primelend_zambia/admin/settings/index.php', 'Invalid loan product.');
    $stmt = $pdo->prepare("UPDATE loan_products SET name = ?, description = ?, min_amount = ?, max_amount = ?, interest_rate = ?, interest_type = 'flat', term_min_months = ?, term_max_months = ?, processing_fee = ?, late_fee = ?, status = ? WHERE id = ?");
    $stmt->execute([$name, $description ?: null, $minAmount, $maxAmount, $interestRate, $termMin, $termMax, $processingFee, $lateFee, $status, $id]);
    redirect_with_success('/primelend_zambia/admin/settings/index.php', 'Loan product updated successfully.');
} catch (Throwable $e) {
    redirect_with_error('/primelend_zambia/admin/settings/index.php', 'Unable to save loan product: ' . $e->getMessage());
}
