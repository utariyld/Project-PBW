<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kosName = $_POST['kos_name'] ?? '';
    $kosLocation = $_POST['kos_location'] ?? '';
    $kosPrice = $_POST['kos_price'] ?? '';
    $kosType = $_POST['kos_type'] ?? '';
    $roomSize = $_POST['room_size'] ?? '';
    $kosAddress = $_POST['kos_address'] ?? '';
    $kosDescription = $_POST['kos_description'] ?? '';
    $facilities = $_POST['facilities'] ?? [];
    
    // In real application, save to database
    $success = 'Kos berhasil ditambahkan!';
}

$facilityOptions = [
    'WiFi Gratis', 'AC', 'Kamar Mandi Dalam', 'Kamar Mandi Luar',
    'Dapur Bersama', 'Dapur Pribadi', 'Parkir Motor', 'Parkir Mobil',
    'Laundry', 'Security 24 Jam', 'CCTV', 'Lemari Pakaian',
    'Kasur', 'Meja Belajar', 'Kursi'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kos - TemanKosan</title>
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

        /* Form Sections */
        .form-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 1.5rem;
            color: #333;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
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

        /* Facilities */
        .facilities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .facility-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .facility-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #00c851;
        }

        .facility-item label {
            cursor: pointer;
            margin: 0;
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
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
        }

        .btn-cancel {
            background: #6c757d;
            color: white;
        }

        .btn-cancel:hover {
            background: #5a6268;
        }

        .btn-submit {
            background: #00c851;
            color: white;
        }

        .btn-submit:hover {
            background: #00a844;
        }

        .message {
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
        }

        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .facilities-grid {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="account.php" class="back-link">← Kembali ke Akun</a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Tambah Kos Baru</h1>
            <p class="page-subtitle">Lengkapi informasi kos yang akan Anda tambahkan</p>
        </div>

        <?php if (isset($success)): ?>
            <div class="message success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST">
            <!-- Basic Information -->
            <div class="form-section">
                <h2 class="section-title">📋 Informasi Dasar</h2>
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="kosName">Nama Kos <span class="required">*</span></label>
                        <input type="text" id="kosName" name="kos_name" required placeholder="Masukkan nama kos">
                    </div>
                    <div class="form-group">
                        <label for="kosLocation">Kota/Daerah <span class="required">*</span></label>
                        <input type="text" id="kosLocation" name="kos_location" required placeholder="Contoh: Jakarta Selatan">
                    </div>
                    <div class="form-group">
                        <label for="kosPrice">Harga per Bulan <span class="required">*</span></label>
                        <input type="number" id="kosPrice" name="kos_price" required placeholder="Contoh: 1200000">
                    </div>
                    <div class="form-group">
                        <label for="kosType">Tipe Kos <span class="required">*</span></label>
                        <select id="kosType" name="kos_type" required>
                            <option value="">Pilih tipe kos</option>
                            <option value="putra">Putra</option>
                            <option value="putri">Putri</option>
                            <option value="putra-putri">Putra/Putri</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="roomSize">Ukuran Kamar</label>
                        <input type="text" id="roomSize" name="room_size" placeholder="Contoh: 3x4 meter">
                    </div>
                    <div class="form-group full-width">
                        <label for="kosAddress">Alamat Lengkap <span class="required">*</span></label>
                        <textarea id="kosAddress" name="kos_address" required placeholder="Masukkan alamat lengkap kos"></textarea>
                    </div>
                    <div class="form-group full-width">
                        <label for="kosDescription">Deskripsi Kos</label>
                        <textarea id="kosDescription" name="kos_description" placeholder="Deskripsikan kos Anda secara detail..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Facilities -->
            <div class="form-section">
                <h2 class="section-title">🏠 Fasilitas</h2>
                <div class="facilities-grid">
                    <?php foreach ($facilityOptions as $facility): ?>
                        <div class="facility-item">
                            <input type="checkbox" id="facility-<?php echo str_replace(' ', '-', strtolower($facility)); ?>" name="facilities[]" value="<?php echo htmlspecialchars($facility); ?>">
                            <label for="facility-<?php echo str_replace(' ', '-', strtolower($facility)); ?>"><?php echo htmlspecialchars($facility); ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <a href="account.php" class="btn btn-cancel">Batal</a>
                <button type="submit" class="btn btn-submit">Tambah Kos</button>
            </div>
        </form>
    </div>
</body>
</html>
