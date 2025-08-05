-- TechEase Solutions Database Schema
-- Sprint 7 - Database Tables Setup

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS techease_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE techease_db;

-- Genders table
CREATE TABLE IF NOT EXISTS genders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(20) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Roles table
CREATE TABLE IF NOT EXISTS roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    gender_id INT,
    role_id INT DEFAULT 2, -- Default to customer role
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (gender_id) REFERENCES genders(id),
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category VARCHAR(100) NOT NULL,
    image_url VARCHAR(500),
    stock_quantity INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Articles table
CREATE TABLE IF NOT EXISTS articles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    author_id INT,
    image_url VARCHAR(500),
    is_published BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id)
);

-- Orders table (for future use)
CREATE TABLE IF NOT EXISTS orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Order items table (for future use)
CREATE TABLE IF NOT EXISTS order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Insert default data
INSERT INTO genders (name) VALUES 
('Male'),
('Female'),
('Other');

INSERT INTO roles (name, description) VALUES 
('Admin', 'System administrator with full access'),
('Customer', 'Regular customer with limited access'),
('Manager', 'Store manager with moderate access');

-- Insert sample products
INSERT INTO products (name, description, price, category, stock_quantity) VALUES 
('Business Laptop', 'High-performance laptop perfect for business and productivity', 120000.00, 'Laptops', 10),
('Gaming Laptop', 'Powerful gaming laptop with high-end graphics and performance', 180000.00, 'Laptops', 5),
('Smartphone', 'Latest smartphone with advanced camera and powerful processor', 85000.00, 'Phones', 15),
('Budget Phone', 'Affordable smartphone with great features and long battery life', 45000.00, 'Phones', 20),
('Tablet', 'Lightweight tablet perfect for work, entertainment, and creativity', 65000.00, 'Accessories', 8),
('Wireless Headphones', 'Premium wireless headphones with noise cancellation and long battery life', 25000.00, 'Accessories', 12),
('Gaming Mouse', 'High-precision gaming mouse with customizable RGB lighting', 8500.00, 'Accessories', 25),
('Mechanical Keyboard', 'Professional mechanical keyboard with tactile switches and backlighting', 15000.00, 'Accessories', 15),
('USB-C Hub', 'Multi-port USB-C hub for expanding connectivity options', 5500.00, 'Accessories', 30);

-- Insert sample articles
INSERT INTO articles (title, content, is_published) VALUES 
('Getting Started with Web Development', 'Web development is an exciting journey that opens up countless opportunities in the digital world...', TRUE),
('The Future of Technology in Kenya', 'Kenya is rapidly becoming a technology hub in East Africa, with innovations in mobile money...', TRUE),
('Choosing the Right Laptop for Your Needs', 'With so many options available, choosing the right laptop can be overwhelming...', TRUE); 