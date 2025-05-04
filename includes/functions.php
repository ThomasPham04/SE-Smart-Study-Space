/**
 * Set a notification message
 * @param string $message The notification message
 * @param string $type The type of notification (success, danger, warning, info)
 */
function setNotification($message, $type = 'success') {
    $_SESSION['notification'] = [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Get all notifications (most recent first)
 */
function getNotifications() {
    return array_reverse($_SESSION['notifications'] ?? []);
}

/**
 * Count unread notifications
 */
function countUnreadNotifications() {
    $notis = $_SESSION['notifications'] ?? [];
    return count(array_filter($notis, fn($n) => !$n['read']));
}

/**
 * Mark all notifications as read
 */
function markNotificationsAsRead() {
    if (isset($_SESSION['notifications'])) {
        foreach ($_SESSION['notifications'] as &$n) {
            $n['read'] = true;
        }
        unset($n);
    }
} 