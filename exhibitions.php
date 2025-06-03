<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

$page_title = "Exhibitions - National Museum of Art & Culture";

// Get exhibitions by status
$current_exhibitions = getExhibitions('current');
$upcoming_exhibitions = getExhibitions('upcoming');
$past_exhibitions = getExhibitions('past');

// Get all categories for filter
try {
    $stmt = $pdo->prepare("SELECT DISTINCT category FROM exhibitions WHERE category IS NOT NULL AND category != ''");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch(PDOException $e) {
    $categories = [];
}

include 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Exhibitions</h1>
        <p>Discover our current, upcoming, and past exhibitions</p>
    </div>
</section>

<section class="exhibition-filters">
    <div class="container">
        <div class="filter-controls">
            <div class="filter-tabs">
                <button class="filter-tab active" data-filter="current">Current</button>
                <button class="filter-tab" data-filter="upcoming">Upcoming</button>
                <button class="filter-tab" data-filter="past">Past</button>
            </div>
            <div class="filter-dropdown">
                <select id="category-filter" aria-label="Filter by category">
                    <option value="all">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
</section>

<section class="exhibitions-list">
    <div class="container">
        <div class="exhibitions-grid" id="current-exhibitions">
            <?php if (!empty($current_exhibitions)): ?>
                <?php foreach ($current_exhibitions as $exhibition): ?>
                    <div class="exhibition-card" data-category="<?php echo htmlspecialchars($exhibition['category']); ?>">
                        <div class="exhibition-image">
                            <img src="<?php echo getImageUrl($exhibition['image'], 'exhibition', $exhibition['category']); ?>" 
                                 alt="<?php echo htmlspecialchars($exhibition['title']); ?>"
                                 onerror="this.src='<?php echo getFallbackImage('exhibition', $exhibition['category']); ?>'">
                            <div class="exhibition-date">Until <?php echo formatDate($exhibition['end_date']); ?></div>
                        </div>
                        <div class="exhibition-details">
                            <h3><?php echo htmlspecialchars($exhibition['title']); ?></h3>
                            <p><?php echo htmlspecialchars(substr($exhibition['description'], 0, 150)); ?>...</p>
                            <div class="tags">
                                <span><?php echo htmlspecialchars($exhibition['category']); ?></span>
                            </div>
                            <a href="exhibition-detail.php?id=<?php echo $exhibition['id']; ?>" class="btn btn-secondary">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="exhibition-card" data-category="modern art">
                    <div class="exhibition-image">
                        <img src="<?php echo getFallbackImage('exhibition', 'modern art'); ?>" alt="Modern Masterpieces exhibition">
                        <div class="exhibition-date">Until August 15, 2024</div>
                    </div>
                    <div class="exhibition-details">
                        <h3>Modern Masterpieces: A Century of Innovation</h3>
                        <p>Explore the evolution of modern art through a curated selection of masterpieces spanning the 20th century.</p>
                        <div class="tags">
                            <span>Modern Art</span>
                        </div>
                        <a href="#" class="btn btn-secondary">View Details</a>
                    </div>
                </div>
                <div class="exhibition-card" data-category="renaissance">
                    <div class="exhibition-image">
                        <img src="<?php echo getFallbackImage('exhibition', 'renaissance'); ?>" alt="Renaissance Reimagined exhibition">
                        <div class="exhibition-date">Until July 30, 2024</div>
                    </div>
                    <div class="exhibition-details">
                        <h3>Renaissance Reimagined</h3>
                        <p>A fresh look at Renaissance art and its influence on contemporary artistic expression.</p>
                        <div class="tags">
                            <span>Renaissance</span>
                        </div>
                        <a href="#" class="btn btn-secondary">View Details</a>
                    </div>
                </div>
                <div class="exhibition-card" data-category="digital art">
                    <div class="exhibition-image">
                        <img src="<?php echo getFallbackImage('exhibition', 'digital art'); ?>" alt="Digital Frontiers exhibition">
                        <div class="exhibition-date">Until September 5, 2024</div>
                    </div>
                    <div class="exhibition-details">
                        <h3>Digital Frontiers</h3>
                        <p>Exploring the intersection of art and technology in the digital age.</p>
                        <div class="tags">
                            <span>Digital Art</span>
                        </div>
                        <a href="#" class="btn btn-secondary">View Details</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="exhibitions-grid hidden" id="upcoming-exhibitions">
            <?php if (!empty($upcoming_exhibitions)): ?>
                <?php foreach ($upcoming_exhibitions as $exhibition): ?>
                    <div class="exhibition-card" data-category="<?php echo htmlspecialchars($exhibition['category']); ?>">
                        <div class="exhibition-image">
                            <img src="<?php echo getImageUrl($exhibition['image'], 'exhibition', $exhibition['category']); ?>" 
                                 alt="<?php echo htmlspecialchars($exhibition['title']); ?>"
                                 onerror="this.src='<?php echo getFallbackImage('exhibition', $exhibition['category']); ?>'">
                            <div class="exhibition-date"><?php echo formatDate($exhibition['start_date']); ?> - <?php echo formatDate($exhibition['end_date']); ?></div>
                        </div>
                        <div class="exhibition-details">
                            <h3><?php echo htmlspecialchars($exhibition['title']); ?></h3>
                            <p><?php echo htmlspecialchars(substr($exhibition['description'], 0, 150)); ?>...</p>
                            <div class="tags">
                                <span><?php echo htmlspecialchars($exhibition['category']); ?></span>
                            </div>
                            <a href="exhibition-detail.php?id=<?php echo $exhibition['id']; ?>" class="btn btn-secondary">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-content">
                    <p>No upcoming exhibitions at this time. Check back soon!</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="exhibitions-grid hidden" id="past-exhibitions">
            <?php if (!empty($past_exhibitions)): ?>
                <?php foreach ($past_exhibitions as $exhibition): ?>
                    <div class="exhibition-card" data-category="<?php echo htmlspecialchars($exhibition['category']); ?>">
                        <div class="exhibition-image">
                            <img src="<?php echo getImageUrl($exhibition['image'], 'exhibition', $exhibition['category']); ?>" 
                                 alt="<?php echo htmlspecialchars($exhibition['title']); ?>"
                                 onerror="this.src='<?php echo getFallbackImage('exhibition', $exhibition['category']); ?>'">
                            <div class="exhibition-date"><?php echo formatDate($exhibition['start_date']); ?> - <?php echo formatDate($exhibition['end_date']); ?></div>
                        </div>
                        <div class="exhibition-details">
                            <h3><?php echo htmlspecialchars($exhibition['title']); ?></h3>
                            <p><?php echo htmlspecialchars(substr($exhibition['description'], 0, 150)); ?>...</p>
                            <div class="tags">
                                <span><?php echo htmlspecialchars($exhibition['category']); ?></span>
                            </div>
                            <a href="exhibition-detail.php?id=<?php echo $exhibition['id']; ?>" class="btn btn-secondary">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-content">
                    <p>No past exhibitions to display.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php 
$page_scripts = ['js/exhibitions.js'];
include 'includes/footer.php'; 
?>
