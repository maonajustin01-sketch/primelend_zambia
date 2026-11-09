document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-confirm]').forEach((el) => {
        el.addEventListener('click', (event) => {
            if (!confirm(el.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });

    const product = document.getElementById('calculator_product');
    const amount = document.getElementById('calculator_amount');
    const term = document.getElementById('calculator_term');

    if (!product || !amount || !term) {
        return;
    }

    const rate = document.getElementById('calculator_rate');
    const fee = document.getElementById('calculator_fee');
    const interest = document.getElementById('calculator_interest');
    const total = document.getElementById('calculator_total');
    const payment = document.getElementById('calculator_payment');
    const amountHint = document.getElementById('amount_hint');
    const termHint = document.getElementById('term_hint');
    const message = document.getElementById('calculator_message');
    const formula = document.getElementById('calculator_formula');

    const money = (value) => `K${Number(value || 0).toLocaleString('en-ZM', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    })}`;

    const resetResults = () => {
        rate.value = '0.00';
        fee.value = 'K0.00';
        interest.value = 'K0.00';
        total.value = 'K0.00';
        payment.value = 'K0.00';
    };

    const calculate = () => {
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

        const minAmount = Number(selected.dataset.min || 0);
        const maxAmount = Number(selected.dataset.max || 0);
        const productRate = Number(selected.dataset.rate || 0);
        const processingFee = Number(selected.dataset.fee || 0);
        const minTerm = Number(selected.dataset.termMin || 1);
        const maxTerm = Number(selected.dataset.termMax || 1);
        const interestType = (selected.dataset.interestType || 'flat').toLowerCase();

        rate.value = productRate.toFixed(2);
        fee.value = money(processingFee);
        amount.min = String(minAmount);
        amount.max = String(maxAmount);
        term.min = String(minTerm);
        term.max = String(maxTerm);
        amountHint.textContent = `Allowed: ${money(minAmount)} – ${money(maxAmount)}`;
        termHint.textContent = `Allowed: ${minTerm} – ${maxTerm} months`;

        const principal = Number(amount.value);
        const months = Number(term.value);

        if (!principal || !months) {
            resetResults();
            rate.value = productRate.toFixed(2);
            fee.value = money(processingFee);
            formula.textContent = `Flat interest at ${productRate.toFixed(2)}% plus the ${money(processingFee)} processing fee.`;
            return;
        }

        const errors = [];
        if (principal < minAmount || principal > maxAmount) {
            errors.push(`Loan amount must be between ${money(minAmount)} and ${money(maxAmount)}.`);
        }
        if (months < minTerm || months > maxTerm || !Number.isInteger(months)) {
            errors.push(`Term must be a whole number between ${minTerm} and ${maxTerm} months.`);
        }

        if (errors.length) {
            resetResults();
            rate.value = productRate.toFixed(2);
            fee.value = money(processingFee);
            message.textContent = errors.join(' ');
            message.style.display = 'block';
            formula.textContent = `Flat interest at ${productRate.toFixed(2)}% plus the ${money(processingFee)} processing fee.`;
            return;
        }

        let interestAmount;
        if (interestType === 'flat') {
            interestAmount = principal * productRate / 100;
        } else {
            // Current PrimeLend products use flat interest. This fallback keeps
            // the calculator safe if another product type is added later.
            interestAmount = principal * productRate / 100;
        }

        const totalPayable = principal + interestAmount + processingFee;
        const monthlyPayment = totalPayable / months;

        interest.value = money(interestAmount);
        total.value = money(totalPayable);
        payment.value = money(monthlyPayment);
        formula.textContent = `${money(principal)} + ${money(interestAmount)} interest + ${money(processingFee)} fee = ${money(totalPayable)} total, divided over ${months} months = ${money(monthlyPayment)} per month.`;
    };

    product.addEventListener('change', calculate);
    amount.addEventListener('input', calculate);
    term.addEventListener('input', calculate);
    calculate();
});
