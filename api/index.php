<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once 'Database.php';
require_once 'jwt_helper.php';
require_once 'UserController.php';
require_once 'ProductController.php';
require_once 'OrderController.php';
require_once 'CouponController.php';
require_once 'ReviewController.php';
require_once 'TopupController.php';
require_once 'NotificationController.php';
require_once 'LogController.php';
require_once 'StatsController.php';
require_once 'CartController.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = $_GET['route'] ?? '';
$parts = explode('/', trim($path, '/'));
$resource = $parts[0] ?? '';
$action = $parts[1] ?? '';
$id = $parts[2] ?? null;
$data = json_decode(file_get_contents('php://input'), true) ?? [];

$response = ['status' => 'error', 'message' => 'ไม่พบเส้นทาง'];

try {
    switch ($resource) {
        case 'user':
            $ctrl = new UserController();
            if ($method === 'POST' && $action === 'register') {
                $response = $ctrl->register($data['username'], $data['password'], $data['email'] ?? null);
            } elseif ($method === 'POST' && $action === 'login') {
                $response = $ctrl->login($data['username'], $data['password']);
            } elseif ($method === 'GET' && $action === 'profile') {
                $auth = getAuthUser();
                if (!$auth) { $response = ['status' => 'error', 'message' => 'Unauthorized']; break; }
                $response = ['status' => 'success', 'user' => $ctrl->getProfile($auth->sub)];
            } elseif ($method === 'PUT' && $action === 'profile') {
                $auth = getAuthUser();
                if (!$auth) { $response = ['status' => 'error', 'message' => 'Unauthorized']; break; }
                $response = $ctrl->updateProfile($auth->sub, $data);
            } elseif ($method === 'POST' && $action === 'balance' && $id) {
                $auth = getAuthUser();
                if (!$auth || $auth->role !== 'admin') { $response = ['status' => 'error', 'message' => 'Unauthorized']; break; }
                $response = $ctrl->addBalance($id, $data['amount']);
            } elseif ($method === 'GET' && $action === 'list') {
                $auth = getAuthUser();
                if (!$auth || $auth->role !== 'admin') { $response = ['status' => 'error', 'message' => 'Unauthorized']; break; }
                $response = ['status' => 'success', 'users' => $ctrl->getUsers()];
            } else {
                $response = ['status' => 'error', 'message' => 'Invalid user action'];
            }
            break;
        case 'products':
            $ctrl = new ProductController();
            if ($method === 'GET') {
                $category = $_GET['category'] ?? null;
                $search = $_GET['search'] ?? null;
                $limit = (int)($_GET['limit'] ?? 100);
                $offset = (int)($_GET['offset'] ?? 0);
                $response = ['status' => 'success', 'products' => $ctrl->getProducts($category, $limit, $offset, $search)];
            } elseif ($method === 'GET' && $action === 'detail' && $id) {
                $product = $ctrl->getProduct($id);
                $response = $product ? ['status' => 'success', 'product' => $product] : ['status' => 'error', 'message' => 'ไม่พบสินค้า'];
            } elseif ($method === 'POST') {
                $auth = getAuthUser();
                if (!$auth || $auth->role !== 'admin') { $response = ['status' => 'error', 'message' => 'Unauthorized']; break; }
                $response = $ctrl->createProduct($data);
            } elseif ($method === 'PUT' && $id) {
                $auth = getAuthUser();
                if (!$auth || $auth->role !== 'admin') { $response = ['status' => 'error', 'message' => 'Unauthorized']; break; }
                $response = $ctrl->updateProduct($id, $data);
            } elseif ($method === 'DELETE' && $id) {
                $auth = getAuthUser();
                if (!$auth || $auth->role !== 'admin') { $response = ['status' => 'error', 'message' => 'Unauthorized']; break; }
                $response = $ctrl->deleteProduct($id);
            } else {
                $response = ['status' => 'error', 'message' => 'Invalid product action'];
            }
            break;
        case 'orders':
            $ctrl = new OrderController();
            if ($method === 'POST' && $action === 'create') {
                $auth = getAuthUser();
                if (!$auth) { $response = ['status' => 'error', 'message' => 'Unauthorized']; break; }
                $response = $ctrl->createOrder($auth->sub, $data['items'] ?? [], $data['coupon'] ?? null);
            } elseif ($method === 'GET' && $action === 'history') {
                $auth = getAuthUser();
                if (!$auth) { $response = ['status' => 'error', 'message' => 'Unauthorized']; break; }
                $response = ['status' => 'success', 'orders' => $ctrl->getOrders($auth->sub)];
            } elseif ($method === 'GET' && $action === 'list') {
                $auth = getAuthUser();
                if (!$auth || $auth->role !== 'admin') { $response = ['status' => 'error', 'message' => 'Unauthorized']; break; }
                $status = $_GET['status'] ?? null;
                $response = ['status' => 'success', 'orders' => $ctrl->getAllOrders(100, 0, $status)];
            } elseif ($method === 'PUT' && $action === 'status' && $id) {
                $auth = getAuthUser();
                if (!$auth || $auth->role !== 'admin') { $response = ['status' => 'error', 'message' => 'Unauthorized']; break; }
                $response = $ctrl->updateStatus($id, $data['status'], $auth->sub);
            } else {
                $response = ['status' => 'error', 'message' => 'Invalid order action'];
            }
            break;
        case 'topup':
            $ctrl = new TopupController();
            if ($method === 'GET' && $action === 'channels') {
                $response = ['status' => 'success', 'channels' => $ctrl->getActiveChannels()];
            } elseif ($method === 'POST' && $action === 'request') {
                $auth = getAuthUser();
                if (!$auth) { $response = ['status' => 'error', 'message' => 'Unauthorized']; break; }
                $response = $ctrl->requestTopup($auth->sub, $data['channel_id'], $data['amount'], $data['slip_image'] ?? null);
            } elseif ($method === 'POST' && $action === 'approve' && $id) {
                $auth = getAuthUser();
                if (!$auth || $auth->role !== 'admin') { $response = ['status' => 'error', 'message' => 'Unauthorized']; break; }
                $response = $ctrl->approveTopup($id, $auth->sub);
            } elseif ($method === 'GET' && $action === 'pending') {
                $auth = getAuthUser();
                if (!$auth || $auth->role !== 'admin') { $response = ['status' => 'error', 'message' => 'Unauthorized']; break; }
                $response = ['status' => 'success', 'requests' => $ctrl->getPendingRequests()];
            } elseif ($method === 'GET' && $action === 'history') {
                $auth = getAuthUser();
                if (!$auth) { $response = ['status' => 'error', 'message' => 'Unauthorized']; break; }
                $response = ['status' => 'success', 'history' => $ctrl->getHistory($auth->sub)];
            } else {
                $response = ['status' => 'error', 'message' => 'Invalid topup action'];
            }
            break;
        case 'coupons':
            $ctrl = new CouponController();
            if ($method === 'GET') {
                $auth = getAuthUser();
                if (!$auth || $auth->role !== 'admin') { $response = ['status' => 'error', 'message' => 'Unauthorized']; break; }
                $response = ['status' => 'success', 'coupons' => $ctrl->getCoupons()];
            } elseif ($method === 'POST') {
                $auth = getAuthUser();
                if (!$auth || $auth->role !== 'admin') { $response = ['status' => 'error', 'message' => 'Unauthorized']; break; }
                $response = $ctrl->createCoupon($data);
            } elseif ($method === 'DELETE' && $id) {
                $auth = getAuthUser();
                if (!$auth || $auth->role !== 'admin') { $response = ['status' => 'error', 'message' => 'Unauthorized']; break; }
                $response = $ctrl->deleteCoupon($id);
            } else {
                $response = ['status' => 'error', 'message' => 'Invalid coupon action'];
            }
            break;
        case 'reviews':
            $ctrl = new ReviewController();
            if ($method === 'POST' && $action === 'add') {
                $auth = getAuthUser();
                if (!$auth) { $response = ['status' => 'error', 'message' => 'Unauthorized']; break; }
                $response = $ctrl->addReview($auth->sub, $data['product_id'], $data['rating'], $data['comment']);
            } elseif ($method === 'GET' && $id) {
                $response = ['status' => 'success', 'reviews' => $ctrl->getReviews($id)];
            } elseif ($method === 'DELETE' && $id) {
                $auth = getAuthUser();
                if (!$auth) { $response = ['status' => 'error', 'message' => 'Unauthorized']; break; }
                $response = $ctrl->deleteReview($id, $auth->sub);
            } else {
                $response = ['status' => 'error', 'message' => 'Invalid review action'];
            }
            break;
        case 'notifications':
            $ctrl = new NotificationController();
            if ($method === 'GET') {
                $auth = getAuthUser();
                if (!$auth) { $response = ['status' => 'error', 'message' => 'Unauthorized']; break; }
                $response = ['status' => 'success', 'notifications' => $ctrl->getNotifications($auth->sub)];
            } elseif ($method === 'POST' && $action === 'read' && $id) {
                $auth = getAuthUser();
                if (!$auth) { $response = ['status' => 'error', 'message' => 'Unauthorized']; break; }
                $response = $ctrl->markAsRead($id, $auth->sub);
            } elseif ($method === 'POST' && $action === 'read_all') {
                $auth = getAuthUser();
                if (!$auth) { $response = ['status' => 'error', 'message' => 'Unauthorized']; break; }
                $response = $ctrl->markAllRead($auth->sub);
            } else {
                $response = ['status' => 'error', 'message' => 'Invalid notification action'];
            }
            break;
        case 'logs':
            $ctrl = new LogController();
            if ($method === 'GET') {
                $auth = getAuthUser();
                if (!$auth || $auth->role !== 'admin') { $response = ['status' => 'error', 'message' => 'Unauthorized']; break; }
                $userId = $_GET['user_id'] ?? null;
                $response = ['status' => 'success', 'logs' => $ctrl->getLogs(100, 0, $userId)];
            } else {
                $response = ['status' => 'error', 'message' => 'Invalid log action'];
            }
            break;
        case 'stats':
            $ctrl = new StatsController();
            if ($method === 'GET') {
                $auth = getAuthUser();
                if (!$auth || $auth->role !== 'admin') { $response = ['status' => 'error', 'message' => 'Unauthorized']; break; }
                $response = ['status' => 'success', 'stats' => $ctrl->getDashboardStats()];
            } else {
                $response = ['status' => 'error', 'message' => 'Invalid stats action'];
            }
            break;
        case 'cart':
            $ctrl = new CartController();
            if ($method === 'GET') {
                $response = ['status' => 'success', 'cart' => $ctrl->getCart()];
            } elseif ($method === 'POST' && $action === 'add') {
                $response = $ctrl->addToCart($data['product_id'], $data['qty'] ?? 1);
            } elseif ($method === 'DELETE' && $id) {
                $response = $ctrl->removeFromCart($id);
            } elseif ($method === 'DELETE' && $action === 'clear') {
                $response = $ctrl->clearCart();
            } else {
                $response = ['status' => 'error', 'message' => 'Invalid cart action'];
            }
            break;
        default:
            $response = ['status' => 'error', 'message' => 'API endpoint not found'];
    }
} catch (Exception $e) {
    $response = ['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
