<?php
class UserController {
    private $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }
    public function register($username, $password, $email = null) {
        if (strlen($username) < 3 || strlen($password) < 4) {
            return ['status' => 'error', 'message' => 'Username ต้อง >=3 ตัว, Password >=4 ตัว'];
        }
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) return ['status' => 'error', 'message' => 'Username นี้มีอยู่แล้ว'];
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (username, password, email, balance, role, created_at) VALUES (?, ?, ?, 0, 'user', NOW())");
        $stmt->execute([$username, $hashed, $email]);
        $id = $this->db->lastInsertId();
        $token = generateJWT($id, $username);
        return ['status' => 'success', 'message' => 'สมัครสำเร็จ', 'token' => $token, 'user' => ['id' => $id, 'username' => $username]];
    }
    public function login($username, $password) {
        $stmt = $this->db->prepare("SELECT id, username, password, balance, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if (!$user || !password_verify($password, $user['password'])) {
            return ['status' => 'error', 'message' => 'Username หรือ Password ไม่ถูกต้อง'];
        }
        $token = generateJWT($user['id'], $user['username'], $user['role']);
        return ['status' => 'success', 'message' => 'เข้าสู่ระบบสำเร็จ', 'token' => $token, 'user' => [
            'id' => $user['id'], 'username' => $user['username'], 'balance' => (int)$user['balance'], 'role' => $user['role']
        ]];
    }
    public function getProfile($userId) {
        $stmt = $this->db->prepare("SELECT id, username, email, balance, role, created_at FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
    public function updateProfile($userId, $data) {
        $fields = []; $params = [];
        if (isset($data['email'])) { $fields[] = "email = ?"; $params[] = $data['email']; }
        if (isset($data['password'])) { $fields[] = "password = ?"; $params[] = password_hash($data['password'], PASSWORD_DEFAULT); }
        if (empty($fields)) return ['status' => 'error', 'message' => 'ไม่มีข้อมูลที่จะอัปเดต'];
        $params[] = $userId;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return ['status' => 'success', 'message' => 'อัปเดตโปรไฟล์สำเร็จ'];
    }
    public function addBalance($userId, $amount) {
        $stmt = $this->db->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        $stmt->execute([$amount, $userId]);
        return ['status' => 'success', 'message' => 'เติมเงินสำเร็จ', 'amount' => $amount];
    }
    public function getUsers($limit = 100, $offset = 0) {
        $stmt = $this->db->prepare("SELECT id, username, email, balance, role, created_at FROM users ORDER BY id DESC LIMIT ? OFFSET ?");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }
    public function deleteUser($userId) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return ['status' => 'success', 'message' => 'ลบผู้ใช้สำเร็จ'];
    }
}
