<?php
/**
 * Register Module - New Member
 * Proses registrasi + form HTML + password strength + animasi Ghibli
 */

require_once '../../includes/config.php';

// Redirect kalau sudah login
if (isLoggedIn()) {
    redirect('/modules/member/dashboard.php');
}

$error = '';
$success = '';

// Proses registrasi (line 20-100)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi CSRF token
    if (!validateCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid token. Please refresh page.';
        goto show_form;
    }

    // Ambil & sanitize input
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $fullName = sanitize($_POST['full_name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validasi kosong
    $required = ['username', 'email', 'full_name', 'password'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $error = 'All fields marked with * are required';
            goto show_form;
        }
    }

    // Validasi email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
        goto show_form;
    }

    // Validasi password match
    if ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
        goto show_form;
    }

    // Validasi password strength
    if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
        goto show_form;
    }

    // Cek username & email sudah ada?
    $db = Database::getInstance();
    $exists = $db->getOne(
        "SELECT id FROM users WHERE username = ? OR email = ?",
        'ss',
        $username,
        $email
    );

    if ($exists) {
        $error = 'Username or email already exists';
        goto show_form;
    }

    // Hash password & insert (line 70-90)
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $userId = $db->insert(
        "INSERT INTO users (username, email, password, full_name, phone) VALUES (?, ?, ?, ?, ?)",
        'sssss',
        $username,
        $email,
        $hashedPassword,
        $fullName,
        $phone
    );

    if ($userId) {
        // Auto login setelah registrasi
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $fullName;
        $_SESSION['user_role'] = 'member';
        $_SESSION['user_avatar'] = 'default-avatar.png';

        $success = 'Registration successful! Redirecting to dashboard...';
        
        // Redirect setelah 2 detik
        header('refresh:2;url=/modules/member/dashboard.php');
    } else {
        $error = 'Registration failed. Please try again.';
    }
}

show_form:
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?= htmlspecialchars(SITE_NAME) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --sage-50: #f6f7f6;
            --meadow-50: #f0f9f0;
            --meadow-500: #3a9a3a;
            --sage-600: #495d49;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--sage-50), var(--meadow-50));
            min-height: 100vh;
        }

        .ghibli-text {
            font-family: 'Playfair Display', serif;
            background: linear-gradient(135deg, var(--sage-600), var(--meadow-500));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .floating-leaf {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-20px) rotate(5deg); }
            66% { transform: translateY(10px) rotate(-3deg); }
        }

        .input-focus:focus {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="flex items-center justify-center px-4 py-8">

<!-- Background Animasi -->
<div class="fixed inset-0 z-0">
    <img src="../../images/leaf-1.png" alt="" class="floating-leaf absolute top-20 left-10 w-8 h-8 opacity-30">
    <img src="../../images/leaf-2.png" alt="" class="floating-leaf absolute top-40 right-20 w-6 h-6 opacity-20">
    <img src="../../images/leaf-3.png" alt="" class="floating-leaf absolute bottom-40 left-1/4 w-10 h-10 opacity-25">
</div>

<!-- Form Container -->
<div class="relative z-10 bg-white/90 backdrop-blur-md rounded-2xl shadow-xl p-8 w-full max-w-lg">
    <div class="text-center mb-6">
        <div class="flex items-center justify-center space-x-2 mb-4">
            <img src="../../images/ghibli-leaf.png" alt="Logo" class="w-10 h-10">
            <span class="ghibli-text text-2xl font-bold">Seijaku</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-800">Daftar Akun</h1>
        <p class="text-gray-600">Gabung dengan komunitas Pilates kami</p>
    </div>

    <!-- Pesan Sukses / Error -->
    <?php if ($success): ?>
        <div class="bg-green-100 text-green-700 px-4 py-3 rounded mb-4">
            <i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="bg-red-100 text-red-700 px-4 py-3 rounded mb-4">
            <i class="fas fa-exclamation-triangle mr-2"></i><?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- Form Registrasi -->
    <form method="POST" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Username *</label>
                <input name="username" required class="input-focus w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500" placeholder="Username">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Nama Lengkap *</label>
                <input name="full_name" required class="input-focus w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500" placeholder="Nama Anda">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Email *</label>
            <input type="email" name="email" required class="input-focus w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500" placeholder="email@anda.com">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">No. HP</label>
            <input type="tel" name="phone" class="input-focus w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500" placeholder="Opsional">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Password *</label>
                <input type="password" name="password" required minlength="6" class="input-focus w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500" placeholder="Min. 6 karakter">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Konfirmasi Password *</label>
                <input type="password" name="confirm_password" required class="input-focus w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500" placeholder="Ulangi password">
            </div>
        </div>

        <div class="flex items-center">
            <input type="checkbox" name="terms" required class="rounded border-gray-300 text-green-600 focus:ring-green-500">
            <label class="ml-2 text-sm text-gray-600">Saya setuju dengan <a href="#" class="text-green-600">syarat & ketentuan</a></label>
        </div>

        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg font-medium transition">
            Daftar
        </button>
    </form>

    <div class="text-center mt-6 text-sm text-gray-600">
        Sudah punya akun? <a href="login.php" class="text-green-600 font-medium">Login di sini</a>
    </div>
</div>

</body>
</html>