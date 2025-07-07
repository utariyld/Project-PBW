<?php
require_once(__DIR__ . '/../config/database.php');

header('Content-Type: application/json');

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (empty($query)) {
    echo json_encode([]);
    exit;
}

$results = [];

try {
    // Try to get database connection
    $db = Database::getInstance()->getConnection();
    
    if ($db) {
        // Try to query database
        $sql = "SELECT k.id, k.name, l.city, l.province, k.address
                FROM kos k 
                LEFT JOIN locations l ON k.location_id = l.id 
                WHERE k.status = 'published' AND k.is_available = 1 
                AND (k.name LIKE :q 
                   OR k.address LIKE :q 
                   OR l.city LIKE :q 
                   OR l.province LIKE :q)
                LIMIT 10";

        $stmt = $db->prepare($sql);
        $stmt->execute(['q' => "%$query%"]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    error_log("Search suggestion database error: " . $e->getMessage());
}

// If no database results or database not available, use fallback data
if (empty($results)) {
    $fallbackData = [
        ['id' => 1, 'name' => 'Kos Melati Putih Jakarta Pusat', 'city' => 'Jakarta Pusat', 'province' => 'DKI Jakarta', 'address' => 'Jl. Menteng Raya No. 45'],
        ['id' => 2, 'name' => 'Kos Mawar Indah Tanah Abang', 'city' => 'Jakarta Pusat', 'province' => 'DKI Jakarta', 'address' => 'Jl. Bendungan Hilir No. 12'],
        ['id' => 3, 'name' => 'Kos Anggrek Residence Senayan', 'city' => 'Jakarta Selatan', 'province' => 'DKI Jakarta', 'address' => 'Jl. Senayan Raya No. 88'],
        ['id' => 4, 'name' => 'Kos Dahlia Residence Kemang', 'city' => 'Jakarta Selatan', 'province' => 'DKI Jakarta', 'address' => 'Jl. Kemang Raya No. 67'],
        ['id' => 5, 'name' => 'Kos Sakura House Pancoran', 'city' => 'Jakarta Selatan', 'province' => 'DKI Jakarta', 'address' => 'Jl. Pancoran Barat No. 23'],
        ['id' => 6, 'name' => 'Kos Flamboyan Bekasi', 'city' => 'Bekasi', 'province' => 'Jawa Barat', 'address' => 'Jl. Ahmad Yani No. 45'],
        ['id' => 7, 'name' => 'Kos Teratai Depok', 'city' => 'Depok', 'province' => 'Jawa Barat', 'address' => 'Jl. Margonda Raya No. 123'],
        ['id' => 8, 'name' => 'Kos Kenanga Tangerang', 'city' => 'Tangerang', 'province' => 'Banten', 'address' => 'Jl. Sudirman No. 78']
    ];
    
    // Filter fallback data based on query
    $queryLower = strtolower($query);
    $results = array_filter($fallbackData, function($item) use ($queryLower) {
        return stripos($item['name'], $queryLower) !== false ||
               stripos($item['city'], $queryLower) !== false ||
               stripos($item['province'], $queryLower) !== false ||
               stripos($item['address'], $queryLower) !== false;
    });
    
    // Limit to 10 results
    $results = array_slice(array_values($results), 0, 10);
}

echo json_encode($results);
?>
