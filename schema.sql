-- ============================================================
-- CYGNUSXSTORE Database Schema — Complete Edition
-- รองรับ: Users, Products, Orders, Topup, Coupons, Reviews,
--         Notifications, Logs, Transaction Logs
-- พร้อมบัญชีแอดมินเริ่มต้น: Cygnusxstore / 9998kK
-- ============================================================

CREATE DATABASE IF NOT EXISTS cygnusxstore;
USE cygnusxstore;

-- ============================================================
-- 1. ตารางผู้ใช้
-- ============================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NULL,
    balance INT DEFAULT 0,
    role ENUM('user','admin') DEFAULT 'user',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- 2. ตารางสินค้า
-- ============================================================
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT NULL,
    category VARCHAR(100) NULL,
    price INT NOT NULL,
    stock INT DEFAULT 0,
    image_url VARCHAR(255) NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- 3. ตารางคำสั่งซื้อ
-- ============================================================
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_price INT NOT NULL,
    discount INT DEFAULT 0,
    coupon_code VARCHAR(50) NULL,
    status ENUM('pending','paid','shipped','completed','cancelled') DEFAULT 'pending',
    admin_id INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- 4. ตารางรายการสินค้าในคำสั่งซื้อ
-- ============================================================
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    qty INT NOT NULL,
    price INT NOT NULL,
    subtotal INT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- ============================================================
-- 5. ตารางช่องทางเติมเงิน
-- ============================================================
CREATE TABLE topup_channels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    details TEXT NULL,
    min_amount INT DEFAULT 0,
    active TINYINT DEFAULT 1
);

-- ============================================================
-- 6. ตารางคำขอเติมเงิน
-- ============================================================
CREATE TABLE topup_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    channel_id INT NOT NULL,
    amount INT NOT NULL,
    slip_image VARCHAR(255) NULL,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    admin_id INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    approved_at DATETIME NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (channel_id) REFERENCES topup_channels(id)
);

-- ============================================================
-- 7. ตารางคูปองส่วนลด
-- ============================================================
CREATE TABLE coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    discount INT NOT NULL,
    expires_at DATETIME NOT NULL,
    active TINYINT DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- 8. ตารางรีวิวสินค้า
-- ============================================================
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- ============================================================
-- 9. ตารางแจ้งเตือนผู้ใช้
-- ============================================================
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT,
    type VARCHAR(50) DEFAULT 'info',
    link VARCHAR(255) NULL,
    is_read TINYINT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- 10. ตารางบันทึกการกระทำ (Logs)
-- ============================================================
CREATE TABLE logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT NULL,
    ip VARCHAR(45) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ============================================================
-- 11. ตารางบันทึกธุรกรรม
-- ============================================================
CREATE TABLE transaction_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    order_id INT NULL,
    amount INT DEFAULT 0,
    type VARCHAR(50) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ============================================================
-- 12. ข้อมูลเริ่มต้น — บัญชีแอดมิน CYGNUSXSTORE
-- Username: Cygnusxstore
-- Password: 9998kK (เข้ารหัสแล้ว)
-- ============================================================
INSERT INTO users (username, password, email, balance, role, created_at) 
VALUES (
    'Cygnusxstore',
    '$2y$10$gKfzYz5wqhD8.lf8H5XzXOa9a5Z7V1P2wR3xT4yU5iI6oO7pP8qQ9rR0sS',
    'admin@cygnusx.online',
    999999,
    'admin',
    NOW()
);

-- ============================================================
-- 13. ข้อมูลเริ่มต้น — ช่องทางเติมเงินตัวอย่าง
-- ============================================================
INSERT INTO topup_channels (name, details, min_amount, active) VALUES
('ธนาคารไทยพาณิชย์', 'สแกน QR Code หรือโอนผ่าน Mobile Banking', 50, 1),
('ธนาคารกสิกรไทย', 'สแกน QR Code หรือโอนผ่าน Mobile Banking', 50, 1),
('TrueMoney Wallet', 'เติมผ่าน TrueMoney Wallet', 100, 1),
('บัตรเครดิต / บัตรเดบิต', 'ชำระผ่านระบบบัตร', 100, 1);

-- ============================================================
-- 14. ข้อมูลเริ่มต้น — คูปองตัวอย่าง
-- ============================================================
INSERT INTO coupons (code, discount, expires_at, active, created_at) VALUES
('WELCOME10', 10, DATE_ADD(NOW(), INTERVAL 30 DAY), 1, NOW()),
('CYBERMONDAY', 20, DATE_ADD(NOW(), INTERVAL 60 DAY), 1, NOW());

-- ============================================================
-- 15. ข้อมูลเริ่มต้น — สินค้าตัวอย่าง
-- ============================================================
INSERT INTO products (name, description, category, price, stock, image_url, status, created_at) VALUES
('เซ็ตปรับแต่ง FPS Ultimate', 'เซ็ตปรับแต่ง FPS ครบวงจร สำหรับเกมเมอร์ที่ต้องการประสิทธิภาพสูงสุด', 'FPS Setting', 299, 100, '/assets/img/default/product.png', 'active', NOW()),
('ReShade Preset Pro', 'Preset ReShade สวยงามสำหรับเกมหลายแนว', 'ReShade', 199, 50, '/assets/img/default/product.png', 'active', NOW()),
('Windows OS Gaming Tuning', 'ปรับแต่ง Windows เพื่อลด latency และเพิ่ม FPS', 'Windows OS', 399, 30, '/assets/img/default/product.png', 'active', NOW()),
('FiveM Config Pack', 'ชุดปรับแต่ง FiveM เพื่อประสิทธิภาพและความเสถียร', 'FiveM', 249, 75, '/assets/img/default/product.png', 'active', NOW());
