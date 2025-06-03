<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

$page_title = "Collections - National Museum of Art & Culture";

// Sample collections with images for demonstration
$sample_collections = [
    [
        'id' => 1,
        'title' => 'The Starry Night (Study)',
        'artist' => 'Vincent van Gogh',
        'year' => '1889',
        'medium' => 'Oil on canvas',
        'category' => 'painting',
        'image' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=400&h=500&fit=crop',
        'description' => 'A masterful study of van Gogh\'s famous work, showcasing his distinctive swirling brushstrokes and vibrant color palette.'
    ],
    [
        'id' => 2,
        'title' => 'Digital Infinity',
        'artist' => 'Alex Chen',
        'year' => '2024',
        'medium' => 'Digital installation',
        'category' => 'digital',
        'image' => 'https://images.unsplash.com/photo-1518709268805-4e9042af2176?w=400&h=500&fit=crop',
        'description' => 'An immersive digital art installation that explores the concept of infinity through interactive light patterns and sound.'
    ],
    [
        'id' => 3,
        'title' => 'David (Bronze Cast)',
        'artist' => 'Michelangelo',
        'year' => '1501-1504',
        'medium' => 'Bronze',
        'category' => 'sculpture',
        'image' => 'https://images.unsplash.com/photo-1594736797933-d0401ba2fe65?w=400&h=500&fit=crop',
        'description' => 'A bronze cast of Michelangelo\'s iconic Renaissance sculpture, representing the biblical hero David.'
    ],
    [
        'id' => 4,
        'title' => 'Water Lilies',
        'artist' => 'Claude Monet',
        'year' => '1919',
        'medium' => 'Oil on canvas',
        'category' => 'painting',
        'image' => 'https://images.unsplash.com/photo-1578321272176-b7bbc0679853?w=400&h=500&fit=crop',
        'description' => 'Part of Monet\'s famous series depicting his flower garden at Giverny, capturing the play of light on water.'
    ],
    [
        'id' => 5,
        'title' => 'Urban Rhythms',
        'artist' => 'Maya Patel',
        'year' => '2023',
        'medium' => 'Mixed media',
        'category' => 'contemporary',
        'image' => 'https://images.unsplash.com/photo-1541961017774-22349e4a1262?w=400&h=500&fit=crop',
        'description' => 'A contemporary piece exploring the rhythm and energy of modern city life through abstract forms and vibrant colors.'
    ],
    [
        'id' => 6,
        'title' => 'Ancient Vessel',
        'artist' => 'Unknown',
        'year' => '500 BCE',
        'medium' => 'Ceramic',
        'category' => 'ancient',
        'image' => 'https://images.unsplash.com/photo-1606761568499-6d2451b23c66?w=400&h=500&fit=crop',
        'description' => 'A beautifully preserved ceramic vessel from ancient civilization, showcasing intricate geometric patterns.'
    ]
];

// Get actual collections from database
$collections = getCollections();

// If no collections in database, use sample data
if (empty($collections)) {
    $collections = $sample_collections;
}

// Get categories for filter
$categories = array_unique(array_column($collections, 'category'));

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
                    <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars(ucfirst($category)); ?></option>
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
            <?php foreach ($collections as $collection): ?>
                <div class="collection-card" data-category="<?php echo htmlspecialchars($collection['category']); ?>">
                    <div class="collection-image">
                        <img src="<?php echo htmlspecialchars($collection['image']); ?>" 
                             alt="<?php echo htmlspecialchars($collection['title']); ?>"
                             onerror="this.src='<?php echo getFallbackImage('collection', $collection['category']); ?>'">
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
                            <span><?php echo htmlspecialchars(ucfirst($collection['category'])); ?></span>
                        </div>
                        <a href="collection-detail.php?id=<?php echo $collection['id']; ?>" class="btn btn-secondary">View Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
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
