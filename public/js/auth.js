// auth.js — K HO K Login & Register shared scripts

document.addEventListener('DOMContentLoaded', () => {

    // ─── Password visibility toggle ───
    const toggleBtn = document.getElementById('togglePwd');
    const pwdInput  = document.getElementById('password') || document.getElementById('reg_password');
    if (toggleBtn && pwdInput) {
        toggleBtn.addEventListener('click', () => {
            pwdInput.type = pwdInput.type === 'password' ? 'text' : 'password';
            toggleBtn.textContent = pwdInput.type === 'password' ? '👁' : '🙈';
        });
    }

    // ─── Name validation — no numbers allowed ───
const nameInput = document.getElementById('full_name');
if (nameInput) {
    nameInput.addEventListener('input', () => {
        // Strip any numbers typed
        nameInput.value = nameInput.value.replace(/[0-9]/g, '');
    });
    nameInput.addEventListener('blur', () => {
        if (nameInput.value.length < 2) {
            nameInput.style.borderColor = 'var(--error)';
            showFieldHint(nameInput, 'Name must be at least 2 letters, no numbers.');
        } else {
            nameInput.style.borderColor = 'var(--success)';
            clearFieldHint(nameInput);
        }
    });
}

// ─── Password strength validation ───
const pwdInput = document.getElementById('reg_password');
if (pwdInput) {
    pwdInput.addEventListener('input', () => {
        const val      = pwdInput.value;
        const hasUpper = /[A-Z]/.test(val);
        const hasLower = /[a-z]/.test(val);
        const hasNum   = /[0-9]/.test(val);
        const hasLen   = val.length >= 8;

        // Show strength indicator
        let strength = 0;
        if (hasLen)   strength++;
        if (hasUpper) strength++;
        if (hasLower) strength++;
        if (hasNum)   strength++;

        const bar = document.getElementById('strengthBar');
        const txt = document.getElementById('strengthText');
        if (bar && txt) {
            const colors = ['var(--error)', 'var(--error)', 'var(--warning)', '#60A5FA', 'var(--success)'];
            const labels = ['Too short', 'Weak', 'Fair', 'Good', 'Strong'];
            bar.style.width      = (strength * 25) + '%';
            bar.style.background = colors[strength];
            txt.textContent      = val.length === 0 ? '' : labels[strength];
            txt.style.color      = colors[strength];
        }

        if (!hasLen) {
            pwdInput.style.borderColor = 'var(--error)';
        } else {
            pwdInput.style.borderColor = 'var(--success)';
        }
    });
}

// ─── Confirm password match ───
const confirmPwd = document.getElementById('confirm_password');
if (confirmPwd) {
    confirmPwd.addEventListener('input', () => {
        const pwd = document.getElementById('reg_password').value;
        if (confirmPwd.value && confirmPwd.value !== pwd) {
            confirmPwd.style.borderColor = 'var(--error)';
            showFieldHint(confirmPwd, 'Passwords do not match.');
        } else if (confirmPwd.value) {
            confirmPwd.style.borderColor = 'var(--success)';
            clearFieldHint(confirmPwd);
        }
    });
}

// ─── Phone validation — must start with 97/98, exactly 10 digits ───
const phoneInput = document.getElementById('phone');
if (phoneInput) {
    phoneInput.addEventListener('input', () => {
        phoneInput.value = phoneInput.value.replace(/\D/g, '').slice(0, 10);
    });
    phoneInput.addEventListener('blur', () => {
        if (!/^(97|98)\d{8}$/.test(phoneInput.value)) {
            phoneInput.style.borderColor = 'var(--error)';
            showFieldHint(phoneInput, 'Must be 10 digits starting with 97 or 98.');
        } else {
            phoneInput.style.borderColor = 'var(--success)';
            clearFieldHint(phoneInput);
        }
    });
}

// ─── Helper functions ───
function showFieldHint(input, message) {
    clearFieldHint(input);
    const hint       = document.createElement('span');
    hint.className   = 'field-error-hint';
    hint.textContent = message;
    hint.style.cssText = 'font-size:.72rem;color:var(--error);margin-top:.2rem;display:block';
    input.parentNode.appendChild(hint);
}

function clearFieldHint(input) {
    const existing = input.parentNode.querySelector('.field-error-hint');
    if (existing) existing.remove();
}

    // ─── Form submit with fetch ───
    const loginForm    = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const activeForm   = loginForm || registerForm;

    if (activeForm) {
        activeForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const btn    = activeForm.querySelector('[type=submit]');
            const errBox = document.getElementById('formError');
            const btnText   = btn.querySelector('.btn-text');
            const btnLoader = btn.querySelector('.btn-loader');

            // Validate register passwords match
            if (registerForm) {
                const p1 = document.getElementById('reg_password').value;
                const p2 = document.getElementById('confirm_password').value;
                if (p1 !== p2) {
                    showError('Passwords do not match.', errBox);
                    return;
                }
            }

            // Loading state
            btn.disabled       = true;
            btnText.style.display  = 'none';
            btnLoader.style.display = 'inline';

            try {
                const formData = new FormData(activeForm);
                const res  = await fetch(activeForm.action, { method: 'POST', body: formData });
                const data = await res.json();

                if (data.success) {
                    window.location.href = data.redirect || '/khok/';
                } else {
                    showError(data.message || 'Something went wrong.', errBox);
                }
            } catch (err) {
                showError('Network error. Please try again.', errBox);
            } finally {
                btn.disabled        = false;
                btnText.style.display  = 'inline';
                btnLoader.style.display = 'none';
            }
        });
    }

    function showError(msg, box) {
        if (!box) return;
        box.textContent    = msg;
        box.style.display  = 'block';
        setTimeout(() => box.style.display = 'none', 5000);
    }

});