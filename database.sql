-- ConsultEase database
-- Import this file in phpMyAdmin (Import tab) or run it in the MySQL console.

DROP DATABASE IF EXISTS consultease;
CREATE DATABASE consultease CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE consultease;

-- Users: both students and lecturers
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'lecturer') NOT NULL
);

-- Slots: consultation times a lecturer makes available
CREATE TABLE slots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lecturer_id INT NOT NULL,
    slot_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    venue VARCHAR(100) NOT NULL,
    FOREIGN KEY (lecturer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bookings: a student booking a slot
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slot_id INT NOT NULL,
    student_id INT NOT NULL,
    reason VARCHAR(255) NOT NULL,
    status ENUM('Pending', 'Confirmed', 'Declined', 'Cancelled') NOT NULL DEFAULT 'Pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (slot_id) REFERENCES slots(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Sample users (hashes made with PHP password_hash)
-- Lecturer password: lecturer123
-- Student password:  student123
INSERT INTO users (name, email, password, role) VALUES
('Dr. Siti Aminah', 'lecturer@consultease.test', '$2y$10$SqPuG7sCRmEkEy3wOTUlcuzDbGVd5VOG6CIO8SVyEVYj3nYLBT5qq', 'lecturer'),
('Ali Hassan', 'student1@consultease.test', '$2y$10$anSIqbvGfDEPe/g/7zM2Sun7seBqsJjQ7Ol9.g47JmhqU4rtCTdJK', 'student'),
('Mei Ling Tan', 'student2@consultease.test', '$2y$10$anSIqbvGfDEPe/g/7zM2Sun7seBqsJjQ7Ol9.g47JmhqU4rtCTdJK', 'student');
