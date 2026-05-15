// admin_dashboard.js — K HO K Admin Charts

document.addEventListener('DOMContentLoaded', () => {

    const d = window.KHOK_CHARTS;
    if (!d) return;

    const purple  = '#A855F7';
    const green   = '#10B981';
    const amber   = '#F59E0B';
    const blue    = '#3B82F6';
    const pink    = '#EC4899';
    const gray    = '#71717A';

    Chart.defaults.color           = '#A1A1AA';
    Chart.defaults.borderColor     = '#262626';
    Chart.defaults.font.family     = "'DM Sans', sans-serif";

    // ── Revenue Line Chart ──
    new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
            labels:   d.revenue.labels.length ? d.revenue.labels : ['No data'],
            datasets: [{
                label:           'Revenue (Rs.)',
                data:            d.revenue.data.length ? d.revenue.data : [0],
                borderColor:     purple,
                backgroundColor: 'rgba(168,85,247,0.08)',
                borderWidth:     2,
                pointBackgroundColor: purple,
                pointRadius:     4,
                fill:            true,
                tension:         0.4,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' Rs. ' + Number(ctx.raw).toLocaleString()
                    }
                }
            },
            scales: {
                x: { grid: { color: '#1a1a1a' } },
                y: {
                    grid: { color: '#1a1a1a' },
                    ticks: {
                        callback: v => 'Rs.' + Number(v).toLocaleString()
                    }
                }
            }
        }
    });

    // ── Box Pie Chart ──
    new Chart(document.getElementById('boxPieChart'), {
        type: 'doughnut',
        data: {
            labels:   d.boxes.labels.length ? d.boxes.labels : ['No orders'],
            datasets: [{
                data:            d.boxes.data.length ? d.boxes.data : [1],
                backgroundColor: [gray, blue, green, purple, pink, amber],
                borderColor:     '#0D0D0D',
                borderWidth:     3,
                hoverOffset:     6,
            }]
        },
        options: {
            responsive: true,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 16, font: { size: 11 } }
                }
            }
        }
    });

    // ── Status Bar Chart ──
    const statusColors = {
        placed:    gray,
        confirmed: blue,
        packed:    amber,
        shipped:   purple,
        delivered: green,
        cancelled: '#EF4444',
    };
    new Chart(document.getElementById('statusBarChart'), {
        type: 'bar',
        data: {
            labels:   d.status.labels.length ? d.status.labels : ['No data'],
            datasets: [{
                label:           'Orders',
                data:            d.status.data.length ? d.status.data : [0],
                backgroundColor: d.status.labels.map(s => statusColors[s] || gray),
                borderRadius:    6,
                borderSkipped:   false,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false } },
                y: { grid: { color: '#1a1a1a' }, ticks: { precision: 0 } }
            }
        }
    });

});