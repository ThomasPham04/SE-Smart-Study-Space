<?php
// Get the current page name to highlight active menu item
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="card">
    <div class="card-body">
        <h5 class="card-title">Admin Menu</h5>
        <div class="list-group">
            <a href="admin.php" class="list-group-item list-group-item-action <?php echo $current_page === 'admin.php' ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="manage_users.php" class="list-group-item list-group-item-action <?php echo $current_page === 'manage_users.php' ? 'active' : ''; ?>">
                <i class="bi bi-people"></i> Quản lý người dùng
            </a>
            <a href="manage_rooms.php" class="list-group-item list-group-item-action <?php echo $current_page === 'manage_rooms.php' ? 'active' : ''; ?>">
                <i class="bi bi-building"></i> Quản lý phòng
            </a>
            <a href="manage_bookings.php" class="list-group-item list-group-item-action <?php echo $current_page === 'manage_bookings.php' ? 'active' : ''; ?>">
                <i class="bi bi-calendar-check"></i> Quản lý đặt phòng
            </a>
        </div>
    </div>
</div> 