-- ===================================
-- TemanKosan Complete Database Schema
-- ===================================

SET FOREIGN_KEY_CHECKS = 0;

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS temankosan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE temankosan;

-- ===================================
-- Users Table
-- ===================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'owner', 'member') DEFAULT 'member',
    email_verified_at TINYINT(1) DEFAULT 0,
    profile_image VARCHAR(255),
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- Locations Table
-- ===================================
CREATE TABLE IF NOT EXISTS locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    city VARCHAR(100) NOT NULL,
    district VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_city (city),
    INDEX idx_district (district)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- Facilities Table
-- ===================================
CREATE TABLE IF NOT EXISTS facilities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    icon VARCHAR(50),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- Kos Table
-- ===================================
CREATE TABLE IF NOT EXISTS kos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL,
    location_id INT,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    address TEXT NOT NULL,
    price DECIMAL(10,0) NOT NULL,
    type ENUM('putra', 'putri', 'campur') NOT NULL,
    room_size VARCHAR(50),
    room_count INT DEFAULT 1,
    available_rooms INT DEFAULT 1,
    is_available TINYINT(1) DEFAULT 1,
    is_featured TINYINT(1) DEFAULT 0,
    status ENUM('draft', 'published', 'archived') DEFAULT 'published',
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    contact_person VARCHAR(100),
    contact_phone VARCHAR(20),
    rules TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE SET NULL,
    INDEX idx_location (location_id),
    INDEX idx_price (price),
    INDEX idx_type (type),
    INDEX idx_available (is_available),
    INDEX idx_featured (is_featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- Kos Images Table
-- ===================================
CREATE TABLE IF NOT EXISTS kos_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kos_id INT NOT NULL,
    image_url VARCHAR(500) NOT NULL,
    is_primary TINYINT(1) DEFAULT 0,
    caption VARCHAR(255),
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kos_id) REFERENCES kos(id) ON DELETE CASCADE,
    INDEX idx_kos_primary (kos_id, is_primary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- Kos Facilities Table (Junction)
-- ===================================
CREATE TABLE IF NOT EXISTS kos_facilities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kos_id INT NOT NULL,
    facility_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kos_id) REFERENCES kos(id) ON DELETE CASCADE,
    FOREIGN KEY (facility_id) REFERENCES facilities(id) ON DELETE CASCADE,
    UNIQUE KEY unique_kos_facility (kos_id, facility_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- Bookings Table
-- ===================================
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_code VARCHAR(50) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    kos_id INT NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    check_in_date DATE NOT NULL,
    check_out_date DATE,
    duration_months INT NOT NULL DEFAULT 1,
    subtotal DECIMAL(10,0) NOT NULL DEFAULT 0,
    admin_fee DECIMAL(10,0) NOT NULL DEFAULT 50000,
    discount_amount DECIMAL(10,0) DEFAULT 0,
    total_price DECIMAL(10,0) NOT NULL,
    total_amount DECIMAL(10,0) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    booking_status ENUM('pending', 'confirmed', 'active', 'completed', 'cancelled', 'expired') DEFAULT 'pending',
    notes TEXT,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    confirmed_at DATETIME,
    cancelled_at DATETIME,
    cancelled_reason TEXT,
    expires_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (kos_id) REFERENCES kos(id) ON DELETE CASCADE,
    INDEX idx_booking_code (booking_code),
    INDEX idx_user (user_id),
    INDEX idx_kos (kos_id),
    INDEX idx_status (booking_status),
    INDEX idx_payment_status (payment_status),
    INDEX idx_check_in (check_in_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- Reviews Table
-- ===================================
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kos_id INT NOT NULL,
    user_id INT NOT NULL,
    booking_id INT,
    rating TINYINT(1) NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    is_approved TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (kos_id) REFERENCES kos(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
    INDEX idx_kos_approved (kos_id, is_approved),
    INDEX idx_rating (rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- User Activities Table
-- ===================================
CREATE TABLE IF NOT EXISTS user_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_action (user_id, action),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- Testimonials Table (from existing setup)
-- ===================================
CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    kos_name VARCHAR(200) NOT NULL,
    rating TINYINT(1) NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT NOT NULL,
    is_approved TINYINT(1) DEFAULT 0,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_approved (is_approved),
    INDEX idx_rating (rating),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- Insert Sample Data
-- ===================================

-- Insert sample users
INSERT IGNORE INTO users (name, email, phone, password, role, email_verified_at) VALUES
('Admin System', 'admin@temankosan.com', '081234567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1),
('John Doe', 'john@email.com', '081234567891', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member', 1),
('Jane Smith', 'jane@email.com', '081234567892', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member', 1),
('Owner Kos', 'owner@email.com', '081234567893', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', 1);

-- Insert sample locations
INSERT IGNORE INTO locations (city, district) VALUES
('Jakarta', 'Senayan'),
('Jakarta', 'Kemang'),
('Jakarta', 'Menteng'),
('Bandung', 'Dago'),
('Bandung', 'Dipatiukur'),
('Yogyakarta', 'Malioboro'),
('Yogyakarta', 'Tugu');

-- Insert sample facilities
INSERT IGNORE INTO facilities (name, icon, description) VALUES
('WiFi Gratis', 'wifi', 'Internet WiFi berkecepatan tinggi'),
('AC', 'snowflake', 'Air Conditioner di setiap kamar'),
('Kamar Mandi Dalam', 'bath', 'Kamar mandi pribadi di dalam kamar'),
('Parkir', 'car', 'Area parkir motor/mobil'),
('Dapur Bersama', 'utensils', 'Dapur bersama dengan peralatan masak'),
('Laundry', 'tshirt', 'Layanan laundry/cuci'),
('CCTV', 'video', 'Keamanan dengan CCTV 24 jam'),
('Keamanan 24 Jam', 'shield-alt', 'Penjagaan keamanan 24 jam');

-- Insert sample kos
INSERT IGNORE INTO kos (owner_id, location_id, name, description, address, price, type, room_size, room_count, available_rooms) VALUES
(4, 1, 'Kos Melati Senayan', 'Kos nyaman dan strategis di kawasan Senayan', 'Jl. Senayan Raya No. 123, Jakarta Selatan', 2500000, 'campur', '3x4 meter', 20, 15),
(4, 2, 'Kos Mawar Kemang', 'Kos modern dengan fasilitas lengkap di Kemang', 'Jl. Kemang Raya No. 456, Jakarta Selatan', 3000000, 'putri', '4x4 meter', 15, 10),
(4, 3, 'Kos Anggrek Menteng', 'Kos premium di kawasan bisnis Menteng', 'Jl. Menteng Tengah No. 789, Jakarta Pusat', 4000000, 'putra', '4x5 meter', 12, 8),
(4, 4, 'Kos Dahlia Dago', 'Kos sejuk dengan pemandangan gunung di Dago', 'Jl. Ir. H. Djuanda No. 321, Bandung', 1800000, 'campur', '3x3 meter', 25, 20),
(4, 5, 'Kos Tulip Dipatiukur', 'Kos dekat ITB dengan suasana akademis', 'Jl. Dipatiukur No. 654, Bandung', 2200000, 'putra', '3x4 meter', 18, 12);

-- Insert sample kos images
INSERT IGNORE INTO kos_images (kos_id, image_url, is_primary, caption) VALUES
(1, 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=600&h=400&fit=crop', 1, 'Tampak depan Kos Melati'),
(2, 'https://images.unsplash.com/photo-1551361415-69c87624334f?w=600&h=400&fit=crop', 1, 'Kamar Kos Mawar'),
(3, 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?w=600&h=400&fit=crop', 1, 'Interior Kos Anggrek'),
(4, 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=600&h=400&fit=crop', 1, 'Pemandangan Kos Dahlia'),
(5, 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=600&h=400&fit=crop', 1, 'Ruang tamu Kos Tulip');

-- Insert sample kos facilities
INSERT IGNORE INTO kos_facilities (kos_id, facility_id) VALUES
(1, 1), (1, 3), (1, 4), (1, 5), (1, 7),
(2, 1), (2, 2), (2, 3), (2, 4), (2, 6), (2, 7), (2, 8),
(3, 1), (3, 2), (3, 3), (3, 4), (3, 5), (3, 6), (3, 7), (3, 8),
(4, 1), (4, 3), (4, 4), (4, 5), (4, 7),
(5, 1), (5, 2), (5, 3), (5, 4), (5, 5), (5, 7);

SET FOREIGN_KEY_CHECKS = 1;

-- ===================================
-- Update bookings table structure
-- ===================================
ALTER TABLE bookings 
ADD COLUMN IF NOT EXISTS total_amount DECIMAL(10,0) NOT NULL DEFAULT 0 AFTER total_price;

-- Update existing records to set total_amount = total_price if total_amount is 0
UPDATE bookings SET total_amount = total_price WHERE total_amount = 0;

-- ===================================
-- Success Message
-- ===================================
SELECT 'Database schema created successfully!' as message;