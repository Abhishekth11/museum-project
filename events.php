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
                        <option value="<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars(ucfirst($type)); ?></option>
                    <?php endforeach; ?>
                    <option value="tour">Tours</option>
                    <option value="talk">Talks</option>
                    <option value="workshop">Workshops</option>
                    <option value="family">Family Programs</option>
                    <option value="special">Special Events</option>
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
                            <div class="event-time"><?php echo date('g:i A', strtotime($event['start_time'])); ?> - <?php echo date('g:i A', strtotime($event['end_time'])); ?></div>
                            <div class="event-details">
                                <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                                <p class="event-location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></p>
                                <p><?php echo htmlspecialchars(substr($event['description'], 0, 150)); ?>...</p>
                                <a href="event-detail.php?id=<?php echo $event['id']; ?>" class="btn btn-secondary">Learn More</a>
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
                                <div class="event-date"><?php echo date('M j, Y', strtotime($event['event_date'])); ?></div>
                                <div class="event-time"><?php echo date('g:i A', strtotime($event['start_time'])); ?> - <?php echo date('g:i A', strtotime($event['end_time'])); ?></div>
                                <div class="event-details">
                                    <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                                    <p class="event-location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></p>
                                    <p><?php echo htmlspecialchars(substr($event['description'], 0, 150)); ?>...</p>
                                    <a href="event-detail.php?id=<?php echo $event['id']; ?>" class="btn btn-secondary">Learn More</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Fallback sample events -->
                        <div class="event-item" data-type="tour">
                            <div class="event-date"><?php echo date('M j, Y', strtotime('+3 days')); ?></div>
                            <div class="event-time">2:00 PM - 3:30 PM</div>
                            <div class="event-details">
                                <h4>Guided Tour: Modern Masterpieces</h4>
                                <p class="event-location"><i class="fas fa-map-marker-alt"></i> Main Gallery</p>
                                <p>Join our expert curator for a guided tour of our Modern Masterpieces exhibition featuring works from the 20th century.</p>
                                <a href="event-detail.php" class="btn btn-secondary">Learn More</a>
                            </div>
                        </div>
                        <div class="event-item" data-type="workshop">
                            <div class="event-date"><?php echo date('M j, Y', strtotime('+5 days')); ?></div>
                            <div class="event-time">10:00 AM - 12:00 PM</div>
                            <div class="event-details">
                                <h4>Family Workshop: Art Exploration</h4>
                                <p class="event-location"><i class="fas fa-map-marker-alt"></i> Education Center</p>
                                <p>A hands-on art workshop for families with children ages 5-12. Create your own masterpiece!</p>
                                <a href="event-detail.php" class="btn btn-secondary">Learn More</a>
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
                    <img src="https://source.unsplash.com/random/600x400/?lecture" alt="Artist Lecture Series">
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
                    <img src="https://source.unsplash.com/random/600x400/?art-workshop" alt="Summer Art Workshops">
                </div>
                <div class="event-details">
                    <h3>Summer Art Workshops</h3>
                    <p class="event-dates">June - August 2024</p>
                    <p>Develop your artistic skills with our series of hands-on workshops taught by professional artists.</p>
                    <a href="#" class="btn btn-secondary">View Schedule</a>
                </div>
            </div>
            <div class="featured-event-card">
                <div class="event-image">
                    <img src="https://source.unsplash.com/random/600x400/?music-performance" alt="Summer Concert Series">
                </div>
                <div class="event-details">
                    <h3>Summer Concert Series</h3>
                    <p class="event-dates">Friday Evenings, 7:00 PM</p>
                    <p>Enjoy live music in our sculpture garden every Friday evening throughout the summer.</p>
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
                <img src="https://source.unsplash.com/random/800x600/?art-gala" alt="Annual Summer Gala">
            </div>
            <div class="highlight-details">
                <h2>Annual Summer Gala</h2>
                <p class="highlight-date">July 15, 2024 • 7:00 PM</p>
                <p>Join us for our most anticipated event of the year. The Annual Summer Gala brings together art lovers, collectors, and artists for an evening of celebration, with proceeds supporting our educational programs.</p>
                <p>The event includes a silent auction, live music, gourmet dining, and special exhibition previews.</p>
                <a href="#" class="btn btn-primary">Learn More & Purchase Tickets</a>
            </div>
        </div>
    </div>
</section>

<script src="js/main.js"></script>
<script src="js/events-calendar.js"></script>

<?php include 'includes/footer.php'; ?>
