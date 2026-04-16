-- =============================================
-- DIU Roommate Finder - Database Schema
-- Run this SQL in phpMyAdmin to set up the tables
-- =============================================

-- Table: users
-- Stores all registered students
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,       -- Must be @diu.edu.bd
    password VARCHAR(255) NOT NULL,            -- Stored as hashed password
    is_admin TINYINT(1) DEFAULT 0,             -- 1 = admin, 0 = regular user
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: ads
-- Stores all roommate ads posted by users
CREATE TABLE IF NOT EXISTS ads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,                      -- Who posted this ad
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    rent DECIMAL(10,2) NOT NULL,               -- Monthly rent in BDT
    location VARCHAR(200) NOT NULL,            -- Required location
    google_location VARCHAR(500) DEFAULT NULL, -- Optional Google Maps location
    gender_tag ENUM('male', 'female', 'any') NOT NULL,  -- Who can apply
    room_type ENUM('full', 'shared') NOT NULL,           -- Full room or shared
    contact_phone VARCHAR(20) NOT NULL,
    whatsapp VARCHAR(20) DEFAULT NULL,         -- Optional WhatsApp number
    telegram VARCHAR(100) DEFAULT NULL,         -- Optional Telegram username/link
    facebook VARCHAR(255) DEFAULT NULL,         -- Optional Facebook profile link
    is_hidden TINYINT(1) NOT NULL DEFAULT 0,    -- 1 = hidden from public pages
    expiry_days INT NOT NULL DEFAULT 30,       -- 7, 15, 30, or 45 days
    expires_at DATETIME NOT NULL,              -- Auto-calculated expiry date
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Safe updates for existing databases (keeps existing columns as-is)
ALTER TABLE ads ADD COLUMN IF NOT EXISTS whatsapp VARCHAR(20) DEFAULT NULL;
ALTER TABLE ads ADD COLUMN IF NOT EXISTS telegram VARCHAR(100) DEFAULT NULL;
ALTER TABLE ads ADD COLUMN IF NOT EXISTS facebook VARCHAR(255) DEFAULT NULL;
ALTER TABLE ads ADD COLUMN IF NOT EXISTS is_hidden TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE ads ADD COLUMN IF NOT EXISTS expiry_days INT NOT NULL DEFAULT 30;
ALTER TABLE ads ADD COLUMN IF NOT EXISTS expires_at DATETIME NOT NULL;
ALTER TABLE ads ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE ads ADD COLUMN IF NOT EXISTS google_location VARCHAR(500) DEFAULT NULL;
-- Make location required
ALTER TABLE ads MODIFY COLUMN location VARCHAR(200) NOT NULL;

-- Table: ad_images
-- Stores images for each ad (one ad can have multiple images)
CREATE TABLE IF NOT EXISTS ad_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,          -- File name stored in /uploads/
    FOREIGN KEY (ad_id) REFERENCES ads(id) ON DELETE CASCADE
);

-- =============================================
-- Insert a default admin user
-- Email: admin@diu.edu.bd
-- Password: Admin@123
-- (Change this password after first login!)
-- =============================================
INSERT INTO users (name, email, password, is_admin)
VALUES (
    'Admin',
    'admin@diu.edu.bd',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    1
)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    is_admin = VALUES(is_admin);
-- Note: The hash above is for 'password' - please change it!
-- To generate a proper hash, use: password_hash('YourPassword', PASSWORD_DEFAULT)
