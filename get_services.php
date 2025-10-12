<?php
require_once 'config.php';

header('Content-Type: application/json');

try {
    $sql = "SELECT s.service_id, s.service_name, s.service_description, s.base_price, s.category, s.customizable, s.customization_options,
                   GROUP_CONCAT(DISTINCT sd.detail_name) as details,
                   GROUP_CONCAT(DISTINCT sf.feature_name) as features
            FROM tbl_services s
            LEFT JOIN tbl_service_details sd ON s.service_id = sd.service_id
            LEFT JOIN tbl_service_features sf ON s.service_id = sf.service_id
            WHERE s.status = 'active'
            GROUP BY s.service_id";
    
    $result = $conn->query($sql);
    
    $services = [];
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
    
    echo json_encode($services);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>