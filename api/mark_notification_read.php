<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Notification.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$notificationId = $data['notification_id'] ?? null;

if (!$notificationId) {
    echo json_encode(['success' => false, 'message' => 'Notification ID is required']);
    exit;
}

$notification = new Notification($conn);
$success = $notification->markAsRead($notificationId);

echo json_encode(['success' => $success]); 