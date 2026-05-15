// boxes.js — FAQ accordion + rarity bar animation

document.addEventListener('DOMContentLoaded', () => {

    // FAQ accordion
    document.querySelectorAll('.faq-q').forEach(btn => {
        btn.addEventListener('click', () => {
            const item = btn.parentElement;
            const isOpen = item.classList.contains('open');
            document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('open'));
            if (!isOpen) item.classList.add('open');
        });
    });

    // Animate rarity bars on scroll
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.querySelectorAll('.rarity-fill').forEach(fill => {
                    const w = fill.style.width;
                    fill.style.width = '0';
                    setTimeout(() => fill.style.width = w, 100);
                });
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.2 });

    document.querySelectorAll('.box-listing-card').forEach(card => observer.observe(card));

});