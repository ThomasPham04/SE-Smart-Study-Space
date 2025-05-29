document.addEventListener('DOMContentLoaded', function() {
    const notificationDropdown = document.getElementById('notificationDropdown');
    const markAllReadBtn = document.querySelector('.mark-all-read');
    const notificationItems = document.querySelectorAll('.dropdown-item[data-notification-id]');

    // Mark single notification as read
    notificationItems.forEach(item => {
        item.addEventListener('click', function(e) {
            const notificationId = this.dataset.notificationId;
            if (notificationId) {
                markAsRead(notificationId);
            }
        });
    });

    // Mark all notifications as read
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function(e) {
            e.preventDefault();
            markAllAsRead();
        });
    }

    function markAsRead(notificationId) {
        fetch('api/mark_notification_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ notification_id: notificationId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateNotificationBadge();
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function markAllAsRead() {
        fetch('api/mark_all_notifications_read.php', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateNotificationBadge();
                // Remove bold from all notifications
                notificationItems.forEach(item => {
                    item.classList.remove('fw-bold');
                });
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function updateNotificationBadge() {
        const badge = document.querySelector('.badge');
        if (badge) {
            const currentCount = parseInt(badge.textContent);
            if (currentCount > 1) {
                badge.textContent = currentCount - 1;
            } else {
                badge.remove();
            }
        }
    }
}); 