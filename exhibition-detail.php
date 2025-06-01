<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

$exhibition_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$exhibition = null;

if ($exhibition_id > 0) {
    $exhibition = getExhibitionById($exhibition_id);
}

if (!$exhibition) {
    // Redirect to exhibitions page if not found
    header('Location: exhibitions.php');
    exit;
}

$page_title = htmlspecialchars($exhibition['title']) . " - National Museum of Art & Culture";

// Get related events for this exhibition
try {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE related_exhibition_id = ? AND event_date >= CURDATE() ORDER BY event_date ASC LIMIT 3");
    $stmt->execute([$exhibition_id]);
    $related_events = $stmt->fetchAll();
} catch(PDOException $e) {
    $related_events = [];
}

include 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <nav class="breadcrumb">
            <a href="index.php">Home</a> > 
            <a href="exhibitions.php">Exhibitions</a> > 
            <span><?php echo htmlspecialchars($exhibition['title']); ?></span>
        </nav>
    </div>
</section>

<section class="exhibition-detail">
    <div class="container">
        <div class="detail-grid">
            <div class="exhibition-image">
                <img src="<?php echo !empty($exhibition['image']) ? 'uploads/exhibitions/' . $exhibition['image'] : 'images/exhibitions/default.jpg'; ?>" 
                     alt="<?php echo htmlspecialchars($exhibition['title']); ?>">
            </div>
            
            <div class="exhibition-info">
                <h1><?php echo htmlspecialchars($exhibition['title']); ?></h1>
                
                <div class="exhibition-meta">
                    <div class="meta-item">
                        <i class="fas fa-calendar"></i>
                        <span><?php echo formatDate($exhibition['start_date']); ?> - <?php echo formatDate($exhibition['end_date']); ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?php echo htmlspecialchars($exhibition['location']); ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-tag"></i>
                        <span><?php echo htmlspecialchars($exhibition['category']); ?></span>
                    </div>
                    <div class="meta-item status-<?php echo $exhibition['status']; ?>">
                        <i class="fas fa-info-circle"></i>
                        <span><?php echo ucfirst($exhibition['status']); ?> Exhibition</span>
                    </div>
                </div>
                
                <div class="exhibition-description">
                    <h3>About This Exhibition</h3>
                    <p><?php echo nl2br(htmlspecialchars($exhibition['description'])); ?></p>
                </div>
                
                <div class="exhibition-actions">
                    <a href="visit.php" class="btn btn-primary">Plan Your Visit</a>
                    <button class="btn btn-secondary" onclick="shareExhibition()">
                        <i class="fas fa-share"></i> Share
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($related_events)): ?>
<section class="related-events">
    <div class="container">
        <h2>Related Events</h2>
        <div class="events-grid">
            <?php foreach ($related_events as $event): ?>
                <div class="event-card">
                    <div class="event-date">
                        <span class="month"><?php echo strtoupper(date('M', strtotime($event['event_date']))); ?></span>
                        <span class="day"><?php echo date('d', strtotime($event['event_date'])); ?></span>
                    </div>
                    <div class="event-details">
                        <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                        <p class="event-time"><i class="far fa-clock"></i> <?php echo date('g:i A', strtotime($event['start_time'])); ?></p>
                        <p class="event-location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></p>
                        <a href="event-detail.php?id=<?php echo $event['id']; ?>" class="btn btn-text">Learn More <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<style>
.detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6rem;
    align-items: start;
    margin-bottom: 6rem;
}

.exhibition-image img {
    width: 100%;
    border-radius: var(--border-radius-lg);
    box-shadow: 0 10px 30px var(--shadow);
}

.exhibition-meta {
    background: var(--background-alt);
    padding: 2rem;
    border-radius: var(--border-radius);
    margin-bottom: 3rem;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 0;
    border-bottom: 1px solid var(--divider);
}

.meta-item:last-child {
    border-bottom: none;
}

.meta-item i {
    color: var(--primary);
    width: 2rem;
}

.meta-item.status-current {
    color: var(--primary);
    font-weight: 600;
}

.meta-item.status-upcoming {
    color: var(--accent);
    font-weight: 600;
}

.meta-item.status-past {
    color: var(--text-tertiary);
}

.exhibition-description {
    margin-bottom: 3rem;
}

.exhibition-actions {
    display: flex;
    gap: 1rem;
}

@media (max-width: 768px) {
    .detail-grid {
        grid-template-columns: 1fr;
        gap: 3rem;
    }
    
    .exhibition-actions {
        flex-direction: column;
    }
}
</style>

<script>
function shareExhibition() {
    if (navigator.share) {
        navigator.share({
            title: '<?php echo htmlspecialchars($exhibition['title']); ?>',
            text: 'Check out this exhibition at the National Museum of Art & Culture',
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
