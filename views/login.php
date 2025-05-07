<?php
session_start();
require_once '../classes/User.php';

// Redirect if already logged in
if (isset($_SESSION['user'])) {
    if ($_SESSION['user']['user_type'] === 'admin') {
        header('Location: admin.php');
    } else {
        header('Location: ' . ($_SESSION['redirect_after_login'] ?? '../index.php'));
    }
    exit();
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $login_type = $_POST['login_type'] ?? '';

    if (!empty($username) && !empty($password)) {
        $userObj = new User();
        if ($userObj->login($username, $password, $login_type)) {
            if ($_SESSION['user']['user_type'] === 'admin') {
                header('Location: admin.php');
            } else {
                $redirect = $_SESSION['redirect_after_login'] ?? '../index.php';
                unset($_SESSION['redirect_after_login']);
                header('Location: ' . $redirect);
            }
            exit();
        } else {
            if ($login_type === 'admin') {
                $error = "Thông tin đăng nhập quản trị viên không đúng";
            } else {
                $error = "Tên đăng nhập hoặc mật khẩu không đúng";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - BKSpace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/styles.css" rel="stylesheet">
</head>
<body>
    <?php include '../components/header.php'; ?>
    
    <div class="login-page d-flex align-items-center justify-content-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card shadow-lg">
                        <div class="card-body p-5">
                            <div class="text-center mb-4">
                                <img src="../assets/img/logo.png" alt="BKSpace Logo" class="img-fluid mb-4" style="max-width: 120px;">
                                <h2 class="login-title">Đăng nhập với vai trò là:</h2>
                            </div>
                            
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                            <?php endif; ?>
                            
                            <div class="d-grid gap-3">
                                <a href="https://sso.hcmut.edu.vn/cas/login" target="_blank" class="btn btn-outline-primary p-3 role-option">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <i class="bi bi-mortarboard-fill me-2 fs-4"></i>
                                        <h3 class="mb-0">Sinh viên trường Đại học Bách Khoa (HCMUT)</h3>
                                    </div>
                                </a>
                                
                                <button type="button" class="btn btn-outline-primary p-3 role-option" data-bs-toggle="modal" data-bs-target="#adminLoginModal">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <i class="bi bi-person-workspace me-2 fs-4"></i>
                                        <h3 class="mb-0">Quản trị viên BKSpace</h3>
                                    </div>
                                </button>

                                <button type="button" class="btn btn-outline-secondary p-3 role-option" data-bs-toggle="modal" data-bs-target="#localUserModal">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <i class="bi bi-person-fill me-2 fs-4"></i>
                                        <h3 class="mb-0">User local</h3>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Login Modal -->
    <div class="modal fade" id="adminLoginModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Đăng nhập quản trị viên</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="">
                        <input type="hidden" name="login_type" value="admin">
                        <div class="mb-3">
                            <label for="username" class="form-label">Tên đăng nhập</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Mật khẩu</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Local User Login Modal -->
    <div class="modal fade" id="localUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Đăng nhập người dùng cục bộ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="">
                        <input type="hidden" name="login_type" value="local">
                        <div class="mb-3">
                            <label for="local_username" class="form-label">Tên đăng nhập</label>
                            <input type="text" class="form-control" id="local_username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="local_password" class="form-label">Mật khẩu</label>
                            <input type="password" class="form-control" id="local_password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
                        <div class="mt-3 text-center">
                            <small class="text-muted">Chưa có tài khoản? Vui lòng liên hệ quản trị viên</small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include '../components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 