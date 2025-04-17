<?php
session_start();
require_once 'config/db_connection.php';

// // Get current user session if exists
// $currentUser = isset($_SESSION['user']) ? $_SESSION['user'] : null;

// // Get rooms data from database
// $db = Database::getInstance();
// $rooms = $db->query("SELECT * FROM rooms")->fetchAll(PDO::FETCH_ASSOC);

// // Get bookings data to check availability
// $bookings = $db->query("
//     SELECT r.room_id, r.room_name, b.status, b.booking_start, b.booking_end 
//     FROM rooms r 
//     LEFT JOIN bookings b ON r.room_id = b.room_id 
//     WHERE b.status IS NULL OR b.status != 'Quá hạn'
// ")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BKSpace - Smart Study Space at HCMUT</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <!-- External CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <!-- Include navbar using PHP -->
    <?php 
        require 'components/header.php';
    ?>

    <!-- Hero Section -->
    <div class="hero">
        <img src="assets/img/main_page_bg.png" alt="HCMUT Building">
        <div class="hero-text"> 
            CHÀO MỪNG BẠN ĐẾN VỚI<br>BKSPACE!
            
        </div>
    </div>
    
    <!-- Features Section -->
    <section class="features py-5">
        <div class="container">
            <h2 class="text-center features-title">Những tính năng tuyệt vời của<br>BKSpace</h2>
            <div class="row g-4">
                <!-- Feature 1 -->
                <div class="col-md-4">
                    <div class="card feature-card shadow-sm p-4">
                        <div class="card-body text-center">
                            <div class="feature-icon">
                                <i class="bi bi-arrow-repeat"></i>
                            </div>
                            <h3 class="h4 mb-3">Tiện lợi</h3>
                            <ul class="list-group">
                                <li class="list-group-item">Đặt chỗ dễ dàng qua app</li>
                                <li class="list-group-item">Quét QR xác nhận, cập nhật chỗ trống theo thời gian thực</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Feature 2 -->
                <div class="col-md-4">
                    <div class="card feature-card shadow-sm p-4">
                        <div class="card-body text-center">
                            <div class="feature-icon">
                                <i class="bi bi-gear"></i>
                            </div>
                            <h3 class="h4 mb-3">Khoa học</h3>
                            <ul class="list-group">
                                <li class="list-group-item">Giao diện dễ dùng, cung cấp thông tin rõ ràng</li>
                                <li class="list-group-item">Gợi ý chỗ ngồi theo yêu cầu</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Feature 3 -->
                <div class="col-md-4">
                    <div class="card feature-card shadow-sm p-4">
                        <div class="card-body text-center">
                            <div class="feature-icon">
                                <i class="bi bi-lock"></i>
                            </div>
                            <h3 class="h4 mb-3">Bảo mật</h3>
                            <ul class="list-group">
                                <li class="list-group-item">Xác thực sinh viên khi đặt chỗ</li>
                                <li class="list-group-item">Báo cáo vi phạm</li>
                                <li class="list-group-item">Quản lí tài nguyên hiệu quả</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Include footer using PHP -->
    <?php require 'components/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- <script src="script.js"></script> -->
</body>
</html> 