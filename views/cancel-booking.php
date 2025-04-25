<?php
session_start();
require_once '../config/db_connection.php';

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

// Get booking information
$db = new DbConnect();
$conn = $db->connect();

// Verify the booking belongs to the user and is in 'pending' status
$check_stmt = $conn->prepare("
    SELECT b.*, r.name as room_name
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    WHERE b.id = ? AND b.user_id = ? AND b.status = 'pending'
");
$user_id = $_SESSION['user']['id'];
$check_stmt->bind_param("ii", $booking_id, $user_id);
$check_stmt->execute();
$booking = $check_stmt->get_result()->fetch_assoc();

if (!$booking) {
    $_SESSION['error'] = 'Không tìm thấy thông tin đặt phòng hoặc bạn không có quyền hủy';
    header('Location: booking-history.php');
    exit();
}

// Cancel the booking
$update_stmt = $conn->prepare("
    UPDATE bookings 
    SET status = 'cancelled',
        cancelled_at = NOW()
    WHERE id = ?
");
$update_stmt->bind_param("i", $booking_id);

if ($update_stmt->execute()) {
    $_SESSION['success_message'] = 'Bạn đã hủy đặt phòng ' . $booking['room_name'] . ' thành công';
} else {
    $_SESSION['error'] = 'Có lỗi xảy ra khi hủy đặt phòng: ' . $update_stmt->error;
}

header('Location: booking-history.php');
exit(); 