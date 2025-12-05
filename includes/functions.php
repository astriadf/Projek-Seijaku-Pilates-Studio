<?php
/**
 * Global Helper Functions
 * Dipakai di SEMUA module/module
 */

// 1. CEK LOGIN
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// 2. CEK ROLE
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// 3. REDIRECT
function redirect($url) {
    header("Location: " . SITE_URL . $url);
    exit();
}

// 4. SANITIZE INPUT
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// 5. FORMAT RUPIAH
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// 6. FORMAT TANGGAL INDONESIA
function formatTanggal($tanggal) {
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $t = strtotime($tanggal);
    return date('d', $t) . ' ' . $bulan[date('n', $t)] . ' ' . date('Y', $t);
}

// 7. GET CURRENT USER (kalau sudah login)
function currentUser() {
    if (!isLoggedIn()) return null;
    
    static $user = null;
    if ($user === null) {
        $db = Database::getInstance();
        $user = $db->getOne(
            "SELECT * FROM users WHERE id = ?",
            'i',
            $_SESSION['user_id']
        );
    }
    return $user;
}

// 8. SHOW ALERT (untuk flash message)
function showAlert($message, $type = 'info') {
    $color = [
        'info'    => 'blue',
        'success' => 'green',
        'error'   => 'red',
        'warning' => 'yellow'
    ];
    
    echo '<div class="alert alert-' . $color[$type] . ' p-4 rounded-lg mb-4">';
    echo '<i class="fas fa-' . ($type === 'error' ? 'exclamation' : 'info') . '-circle mr-2"></i>';
    echo htmlspecialchars($message);
    echo '</div>';
}

// 9. CSRF TOKEN (security)
function csrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// 10. VALIDATE CSRF
function validateCsrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}