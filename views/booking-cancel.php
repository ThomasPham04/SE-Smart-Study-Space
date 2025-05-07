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
require_once '../classes/Student.php';

// Get booking ID
$bookingId = $_GET['id'];
$user = $_SESSION['user'];
$student = new Student($user['id'], $user['name'], $user['user_type'], $user['username'] ?? null, $user['student_id'] ?? null, $user['phone_number'] ?? null);

// Establish database connection *before* any conditional logic
$db = new DbConnect();
$conn = $db->connect();

// Check if connection was successful
if (!$conn) {
    // Handle connection error appropriately, maybe redirect with an error message
    $_SESSION['error'] = "Lỗi kết nối cơ sở dữ liệu.";
    // You might want to log this error as well
    error_log("Database connection failed in booking-cancel.php"); 
    header('Location: booking-history.php');
    exit;
}

// If we're already processing a form submission for cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_cancel'])) {
    try {
        // Fetch booking info for time check
        $stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ? AND (status = 'confirmed' OR status = 'pending')");
        $stmt->bind_param("ii", $bookingId, $user['id']);
        $stmt->execute();
        $booking = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$booking) {
            // Booking not found, not owned by user, or not in a cancellable status
            $_SESSION['error'] = "Không tìm thấy thông tin đặt phòng hoặc phòng không ở trạng thái có thể hủy"; // Updated error message
            header('Location: booking-history.php');
            exit;
        }
        
        // Additional check: Don't allow cancellations too close to the booking time
        $bookingStartTime = strtotime($booking['booking_date'] . ' ' . $booking['start_time']);
        $currentTime = time();
        $timeUntilBooking = $bookingStartTime - $currentTime;
        
        // If booking is less than 1 hour away, don't allow cancellation
        if ($timeUntilBooking < 3600 && $timeUntilBooking > 0) {
            $_SESSION['error'] = "Không thể hủy đặt phòng trong vòng 1 giờ trước thời gian bắt đầu";
            header('Location: booking-history.php');
            exit;
        }
        
        // Use Student class to cancel reservation
        $success = $student->cancelReservation($bookingId);
        
        if ($success) {
            $_SESSION['success_message'] = "Bạn đã hủy đặt phòng " . htmlspecialchars($booking['room_name']) . " thành công";
        } else {
            $_SESSION['error'] = "Không thể hủy đặt phòng. Vui lòng thử lại.";
        }
        
        // Redirect back to booking history page after cancellation
        header('Location: booking-history.php');
        exit;
        
    } catch (Exception $e) { // Catch generic Exception for mysqli
        // Log error and display message
        error_log("Database error: " . $e->getMessage());
        $_SESSION['error'] = "Đã xảy ra lỗi khi xử lý hủy phòng. Vui lòng thử lại sau.";
        header('Location: booking-history.php');
        exit;
    }
}

// If we're just displaying the cancellation form, get the booking details
try {
    // Get the booking details for display - Allow fetching pending or confirmed
    $stmt = $conn->prepare("SELECT b.*, r.name as room_name, r.building, r.floor FROM bookings b JOIN rooms r ON b.room_id = r.id WHERE b.id = ? AND b.user_id = ? AND (b.status = 'confirmed' OR b.status = 'pending')");
    $stmt->bind_param("ii", $bookingId, $user['id']);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$booking) {
        // Booking not found, not owned by user, or not in a cancellable status
        $_SESSION['error'] = "Không tìm thấy thông tin đặt phòng hoặc phòng không ở trạng thái có thể hủy"; // Updated error message
        header('Location: booking-history.php');
        exit;
    }
    
} catch (Exception $e) { // Catch generic Exception for mysqli
    // Log error and display message
    error_log("Database error: " . $e->getMessage());
    $_SESSION['error'] = "Đã xảy ra lỗi khi truy vấn thông tin đặt phòng. Vui lòng thử lại sau.";
    header('Location: booking-history.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hủy đặt phòng - BKSpace</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <!-- External CSS -->
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <?php require '../components/header.php'; ?>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-danger text-white">
                        <h3 class="mb-0">Xác nhận hủy đặt phòng</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>

                        <h4>Thông tin đặt phòng</h4>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <p><strong>Phòng:</strong> <?php echo htmlspecialchars($booking['room_name']); ?></p>
                                <p><strong>Ngày:</strong> <?php echo date('d/m/Y', strtotime($booking['booking_date'])); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Thời gian:</strong> <?php echo date('H:i', strtotime($booking['start_time'])); ?> - <?php echo date('H:i', strtotime($booking['end_time'])); ?></p>
                                <p><strong>Trạng thái:</strong> <?php echo ($booking['status'] === 'pending') ? 'Chưa tới ngày' : 'Đã xác nhận'; ?></p> // Show correct status
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            Bạn có chắc chắn muốn hủy đặt phòng này không? Hành động này không thể hoàn tác.
                        </div>

                        <form method="post" class="mt-4">
                            <div class="d-flex justify-content-between">
                                <a href="booking-history.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Quay lại
                                </a>
                                <button type="submit" name="confirm_cancel" class="btn btn-danger">
                                    <i class="bi bi-x-circle me-2"></i>Xác nhận hủy đặt phòng
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require '../components/footer.php'; ?>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 