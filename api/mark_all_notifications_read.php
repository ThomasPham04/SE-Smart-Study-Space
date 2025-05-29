<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Notification.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$notification = new Notification($conn);
$success = $notification->markAllAsRead($_SESSION['user']['user_id']);

echo json_encode(['success' => $success]); 