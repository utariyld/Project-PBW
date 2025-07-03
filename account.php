<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akun Saya - TemanKosan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f8f9fa;
        }

        /* Navigation */
        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
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
            font-weight: bold;
            color: #00c851;
            text-decoration: none;
        }

        .nav-actions {
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            font-weight: 500;
        }

        .btn-outline {
            background: white;
            color: #666;
            border: 1px solid #ddd;
        }

        .btn-outline:hover {
            background: #f8f9fa;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        /* Main Content */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .account-layout {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 2rem;
        }

        /* Profile Sidebar */
        .profile-sidebar {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            height: fit-content;
            position: sticky;
            top: 100px;
        }

        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #00c851;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            font-weight: bold;
            margin: 0 auto 1rem;
        }

        .profile-info {
            text-align: center;
            margin-bottom: 2rem;
        }

        .profile-name {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .profile-email {
            color: #666;
            margin-bottom: 0.5rem;
        }

        .profile-role {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .role-admin {
            background: #e3f2fd;
            color: #1976d2;
        }

        .role-member {
            background: #e8f5e8;
            color: #00c851;
        }

        /* Main Content Area */
        .main-content {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .profile-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }

        .form-group .value {
            padding: 0.75rem;
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            color: #666;
        }

        .btn-edit {
            background: #00c851;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            width: fit-content;
            margin-top: 2rem;
        }

        .btn-edit:hover {
            background: #00a844;
        }

        .btn-add {
            background: #00c851;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            width: fit-content;
        }

        .btn-add:hover {
            background: #00a844;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .account-layout {
                grid-template-columns: 1fr;
            }

            .profile-form {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">TemanKosan</a>
            <div class="nav-actions">
                <a href="index.php" class="btn btn-outline">Kembali ke Beranda</a>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <div class="account-layout">
            <!-- Profile Sidebar -->
            <div class="profile-sidebar">
                <div class="profile-avatar"><?php echo strtoupper(substr($user['name'], 0, 1)); ?></div>
                <div class="profile-info">
                    <div class="profile-name"><?php echo htmlspecialchars($user['name']); ?></div>
                    <div class="profile-email"><?php echo htmlspecialchars($user['email']); ?></div>
                    <div class="profile-role role-<?php echo $user['role']; ?>">
                        <?php echo $user['role'] === 'admin' ? 'Admin' : 'Member'; ?>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="main-content">
                <h2 class="section-title">👤 Informasi Profil</h2>
                <div class="profile-form">
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <div class="value"><?php echo htmlspecialchars($user['name']); ?></div>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <div class="value"><?php echo htmlspecialchars($user['email']); ?></div>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <div class="value"><?php echo $user['role'] === 'admin' ? 'Admin' : 'Member'; ?></div>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <div class="value">Aktif</div>
                    </div>
                </div>
                
                <button class="btn-edit" onclick="alert('Fitur edit profil akan segera hadir!')">
                    ⚙️ Edit Profil
                </button>

                <?php if ($user['role'] === 'admin'): ?>
                    <div style="margin-top: 3rem;">
                        <h2 class="section-title">🏠 Kelola Kos</h2>
                        <p style="margin-bottom: 1rem; color: #666;">Sebagai admin, Anda dapat menambah dan mengelola kos.</p>
                        <a href="add-kos.php" class="btn-add">➕ Tambah Kos Baru</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
