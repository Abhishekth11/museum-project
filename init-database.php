<?php
// Database initialization script
require_once 'includes/db.php';

function initializeDatabase() {
    global $pdo;
    
    try {
        // Create users table if it doesn't exist
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                role ENUM('admin', 'member', 'user') DEFAULT 'user',
                status ENUM('active', 'inactive', 'deleted') DEFAULT 'active',
                last_login TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Create user_activity_logs table if it doesn't exist
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS user_activity_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT,
                action VARCHAR(100) NOT NULL,
                details TEXT,
                ip_address VARCHAR(45),
                user_agent TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Create other essential tables
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS exhibitions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                start_date DATE,
                end_date DATE,
                location VARCHAR(255),
                image VARCHAR(255),
                category VARCHAR(100),
                status ENUM('current', 'upcoming', 'past') DEFAULT 'upcoming',
                featured BOOLEAN DEFAULT FALSE,
                curator VARCHAR(255),
                price DECIMAL(10,2) DEFAULT 0.00,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS events (
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
                featured BOOLEAN DEFAULT FALSE,
                instructor VARCHAR(255),
                requirements TEXT,
                related_exhibition_id INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (related_exhibition_id) REFERENCES exhibitions(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS collections (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                artist VARCHAR(255),
                year VARCHAR(50),
                medium VARCHAR(255),
                image VARCHAR(255),
                category VARCHAR(100),
                dimensions VARCHAR(255),
                acquisition_date DATE,
                provenance TEXT,
                featured BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS memberships (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT,
                membership_type ENUM('individual', 'family', 'student', 'senior', 'patron') NOT NULL,
                member_id VARCHAR(50) UNIQUE,
                start_date DATE,
                end_date DATE,
                status ENUM('active', 'expired', 'cancelled', 'pending') DEFAULT 'active',
                price_paid DECIMAL(10,2) DEFAULT 0.00,
                payment_method ENUM('credit_card', 'debit_card', 'bank_transfer', 'paypal', 'cash', 'check') DEFAULT 'credit_card',
                billing_address TEXT,
                phone_number VARCHAR(20),
                newsletter_opt_in BOOLEAN DEFAULT TRUE,
                card_mailed BOOLEAN DEFAULT FALSE,
                card_mailed_date DATE NULL,
                renewal_reminder_sent BOOLEAN DEFAULT FALSE,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS subscriptions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) UNIQUE NOT NULL,
                name VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Insert default admin user if it doesn't exist
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = 'admin@admin.com'");
        $stmt->execute();
        
        if (!$stmt->fetch()) {
            $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES ('Admin', 'User', 'admin@admin.com', ?, 'admin')");
            $stmt->execute([$adminPassword]);
        }
        
        return true;
        
    } catch(PDOException $e) {
        error_log("Database initialization error: " . $e->getMessage());
        return false;
    }
}

// Run initialization if called directly
if (basename($_SERVER['PHP_SELF']) === 'init-database.php') {
    if (initializeDatabase()) {
        echo "Database initialized successfully!";
    } else {
        echo "Database initialization failed. Check error logs.";
    }
}
?>
