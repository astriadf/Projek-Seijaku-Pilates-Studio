<?php
/**
 * modules/admin/dashboard.php
 * Admin Dashboard - Setelah login
 * Menampilkan: statistik, booking pending, quick actions
 * Tema: Ghibli Meadows dengan animasi halus
 */

require_once __DIR__ . '/../../includes/config.php';

// 1. Cek login & role admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('/modules/auth/login.php');
}

// 2. Ambil data admin dari session
$adminName = $_SESSION['user_name'] ?? 'Admin';
$adminAvatar = $_SESSION['user_avatar'] ?? 'default-avatar.png';

// 3. Hitung statistik utama
$totalMembers = (int) ($pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn() ?? 0);
$totalBookings = (int) ($pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn() ?? 0);
$pendingCount = (int) ($pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn() ?? 0);
$approvedToday = (int) ($pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'confirmed' AND DATE(approved_at) = CURDATE()")->fetchColumn() ?? 0);

// 4. Ambil 10 booking pending terbaru
$pendingBookings = $pdo->prepare("
    SELECT 
        b.id AS booking_id,
        b.booking_date,
        u.full_name AS member_name,
        u.email AS member_email,
        ct.name AS class_name,
        cs.class_date,
        cs.start_time,
        cs.end_time,
        ct.difficulty
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN class_schedules cs ON b.class_schedule_id = cs.id
    JOIN class_types ct ON cs.class_type_id = ct.id
    WHERE b.status = 'pending'
    ORDER BY b.booking_date ASC
    LIMIT 10
");
$pendingBookings->execute();

// 5. Ambil jumlah member baru bulan ini
$newMembersThisMonth = (int) ($pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())")->fetchColumn() ?? 0);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?= htmlspecialchars(SITE_NAME) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
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

        .btn-primary {
            background: linear-gradient(135deg, var(--meadow-500), var(--sage-600));
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--meadow-600), var(--sage-700));
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .status-pending {
            background: #fef3c7;
            color: #d97706;
        }

        .status-confirmed {
            background: #d1fae5;
            color: #059669;
        }

        .status-completed {
            background: #dbeafe;
            color: #2563eb;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #dc2626;
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
        <a href="/modules/admin/dashboard.php" class="flex items-center space-x-3 text-green-600 font-semibold"><i class="fas fa-home"></i><span>Dashboard</span></a>
        <a href="/modules/admin/bookings.php" class="flex items-center space-x-3 text-gray-700 hover:text-green-600"><i class="fas fa-calendar-check"></i><span>Bookings</span></a>
        <a href="/modules/admin/members.php" class="flex items-center space-x-3 text-gray-700 hover:text-green-600"><i class="fas fa-users"></i><span>Members</span></a>
        <a href="/modules/admin/classes.php" class="flex items-center space-x-3 text-gray-700 hover:text-green-600"><i class="fas fa-dumbbell"></i><span>Classes</span></a>
        <a href="/modules/admin/instructors.php" class="flex items-center space-x-3 text-gray-700 hover:text-green-600"><i class="fas fa-chalkboard-teacher"></i><span>Instructors</span></a>
        <a href="/modules/admin/videos.php" class="flex items-center space-x-3 text-gray-700 hover:text-green-600"><i class="fas fa-video"></i><span>Videos</span></a>
        <a href="/modules/admin/reviews.php" class="flex items-center space-x-3 text-gray-700 hover:text-green-600"><i class="fas fa-star"></i><span>Reviews</span></a>
        <a href="/modules/auth/logout.php" class="flex items-center space-x-3 text-gray-700 hover:text-red-600"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
    </nav>

    <!-- Admin Info -->
    <div class="mt-8 text-center">
        <img src="/images/<?= htmlspecialchars($adminAvatar) ?>" alt="Avatar" class="w-12 h-12 rounded-full mx-auto mb-2">
        <p class="text-sm font-semibold"><?= htmlspecialchars($adminName) ?></p>
        <p class="text-xs text-gray-500">Administrator</p>
    </div>
</aside>

<!-- Main Content -->
<main class="flex-1 p-8 relative z-10">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="ghibli-text text-3xl font-bold mb-2">Admin Dashboard</h1>
            <p class="text-gray-600">Kelola studio Pilates dengan mudah</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white/90 backdrop-blur-md rounded-xl p-6 card-hover text-center">
                <i class="fas fa-users text-3xl text-blue-600 mb-3"></i>
                <div class="text-2xl font-bold"><?= $totalMembers ?></div>
                <div class="text-sm text-gray-600">Total Members</div>
            </div>
            <div class="bg-white/90 backdrop-blur-md rounded-xl p-6 card-hover text-center">
                <i class="fas fa-calendar-check text-3xl text-green-600 mb-3"></i>
                <div class="text-2xl font-bold"><?= $totalBookings ?></div>
                <div class="text-sm text-gray-600">Total Bookings</div>
            </div>
            <div class="bg-white/90 backdrop-blur-md rounded-xl p-6 card-hover text-center">
                <i class="fas fa-clock text-3xl text-yellow-600 mb-3"></i>
                <div class="text-2xl font-bold"><?= $pendingCount ?></div>
                <div class="text-sm text-gray-600">Pending</div>
            </div>
            <div class="bg-white/90 backdrop-blur-md rounded-xl p-6 card-hover text-center">
                <i class="fas fa-check-circle text-3xl text-purple-600 mb-3"></i>
                <div class="text-2xl font-bold"><?= $approvedToday ?></div>
                <div class="text-sm text-gray-600">Approved Today</div>
            </div>
        </div>

        <!-- Pending Bookings Table -->
        <div class="bg-white/90 backdrop-blur-md rounded-xl p-6 mb-8 card-hover">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Pending Booking Requests</h2>
                <a href="/modules/admin/bookings.php" class="text-green-600 hover:text-green-700 font-medium">View All</a>
            </div>

            <?php if ($pendingBookings->rowCount() > 0): ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-2">Member</th>
                                <th class="text-left py-2">Class</th>
                                <th class="text-left py-2">Date & Time</th>
                                <th class="text-left py-2">Level</th>
                                <th class="text-left py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($b = $pendingBookings->fetch()): ?>
                                <tr class="border-b">
                                    <td class="py-3">
                                        <div>
                                            <div class="font-medium"><?= htmlspecialchars($b['member_name']) ?></div>
                                            <div class="text-xs text-gray-500"><?= htmlspecialchars($b['member_email']) ?></div>
                                        </div>
                                    </td>
                                    <td class="py-3"><?= htmlspecialchars($b['class_name']) ?></td>
                                    <td class="py-3">
                                        <div><?= date('d M Y', strtotime($b['class_date'])) ?></div>
                                        <div class="text-xs text-gray-500"><?= date('H:i', strtotime($b['start_time'])) ?> - <?= date('H:i', strtotime($b['end_time'])) ?></div>
                                    </td>
                                    <td class="py-3"><span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs capitalize"><?= $b['difficulty'] ?></span></td>
                                    <td class="py-3">
                                        <div class="flex gap-2">
                                            <button onclick="approveBooking(<?= $b['booking_id'] ?>)" class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white rounded text-xs">Approve</button>
                                            <button onclick="rejectBooking(<?= $b['booking_id'] ?>)" class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded text-xs">Reject</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-gray-500">No pending bookings. <a href="/modules/member/book-class.php" class="text-green-600 font-medium">Check member area</a></p>
            <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white/90 backdrop-blur-md rounded-xl p-6 card-hover">
            <h2 class="text-xl font-semibold mb-4">Quick Actions</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="/modules/admin/members.php" class="btn-primary text-white py-3 rounded-lg text-center font-medium"><i class="fas fa-users mr-2"></i>Manage Members</a>
                <a href="/modules/admin/classes.php" class="bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg text-center font-medium"><i class="fas fa-dumbbell mr-2"></i>Manage Classes</a>
                <a href="/modules/admin/instructors.php" class="bg-purple-600 hover:bg-purple-700 text-white py-3 rounded-lg text-center font-medium"><i class="fas fa-chalkboard-teacher mr-2"></i>Manage Instructors</a>
            </div>
        </div>
    </div>
</main>

<!-- JavaScript (akan dipisah ke main.js nanti) -->
<script>
    function approveBooking(bookingId) {
        if (confirm('Approve this booking?')) {
            fetch('/api/bookings/admin.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ booking_id: bookingId, action: 'approve' })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to approve');
                }
            });
        }
    }

    function rejectBooking(bookingId) {
        const reason = prompt('Reason for rejection (optional):');
        if (reason !== null) {
            fetch('/api/bookings/admin.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ booking_id: bookingId, action: 'reject', reason: reason })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to reject');
                }
            });
        }
    }

    // Add entrance animations
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.card-hover');

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    anime({
                        targets: entry.target,
                        opacity: [0, 1],
                        translateY: [30, 0],
                        duration: 600,
                        delay: index * 100,
                        easing: 'easeOutQuart'
                    });
                    observer.unobserve(entry.target);
                }
            });
        });

        cards.forEach(card => {
            card.style.opacity = '0';
            observer.observe(card);
        });
    });
</script>

</body>
</html>