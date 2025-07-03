<?php
session_start();

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'login') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            // Simple authentication (in real app, use database and password hashing)
            $users = [
                ['id' => 1, 'name' => 'Admin Demo', 'email' => 'admin@temankosan.com', 'password' => 'admin123', 'role' => 'admin'],
                ['id' => 2, 'name' => 'Member Demo', 'email' => 'member@temankosan.com', 'password' => 'member123', 'role' => 'member']
            ];
            
            $user = null;
            foreach ($users as $u) {
                if ($u['email'] === $email && $u['password'] === $password) {
                    $user = $u;
                    break;
                }
            }
            
            if ($user) {
                $_SESSION['user'] = $user;
                header('Location: index.php');
                exit;
            } else {
                $error = 'Email atau password salah!';
            }
        } elseif ($_POST['action'] === 'register') {
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $role = $_POST['role'] ?? 'member';
            
            if ($password !== $confirmPassword) {
                $error = 'Password tidak cocok!';
            } elseif (strlen($password) < 6) {
                $error = 'Password minimal 6 karakter!';
            } else {
                // In real app, save to database
                $success = 'Registrasi berhasil! Silakan login.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TemanKosan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #00c851 0%, #00a844 50%, #ff69b4 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: 10%;
            left: 10%;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        body::after {
            content: '';
            position: absolute;
            bottom: 10%;
            right: 10%;
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            position: relative;
            z-index: 2;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            color: white;
            text-decoration: none;
            margin-bottom: 2rem;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: #ff69b4;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h1 {
            font-size: 2rem;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: #666;
        }

        .tabs {
            display: flex;
            margin-bottom: 2rem;
            background: #f8f9fa;
            border-radius: 10px;
            padding: 0.25rem;
        }

        .tab {
            flex: 1;
            padding: 0.75rem;
            text-align: center;
            background: transparent;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
        }

        .tab.active {
            background: white;
            color: #00c851;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .form-container {
            position: relative;
        }

        .form {
            display: none;
        }

        .form.active {
            display: block;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #00c851;
        }

        .password-container {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #666;
        }

        .btn {
            width: 100%;
            padding: 0.75rem;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-login {
            background: #00c851;
            color: white;
        }

        .btn-login:hover {
            background: #00a844;
        }

        .btn-register {
            background: #ff69b4;
            color: white;
        }

        .btn-register:hover {
            background: #ff1493;
        }

        .message {
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        @media (max-width: 480px) {
            .login-container {
                margin: 1rem;
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div style="text-align: center; margin-bottom: 1rem;">
        <a href="demo_accounts.php" style="
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        " onmouseover="this.style.background='rgba(255, 255, 255, 0.3)'" 
           onmouseout="this.style.background='rgba(255, 255, 255, 0.2)'">
            🔐 Lihat Demo Accounts
        </a>
    </div>
    <a href="index.php" class="back-link">← Kembali ke Beranda</a>
    
    <div class="login-container">
        <div class="login-header">
            <h1>TemanKosan</h1>
            <p>Masuk atau daftar untuk melanjutkan</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="message success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="tabs">
            <button class="tab active" onclick="switchTab('login')">Masuk</button>
            <button class="tab" onclick="switchTab('register')">Daftar</button>
        </div>

        <div class="form-container">
            <!-- Login Form -->
            <form class="form active" id="loginForm" method="POST">
                <input type="hidden" name="action" value="login">
                <div class="form-group">
                    <label for="loginEmail">Email</label>
                    <input type="email" id="loginEmail" name="email" required>
                </div>
                <div class="form-group">
                    <label for="loginPassword">Password</label>
                    <div class="password-container">
                        <input type="password" id="loginPassword" name="password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('loginPassword')">👁️</button>
                    </div>
                </div>
                <button type="submit" class="btn btn-login">Masuk</button>
            </form>

            <!-- Register Form -->
            <form class="form" id="registerForm" method="POST">
                <input type="hidden" name="action" value="register">
                <div class="form-group">
                    <label for="registerName">Nama Lengkap</label>
                    <input type="text" id="registerName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="registerEmail">Email</label>
                    <input type="email" id="registerEmail" name="email" required>
                </div>
                <div class="form-group">
                    <label for="registerPassword">Password</label>
                    <div class="password-container">
                        <input type="password" id="registerPassword" name="password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('registerPassword')">👁️</button>
                    </div>
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Konfirmasi Password</label>
                    <div class="password-container">
                        <input type="password" id="confirmPassword" name="confirm_password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword')">👁️</button>
                    </div>
                </div>
                <div class="form-group">
                    <label for="userRole">Daftar sebagai</label>
                    <select id="userRole" name="role" required>
                        <option value="member">Member</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-register">Daftar</button>
            </form>
        </div>
    </div>

    <script>
        // Switch between login and register tabs
        function switchTab(tab) {
            const tabs = document.querySelectorAll('.tab');
            const forms = document.querySelectorAll('.form');
            
            tabs.forEach(t => t.classList.remove('active'));
            forms.forEach(f => f.classList.remove('active'));
            
            document.querySelector(`[onclick="switchTab('${tab}')"]`).classList.add('active');
            document.getElementById(tab + 'Form').classList.add('active');
        }

        // Toggle password visibility
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const button = input.nextElementSibling;
            
            if (input.type === 'password') {
                input.type = 'text';
                button.textContent = '🙈';
            } else {
                input.type = 'password';
                button.textContent = '👁️';
            }
        }
    </script>
</body>
</html>
