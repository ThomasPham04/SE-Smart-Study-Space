<?php
session_start();
require_once '../config/db_connection.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Get all rooms with their details
$db = new DbConnect();
$conn = $db->connect();

$stmt = $conn->prepare("
    SELECT r.*, 
           rt.name as room_type_name,
           rt.capacity
    FROM rooms r
    LEFT JOIN room_types rt ON r.room_type_id = rt.id
    ORDER BY r.building, r.floor, r.name
");
$stmt->execute();
$rooms = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get room types for the add room form
$stmt = $conn->prepare("SELECT id, name FROM room_types ORDER BY capacity");
$stmt->execute();
$room_types = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý phòng - BKSpace Admin</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <!-- External CSS -->
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .page-header {
            background: url('../assets/img/admin-bg.jpg') no-repeat center;
            background-size: cover;
            padding: 3rem 0;
            position: relative;
            margin-bottom: 2rem;
        }
        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
        }
        .page-header h1 {
            color: white;
            position: relative;
            text-align: center;
            font-size: 2.5rem;
            font-weight: bold;
            margin: 0;
        }
        .search-box {
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
        }
        .search-box input {
            flex: 1;
            padding: 0.5rem 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            text-align: center;
            vertical-align: middle;
        }
        .table td {
            vertical-align: middle;
            text-align: center;
        }
        .status-active {
            color: #28a745;
            font-weight: 500;
        }
        .status-maintenance {
            color: #ffc107;
            font-weight: 500;
        }
        .status-inactive {
            color: #dc3545;
            font-weight: 500;
        }
        .btn-add-room {
            background-color: #28a745;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-add-room:hover {
            background-color: #218838;
        }
        .equipment-status {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.875rem;
        }
        .equipment-ok {
            background-color: #d4edda;
            color: #155724;
        }
        .equipment-issue {
            background-color: #fff3cd;
            color: #856404;
        }
        .equipment-missing {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <?php require '../components/header.php'; ?>

    <div class="page-header">
        <h1>QUẢN LÝ PHÒNG</h1>
    </div>

    <div class="admin-container">
        <div class="search-box">
            <input type="text" class="form-control" placeholder="Tìm kiếm phòng...">
            <button class="btn-add-room" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                <i class="bi bi-plus-circle me-2"></i>Thêm phòng
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tên phòng</th>
                        <th>Loại phòng</th>
                        <th>Trạng thái</th>
                        <th>Thiết bị</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rooms as $index => $room): 
                        $status_class = match($room['status']) {
                            'available' => 'status-active',
                            'maintenance' => 'status-maintenance',
                            'unavailable' => 'status-inactive',
                            default => ''
                        };
                        
                        $status_text = match($room['status']) {
                            'available' => 'Hoạt động',
                            'maintenance' => 'Bảo trì',
                            'unavailable' => 'Tạm đóng',
                            default => $room['status']
                        };
                    ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($room['name']); ?>
                            <br>
                            <small class="text-muted">
                                <?php echo htmlspecialchars($room['building']) . ' - Tầng ' . $room['floor']; ?>
                            </small>
                        </td>
                        <td data-room-type-id="<?php echo $room['room_type_id']; ?>">
                            <?php echo htmlspecialchars($room['room_type_name']); ?>
                            <br>
                            <small class="text-muted"><?php echo $room['capacity']; ?> người</small>
                        </td>
                        <td class="<?php echo $status_class; ?>" data-status="<?php echo $room['status']; ?>">
                            <?php echo $status_text; ?>
                        </td>
                        <td>
                            <?php if ($room['equipment_status'] === 'Đầy đủ'): ?>
                                <span class="equipment-status equipment-ok">Đầy đủ</span>
                            <?php elseif ($room['equipment_status'] === 'Bóng đèn bị hư'): ?>
                                <span class="equipment-status equipment-issue">Bóng đèn bị hư</span>
                            <?php elseif ($room['equipment_status'] === 'Thiếu ghế'): ?>
                                <span class="equipment-status equipment-issue">Thiếu ghế</span>
                            <?php elseif ($room['equipment_status'] === 'Hỏng máy lạnh'): ?>
                                <span class="equipment-status equipment-issue">Hỏng máy lạnh</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-primary me-1" title="Chỉnh sửa" 
                                    data-bs-toggle="modal" data-bs-target="#editRoomModal" 
                                    data-room-id="<?php echo $room['id']; ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" title="Xóa" 
                                    onclick="deleteRoom(<?php echo $room['id']; ?>)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Room Modal -->
    <div class="modal fade" id="addRoomModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm phòng mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addRoomForm">
                        <div class="mb-3">
                            <label for="roomName" class="form-label">Tên phòng</label>
                            <input type="text" class="form-control" id="roomName" required>
                        </div>
                        <div class="mb-3">
                            <label for="roomType" class="form-label">Loại phòng</label>
                            <select class="form-select" id="roomType" required>
                                <?php foreach ($room_types as $type): ?>
                                    <option value="<?php echo $type['id']; ?>">
                                        <?php echo htmlspecialchars($type['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="building" class="form-label">Tòa nhà</label>
                            <select class="form-select" id="building" required>
                                <option value="Cơ sở 1">Cơ sở 1</option>
                                <option value="Cơ sở 2">Cơ sở 2</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="floor" class="form-label">Tầng</label>
                            <input type="number" class="form-control" id="floor" min="1" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" onclick="addRoom()">Thêm phòng</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Room Modal -->
    <div class="modal fade" id="editRoomModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Chỉnh sửa phòng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editRoomForm">
                        <input type="hidden" id="editRoomId">
                        <div class="mb-3">
                            <label for="editRoomName" class="form-label">Tên phòng</label>
                            <input type="text" class="form-control" id="editRoomName" required>
                        </div>
                        <div class="mb-3">
                            <label for="editRoomType" class="form-label">Loại phòng</label>
                            <select class="form-select" id="editRoomType" required>
                                <?php foreach ($room_types as $type): ?>
                                    <option value="<?php echo $type['id']; ?>">
                                        <?php echo htmlspecialchars($type['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editBuilding" class="form-label">Tòa nhà</label>
                            <select class="form-select" id="editBuilding" required>
                                <option value="Cơ sở 1">Cơ sở 1</option>
                                <option value="Cơ sở 2">Cơ sở 2</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editFloor" class="form-label">Tầng</label>
                            <input type="number" class="form-control" id="editFloor" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="editStatus" class="form-label">Trạng thái</label>
                            <select class="form-select" id="editStatus" required>
                                <option value="available">Hoạt động</option>
                                <option value="maintenance">Bảo trì</option>
                                <option value="unavailable">Tạm đóng</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editEquipmentStatus" class="form-label">Tình trạng thiết bị</label>
                            <select class="form-select" id="editEquipmentStatus" required>
                                <option value="Đầy đủ">Đầy đủ</option>
                                <option value="Bóng đèn bị hư">Bóng đèn bị hư</option>
                                <option value="Thiếu ghế">Thiếu ghế</option>
                                <option value="Hỏng máy lạnh">Hỏng máy lạnh</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" onclick="updateRoom()">Lưu thay đổi</button>
                </div>
            </div>
        </div>
    </div>

    <?php require '../components/footer.php'; ?>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
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

        // Add room function
        function addRoom() {
            const formData = {
                name: document.getElementById('roomName').value,
                room_type_id: document.getElementById('roomType').value,
                building: document.getElementById('building').value,
                floor: document.getElementById('floor').value
            };

            fetch('api/add_room.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Lỗi: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Đã xảy ra lỗi khi thêm phòng');
            });
        }

        // Edit room button handler
        document.querySelectorAll('.btn-primary').forEach(button => {
            button.addEventListener('click', function() {
                const roomId = this.getAttribute('data-room-id');
                const row = this.closest('tr');
                
                // Get room data from the row
                const roomName = row.querySelector('td:nth-child(2)').textContent.trim().split('\n')[0];
                const building = row.querySelector('td:nth-child(2) small').textContent.split('-')[0].trim();
                const floor = parseInt(row.querySelector('td:nth-child(2) small').textContent.split('Tầng')[1]);
                const roomType = row.querySelector('td:nth-child(3)').getAttribute('data-room-type-id');
                const status = row.querySelector('td:nth-child(4)').getAttribute('data-status');
                const equipmentStatus = row.querySelector('td:nth-child(5) span').textContent;

                // Populate edit modal
                document.getElementById('editRoomId').value = roomId;
                document.getElementById('editRoomName').value = roomName;
                document.getElementById('editRoomType').value = roomType;
                document.getElementById('editBuilding').value = building;
                document.getElementById('editFloor').value = floor;
                document.getElementById('editStatus').value = status;
                document.getElementById('editEquipmentStatus').value = equipmentStatus;
            });
        });

        // Update room function
        function updateRoom() {
            const formData = {
                id: document.getElementById('editRoomId').value,
                name: document.getElementById('editRoomName').value,
                room_type_id: document.getElementById('editRoomType').value,
                building: document.getElementById('editBuilding').value,
                floor: document.getElementById('editFloor').value,
                status: document.getElementById('editStatus').value,
                equipment_status: document.getElementById('editEquipmentStatus').value
            };

            fetch('api/update_room.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Lỗi: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Đã xảy ra lỗi khi cập nhật phòng');
            });
        }

        // Delete room function
        function deleteRoom(roomId) {
            if (confirm('Bạn có chắc chắn muốn xóa phòng này?')) {
                fetch('api/delete_room.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: roomId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Lỗi: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Đã xảy ra lỗi khi xóa phòng');
                });
            }
        }
    </script>
</body>
</html> 