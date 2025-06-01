<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

$tour = isset($_GET['tour']) ? $_GET['tour'] : 'complete';

$tours = [
    'complete' => [
        'title' => 'Complete Museum Tour',
        'description' => 'Experience our entire museum with this comprehensive virtual tour featuring all major galleries and exhibitions.',
        'duration' => '45 minutes',
        'highlights' => ['Main Gallery', 'Modern Art Wing', 'Renaissance Collection', 'Sculpture Garden', 'Photography Gallery'],
        'image' => 'images/tours/complete-tour.jpg'
    ],
    'modern' => [
        'title' => 'Modern Masterpieces Gallery',
        'description' => 'Explore our collection of 20th-century art with detailed commentary on each masterpiece.',
        'duration' => '20 minutes',
        'highlights' => ['Abstract Expressionism', 'Pop Art', 'Minimalism', 'Contemporary Sculptures'],
        'image' => 'images/tours/modern-tour.jpg'
    ],
    'renaissance' => [
        'title' => 'Renaissance Collection',
        'description' => 'Journey through the Renaissance period with our curated collection of paintings and sculptures.',
        'duration' => '25 minutes',
        'highlights' => ['Italian Masters', 'Religious Art', 'Portrait Gallery', 'Sculpture Hall'],
        'image' => 'images/tours/renaissance-tour.jpg'
    ],
    'sculpture-garden' => [
        'title' => 'Sculpture Garden',
        'description' => 'Take a peaceful walk through our outdoor sculpture garden featuring contemporary works.',
        'duration' => '15 minutes',
        'highlights' => ['Contemporary Sculptures', 'Garden Landscapes', 'Interactive Installations'],
        'image' => 'images/tours/garden-tour.jpg'
    ]
];

$current_tour = $tours[$tour] ?? $tours['complete'];
$page_title = $current_tour['title'] . " - Virtual Tour - National Museum of Art & Culture";

include 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <nav class="breadcrumb">
            <a href="index.php">Home</a> > 
            <a href="virtual-tours.php">Virtual Tours</a> > 
            <span><?php echo htmlspecialchars($current_tour['title']); ?></span>
        </nav>
    </div>
</section>

<section class="tour-viewer">
    <div class="container">
        <div class="tour-header">
            <h1><?php echo htmlspecialchars($current_tour['title']); ?></h1>
            <div class="tour-meta">
                <span><i class="fas fa-clock"></i> <?php echo $current_tour['duration']; ?></span>
                <span><i class="fas fa-eye"></i> 360° Experience</span>
                <span><i class="fas fa-headphones"></i> Audio Guide Available</span>
            </div>
        </div>
        
        <div class="tour-player">
            <div class="player-container">
                <div class="tour-placeholder">
                    <i class="fas fa-play-circle"></i>
                    <h3>Virtual Tour Player</h3>
                    <p>Click to start your immersive 360° tour experience</p>
                    <button class="btn btn-primary btn-lg" onclick="startTour()">Start Tour</button>
                </div>
            </div>
            
            <div class="tour-controls" style="display: none;">
                <button class="control-btn" onclick="pauseTour()"><i class="fas fa-pause"></i></button>
                <button class="control-btn" onclick="playTour()"><i class="fas fa-play"></i></button>
                <button class="control-btn" onclick="toggleAudio()"><i class="fas fa-volume-up"></i></button>
                <button class="control-btn" onclick="toggleFullscreen()"><i class="fas fa-expand"></i></button>
                <div class="progress-bar">
                    <div class="progress-fill"></div>
                </div>
            </div>
        </div>
        
        <div class="tour-info">
            <div class="tour-description">
                <h3>About This Tour</h3>
                <p><?php echo htmlspecialchars($current_tour['description']); ?></p>
            </div>
            
            <div class="tour-highlights">
                <h3>Tour Highlights</h3>
                <ul>
                    <?php foreach ($current_tour['highlights'] as $highlight): ?>
                        <li><?php echo htmlspecialchars($highlight); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="other-tours">
    <div class="container">
        <h2>Other Virtual Tours</h2>
        <div class="tours-grid">
            <?php foreach ($tours as $tour_key => $tour_data): ?>
                <?php if ($tour_key !== $tour): ?>
                    <div class="tour-card">
                        <div class="tour-image">
                            <img src="<?php echo $tour_data['image']; ?>" alt="<?php echo htmlspecialchars($tour_data['title']); ?>">
                            <div class="tour-duration"><?php echo $tour_data['duration']; ?></div>
                        </div>
                        <div class="tour-details">
                            <h3><?php echo htmlspecialchars($tour_data['title']); ?></h3>
                            <p><?php echo htmlspecialchars(substr($tour_data['description'], 0, 100)); ?>...</p>
                            <a href="virtual-tour-detail.php?tour=<?php echo $tour_key; ?>" class="btn btn-secondary">Start Tour</a>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<style>
.tour-header {
    text-align: center;
    margin-bottom: 4rem;
}

.tour-meta {
    display: flex;
    justify-content: center;
    gap: 3rem;
    margin-top: 2rem;
    color: var(--text-secondary);
}

.tour-meta span {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.tour-meta i {
    color: var(--primary);
}

.player-container {
    position: relative;
    width: 100%;
    height: 60rem;
    background: #000;
    border-radius: var(--border-radius-lg);
    overflow: hidden;
    margin-bottom: 2rem;
}

.tour-placeholder {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    color: white;
}

.tour-placeholder i {
    font-size: 8rem;
    margin-bottom: 2rem;
    color: var(--primary);
}

.tour-controls {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: var(--surface);
    border-radius: var(--border-radius);
    box-shadow: 0 2px 10px var(--shadow);
}

.control-btn {
    width: 4rem;
    height: 4rem;
    border: none;
    background: var(--primary);
    color: white;
    border-radius: 50%;
    cursor: pointer;
    transition: background-color var(--transition-fast);
}

.control-btn:hover {
    background: var(--primary-hover);
}

.progress-bar {
    flex: 1;
    height: 0.5rem;
    background: var(--border);
    border-radius: 0.25rem;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: var(--primary);
    width: 0%;
    transition: width 0.3s ease;
}

.tour-info {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 4rem;
    margin-top: 4rem;
}

.tour-highlights ul {
    list-style: none;
    padding: 0;
}

.tour-highlights li {
    padding: 1rem;
    background: var(--background-alt);
    margin-bottom: 1rem;
    border-radius: var(--border-radius);
    border-left: 4px solid var(--primary);
}

.tours-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(30rem, 1fr));
    gap: 3rem;
    margin-top: 3rem;
}

@media (max-width: 768px) {
    .tour-meta {
        flex-direction: column;
        gap: 1rem;
    }
    
    .player-container {
        height: 40rem;
    }
    
    .tour-info {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .tour-controls {
        flex-wrap: wrap;
    }
}
</style>

<script>
let tourPlaying = false;
let audioEnabled = true;

function startTour() {
    document.querySelector('.tour-placeholder').style.display = 'none';
    document.querySelector('.tour-controls').style.display = 'flex';
    
    // Simulate tour start
    tourPlaying = true;
    simulateProgress();
    
    // In a real implementation, this would initialize the 360° viewer
    console.log('Starting virtual tour...');
}

function pauseTour() {
    tourPlaying = false;
    console.log('Tour paused');
}

function playTour() {
    tourPlaying = true;
    simulateProgress();
    console.log('Tour resumed');
}

function toggleAudio() {
    audioEnabled = !audioEnabled;
    const audioBtn = document.querySelector('.control-btn:nth-child(3) i');
    audioBtn.className = audioEnabled ? 'fas fa-volume-up' : 'fas fa-volume-mute';
    console.log('Audio toggled:', audioEnabled);
}

function toggleFullscreen() {
    if (document.fullscreenElement) {
        document.exitFullscreen();
    } else {
        document.querySelector('.player-container').requestFullscreen();
    }
}

function simulateProgress() {
    const progressFill = document.querySelector('.progress-fill');
    let progress = 0;
    
    const interval = setInterval(() => {
        if (!tourPlaying) {
            clearInterval(interval);
            return;
        }
        
        progress += 0.5;
        progressFill.style.width = progress + '%';
        
        if (progress >= 100) {
            clearInterval(interval);
            console.log('Tour completed');
        }
    }, 100);
}
</script>

<?php include 'includes/footer.php'; ?>
