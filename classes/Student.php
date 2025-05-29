<?php
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/Notification.php';
require_once __DIR__ . '/../config/config.php';

class Student extends User {
    protected $studentID;
    protected $phoneNumber;
    protected $bookedReservations = [];
    protected $notification;

    public function __construct($conn, $id = null, $fullName = null, $role = null, $username = null, $studentID = null, $phoneNumber = null) {
        parent::__construct($conn, $id, $fullName, $role, $username);
        $this->studentID = $studentID;
        $this->phoneNumber = $phoneNumber;
        $this->notification = new Notification($conn);
    }

    public function makeReservation($room_id, $start_date, $start_time, $end_time) {
        $user_id = $this->id;
        // Check if room is available
        $check_stmt = $this->conn->prepare("
            SELECT COUNT(*) as count FROM bookings 
            WHERE room_id = ? 
            AND booking_date = ?
            AND (
                (start_time <= ? AND end_time >= ?) OR
                (start_time <= ? AND end_time >= ?) OR
                (start_time >= ? AND end_time <= ?)
            )
        ");
        $check_stmt->bind_param("isssssss", $room_id, $start_date, $start_time, $start_time, $end_time, $end_time, $start_time, $end_time);
        $check_stmt->execute();
        $result = $check_stmt->get_result()->fetch_assoc();
        if ($result['count'] > 0) {
            return false;
        }

        // Get room information for the notification
        $room_stmt = $this->conn->prepare("SELECT name, building FROM rooms WHERE id = ?");
        $room_stmt->bind_param("i", $room_id);
        $room_stmt->execute();
        $room = $room_stmt->get_result()->fetch_assoc();

        // Create booking
        $insert_stmt = $this->conn->prepare("
            INSERT INTO bookings (user_id, room_id, booking_date, start_time, end_time, status) 
            VALUES (?, ?, ?, ?, ?, 'pending')
        ");
        $insert_stmt->bind_param("iisss", $user_id, $room_id, $start_date, $start_time, $end_time);
        $success = $insert_stmt->execute();

        if ($success) {
            // Create notification for the student
            $booking_id = $this->conn->insert_id;
            $message = sprintf(
                "Đặt phòng %s tại %s vào ngày %s từ %s đến %s đang chờ xác nhận",
                $room['name'],
                $room['building'],
                $start_date,
                $start_time,
                $end_time
            );
            $this->notification->create(
                $user_id,
                $message,
                'info',
                BASE_URL . 'views/student/booking-history.php'
            );

            // Create notification for admin
            $admin_stmt = $this->conn->prepare("SELECT id FROM users WHERE user_type = 'admin'");
            $admin_stmt->execute();
            $admin_result = $admin_stmt->get_result();
            while ($admin = $admin_result->fetch_assoc()) {
                $admin_message = sprintf(
                    "Có yêu cầu đặt phòng mới: Phòng %s tại %s vào ngày %s từ %s đến %s",
                    $room['name'],
                    $room['building'],
                    $start_date,
                    $start_time,
                    $end_time
                );
                $this->notification->create(
                    $admin['id'],
                    $admin_message,
                    'warning',
                    BASE_URL . 'views/admin/manage_bookings.php'
                );
            }
        }

        return $success;
    }

    public function cancelReservation($booking_id) {
        $user_id = $this->id;
        // Only allow cancel if booking belongs to user and is pending/confirmed
        $check_stmt = $this->conn->prepare("
            SELECT * FROM bookings WHERE id = ? AND user_id = ? AND (status = 'pending' OR status = 'confirmed')
        ");
        $check_stmt->bind_param("ii", $booking_id, $user_id);
        $check_stmt->execute();
        $booking = $check_stmt->get_result()->fetch_assoc();
        if (!$booking) return false;
        $update_stmt = $this->conn->prepare("
            UPDATE bookings SET status = 'cancelled', cancelled_at = NOW() WHERE id = ?
        ");
        $update_stmt->bind_param("i", $booking_id);
        return $update_stmt->execute();
    }

    public function checkIn($booking_id) {
        $user_id = $this->id;
        // Only allow check-in if booking belongs to user and is confirmed
        $check_stmt = $this->conn->prepare("
            SELECT * FROM bookings WHERE id = ? AND user_id = ? AND status = 'confirmed'
        ");
        $check_stmt->bind_param("ii", $booking_id, $user_id);
        $check_stmt->execute();
        $booking = $check_stmt->get_result()->fetch_assoc();
        if (!$booking) return false;
        $update_stmt = $this->conn->prepare("
            UPDATE bookings SET status = 'checked_in', checkin_time = NOW() WHERE id = ?
        ");
        $update_stmt->bind_param("i", $booking_id);
        return $update_stmt->execute();
    }

    public function checkOut($booking_id) {
        $user_id = $this->id;
        // Only allow check-out if booking belongs to user and is checked_in/confirmed
        $check_stmt = $this->conn->prepare("
            SELECT * FROM bookings WHERE id = ? AND user_id = ? AND (status = 'checked_in' OR status = 'confirmed')
        ");
        $check_stmt->bind_param("ii", $booking_id, $user_id);
        $check_stmt->execute();
        $booking = $check_stmt->get_result()->fetch_assoc();
        if (!$booking) return false;
        $update_stmt = $this->conn->prepare("
            UPDATE bookings SET status = 'completed', checkout_time = NOW() WHERE id = ?
        ");
        $update_stmt->bind_param("i", $booking_id);
        return $update_stmt->execute();
    }

    public function viewBookingHistory() {
        $user_id = $this->id;
        $stmt = $this->conn->prepare("SELECT * FROM bookings WHERE user_id = ? ORDER BY booking_date DESC, start_time DESC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $history = [];
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
        return $history;
    }
} 