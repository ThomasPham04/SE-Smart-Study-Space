<?php
session_start();
require_once '../config/db_connection.php';
require_once '../classes/Admin.php';

// Check if user is logged in and is an admin
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

// Handle user deletion
if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    // Prevent admin from deleting themselves
    if ($user_id != $_SESSION['user']['id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND user_type != 'admin'");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $success_message = "Người dùng đã được xóa thành công";
        } else {
            $error_message = "Không thể xóa người dùng này";
        }
    } else {
        $error_message = "Không thể xóa tài khoản admin của chính mình";
    }
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Records per page
$offset = ($page - 1) * $limit;

// Search and filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter_type = isset($_GET['type']) ? $_GET['type'] : '';

// Build query with search and filter
$where_clause = "WHERE 1=1";
if (!empty($search)) {
    $search = "%$search%";
    $where_clause .= " AND (username LIKE ? OR name LIKE ? OR email LIKE ?)";
}
if (!empty($filter_type)) {
    $where_clause .= " AND user_type = ?";
}

// Count total records for pagination
$count_query = "SELECT COUNT(*) as total FROM users $where_clause";
$stmt = $conn->prepare($count_query);
if (!empty($search) && !empty($filter_type)) {
    $stmt->bind_param("ssss", $search, $search, $search, $filter_type);
} elseif (!empty($search)) {
    $stmt->bind_param("sss", $search, $search, $search);
} elseif (!empty($filter_type)) {
    $stmt->bind_param("s", $filter_type);
}
$stmt->execute();
$total_records = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

// Get users with pagination, search and filter
$query = "SELECT * FROM users $where_clause ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
if (!empty($search) && !empty($filter_type)) {
    $stmt->bind_param("ssssii", $search, $search, $search, $filter_type, $limit, $offset);
} elseif (!empty($search)) {
    $stmt->bind_param("sssii", $search, $search, $search, $limit, $offset);
} elseif (!empty($filter_type)) {
    $stmt->bind_param("sii", $filter_type, $limit, $offset);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$users = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý người dùng - BKSpace</title>
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
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="card-title mb-0">Quản lý người dùng</h4>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                <i class="bi bi-person-plus"></i> Thêm người dùng
                            </button>
                        </div>

                        <?php if (isset($success_message)): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                        <?php endif; ?>
                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                        <?php endif; ?>

                        <!-- Search and Filter -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <form class="d-flex gap-2" method="GET">
                                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Tìm kiếm theo tên, email..." value="<?php echo htmlspecialchars($search); ?>">
                                    <select name="type" class="form-select form-select-sm" style="width: 100px;">
                                        <option value="">Tất cả</option>
                                        <option value="admin" <?php echo $filter_type === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                        <option value="student" <?php echo $filter_type === 'student' ? 'selected' : ''; ?>>Sinh viên</option>
                                        <option value="staff" <?php echo $filter_type === 'staff' ? 'selected' : ''; ?>>Nhân viên</option>
                                    </select>
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Users Table -->
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tên đăng nhập</th>
                                        <th>Họ tên</th>
                                        <th>Email</th>
                                        <th>Vai trò</th>
                                        <th>Ngày tạo</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($user = $users->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td>
                                                <span class="badge <?php 
                                                    echo match($user['user_type']) {
                                                        'admin' => 'bg-danger',
                                                        'student' => 'bg-primary',
                                                        'staff' => 'bg-success',
                                                        default => 'bg-secondary'
                                                    };
                                                ?>">
                                                    <?php echo htmlspecialchars($user['user_type']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-primary" 
                                                            onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <?php if ($user['id'] != $_SESSION['user']['id']): ?>
                                                        <form method="POST" class="d-inline" 
                                                              onsubmit="return confirm('Bạn có chắc chắn muốn xóa người dùng này?');">
                                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                            <button type="submit" name="delete_user" class="btn btn-danger">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Page navigation" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($filter_type); ?>">
                                            Trước
                                        </a>
                                    </li>
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($filter_type); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($filter_type); ?>">
                                            Sau
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

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm người dùng mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addUserForm" method="POST" action="add_user.php">
                        <div class="mb-3">
                            <label for="new_username" class="form-label">Tên đăng nhập</label>
                            <input type="text" class="form-control" id="new_username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Mật khẩu</label>
                            <input type="password" class="form-control" id="new_password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_name" class="form-label">Họ tên</label>
                            <input type="text" class="form-control" id="new_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="new_email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_user_type" class="form-label">Vai trò</label>
                            <select class="form-select" id="new_user_type" name="user_type" required>
                                <option value="student">Sinh viên</option>
                                <option value="staff">Nhân viên</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Thêm người dùng</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Chỉnh sửa thông tin người dùng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editUserForm" method="POST" action="update_user.php">
                        <input type="hidden" id="edit_user_id" name="user_id">
                        <div class="mb-3">
                            <label for="edit_username" class="form-label">Tên đăng nhập</label>
                            <input type="text" class="form-control" id="edit_username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Họ tên</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_user_type" class="form-label">Vai trò</label>
                            <select class="form-select" id="edit_user_type" name="user_type" required>
                                <option value="student">Sinh viên</option>
                                <option value="staff">Nhân viên</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_password" class="form-label">Mật khẩu mới (để trống nếu không thay đổi)</label>
                            <input type="password" class="form-control" id="edit_password" name="password">
                        </div>
                        <button type="submit" class="btn btn-primary">Cập nhật</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include '../components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editUser(user) {
            document.getElementById('edit_user_id').value = user.id;
            document.getElementById('edit_username').value = user.username;
            document.getElementById('edit_name').value = user.name;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_user_type').value = user.user_type;
            document.getElementById('edit_password').value = '';
            
            new bootstrap.Modal(document.getElementById('editUserModal')).show();
        }
    </script>
</body>
</html> 