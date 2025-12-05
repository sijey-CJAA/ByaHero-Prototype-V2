#!/usr/bin/env php
<?php
/**
 * scripts/clear_locations.php
 *
 * Usage:
 *   php scripts/clear_locations.php BUS-001 BUS-002
 *   php scripts/clear_locations.php --id 1 2 3
 *
 * This script sets lat and lng to NULL for the specified buses (by code or id),
 * sets status to 'unavailable' and updates updated_at to CURRENT_TIMESTAMP.
 */

$dbPath = __DIR__ . '/../data/db.sqlite';

// Simple argument parsing
$args = $argv;
array_shift($args); // remove script name

$byId = false;
if (isset($args[0]) && $args[0] === '--id') {
    $byId = true;
    array_shift($args);
}

if (empty($args)) {
    // Print usage and exit
    fwrite(STDOUT, "Usage: php scripts/clear_locations.php BUS-001 BUS-002\n");
    fwrite(STDOUT, "  or: php scripts/clear_locations.php --id 1 2 3\n");
    exit(1);
}

try {
    if (!file_exists($dbPath)) {
        throw new RuntimeException("Database not found at: $dbPath");
    }

    // Connect to SQLite
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($byId) {
        // Ensure all args are integers
        $ids = array_map('intval', $args);
        if (count($ids) === 0) {
            throw new RuntimeException("No IDs specified.");
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "UPDATE buses SET lat = NULL, lng = NULL, status = 'unavailable', updated_at = CURRENT_TIMESTAMP WHERE id IN ($placeholders)";
        $stmt = $db->prepare($sql);
        foreach ($ids as $i => $val) {
            $stmt->bindValue($i + 1, $val, PDO::PARAM_INT);
        }
    } else {
        // Treat args as bus codes (strings)
        $codes = $args;
        if (count($codes) === 0) {
            throw new RuntimeException("No bus codes specified.");
        }

        $placeholders = implode(',', array_fill(0, count($codes), '?'));
        $sql = "UPDATE buses SET lat = NULL, lng = NULL, status = 'unavailable', updated_at = CURRENT_TIMESTAMP WHERE code IN ($placeholders)";
        $stmt = $db->prepare($sql);
        foreach ($codes as $i => $val) {
            $stmt->bindValue($i + 1, $val, PDO::PARAM_STR);
        }
    }

    $stmt->execute();
    $updated = $stmt->rowCount();

    fwrite(STDOUT, "Cleared locations for " . ($byId ? "IDs: " : "codes: ") . implode(', ', $args) . PHP_EOL);
    fwrite(STDOUT, "Rows updated: $updated" . PHP_EOL);
    exit(0);

} catch (PDOException $e) {
    fwrite(STDERR, "Database error: " . $e->getMessage() . PHP_EOL);
    exit(2);
} catch (Exception $e) {
    fwrite(STDERR, "Error: " . $e->getMessage() . PHP_EOL);
    exit(1);
}