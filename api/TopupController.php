<?php
class TopupController {
    private $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }
    public function getActiveChannels() {
        $stmt = $this->db->prepare("SELECT * FROM topup_channels WHERE active = 1 ORDER BY id");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function createChannel($name, $details, $minAmount = 0) {
        $stmt = $this->db->prepare("INSERT INTO topup_channels (name, details, min_amount, active) VALUES (?, ?, ?, 1)");
        $stmt->execute([$name, $details, $minAmount]);
        return ['status' => 'success', 'id' => $this->db->lastInsertId()];
    }
    public function requestTopup($userId, $channelId, $amount, $slipImage = null) {
        $stmt = $this->db->prepare("INSERT INTO topup_requests (user_id, channel_id, amount, slip_image, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
        $stmt->execute([$userId, $channelId, $amount, $slipImage]);
        return ['status' => 'success', 'request_id' => $this->db->lastInsertId()];
    }
    public function approveTopup($requestId, $adminId) {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("SELECT user_id, amount FROM topup_requests WHERE id = ? AND status = 'pending' FOR UPDATE");
            $stmt->execute([$requestId]);
            $req = $stmt->fetch();
            if (!$req) throw new Exception("ไม่พบคำขอหรือสถานะไม่ใช่ pending");
            $stmt2 = $this->db->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
            $stmt2->execute([$req['amount'], $req['user_id']]);
            $stmt3 = $this->db->prepare("UPDATE topup_requests SET status = 'approved', admin_id = ?, approved_at = NOW() WHERE id = ?");
            $stmt3->execute([$adminId, $requestId]);
            $stmt4 = $this->db->prepare("INSERT INTO transaction_logs (user_id, amount, type, created_at) VALUES (?, ?, 'topup', NOW())");
            $stmt4->execute([$req['user_id'], $req['amount']]);
            $this->db->commit();
            return ['status' => 'success', 'message' => 'เติมเงินเรียบร้อย'];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    public function getPendingRequests($limit = 100) {
        $stmt = $this->db->prepare("SELECT r.*, u.username, c.name as channel_name FROM topup_requests r LEFT JOIN users u ON r.user_id = u.id LEFT JOIN topup_channels c ON r.channel_id = c.id WHERE r.status = 'pending' ORDER BY r.id ASC LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    public function getHistory($userId, $limit = 20) {
        $stmt = $this->db->prepare("SELECT * FROM topup_requests WHERE user_id = ? ORDER BY id DESC LIMIT ?");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }
}
