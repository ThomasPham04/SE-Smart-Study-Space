<?php
class AdminController {
    private $admin;
    private $db;

    public function __construct() {
        $this->db = new DbConnect();
        $conn = $this->db->connect();
        
        // Check if user is logged in and is admin
        if (!isset($_SESSION['user']) || $_SESSION['user']['user_type'] !== 'admin') {
            header('Location: /login');
            exit();
        }

        $user = $_SESSION['user'];
        $this->admin = new Admin($conn, $user['id'], $user['name'], $user['user_type'], $user['username'] ?? null, $user['id']);
    }

    public function index() {
        $adminProfile = $this->admin->getProfile();
        require_once 'views/admin/dashboard.php';
    }

    public function manageBookings() {
        // Get all bookings with user and room details
        $bookings = $this->admin->bookingReport();

        // Handle deletion if delete_id is set
        if (isset($_POST['delete_id'])) {
            $delete_id = intval($_POST['delete_id']);
            $user_id = $bookings[array_search($delete_id, array_column($bookings, 'id'))]['user_id'] ?? null;
            
            if ($user_id && $this->admin->deleteUserBooking($user_id, $delete_id)) {
                $_SESSION['success_message'] = "Đã hủy đặt phòng thành công!";
            } else {
                $_SESSION['error_message'] = "Có lỗi xảy ra khi hủy đặt phòng!";
            }
            
            header('Location: /admin/manage-bookings');
            exit();
        }

        // Pagination setup
        $bookings_per_page = 10;
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $total_bookings = count($bookings);
        $total_pages = ceil($total_bookings / $bookings_per_page);
        $offset = ($page - 1) * $bookings_per_page;
        $bookings_page = array_slice($bookings, $offset, $bookings_per_page);

        require_once 'views/admin/manage_bookings.php';
    }

    public function manageRooms() {
        // Add room management logic here
        require_once 'views/admin/manage_rooms.php';
    }

    public function manageUsers() {
        // Add user management logic here
        require_once 'views/admin/manage_users.php';
    }
} 