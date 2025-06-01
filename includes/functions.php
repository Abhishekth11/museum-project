<?php
// Format date
function formatDate($date, $format = 'F j, Y') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

// Sanitize input
function sanitize($input) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($input));
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
        return [];
    }
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin';
}

// Get user by ID
function getUserById($id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch(PDOException $e) {
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
        $stmt = $pdo->prepare("INSERT INTO subscriptions (email, name) VALUES (?, ?)");
        $stmt->execute([$email, $name]);
        
        return ['success' => true, 'message' => 'Thank you for subscribing to our newsletter!'];
        
    } catch(PDOException $e) {
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
        return [];
    }
}

// Log search term
function logSearchTerm($query, $results_count = 0) {
    global $pdo;
    
    if (empty($query)) return;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO search_logs (search_term, results_count, ip_address) VALUES (?, ?, ?)");
        $stmt->execute([$query, $results_count, $_SERVER['REMOTE_ADDR'] ?? '']);
    } catch(PDOException $e) {
        // Silently fail - logging is not critical
    }
}
?>
