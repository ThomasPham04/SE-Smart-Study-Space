<?php
session_start();
require_once '../config/database.php';

// Get current user session if exists
$currentUser = isset($_SESSION['user']) ? $_SESSION['user'] : null;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BKSpace - Liên hệ</title>
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
    <?php require '../components/header.php'; ?>

    <div class="sub-hero">
        <img src="../assets/img/main_page_bg.png" alt="HCMUT Building">
        <div class="sub-hero-text">
            Liên hệ với chúng tôi
        </div>
    </div>

    <!-- Contact Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <!-- Contact Information -->
                <div class="col-md-6 mb-4">
                    <h3 class="mb-4">Thông tin liên hệ</h3>
                    <div class="contact-info">
                        <div class="mb-4">
                            <h5>Sinh viên</h5>
                            <p><i class="bi bi-link-45deg"></i> <a href="https://mybk.hcmut.edu.vn/my/index.action" target="_blank">MyBK</a></p>
                        </div>
                        
                        <div class="mb-4">
                            <h5>Quý khách</h5>
                            <p><i class="bi bi-envelope"></i> <a href="mailto:dl-cntt@hcmut.edu.vn">dl-cntt@hcmut.edu.vn</a></p>
                            <p><i class="bi bi-link-45deg"></i> <a href="https://hcmut.edu.vn/contact" target="_blank">Biểu mẫu thông tin liên hệ</a></p>
                        </div>

                        <div class="mb-4">
                            <h5>Địa chỉ</h5>
                            <p><i class="bi bi-geo-alt"></i> 268 Lý Thường Kiệt, Phường 14, Quận 10, TP.HCM</p>
                        </div>

                        <div class="social-links">
                            <h5>Theo dõi chúng tôi</h5>
                            <div class="d-flex gap-3">
                                <a href="https://www.facebook.com/truongdhbachkhoa" target="_blank" class="social-icon">
                                    <i class="bi bi-facebook"></i>
                                </a>
                                <a href="https://www.youtube.com/@bkoisp" target="_blank" class="social-icon">
                                    <i class="bi bi-youtube"></i>
                                </a>
                                <a href="https://twitter.com/DHBK_TPHCM" target="_blank" class="social-icon">
                                    <i class="bi bi-twitter"></i>
                                </a>
                                <a href="https://www.instagram.com/truongdaihocbachkhoa.1957/" target="_blank" class="social-icon">
                                    <i class="bi bi-instagram"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="col-md-6">
                    <h3 class="mb-4">Gửi tin nhắn cho chúng tôi</h3>
                    <form class="contact-form">
                        <div class="mb-3">
                            <label for="name" class="form-label">Họ và tên</label>
                            <input type="text" class="form-control" id="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Chủ đề</label>
                            <input type="text" class="form-control" id="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Nội dung</label>
                            <textarea class="form-control" id="message" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Gửi tin nhắn</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <?php require '../components/footer.php'; ?>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 