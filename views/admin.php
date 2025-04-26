<?php
session_start();
require_once '../config/db_connection.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Get admin name from session
$adminName = $_SESSION['user']['name'];

// Get all rooms with their details
$db = new DbConnect();
$conn = $db->connect();

// Get recent bookings
$stmt = $conn->prepare("
    SELECT b.id, 
           u.name as user_name,
           r.name as room_name,
           b.booking_date,
           b.start_time,
           b.end_time,
           b.status
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN rooms r ON b.room_id = r.id
    ORDER BY b.booking_date DESC, b.start_time DESC
    LIMIT 10
");
$stmt->execute();
$recent_bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get room types for the add room form
// ... existing code ...
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - BKSpace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/styles.css" rel="stylesheet">
</head>
<body>
    <?php include '../components/header.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3">
                <?php include '../components/admin_menu.php'; ?>
            </div>
            <div class="col-md-9">
                <!-- Admin Content -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Chào mừng, <?php echo htmlspecialchars($adminName); ?>!</h4>
                        
                        <!-- Statistics Cards -->
                        <div class="row mt-4">
                            <div class="col-md-4 mb-4">
                                <div class="card bg-primary text-white">
                                    <div class="card-body d-flex flex-column align-items-center">
                                        <h5 class="card-title fw-bold">Tổng số người dùng</h5>
                                        <h2 class="card-text fw-bold">0</h2>
                                        <a href="manage_users.php" class="btn btn-light btn-sm mt-2 fw-bold">
                                            <i class="bi bi-people"></i> Quản lý người dùng
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="card bg-success text-white">
                                    <div class="card-body d-flex flex-column align-items-center">
                                        <h5 class="card-title fw-bold">Đặt phòng hôm nay</h5>
                                        <h2 class="card-text fw-bold">0</h2>
                                        <a href="#" class="btn btn-light btn-sm mt-2 fw-bold">
                                            <i class="bi bi-calendar-check"></i> Xem đặt phòng
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="card bg-info text-white">
                                    <div class="card-body d-flex flex-column align-items-center">
                                        <h5 class="card-title fw-bold">Tổng số phòng</h5>
                                        <h2 class="card-text fw-bold">0</h2>
                                        <a href="manage_rooms.php" class="btn btn-light btn-sm mt-2 fw-bold">
                                            <i class="bi bi-building"></i> Quản lý phòng
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5 class="fw-bold">Thao tác nhanh</h5>
                                <div class="d-flex gap-2 align-items-center justify-content-center">
                                    <a href="manage_users.php" class="btn btn-primary">
                                        <i class="bi bi-person-plus"></i> Thêm người dùng mới
                                    </a>
                                    <a href="#" class="btn btn-success">
                                        <i class="bi bi-calendar-plus"></i> Tạo đặt phòng mới
                                    </a>
                                    <a href="manage_rooms.php" class="btn btn-info text-white">
                                        <i class="bi bi-building-add"></i> Thêm phòng mới
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Recent Bookings Table -->
                        <div class="mt-4">
                            <h5>Đặt phòng gần đây</h5>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Người dùng</th>
                                            <th>Phòng</th>
                                            <th>Ngày</th>
                                            <th>Thời gian</th>
                                            <th>Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_bookings as $booking): 
                                            $status_class = match($booking['status']) {
                                                'pending' => 'text-warning',
                                                'approved' => 'text-success',
                                                'rejected' => 'text-danger',
                                                'completed' => 'text-info',
                                                default => ''
                                            };
                                            
                                            $status_text = match($booking['status']) {
                                                'pending' => 'Chờ duyệt',
                                                'approved' => 'Đã duyệt',
                                                'rejected' => 'Từ chối',
                                                'completed' => 'Hoàn thành',
                                                default => $booking['status']
                                            };
                                        ?>
                                        <tr>
                                            <td><?php echo $booking['id']; ?></td>
                                            <td><?php echo htmlspecialchars($booking['user_name']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['room_name']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($booking['booking_date'])); ?></td>
                                            <td><?php echo date('H:i', strtotime($booking['start_time'])) . ' - ' . date('H:i', strtotime($booking['end_time'])); ?></td>
                                            <td class="<?php echo $status_class; ?>"><?php echo $status_text; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($recent_bookings)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center">Không có đặt phòng nào gần đây</td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 