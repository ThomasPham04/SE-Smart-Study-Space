<?php
class Notification {
    protected $conn;
    public $notificationId;
    public $userId;
    public $message;
    public $timestamp;

    public function __construct($conn, $notificationId = null, $userId = null, $message = null, $timestamp = null) {
        $this->conn = $conn;
        $this->notificationId = $notificationId;
        $this->userId = $userId;
        $this->message = $message;
        $this->timestamp = $timestamp;
    }

    public function send() {
        // Example: Insert notification into DB
        // $stmt = $this->conn->prepare("INSERT INTO notifications (user_id, message, timestamp) VALUES (?, ?, NOW())");
        // $stmt->bind_param("is", $this->userId, $this->message);
        // return $stmt->execute();
        return true;
    }

    public function schedule($time) {
        // Example: Schedule notification for a future time
        // $stmt = $this->conn->prepare("INSERT INTO notifications (user_id, message, timestamp) VALUES (?, ?, ?)");
        // $stmt->bind_param("iss", $this->userId, $this->message, $time);
        // return $stmt->execute();
        return true;
    }

    public function archive() {
        // Example: Mark notification as archived in DB
        // $stmt = $this->conn->prepare("UPDATE notifications SET archived = 1 WHERE id = ?");
        // $stmt->bind_param("i", $this->notificationId);
        // return $stmt->execute();
        return true;
    }
} 