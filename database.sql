-- GadgetZone Database Schema
CREATE DATABASE IF NOT EXISTS gadget_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gadget_store;

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    pincode VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(150) NOT NULL UNIQUE,
    icon VARCHAR(100) DEFAULT 'fas fa-tag',
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(300) NOT NULL UNIQUE,
    description TEXT,
    specifications TEXT,
    price DECIMAL(12,2) NOT NULL DEFAULT 0,
    sale_price DECIMAL(12,2) DEFAULT NULL,
    stock INT DEFAULT 0,
    image VARCHAR(255),
    images TEXT,
    is_featured TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    rating DECIMAL(3,2) DEFAULT 0,
    review_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_cart (user_id, product_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_wishlist (user_id, product_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    type ENUM('percent','fixed') DEFAULT 'percent',
    value DECIMAL(10,2) NOT NULL,
    min_amount DECIMAL(12,2) DEFAULT 0,
    max_uses INT DEFAULT NULL,
    used_count INT DEFAULT 0,
    expires_at DATE DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(20),
    address TEXT NOT NULL,
    city VARCHAR(100),
    state VARCHAR(100),
    pincode VARCHAR(10),
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
    discount DECIMAL(12,2) DEFAULT 0,
    shipping DECIMAL(12,2) DEFAULT 0,
    total DECIMAL(12,2) NOT NULL DEFAULT 0,
    coupon_code VARCHAR(50),
    payment_method VARCHAR(50) DEFAULT 'razorpay',
    payment_id VARCHAR(255),
    razorpay_order_id VARCHAR(255),
    status ENUM('pending','paid','processing','shipped','delivered','cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT,
    product_name VARCHAR(255) NOT NULL,
    product_image VARCHAR(255),
    price DECIMAL(12,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    total DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    youtube_id VARCHAR(50) NOT NULL,
    description TEXT,
    thumbnail VARCHAR(255),
    category VARCHAR(100),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS channels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    channel_url VARCHAR(500) NOT NULL,
    description TEXT,
    subscribers VARCHAR(50),
    thumbnail VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS blogs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(300) NOT NULL UNIQUE,
    content LONGTEXT,
    excerpt TEXT,
    image VARCHAR(255),
    author VARCHAR(100) DEFAULT 'Admin',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS banners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    subtitle VARCHAR(255),
    image VARCHAR(255),
    link VARCHAR(500),
    position VARCHAR(50) DEFAULT 'hero',
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Default settings
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
('site_name', 'GadgetZone'),
('site_description', 'Your one-stop shop for the latest gadgets'),
('site_email', 'admin@gadgetzone.com'),
('site_phone', '+91 98765 43210'),
('site_address', '123 Tech Street, Bangalore, India'),
('currency', 'INR'),
('shipping_charge', '50'),
('free_shipping_above', '999'),
('razorpay_key_id', 'rzp_test_yourkeyid'),
('razorpay_key_secret', 'yourkeysecret'),
('logo_file', ''),
('meta_keywords', 'gadgets, electronics, mobile, laptop'),
('facebook_url', ''),
('twitter_url', ''),
('instagram_url', ''),
('youtube_url', '');

-- Default admin
INSERT IGNORE INTO admins (name, email, password) VALUES ('Admin', 'admin@gadgetzone.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Sample categories
INSERT IGNORE INTO categories (name, slug, icon) VALUES
('Smartphones', 'smartphones', 'fas fa-mobile-alt'),
('Laptops', 'laptops', 'fas fa-laptop'),
('Audio', 'audio', 'fas fa-headphones'),
('Cameras', 'cameras', 'fas fa-camera'),
('Accessories', 'accessories', 'fas fa-plug'),
('Wearables', 'wearables', 'fas fa-watch');
