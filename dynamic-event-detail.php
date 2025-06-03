<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Sample events data for demonstration
$sample_events = [
    1 => [
        'id' => 1,
        'title' => 'Guided Tour: Modern Masterpieces',
        'description' => 'Join our expert curator for an in-depth guided tour of our Modern Masterpieces exhibition. This 90-minute tour will take you through the evolution of modern art from the late 19th century to the mid-20th century, featuring works by renowned artists such as Van Gogh, Picasso, and Monet. Learn about the revolutionary techniques and bold visions that defined an era of artistic innovation. Our knowledgeable guide will share fascinating stories behind each masterpiece and provide insights into the historical context that shaped these groundbreaking works.',
        'event_date' => '2025-01-15',
        'start_time' => '14:00:00',
        'end_time' => '15:30:00',
        'location' => 'Main Gallery',
        'event_type' => 'guided_tour',
        'capacity' => 25,
        'price' => 15.00,
        'image' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=800&h=600&fit=crop',
        'instructor' => 'Dr. Sarah Mitchell',
        'status' => 'active'
    ],
    2 => [
        'id' => 2,
        'title' => 'Family Workshop: Art Exploration',
        'description' => 'A hands-on art workshop designed for families with children ages 5-12. This interactive session will introduce young artists to various art techniques and mediums while exploring creativity and self-expression. Participants will create their own masterpiece to take home, inspired by works from our permanent collection. All materials are provided, and no prior art experience is necessary. This workshop encourages family bonding through art and provides a fun, educational experience for all ages.',
        'event_date' => '2025-01-18',
        'start_time' => '10:00:00',
        'end_time' => '12:00:00',
        'location' => 'Education Center',
        'event_type' => 'workshop',
        'capacity' => 20,
        'price' => 35.00,
        'image' => 'https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?w=800&h=600&fit=crop',
        'instructor' => 'Emma Rodriguez',
        'status' => 'active'
    ],
    3 => [
        'id' => 3,
        'title' => 'Winter Concert: Jazz in the Gallery',
        'description' => 'Enjoy an enchanting evening of smooth jazz in our beautiful sculpture garden. The Marcus Williams Quartet will perform a selection of classic jazz standards and contemporary pieces, creating the perfect ambiance for a winter evening under the stars. This intimate concert setting allows you to experience music surrounded by stunning sculptures and art installations. Light refreshments will be available for purchase. Dress warmly as this is an outdoor event.',
        'event_date' => '2025-01-22',
        'start_time' => '19:00:00',
        'end_time' => '21:00:00',
        'location' => 'Sculpture Garden',
        'event_type' => 'concert',
        'capacity' => 100,
        'price' => 25.00,
        'image' => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=800&h=600&fit=crop',
        'instructor' => 'Marcus Williams Quartet',
        'status' => 'active'
    ],
    4 => [
        'id' => 4,
        'title' => 'Digital Art Lecture: The Future of Creativity',
        'description' => 'Join renowned digital artist Alex Chen for an inspiring lecture on the intersection of technology and art. This presentation will explore how digital tools are revolutionizing artistic expression and creating new possibilities for creative storytelling. Chen will showcase their latest work, including the museum\'s featured "Digital Infinity" installation, and discuss the future of digital art in museum spaces. The lecture includes a Q&A session and networking opportunity with fellow art enthusiasts.',
        'event_date' => '2025-01-25',
        'start_time' => '18:30:00',
        'end_time' => '20:00:00',
        'location' => 'Auditorium',
        'event_type' => 'lecture',
        'capacity' => 150,
        'price' => 20.00,
        'image' => 'https://images.unsplash.com/photo-1518709268805-4e9042af2176?w=800&h=600&fit=crop',
        'instructor' => 'Alex Chen',
        'status' => 'active'
    ]
];

$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$event = null;

// Try to get event from database first
if ($event_id > 0) {
    $event = getEventById($event_id);
}

// If not found in database, use sample data
if (!$event && isset($sample_events[$event_id])) {
    $event = $sample_events[$event_id];
}

if (!$event) {
    // Redirect to events page if not found
    header('Location: events.php');
    exit;
}

$page_title = htmlspecialchars($event['title']) . " - National Museum of Art & Culture";

// Handle booking submission
$booking_message = '';
$booking_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_event'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $tickets = (int)($_POST['tickets'] ?? 1);
    
    if (!empty($name) && !empty($email) && $tickets > 0) {
        try {
            $total_price = $event['price'] * $tickets;
            $user_id = isLoggedIn() ? $_SESSION['user_id'] : null;
            
            // For sample events, just show success message
            if (isset($sample_events[$event_id])) {
                $booking_message = 'Your booking has been confirmed! You will receive a confirmation email shortly.';
                $booking_type = 'success';
            } else {
                // For database events, actually save the booking
                $stmt = $pdo->prepare("INSERT INTO event_bookings (event_id, user_id, name, email, tickets, total_price) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$event_id, $user_id, $name, $email, $tickets, $total_price]);
                
                $booking_message = 'Your booking has been confirmed! You will receive a confirmation email shortly.';
                $booking_type = 'success';
            }
        } catch(PDOException $e) {
            $booking_message = 'An error occurred while processing your booking. Please try again.';
            $booking_type = 'error';
        }
    } else {
        $booking_message = 'Please fill in all required fields.';
        $booking_type = 'error';
    }
}

include 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <nav class="breadcrumb">
            <a href="index.php">Home</a> > 
            <a href="events.php">Events</a> > 
            <span><?php echo htmlspecialchars($event['title']); ?></span>
        </nav>
    </div>
</section>

<section class="event-detail">
    <div class="container">
        <div class="detail-grid">
            <div class="event-image">
                <img src="<?php echo htmlspecialchars($event['image']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>">
            </div>
            
            <div class="event-info">
                <h1><?php echo htmlspecialchars($event['title']); ?></h1>
                
                <div class="event-meta">
                    <div class="meta-item">
                        <i class="fas fa-calendar"></i>
                        <span><?php echo formatDate($event['event_date'], 'l, F j, Y'); ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-clock"></i>
                        <span><?php echo date('g:i A', strtotime($event['start_time'])); ?> - <?php echo date('g:i A', strtotime($event['end_time'])); ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?php echo htmlspecialchars($event['location']); ?></span>
                    </div>
                    <?php if (!empty($event['instructor'])): ?>
                        <div class="meta-item">
                            <i class="fas fa-user"></i>
                            <span><?php echo htmlspecialchars($event['instructor']); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ($event['price'] > 0): ?>
                        <div class="meta-item">
                            <i class="fas fa-ticket-alt"></i>
                            <span>$<?php echo number_format($event['price'], 2); ?> per person</span>
                        </div>
                    <?php else: ?>
                        <div class="meta-item">
                            <i class="fas fa-ticket-alt"></i>
                            <span>Free admission</span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($event['capacity'])): ?>
                        <div class="meta-item">
                            <i class="fas fa-users"></i>
                            <span>Limited to <?php echo $event['capacity']; ?> participants</span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="event-description">
                    <h3>About This Event</h3>
                    <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                </div>
                
                <?php if (!empty($event['event_type'])): ?>
                    <div class="event-type">
                        <span class="type-badge"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $event['event_type']))); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<section class="event-booking">
    <div class="container">
        <div class="booking-container">
            <h2>Reserve Your Spot</h2>
            
            <?php if (!empty($booking_message)): ?>
                <div class="message <?php echo $booking_type; ?>">
                    <?php echo htmlspecialchars($booking_message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="booking-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="tickets">Number of Tickets</label>
                    <select id="tickets" name="tickets" onchange="updateTotal()">
                        <option value="1">1 ticket</option>
                        <option value="2">2 tickets</option>
                        <option value="3">3 tickets</option>
                        <option value="4">4 tickets</option>
                        <option value="5">5 tickets</option>
                    </select>
                </div>
                
                <div class="booking-summary">
                    <div class="summary-row">
                        <span>Event:</span>
                        <span><?php echo htmlspecialchars($event['title']); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Date:</span>
                        <span><?php echo formatDate($event['event_date'], 'F j, Y'); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Time:</span>
                        <span><?php echo date('g:i A', strtotime($event['start_time'])); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Price per ticket:</span>
                        <span>$<?php echo number_format($event['price'], 2); ?></span>
                    </div>
                    <div class="summary-row total">
                        <span>Total:</span>
                        <span id="total-price">$<?php echo number_format($event['price'], 2); ?></span>
                    </div>
                </div>
                
                <button type="submit" name="book_event" class="btn btn-primary btn-block">
                    <?php echo $event['price'] > 0 ? 'Reserve & Pay' : 'Reserve Spot'; ?>
                </button>
            </form>
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
    margin-bottom: 6rem;
}

.event-image img {
    width: 100%;
    border-radius: var(--border-radius-lg);
    box-shadow: 0 10px 30px var(--shadow);
}

.event-meta {
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

.event-description {
    margin-bottom: 3rem;
}

.type-badge {
    background: var(--primary);
    color: white;
    padding: 0.5rem 1.5rem;
    border-radius: 2rem;
    font-size: 1.4rem;
    font-weight: 500;
}

.booking-container {
    max-width: 60rem;
    margin: 0 auto;
    background: var(--surface);
    padding: 4rem;
    border-radius: var(--border-radius-lg);
    box-shadow: 0 10px 30px var(--shadow);
}

.booking-form .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
}

.booking-summary {
    background: var(--background-alt);
    padding: 2rem;
    border-radius: var(--border-radius);
    margin: 2rem 0;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 1rem 0;
    border-bottom: 1px solid var(--divider);
}

.summary-row:last-child {
    border-bottom: none;
}

.summary-row.total {
    font-weight: 600;
    font-size: 1.8rem;
    color: var(--primary);
}

.message {
    padding: 1rem 2rem;
    border-radius: var(--border-radius);
    margin-bottom: 2rem;
}

.message.success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.message.error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

@media (max-width: 768px) {
    .detail-grid {
        grid-template-columns: 1fr;
        gap: 3rem;
    }
    
    .booking-container {
        padding: 2rem;
    }
    
    .booking-form .form-row {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function updateTotal() {
    const tickets = document.getElementById('tickets').value;
    const pricePerTicket = <?php echo $event['price']; ?>;
    const total = tickets * pricePerTicket;
    document.getElementById('total-price').textContent = '$' + total.toFixed(2);
}
</script>

<?php include 'includes/footer.php'; ?>
