<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
session_start();
require_once '../../config/db_connection.php';
require_once '../../classes/Admin.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Instantiate Admin class from session
$user = $_SESSION['user'];
$db = new DbConnect();
$conn = $db->connect();
$admin = new Admin($conn, $user['id'], $user['name'], $user['user_type'], $user['username'] ?? null, $user['id']);
$adminProfile = $admin->getProfile();

// Get all bookings with user and room details
$bookings = $admin->bookingReport();

// Sort bookings by booking_date (newest first)
// usort($bookings, function($a, $b) {
//     return strtotime($b['booking_date']) <=> strtotime($a['booking_date']);
// });

// Handle deletion if delete_id is set
if (isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    $user_id = $bookings[array_search($delete_id, array_column($bookings, 'id'))]['user_id'] ?? null;
    
    if ($user_id && $admin->deleteUserBooking($user_id, $delete_id)) {
        $_SESSION['success_message'] = "Đã hủy đặt phòng thành công!";
    } else {
        $_SESSION['error_message'] = "Có lỗi xảy ra khi hủy đặt phòng!";
    }
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Pagination setup
$bookings_per_page = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$total_bookings = count($bookings);
$total_pages = ceil($total_bookings / $bookings_per_page);
$offset = ($page - 1) * $bookings_per_page;
$bookings_page = array_slice($bookings, $offset, $bookings_per_page);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đặt phòng - BKSpace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/styles.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/../../components/header.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3">
                <?php include __DIR__ . '/../../components/admin_menu.php'; ?>
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
                                        <th>#</th>
                                        <th>Người dùng</th>
                                        <th>Phòng</th>
                                        <th>Ngày</th>
                                        <th>Thời gian</th>
                                        <th>Trạng thái</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bookings_page as $index => $booking): ?>
                                    <tr>
                                        <td><?php echo $offset + $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($booking['username']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['room_name']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($booking['booking_date'])); ?></td>
                                        <td><?php echo date('H:i', strtotime($booking['start_time'])) . ' - ' . 
                                                   date('H:i', strtotime($booking['end_time'])); ?></td>
                                        <td>
                                            <?php
                                            $status = $booking['status'];
                                            $status_text = '';
                                            $status_class = '';
                                            switch ($status) {
                                                case 'cancelled':
                                                    $status_text = 'Đã hủy';
                                                    $status_class = 'badge bg-danger';
                                                    break;
                                                case 'completed':
                                                    $status_text = 'Đã hoàn thành';
                                                    $status_class = 'badge bg-secondary';
                                                    break;
                                                case 'checked_in':
                                                    $status_text = 'Đang sử dụng';
                                                    $status_class = 'badge bg-info';
                                                    break;
                                                case 'pending':
                                                    $status_text = 'Đang chờ';
                                                    $status_class = 'badge bg-warning text-dark';
                                                    break;
                                                case 'confirmed':
                                                default:
                                                    $status_text = 'Đã xác nhận';
                                                    $status_class = 'badge bg-success';
                                                    break;
                                            }
                                            ?>
                                            <span class="<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                        </td>
                                        <td>
                                            <?php
                                            $booking_datetime = strtotime($booking['booking_date'] . ' ' . $booking['end_time']);
                                            $current_datetime = time();
                                            $is_booking_passed = $booking_datetime < $current_datetime;
                                            $is_booking_cancelled = in_array($booking['status'], ['cancelled', 'completed']);
                                            $disable_button = $is_booking_passed || $is_booking_cancelled;
                                            ?>
                                            <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Bạn có chắc chắn muốn hủy đặt phòng này?');">
                                                <input type="hidden" name="delete_id" value="<?php echo $booking['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" <?php echo $disable_button ? 'disabled' : ''; ?>>
                                                    <i class="bi bi-trash"></i> Hủy
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item<?php if ($page <= 1) echo ' disabled'; ?>">
                                    <a class="page-link" href="?page=<?php echo max(1, $page - 1); ?>" aria-label="Previous">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item<?php if ($i == $page) echo ' active'; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item<?php if ($page >= $total_pages) echo ' disabled'; ?>">
                                    <a class="page-link" href="?page=<?php echo min($total_pages, $page + 1); ?>" aria-label="Next">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../../components/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 