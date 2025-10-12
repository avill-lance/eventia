<?php
require_once 'config/database.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("
        SELECT package_id, package_name, package_description, base_price, event_type 
        FROM packages 
        WHERE is_active = 1 
        ORDER BY base_price ASC
    ");
    
    $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($packages);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Unable to load packages']);
}
?>