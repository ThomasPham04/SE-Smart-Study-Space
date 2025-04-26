<?php
session_start();
require_once '../config/db_connection.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$db = new DbConnect();
$conn = $db->connect();

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
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Người dùng</th>
                                        <th>Phòng</th>
                                        <th>Ngày</th>
                                        <th>Thời gian</th>
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