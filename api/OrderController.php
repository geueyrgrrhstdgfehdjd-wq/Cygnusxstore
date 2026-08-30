<?php
class OrderController {
    private $db;
    private $productCtrl;
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->productCtrl = new ProductController();
    }
    public function createOrder($userId, $items, $couponCode = null) {
        if (empty($items)) return ['status' => 'error', 'message' => 'ไม่มีสินค้าในคำสั่งซื้อ'];
        $totalPrice = 0; $orderItems = []; $discount = 0;
        $this->db->beginTransaction();
        try {
            if ($couponCode) {
                $couponCtrl = new CouponController();
                $coupon = $couponCtrl->getCouponByCode($couponCode);
                if ($coupon && $coupon['active'] && $coupon['expires_at'] > date('Y-m-d H:i:s')) {
                    $discount = $coupon['discount'];
                }
            }
            foreach ($items as $item) {
                $product = $this->productCtrl->getProduct($item['product_id']);
                if (!$product) throw new Exception("สินค้า ID {$item['product_id']} ไม่มีอยู่");
                if ($product['stock'] < $item['qty']) throw new Exception("สินค้า {$product['name']} สต็อกไม่เพียงพอ");
                $price = $product['price'];
                $subtotal = $price * $item['qty'];
                $totalPrice += $subtotal;
                $orderItems[] = ['product_id' => $product['id'], 'name' => $product['name'], 'qty' => $item['qty'], 'price' => $price, 'subtotal' => $subtotal];
            }
            if ($discount > 0) $totalPrice = max(0, $totalPrice - $discount);
            $stmt = $this->db->prepare("SELECT balance FROM users WHERE id = ? FOR UPDATE");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            if (!$user || $user['balance'] < $totalPrice) throw new Exception("ยอดเงินคงเหลือไม่เพียงพอ (ต้องการ {$totalPrice} บาท)");
            $stmt = $this->db->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
            $stmt->execute([$totalPrice, $userId]);
            $stmt = $this->db->prepare("INSERT INTO orders (user_id, total_price, discount, coupon_code, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
            $stmt->execute([$userId, $totalPrice, $discount, $couponCode]);
            $orderId = $this->db->lastInsertId();
            foreach ($orderItems as $oi) {
                $stmt = $this->db->prepare("INSERT INTO order_items (order_id, product_id, qty, price, subtotal) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$orderId, $oi['product_id'], $oi['qty'], $oi['price'], $oi['subtotal']]);
                $this->productCtrl->reduceStock($oi['product_id'], $oi['qty']);
            }
            $this->logTransaction($userId, $orderId, $totalPrice, 'order');
            $this->db->commit();
            return ['status' => 'success', 'message' => 'สั่งซื้อสำเร็จ', 'order_id' => $orderId, 'total' => $totalPrice, 'discount' => $discount];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    public function getOrders($userId, $limit = 20) {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC LIMIT ?");
        $stmt->execute([$userId, $limit]);
        $orders = $stmt->fetchAll();
        foreach ($orders as &$o) {
            $stmt2 = $this->db->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $stmt2->execute([$o['id']]);
            $o['items'] = $stmt2->fetchAll();
        }
        return $orders;
    }
    public function getAllOrders($limit = 100, $offset = 0, $status = null) {
        $sql = "SELECT o.*, u.username FROM orders o LEFT JOIN users u ON o.user_id = u.id";
        $params = [];
        if ($status) { $sql .= " WHERE o.status = ?"; $params[] = $status; }
        $sql .= " ORDER BY o.id DESC LIMIT ? OFFSET ?";
        $params[] = (int)$limit; $params[] = (int)$offset;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    public function updateStatus($orderId, $status, $adminId = null) {
        $allowed = ['pending', 'paid', 'shipped', 'completed', 'cancelled'];
        if (!in_array($status, $allowed)) return ['status' => 'error', 'message' => 'สถานะไม่ถูกต้อง'];
        $stmt = $this->db->prepare("UPDATE orders SET status = ?, admin_id = ? WHERE id = ?");
        $stmt->execute([$status, $adminId, $orderId]);
        $this->logTransaction(null, $orderId, 0, "status_change_$status");
        return ['status' => 'success', 'message' => "อัปเดตสถานะเป็น $status แล้ว"];
    }
    private function logTransaction($userId, $orderId, $amount, $type) {
        $stmt = $this->db->prepare("INSERT INTO transaction_logs (user_id, order_id, amount, type, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$userId, $orderId, $amount, $type]);
    }
}
