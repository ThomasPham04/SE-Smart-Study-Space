<?php
class Reservation {
    protected $conn;
    public $reservationId;
    public $userId;
    public $roomId;
    public $startTime;
    public $endTime;
    public $status;

    public function __construct($conn, $reservationId = null, $userId = null, $roomId = null, $startTime = null, $endTime = null, $status = null) {
        $this->conn = $conn;
        $this->reservationId = $reservationId;
        $this->userId = $userId;
        $this->roomId = $roomId;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->status = $status;
    }

    public function create() {
        // Example: Insert reservation into DB
        // $stmt = $this->conn->prepare("INSERT INTO bookings (user_id, room_id, booking_date, start_time, end_time, status) VALUES (?, ?, ?, ?, ?, ?)");
        // $stmt->bind_param("iissss", $this->userId, $this->roomId, $this->startTime, $this->startTime, $this->endTime, $this->status);
        // return $stmt->execute();
        return true;
    }

    public function cancel() {
        // Example: Update reservation status in DB
        // $stmt = $this->conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
        // $stmt->bind_param("i", $this->reservationId);
        // return $stmt->execute();
        return true;
    }

    public function getDetails() {
        return [
            'reservationId' => $this->reservationId,
            'userId' => $this->userId,
            'roomId' => $this->roomId,
            'startTime' => $this->startTime,
            'endTime' => $this->endTime,
            'status' => $this->status
        ];
    }
} 