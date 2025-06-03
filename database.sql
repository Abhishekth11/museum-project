-- Create database
CREATE DATABASE IF NOT EXISTS museum_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE museum_db;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'member', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Exhibitions table
CREATE TABLE exhibitions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    start_date DATE,
    end_date DATE,
    location VARCHAR(255),
    image VARCHAR(255),
    category VARCHAR(100),
    status ENUM('current', 'upcoming', 'past') DEFAULT 'upcoming',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Events table
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    event_date DATE,
    start_time TIME,
    end_time TIME,
    location VARCHAR(255),
    image VARCHAR(255),
    price DECIMAL(10,2) DEFAULT 0.00,
    capacity INT DEFAULT 0,
    event_type VARCHAR(100),
    related_exhibition_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (related_exhibition_id) REFERENCES exhibitions(id) ON DELETE SET NULL
);

-- Collections table
CREATE TABLE collections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    artist VARCHAR(255),
    year VARCHAR(50),
    medium VARCHAR(255),
    image VARCHAR(255),
    category VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Memberships table
CREATE TABLE memberships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    membership_type ENUM('individual', 'family', 'student', 'senior', 'patron') NOT NULL,
    start_date DATE,
    end_date DATE,
    status ENUM('active', 'expired', 'cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Newsletter subscriptions
CREATE TABLE subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Event bookings
CREATE TABLE event_bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT,
    user_id INT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    tickets INT DEFAULT 1,
    total_price DECIMAL(10,2),
    booking_status ENUM('confirmed', 'cancelled', 'pending') DEFAULT 'confirmed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Search logs table for analytics
CREATE TABLE search_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    search_term VARCHAR(255) NOT NULL,
    results_count INT DEFAULT 0,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample admin user (password: admin123)
INSERT INTO users (first_name, last_name, email, password, role) VALUES 
('Admin', 'User', 'admin@nmac.org', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample exhibitions
INSERT INTO exhibitions (title, description, start_date, end_date, location, category, status) VALUES 
('Modern Masterpieces: A Century of Innovation', 'Explore the evolution of modern art through a curated selection of masterpieces spanning the 20th century. This exhibition showcases the revolutionary techniques and bold visions that defined an era of artistic innovation.', '2024-03-01', '2024-08-15', 'Main Gallery - East Wing', 'Modern Art', 'current'),
('Renaissance Reimagined', 'A fresh look at Renaissance art and its influence on contemporary artistic expression.', '2024-02-15', '2024-07-30', 'Gallery 2', 'Renaissance', 'current'),
('Digital Frontiers', 'Exploring the intersection of art and technology in the digital age.', '2024-04-01', '2024-09-05', 'Digital Gallery', 'Digital Art', 'current'),
('Photography Through Time', 'A comprehensive look at the evolution of photography from its earliest days to modern digital techniques.', '2024-09-15', '2025-01-15', 'Photography Wing', 'Photography', 'upcoming'),
('Ancient Civilizations', 'Discover artifacts and artworks from ancient civilizations around the world.', '2023-10-01', '2024-02-28', 'History Hall', 'Ancient Art', 'past');

-- Insert sample events
INSERT INTO events (title, description, event_date, start_time, end_time, location, event_type, price) VALUES 
('Guided Tour: Modern Masterpieces', 'Join our expert curator for a guided tour of our Modern Masterpieces exhibition.', '2024-06-15', '14:00:00', '15:30:00', 'Main Gallery', 'tour', 10.00),
('Artist Talk: Contemporary Expressions', 'Meet with contemporary artists and learn about their creative process.', '2024-06-18', '18:30:00', '20:00:00', 'Auditorium', 'talk', 15.00),
('Family Workshop: Art Exploration', 'A hands-on art workshop for families with children ages 5-12.', '2024-06-22', '10:00:00', '13:00:00', 'Education Center', 'workshop', 25.00),
('Summer Concert Series', 'Enjoy live music in our sculpture garden.', '2024-07-05', '19:00:00', '21:00:00', 'Sculpture Garden', 'special', 20.00),
('Photography Workshop', 'Learn the basics of photography with professional photographers.', '2024-07-12', '09:00:00', '16:00:00', 'Photography Studio', 'workshop', 75.00);

-- Insert sample collections
INSERT INTO collections (title, description, artist, year, category) VALUES 
('The Starry Night', 'A masterpiece of post-impressionist art depicting a swirling night sky.', 'Vincent van Gogh', '1889', 'Painting'),
('David', 'A Renaissance sculpture representing the Biblical hero David.', 'Michelangelo', '1501-1504', 'Sculpture'),
('Guernica', 'A powerful anti-war painting depicting the horrors of the Spanish Civil War.', 'Pablo Picasso', '1937', 'Painting'),
('The Thinker', 'A bronze sculpture depicting a man in deep thought.', 'Auguste Rodin', '1904', 'Sculpture'),
('Campbell\'s Soup Cans', 'An iconic work of pop art featuring 32 canvases of Campbell\'s soup cans.', 'Andy Warhol', '1962', 'Pop Art');

-- Insert sample newsletter subscriptions
INSERT INTO subscriptions (email, name) VALUES 
('visitor1@example.com', 'John Doe'),
('visitor2@example.com', 'Jane Smith'),
('artlover@example.com', 'Art Enthusiast');
