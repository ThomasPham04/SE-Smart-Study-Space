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

-- Rooms table
CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    capacity INT NOT NULL,
    room_type ENUM('single', 'group_2', 'group_3', 'group_4', 'group_5', 'group_6') NOT NULL,
    building VARCHAR(50) NOT NULL,
    floor INT NOT NULL,
    status ENUM('available', 'maintenance', 'unavailable') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    room_id INT NOT NULL,
    booking_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (room_id) REFERENCES rooms(id)
);

-- Insert default admin user with password 'admin123'
INSERT INTO users (username, password, name, email, user_type) 
VALUES ('admin', 'admin123', 'Admin User', 'admin@bkspace.com', 'admin');

-- Insert sample rooms for Cơ sở 1
INSERT INTO rooms (name, capacity, room_type, building, floor) VALUES
-- Cơ sở 1 - Tầng 1
('Phòng 101', 1, 'single', 'Cơ sở 1', 1),
('Phòng 102', 2, 'group_2', 'Cơ sở 1', 1),
('Phòng 103', 3, 'group_3', 'Cơ sở 1', 1),
('Phòng 104', 4, 'group_4', 'Cơ sở 1', 1),
('Phòng 105', 5, 'group_5', 'Cơ sở 1', 1),
('Phòng 106', 6, 'group_6', 'Cơ sở 1', 1),
-- Cơ sở 1 - Tầng 2
('Phòng 201', 1, 'single', 'Cơ sở 1', 2),
('Phòng 202', 2, 'group_2', 'Cơ sở 1', 2),
('Phòng 203', 3, 'group_3', 'Cơ sở 1', 2),
('Phòng 204', 4, 'group_4', 'Cơ sở 1', 2),
('Phòng 205', 5, 'group_5', 'Cơ sở 1', 2),
('Phòng 206', 6, 'group_6', 'Cơ sở 1', 2),
-- Cơ sở 1 - Tầng 3
('Phòng 301', 1, 'single', 'Cơ sở 1', 3),
('Phòng 302', 2, 'group_2', 'Cơ sở 1', 3),
('Phòng 303', 3, 'group_3', 'Cơ sở 1', 3),
('Phòng 304', 4, 'group_4', 'Cơ sở 1', 3),
('Phòng 305', 5, 'group_5', 'Cơ sở 1', 3),
('Phòng 306', 6, 'group_6', 'Cơ sở 1', 3);

-- Insert sample rooms for Cơ sở 2
INSERT INTO rooms (name, capacity, room_type, building, floor) VALUES
-- Cơ sở 2 - Tầng 1
('Phòng 101', 1, 'single', 'Cơ sở 2', 1),
('Phòng 102', 2, 'group_2', 'Cơ sở 2', 1),
('Phòng 103', 3, 'group_3', 'Cơ sở 2', 1),
('Phòng 104', 4, 'group_4', 'Cơ sở 2', 1),
('Phòng 105', 5, 'group_5', 'Cơ sở 2', 1),
('Phòng 106', 6, 'group_6', 'Cơ sở 2', 1),
-- Cơ sở 2 - Tầng 2
('Phòng 201', 1, 'single', 'Cơ sở 2', 2),
('Phòng 202', 2, 'group_2', 'Cơ sở 2', 2),
('Phòng 203', 3, 'group_3', 'Cơ sở 2', 2),
('Phòng 204', 4, 'group_4', 'Cơ sở 2', 2),
('Phòng 205', 5, 'group_5', 'Cơ sở 2', 2),
('Phòng 206', 6, 'group_6', 'Cơ sở 2', 2),
-- Cơ sở 2 - Tầng 3
('Phòng 301', 1, 'single', 'Cơ sở 2', 3),
('Phòng 302', 2, 'group_2', 'Cơ sở 2', 3),
('Phòng 303', 3, 'group_3', 'Cơ sở 2', 3),
('Phòng 304', 4, 'group_4', 'Cơ sở 2', 3),
('Phòng 305', 5, 'group_5', 'Cơ sở 2', 3),
('Phòng 306', 6, 'group_6', 'Cơ sở 2', 3);
