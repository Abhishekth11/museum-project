<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

$page_title = "National Museum of Art & Culture";

// Check for login success message
$welcome_message = '';
if (isset($_SESSION['login_success'])) {
    $welcome_message = $_SESSION['login_success'];
    unset($_SESSION['login_success']);
}

// Get featured exhibition
$featured_exhibition = getExhibitions('current', 1);
$featured_exhibition = !empty($featured_exhibition) ? $featured_exhibition[0] : null;

// Get upcoming events
$upcoming_events = getEvents(3, true);

// Get collection highlights
$collection_highlights = getCollections('all', 4);

include 'includes/header.php';
?>

<?php if ($welcome_message): ?>
<div class="welcome-banner">
    <div class="container">
        <div class="welcome-message">
            <i class="fas fa-check-circle"></i>
            <span><?php echo htmlspecialchars($welcome_message); ?></span>
            <button class="close-welcome" onclick="this.parentElement.parentElement.style.display='none'">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (isLoggedIn()): ?>
<div class="user-dashboard-preview">
    <div class="container">
        <div class="dashboard-preview-content">
            <h3>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h3>
            <?php if (hasPermission('manage_exhibitions')): ?>
                <div class="admin-quick-links">
                    <a href="admin/dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                    </a>
                    <a href="admin/exhibitions.php" class="btn btn-outline">
                        <i class="fas fa-image"></i> Manage Exhibitions
                    </a>
                </div>
            <?php else: ?>
                <div class="user-quick-links">
                    <a href="events.php" class="btn btn-secondary">
                        <i class="fas fa-calendar"></i> View Events
                    </a>
                    <a href="collections.php" class="btn btn-outline">
                        <i class="fas fa-palette"></i> Browse Collections
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<section class="hero">
    <div class="hero-content">
        <h1>Experience Art & Culture</h1>
        <p>Discover our world-class collections and exhibitions</p>
        <div class="hero-buttons">
            <a href="exhibitions.php" class="btn btn-lg btn-primary">Current Exhibitions</a>
            <a href="visit.php" class="btn btn-lg btn-outline">Plan Your Visit</a>
        </div>
    </div>
</section>

<section class="featured-exhibition">
    <div class="container">
        <div class="section-header">
            <h2>Featured Exhibition</h2>
            <a href="exhibitions.php" class="view-all">View All Exhibitions</a>
        </div>
        <?php if ($featured_exhibition): ?>
        <div class="featured-content">
            <div class="featured-image">
                <img src="<?php echo !empty($featured_exhibition['image']) ? 'uploads/exhibitions/' . $featured_exhibition['image'] : 'https://source.unsplash.com/random/800x600/?art'; ?>" alt="<?php echo htmlspecialchars($featured_exhibition['title']); ?>">
                <div class="exhibit-date">Until <?php echo formatDate($featured_exhibition['end_date']); ?></div>
            </div>
            <div class="featured-details">
                <h3><?php echo htmlspecialchars($featured_exhibition['title']); ?></h3>
                <p class="exhibition-info"><?php echo htmlspecialchars($featured_exhibition['location']); ?></p>
                <p><?php echo htmlspecialchars($featured_exhibition['description']); ?></p>
                <div class="tags">
                    <span><?php echo htmlspecialchars($featured_exhibition['category']); ?></span>
                </div>
                <a href="exhibition-detail.php?id=<?php echo $featured_exhibition['id']; ?>" class="btn btn-secondary">Learn More</a>
            </div>
        </div>
        <?php else: ?>
        <div class="featured-content">
            <div class="featured-image">
                <img src="https://source.unsplash.com/random/800x600/?art" alt="Modern Masterpieces Exhibition">
                <div class="exhibit-date">Until August 15, 2024</div>
            </div>
            <div class="featured-details">
                <h3>Modern Masterpieces: A Century of Innovation</h3>
                <p class="exhibition-info">Main Gallery • Floor 2</p>
                <p>Explore the evolution of modern art through a curated selection of masterpieces spanning the 20th century. This exhibition showcases the revolutionary techniques and bold visions that defined an era of artistic innovation.</p>
                <div class="tags">
                    <span>Modern Art</span>
                    <span>Painting</span>
                    <span>Sculpture</span>
                </div>
                <a href="exhibitions.php" class="btn btn-secondary">Learn More</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="upcoming-events">
    <div class="container">
        <div class="section-header">
            <h2>Upcoming Events</h2>
            <a href="events.php" class="view-all">View Calendar</a>
        </div>
        <div class="events-grid">
            <?php if (!empty($upcoming_events)): ?>
                <?php foreach ($upcoming_events as $event): ?>
                    <div class="event-card">
                        <div class="event-date">
                            <span class="month"><?php echo strtoupper(date('M', strtotime($event['event_date']))); ?></span>
                            <span class="day"><?php echo date('d', strtotime($event['event_date'])); ?></span>
                        </div>
                        <div class="event-details">
                            <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                            <p class="event-time"><i class="far fa-clock"></i> <?php echo date('g:i A', strtotime($event['start_time'])); ?> - <?php echo date('g:i A', strtotime($event['end_time'])); ?></p>
                            <p class="event-location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></p>
                            <a href="event-detail.php?id=<?php echo $event['id']; ?>" class="btn btn-text">Reserve Spot <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="event-card">
                    <div class="event-date">
                        <span class="month">JUN</span>
                        <span class="day">15</span>
                    </div>
                    <div class="event-details">
                        <h3>Guided Tour: Modern Masterpieces</h3>
                        <p class="event-time"><i class="far fa-clock"></i> 2:00 PM - 3:30 PM</p>
                        <p class="event-location"><i class="fas fa-map-marker-alt"></i> Main Gallery</p>
                        <a href="events.php" class="btn btn-text">Reserve Spot <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <div class="event-card">
                    <div class="event-date">
                        <span class="month">JUN</span>
                        <span class="day">18</span>
                    </div>
                    <div class="event-details">
                        <h3>Artist Talk: Contemporary Expressions</h3>
                        <p class="event-time"><i class="far fa-clock"></i> 6:30 PM - 8:00 PM</p>
                        <p class="event-location"><i class="fas fa-map-marker-alt"></i> Auditorium</p>
                        <a href="events.php" class="btn btn-text">Reserve Spot <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <div class="event-card">
                    <div class="event-date">
                        <span class="month">JUN</span>
                        <span class="day">22</span>
                    </div>
                    <div class="event-details">
                        <h3>Workshop: Introduction to Sculpture</h3>
                        <p class="event-time"><i class="far fa-clock"></i> 10:00 AM - 1:00 PM</p>
                        <p class="event-location"><i class="fas fa-map-marker-alt"></i> Workshop Space</p>
                        <a href="events.php" class="btn btn-text">Reserve Spot <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="collections-highlight">
    <div class="container">
        <div class="section-header">
            <h2>Collection Highlights</h2>
            <a href="collections.php" class="view-all">Explore Collection</a>
        </div>
        <div class="collection-slider-container">
            <div class="collection-slider" id="collection-slider">
                <?php if (!empty($collection_highlights)): ?>
                    <?php foreach ($collection_highlights as $index => $collection): ?>
                        <div class="collection-item <?php echo $index === 0 ? 'active' : ''; ?>">
                            <img src="<?php echo !empty($collection['image']) ? 'uploads/collections/' . $collection['image'] : 'https://source.unsplash.com/random/400x500/?painting'; ?>" alt="<?php echo htmlspecialchars($collection['title']); ?>">
                            <div class="collection-info">
                                <h3><?php echo htmlspecialchars($collection['title']); ?></h3>
                                <p><?php echo htmlspecialchars($collection['artist'] ?? 'Unknown Artist'); ?></p>
                                <p class="collection-year"><?php echo htmlspecialchars($collection['year'] ?? 'Date Unknown'); ?></p>
                                <a href="collection-detail.php?id=<?php echo $collection['id']; ?>" class="btn btn-text">View Details</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="collection-item active">
                        <img src="https://source.unsplash.com/random/400x500/?renaissance-painting" alt="Renaissance Masterpiece">
                        <div class="collection-info">
                            <h3>The Birth of Venus</h3>
                            <p>Sandro Botticelli</p>
                            <p class="collection-year">c. 1484-1486</p>
                            <a href="collections.php" class="btn btn-text">View Details</a>
                        </div>
                    </div>
                    <div class="collection-item">
                        <img src="https://source.unsplash.com/random/400x500/?modern-sculpture" alt="Modern Sculpture">
                        <div class="collection-info">
                            <h3>Abstract Form III</h3>
                            <p>Henry Moore</p>
                            <p class="collection-year">1953</p>
                            <a href="collections.php" class="btn btn-text">View Details</a>
                        </div>
                    </div>
                    <div class="collection-item">
                        <img src="https://source.unsplash.com/random/400x500/?photography-art" alt="Photography Collection">
                        <div class="collection-info">
                            <h3>Urban Landscapes</h3>
                            <p>Ansel Adams</p>
                            <p class="collection-year">1940-1960</p>
                            <a href="collections.php" class="btn btn-text">View Details</a>
                        </div>
                    </div>
                    <div class="collection-item">
                        <img src="https://source.unsplash.com/random/400x500/?ancient-artifacts" alt="Ancient Artifacts">
                        <div class="collection-info">
                            <h3>Greek Amphora</h3>
                            <p>Unknown Artist</p>
                            <p class="collection-year">5th Century BCE</p>
                            <a href="collections.php" class="btn btn-text">View Details</a>
                        </div>
                    </div>
                    <div class="collection-item">
                        <img src="https://source.unsplash.com/random/400x500/?impressionist-painting" alt="Impressionist Painting">
                        <div class="collection-info">
                            <h3>Water Lilies</h3>
                            <p>Claude Monet</p>
                            <p class="collection-year">1919</p>
                            <a href="collections.php" class="btn btn-text">View Details</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="collection-controls">
                <button class="prev-btn" aria-label="Previous item" onclick="moveSlider(-1)">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div class="slider-dots" id="slider-dots">
                    <!-- Dots will be generated by JavaScript -->
                </div>
                <button class="next-btn" aria-label="Next item" onclick="moveSlider(1)">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</section>

<section class="visit-info">
    <div class="container">
        <div class="visit-grid">
            <div class="visit-content">
                <h2>Plan Your Visit</h2>
                <div class="visit-details">
                    <div class="detail-item">
                        <h3><i class="far fa-clock"></i> Hours</h3>
                        <p>Tuesday - Sunday: 10:00 AM - 5:00 PM</p>
                        <p>Thursday: 10:00 AM - 8:00 PM</p>
                        <p>Closed Mondays</p>
                    </div>
                    <div class="detail-item">
                        <h3><i class="fas fa-ticket-alt"></i> Admission</h3>
                        <p>Adults: $15</p>
                        <p>Seniors & Students: $10</p>
                        <p>Children under 12: Free</p>
                    </div>
                    <div class="detail-item">
                        <h3><i class="fas fa-map-marker-alt"></i> Location</h3>
                        <p>123 Museum Street</p>
                        <p>City, State 12345</p>
                        <p><a href="https://maps.google.com" target="_blank">Get Directions</a></p>
                    </div>
                </div>
                <a href="visit.php" class="btn btn-primary">More Information</a>
            </div>
            <div class="visit-image">
                <img src="https://source.unsplash.com/random/600x800/?museum" alt="Museum building">
            </div>
        </div>
    </div>
</section>

<section class="membership">
    <div class="container">
        <div class="membership-content">
            <h2>Become a Member</h2>
            <p>Join our community and enjoy exclusive benefits, including free unlimited admission, special exhibition previews, discounts, and more.</p>
            <a href="membership.php" class="btn btn-primary">Join Today</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
// Collection Slider Functionality
let currentSlide = 0;
const slides = document.querySelectorAll('.collection-item');
const totalSlides = slides.length;

// Generate dots
function generateDots() {
    const dotsContainer = document.getElementById('slider-dots');
    dotsContainer.innerHTML = '';
    
    for (let i = 0; i < totalSlides; i++) {
        const dot = document.createElement('span');
        dot.className = `dot ${i === 0 ? 'active' : ''}`;
        dot.onclick = () => goToSlide(i);
        dotsContainer.appendChild(dot);
    }
}

// Move slider
function moveSlider(direction) {
    currentSlide += direction;
    
    if (currentSlide >= totalSlides) {
        currentSlide = 0;
    } else if (currentSlide < 0) {
        currentSlide = totalSlides - 1;
    }
    
    updateSlider();
}

// Go to specific slide
function goToSlide(slideIndex) {
    currentSlide = slideIndex;
    updateSlider();
}

// Update slider display
function updateSlider() {
    // Update slides
    slides.forEach((slide, index) => {
        slide.classList.toggle('active', index === currentSlide);
    });
    
    // Update dots
    const dots = document.querySelectorAll('.dot');
    dots.forEach((dot, index) => {
        dot.classList.toggle('active', index === currentSlide);
    });
}

// Auto-advance slider
function autoAdvance() {
    moveSlider(1);
}

// Initialize slider
document.addEventListener('DOMContentLoaded', function() {
    if (totalSlides > 0) {
        generateDots();
        
        // Auto-advance every 5 seconds
        setInterval(autoAdvance, 5000);
        
        // Pause auto-advance on hover
        const slider = document.getElementById('collection-slider');
        let autoAdvanceInterval;
        
        slider.addEventListener('mouseenter', () => {
            clearInterval(autoAdvanceInterval);
        });
        
        slider.addEventListener('mouseleave', () => {
            autoAdvanceInterval = setInterval(autoAdvance, 5000);
        });
    }
});
</script>
