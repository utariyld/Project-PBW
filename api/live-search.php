<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../includes/functions.php';

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    $pdo = getConnection();
    if (!$pdo) {
        throw new Exception('Database connection failed');
    }

    $query = isset($_GET['q']) ? trim($_GET['q']) : '';
    $limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 20) : 10;
    
    if (strlen($query) < 2) {
        echo json_encode(['success' => true, 'data' => [], 'count' => 0]);
        exit;
    }

    // Search query with FULLTEXT or LIKE
    $sql = "
        SELECT 
            k.id,
            k.name,
            k.address,
            k.price,
            k.type,
            k.room_size,
            CONCAT(l.city, ', ', l.district) AS location,
            COALESCE(ki.image_url, 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=400&h=300&fit=crop') as image_url,
            GROUP_CONCAT(DISTINCT f.name SEPARATOR ', ') AS facilities,
            COALESCE(AVG(r.rating), 4.5) as avg_rating,
            COUNT(DISTINCT r.id) as review_count
        FROM kos k
        LEFT JOIN locations l ON k.location_id = l.id
        LEFT JOIN kos_images ki ON k.id = ki.kos_id AND ki.is_primary = 1
        LEFT JOIN kos_facilities kf ON k.id = kf.kos_id
        LEFT JOIN facilities f ON kf.facility_id = f.id
        LEFT JOIN reviews r ON k.id = r.kos_id
        WHERE k.status = 'published' 
        AND k.is_available = 1
        AND (
            k.name LIKE :query 
            OR k.address LIKE :query 
            OR l.city LIKE :query 
            OR l.district LIKE :query
            OR f.name LIKE :query
        )
        GROUP BY k.id, k.name, k.address, k.price, k.type, k.room_size, l.city, l.district, ki.image_url
        ORDER BY 
            CASE 
                WHEN k.name LIKE :exact_query THEN 1
                WHEN k.name LIKE :start_query THEN 2
                ELSE 3
            END,
            k.is_featured DESC,
            avg_rating DESC
        LIMIT :limit
    ";

    $stmt = $pdo->prepare($sql);
    $searchTerm = "%{$query}%";
    $exactTerm = $query;
    $startTerm = "{$query}%";
    
    $stmt->bindParam(':query', $searchTerm, PDO::PARAM_STR);
    $stmt->bindParam(':exact_query', $exactTerm, PDO::PARAM_STR);
    $stmt->bindParam(':start_query', $startTerm, PDO::PARAM_STR);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format results
    $formattedResults = array_map(function($row) {
        return [
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'location' => $row['location'],
            'address' => $row['address'],
            'price' => (int)$row['price'],
            'formatted_price' => 'Rp ' . number_format($row['price'], 0, ',', '.'),
            'type' => ucfirst(str_replace('-', '/', $row['type'])),
            'room_size' => $row['room_size'],
            'image' => $row['image_url'],
            'facilities' => $row['facilities'] ? explode(', ', $row['facilities']) : [],
            'rating' => round((float)$row['avg_rating'], 1),
            'review_count' => (int)$row['review_count']
        ];
    }, $results);

    echo json_encode([
        'success' => true,
        'data' => $formattedResults,
        'count' => count($formattedResults),
        'query' => $query
    ]);

} catch (Exception $e) {
    error_log("Live search error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Search failed',
        'message' => $e->getMessage()
    ]);
}
?>