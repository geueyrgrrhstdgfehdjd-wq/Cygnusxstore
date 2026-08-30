const CYGNUSX = {
    API: '/api/index.php?route=',
    token: localStorage.getItem('cygnusx_token'),
    user: JSON.parse(localStorage.getItem('cygnusx_user') || 'null'),

    login: async function(username, password) {
        const res = await fetch(this.API + 'user/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password })
        });
        const data = await res.json();
        if (data.status === 'success') {
            this.token = data.token;
            this.user = data.user;
            localStorage.setItem('cygnusx_token', data.token);
            localStorage.setItem('cygnusx_user', JSON.stringify(data.user));
        }
        return data;
    },
    register: async function(username, password, email) {
        const res = await fetch(this.API + 'user/register', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password, email })
        });
        const data = await res.json();
        if (data.status === 'success') {
            this.token = data.token;
            this.user = data.user;
            localStorage.setItem('cygnusx_token', data.token);
            localStorage.setItem('cygnusx_user', JSON.stringify(data.user));
        }
        return data;
    },
    logout: function() {
        this.token = null;
        this.user = null;
        localStorage.removeItem('cygnusx_token');
        localStorage.removeItem('cygnusx_user');
    },
    getProfile: async function() {
        const res = await this.fetchWithAuth('user/profile');
        return res.json();
    },
    getProducts: async function(category, search, limit = 100, offset = 0) {
        let url = `${this.API}products?limit=${limit}&offset=${offset}`;
        if (category) url += `&category=${encodeURIComponent(category)}`;
        if (search) url += `&search=${encodeURIComponent(search)}`;
        const res = await fetch(url);
        return res.json();
    },
    getProduct: async function(id) {
        const res = await fetch(`${this.API}products/detail/${id}`);
        return res.json();
    },
    getCart: async function() {
        const res = await fetch(`${this.API}cart`);
        return res.json();
    },
    addToCart: async function(productId, qty = 1) {
        const res = await fetch(`${this.API}cart/add`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ product_id: productId, qty })
        });
        return res.json();
    },
    removeFromCart: async function(productId) {
        const res = await fetch(`${this.API}cart/${productId}`, { method: 'DELETE' });
        return res.json();
    },
    clearCart: async function() {
        const res = await fetch(`${this.API}cart/clear`, { method: 'DELETE' });
        return res.json();
    },
    createOrder: async function(items, coupon = null) {
        const res = await this.fetchWithAuth('orders/create', {
            method: 'POST',
            body: JSON.stringify({ items, coupon })
        });
        return res.json();
    },
    getOrderHistory: async function() {
        const res = await this.fetchWithAuth('orders/history');
        return res.json();
    },
    getTopupChannels: async function() {
        const res = await fetch(`${this.API}topup/channels`);
        return res.json();
    },
    requestTopup: async function(channelId, amount, slipImage = null) {
        const res = await this.fetchWithAuth('topup/request', {
            method: 'POST',
            body: JSON.stringify({ channel_id: channelId, amount, slip_image: slipImage })
        });
        return res.json();
    },
    addReview: async function(productId, rating, comment) {
        const res = await this.fetchWithAuth('reviews/add', {
            method: 'POST',
            body: JSON.stringify({ product_id: productId, rating, comment })
        });
        return res.json();
    },
    getReviews: async function(productId) {
        const res = await fetch(`${this.API}reviews/${productId}`);
        return res.json();
    },
    getNotifications: async function() {
        const res = await this.fetchWithAuth('notifications');
        return res.json();
    },
    markNotificationRead: async function(id) {
        const res = await this.fetchWithAuth(`notifications/read/${id}`, { method: 'POST' });
        return res.json();
    },
    adminGetUsers: async function() {
        const res = await this.fetchWithAuth('user/list');
        return res.json();
    },
    adminGetOrders: async function(status = null) {
        let url = 'orders/list';
        if (status) url += `?status=${status}`;
        const res = await this.fetchWithAuth(url);
        return res.json();
    },
    adminUpdateOrderStatus: async function(orderId, status) {
        const res = await this.fetchWithAuth(`orders/status/${orderId}`, {
            method: 'PUT',
            body: JSON.stringify({ status })
        });
        return res.json();
    },
    adminGetPendingTopups: async function() {
        const res = await this.fetchWithAuth('topup/pending');
        return res.json();
    },
    adminApproveTopup: async function(requestId) {
        const res = await this.fetchWithAuth(`topup/approve/${requestId}`, { method: 'POST' });
        return res.json();
    },
    adminGetStats: async function() {
        const res = await this.fetchWithAuth('stats');
        return res.json();
    },
    adminCreateProduct: async function(data) {
        const res = await this.fetchWithAuth('products', {
            method: 'POST',
            body: JSON.stringify(data)
        });
        return res.json();
    },
    adminUpdateProduct: async function(id, data) {
        const res = await this.fetchWithAuth(`products/${id}`, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
        return res.json();
    },
    adminDeleteProduct: async function(id) {
        const res = await this.fetchWithAuth(`products/${id}`, { method: 'DELETE' });
        return res.json();
    },
    fetchWithAuth: async function(endpoint, options = {}) {
        if (!this.token) throw new Error('Unauthorized');
        options.headers = {
            ...options.headers,
            'Authorization': `Bearer ${this.token}`,
            'Content-Type': 'application/json'
        };
        const res = await fetch(this.API + endpoint, options);
        return res;
    }
};

document.addEventListener('DOMContentLoaded', function() {
    if (CYGNUSX.token && CYGNUSX.user) {
        document.querySelectorAll('.auth-required').forEach(el => el.style.display = 'block');
        document.querySelectorAll('.guest-only').forEach(el => el.style.display = 'none');
        const balanceEl = document.querySelector('#userBalance');
        if (balanceEl) balanceEl.textContent = CYGNUSX.user.balance;
    } else {
        document.querySelectorAll('.auth-required').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.guest-only').forEach(el => el.style.display = 'block');
    }
});
