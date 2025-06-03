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

<?php if ($welcome_message || isLoggedIn()): ?>
<div class="welcome-banner">
    <div class="container">
        <div class="welcome-message col-6">
            <i class="fas fa-check-circle"></i>
            <span><?php echo htmlspecialchars($welcome_message); ?></span>
            <button class="close-welcome" onclick="this.parentElement.parentElement.style.display='none'">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="dashboard-preview-content col-6">
            <h3>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h3>
                <div class="admin-quick-links">
                    <a href="admin/index.php" class="btn btn-secondary">
                         Go to Dashboard
                    </a>
                </div>
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
                <img src="<?php echo !empty($featured_exhibition['image']) ? 'uploads/exhibitions/' . $featured_exhibition['image'] : 'https://images.unsplash.com/photo-1541961017774-22349e4a1262?w=800&h=600&fit=crop'; ?>" alt="<?php echo htmlspecialchars($featured_exhibition['title']); ?>">
                <div class="exhibit-date">Until <?php echo formatDate($featured_exhibition['end_date']); ?></div>
            </div>
            <div class="featured-details">
                <h3><?php echo htmlspecialchars($featured_exhibition['title']); ?></h3>
                <p class="exhibition-info"><?php echo htmlspecialchars($featured_exhibition['location']); ?></p>
                <?php if (!empty($featured_exhibition['curator'])): ?>
                    <p class="curator">Curated by <?php echo htmlspecialchars($featured_exhibition['curator']); ?></p>
                <?php endif; ?>
                <p><?php echo htmlspecialchars($featured_exhibition['description']); ?></p>
                <div class="tags">
                    <span><?php echo htmlspecialchars($featured_exhibition['category']); ?></span>
                    <?php if ($featured_exhibition['price'] > 0): ?>
                        <span>$<?php echo number_format($featured_exhibition['price'], 2); ?></span>
                    <?php endif; ?>
                </div>
                <a href="exhibition-detail.php?id=<?php echo $featured_exhibition['id']; ?>" class="btn btn-secondary">Learn More</a>
            </div>
        </div>
        <?php else: ?>
        <div class="featured-content">
            <div class="featured-image">
                <img src="https://images.unsplash.com/photo-1541961017774-22349e4a1262?w=800&h=600&fit=crop" alt="Modern Masterpieces Exhibition">
                <div class="exhibit-date">Until March 15, 2025</div>
            </div>
            <div class="featured-details">
                <h3>Modern Masterpieces: A Century of Innovation</h3>
                <p class="exhibition-info">Main Gallery • Floor 2</p>
                <p>Curated by Dr. Sarah Mitchell</p>
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
                            <?php if (!empty($event['instructor'])): ?>
                                <p class="event-instructor"><i class="fas fa-user"></i> <?php echo htmlspecialchars($event['instructor']); ?></p>
                            <?php endif; ?>
                            <?php if ($event['price'] > 0): ?>
                                <p class="event-price">$<?php echo number_format($event['price'], 2); ?></p>
                            <?php else: ?>
                                <p class="event-price free">Free</p>
                            <?php endif; ?>
                            <a href="event-detail.php?id=<?php echo $event['id']; ?>" class="btn btn-text">Reserve Spot <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="event-card">
                    <div class="event-date">
                        <span class="month">JAN</span>
                        <span class="day">15</span>
                    </div>
                    <div class="event-details">
                        <h3>Guided Tour: Modern Masterpieces</h3>
                        <p class="event-time"><i class="far fa-clock"></i> 2:00 PM - 3:30 PM</p>
                        <p class="event-location"><i class="fas fa-map-marker-alt"></i> Main Gallery</p>
                        <p class="event-instructor"><i class="fas fa-user"></i> Dr. Sarah Mitchell</p>
                        <p class="event-price">$15.00</p>
                        <a href="events.php" class="btn btn-text">Reserve Spot <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <div class="event-card">
                    <div class="event-date">
                        <span class="month">JAN</span>
                        <span class="day">18</span>
                    </div>
                    <div class="event-details">
                        <h3>Artist Talk: Contemporary Expressions</h3>
                        <p class="event-time"><i class="far fa-clock"></i> 6:30 PM - 8:00 PM</p>
                        <p class="event-location"><i class="fas fa-map-marker-alt"></i> Auditorium</p>
                        <p class="event-instructor"><i class="fas fa-user"></i> Alex Chen</p>
                        <p class="event-price">$20.00</p>
                        <a href="events.php" class="btn btn-text">Reserve Spot <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <div class="event-card">
                    <div class="event-date">
                        <span class="month">JAN</span>
                        <span class="day">22</span>
                    </div>
                    <div class="event-details">
                        <h3>Family Workshop: Create Your Own Masterpiece</h3>
                        <p class="event-time"><i class="far fa-clock"></i> 10:00 AM - 1:00 PM</p>
                        <p class="event-location"><i class="fas fa-map-marker-alt"></i> Workshop Space</p>
                        <p class="event-instructor"><i class="fas fa-user"></i> Emma Rodriguez</p>
                        <p class="event-price">$35.00</p>
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
                <?php 
                // Sample collection highlights with images
                $sample_highlights = [
                    [
                        'id' => 1,
                        'title' => 'The Starry Night (Study)',
                        'artist' => 'Vincent van Gogh',
                        'year' => '1889',
                        'image' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=400&h=500&fit=crop'
                    ],
                    [
                        'id' => 2,
                        'title' => 'Digital Infinity',
                        'artist' => 'Alex Chen',
                        'year' => '2024',
                        'image' => 'https://images.unsplash.com/photo-1518709268805-4e9042af2176?w=400&h=500&fit=crop'
                    ],
                    [
                        'id' => 3,
                        'title' => 'David (Bronze Cast)',
                        'artist' => 'Michelangelo',
                        'year' => '1501-1504',
                        'image' => 'https://images.unsplash.com/photo-1594736797933-d0401ba2fe65?w=400&h=500&fit=crop'
                    ],
                    [
                        'id' => 4,
                        'title' => 'Water Lilies',
                        'artist' => 'Claude Monet',
                        'year' => '1919',
                        'image' => 'https://images.unsplash.com/photo-1578321272176-b7bbc0679853?w=400&h=500&fit=crop'
                    ]
                ];
                
                $highlights = !empty($collection_highlights) ? $collection_highlights : $sample_highlights;
                
                foreach ($highlights as $index => $collection): ?>
                    <div class="collection-item <?php echo $index === 0 ? 'active' : ''; ?>" style="display: <?php echo $index < 2 ? 'block' : 'none'; ?>;">
                        <img src="<?php echo htmlspecialchars($collection['image']); ?>" alt="<?php echo htmlspecialchars($collection['title']); ?>">
                        <div class="collection-info">
                            <h3><?php echo htmlspecialchars($collection['title']); ?></h3>
                            <p><?php echo htmlspecialchars($collection['artist'] ?? 'Unknown Artist'); ?></p>
                            <p class="collection-year"><?php echo htmlspecialchars($collection['year'] ?? 'Date Unknown'); ?></p>
                            <a href="collection-detail.php?id=<?php echo $collection['id']; ?>" class="btn btn-text">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
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
                <img src="https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=600&h=800&fit=crop" alt="Museum building">
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
// Enhanced Collection Slider Functionality - Show 2 items at a time
let currentSlide = 0;
const slides = document.querySelectorAll('.collection-item');
const totalSlides = slides.length;
const itemsPerView = 2;
const maxSlides = Math.ceil(totalSlides / itemsPerView);

// Generate dots
function generateDots() {
    const dotsContainer = document.getElementById('slider-dots');
    dotsContainer.innerHTML = '';
    
    for (let i = 0; i < maxSlides; i++) {
        const dot = document.createElement('span');
        dot.className = `dot ${i === 0 ? 'active' : ''}`;
        dot.onclick = () => goToSlide(i);
        dotsContainer.appendChild(dot);
    }
}

// Move slider
function moveSlider(direction) {
    currentSlide += direction;
    
    if (currentSlide >= maxSlides) {
        currentSlide = 0;
    } else if (currentSlide < 0) {
        currentSlide = maxSlides - 1;
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
    // Hide all slides
    slides.forEach((slide, index) => {
        slide.style.display = 'none';
        slide.classList.remove('active');
    });
    
    // Show current 2 slides
    const startIndex = currentSlide * itemsPerView;
    for (let i = startIndex; i < startIndex + itemsPerView && i < totalSlides; i++) {
        slides[i].style.display = 'block';
        if (i === startIndex) {
            slides[i].classList.add('active');
        }
    }
    
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
        updateSlider(); // Initial display
        
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

<style>
/* Enhanced styling for better image display */
.curator {
    font-style: italic;
    color: var(--text-secondary);
    margin-bottom: 1rem;
}

.event-instructor {
    color: var(--primary);
    font-weight: 500;
}

.event-price {
    font-weight: 600;
    color: var(--primary);
}

.event-price.free {
    color: var(--success);
}

.collection-slider {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 2rem;
    margin-bottom: 2rem;
}

.collection-item {
    position: relative;
    border-radius: var(--border-radius);
    overflow: hidden;
    height: 40rem;
}

.collection-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform var(--transition-medium);
}

.collection-item:hover img {
    transform: scale(1.05);
}

@media (max-width: 768px) {
    .collection-slider {
        grid-template-columns: 1fr;
    }
}
</style>
