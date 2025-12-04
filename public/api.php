<?php
/**
 * ByaHero Bus Tracking API
 * Simple REST-ish API using SQLite for real-time bus tracking
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Database configuration
define('DB_PATH', __DIR__ . '/../data/db.sqlite');

/**
 * Get PDO database connection
 */
function getDB() {
    try {
        $db = new PDO('sqlite:' . DB_PATH);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
        exit;
    }
}

/**
 * Initialize database tables and seed initial data
 */
function initDB() {
    $db = getDB();
    
    // Create buses table
    $db->exec("
        CREATE TABLE IF NOT EXISTS buses (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            code TEXT UNIQUE NOT NULL,
            route TEXT,
            lat REAL,
            lng REAL,
            seats_total INTEGER NOT NULL DEFAULT 40,
            seats_available INTEGER NOT NULL DEFAULT 40,
            status TEXT NOT NULL DEFAULT 'available',
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Check if buses already exist
    $count = $db->query("SELECT COUNT(*) FROM buses")->fetchColumn();
    
    if ($count == 0) {
        // Seed initial buses
        $stmt = $db->prepare("
            INSERT INTO buses (code, seats_total, seats_available, status) 
            VALUES (?, 40, 40, 'available')
        ");
        
        $buses = ['BUS-001', 'BUS-002', 'BUS-003'];
        foreach ($buses as $busCode) {
            $stmt->execute([$busCode]);
        }
    }
    
    return ['success' => true, 'message' => 'Database initialized successfully'];
}

/**
 * Get all buses
 */
function getBuses() {
    $db = getDB();
    $stmt = $db->query("
        SELECT id, code, route, lat, lng, seats_total, seats_available, status, updated_at 
        FROM buses 
        ORDER BY code
    ");
    
    $buses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return ['success' => true, 'buses' => $buses];
}

/**
 * Register a bus (set route and mark as available)
 */
function registerBus() {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if ($data === null) {
        http_response_code(400);
        return ['success' => false, 'error' => 'Invalid JSON'];
    }
    
    if (!isset($data['bus_id']) || !isset($data['route'])) {
        http_response_code(400);
        return ['success' => false, 'error' => 'Missing bus_id or route'];
    }
    
    $db = getDB();
    $stmt = $db->prepare("
        UPDATE buses 
        SET route = ?, status = 'available', updated_at = CURRENT_TIMESTAMP 
        WHERE id = ?
    ");
    
    $stmt->execute([$data['route'], $data['bus_id']]);
    
    return ['success' => true, 'message' => 'Bus registered successfully'];
}

/**
 * Update bus location and details
 */
function updateLocation() {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if ($data === null) {
        http_response_code(400);
        return ['success' => false, 'error' => 'Invalid JSON'];
    }
    
    if (!isset($data['bus_id']) || !isset($data['lat']) || !isset($data['lng'])) {
        http_response_code(400);
        return ['success' => false, 'error' => 'Missing required fields: bus_id, lat, lng'];
    }
    
    // Validate latitude and longitude
    $lat = filter_var($data['lat'], FILTER_VALIDATE_FLOAT);
    $lng = filter_var($data['lng'], FILTER_VALIDATE_FLOAT);
    
    if ($lat === false || $lng === false || $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
        http_response_code(400);
        return ['success' => false, 'error' => 'Invalid latitude or longitude values'];
    }
    
    $db = getDB();
    
    // Build dynamic query based on provided fields
    $fields = ['lat = ?', 'lng = ?', 'updated_at = CURRENT_TIMESTAMP'];
    $params = [$lat, $lng];
    
    if (isset($data['route'])) {
        $fields[] = 'route = ?';
        $params[] = $data['route'];
    }
    
    if (isset($data['seats_available'])) {
        $seatsAvailable = filter_var($data['seats_available'], FILTER_VALIDATE_INT);
        if ($seatsAvailable === false || $seatsAvailable < 0) {
            http_response_code(400);
            return ['success' => false, 'error' => 'Invalid seats_available value'];
        }
        $fields[] = 'seats_available = ?';
        $params[] = $seatsAvailable;
    }
    
    if (isset($data['status'])) {
        $allowedStatuses = ['available', 'on_stop', 'full', 'unavailable'];
        if (!in_array($data['status'], $allowedStatuses)) {
            http_response_code(400);
            return ['success' => false, 'error' => 'Invalid status value'];
        }
        $fields[] = 'status = ?';
        $params[] = $data['status'];
    }
    
    $params[] = $data['bus_id'];
    
    $sql = "UPDATE buses SET " . implode(', ', $fields) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    return ['success' => true, 'message' => 'Location updated successfully'];
}

// Handle the request
$action = $_GET['action'] ?? $_POST['action'] ?? 'get_buses';

try {
    switch ($action) {
        case 'init_db':
            $response = initDB();
            break;
            
        case 'get_buses':
            $response = getBuses();
            break;
            
        case 'register_bus':
            $response = registerBus();
            break;
            
        case 'update_location':
            $response = updateLocation();
            break;
            
        default:
            http_response_code(400);
            $response = ['success' => false, 'error' => 'Invalid action'];
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
