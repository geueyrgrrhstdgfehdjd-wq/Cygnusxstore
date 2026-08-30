<?php
class ReviewController {
    private $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }
    public function addReview($userId, $productId, $rating, $comment) {
        $stmt = $this->db->prepare("INSERT INTO reviews (user_id, product_id, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$userId, $productId, $rating, $comment]);
        return ['status' => 'success', 'id' => $this->db->lastInsertId()];
    }
    public function getReviews($productId, $limit = 50) {
        $stmt = $this->db->prepare("SELECT r.*, u.username FROM reviews r LEFT JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.id DESC LIMIT ?");
        $stmt->execute([$productId, $limit]);
        return $stmt->fetchAll();
    }
    public function deleteReview($id, $userId = null) {
        $sql = "DELETE FROM reviews WHERE id = ?";
        $params = [$id];
        if ($userId) { $sql .= " AND user_id = ?"; $params[] = $userId; }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return ['status' => 'success'];
    }
}
