<?php
class CartController {
    public function getCart() {
        session_start();
        return $_SESSION['cart'] ?? [];
    }
    public function addToCart($productId, $qty = 1) {
        session_start();
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] += $qty;
        } else {
            $_SESSION['cart'][$productId] = $qty;
        }
        return ['status' => 'success', 'cart' => $_SESSION['cart']];
    }
    public function removeFromCart($productId) {
        session_start();
        unset($_SESSION['cart'][$productId]);
        return ['status' => 'success', 'cart' => $_SESSION['cart']];
    }
    public function clearCart() {
        session_start();
        $_SESSION['cart'] = [];
        return ['status' => 'success'];
    }
}
