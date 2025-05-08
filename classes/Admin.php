<?php
require_once __DIR__ . '/User.php';

class Admin extends User {
    protected $adminID;
    protected $managedSpaces = [];
    protected $conn;

    public function __construct($conn, $id = null, $fullName = null, $role = null, $username = null, $adminID = null) {
        parent::__construct($id, $fullName, $role, $username);
        $this->conn = $conn;
        $this->adminID = $adminID;
    }

    public function getProfile() {
        return [
            'id' => $this->id,
            'adminID' => $this->adminID,
            'fullName' => $this->fullName,
            'role' => $this->role,
            'username' => $this->username
        ];
    }

    /**
     * Load all users from the database
     */
    public function loadUsers() {
        $stmt = $this->conn->prepare("SELECT * FROM users ORDER BY id");
        $stmt->execute();
        $result = $stmt->get_result();
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        return $users;
    }

    /**
     * Load all rooms from the database
     */
    public function loadRooms() {
        $stmt = $this->conn->prepare("SELECT * FROM rooms ORDER BY id");
        $stmt->execute();
        $result = $stmt->get_result();
        $rooms = [];
        while ($row = $result->fetch_assoc()) {
            $rooms[] = $row;
        }
        return $rooms;
    }

    /**
     * Lock a room by setting its status to 'locked'
     */
    public function lockRoom($roomID) {
        $stmt = $this->conn->prepare("UPDATE rooms SET status = 'locked' WHERE id = ?");
        $stmt->bind_param("i", $roomID);
        return $stmt->execute();
    }

    /**
     * Unlock a room by setting its status to 'available'
     */
    public function unlockRoom($roomID) {
        $stmt = $this->conn->prepare("UPDATE rooms SET status = 'available' WHERE id = ?");
        $stmt->bind_param("i", $roomID);
        return $stmt->execute();
    }

    /**
     * Monitor room usage statistics
     */
    public function monitorUsage() {
        $stats = [];
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total_bookings FROM bookings");
        $stmt->execute();
        $stats['total_bookings'] = $stmt->get_result()->fetch_assoc()['total_bookings'];
        $stmt = $this->conn->prepare("SELECT COUNT(*) as active_bookings FROM bookings WHERE status IN ('pending', 'confirmed', 'checked_in')");
        $stmt->execute();
        $stats['active_bookings'] = $stmt->get_result()->fetch_assoc()['active_bookings'];
        $stmt = $this->conn->prepare("
            SELECT r.id, r.name, COUNT(b.id) as booking_count 
            FROM rooms r 
            LEFT JOIN bookings b ON r.id = b.room_id 
            GROUP BY r.id
        ");
        $stmt->execute();
        $stats['room_utilization'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        return $stats;
    }

    /**
     * Generate booking report
     */
    public function bookingReport() {
        $stmt = $this->conn->prepare("
            SELECT b.*, u.username, r.name as room_name 
            FROM bookings b 
            JOIN users u ON b.user_id = u.id 
            JOIN rooms r ON b.room_id = r.id 
            ORDER BY b.booking_date DESC, b.start_time DESC
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        $report = [];
        while ($row = $result->fetch_assoc()) {
            $report[] = $row;
        }
        return $report;
    }

    /**
     * Delete a user's booking
     */
    public function deleteUserBooking($userID, $reservationID) {
        $stmt = $this->conn->prepare("DELETE FROM bookings WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $reservationID, $userID);
        return $stmt->execute();
    }

    /**
     * Manage user actions (create, update, delete)
     */
    public function manageUser($action, $user, $userID = null) {
        switch ($action) {
            case 'create':
                $stmt = $this->conn->prepare("INSERT INTO users (username, name, user_type) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $user['username'], $user['name'], $user['user_type']);
                break;
            case 'update':
                if (!$userID) return false;
                $stmt = $this->conn->prepare("UPDATE users SET username = ?, name = ?, user_type = ? WHERE id = ?");
                $stmt->bind_param("sssi", $user['username'], $user['name'], $user['user_type'], $userID);
                break;
            case 'delete':
                if (!$userID) return false;
                $stmt = $this->conn->prepare("DELETE FROM users WHERE id = ?");
                $stmt->bind_param("i", $userID);
                break;
            default:
                return false;
        }
        return $stmt->execute();
    }

    /**
     * Manage room list (create, update, delete multiple rooms)
     */
    public function manageRoomList($rooms) {
        $success = true;
        $this->conn->begin_transaction();
        try {
            foreach ($rooms as $room) {
                if (isset($room['id'])) {
                    $stmt = $this->conn->prepare("UPDATE rooms SET name = ?, building = ?, floor = ?, capacity = ?, status = ? WHERE id = ?");
                    $stmt->bind_param("sssisi", $room['name'], $room['building'], $room['floor'], $room['capacity'], $room['status'], $room['id']);
                } else {
                    $stmt = $this->conn->prepare("INSERT INTO rooms (name, building, floor, capacity, status) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssis", $room['name'], $room['building'], $room['floor'], $room['capacity'], $room['status']);
                }
                if (!$stmt->execute()) {
                    throw new Exception("Failed to manage room");
                }
            }
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            $success = false;
        }
        return $success;
    }

    /**
     * Update a room (space) info in the database
     * $spaceData should be an associative array with keys: id, name, building, floor, capacity, status, etc.
     */
    public function updateSpaceList($spaceData) {
        if (!isset($spaceData['id'])) return false;
        $stmt = $this->conn->prepare("UPDATE rooms SET name = ?, building = ?, floor = ?, capacity = ?, status = ? WHERE id = ?");
        $stmt->bind_param(
            "sssisi",
            $spaceData['name'],
            $spaceData['building'],
            $spaceData['floor'],
            $spaceData['capacity'],
            $spaceData['status'],
            $spaceData['id']
        );
        return $stmt->execute();
    }
    // Optionally, you can add methods to add/remove rooms, fetch managed rooms, etc.
} 


