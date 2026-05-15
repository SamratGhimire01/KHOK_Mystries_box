// checkout.js — K HO K Checkout Page

document.addEventListener('DOMContentLoaded', () => {

    const form = document.getElementById('checkoutForm');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const btn       = document.getElementById('checkoutBtn');
        const errBox    = document.getElementById('checkoutError');
        const btnText   = btn.querySelector('.btn-text');
        const btnLoader = btn.querySelector('.btn-loader');

        btn.disabled           = true;
        btnText.style.display  = 'none';
        btnLoader.style.display = 'inline';
        errBox.style.display   = 'none';

        try {
            const formData = new FormData(form);
            const res  = await fetch('/khok/api/orders/create.php', {
                method: 'POST',
                body:   formData
            });
            const data = await res.json();

            if (data.success) {
                // Redirect to payment gateway or confirmation
                window.location.href = data.redirect;
            } else {
                errBox.textContent   = data.message || 'Something went wrong. Please try again.';
                errBox.style.display = 'block';
            }
        } catch {
            errBox.textContent   = 'Network error. Please try again.';
            errBox.style.display = 'block';
        } finally {
            btn.disabled            = false;
            btnText.style.display   = 'inline';
            btnLoader.style.display = 'none';
        }
    });

    // Highlight selected payment method visually
    document.querySelectorAll('.payment-option input').forEach(radio => {
        radio.addEventListener('change', () => {
            document.querySelectorAll('.payment-card').forEach(c => c.style.borderColor = '');
        });
    });

});