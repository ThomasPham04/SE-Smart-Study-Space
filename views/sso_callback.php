<?php
session_start();
require_once '../config/db_connection.php';
require_once '../classes/User.php';

// Get the ticket from SSO
$ticket = $_GET['ticket'] ?? '';

if (empty($ticket)) {
    header('Location: login.php');
    exit();
}

// Validate the ticket with SSO
$service = urlencode('http://' . $_SERVER['HTTP_HOST'] . '/software_engineer/views/sso_callback.php');
$validate_url = "https://sso.hcmut.edu.vn/cas/serviceValidate?ticket={$ticket}&service={$service}";

$response = file_get_contents($validate_url);
if ($response === false) {
    $_SESSION['error'] = "Không thể xác thực với SSO HCMUT";
    header('Location: login.php');
    exit();
}

// Parse the XML response
$xml = simplexml_load_string($response);
if ($xml === false) {
    $_SESSION['error'] = "Lỗi xử lý phản hồi từ SSO";
    header('Location: login.php');
    exit();
}

// Check if authentication was successful
$ns = $xml->getNamespaces(true);
$serviceResponse = $xml->children($ns['cas']);
$authenticationSuccess = $serviceResponse->authenticationSuccess;

if (!$authenticationSuccess) {
    $_SESSION['error'] = "Xác thực SSO thất bại";
    header('Location: login.php');
    exit();
}

// Get user information
$username = (string)$authenticationSuccess->user;
$attributes = $authenticationSuccess->attributes;

// Connect to database
$db = new DbConnect();
$conn = $db->connect();

// Check if user exists
$userObj = new User($conn);
$user = $userObj->getUserByUsername($username);

if (!$user) {
    // Create new user from SSO
    $userData = [
        'username' => $username,
        'password' => bin2hex(random_bytes(16)), // Generate random password
        'email' => (string)$attributes->email ?? '',
        'full_name' => (string)$attributes->displayName ?? $username,
        'user_type' => 'student'
    ];
    
    $user = $userObj->createUserFromSSO($userData);
}

if ($user) {
    // Set session
    $_SESSION['user'] = [
        'id' => $user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'full_name' => $user['full_name'],
        'user_type' => $user['user_type']
    ];
    
    // Redirect to appropriate page
    if ($user['user_type'] === 'admin') {
        header('Location: admin/admin.php');
    } else {
        $redirect = $_SESSION['redirect_after_login'] ?? '../index.php';
        unset($_SESSION['redirect_after_login']);
        header('Location: ' . $redirect);
    }
    exit();
} else {
    $_SESSION['error'] = "Không thể tạo hoặc lấy thông tin người dùng";
    header('Location: login.php');
    exit();
} 