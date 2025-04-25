<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BKSpace - Booking</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <!-- External CSS -->
    <link rel="stylesheet" href="../assets/css/styles.css">

</head>
<body>
    <?php 
    session_start();
    date_default_timezone_set('Asia/Ho_Chi_Minh'); // Set timezone to Vietnam time
    require '../components/header.php';
    require_once '../config/db_connection.php';

    // Check if user is logged in
    if (!isset($_SESSION['user'])) {
        $_SESSION['redirect_after_login'] = 'booking-confirm.php?room_id=' . ($_GET['room_id'] ?? '');
        header('Location: login.php');
        exit();
    }

    // Get room information
    $db = new DbConnect();
    $conn = $db->connect();
    $room_id = $_GET['room_id'] ?? '';
    
    if (empty($room_id)) {
        header('Location: booking.php');
        exit();
    }

    $stmt = $conn->prepare("
        SELECT r.*, rt.name as room_type_name, rt.capacity 
        FROM rooms r
        JOIN room_types rt ON r.room_type_id = rt.id
        WHERE r.id = ?
    ");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $room = $stmt->get_result()->fetch_assoc();

    if (!$room) {
        $_SESSION['error'] = 'Phòng không tồn tại';
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
            header('Location: booking-confirm.php?room_id=' . $room_id);
            exit();
        }

        // Combine date and time for database
        $start_datetime_str = $start_date . ' ' . $start_time;
        $end_datetime_str = $start_date . ' ' . $end_time;
        
        // --- Server-side Time Validation ---
        $current_timestamp = time(); // Current time based on Asia/Ho_Chi_Minh timezone
        $start_timestamp = strtotime($start_datetime_str);
        $end_timestamp = strtotime($end_datetime_str);

        if ($start_timestamp === false || $end_timestamp === false) {
             $_SESSION['error'] = 'Định dạng ngày giờ không hợp lệ!';
             header('Location: booking-confirm.php?room_id=' . $room_id);
             exit();
        }
        
        // Check if start time is in the past
        if ($start_timestamp < $current_timestamp) {
            $_SESSION['error'] = 'Không thể đặt phòng trong quá khứ!';
            header('Location: booking-confirm.php?room_id=' . $room_id);
            exit();
        }
        
        // Check if end time is before start time
        if ($end_timestamp <= $start_timestamp) {
            $_SESSION['error'] = 'Thời gian kết thúc phải sau thời gian bắt đầu!';
            header('Location: booking-confirm.php?room_id=' . $room_id);
            exit();
        }
        // --- End Server-side Time Validation ---

        // Check if room is available
        $check_stmt = $conn->prepare("
            SELECT COUNT(*) as count FROM bookings 
            WHERE room_id = ? 
            AND booking_date = ?
            AND (
                (start_time <= ? AND end_time >= ?) OR
                (start_time <= ? AND end_time >= ?) OR
                (start_time >= ? AND end_time <= ?)
            )
        ");
        $check_stmt->bind_param("isssssss", $room_id, $start_date, $start_time, $start_time, $end_time, $end_time, $start_time, $end_time);
        $check_stmt->execute();
        $result = $check_stmt->get_result()->fetch_assoc();

        if ($result['count'] > 0) {
            $_SESSION['error'] = 'Phòng đã được đặt trong khoảng thời gian này!';
            header('Location: booking-confirm.php?room_id=' . $room_id);
            exit();
        }

        // Create booking
        $insert_stmt = $conn->prepare("
            INSERT INTO bookings (user_id, room_id, booking_date, start_time, end_time, status) 
            VALUES (?, ?, ?, ?, ?, 'pending')
        ");
        $insert_stmt->bind_param("iisss", $user_id, $room_id, $start_date, $start_time, $end_time);
        
        if ($insert_stmt->execute()) {
            $_SESSION['success'] = 'Đặt phòng thành công!';
            header('Location: booking-success.php');
            exit();
        } else {
            $_SESSION['error'] = 'Có lỗi xảy ra khi đặt phòng: ' . $insert_stmt->error;
            header('Location: booking-confirm.php?room_id=' . $room_id);
            exit();
        }
    }
    ?>

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
            <h3>Thông tin phòng</h3>
            <p><strong>Tên phòng:</strong> <?php echo htmlspecialchars($room['name']); ?></p>
            <p><strong>Loại phòng:</strong> <?php echo htmlspecialchars($room['room_type_name']); ?></p>
            <p><strong>Sức chứa:</strong> <?php echo htmlspecialchars($room['capacity']); ?> người</p>
            <p><strong>Tòa nhà:</strong> <?php echo htmlspecialchars($room['building']); ?></p>
            <p><strong>Tầng:</strong> <?php echo htmlspecialchars($room['floor']); ?></p>
        </div>
        
        <form class="booking-form" action="booking-confirm.php?room_id=<?php echo $room_id; ?>" method="POST">
            <input type="hidden" name="room_id" value="<?php echo $room_id; ?>">
            
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
        // Enhanced date/time validation
        document.addEventListener('DOMContentLoaded', function() {
            const startDateInput = document.getElementById('start_date');
            const startTimeInput = document.getElementById('start_time');
            const endTimeInput = document.getElementById('end_time');
            
            // Get current date and time in Vietnam timezone
            const now = new Date();
            const currentDate = now.toISOString().split('T')[0]; // YYYY-MM-DD format
            const currentHour = now.getHours();
            const currentMinute = now.getMinutes();
            
            // Format current time as HH:MM
            const currentTimeFormatted = 
                `${currentHour.toString().padStart(2, '0')}:${currentMinute.toString().padStart(2, '0')}`;
            
            // Update min time when date changes
            function updateMinTime() {
                // If selected date is today, set min time to current time
                if (startDateInput.value === currentDate) {
                    startTimeInput.min = currentTimeFormatted;
                    
                    // If current time is already set and it's before current time, reset it
                    if (startTimeInput.value && startTimeInput.value < currentTimeFormatted) {
                        startTimeInput.value = currentTimeFormatted;
                    }
                } else {
                    // For future dates, any time is valid
                    startTimeInput.min = "";
                }
            }
            
            // Initial setup
            updateMinTime();
            
            // Update min time whenever date changes
            startDateInput.addEventListener('change', updateMinTime);
            
            // Form validation
            document.querySelector('.booking-form').addEventListener('submit', function(e) {
                const startDate = startDateInput.value;
                const startTime = startTimeInput.value;
                const endTime = endTimeInput.value;
                
                // Check if time fields are filled
                if (!startTime || !endTime) {
                    e.preventDefault();
                    alert('Vui lòng chọn thời gian bắt đầu và kết thúc!');
                    return;
                }
                
                // Convert to Date objects for comparison
                const startDateTime = new Date(startDate + 'T' + startTime);
                const endDateTime = new Date(startDate + 'T' + endTime);
                const currentDateTime = new Date();
                
                // Check if booking start time is in the past
                if (startDateTime <= currentDateTime) {
                    e.preventDefault();
                    alert('Không thể đặt phòng trong quá khứ!');
                    return;
                }
                
                // Check if end time is after start time
                if (startDateTime >= endDateTime) {
                    e.preventDefault();
                    alert('Thời gian kết thúc phải sau thời gian bắt đầu!');
                    return;
                }
            });
        });
    </script>
</body>
</html> 