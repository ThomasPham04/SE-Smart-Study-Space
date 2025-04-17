<?php
session_start();
require_once '../config/db_connection.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$db = Database::getInstance();
$user = $_SESSION['user'];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    
    if (!empty($name) && !empty($email)) {
        $db->query(
            "UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?",
            [$name, $email, $phone, $user['id']]
        );
        
        // Update session
        $_SESSION['user']['name'] = $name;
        $_SESSION['user']['email'] = $email;
        $_SESSION['user']['phone'] = $phone;
        
        $success = "Cập nhật thông tin thành công!";
    }
}

// Get user's statistics
$stats = $db->query(
    "SELECT 
        COUNT(*) as total_bookings,
        SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
        SUM(CASE WHEN start_time > NOW() THEN 1 ELSE 0 END) as upcoming_bookings
     FROM bookings 
     WHERE user_id = ?",
    [$user['id']]
)->fetch();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BKSpace - Hồ sơ người dùng</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <!-- External CSS -->
    <link rel="stylesheet" href="./assets/css/styles.css">  
</head>
<body>
    <?php include './components/header.php'; ?>

    <div class="container py-5">
        <div class="row">
            <!-- Profile Section -->
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <img src="<?php echo htmlspecialchars($user['avatar'] ?? '/assets/img/avatar-placeholder.png'); ?>" 
                             alt="User Avatar" 
                             class="rounded-circle mb-3" 
                             style="width: 150px; height: 150px; object-fit: cover;">
                        <h3 class="card-title"><?php echo htmlspecialchars($user['name']); ?></h3>
                        <p class="text-muted"><?php echo htmlspecialchars($user['role']); ?></p>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="card shadow-sm mt-4">
                    <div class="card-body">
                        <h4 class="card-title">Thống kê</h4>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Tổng số lần đặt phòng
                                <span class="badge bg-primary rounded-pill"><?php echo $stats['total_bookings']; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Đặt phòng đã xác nhận
                                <span class="badge bg-success rounded-pill"><?php echo $stats['confirmed_bookings']; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Đặt phòng sắp tới
                                <span class="badge bg-info rounded-pill"><?php echo $stats['upcoming_bookings']; ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Profile Form -->
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title mb-4">Thông tin cá nhân</h3>
                        
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="name" class="form-label">Họ và tên</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="name" 
                                       name="name" 
                                       value="<?php echo htmlspecialchars($user['name']); ?>" 
                                       required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" 
                                       required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Số điện thoại</label>
                                <input type="tel" 
                                       class="form-control" 
                                       id="phone" 
                                       name="phone" 
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Cập nhật thông tin</button>
                        </form>
                    </div>
                </div>

                <!-- Change Password Section -->
                <div class="card shadow-sm mt-4">
                    <div class="card-body">
                        <h3 class="card-title mb-4">Đổi mật khẩu</h3>
                        <form method="POST" action="change_password.php">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Mật khẩu hiện tại</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Mật khẩu mới</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Xác nhận mật khẩu mới</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Đổi mật khẩu</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../components/footer.php'; ?>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Components JS -->
    <!-- <script src="/script.js"></script> -->
</body>
</html> 