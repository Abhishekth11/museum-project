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

// Enhanced image handling function
function getImageUrl($imagePath, $type = 'general', $category = '') {
    // If we have a database image path, check if file exists
    if (!empty($imagePath)) {
        $fullPath = '';
        
        // Determine the correct upload directory based on type
        switch ($type) {
            case 'exhibition':
                $fullPath = 'uploads/exhibitions/' . $imagePath;
                break;
            case 'collection':
                $fullPath = 'uploads/collections/' . $imagePath;
                break;
            case 'event':
                $fullPath = 'uploads/events/' . $imagePath;
                break;
            case 'virtual_tour':
                $fullPath = 'uploads/virtual_tours/' . $imagePath;
                break;
            default:
                $fullPath = 'uploads/' . $imagePath;
        }
        
        // Check if file exists
        if (file_exists($fullPath)) {
            return $fullPath;
        }
        
        // If the path doesn't include uploads/, try with it
        if (strpos($imagePath, 'uploads/') === false) {
            $altPath = 'uploads/' . $type . 's/' . $imagePath;
            if (file_exists($altPath)) {
                return $altPath;
            }
        }
        
        // If it's already a full URL, return it
        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
            return $imagePath;
        }
    }
    
    // Return category-specific fallback image
    return getFallbackImage($type, $category);
}

// Get appropriate fallback image based on type and category
function getFallbackImage($type, $category = '') {
    $category = strtolower($category);
    
    switch ($type) {
        case 'exhibition':
            if (strpos($category, 'modern') !== false || strpos($category, 'contemporary') !== false) {
                return 'https://images.unsplash.com/photo-1544967082-d9d25d867d66?w=600&h=400&fit=crop&auto=format';
            } elseif (strpos($category, 'renaissance') !== false || strpos($category, 'classical') !== false) {
                return 'https://images.unsplash.com/photo-1577083552431-6e5fd01aa342?w=600&h=400&fit=crop&auto=format';
            } elseif (strpos($category, 'digital') !== false || strpos($category, 'technology') !== false) {
                return 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=600&h=400&fit=crop&auto=format';
            } elseif (strpos($category, 'impressionism') !== false || strpos($category, 'impressionist') !== false) {
                return 'https://images.unsplash.com/photo-1578321272176-b7bbc0679853?w=600&h=400&fit=crop&auto=format';
            } elseif (strpos($category, 'photography') !== false) {
                return 'https://images.unsplash.com/photo-1606983340126-99ab4feaa64a?w=600&h=400&fit=crop&auto=format';
            } elseif (strpos($category, 'ancient') !== false || strpos($category, 'historical') !== false) {
                return 'https://images.unsplash.com/photo-1594736797933-d0401ba2fe65?w=600&h=400&fit=crop&auto=format';
            } elseif (strpos($category, 'abstract') !== false) {
                return 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=600&h=400&fit=crop&auto=format';
            } elseif (strpos($category, 'sculpture') !== false) {
                return 'https://images.unsplash.com/photo-1594736797933-d0401ba2fe65?w=600&h=400&fit=crop&auto=format';
            } else {
                return 'https://images.unsplash.com/photo-1541961017774-22349e4a1262?w=600&h=400&fit=crop&auto=format';
            }
            
        case 'collection':
            if (strpos($category, 'painting') !== false) {
                return 'https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?w=400&h=500&fit=crop&auto=format';
            } elseif (strpos($category, 'sculpture') !== false) {
                return 'https://images.unsplash.com/photo-1638186824584-6d6367254927?w=400&h=500&fit=crop&auto=format';
            } elseif (strpos($category, 'photography') !== false) {
                return 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=400&h=500&fit=crop&auto=format';
            } elseif (strpos($category, 'ancient') !== false || strpos($category, 'artifact') !== false) {
                return 'https://images.unsplash.com/photo-1606761568499-6d2451b23c66?w=400&h=500&fit=crop&auto=format';
            } elseif (strpos($category, 'digital') !== false) {
                return 'https://images.unsplash.com/photo-1518709268805-4e9042af2176?w=400&h=500&fit=crop&auto=format';
            } else {
                return 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=400&h=500&fit=crop&auto=format';
            }
            
        case 'event':
            if (strpos($category, 'workshop') !== false || strpos($category, 'class') !== false) {
                return 'https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?w=600&h=400&fit=crop&auto=format';
            } elseif (strpos($category, 'concert') !== false || strpos($category, 'music') !== false) {
                return 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=600&h=400&fit=crop&auto=format';
            } elseif (strpos($category, 'lecture') !== false || strpos($category, 'talk') !== false) {
                return 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=600&h=400&fit=crop&auto=format';
            } elseif (strpos($category, 'tour') !== false) {
                return 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=600&h=400&fit=crop&auto=format';
            } else {
                return 'https://images.unsplash.com/photo-1475721027785-f74eccf877e2?w=600&h=400&fit=crop&auto=format';
            }
            
        case 'virtual_tour':
            return 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=800&h=600&fit=crop&auto=format';
            
        default:
            return 'https://images.unsplash.com/photo-1541961017774-22349e4a1262?w=600&h=400&fit=crop&auto=format';
    }
}

// Create upload directories if they don't exist
function ensureUploadDirectories() {
    $directories = [
        'uploads',
        'uploads/exhibitions',
        'uploads/collections',
        'uploads/events',
        'uploads/virtual_tours'
    ];
    
    foreach ($directories as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
    }
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

// Enhanced membership function with email confirmation
function joinMembershipEnhanced($user_data, $membership_data) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Check if user already has an active membership
        $stmt = $pdo->prepare("SELECT id, membership_type, end_date FROM memberships WHERE user_id = ? AND status = 'active' AND end_date > NOW()");
        $stmt->execute([$user_data['id']]);
        $existing_membership = $stmt->fetch();
        
        if ($existing_membership) {
            $pdo->rollback();
            return [
                'success' => false, 
                'message' => 'You already have an active ' . ucfirst($existing_membership['membership_type']) . ' membership valid until ' . formatDate($existing_membership['end_date']) . '.'
            ];
        }
        
        // Get membership pricing
        $membership_prices = [
            'individual' => 75,
            'family' => 125,
            'student' => 45,
            'senior' => 60,
            'patron' => 500
        ];
        
        $membership_type = $membership_data['membership_type'];
        $price = $membership_prices[$membership_type] ?? 0;
        
        // Create membership record
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d', strtotime('+1 year'));
        $member_id = 'NMAC-' . date('Y') . '-' . str_pad($user_data['id'], 4, '0', STR_PAD_LEFT);
        
        $stmt = $pdo->prepare("
            INSERT INTO memberships (
                user_id, membership_type, start_date, end_date, status, 
                member_id, price_paid, payment_method, billing_address, 
                phone_number, created_at
            ) VALUES (?, ?, ?, ?, 'active', ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $user_data['id'],
            $membership_type,
            $start_date,
            $end_date,
            $member_id,
            $price,
            $membership_data['payment_method'],
            $membership_data['billing_address'],
            $membership_data['phone_number']
        ]);
        
        $membership_id = $pdo->lastInsertId();
        
        // Prepare membership details for email
        $membership_details = [
            'id' => $membership_id,
            'member_id' => $member_id,
            'membership_type' => $membership_type,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'price_paid' => $price,
            'payment_method' => $membership_data['payment_method'],
            'billing_address' => $membership_data['billing_address'],
            'phone_number' => $membership_data['phone_number']
        ];
        
        // Send congratulations email
        $email_result = sendMembershipCongratulationsEmail($user_data, $membership_details);
        
        // Also subscribe to newsletter if opted in
        if (isset($membership_data['newsletter_opt_in'])) {
            subscribeNewsletter($user_data['email'], $user_data['first_name'] . ' ' . $user_data['last_name']);
        }
        
        // Log the membership creation
        logUserActivity($user_data['id'], 'membership_created', "Joined as $membership_type member - Member ID: $member_id");
        
        // Send notification to admin
        sendAdminMembershipNotification($user_data, $membership_details);
        
        $pdo->commit();
        
        return [
            'success' => true, 
            'message' => 'Congratulations! Your membership has been successfully activated. A confirmation email has been sent to your email address.',
            'membership_id' => $membership_id,
            'membership_details' => $membership_details
        ];
        
    } catch(PDOException $e) {
        $pdo->rollback();
        error_log("Enhanced membership creation error: " . $e->getMessage());
        return ['success' => false, 'message' => 'An error occurred while processing your membership. Please try again or contact support.'];
    }
}

// Send congratulations email with enhanced template
function sendMembershipCongratulationsEmail($user_data, $membership_details) {
    $to = $user_data['email'];
    $subject = "🎉 Congratulations! Welcome to NMAC - Your Membership is Active";
    
    // Get membership benefits based on type
    $benefits = getMembershipBenefits($membership_details['membership_type']);
    
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <style>
            body { 
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                line-height: 1.6; 
                color: #333; 
                margin: 0; 
                padding: 0;
                background-color: #f8f9fa;
            }
            .container { 
                max-width: 600px; 
                margin: 0 auto; 
                background: white;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }
            .header { 
                background: linear-gradient(135deg, #2c3e50, #3498db); 
                color: white; 
                padding: 30px 20px; 
                text-align: center; 
            }
            .header h1 { 
                margin: 0; 
                font-size: 28px; 
                font-weight: 300; 
            }
            .header .emoji { 
                font-size: 48px; 
                margin-bottom: 10px; 
                display: block; 
            }
            .content { 
                padding: 30px 20px; 
            }
            .welcome-message {
                background: #e8f5e8;
                padding: 20px;
                border-radius: 8px;
                margin: 20px 0;
                border-left: 4px solid #28a745;
            }
            .membership-card {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 25px;
                border-radius: 12px;
                margin: 25px 0;
                text-align: center;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            }
            .member-id {
                font-size: 24px;
                font-weight: bold;
                letter-spacing: 2px;
                margin: 10px 0;
            }
            .membership-details { 
                background: #f8f9fa; 
                padding: 20px; 
                margin: 20px 0; 
                border-radius: 8px;
                border: 1px solid #dee2e6;
            }
            .detail-row {
                display: flex;
                justify-content: space-between;
                padding: 8px 0;
                border-bottom: 1px solid #e9ecef;
            }
            .detail-row:last-child {
                border-bottom: none;
                font-weight: bold;
                font-size: 18px;
                color: #2c3e50;
            }
            .benefits-list {
                background: white;
                border: 2px solid #3498db;
                border-radius: 8px;
                padding: 20px;
                margin: 20px 0;
            }
            .benefits-list h3 {
                color: #2c3e50;
                margin-top: 0;
                text-align: center;
            }
            .benefits-list ul {
                list-style: none;
                padding: 0;
            }
            .benefits-list li {
                padding: 8px 0;
                border-bottom: 1px solid #ecf0f1;
                position: relative;
                padding-left: 30px;
            }
            .benefits-list li:before {
                content: '✓';
                position: absolute;
                left: 0;
                color: #27ae60;
                font-weight: bold;
                font-size: 16px;
            }
            .cta-section {
                text-align: center;
                margin: 30px 0;
            }
            .btn { 
                display: inline-block; 
                padding: 15px 30px; 
                background: #3498db; 
                color: white; 
                text-decoration: none; 
                border-radius: 25px; 
                font-weight: bold;
                margin: 10px;
                transition: background 0.3s ease;
            }
            .btn:hover {
                background: #2980b9;
            }
            .btn-secondary {
                background: #95a5a6;
            }
            .footer { 
                text-align: center; 
                padding: 30px 20px; 
                color: #666; 
                font-size: 14px; 
                background: #f8f9fa;
                border-top: 1px solid #dee2e6;
            }
            .social-links {
                margin: 20px 0;
            }
            .social-links a {
                display: inline-block;
                margin: 0 10px;
                color: #3498db;
                text-decoration: none;
            }
            @media (max-width: 600px) {
                .container { width: 100%; }
                .content { padding: 20px 15px; }
                .header { padding: 20px 15px; }
                .detail-row { flex-direction: column; }
                .detail-row span:first-child { font-weight: bold; }
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <span class='emoji'>🎉</span>
                <h1>Congratulations, " . htmlspecialchars($user_data['first_name']) . "!</h1>
                <p>Welcome to the NMAC Family</p>
            </div>
            
            <div class='content'>
                <div class='welcome-message'>
                    <h2>🌟 Your Membership is Now Active!</h2>
                    <p>Thank you for joining the National Museum of Art & Culture! We're thrilled to welcome you as our newest " . ucfirst($membership_details['membership_type']) . " member.</p>
                </div>
                
                <div class='membership-card'>
                    <h3>Digital Membership Card</h3>
                    <div class='member-id'>" . htmlspecialchars($membership_details['member_id']) . "</div>
                    <p>" . ucfirst($membership_details['membership_type']) . " Member</p>
                    <p>Valid: " . formatDate($membership_details['start_date']) . " - " . formatDate($membership_details['end_date']) . "</p>
                </div>
                
                <div class='membership-details'>
                    <h3>📋 Membership Details</h3>
                    <div class='detail-row'>
                        <span>Member Name:</span>
                        <span>" . htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']) . "</span>
                    </div>
                    <div class='detail-row'>
                        <span>Membership Type:</span>
                        <span>" . ucfirst($membership_details['membership_type']) . "</span>
                    </div>
                    <div class='detail-row'>
                        <span>Member ID:</span>
                        <span>" . htmlspecialchars($membership_details['member_id']) . "</span>
                    </div>
                    <div class='detail-row'>
                        <span>Start Date:</span>
                        <span>" . formatDate($membership_details['start_date']) . "</span>
                    </div>
                    <div class='detail-row'>
                        <span>Valid Until:</span>
                        <span>" . formatDate($membership_details['end_date']) . "</span>
                    </div>
                    <div class='detail-row'>
                        <span>Amount Paid:</span>
                        <span>$" . number_format($membership_details['price_paid'], 2) . "</span>
                    </div>
                </div>
                
                <div class='benefits-list'>
                    <h3>🎁 Your Exclusive Benefits</h3>
                    <ul>
                        " . implode('', array_map(function($benefit) { return "<li>$benefit</li>"; }, $benefits)) . "
                    </ul>
                </div>
                
                <div class='cta-section'>
                    <h3>Ready to Start Exploring?</h3>
                    <p>Your membership gives you immediate access to all our exhibitions and member benefits.</p>
                    <a href='http://nmac.org/exhibitions' class='btn'>View Current Exhibitions</a>
                    <a href='http://nmac.org/events' class='btn btn-secondary'>Browse Member Events</a>
                </div>
                
                <div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107;'>
                    <h4>📱 Important Information:</h4>
                    <ul style='margin: 0; padding-left: 20px;'>
                        <li>Your physical membership card will arrive within 7-10 business days</li>
                        <li>Show this email for immediate member access</li>
                        <li>Download our mobile app for digital membership card access</li>
                        <li>Contact us at membership@nmac.org for any questions</li>
                    </ul>
                </div>
                
                <p>Thank you for supporting the arts and becoming part of our community. We look forward to seeing you at the museum soon!</p>
                
                <p style='margin-top: 30px;'>
                    <strong>Warm regards,</strong><br>
                    <strong>The NMAC Membership Team</strong><br>
                    National Museum of Art & Culture
                </p>
            </div>
            
            <div class='footer'>
                <div class='social-links'>
                    <a href='#'>Facebook</a> | 
                    <a href='#'>Instagram</a> | 
                    <a href='#'>Twitter</a> | 
                    <a href='#'>YouTube</a>
                </div>
                <p><strong>National Museum of Art & Culture</strong><br>
                123 Museum Street, City, State 12345<br>
                Phone: (123) 456-7890 | Email: info@nmac.org<br>
                <a href='http://nmac.org'>www.nmac.org</a></p>
                
                <p style='font-size: 12px; color: #999; margin-top: 20px;'>
                    You received this email because you signed up for a membership at NMAC.<br>
                    <a href='#' style='color: #999;'>Unsubscribe</a> | 
                    <a href='#' style='color: #999;'>Privacy Policy</a>
                </p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($to, $subject, $message);
}

// Get membership benefits based on type
function getMembershipBenefits($membership_type) {
    $benefits = [
        'individual' => [
            'Unlimited free admission to all exhibitions',
            '10% discount in museum shop and café',
            'Monthly member newsletter with exclusive content',
            'Priority registration for events and workshops',
            'Free coat check service',
            'Access to member-only events',
            'Invitations to exhibition openings'
        ],
        'family' => [
            'Unlimited free admission for 2 adults + children under 18',
            '15% discount in museum shop and café',
            'Monthly member newsletter with exclusive content',
            'Priority registration for events and workshops',
            'Free coat check service',
            'Access to member-only events and family programs',
            'Invitations to exhibition openings',
            '4 guest passes per year',
            'Special family workshop discounts'
        ],
        'student' => [
            'Unlimited free admission with valid student ID',
            '10% discount in museum shop and café',
            'Monthly member newsletter',
            'Priority registration for educational programs',
            'Free coat check service',
            'Access to student networking events',
            'Special student workshop rates'
        ],
        'senior' => [
            'Unlimited free admission',
            '15% discount in museum shop and café',
            'Monthly member newsletter',
            'Priority registration for events and workshops',
            'Free coat check service',
            'Access to senior-focused programs',
            'Invitations to exhibition openings',
            '2 guest passes per year'
        ],
        'patron' => [
            'All Family membership benefits',
            '20% discount in museum shop and café',
            'Exclusive patron events and receptions',
            'Behind-the-scenes tours with curators',
            'Private viewing opportunities',
            'Recognition in annual report',
            '8 guest passes per year',
            'Complimentary exhibition catalogs',
            'Access to conservation lab tours',
            'Annual patron appreciation dinner'
        ]
    ];
    
    return $benefits[$membership_type] ?? $benefits['individual'];
}

// Send admin notification about new membership
function sendAdminMembershipNotification($user_data, $membership_details) {
    $to = 'admin@nmac.org'; // Admin email
    $subject = "New Membership Registration - " . ucfirst($membership_details['membership_type']);
    
    $message = "
    <h2>New Membership Registration</h2>
    <p>A new member has joined NMAC:</p>
    
    <h3>Member Information:</h3>
    <ul>
        <li><strong>Name:</strong> " . htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']) . "</li>
        <li><strong>Email:</strong> " . htmlspecialchars($user_data['email']) . "</li>
        <li><strong>Member ID:</strong> " . htmlspecialchars($membership_details['member_id']) . "</li>
        <li><strong>Membership Type:</strong> " . ucfirst($membership_details['membership_type']) . "</li>
        <li><strong>Amount Paid:</strong> $" . number_format($membership_details['price_paid'], 2) . "</li>
        <li><strong>Payment Method:</strong> " . ucfirst(str_replace('_', ' ', $membership_details['payment_method'])) . "</li>
        <li><strong>Phone:</strong> " . htmlspecialchars($membership_details['phone_number']) . "</li>
        <li><strong>Registration Date:</strong> " . formatDate($membership_details['start_date']) . "</li>
    </ul>
    
    <p>Please ensure the physical membership card is prepared and mailed within 7-10 business days.</p>
    ";
    
    return sendEmail($to, $subject, $message);
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

// Get exhibitions by status with enhanced image handling
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
        $exhibitions = $stmt->fetchAll();
        
        // Ensure upload directories exist
        ensureUploadDirectories();
        
        return $exhibitions;
    } catch(PDOException $e) {
        error_log("Database error in getExhibitions: " . $e->getMessage());
        return [];
    }
}

// Get events with enhanced image handling
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
        $events = $stmt->fetchAll();
        
        // Ensure upload directories exist
        ensureUploadDirectories();
        
        return $events;
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

// Get collections with enhanced image handling
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
        $collections = $stmt->fetchAll();
        
        // Ensure upload directories exist
        ensureUploadDirectories();
        
        return $collections;
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

// Get virtual tours with video support
function getVirtualTours($limit = 0) {
    global $pdo;
    
    $sql = "SELECT * FROM virtual_tours ORDER BY featured DESC, id DESC";
    
    if ($limit > 0) {
        $sql .= " LIMIT ?";
        $params = [$limit];
    } else {
        $params = [];
    }
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        error_log("Database error in getVirtualTours: " . $e->getMessage());
        return [];
    }
}

// Get virtual tour by ID
function getVirtualTourById($id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM virtual_tours WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch(PDOException $e) {
        error_log("Database error in getVirtualTourById: " . $e->getMessage());
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
