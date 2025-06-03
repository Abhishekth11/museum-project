<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Ensure only admins can access this page
requireAdmin();

// Determine current action (defaults to 'list')
$currentAction = $_GET['action'] ?? 'list';
$userId        = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;

$message = '';
$error   = '';

// Handle form submissions (add / edit / delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $formAction = $_POST['action'] ?? '';
        switch ($formAction) {
            case 'add':
                if (!hasPermission('create_users')) {
                    $error = 'Permission denied';
                    break;
                }
                $validationRules = [
                    'first_name' => ['required' => true, 'max_length' => 100],
                    'last_name'  => ['required' => true, 'max_length' => 100],
                    'email'      => ['required' => true, 'type' => 'email', 'max_length' => 255],
                    'role'       => ['required' => true],
                    'status'     => ['required' => true],
                ];
                $validation = validateAdminInput($_POST, $validationRules);

                if (empty($validation['errors'])) {
                    $result = createUser($validation['data']);
                    if ($result['success']) {
                        $message       = $result['message'];
                        $currentAction = 'list';
                    } else {
                        $error = $result['message'];
                    }
                } else {
                    $error = implode(', ', $validation['errors']);
                }
                break;

            case 'edit':
                if (!hasPermission('edit_users')) {
                    $error = 'Permission denied';
                    break;
                }

                $editingId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
                if ($editingId > 0) {
                    $validationRules = [
                        'first_name' => ['required' => true, 'max_length' => 100],
                        'last_name'  => ['required' => true, 'max_length' => 100],
                        'email'      => ['required' => true, 'type' => 'email', 'max_length' => 255],
                        'role'       => ['required' => true],
                        'status'     => ['required' => true],
                    ];
                    $validation = validateAdminInput($_POST, $validationRules);

                    if (empty($validation['errors'])) {
                        $result = updateUser($editingId, $validation['data']);
                        if ($result['success']) {
                            $message       = $result['message'];
                            $currentAction = 'list';
                        } else {
                            $error = $result['message'];
                        }
                    } else {
                        $error = implode(', ', $validation['errors']);
                    }
                }
                break;

            case 'delete':
                if (!hasPermission('delete_users')) {
                    $error = 'Permission denied';
                    break;
                }
                $deleteId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
                // Prevent deleting the currently logged-in admin
                if ($deleteId && $deleteId !== (int)$_SESSION['user_id']) {
                    $result = deleteUser($deleteId);
                    if ($result['success']) {
                        $message = $result['message'];
                    } else {
                        $error = $result['message'];
                    }
                }
                break;
        }
    }
}

// Fetch data for editing or ensure user exists
$userData = null;
if (in_array($currentAction, ['edit', 'delete'], true) && is_numeric($userId)) {
    $userData = getUserById($userId);
    if (!$userData) {
        $error         = 'User not found.';
        $currentAction = 'list';
    }
}

// For listing, retrieve all users except those marked as 'deleted'
$allUsers = [];
if ($currentAction === 'list') {
    $allUsers = array_filter(getAllUsers(), function($user) {
        return $user['status'] !== 'deleted';
    });
}

include 'includes/admin-header.php';
?>

<div class="admin-container">
    <aside class="admin-sidebar">
        <?php include 'includes/sidebar.php'; ?>
    </aside>

    <main class="admin-content">
        <header class="admin-header">
            <h1>
                <i class="fas fa-users"></i>
                <?php
                    switch ($currentAction) {
                        case 'add':
                            echo 'Add New User';
                            break;
                        case 'edit':
                            echo 'Edit User';
                            break;
                        default:
                            echo 'Manage Users';
                            break;
                    }
                ?>
            </h1>

            <?php if ($currentAction === 'list' && hasPermission('create_users')): ?>
                <a href="users.php?action=add" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New User
                </a>
            <?php endif; ?>
        </header>

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

        <?php if ($currentAction === 'list'): ?>
            <!-- Users List -->
            <div class="admin-card">
                <div class="card-header">
                    <h2>All Users</h2>
                    <div class="card-actions">
                        <div class="input-group mb-3">
                            <input
                                type="text"
                                id="user-search"
                                class="form-control"
                                placeholder="Search users..."
                                aria-label="Search users"
                            >
                            <button class="btn btn-outline-secondary" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-content">
                    <?php if (empty($allUsers)): ?>
                        <div class="empty-state">
                            <i class="fas fa-users"></i>
                            <h3>No Users Found</h3>
                            <p>Create your first user now.</p>
                            <?php if (hasPermission('create_users')): ?>
                                <a href="users.php?action=add" class="btn btn-primary">
                                    Add New User
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Joined</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="users-table-body">
                                    <?php foreach ($allUsers as $user): ?>
                                        <tr>
                                            <td>
                                                <div class="user-info">
                                                    <div class="user-avatar">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                    <div>
                                                        <strong>
                                                            <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                                        </strong><br>
                                                        <small>ID: <?php echo (int)$user['id']; ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td>
                                                <span class="role-badge role-<?php echo htmlspecialchars($user['role']); ?>">
                                                    <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="status-badge status-<?php echo htmlspecialchars($user['status']); ?>">
                                                    <?php echo ucfirst(htmlspecialchars($user['status'])); ?>
                                                </span>
                                            </td>
                                            <td><?php echo formatDate($user['created_at']); ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <?php if (hasPermission('edit_users')): ?>
                                                        <a
                                                            href="users.php?action=edit&id=<?php echo (int)$user['id']; ?>"
                                                            class="btn btn-sm btn-secondary"
                                                            title="Edit"
                                                        >
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    <?php endif; ?>

                                                    <?php if (hasPermission('delete_users') && (int)$user['id'] !== (int)$_SESSION['user_id']): ?>
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-danger"
                                                            title="Delete"
                                                            onclick="confirmDelete(<?php echo (int)$user['id']; ?>, '<?php echo htmlspecialchars(addslashes($user['first_name'] . ' ' . $user['last_name'])); ?>')"
                                                        >
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

        <?php elseif (in_array($currentAction, ['add', 'edit'], true)): ?>
            <!-- Add / Edit User Form -->
            <div class="admin-card">
                <div class="card-header">
                    <h2>
                        <?php echo $currentAction === 'add' ? 'Add New User' : 'Edit User'; ?>
                    </h2>
                </div>
                <div class="card-content">
                    <form method="POST" class="admin-form">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        <input type="hidden" name="action" value="<?php echo $currentAction; ?>">
                        <?php if ($currentAction === 'edit'): ?>
                            <input type="hidden" name="user_id" value="<?php echo (int)$userData['id']; ?>">
                        <?php endif; ?>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First Name *</label>
                                <input
                                    type="text"
                                    id="first_name"
                                    name="first_name"
                                    required
                                    class="form-control"
                                    value="<?php echo htmlspecialchars($userData['first_name'] ?? ''); ?>"
                                    placeholder="Enter first name"
                                >
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name *</label>
                                <input
                                    type="text"
                                    id="last_name"
                                    name="last_name"
                                    required
                                    class="form-control"
                                    value="<?php echo htmlspecialchars($userData['last_name'] ?? ''); ?>"
                                    placeholder="Enter last name"
                                >
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                required
                                class="form-control"
                                value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>"
                                placeholder="Enter email address"
                            >
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="role">Role *</label>
                                <select id="role" name="role" required class="form-control">
                                    <?php
                                        $roles = ['user', 'staff', 'moderator', 'admin'];
                                        foreach ($roles as $roleOption):
                                    ?>
                                        <option
                                            value="<?php echo $roleOption; ?>"
                                            <?php echo (isset($userData['role']) && $userData['role'] === $roleOption) ? 'selected' : ''; ?>
                                        >
                                            <?php echo ucfirst($roleOption); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="status">Status *</label>
                                <select id="status" name="status" required class="form-control">
                                    <?php
                                        $statuses = ['active', 'inactive', 'suspended'];
                                        foreach ($statuses as $statusOption):
                                    ?>
                                        <option
                                            value="<?php echo $statusOption; ?>"
                                            <?php echo (isset($userData['status']) && $userData['status'] === $statusOption) ? 'selected' : ''; ?>
                                        >
                                            <?php echo ucfirst($statusOption); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                <?php echo $currentAction === 'add' ? 'Create User' : 'Update User'; ?>
                            </button>
                            <a href="users.php" class="btn btn-secondary">
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
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <p>
                Are you sure you want to delete the user
                "<span id="deleteUserName"></span>"?
            </p>
            <p class="warning">This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <form method="POST" id="deleteForm">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <input type="hidden" name="action" value="delete">
                <input type="hidden" name="user_id" id="deleteUserId">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">
                    Cancel
                </button>
                <button type="submit" class="btn btn-danger">Delete User</button>
            </form>
        </div>
    </div>
</div>

<script>

document.getElementById('user-search')?.addEventListener('input', function () {
    const searchTerm = this.value.toLowerCase();
    const tableBody = document.getElementById('users-table-body');
    const rows = tableBody?.getElementsByTagName('tr') || [];

    Array.from(rows).forEach(row => {
        const userName = row.querySelector('.user-info strong')?.textContent.toLowerCase() || '';
        const userEmail = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
        const matchesSearch = userName.includes(searchTerm) || userEmail.includes(searchTerm);
        row.style.display = matchesSearch ? '' : 'none';
    });
});


function confirmDelete(userId, userName) {
    const modal = document.getElementById('deleteModal');
    document.getElementById('deleteUserId').value = userId;
    document.getElementById('deleteUserName').textContent = userName;
    modal.style.display = 'block';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}


window.addEventListener('click', function (event) {
    const modal = document.getElementById('deleteModal');
    if (event.target === modal) {
        closeDeleteModal();
    }
});


document.querySelector('#deleteModal .close-modal')?.addEventListener('click', closeDeleteModal);
</script>

<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check admin access
requireAdmin();

$page_title = "User Management - Admin Dashboard";
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
            case 'update_user':
                if (hasPermission('edit_users')) {
                    $user_id = $_POST['user_id'] ?? 0;
                    $update_data = [
                        'first_name' => trim($_POST['first_name'] ?? ''),
                        'last_name' => trim($_POST['last_name'] ?? ''),
                        'email' => trim($_POST['email'] ?? ''),
                        'role' => $_POST['role'] ?? '',
                        'status' => $_POST['status'] ?? ''
                    ];
                    
                    if ($user_id > 0) {
                        $result = updateUser($user_id, $update_data);
                        $message = $result['message'];
                        $message_type = $result['success'] ? 'success' : 'error';
                    }
                } else {
                    $message = 'You do not have permission to edit users.';
                    $message_type = 'error';
                }
                break;
                
            case 'delete_user':
                if (hasPermission('delete_users')) {
                    $user_id = $_POST['user_id'] ?? 0;
                    if ($user_id > 0) {
                        $result = deleteUser($user_id);
                        $message = $result['message'];
                        $message_type = $result['success'] ? 'success' : 'error';
                    }
                } else {
                    $message = 'You do not have permission to delete users.';
                    $message_type = 'error';
                }
                break;
        }
    }
}

// Get users for display
$users = getAllUsers();
$editing_user = null;

// Check if editing a user
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editing_user = getUserById($_GET['edit']);
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
                    <li><a href="events.php"><i class="fas fa-calendar"></i> Events</a></li>
                    <?php endif; ?>
                    <?php if (hasPermission('manage_collections')): ?>
                    <li><a href="collections.php"><i class="fas fa-palette"></i> Collections</a></li>
                    <?php endif; ?>
                    <?php if (hasPermission('manage_users')): ?>
                    <li class="nav-section">User Management</li>
                    <li><a href="users.php" class="active"><i class="fas fa-users"></i> Users</a></li>
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
                    <h1>User Management</h1>
                    <p>Manage user accounts and permissions</p>
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
                
                <!-- User Edit Form (if editing) -->
                <?php if ($editing_user): ?>
                <div class="content-card">
                    <div class="card-header">
                        <h2>Edit User</h2>
                        <a href="users.php" class="btn btn-secondary">Cancel Edit</a>
                    </div>
                    
                    <form method="POST" class="admin-form">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="action" value="update_user">
                        <input type="hidden" name="user_id" value="<?php echo $editing_user['id']; ?>">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First Name *</label>
                                <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($editing_user['first_name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="last_name">Last Name *</label>
                                <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($editing_user['last_name']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($editing_user['email']); ?>" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="role">Role</label>
                                <select id="role" name="role">
                                    <option value="user" <?php echo $editing_user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                    <option value="staff" <?php echo $editing_user['role'] === 'staff' ? 'selected' : ''; ?>>Staff</option>
                                    <option value="moderator" <?php echo $editing_user['role'] === 'moderator' ? 'selected' : ''; ?>>Moderator</option>
                                    <option value="admin" <?php echo $editing_user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status">
                                    <option value="active" <?php echo $editing_user['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo $editing_user['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    <option value="suspended" <?php echo $editing_user['status'] === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Update User</button>
                            <a href="users.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
                <?php endif; ?>
                
                <!-- Users List -->
                <div class="content-card">
                    <div class="card-header">
                        <h2>All Users</h2>
                        <div class="card-actions">
                            <span class="count-badge"><?php echo count($users); ?> users</span>
                        </div>
                    </div>
                    
                    <div class="table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Membership</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>
                                            <div class="user-info">
                                                <div class="user-avatar">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong>
                                                    <small>ID: <?php echo $user['id']; ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <span class="role-badge role-<?php echo $user['role']; ?>">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($user['membership_type'])): ?>
                                                <span class="membership-badge membership-<?php echo $user['membership_type']; ?>">
                                                    <?php echo ucfirst($user['membership_type']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">None</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo $user['status']; ?>">
                                                <?php echo ucfirst($user['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatDate($user['created_at']); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <?php if (hasPermission('edit_users')): ?>
                                                    <a href="users.php?edit=<?php echo $user['id']; ?>" class="btn btn-sm btn-secondary">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (hasPermission('delete_users') && $user['id'] != $_SESSION['user_id']): ?>
                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                        <input type="hidden" name="action" value="delete_user">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            <div class="empty-state">
                                                <i class="fas fa-users"></i>
                                                <p>No users found.</p>
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
