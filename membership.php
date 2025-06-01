<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

$page_title = "Membership - National Museum of Art & Culture";

$message = '';
$message_type = '';

// Handle membership signup
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['join_membership'])) {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $membership_type = $_POST['membership_type'] ?? '';
    
    if (!empty($first_name) && !empty($last_name) && !empty($email) && !empty($membership_type)) {
        try {
            // Check if user already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $existing_user = $stmt->fetch();
            
            if ($existing_user) {
                $user_id = $existing_user['id'];
            } else {
                // Create new user account
                $password = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, 'member')");
                $stmt->execute([$first_name, $last_name, $email, $password]);
                $user_id = $pdo->lastInsertId();
            }
            
            // Create membership
            $start_date = date('Y-m-d');
            $end_date = date('Y-m-d', strtotime('+1 year'));
            
            $stmt = $pdo->prepare("INSERT INTO memberships (user_id, membership_type, start_date, end_date, status) VALUES (?, ?, ?, ?, 'active')");
            $stmt->execute([$user_id, $membership_type, $start_date, $end_date]);
            
            $message = 'Thank you for joining! Your membership application has been submitted. You will receive a confirmation email shortly.';
            $message_type = 'success';
            
        } catch(PDOException $e) {
            $message = 'An error occurred while processing your membership. Please try again.';
            $message_type = 'error';
        }
    } else {
        $message = 'Please fill in all required fields.';
        $message_type = 'error';
    }
}

include 'includes/header.php';
?>

<section class="membership-hero">
    <div class="container">
        <h1>Become a Member</h1>
        <p>Join our community and enjoy exclusive benefits, unlimited access, and special experiences at the National Museum of Art & Culture.</p>
    </div>
</section>

<section class="membership-tiers">
    <div class="container">
        <h2>Membership Levels</h2>
        <p>Choose the membership level that's right for you and start enjoying the benefits today.</p>
        
        <div class="tiers-grid">
            <div class="tier-card">
                <h3 class="tier-name">Individual</h3>
                <div class="tier-price">$75</div>
                <div class="tier-period">per year</div>
                <ul class="tier-benefits">
                    <li><i class="fas fa-check"></i> Unlimited free admission</li>
                    <li><i class="fas fa-check"></i> 10% discount in museum shop</li>
                    <li><i class="fas fa-check"></i> Member newsletter</li>
                    <li><i class="fas fa-check"></i> Priority event registration</li>
                    <li><i class="fas fa-check"></i> Free coat check</li>
                </ul>
                <button class="btn btn-primary" onclick="selectMembership('individual', 75)">Join Now</button>
            </div>
            
            <div class="tier-card featured">
                <h3 class="tier-name">Family</h3>
                <div class="tier-price">$125</div>
                <div class="tier-period">per year</div>
                <ul class="tier-benefits">
                    <li><i class="fas fa-check"></i> Unlimited free admission for 2 adults + children</li>
                    <li><i class="fas fa-check"></i> 15% discount in museum shop</li>
                    <li><i class="fas fa-check"></i> Member newsletter</li>
                    <li><i class="fas fa-check"></i> Priority event registration</li>
                    <li><i class="fas fa-check"></i> Free coat check</li>
                    <li><i class="fas fa-check"></i> Family program discounts</li>
                    <li><i class="fas fa-check"></i> Guest passes (4 per year)</li>
                </ul>
                <button class="btn btn-primary" onclick="selectMembership('family', 125)">Join Now</button>
            </div>
            
            <div class="tier-card">
                <h3 class="tier-name">Student</h3>
                <div class="tier-price">$45</div>
                <div class="tier-period">per year</div>
                <ul class="tier-benefits">
                    <li><i class="fas fa-check"></i> Unlimited free admission</li>
                    <li><i class="fas fa-check"></i> 10% discount in museum shop</li>
                    <li><i class="fas fa-check"></i> Member newsletter</li>
                    <li><i class="fas fa-check"></i> Priority event registration</li>
                    <li><i class="fas fa-check"></i> Student workshop discounts</li>
                </ul>
                <button class="btn btn-primary" onclick="selectMembership('student', 45)">Join Now</button>
            </div>
            
            <div class="tier-card">
                <h3 class="tier-name">Senior</h3>
                <div class="tier-price">$60</div>
                <div class="tier-period">per year</div>
                <ul class="tier-benefits">
                    <li><i class="fas fa-check"></i> Unlimited free admission</li>
                    <li><i class="fas fa-check"></i> 15% discount in museum shop</li>
                    <li><i class="fas fa-check"></i> Member newsletter</li>
                    <li><i class="fas fa-check"></i> Priority event registration</li>
                    <li><i class="fas fa-check"></i> Senior program discounts</li>
                    <li><i class="fas fa-check"></i> Guest passes (2 per year)</li>
                </ul>
                <button class="btn btn-primary" onclick="selectMembership('senior', 60)">Join Now</button>
            </div>
            
            <div class="tier-card">
                <h3 class="tier-name">Patron</h3>
                <div class="tier-price">$500</div>
                <div class="tier-period">per year</div>
                <ul class="tier-benefits">
                    <li><i class="fas fa-check"></i> All Family benefits</li>
                    <li><i class="fas fa-check"></i> 20% discount in museum shop</li>
                    <li><i class="fas fa-check"></i> Exclusive patron events</li>
                    <li><i class="fas fa-check"></i> Behind-the-scenes tours</li>
                    <li><i class="fas fa-check"></i> Curator talks</li>
                    <li><i class="fas fa-check"></i> Guest passes (8 per year)</li>
                    <li><i class="fas fa-check"></i> Recognition in annual report</li>
                </ul>
                <button class="btn btn-primary" onclick="selectMembership('patron', 500)">Join Now</button>
            </div>
        </div>
    </div>
</section>

<section class="membership-benefits">
    <div class="container">
        <h2>Member Benefits</h2>
        <p>Discover all the exclusive advantages of being a museum member.</p>
        
        <div class="benefits-grid">
            <div class="benefit-item">
                <div class="benefit-icon">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <h3>Free Admission</h3>
                <p>Enjoy unlimited free admission to all exhibitions and permanent collections year-round.</p>
            </div>
            
            <div class="benefit-item">
                <div class="benefit-icon">
                    <i class="fas fa-calendar-star"></i>
                </div>
                <h3>Exclusive Events</h3>
                <p>Access to member-only events, exhibition previews, and special programs throughout the year.</p>
            </div>
            
            <div class="benefit-item">
                <div class="benefit-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <h3>Shop Discounts</h3>
                <p>Save on books, gifts, and exhibition catalogs with exclusive member discounts.</p>
            </div>
            
            <div class="benefit-item">
                <div class="benefit-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <h3>Member Newsletter</h3>
                <p>Stay informed with our monthly newsletter featuring upcoming exhibitions and events.</p>
            </div>
            
            <div class="benefit-item">
                <div class="benefit-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Guest Privileges</h3>
                <p>Bring friends and family with complimentary guest passes included with your membership.</p>
            </div>
            
            <div class="benefit-item">
                <div class="benefit-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <h3>Support the Arts</h3>
                <p>Your membership directly supports our mission to preserve and share art and culture.</p>
            </div>
        </div>
    </div>
</section>

<!-- Membership Signup Modal -->
<div id="membership-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Join as <span id="selected-tier"></span> Member</h2>
            <button class="modal-close" onclick="closeMembershipModal()">&times;</button>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="membership-form">
            <input type="hidden" name="membership_type" id="membership-type-input">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name *</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name *</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone">
            </div>
            
            <div class="membership-summary">
                <h3>Membership Summary</h3>
                <div class="summary-item">
                    <span>Membership Type:</span>
                    <span id="summary-type"></span>
                </div>
                <div class="summary-item">
                    <span>Annual Fee:</span>
                    <span id="summary-price"></span>
                </div>
                <div class="summary-item">
                    <span>Valid Until:</span>
                    <span><?php echo date('F j, Y', strtotime('+1 year')); ?></span>
                </div>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" required>
                    I agree to the <a href="terms.php" target="_blank">Terms and Conditions</a> and <a href="privacy.php" target="_blank">Privacy Policy</a>
                </label>
            </div>
            
            <button type="submit" name="join_membership" class="btn btn-primary btn-block">Complete Membership</button>
        </form>
    </div>
</div>

<style>
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: var(--surface);
    padding: 3rem;
    border-radius: var(--border-radius-lg);
    max-width: 60rem;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--divider);
}

.modal-close {
    background: none;
    border: none;
    font-size: 3rem;
    cursor: pointer;
    color: var(--text-secondary);
}

.membership-form .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
}

.membership-summary {
    background: var(--background-alt);
    padding: 2rem;
    border-radius: var(--border-radius);
    margin: 2rem 0;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--divider);
}

.summary-item:last-child {
    border-bottom: none;
    font-weight: 600;
    font-size: 1.8rem;
}

.btn-block {
    width: 100%;
    padding: 1.5rem;
    font-size: 1.8rem;
}

@media (max-width: 768px) {
    .membership-form .form-row {
        grid-template-columns: 1fr;
    }
    
    .modal-content {
        width: 95%;
        padding: 2rem;
    }
}
</style>

<script>
function selectMembership(type, price) {
    document.getElementById('selected-tier').textContent = type.charAt(0).toUpperCase() + type.slice(1);
    document.getElementById('membership-type-input').value = type;
    document.getElementById('summary-type').textContent = type.charAt(0).toUpperCase() + type.slice(1);
    document.getElementById('summary-price').textContent = '$' + price;
    document.getElementById('membership-modal').style.display = 'flex';
}

function closeMembershipModal() {
    document.getElementById('membership-modal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('membership-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeMembershipModal();
    }
});
</script>

<?php include 'includes/footer.php'; ?>
