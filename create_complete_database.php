<?php
/**
 * Complete Database Setup Script for TemanKosan
 * Mengatasi masalah pencarian live location yang gagal
 */

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🚀 TemanKosan Complete Database Setup</h1>";
echo "<p>Script ini akan membuat semua tabel yang diperlukan untuk fitur pencarian live location.</p>";

// Database configuration
$config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'temankosan',
    'port' => 3306
];

// Test MySQL connection
echo "<h2>Step 1: Testing MySQL Connection</h2>";
try {
    $dsn = "mysql:host={$config['host']};port={$config['port']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "<p style='color: green;'>✅ MySQL connection successful!</p>";
    $version = $pdo->query("SELECT VERSION()")->fetchColumn();
    echo "<p>MySQL Version: <strong>{$version}</strong></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ MySQL connection failed!</p>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<h3>🔧 Troubleshooting:</h3>";
    echo "<ul>";
    echo "<li>Pastikan MySQL server sudah running</li>";
    echo "<li>Untuk XAMPP: Start Apache dan MySQL di Control Panel</li>";
    echo "<li>Untuk MAMP: Start servers dan periksa port</li>";
    echo "</ul>";
    exit();
}

// Create database if not exists
echo "<h2>Step 2: Creating Database</h2>";
try {
    $databases = $pdo->query("SHOW DATABASES LIKE '{$config['database']}'")->fetchAll();
    
    if (empty($databases)) {
        $pdo->exec("CREATE DATABASE `{$config['database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "<p style='color: green;'>✅ Database '{$config['database']}' created successfully!</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Database '{$config['database']}' already exists.</p>";
    }
    
    // Connect to the database
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Failed to create database!</p>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    exit();
}

// Create all required tables
echo "<h2>Step 3: Creating Tables</h2>";

$tables = [
    // Locations table
    'locations' => "
    CREATE TABLE IF NOT EXISTS `locations` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `city` varchar(100) NOT NULL,
        `district` varchar(100) NOT NULL,
        `subdistrict` varchar(100) DEFAULT NULL,
        `province` varchar(100) NOT NULL,
        `postal_code` varchar(10) DEFAULT NULL,
        `latitude` decimal(10,8) DEFAULT NULL,
        `longitude` decimal(11,8) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `idx_city` (`city`),
        KEY `idx_district` (`district`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    // Users table
    'users' => "
    CREATE TABLE IF NOT EXISTS `users` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(100) NOT NULL,
        `email` varchar(100) NOT NULL UNIQUE,
        `phone` varchar(20) DEFAULT NULL,
        `password_hash` varchar(255) NOT NULL,
        `role` enum('member','owner','admin') DEFAULT 'member',
        `email_verified` tinyint(1) DEFAULT 0,
        `verification_token` varchar(64) DEFAULT NULL,
        `last_login` timestamp NULL DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `email` (`email`),
        KEY `idx_role` (`role`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    // Facilities table
    'facilities' => "
    CREATE TABLE IF NOT EXISTS `facilities` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(100) NOT NULL,
        `icon` varchar(50) DEFAULT NULL,
        `category` enum('basic','comfort','security','location') DEFAULT 'basic',
        `is_active` tinyint(1) DEFAULT 1,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `name` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    // Kos table
    'kos' => "
    CREATE TABLE IF NOT EXISTS `kos` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `owner_id` int(11) NOT NULL,
        `location_id` int(11) NOT NULL,
        `name` varchar(200) NOT NULL,
        `slug` varchar(250) NOT NULL UNIQUE,
        `description` text,
        `address` text NOT NULL,
        `price` int(11) NOT NULL,
        `type` enum('putra','putri','campur') NOT NULL,
        `room_size` varchar(50) DEFAULT NULL,
        `total_rooms` int(11) DEFAULT 1,
        `available_rooms` int(11) DEFAULT 1,
        `is_available` tinyint(1) DEFAULT 1,
        `is_featured` tinyint(1) DEFAULT 0,
        `status` enum('draft','published','archived') DEFAULT 'published',
        `view_count` int(11) DEFAULT 0,
        `rating` decimal(3,2) DEFAULT 0.00,
        `review_count` int(11) DEFAULT 0,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `slug` (`slug`),
        KEY `owner_id` (`owner_id`),
        KEY `location_id` (`location_id`),
        KEY `idx_type` (`type`),
        KEY `idx_price` (`price`),
        KEY `idx_status` (`status`),
        KEY `idx_available` (`is_available`),
        FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
        FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    // Kos facilities junction table
    'kos_facilities' => "
    CREATE TABLE IF NOT EXISTS `kos_facilities` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `kos_id` int(11) NOT NULL,
        `facility_id` int(11) NOT NULL,
        `is_available` tinyint(1) DEFAULT 1,
        `notes` varchar(255) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `kos_facility_unique` (`kos_id`,`facility_id`),
        KEY `kos_id` (`kos_id`),
        KEY `facility_id` (`facility_id`),
        FOREIGN KEY (`kos_id`) REFERENCES `kos` (`id`) ON DELETE CASCADE,
        FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    // Kos images table
    'kos_images' => "
    CREATE TABLE IF NOT EXISTS `kos_images` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `kos_id` int(11) NOT NULL,
        `image_url` varchar(500) NOT NULL,
        `alt_text` varchar(255) DEFAULT NULL,
        `is_primary` tinyint(1) DEFAULT 0,
        `sort_order` int(11) DEFAULT 0,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `kos_id` (`kos_id`),
        KEY `idx_primary` (`is_primary`),
        FOREIGN KEY (`kos_id`) REFERENCES `kos` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    // Testimonials table (keeping existing)
    'testimonials' => "
    CREATE TABLE IF NOT EXISTS `testimonials` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(100) NOT NULL,
        `email` varchar(100) NOT NULL,
        `phone` varchar(20) DEFAULT NULL,
        `kos_name` varchar(200) NOT NULL,
        `rating` tinyint(1) NOT NULL CHECK (`rating` >= 1 AND `rating` <= 5),
        `comment` text NOT NULL,
        `is_approved` tinyint(1) DEFAULT 0,
        `ip_address` varchar(45) DEFAULT NULL,
        `user_agent` text DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `idx_approved` (`is_approved`),
        KEY `idx_rating` (`rating`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
];

$createdTables = 0;
foreach ($tables as $tableName => $sql) {
    try {
        $pdo->exec($sql);
        echo "<p style='color: green;'>✅ Table '{$tableName}' created successfully!</p>";
        $createdTables++;
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Failed to create table '{$tableName}': " . $e->getMessage() . "</p>";
    }
}

echo "<p><strong>Created {$createdTables} tables successfully!</strong></p>";

// Insert sample data
echo "<h2>Step 4: Inserting Sample Data</h2>";

// Insert locations
$sampleLocations = [
    ['Jakarta Pusat', 'Menteng', 'Menteng', 'DKI Jakarta', '10310', -6.200000, 106.816666],
    ['Jakarta Pusat', 'Tanah Abang', 'Bendungan Hilir', 'DKI Jakarta', '10210', -6.207500, 106.814167],
    ['Jakarta Selatan', 'Kebayoran Baru', 'Senayan', 'DKI Jakarta', '12190', -6.225000, 106.800000],
    ['Jakarta Barat', 'Kebon Jeruk', 'Palmerah', 'DKI Jakarta', '11480', -6.186667, 106.783333],
    ['Jakarta Timur', 'Jatinegara', 'Kampung Melayu', 'DKI Jakarta', '13340', -6.233333, 106.866667]
];

$insertLocation = $pdo->prepare("INSERT INTO locations (city, district, subdistrict, province, postal_code, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?)");
$locationIds = [];
foreach ($sampleLocations as $location) {
    try {
        $insertLocation->execute($location);
        $locationIds[] = $pdo->lastInsertId();
    } catch (PDOException $e) {
        // Skip if duplicate
        if ($e->getCode() != 23000) {
            echo "<p style='color: orange;'>⚠️ Location insert warning: " . $e->getMessage() . "</p>";
        }
    }
}
echo "<p style='color: green;'>✅ Inserted " . count($locationIds) . " sample locations!</p>";

// Insert facilities
$sampleFacilities = [
    ['WiFi', 'wifi', 'basic'],
    ['AC', 'snowflake', 'comfort'],
    ['Kamar Mandi Dalam', 'bath', 'basic'],
    ['Kamar Mandi Luar', 'restroom', 'basic'],
    ['Parkir Motor', 'motorcycle', 'basic'],
    ['Parkir Mobil', 'car', 'basic'],
    ['Dapur', 'utensils', 'basic'],
    ['Kulkas', 'snowflake', 'comfort'],
    ['Laundry', 'tshirt', 'comfort'],
    ['Security 24 Jam', 'shield-alt', 'security'],
    ['CCTV', 'video', 'security'],
    ['Akses 24 Jam', 'clock', 'security'],
    ['Dekat Kampus', 'university', 'location'],
    ['Dekat Mall', 'shopping-cart', 'location'],
    ['Dekat Stasiun', 'train', 'location']
];

$insertFacility = $pdo->prepare("INSERT INTO facilities (name, icon, category) VALUES (?, ?, ?)");
$facilityIds = [];
foreach ($sampleFacilities as $facility) {
    try {
        $insertFacility->execute($facility);
        $facilityIds[] = $pdo->lastInsertId();
    } catch (PDOException $e) {
        // Skip if duplicate
        if ($e->getCode() != 23000) {
            echo "<p style='color: orange;'>⚠️ Facility insert warning: " . $e->getMessage() . "</p>";
        }
    }
}
echo "<p style='color: green;'>✅ Inserted " . count($facilityIds) . " sample facilities!</p>";

// Insert sample user (owner)
$insertUser = $pdo->prepare("INSERT INTO users (name, email, phone, password_hash, role, email_verified) VALUES (?, ?, ?, ?, ?, ?)");
$ownerIds = [];
$sampleUsers = [
    ['Budi Santoso', 'budi@temankosan.com', '081234567890', password_hash('password123', PASSWORD_DEFAULT), 'owner', 1],
    ['Sari Dewi', 'sari@temankosan.com', '081234567891', password_hash('password123', PASSWORD_DEFAULT), 'owner', 1],
    ['Ahmad Wijaya', 'ahmad@temankosan.com', '081234567892', password_hash('password123', PASSWORD_DEFAULT), 'owner', 1]
];

foreach ($sampleUsers as $user) {
    try {
        $insertUser->execute($user);
        $ownerIds[] = $pdo->lastInsertId();
    } catch (PDOException $e) {
        // Skip if duplicate
        if ($e->getCode() != 23000) {
            echo "<p style='color: orange;'>⚠️ User insert warning: " . $e->getMessage() . "</p>";
        }
    }
}
echo "<p style='color: green;'>✅ Inserted " . count($ownerIds) . " sample users!</p>";

// Insert sample kos data
if (!empty($ownerIds) && !empty($locationIds)) {
    $sampleKos = [
        [
            'owner_id' => $ownerIds[0],
            'location_id' => $locationIds[0],
            'name' => 'Kos Melati Putih Jakarta Pusat',
            'slug' => 'kos-melati-putih-jakarta-pusat',
            'description' => 'Kos nyaman di pusat Jakarta dengan fasilitas lengkap. Dekat dengan berbagai kampus dan pusat perbelanjaan.',
            'address' => 'Jl. Menteng Raya No. 45, Menteng, Jakarta Pusat',
            'price' => 2500000,
            'type' => 'campur',
            'room_size' => '3x4 meter',
            'total_rooms' => 20,
            'available_rooms' => 5,
            'is_featured' => 1
        ],
        [
            'owner_id' => $ownerIds[1],
            'location_id' => $locationIds[1],
            'name' => 'Kos Mawar Indah Tanah Abang',
            'slug' => 'kos-mawar-indah-tanah-abang',
            'description' => 'Kos putri dengan keamanan 24 jam. Lokasi strategis dekat stasiun Tanah Abang.',
            'address' => 'Jl. Bendungan Hilir No. 12, Tanah Abang, Jakarta Pusat',
            'price' => 2200000,
            'type' => 'putri',
            'room_size' => '3x3 meter',
            'total_rooms' => 15,
            'available_rooms' => 3
        ],
        [
            'owner_id' => $ownerIds[2],
            'location_id' => $locationIds[2],
            'name' => 'Kos Anggrek Residence Senayan',
            'slug' => 'kos-anggrek-residence-senayan',
            'description' => 'Kos premium di area Senayan dengan fasilitas mewah dan akses mudah ke berbagai tempat.',
            'address' => 'Jl. Senayan Raya No. 88, Senayan, Jakarta Selatan',
            'price' => 3500000,
            'type' => 'putra',
            'room_size' => '4x4 meter',
            'total_rooms' => 25,
            'available_rooms' => 8,
            'is_featured' => 1
        ]
    ];

    $insertKos = $pdo->prepare("
        INSERT INTO kos (owner_id, location_id, name, slug, description, address, price, type, room_size, total_rooms, available_rooms, is_featured) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $kosIds = [];
    foreach ($sampleKos as $kos) {
        try {
            $insertKos->execute(array_values($kos));
            $kosIds[] = $pdo->lastInsertId();
        } catch (PDOException $e) {
            echo "<p style='color: orange;'>⚠️ Kos insert warning: " . $e->getMessage() . "</p>";
        }
    }
    echo "<p style='color: green;'>✅ Inserted " . count($kosIds) . " sample kos!</p>";

    // Insert kos facilities relationships
    if (!empty($kosIds) && !empty($facilityIds)) {
        $insertKosFacility = $pdo->prepare("INSERT INTO kos_facilities (kos_id, facility_id) VALUES (?, ?)");
        $facilityCount = 0;
        
        foreach ($kosIds as $kosId) {
            // Add random facilities to each kos
            $randomFacilities = array_rand($facilityIds, min(rand(5, 10), count($facilityIds)));
            if (!is_array($randomFacilities)) {
                $randomFacilities = [$randomFacilities];
            }
            
            foreach ($randomFacilities as $facilityIndex) {
                try {
                    $insertKosFacility->execute([$kosId, $facilityIds[$facilityIndex]]);
                    $facilityCount++;
                } catch (PDOException $e) {
                    // Skip duplicates
                }
            }
        }
        echo "<p style='color: green;'>✅ Inserted {$facilityCount} kos-facility relationships!</p>";
    }

    // Insert sample images
    if (!empty($kosIds)) {
        $sampleImages = [
            'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1631889993959-41b4e9c6e2c4?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?w=800&h=600&fit=crop'
        ];

        $insertImage = $pdo->prepare("INSERT INTO kos_images (kos_id, image_url, is_primary, sort_order) VALUES (?, ?, ?, ?)");
        $imageCount = 0;
        
        foreach ($kosIds as $index => $kosId) {
            // Add 2-3 images per kos
            $numImages = rand(2, 3);
            for ($i = 0; $i < $numImages; $i++) {
                $imageUrl = $sampleImages[($index + $i) % count($sampleImages)];
                $isPrimary = $i === 0 ? 1 : 0;
                try {
                    $insertImage->execute([$kosId, $imageUrl, $isPrimary, $i + 1]);
                    $imageCount++;
                } catch (PDOException $e) {
                    echo "<p style='color: orange;'>⚠️ Image insert warning: " . $e->getMessage() . "</p>";
                }
            }
        }
        echo "<p style='color: green;'>✅ Inserted {$imageCount} sample images!</p>";
    }
}

// Verify setup
echo "<h2>Step 5: Verifying Setup</h2>";

try {
    $tableChecks = [
        'locations' => "SELECT COUNT(*) FROM locations",
        'users' => "SELECT COUNT(*) FROM users",
        'facilities' => "SELECT COUNT(*) FROM facilities", 
        'kos' => "SELECT COUNT(*) FROM kos",
        'kos_facilities' => "SELECT COUNT(*) FROM kos_facilities",
        'kos_images' => "SELECT COUNT(*) FROM kos_images"
    ];

    foreach ($tableChecks as $table => $query) {
        $count = $pdo->query($query)->fetchColumn();
        echo "<p style='color: green;'>✅ Table '{$table}': <strong>{$count}</strong> records</p>";
    }

    // Test search functionality
    echo "<h3>Testing Search Query:</h3>";
    $testQuery = "
        SELECT k.name, l.city, l.district, 
               GROUP_CONCAT(f.name) as facilities
        FROM kos k
        LEFT JOIN locations l ON k.location_id = l.id
        LEFT JOIN kos_facilities kf ON k.id = kf.kos_id
        LEFT JOIN facilities f ON kf.facility_id = f.id
        WHERE k.name LIKE '%Jakarta%' OR l.city LIKE '%Jakarta%'
        GROUP BY k.id
        LIMIT 3
    ";
    
    $results = $pdo->query($testQuery)->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($results)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Nama Kos</th><th>Lokasi</th><th>Fasilitas</th></tr>";
        foreach ($results as $result) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($result['name']) . "</td>";
            echo "<td>" . htmlspecialchars($result['city']) . ", " . htmlspecialchars($result['district']) . "</td>";
            echo "<td>" . htmlspecialchars($result['facilities'] ?? 'Tidak ada') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<p style='color: green;'>✅ Search functionality test passed!</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ No search results found</p>";
    }

} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Verification failed: " . $e->getMessage() . "</p>";
}

echo "<h2>🎉 Database Setup Complete!</h2>";
echo "<p style='color: green; font-size: 18px;'><strong>Semua tabel untuk fitur pencarian live location telah dibuat!</strong></p>";

echo "<h3>What's Next:</h3>";
echo "<ol>";
echo "<li>✅ Database dan tabel sudah siap</li>";
echo "<li>✅ Sample data sudah diinsert</li>";
echo "<li>🔄 Test pencarian di halaman utama</li>";
echo "<li>🔄 Pastikan API endpoint berfungsi</li>";
echo "</ol>";

echo "<h3>Test Links:</h3>";
echo "<ul>";
echo "<li><a href='search.php?location=Jakarta' target='_blank'>Test Search: Jakarta</a></li>";
echo "<li><a href='api/search-suggestion.php?q=Jakarta' target='_blank'>Test API Suggestion</a></li>";
echo "<li><a href='index.php' target='_blank'>Homepage</a></li>";
echo "</ul>";

echo "<p><em>Script ini dapat dihapus setelah database setup selesai.</em></p>";
?>