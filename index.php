<?php
$page = $_GET['page'] ?? 'home';
$allowed = ['home', 'shop', 'product', 'topup', 'cart', 'checkout', 'profile', 'orders', 'contact', 'register', 'login', 'logout'];
if (!in_array($page, $allowed)) $page = 'home';
include __DIR__ . "/pages/{$page}.php";
