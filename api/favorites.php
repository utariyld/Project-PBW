<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/database.php';

function sendResponse($success, $message, $data = null, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    sendResponse(false, 'Unauthorized access', null, 401);
}

$userId = $_SESSION['user']['id'];
$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    switch ($method) {
        case 'GET':
            // Get user favorites
            $sql = "SELECT f.*, k.name, k.price, k.type, 
                           l.city, l.district,
                           (SELECT image_url FROM kos_images WHERE kos_id = k.id AND is_primary = 1 LIMIT 1) as primary_image
                    FROM favorites f
                    JOIN kos k ON f.kos_id = k.id
                    LEFT JOIN locations l ON k.location_id = l.id
                    WHERE f.user_id = ? AND k.status = 'published'
                    ORDER BY f.created_at DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId]);
            $favorites = $stmt->fetchAll();
            
            sendResponse(true, 'Favorites retrieved successfully', $favorites);
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['kos_id']) || !isset($input['action'])) {
                sendResponse(false, 'Missing required parameters', null, 400);
            }
            
            $kosId = (int)$input['kos_id'];
            $action = $input['action'];
            
            // Check if kos exists
            $stmt = $pdo->prepare("SELECT id FROM kos WHERE id = ? AND status = 'published'");
            $stmt->execute([$kosId]);
            if (!$stmt->fetch()) {
                sendResponse(false, 'Kos not found', null, 404);
            }
            
            if ($action === 'toggle') {
                // Check if already favorited
                $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND kos_id = ?");
                $stmt->execute([$userId, $kosId]);
                $existing = $stmt->fetch();
                
                if ($existing) {
                    // Remove from favorites
                    $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND kos_id = ?");
                    $stmt->execute([$userId, $kosId]);
                    sendResponse(true, 'Kos dihapus dari favorit');
                } else {
                    // Add to favorites
                    $stmt = $pdo->prepare("INSERT INTO favorites (user_id, kos_id) VALUES (?, ?)");
                    $stmt->execute([$userId, $kosId]);
                    sendResponse(true, 'Kos ditambahkan ke favorit');
                }
            } else {
                sendResponse(false, 'Invalid action', null, 400);
            }
            break;
            
        case 'DELETE':
            if (!isset($_GET['kos_id'])) {
                sendResponse(false, 'Missing kos_id parameter', null, 400);
            }
            
            $kosId = (int)$_GET['kos_id'];
            
            $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND kos_id = ?");
            $result = $stmt->execute([$userId, $kosId]);
            
            if ($stmt->rowCount() > 0) {
                sendResponse(true, 'Kos dihapus dari favorit');
            } else {
                sendResponse(false, 'Kos tidak ditemukan di favorit', null, 404);
            }
            break;
            
        default:
            sendResponse(false, 'Method not allowed', null, 405);
            break;
    }
    
} catch (Exception $e) {
    error_log("Favorites API Error: " . $e->getMessage());
    sendResponse(false, 'Internal server error', null, 500);
}
?>
