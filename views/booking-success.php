<?php
session_start();
require_once '../config/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

// Check if we have booking info in session
$booking_info = $_SESSION['booking_info'] ?? null;
$booking_id = $_SESSION['booking_id'] ?? null;

// If we don't have booking info in session but have a booking ID, fetch it from database
if (!$booking_info && $booking_id) {
    $db = new DbConnect();
    $conn = $db->connect();
    
    $stmt = $conn->prepare("
        SELECT b.*, r.name as room_name, r.building, r.floor, rt.name as room_type, rt.capacity
        FROM bookings b
        JOIN rooms r ON b.room_id = r.id
        JOIN room_types rt ON r.room_type_id = rt.id
        WHERE b.id = ?
    ");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result) {
        $booking_info = [
            'room_id' => $result['room_id'],
            'room_name' => $result['room_name'],
            'building' => $result['building'],
            'floor' => $result['floor'],
            'room_type' => $result['room_type'],
            'capacity' => $result['capacity'],
            'date' => $result['booking_date'],
            'start_time' => $result['start_time'],
            'end_time' => $result['end_time']
        ];
    }
}

// If we still don't have booking info, redirect to booking page
if (!$booking_info) {
    header('Location: booking.php');
    exit();
}

// If we have booking info but no room name, fetch room details
if (!isset($booking_info['room_name']) && isset($booking_info['room_id'])) {
    $db = new DbConnect();
    $conn = $db->connect();
    
    $stmt = $conn->prepare("
        SELECT r.*, rt.name as room_type, rt.capacity
        FROM rooms r
        JOIN room_types rt ON r.room_type_id = rt.id
        WHERE r.id = ?
    ");
    $stmt->bind_param("i", $booking_info['room_id']);
    $stmt->execute();
    $room = $stmt->get_result()->fetch_assoc();
    
    if ($room) {
        $booking_info['room_name'] = $room['name'];
        $booking_info['building'] = $room['building'];
        $booking_info['floor'] = $room['floor'];
    }
}

// Clear the session booking info to prevent showing it again
unset($_SESSION['booking_info']);
unset($_SESSION['booking_id']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt phòng thành công - BKSpace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <?php require '../components/header.php'; ?>

    <div class="container mt-4">
        <div class="success-container">
            <i class="bi bi-check-circle-fill success-icon"></i>
            <h1 class="text-center">Đặt phòng thành công!</h1>
            <p class="text-center mb-4">Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi. Dưới đây là thông tin chi tiết về đặt phòng của bạn.</p>
            
            <div class="booking-details">
                <h3>THÔNG TIN PHÒNG ĐÃ ĐƯỢC PHÂN CÔNG</h3>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Mã phòng:</strong> <?php echo htmlspecialchars($booking_info['room_name'] ?? 'N/A'); ?></p>
                        <p><strong>Tòa nhà:</strong> <?php echo htmlspecialchars($booking_info['building'] ?? 'N/A'); ?></p>
                        <p><strong>Tầng:</strong> <?php echo htmlspecialchars($booking_info['floor'] ?? 'N/A'); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Loại phòng:</strong> <?php echo htmlspecialchars($booking_info['room_type'] ?? 'N/A'); ?></p>
                        <p><strong>Sức chứa:</strong> <?php echo htmlspecialchars($booking_info['capacity'] ?? 'N/A'); ?> người</p>
                    </div>
                </div>
            </div>
            
            <div class="booking-details">
                <h3>THỜI GIAN ĐẶT PHÒNG</h3>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Ngày:</strong> <?php echo date('d/m/Y', strtotime($booking_info['date'])); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Thời gian:</strong> <?php echo date('H:i', strtotime($booking_info['start_time'])); ?> - <?php echo date('H:i', strtotime($booking_info['end_time'])); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="text-center">
                <p>Vui lòng lưu lại thông tin này để sử dụng khi check-in.</p>
                <a href="booking-history.php" class="btn btn-primary btn-action">Xem lịch sử đặt phòng</a>
                <a href="booking.php" class="btn btn-outline-primary btn-action mt-2">Đặt phòng khác</a>
            </div>
        </div>
    </div>

    <?php require '../components/footer.php'; ?>
</body>
</html> 