<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check admin access
requireModerator();

$page_title = "Collection Management - Admin Dashboard";
$action = $_GET['action'] ?? 'list';
$collection_id = $_GET['id'] ?? null;
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
                $result = addCollection($_POST);
                if ($result['success']) {
                    $message = $result['message'];
                    $action = 'list';
                } else {
                    $error = $result['message'];
                }
                break;
                
            case 'edit':
                $result = updateCollectionData($collection_id, $_POST);
                if ($result['success']) {
                    $message = $result['message'];
                    $action = 'list';
                } else {
                    $error = $result['message'];
                }
                break;
                
            case 'delete':
                $result = deleteCollectionData($_POST['collection_id']);
                if ($result['success']) {
                    $message = $result['message'];
                } else {
                    $error = $result['message'];
                }
                break;
        }
    }
}

// Get collection data for editing
$collection_data = null;
if ($action === 'edit' && $collection_id) {
    $collection_data = getCollectionById($collection_id);
    if (!$collection_data) {
        $error = 'Collection not found.';
        $action = 'list';
    }
}

// Get all collections for listing
$collections = [];
if ($action === 'list') {
    $collections = getAllCollections();
}

// Add Collection Function
function addCollection($data) {
    global $pdo;
    
    if (!hasPermission('manage_collections')) {
        return ['success' => false, 'message' => 'Permission denied'];
    }
    
    // Validate required fields
    $validation = validateCollectionData($data);
    if (!$validation['success']) {
        return $validation;
    }
    
    try {
        // Handle image upload
        $image_filename = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_result = handleImageUpload($_FILES['image'], 'collections');
            if ($upload_result['success']) {
                $image_filename = $upload_result['filename'];
            } else {
                return ['success' => false, 'message' => $upload_result['message']];
            }
        }
        
        $stmt = $pdo->prepare("INSERT INTO collections (title, description, artist, year, medium, category, dimensions, acquisition_date, image, status, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        
        $stmt->execute([
            $data['title'],
            $data['description'],
            $data['artist'],
            $data['year'],
            $data['medium'],
            $data['category'],
            $data['dimensions'] ?? null,
            $data['acquisition_date'] ?? null,
            $image_filename,
            $data['status'] ?? 'active',
            $_SESSION['user_id']
        ]);
        
        $collection_id = $pdo->lastInsertId();
        
        logUserActivity($_SESSION['user_id'], 'collection_created', "Created collection item: " . $data['title']);
        
        return ['success' => true, 'message' => 'Collection item created successfully', 'collection_id' => $collection_id];
        
    } catch(PDOException $e) {
        error_log("Create collection error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to create collection item. Please try again.'];
    }
}

// Update Collection Function
function updateCollectionData($collection_id, $data) {
    global $pdo;
    
    if (!hasPermission('manage_collections')) {
        return ['success' => false, 'message' => 'Permission denied'];
    }
    
    // Validate required fields
    $validation = validateCollectionData($data);
    if (!$validation['success']) {
        return $validation;
    }
    
    try {
        // Handle image upload
        $image_filename = $data['current_image'] ?? null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_result = handleImageUpload($_FILES['image'], 'collections');
            if ($upload_result['success']) {
                // Delete old image if exists
                if ($image_filename && file_exists("../uploads/collections/" . $image_filename)) {
                    unlink("../uploads/collections/" . $image_filename);
                }
                $image_filename = $upload_result['filename'];
            } else {
                return ['success' => false, 'message' => $upload_result['message']];
            }
        }
        
        $stmt = $pdo->prepare("UPDATE collections SET title = ?, description = ?, artist = ?, year = ?, medium = ?, category = ?, dimensions = ?, acquisition_date = ?, image = ?, status = ? WHERE id = ?");
        
        $stmt->execute([
            $data['title'],
            $data['description'],
            $data['artist'],
            $data['year'],
            $data['medium'],
            $data['category'],
            $data['dimensions'] ?? null,
            $data['acquisition_date'] ?? null,
            $image_filename,
            $data['status'] ?? 'active',
            $collection_id
        ]);
        
        logUserActivity($_SESSION['user_id'], 'collection_updated', "Updated collection ID: $collection_id");
        
        return ['success' => true, 'message' => 'Collection item updated successfully'];
        
    } catch(PDOException $e) {
        error_log("Update collection error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to update collection item. Please try again.'];
    }
}

// Delete Collection Function
function deleteCollectionData($collection_id) {
    global $pdo;
    
    if (!hasPermission('manage_collections')) {
        return ['success' => false, 'message' => 'Permission denied'];
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE collections SET status = 'deleted' WHERE id = ?");
        $stmt->execute([$collection_id]);
        
        logUserActivity($_SESSION['user_id'], 'collection_deleted', "Deleted collection ID: $collection_id");
        
        return ['success' => true, 'message' => 'Collection item deleted successfully'];
        
    } catch(PDOException $e) {
        error_log("Delete collection error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to delete collection item. Please try again.'];
    }
}

// Get All Collections Function
function getAllCollections() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT c.*, u.first_name, u.last_name FROM collections c LEFT JOIN users u ON c.created_by = u.id WHERE c.status != 'deleted' ORDER BY c.created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        error_log("Get collections error: " . $e->getMessage());
        return [];
    }
}

// Validate Collection Data
function validateCollectionData($data) {
    $errors = [];
    
    if (empty($data['title'])) {
        $errors[] = 'Title is required';
    }
    
    if (empty($data['description'])) {
        $errors[] = 'Description is required';
    }
    
    if (empty($data['artist'])) {
        $errors[] = 'Artist name is required';
    }
    
    if (empty($data['year'])) {
        $errors[] = 'Year is required';
    } else if (!is_numeric($data['year']) || $data['year'] < 0 || $data['year'] > date('Y')) {
        $errors[] = 'Year must be a valid year';
    }
    
    if (empty($data['medium'])) {
        $errors[] = 'Medium is required';
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
    <div class="col-3">
        <?php include 'includes/sidebar.php'; ?>
    </div>
    <div class="col-9">
        <main class="admin-main">
        <div class="admin-header">
            <h1>
                <i class="fas fa-palette"></i>
                <?php 
                switch($action) {
                    case 'add': echo 'Add New Collection Item'; break;
                    case 'edit': echo 'Edit Collection Item'; break;
                    default: echo 'Manage Collections'; break;
                }
                ?>
            </h1>
            <?php if ($action === 'list' && hasPermission('manage_collections')): ?>
            <a href="collections.php?action=add" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Item
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
        <!-- Collections List -->
        <div class="admin-card">
            <div class="card-header">
                <h2>All Collection Items</h2>
                <div class="card-actions">
                    <div class="search-box">
                        <input type="text" id="collection-search" placeholder="Search collections...">
                        <i class="fas fa-search"></i>
                    </div>
                    <div class="filter-box">
                        <select id="category-filter">
                            <option value="">All Categories</option>
                            <option value="Painting">Painting</option>
                            <option value="Sculpture">Sculpture</option>
                            <option value="Photography">Photography</option>
                            <option value="Digital Art">Digital Art</option>
                            <option value="Mixed Media">Mixed Media</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-content">
                <?php if (empty($collections)): ?>
                <div class="empty-state">
                    <i class="fas fa-palette"></i>
                    <h3>No Collection Items Found</h3>
                    <p>Start by adding your first collection item.</p>
                    <?php if (hasPermission('manage_collections')): ?>
                    <a href="collections.php?action=add" class="btn btn-primary">Add New Item</a>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Artist</th>
                                <th>Year</th>
                                <th>Category</th>
                                <th>Medium</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="collections-table-body">
                            <?php foreach ($collections as $collection): ?>
                            <tr data-category="<?php echo htmlspecialchars($collection['category']); ?>">
                                <td>
                                    <div class="table-image">
                                        <?php if ($collection['image']): ?>
                                        <img src="../uploads/collections/<?php echo htmlspecialchars($collection['image']); ?>" alt="<?php echo htmlspecialchars($collection['title']); ?>">
                                        <?php else: ?>
                                        <div class="image-placeholder">
                                            <i class="fas fa-palette"></i>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="table-title">
                                        <strong><?php echo htmlspecialchars($collection['title']); ?></strong>
                                        <small><?php echo htmlspecialchars(substr($collection['description'], 0, 100)); ?>...</small>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($collection['artist']); ?></td>
                                <td><?php echo htmlspecialchars($collection['year']); ?></td>
                                <td>
                                    <span class="badge badge-category"><?php echo htmlspecialchars($collection['category']); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($collection['medium']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $collection['status']; ?>">
                                        <?php echo ucfirst($collection['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="../collection-detail.php?id=<?php echo $collection['id']; ?>" class="btn btn-sm btn-secondary" title="View" target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if (hasPermission('manage_collections')): ?>
                                        <a href="collections.php?action=edit&id=<?php echo $collection['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" title="Delete" onclick="deleteCollection(<?php echo $collection['id']; ?>, '<?php echo htmlspecialchars($collection['title']); ?>')">
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
        <!-- Add/Edit Collection Form -->
        <div class="admin-card">
            <div class="card-header">
                <h2><?php echo $action === 'add' ? 'Add New Collection Item' : 'Edit Collection Item'; ?></h2>
                <a href="collections.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
            <div class="card-content">
                <form method="POST" enctype="multipart/form-data" class="admin-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                    <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($collection_data['image'] ?? ''); ?>">
                    <?php endif; ?>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="title">Title *</label>
                            <input type="text" id="title" name="title" required 
                                   value="<?php echo htmlspecialchars($collection_data['title'] ?? ''); ?>"
                                   placeholder="Enter item title">
                        </div>
                        <div class="form-group">
                            <label for="artist">Artist *</label>
                            <input type="text" id="artist" name="artist" required 
                                   value="<?php echo htmlspecialchars($collection_data['artist'] ?? ''); ?>"
                                   placeholder="Enter artist name">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Description *</label>
                        <textarea id="description" name="description" required rows="4" 
                                  placeholder="Enter item description"><?php echo htmlspecialchars($collection_data['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="year">Year *</label>
                            <input type="number" id="year" name="year" required 
                                   value="<?php echo $collection_data['year'] ?? ''; ?>"
                                   placeholder="YYYY" min="1" max="<?php echo date('Y'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="medium">Medium *</label>
                            <input type="text" id="medium" name="medium" required 
                                   value="<?php echo htmlspecialchars($collection_data['medium'] ?? ''); ?>"
                                   placeholder="e.g., Oil on canvas, Bronze, etc.">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="category">Category *</label>
                            <select id="category" name="category" required>
                                <option value="">Select Category</option>
                                <option value="Painting" <?php echo ($collection_data['category'] ?? '') === 'Painting' ? 'selected' : ''; ?>>Painting</option>
                                <option value="Sculpture" <?php echo ($collection_data['category'] ?? '') === 'Sculpture' ? 'selected' : ''; ?>>Sculpture</option>
                                <option value="Photography" <?php echo ($collection_data['category'] ?? '') === 'Photography' ? 'selected' : ''; ?>>Photography</option>
                                <option value="Drawing" <?php echo ($collection_data['category'] ?? '') === 'Drawing' ? 'selected' : ''; ?>>Drawing</option>
                                <option value="Print" <?php echo ($collection_data['category'] ?? '') === 'Print' ? 'selected' : ''; ?>>Print</option>
                                <option value="Digital Art" <?php echo ($collection_data['category'] ?? '') === 'Digital Art' ? 'selected' : ''; ?>>Digital Art</option>
                                <option value="Mixed Media" <?php echo ($collection_data['category'] ?? '') === 'Mixed Media' ? 'selected' : ''; ?>>Mixed Media</option>
                                <option value="Installation" <?php echo ($collection_data['category'] ?? '') === 'Installation' ? 'selected' : ''; ?>>Installation</option>
                                <option value="Textile" <?php echo ($collection_data['category'] ?? '') === 'Textile' ? 'selected' : ''; ?>>Textile</option>
                                <option value="Ceramic" <?php echo ($collection_data['category'] ?? '') === 'Ceramic' ? 'selected' : ''; ?>>Ceramic</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="dimensions">Dimensions</label>
                            <input type="text" id="dimensions" name="dimensions" 
                                   value="<?php echo htmlspecialchars($collection_data['dimensions'] ?? ''); ?>"
                                   placeholder="e.g., 100 x 80 cm">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="acquisition_date">Acquisition Date</label>
                            <input type="date" id="acquisition_date" name="acquisition_date" 
                                   value="<?php echo $collection_data['acquisition_date'] ?? ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="active" <?php echo ($collection_data['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="on_loan" <?php echo ($collection_data['status'] ?? '') === 'on_loan' ? 'selected' : ''; ?>>On Loan</option>
                                <option value="in_storage" <?php echo ($collection_data['status'] ?? '') === 'in_storage' ? 'selected' : ''; ?>>In Storage</option>
                                <option value="under_restoration" <?php echo ($collection_data['status'] ?? '') === 'under_restoration' ? 'selected' : ''; ?>>Under Restoration</option>
                                <option value="archived" <?php echo ($collection_data['status'] ?? '') === 'archived' ? 'selected' : ''; ?>>Archived</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="image">Item Image</label>
                        <input type="file" id="image" name="image" accept="image/*">
                        <small class="form-help">Maximum file size: 5MB. Supported formats: JPEG, PNG, GIF, WebP</small>
                        <?php if ($action === 'edit' && $collection_data['image']): ?>
                        <div class="current-image">
                            <img src="../uploads/collections/<?php echo htmlspecialchars($collection_data['image']); ?>" alt="Current image" style="max-width: 200px; margin-top: 10px;">
                            <p><small>Current image</small></p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            <?php echo $action === 'add' ? 'Create Item' : 'Update Item'; ?>
                        </button>
                        <a href="collections.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
        </main>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Confirm Delete</h3>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete the collection item "<span id="deleteCollectionName"></span>"?</p>
            <p class="warning">This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <form method="POST" id="deleteForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="collection_id" id="deleteCollectionId">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                <button type="submit" class="btn btn-danger">Delete Item</button>
            </form>
        </div>
    </div>
</div>

<script>
// Search functionality
document.getElementById('collection-search').addEventListener('input', function() {
    filterCollections();
});

// Category filter
document.getElementById('category-filter').addEventListener('change', function() {
    filterCollections();
});

function filterCollections() {
    const searchTerm = document.getElementById('collection-search').value.toLowerCase();
    const categoryFilter = document.getElementById('category-filter').value;
    const tableBody = document.getElementById('collections-table-body');
    const rows = tableBody.getElementsByTagName('tr');
    
    for (let row of rows) {
        const text = row.textContent.toLowerCase();
        const category = row.getAttribute('data-category');
        
        const matchesSearch = text.includes(searchTerm);
        const matchesCategory = categoryFilter === '' || category === categoryFilter;
        
        row.style.display = (matchesSearch && matchesCategory) ? '' : 'none';
    }
}

// Delete modal functionality
function deleteCollection(id, name) {
    document.getElementById('deleteCollectionId').value = id;
    document.getElementById('deleteCollectionName').textContent = name;
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
</script>

<?php include 'includes/admin-footer.php'; ?>
