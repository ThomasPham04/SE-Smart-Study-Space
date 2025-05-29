<?php
require_once __DIR__ . '/config.php';

try {
    // Read the SQL file
    $sql = file_get_contents(__DIR__ . '/notifications.sql');
    
    // Execute the SQL
    if ($conn->multi_query($sql)) {
        echo "Notifications table created successfully\n";
    } else {
        echo "Error creating notifications table: " . $conn->error . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 