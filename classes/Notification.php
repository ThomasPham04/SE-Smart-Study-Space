<?php
class Notification {
    protected $conn;
    public $id;
    public $user_id;
    public $message;
    public $type; // 'success', 'warning', 'info', 'error'
    public $is_read;
    public $link;
    public $created_at;
    public $updated_at;

    public function __construct($conn) {
        if (!$conn instanceof mysqli) {
            throw new Exception('Invalid database connection');
        }
        $this->conn = $conn;
    }

    public function create($user_id, $message, $type = 'info', $link = null) {
        if (!$this->conn) {
            throw new Exception('Database connection not available');
        }

        $sql = "INSERT INTO notifications (user_id, message, type, is_read, link, created_at) 
                VALUES (?, ?, ?, 0, ?, NOW())";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $this->conn->error);
        }

        $stmt->bind_param("isss", $user_id, $message, $type, $link);
        return $stmt->execute();
    }

    public function getUnreadCount($user_id) {
        if (!$this->conn) {
            throw new Exception('Database connection not available');
        }

        if (!$user_id) return 0;
        
        $sql = "SELECT COUNT(*) as count FROM notifications 
                WHERE user_id = ? AND is_read = 0";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $this->conn->error);
        }

        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'];
    }

    public function getRecentNotifications($user_id, $limit = 5) {
        if (!$this->conn) {
            throw new Exception('Database connection not available');
        }

        if (!$user_id) return [];
        
        $sql = "SELECT * FROM notifications 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $this->conn->error);
        }

        $stmt->bind_param("ii", $user_id, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $notifications = [];
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
        return $notifications;
    }

    public function markAsRead($id) {
        if (!$this->conn) {
            throw new Exception('Database connection not available');
        }

        $sql = "UPDATE notifications 
                SET is_read = 1 
                WHERE notification_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $this->conn->error);
        }

        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function markAllAsRead($user_id) {
        if (!$this->conn) {
            throw new Exception('Database connection not available');
        }

        if (!$user_id) return false;
        
        $sql = "UPDATE notifications 
                SET is_read = 1 
                WHERE user_id = ? AND is_read = 0";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $this->conn->error);
        }

        $stmt->bind_param("i", $user_id);
        return $stmt->execute();
    }

    public function delete($id) {
        if (!$this->conn) {
            throw new Exception('Database connection not available');
        }

        $sql = "DELETE FROM notifications 
                WHERE notification_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $this->conn->error);
        }

        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function formatTimestamp($timestamp) {
        $now = new DateTime();
        $notificationTime = new DateTime($timestamp);
        $interval = $now->diff($notificationTime);

        if ($interval->y > 0) {
            return $interval->y . ' năm trước';
        } elseif ($interval->m > 0) {
            return $interval->m . ' tháng trước';
        } elseif ($interval->d > 0) {
            return $interval->d . ' ngày trước';
        } elseif ($interval->h > 0) {
            return $interval->h . ' giờ trước';
        } elseif ($interval->i > 0) {
            return $interval->i . ' phút trước';
        } else {
            return 'Vừa xong';
        }
    }

    public function getIconByType($type) {
        switch ($type) {
            case 'success':
                return 'bi-calendar-check text-success';
            case 'warning':
                return 'bi-exclamation-circle text-warning';
            case 'error':
                return 'bi-x-circle text-danger';
            default:
                return 'bi-info-circle text-info';
        }
    }
} 