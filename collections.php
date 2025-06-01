<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

$page_title = "Collections - National Museum of Art & Culture";

// Get all collections
$collections = getCollections();

// Get categories for filter
try {
    $stmt = $pdo->prepare("SELECT DISTINCT category FROM collections WHERE category IS NOT NULL AND category != ''");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch(PDOException $e) {
    $categories = [];
}

include 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Collections</h1>
        <p>Explore our permanent collection of artworks and artifacts</p>
    </div>
</section>

<section class="collections-filters">
    <div class="container">
        <div class="filter-controls">
            <select id="category-filter" aria-label="Filter by category">
                <option value="all">All Categories</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></option>
                <?php endforeach; ?>
            </select>
            <div class="view-toggle">
                <button class="view-btn active" data-view="grid" aria-label="Grid view">
                    <i class="fas fa-th"></i>
                </button>
                <button class="view-btn" data-view="list" aria-label="List view">
                    <i class="fas fa-list"></i>
                </button>
            </div>
        </div>
    </div>
</section>

<section class="collections-list">
    <div class="container">
        <div class="collections-grid" id="collections-container">
            <?php if (!empty($collections)): ?>
                <?php foreach ($collections as $collection): ?>
                    <div class="collection-card" data-category="<?php echo htmlspecialchars($collection['category']); ?>">
                        <div class="collection-image">
                            <img src="<?php echo !empty($collection['image']) ? 'uploads/collections/' . $collection['image'] : 'https://source.unsplash.com/random/400x500/?art'; ?>" 
                                 alt="<?php echo htmlspecialchars($collection['title']); ?>">
                        </div>
                        <div class="collection-details">
                            <h3><?php echo htmlspecialchars($collection['title']); ?></h3>
                            <?php if (!empty($collection['artist'])): ?>
                                <p class="artist"><?php echo htmlspecialchars($collection['artist']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($collection['year'])): ?>
                                <p class="year"><?php echo htmlspecialchars($collection['year']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($collection['medium'])): ?>
                                <p class="medium"><?php echo htmlspecialchars($collection['medium']); ?></p>
                            <?php endif; ?>
                            <div class="tags">
                                <span><?php echo htmlspecialchars($collection['category']); ?></span>
                            </div>
                            <a href="collection-detail.php?id=<?php echo $collection['id']; ?>" class="btn btn-secondary">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Sample collections for demonstration -->
                <div class="collection-card" data-category="painting">
                    <div class="collection-image">
                        <img src="https://source.unsplash.com/random/400x500/?painting" alt="Renaissance Painting">
                    </div>
                    <div class="collection-details">
                        <h3>Renaissance Masterpiece</h3>
                        <p class="artist">Leonardo da Vinci</p>
                        <p class="year">1503-1519</p>
                        <p class="medium">Oil on poplar panel</p>
                        <div class="tags">
                            <span>Painting</span>
                        </div>
                        <a href="collection-detail.php?id=1" class="btn btn-secondary">View Details</a>
                    </div>
                </div>
                <!-- Add more sample collections as needed -->
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
// Collection filtering and view toggle
document.addEventListener('DOMContentLoaded', function() {
    const categoryFilter = document.getElementById('category-filter');
    const viewButtons = document.querySelectorAll('.view-btn');
    const container = document.getElementById('collections-container');
    
    // Category filtering
    categoryFilter.addEventListener('change', function() {
        const selectedCategory = this.value;
        const cards = container.querySelectorAll('.collection-card');
        
        cards.forEach(card => {
            if (selectedCategory === 'all' || card.dataset.category === selectedCategory) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });
    
    // View toggle
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            viewButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            if (this.dataset.view === 'list') {
                container.classList.add('list-view');
            } else {
                container.classList.remove('list-view');
            }
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
