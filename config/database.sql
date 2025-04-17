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
    room_type ENUM('single', 'group_2', 'group_4') NOT NULL,
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

-- Insert some sample rooms
INSERT INTO rooms (name, capacity, room_type, building, floor) VALUES
('Room 101', 1, 'single', 'Building A', 1),
('Room 102', 2, 'group_2', 'Building A', 1),
('Room 103', 4, 'group_4', 'Building B', 1),
('Room 201', 1, 'single', 'Building B', 2),
('Room 202', 2, 'group_2', 'Building A', 2),
('Room 203', 4, 'group_4', 'Building A', 2); 


