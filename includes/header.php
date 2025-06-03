<?php
// Get current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);

// Enhanced theme detection with better fallbacks
function getThemePreference() {
    // Ensure session is started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Priority: Cookie > Session > Default (light)
    if (isset($_COOKIE['theme']) && in_array($_COOKIE['theme'], ['light', 'dark'])) {
        return $_COOKIE['theme'];
    }
    
    if (isset($_SESSION['theme']) && in_array($_SESSION['theme'], ['light', 'dark'])) {
        return $_SESSION['theme'];
    }
    
    // Default to light theme for consistent behavior
    return 'light';
}

// Helper function to get the base URL for assets and links
function getBaseUrl() {
    // Check if we're in a subdirectory
    $currentDir = dirname($_SERVER['PHP_SELF']);
    
    // If we're in admin directory, go up one level
    if (strpos($currentDir, '/admin') !== false) {
        return '../';
    }
    
    return '';
}

// Fallback function definitions
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['user_id']) && isset($_SESSION['login_time']);
    }
}

if (!function_exists('isAdmin')) {
    function isAdmin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
}

$theme = getThemePreference();
$themeClass = $theme . '-theme';
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $theme; ?>" data-default-theme="<?php echo $theme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'National Museum of Art & Culture'; ?></title>
    
    <!-- Base styles (light theme) - Always loaded -->
    <link rel="stylesheet" href="<?php echo getBaseUrl(); ?>css/style.css">
    
    <!-- Alternative styles (dark theme) - Conditionally loaded -->
    <?php if ($theme === 'dark'): ?>
        <link rel="stylesheet" href="<?php echo getBaseUrl(); ?>css/alternative-style.css" id="alternative-style-css">
    <?php endif; ?>
    
    <!-- Preload alternative CSS for faster switching -->
    <link rel="preload" href="<?php echo getBaseUrl(); ?>css/alternative-style.css" as="style">
    
    <!-- External dependencies -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Raleway:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Theme meta tags -->
    <meta name="theme-color" content="<?php echo $theme === 'dark' ? '#121212' : '#ffffff'; ?>">
    <meta name="color-scheme" content="<?php echo $theme === 'dark' ? 'dark' : 'light'; ?>">
    
    <!-- Prevent flash of unstyled content -->
    <script>
        // Immediate theme class application
        (function() {
            try {
                const savedTheme = localStorage.getItem('museum-theme-preference') || '<?php echo $theme; ?>';
                const themeClass = savedTheme + '-theme';
                document.documentElement.className = themeClass;
                document.documentElement.setAttribute('data-theme', savedTheme);
            } catch (e) {
                // Fallback to PHP-provided theme if localStorage fails
                document.documentElement.className = '<?php echo $themeClass; ?>';
                document.documentElement.setAttribute('data-theme', '<?php echo $theme; ?>');
            }
        })();
    </script>
</head>
<body class="<?php echo $themeClass; ?>">
    <header class="site-header">
        <div class="container">
            <div class="header-inner">
                <div class="logo">
                    <a href="<?php echo getBaseUrl(); ?>index.php">
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
                        <li><a href="<?php echo getBaseUrl(); ?>index.php" <?php echo $current_page == 'index.php' ? 'class="active"' : ''; ?>>Home</a></li>
                        <li><a href="<?php echo getBaseUrl(); ?>exhibitions.php" <?php echo $current_page == 'exhibitions.php' ? 'class="active"' : ''; ?>>Exhibitions</a></li>
                        <li><a href="<?php echo getBaseUrl(); ?>events.php" <?php echo $current_page == 'events.php' ? 'class="active"' : ''; ?>>Events</a></li>
                        <li><a href="<?php echo getBaseUrl(); ?>collections.php" <?php echo $current_page == 'collections.php' ? 'class="active"' : ''; ?>>Collections</a></li>
                        <li><a href="<?php echo getBaseUrl(); ?>virtual-tours.php" <?php echo $current_page == 'virtual-tours.php' ? 'class="active"' : ''; ?>>Virtual Tours</a></li>
                        <li><a href="<?php echo getBaseUrl(); ?>visit.php" <?php echo $current_page == 'visit.php' ? 'class="active"' : ''; ?>>Visit</a></li>
                        <li><a href="<?php echo getBaseUrl(); ?>membership.php" <?php echo $current_page == 'membership.php' ? 'class="active"' : ''; ?>>Membership</a></li>
                        <li><a href="<?php echo getBaseUrl(); ?>about.php" <?php echo $current_page == 'about.php' ? 'class="active"' : ''; ?>>About</a></li>
                        <li><a href="<?php echo getBaseUrl(); ?>contact.php" <?php echo $current_page == 'contact.php' ? 'class="active"' : ''; ?>>Contact</a></li>
                    </ul>
                </nav>
                <div class="header-actions">
                    <button class="search-toggle" aria-label="Search" id="search-toggle-btn">
                        <i class="fas fa-search"></i>
                    </button>
                    
                    <!-- Enhanced Theme Toggle Button -->
                    <button class="theme-toggle" 
                            aria-label="<?php echo $theme === 'dark' ? 'Switch to light theme' : 'Switch to dark theme'; ?>" 
                            title="Switch theme (Ctrl+Shift+T)">
                        <i class="fas fa-sun" style="<?php echo $theme === 'dark' ? 'display: inline-block;' : 'display: none;'; ?>"></i>
                        <i class="fas fa-moon" style="<?php echo $theme === 'light' ? 'display: inline-block;' : 'display: none;'; ?>"></i>
                    </button>
                    
                    <?php if (isLoggedIn()): ?>
                        <div class="user-menu">
                            <span>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                            <?php if (isAdmin()): ?>
                                <a href="<?php echo getBaseUrl(); ?>admin/dashboard.php" class="btn btn-secondary">Admin</a>
                            <?php endif; ?>
                            <a href="<?php echo getBaseUrl(); ?>logout.php" class="btn btn-primary">Logout</a>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo getBaseUrl(); ?>login.php" class="btn btn-primary">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <div class="search-overlay" id="search-overlay">
        <div class="container">
            <form class="search-form" action="<?php echo getBaseUrl(); ?>search.php" method="GET">
                <input type="text" name="q" placeholder="Search for exhibitions, events, artists..." aria-label="Search" required>
                <button type="submit" aria-label="Submit search">
                    <i class="fas fa-search"></i>
                </button>
            </form>
            <button class="search-close" aria-label="Close search" id="search-close-btn">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <!-- Load simple theme switcher -->
    <script src="<?php echo getBaseUrl(); ?>js/simple-theme-switcher.js"></script>
    
    <!-- Ensure search functionality works -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Search functionality
        const searchToggleBtn = document.getElementById('search-toggle-btn');
        const searchCloseBtn = document.getElementById('search-close-btn');
        const searchOverlay = document.getElementById('search-overlay');
        
        if (searchToggleBtn && searchOverlay && searchCloseBtn) {
            // Open search overlay
            searchToggleBtn.addEventListener('click', function(e) {
                e.preventDefault();
                searchOverlay.classList.add('active');
                setTimeout(() => {
                    const searchInput = searchOverlay.querySelector('input[name="q"]');
                    if (searchInput) searchInput.focus();
                }, 100);
            });
            
            // Close search overlay
            searchCloseBtn.addEventListener('click', function(e) {
                e.preventDefault();
                searchOverlay.classList.remove('active');
            });
            
            // Close search with Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && searchOverlay.classList.contains('active')) {
                    searchOverlay.classList.remove('active');
                }
            });
        } else {
            console.error('Search elements not found');
        }
    });
    </script>
</body>
</html>
