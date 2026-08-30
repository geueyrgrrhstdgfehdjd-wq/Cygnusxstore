<?php
class LogController {
    private $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }
    public function addLog($userId, $action, $details = null) {
        $stmt = $this->db->prepare("INSERT INTO logs (user_id, action, details, ip, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$userId, $action, $details, $_SERVER['REMOTE_ADDR'] ?? null]);
        return ['status' => 'success'];
    }
    public function getLogs($limit = 100, $offset = 0, $userId = null) {
        $sql = "SELECT l.*, u.username FROM logs l LEFT JOIN users u ON l.user_id = u.id";
        $params = [];
        if ($userId) { $sql .= " WHERE l.user_id = ?"; $params[] = $userId; }
        $sql .= " ORDER BY l.id DESC LIMIT ? OFFSET ?";
        $params[] = (int)$limit; $params[] = (int)$offset;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
