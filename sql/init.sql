-- Create database first (change name if you like)
CREATE DATABASE IF NOT EXISTS attendance_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE attendance_db;

-- Users table (admin/teacher)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Students table
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_name VARCHAR(100) NOT NULL,
    roll_no VARCHAR(50) NOT NULL UNIQUE,
    class VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Attendance table
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('Present', 'Absent') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_attendance (student_id, date),
    CONSTRAINT fk_student FOREIGN KEY (student_id) REFERENCES students (id) ON DELETE CASCADE
);

-- Seed admin: username=admin, password=admin123 (will be hashed below)
INSERT INTO
    users (username, password, full_name)
VALUES (
        'admin',
        '$2y$12$1b3LKJnbyekHYfdEhLCpEuaVEdtj79t/2vIiYt.33/w.1lmy.H2Va',
        'System Administrator'
    )
ON DUPLICATE KEY UPDATE
    username = username;
-- (Hash above is for 'admin123')