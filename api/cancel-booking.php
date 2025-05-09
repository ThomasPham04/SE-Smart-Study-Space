<?php
session_start();
header('Content-Type: application/json');
require_once '../config/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$booking_id = $data['booking_id'] ?? null;

if (!$booking_id || !isset($_SESSION['user']['id'])) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin hoặc chưa đăng nhập']);
    exit();
}

$user_id = $_SESSION['user']['id'];
$db = new DbConnect();
$conn = $db->connect();

// Kiểm tra booking thuộc về user và chưa bị hủy
$stmt = $conn->prepare('SELECT * FROM bookings WHERE id = ? AND user_id = ? AND status NOT IN ("cancelled", "completed")');
$stmt->bind_param('ii', $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy booking hợp lệ']);
    exit();
}

// Cập nhật trạng thái
$update = $conn->prepare('UPDATE bookings SET status = "cancelled" WHERE id = ?');
$update->bind_param('i', $booking_id);
if ($update->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Không thể cập nhật trạng thái']);
} 