<div class="container py-5" style="max-width:400px;">
    <h3 class="text-center text-warning">เข้าสู่ระบบ</h3>
    <form id="loginForm">
        <div class="mb-3"><input type="text" id="loginUser" class="form-control bg-dark text-light" placeholder="Username" required></div>
        <div class="mb-3"><input type="password" id="loginPass" class="form-control bg-dark text-light" placeholder="Password" required></div>
        <button type="submit" class="btn btn-warning w-100">เข้าสู่ระบบ</button>
    </form>
    <p class="text-center mt-3"><a href="?page=register">สมัครสมาชิก</a></p>
</div>
<script>
document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const res = await CYGNUSX.login(document.getElementById('loginUser').value, document.getElementById('loginPass').value);
    if (res.status === 'success') location.href = '?page=home';
    else alert(res.message);
});
</script>
