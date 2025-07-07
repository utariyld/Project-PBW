<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/admin-functions.php';

// Check if user is admin
require_admin();

// Get dashboard statistics
$stats = get_dashboard_stats();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - TemanKosan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #00c851;
            --secondary-color: #ff69b4;
            --accent-color: #ff1493;
            --dark-color: #2c3e50;
            --light-color: #f8f9fa;
            --white: #ffffff;
            --gray-100: #f8f9fa;
            --gray-200: #e9ecef;
            --gray-300: #dee2e6;
            --gray-400: #ced4da;
            --gray-500: #adb5bd;
            --gray-600: #6c757d;
            --gray-700: #495057;
            --gray-800: #343a40;
            --gray-900: #212529;
            --shadow-sm: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            --shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            --shadow-lg: 1rem 3rem rgba(0, 0, 0, 0.175);
            --border-radius: 0.75rem;
            --border-radius-lg: 1rem;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: var(--gray-800);
            background: var(--gray-100);
        }

        /* Admin Layout */
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .admin-sidebar {
            width: 280px;
            background: linear-gradient(135deg, var(--dark-color), #34495e);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-logo {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary-color);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .nav-item {
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.5rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: var(--transition);
            border-left: 3px solid transparent;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-left-color: var(--primary-color);
        }

        .nav-link i {
            width: 20px;
            text-align: center;
        }

        /* Main Content */
        .admin-main {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
        }

        .admin-header {
            background: white;
            padding: 1.5rem 2rem;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--gray-800);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .header-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
            font-weight: 600;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #00a844);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 200, 81, 0.3);
        }

        .btn-outline {
            background: white;
            color: var(--gray-600);
            border: 2px solid var(--gray-300);
        }

        .btn-outline:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 1.5rem;
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .stat-icon.kos {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .stat-icon.users {
            background: linear-gradient(135deg, #f093fb, #f5576c);
        }

        .stat-icon.bookings {
            background: linear-gradient(135deg, #4facfe, #00f2fe);
        }

        .stat-icon.revenue {
            background: linear-gradient(135deg, #43e97b, #38f9d7);
        }

        .stat-content h3 {
            font-size: 2rem;
            font-weight: 800;
            color: var(--gray-800);
            margin-bottom: 0.5rem;
        }

        .stat-content p {
            color: var(--gray-600);
            font-weight: 600;
        }

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }

        .content-card {
            background: white;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .card-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--gray-800);
        }

        .card-body {
            padding: 1.5rem 2rem;
        }

        /* Recent Bookings */
        .booking-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid var(--gray-200);
        }

        .booking-item:last-child {
            border-bottom: none;
        }

        .booking-info h4 {
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0.25rem;
        }

        .booking-info p {
            color: var(--gray-600);
            font-size: 0.9rem;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-confirmed {
            background: #d4edda;
            color: #155724;
        }

        .status-completed {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            gap: 1rem;
        }

        .action-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border: 2px solid var(--gray-200);
            border-radius: var(--border-radius);
            text-decoration: none;
            color: var(--gray-700);
            transition: var(--transition);
        }

        .action-item:hover {
            border-color: var(--primary-color);
            background: rgba(0, 200, 81, 0.05);
        }

        .action-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .admin-main {
                margin-left: 0;
                padding: 1rem;
            }

            .content-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <a href="dashboard.php" class="sidebar-logo">
                    <i class="fas fa-home"></i>
                    TemanKosan Admin
                </a>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-item">
                    <a href="dashboard.php" class="nav-link active">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="manage-kos.php" class="nav-link">
                        <i class="fas fa-home"></i>
                        <span>Kelola Kos</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="manage-users.php" class="nav-link">
                        <i class="fas fa-users"></i>
                        <span>Kelola User</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="manage-bookings.php" class="nav-link">
                        <i class="fas fa-calendar-check"></i>
                        <span>Kelola Booking</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="account.php" class="nav-link">
                        <i class="fas fa-user"></i>
                        <span>Profil Saya</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="../logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-header">
                <h1 class="page-title">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard Admin
                </h1>
                <div class="header-actions">
                    <a href="../index.php" class="btn btn-outline">
                        <i class="fas fa-globe"></i>
                        Lihat Website
                    </a>
                </div>
            </div>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon kos">
                        <i class="fas fa-home"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['total_kos']); ?></h3>
                        <p>Total Kos Aktif</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon users">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['total_users']); ?></h3>
                        <p>Total User</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon bookings">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['total_bookings']); ?></h3>
                        <p>Total Booking</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon revenue">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo format_currency($stats['total_revenue']); ?></h3>
                        <p>Total Pendapatan</p>
                    </div>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="content-grid">
                <!-- Recent Bookings -->
                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title">Booking Terbaru</h3>
                        <a href="manage-bookings.php" class="btn btn-outline">Lihat Semua</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($stats['recent_bookings'])): ?>
                            <p style="text-align: center; color: var(--gray-600); padding: 2rem;">
                                Belum ada booking
                            </p>
                        <?php else: ?>
                            <?php foreach ($stats['recent_bookings'] as $booking): ?>
                            <div class="booking-item">
                                <div class="booking-info">
                                    <h4><?php echo htmlspecialchars($booking['user_name']); ?></h4>
                                    <p><?php echo htmlspecialchars($booking['kos_name']); ?></p>
                                    <p style="font-size: 0.8rem;"><?php echo format_date($booking['created_at']); ?></p>
                                </div>
                                <div>
                                    <span class="status-badge status-<?php echo $booking['booking_status']; ?>">
                                        <?php echo ucfirst($booking['booking_status']); ?>
                                    </span>
                                    <p style="font-weight: 600; margin-top: 0.5rem;">
                                        <?php echo format_currency($booking['total_price']); ?>
                                    </p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title">Quick Actions</h3>
                    </div>
                    <div class="card-body">
                        <div class="quick-actions">
                            <a href="manage-kos.php" class="action-item">
                                <div class="action-icon">
                                    <i class="fas fa-home"></i>
                                </div>
                                <div>
                                    <h4>Kelola Kos</h4>
                                    <p>Manage kos listings</p>
                                </div>
                            </a>
                            
                            <a href="manage-users.php" class="action-item">
                                <div class="action-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div>
                                    <h4>Kelola User</h4>
                                    <p>Manage user accounts</p>
                                </div>
                            </a>
                            
                            <a href="manage-bookings.php" class="action-item">
                                <div class="action-icon">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <div>
                                    <h4>Kelola Booking</h4>
                                    <p>Manage bookings</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
