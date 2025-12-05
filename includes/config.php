<?php

// 1. DATABASE CONFIG (MySQLi)
define('DB_HOST', 'localhost');
define('DB_NAME', 'seijaku_pilates_pbg');  // ganti kalau pake nama lain
define('DB_USER', 'root');
define('DB_PASS', '');                      // kosong untuk XAMPP default
define('DB_CHARSET', 'utf8mb4');

// 2. SITE CONFIG
define('SITE_NAME', 'Seijaku Studio Pilates');
define('SITE_URL', 'http://localhost/kode-coba-baru'); // ganti sesuai folder kamu
define('SITE_EMAIL', 'info@seijaku.com');

// 3. SESSION CONFIG
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // 1 kalau pakai HTTPS
ini_set('session.cookie_samesite', 'Lax');

// 4. ERROR REPORTING (development mode)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log'); // buat folder logs dulu

// 5. TIMEZONE
date_default_timezone_set('Asia/Jakarta');

// 6. START SESSION (hanya sekali di seluruh app)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 7. REQUIRE DEPENDENCIES (hanya sekali)
require_once __DIR__ . '/functions.php';     // helper functions
require_once __DIR__ . '/../classes/Database.php'; // MySQLi OOP