<?php
/**
 * Database Initialization Script
 * Creates SQLite database, tables, and seeds initial bus data
 */

require_once __DIR__ . '/../src/Database.php';

$dbPath = __DIR__ . '/../data/buses.sqlite';

echo "ByaHero Database Initialization\n";
echo "================================\n\n";

// Remove existing database if it exists
if (file_exists($dbPath)) {
    echo "Removing existing database...\n";
    unlink($dbPath);
}

// Ensure data directory exists
$dataDir = dirname($dbPath);
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
    echo "Created data directory: $dataDir\n";
}

try {
    // Create database connection
    echo "Creating new database...\n";
    $db = Database::getInstance($dbPath);
    
    // Create buses table
    echo "Creating buses table...\n";
    $createTableSQL = "
        CREATE TABLE IF NOT EXISTS buses (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            code TEXT UNIQUE NOT NULL,
            route TEXT NOT NULL,
            seats_total INTEGER NOT NULL,
            seats_available INTEGER NOT NULL,
            status TEXT NOT NULL,
            last_update INTEGER NOT NULL
        )
    ";
    $db->execute($createTableSQL);
    
    // Seed initial bus data
    echo "Seeding initial bus data...\n";
    
    $buses = [
        ['code' => 'BUS001', 'route' => 'Route 1', 'seats_total' => 40, 'seats_available' => 35, 'status' => 'available'],
        ['code' => 'BUS002', 'route' => 'Route 1', 'seats_total' => 40, 'seats_available' => 20, 'status' => 'available'],
        ['code' => 'BUS003', 'route' => 'Route 1', 'seats_total' => 40, 'seats_available' => 0, 'status' => 'full'],
    ];
    
    $insertSQL = "
        INSERT INTO buses (code, route, seats_total, seats_available, status, last_update)
        VALUES (:code, :route, :seats_total, :seats_available, :status, :last_update)
    ";
    
    foreach ($buses as $bus) {
        $bus['last_update'] = time();
        $db->execute($insertSQL, $bus);
        echo "  - Inserted {$bus['code']} on {$bus['route']}\n";
    }
    
    echo "\n✓ Database initialized successfully!\n";
    echo "Database location: $dbPath\n";
    
    // Display seeded buses
    echo "\nSeeded buses:\n";
    $allBuses = $db->fetchAll("SELECT * FROM buses ORDER BY code");
    foreach ($allBuses as $bus) {
        echo "  {$bus['code']}: {$bus['route']} - {$bus['status']} ({$bus['seats_available']}/{$bus['seats_total']} seats)\n";
    }
    
} catch (Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nDatabase is ready to use!\n";
