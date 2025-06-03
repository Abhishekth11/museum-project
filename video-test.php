<?php
$page_title = "Video Test Page";
include 'includes/header.php';
?>

<div class="container" style="padding: 50px 0;">
    <h1>Video Test Page</h1>
    <p>This page tests direct video playback without any custom controls or JavaScript.</p>
    
    <div style="margin: 30px 0;">
        <h2>Video 1 - Complete Museum Tour</h2>
        <video width="100%" height="auto" controls>
            <source src="videos/video1.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>
    
    <div style="margin: 30px 0;">
        <h2>Video 2 - Modern Masterpieces Gallery</h2>
        <video width="100%" height="auto" controls>
            <source src="videos/video2.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>
    
    <div style="margin: 30px 0;">
        <h2>Video 3 - Renaissance Collection</h2>
        <video width="100%" height="auto" controls>
            <source src="videos/video3.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>
    
    <div style="margin: 30px 0;">
        <h2>Video 4 - Sculpture Garden</h2>
        <video width="100%" height="auto" controls>
            <source src="videos/video4.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>
    
    <div style="margin: 30px 0; padding: 20px; background: #f5f5f5; border-radius: 5px;">
        <h3>Browser Information</h3>
        <pre id="browser-info" style="background: #eee; padding: 15px; overflow-x: auto;"></pre>
        
        <h3>Video Support</h3>
        <pre id="video-support" style="background: #eee; padding: 15px; overflow-x: auto;"></pre>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Display browser information
    const browserInfo = document.getElementById('browser-info');
    browserInfo.textContent = `User Agent: ${navigator.userAgent}
Platform: ${navigator.platform}
Vendor: ${navigator.vendor}
Cookies Enabled: ${navigator.cookieEnabled}
Language: ${navigator.language}`;
    
    // Check video support
    const videoSupport = document.getElementById('video-support');
    const videoElement = document.createElement('video');
    
    videoSupport.textContent = `Video Element Support: ${'video' in document.createElement ? 'Yes' : 'No'}
MP4 Support: ${videoElement.canPlayType('video/mp4') || 'No'}
WebM Support: ${videoElement.canPlayType('video/webm') || 'No'}
Ogg Support: ${videoElement.canPlayType('video/ogg') || 'No'}`;
    
    // Add event listeners to all videos
    const videos = document.querySelectorAll('video');
    videos.forEach((video, index) => {
        const videoNumber = index + 1;
        
        video.addEventListener('loadeddata', function() {
            console.log(`Video ${videoNumber} loaded successfully`);
        });
        
        video.addEventListener('error', function(e) {
            console.error(`Error loading Video ${videoNumber}:`, video.error);
            const errorDiv = document.createElement('div');
            errorDiv.style.color = 'red';
            errorDiv.style.padding = '10px';
            errorDiv.style.marginTop = '10px';
            errorDiv.style.background = '#ffeeee';
            errorDiv.style.border = '1px solid #ffcccc';
            errorDiv.style.borderRadius = '5px';
            
            let errorMessage = 'Unknown error';
            if (video.error) {
                switch (video.error.code) {
                    case 1: errorMessage = 'Video loading aborted'; break;
                    case 2: errorMessage = 'Network error'; break;
                    case 3: errorMessage = 'Decoding error'; break;
                    case 4: errorMessage = 'Video not supported'; break;
                }
                errorMessage += ` (Error code: ${video.error.code})`;
            }
            
            errorDiv.textContent = `Error: ${errorMessage}`;
            video.parentNode.appendChild(errorDiv);
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
