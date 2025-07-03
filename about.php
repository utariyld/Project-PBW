<?php
session_start();
require_once 'config/database.php';

// Get some statistics for the about page
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get total kos count
    $stmt = $pdo->query("SELECT COUNT(*) as total_kos FROM kos WHERE status = 'active'");
    $total_kos = $stmt->fetch()['total_kos'];
    
    // Get total users count
    $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users WHERE status = 'active'");
    $total_users = $stmt->fetch()['total_users'];
    
    // Get total bookings count
    $stmt = $pdo->query("SELECT COUNT(*) as total_bookings FROM bookings");
    $total_bookings = $stmt->fetch()['total_bookings'];
    
    // Get total locations count
    $stmt = $pdo->query("SELECT COUNT(DISTINCT city) as total_cities FROM locations");
    $total_cities = $stmt->fetch()['total_cities'];
    
} catch(PDOException $e) {
    $total_kos = 150;
    $total_users = 500;
    $total_bookings = 300;
    $total_cities = 25;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - TemanKosan</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #00c851;
            --secondary-color: #ff69b4;
            --accent-color: #ffd700;
            --text-dark: #2c3e50;
            --text-light: #7f8c8d;
            --bg-light: #f8f9fa;
            --white: #ffffff;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 20px 40px rgba(0, 0, 0, 0.15);
            --border-radius: 15px;
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
            color: var(--text-dark);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        /* Loading Screen */
        .loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease-out;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Navbar Styles */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            padding: 1rem 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .navbar.scrolled {
            background: rgba(255, 255, 255, 0.98);
            padding: 0.5rem 0;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 500;
            transition: var(--transition);
            position: relative;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: var(--primary-color);
        }

        .nav-links a.active::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 2px;
            bottom: -5px;
            left: 0;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }

        .nav-buttons {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-outline {
            background: transparent;
            color: var(--text-dark);
            border: 2px solid var(--primary-color);
        }

        .btn-outline:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }

        /* Mobile Menu */
        .mobile-menu-btn {
            display: none;
            flex-direction: column;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
        }

        .mobile-menu-btn span {
            width: 25px;
            height: 3px;
            background: var(--text-dark);
            margin: 3px 0;
            transition: 0.3s;
            border-radius: 2px;
        }

        .mobile-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: var(--shadow);
            padding: 2rem;
        }

        .mobile-menu.active {
            display: block;
        }

        .mobile-nav-links {
            list-style: none;
            margin-bottom: 2rem;
        }

        .mobile-nav-links li {
            margin-bottom: 1rem;
        }

        .mobile-nav-links a {
            color: var(--text-dark);
            text-decoration: none;
            font-weight: 500;
            font-size: 1.1rem;
            display: block;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .mobile-nav-buttons {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        /* Main Content */
        .main-content {
            margin-top: 100px;
            min-height: calc(100vh - 100px);
            background: var(--bg-light);
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, rgba(0, 200, 81, 0.9), rgba(255, 105, 180, 0.9)),
                        url('/placeholder.svg?height=600&width=1200') center/cover;
            padding: 6rem 0;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.1) 50%, transparent 70%);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 2rem;
            position: relative;
            z-index: 1;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            animation: fadeInUp 1s ease-out;
        }

        .hero-subtitle {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            animation: fadeInUp 1s ease-out 0.2s both;
        }

        /* Stats Section */
        .stats-section {
            padding: 4rem 0;
            background: white;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }

        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            text-align: center;
            transition: var(--transition);
            border: 1px solid rgba(0, 200, 81, 0.1);
        }

        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-hover);
        }

        .stat-icon {
            font-size: 3rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1rem;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--text-light);
            font-weight: 500;
        }

        /* About Content */
        .about-content {
            padding: 4rem 0;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
            margin-bottom: 4rem;
        }

        .content-text h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .content-text p {
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--text-light);
            margin-bottom: 1.5rem;
        }

        .content-image {
            position: relative;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .content-image img {
            width: 100%;
            height: 400px;
            object-fit: cover;
            transition: var(--transition);
        }

        .content-image:hover img {
            transform: scale(1.05);
        }

        /* Features Section */
        .features-section {
            padding: 4rem 0;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background: white;
            padding: 2.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            text-align: center;
            transition: var(--transition);
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: white;
        }

        .feature-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-dark);
        }

        .feature-description {
            color: var(--text-light);
            line-height: 1.6;
        }

        /* Team Section */
        .team-section {
            padding: 4rem 0;
            background: white;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 3rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .team-card {
            background: white;
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            text-align: center;
            transition: var(--transition);
        }

        .team-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-hover);
        }

        .team-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: white;
            font-weight: 700;
        }

        .team-name {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
        }

        .team-role {
            color: var(--text-light);
            font-weight: 500;
        }

        /* Footer */
        .footer {
            background: linear-gradient(135deg, var(--text-dark), #34495e);
            color: white;
            padding: 3rem 0 1rem;
            margin-top: 4rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-section h3 {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--accent-color);
        }

        .footer-section p,
        .footer-section a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            line-height: 1.6;
        }

        .footer-section a:hover {
            color: var(--primary-color);
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 1rem;
            text-align: center;
            color: rgba(255, 255, 255, 0.6);
        }

        /* Scroll to Top Button */
        .scroll-top {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            transition: var(--transition);
            z-index: 1000;
        }

        .scroll-top:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
        }

        .scroll-top.show {
            display: flex;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.8s ease-out;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-links,
            .nav-buttons {
                display: none;
            }

            .mobile-menu-btn {
                display: flex;
            }

            .hero-title {
                font-size: 2.5rem;
            }

            .hero-subtitle {
                font-size: 1.1rem;
            }

            .content-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .team-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .team-grid {
                grid-template-columns: 1fr;
            }

            .hero-title {
                font-size: 2rem;
            }

            .container {
                padding: 0 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Loading Screen -->
    <div class="loading-screen" id="loadingScreen">
        <div class="loading-spinner"></div>
    </div>

    <!-- Navigation -->
    <?php include 'components/navbar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-content">
                <h1 class="hero-title">Tentang TemanKosan</h1>
                <p class="hero-subtitle">Platform terpercaya untuk menemukan kos impian Anda dengan mudah dan aman</p>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="stats-section">
            <div class="container">
                <div class="stats-grid">
                    <div class="stat-card fade-in-up">
                        <div class="stat-icon">
                            <i class="fas fa-home"></i>
                        </div>
                        <div class="stat-number"><?php echo number_format($total_kos); ?>+</div>
                        <div class="stat-label">Kos Tersedia</div>
                    </div>
                    <div class="stat-card fade-in-up">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-number"><?php echo number_format($total_users); ?>+</div>
                        <div class="stat-label">Pengguna Aktif</div>
                    </div>
                    <div class="stat-card fade-in-up">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-number"><?php echo number_format($total_bookings); ?>+</div>
                        <div class="stat-label">Booking Berhasil</div>
                    </div>
                    <div class="stat-card fade-in-up">
                        <div class="stat-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="stat-number"><?php echo number_format($total_cities); ?>+</div>
                        <div class="stat-label">Kota Tersedia</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- About Content -->
        <section class="about-content">
            <div class="container">
                <div class="content-grid">
                    <div class="content-text">
                        <h2>Misi Kami</h2>
                        <p>TemanKosan hadir untuk memberikan solusi terbaik bagi para pencari kos dan pemilik kos. Kami berkomitmen untuk menyediakan platform yang mudah, aman, dan terpercaya.</p>
                        <p>Dengan teknologi terdepan dan layanan pelanggan yang responsif, kami memastikan setiap transaksi berjalan lancar dan memuaskan semua pihak.</p>
                    </div>
                    <div class="content-image">
                        <img src="/placeholder.svg?height=400&width=500" alt="Misi TemanKosan">
                    </div>
                </div>

                <div class="content-grid">
                    <div class="content-image">
                        <img src="/placeholder.svg?height=400&width=500" alt="Visi TemanKosan">
                    </div>
                    <div class="content-text">
                        <h2>Visi Kami</h2>
                        <p>Menjadi platform nomor satu di Indonesia untuk pencarian dan penyewaan kos-kosan, dengan memberikan pengalaman terbaik bagi pengguna dan pemilik kos.</p>
                        <p>Kami percaya bahwa setiap orang berhak mendapatkan tempat tinggal yang nyaman dan terjangkau, dan kami berkomitmen untuk mewujudkan hal tersebut.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="features-section">
            <div class="container">
                <h2 class="section-title">Mengapa Memilih TemanKosan?</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3 class="feature-title">Pencarian Mudah</h3>
                        <p class="feature-description">Temukan kos impian Anda dengan filter pencarian yang lengkap dan akurat berdasarkan lokasi, harga, dan fasilitas.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3 class="feature-title">Aman & Terpercaya</h3>
                        <p class="feature-description">Semua kos telah diverifikasi dan sistem pembayaran yang aman memberikan perlindungan maksimal untuk transaksi Anda.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h3 class="feature-title">Layanan 24/7</h3>
                        <p class="feature-description">Tim customer service kami siap membantu Anda kapan saja dengan respon cepat dan solusi terbaik.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Team Section -->
        <section class="team-section">
            <div class="container">
                <h2 class="section-title">Tim Kami</h2>
                <div class="team-grid">
                    <div class="team-card">
                        <div class="team-avatar">A</div>
                        <h3 class="team-name">Ahmad Rizki</h3>
                        <p class="team-role">CEO & Founder</p>
                    </div>
                    <div class="team-card">
                        <div class="team-avatar">S</div>
                        <h3 class="team-name">Sari Dewi</h3>
                        <p class="team-role">CTO</p>
                    </div>
                    <div class="team-card">
                        <div class="team-avatar">B</div>
                        <h3 class="team-name">Budi Santoso</h3>
                        <p class="team-role">Head of Marketing</p>
                    </div>
                    <div class="team-card">
                        <div class="team-avatar">L</div>
                        <h3 class="team-name">Lisa Permata</h3>
                        <p class="team-role">Head of Operations</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>TemanKosan</h3>
                    <p>Platform terpercaya untuk menemukan kos impian Anda dengan mudah dan aman.</p>
                </div>
                <div class="footer-section">
                    <h3>Layanan</h3>
                    <p><a href="search.php">Cari Kos</a></p>
                    <p><a href="add-kos.php">Daftarkan Kos</a></p>
                    <p><a href="account.php">Akun Saya</a></p>
                </div>
                <div class="footer-section">
                    <h3>Bantuan</h3>
                    <p><a href="#faq">FAQ</a></p>
                    <p><a href="#contact">Hubungi Kami</a></p>
                    <p><a href="#terms">Syarat & Ketentuan</a></p>
                </div>
                <div class="footer-section">
                    <h3>Kontak</h3>
                    <p>Email: info@temankosan.com</p>
                    <p>Phone: +62 812-3456-7890</p>
                    <p>Alamat: Jakarta, Indonesia</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 TemanKosan. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Scroll to Top Button -->
    <button class="scroll-top" id="scrollTop">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script>
        // Loading Screen
        window.addEventListener('load', function() {
            const loadingScreen = document.getElementById('loadingScreen');
            setTimeout(() => {
                loadingScreen.style.opacity = '0';
                setTimeout(() => {
                    loadingScreen.style.display = 'none';
                }, 500);
            }, 1000);
        });

        // Mobile Menu Toggle
        function toggleMobileMenu() {
            const mobileMenu = document.getElementById('mobileMenu');
            const menuBtn = document.querySelector('.mobile-menu-btn');
            
            mobileMenu.classList.toggle('active');
            menuBtn.classList.toggle('active');
        }

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const mobileMenu = document.getElementById('mobileMenu');
            const menuBtn = document.querySelector('.mobile-menu-btn');
            
            if (mobileMenu && menuBtn && !menuBtn.contains(event.target) && !mobileMenu.contains(event.target)) {
                mobileMenu.classList.remove('active');
                menuBtn.classList.remove('active');
            }
        });

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            const scrollTop = document.getElementById('scrollTop');
            
            if (window.scrollY > 100) {
                navbar.classList.add('scrolled');
                scrollTop.classList.add('show');
            } else {
                navbar.classList.remove('scrolled');
                scrollTop.classList.remove('show');
            }
        });

        // Scroll to top functionality
        document.getElementById('scrollTop').addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe elements for animation
        document.querySelectorAll('.stat-card, .feature-card, .team-card').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
            observer.observe(el);
        });

        // Counter animation for stats
        function animateCounter(element, target) {
            let current = 0;
            const increment = target / 100;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                element.textContent = Math.floor(current).toLocaleString() + '+';
            }, 20);
        }

        // Animate counters when they come into view
        const statsObserver = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const numberElement = entry.target.querySelector('.stat-number');
                    const targetNumber = parseInt(numberElement.textContent.replace(/[^\d]/g, ''));
                    animateCounter(numberElement, targetNumber);
                    statsObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        document.querySelectorAll('.stat-card').forEach(card => {
            statsObserver.observe(card);
        });
    </script>
</body>
</html>
