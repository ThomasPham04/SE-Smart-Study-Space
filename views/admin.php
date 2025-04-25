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
                <!-- Admin Sidebar -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Admin Menu</h5>
                        <div class="list-group">
                            <a href="#" class="list-group-item list-group-item-action active">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard
                            </a>
                            <a href="manage_users.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-people me-2"></i>Quản lý người dùng
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="bi bi-calendar-check me-2"></i>Quản lý đặt phòng
                            </a>
                            <a href="manage_rooms.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-building me-2"></i>Quản lý phòng
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="bi bi-gear me-2"></i>Cài đặt
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9">
                <!-- Admin Content -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Chào mừng, <?php echo htmlspecialchars($adminName); ?>!</h4>
                        <p class="text-muted">Đây là trang quản trị của BKSpace</p>
                        
                        <!-- Statistics Cards -->
                        <div class="row mt-4">
                            <div class="col-md-4 mb-4">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Tổng số người dùng</h5>
                                        <h2 class="card-text">0</h2>
                                        <a href="manage_users.php" class="btn btn-light btn-sm mt-2">
                                            <i class="bi bi-people"></i> Quản lý người dùng
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Đặt phòng hôm nay</h5>
                                        <h2 class="card-text">0</h2>
                                        <a href="#" class="btn btn-light btn-sm mt-2">
                                            <i class="bi bi-calendar-check"></i> Xem đặt phòng
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Tổng số phòng</h5>
                                        <h2 class="card-text">0</h2>
                                        <a href="manage_rooms.php" class="btn btn-light btn-sm mt-2">
                                            <i class="bi bi-building"></i> Quản lý phòng
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5>Thao tác nhanh</h5>
                                <div class="d-flex gap-2">
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
                                        <!-- Table content will be populated dynamically -->
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