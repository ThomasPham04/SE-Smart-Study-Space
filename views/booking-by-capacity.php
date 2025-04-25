<?php
session_start();
require_once '../config/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    $_SESSION['redirect_after_login'] = 'booking-by-capacity.php?type_id=' . ($_GET['type_id'] ?? '');
    header('Location: login.php');
    exit();
}

// Get database connection and parameters
$db = new DbConnect();
$conn = $db->connect();
$type_id = $_GET['type_id'] ?? '';
$building = $_GET['building'] ?? '';

if (empty($type_id)) {
    header('Location: booking.php');
    exit();
}

// Get room type information
$type_stmt = $conn->prepare("
    SELECT * FROM room_types WHERE id = ?
");
$type_stmt->bind_param("i", $type_id);
$type_stmt->execute();
$room_type = $type_stmt->get_result()->fetch_assoc();

if (!$room_type) {
    $_SESSION['error'] = 'Loại phòng không tồn tại';
    header('Location: booking.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = $_POST['start_date'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';
    
    // Make sure we have a valid user_id
    if (!isset($_SESSION['user']['id'])) {
        $_SESSION['error'] = 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại!';
        header('Location: login.php');
        exit();
    }
    $user_id = $_SESSION['user']['id'];

    // Validate input
    if (empty($start_date) || empty($start_time) || empty($end_time)) {
        $_SESSION['error'] = 'Vui lòng điền đầy đủ thông tin!';
        header('Location: booking-by-capacity.php?type_id=' . $type_id . (!empty($building) ? '&building=' . urlencode($building) : ''));
        exit();
    }

    // Find available room of the selected type
    $query = "
        SELECT r.id 
        FROM rooms r
        LEFT JOIN bookings b ON r.id = b.room_id 
            AND b.booking_date = ? 
            AND (
                (b.start_time <= ? AND b.end_time >= ?) OR
                (b.start_time <= ? AND b.end_time >= ?) OR
                (b.start_time >= ? AND b.end_time <= ?)
            )
        WHERE r.room_type_id = ? 
            AND r.status = 'available'
            " . (!empty($building) ? "AND r.building = ?" : "") . "
            AND b.id IS NULL
        LIMIT 1
    ";
    
    if (!empty($building)) {
        $room_stmt = $conn->prepare($query);
        $room_stmt->bind_param("sssssssss", $start_date, $start_time, $start_time, $end_time, $end_time, $start_time, $end_time, $type_id, $building);
    } else {
        $room_stmt = $conn->prepare($query);
        $room_stmt->bind_param("ssssssss", $start_date, $start_time, $start_time, $end_time, $end_time, $start_time, $end_time, $type_id);
    }
    
    $room_stmt->execute();
    $result = $room_stmt->get_result()->fetch_assoc();
    
    if (!$result) {
        $_SESSION['error'] = 'Không còn phòng trống phù hợp với yêu cầu của bạn trong khoảng thời gian này!';
        header('Location: booking-by-capacity.php?type_id=' . $type_id . (!empty($building) ? '&building=' . urlencode($building) : ''));
        exit();
    }
    
    $room_id = $result['id'];
    
    // Create booking
    $insert_stmt = $conn->prepare("
        INSERT INTO bookings (user_id, room_id, booking_date, start_time, end_time, status) 
        VALUES (?, ?, ?, ?, ?, 'pending')
    ");
    $insert_stmt->bind_param("iisss", $user_id, $room_id, $start_date, $start_time, $end_time);
    
    if ($insert_stmt->execute()) {
        // Store the booking information for the success page
        $_SESSION['booking_info'] = [
            'room_id' => $room_id,
            'room_type' => $room_type['name'],
            'capacity' => $room_type['capacity'],
            'date' => $start_date,
            'start_time' => $start_time,
            'end_time' => $end_time
        ];
        
        header('Location: booking-success.php');
        exit();
    } else {
        $_SESSION['error'] = 'Có lỗi xảy ra khi đặt phòng: ' . $insert_stmt->error;
        header('Location: booking-by-capacity.php?type_id=' . $type_id . (!empty($building) ? '&building=' . urlencode($building) : ''));
        exit();
    }
}

// Get the number of available rooms
$available_stmt = $conn->prepare("
    SELECT COUNT(*) as count 
    FROM rooms 
    WHERE room_type_id = ? 
    AND status = 'available'
    " . (!empty($building) ? "AND building = ?" : "")
);

if (!empty($building)) {
    $available_stmt->bind_param("is", $type_id, $building);
} else {
    $available_stmt->bind_param("i", $type_id);
}

$available_stmt->execute();
$available_result = $available_stmt->get_result()->fetch_assoc();
$available_rooms = $available_result['count'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt phòng - BKSpace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <?php require '../components/header.php'; ?>

    <div class="booking-container">
        <h1 class="page-title">FORM ĐĂNG KÝ ĐẶT CHỖ</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <div class="room-info">
            <h3>Thông tin loại phòng</h3>
            <p><strong>Loại phòng:</strong> <?php echo htmlspecialchars($room_type['name']); ?></p>
            <p><strong>Sức chứa:</strong> <?php echo htmlspecialchars($room_type['capacity']); ?> người</p>
            <p><strong>Phòng còn trống:</strong> <?php echo $available_rooms; ?> phòng</p>
            <?php if (!empty($building)): ?>
                <p><strong>Cơ sở:</strong> <?php echo htmlspecialchars($building); ?></p>
            <?php endif; ?>
            <p><small class="text-muted">Hệ thống sẽ tự động chọn phòng phù hợp cho bạn.</small></p>
        </div>
        
        <form class="booking-form" method="POST">
            <div class="form-group">
                <label for="start_date">NGÀY SỬ DỤNG</label>
                <input 
                    type="date" 
                    id="start_date" 
                    name="start_date" 
                    required
                    min="<?php echo date('Y-m-d'); ?>"
                    class="form-control"
                >
                <span class="hint">Ví dụ: 01/01/2024</span>
            </div>

            <div class="form-group">
                <label for="start_time">THỜI GIAN BẮT ĐẦU</label>
                <input 
                    type="time" 
                    id="start_time" 
                    name="start_time" 
                    required
                    class="form-control"
                >
                <span class="hint">Ví dụ: 7:00</span>
            </div>

            <div class="form-group">
                <label for="end_time">THỜI GIAN KẾT THÚC</label>
                <input 
                    type="time" 
                    id="end_time" 
                    name="end_time" 
                    required
                    class="form-control"
                >
                <span class="hint">Ví dụ: 14:00</span>
            </div>

            <div class="button-group">
                <button type="submit" class="btn btn-confirm">XÁC NHẬN</button>
                <button type="button" class="btn btn-back" onclick="history.back()">TRỞ LẠI</button>
            </div>
        </form>
    </div>

    <?php require '../components/footer.php'; ?>

    <script>
        // Form validation
        document.querySelector('.booking-form').addEventListener('submit', function(e) {
            const startDate = document.getElementById('start_date').value;
            const startTime = document.getElementById('start_time').value;
            const endTime = document.getElementById('end_time').value;
            
            // Convert to Date objects for comparison
            const startDateTime = new Date(startDate + 'T' + startTime);
            const endDateTime = new Date(startDate + 'T' + endTime);
            
            if (startDateTime >= endDateTime) {
                e.preventDefault();
                alert('Thời gian kết thúc phải sau thời gian bắt đầu!');
            }
        });
    </script>
</body>
</html> 