<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check admin access
requireModerator();

$page_title = "Manage Exhibitions - Admin Panel";
$action = $_GET['action'] ?? 'list';
$exhibition_id = $_GET['id'] ?? null;
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
                $result = addExhibition($_POST);
                if ($result['success']) {
                    $message = $result['message'];
                    $action = 'list';
                } else {
                    $error = $result['message'];
                }
                break;
                
            case 'edit':
                $result = updateExhibitionData($exhibition_id, $_POST);
                if ($result['success']) {
                    $message = $result['message'];
                    $action = 'list';
                } else {
                    $error = $result['message'];
                }
                break;
                
            case 'delete':
                $result = deleteExhibitionData($_POST['exhibition_id']);
                if ($result['success']) {
                    $message = $result['message'];
                } else {
                    $error = $result['message'];
                }
                break;
        }
    }
}

// Get exhibition data for editing
$exhibition_data = null;
if ($action === 'edit' && $exhibition_id) {
    $exhibition_data = getExhibitionById($exhibition_id);
    if (!$exhibition_data) {
        $error = 'Exhibition not found.';
        $action = 'list';
    }
}
// Get all exhibitions for listing
$exhibitions = [];
if ($action === 'list') {
    $exhibitions = getAllExhibitions();
}

// Add Exhibition Function
function addExhibition($data) {
    global $pdo;
    
    if (!hasPermission('create_exhibitions')) {
        return ['success' => false, 'message' => 'Permission denied'];
    }
    
    // Validate required fields
    $validation = validateExhibitionData($data);
    if (!$validation['success']) {
        return $validation;
    }
    
    try {
        // Handle image upload
        $image_filename = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_result = handleImageUpload($_FILES['image'], 'exhibitions');
            if ($upload_result['success']) {
                $image_filename = $upload_result['filename'];
            } else {
                return ['success' => false, 'message' => $upload_result['message']];
            }
        }
        $stmt = $pdo->prepare("INSERT INTO exhibitions (title, description, start_date, end_date, location, category, image, exhibitions_status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        
        $stmt->execute([
            $data['title'],
            $data['description'],
            $data['start_date'],
            $data['end_date'],
            $data['location'],
            $data['category'],
            $image_filename,
            $data['exhibitions_status'] ?? 'upcoming'
        ]);
        
        $exhibition_id = $pdo->lastInsertId();
        
        logUserActivity($_SESSION['user_id'], 'exhibition_created', "Created exhibition: " . $data['title']);
        
        return ['success' => true, 'message' => 'Exhibition created successfully', 'exhibition_id' => $exhibition_id];
        
    } catch(PDOException $e) {
        error_log("Create exhibition error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to create exhibition. Please try again.'];
    }
}

// Update Exhibition Function
function updateExhibitionData($exhibition_id, $data) {
    global $pdo;
    
    if (!hasPermission('edit_exhibitions')) {
        return ['success' => false, 'message' => 'Permission denied'];
    }
    
    // Validate required fields
    $validation = validateExhibitionData($data);
    if (!$validation['success']) {
        return $validation;
    }
    
    try {
        // Handle image upload
        $image_filename = $data['current_image'] ?? null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_result = handleImageUpload($_FILES['image'], 'exhibitions');
            if ($upload_result['success']) {
                // Delete old image if exists
                if ($image_filename && file_exists("../uploads/exhibitions/" . $image_filename)) {
                    unlink("../uploads/exhibitions/" . $image_filename);
                }
                $image_filename = $upload_result['filename'];
            } else {
                return ['success' => false, 'message' => $upload_result['message']];
            }
        }
        

        $stmt = $pdo->prepare("UPDATE exhibitions SET title = ?, description = ?, start_date = ?, end_date = ?, location = ? , image = ?, category = ?,exhibitions_status = ?, updated_at = NOW() WHERE id = ?");
        
        $stmt->execute([
            $data['title'],
            $data['description'],
            $data['start_date'],
            $data['end_date'],
            $data['location'],
            $image_filename,
            $data['category'],
            $data['exhibitions_status'] ?? 'upcoming',
            $exhibition_id
        ]);
        logUserActivity($_SESSION['user_id'], 'exhibition_updated', "Updated exhibition ID: $exhibition_id");
        
        return ['success' => true, 'message' => 'Exhibition updated successfully'];
        
    } catch(PDOException $e) {
        error_log("Update exhibition error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to update exhibition. Please try again.'];
    }
}

// Delete Exhibition Function
function deleteExhibitionData($exhibition_id) {
    global $pdo;
    
    if (!hasPermission('delete_exhibitions')) {
        return ['success' => false, 'message' => 'Permission denied'];
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM exhibitions WHERE id = ?");
        $stmt->execute([$exhibition_id]);
        
        logUserActivity($_SESSION['user_id'], 'exhibition_deleted', "Deleted exhibition ID: $exhibition_id");
        
        return ['success' => true, 'message' => 'Exhibition deleted successfully'];
        
    } catch(PDOException $e) {
        error_log("Delete exhibition error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to delete exhibition. Please try again.'];
    }
}

// Get All Exhibitions Function
function getAllExhibitions() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM exhibitions ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        error_log("Get exhibitions error: " . $e->getMessage());
        return [];
    }
}

// Validate Exhibition Data
function validateExhibitionData($data) {
    $errors = [];
    
    if (empty($data['title'])) {
        $errors[] = 'Title is required';
    }
    
    if (empty($data['description'])) {
        $errors[] = 'Description is required';
    }
    
    if (empty($data['start_date'])) {
        $errors[] = 'Start date is required';
    }
    
    if (empty($data['end_date'])) {
        $errors[] = 'End date is required';
    }
    
    if (!empty($data['start_date']) && !empty($data['end_date'])) {
        if (strtotime($data['start_date']) > strtotime($data['end_date'])) {
            $errors[] = 'End date must be after start date';
        }
    }
    
    if (empty($data['location'])) {
        $errors[] = 'Location is required';
    }
    
    if (empty($data['category'])) {
        $errors[] = 'Category is required';
    }
    
    if (!empty($errors)) {
        return ['success' => false, 'message' => implode(', ', $errors)];
    }
    
    return ['success' => true];
}

// Handle Image Upload
function handleImageUpload($file, $folder) {
    $upload_dir = "../uploads/$folder/";
    
    // Create directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Validate file
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
        return ['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.'];
    }
    
    if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
        return ['success' => false, 'message' => 'File size too large. Maximum 5MB allowed.'];
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename];
    } else {
        return ['success' => false, 'message' => 'Failed to upload file.'];
    }
}

include 'includes/admin-header.php';
?>

<div class="admin-container">
    <div class="admin-sidebar">
        <?php include 'includes/sidebar.php'; ?>
     </div>
    <main class="admin-content">
        <div class="admin-header">
            <h1>
                <i class="fas fa-image"></i>
                <?php 
                switch($action) {
                    case 'add': echo 'Add New Exhibition'; break;
                    case 'edit': echo 'Edit Exhibition'; break;
                    default: echo 'Manage Exhibitions'; break;
                }
                ?>
            </h1>
            <?php if ($action === 'list' && hasPermission('create_exhibitions')): ?>
            <a href="exhibitions.php?action=add" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Exhibition
            </a>
            <?php endif; ?>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <?php if ($action === 'list'): ?>
        <!-- Exhibitions List -->
        <div class="admin-card">
            <div class="card-header ">
            <h2>All Exhibitions</h2>
            <div class="card-actions col-6">
                <div class="input-group mb-3">
                    <input type="text" id="exhibition-search" class="form-control" placeholder="Search exhibitions..." aria-label="Search exhibitions">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            </div>
            <div class="card-content">
            <?php if (empty($exhibitions)): ?>
            <div class="empty-state">
                <i class="fas fa-image"></i>
                <h3>No Exhibitions Found</h3>
                <p>Start by creating your first exhibition.</p>
                <?php if (hasPermission('create_exhibitions')): ?>
                <a href="exhibitions.php?action=add" class="btn btn-primary">Add New Exhibition</a>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="admin-table">
                <thead>
                    <tr>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Dates</th>
                    <th>Location</th>
                    <th>status</th>
                    <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="exhibitions-table-body">
                    <?php foreach ($exhibitions as $exhibition): ?>
                    <tr>
                    <td>
                        <div class="table-image">
                        <?php if ($exhibition['image']): ?>
                        <img src="<?php echo htmlspecialchars($exhibition['image']); ?>" alt="<?php echo htmlspecialchars($exhibition['title']); ?>">
                        <?php else: ?>
                        <div class="image-placeholder">
                            <i class="fas fa-image"></i>
                        </div>
                        <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <div class="table-title">
                        <strong><?php echo htmlspecialchars($exhibition['title']); ?></strong>
                        <small><?php echo htmlspecialchars(substr($exhibition['description'], 0, 100)); ?>...</small>
                        </div>
                    </td>
                    <td>
                        <span class=""><?php echo htmlspecialchars($exhibition['category']); ?></span>
                    </td>
                    <td>
                        <div class="date-range">
                        <small>From:</small> <?php echo formatDate($exhibition['start_date']); ?><br>
                        <small>To:</small> <?php echo formatDate($exhibition['end_date']); ?>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($exhibition['location']); ?></td>
                    <td>
                        <span >
                        <?php echo ucfirst($exhibition['exhibitions_status']); ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                        <a href="../exhibition-detail.php?id=<?php echo $exhibition['id']; ?>" class="btn btn-sm btn-secondary" title="View" target="_blank">
                            <i class="fas fa-eye"></i>
                        </a>
                        <?php if (hasPermission('edit_exhibitions')): ?>
                        <a href="exhibitions.php?action=edit&id=<?php echo $exhibition['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <?php endif; ?>
                        <?php if (hasPermission('delete_exhibitions')): ?>
                        <button type="button" class="btn btn-sm btn-danger" title="Delete" onclick="deleteExhibition(<?php echo $exhibition['id']; ?>, '<?php echo htmlspecialchars($exhibition['title']); ?>')">
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
        <!-- Add/Edit Exhibition Form -->
        <div class="admin-card">
            <div class="card-header">
            <h2><?php echo $action === 'add' ? 'Add New Exhibition' : 'Edit Exhibition'; ?></h2>
            </div>
            <div class="card-content">
            <form method="POST" enctype="multipart/form-data" class="admin-form">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="<?php echo $action; ?>">
            <?php if ($action === 'edit'): ?>
            <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($exhibition_data['image'] ?? ''); ?>">
            <?php endif; ?>

            <div class="form-row">
            <div class="form-group">
                <label for="title">Exhibition Title *</label>
                <input type="text" id="title" name="title" required 
                   value="<?php echo htmlspecialchars($exhibition_data['title'] ?? ''); ?>"
                   placeholder="Enter exhibition title">
            </div>
            <div class="form-group">
                <label for="category">Category *</label>
                <select id="category" name="category" required>
                <option value="">Select Category</option>
                <?php 
                $categories = ['Painting', 'Sculpture', 'Photography', 'Installation', 'Performance Art', 'Digital Art'];
                foreach ($categories as $category): ?>
                <option value="<?php echo $category; ?>" <?php echo ($exhibition_data['category'] ?? '') === $category ? 'selected' : ''; ?>>
                <?php echo $category; ?>
                </option>
                <?php endforeach; ?>
                </select>
            </div>
            </div>

            <div class="form-group">
            <label for="description">Description *</label>
            <textarea id="description" name="description" required rows="4" 
                  placeholder="Enter exhibition description"><?php echo htmlspecialchars($exhibition_data['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-row">
            <div class="form-group">
                <label for="start_date">Start Date *</label>
                <input type="date" id="start_date" name="start_date" required 
                   value="<?php echo $exhibition_data['start_date'] ?? ''; ?>">
            </div>
            <div class="form-group">
                <label for="end_date">End Date *</label>
                <input type="date" id="end_date" name="end_date" required 
                   value="<?php echo $exhibition_data['end_date'] ?? ''; ?>">
            </div>
            </div>

            <div class="form-row">
            <div class="form-group">
                <label for="location">Location *</label>
                <input type="text" id="location" name="location" required 
                   value="<?php echo htmlspecialchars($exhibition_data['location'] ?? ''); ?>"
                   placeholder="e.g., Gallery A, Main Hall">
            </div>
            </div>

            <div class="form-row">
            <div class="form-group">
                <label for="exhibitions_status">exhibitions_status</label>
                <select id="exhibitions_status" name="exhibitions_status">
                <?php 
                $exhibitions_statuses = ['current', 'upcoming', 'past'];
                foreach ($exhibitions_statuses as $exhibitions_status): ?>
                <option value="<?php echo $exhibitions_status; ?>" <?php echo ($exhibition_data['exhibitions_status'] ?? 'active') === $exhibitions_status ? 'selected' : ''; ?>>
                <?php echo ucfirst($exhibitions_status); ?>
                </option>
                <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="image">Exhibition Image</label>
                <input type="file" id="image" name="image" accept="image/*">
                <small class="form-help">Maximum file size: 5MB. Supported formats: JPEG, PNG, GIF, WebP</small>
                <?php if ($action === 'edit' && $exhibition_data['image']): ?>
                <div class="current-image">
                <img src="../uploads/exhibitions/<?php echo htmlspecialchars($exhibition_data['image']); ?>" alt="Current image" style="max-width: 200px; margin-top: 10px;">
                <p><small>Current image</small></p>
                </div>
                <?php endif; ?>
            </div>
            </div>

            <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                <?php echo $action === 'add' ? 'Create Exhibition' : 'Update Exhibition'; ?>
            </button>
            <a href="exhibitions.php" class="btn btn-secondary">
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
            <p>Are you sure you want to delete the exhibition "<span id="deleteExhibitionName"></span>"?</p>
            <p class="warning">This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <form method="POST" id="deleteForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="exhibition_id" id="deleteExhibitionId">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                <button type="submit" class="btn btn-danger">Delete Exhibition</button>
            </form>
        </div>
    </div>
</div>

<script>
// Search functionality
document.getElementById('exhibition-search').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const tableBody = document.getElementById('exhibitions-table-body');
    const rows = tableBody.getElementsByTagName('tr');
    
    for (let row of rows) {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    }
});

// Delete modal functionality
function deleteExhibition(id, name) {
    document.getElementById('deleteExhibitionId').value = id;
    document.getElementById('deleteExhibitionName').textContent = name;
    document.getElementById('deleteModal').style.display = 'block';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('deleteModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}

// Close modal with X button
document.querySelector('.close').onclick = function() {
    closeDeleteModal();
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    if (startDateInput && endDateInput) {
        function validateDates() {
            const startDate = new Date(startDateInput.value);
            const endDate = new Date(endDateInput.value);
            
            if (startDate && endDate && startDate > endDate) {
                endDateInput.setCustomValidity('End date must be after start date');
            } else {
                endDateInput.setCustomValidity('');
            }
        }
        
        startDateInput.addEventListener('change', validateDates);
        endDateInput.addEventListener('change', validateDates);
    }
});
</script>

<?php include 'includes/admin-footer.php'; ?>
