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
        return true;
    }

    public function cancel() {();
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