<?php
session_start();
require_once 'models/Kos.php';
require_once 'includes/functions.php';

$kosModel = new Kos();

// Get all available facilities for filter options
$allFacilities = [
    'WiFi', 'AC', 'Kamar Mandi Dalam', 'Kamar Mandi Luar', 'Parkir Motor', 'Parkir Mobil',
    'Dapur', 'Kulkas', 'Laundry', 'Security 24 Jam', 'CCTV', 'Akses 24 Jam',
    'Dekat Kampus', 'Dekat Mall', 'Dekat Stasiun', 'Dekat Halte', 'Warung Makan',
    'Minimarket', 'ATM', 'Apotek', 'Rumah Sakit', 'Masjid', 'Gereja'
];

// Get search parameters
$searchLocation = isset($_GET['location']) ? trim($_GET['location']) : '';
$selectedFacilities = isset($_GET['facilities']) ? $_GET['facilities'] : [];
$minPrice = isset($_GET['min_price']) ? (int)$_GET['min_price'] : 0;
$maxPrice = isset($_GET['max_price']) ? (int)$_GET['max_price'] : 10000000;
$kosType = isset($_GET['type']) ? $_GET['type'] : '';
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Sample data with more detailed facilities
$kosData = get_search_kos();

// Filter the data based on search criteria
$filteredKos = $kosData;

// Filter by location
if (!empty($searchLocation)) {
    $filteredKos = array_filter($filteredKos, function($kos) use ($searchLocation) {
        return stripos($kos['location'], $searchLocation) !== false || 
               stripos($kos['name'], $searchLocation) !== false ||
               stripos($kos['address'], $searchLocation) !== false;
    });
}

// Filter by facilities
if (!empty($selectedFacilities)) {
    $filteredKos = array_filter($filteredKos, function($kos) use ($selectedFacilities) {
        foreach ($selectedFacilities as $facility) {
            if (!in_array($facility, $kos['facilities'])) {
                return false;
            }
        }
        return true;
    });
}

// Filter by price range
$filteredKos = array_filter($filteredKos, function($kos) use ($minPrice, $maxPrice) {
    return $kos['price'] >= $minPrice && $kos['price'] <= $maxPrice;
});

// Filter by type
if (!empty($kosType)) {
    $filteredKos = array_filter($filteredKos, function($kos) use ($kosType) {
        return $kos['type'] === $kosType;
    });
}

// Sort the results
switch ($sortBy) {
    case 'price_low':
        usort($filteredKos, function($a, $b) { return $a['price'] - $b['price']; });
        break;
    case 'price_high':
        usort($filteredKos, function($a, $b) { return $b['price'] - $a['price']; });
        break;
    case 'rating':
        usort($filteredKos, function($a, $b) { return $b['rating'] <=> $a['rating']; });
        break;
    case 'newest':
    default:
        usort($filteredKos, function($a, $b) { return strtotime($b['created_at']) - strtotime($a['created_at']); });
        break;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pencarian Kos - TemanKosan</title>
    <link rel="stylesheet" href="assets/css/live-search.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
        }

        /* Navigation */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding: 1rem 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
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
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, #00c851, #ff69b4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-links a {
            color: #333;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: #ff69b4;
        }

        .nav-buttons {
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .btn-outline {
            background: rgba(255, 255, 255, 0.1);
            color: #333;
            border: 2px solid rgba(0, 200, 81, 0.3);
        }

        .btn-outline:hover {
            background: rgba(0, 200, 81, 0.1);
            border-color: #00c851;
            transform: translateY(-2px);
        }

        .btn-primary {
            background: linear-gradient(135deg, #ff69b4, #ff1493);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 105, 180, 0.4);
        }

        /* Main Content */
        .main-content {
            margin-top: 100px;
            padding: 2rem 0;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .page-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .page-title {
            font-size: 3rem;
            font-weight: 800;
            color: #333;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #00c851, #ff69b4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .page-subtitle {
            font-size: 1.2rem;
            color: #666;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Search Layout */
        .search-layout {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 3rem;
            align-items: start;
        }

        /* Search Filters */
        .search-filters {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 120px;
            max-height: calc(100vh - 140px);
            overflow-y: auto;
        }

        .filter-section {
            margin-bottom: 2rem;
        }

        .filter-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #00c851;
            box-shadow: 0 0 0 3px rgba(0, 200, 81, 0.1);
        }

        .price-range {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        /* Facilities Checkboxes */
        .facilities-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.5rem;
            max-height: 300px;
            overflow-y: auto;
            padding: 0.5rem;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
        }

        .facility-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            border-radius: 8px;
            transition: background 0.3s ease;
        }

        .facility-item:hover {
            background: rgba(0, 200, 81, 0.05);
        }

        .facility-item input[type="checkbox"] {
            width: auto;
            margin: 0;
        }

        .facility-item label {
            margin: 0;
            cursor: pointer;
            font-weight: 500;
        }

        /* Filter Buttons */
        .filter-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn-filter {
            flex: 1;
            padding: 0.75rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-search-filter {
            background: linear-gradient(135deg, #00c851, #00a844);
            color: white;
        }

        .btn-search-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 200, 81, 0.3);
        }

        .btn-reset {
            background: #f8f9fa;
            color: #666;
            border: 1px solid #e0e0e0;
        }

        .btn-reset:hover {
            background: #e9ecef;
        }

        /* Search Results */
        .search-results {
            flex: 1;
        }

        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.05);
        }

        .results-count {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
        }

        .sort-dropdown {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .sort-dropdown select {
            padding: 0.5rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-weight: 500;
        }

        /* Kos Grid */
        .kos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
        }

        .kos-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            transition: all 0.4s ease;
            cursor: pointer;
            position: relative;
        }

        .kos-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        }

        .kos-image {
            position: relative;
            height: 220px;
            overflow: hidden;
        }

        .kos-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .kos-card:hover .kos-image img {
            transform: scale(1.1);
        }

        .kos-badge {
            position: absolute;
            top: 1rem;
            left: 1rem;
            background: linear-gradient(135deg, #00c851, #00a844);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 700;
            z-index: 2;
        }

        .favorite-btn {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 50%;
            width: 45px;
            height: 45px;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .favorite-btn:hover {
            background: white;
            transform: scale(1.1);
        }

        .kos-content {
            padding: 1.5rem;
        }

        .kos-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .kos-location {
            color: #666;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
        }

        .kos-address {
            color: #888;
            font-size: 0.85rem;
            margin-bottom: 1rem;
        }

        .kos-rating {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .stars {
            color: #ffc107;
        }

        .kos-facilities {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .facility-tag {
            background: linear-gradient(135deg, #e9ecef, #f8f9fa);
            color: #495057;
            padding: 0.3rem 0.6rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .kos-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid #f0f0f0;
        }

        .kos-price {
            font-size: 1.3rem;
            font-weight: 800;
            background: linear-gradient(135deg, #00c851, #00a844);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .kos-price span {
            font-size: 0.8rem;
            color: #666;
        }

        .btn-booking {
            background: linear-gradient(135deg, #ff69b4, #ff1493);
            color: white;
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .btn-booking:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 105, 180, 0.4);
        }

        /* No Results */
        .no-results {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
        }

        .no-results-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .no-results h3 {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 1rem;
        }

        .no-results p {
            color: #666;
            margin-bottom: 2rem;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .search-layout {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .search-filters {
                position: static;
                max-height: none;
            }
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .page-title {
                font-size: 2.2rem;
            }

            .results-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .kos-grid {
                grid-template-columns: 1fr;
            }

            .price-range {
                grid-template-columns: 1fr;
            }

            .filter-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">TemanKosan</a>
            <ul class="nav-links">
                <li><a href="index.php">Beranda</a></li>
                <li><a href="search.php">Cari Kos</a></li>
                <li><a href="about.php">Tentang</a></li>
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
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">🔍 Pencarian Kos</h1>
                <p class="page-subtitle">Temukan kos impian Anda dengan filter fasilitas yang lengkap dan detail</p>
            </div>

            <!-- Search Layout -->
            <div class="search-layout">
                <!-- Search Filters -->
                <div class="search-filters">
                    <form method="GET" action="search.php" id="searchForm">
                        <!-- Location Filter with Live Search -->
                        <div class="filter-section">
                            <h3 class="filter-title">📍 Lokasi</h3>
                            <div class="form-group">
                                <div class="live-search-wrapper">
                                    <input type="text" name="location" value="<?php echo htmlspecialchars($searchLocation); ?>" placeholder="Masukkan kota atau daerah..." data-live-search="#liveSearchResults">
                                    <div id="liveSearchResults" class="live-search-results"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Price Range Filter -->
                        <div class="filter-section">
                            <h3 class="filter-title">💰 Rentang Harga</h3>
                            <div class="price-range">
                                <div class="form-group">
                                    <label>Harga Minimum</label>
                                    <input type="number" name="min_price" value="<?php echo $minPrice; ?>" placeholder="0" min="0" step="100000">
                                </div>
                                <div class="form-group">
                                    <label>Harga Maksimum</label>
                                    <input type="number" name="max_price" value="<?php echo $maxPrice; ?>" placeholder="10000000" min="0" step="100000">
                                </div>
                            </div>
                        </div>

                        <!-- Type Filter -->
                        <div class="filter-section">
                            <h3 class="filter-title">👥 Tipe Kos</h3>
                            <div class="form-group">
                                <select name="type">
                                    <option value="">Semua Tipe</option>
                                    <option value="putra" <?php echo $kosType === 'putra' ? 'selected' : ''; ?>>Putra</option>
                                    <option value="putri" <?php echo $kosType === 'putri' ? 'selected' : ''; ?>>Putri</option>
                                    <option value="putra-putri" <?php echo $kosType === 'putra-putri' ? 'selected' : ''; ?>>Putra/Putri</option>
                                </select>
                            </div>
                        </div>

                        <!-- Facilities Filter -->
                        <div class="filter-section">
                            <h3 class="filter-title">🏠 Fasilitas</h3>
                            <div class="facilities-grid">
                                <?php foreach ($allFacilities as $facility): ?>
                                    <div class="facility-item">
                                        <input type="checkbox" 
                                               name="facilities[]" 
                                               value="<?php echo htmlspecialchars($facility); ?>" 
                                               id="facility_<?php echo str_replace(' ', '_', $facility); ?>"
                                               <?php echo in_array($facility, $selectedFacilities) ? 'checked' : ''; ?>>
                                        <label for="facility_<?php echo str_replace(' ', '_', $facility); ?>">
                                            <?php echo htmlspecialchars($facility); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Filter Buttons -->
                        <div class="filter-buttons">
                            <button type="submit" class="btn-filter btn-search-filter">🔍 Cari Kos</button>
                            <button type="button" class="btn-filter btn-reset" onclick="resetFilters()">🔄 Reset</button>
                        </div>
                    </form>
                </div>

                <!-- Search Results -->
                <div class="search-results">
                    <!-- Results Header -->
                    <div class="results-header">
                        <div class="results-count">
                            <?php if (count($filteredKos) > 0): ?>
                                🎉 Ditemukan <?php echo count($filteredKos); ?> kos sesuai kriteria Anda
                            <?php else: ?>
                                😔 Tidak ada kos yang sesuai dengan kriteria pencarian
                            <?php endif; ?>
                        </div>
                        
                        <?php if (count($filteredKos) > 0): ?>
                            <div class="sort-dropdown">
                                <label>Urutkan:</label>
                                <select name="sort" onchange="updateSort(this.value)">
                                    <option value="newest" <?php echo $sortBy === 'newest' ? 'selected' : ''; ?>>Terbaru</option>
                                    <option value="price_low" <?php echo $sortBy === 'price_low' ? 'selected' : ''; ?>>Harga Terendah</option>
                                    <option value="price_high" <?php echo $sortBy === 'price_high' ? 'selected' : ''; ?>>Harga Tertinggi</option>
                                    <option value="rating" <?php echo $sortBy === 'rating' ? 'selected' : ''; ?>>Rating Tertinggi</option>
                                </select>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Kos Grid -->
                    <?php if (count($filteredKos) > 0): ?>
                        <div class="kos-grid">
                            <?php foreach ($filteredKos as $kos): ?>
                                <div class="kos-card" onclick="window.location.href='kos-detail.php?id=<?php echo $kos['id']; ?>'">
                                    <div class="kos-image">
                                        <img src="<?php echo htmlspecialchars($kos['image']); ?>" alt="<?php echo htmlspecialchars($kos['name']); ?>">
                                        <div class="kos-badge"><?php echo htmlspecialchars(ucfirst(str_replace('-', '/', $kos['type']))); ?></div>
                                        <button class="favorite-btn" onclick="event.stopPropagation(); toggleFavorite(<?php echo $kos['id']; ?>)">
                                            ❤️
                                        </button>
                                    </div>
                                    <div class="kos-content">
                                        <h3 class="kos-title"><?php echo htmlspecialchars($kos['name']); ?></h3>
                                        <div class="kos-location">
                                            📍 <?php echo htmlspecialchars($kos['location']); ?>
                                        </div>
                                        <div class="kos-address">
                                            <?php echo htmlspecialchars($kos['address']); ?>
                                        </div>
                                        <div class="kos-rating">
                                            <span class="stars">⭐</span>
                                            <span><?php echo $kos['rating']; ?></span>
                                            <span>(<?php echo $kos['reviewCount']; ?> ulasan)</span>
                                            <span>• <?php echo $kos['room_size']; ?></span>
                                        </div>
                                        <div class="kos-facilities">
                                            <?php 
                                            $displayFacilities = array_slice($kos['facilities'], 0, 4);
                                            foreach ($displayFacilities as $facility): 
                                            ?>
                                                <span class="facility-tag"><?php echo htmlspecialchars($facility); ?></span>
                                            <?php endforeach; ?>
                                            <?php if (count($kos['facilities']) > 4): ?>
                                                <span class="facility-tag">+<?php echo count($kos['facilities']) - 4; ?> lagi</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="kos-footer">
                                            <div class="kos-price">
                                                Rp <?php echo number_format($kos['price'], 0, ',', '.'); ?>
                                                <span>/bulan</span>
                                            </div>
                                            <a href="booking.php?id=<?php echo $kos['id']; ?>" class="btn-booking" onclick="event.stopPropagation();">
                                                Booking
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <!-- No Results -->
                        <div class="no-results">
                            <div class="no-results-icon">🏠</div>
                            <h3>Tidak Ada Kos Ditemukan</h3>
                            <p>Coba ubah kriteria pencarian Anda atau hapus beberapa filter untuk melihat lebih banyak hasil.</p>
                            <button class="btn-filter btn-search-filter" onclick="resetFilters()">Reset Pencarian</button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Panggil file eksternal JavaScript -->
<script src="php11F_suggestion.js"></script>
<script src="assets/js/live-search.js"></script>

<script>
    // Toggle favorite function
    function toggleFavorite(kosId) {
        <?php if (isset($_SESSION['user'])): ?>
            // Tambahkan logika untuk menambah/menghapus favorit
            console.log('Toggle favorite for kos:', kosId);
        <?php else: ?>
            alert('Silakan login terlebih dahulu untuk menambah favorit');
            window.location.href = 'login.php';
        <?php endif; ?>
    }

    // Reset filter pencarian
    function resetFilters() {
        window.location.href = 'search.php';
    }

    // Update sorting berdasarkan nilai pilihan
    function updateSort(sortValue) {
        const url = new URL(window.location);
        url.searchParams.set('sort', sortValue);
        window.location.href = url.toString();
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Auto-submit form saat checkbox fasilitas diubah (opsional)
        const checkboxes = document.querySelectorAll('input[name="facilities[]"]');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                // document.getElementById('searchForm').submit(); // aktifkan kalau mau auto-submit
            });
        });

        // Tambahkan animasi ke kartu kosan
        const cards = document.querySelectorAll('.kos-card');
        cards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
            card.style.animation = 'fadeInUp 0.6s ease-out forwards';
        });
    });

    // Tambahkan animasi CSS fadeInUp ke halaman
    const style = document.createElement('style');
    style.textContent = `
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
    `;
    document.head.appendChild(style);
</script>
</body>
</html>
