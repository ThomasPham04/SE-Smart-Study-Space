<?php
session_start();
require_once '../../classes/Student.php';
require_once '../../config/db_connection.php';

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
    $user = $_SESSION['user'];
    $student = new Student($conn, $user['id'], $user['name'], $user['user_type'], $user['username'] ?? null, $user['student_id'] ?? null, $user['phone_number'] ?? null);

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
    
    // Use Student class to make reservation
    $success = $student->makeReservation($room_id, $start_date, $start_time, $end_time);
    if ($success) {
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
        $_SESSION['error'] = 'Có lỗi xảy ra khi đặt phòng hoặc phòng đã được đặt trong khoảng thời gian này!';
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
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <style>
        .booking-container {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.08);
            padding: 2.5rem 2rem;
            margin-bottom: 2rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>
<body>
    <?php require '../../components/header.php'; ?>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="booking-container">
                    <h1 class="page-title">FORM ĐĂNG KÝ ĐẶT CHỖ</h1>
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
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
                                min="07:00"
                                max="17:00"
                                class="form-control"
                            >
                            <span class="hint">Ví dụ: 7:00 (Từ 7:00 sáng đến 17:00)</span>
                        </div>
                        <div class="form-group">
                            <label for="end_time">THỜI GIAN KẾT THÚC</label>
                            <input 
                                type="time" 
                                id="end_time" 
                                name="end_time" 
                                required
                                min="07:00"
                                max="17:00"
                                class="form-control"
                            >
                            <span class="hint">Ví dụ: 14:00 (Từ 7:00 sáng đến 17:00)</span>
                        </div>
                        <div class="button-group">
                            <button type="submit" class="btn btn-confirm">XÁC NHẬN</button>
                            <button type="button" class="btn btn-back" onclick="history.back()">TRỞ LẠI</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php require '../../components/footer.php'; ?>

    <script>
        // Form validation
        document.querySelector('.booking-form').addEventListener('submit', function(e) {
            const startDate = document.getElementById('start_date').value;
            const startTime = document.getElementById('start_time').value;
            const endTime = document.getElementById('end_time').value;
            // Check if time is within allowed range (7:00 - 17:00)
            const startTimeValue = startTime.split(':').map(Number);
            const endTimeValue = endTime.split(':').map(Number);
            const startMinutes = startTimeValue[0] * 60 + startTimeValue[1];
            const endMinutes = endTimeValue[0] * 60 + endTimeValue[1];
            if (startMinutes < 420 || startMinutes >= 1020 || endMinutes < 420 || endMinutes > 1020) {
                e.preventDefault();
                alert('Thời gian đặt phòng phải từ 7:00 sáng đến 17:00!');
                return;
            }
            // Convert to Date objects for comparison
            const startDateTime = new Date(startDate + 'T' + startTime);
            const endDateTime = new Date(startDate + 'T' + endTime);
            const now = new Date();
            // Prevent end time before or equal to start time
            if (startDateTime >= endDateTime) {
                e.preventDefault();
                alert('Thời gian kết thúc phải sau thời gian bắt đầu!');
                return;
            }
            // Prevent booking a time in the past for today
            const today = now.toISOString().slice(0, 10);
            if (startDate === today && startDateTime <= now) {
                e.preventDefault();
                alert('Không thể đặt phòng cho thời gian đã qua trong ngày hôm nay!');
                return;
            }
        });
        // Optionally, update min attribute for time inputs when date changes
        document.getElementById('start_date').addEventListener('change', function() {
            const selectedDate = this.value;
            const now = new Date();
            const today = now.toISOString().slice(0, 10);
            const startTimeInput = document.getElementById('start_time');
            if (selectedDate === today) {
                // Set min time to now (rounded to next minute) or 7:00, whichever is later
                let minHour = Math.max(7, now.getHours());
                let minMinute = now.getMinutes() + 1;
                if (minMinute >= 60) {
                    minHour++;
                    minMinute = 0;
                }
                if (minHour >= 17) {
                    alert('Không thể đặt phòng cho ngày hôm nay vì đã quá giờ làm việc!');
                    this.value = '';
                    return;
                }
                const minTime = `${minHour.toString().padStart(2, '0')}:${minMinute.toString().padStart(2, '0')}`;
                startTimeInput.min = minTime;
            } else {
                startTimeInput.min = '07:00';
            }
        });
    </script>
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 