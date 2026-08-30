<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fa-solid fa-chart-pie me-2" style="color:#facc15;"></i> Dashboard</h2>
    <span class="text-muted small">อัปเดตล่าสุด: <span id="lastUpdate">กำลังโหลด...</span></span>
</div>
<div class="row g-4 mb-4" id="statsContainer">
    <div class="col-md-3"><div class="admin-card text-center"><div class="stat-value" id="totalUsers">-</div><div class="stat-label">ผู้ใช้ทั้งหมด</div></div></div>
    <div class="col-md-3"><div class="admin-card text-center"><div class="stat-value" id="totalProducts">-</div><div class="stat-label">สินค้า</div></div></div>
    <div class="col-md-3"><div class="admin-card text-center"><div class="stat-value" id="totalOrders">-</div><div class="stat-label">คำสั่งซื้อ</div></div></div>
    <div class="col-md-3"><div class="admin-card text-center"><div class="stat-value" id="revenue">-</div><div class="stat-label">รายได้รวม</div></div></div>
</div>
<div class="row g-4 mb-4">
    <div class="col-md-8"><div class="admin-card"><canvas id="revenueChart"></canvas></div></div>
    <div class="col-md-4"><div class="admin-card"><h6>Pending</h6><div>คำสั่งซื้อรอ: <span id="pendingOrders" class="text-warning">-</span></div><div>เติมเงินรอ: <span id="pendingTopups" class="text-warning">-</span></div></div></div>
</div>
<div class="admin-card">
    <h5 class="mb-3"><i class="fa-solid fa-clock me-2"></i> คำสั่งซื้อล่าสุด</h5>
    <div id="recentOrders"><p class="text-muted">กำลังโหลด...</p></div>
</div>
<script>
async function loadDashboard() {
    const stats = await CYGNUSX.adminGetStats();
    if (stats.status === 'success') {
        document.getElementById('totalUsers').textContent = stats.stats.total_users;
        document.getElementById('totalProducts').textContent = stats.stats.total_products;
        document.getElementById('totalOrders').textContent = stats.stats.total_orders;
        document.getElementById('revenue').textContent = '฿' + stats.stats.revenue;
        document.getElementById('pendingOrders').textContent = stats.stats.pending_orders;
        document.getElementById('pendingTopups').textContent = stats.stats.pending_topups;
        document.getElementById('lastUpdate').textContent = new Date().toLocaleString('th-TH');
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const months = stats.stats.monthly_revenue.map(m => m.month).reverse();
        const totals = stats.stats.monthly_revenue.map(m => m.total).reverse();
        new Chart(ctx, { type: 'bar', data: { labels: months, datasets: [{ label: 'รายได้ (บาท)', data: totals, backgroundColor: '#facc1566', borderColor: '#facc15', borderWidth: 2 }] }, options: { responsive: true, plugins: { legend: { labels: { color: '#e2e8f0' } } }, scales: { y: { ticks: { color: '#94a3b8' } }, x: { ticks: { color: '#94a3b8' } } } } });
    }
    const orders = await CYGNUSX.adminGetOrders();
    if (orders.status === 'success') {
        let html = '<table class="table table-dark-custom"><thead><tr><th>#</th><th>ผู้ใช้</th><th>รวม</th><th>สถานะ</th><th>วันที่</th></tr></thead><tbody>';
        orders.orders.slice(0, 10).forEach(o => {
            const statusColor = { pending: 'warning', paid: 'info', shipped: 'primary', completed: 'success', cancelled: 'danger' }[o.status] || 'secondary';
            html += `<tr><td>#${o.id}</td><td>${o.username || 'N/A'}</td><td>฿${o.total_price}</td><td><span class="badge-status bg-${statusColor}">${o.status}</span></td><td>${new Date(o.created_at).toLocaleString('th-TH')}</td></tr>`;
        });
        html += '</tbody></table>';
        document.getElementById('recentOrders').innerHTML = html;
    }
}
loadDashboard();
setInterval(loadDashboard, 30000);
</script>
