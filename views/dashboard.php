<?php
session_start();
require_once '../config/database.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$db = Database::getInstance();
$user = $_SESSION['user'];

// Get user's recent bookings
$recentBookings = $db->query(
    "SELECT b.*, r.name as room_name, c.name as campus_name 
     FROM bookings b 
     JOIN rooms r ON b.room_id = r.id 
     JOIN campuses c ON r.campus_id = c.id 
     WHERE b.user_id = ? 
     ORDER BY b.start_time DESC 
     LIMIT 5",
    [$user['id']]
)->fetchAll();

// Get upcoming bookings
$upcomingBookings = $db->query(
    "SELECT b.*, r.name as room_name, c.name as campus_name 
     FROM bookings b 
     JOIN rooms r ON b.room_id = r.id 
     JOIN campuses c ON r.campus_id = c.id 
     WHERE b.user_id = ? AND b.start_time > NOW() 
     ORDER BY b.start_time ASC 
     LIMIT 3",
    [$user['id']]
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BKSpace - Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- External CSS -->
    <link href="/assets/css/styles.css" rel="stylesheet">
</head>
<body>
    <?php include '../components/header.php'; ?>

    <!-- Hero Section -->
    <div class="hero">
        <img src="/assets/img/main_page_bg.png" alt="HCMUT Building">
        <div class="hero-text">
            CHÀO MỪNG BẠN ĐẾN VỚI<br>BKSPACE!
            <p class="welcome-text">Xin chào, <?php echo htmlspecialchars($user['name']); ?>!</p>
        </div>
    </div>
    
    <!-- Quick Actions Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-4">Thao tác nhanh</h2>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body text-center p-4">
                            <i class="bi bi-calendar-plus display-4 text-primary mb-3"></i>
                            <h3 class="h5">Đặt phòng mới</h3>
                            <p class="text-muted">Đặt phòng học ngay bây giờ</p>
                            <a href="booking.php" class="btn btn-primary">Đặt phòng</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body text-center p-4">
                            <i class="bi bi-clock-history display-4 text-primary mb-3"></i>
                            <h3 class="h5">Lịch sử đặt phòng</h3>
                            <p class="text-muted">Xem lại các lần đặt phòng trước đây</p>
                            <a href="booking_history.php" class="btn btn-primary">Xem lịch sử</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Upcoming Bookings Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-4">Đặt phòng sắp tới</h2>
            <?php if (empty($upcomingBookings)): ?>
                <div class="alert alert-info text-center">
                    Bạn chưa có đặt phòng nào sắp tới.
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($upcomingBookings as $booking): ?>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($booking['room_name']); ?></h5>
                                    <p class="card-text">
                                        <strong>Cơ sở:</strong> <?php echo htmlspecialchars($booking['campus_name']); ?><br>
                                        <strong>Thời gian:</strong> <?php echo date('d/m/Y H:i', strtotime($booking['start_time'])); ?><br>
                                        <strong>Trạng thái:</strong> 
                                        <span class="badge bg-<?php echo $booking['status'] === 'confirmed' ? 'success' : 'warning'; ?>">
                                            <?php echo $booking['status'] === 'confirmed' ? 'Đã xác nhận' : 'Đang chờ'; ?>
                                        </span>
                                    </p>
                                    <a href="booking_details.php?id=<?php echo $booking['id']; ?>" class="btn btn-primary">Xem chi tiết</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Recent Bookings Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-4">Lịch sử đặt phòng gần đây</h2>
            <?php if (empty($recentBookings)): ?>
                <div class="alert alert-info text-center">
                    Bạn chưa có đặt phòng nào.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Phòng</th>
                                <th>Cơ sở</th>
                                <th>Thời gian</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentBookings as $booking): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($booking['room_name']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['campus_name']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($booking['start_time'])); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $booking['status'] === 'confirmed' ? 'success' : 'warning'; ?>">
                                            <?php echo $booking['status'] === 'confirmed' ? 'Đã xác nhận' : 'Đang chờ'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="booking_details.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-primary">Xem</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include '../components/footer.php'; ?>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Components JS -->
    <!-- <script src="/script.js"></script> -->
</body>
</html> 