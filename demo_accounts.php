<?php
/**
 * Demo Accounts Information
 * File ini berisi informasi akun demo untuk testing
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo Accounts - TemanKosan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #00c851 0%, #ff69b4 100%);
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .header h1 {
            color: #333;
            margin-bottom: 0.5rem;
        }

        .header p {
            color: #666;
        }

        .accounts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .account-card {
            border: 2px solid #e0e0e0;
            border-radius: 15px;
            padding: 1.5rem;
            transition: all 0.3s ease;
        }

        .account-card:hover {
            border-color: #00c851;
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 200, 81, 0.1);
        }

        .account-card.admin {
            border-color: #ff6b6b;
        }

        .account-card.owner {
            border-color: #4ecdc4;
        }

        .account-card.member {
            border-color: #45b7d1;
        }

        .role-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 1rem;
        }

        .role-badge.admin {
            background: #ff6b6b;
            color: white;
        }

        .role-badge.owner {
            background: #4ecdc4;
            color: white;
        }

        .role-badge.member {
            background: #45b7d1;
            color: white;
        }

        .account-info {
            margin-bottom: 1rem;
        }

        .account-info h3 {
            color: #333;
            margin-bottom: 0.5rem;
        }

        .credential {
            background: #f8f9fa;
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            font-family: 'Courier New', monospace;
            border-left: 4px solid #00c851;
        }

        .credential strong {
            color: #333;
        }

        .features {
            margin-top: 1rem;
        }

        .features h4 {
            color: #555;
            margin-bottom: 0.5rem;
        }

        .features ul {
            list-style: none;
            padding-left: 0;
        }

        .features li {
            padding: 0.25rem 0;
            color: #666;
        }

        .features li:before {
            content: "✓ ";
            color: #00c851;
            font-weight: bold;
        }

        .login-button {
            display: inline-block;
            background: linear-gradient(135deg, #00c851, #00a844);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 200, 81, 0.3);
        }

        .instructions {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 2rem;
        }

        .instructions h3 {
            color: #1976d2;
            margin-bottom: 1rem;
        }

        .instructions ol {
            color: #555;
            padding-left: 1.5rem;
        }

        .instructions li {
            margin-bottom: 0.5rem;
        }

        .back-link {
            display: inline-block;
            color: white;
            text-decoration: none;
            margin-bottom: 2rem;
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }
            
            .accounts-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <a href="index.php" class="back-link">← Kembali ke Beranda</a>
    
    <div class="container">
        <div class="header">
            <h1>🔐 Demo Accounts TemanKosan</h1>
            <p>Gunakan akun demo berikut untuk testing fitur-fitur TemanKosan</p>
        </div>

        <div class="accounts-grid">
            <!-- Admin Account -->
            <div class="account-card admin">
                <span class="role-badge admin">Admin</span>
                <div class="account-info">
                    <h3>👨‍💼 Super Admin</h3>
                    <div class="credential">
                        <strong>Email:</strong> admin@temankosan.com
                    </div>
                    <div class="credential">
                        <strong>Password:</strong> admin123
                    </div>
                </div>
                <div class="features">
                    <h4>Fitur yang Tersedia:</h4>
                    <ul>
                        <li>Dashboard admin lengkap</li>
                        <li>Kelola semua kos</li>
                        <li>Kelola user dan owner</li>
                        <li>Approve/reject testimoni</li>
                        <li>Lihat statistik sistem</li>
                        <li>Kelola booking semua kos</li>
                    </ul>
                </div>
                <a href="login.php" class="login-button">Login sebagai Admin</a>
            </div>

            <!-- Owner Account 1 -->
            <div class="account-card owner">
                <span class="role-badge owner">Owner</span>
                <div class="account-info">
                    <h3>🏠 Pemilik Kos A</h3>
                    <div class="credential">
                        <strong>Email:</strong> owner1@example.com
                    </div>
                    <div class="credential">
                        <strong>Password:</strong> owner123
                    </div>
                </div>
                <div class="features">
                    <h4>Fitur yang Tersedia:</h4>
                    <ul>
                        <li>Tambah dan kelola kos</li>
                        <li>Upload foto kos</li>
                        <li>Atur fasilitas dan aturan</li>
                        <li>Kelola booking masuk</li>
                        <li>Lihat review dan rating</li>
                        <li>Dashboard owner</li>
                    </ul>
                </div>
                <a href="login.php" class="login-button">Login sebagai Owner</a>
            </div>

            <!-- Owner Account 2 -->
            <div class="account-card owner">
                <span class="role-badge owner">Owner</span>
                <div class="account-info">
                    <h3>🏘️ Pemilik Kos B</h3>
                    <div class="credential">
                        <strong>Email:</strong> owner2@example.com
                    </div>
                    <div class="credential">
                        <strong>Password:</strong> owner123
                    </div>
                </div>
                <div class="features">
                    <h4>Kos yang Dimiliki:</h4>
                    <ul>
                        <li>Kos Anggrek Modern (Bandung)</li>
                        <li>Kos Dahlia Premium (Yogyakarta)</li>
                        <li>Kos Sakura Minimalis (Malang)</li>
                    </ul>
                </div>
                <a href="login.php" class="login-button">Login sebagai Owner</a>
            </div>

            <!-- Member Account 1 -->
            <div class="account-card member">
                <span class="role-badge member">Member</span>
                <div class="account-info">
                    <h3>👤 John Doe</h3>
                    <div class="credential">
                        <strong>Email:</strong> john@example.com
                    </div>
                    <div class="credential">
                        <strong>Password:</strong> member123
                    </div>
                </div>
                <div class="features">
                    <h4>Fitur yang Tersedia:</h4>
                    <ul>
                        <li>Cari dan filter kos</li>
                        <li>Booking kos online</li>
                        <li>Lihat history booking</li>
                        <li>Beri review dan rating</li>
                        <li>Kelola profil</li>
                        <li>Favorit kos</li>
                    </ul>
                </div>
                <a href="login.php" class="login-button">Login sebagai Member</a>
            </div>

            <!-- Member Account 2 -->
            <div class="account-card member">
                <span class="role-badge member">Member</span>
                <div class="account-info">
                    <h3>👩 Jane Smith</h3>
                    <div class="credential">
                        <strong>Email:</strong> jane@example.com
                    </div>
                    <div class="credential">
                        <strong>Password:</strong> member123
                    </div>
                </div>
                <div class="features">
                    <h4>Status Demo:</h4>
                    <ul>
                        <li>Sudah pernah booking kos</li>
                        <li>Memiliki review history</li>
                        <li>Profile lengkap</li>
                        <li>Favorit beberapa kos</li>
                    </ul>
                </div>
                <a href="login.php" class="login-button">Login sebagai Member</a>
            </div>

            <!-- Quick Test Account -->
            <div class="account-card" style="border-color: #ffa726;">
                <span class="role-badge" style="background: #ffa726; color: white;">Quick Test</span>
                <div class="account-info">
                    <h3>⚡ Test User</h3>
                    <div class="credential">
                        <strong>Email:</strong> test@test.com
                    </div>
                    <div class="credential">
                        <strong>Password:</strong> test123
                    </div>
                </div>
                <div class="features">
                    <h4>Untuk Testing Cepat:</h4>
                    <ul>
                        <li>Akun member sederhana</li>
                        <li>Tanpa data history</li>
                        <li>Cocok untuk demo fitur</li>
                        <li>Reset otomatis setiap hari</li>
                    </ul>
                </div>
                <a href="login.php" class="login-button" style="background: linear-gradient(135deg, #ffa726, #ff9800);">Login untuk Test</a>
            </div>
        </div>

        <div class="instructions">
            <h3>📋 Cara Menggunakan Demo Accounts</h3>
            <ol>
                <li><strong>Pilih akun</strong> sesuai dengan role yang ingin Anda test</li>
                <li><strong>Klik tombol login</strong> pada card akun yang dipilih</li>
                <li><strong>Copy email dan password</strong> dari card ke form login</li>
                <li><strong>Klik "Masuk"</strong> untuk login ke sistem</li>
                <li><strong>Explore fitur-fitur</strong> sesuai dengan role akun</li>
                <li><strong>Logout</strong> jika ingin mencoba akun lain</li>
            </ol>
            
            <h4 style="margin-top: 1.5rem; color: #1976d2;">💡 Tips Demo:</h4>
            <ul style="list-style: none; padding-left: 0; margin-top: 0.5rem;">
                <li style="margin-bottom: 0.5rem;">🔹 <strong>Admin:</strong> Coba kelola testimoni dan lihat dashboard</li>
                <li style="margin-bottom: 0.5rem;">🔹 <strong>Owner:</strong> Tambah kos baru dan kelola booking</li>
                <li style="margin-bottom: 0.5rem;">🔹 <strong>Member:</strong> Cari kos, booking, dan beri testimoni</li>
                <li style="margin-bottom: 0.5rem;">🔹 <strong>Test User:</strong> Untuk testing fitur dasar dengan cepat</li>
            </ul>
        </div>
    </div>
</body>
</html>
