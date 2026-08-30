<?php
$id = (int)($_GET['id'] ?? 0);
?>
<div class="container py-4" id="productDetail">
    <div class="text-center"><div class="spinner-border text-warning"></div></div>
</div>
<script>
(async function() {
    const id = <?= $id ?>;
    if (!id) { document.getElementById('productDetail').innerHTML = '<p class="text-danger">ไม่พบสินค้า</p>'; return; }
    const res = await CYGNUSX.getProduct(id);
    if (res.status !== 'success' || !res.product) {
        document.getElementById('productDetail').innerHTML = '<p class="text-danger">ไม่พบสินค้า</p>';
        return;
    }
    const p = res.product;
    document.getElementById('productDetail').innerHTML = `
        <div class="row">
            <div class="col-md-6"><img src="${p.image_url || '/assets/img/default/product.png'}" class="img-fluid rounded" style="max-height:400px;object-fit:cover;"></div>
            <div class="col-md-6">
                <h2>${p.name}</h2>
                <p class="text-muted">${p.description || 'ไม่มีคำอธิบาย'}</p>
                <h4 class="text-warning">฿${p.price}</h4>
                <p>คงเหลือ: ${p.stock} ชิ้น</p>
                <button class="btn btn-warning" onclick="addToCart(${p.id})">เพิ่มในตะกร้า</button>
                <a href="?page=checkout&product=${p.id}" class="btn btn-success">ซื้อเลย</a>
                <div id="reviewsSection" class="mt-4"></div>
            </div>
        </div>
    `;
    // Load reviews
    const revRes = await CYGNUSX.getReviews(id);
    if (revRes.status === 'success') {
        let html = '<h5>รีวิว</h5>';
        revRes.reviews.forEach(r => {
            html += `<div class="border-bottom border-secondary py-2"><strong>${r.username}</strong> ★${r.rating} <span class="text-muted small">${new Date(r.created_at).toLocaleString('th-TH')}</span><p>${r.comment}</p></div>`;
        });
        document.getElementById('reviewsSection').innerHTML = html;
    }
})();
function addToCart(productId) {
    CYGNUSX.addToCart(productId).then(res => {
        if (res.status === 'success') alert('เพิ่มสินค้าในตะกร้าแล้ว!');
        else alert('เกิดข้อผิดพลาด');
    });
}
</script>
