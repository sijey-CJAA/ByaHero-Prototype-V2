<?php
/**
 * API Endpoint: Get Buses
 * Returns list of buses, optionally filtered by route
 * Query parameter: ?route=Route%201
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../src/Database.php';

try {
    $db = Database::getInstance();
    
    // Check if route filter is provided
    $route = isset($_GET['route']) ? trim($_GET['route']) : null;
    
    if ($route) {
        // Filter by route
        $buses = $db->fetchAll(
            "SELECT * FROM buses WHERE route = :route ORDER BY code",
            ['route' => $route]
        );
    } else {
        // Get all buses
        $buses = $db->fetchAll("SELECT * FROM buses ORDER BY code");
    }
    
    // Format response with human-readable timestamps
    foreach ($buses as &$bus) {
        $bus['last_update_formatted'] = date('Y-m-d H:i:s', $bus['last_update']);
    }
    unset($bus);
    
    $response = [
        'success' => true,
        'buses' => $buses,
        'count' => count($buses)
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
