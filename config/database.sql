-- Create the database
CREATE DATABASE IF NOT EXISTS bkspace;
USE bkspace;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    user_type ENUM('admin', 'student', 'staff') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Room types table
CREATE TABLE IF NOT EXISTS room_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    capacity INT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Equipment s
CREATE TABLE IF NOT EXISTS equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Rooms table
CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    room_type_id INT NOT NULL,
    building VARCHAR(50) NOT NULL,
    floor INT NOT NULL,
    status ENUM('available', 'maintenance', 'unavailable') DEFAULT 'available',
    equipment_status VARCHAR(100) DEFAULT 'Đầy đủ',
    last_maintenance_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (room_type_id) REFERENCES room_types(id)
);

-- Room equipment mapping table
CREATE TABLE IF NOT EXISTS room_equipment (
    room_id INT NOT NULL,
    equipment_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    status ENUM('good', 'needs_repair', 'broken') DEFAULT 'good',
    last_checked_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (room_id, equipment_id),
    FOREIGN KEY (room_id) REFERENCES rooms(id),
    FOREIGN KEY (equipment_id) REFERENCES equipment(id)
);

-- Maintenance logs table
CREATE TABLE IF NOT EXISTS maintenance_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    reported_by INT NOT NULL,
    issue_description TEXT NOT NULL,
    status ENUM('reported', 'in_progress', 'completed') DEFAULT 'reported',
    reported_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_date TIMESTAMP NULL,
    notes TEXT,
    FOREIGN KEY (room_id) REFERENCES rooms(id),
    FOREIGN KEY (reported_by) REFERENCES users(id)
);

-- Bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    room_id INT NOT NULL,
    booking_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    status ENUM('pending', 'confirmed', 'checked_in', 'completed', 'cancelled') DEFAULT 'confirmed',
    access_code VARCHAR(32) NULL,
    access_generated_at TIMESTAMP NULL,
    checkin_time TIMESTAMP NULL,
    checkout_time TIMESTAMP NULL,
    cancelled_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (room_id) REFERENCES rooms(id)
);

-- Insert default admin user
INSERT INTO users (username, password, name, email, user_type) 
VALUES ('admin', 'admin123', 'Admin User', 'admin@bkspace.com', 'admin');

-- Insert room types
INSERT INTO room_types (name, capacity, description) VALUES
('Phòng học 1 người', 1, 'Phòng học cá nhân'),
('Phòng học nhóm 2', 2, 'Phòng học nhóm 2 người'),
('Phòng học nhóm 3', 3, 'Phòng học nhóm 3 người'),
('Phòng học nhóm 4', 4, 'Phòng học nhóm 4 người'),
('Phòng học nhóm 5', 5, 'Phòng học nhóm 5 người'),
('Phòng học nhóm 6', 6, 'Phòng học nhóm 6 người');

-- Insert basic equipment types
INSERT INTO equipment (name, description) VALUES
('Bàn', 'Bàn học tiêu chuẩn'),
('Ghế', 'Ghế học tiêu chuẩn'),
('Máy lạnh', 'Máy lạnh 1.5HP'),
('Đèn', 'Đèn LED chiếu sáng'),
('Ổ cắm điện', 'Ổ cắm điện đa năng'),
('Bảng trắng', 'Bảng trắng từ tính');

-- Insert rooms for Cơ sở 1
INSERT INTO rooms (name, room_type_id, building, floor, equipment_status) VALUES
-- Cơ sở 1 - Tầng 1
('Phòng 101', 1, 'Cơ sở 1', 1, 'Đầy đủ'),
('Phòng 102', 2, 'Cơ sở 1', 1, 'Đầy đủ'),
('Phòng 103', 3, 'Cơ sở 1', 1, 'Đầy đủ'),
('Phòng 104', 4, 'Cơ sở 1', 1, 'Đầy đủ'),
('Phòng 105', 5, 'Cơ sở 1', 1, 'Đầy đủ'),
('Phòng 106', 6, 'Cơ sở 1', 1, 'Đầy đủ'),
-- Cơ sở 1 - Tầng 2
('Phòng 201', 1, 'Cơ sở 1', 2, 'Đầy đủ'),
('Phòng 202', 2, 'Cơ sở 1', 2, 'Bóng đèn bị hư'),
('Phòng 203', 3, 'Cơ sở 1', 2, 'Đầy đủ'),
('Phòng 204', 4, 'Cơ sở 1', 2, 'Thiếu ghế'),
('Phòng 205', 5, 'Cơ sở 1', 2, 'Đầy đủ'),
('Phòng 206', 6, 'Cơ sở 1', 2, 'Đầy đủ');

-- Insert rooms for Cơ sở 2
INSERT INTO rooms (name, room_type_id, building, floor, equipment_status) VALUES
-- Cơ sở 2 - Tầng 1
('Phòng 101', 1, 'Cơ sở 2', 1, 'Đầy đủ'),
('Phòng 102', 2, 'Cơ sở 2', 1, 'Đầy đủ'),
('Phòng 103', 3, 'Cơ sở 2', 1, 'Hỏng máy lạnh'),
('Phòng 104', 4, 'Cơ sở 2', 1, 'Đầy đủ'),
('Phòng 105', 5, 'Cơ sở 2', 1, 'Đầy đủ'),
('Phòng 106', 6, 'Cơ sở 2', 1, 'Đầy đủ'),
-- Cơ sở 2 - Tầng 2
('Phòng 201', 1, 'Cơ sở 2', 2, 'Đầy đủ'),
('Phòng 202', 2, 'Cơ sở 2', 2, 'Đầy đủ'),
('Phòng 203', 3, 'Cơ sở 2', 2, 'Đầy đủ'),
('Phòng 204', 4, 'Cơ sở 2', 2, 'Đầy đủ'),
('Phòng 205', 5, 'Cơ sở 2', 2, 'Đầy đủ'),
('Phòng 206', 6, 'Cơ sở 2', 2, 'Đầy đủ');
