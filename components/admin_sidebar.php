<?php
// Admin Sidebar Navigation Component
?>
<div class="card">
    <div class="card-body">
        <h5 class="card-title mb-3">Admin Menu</h5>
        <div class="list-group">
            <a href="admin.php" class="list-group-item list-group-item-action<?php if(basename($_SERVER['PHP_SELF']) == 'admin.php') echo ' active'; ?>">
                <i class="bi bi-speedometer2 me-2"></i>Dashboard
            </a>
            <a href="manage_users.php" class="list-group-item list-group-item-action<?php if(basename($_SERVER['PHP_SELF']) == 'manage_users.php') echo ' active'; ?>">
                <i class="bi bi-people me-2"></i>Quản lý người dùng
            </a>
            <a href="manage_bookings.php" class="list-group-item list-group-item-action<?php if(basename($_SERVER['PHP_SELF']) == 'manage_bookings.php') echo ' active'; ?>">
                <i class="bi bi-calendar-check me-2"></i>Quản lý đặt phòng
            </a>
            <a href="manage_rooms.php" class="list-group-item list-group-item-action<?php if(basename($_SERVER['PHP_SELF']) == 'manage_rooms.php') echo ' active'; ?>">
                <i class="bi bi-building me-2"></i>Quản lý phòng
            </a>
            <a href="admin_settings.php" class="list-group-item list-group-item-action<?php if(basename($_SERVER['PHP_SELF']) == 'admin_settings.php') echo ' active'; ?>">
                <i class="bi bi-gear me-2"></i>Cài đặt
            </a>
        </div>
    </div>
</div> 