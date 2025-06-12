-- Mobile Barber Platform Database Schema

-- Drop existing tables if they exist
DROP TABLE IF EXISTS chat_messages;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS barber_profiles;
DROP TABLE IF EXISTS user_roles;
DROP TABLE IF EXISTS users;

-- Create users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT,
    profile_image VARCHAR(255) DEFAULT 'default.jpg',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active'
);

-- Create user_roles table
CREATE TABLE user_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    role ENUM('client', 'barber', 'admin') NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_role (user_id, role)
);

-- Create barber_profiles table
CREATE TABLE barber_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    bio TEXT,
    experience INT DEFAULT 0, -- Years of experience
    rating DECIMAL(3,2) DEFAULT 0.00,
    total_ratings INT DEFAULT 0,
    hourly_rate DECIMAL(10,2) NOT NULL,
    availability JSON, -- Store availability as JSON
    location_lat DECIMAL(10,8),
    location_lng DECIMAL(11,8),
    is_available BOOLEAN DEFAULT FALSE,
    portfolio JSON, -- Store portfolio images as JSON
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create services table
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    duration INT NOT NULL, -- Duration in minutes
    image VARCHAR(255),
    category VARCHAR(50) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create bookings table
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    barber_id INT NOT NULL,
    service_id INT NOT NULL,
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    location_address TEXT NOT NULL,
    location_lat DECIMAL(10,8),
    location_lng DECIMAL(11,8),
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (barber_id) REFERENCES barber_profiles(user_id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
);

-- Create payments table
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('card', 'bank_transfer', 'mobile_money') NOT NULL,
    transaction_id VARCHAR(100) UNIQUE,
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

-- Create chat_messages table
CREATE TABLE chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default admin user
INSERT INTO users (username, email, password, first_name, last_name, phone, address)
VALUES ('admin', 'admin@mobilebarber.com', '$2y$10$8tPjdlv.K4A/zRs.uKK.9OQP3GJAZYd5aDQRSwy5VT5uULxKUvl4e', 'Admin', 'User', '1234567890', 'Admin Office');

-- Set admin role
INSERT INTO user_roles (user_id, role) VALUES (1, 'admin');

-- Insert sample services
INSERT INTO services (name, description, price, duration, category) VALUES
('Basic Haircut', 'Standard haircut with clippers and scissors', 15.00, 30, 'Haircut'),
('Premium Haircut', 'Haircut with styling and hot towel treatment', 25.00, 45, 'Haircut'),
('Beard Trim', 'Shaping and trimming of facial hair', 10.00, 15, 'Facial'),
('Full Shave', 'Traditional straight razor shave with hot towel', 20.00, 30, 'Facial'),
('Kids Haircut', 'Haircut for children under 12', 12.00, 20, 'Haircut'),
('Hair & Beard Combo', 'Haircut with beard trim and styling', 30.00, 60, 'Combo');