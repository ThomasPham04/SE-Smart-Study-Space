<?php
class User {
    protected $fullName;
    protected $role;
    protected $id;
    protected $username;

    public function __construct($id = null, $fullName = null, $role = null, $username = null) {
        $this->id = $id;
        $this->fullName = $fullName;
        $this->role = $role;
        $this->username = $username;
    }

    public function login($username, $password, $login_type) {
        require_once __DIR__ . '/../config/db_connection.php';
        $db = new DbConnect();
        $conn = $db->connect();

        if ($login_type === 'admin') {
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ? AND user_type = 'admin'");
        } else {
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ? AND user_type != 'admin'");
        }
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            $this->id = $user['id'];
            $this->fullName = $user['name'];
            $this->role = $user['user_type'];
            $this->username = $user['username'];
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'user_type' => $user['user_type'],
                'username' => $user['username']
            ];
            return true;
        }
        return false;
    }

    public function logout() {
        session_destroy();
    }

    public function getProfile() {
        return [
            'id' => $this->id,
            'fullName' => $this->fullName,
            'role' => $this->role,
            'username' => $this->username
        ];
    }
}

