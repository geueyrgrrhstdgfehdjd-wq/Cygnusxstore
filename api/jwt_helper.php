<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
function generateJWT($userId, $username, $role = 'user') {
    return JWT::encode([
        'sub' => $userId,
        'user' => $username,
        'role' => $role,
        'iat' => time(),
        'exp' => time() + JWT_EXPIRE
    ], JWT_SECRET, 'HS256');
}
function verifyJWT($token) {
    try { return JWT::decode($token, new Key(JWT_SECRET, 'HS256')); }
    catch (Exception $e) { return null; }
}
function getAuthUser() {
    $headers = getallheaders();
    $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;
    return $token ? verifyJWT($token) : null;
}
