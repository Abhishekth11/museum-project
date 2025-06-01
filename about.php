<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

$page_title = "About Us - National Museum of Art & Culture";

include 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>About NMAC</h1>
        <p>Discover our mission, history, and commitment to art and culture</p>
    </div>
</section>

<section class="about-intro">
    <div class="container">
        <div class="about-content">
            <div class="about-text">
                <h2>Our Mission</h2>
                <p>The National Museum of Art & Culture is dedicated to collecting, preserving, and presenting the finest examples of art and cultural artifacts from around the world. We strive to inspire, educate, and engage our community through innovative exhibitions, educational programs, and cultural events.</p>
                <p>Since our founding in 1952, we have been committed to making art accessible to all, fostering creativity, and building bridges between cultures through the universal language of art.</p>
            </div>
            <div class="about-image">
                <img src="https://source.unsplash.com/random/600x800/?museum-building" alt="Museum building exterior">
            </div>
        </div>
    </div>
</section>

<section class="history">
    <div class="container">
        <h2>Our History</h2>
        <div class="timeline">
            <div class="timeline-item">
                <div class="timeline-year">1952</div>
                <div class="timeline-content">
                    <h3>Foundation</h3>
                    <p>The National Museum of Art & Culture was established with a generous donation from philanthropist Margaret Harrison.</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-year">1967</div>
                <div class="timeline-content">
                    <h3>First Major Expansion</h3>
                    <p>The museum doubled in size with the addition of the Modern Art Wing, designed by renowned architect James Mitchell.</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-year">1985</div>
                <div class="timeline-content">
                    <h3>Digital Innovation</h3>
                    <p>NMAC became one of the first museums to implement digital cataloging and interactive displays.</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-year">2010</div>
                <div class="timeline-content">
                    <h3>Sustainability Initiative</h3>
                    <p>The museum achieved LEED Gold certification through comprehensive green building renovations.</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-year">2020</div>
                <div class="timeline-content">
                    <h3>Virtual Expansion</h3>
                    <p>Launch of comprehensive virtual tour program, making our collections accessible worldwide.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="team">
    <div class="container">
        <h2>Leadership Team</h2>
        <div class="team-grid">
            <div class="team-member">
                <div class="member-image">
                    <img src="https://source.unsplash.com/random/300x400/?professional-woman" alt="Dr. Sarah Chen">
                </div>
                <div class="member-info">
                    <h3>Dr. Sarah Chen</h3>
                    <p class="member-title">Director & Chief Curator</p>
                    <p>Dr. Chen brings over 20 years of experience in museum leadership and has curated exhibitions for major institutions worldwide.</p>
                </div>
            </div>
            <div class="team-member">
                <div class="member-image">
                    <img src="https://source.unsplash.com/random/300x400/?professional-man" alt="Michael Rodriguez">
                </div>
                <div class="member-info">
                    <h3>Michael Rodriguez</h3>
                    <p class="member-title">Deputy Director</p>
                    <p>Michael oversees daily operations and strategic planning, with expertise in museum administration and public programming.</p>
                </div>
            </div>
            <div class="team-member">
                <div class="member-image">
                    <img src="https://source.unsplash.com/random/300x400/?professional-woman-2" alt="Dr. Emily Watson">
                </div>
                <div class="member-info">
                    <h3>Dr. Emily Watson</h3>
                    <p class="member-title">Chief Conservator</p>
                    <p>Dr. Watson leads our conservation efforts, ensuring the preservation of our collection for future generations.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="stats">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number">15,000+</div>
                <div class="stat-label">Artworks in Collection</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">500,000+</div>
                <div class="stat-label">Annual Visitors</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">50+</div>
                <div class="stat-label">Exhibitions per Year</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">25,000+</div>
                <div class="stat-label">Students Reached</div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
