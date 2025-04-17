<?php
session_start();
require_once '../config/db_connection.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $user_type = $_POST['user_type'] ?? '';

    // Validate input
    $errors = [];
    if (empty($username)) $errors[] = "Username is required";
    if (empty($password)) $errors[] = "Password is required";
    if (empty($name)) $errors[] = "Name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    if (!in_array($user_type, ['admin', 'student', 'staff'])) $errors[] = "Invalid user type";

    if (empty($errors)) {
        $db = new DbConnect();
        $conn = $db->connect();

        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $_SESSION['error'] = "Username or email already exists";
        } else {
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (username, password, name, email, user_type) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $username, $password, $name, $email, $user_type);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "User added successfully";
            } else {
                $_SESSION['error'] = "Error adding user: " . $conn->error;
            }
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
}

// Redirect back to manage users page
header('Location: manage_users.php');
exit(); 