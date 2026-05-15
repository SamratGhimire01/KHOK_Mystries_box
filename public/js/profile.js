// profile.js — K HO K Profile Page (edit mode + validation)

document.addEventListener('DOMContentLoaded', () => {

    // ─── Scroll reveal for order cards ───
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity   = '1';
                entry.target.style.transform = 'translateY(0)';
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.order-card').forEach((card, i) => {
        card.style.opacity    = '0';
        card.style.transform  = 'translateY(16px)';
        card.style.transition = `opacity 0.4s ease ${i * 0.07}s, transform 0.4s ease ${i * 0.07}s`;
        observer.observe(card);
    });

    // ─── Live name validation (no numbers) ───
    const nameInput = document.getElementById('editName');
    if (nameInput) {
        nameInput.addEventListener('input', () => {
            nameInput.value = nameInput.value.replace(/[0-9]/g, '');
        });
    }

    // ─── Live phone validation (max 10 digits only) ───
    const phoneInput = document.getElementById('editPhone');
    if (phoneInput) {
        phoneInput.addEventListener('input', () => {
            phoneInput.value = phoneInput.value.replace(/\D/g, '').slice(0, 10);
        });
    }

    // ─── Edit form submit ───
    const editForm = document.getElementById('editForm');
    if (editForm) {
        editForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const btn       = document.getElementById('saveBtn');
            const errBox    = document.getElementById('editError');
            const sucBox    = document.getElementById('editSuccess');
            const btnText   = btn.querySelector('.btn-text');
            const btnLoader = btn.querySelector('.btn-loader');

            // Client-side validation
            const name  = document.getElementById('editName').value.trim();
            const phone = document.getElementById('editPhone').value.trim();

            errBox.style.display = 'none';
            sucBox.style.display = 'none';

            if (!/^[a-zA-Z\s'\-\.]{2,100}$/.test(name)) {
                errBox.textContent   = 'Name must contain only letters — no numbers.';
                errBox.style.display = 'block';
                return;
            }
            if (!/^(97|98)\d{8}$/.test(phone)) {
                errBox.textContent   = 'Phone must be 10 digits and start with 97 or 98.';
                errBox.style.display = 'block';
                return;
            }

            btn.disabled            = true;
            btnText.style.display   = 'none';
            btnLoader.style.display = 'inline';

            try {
                const res  = await fetch('/khok/api/auth/update_profile.php', {
                    method: 'POST',
                    body:   new FormData(editForm)
                });
                const data = await res.json();

                if (data.success) {
                    // Update display name
                    document.getElementById('displayName').textContent = name;
                    sucBox.textContent   = data.message;
                    sucBox.style.display = 'block';
                    // Switch back to view mode after 1.5s
                    setTimeout(() => toggleEdit(), 1500);
                } else {
                    errBox.textContent   = data.message;
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
    }
});

// ─── Toggle edit/view mode ───
function toggleEdit() {
    const view = document.getElementById('viewMode');
    const edit = document.getElementById('editMode');
    const btn  = document.getElementById('editToggleBtn');

    if (edit.style.display === 'none') {
        view.style.display = 'none';
        edit.style.display = 'block';
        btn.textContent    = '✕ Cancel Edit';
    } else {
        view.style.display = 'block';
        edit.style.display = 'none';
        btn.textContent    = '✏️ Edit Profile';
    }
}