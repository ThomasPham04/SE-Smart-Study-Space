<?php
// Start session
session_start();
require_once '../classes/Student.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    // Redirect to login page if not logged in
    header('Location: login.php');
    exit;
}

// Check if booking ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // No booking ID provided
    $_SESSION['error'] = "Không tìm thấy thông tin đặt phòng";
    header('Location: booking-history.php');
    exit;
}

$bookingId = $_GET['id'];
$user = $_SESSION['user'];
$student = new Student($user['id'], $user['name'], $user['user_type'], $user['username'] ?? null, $user['student_id'] ?? null, $user['phone_number'] ?? null);

// Use Student class to check out
$success = $student->checkOut($bookingId);

if ($success) {
    // Optionally, you can fetch the room name for the success message if needed
    require_once '../config/db_connection.php';
    $db = new DbConnect();
    $conn = $db->connect();
    $stmt = $conn->prepare("SELECT r.name as room_name FROM bookings b JOIN rooms r ON b.room_id = r.id WHERE b.id = ?");
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $room_name = $result['room_name'] ?? '';
    $_SESSION['success_message'] = "Bạn đã check-out khỏi phòng " . htmlspecialchars($room_name) . " thành công. Cảm ơn bạn đã sử dụng dịch vụ!";
} else {
    $_SESSION['error'] = "Không thể check-out. Vui lòng thử lại hoặc bạn không có quyền check-out.";
}

// Redirect back to booking history page
header('Location: booking-history.php');
exit;
?> 