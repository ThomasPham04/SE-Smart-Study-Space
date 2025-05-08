<?php
session_start();
require_once '../config/db_connection.php';
require_once '../classes/User.php';

$db = new DbConnect();
$conn = $db->connect();
$user = new User($conn);
$user->logout();
header('Location: login.php');
exit();
?> 