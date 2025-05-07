<?php
require_once __DIR__ . '/User.php';

class Admin extends User {
    protected $adminID;
    protected $managedSpaces = [];

    public function __construct($id = null, $fullName = null, $role = null, $username = null, $adminID = null) {
        parent::__construct($id, $fullName, $role, $username);
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
     * Update a room (space) info in the database
     * $spaceData should be an associative array with keys: id, name, building, floor, capacity, status, etc.
     */
    public function updateSpaceList($spaceData) {
        require_once __DIR__ . '/../config/db_connection.php';
        $db = new DbConnect();
        $conn = $db->connect();
        if (!isset($spaceData['id'])) return false;
        $stmt = $conn->prepare("UPDATE rooms SET name = ?, building = ?, floor = ?, capacity = ?, status = ? WHERE id = ?");
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


