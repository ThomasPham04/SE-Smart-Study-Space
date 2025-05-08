<?php
class User {
    protected $fullName;
    protected $role;
    protected $id;
    protected $username;
    protected $conn;

    public function __construct($conn, $id = null, $fullName = null, $role = null, $username = null) {
        $this->conn = $conn;
        $this->id = $id;
        $this->fullName = $fullName;
        $this->role = $role;
        $this->username = $username;
    }

    public function login($username, $password, $login_type) {
        if ($login_type === 'admin') {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ? AND password = ? AND user_type = 'admin'");
        } else {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ? AND password = ? AND user_type != 'admin'");
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

    public function getUserByUsername($username) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function createUserFromSSO($userData) {
        $stmt = $this->conn->prepare("INSERT INTO users (username, password, name, email, user_type) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", 
            $userData['username'],
            $userData['password'],
            $userData['full_name'],
            $userData['email'],
            $userData['user_type']
        );
        
        if ($stmt->execute()) {
            $user_id = $this->conn->insert_id;
            return $this->getUserByUsername($userData['username']);
        }
        return null;
    }

    public function loginWithSSO($username) {
        $user = $this->getUserByUsername($username);
        if ($user) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'name' => $user['name'],
                'email' => $user['email'],
                'user_type' => $user['user_type']
            ];
            return true;
        }
        return false;
    }
}

