<h2><i class="fa-solid fa-cart-shopping me-2" style="color:#facc15;"></i> จัดการคำสั่งซื้อ</h2>
<div class="admin-card mt-3">
    <div id="ordersList"><p class="text-muted">กำลังโหลด...</p></div>
</div>
<script>
async function loadOrders() {
    const res = await CYGNUSX.adminGetOrders();
    if (res.status === 'success') {
        let html = `<table class="table table-dark-custom"><thead><tr><th>#</th><th>ผู้ใช้</th><th>รวม</th><th>ส่วนลด</th><th>สถานะ</th><th>วันที่</th><th>จัดการ</th></tr></thead><tbody>`;
        res.orders.forEach(o => {
            const statusColor = { pending: 'warning', paid: 'info', shipped: 'primary', completed: 'success', cancelled: 'danger' }[o.status] || 'secondary';
            html += `<tr>
                <td>#${o.id}</td>
                <td>${o.username || 'N/A'}</td>
                <td>฿${o.total_price}</td>
                <td>${o.discount || 0}</td>
                <td><span class="badge-status bg-${statusColor}">${o.status}</span></td>
                <td>${new Date(o.created_at).toLocaleString('th-TH')}</td>
                <td>
                    <select class="form-select form-select-sm bg-dark text-light border-secondary" onchange="updateOrderStatus(${o.id}, this.value)">
                        <option value="pending" ${o.status==='pending'?'selected':''}>pending</option>
                        <option value="paid" ${o.status==='paid'?'selected':''}>paid</option>
                        <option value="shipped" ${o.status==='shipped'?'selected':''}>shipped</option>
                        <option value="completed" ${o.status==='completed'?'selected':''}>completed</option>
                        <option value="cancelled" ${o.status==='cancelled'?'selected':''}>cancelled</option>
                    </select>
                </td>
            </tr>`;
        });
        html += '</tbody></table>';
        document.getElementById('ordersList').innerHTML = html;
    }
}
async function updateOrderStatus(orderId, status) {
    const res = await CYGNUSX.adminUpdateOrderStatus(orderId, status);
    if (res.status === 'success') loadOrders();
    else alert(res.message);
}
loadOrders();
</script>
