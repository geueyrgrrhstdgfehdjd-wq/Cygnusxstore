<div class="container py-4">
    <div class="text-center mb-4">
        <h1 style="color:#facc15;font-family:'Orbitron',sans-serif;">CYGNUSXSTORE</h1>
        <p class="text-muted">จำหน่ายสินค้าและบริการออนไลน์ 24 ชม.</p>
    </div>
    <div class="row" id="homeProducts"></div>
</div>
<script>
(async function() {
    const res = await CYGNUSX.getProducts(null, null, 8);
    if (res.status === 'success') {
        let html = '';
        res.products.forEach(p => {
            html += `<div class="col-md-3 col-6 mb-3">
                <div class="card bg-dark text-light border-secondary" onclick="window.location.href='?page=product&id=${p.id}'">
                    <img src="${p.image_url || '/assets/img/default/product.png'}" class="card-img-top" style="height:150px;object-fit:cover;">
                    <div class="card-body">
                        <h6 class="card-title">${p.name}</h6>
                        <p class="card-text text-warning">฿${p.price}</p>
                        <span class="badge bg-secondary">${p.category || 'ทั่วไป'}</span>
                    </div>
                </div>
            </div>`;
        });
        document.getElementById('homeProducts').innerHTML = html;
    }
})();
</script>
