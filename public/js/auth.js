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

    // ─── Register: password match check ───
    const confirmPwd = document.getElementById('confirm_password');
    if (confirmPwd) {
        confirmPwd.addEventListener('input', () => {
            const pwd = document.getElementById('reg_password').value;
            if (confirmPwd.value && confirmPwd.value !== pwd) {
                confirmPwd.style.borderColor = 'var(--error)';
            } else {
                confirmPwd.style.borderColor = '';
            }
        });
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