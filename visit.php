<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

$page_title = "Plan Your Visit - National Museum of Art & Culture";

include 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Plan Your Visit</h1>
        <p>Everything you need to know for your museum experience</p>
    </div>
</section>

<section class="visit-info">
    <div class="container">
        <div class="visit-grid">
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <h3>Hours</h3>
                <div class="hours-list">
                    <div class="hours-item">
                        <span>Tuesday - Sunday</span>
                        <span>10:00 AM - 5:00 PM</span>
                    </div>
                    <div class="hours-item">
                        <span>Thursday</span>
                        <span>10:00 AM - 8:00 PM</span>
                    </div>
                    <div class="hours-item closed">
                        <span>Monday</span>
                        <span>Closed</span>
                    </div>
                </div>
                <p class="note">Last admission 30 minutes before closing</p>
            </div>
            
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <h3>Admission</h3>
                <div class="pricing-list">
                    <div class="pricing-item">
                        <span>Adults</span>
                        <span>$15</span>
                    </div>
                    <div class="pricing-item">
                        <span>Seniors (65+)</span>
                        <span>$10</span>
                    </div>
                    <div class="pricing-item">
                        <span>Students</span>
                        <span>$10</span>
                    </div>
                    <div class="pricing-item">
                        <span>Children (under 12)</span>
                        <span>Free</span>
                    </div>
                    <div class="pricing-item">
                        <span>Members</span>
                        <span>Free</span>
                    </div>
                </div>
                <a href="membership.php" class="btn btn-secondary">Become a Member</a>
            </div>
            
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <h3>Location</h3>
                <div class="location-info">
                    <p>123 Museum Street<br>City, State 12345</p>
                    <p><strong>Phone:</strong> (555) 123-4567</p>
                    <p><strong>Email:</strong> info@nmac.org</p>
                </div>
                <a href="https://maps.google.com" target="_blank" class="btn btn-secondary">Get Directions</a>
            </div>
        </div>
    </div>
</section>

<section class="accessibility">
    <div class="container">
        <h2>Accessibility</h2>
        <div class="accessibility-grid">
            <div class="accessibility-item">
                <i class="fas fa-wheelchair"></i>
                <h3>Wheelchair Access</h3>
                <p>The museum is fully wheelchair accessible with ramps, elevators, and accessible restrooms throughout.</p>
            </div>
            <div class="accessibility-item">
                <i class="fas fa-eye"></i>
                <h3>Visual Accessibility</h3>
                <p>Large print guides, audio descriptions, and tactile tours are available for visitors with visual impairments.</p>
            </div>
            <div class="accessibility-item">
                <i class="fas fa-deaf"></i>
                <h3>Hearing Accessibility</h3>
                <p>ASL interpreters can be arranged for tours and programs with advance notice.</p>
            </div>
            <div class="accessibility-item">
                <i class="fas fa-parking"></i>
                <h3>Parking</h3>
                <p>Accessible parking spaces are available in our parking garage with direct museum access.</p>
            </div>
        </div>
    </div>
</section>

<section class="amenities">
    <div class="container">
        <h2>Museum Amenities</h2>
        <div class="amenities-grid">
            <div class="amenity-item">
                <i class="fas fa-utensils"></i>
                <h3>Museum Café</h3>
                <p>Enjoy light meals, coffee, and snacks in our café overlooking the sculpture garden.</p>
                <p><strong>Hours:</strong> 10:00 AM - 4:00 PM</p>
            </div>
            <div class="amenity-item">
                <i class="fas fa-shopping-bag"></i>
                <h3>Museum Shop</h3>
                <p>Browse art books, unique gifts, and exhibition catalogs in our museum shop.</p>
                <p><strong>Hours:</strong> 10:00 AM - 5:00 PM</p>
            </div>
            <div class="amenity-item">
                <i class="fas fa-coat-hanger"></i>
                <h3>Coat Check</h3>
                <p>Complimentary coat check service available at the main entrance.</p>
            </div>
            <div class="amenity-item">
                <i class="fas fa-wifi"></i>
                <h3>Free WiFi</h3>
                <p>Stay connected with complimentary WiFi throughout the museum.</p>
                <p><strong>Network:</strong> NMAC_Guest</p>
            </div>
        </div>
    </div>
</section>

<section class="group-visits">
    <div class="container">
        <h2>Group Visits</h2>
        <div class="group-info">
            <div class="group-details">
                <h3>School Groups</h3>
                <p>We offer special educational programs for school groups of all ages. Our guided tours are aligned with curriculum standards and include hands-on activities.</p>
                <ul>
                    <li>Free admission for school groups (advance booking required)</li>
                    <li>Educational materials provided</li>
                    <li>Interactive workshops available</li>
                    <li>Lunch facilities for groups</li>
                </ul>
                <a href="contact.php" class="btn btn-primary">Book School Visit</a>
            </div>
            <div class="group-details">
                <h3>Adult Groups</h3>
                <p>Perfect for corporate outings, senior groups, or special interest organizations. We can customize tours based on your group's interests.</p>
                <ul>
                    <li>Discounted group rates (10+ people)</li>
                    <li>Private guided tours available</li>
                    <li>Special exhibition access</li>
                    <li>Group dining options</li>
                </ul>
                <a href="contact.php" class="btn btn-primary">Plan Group Visit</a>
            </div>
        </div>
    </div>
</section>

<section class="visit-tips">
    <div class="container">
        <h2>Visitor Tips</h2>
        <div class="tips-grid">
            <div class="tip-item">
                <h3>Best Times to Visit</h3>
                <p>For a quieter experience, visit on weekday mornings or Thursday evenings. Weekends tend to be busier, especially during special exhibitions.</p>
            </div>
            <div class="tip-item">
                <h3>What to Bring</h3>
                <p>Comfortable walking shoes are recommended. Photography is allowed in most areas (no flash). Bags may be subject to security screening.</p>
            </div>
            <div class="tip-item">
                <h3>Duration</h3>
                <p>Plan 2-3 hours for a comprehensive visit. Audio guides are available for self-guided tours, or join one of our daily guided tours.</p>
            </div>
            <div class="tip-item">
                <h3>Special Events</h3>
                <p>Check our events calendar for special exhibitions, artist talks, and family programs that may enhance your visit.</p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
