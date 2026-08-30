<?php
class ProductController {
    private $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }
    public function getProducts($category = null, $limit = 100, $offset = 0, $search = null) {
        $sql = "SELECT * FROM products WHERE status = 'active'";
        $params = [];
        if ($category) { $sql .= " AND category = ?"; $params[] = $category; }
        if ($search) { $sql .= " AND (name LIKE ? OR description LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
        $sql .= " ORDER BY id DESC LIMIT ? OFFSET ?";
        $params[] = (int)$limit; $params[] = (int)$offset;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    public function getProduct($id) {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE id = ? AND status = 'active'");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    public function createProduct($data) {
        $stmt = $this->db->prepare("INSERT INTO products (name, description, category, price, stock, image_url, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$data['name'], $data['description'], $data['category'], $data['price'], $data['stock'], $data['image_url'] ?? null, $data['status'] ?? 'active']);
        return ['status' => 'success', 'id' => $this->db->lastInsertId()];
    }
    public function updateProduct($id, $data) {
        $fields = []; $params = [];
        foreach (['name', 'description', 'category', 'price', 'stock', 'image_url', 'status'] as $field) {
            if (isset($data[$field])) { $fields[] = "$field = ?"; $params[] = $data[$field]; }
        }
        if (empty($fields)) return ['status' => 'error', 'message' => 'ไม่มีข้อมูลที่จะอัปเดต'];
        $params[] = $id;
        $sql = "UPDATE products SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return ['status' => 'success', 'message' => 'อัปเดตสินค้าสำเร็จ'];
    }
    public function deleteProduct($id) {
        $stmt = $this->db->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        return ['status' => 'success', 'message' => 'ลบสินค้าสำเร็จ'];
    }
    public function reduceStock($id, $qty) {
        $stmt = $this->db->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
        $stmt->execute([$qty, $id, $qty]);
        return $stmt->rowCount() > 0;
    }
    public function getCategories() {
        $stmt = $this->db->prepare("SELECT DISTINCT category FROM products WHERE status = 'active' ORDER BY category");
        $stmt->execute();
        return array_column($stmt->fetchAll(), 'category');
    }
}
