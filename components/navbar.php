<!-- Buat komponen navbar yang bisa digunakan di semua halaman -->
<nav class="navbar">
    <div class="nav-container">
        <a href="index.php" class="logo">TemanKosan</a>
        <ul class="nav-links">
            <li><a href="index.php" <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'class="active"' : ''; ?>>Beranda</a></li>
            <li><a href="search.php" <?php echo basename($_SERVER['PHP_SELF']) == 'search.php' ? 'class="active"' : ''; ?>>Cari Kos</a></li>
            <li><a href="about.php" <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'class="active"' : ''; ?>>Tentang</a></li>
            <li><a href="#contact">Kontak</a></li>
        </ul>
        <div class="nav-buttons">
            <?php if (isset($_SESSION['user'])): ?>
                <a href="account.php" class="btn btn-outline">👤 <?php echo htmlspecialchars($_SESSION['user']['name']); ?></a>
                <a href="logout.php" class="btn btn-primary">Logout</a>
            <?php else: ?>
                <a href="account.php" class="btn btn-outline">👤 Akun</a>
                <a href="login.php" class="btn btn-primary">Masuk</a>
            <?php endif; ?>
        </div>
        
        <!-- Mobile Menu Button -->
        <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
    
    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu">
        <ul class="mobile-nav-links">
            <li><a href="index.php">Beranda</a></li>
            <li><a href="search.php">Cari Kos</a></li>
            <li><a href="about.php">Tentang</a></li>
            <li><a href="#contact">Kontak</a></li>
        </ul>
        <div class="mobile-nav-buttons">
            <?php if (isset($_SESSION['user'])): ?>
                <a href="account.php" class="btn btn-outline">👤 <?php echo htmlspecialchars($_SESSION['user']['name']); ?></a>
                <a href="logout.php" class="btn btn-primary">Logout</a>
            <?php else: ?>
                <a href="account.php" class="btn btn-outline">👤 Akun</a>
                <a href="login.php" class="btn btn-primary">Masuk</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<style>
/* Active Navigation Link */
.nav-links a.active {
    color: #ff69b4;
    position: relative;
}

.nav-links a.active::after {
    content: '';
    position: absolute;
    width: 100%;
    height: 2px;
    bottom: -5px;
    left: 0;
    background: linear-gradient(135deg, #00c851, #ff69b4);
}

/* Mobile Menu Styles */
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
    background: #333;
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
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
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
    color: #333;
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

@media (max-width: 768px) {
    .nav-links {
        display: none;
    }
    
    .nav-buttons {
        display: none;
    }
    
    .mobile-menu-btn {
        display: flex;
    }
}
</style>

<script>
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
    
    if (!menuBtn.contains(event.target) && !mobileMenu.contains(event.target)) {
        mobileMenu.classList.remove('active');
        menuBtn.classList.remove('active');
    }
});
</script>
