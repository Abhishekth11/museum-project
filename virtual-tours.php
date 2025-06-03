<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

$page_title = "Virtual Tours - National Museum of Art & Culture";

include 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Virtual Tours</h1>
        <p>Explore our museum from anywhere in the world</p>
    </div>
</section>

<section class="virtual-tours">
    <div class="container">
        <div class="tours-grid">
            <div class="tour-card featured">
                <div class="tour-image">
                    <img src="https://source.unsplash.com/random/800x600/?museum-gallery" alt="Main Gallery Virtual Tour">
                    <div class="tour-duration">45 minutes</div>
                    <div class="tour-overlay">
                        <button class="play-btn" aria-label="Start virtual tour">
                            <i class="fas fa-play"></i>
                        </button>
                    </div>
                </div>
                <div class="tour-details">
                    <h3>Complete Museum Tour</h3>
                    <p>Experience our entire museum with this comprehensive virtual tour featuring all major galleries and exhibitions.</p>
                    <div class="tour-features">
                        <span><i class="fas fa-eye"></i> 360° Views</span>
                        <span><i class="fas fa-volume-up"></i> Audio Guide</span>
                        <span><i class="fas fa-info-circle"></i> Interactive Info</span>
                    </div>
                    <a href="virtual-tour-detail.php?tour=complete" class="btn btn-primary">Start Tour</a>
                </div>
            </div>
            
            <div class="tour-card">
                <div class="tour-image">
                    <img src="https://source.unsplash.com/random/600x400/?modern-art" alt="Modern Art Gallery">
                    <div class="tour-duration">20 minutes</div>
                    <div class="tour-overlay">
                        <button class="play-btn" aria-label="Start virtual tour">
                            <i class="fas fa-play"></i>
                        </button>
                    </div>
                </div>
                <div class="tour-details">
                    <h3>Modern Masterpieces Gallery</h3>
                    <p>Explore our collection of 20th-century art with detailed commentary on each masterpiece.</p>
                    <a href="virtual-tour-detail.php?tour=modern" class="btn btn-secondary">Start Tour</a>
                </div>
            </div>
            
            <div class="tour-card">
                <div class="tour-image">
                    <img src="https://source.unsplash.com/random/600x400/?renaissance-art" alt="Renaissance Gallery">
                    <div class="tour-duration">25 minutes</div>
                    <div class="tour-overlay">
                        <button class="play-btn" aria-label="Start virtual tour">
                            <i class="fas fa-play"></i>
                        </button>
                    </div>
                </div>
                <div class="tour-details">
                    <h3>Renaissance Collection</h3>
                    <p>Journey through the Renaissance period with our curated collection of paintings and sculptures.</p>
                    <a href="virtual-tour-detail.php?tour=renaissance" class="btn btn-secondary">Start Tour</a>
                </div>
            </div>
            
            <div class="tour-card">
                <div class="tour-image">
                    <img src="https://source.unsplash.com/random/600x400/?sculpture-garden" alt="Sculpture Garden">
                    <div class="tour-duration">15 minutes</div>
                    <div class="tour-overlay">
                        <button class="play-btn" aria-label="Start virtual tour">
                            <i class="fas fa-play"></i>
                        </button>
                    </div>
                </div>
                <div class="tour-details">
                    <h3>Sculpture Garden</h3>
                    <p>Take a peaceful walk through our outdoor sculpture garden featuring contemporary works.</p>
                    <a href="virtual-tour-detail.php?tour=sculpture-garden" class="btn btn-secondary">Start Tour</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="tour-features">
    <div class="container">
        <h2>Virtual Tour Features</h2>
        <div class="features-grid">
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-vr-cardboard"></i>
                </div>
                <h3>360° Experience</h3>
                <p>Immersive 360-degree views of every gallery and exhibition space.</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-headphones"></i>
                </div>
                <h3>Expert Commentary</h3>
                <p>Professional audio guides with insights from our curators and art historians.</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-mouse-pointer"></i>
                </div>
                <h3>Interactive Elements</h3>
                <p>Click on artworks for detailed information, artist biographies, and historical context.</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h3>Multi-Device Access</h3>
                <p>Enjoy tours on desktop, tablet, or mobile device with optimized viewing experience.</p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
