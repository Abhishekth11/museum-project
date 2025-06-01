<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

$page_title = "Search Results - National Museum of Art & Culture";

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$type = isset($_GET['type']) ? $_GET['type'] : 'all';
$results = [];
$suggestions = [];

if (!empty($query)) {
    $results = searchContent($query, $type);
    logSearchTerm($query, count($results));
} else {
    $suggestions = getPopularSearchTerms(8);
}

include 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Search Results</h1>
        <?php if (!empty($query)): ?>
            <p>Showing <?php echo count($results); ?> result(s) for: "<strong><?php echo htmlspecialchars($query); ?></strong>"</p>
        <?php else: ?>
            <p>Search our exhibitions, events, and collections</p>
        <?php endif; ?>
    </div>
</section>

<section class="search-results">
    <div class="container">
        <div class="search-form-container">
            <form action="search.php" method="GET" class="search-page-form">
                <div class="search-input-group">
                    <input type="text" name="q" value="<?php echo htmlspecialchars($query); ?>" 
                           placeholder="Search for exhibitions, events, artists..." 
                           aria-label="Search" id="search-input" autocomplete="off">
                    <div class="search-suggestions" id="search-suggestions"></div>
                </div>
                <select name="type" aria-label="Search type">
                    <option value="all" <?php echo $type == 'all' ? 'selected' : ''; ?>>All Content</option>
                    <option value="exhibitions" <?php echo $type == 'exhibitions' ? 'selected' : ''; ?>>Exhibitions</option>
                    <option value="events" <?php echo $type == 'events' ? 'selected' : ''; ?>>Events</option>
                    <option value="collections" <?php echo $type == 'collections' ? 'selected' : ''; ?>>Collections</option>
                </select>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>
        
        <?php if (empty($query)): ?>
            <div class="search-help">
                <h2>Popular Searches</h2>
                <div class="popular-searches">
                    <?php if (!empty($suggestions)): ?>
                        <?php foreach ($suggestions as $suggestion): ?>
                            <a href="search.php?q=<?php echo urlencode($suggestion['search_term']); ?>" class="search-tag">
                                <?php echo htmlspecialchars($suggestion['search_term']); ?>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <a href="search.php?q=modern+art" class="search-tag">Modern Art</a>
                        <a href="search.php?q=renaissance" class="search-tag">Renaissance</a>
                        <a href="search.php?q=sculpture" class="search-tag">Sculpture</a>
                        <a href="search.php?q=photography" class="search-tag">Photography</a>
                        <a href="search.php?q=guided+tours" class="search-tag">Guided Tours</a>
                        <a href="search.php?q=workshops" class="search-tag">Workshops</a>
                    <?php endif; ?>
                </div>
                
                <div class="search-tips">
                    <h3>Search Tips</h3>
                    <ul>
                        <li>Use specific terms like artist names, art movements, or exhibition titles</li>
                        <li>Try searching for event types like "workshops", "tours", or "lectures"</li>
                        <li>Search by time periods like "Renaissance", "Modern", or "Contemporary"</li>
                        <li>Use the filter dropdown to narrow your search to specific content types</li>
                    </ul>
                </div>
            </div>
        <?php elseif (empty($results)): ?>
            <div class="no-results">
                <h2>No results found</h2>
                <p>We couldn't find anything matching "<strong><?php echo htmlspecialchars($query); ?></strong>"</p>
                <div class="search-suggestions-text">
                    <h3>Try these suggestions:</h3>
                    <ul>
                        <li>Check your spelling</li>
                        <li>Use more general terms</li>
                        <li>Try different keywords</li>
                        <li>Browse our <a href="exhibitions.php">current exhibitions</a> or <a href="events.php">upcoming events</a></li>
                    </ul>
                </div>
            </div>
        <?php else: ?>
            <div class="search-filters">
                <div class="results-count">
                    <p><?php echo count($results); ?> result(s) found</p>
                </div>
                <div class="filter-buttons">
                    <a href="search.php?q=<?php echo urlencode($query); ?>&type=all" 
                       class="filter-btn <?php echo $type == 'all' ? 'active' : ''; ?>">All</a>
                    <a href="search.php?q=<?php echo urlencode($query); ?>&type=exhibitions" 
                       class="filter-btn <?php echo $type == 'exhibitions' ? 'active' : ''; ?>">Exhibitions</a>
                    <a href="search.php?q=<?php echo urlencode($query); ?>&type=events" 
                       class="filter-btn <?php echo $type == 'events' ? 'active' : ''; ?>">Events</a>
                    <a href="search.php?q=<?php echo urlencode($query); ?>&type=collections" 
                       class="filter-btn <?php echo $type == 'collections' ? 'active' : ''; ?>">Collections</a>
                </div>
            </div>
            
            <div class="results-grid">
                <?php foreach ($results as $result): ?>
                    <div class="result-card">
                        <div class="result-image">
                            <img src="<?php echo !empty($result['image']) ? 'uploads/' . $result['type'] . 's/' . $result['image'] : 'https://source.unsplash.com/random/300x200/?' . $result['type']; ?>" 
                                 alt="<?php echo htmlspecialchars($result['title']); ?>">
                            <div class="result-type"><?php echo ucfirst($result['type']); ?></div>
                        </div>
                        <div class="result-details">
                            <h3><?php echo htmlspecialchars($result['title']); ?></h3>
                            <?php if (isset($result['artist']) && !empty($result['artist'])): ?>
                                <p class="result-artist">by <?php echo htmlspecialchars($result['artist']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($result['category'])): ?>
                                <p class="result-category"><?php echo htmlspecialchars($result['category']); ?></p>
                            <?php endif; ?>
                            <p class="result-description">
                                <?php echo htmlspecialchars(substr($result['description'], 0, 150)); ?>...
                            </p>
                            <?php if ($result['type'] == 'exhibition'): ?>
                                <a href="exhibition-detail.php?id=<?php echo $result['id']; ?>" class="btn btn-secondary">View Exhibition</a>
                            <?php elseif ($result['type'] == 'event'): ?>
                                <a href="event-detail.php?id=<?php echo $result['id']; ?>" class="btn btn-secondary">View Event</a>
                            <?php elseif ($result['type'] == 'collection'): ?>
                                <a href="collection-detail.php?id=<?php echo $result['id']; ?>" class="btn btn-secondary">View Artwork</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
// Search suggestions functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const suggestionsContainer = document.getElementById('search-suggestions');
    let debounceTimer;

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const query = this.value.trim();
        
        if (query.length < 2) {
            suggestionsContainer.style.display = 'none';
            return;
        }
        
        debounceTimer = setTimeout(() => {
            fetch(`api/search-suggestions.php?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(suggestions => {
                    if (suggestions.length > 0) {
                        suggestionsContainer.innerHTML = suggestions
                            .map(suggestion => `<div class="suggestion-item" data-suggestion="${suggestion}">${suggestion}</div>`)
                            .join('');
                        suggestionsContainer.style.display = 'block';
                    } else {
                        suggestionsContainer.style.display = 'none';
                    }
                })
                .catch(() => {
                    suggestionsContainer.style.display = 'none';
                });
        }, 300);
    });

    // Handle suggestion clicks
    suggestionsContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('suggestion-item')) {
            searchInput.value = e.target.dataset.suggestion;
            suggestionsContainer.style.display = 'none';
        }
    });

    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
            suggestionsContainer.style.display = 'none';
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
