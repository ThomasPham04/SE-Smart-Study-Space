<?php
session_start();
require_once '../config/db_connection.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['user_type'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($data['id']) || !isset($data['name']) || !isset($data['room_type_id']) || 
    !isset($data['building']) || !isset($data['floor']) || !isset($data['status']) || 
    !isset($data['equipment_status'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

try {
    $db = new DbConnect();
    $conn = $db->connect();

    // Update room
    $stmt = $conn->prepare("
        UPDATE rooms 
        SET name = ?, 
            room_type_id = ?, 
            building = ?, 
            floor = ?, 
            status = ?, 
            equipment_status = ?,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    ");

    $stmt->bind_param(
        "sissssi",
        $data['name'],
        $data['room_type_id'],
        $data['building'],
        $data['floor'],
        $data['status'],
        $data['equipment_status'],
        $data['id']
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception($stmt->error);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 