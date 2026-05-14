// home.js — K HO K Home Page Scripts

document.addEventListener('DOMContentLoaded', () => {

    // ─── Box particle burst on click ───
    const box       = document.getElementById('mysteryBox');
    const particles = document.getElementById('boxParticles');
    const colors    = ['#A855F7','#7C3AED','#C084FC','#E879F9','#F59E0B','#fff'];

    if (box && particles) {
        box.addEventListener('click', () => {
            particles.innerHTML = '';
            for (let i = 0; i < 18; i++) {
                const p   = document.createElement('span');
                const angle = (i / 18) * 360;
                const dist  = 60 + Math.random() * 60;
                const size  = 4 + Math.random() * 6;
                const color = colors[Math.floor(Math.random() * colors.length)];
                p.style.cssText = `
                    position:absolute;
                    width:${size}px; height:${size}px;
                    background:${color};
                    border-radius:50%;
                    top:50%; left:50%;
                    transform:translate(-50%,-50%);
                    animation:particle 0.7s ease forwards;
                    --dx:${Math.cos(angle * Math.PI/180) * dist}px;
                    --dy:${Math.sin(angle * Math.PI/180) * dist}px;
                `;
                particles.appendChild(p);
            }
            // inject keyframe once
            if (!document.getElementById('particleStyle')) {
                const s = document.createElement('style');
                s.id = 'particleStyle';
                s.textContent = `@keyframes particle {
                    0%   { transform: translate(-50%,-50%) scale(1); opacity:1; }
                    100% { transform: translate(calc(-50% + var(--dx)), calc(-50% + var(--dy))) scale(0); opacity:0; }
                }`;
                document.head.appendChild(s);
            }
            setTimeout(() => particles.innerHTML = '', 800);
        });
    }

    // ─── Scroll-reveal for steps ───
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity    = '1';
                entry.target.style.transform  = 'translateY(0)';
            }
        });
    }, { threshold: 0.15 });

    document.querySelectorAll('.step, .box-card, .product-teaser-card').forEach(el => {
        el.style.opacity   = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        observer.observe(el);
    });

    // ─── Smooth scroll for anchor links ───
    document.querySelectorAll('a[href^="#"]').forEach(a => {
        a.addEventListener('click', e => {
            e.preventDefault();
            const target = document.querySelector(a.getAttribute('href'));
            if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

});