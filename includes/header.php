<?php
// Get current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);

// Theme handling
$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light-theme';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'National Museum of Art & Culture'; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Raleway:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="<?php echo $theme; ?>">
    <header class="site-header">
        <div class="container">
            <div class="header-inner">
                <div class="logo">
                    <a href="index.php">
                        <h1>NMAC</h1>
                    </a>
                </div>
                <nav class="main-nav">
                    <button class="menu-toggle" aria-label="Toggle menu">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                    <ul class="nav-menu">
                        <li><a href="index.php" <?php echo $current_page == 'index.php' ? 'class="active"' : ''; ?>>Home</a></li>
                        <li><a href="exhibitions.php" <?php echo $current_page == 'exhibitions.php' ? 'class="active"' : ''; ?>>Exhibitions</a></li>
                        <li><a href="events.php" <?php echo $current_page == 'events.php' ? 'class="active"' : ''; ?>>Events</a></li>
                        <li><a href="collections.php" <?php echo $current_page == 'collections.php' ? 'class="active"' : ''; ?>>Collections</a></li>
                        <li><a href="virtual-tours.php" <?php echo $current_page == 'virtual-tours.php' ? 'class="active"' : ''; ?>>Virtual Tours</a></li>
                        <li><a href="visit.php" <?php echo $current_page == 'visit.php' ? 'class="active"' : ''; ?>>Visit</a></li>
                        <li><a href="about.php" <?php echo $current_page == 'about.php' ? 'class="active"' : ''; ?>>About</a></li>
                        <li><a href="contact.php" <?php echo $current_page == 'contact.php' ? 'class="active"' : ''; ?>>Contact</a></li>
                    </ul>
                </nav>
                <div class="header-actions">
                    <button class="search-toggle" aria-label="Search">
                        <i class="fas fa-search"></i>
                    </button>
                    <button class="theme-toggle" aria-label="Toggle theme">
                        <i class="fas fa-sun"></i>
                        <i class="fas fa-moon"></i>
                    </button>
                    <?php if (isLoggedIn()): ?>
                        <div class="user-menu">
                            <span>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                            <?php if (isAdmin()): ?>
                                <a href="admin/dashboard.php" class="btn btn-secondary">Admin</a>
                            <?php endif; ?>
                            <a href="logout.php" class="btn btn-primary">Logout</a>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <div class="search-overlay">
        <div class="container">
            <form class="search-form" action="search.php" method="GET">
                <input type="text" name="q" placeholder="Search for exhibitions, events, artists..." aria-label="Search" required>
                <button type="submit" aria-label="Submit search">
                    <i class="fas fa-search"></i>
                </button>
            </form>
            <button class="search-close" aria-label="Close search">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
