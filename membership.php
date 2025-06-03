<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

$page_title = "Membership - National Museum of Art & Culture";

$message = '';
$message_type = '';

// Handle membership signup
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['join_membership'])) {
    // Check if user is logged in
    if (!isLoggedIn()) {
        $_SESSION['redirect_url'] = 'membership.php';
        header('Location: login.php');
        exit;
    }
    
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid security token. Please try again.';
        $message_type = 'error';
    } else {
        $membership_type = $_POST['membership_type'] ?? '';
        $payment_method = $_POST['payment_method'] ?? 'credit_card';
        $billing_address = $_POST['billing_address'] ?? '';
        $phone_number = $_POST['phone_number'] ?? '';
        
        if (!empty($membership_type)) {
            // Get current user data
            $user_data = getUserById($_SESSION['user_id']);
            
            if ($user_data) {
                // Create membership with additional details
                $membership_data = [
                    'membership_type' => $membership_type,
                    'payment_method' => $payment_method,
                    'billing_address' => $billing_address,
                    'phone_number' => $phone_number
                ];
                
                $result = joinMembershipEnhanced($user_data, $membership_data);
                $message = $result['message'];
                $message_type = $result['success'] ? 'success' : 'error';
                
                if ($result['success']) {
                    // Redirect to success page to prevent form resubmission
                    $_SESSION['membership_success'] = true;
                    $_SESSION['membership_details'] = $result['membership_details'];
                    header('Location: membership.php?success=1');
                    exit;
                }
            } else {
                $message = 'User data not found. Please try logging in again.';
                $message_type = 'error';
            }
        } else {
            $message = 'Please select a membership type.';
            $message_type = 'error';
        }
    }
}

// Handle success redirect
if (isset($_GET['success']) && isset($_SESSION['membership_success'])) {
    $message = 'Congratulations! Your membership has been successfully activated. A confirmation email has been sent to your email address.';
    $message_type = 'success';
    $membership_details = $_SESSION['membership_details'] ?? null;
    unset($_SESSION['membership_success'], $_SESSION['membership_details']);
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
        
        <?php if (isLoggedIn()): ?>
            <form method="POST" class="membership-form" id="membership-signup-form">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="membership_type" id="membership-type-input">
                
                <div class="user-info">
                    <h3>Your Information</h3>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars(getUserById($_SESSION['user_id'])['email'] ?? ''); ?></p>
                </div>
                
                <div class="form-group">
                    <label for="phone_number">Phone Number</label>
                    <input type="tel" id="phone_number" name="phone_number" required 
                           placeholder="(123) 456-7890" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="billing_address">Billing Address</label>
                    <textarea id="billing_address" name="billing_address" required 
                              placeholder="Enter your complete billing address" 
                              class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="payment_method">Payment Method</label>
                    <select id="payment_method" name="payment_method" required class="form-control">
                        <option value="credit_card">Credit Card</option>
                        <option value="debit_card">Debit Card</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="paypal">PayPal</option>
                    </select>
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
                    <div class="summary-item">
                        <span>Member ID:</span>
                        <span id="member-id-preview">Will be assigned after signup</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" required>
                        <span class="checkmark"></span>
                        I agree to the <a href="terms.php" target="_blank">Terms and Conditions</a> and <a href="privacy.php" target="_blank">Privacy Policy</a>
                    </label>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="newsletter_opt_in" checked>
                        <span class="checkmark"></span>
                        I would like to receive the museum newsletter and updates about special events
                    </label>
                </div>
                
                <button type="submit" name="join_membership" class="btn btn-primary btn-block">
                    <i class="fas fa-credit-card"></i> Complete Membership Registration
                </button>
            </form>
        <?php else: ?>
            <div class="login-required">
                <h3>Login Required</h3>
                <p>Please log in to your account to join as a member.</p>
                <a href="login.php" class="btn btn-primary">Login</a>
                <p>Don't have an account? <a href="login.php?register=1">Create one here</a></p>
            </div>
        <?php endif; ?>
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

.user-info {
    background: var(--background-alt);
    padding: 2rem;
    border-radius: var(--border-radius);
    margin: 2rem 0;
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

.login-required {
    text-align: center;
    padding: 2rem;
}

.message {
    padding: 1rem;
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

// Auto-hide success/error messages after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const messages = document.querySelectorAll('.message');
    messages.forEach(message => {
        setTimeout(() => {
            message.style.opacity = '0';
            setTimeout(() => {
                message.remove();
            }, 300);
        }, 5000);
    });
});
</script>

<?php include 'includes/footer.php'; ?>
