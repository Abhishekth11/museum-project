<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check admin access
requireAdmin();

$page_title = "Event Management - Admin Dashboard";
$action = $_GET['action'] ?? 'list';
$event_id = $_GET['id'] ?? null;
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $form_action = $_POST['action'] ?? '';
        switch ($form_action) {
            case 'add':
                $validation_rules = [
                    'title' => ['required' => true, 'max_length' => 255],
                    'description' => ['required' => true],
                    'event_date' => ['required' => true, 'type' => 'date'],
                    'start_time' => ['required' => true],
                    'end_time' => ['required' => true],
                    'location' => ['required' => true, 'max_length' => 255],
                    'event_type' => ['required' => true],
                    'capacity' => ['type' => 'number'],
                    'price' => ['type' => 'number'],
                    'image' => ['max_length' => 255],
                    'related_exhibition_id' => ['required' => false]
                ];
                if (!hasPermission('create_events')) {
                    $error = 'Permission denied';
                } else {
                    $validation = validateAdminInput($_POST, $validation_rules);
                    $validation['data']['status'] = 'active';
                    if (empty($validation['errors'])) {
                        $result = addEvent($validation['data']);
                        if ($result['success']) {
                            $message = $result['message'];
                            $action = 'list';
                        } else {
                            $error = $result['message'];
                        }
                    } else {
                        $error = implode(', ', $validation['errors']);
                    }
                }
                break;
            case 'edit':
                $validation_rules = [
                    'title' => ['required' => true, 'max_length' => 255],
                    'description' => ['required' => true],
                    'event_date' => ['required' => true, 'type' => 'date'],
                    'start_time' => ['required' => true],
                    'end_time' => ['required' => true],
                    'location' => ['required' => true, 'max_length' => 255],
                    'event_type' => ['required' => true],
                    'capacity' => ['type' => 'number'],
                    'price' => ['type' => 'number'],
                    'image' => ['max_length' => 255],
                    'related_exhibition_id' => ['required' => false],
                    'status' => ['required' => true]
                ];
                if (!hasPermission('edit_events')) {
                    $error = 'Permission denied';
                } else {
                    $validation = validateAdminInput($_POST, $validation_rules);
                    if (empty($validation['errors']) && $event_id) {
                        $result = update_event($event_id, $validation['data']);
                        if ($result['success']) {
                            $message = $result['message'];
                            $action = 'list';
                        } else {
                            $error = $result['message'];
                        }
                    } else {
                        $error = implode(', ', $validation['errors']);
                    }
                }
                break;
            case 'delete':
                if (!hasPermission('delete_events')) {
                    $error = 'Permission denied';
                } else {
                    $id_to_delete = $_POST['event_id'] ?? 0;
                    if ($id_to_delete) {
                        $result = delete_event($id_to_delete);
                        if ($result['success']) {
                            $message = $result['message'];
                        } else {
                            $error = $result['message'];
                        }
                    }
                }
                break;
        }
    }
}

// Fetch data for editing or listing
$event_data = null;
if (($action === 'edit' || $action === 'delete') && $event_id) {
    $event_data = get_event_by_id($event_id);
    if (!$event_data) {
        $error = 'Event not found.';
        $action = 'list';
    }
}

$events = [];
if ($action === 'list') {
    $events = getAllEvents();
}
$exhibitions = getAllExhibitions();

include 'includes/admin-header.php';


// Create Event Function
function addEvent($data) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, start_time, end_time, location, image, price, capacity, event_type, related_exhibition_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(),?)");
        $stmt->execute([
            $data['title'],
            $data['description'],
            $data['event_date'],
            $data['start_time'],
            $data['end_time'],
            $data['location'],
            $data['image'] ?? null,
            $data['price'] ?? 0.00,
            $data['capacity'] ?? 0,
            $data['event_type'],
            $data['related_exhibition_id'] ?: null,
            $data['updated_at'] ?? date('Y-m-d H:i:s'),
        ]);
        $event_id = $pdo->lastInsertId();
        logUserActivity($_SESSION['user_id'], 'event_created', "Created event: " . $data['title']);
        return ['success' => true, 'message' => 'Event created successfully', 'event_id' => $event_id];
    } catch (PDOException $e) {
        error_log("Create event error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to create event. Please try again.'];
    }
}

// Update Event Function
function update_event($event_id, $data) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("UPDATE events SET title = ?, description = ?, event_date = ?, start_time = ?, end_time = ?, location = ?, image = ?, price = ?, capacity = ?, event_type = ?, related_exhibition_id = ?, created_at = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([
            $data['title'],
            $data['description'],
            $data['event_date'],
            $data['start_time'],
            $data['end_time'],
            $data['location'],
            $data['image'] ?? null,
            $data['price'] ?? 0.00,
            $data['capacity'] ?? 0,
            $data['event_type'],
            $data['related_exhibition_id'] ?: null,
            $data['created_at'],
            $event_id
        ]);
        
        logUserActivity($_SESSION['user_id'], 'event_updated', "Updated event ID: $event_id");
        return ['success' => true, 'message' => 'Event updated successfully'];
    } catch (PDOException $e) {
        error_log("Update event error: " . $e->getMessage());
        echo "Error: " . htmlspecialchars($e->getMessage());
        return ['success' => false, 'message' => 'Failed to update event. Please try again.'];
    }
}

// Delete Event Function
function delete_event($event_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
        $stmt->execute([$event_id]);
        logUserActivity($_SESSION['user_id'], 'event_deleted', "Deleted event ID: $event_id");
        return ['success' => true, 'message' => 'Event deleted successfully'];
    } catch (PDOException $e) {
        error_log("Delete event error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to delete event. Please try again.'];
    }
}

// Get All Events Function
function getAllEvents() {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM events ORDER BY event_date DESC, start_time DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Get events error: " . $e->getMessage());
        return [];
    }
}

// Get Event By ID Function
function get_event_by_id($id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Get event by ID error: " . $e->getMessage());
        return null;
    }
}

// Get Exhibitions for dropdown
function getAllExhibitions() {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT id, title FROM exhibitions ORDER BY title ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Get exhibitions error: " . $e->getMessage());
        return [];
    }
}


?>

<div class="admin-container">
    <div class="admin-sidebar">
        <?php include 'includes/sidebar.php'; ?>
    </div>
    <main class="admin-content">
        <div class="admin-header">
            <h1>
                <i class="fas fa-calendar"></i>
                <?php
                switch ($action) {
                    case 'add': echo 'Add New Event'; break;
                    case 'edit': echo 'Edit Event'; break;
                    default: echo 'Manage Events'; break;
                }
                ?>
            </h1>
            <?php if ($action === 'list' && hasPermission('create_events')): ?>
            <a href="events.php?action=add" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Event
            </a>
            <?php endif; ?>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <?php if ($action === 'list'): ?>
        <!-- Events List -->
        <div class="admin-card">
            <div class="card-header">
                <h2>All Events</h2>
                <div class="card-actions col-6">
                    <div class="input-group mb-3">
                        <input type="text" id="event-search" class="form-control" placeholder="Search events..." aria-label="Search events">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-content">
                <?php if (empty($events)): ?>
                <div class="empty-state">
                    <i class="fas fa-calendar"></i>
                    <h3>No Events Found</h3>
                    <p>Create your first event now.</p>
                    <?php if (hasPermission('create_events')): ?>
                    <a href="events.php?action=add" class="btn btn-primary">Add New Event</a>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Dates</th>
                                <th>Location</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="events-table-body">
                            <?php foreach ($events as $event): ?>
                            <tr>
                                <td>
                                    <div class="table-image">
                                    <?php if ($event['image']): ?>
                                    <img src="<?php echo htmlspecialchars($event['image']); ?>" alt="<?php echo htmlspecialchars($exhibition['title']); ?>">
                                    <?php else: ?>
                                    <div class="image-placeholder">
                                        <i class="fas fa-image"></i>
                                    </div>
                                    <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="table-title">
                                        <strong><?php echo htmlspecialchars($event['title']); ?></strong><br>
                                        <small><?php echo htmlspecialchars(substr($event['description'], 0, 50)); ?>...</small>
                                    </div>
                                </td>
                                <td><?php echo ucfirst(str_replace('_', ' ', $event['event_type'])); ?></td>
                                <td>
                                    <div class="date-range">
                                        <small>Date:</small> <?php echo formatDate($event['event_date']); ?><br>
                                        <small>Time:</small> <?php echo date('g:i A', strtotime($event['start_time'])); ?> - <?php echo date('g:i A', strtotime($event['end_time'])); ?>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($event['location']); ?></td>
                            
                                <td>
                                    <div class="action-buttons">
                                        <?php if (hasPermission('edit_events')): ?>
                                        <a href="events.php?action=edit&id=<?php echo $event['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php endif; ?>
                                        <?php if (hasPermission('delete_events')): ?>
                                        <button type="button" class="btn btn-sm btn-danger" title="Delete" onclick="delete_event(<?php echo $event['id']; ?>, '<?php echo htmlspecialchars(addslashes($event['title'])); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php elseif ($action === 'add' || $action === 'edit'): ?>
        <!-- Add/Edit Event Form -->
        <div class="admin-card">
            <div class="card-header">
                <h2><?php echo $action === 'add' ? 'Add New Event' : 'Edit Event'; ?></h2>
            </div>
            <div class="card-content">
                <form method="POST" class="admin-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                    <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="event_id" value="<?php echo $event_data['id']; ?>">
                    <?php endif; ?>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="title">Event Title *</label>
                            <input type="text" id="title" name="title" required class="form-control"
                                value="<?php echo htmlspecialchars($event_data['title'] ?? ''); ?>"
                                placeholder="Enter event title">
                        </div>
                        <div class="form-group">
                            <label for="event_type">Event Type *</label>
                            <select id="event_type" name="event_type" required class="form-control">
                                <option value="">Select Type</option>
                                <?php $types = ['workshop', 'lecture', 'exhibition_opening', 'guided_tour', 'special_event'];
                                foreach ($types as $type): ?>
                                <option value="<?php echo $type; ?>" <?php echo (($event_data['event_type'] ?? '') === $type) ? 'selected' : ''; ?>><?php echo ucfirst(str_replace('_', ' ', $type)); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Description *</label>
                        <textarea id="description" name="description" required class="form-control" rows="4"
                            placeholder="Enter event description"><?php echo htmlspecialchars($event_data['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="event_date">Event Date *</label>
                            <input type="date" id="event_date" name="event_date" required class="form-control"
                                value="<?php echo $event_data['event_date'] ?? ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="start_time">Start Time *</label>
                            <input type="time" id="start_time" name="start_time" required class="form-control"
                                value="<?php echo $event_data['start_time'] ?? ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="end_time">End Time *</label>
                            <input type="time" id="end_time" name="end_time" required class="form-control"
                                value="<?php echo $event_data['end_time'] ?? ''; ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="location">Location *</label>
                            <input type="text" id="location" name="location" required class="form-control"
                                value="<?php echo htmlspecialchars($event_data['location'] ?? ''); ?>"
                                placeholder="Enter event location">
                        </div>
                        <div class="form-group">
                            <label for="capacity">Capacity</label>
                            <input type="number" id="capacity" name="capacity" class="form-control" min="0"
                                value="<?php echo $event_data['capacity'] ?? ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="price">Price ($)</label>
                            <input type="number" id="price" name="price" class="form-control" min="0" step="0.01"
                                value="<?php echo $event_data['price'] ?? '0'; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="related_exhibition_id">Related Exhibition</label>
                        <select id="related_exhibition_id" name="related_exhibition_id" class="form-control">
                            <option value="">None</option>
                            <?php foreach ($exhibitions as $exhibition): ?>
                            <option value="<?php echo $exhibition['id']; ?>" <?php echo (($event_data['related_exhibition_id'] ?? '') == $exhibition['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($exhibition['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="image">Image URL</label>
                        <input type="url" id="image" name="image" class="form-control"
                            placeholder="/placeholder.svg?height=400&width=600"
                            value="<?php echo htmlspecialchars($event_data['image'] ?? ''); ?>">
                    </div>

                    <?php if ($action === 'edit'): ?>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" class="form-control">
                            <option value="active" <?php echo (($event_data['status'] ?? '') === 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo (($event_data['status'] ?? '') === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                            <option value="cancelled" <?php echo (($event_data['status'] ?? '') === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            <?php echo $action === 'add' ? 'Create Event' : 'Update Event'; ?>
                        </button>
                        <a href="events.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </main>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Confirm Delete</h3>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete the event "<span id="delete_eventName"></span>"?</p>
            <p class="warning">This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <form method="POST" id="deleteForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="event_id" id="delete_eventId">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                <button type="submit" class="btn btn-danger">Delete Event</button>
            </form>
        </div>
    </div>
</div>

<script>
// Search functionality
const searchInput = document.getElementById('event-search');
if (searchInput) {
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const tableBody = document.getElementById('events-table-body');
        const rows = tableBody.getElementsByTagName('tr');
        for (let row of rows) {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        }
    });
}

// Delete modal functionality
function delete_event(id, name) {
    document.getElementById('delete_eventId').value = id;
    document.getElementById('delete_eventName').textContent = name;
    document.getElementById('deleteModal').style.display = 'block';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('deleteModal');
    if (event.target === modal) {
        closeDeleteModal();
    }
});

document.querySelector('#deleteModal .close').onclick = function() {
    closeDeleteModal();
};

// Form validation for dates (optional)
document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.getElementById('event_date');
    const endDateInput = document.getElementById('end_time'); // using end_time only for time validation here (if needed)
});
</script>

<?php include 'includes/admin-footer.php'; ?>
