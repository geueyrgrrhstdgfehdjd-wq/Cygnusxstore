<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'cygnusxstore');
define('DB_USER', 'root');
define('DB_PASS', '');
define('JWT_SECRET', 'cygnusx-secret-key-2026-change-me');
define('JWT_EXPIRE', 86400);
define('SITE_URL', 'https://cygnusx.online');
define('ADMIN_EMAIL', 'admin@cygnusx.online');
define('TIMEZONE', 'Asia/Bangkok');
date_default_timezone_set(TIMEZONE);
error_reporting(E_ALL);
ini_set('display_errors', 1);
