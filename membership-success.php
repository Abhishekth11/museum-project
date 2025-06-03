<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

$page_title = "Membership Confirmation - National Museum of Art & Culture";

// Check if user has valid membership success session
if (!isset($_SESSION['membership_success']) || !isset($_SESSION['membership_details'])) {
    header('Location: membership.php');
    exit;
}

$membership_details = $_SESSION['membership_details'];
$user_data = getUserById($_SESSION['user_id']);

// Clear the session data
unset($_SESSION['membership_success'], $_SESSION['membership_details']);

include 'includes/header.php';
?>

<section class="membership-success-hero">
    <div class="container">
        <div class="success-content">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>🎉 Congratulations!</h1>
            <h2>Your Membership is Now Active</h2>
            <p>Welcome to the National Museum of Art & Culture family!</p>
        </div>
    </div>
</section>

<section class="membership-confirmation">
    <div class="container">
        <div class="confirmation-grid">
            <div class="membership-card-display">
                <div class="digital-membership-card">
                    <div class="card-header">
                        <h3>Digital Membership Card</h3>
                        <div class="museum-logo">NMAC</div>
                    </div>
                    <div class="card-body">
                        <div class="member-name"><?php echo htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']); ?></div>
                        <div class="member-id"><?php echo htmlspecialchars($membership_details['member_id']); ?></div>
                        <div class="membership-type"><?php echo ucfirst($membership_details['membership_type']); ?> Member</div>
                        <div class="validity">Valid: <?php echo formatDate($membership_details['start_date']); ?> - <?php echo formatDate($membership_details['end_date']); ?></div>
                    </div>
                    <div class="card-footer">
                        <div class="qr-placeholder">
                            <i class="fas fa-qrcode"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="confirmation-details">
                <h3>📋 Membership Details</h3>
                <div class="details-list">
                    <div class="detail-item">
                        <span class="label">Member Name:</span>
                        <span class="value"><?php echo htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Email:</span>
                        <span class="value"><?php echo htmlspecialchars($user_data['email']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Member ID:</span>
                        <span class="value"><?php echo htmlspecialchars($membership_details['member_id']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Membership Type:</span>
                        <span class="value"><?php echo ucfirst($membership_details['membership_type']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Start Date:</span>
                        <span class="value"><?php echo formatDate($membership_details['start_date']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Valid Until:</span>
                        <span class="value"><?php echo formatDate($membership_details['end_date']); ?></span>
                    </div>
                    <div class="detail-item highlight">
                        <span class="label">Amount Paid:</span>
                        <span class="value">$<?php echo number_format($membership_details['price_paid'], 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="next-steps">
            <h3>🚀 What's Next?</h3>
            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h4>Check Your Email</h4>
                    <p>A detailed confirmation email with your digital membership card has been sent to your email address.</p>
                </div>
                
                <div class="step-card">
                    <div class="step-icon">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <h4>Physical Card</h4>
                    <p>Your physical membership card will be mailed to you within 7-10 business days.</p>
                </div>
                
                <div class="step-card">
                    <div class="step-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h4>Immediate Access</h4>
                    <p>Show this page or your email confirmation for immediate member access to the museum.</p>
                </div>
                
                <div class="step-card">
                    <div class="step-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h4>Book Events</h4>
                    <p>Start booking member-exclusive events and workshops with priority registration.</p>
                </div>
            </div>
        </div>
        
        <div class="member-benefits">
            <h3>🎁 Your Exclusive Benefits</h3>
            <div class="benefits-showcase">
                <?php 
                $benefits = getMembershipBenefits($membership_details['membership_type']);
                foreach ($benefits as $benefit): 
                ?>
                <div class="benefit-item">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo htmlspecialchars($benefit); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="action-buttons">
            <a href="exhibitions.php" class="btn btn-primary">
                <i class="fas fa-palette"></i> View Current Exhibitions
            </a>
            <a href="events.php" class="btn btn-secondary">
                <i class="fas fa-calendar"></i> Browse Member Events
            </a>
            <a href="virtual-tours.php" class="btn btn-outline">
                <i class="fas fa-vr-cardboard"></i> Take Virtual Tours
            </a>
        </div>
        
        <div class="contact-info">
            <h4>Questions about your membership?</h4>
            <p>Contact our membership team:</p>
            <div class="contact-methods">
                <div class="contact-method">
                    <i class="fas fa-envelope"></i>
                    <a href="mailto:membership@nmac.org">membership@nmac.org</a>
                </div>
                <div class="contact-method">
                    <i class="fas fa-phone"></i>
                    <a href="tel:+11234567890">(123) 456-7890</a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.membership-success-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 8rem 0 6rem;
    text-align: center;
}

.success-content h1 {
    font-size: 4rem;
    margin-bottom: 1rem;
    font-weight: 300;
}

.success-content h2 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    font-weight: 400;
}

.success-icon {
    font-size: 8rem;
    color: #27ae60;
    margin-bottom: 2rem;
}

.membership-confirmation {
    padding: 6rem 0;
    background: var(--background);
}

.confirmation-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    margin-bottom: 6rem;
}

.digital-membership-card {
    background: linear-gradient(135deg, #2c3e50, #3498db);
    color: white;
    border-radius: 15px;
    padding: 3rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    transform: perspective(1000px) rotateY(-5deg);
    transition: transform 0.3s ease;
}

.digital-membership-card:hover {
    transform: perspective(1000px) rotateY(0deg);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    padding-bottom: 1rem;
}

.museum-logo {
    font-size: 2rem;
    font-weight: bold;
    background: rgba(255, 255, 255, 0.2);
    padding: 0.5rem 1rem;
    border-radius: 5px;
}

.member-name {
    font-size: 2.2rem;
    font-weight: bold;
    margin-bottom: 1rem;
}

.member-id {
    font-size: 1.8rem;
    font-family: 'Courier New', monospace;
    letter-spacing: 2px;
    margin-bottom: 1rem;
    color: #f39c12;
}

.membership-type {
    font-size: 1.4rem;
    margin-bottom: 1rem;
    opacity: 0.9;
}

.validity {
    font-size: 1.2rem;
    opacity: 0.8;
}

.qr-placeholder {
    text-align: center;
    margin-top: 2rem;
    font-size: 3rem;
    opacity: 0.6;
}

.confirmation-details h3 {
    color: var(--primary);
    margin-bottom: 2rem;
    font-size: 2.4rem;
}

.details-list {
    background: var(--surface);
    border-radius: var(--border-radius);
    padding: 2rem;
    box-shadow: var(--shadow-sm);
}

.detail-item {
    display: flex;
    justify-content: space-between;
    padding: 1rem 0;
    border-bottom: 1px solid var(--divider);
}

.detail-item:last-child {
    border-bottom: none;
}

.detail-item.highlight {
    font-weight: bold;
    font-size: 1.2rem;
    color: var(--primary);
}

.label {
    font-weight: 500;
    color: var(--text-secondary);
}

.value {
    font-weight: 600;
    color: var(--text-primary);
}

.next-steps {
    margin: 6rem 0;
}

.next-steps h3 {
    text-align: center;
    font-size: 2.8rem;
    margin-bottom: 3rem;
    color: var(--primary);
}

.steps-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
}

.step-card {
    background: var(--surface);
    padding: 2.5rem;
    border-radius: var(--border-radius-lg);
    text-align: center;
    box-shadow: var(--shadow-sm);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.step-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-md);
}

.step-icon {
    font-size: 3rem;
    color: var(--primary);
    margin-bottom: 1.5rem;
}

.step-card h4 {
    font-size: 1.6rem;
    margin-bottom: 1rem;
    color: var(--text-primary);
}

.member-benefits {
    background: var(--surface);
    padding: 4rem;
    border-radius: var(--border-radius-lg);
    margin: 4rem 0;
}

.member-benefits h3 {
    text-align: center;
    font-size: 2.8rem;
    margin-bottom: 3rem;
    color: var(--primary);
}

.benefits-showcase {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.benefit-item {
    display: flex;
    align-items: center;
    padding: 1rem;
    background: var(--background);
    border-radius: var(--border-radius);
    border-left: 4px solid var(--primary);
}

.benefit-item i {
    color: #27ae60;
    margin-right: 1rem;
    font-size: 1.2rem;
}

.action-buttons {
    text-align: center;
    margin: 4rem 0;
}

.action-buttons .btn {
    margin: 1rem;
    padding: 1.5rem 3rem;
    font-size: 1.4rem;
}

.contact-info {
    text-align: center;
    background: var(--background-alt);
    padding: 3rem;
    border-radius: var(--border-radius-lg);
    margin-top: 4rem;
}

.contact-methods {
    display: flex;
    justify-content: center;
    gap: 3rem;
    margin-top: 2rem;
}

.contact-method {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.contact-method i {
    color: var(--primary);
    font-size: 1.2rem;
}

.contact-method a {
    color: var(--primary);
    text-decoration: none;
    font-weight: 500;
}

@media (max-width: 768px) {
    .confirmation-grid {
        grid-template-columns: 1fr;
        gap: 3rem;
    }
    
    .digital-membership-card {
        transform: none;
        padding: 2rem;
    }
    
    .success-content h1 {
        font-size: 3rem;
    }
    
    .success-content h2 {
        font-size: 2rem;
    }
    
    .contact-methods {
        flex-direction: column;
        gap: 1rem;
    }
    
    .action-buttons .btn {
        display: block;
        margin: 1rem 0;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
