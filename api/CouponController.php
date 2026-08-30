<?php
class CouponController {
    private $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }
    public function getCouponByCode($code) {
        $stmt = $this->db->prepare("SELECT * FROM coupons WHERE code = ? AND active = 1");
        $stmt->execute([$code]);
        return $stmt->fetch();
    }
    public function createCoupon($data) {
        $stmt = $this->db->prepare("INSERT INTO coupons (code, discount, expires_at, active, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$data['code'], $data['discount'], $data['expires_at'], $data['active'] ?? 1]);
        return ['status' => 'success', 'id' => $this->db->lastInsertId()];
    }
    public function getCoupons($limit = 100) {
        $stmt = $this->db->prepare("SELECT * FROM coupons ORDER BY id DESC LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    public function deleteCoupon($id) {
        $stmt = $this->db->prepare("DELETE FROM coupons WHERE id = ?");
        $stmt->execute([$id]);
        return ['status' => 'success'];
    }
}
