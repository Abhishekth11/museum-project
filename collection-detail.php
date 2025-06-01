<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

$collection_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$collection = null;

if ($collection_id > 0) {
    $collection = getCollectionById($collection_id);
}

if (!$collection) {
    // Redirect to collections page if not found
    header('Location: collections.php');
    exit;
}

$page_title = htmlspecialchars($collection['title']) . " - National Museum of Art & Culture";

include 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <nav class="breadcrumb">
            <a href="index.php">Home</a> > 
            <a href="collections.php">Collections</a> > 
            <span><?php echo htmlspecialchars($collection['title']); ?></span>
        </nav>
    </div>
</section>

<section class="collection-detail">
    <div class="container">
        <div class="detail-grid">
            <div class="artwork-image">
                <img src="<?php echo !empty($collection['image']) ? 'uploads/collections/' . $collection['image'] : 'images/collections/default.jpg'; ?>" 
                     alt="<?php echo htmlspecialchars($collection['title']); ?>">
            </div>
            
            <div class="artwork-info">
                <h1><?php echo htmlspecialchars($collection['title']); ?></h1>
                
                <?php if (!empty($collection['artist'])): ?>
                    <p class="artist-name"><?php echo htmlspecialchars($collection['artist']); ?></p>
                <?php endif; ?>
                
                <div class="artwork-details">
                    <?php if (!empty($collection['year'])): ?>
                        <div class="detail-item">
                            <strong>Year:</strong> <?php echo htmlspecialchars($collection['year']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($collection['medium'])): ?>
                        <div class="detail-item">
                            <strong>Medium:</strong> <?php echo htmlspecialchars($collection['medium']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($collection['category'])): ?>
                        <div class="detail-item">
                            <strong>Category:</strong> <?php echo htmlspecialchars($collection['category']); ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($collection['description'])): ?>
                    <div class="artwork-description">
                        <h3>About This Artwork</h3>
                        <p><?php echo nl2br(htmlspecialchars($collection['description'])); ?></p>
                    </div>
                <?php endif; ?>
                
                <div class="artwork-actions">
                    <button class="btn btn-secondary" onclick="window.print()">
                        <i class="fas fa-print"></i> Print Details
                    </button>
                    <button class="btn btn-secondary" onclick="shareArtwork()">
                        <i class="fas fa-share"></i> Share
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="related-collections">
    <div class="container">
        <h2>Related Artworks</h2>
        <div class="related-grid">
            <?php
            // Get related collections from same category
            $related = getCollections($collection['category'], 4);
            foreach ($related as $related_item):
                if ($related_item['id'] != $collection['id']):
            ?>
                <div class="related-item">
                    <a href="collection-detail.php?id=<?php echo $related_item['id']; ?>">
                        <img src="<?php echo !empty($related_item['image']) ? 'uploads/collections/' . $related_item['image'] : 'images/collections/default.jpg'; ?>" 
                             alt="<?php echo htmlspecialchars($related_item['title']); ?>">
                        <h4><?php echo htmlspecialchars($related_item['title']); ?></h4>
                        <?php if (!empty($related_item['artist'])): ?>
                            <p><?php echo htmlspecialchars($related_item['artist']); ?></p>
                        <?php endif; ?>
                    </a>
                </div>
            <?php 
                endif;
            endforeach; 
            ?>
        </div>
    </div>
</section>

<style>
.breadcrumb {
    font-size: 1.4rem;
    color: var(--text-secondary);
    margin-bottom: 2rem;
}

.breadcrumb a {
    color: var(--primary);
    text-decoration: none;
}

.detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6rem;
    align-items: start;
}

.artwork-image img {
    width: 100%;
    border-radius: var(--border-radius-lg);
    box-shadow: 0 10px 30px var(--shadow);
}

.artist-name {
    font-size: 2rem;
    color: var(--primary);
    font-weight: 500;
    margin-bottom: 2rem;
}

.artwork-details {
    background: var(--background-alt);
    padding: 2rem;
    border-radius: var(--border-radius);
    margin-bottom: 3rem;
}

.detail-item {
    padding: 1rem 0;
    border-bottom: 1px solid var(--divider);
}

.detail-item:last-child {
    border-bottom: none;
}

.artwork-description {
    margin-bottom: 3rem;
}

.artwork-actions {
    display: flex;
    gap: 1rem;
}

.related-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(20rem, 1fr));
    gap: 2rem;
    margin-top: 3rem;
}

.related-item {
    text-align: center;
}

.related-item img {
    width: 100%;
    height: 20rem;
    object-fit: cover;
    border-radius: var(--border-radius);
    margin-bottom: 1rem;
}

.related-item h4 {
    margin-bottom: 0.5rem;
}

.related-item p {
    color: var(--text-secondary);
    font-size: 1.4rem;
}

@media (max-width: 768px) {
    .detail-grid {
        grid-template-columns: 1fr;
        gap: 3rem;
    }
    
    .artwork-actions {
        flex-direction: column;
    }
}
</style>

<script>
function shareArtwork() {
    if (navigator.share) {
        navigator.share({
            title: '<?php echo htmlspecialchars($collection['title']); ?>',
            text: 'Check out this artwork at the National Museum of Art & Culture',
            url: window.location.href
        });
    } else {
        // Fallback for browsers that don't support Web Share API
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert('Link copied to clipboard!');
        });
    }
}
</script>

<?php include 'includes/footer.php'; ?>
