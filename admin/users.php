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

