<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$event = null;

if ($event_id > 0) {
    $event = getEventById($event_id);
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
            
            $stmt = $pdo->prepare("INSERT INTO event_bookings (event_id, user_id, name, email, tickets, total_price) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$event_id, $user_id, $name, $email, $tickets, $total_price]);
            
            $booking_message = 'Your booking has been confirmed! You will receive a confirmation email shortly.';
            $booking_type = 'success';
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
                <img src="<?php echo !empty($event['image']) ? 'uploads/events/' . $event['image'] : 'images/events/default.jpg'; ?>" 
                     alt="<?php echo htmlspecialchars($event['title']); ?>">
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
                </div>
                
                <div class="event-description">
                    <h3>About This Event</h3>
                    <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                </div>
                
                <?php if (!empty($event['event_type'])): ?>
                    <div class="event-type">
                        <span class="type-badge"><?php echo htmlspecialchars(ucfirst($event['event_type'])); ?></span>
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
