<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    $_SESSION['redirect_after_login'] = 'booking.php'; // Store the intended destination
    header('Location: login.php');
    exit();
}

require_once '../config/db_connection.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt chỗ - BKSpace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/styles.css" rel="stylesheet">
</head>
<body>
    <?php include '../components/header.php'; ?>

    <div class="sub-hero">
        <img src="../assets/img/booking-bg.png" alt="HCMUT Building">
        <div class="hero-text">
            Đặt phòng tự học
        </div>
        <div class="search-container">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" class="form-control border-start-0" placeholder="Search">
            </div>
        </div>
    </div>

    <!-- Booking Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <h4 class="border-bottom pb-2 text-center">DANH SÁCH CƠ SỞ</h4>
                    <ul class="list-unstyled mt-4">
                        <li class="mb-3">
                            <span class="me-2">+</span>Cơ sở 1
                        </li>
                        <li class="mb-3">
                            <span class="me-2">+</span>Cơ sở 2
                        </li>
                    </ul>
                </div>
                <div class="col-md-9">
                    <h4 class="border-bottom pb-2 mb-4 text-center">DANH SÁCH PHÒNG</h4>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4 mb-3">
                            <a href="booking-confirm.php" class="room-card">
                                <img src="../assets/img/Card.png" alt="Phòng học 1 người" class="img-fluid">
                                <p class="text-center">Phòng học 1 người</p>
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="booking-confirm.php" class="room-card">
                                <img src="../assets/img/Card.png" alt="Phòng học nhóm 2" class="img-fluid">
                                <p class="text-center">Phòng học nhóm 2</p>
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="booking-confirm.php" class="room-card">
                                <img src="../assets/img/Card.png" alt="Phòng học nhóm 4" class="img-fluid">
                                <p class="text-center">Phòng học nhóm 3</p>
                            </a>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4 mb-3">
                            <a href="booking-confirm.php" class="room-card">
                                <img src="../assets/img/Card.png" alt="Phòng học 1 người" class="img-fluid">
                                <p class="text-center mt-3">Phòng học nhóm 4</p>
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="booking-confirm.php" class="room-card">
                                <img src="../assets/img/Card.png" alt="Phòng học nhóm 2" class="img-fluid">
                                <p class="text-center">Phòng học nhóm 5</p>
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="booking-confirm.php" class="room-card">
                                <img src="../assets/img/Card.png" alt="Phòng học nhóm 4" class="img-fluid">
                                <p class="text-center">Phòng học nhóm 6</p>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php require '../components/footer.php'; ?>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 