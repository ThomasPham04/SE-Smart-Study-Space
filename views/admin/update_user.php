<?php
session_start();
require_once '../../config/db_connection.php';
require_once '../../classes/Admin.php';

// Check if user is logged in and is an admin
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $user_type = $_POST['user_type'] ?? '';

    // Validate input
    $errors = [];
    if (empty($user_id)) $errors[] = "User ID is required";
    if (empty($username)) $errors[] = "Username is required";
    if (empty($name)) $errors[] = "Name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    if (!in_array($user_type, ['admin', 'student', 'staff'])) $errors[] = "Invalid user type";

    if (empty($errors)) {
        $userData = [
            'username' => $username,
            'name' => $name,
            'email' => $email,
            'user_type' => $user_type
        ];
        
        if (!empty($password)) {
            $userData['password'] = $password;
        }
        
        if ($admin->manageUser('update', $userData, $user_id)) {
            $_SESSION['success'] = "User updated successfully";
        } else {
            $_SESSION['error'] = "Error updating user";
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
}

// Redirect back to manage users page
header('Location: manage_users.php');
exit(); 