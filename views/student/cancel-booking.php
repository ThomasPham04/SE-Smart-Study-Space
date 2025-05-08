<?php
session_start();
require_once '../../classes/Student.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

// Get booking ID
$booking_id = $_GET['id'] ?? null;

if (!$booking_id) {
    $_SESSION['error'] = 'Không tìm thấy thông tin đặt phòng';
    header('Location: booking-history.php');
    exit();
}

$user = $_SESSION['user'];
$student = new Student($user['id'], $user['name'], $user['user_type'], $user['username'] ?? null, $user['student_id'] ?? null, $user['phone_number'] ?? null);

// Use Student class to cancel reservation
$success = $student->cancelReservation($booking_id);

if ($success) {
    // Optionally, you can fetch the room name for the success message if needed
    require_once '../../config/db_connection.php';
    $db = new DbConnect();
    $conn = $db->connect();
    $stmt = $conn->prepare("SELECT r.name as room_name FROM bookings b JOIN rooms r ON b.room_id = r.id WHERE b.id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $room_name = $result['room_name'] ?? '';
    $_SESSION['success_message'] = 'Bạn đã hủy đặt phòng ' . $room_name . ' thành công';
} else {
    $_SESSION['error'] = 'Không tìm thấy thông tin đặt phòng hoặc bạn không có quyền hủy, hoặc có lỗi xảy ra khi hủy đặt phòng.';
}

header('Location: booking-history.php');
exit(); 