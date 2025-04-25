<?php
// Start session
session_start();

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

// Include database connection
require_once '../config/db_connection.php';

// Get booking ID
$bookingId = $_GET['id'];
$userId = $_SESSION['user']['id'];

try {
    // Verify that the booking belongs to the current user and is in 'checked_in' status
    $query = "SELECT b.*, r.name as room_name FROM bookings b 
              JOIN rooms r ON b.room_id = r.id 
              WHERE b.id = ? AND b.user_id = ? AND b.status = 'checked_in'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $bookingId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();
    
    if (!$booking) {
        // Booking not found, not owned by user, or not in 'checked_in' status
        $_SESSION['error'] = "Không tìm thấy thông tin đặt phòng đã check-in hoặc bạn không có quyền truy cập";
        header('Location: booking-history.php');
        $stmt->close();
        exit;
    }
    $stmt->close();
    
    // Update booking status to 'completed'
    $updateQuery = "UPDATE bookings SET status = 'completed', checkout_time = NOW() WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("i", $bookingId);
    $updateStmt->execute();
    
    // Check if update was successful
    if ($updateStmt->affected_rows > 0) {
        $_SESSION['success_message'] = "Bạn đã check-out khỏi phòng " . htmlspecialchars($booking['room_name']) . " thành công. Cảm ơn bạn đã sử dụng dịch vụ!";
    } else {
        $_SESSION['error'] = "Không thể check-out. Vui lòng thử lại.";
    }
    $updateStmt->close();
    
} catch (Exception $e) {
    // Log error and display message
    error_log("Database error: " . $e->getMessage());
    $_SESSION['error'] = "Đã xảy ra lỗi khi xử lý check-out. Vui lòng thử lại sau.";
}

// Redirect back to booking history page
header('Location: booking-history.php');
exit;
?> 