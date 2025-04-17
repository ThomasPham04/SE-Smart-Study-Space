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

    $stmt = $conn->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $room = $stmt->get_result()->fetch_assoc();

    if (!$room) {
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
        $start_datetime = $start_date . ' ' . $start_time;
        $end_datetime = $start_date . ' ' . $end_time;

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
            <p><strong>Loại phòng:</strong> 
                <?php 
                echo match($room['room_type']) {
                    'single' => 'Phòng học 1 người',
                    'group_2' => 'Phòng học nhóm 2',
                    'group_3' => 'Phòng học nhóm 3',
                    'group_4' => 'Phòng học nhóm 4',
                    'group_5' => 'Phòng học nhóm 5',
                    'group_6' => 'Phòng học nhóm 6',
                    default => 'Phòng học'
                };
                ?>
            </p>
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