<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    $_SESSION['redirect_after_login'] = 'booking.php'; // Store the intended destination
    header('Location: login.php');
    exit();
}

require_once '../config/db_connection.php';

// Get database connection
$db = new DbConnect();
$conn = $db->connect();

// Get selected building (cơ sở)
$selected_building = isset($_GET['building']) ? $_GET['building'] : '';

// Get all room types with their capacity information
$type_stmt = $conn->prepare("
    SELECT rt.id, rt.name, rt.capacity, rt.description,
           COUNT(r.id) as available_rooms
    FROM room_types rt
    LEFT JOIN rooms r ON rt.id = r.room_type_id AND r.status = 'available'
    " . (!empty($selected_building) ? "AND r.building = ?" : "") . "
    GROUP BY rt.id
    ORDER BY rt.capacity
");

if (!empty($selected_building)) {
    $type_stmt->bind_param("s", $selected_building);
}
$type_stmt->execute();
$room_types = $type_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get all unique buildings
$building_stmt = $conn->prepare("SELECT DISTINCT building FROM rooms ORDER BY building");
$building_stmt->execute();
$buildings = $building_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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
    <style>
        .building-item {
            cursor: pointer;
            padding: 8px 0;
            transition: all 0.3s;
        }
        .building-item:hover, .building-item.active {
            color: #0d6efd;
            font-weight: bold;
        }
        .building-item i {
            transition: transform 0.3s;
        }
        .building-item:hover i, .building-item.active i {
            transform: rotate(90deg);
        }
        .room-type-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
        }
        .room-type-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .card-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: rgba(25, 135, 84, 0.9);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }
        .card-badge.unavailable {
            background-color: rgba(220, 53, 69, 0.9);
        }
    </style>
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
                <input type="text" class="form-control border-start-0" id="roomSearch" placeholder="Tìm kiếm phòng...">
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
                            <a href="booking.php" class="building-item d-block text-decoration-none text-dark <?php echo empty($selected_building) ? 'active' : ''; ?>">
                                <i class="bi bi-chevron-right me-2"></i>Tất cả cơ sở
                            </a>
                        </li>
                        <?php foreach($buildings as $building): ?>
                        <li class="mb-3">
                            <a href="booking.php?building=<?php echo urlencode($building['building']); ?>" 
                               class="building-item d-block text-decoration-none text-dark <?php echo $selected_building === $building['building'] ? 'active' : ''; ?>">
                                <i class="bi bi-chevron-right me-2"></i><?php echo htmlspecialchars($building['building']); ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="col-md-9">
                    <h4 class="border-bottom pb-2 mb-4 text-center">
                        DANH SÁCH LOẠI PHÒNG
                        <?php if(!empty($selected_building)): ?>
                            - <?php echo htmlspecialchars($selected_building); ?>
                        <?php endif; ?>
                    </h4>
                    
                    <?php if(empty($room_types)): ?>
                    <div class="alert alert-info">
                        Không có loại phòng nào khả dụng ở khu vực này.
                    </div>
                    <?php else: ?>
                    <div class="row g-4 mb-3" id="roomsContainer">
                        <?php foreach($room_types as $type): ?>
                        <div class="col-md-4 mb-3 room-item">
                            <?php if($type['available_rooms'] > 0): ?>
                            <a href="booking-by-capacity.php?type_id=<?php echo $type['id']; ?><?php echo !empty($selected_building) ? '&building=' . urlencode($selected_building) : ''; ?>" 
                               class="text-decoration-none">
                            <?php endif; ?>
                                <div class="card room-type-card position-relative">
                                    <img src="../assets/img/Card.png" alt="<?php echo htmlspecialchars($type['name']); ?>" class="card-img-top">
                                    <span class="card-badge <?php echo $type['available_rooms'] ? '' : 'unavailable'; ?>">
                                        <?php echo $type['available_rooms'] ? $type['available_rooms'] . ' phòng trống' : 'Hết phòng'; ?>
                                    </span>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($type['name']); ?></h5>
                                        <p class="card-text">
                                            <i class="bi bi-people me-2"></i>Sức chứa: <?php echo $type['capacity']; ?> người
                                        </p>
                                        <p class="card-text small text-muted">
                                            <?php echo htmlspecialchars($type['description']); ?>
                                        </p>
                                    </div>
                                    <div class="card-footer bg-transparent">
                                        <?php if($type['available_rooms'] > 0): ?>
                                            <button class="btn btn-primary w-100">Chọn phòng</button>
                                        <?php else: ?>
                                            <button class="btn btn-secondary w-100" disabled>Không khả dụng</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php if($type['available_rooms'] > 0): ?>
                            </a>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <?php require '../components/footer.php'; ?>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Room search functionality
        document.getElementById('roomSearch').addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            const roomItems = document.querySelectorAll('.room-item');
            
            roomItems.forEach(item => {
                const roomText = item.textContent.toLowerCase();
                if (roomText.includes(searchText)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html> 