<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check admin access
requireAdmin();

$page_title = "Event Management - Admin Dashboard";
$message = '';
$message_type = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid security token. Please try again.';
        $message_type = 'error';
    } else {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'create':
                if (hasPermission('create_events')) {
                    $validation_rules = [
                        'title' => ['required' => true, 'max_length' => 255],
                        'description' => ['required' => true],
                        'event_date' => ['required' => true, 'type' => 'date'],
                        'event_time' => ['required' => true],
                        'location' => ['required' => true, 'max_length' => 255],
                        'event_type' => ['required' => true],
                        'capacity' => ['type' => 'number'],
                        'price' => ['type' => 'number']
                    ];
                    
                    $validation = validateAdminInput($_POST, $validation_rules);
                    
                    if (empty($validation['errors'])) {
                        $result = createEvent($validation['data']);
                        $message = $result['message'];
                        $message_type = $result['success'] ? 'success' : 'error';
                    } else {
                        $message = 'Please fix the following errors: ' . implode(', ', $validation['errors']);
                        $message_type = 'error';
                    }
                } else {
                    $message = 'You do not have permission to create events.';
                    $message_type = 'error';
                }
                break;
                
            case 'update':
                if (hasPermission('edit_events')) {
                    $event_id = $_POST['event_id'] ?? 0;
                    $validation_rules = [
                        'title' => ['required' => true, 'max_length' => 255],
                        'description' => ['required' => true],
                        'event_date' => ['required' => true, 'type' => 'date'],
                        'event_time' => ['required' => true],
                        'location' => ['required' => true, 'max_length' => 255],
                        'event_type' => ['required' => true],
                        'capacity' => ['type' => 'number'],
                        'price' => ['type' => 'number'],
                        'status' => ['required' => true]
                    ];
                    
                    $validation = validateAdminInput($_POST, $validation_rules);
                    
                    if (empty($validation['errors']) && $event_id > 0) {
                        $result = updateEvent($event_id, $validation['data']);
                        $message = $result['message'];
                        $message_type = $result['success'] ? 'success' : 'error';
                    } else {
                        $message = 'Please fix the following errors: ' . implode(', ', $validation['errors']);
                        $message_type = 'error';
                    }
                } else {
                    $message = 'You do not have permission to edit events.';
                    $message_type = 'error';
                }
                break;
                
            case 'delete':
                if (hasPermission('delete_events')) {
                    $event_id = $_POST['event_id'] ?? 0;
                    if ($event_id > 0) {
                        $result = deleteEvent($event_id);
                        $message = $result['message'];
                        $message_type = $result['success'] ? 'success' : 'error';
                    }
                } else {
                    $message = 'You do not have permission to delete events.';
                    $message_type = 'error';
                }
                break;
        }
    }
}

// Get events for display
$events = getEvents();
$editing_event = null;

// Check if editing an event
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editing_event = getEventById($_GET['edit']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-page">
    <div class="admin-container">
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-museum"></i> NMAC Admin</h2>
                <div class="user-role-badge">
                    <i class="fas fa-user-shield"></i>
                    <span><?php echo ucfirst($_SESSION['user_role']); ?></span>
                </div>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li class="nav-section">Content Management</li>
                    <?php if (hasPermission('manage_exhibitions')): ?>
                    <li><a href="exhibitions.php"><i class="fas fa-image"></i> Exhibitions</a></li>
                    <?php endif; ?>
                    <?php if (hasPermission('manage_events')): ?>
                    <li><a href="events.php" class="active"><i class="fas fa-calendar"></i> Events</a></li>
                    <?php endif; ?>
                    <?php if (hasPermission('manage_collections')): ?>
                    <li><a href="collections.php"><i class="fas fa-palette"></i> Collections</a></li>
                    <?php endif; ?>
                    <?php if (hasPermission('manage_users')): ?>
                    <li class="nav-section">User Management</li>
                    <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                    <?php endif; ?>
                    <li class="nav-divider"></li>
                    <li><a href="../index.php"><i class="fas fa-globe"></i> View Website</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <main class="admin-content">
            <header class="admin-header">
                <div class="header-left">
                    <h1>Event Management</h1>
                    <p>Create, edit, and manage museum events</p>
                </div>
                <div class="header-right">
                    <div class="admin-user">
                        <i class="fas fa-user-shield"></i>
                        <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    </div>
                </div>
            </header>
            
            <div class="admin-main-content">
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $message_type; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Event Form -->
                <div class="content-card">
                    <div class="card-header">
                        <h2><?php echo $editing_event ? 'Edit Event' : 'Create New Event'; ?></h2>
                        <?php if ($editing_event): ?>
                            <a href="events.php" class="btn btn-secondary">Cancel Edit</a>
                        <?php endif; ?>
                    </div>
                    
                    <form method="POST" class="admin-form">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="action" value="<?php echo $editing_event ? 'update' : 'create'; ?>">
                        <?php if ($editing_event): ?>
                            <input type="hidden" name="event_id" value="<?php echo $editing_event['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="title">Event Title *</label>
                                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($editing_event['title'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="event_type">Event Type *</label>
                                <select id="event_type" name="event_type" required>
                                    <option value="">Select Type</option>
                                    <option value="workshop" <?php echo ($editing_event['event_type'] ?? '') === 'workshop' ? 'selected' : ''; ?>>Workshop</option>
                                    <option value="lecture" <?php echo ($editing_event['event_type'] ?? '') === 'lecture' ? 'selected' : ''; ?>>Lecture</option>
                                    <option value="exhibition_opening" <?php echo ($editing_event['event_type'] ?? '') === 'exhibition_opening' ? 'selected' : ''; ?>>Exhibition Opening</option>
                                    <option value="guided_tour" <?php echo ($editing_event['event_type'] ?? '') === 'guided_tour' ? 'selected' : ''; ?>>Guided Tour</option>
                                    <option value="special_event" <?php echo ($editing_event['event_type'] ?? '') === 'special_event' ? 'selected' : ''; ?>>Special Event</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description *</label>
                            <textarea id="description" name="description" rows="4" required><?php echo htmlspecialchars($editing_event['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="event_date">Event Date *</label>
                                <input type="date" id="event_date" name="event_date" value="<?php echo $editing_event['event_date'] ?? ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="event_time">Event Time *</label>
                                <input type="time" id="event_time" name="event_time" value="<?php echo $editing_event['event_time'] ?? ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="location">Location *</label>
                                <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($editing_event['location'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="capacity">Capacity</label>
                                <input type="number" id="capacity" name="capacity" value="<?php echo $editing_event['capacity'] ?? ''; ?>" min="1">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="price">Price ($)</label>
                                <input type="number" id="price" name="price" value="<?php echo $editing_event['price'] ?? '0'; ?>" min="0" step="0.01">
                            </div>
                            
                            <?php if ($editing_event): ?>
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status">
                                    <option value="active" <?php echo ($editing_event['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo ($editing_event['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    <option value="cancelled" <?php echo ($editing_event['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="image">Image URL</label>
                            <input type="url" id="image" name="image" value="<?php echo htmlspecialchars($editing_event['image'] ?? ''); ?>" placeholder="/placeholder.svg?height=400&width=600">
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <?php echo $editing_event ? 'Update Event' : 'Create Event'; ?>
                            </button>
                            <?php if ($editing_event): ?>
                                <a href="events.php" class="btn btn-secondary">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
                
                <!-- Events List -->
                <div class="content-card">
                    <div class="card-header">
                        <h2>All Events</h2>
                        <div class="card-actions">
                            <span class="count-badge"><?php echo count($events); ?> events</span>
                        </div>
                    </div>
                    
                    <div class="table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Date & Time</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($events as $event): ?>
                                    <tr>
                                        <td>
                                            <div class="item-info">
                                                <?php if (!empty($event['image'])): ?>
                                                    <img src="<?php echo htmlspecialchars($event['image']); ?>" alt="Event image" class="item-thumbnail">
                                                <?php else: ?>
                                                    <img src="/placeholder.svg?height=50&width=50" alt="No image" class="item-thumbnail">
                                                <?php endif; ?>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($event['title']); ?></strong>
                                                    <small><?php echo htmlspecialchars(substr($event['description'], 0, 100)) . '...'; ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="type-badge type-<?php echo $event['event_type']; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $event['event_type'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="date-time">
                                                <strong><?php echo formatDate($event['event_date']); ?></strong>
                                                <small><?php echo date('g:i A', strtotime($event['event_time'])); ?></small>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($event['location']); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $event['status']; ?>">
                                                <?php echo ucfirst($event['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <?php if (hasPermission('edit_events')): ?>
                                                    <a href="events.php?edit=<?php echo $event['id']; ?>" class="btn btn-sm btn-secondary">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (hasPermission('delete_events')): ?>
                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this event?');">
                                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                
                                <?php if (empty($events)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            <div class="empty-state">
                                                <i class="fas fa-calendar"></i>
                                                <p>No events found. Create your first event!</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="js/admin-dashboard.js"></script>
    <script>
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.remove();
                    }, 300);
                }, 5000);
            });
        });
    </script>
</body>
</html>
