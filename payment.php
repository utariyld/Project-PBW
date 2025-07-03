<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$bookingId = $_GET['booking_id'] ?? '';
$total = $_GET['total'] ?? 0;

if (empty($bookingId) || $total <= 0) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - TemanKosan</title>
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

        .container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
        }

        .payment-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .success-icon {
            font-size: 4rem;
            color: #00c851;
            margin-bottom: 1rem;
        }

        .payment-title {
            font-size: 2rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 1rem;
        }

        .payment-amount {
            font-size: 2.5rem;
            font-weight: bold;
            color: #00c851;
            margin-bottom: 2rem;
        }

        .payment-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            text-align: left;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .btn {
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            margin: 0.5rem;
        }

        .btn-primary {
            background: #00c851;
            color: white;
        }

        .btn-primary:hover {
            background: #00a844;
        }

        .btn-outline {
            background: white;
            color: #666;
            border: 2px solid #ddd;
        }

        .btn-outline:hover {
            background: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="payment-card">
            <div class="success-icon">✅</div>
            <h1 class="payment-title">Booking Berhasil!</h1>
            <div class="payment-amount">Rp <?php echo number_format($total, 0, ',', '.'); ?></div>
            
            <div class="payment-info">
                <div class="info-row">
                    <span>Booking ID:</span>
                    <span><strong><?php echo htmlspecialchars($bookingId); ?></strong></span>
                </div>
                <div class="info-row">
                    <span>Status:</span>
                    <span><strong>Menunggu Pembayaran</strong></span>
                </div>
                <div class="info-row">
                    <span>Batas Waktu:</span>
                    <span><strong>24 Jam</strong></span>
                </div>
            </div>

            <p style="margin-bottom: 2rem; color: #666;">
                Silakan lakukan pembayaran dalam 24 jam untuk mengkonfirmasi booking Anda.
            </p>

            <div>
                <a href="account.php" class="btn btn-primary">Lihat Booking Saya</a>
                <a href="index.php" class="btn btn-outline">Kembali ke Beranda</a>
            </div>
        </div>
    </div>
</body>
</html>
