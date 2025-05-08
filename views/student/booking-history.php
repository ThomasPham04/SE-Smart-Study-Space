<?php
session_start();
require_once '../../config/db_connection.php';

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

// Pagination setup (like manage_rooms)
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
    <title>Lịch sử đặt phòng - BKSpace</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <!-- External CSS -->
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"> </script>
</head>
<body>
    <?php require '../../components/header.php'; ?>

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
                <?php foreach ($bookings_page as $index => $booking): 
                    // Tính thời gian
                    $bookingStartTime = strtotime($booking['booking_date'] . ' ' . $booking['start_time']);
                    $bookingEndTime = strtotime($booking['booking_date'] . ' ' . $booking['end_time']);
                    $currentTime = time();

                    // Hiển thị trạng thái
                    $status_text = '';
                    $status_class = '';

                    if ($booking['status'] === 'cancelled') {
                        $status_text = 'Đã hủy';
                        $status_class = 'status-cancelled';
                    } elseif ($currentTime < $bookingStartTime) {
                        $status_text = 'Chưa tới ngày';
                        $status_class = 'status-pending';
                    } elseif ($currentTime >= $bookingStartTime && $currentTime <= $bookingEndTime) {
                        $status_text = 'Hiện tại';
                        $status_class = 'text-success';
                    } elseif ($currentTime > $bookingEndTime) {
                        if ($booking['status'] === 'completed') {
                            $status_text = 'Đã hoàn thành';
                            $status_class = 'status-completed';
                        } else {
                            $status_text = 'Đã hết hạn';
                            $status_class = 'status-expired';
                        }
                    } else {
                        $status_text = $booking['status'];
                        $status_class = '';
                    }

                    // Thời gian hiển thị
                    $booking_time = date('H:i', strtotime($booking['start_time'])) . ' - ' . 
                                    date('H:i', strtotime($booking['end_time']));

                    // Tính logic hành động
                    $bookingInProgress = $currentTime >= $bookingStartTime && $currentTime <= $bookingEndTime;
                    $canCheckIn = (in_array($booking['status'], ['pending', 'confirmed']) && $bookingInProgress);
                    $canCheckOut = (in_array($booking['status'], ['pending', 'confirmed', 'checked_in']) && $bookingInProgress);
                    $canCancel = (in_array($booking['status'], ['pending', 'confirmed']) && $currentTime < $bookingStartTime);
                ?>
                <tr data-booking-id="<?php echo $booking['id']; ?>">
                    <td><?php echo $offset + $index + 1; ?></td>
                    <td>
                        <?php echo htmlspecialchars($booking['room_name']); ?> -
                        <small class="text-muted">
                            <?php echo htmlspecialchars($booking['building']) . ' - Tầng ' . $booking['floor']; ?>
                        </small>
                    </td>
                    <td class="status-cell"
                        data-start="<?php echo $booking['booking_date'] . ' ' . $booking['start_time']; ?>"
                        data-end="<?php echo $booking['booking_date'] . ' ' . $booking['end_time']; ?>"
                        data-status="<?php echo $booking['status']; ?>">
                        <?php echo $status_text; ?>
                    </td>
                    <td><?php echo $booking_time; ?></td>
                    <td><?php echo date('d/m/Y', strtotime($booking['booking_date'])); ?></td>
                    <td>
                        <div class="action-buttons"
                            data-start="<?php echo $booking['booking_date'] . ' ' . $booking['start_time']; ?>"
                            data-end="<?php echo $booking['booking_date'] . ' ' . $booking['end_time']; ?>"
                            data-status="<?php echo $booking['status']; ?>">
                            
                            <!-- CHECK-IN -->
                            <button class="btn btn-sm btn-check-in"
                                data-booking-id="<?php echo $booking['id']; ?>"
                                <?php echo $canCheckIn ? '' : 'disabled'; ?>>
                                CHECK-IN
                            </button>

                            <!-- CHECK-OUT -->
                            <button class="btn btn-sm btn-check-out"
                                data-booking-id="<?php echo $booking['id']; ?>"
                                <?php echo $canCheckOut ? '' : 'disabled'; ?>>
                                CHECK-OUT
                            </button>

                            <!-- CANCEL -->
                            <button class="btn btn-sm btn-cancel"
                                <?php echo $canCancel ? '' : 'disabled'; ?>>
                                HỦY PHÒNG
                            </button>
                        </div>
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
          </div>
        </div>
      </div>
    </div>

    <?php require '../../components/footer.php'; ?>

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
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, setting up check-in buttons');
            const checkInButtons = document.querySelectorAll('.btn-check-in:not([disabled])');
            console.log('Found check-in buttons:', checkInButtons.length);

            // On page load, re-enable CHECK-OUT for bookings marked as checked-in in localStorage
            const checkedInBookings = JSON.parse(localStorage.getItem('checkedInBookings') || '[]');
            checkedInBookings.forEach(function(bookingId) {
                const row = document.querySelector('tr[data-booking-id="' + bookingId + '"]');
                if (row) {
                    const checkOutBtn = row.querySelector('.btn-check-out');
                    if (checkOutBtn) {
                        checkOutBtn.disabled = false;
                    }
                }
            });

            checkInButtons.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Check-in button clicked');
                    const bookingId = this.getAttribute('data-booking-id');
                    console.log('Booking ID:', bookingId);

                    // Get the modal element
                    const modalElement = document.getElementById('qrModal');
                    if (!modalElement) {
                        console.error('Modal element not found');
                        return;
                    }

                    // Create and show the modal
                    const modal = new bootstrap.Modal(modalElement);
                    console.log('Modal created');

                    // Clear previous QR code
                    const qrContainer = document.getElementById('qrcode-modal');
                    qrContainer.innerHTML = '';
                    
                    // Show loading
                    const loadingElement = document.getElementById('qrLoading');
                    if (loadingElement) {
                        loadingElement.style.display = '';
                    }

                    // Generate random data
                    const randomData = 'checkin_' + bookingId + '_' + Math.random().toString(36).substring(2, 15);
                    console.log('Generated QR data:', randomData);

                    try {
                        // Create QR code
                        new QRCode(qrContainer, {
                            text: randomData,
                            width: 256,
                            height: 256,
                            colorDark: "#000000",
                            colorLight: "#ffffff",
                            correctLevel: QRCode.CorrectLevel.H
                        });
                        console.log('QR code generated');

                        // Hide loading
                        if (loadingElement) {
                            loadingElement.style.display = 'none';
                        }

                        // Show the modal
                        modal.show();

                        // Simulate enabling CHECK-OUT after CHECK-IN
                        const row = this.closest('tr');
                        if (row) {
                            const checkOutBtn = row.querySelector('.btn-check-out');
                            if (checkOutBtn) {
                                checkOutBtn.disabled = false;
                            }
                        }
                        // Store checked-in booking in localStorage
                        let checkedIn = JSON.parse(localStorage.getItem('checkedInBookings') || '[]');
                        if (!checkedIn.includes(bookingId)) {
                            checkedIn.push(bookingId);
                            localStorage.setItem('checkedInBookings', JSON.stringify(checkedIn));
                        }
                    } catch (error) {
                        console.error('Error generating QR code:', error);
                        qrContainer.innerHTML = '<div class=\'alert alert-danger\'>Error generating QR code</div>';
                        if (loadingElement) {
                            loadingElement.style.display = 'none';
                        }
                    }
                });
            });

            // Use event delegation for CHECK-OUT button so QR code is generated even if enabled after page load
            document.addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('btn-check-out') && !e.target.disabled) {
                    e.preventDefault();
                    const bookingId = e.target.getAttribute('data-booking-id');
                    // Get the modal element
                    const modalElement = document.getElementById('qrModal');
                    if (!modalElement) return;
                    const modal = new bootstrap.Modal(modalElement);
                    const qrContainer = document.getElementById('qrcode-modal');
                    qrContainer.innerHTML = '';
                    const loadingElement = document.getElementById('qrLoading');
                    if (loadingElement) loadingElement.style.display = '';
                    // Generate random data for check-out
                    const randomData = 'checkout_' + bookingId + '_' + Math.random().toString(36).substring(2, 15);
                    try {
                        new QRCode(qrContainer, {
                            text: randomData,
                            width: 256,
                            height: 256,
                            colorDark: "#000000",
                            colorLight: "#ffffff",
                            correctLevel: QRCode.CorrectLevel.H
                        });
                        if (loadingElement) loadingElement.style.display = 'none';
                        modal.show();
                    } catch (error) {
                        qrContainer.innerHTML = '<div class="alert alert-danger">Error generating QR code</div>';
                        if (loadingElement) loadingElement.style.display = 'none';
                    }
                }
            });
        });

        // Real-time status and button update based on user's PC time
        function updateBookingStatusUI() {
            document.querySelectorAll('tr[data-booking-id]').forEach(function(row) {
                const statusCell = row.querySelector('.status-cell');
                const actionDiv = row.querySelector('.action-buttons');
                if (!statusCell || !actionDiv) return;
                const start = new Date(statusCell.dataset.start.replace(/-/g, '/'));
                const end = new Date(statusCell.dataset.end.replace(/-/g, '/'));
                const now = new Date();
                let statusText = statusCell.dataset.status;
                let statusClass = '';
                let canCheckIn = false, canCheckOut = false, canCancel = false;
                
                // Check if current time is within booking time
                const bookingInProgress = now >= start && now <= end;
                
                if (statusText === 'cancelled') {
                    statusText = 'Đã hủy';
                    statusClass = 'status-cancelled';
                } else if (now < start) {
                    statusText = 'Chưa tới ngày';
                    statusClass = 'status-pending';
                    canCancel = (statusCell.dataset.status === 'pending' || statusCell.dataset.status === 'confirmed');
                } else if (bookingInProgress) {
                    statusText = 'Hiện tại';
                    statusClass = 'text-success';
                    canCheckIn = (statusCell.dataset.status === 'confirmed' || statusCell.dataset.status === 'pending');
                    canCheckOut = (statusCell.dataset.status === 'checked_in');
                } else if (now > end) {
                    if (statusCell.dataset.status === 'completed') {
                        statusText = 'Đã hoàn thành';
                        statusClass = 'status-completed';
                    } else {
                        statusText = 'Đã hết hạn';
                        statusClass = 'status-expired';
                    }
                } else {
                    statusText = statusCell.dataset.status;
                    statusClass = '';
                }
                
                statusCell.textContent = statusText;
                statusCell.className = 'status-cell ' + statusClass;
                
                // Update buttons
                const btns = actionDiv.querySelectorAll('button');
                if (btns.length === 3) {
                    btns[0].disabled = !canCheckIn;
                    btns[1].disabled = !canCheckOut;
                    btns[2].disabled = !canCancel;
                }
            });
        }
        // Initial update
        updateBookingStatusUI();
        // Optionally, update every minute
        setInterval(updateBookingStatusUI, 60000);
    </script>
</body>
</html> 