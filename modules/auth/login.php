<?php
/**
 * Login Module - Member & Admin
 * Proses login + form HTML + animasi Ghibli
 */

require_once '../../includes/config.php';

// Redirect kalau sudah login
if (isLoggedIn()) {
    $role = $_SESSION['user_role'] ?? 'member';
    redirect($role === 'admin' ? '/modules/admin/dashboard.php' : '/modules/member/dashboard.php');
}

$error = '';
$success = '';

// Proses login (line 20-60)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi CSRF token (security)
    if (!validateCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid token. Please refresh page.';
        goto show_form; // skip ke bagian form
    }

    // Ambil input & sanitize
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validasi kosong
    if (empty($email) || empty($password)) {
        $error = 'Email dan password wajib diisi';
        goto show_form;
    }

    // Cari user (email atau username)
    $db = Database::getInstance();
    $user = $db->getOne(
        "SELECT id, username, email, password, full_name, role, avatar 
         FROM users 
         WHERE (email = ? OR username = ?) AND is_active = 1",
        'ss',
        $email,
        $email
    );

    // Cek password
    if (!$user || !password_verify($password, $user['password'])) {
        $error = 'Email/username atau password salah';
        goto show_form;
    }

    // Set session (line 50-60)
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_avatar'] = $user['avatar'];

    // Update last login
    $db->execute(
        "UPDATE users SET last_login = NOW() WHERE id = ?",
        'i',
        $user['id']
    );

    // Redirect berdasarkan role
    $redirect = $user['role'] === 'admin' 
        ? '/modules/admin/dashboard.php' 
        : '/modules/member/dashboard.php';
    
    redirect($redirect);
}

show_form:
// Bagian HTML (line 70-200)
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= SITE_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --sage-50: #f6f7f6;
            --sage-100: #e3e7e3;
            --sage-200: #c7d0c7;
            --sage-300: #a3b2a3;
            --sage-400: #7a927a;
            --sage-500: #5c755c;
            --sage-600: #495d49;
            --sage-700: #3d4d3d;
            --sage-800: #334133;
            --sage-900: #2d372d;
            --meadow-50: #f0f9f0;
            --meadow-100: #dcf2dc;
            --meadow-200: #bbe5bb;
            --meadow-300: #8dd18d;
            --meadow-400: #5bb85b;
            --meadow-500: #3a9a3a;
            --meadow-600: #2e7d2e;
            --meadow-700: #276427;
            --meadow-800: #235023;
            --meadow-900: #1f421f;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--sage-50) 0%, var(--meadow-50) 100%);
            min-height: 100vh;
        }
        
        .ghibli-text {
            font-family: 'Playfair Display', serif;
            background: linear-gradient(135deg, var(--sage-700), var(--meadow-600));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--meadow-500), var(--sage-600));
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--meadow-600), var(--sage-700));
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .floating-leaf {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-20px) rotate(5deg); }
            66% { transform: translateY(10px) rotate(-3deg); }
        }
        
        .input-focus {
            transition: all 0.3s ease;
        }
        
        .input-focus:focus {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <!-- Background Animation (floating leaves) -->
    <div class="fixed inset-0 z-0">
        <img src="../../images/leaf-1.png" alt="" class="floating-leaf absolute top-20 left-10 w-8 h-8 opacity-30">
        <img src="../../images/leaf-2.png" alt="" class="floating-leaf absolute top-40 right-20 w-6 h-6 opacity-20">
        <img src="../../images/leaf-3.png" alt="" class="floating-leaf absolute bottom-40 left-1/4 w-10 h-10 opacity-25">
    </div>
    
    <div class="relative z-10 min-h-screen flex items-center justify-center px-4">
        <div class="login-card rounded-2xl p-8 w-full max-w-md">
            <!-- Logo -->
            <div class="text-center mb-8">
                <div class="flex items-center justify-center space-x-2 mb-4">
                    <img src="../../images/ghibli-leaf.png" alt="Seijaku Logo" class="w-10 h-10">
                    <span class="ghibli-text text-2xl font-bold">Seijaku</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Welcome Back</h1>
                <p class="text-gray-600">Login to your account</p>
            </div>
            
            <!-- Flash Message -->
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 text-sm">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            
            <!-- Login Form -->
            <form method="POST" class="space-y-6" id="loginForm">
                <!-- CSRF Token (security) -->
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                
                <!-- Email/Username -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email or Username
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                        <input type="text" name="email" id="email" required
                               class="input-focus w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                               placeholder="Enter your email or username"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                </div>
                
                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" name="password" id="password" required
                               class="input-focus w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                               placeholder="Enter your password">
                        <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center" onclick="togglePassword()">
                            <i class="fas fa-eye text-gray-400" id="eye-icon"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Remember Me -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                        <span class="ml-2 text-sm text-gray-600">Remember me</span>
                    </label>
                    <a href="forgot-password.php" class="text-sm text-green-600 hover:text-green-500">
                        Forgot password?
                    </a>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" class="w-full btn-primary text-white py-3 rounded-lg font-medium">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Login
                </button>
            </form>
            
            <!-- Divider -->
            <div class="mt-8 flex items-center">
                <div class="flex-1 border-t border-gray-300"></div>
                <div class="px-4 text-sm text-gray-500">OR</div>
                <div class="flex-1 border-t border-gray-300"></div>
            </div>
            
            <!-- Register Link -->
            <div class="mt-6 text-center">
                <p class="text-gray-600">
                    Don't have an account?
                    <a href="register.php" class="text-green-600 hover:text-green-500 font-medium">
                        Register here
                    </a>
                </p>
            </div>
            
            <!-- Quick Links -->
            <div class="mt-8 text-center">
                <p class="text-sm text-gray-500 mb-2">Quick Access:</p>
                <div class="flex justify-center space-x-4">
                    <a href="../../public/index.php" class="text-green-600 hover:text-green-500 text-sm">
                        <i class="fas fa-home mr-1"></i>Home
                    </a>
                    <a href="../../public/videos.php" class="text-green-600 hover:text-green-500 text-sm">
                        <i class="fas fa-video mr-1"></i>Videos
                    </a>
                    <a href="#contact" class="text-green-600 hover:text-green-500 text-sm">
                        <i class="fas fa-phone mr-1"></i>Contact
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- JavaScript (akan dipisah ke main.js nanti) -->
    <script>
        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }
        
        // Form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                e.preventDefault();
                alert('Please fill in all fields');
                return;
            }
        });
        
        // Loading state
        this.addEventListener('submit', function() {
            const btn = this.querySelector('button[type="submit"]');
            if (this.checkValidity()) {
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Logging in...';
                btn.disabled = true;
            }
        });
    </script>
</body>
</html>