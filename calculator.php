<?php
$page_title = 'Loan Calculator';
require __DIR__ . '/includes/header.php';

$products = [];
try {
    $stmt = $pdo->query("SELECT id, name, min_amount, max_amount, interest_rate, interest_type, term_min_months, term_max_months, processing_fee FROM loan_products WHERE status = 'active' ORDER BY name ASC");
    $products = $stmt->fetchAll();
} catch (Throwable $e) {
    $products = [];
}
?>

<div class="card calculator-card">
    <h1>Loan Calculator</h1>
    <p class="muted">Choose a loan product, enter the amount and term, and the repayment estimate will update automatically.</p>

    <?php if (!$products): ?>
        <div class="alert alert-warning">No active loan products are available. Please ask an administrator to activate a loan product.</div>
    <?php endif; ?>

    <div class="form-grid">
        <div>
            <label for="calculator_product">Loan product</label>
            <select id="calculator_product" <?= !$products ? 'disabled' : '' ?>>
                <option value="">Select a loan product</option>
                <?php foreach ($products as $index => $product): ?>
                    <option
                        value="<?= e((string)$product['id']) ?>"
                        data-min="<?= e((string)$product['min_amount']) ?>"
                        data-max="<?= e((string)$product['max_amount']) ?>"
                        data-rate="<?= e((string)$product['interest_rate']) ?>"
                        data-interest-type="<?= e((string)$product['interest_type']) ?>"
                        data-term-min="<?= e((string)$product['term_min_months']) ?>"
                        data-term-max="<?= e((string)$product['term_max_months']) ?>"
                        data-fee="<?= e((string)$product['processing_fee']) ?>"
                    ><?= e($product['name']) ?> — <?= e(number_format((float)$product['interest_rate'], 2)) ?>% interest</option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="calculator_amount">Loan amount (ZMW)</label>
            <input id="calculator_amount" type="number" min="0" step="0.01" inputmode="decimal" placeholder="e.g. 5000" <?= !$products ? 'disabled' : '' ?> autocomplete="off">
            <small id="amount_hint" class="muted"></small>
        </div>

        <div>
            <label for="calculator_term">Term (months)</label>
            <input id="calculator_term" type="number" min="1" step="1" inputmode="numeric" placeholder="e.g. 6" <?= !$products ? 'disabled' : '' ?> autocomplete="off">
            <small id="term_hint" class="muted"></small>
        </div>

        <div>
            <label for="calculator_rate">Interest rate (%) <span class="muted">(calculated)</span></label>
            <input id="calculator_rate" type="text" readonly value="0.00%" aria-readonly="true">
        </div>

        <div>
            <label for="calculator_fee">Processing fee (ZMW) <span class="muted">(calculated)</span></label>
            <input id="calculator_fee" type="text" readonly value="K0.00" aria-readonly="true">
        </div>

        <div>
            <label for="calculator_interest">Interest amount (ZMW) <span class="muted">(calculated)</span></label>
            <input id="calculator_interest" type="text" readonly value="K0.00" aria-readonly="true">
        </div>

        <div>
            <label for="calculator_total">Total payable (ZMW) <span class="muted">(calculated)</span></label>
            <input id="calculator_total" type="text" readonly value="K0.00" aria-readonly="true">
        </div>

        <div>
            <label for="calculator_payment">Estimated monthly payment (ZMW) <span class="muted">(calculated)</span></label>
            <input id="calculator_payment" type="text" readonly value="K0.00" aria-readonly="true">
        </div>
    </div>

    <div id="calculator_message" class="alert alert-warning" style="display:none;margin-top:18px;"></div>

    <div class="calculator-result" style="margin-top:24px;">
        <strong>How it is calculated</strong>
        <p class="muted" id="calculator_formula">Select a loan product and enter an amount and term to see your estimate.</p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const product = document.getElementById('calculator_product');
    const amount = document.getElementById('calculator_amount');
    const term = document.getElementById('calculator_term');
    const rate = document.getElementById('calculator_rate');
    const fee = document.getElementById('calculator_fee');
    const interest = document.getElementById('calculator_interest');
    const total = document.getElementById('calculator_total');
    const payment = document.getElementById('calculator_payment');
    const amountHint = document.getElementById('amount_hint');
    const termHint = document.getElementById('term_hint');
    const message = document.getElementById('calculator_message');
    const formula = document.getElementById('calculator_formula');

    if (!product || !amount || !term) return;

    function money(value) {
        return 'K' + Number(value || 0).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function resetResults() {
        rate.value = '0.00%';
        fee.value = 'K0.00';
        interest.value = 'K0.00';
        total.value = 'K0.00';
        payment.value = 'K0.00';
    }

    function calculate() {
        const selected = product.options[product.selectedIndex];
        const hasProduct = selected && selected.value !== '';

        message.style.display = 'none';
        message.textContent = '';

        if (!hasProduct) {
            amountHint.textContent = '';
            termHint.textContent = '';
            resetResults();
            formula.textContent = 'Select a loan product and enter an amount and term to see your estimate.';
            return;
        }

        const minAmount = Number(selected.dataset.min);
        const maxAmount = Number(selected.dataset.max);
        const productRate = Number(selected.dataset.rate);
        const processingFee = Number(selected.dataset.fee);
        const minTerm = Number(selected.dataset.termMin);
        const maxTerm = Number(selected.dataset.termMax);
        const interestType = (selected.dataset.interestType || 'flat').toLowerCase();

        rate.value = productRate.toFixed(2) + '%';
        fee.value = money(processingFee);
        amount.min = String(minAmount);
        amount.max = String(maxAmount);
        term.min = String(minTerm);
        term.max = String(maxTerm);
        amountHint.textContent = 'Allowed: ' + money(minAmount) + ' – ' + money(maxAmount);
        termHint.textContent = 'Allowed: ' + minTerm + ' – ' + maxTerm + ' months';

        const principal = Number(amount.value);
        const months = Number(term.value);

        if (amount.value === '' || term.value === '') {
            interest.value = 'K0.00';
            total.value = 'K0.00';
            payment.value = 'K0.00';
            formula.textContent = 'Flat interest at ' + productRate.toFixed(2) + '% plus the ' + money(processingFee) + ' processing fee.';
            return;
        }

        const errors = [];
        if (!Number.isFinite(principal) || principal < minAmount || principal > maxAmount) {
            errors.push('Loan amount must be between ' + money(minAmount) + ' and ' + money(maxAmount) + '.');
        }
        if (!Number.isFinite(months) || months < minTerm || months > maxTerm || !Number.isInteger(months)) {
            errors.push('Term must be a whole number between ' + minTerm + ' and ' + maxTerm + ' months.');
        }

        if (errors.length) {
            interest.value = 'K0.00';
            total.value = 'K0.00';
            payment.value = 'K0.00';
            message.textContent = errors.join(' ');
            message.style.display = 'block';
            formula.textContent = 'Enter values within the allowed product limits.';
            return;
        }

        // PrimeLend's current products use flat interest.
        const interestAmount = principal * productRate / 100;
        const totalPayable = principal + interestAmount + processingFee;
        const monthlyPayment = totalPayable / months;

        interest.value = money(interestAmount);
        total.value = money(totalPayable);
        payment.value = money(monthlyPayment);
        formula.textContent = money(principal) + ' + ' + money(interestAmount) + ' interest + ' + money(processingFee) + ' fee = ' + money(totalPayable) + ' total, divided over ' + months + ' months = ' + money(monthlyPayment) + ' per month.';
    }

    product.addEventListener('change', calculate);
    amount.addEventListener('input', calculate);
    amount.addEventListener('change', calculate);
    term.addEventListener('input', calculate);
    term.addEventListener('change', calculate);
    calculate();
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
