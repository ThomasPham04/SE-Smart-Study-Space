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
    // Verify that the booking belongs to the current user and is in 'confirmed' status
    $query = "SELECT b.*, r.name as room_name FROM bookings b 
              JOIN rooms r ON b.room_id = r.id 
              WHERE b.id = ? AND b.user_id = ? AND b.status = 'confirmed'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $bookingId, $userId); // Use mysqli bind_param
    $stmt->execute();
    $result = $stmt->get_result(); // Get mysqli result
    $booking = $result->fetch_assoc(); // Fetch using mysqli
    
    if (!$booking) {
        // Booking not found, not owned by user, or not in 'confirmed' status
        $_SESSION['error'] = "Không tìm thấy thông tin đặt phòng hoặc phòng không ở trạng thái 'Đã xác nhận'";
        header('Location: booking-history.php');
        $stmt->close(); // Close statement
        exit;
    }
    $stmt->close(); // Close statement
    
    // Additional check: Only allow check-in within a reasonable time window
    // For example, 15 minutes before the booking start time
    $bookingStartTime = strtotime($booking['booking_date'] . ' ' . $booking['start_time']);
    $currentTime = time();
    $timeUntilBooking = $bookingStartTime - $currentTime;
    
    // If booking is more than 15 minutes away, don't allow check-in yet
    if ($timeUntilBooking > 900) { // 900 seconds = 15 minutes
        $_SESSION['error'] = "Check-in chỉ khả dụng trước 15 phút giờ đặt phòng của bạn.";
        header('Location: booking-history.php');
        exit;
    }
    
    // If booking is already past end time, don't allow check-in
    $bookingEndTime = strtotime($booking['booking_date'] . ' ' . $booking['end_time']);
    if ($currentTime > $bookingEndTime) {
        $_SESSION['error'] = "Đặt phòng này đã hết hạn và không thể check-in.";
        header('Location: booking-history.php');
        exit;
    }
    
    // Update booking status to 'checked_in'
    $updateQuery = "UPDATE bookings SET status = 'checked_in', checkin_time = NOW() WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("i", $bookingId); // Use mysqli bind_param
    $updateStmt->execute();
    
    // Check if update was successful
    if ($updateStmt->affected_rows > 0) { // Use mysqli affected_rows
        $_SESSION['success_message'] = "Bạn đã check-in vào phòng " . htmlspecialchars($booking['room_name']) . " thành công. Chúc bạn học tập hiệu quả!";
    } else {
        $_SESSION['error'] = "Không thể check-in. Vui lòng thử lại.";
    }
    $updateStmt->close(); // Close statement
    
} catch (Exception $e) { // Catch generic Exception for mysqli
    // Log error and display message
    error_log("Database error: " . $e->getMessage());
    $_SESSION['error'] = "Đã xảy ra lỗi khi xử lý check-in. Vui lòng thử lại sau.";
}

// Redirect back to booking history page
header('Location: booking-history.php');
exit;
?> 