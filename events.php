<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

$page_title = "Events - National Museum of Art & Culture";

// Get all events
$all_events = getEvents();

// Get event types for filter
try {
    $stmt = $pdo->prepare("SELECT DISTINCT event_type FROM events WHERE event_type IS NOT NULL AND event_type != ''");
    $stmt->execute();
    $event_types = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch(PDOException $e) {
    $event_types = [];
}

include 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Events & Programs</h1>
        <p>Join us for tours, talks, workshops, and special events</p>
    </div>
</section>

<section class="events-calendar">
    <div class="container">
        <div class="calendar-header">
            <div class="month-selection">
                <button class="prev-month" aria-label="Previous month"><i class="fas fa-chevron-left"></i></button>
                <h2 id="current-month"><?php echo date('F Y'); ?></h2>
                <button class="next-month" aria-label="Next month"><i class="fas fa-chevron-right"></i></button>
            </div>
            <div class="calendar-filters">
                <select id="event-type-filter" aria-label="Filter by event type">
                    <option value="all">All Events</option>
                    <?php foreach ($event_types as $type): ?>
                        <option value="<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $type))); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="calendar-grid">
            <div class="calendar-weekdays">
                <div>Sun</div>
                <div>Mon</div>
                <div>Tue</div>
                <div>Wed</div>
                <div>Thu</div>
                <div>Fri</div>
                <div>Sat</div>
            </div>
            <div class="calendar-days" id="calendar-days">
                <!-- Calendar days will be populated by JavaScript -->
            </div>
        </div>

        <div class="event-list">
            <h3>Today's Events - <?php echo date('F j, Y'); ?></h3>
            <div class="events-container" id="events-container">
                <?php 
                // Get today's events
                $today = date('Y-m-d');
                $today_events = [];
                
                if (!empty($all_events)) {
                    foreach ($all_events as $event) {
                        if (date('Y-m-d', strtotime($event['event_date'])) == $today) {
                            $today_events[] = $event;
                        }
                    }
                }
                
                if (!empty($today_events)): ?>
                    <?php foreach ($today_events as $event): ?>
                        <div class="event-item" data-type="<?php echo htmlspecialchars($event['event_type'] ?? 'general'); ?>">
                            <div class="event-image">
                                <img src="<?php echo !empty($event['image']) ? 'uploads/events/' . $event['image'] : 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=150&h=100&fit=crop'; ?>" alt="<?php echo htmlspecialchars($event['title']); ?>">
                            </div>
                            <div class="event-details">
                                <div class="event-time"><?php echo date('g:i A', strtotime($event['start_time'])); ?> - <?php echo date('g:i A', strtotime($event['end_time'])); ?></div>
                                <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                                <p class="event-location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></p>
                                <?php if (!empty($event['instructor'])): ?>
                                    <p class="event-instructor"><i class="fas fa-user"></i> <?php echo htmlspecialchars($event['instructor']); ?></p>
                                <?php endif; ?>
                                <p><?php echo htmlspecialchars(substr($event['description'], 0, 150)); ?>...</p>
                                <div class="event-footer">
                                    <?php if ($event['price'] > 0): ?>
                                        <span class="event-price">$<?php echo number_format($event['price'], 2); ?></span>
                                    <?php else: ?>
                                        <span class="event-price free">Free</span>
                                    <?php endif; ?>
                                    <a href="dynamic-event-detail.php?id=<?php echo $event['id']; ?>" class="btn btn-secondary">Learn More</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-events">
                        <p>No events scheduled for today. Check out our upcoming events below!</p>
                    </div>
                    
                    <!-- Show next few upcoming events -->
                    <?php 
                    $upcoming = [];
                    if (!empty($all_events)) {
                        foreach ($all_events as $event) {
                            if (strtotime($event['event_date']) > time()) {
                                $upcoming[] = $event;
                            }
                        }
                        $upcoming = array_slice($upcoming, 0, 3);
                    }
                    
                    if (!empty($upcoming)): ?>
                        <h4>Upcoming Events</h4>
                        <?php foreach ($upcoming as $event): ?>
                            <div class="event-item" data-type="<?php echo htmlspecialchars($event['event_type'] ?? 'general'); ?>">
                                <div class="event-image">
                                    <img src="<?php echo !empty($event['image']) ? 'uploads/events/' . $event['image'] : 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=150&h=100&fit=crop'; ?>" alt="<?php echo htmlspecialchars($event['title']); ?>">
                                </div>
                                <div class="event-details">
                                    <div class="event-date"><?php echo date('M j, Y', strtotime($event['event_date'])); ?></div>
                                    <div class="event-time"><?php echo date('g:i A', strtotime($event['start_time'])); ?> - <?php echo date('g:i A', strtotime($event['end_time'])); ?></div>
                                    <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                                    <p class="event-location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></p>
                                    <?php if (!empty($event['instructor'])): ?>
                                        <p class="event-instructor"><i class="fas fa-user"></i> <?php echo htmlspecialchars($event['instructor']); ?></p>
                                    <?php endif; ?>
                                    <p><?php echo htmlspecialchars(substr($event['description'], 0, 150)); ?>...</p>
                                    <div class="event-footer">
                                        <?php if ($event['price'] > 0): ?>
                                            <span class="event-price">$<?php echo number_format($event['price'], 2); ?></span>
                                        <?php else: ?>
                                            <span class="event-price free">Free</span>
                                        <?php endif; ?>
                                        <a href="dynamic-event-detail.php?id=<?php echo $event['id']; ?>" class="btn btn-secondary">Learn More</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Fallback sample events with images -->
                        <div class="event-item" data-type="tour">
                            <div class="event-image">
                                <img src="https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=150&h=100&fit=crop" alt="Guided Tour">
                            </div>
                            <div class="event-details">
                                <div class="event-date"><?php echo date('M j, Y', strtotime('2025-01-15')); ?></div>
                                <div class="event-time">2:00 PM - 3:30 PM</div>
                                <h4>Guided Tour: Modern Masterpieces</h4>
                                <p class="event-location"><i class="fas fa-map-marker-alt"></i> Main Gallery</p>
                                <p class="event-instructor"><i class="fas fa-user"></i> Dr. Sarah Mitchell</p>
                                <p>Join our expert curator for a guided tour of our Modern Masterpieces exhibition featuring works from the 20th century.</p>
                                <div class="event-footer">
                                    <span class="event-price">$15.00</span>
                                    <a href="dynamic-event-detail.php?id=1" class="btn btn-secondary">Learn More</a>
                                </div>
                            </div>
                        </div>
                        <div class="event-item" data-type="workshop">
                            <div class="event-image">
                                <img src="https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?w=150&h=100&fit=crop" alt="Family Workshop">
                            </div>
                            <div class="event-details">
                                <div class="event-date"><?php echo date('M j, Y', strtotime('2025-01-18')); ?></div>
                                <div class="event-time">10:00 AM - 12:00 PM</div>
                                <h4>Family Workshop: Art Exploration</h4>
                                <p class="event-location"><i class="fas fa-map-marker-alt"></i> Education Center</p>
                                <p class="event-instructor"><i class="fas fa-user"></i> Emma Rodriguez</p>
                                <p>A hands-on art workshop for families with children ages 5-12. Create your own masterpiece!</p>
                                <div class="event-footer">
                                    <span class="event-price">$35.00</span>
                                    <a href="dynamic-event-detail.php?id=2" class="btn btn-secondary">Learn More</a>
                                </div>
                            </div>
                        </div>
                        <div class="event-item" data-type="concert">
                            <div class="event-image">
                                <img src="https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=150&h=100&fit=crop" alt="Jazz Concert">
                            </div>
                            <div class="event-details">
                                <div class="event-date"><?php echo date('M j, Y', strtotime('2025-01-22')); ?></div>
                                <div class="event-time">7:00 PM - 9:00 PM</div>
                                <h4>Winter Concert: Jazz in the Gallery</h4>
                                <p class="event-location"><i class="fas fa-map-marker-alt"></i> Sculpture Garden</p>
                                <p class="event-instructor"><i class="fas fa-user"></i> Marcus Williams Quartet</p>
                                <p>Enjoy an evening of smooth jazz in our beautiful sculpture garden under the winter stars.</p>
                                <div class="event-footer">
                                    <span class="event-price">$25.00</span>
                                    <a href="dynamic-event-detail.php?id=3" class="btn btn-secondary">Learn More</a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<section class="featured-events">
    <div class="container">
        <div class="section-header">
            <h2>Featured Programs</h2>
        </div>
        <div class="featured-events-grid">
            <div class="featured-event-card">
                <div class="event-image">
                    <img src="https://images.unsplash.com/photo-1475721027785-f74eccf877e2?w=600&h=400&fit=crop" alt="Artist Lecture Series">
                </div>
                <div class="event-details">
                    <h3>Artist Lecture Series</h3>
                    <p class="event-dates">Thursdays, 6:30 PM - 8:00 PM</p>
                    <p>Join us for our ongoing lecture series featuring prominent artists discussing their work and creative process.</p>
                    <a href="#" class="btn btn-secondary">View Schedule</a>
                </div>
            </div>
            <div class="featured-event-card">
                <div class="event-image">
                    <img src="https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?w=600&h=400&fit=crop" alt="Winter Art Workshops">
                </div>
                <div class="event-details">
                    <h3>Winter Art Workshops</h3>
                    <p class="event-dates">January - March 2025</p>
                    <p>Develop your artistic skills with our series of hands-on workshops taught by professional artists.</p>
                    <a href="#" class="btn btn-secondary">View Schedule</a>
                </div>
            </div>
            <div class="featured-event-card">
                <div class="event-image">
                    <img src="https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=600&h=400&fit=crop" alt="Winter Concert Series">
                </div>
                <div class="event-details">
                    <h3>Winter Concert Series</h3>
                    <p class="event-dates">Friday Evenings, 7:00 PM</p>
                    <p>Enjoy live music in our sculpture garden every Friday evening throughout the winter season.</p>
                    <a href="#" class="btn btn-secondary">View Schedule</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="upcoming-highlight">
    <div class="container">
        <div class="highlight-content">
            <div class="highlight-image">
                <img src="https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?w=800&h=600&fit=crop" alt="Annual Summer Gala">
            </div>
            <div class="highlight-details">
                <h2>Annual Winter Gala</h2>
                <p class="highlight-date">February 15, 2025 • 7:00 PM</p>
                <p>Join us for our most anticipated event of the year. The Annual Winter Gala brings together art lovers, collectors, and artists for an evening of celebration, with proceeds supporting our educational programs.</p>
                <p>The event includes a silent auction, live music, gourmet dining, and special exhibition previews.</p>
                <a href="#" class="btn btn-primary">Learn More & Purchase Tickets</a>
            </div>
        </div>
    </div>
</section>

<style>
/* Enhanced Event Item Styling */
.event-item {
    display: grid;
    grid-template-columns: 150px 1fr;
    gap: 2rem;
    padding: 2rem;
    background: var(--surface);
    border-radius: var(--border-radius-lg);
    margin-bottom: 2rem;
    box-shadow: 0 4px 15px var(--shadow);
    transition: transform 0.3s ease;
}

.event-item:hover {
    transform: translateY(-3px);
}

.event-image {
    border-radius: var(--border-radius);
    overflow: hidden;
}

.event-image img {
    width: 100%;
    height: 100px;
    object-fit: cover;
}

.event-details {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.event-time,
.event-date {
    font-weight: 600;
    color: var(--primary);
    font-size: 1.4rem;
}

.event-details h4 {
    margin: 0.5rem 0;
    color: var(--text);
}

.event-location,
.event-instructor {
    color: var(--text-secondary);
    font-size: 1.3rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.event-location i,
.event-instructor i {
    color: var(--primary);
    width: 1.2rem;
}

.event-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1rem;
}

.event-price {
    background: var(--primary);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: var(--border-radius);
    font-weight: 600;
}

.event-price.free {
    background: var(--success);
}

/* Responsive Design */
@media (max-width: 768px) {
    .event-item {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .event-image {
        height: 200px;
    }
    
    .event-image img {
        height: 200px;
    }
    
    .event-footer {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
}
</style>

<script src="js/main.js"></script>
<script src="js/events-calendar.js"></script>
<script src="js/search.js"></script>
<script>
    // Force light theme for events page
    document.addEventListener('DOMContentLoaded', function() {
        if (window.simpleThemeSwitcher) {
            window.simpleThemeSwitcher.setTheme('light');
        }
        
        // Ensure search functionality works
        if (window.searchManager && !window.searchManager.initialized) {
            window.searchManager.init();
        }
    });
</script>

<?php include 'includes/footer.php'; ?>
