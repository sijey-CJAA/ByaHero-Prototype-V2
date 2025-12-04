<?php
/**
 * API Endpoint: Update Bus Status
 * Accepts POST requests with JSON body to update bus status and seats
 * Expected format: {"code":"BUS001","status":"full","seats_available":0}
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../src/Database.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed. Use POST.'
    ]);
    exit;
}

try {
    // Read JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input');
    }
    
    // Validate required fields
    if (!isset($data['code']) || empty($data['code'])) {
        throw new Exception('Bus code is required');
    }
    
    $code = trim($data['code']);
    $db = Database::getInstance();
    
    // Check if bus exists and get current data
    $bus = $db->fetchOne("SELECT * FROM buses WHERE code = :code", ['code' => $code]);
    
    if (!$bus) {
        http_response_code(404);
        throw new Exception('Bus not found: ' . $code);
    }
    
    // Prepare update fields
    $updateFields = [];
    $params = ['code' => $code];
    
    // Validate and add status if provided
    if (isset($data['status'])) {
        $allowedStatuses = ['available', 'unavailable', 'on_stop', 'full'];
        $status = trim($data['status']);
        
        if (!in_array($status, $allowedStatuses)) {
            throw new Exception('Invalid status. Allowed values: ' . implode(', ', $allowedStatuses));
        }
        
        $updateFields[] = 'status = :status';
        $params['status'] = $status;
    }
    
    // Validate and add seats_available if provided
    if (isset($data['seats_available'])) {
        $seatsAvailable = (int)$data['seats_available'];
        $seatsTotal = (int)$bus['seats_total'];
        
        if ($seatsAvailable < 0) {
            throw new Exception('seats_available cannot be negative');
        }
        
        if ($seatsAvailable > $seatsTotal) {
            throw new Exception("seats_available ($seatsAvailable) cannot exceed seats_total ($seatsTotal)");
        }
        
        $updateFields[] = 'seats_available = :seats_available';
        $params['seats_available'] = $seatsAvailable;
    }
    
    // Always update timestamp
    $updateFields[] = 'last_update = :last_update';
    $params['last_update'] = time();
    
    // Nothing to update
    if (count($updateFields) === 1) { // Only timestamp
        throw new Exception('No fields to update. Provide status or seats_available.');
    }
    
    // Build and execute update query
    $sql = "UPDATE buses SET " . implode(', ', $updateFields) . " WHERE code = :code";
    $rowCount = $db->execute($sql, $params);
    
    // Get updated bus data
    $updatedBus = $db->fetchOne("SELECT * FROM buses WHERE code = :code", ['code' => $code]);
    $updatedBus['last_update_formatted'] = date('Y-m-d H:i:s', $updatedBus['last_update']);
    
    $response = [
        'success' => true,
        'message' => 'Bus updated successfully',
        'bus' => $updatedBus
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    // Use 400 for validation errors, unless a specific status was already set
    if (http_response_code() === 200) {
        http_response_code(400);
    }
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
