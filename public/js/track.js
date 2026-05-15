// track.js — K HO K Order Tracking

document.addEventListener('DOMContentLoaded', () => {

    const form      = document.getElementById('trackSearchForm');
    const result    = document.getElementById('trackResult');
    const empty     = document.getElementById('trackEmpty');
    const loading   = document.getElementById('trackLoading');

    // Auto-search if ref in URL
    const urlRef = new URLSearchParams(window.location.search).get('ref');
    if (urlRef) doSearch(urlRef);

    if (form) {
        form.addEventListener('submit', e => {
            e.preventDefault();
            const ref = document.getElementById('orderRefInput').value.trim();
            if (ref) doSearch(ref);
        });
    }

    async function doSearch(ref) {
        result.style.display  = 'none';
        empty.style.display   = 'none';
        loading.style.display = 'block';

        try {
            const res  = await fetch(`/khok/api/orders/track.php?ref=${encodeURIComponent(ref)}`);
            const data = await res.json();
            loading.style.display = 'none';

            if (data.success && data.order) {
                renderOrder(data.order, data.tracking);
                result.style.display = 'flex';
            } else {
                empty.style.display = 'block';
            }
        } catch {
            loading.style.display = 'none';
            empty.style.display   = 'block';
        }
    }

    function renderOrder(order, tracking) {
        // Order card
        document.getElementById('trackOrderCard').innerHTML = `
            <p class="toc-ref">Order #${order.order_ref}</p>
            <p class="toc-box">${order.box_name}</p>
            <div class="toc-meta">
                <span>👤 ${order.customer_name}</span>
                <span>📍 ${order.city}</span>
                <span>💳 ${order.payment_method.toUpperCase()}</span>
                <span>🗓 ${order.created_at}</span>
            </div>
            <span class="toc-status status-${order.order_status}">${order.order_status.toUpperCase()}</span>
        `;

        // Stepper
        const statusMap = { placed:0, confirmed:1, packed:1, shipped:2, delivered:3 };
        const level = statusMap[order.order_status] ?? 0;
        const steps = ['step1','step2','step3'];
        steps.forEach((id, i) => {
            const el = document.getElementById(id);
            el.classList.remove('active','done');
            if (i < level)      el.classList.add('done');
            else if (i === level) el.classList.add('active');
        });

        // Delivery proof
        const proofEl = document.getElementById('trackProof');
        if (tracking?.proof_image) {
            document.getElementById('proofImg').src = `/khok/uploads/delivery_proof/${tracking.proof_image}`;
            document.getElementById('proofNote').textContent = tracking.delivery_note || '';
            proofEl.style.display = 'block';
        } else {
            proofEl.style.display = 'none';
        }

        // WhatsApp link
        const waEl = document.getElementById('trackWa');
        if (order.phone) {
            const phone = order.phone.replace(/\D/g,'');
            const msg   = encodeURIComponent(`Hi K HO K! I'm checking on my order: ${order.order_ref}`);
            document.getElementById('waLink').href = `https://wa.me/977${phone}?text=${msg}`;
            waEl.style.display = 'flex';
        }
    }

});