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
        $db = new DbConnect();
        $conn = $db->connect();

        // Check if username or email already exists for other users
        $stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $stmt->bind_param("ssi", $username, $email, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $_SESSION['error'] = "Username or email already exists";
        } else {
            // Update user
            if (!empty($password)) {
                // Update with new password
                $stmt = $conn->prepare("UPDATE users SET username = ?, password = ?, name = ?, email = ?, user_type = ? WHERE id = ?");
                $stmt->bind_param("sssssi", $username, $password, $name, $email, $user_type, $user_id);
            } else {
                // Update without changing password
                $stmt = $conn->prepare("UPDATE users SET username = ?, name = ?, email = ?, user_type = ? WHERE id = ?");
                $stmt->bind_param("ssssi", $username, $name, $email, $user_type, $user_id);
            }
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "User updated successfully";
            } else {
                $_SESSION['error'] = "Error updating user: " . $conn->error;
            }
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
}

// Redirect back to manage users page
header('Location: manage_users.php');
exit(); 