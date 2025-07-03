<?php
$db = new PDO("mysql:host=localhost;dbname=temankosan;charset=utf8mb4", "root", "");

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (!empty($query)) {
    $sql = "SELECT kos.id, kos.name, locations.city, locations.province 
            FROM kos 
            JOIN locations ON kos.location_id = locations.id 
            WHERE kos.name LIKE :q 
               OR kos.address LIKE :q 
               OR locations.city LIKE :q 
               OR locations.province LIKE :q";

    $stmt = $db->prepare($sql);
    $stmt->execute(['q' => "%$query%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($results);
} else {
    echo json_encode([]);
}
?>
