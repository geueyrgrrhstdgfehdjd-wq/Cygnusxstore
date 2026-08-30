<div class="container py-5" style="max-width:400px;">
    <h3 class="text-center text-warning">สมัครสมาชิก</h3>
    <form id="registerForm">
        <div class="mb-3"><input type="text" id="regUser" class="form-control bg-dark text-light" placeholder="Username" required></div>
        <div class="mb-3"><input type="password" id="regPass" class="form-control bg-dark text-light" placeholder="Password" required></div>
        <div class="mb-3"><input type="email" id="regEmail" class="form-control bg-dark text-light" placeholder="Email (ไม่บังคับ)"></div>
        <button type="submit" class="btn btn-warning w-100">สมัคร</button>
    </form>
    <p class="text-center mt-3"><a href="?page=login">มีบัญชีแล้ว? เข้าสู่ระบบ</a></p>
</div>
<script>
document.getElementById('registerForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const res = await CYGNUSX.register(document.getElementById('regUser').value, document.getElementById('regPass').value, document.getElementById('regEmail').value);
    if (res.status === 'success') location.href = '?page=home';
    else alert(res.message);
});
</script>
