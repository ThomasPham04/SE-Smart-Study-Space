<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Notification.php';

// Get database connection
$conn = require_once __DIR__ . '/../config/database.php';

$notification = new Notification($conn);
$unreadCount = 0;
$recentNotifications = [];

if (isset($_SESSION['user']) && isset($_SESSION['user']['id'])) {
    $unreadCount = $notification->getUnreadCount($_SESSION['user']['id']);
    $recentNotifications = $notification->getRecentNotifications($_SESSION['user']['id']);
}
?>
    <!-- Header/Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="<?php echo BASE_URL; ?>index.php">
            <img src="<?php echo BASE_URL; ?>assets/img/logo.png" alt="BKSpace Logo" class="logo">
            <div>
                <h1 class="h5 mb-0">BKSpace</h1>
                <small>Smart Study Space at HCMUT</small>
            </div>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav align-items-center">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>index.php">Trang chủ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>views/student/booking.php">Đặt chỗ</a>
                </li>
                <!-- <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>views/contact.php">Liên hệ</a>
                </li> -->
                <?php if (isset($_SESSION['user'])): ?>
                    <li class="nav-item dropdown me-3">
                        <a class="nav-link position-relative" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-bell"></i>
                            <?php if ($unreadCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?php echo $unreadCount; ?>
                                <span class="visually-hidden">unread notifications</span>
                            </span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end notification-dropdown" style="min-width: 300px; max-width: 400px;">
                            <li class="dropdown-header d-flex justify-content-between align-items-center">
                                <span>Thông báo</span>
                                <?php if ($unreadCount > 0): ?>
                                <a href="#" class="text-decoration-none small mark-all-read">Đánh dấu là tất cả đã đọc</a>
                                <?php endif; ?>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <?php if (empty($recentNotifications)): ?>
                            <li>
                                <div class="dropdown-item text-center text-muted">
                                    Không có thông báo nào
                                </div>
                            </li>
                            <?php else: ?>
                                <?php foreach ($recentNotifications as $notif): ?>
                                <li>
                                    <a class="dropdown-item <?php echo $notif['is_read'] ? '' : 'fw-bold'; ?>" 
                                       href="<?php echo $notif['link'] ?: '#'; ?>"
                                       data-notification-id="<?php echo $notif['notification_id']; ?>">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0">
                                                <i class="bi <?php echo $notification->getIconByType($notif['type']); ?>"></i>
                                            </div>
                                            <div class="flex-grow-1 ms-2">
                                                <p class="mb-0"><?php echo htmlspecialchars($notif['message']); ?></p>
                                                <small class="text-muted"><?php echo $notification->formatTimestamp($notif['created_at']); ?></small>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-center" href="<?php echo BASE_URL; ?>views/notifications.php">Xem tất cả thông báo</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>
                            <?php echo htmlspecialchars($_SESSION['user']['name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if ($_SESSION['user']['user_type'] === 'admin'): ?>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>views/admin/admin.php">
                                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                                </a></li>
                            <?php else: ?>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>views/student/booking-history.php">
                                    <i class="bi bi-clock-history me-2"></i>Lịch sử đặt phòng
                                </a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>views/logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i>Đăng xuất
                            </a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>views/login.php">Đăng nhập</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav> 