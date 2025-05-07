<?php
session_start();
require_once '../config/db_connection.php';
require_once '../classes/Admin.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Instantiate Admin class from session
$user = $_SESSION['user'];
$admin = new Admin($user['id'], $user['name'], $user['user_type'], $user['username'] ?? null, $user['id']);
$adminProfile = $admin->getProfile();

$db = new DbConnect();
$conn = $db->connect();

// Handle deletion if delete_id is set
if (isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    $delete_query = "DELETE FROM bookings WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $delete_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Đã hủy đặt phòng thành công!";
    } else {
        $_SESSION['error_message'] = "Có lỗi xảy ra khi hủy đặt phòng!";
    }
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Get all bookings with user and room details
$query = "SELECT b.*, u.name as user_name, r.name as room_name 
          FROM bookings b 
          JOIN users u ON b.user_id = u.id 
          JOIN rooms r ON b.room_id = r.id 
          ORDER BY b.booking_date DESC, b.start_time DESC";
$result = $conn->query($query);
$bookings = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đặt phòng - BKSpace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
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
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Quản lý đặt phòng</h4>
                        
                        <?php if (isset($_SESSION['success_message'])): ?>
                            <div class="alert alert-success">
                                <?php 
                                    echo $_SESSION['success_message'];
                                    unset($_SESSION['success_message']);
                                ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['error_message'])): ?>
                            <div class="alert alert-danger">
                                <?php 
                                    echo $_SESSION['error_message'];
                                    unset($_SESSION['error_message']);
                                ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Người dùng</th>
                                        <th>Phòng</th>
                                        <th>Ngày</th>
                                        <th>Thời gian</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bookings as $booking): ?>
                                    <tr>
                                        <td><?php echo $booking['id']; ?></td>
                                        <td><?php echo htmlspecialchars($booking['user_name']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['room_name']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($booking['booking_date'])); ?></td>
                                        <td><?php echo date('H:i', strtotime($booking['start_time'])) . ' - ' . 
                                                   date('H:i', strtotime($booking['end_time'])); ?></td>
                                        <td>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Bạn có chắc chắn muốn hủy đặt phòng này?');">
                                                <input type="hidden" name="delete_id" value="<?php echo $booking['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="bi bi-trash"></i> Hủy
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
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