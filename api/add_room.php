<?php 
session_start();
require_once '../config/db_connection.php';

header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['user_type'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required_fields = ['name', 'room_type_id', 'building', 'floor'];
foreach ($required_fields as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit();
    }
}

try {
    $db = new DbConnect();
    $conn = $db->connect();

    // Prepare the SQL statement
    $stmt = $conn->prepare("
        INSERT INTO rooms (name, room_type_id, building, floor, status, equipment_status) 
        VALUES (?, ?, ?, ?, 'available', 'Đầy đủ')
    ");

    // Bind parameters
    $stmt->bind_param("siss", 
        $data['name'],
        $data['room_type_id'],
        $data['building'],
        $data['floor']
    );

    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Room added successfully',
            'room_id' => $stmt->insert_id
        ]);
    } else {
        throw new Exception("Failed to add room");
    }

    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}



