<?php
session_start();
require_once '../../config/db_connection.php';
require_once '../../classes/Admin.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Instantiate Admin class from session
$user = $_SESSION['user'];
$admin = new Admin($user['id'], $user['name'], $user['user_type'], $user['username'] ?? null, $user['id']);
$adminProfile = $admin->getProfile();
$adminName = $adminProfile['fullName'];

// Get all rooms with their details
$db = new DbConnect();
$conn = $db->connect();

// Get statistics
$stats = [];

// Total users
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE user_type != 'admin'");
$stmt->execute();
$stats['total_users'] = $stmt->get_result()->fetch_assoc()['total'];

// Total rooms
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM rooms");
$stmt->execute();
$stats['total_rooms'] = $stmt->get_result()->fetch_assoc()['total'];

// Today's bookings
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM bookings WHERE DATE(start_time) = CURDATE()");
$stmt->execute();
$stats['today_bookings'] = $stmt->get_result()->fetch_assoc()['total'];

// Thống kê tổng số lượt đặt phòng mỗi ngày trong 7 ngày qua
$stmt = $conn->prepare("
    SELECT 
        DATE(start_time) as date,
        COUNT(*) as count
    FROM bookings
    WHERE start_time >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(start_time)
    ORDER BY date ASC
");
$stmt->execute();
$booking_stats = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Most popular rooms
$stmt = $conn->prepare("
    SELECT 
        r.name,
        COUNT(*) as booking_count
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    WHERE b.start_time >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY r.id
    ORDER BY booking_count DESC
    LIMIT 5
");
$stmt->execute();
$popular_rooms = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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
    <link href="../../assets/css/styles.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include '../../components/header.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3">
                <?php include '../../components/admin_menu.php'; ?>
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
                                        <h2 class="card-text fw-bold"><?php echo $stats['total_users']; ?></h2>
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
                                        <h2 class="card-text fw-bold"><?php echo $stats['today_bookings']; ?></h2>
                                        <a href="manage_bookings.php" class="btn btn-light btn-sm mt-2 fw-bold">
                                            <i class="bi bi-calendar-check"></i> Xem đặt phòng
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="card bg-info text-white">
                                    <div class="card-body d-flex flex-column align-items-center">
                                        <h5 class="card-title fw-bold">Tổng số phòng</h5>
                                        <h2 class="card-text fw-bold"><?php echo $stats['total_rooms']; ?></h2>
                                        <a href="manage_rooms.php" class="btn btn-light btn-sm mt-2 fw-bold">
                                            <i class="bi bi-building"></i> Quản lý phòng
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Actions
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5 class="fw-bold">Thao tác nhanh</h5>
                                <div class="d-flex gap-2 align-items-center justify-content-center">
                                    <a href="manage_users.php" class="btn btn-primary">
                                        <i class="bi bi-person-plus"></i> Thêm người dùng mới
                                    </a>
                                    <a href="manage_bookings.php" class="btn btn-success">
                                        <i class="bi bi-calendar-plus"></i> Quản lý phòng
                                    </a>
                                    <a href="manage_rooms.php" class="btn btn-info text-white">
                                        <i class="bi bi-building-add"></i> Thêm phòng mới
                                    </a>
                                </div>
                            </div>
                        </div> -->

                        <!-- Statistics Section -->
                        <div class="row mt-4">
                            <!-- Booking Trends Chart -->
                            <div class="col-md-8 mb-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Thống kê đặt phòng 7 ngày qua</h5>
                                        <canvas id="bookingTrendsChart"></canvas>
                                    </div>
                                </div>
                            </div>

                            <!-- Popular Rooms -->
                            <div class="col-md-4 mb-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Phòng được đặt nhiều nhất</h5>
                                        <div class="list-group">
                                            <?php foreach ($popular_rooms as $room): ?>
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <?php echo htmlspecialchars($room['name']); ?>
                                                <span class="badge bg-primary rounded-pill">
                                                    <?php echo $room['booking_count']; ?> lượt
                                                </span>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const bookingData = <?php echo json_encode($booking_stats); ?>;
        // Lấy danh sách ngày (7 ngày gần nhất)
        const today = new Date();
        const dates = [];
        for (let i = 6; i >= 0; i--) {
            const d = new Date(today);
            d.setDate(today.getDate() - i);
            dates.push(d.toISOString().slice(0, 10));
        }
        // Tạo mảng số lượt đặt cho từng ngày
        const dataCounts = dates.map(date => {
            const entry = bookingData.find(item => item.date === date);
            return entry ? entry.count : 0;
        });
        new Chart(document.getElementById('bookingTrendsChart'), {
            type: 'line',
            data: {
                labels: dates.map(date => {
                    const d = new Date(date);
                    return d.getDate() + '/' + (d.getMonth() + 1);
                }),
                datasets: [{
                    label: 'Số lượt đặt phòng',
                    data: dataCounts,
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0,123,255,0.1)',
                    fill: true,
                    tension: 0.2
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
</body>
</html> 