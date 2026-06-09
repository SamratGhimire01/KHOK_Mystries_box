// track.js — K HO K Order Tracking v2

document.addEventListener('DOMContentLoaded', () => {

    const form     = document.getElementById('trackSearchForm');
    const result   = document.getElementById('trackResult');
    const empty    = document.getElementById('trackEmpty');
    const loading  = document.getElementById('trackLoading');

    // Auto-search if ref in URL
    const urlRef = new URLSearchParams(window.location.search).get('ref');
    if (urlRef) {
        document.getElementById('orderRefInput').value = urlRef;
        doSearch(urlRef);
    }

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
            const res  = await fetch(`/khok/api/orders/track?ref=${encodeURIComponent(ref)}`);
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

        // ── Order info card ──
        const payLabel = order.payment_status === 'paid'
            ? '✅ Payment Confirmed'
            : '⏳ Payment Pending';

        document.getElementById('trackOrderCard').innerHTML = `
            <div class="toi-top">
                <div>
                    <p class="toi-ref">Order #${order.order_ref}</p>
                    <p class="toi-box">${order.box_name}</p>
                </div>
                <div class="toi-status-wrap">
                    <span class="toi-status toi-status--${order.order_status}">
                        ${order.order_status.toUpperCase()}
                    </span>
                    <span class="toi-pay-badge">${payLabel}</span>
                </div>
            </div>
            <div class="toi-details">
                <div class="toi-detail">
                    <span class="toi-detail-label">Customer</span>
                    <span class="toi-detail-value">${order.customer_name}</span>
                </div>
                <div class="toi-detail">
                    <span class="toi-detail-label">City</span>
                    <span class="toi-detail-value">📍 ${order.city}</span>
                </div>
                <div class="toi-detail">
                    <span class="toi-detail-label">Payment Method</span>
                    <span class="toi-detail-value">${order.payment_method.toUpperCase()}</span>
                </div>
                <div class="toi-detail">
                    <span class="toi-detail-label">Order Date</span>
                    <span class="toi-detail-value">🗓 ${order.created_at}</span>
                </div>
                <div class="toi-detail">
                    <span class="toi-detail-label">Amount Paid</span>
                    <span class="toi-detail-value" style="color:var(--accent);font-weight:700">
                        Rs. ${Number(order.total_amount).toLocaleString()}
                    </span>
                </div>
                ${tracking && tracking.estimated_date ? `
                <div class="toi-detail">
                    <span class="toi-detail-label">Est. Delivery</span>
                    <span class="toi-detail-value">📅 ${tracking.estimated_date}</span>
                </div>` : ''}
            </div>
        `;

        // ── Stepper ──
        const statusMap = {
            placed:    0,
            confirmed: 1,
            packed:    1,
            shipped:   2,
            delivered: 3
        };
        const level = statusMap[order.order_status] ?? 0;
        const steps = ['step1', 'step2', 'step3'];

        steps.forEach((id, i) => {
            const el = document.getElementById(id);
            el.classList.remove('active', 'done');
            if (i < level)       el.classList.add('done');
            else if (i === level) el.classList.add('active');
        });

        // ── Delivery proof ──
        const proofEl = document.getElementById('trackProof');
        if (tracking && tracking.proof_image) {
            document.getElementById('proofImg').src =
                `/khok/uploads/delivery_proof/${tracking.proof_image}`;
            document.getElementById('proofNote').textContent =
                tracking.delivery_note || '';
            proofEl.style.display = 'block';
        } else {
            proofEl.style.display = 'none';
        }

        // ── WhatsApp — always goes to BUSINESS number ──
        const businessNumber = '9779823045928';
        const msg = encodeURIComponent(
            `Hi K HO K! I need help with my order:\n` +
            `Order Ref: ${order.order_ref}\n` +
            `Box: ${order.box_name}\n` +
            `Name: ${order.customer_name}`
        );
        document.getElementById('waLink').href =
            `https://wa.me/${businessNumber}?text=${msg}`;
        document.getElementById('trackWa').style.display = 'flex';
    }

});
// this is the page that will track the tracking system.
// this is another day, another code. I am writing this code to track the order status of the customers. I am using fetch API to get the order details from the server and then displaying it on the page. I am also using some CSS classes to style the page. I hope this code will work fine and will help the customers to track their orders easily.
// I am also adding a WhatsApp link for the customers to contact us in case they have any issues with their orders. The WhatsApp link will open a chat with our business number and pre-fill a message with the order details. This way, the customers can easily reach out to us without having to type everything manually. I hope this will improve our customer support and make it easier for our customers to get help when they need it.
// This code is part of the K HO K Order Tracking system, which allows customers to check the status of their orders in real-time. By entering their order reference number, customers can see the current status of their order, estimated delivery date, and even view delivery proof if available. This enhances the customer experience by providing transparency and easy access to order information.
// Overall, this tracking system is designed to improve customer satisfaction and streamline communication between customers and our support team. By providing clear and accessible order information, we aim to build trust and ensure a positive experience for all our customers.
// I will continue to monitor the performance of this tracking system and make any necessary improvements based on customer feedback and usage patterns. My goal is to create a seamless and user-friendly experience for our customers, allowing them to easily track their orders and get the support they need when they need it. I am committed to providing excellent service and ensuring that our customers have a positive experience with K HO K.