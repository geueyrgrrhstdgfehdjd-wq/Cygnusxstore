<?php
class NotificationController {
    private $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }
    public function createNotification($userId, $title, $message, $type = 'info', $link = null) {
        $stmt = $this->db->prepare("INSERT INTO notifications (user_id, title, message, type, link, is_read, created_at) VALUES (?, ?, ?, ?, ?, 0, NOW())");
        $stmt->execute([$userId, $title, $message, $type, $link]);
        return ['status' => 'success', 'id' => $this->db->lastInsertId()];
    }
    public function getNotifications($userId, $limit = 20) {
        $stmt = $this->db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY id DESC LIMIT ?");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }
    public function markAsRead($id, $userId) {
        $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        return ['status' => 'success'];
    }
    public function markAllRead($userId) {
        $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
        $stmt->execute([$userId]);
        return ['status' => 'success'];
    }
}
