<?php
/**
 * API Endpoint: Get Routes
 * Returns list of unique routes available in the database
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../src/Database.php';

try {
    $db = Database::getInstance();
    
    // Get distinct routes from buses table
    $routes = $db->fetchAll("SELECT DISTINCT route FROM buses ORDER BY route");
    
    // Format response
    $response = [
        'success' => true,
        'routes' => array_map(function($row) {
            return $row['route'];
        }, $routes)
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
