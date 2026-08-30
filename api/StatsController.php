<?php
class StatsController {
    private $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }
    public function getDashboardStats() {
        $stats = [];
        $stmt = $this->db->query("SELECT COUNT(*) as total_users FROM users");
        $stats['total_users'] = $stmt->fetch()['total_users'];
        $stmt = $this->db->query("SELECT COUNT(*) as total_products FROM products WHERE status = 'active'");
        $stats['total_products'] = $stmt->fetch()['total_products'];
        $stmt = $this->db->query("SELECT COUNT(*) as total_orders FROM orders");
        $stats['total_orders'] = $stmt->fetch()['total_orders'];
        $stmt = $this->db->query("SELECT SUM(total_price) as revenue FROM orders WHERE status = 'completed'");
        $stats['revenue'] = (int)($stmt->fetch()['revenue'] ?? 0);
        $stmt = $this->db->query("SELECT COUNT(*) as pending_orders FROM orders WHERE status = 'pending'");
        $stats['pending_orders'] = $stmt->fetch()['pending_orders'];
        $stmt = $this->db->query("SELECT COUNT(*) as pending_topups FROM topup_requests WHERE status = 'pending'");
        $stats['pending_topups'] = $stmt->fetch()['pending_topups'];
        $stmt = $this->db->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total_price) as total FROM orders WHERE status = 'completed' GROUP BY month ORDER BY month DESC LIMIT 12");
        $stats['monthly_revenue'] = $stmt->fetchAll();
        return $stats;
    }
}
