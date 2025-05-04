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
if (!isset($data['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing room ID']);
    exit();
}

try {
    $db = new DbConnect();
    $conn = $db->connect();
    $room_id = $data['id'];

    // Delete all room_equipment associated with this room
    $stmt = $conn->prepare("DELETE FROM room_equipment WHERE room_id = ?");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $stmt->close();

    // Delete all maintenance_logs associated with this room
    $stmt = $conn->prepare("DELETE FROM maintenance_logs WHERE room_id = ?");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $stmt->close();

    // Delete all bookings associated with this room (cascade delete)
    $stmt = $conn->prepare("DELETE FROM bookings WHERE room_id = ?");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $stmt->close();

    // Now, delete the room itself
    $stmt = $conn->prepare("DELETE FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Could not delete room. It may not exist.']);
    }
    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 