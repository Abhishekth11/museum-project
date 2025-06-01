<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit;
}

$page_title = "Admin Dashboard - National Museum of Art & Culture";

// Get counts for dashboard
try {
    $exhibition_count = $pdo->query("SELECT COUNT(*) FROM exhibitions")->fetchColumn();
    $event_count = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
    $user_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $subscription_count = $pdo->query("SELECT COUNT(*) FROM subscriptions")->fetchColumn();
    
    // Get recent activities
    $recent_exhibitions = $pdo->query("SELECT * FROM exhibitions ORDER BY created_at DESC LIMIT 5")->fetchAll();
    $recent_events = $pdo->query("SELECT * FROM events ORDER BY created_at DESC LIMIT 5")->fetchAll();
} catch(PDOException $e) {
    $exhibition_count = $event_count = $user_count = $subscription_count = 0;
    $recent_exhibitions = $recent_events = [];
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="admin-page">
    <div class="admin-container">
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <h2>NMAC Admin</h2>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="exhibitions.php"><i class="fas fa-image"></i> Exhibitions</a></li>
                    <li><a href="events.php"><i class="fas fa-calendar"></i> Events</a></li>
                    <li><a href="collections.php"><i class="fas fa-palette"></i> Collections</a></li>
                    <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                    <li><a href="subscriptions.php"><i class="fas fa-envelope"></i> Subscriptions</a></li>
                    <li><a href="../index.php"><i class="fas fa-globe"></i> View Website</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <main class="admin-content">
            <header class="admin-header">
                <h1>Dashboard</h1>
                <div class="admin-user">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                </div>
            </header>
            
            <div class="dashboard-content">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-image"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Exhibitions</h3>
                            <p class="stat-number"><?php echo $exhibition_count; ?></p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Events</h3>
                            <p class="stat-number"><?php echo $event_count; ?></p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Users</h3>
                            <p class="stat-number"><?php echo $user_count; ?></p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Subscriptions</h3>
                            <p class="stat-number"><?php echo $subscription_count; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="dashboard-charts">
                    <div class="chart-container">
                        <h2>Exhibition Categories</h2>
                        <canvas id="exhibitionChart"></canvas>
                    </div>
                    
                    <div class="chart-container">
                        <h2>Upcoming Events</h2>
                        <canvas id="eventChart"></canvas>
                    </div>
                </div>
                
                <div class="recent-activity">
                    <h2>Recent Activity</h2>
                    
                    <div class="activity-grid">
                        <div class="activity-card">
                            <h3>Recent Exhibitions</h3>
                            <ul class="activity-list">
                                <?php foreach ($recent_exhibitions as $exhibition): ?>
                                    <li>
                                        <span class="activity-title"><?php echo htmlspecialchars($exhibition['title']); ?></span>
                                        <span class="activity-date"><?php echo formatDate($exhibition['created_at']); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                        <div class="activity-card">
                            <h3>Recent Events</h3>
                            <ul class="activity-list">
                                <?php foreach ($recent_events as $event): ?>
                                    <li>
                                        <span class="activity-title"><?php echo htmlspecialchars($event['title']); ?></span>
                                        <span class="activity-date"><?php echo formatDate($event['created_at']); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
    // Chart.js implementation for exhibition categories
    fetch('api/exhibition-categories.php')
        .then(response => response.json())
        .then(data => {
            const ctx = document.getElementById('exhibitionChart').getContext('2d');
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: data.labels,
                    datasets: [{
                        data: data.data,
                        backgroundColor: [
                            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'
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
        
    // Chart.js implementation for upcoming events
    fetch('api/upcoming-events.php')
        .then(response => response.json())
        .then(data => {
            const ctx = document.getElementById('eventChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Upcoming Events',
                        data: data.data,
                        backgroundColor: '#36A2EB'
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        })
        .catch(error => {
            console.error('Error loading event chart:', error);
        });
    </script>
</body>
</html>
