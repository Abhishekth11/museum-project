<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Enhanced admin access check
requireAdmin();

$page_title = "Admin Dashboard - National Museum of Art & Culture";

// Log dashboard access
logUserActivity($_SESSION['user_id'], 'dashboard_access', 'Accessed admin dashboard');

// Get dashboard statistics
$stats = getDashboardStats();

// Get user's permissions
$user_permissions = getUserPermissions();

// Check for welcome message
$welcome_message = '';
if (isset($_SESSION['login_success'])) {
    $welcome_message = $_SESSION['login_success'];
    unset($_SESSION['login_success']);
}

// Get recent activities
try {
    $recent_exhibitions = $pdo->query("SELECT * FROM exhibitions ORDER BY created_at DESC LIMIT 5")->fetchAll();
    $recent_events = $pdo->query("SELECT * FROM events ORDER BY created_at DESC LIMIT 5")->fetchAll();
    $recent_users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();
} catch(PDOException $e) {
    $recent_exhibitions = $recent_events = $recent_users = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<?php include 'includes/admin-header.php'; ?>
<body class="admin-page">
    <?php if ($welcome_message): ?>
    <div class="admin-welcome-banner">
        <div class="welcome-content">
            <i class="fas fa-shield-alt"></i>
            <span><?php echo htmlspecialchars($welcome_message); ?></span>
            <button class="close-welcome" onclick="this.parentElement.parentElement.style.display='none'">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    <?php endif; ?>

    <div class="admin-container">
        <div class="admin-sidebar">
            <?php include 'includes/sidebar.php'; ?>
        </div>
        <main class="admin-content">
            <header class="admin-header">
                <div class="header-left">
                    <h1>Dashboard</h1>
                    <p>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
                    <small class="session-info">
                        Last login: <?php echo date('M j, Y g:i A', $_SESSION['login_time']); ?>
                    </small>
                </div>
                <div class="header-right">
                    <div class="admin-user">
                        <i class="fas fa-user-shield"></i>
                        <span><?php echo htmlspecialchars($_SESSION['user_role']); ?></span>
                    </div>
                    <button class="mobile-menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </header>
            
            <div class="dashboard-content">
                <!-- Quick Stats -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon exhibitions">
                            <i class="fas fa-image"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Exhibitions</h3>
                            <p class="stat-number"><?php echo $stats['exhibitions'] ?? 0; ?></p>
                            <span class="stat-change">Total active</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon events">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Events</h3>
                            <p class="stat-number"><?php echo $stats['events'] ?? 0; ?></p>
                            <span class="stat-change">Total scheduled</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon collections">
                            <i class="fas fa-palette"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Collections</h3>
                            <p class="stat-number"><?php echo $stats['collections'] ?? 0; ?></p>
                            <span class="stat-change">Total items</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon users">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Users</h3>
                            <p class="stat-number"><?php echo $stats['users'] ?? 0; ?></p>
                            <span class="stat-change positive">+<?php echo $stats['recent_users'] ?? 0; ?> this week</span>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="quick-actions">
                    <h2>Quick Actions</h2>
                    <div class="actions-grid">
                        <a href="exhibitions.php?action=add" class="action-card">
                            <i class="fas fa-plus"></i>
                            <span>Add Exhibition</span>
                        </a>
                        <a href="events.php?action=add" class="action-card">
                            <i class="fas fa-calendar-plus"></i>
                            <span>Add Event</span>
                        </a>
                        <a href="collections.php?action=add" class="action-card">
                            <i class="fas fa-palette"></i>
                            <span>Add Collection</span>
                        </a>
                        <a href="users.php" class="action-card">
                            <i class="fas fa-user-plus"></i>
                            <span>Manage Users</span>
                        </a>
                    </div>
                </div>
                
                <!-- Charts Section -->
                <div class="dashboard-charts">
                    <div class="chart-container">
                        <h2>Exhibition Categories</h2>
                        <canvas id="exhibitionChart"></canvas>
                    </div>
                    
                    <div class="chart-container">
                        <h2>Monthly Visitors</h2>
                        <canvas id="visitorsChart"></canvas>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="recent-activity">
                    <h2>Recent Activity</h2>
                    
                    <div class="activity-tabs">
                        <button class="tab-btn active" data-tab="exhibitions">Exhibitions</button>
                        <button class="tab-btn" data-tab="events">Events</button>
                        <button class="tab-btn" data-tab="users">Users</button>
                    </div>
                    
                    <div class="activity-content">
                        <div class="tab-panel active" id="exhibitions-tab">
                            <div class="activity-list">
                                <?php foreach ($recent_exhibitions as $exhibition): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon">
                                            <i class="fas fa-image"></i>
                                        </div>
                                        <div class="activity-details">
                                            <h4><?php echo htmlspecialchars($exhibition['title']); ?></h4>
                                            <p>Created <?php echo formatDate($exhibition['created_at']); ?></p>
                                        </div>
                                        <div class="activity-actions">
                                            <a href="exhibitions.php?action=edit&id=<?php echo $exhibition['id']; ?>" class="btn-sm">Edit</a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="tab-panel" id="events-tab">
                            <div class="activity-list">
                                <?php foreach ($recent_events as $event): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon">
                                            <i class="fas fa-calendar"></i>
                                        </div>
                                        <div class="activity-details">
                                            <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                                            <p>Created <?php echo formatDate($event['created_at']); ?></p>
                                        </div>
                                        <div class="activity-actions">
                                            <a href="events.php?action=edit&id=<?php echo $event['id']; ?>" class="btn-sm">Edit</a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="tab-panel" id="users-tab">
                            <div class="activity-list">
                                <?php foreach ($recent_users as $user): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="activity-details">
                                            <h4><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h4>
                                            <p>Registered <?php echo formatDate($user['created_at']); ?></p>
                                        </div>
                                        <div class="activity-actions">
                                            <a href="users.php?action=edit&id=<?php echo $user['id']; ?>" class="btn-sm">View</a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="js/admin-dashboard.js"></script>
    <script>
        // Initialize charts
        document.addEventListener('DOMContentLoaded', function() {
            // Exhibition Categories Chart
            fetch('api/exhibition-categories.php')
                .then(response => response.json())
                .then(data => {
                    const ctx = document.getElementById('exhibitionChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: data.labels || ['Contemporary', 'Classical', 'Modern', 'Abstract'],
                            datasets: [{
                                data: data.data || [25, 30, 20, 25],
                                backgroundColor: [
                                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'
                                ]
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }
                    });
                })
                .catch(error => {
                    console.error('Error loading exhibition chart:', error);
                });
                
            // Visitors Chart
            const visitorsCtx = document.getElementById('visitorsChart').getContext('2d');
            new Chart(visitorsCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Visitors',
                        data: [1200, 1900, 3000, 2500, 2200, 3000],
                        borderColor: '#36A2EB',
                        backgroundColor: 'rgba(54, 162, 235, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>
</body>
<?php include 'includes/admin-footer.php'; ?>
</html>
