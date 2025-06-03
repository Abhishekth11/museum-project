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

// Enhanced role-based redirection
function redirectBasedOnRole() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
    
    $redirect_url = $_SESSION['redirect_url'] ?? null;
    unset($_SESSION['redirect_url']);
    
    $user_role = $_SESSION['user_role'] ?? 'user';
    
    $role_destinations = [
        'admin' => 'admin/dashboard.php',
        'moderator' => 'admin/dashboard.php',
        'staff' => 'admin/dashboard.php',
        'user' => 'index.php'
    ];
    
    if ($redirect_url) {
        if (in_array($user_role, ['admin', 'moderator', 'staff'])) {
            $final_destination = $redirect_url;
        } else {
            if (strpos($redirect_url, 'admin/') === 0) {
                $final_destination = $role_destinations['user'];
            } else {
                $final_destination = $redirect_url;
            }
        }
    } else {
        $final_destination = $role_destinations[$user_role] ?? $role_destinations['user'];
    }
    
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
    
    if (time() - $_SESSION['login_time'] > 86400) {
        session_destroy();
        $_SESSION['session_expired'] = true;
        header('Location: ../login.php?error=session_expired');
        exit;
    }
    
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
    
    if (time() - $_SESSION['login_time'] > 43200) {
        session_destroy();
        $_SESSION['session_expired'] = true;
        header('Location: ../login.php?error=session_expired');
        exit;
    }
    
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

// Enhanced role-based permissions system
function hasPermission($permission) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $user_role = $_SESSION['user_role'] ?? 'user';
    
    $role_permissions = [
        'admin' => [
            'manage_users','create_users', 'manage_exhibitions', 'manage_events', 'manage_collections',
            'view_analytics', 'manage_settings', 'backup_database', 'moderate_content',
            'delete_users', 'edit_users', 'create_events', 'edit_events', 'delete_events',
            'create_exhibitions', 'edit_exhibitions', 'delete_exhibitions'
        ],
        'moderator' => [
            'manage_exhibitions', 'manage_events', 'manage_collections', 'moderate_content',
            'create_events', 'edit_events', 'create_exhibitions', 'edit_exhibitions'
        ],
        'staff' => [
            'manage_events', 'view_analytics', 'create_events', 'edit_events'
        ],
        'user' => [
            'join_membership', 'book_events', 'leave_reviews'
        ]
    ];
    
    return in_array($permission, $role_permissions[$user_role] ?? []);
}

// Check if user has specific permission
function checkPermission($permission) {
    if (!hasPermission($permission)) {
        $_SESSION['access_denied_message'] = 'You do not have permission to perform this action.';
        logUserActivity($_SESSION['user_id'] ?? 0, 'permission_denied', 'Attempted action: ' . $permission);
        return false;
    }
    return true;
}

// Email sending function with error handling
function sendEmail($to, $subject, $message, $headers = '') {
    // Default headers
    if (empty($headers)) {
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: National Museum of Art & Culture <noreply@nmac.org>" . "\r\n";
    }
    
    try {
        // Attempt to send email
        $result = mail($to, $subject, $message, $headers);
        
        if ($result) {
            logUserActivity($_SESSION['user_id'] ?? 0, 'email_sent', "Email sent to: $to, Subject: $subject");
            return ['success' => true, 'message' => 'Email sent successfully'];
        } else {
            error_log("Failed to send email to: $to, Subject: $subject");
            return ['success' => false, 'message' => 'Failed to send email'];
        }
    } catch (Exception $e) {
        error_log("Email error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Email service error: ' . $e->getMessage()];
    }
}

// Send membership confirmation email
function sendMembershipConfirmationEmail($user_data, $membership_data) {
    $to = $user_data['email'];
    $subject = "Welcome to NMAC - Membership Confirmation";
    
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #2c3e50; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .membership-details { background: white; padding: 15px; margin: 20px 0; border-left: 4px solid #3498db; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            .btn { display: inline-block; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Welcome to NMAC!</h1>
                <p>National Museum of Art & Culture</p>
            </div>
            
            <div class='content'>
                <h2>Dear " . htmlspecialchars($user_data['first_name']) . ",</h2>
                
                <p>Thank you for joining the National Museum of Art & Culture! We're excited to welcome you as a new member.</p>
                
                <div class='membership-details'>
                    <h3>Your Membership Details:</h3>
                    <p><strong>Membership Type:</strong> " . ucfirst($membership_data['membership_type']) . "</p>
                    <p><strong>Start Date:</strong> " . formatDate($membership_data['start_date']) . "</p>
                    <p><strong>Valid Until:</strong> " . formatDate($membership_data['end_date']) . "</p>
                    <p><strong>Member ID:</strong> NMAC-" . str_pad($membership_data['id'], 6, '0', STR_PAD_LEFT) . "</p>
                </div>
                
                <h3>Your Benefits Include:</h3>
                <ul>
                    <li>Unlimited free admission to all exhibitions</li>
                    <li>Exclusive member events and previews</li>
                    <li>Discounts in our museum shop</li>
                    <li>Priority event registration</li>
                    <li>Monthly member newsletter</li>
                </ul>
                
                <p>Your membership card will be mailed to you within 5-7 business days. In the meantime, you can show this email for immediate access to member benefits.</p>
                
                <p style='text-align: center;'>
                    <a href='http://nmac.org/member-portal' class='btn'>Access Member Portal</a>
                </p>
                
                <p>If you have any questions about your membership, please don't hesitate to contact us at membership@nmac.org or (123) 456-7890.</p>
                
                <p>Welcome to the NMAC family!</p>
                
                <p>Best regards,<br>
                The NMAC Membership Team</p>
            </div>
            
            <div class='footer'>
                <p>National Museum of Art & Culture<br>
                123 Museum Street, City, State 12345<br>
                Phone: (123) 456-7890 | Email: info@nmac.org</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($to, $subject, $message);
}

// Join membership function
function joinMembership($user_data, $membership_type) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Check if user already has an active membership
        $stmt = $pdo->prepare("SELECT id FROM memberships WHERE user_id = ? AND status = 'active' AND end_date > NOW()");
        $stmt->execute([$user_data['id']]);
        
        if ($stmt->fetch()) {
            $pdo->rollback();
            return ['success' => false, 'message' => 'You already have an active membership.'];
        }
        
        // Create membership
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d', strtotime('+1 year'));
        
        $stmt = $pdo->prepare("INSERT INTO memberships (user_id, membership_type, start_date, end_date, status, created_at) VALUES (?, ?, ?, ?, 'active', NOW())");
        $stmt->execute([$user_data['id'], $membership_type, $start_date, $end_date]);
        
        $membership_id = $pdo->lastInsertId();
        
        // Get membership data for email
        $membership_data = [
            'id' => $membership_id,
            'membership_type' => $membership_type,
            'start_date' => $start_date,
            'end_date' => $end_date
        ];
        
        // Send confirmation email
        $email_result = sendMembershipConfirmationEmail($user_data, $membership_data);
        
        if (!$email_result['success']) {
            // Log email failure but don't fail the membership creation
            error_log("Failed to send membership confirmation email to: " . $user_data['email']);
        }
        
        // Log the membership creation
        logUserActivity($user_data['id'], 'membership_created', "Joined as $membership_type member");
        
        $pdo->commit();
        
        return [
            'success' => true, 
            'message' => 'Membership created successfully! ' . ($email_result['success'] ? 'A confirmation email has been sent.' : 'Please note: confirmation email could not be sent, but your membership is active.'),
            'membership_id' => $membership_id
        ];
        
    } catch(PDOException $e) {
        $pdo->rollback();
        error_log("Membership creation error: " . $e->getMessage());
        return ['success' => false, 'message' => 'An error occurred while creating your membership. Please try again.'];
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

// Get all users (admin function)
function getAllUsers($limit = 0, $offset = 0) {
    global $pdo;
    
    try {
        $sql = "SELECT u.*, m.membership_type, m.status as membership_status 
                FROM users u 
                LEFT JOIN memberships m ON u.id = m.user_id AND m.status = 'active'
                ORDER BY u.created_at DESC";
        
        if ($limit > 0) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        error_log("Database error in getAllUsers: " . $e->getMessage());
        return [];
    }
}

// Update user (admin function)
function updateUser($user_id, $data) {
    global $pdo;
    
    if (!hasPermission('edit_users')) {
        return ['success' => false, 'message' => 'Permission denied'];
    }
    
    try {
        $fields = [];
        $values = [];
        
        foreach ($data as $field => $value) {
            if (in_array($field, ['first_name', 'last_name', 'email', 'role', 'status'])) {
                $fields[] = "$field = ?";
                $values[] = $value;
            }
        }
        
        if (empty($fields)) {
            return ['success' => false, 'message' => 'No valid fields to update'];
        }
        
        $values[] = $user_id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        
        logUserActivity($_SESSION['user_id'], 'user_updated', "Updated user ID: $user_id");
        
        return ['success' => true, 'message' => 'User updated successfully'];
        
    } catch(PDOException $e) {
        error_log("Update user error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to update user'];
    }
}

// Delete user (admin function)
function deleteUser($user_id) {
    global $pdo;
    
    if (!hasPermission('delete_users')) {
        return ['success' => false, 'message' => 'Permission denied'];
    }
    
    try {
        // Don't allow deleting the current user
        if ($user_id == $_SESSION['user_id']) {
            return ['success' => false, 'message' => 'Cannot delete your own account'];
        }
        
        $stmt = $pdo->prepare("UPDATE users SET status = 'deleted' WHERE id = ?");
        $stmt->execute([$user_id]);
        
        logUserActivity($_SESSION['user_id'], 'user_deleted', "Deleted user ID: $user_id");
        
        return ['success' => true, 'message' => 'User deleted successfully'];
        
    } catch(PDOException $e) {
        error_log("Delete user error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to delete user'];
    }
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

// Create event (admin function)
function createEvent($data) {
    global $pdo;
    
    if (!hasPermission('create_events')) {
        return ['success' => false, 'message' => 'Permission denied'];
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, event_time, location, event_type, capacity, price, image, status, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, NOW())");
        
        $stmt->execute([
            $data['title'],
            $data['description'],
            $data['event_date'],
            $data['event_time'],
            $data['location'],
            $data['event_type'],
            $data['capacity'] ?? null,
            $data['price'] ?? 0,
            $data['image'] ?? null,
            $_SESSION['user_id']
        ]);
        
        $event_id = $pdo->lastInsertId();
        
        logUserActivity($_SESSION['user_id'], 'event_created', "Created event: " . $data['title']);
        
        return ['success' => true, 'message' => 'Event created successfully', 'event_id' => $event_id];
        
    } catch(PDOException $e) {
        error_log("Create event error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to create event'];
    }
}

// Update event (admin function)
function updateEvent($event_id, $data) {
    global $pdo;
    
    if (!hasPermission('edit_events')) {
        return ['success' => false, 'message' => 'Permission denied'];
    }
    
    try {
        $fields = [];
        $values = [];
        
        $allowed_fields = ['title', 'description', 'event_date', 'event_time', 'location', 'event_type', 'capacity', 'price', 'image', 'status'];
        
        foreach ($data as $field => $value) {
            if (in_array($field, $allowed_fields)) {
                $fields[] = "$field = ?";
                $values[] = $value;
            }
        }
        
        if (empty($fields)) {
            return ['success' => false, 'message' => 'No valid fields to update'];
        }
        
        $values[] = $event_id;
        $sql = "UPDATE events SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        
        logUserActivity($_SESSION['user_id'], 'event_updated', "Updated event ID: $event_id");
        
        return ['success' => true, 'message' => 'Event updated successfully'];
        
    } catch(PDOException $e) {
        error_log("Update event error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to update event'];
    }
}

// Delete event (admin function)
function deleteEvent($event_id) {
    global $pdo;
    
    if (!hasPermission('delete_events')) {
        return ['success' => false, 'message' => 'Permission denied'];
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE events SET status = 'deleted' WHERE id = ?");
        $stmt->execute([$event_id]);
        
        logUserActivity($_SESSION['user_id'], 'event_deleted', "Deleted event ID: $event_id");
        
        return ['success' => true, 'message' => 'Event deleted successfully'];
        
    } catch(PDOException $e) {
        error_log("Delete event error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to delete event'];
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
        $stats['users'] = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetchColumn();
        $stats['subscriptions'] = $pdo->query("SELECT COUNT(*) FROM subscriptions")->fetchColumn();
        $stats['memberships'] = $pdo->query("SELECT COUNT(*) FROM memberships WHERE status = 'active'")->fetchColumn();
        
        // Get recent activity
        $stats['recent_users'] = $pdo->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
        $stats['recent_subscriptions'] = $pdo->query("SELECT COUNT(*) FROM subscriptions WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
        $stats['recent_memberships'] = $pdo->query("SELECT COUNT(*) FROM memberships WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
        
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

// Log user activity
function logUserActivity($user_id, $action, $details = '') {
    global $pdo;
    
    // Check if database connection is available
    if (!isset($pdo) || $pdo === null) {
        error_log("Database connection not available when logging user activity: $action");
        return false;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO user_activity_logs (user_id, action, details, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $user_id,
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
        return true;
    } catch(PDOException $e) {
        error_log("Activity logging error: " . $e->getMessage());
        return false;
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
            'view_analytics', 'manage_settings', 'backup_database', 'moderate_content',
            'delete_users', 'edit_users', 'create_events', 'edit_events', 'delete_events',
            'create_exhibitions', 'edit_exhibitions', 'delete_exhibitions'
        ],
        'moderator' => [
            'manage_exhibitions', 'manage_events', 'manage_collections', 'moderate_content',
            'create_events', 'edit_events', 'create_exhibitions', 'edit_exhibitions'
        ],
        'staff' => [
            'manage_events', 'view_analytics', 'create_events', 'edit_events'
        ],
        'user' => [
            'join_membership', 'book_events', 'leave_reviews'
        ]
    ];
    
    return $role_permissions[$user_role] ?? [];
}
?>
