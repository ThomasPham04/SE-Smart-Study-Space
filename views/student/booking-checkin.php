<?php
session_start();
require_once '../../classes/Student.php';
require_once '../../classes/CheckInOut.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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

// Use Student class to check in (update status if possible)
$success = $student->checkIn($booking_id);

require_once '../../config/db_connection.php';
$db = new DbConnect();
$conn = $db->connect();

$stmt = $conn->prepare("
    SELECT b.*, r.name as room_name, r.building, r.floor, rt.name as room_type_name, rt.capacity
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    JOIN room_types rt ON r.room_type_id = rt.id
    WHERE b.id = ? AND b.user_id = ?
");
$stmt->bind_param("ii", $booking_id, $user['id']);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    $_SESSION['error'] = 'Không tìm thấy thông tin đặt phòng hoặc bạn không có quyền truy cập';
    header('Location: booking-history.php');
    exit();
}

// Generate access code - a random string that will be valid for this booking
$access_code = bin2hex(random_bytes(8)); // 16 character hex string

// Create the QR code data with additional security
$current_time = time();
$qr_data = [
    'room_id' => $booking['room_id'],
    'booking_id' => $booking_id,
    'access_code' => $access_code,
    'timestamp' => $current_time,
    'user_id' => $user['id'],
    'csrf_token' => $_SESSION['csrf_token']
];

// Encrypt the data using a simple encryption (in production, use proper encryption)
$encrypted_data = base64_encode(json_encode($qr_data));

// Save the access code in the database
$update_stmt = $conn->prepare("
    UPDATE bookings 
    SET access_code = ?, 
        access_generated_at = FROM_UNIXTIME(?),
        last_updated = NOW()
    WHERE id = ?
");
$update_stmt->bind_param("sii", $access_code, $current_time, $booking_id);
$update_stmt->execute();

// Calculate expiration time (15 minutes from now)
$expiration_time = $current_time + (15 * 60);
$expiration_formatted = date('H:i', $expiration_time);

// QR code content
$qr_content = urlencode($encrypted_data);

$checkInOut = new CheckInOut($conn, $booking_id);

// If qr_only=1, return only the QR code image for modal
if (isset($_GET['qr_only']) && $_GET['qr_only'] == '1') {
    // Use CheckInOut class to generate QR code image
    $checkInOut->generateQRCode($encrypted_data);
    exit();
}

// If qr_data_only=1, return only the QR data string for client-side QR generation
if (isset($_GET['qr_data_only']) && $_GET['qr_data_only'] == '1') {
    echo $encrypted_data;
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check-in - BKSpace</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <!-- External CSS -->
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>
<body>
    <?php require '../../components/header.php'; ?>

    <div class="container mt-4">
        <div class="checkin-container">
            <h1>Check-in QR Code</h1>
            <p class="text-muted">Quét mã QR này tại đầu đọc cửa phòng để mở khóa và bắt đầu sử dụng phòng.</p>
            
            <div class="room-details card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Phòng:</strong> <?php echo htmlspecialchars($booking['room_name']); ?></p>
                            <p><strong>Tòa nhà:</strong> <?php echo htmlspecialchars($booking['building']); ?></p>
                            <p><strong>Tầng:</strong> <?php echo htmlspecialchars($booking['floor']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Ngày:</strong> <?php echo date('d/m/Y', strtotime($booking['booking_date'])); ?></p>
                            <p><strong>Thời gian:</strong> <?php echo date('H:i', strtotime($booking['start_time'])); ?> - <?php echo date('H:i', strtotime($booking['end_time'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="qr-code-container text-center mb-4">
                <div class="qr-code">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=<?php echo $qr_content; ?>" alt="QR Code" class="img-fluid">
                </div>
                
                <div class="access-code-container mt-3">
                    <p class="mb-2">Mã truy cập:</p>
                    <div class="access-code"><?php echo $access_code; ?></div>
                </div>
                
                <div class="timer mt-3">
                    Mã QR sẽ hết hạn sau: <span id="countdown" class="fw-bold">15:00</span>
                </div>
            </div>
            
            <div class="text-center">
                <p class="text-muted small">Mã QR sẽ tự động cập nhật khi hết hạn. <br>Mỗi mã chỉ có thể sử dụng một lần.</p>
                <a href="booking-history.php" class="btn btn-primary mt-3">Quay lại lịch sử đặt phòng</a>
            </div>
        </div>
    </div>

    <?php require '../../components/footer.php'; ?>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Countdown timer
        let timeLeft = 15 * 60; // 15 minutes in seconds
        const countdownElement = document.getElementById('countdown');
        
        function updateCountdown() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            
            countdownElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeLeft <= 0) {
                // Refresh the page to get a new QR code
                window.location.reload();
            } else {
                timeLeft--;
                setTimeout(updateCountdown, 1000);
            }
        }
        
        // Start the countdown
        updateCountdown();
    </script>
</body>
</html> 