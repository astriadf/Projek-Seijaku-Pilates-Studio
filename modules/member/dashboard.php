<?php
/**
 * modules/member/dashboard.php
 * Member Dashboard - Setelah login
 * Menampilkan: statistik, booking upcoming, achievements, quick actions
 */

require_once __DIR__ . '/../../includes/config.php';

// 1. Cek login & role
if (!isLoggedIn()) {
    redirect('/modules/auth/login.php');
}

// 2. Ambil data user dari session
$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];
$userAvatar = $_SESSION['user_avatar'] ?? 'default-avatar.png';

// 3. Hitung statistik
$totalBookings = (int) ($pdo->query("SELECT COUNT(*) FROM bookings WHERE user_id = $userId")->fetchColumn() ?? 0);
$pendingBookings = (int) ($pdo->query("SELECT COUNT(*) FROM bookings WHERE user_id = $userId AND status = 'pending'")->fetchColumn() ?? 0);
$completedClasses = (int) ($pdo->query("SELECT COUNT(*) FROM bookings WHERE user_id = $userId AND status = 'completed'")->fetchColumn() ?? 0);
$badgesEarned = (int) ($pdo->query("SELECT COUNT(*) FROM user_achievements WHERE user_id = $userId")->fetchColumn() ?? 0);

// 4. Ambil 3 booking upcoming
$upcoming = $pdo->prepare("
    SELECT b.*, cs.class_date, cs.start_time, cs.end_time, ct.name AS class_name, i.name AS instructor_name
    FROM bookings b
    JOIN class_schedules cs ON b.class_schedule_id = cs.id
    JOIN class_types ct ON cs.class_type_id = ct.id
    JOIN instructors i ON cs.instructor_id = i.id
    WHERE b.user_id = ? AND b.status = 'confirmed' AND cs.class_date >= CURDATE()
    ORDER BY cs.class_date ASC, cs.start_time ASC
    LIMIT 3
");
$upcoming->execute([$userId]);
$upcomingBookings = $upcoming->fetchAll();

// 5. Ambil 4 achievement terbaru
$achievements = $pdo->prepare("
    SELECT * FROM user_achievements
    WHERE user_id = ?
    ORDER BY earned_at DESC
    LIMIT 4
");
$achievements->execute([$userId]);
$userAchievements = $achievements->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= htmlspecialchars(SITE_NAME) ?></title>
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

        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="flex">

<!-- Background Animasi -->
<div class="fixed inset-0 z-0">
    <img src="/images/leaf-1.png" alt="" class="floating-leaf absolute top-20 left-10 w-8 h-8 opacity-30">
    <img src="/images/leaf-2.png" alt="" class="floating-leaf absolute top-40 right-20 w-6 h-6 opacity-20">
    <img src="/images/leaf-3.png" alt="" class="floating-leaf absolute bottom-40 left-1/4 w-10 h-10 opacity-25">
</div>

<!-- Sidebar -->
<aside class="w-64 bg-white/90 backdrop-blur-md shadow-lg p-6 relative z-10">
    <div class="text-center mb-8">
        <img src="/images/ghibli-leaf.png" alt="Logo" class="w-12 h-12 mx-auto mb-2">
        <span class="ghibli-text text-xl font-bold">Seijaku</span>
    </div>

    <nav class="space-y-4">
        <a href="/modules/member/dashboard.php" class="flex items-center space-x-3 text-green-600 font-semibold"><i class="fas fa-home"></i><span>Dashboard</span></a>
        <a href="/modules/member/book-class.php" class="flex items-center space-x-3 text-gray-700 hover:text-green-600"><i class="fas fa-calendar-plus"></i><span>Book Class</span></a>
        <a href="/modules/member/my-bookings.php" class="flex items-center space-x-3 text-gray-700 hover:text-green-600"><i class="fas fa-list"></i><span>My Bookings</span></a>
        <a href="/modules/member/achievements.php" class="flex items-center space-x-3 text-gray-700 hover:text-green-600"><i class="fas fa-trophy"></i><span>Achievements</span></a>
        <a href="/modules/member/profile.php" class="flex items-center space-x-3 text-gray-700 hover:text-green-600"><i class="fas fa-user"></i><span>Profile</span></a>
        <a href="/modules/auth/logout.php" class="flex items-center space-x-3 text-gray-700 hover:text-red-600"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
    </nav>

    <!-- User Info -->
    <div class="mt-8 text-center">
        <img src="/images/<?= htmlspecialchars($userAvatar) ?>" alt="Avatar" class="w-12 h-12 rounded-full mx-auto mb-2">
        <p class="text-sm font-semibold"><?= htmlspecialchars($userName) ?></p>
        <p class="text-xs text-gray-500">Member</p>
    </div>
</aside>

<!-- Main Content -->
<main class="flex-1 p-8 relative z-10">
    <div class="max-w-6xl mx-auto">
        <!-- Welcome -->
        <div class="mb-8">
            <h1 class="ghibli-text text-3xl font-bold mb-2">Selamat datang, <?= htmlspecialchars($userName) ?>!</h1>
            <p class="text-gray-600">Siap untuk sesi Pilates hari ini?</p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white/90 backdrop-blur-md rounded-xl p-6 card-hover text-center">
                <i class="fas fa-calendar-check text-3xl text-green-600 mb-3"></i>
                <div class="text-2xl font-bold"><?= $totalBookings ?></div>
                <div class="text-sm text-gray-600">Total Bookings</div>
            </div>
            <div class="bg-white/90 backdrop-blur-md rounded-xl p-6 card-hover text-center">
                <i class="fas fa-clock text-3xl text-yellow-600 mb-3"></i>
                <div class="text-2xl font-bold"><?= $pendingBookings ?></div>
                <div class="text-sm text-gray-600">Pending</div>
            </div>
            <div class="bg-white/90 backdrop-blur-md rounded-xl p-6 card-hover text-center">
                <i class="fas fa-check-circle text-3xl text-blue-600 mb-3"></i>
                <div class="text-2xl font-bold"><?= $completedClasses ?></div>
                <div class="text-sm text-gray-600">Attended</div>
            </div>
            <div class="bg-white/90 backdrop-blur-md rounded-xl p-6 card-hover text-center">
                <i class="fas fa-trophy text-3xl text-purple-600 mb-3"></i>
                <div class="text-2xl font-bold"><?= $badgesEarned ?></div>
                <div class="text-sm text-gray-600">Badges</div>
            </div>
        </div>

        <!-- Upcoming Classes -->
        <div class="bg-white/90 backdrop-blur-md rounded-xl p-6 mb-8 card-hover">
            <h2 class="text-xl font-semibold mb-4">Upcoming Classes</h2>
            <?php if ($upcomingBookings): ?>
                <div class="space-y-4">
                    <?php foreach ($upcomingBookings as $b): ?>
                        <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                            <div>
                                <h3 class="font-semibold"><?= htmlspecialchars($b['class_name']) ?></h3>
                                <p class="text-sm text-gray-600"><?= htmlspecialchars($b['instructor_name']) ?></p>
                                <p class="text-xs text-gray-500"><?= date('d M Y', strtotime($b['class_date'])) ?> at <?= date('H:i', strtotime($b['start_time'])) ?></p>
                            </div>
                            <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">Confirmed</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500">No upcoming classes. <a href="/modules/member/book-class.php" class="text-green-600 font-medium">Book one now!</a></p>
            <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white/90 backdrop-blur-md rounded-xl p-6 card-hover">
            <h2 class="text-xl font-semibold mb-4">Quick Actions</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="/modules/member/book-class.php" class="bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg text-center font-medium"><i class="fas fa-plus mr-2"></i>Book Class</a>
                <a href="/modules/member/my-bookings.php" class="bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg text-center font-medium"><i class="fas fa-list mr-2"></i>My Bookings</a>
                <a href="/modules/member/achievements.php" class="bg-purple-600 hover:bg-purple-700 text-white py-3 rounded-lg text-center font-medium"><i class="fas fa-trophy mr-2"></i>Achievements</a>
            </div>
        </div>
    </div>
</main>

</body>
</html>