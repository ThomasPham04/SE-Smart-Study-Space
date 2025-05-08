<?php
session_start();
require_once '../config/db_connection.php';
require_once '../classes/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        // Call the Python SSO script
        $escaped_user = escapeshellarg($username);
        $escaped_pass = escapeshellarg($password);
        $command = "python3 ../scripts/mybk_sso_login.py $escaped_user $escaped_pass";
        $output = shell_exec($command);
        $result = json_decode($output, true);

        if ($result && $result['status'] === 'success') {
            $info = $result['full_info'];
            // Connect to database
            $db = new DbConnect();
            $conn = $db->connect();
            $userObj = new User($conn);

            // Check if user exists
            $user = $userObj->getUserByUsername($username);

            if (!$user) {
                // Create new user
                $userData = [
                    'username' => $username,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'email' => $info['orgEmail'] ?? '',
                    'full_name' => $info['lastName'] . ' ' . $info['firstName'],
                    'user_type' => 'student'
                ];
                $user = $userObj->createUserFromSSO($userData);
            }

            if ($user) {
                // Set session
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'user_type' => $user['user_type']
                ];

                // Redirect to appropriate page
                $redirect = $_SESSION['redirect_after_login'] ?? '../index.php';
                unset($_SESSION['redirect_after_login']);
                header('Location: ' . $redirect);
                exit();
            }
        } else {
            $error = "Đăng nhập thất bại. Vui lòng kiểm tra lại thông tin đăng nhập.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập MyBK - BKSpace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/styles.css" rel="stylesheet">
</head>
<body>
    <?php include '../components/header.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title text-center mb-4">Đăng nhập MyBK</h3>
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label">Tên đăng nhập SSO</label>
                                <input type="text" class="form-control" id="username" name="username" placeholder="VD: 2213370 hoặc email prefix" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Mật khẩu</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Đăng nhập</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../components/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 