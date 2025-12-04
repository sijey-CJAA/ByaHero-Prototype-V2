#!/usr/bin/env php
<?php
/**
 * ByaHero Database Initialization Script
 * 
 * This script initializes the SQLite database and seeds it with initial bus data.
 * Run this script before first use: php init_db.php
 */

define('DB_PATH', __DIR__ . '/data/db.sqlite');

echo "ByaHero Database Initialization\n";
echo "================================\n\n";

// Check if data directory exists
if (!is_dir(__DIR__ . '/data')) {
    echo "Creating data directory...\n";
    mkdir(__DIR__ . '/data', 0750, true);
}

// Check if database already exists
if (file_exists(DB_PATH)) {
    echo "Warning: Database already exists at " . DB_PATH . "\n";
    echo "Do you want to reinitialize it? This will clear all data. (y/N): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    
    if (trim(strtolower($line)) !== 'y') {
        echo "Initialization cancelled.\n";
        exit(0);
    }
    
    echo "Removing existing database...\n";
    unlink(DB_PATH);
}

try {
    echo "Creating database connection...\n";
    $db = new PDO('sqlite:' . DB_PATH);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Creating buses table...\n";
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
    
    echo "Seeding initial bus data...\n";
    $stmt = $db->prepare("
        INSERT INTO buses (code, seats_total, seats_available, status) 
        VALUES (?, 40, 40, 'available')
    ");
    
    $buses = ['BUS-001', 'BUS-002', 'BUS-003'];
    foreach ($buses as $busCode) {
        $stmt->execute([$busCode]);
        echo "  - Added: $busCode\n";
    }
    
    echo "\n✅ Database initialized successfully!\n";
    echo "\nNext steps:\n";
    echo "1. Start the PHP development server:\n";
    echo "   php -S localhost:8000 -t public\n";
    echo "\n2. Open in your browser:\n";
    echo "   - Passenger view: http://localhost:8000/index.php\n";
    echo "   - Conductor view: http://localhost:8000/conductor.php\n";
    echo "\nHappy tracking! 🚌\n";
    
} catch (PDOException $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
