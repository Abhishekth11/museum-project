<?php
// Format date
function formatDate($date, $format = 'F j, Y') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

// Sanitize input
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['login_time']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Check if user is moderator or admin
function isModerator() {
    return isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'moderator']);
}

// Role-based redirection with enhanced logic
function redirectBasedOnRole() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
    
    // Get the intended redirect URL if it exists
    $redirect_url = $_SESSION['redirect_url'] ?? null;
    unset($_SESSION['redirect_url']);
    
    // Get user role
    $user_role = $_SESSION['user_role'] ?? 'user';
    
    // Define role-based default destinations
    $role_destinations = [
        'admin' => 'admin/dashboard.php',
        'moderator' => 'admin/dashboard.php',
        'staff' => 'admin/dashboard.php',
        'user' => 'index.php'
    ];
    
    // Determine final destination
    if ($redirect_url) {
        // If there's a specific redirect URL, validate it's appropriate for the user role
        if (in_array($user_role, ['admin', 'moderator', 'staff'])) {
            // Admins can access any page
            $final_destination = $redirect_url;
        } else {
            // Regular users can't access admin pages
            if (strpos($redirect_url, 'admin/') === 0) {
                $final_destination = $role_destinations['user'];
            } else {
                $final_destination = $redirect_url;
            }
        }
    } else {
        // Use default destination for role
        $final_destination = $role_destinations[$user_role] ?? $role_destinations['user'];
    }
    
    // Log the login activity
    logUserActivity($_SESSION['user_id'], 'login', 'User logged in and redirected to: ' . $final_destination);
    
    header('Location: ' . $final_destination);
    exit;
}

// Enhanced admin access requirement
function requireAdmin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        $_SESSION['access_denied_message'] = 'Please log in to access this page.';
        header('Location: ../login.php');
        exit;
    }
    
    if (!isAdmin()) {
        $_SESSION['access_denied_message'] = 'You do not have permission to access this page.';
        logUserActivity($_SESSION['user_id'], 'access_denied', 'Attempted to access admin area: ' . $_SERVER['REQUEST_URI']);
        header('Location: ../index.php?error=access_denied');
        exit;
    }
    
    // Check session timeout (24 hours for admin)
    if (time() - $_SESSION['login_time'] > 86400) {
        session_destroy();
        $_SESSION['session_expired'] = true;
        header('Location: ../login.php?error=session_expired');
        exit;
    }
    
    // Update last activity
    $_SESSION['last_activity'] = time();
}

// Enhanced moderator access requirement
function requireModerator() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        $_SESSION['access_denied_message'] = 'Please log in to access this page.';
        header('Location: ../login.php');
        exit;
    }
    
    if (!isModerator()) {
        $_SESSION['access_denied_message'] = 'You do not have permission to access this page.';
        logUserActivity($_SESSION['user_id'], 'access_denied', 'Attempted to access moderator area: ' . $_SERVER['REQUEST_URI']);
        header('Location: ../index.php?error=access_denied');
        exit;
    }
    
    // Check session timeout (12 hours for moderators)
    if (time() - $_SESSION['login_time'] > 43200) {
        session_destroy();
        $_SESSION['session_expired'] = true;
        header('Location: ../login.php?error=session_expired');
        exit;
    }
    
    // Update last activity
    $_SESSION['last_activity'] = time();
}

// Generate CSRF token
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Get exhibitions by status
function getExhibitions($status = 'all', $limit = 0) {
    global $pdo;
    
    $sql = "SELECT * FROM exhibitions";
    $params = [];
    
    if ($status != 'all') {
        $sql .= " WHERE status = ?";
        $params[] = $status;
    }
    
    $sql .= " ORDER BY start_date DESC";
    
    if ($limit > 0) {
        $sql .= " LIMIT ?";
        $params[] = $limit;
    }
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        error_log("Database error in getExhibitions: " . $e->getMessage());
        return [];
    }
}

// Get events
function getEvents($limit = 0, $future_only = false) {
    global $pdo;
    
    $sql = "SELECT * FROM events";
    $params = [];
    
    if ($future_only) {
        $sql .= " WHERE event_date >= CURDATE()";
    }
    
    $sql .= " ORDER BY event_date ASC";
    
    if ($limit > 0) {
        $sql .= " LIMIT ?";
        $params[] = $limit;
    }
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        error_log("Database error in getEvents: " . $e->getMessage());
        return [];
    }
}

// Get collections
function getCollections($category = 'all', $limit = 0) {
    global $pdo;
    
    $sql = "SELECT * FROM collections";
    $params = [];
    
    if ($category != 'all') {
        $sql .= " WHERE category = ?";
        $params[] = $category;
    }
    
    $sql .= " ORDER BY id DESC";
    
    if ($limit > 0) {
        $sql .= " LIMIT ?";
        $params[] = $limit;
    }
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        error_log("Database error in getCollections: " . $e->getMessage());
        return [];
    }
}

// Get user by ID
function getUserById($id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch(PDOException $e) {
        error_log("Database error in getUserById: " . $e->getMessage());
        return null;
    }
}

// Get exhibition by ID
function getExhibitionById($id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM exhibitions WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch(PDOException $e) {
        error_log("Database error in getExhibitionById: " . $e->getMessage());
        return null;
    }
}

// Get event by ID
function getEventById($id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch(PDOException $e) {
        error_log("Database error in getEventById: " . $e->getMessage());
        return null;
    }
}

// Get collection by ID
function getCollectionById($id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM collections WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch(PDOException $e) {
        error_log("Database error in getCollectionById: " . $e->getMessage());
        return null;
    }
}

// Enhanced search function with better indexing and relevance scoring
function searchContent($query, $type = 'all', $limit = 50) {
    global $pdo;
    
    if (empty($query)) {
        return [];
    }
    
    $results = [];
    $searchTerms = explode(' ', trim($query));
    $searchPattern = '%' . implode('%', $searchTerms) . '%';
    
    try {
        // Search exhibitions with relevance scoring
        if ($type == 'all' || $type == 'exhibitions') {
            $sql = "SELECT id, title, description, image, location, category, start_date, end_date, 'exhibition' as type,
                    (CASE 
                        WHEN title LIKE ? THEN 10
                        WHEN category LIKE ? THEN 5
                        WHEN description LIKE ? THEN 3
                        WHEN location LIKE ? THEN 2
                        ELSE 1
                    END) as relevance_score
                    FROM exhibitions 
                    WHERE title LIKE ? OR description LIKE ? OR category LIKE ? OR location LIKE ?
                    ORDER BY relevance_score DESC, start_date DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $searchPattern, $searchPattern, $searchPattern, $searchPattern,
                $searchPattern, $searchPattern, $searchPattern, $searchPattern
            ]);
            $results = array_merge($results, $stmt->fetchAll());
        }
        
        // Search events with relevance scoring
        if ($type == 'all' || $type == 'events') {
            $sql = "SELECT id, title, description, image, location, event_type as category, event_date as start_date, event_date as end_date, 'event' as type,
                    (CASE 
                        WHEN title LIKE ? THEN 10
                        WHEN event_type LIKE ? THEN 5
                        WHEN description LIKE ? THEN 3
                        WHEN location LIKE ? THEN 2
                        ELSE 1
                    END) as relevance_score
                    FROM events 
                    WHERE title LIKE ? OR description LIKE ? OR event_type LIKE ? OR location LIKE ?
                    ORDER BY relevance_score DESC, event_date ASC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $searchPattern, $searchPattern, $searchPattern, $searchPattern,
                $searchPattern, $searchPattern, $searchPattern, $searchPattern
            ]);
            $results = array_merge($results, $stmt->fetchAll());
        }
        
        // Search collections with relevance scoring
        if ($type == 'all' || $type == 'collections') {
            $sql = "SELECT id, title, description, image, artist, category, year as start_date, year as end_date, 'collection' as type,
                    (CASE 
                        WHEN title LIKE ? THEN 10
                        WHEN artist LIKE ? THEN 8
                        WHEN category LIKE ? THEN 5
                        WHEN description LIKE ? THEN 3
                        WHEN medium LIKE ? THEN 2
                        ELSE 1
                    END) as relevance_score
                    FROM collections 
                    WHERE title LIKE ? OR description LIKE ? OR artist LIKE ? OR category LIKE ? OR medium LIKE ?
                    ORDER BY relevance_score DESC, id DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $searchPattern, $searchPattern, $searchPattern, $searchPattern, $searchPattern,
                $searchPattern, $searchPattern, $searchPattern, $searchPattern, $searchPattern
            ]);
            $results = array_merge($results, $stmt->fetchAll());
        }
        
        // Sort all results by relevance score
        usort($results, function($a, $b) {
            return $b['relevance_score'] - $a['relevance_score'];
        });
        
        // Limit results if specified
        if ($limit > 0) {
            $results = array_slice($results, 0, $limit);
        }
        
        // Log search term
        logSearchTerm($query, count($results));
        
    } catch(PDOException $e) {
        error_log("Search error: " . $e->getMessage());
        return [];
    }
    
    return $results;
}

// Get search suggestions
function getSearchSuggestions($query, $limit = 5) {
    global $pdo;
    
    if (empty($query) || strlen($query) < 2) {
        return [];
    }
    
    $suggestions = [];
    $searchPattern = $query . '%';
    
    try {
        // Get exhibition titles
        $stmt = $pdo->prepare("SELECT DISTINCT title FROM exhibitions WHERE title LIKE ? LIMIT ?");
        $stmt->execute([$searchPattern, $limit]);
        $suggestions = array_merge($suggestions, $stmt->fetchAll(PDO::FETCH_COLUMN));
        
        // Get artist names
        $stmt = $pdo->prepare("SELECT DISTINCT artist FROM collections WHERE artist LIKE ? AND artist IS NOT NULL LIMIT ?");
        $stmt->execute([$searchPattern, $limit]);
        $suggestions = array_merge($suggestions, $stmt->fetchAll(PDO::FETCH_COLUMN));
        
        // Get categories
        $stmt = $pdo->prepare("SELECT DISTINCT category FROM exhibitions WHERE category LIKE ? AND category IS NOT NULL LIMIT ?");
        $stmt->execute([$searchPattern, $limit]);
        $suggestions = array_merge($suggestions, $stmt->fetchAll(PDO::FETCH_COLUMN));
        
        // Remove duplicates and limit
        $suggestions = array_unique($suggestions);
        $suggestions = array_slice($suggestions, 0, $limit);
        
    } catch(PDOException $e) {
        error_log("Search suggestions error: " . $e->getMessage());
        return [];
    }
    
    return $suggestions;
}

// Subscribe to newsletter
function subscribeNewsletter($email, $name = '') {
    global $pdo;
    
    try {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM subscriptions WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'This email is already subscribed to our newsletter.'];
        }
        
        // Insert new subscription
        $stmt = $pdo->prepare("INSERT INTO subscriptions (email, name, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$email, $name]);
        
        return ['success' => true, 'message' => 'Thank you for subscribing to our newsletter!'];
        
    } catch(PDOException $e) {
        error_log("Newsletter subscription error: " . $e->getMessage());
        return ['success' => false, 'message' => 'An error occurred. Please try again later.'];
    }
}

// Get popular search terms
function getPopularSearchTerms($limit = 10) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT search_term, COUNT(*) as count FROM search_logs 
                              WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
                              GROUP BY search_term 
                              ORDER BY count DESC 
                              LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        error_log("Popular search terms error: " . $e->getMessage());
        return [];
    }
}

// Log search term
function logSearchTerm($query, $results_count = 0) {
    global $pdo;
    
    if (empty($query)) return;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO search_logs (search_term, results_count, ip_address, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$query, $results_count, $_SERVER['REMOTE_ADDR'] ?? '']);
    } catch(PDOException $e) {
        error_log("Search logging error: " . $e->getMessage());
    }
}

// Get dashboard statistics
function getDashboardStats() {
    global $pdo;
    
    try {
        $stats = [];
        
        // Get counts
        $stats['exhibitions'] = $pdo->query("SELECT COUNT(*) FROM exhibitions")->fetchColumn();
        $stats['events'] = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
        $stats['collections'] = $pdo->query("SELECT COUNT(*) FROM collections")->fetchColumn();
        $stats['users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $stats['subscriptions'] = $pdo->query("SELECT COUNT(*) FROM subscriptions")->fetchColumn();
        
        // Get recent activity
        $stats['recent_users'] = $pdo->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
        $stats['recent_subscriptions'] = $pdo->query("SELECT COUNT(*) FROM subscriptions WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
        
        return $stats;
    } catch(PDOException $e) {
        error_log("Dashboard stats error: " . $e->getMessage());
        return [];
    }
}

// Validate and sanitize admin input
function validateAdminInput($data, $rules) {
    $errors = [];
    $sanitized = [];
    
    foreach ($rules as $field => $rule) {
        $value = $data[$field] ?? '';
        
        // Sanitize
        $sanitized[$field] = sanitize($value);
        
        // Required check
        if (isset($rule['required']) && $rule['required'] && empty($sanitized[$field])) {
            $errors[$field] = ucfirst($field) . ' is required';
            continue;
        }
        
        // Type validation
        if (!empty($sanitized[$field]) && isset($rule['type'])) {
            switch ($rule['type']) {
                case 'email':
                    if (!filter_var($sanitized[$field], FILTER_VALIDATE_EMAIL)) {
                        $errors[$field] = 'Invalid email format';
                    }
                    break;
                case 'url':
                    if (!filter_var($sanitized[$field], FILTER_VALIDATE_URL)) {
                        $errors[$field] = 'Invalid URL format';
                    }
                    break;
                case 'date':
                    if (!strtotime($sanitized[$field])) {
                        $errors[$field] = 'Invalid date format';
                    }
                    break;
                case 'number':
                    if (!is_numeric($sanitized[$field])) {
                        $errors[$field] = 'Must be a number';
                    }
                    break;
            }
        }
        
        // Length validation
        if (!empty($sanitized[$field]) && isset($rule['max_length'])) {
            if (strlen($sanitized[$field]) > $rule['max_length']) {
                $errors[$field] = ucfirst($field) . ' must be less than ' . $rule['max_length'] . ' characters';
            }
        }
        
        if (!empty($sanitized[$field]) && isset($rule['min_length'])) {
            if (strlen($sanitized[$field]) < $rule['min_length']) {
                $errors[$field] = ucfirst($field) . ' must be at least ' . $rule['min_length'] . ' characters';
            }
        }
    }
    
    return ['errors' => $errors, 'data' => $sanitized];
}

// Check if user has specific permission
function hasPermission($permission) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $user_role = $_SESSION['user_role'] ?? 'user';
    
    // Define role permissions
    $role_permissions = [
        'admin' => [
            'manage_users', 'manage_exhibitions', 'manage_events', 'manage_collections',
            'view_analytics', 'manage_settings', 'backup_database', 'moderate_content'
        ],
        'moderator' => [
            'manage_exhibitions', 'manage_events', 'manage_collections', 'moderate_content'
        ],
        'staff' => [
            'manage_events', 'view_analytics'
        ],
        'user' => []
    ];
    
    return in_array($permission, $role_permissions[$user_role] ?? []);
}

// Log user activity
function logUserActivity($user_id, $action, $details = '') {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO user_activity_logs (user_id, action, details, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $user_id,
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    } catch(PDOException $e) {
        error_log("Activity logging error: " . $e->getMessage());
    }
}

// Get user permissions for frontend
function getUserPermissions() {
    if (!isLoggedIn()) {
        return [];
    }
    
    $user_role = $_SESSION['user_role'] ?? 'user';
    
    $role_permissions = [
        'admin' => [
            'manage_users', 'manage_exhibitions', 'manage_events', 'manage_collections',
            'view_analytics', 'manage_settings', 'backup_database', 'moderate_content'
        ],
        'moderator' => [
            'manage_exhibitions', 'manage_events', 'manage_collections', 'moderate_content'
        ],
        'staff' => [
            'manage_events', 'view_analytics'
        ],
        'user' => []
    ];
    
    return $role_permissions[$user_role] ?? [];
}
?>
