<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Get kos ID from URL
$kosId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Sample kos data
$kosData = [
    1 => [
        'id' => 1,
        'name' => 'Kos Melati Indah',
        'location' => 'Depok, Jawa Barat',
        'price' => 1200000,
        'image' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=400&h=300&fit=crop'
    ],
    2 => [
        'id' => 2,
        'name' => 'Kos Mawar Residence',
        'location' => 'Jakarta Selatan',
        'price' => 1800000,
        'image' => 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=400&h=300&fit=crop'
    ]
];

$kos = isset($kosData[$kosId]) ? $kosData[$kosId] : null;

if (!$kos) {
    header('Location: index.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $checkInDate = $_POST['check_in_date'] ?? '';
    $duration = (int)($_POST['duration'] ?? 1);
    $notes = $_POST['notes'] ?? '';
    $paymentMethod = $_POST['payment_method'] ?? '';
    
    // Calculate total
    $subtotal = $kos['price'] * $duration;
    $adminFee = 50000;
    $total = $subtotal + $adminFee;
    
    // In real application, save to database
    $bookingId = time(); // Simple booking ID
    
    // Redirect to payment page
    header("Location: payment.php?booking_id=$bookingId&total=$total");
    exit;
}

$adminFee = 50000;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Kos - TemanKosan</title>
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
            align-items: center;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            color: #666;
            text-decoration: none;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: #00c851;
        }

        /* Main Content */
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: #666;
        }

        .booking-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }

        /* Booking Form */
        .booking-form {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 1rem;
            color: #333;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }

        .required {
            color: #dc3545;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #00c851;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .payment-methods {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .payment-option {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .payment-option:hover {
            border-color: #00c851;
        }

        .payment-option input[type="radio"] {
            width: auto;
        }

        .payment-option.selected {
            border-color: #00c851;
            background: #e8f5e8;
        }

        .btn-submit {
            width: 100%;
            padding: 1rem;
            background: #00c851;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-submit:hover {
            background: #00a844;
        }

        /* Booking Summary */
        .booking-summary {
            position: sticky;
            top: 100px;
        }

        .summary-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .kos-preview {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #e0e0e0;
        }

        .kos-image {
            width: 80px;
            height: 80px;
            border-radius: 10px;
            overflow: hidden;
        }

        .kos-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .kos-info h3 {
            font-size: 1.1rem;
            margin-bottom: 0.25rem;
        }

        .kos-info p {
            color: #666;
            font-size: 0.9rem;
        }

        .summary-details {
            margin-bottom: 2rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .summary-row.total {
            font-weight: bold;
            font-size: 1.1rem;
            padding-top: 1rem;
            border-top: 1px solid #e0e0e0;
            color: #00c851;
        }

        .booking-note {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 10px;
            padding: 1rem;
            font-size: 0.9rem;
            color: #856404;
        }

        .booking-note strong {
            display: block;
            margin-bottom: 0.5rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .booking-container {
                grid-template-columns: 1fr;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .kos-preview {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="kos-detail.php?id=<?php echo $kosId; ?>" class="back-link">← Kembali ke Detail Kos</a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Booking Kos</h1>
            <p class="page-subtitle">Lengkapi data booking Anda</p>
        </div>

        <div class="booking-container">
            <!-- Booking Form -->
            <div class="booking-form">
                <form method="POST">
                    <div class="form-section">
                        <h2 class="section-title">👤 Data Penyewa</h2>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="fullName">Nama Lengkap <span class="required">*</span></label>
                                <input type="text" id="fullName" name="full_name" value="<?php echo htmlspecialchars($_SESSION['user']['name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email <span class="required">*</span></label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['user']['email']); ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="phone">Nomor Telepon <span class="required">*</span></label>
                            <input type="tel" id="phone" name="phone" required>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="checkInDate">Tanggal Masuk <span class="required">*</span></label>
                                <input type="date" id="checkInDate" name="check_in_date" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="duration">Durasi Sewa <span class="required">*</span></label>
                                <select id="duration" name="duration" required onchange="updateSummary()">
                                    <option value="1">1 Bulan</option>
                                    <option value="3">3 Bulan</option>
                                    <option value="6">6 Bulan</option>
                                    <option value="12">12 Bulan</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group full-width">
                            <label for="notes">Catatan Tambahan</label>
                            <textarea id="notes" name="notes" placeholder="Tambahkan catatan atau permintaan khusus..."></textarea>
                        </div>
                    </div>

                    <div class="form-section">
                        <h2 class="section-title">💳 Metode Pembayaran</h2>
                        <div class="payment-methods">
                            <label class="payment-option selected">
                                <input type="radio" name="payment_method" value="transfer" checked>
                                <span>🏦 Transfer Bank</span>
                            </label>
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="ewallet">
                                <span>📱 E-Wallet (OVO, GoPay, DANA)</span>
                            </label>
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="credit">
                                <span>💳 Kartu Kredit</span>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">
                        💳 Lanjutkan Pembayaran
                    </button>
                </form>
            </div>

            <!-- Booking Summary -->
            <div class="booking-summary">
                <div class="summary-card">
                    <h2 class="section-title">📋 Ringkasan Booking</h2>
                    
                    <div class="kos-preview">
                        <div class="kos-image">
                            <img src="<?php echo htmlspecialchars($kos['image']); ?>" alt="<?php echo htmlspecialchars($kos['name']); ?>">
                        </div>
                        <div class="kos-info">
                            <h3><?php echo htmlspecialchars($kos['name']); ?></h3>
                            <p><?php echo htmlspecialchars($kos['location']); ?></p>
                        </div>
                    </div>

                    <div class="summary-details">
                        <div class="summary-row">
                            <span>Harga per bulan</span>
                            <span id="monthlyPrice">Rp <?php echo number_format($kos['price'], 0, ',', '.'); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Durasi</span>
                            <span id="durationText">1 bulan</span>
                        </div>
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span id="subtotal">Rp <?php echo number_format($kos['price'], 0, ',', '.'); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Biaya Admin</span>
                            <span id="adminFee">Rp <?php echo number_format($adminFee, 0, ',', '.'); ?></span>
                        </div>
                        <div class="summary-row total">
                            <span>Total</span>
                            <span id="totalPrice">Rp <?php echo number_format($kos['price'] + $adminFee, 0, ',', '.'); ?></span>
                        </div>
                    </div>

                    <div class="booking-note">
                        <strong>Catatan:</strong>
                        Pembayaran harus dilakukan dalam 24 jam setelah booking untuk mengkonfirmasi reservasi Anda.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const kosPrice = <?php echo $kos['price']; ?>;
        const adminFee = <?php echo $adminFee; ?>;

        // Update booking summary
        function updateSummary() {
            const duration = parseInt(document.getElementById('duration').value);
            const subtotal = kosPrice * duration;
            const total = subtotal + adminFee;

            document.getElementById('durationText').textContent = `${duration} bulan`;
            document.getElementById('subtotal').textContent = `Rp ${subtotal.toLocaleString('id-ID')}`;
            document.getElementById('totalPrice').textContent = `Rp ${total.toLocaleString('id-ID')}`;
        }

        // Setup payment method selection
        document.addEventListener('DOMContentLoaded', function() {
            const paymentOptions = document.querySelectorAll('.payment-option');
            paymentOptions.forEach(option => {
                option.addEventListener('click', function() {
                    paymentOptions.forEach(opt => opt.classList.remove('selected'));
                    this.classList.add('selected');
                    this.querySelector('input[type="radio"]').checked = true;
                });
            });
        });
    </script>
</body>
</html>
