<?php
session_start();
require_once '../config/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

// Get user's bookings
$db = new DbConnect();
$conn = $db->connect();

$user_id = $_SESSION['user']['id'];
$stmt = $conn->prepare("
    SELECT b.*, r.name as room_name, r.building, r.floor, r.room_type_id
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Check if there's a success message
$success_message = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử đặt phòng - BKSpace</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <!-- External CSS -->
    <link rel="stylesheet" href="../assets/css/styles.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"> </script>
</head>
<body>
    <?php require '../components/header.php'; ?>

    <div class="booking-history">
        <h1 class="page-title">Lịch sử đặt phòng</h1>
        
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="search-box mb-4">
            <input type="text" class="form-control" placeholder="Tìm kiếm...">
        </div>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tên phòng</th>
                        <th>Trạng thái</th>
                        <th>Thời gian đặt</th>
                        <th>Ngày đặt</th>
                        <th>Tùy chỉnh</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $index => $booking): 
                        // Calculate time variables first
                        $bookingStartTime = strtotime($booking['booking_date'] . ' ' . $booking['start_time']);
                        $bookingEndTime = strtotime($booking['booking_date'] . ' ' . $booking['end_time']);
                        $currentTime = time();
                        
                        // Determine status text and class based on status and time
                        $status_text = '';
                        $status_class = '';

                        switch ($booking['status']) {
                            case 'cancelled':
                                $status_text = 'Đã hủy';
                                $status_class = 'status-cancelled';
                                break;
                            case 'completed':
                                $status_text = 'Đã hoàn thành';
                                $status_class = 'status-completed';
                                break;
                            case 'checked_in':
                                $status_text = 'Đang sử dụng';
                                $status_class = 'status-active';
                                break;
                            case 'pending':
                            case 'confirmed':
                                if ($currentTime < $bookingStartTime) {
                                    $status_text = 'Chưa tới ngày';
                                    $status_class = 'status-pending';
                                } elseif ($currentTime <= $bookingEndTime) {
                                    $status_text = 'Đang mở'; // Or maybe "Đang trong giờ"?
                                    $status_class = 'status-active';
                                } else {
                                    $status_text = 'Đã hết hạn';
                                    $status_class = 'status-expired';
                                }
                                break;
                            default:
                                $status_text = $booking['status']; // Fallback
                                $status_class = '';
                        }
                        
                        $booking_time = date('H:i', strtotime($booking['start_time'])) . ' - ' . 
                                      date('H:i', strtotime($booking['end_time']));
                    ?>
                    <tr data-booking-id="<?php echo $booking['id']; ?>">
                        <td><?php echo $index + 1; ?></td>
                        <td>
                            <?php echo htmlspecialchars($booking['room_name']); ?> -
                            <small class="text-muted">
                                <?php echo htmlspecialchars($booking['building']) . ' - Tầng ' . $booking['floor']; ?>
                            </small>
                        </td>
                        <td class="<?php echo $status_class; ?>"><?php echo $status_text; ?></td>
                        <td><?php echo $booking_time; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($booking['booking_date'])); ?></td>
                        <td>
                            <div class="action-buttons">
                            <?php 
                                // Calculate check-in/cancel availability (already done in previous steps)
                                $timeUntilBooking = $bookingStartTime - $currentTime;
                                $bookingInProgress = $currentTime >= $bookingStartTime && $currentTime <= $bookingEndTime;
                                $canCheckIn = ($booking['status'] === 'confirmed' || $booking['status'] === 'pending') && ($currentTime >= ($bookingStartTime - 900) && $currentTime <= $bookingEndTime);
                            
                                $canCheckOut = ($booking['status'] === 'checked_in');
                                $canCancel = ( ($booking['status'] === 'pending' || $booking['status'] === 'confirmed') && $currentTime < $bookingStartTime );
                            ?>
                            
                            <?php if ($canCheckIn): ?>
                                <button class="btn btn-sm btn-check-in btn-show-qr" data-booking-id="<?php echo $booking['id']; ?>">CHECK-IN</button>
                            <?php else: ?>
                                <button class="btn btn-sm btn-check-in" disabled title="Check-in không khả dụng">CHECK-IN</button>
                            <?php endif; ?>

                            <?php if ($canCheckOut): ?>
                                <a href="booking-checkout.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-check-out">CHECK-OUT</a>
                            <?php else: ?>
                                <button class="btn btn-sm btn-check-out" disabled title="Check-out không khả dụng">CHECK-OUT</button>
                            <?php endif; ?>

                            <?php if ($canCancel): ?>
                                <a href="booking-cancel.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-cancel">HỦY PHÒNG</a>
                            <?php else: ?>
                                <button class="btn btn-sm btn-cancel" disabled title="Chỉ có thể hủy trước thời gian đặt phòng hoặc nếu trạng thái là Pending/Confirmed">HỦY PHÒNG</button>
                            <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- QR Code Modal -->
    <div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="qrModalLabel">Check-in QR Code</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body text-center" id="qrModalBody">
            <div id="qrcode-modal" class="d-flex justify-content-center"></div>
            <div id="qrLoading" class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
          <div class="modal-footer">
            <a href="#" id="fullCheckinPage" class="btn btn-outline-primary" target="_blank">Trang check-in đầy đủ</a>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
          </div>
        </div>
      </div>
    </div>

    <?php require '../components/footer.php'; ?>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        // Handle search functionality
        document.querySelector('.search-box input').addEventListener('keyup', function(e) {
            const searchText = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchText) ? '' : 'none';
            });
        });

        // Show QR modal on check-in button click
        document.querySelectorAll('.btn-show-qr').forEach(btn => {
            btn.addEventListener('click', function() {
                const bookingId = this.getAttribute('data-booking-id');
                const modal = new bootstrap.Modal(document.getElementById('qrModal'));
                document.getElementById('qrcode-modal').innerHTML = '';
                document.getElementById('qrLoading').style.display = '';
                document.getElementById('fullCheckinPage').href = 'booking-checkin.php?id=' + bookingId;
                modal.show();
                // Fetch QR data via AJAX
                fetch('booking-checkin.php?id=' + bookingId + '&qr_data_only=1')
                    .then(res => res.text())
                    .then(qrdata => {
                        document.getElementById('qrLoading').style.display = 'none';
                        if (!qrdata || qrdata.trim().startsWith('<!DOCTYPE') || qrdata.toLowerCase().includes('<html')) {
                            document.getElementById('qrcode-modal').innerHTML = '<div class="alert alert-danger">Không thể tải mã QR. Vui lòng thử lại hoặc kiểm tra quyền truy cập.</div>';
                        } else {
                            new QRCode(document.getElementById('qrcode-modal'), {
                                text: qrdata,
                                width: 256,
                                height: 256
                            });
                        }
                    })
                    .catch(() => {
                        document.getElementById('qrLoading').style.display = 'none';
                        document.getElementById('qrcode-modal').innerHTML = '<div class="alert alert-danger">Không thể tải mã QR. Vui lòng thử lại.</div>';
                    });
            });
        });
    </script>
</body>
</html> 