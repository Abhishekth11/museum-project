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
            <li><a href="index.php" <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'class="active"' : ''; ?>><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li class="nav-section">Content Management</li>
            <?php if (hasPermission('manage_exhibitions')): ?>
            <li><a href="exhibitions.php" <?php echo basename($_SERVER['PHP_SELF']) == 'exhibitions.php' ? 'class="active"' : ''; ?>><i class="fas fa-image"></i> Exhibitions</a></li>
            <?php endif; ?>
            <?php if (hasPermission('manage_events')): ?>
            <li><a href="events.php" <?php echo basename($_SERVER['PHP_SELF']) == 'events.php' ? 'class="active"' : ''; ?>><i class="fas fa-calendar"></i> Events</a></li>
            <?php endif; ?>
            <?php if (hasPermission('manage_collections')): ?>
            <li><a href="collections.php" <?php echo basename($_SERVER['PHP_SELF']) == 'collections.php' ? 'class="active"' : ''; ?>><i class="fas fa-palette"></i> Collections</a></li>
            <!-- <li><a href="virtual-tours.php" <?php echo basename($_SERVER['PHP_SELF']) == 'virtual-tours.php' ? 'class="active"' : ''; ?>><i class="fas fa-vr-cardboard"></i> Virtual Tours</a></li> -->
            <?php endif; ?>
            <?php if (hasPermission('manage_users')): ?>
            <li class="nav-section">User Management</li>
            <li><a href="users.php" <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'class="active"' : ''; ?>><i class="fas fa-users"></i> Users</a></li>
            <!-- <li><a href="subscriptions.php" <?php echo basename($_SERVER['PHP_SELF']) == 'subscriptions.php' ? 'class="active"' : ''; ?>><i class="fas fa-envelope"></i> Subscriptions</a></li> -->
            <?php endif; ?>
            <!-- <?php if (hasPermission('view_analytics')): ?>
            <li class="nav-section">Analytics</li>
            <li><a href="analytics.php" <?php echo basename($_SERVER['PHP_SELF']) == 'analytics.php' ? 'class="active"' : ''; ?>><i class="fas fa-chart-bar"></i> Analytics</a></li>
            <li><a href="search-logs.php" <?php echo basename($_SERVER['PHP_SELF']) == 'search-logs.php' ? 'class="active"' : ''; ?>><i class="fas fa-search"></i> Search Logs</a></li>
            <?php endif; ?> -->
            <!-- <?php if (hasPermission('manage_settings')): ?>
            <li class="nav-section">Settings</li>
            <li><a href="settings.php" <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'class="active"' : ''; ?>><i class="fas fa-cog"></i> Settings</a></li>
            <?php endif; ?>
            <?php if (hasPermission('backup_database')): ?>
            <li><a href="backup.php" <?php echo basename($_SERVER['PHP_SELF']) == 'backup.php' ? 'class="active"' : ''; ?>><i class="fas fa-database"></i> Backup</a></li>
            <?php endif; ?> -->
            <li class="nav-divider"></li>
            <li><a href="../index.php"><i class="fas fa-globe"></i> View Website</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>
</aside>
