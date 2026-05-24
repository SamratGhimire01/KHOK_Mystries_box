// main.js — K HO K global JavaScript

// Auto-dismiss flash messages
document.addEventListener('DOMContentLoaded', () => {
    const flash = document.querySelector('.flash');
    if (flash) {
        setTimeout(() => {
            flash.style.opacity = '0';
            flash.style.transition = 'opacity 0.5s ease';
            setTimeout(() => flash.remove(), 500);
        }, 3500);
    }
    // ─── Logout confirmation on all logout links ───
    document.querySelectorAll('a[href*="/logout"]').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = link.href;
            }
        });
    });
});
