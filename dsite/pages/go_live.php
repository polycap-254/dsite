<?php
$page_title = "Go Live";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Handle starting a live stream
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['start_stream'])) {
    $title = sanitize($_POST['stream_title']);
    $description = sanitize($_POST['stream_description']);
    $stream_key = bin2hex(random_bytes(32));
    
    // Deactivate any existing streams
    mysqli_query($conn, "UPDATE live_streams SET is_active = 0, ended_at = NOW() WHERE user_id = $user_id AND is_active = 1");
    
    $sql = "INSERT INTO live_streams (user_id, stream_key, title, description, is_active, started_at) 
            VALUES ($user_id, '$stream_key', '$title', '$description', 1, NOW())";
    
    if (mysqli_query($conn, $sql)) {
        $stream_id = mysqli_insert_id($conn);
        header("Location: go_live.php?stream=$stream_id");
        exit();
    }
}

// Handle ending stream
if (isset($_GET['end']) && is_numeric($_GET['end'])) {
    $stream_id = (int)$_GET['end'];
    mysqli_query($conn, "UPDATE live_streams SET is_active = 0, ended_at = NOW() WHERE id = $stream_id AND user_id = $user_id");
    header('Location: go_live.php');
    exit();
}

// Check if user has active stream
$active_stream_sql = "SELECT * FROM live_streams WHERE user_id = $user_id AND is_active = 1";
$active_stream_result = mysqli_query($conn, $active_stream_sql);
$active_stream = mysqli_fetch_assoc($active_stream_result);

// Get active live streams
$live_streams_sql = "SELECT ls.*, u.username, u.full_name, u.profile_pic 
                    FROM live_streams ls 
                    JOIN users u ON ls.user_id = u.id 
                    WHERE ls.is_active = 1 
                    ORDER BY ls.viewers_count DESC";
$live_streams_result = mysqli_query($conn, $live_streams_sql);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="golive-container">
    <?php if ($active_stream): ?>
    <!-- Active Stream View -->
    <div class="active-stream">
        <div class="stream-header">
            <div class="stream-info">
                <div class="live-badge">
                    <span class="live-dot"></span> LIVE
                </div>
                <h2><?php echo htmlspecialchars($active_stream['title']); ?></h2>
                <div class="viewer-count">
                    <i class="fas fa-eye"></i> 
                    <span id="viewerCount"><?php echo $active_stream['viewers_count']; ?></span> watching
                </div>
            </div>
            <div class="stream-actions">
                <button class="btn btn-danger" onclick="endStream(<?php echo $active_stream['id']; ?>)">
                    <i class="fas fa-stop"></i> End Stream
                </button>
            </div>
        </div>
        
        <div class="stream-preview">
            <div class="camera-preview">
                <video id="localVideo" autoplay muted playsinline></video>
                <div class="stream-overlay">
                    <div class="stream-controls">
                        <button id="toggleCamera" class="control-btn" title="Toggle Camera">
                            <i class="fas fa-video"></i>
                        </button>
                        <button id="toggleMic" class="control-btn" title="Toggle Microphone">
                            <i class="fas fa-microphone"></i>
                        </button>
                        <button id="shareScreen" class="control-btn" title="Share Screen">
                            <i class="fas fa-desktop"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Live Chat -->
            <div class="stream-chat">
                <div class="stream-chat-header">
                    <h3>Live Chat</h3>
                </div>
                <div class="stream-chat-messages" id="streamChat">
                    <div class="chat-message">
                        <span class="chat-username">System</span>
                        <span class="chat-text">Welcome to the live stream! 🎉</span>
                    </div>
                </div>
                <div class="stream-chat-input">
                    <input type="text" id="streamChatInput" placeholder="Send a message..." 
                           onkeypress="if(event.key==='Enter') sendStreamMessage()">
                    <button onclick="sendStreamMessage()" class="send-btn">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <?php else: ?>
    <!-- Start Stream View -->
    <div class="start-stream-container">
        <div class="stream-setup">
            <div class="setup-header">
                <h1><i class="fas fa-broadcast-tower"></i> Go Live</h1>
                <p>Start a live stream and connect with your followers in real-time</p>
            </div>
            
            <form method="POST" class="stream-form">
                <div class="form-group">
                    <label>Stream Title</label>
                    <input type="text" name="stream_title" class="form-control" 
                           placeholder="Enter your stream title..." required>
                </div>
                
                <div class="form-group">
                    <label>Description (Optional)</label>
                    <textarea name="stream_description" class="form-control" rows="3" 
                              placeholder="What's your stream about?"></textarea>
                </div>
                
                <div class="stream-preview-section">
                    <h3>Camera Preview</h3>
                    <div class="camera-preview-small">
                        <video id="previewVideo" autoplay muted playsinline></video>
                        <div class="preview-overlay">
                            <button class="btn btn-outline" onclick="togglePreviewCamera()">
                                <i class="fas fa-camera"></i> Test Camera
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="stream-tips">
                    <h4><i class="fas fa-lightbulb"></i> Tips for a Great Stream:</h4>
                    <ul>
                        <li>Ensure good lighting</li>
                        <li>Check your internet connection</li>
                        <li>Engage with your viewers in chat</li>
                        <li>Be yourself and have fun!</li>
                    </ul>
                </div>
                
                <button type="submit" name="start_stream" class="btn btn-primary btn-lg btn-block">
                    <i class="fas fa-play-circle"></i> Start Streaming
                </button>
            </form>
        </div>
        
        <!-- Live Streams Section -->
        <div class="live-streams-section">
            <h2><span class="live-dot"></span> Live Now</h2>
            
            <?php if (mysqli_num_rows($live_streams_result) > 0): ?>
            <div class="live-streams-grid">
                <?php while ($stream = mysqli_fetch_assoc($live_streams_result)): ?>
                <div class="live-stream-card" onclick="window.location.href='?watch=<?php echo $stream['id']; ?>'">
                    <div class="stream-thumbnail">
                        <img src="../<?php echo $stream['profile_pic']; ?>" alt="Streamer">
                        <div class="live-label">LIVE</div>
                        <div class="viewers-label">
                            <i class="fas fa-eye"></i> <?php echo $stream['viewers_count']; ?>
                        </div>
                    </div>
                    <div class="stream-card-info">
                        <img src="../<?php echo $stream['profile_pic']; ?>" alt="Avatar" class="streamer-avatar">
                        <div class="stream-details">
                            <h4><?php echo htmlspecialchars($stream['title']); ?></h4>
                            <p class="streamer-name"><?php echo $stream['full_name'] ?: $stream['username']; ?></p>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-video-slash"></i>
                <h3>No Live Streams</h3>
                <p>No one is live right now. Be the first to go live!</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.golive-container {
    max-width: 1400px;
    margin: 0 auto;
}

/* Active Stream */
.active-stream {
    height: calc(100vh - 70px - 60px);
    display: flex;
    flex-direction: column;
}

.stream-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    background: white;
    border-radius: 15px 15px 0 0;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.live-badge {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #ff1744;
    color: white;
    padding: 5px 15px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9em;
    margin-bottom: 10px;
    width: fit-content;
}

.live-dot {
    width: 10px;
    height: 10px;
    background: white;
    border-radius: 50%;
    animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.3; }
}

.viewer-count {
    color: #666;
    font-size: 0.9em;
    margin-top: 5px;
}

.stream-preview {
    flex: 1;
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 10px;
    background: #000;
}

.camera-preview {
    position: relative;
    background: #1a1a1a;
}

.camera-preview video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.stream-overlay {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
}

.stream-controls {
    display: flex;
    gap: 10px;
    background: rgba(0,0,0,0.6);
    padding: 10px 20px;
    border-radius: 30px;
}

.control-btn {
    background: white;
    border: none;
    width: 45px;
    height: 45px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 1.2em;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.control-btn:hover {
    background: #e91e63;
    color: white;
}

/* Stream Chat */
.stream-chat {
    background: white;
    display: flex;
    flex-direction: column;
    border-left: 1px solid #e0e0e0;
}

.stream-chat-header {
    padding: 15px;
    border-bottom: 1px solid #e0e0e0;
}

.stream-chat-messages {
    flex: 1;
    padding: 15px;
    overflow-y: auto;
}

.chat-message {
    margin-bottom: 10px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.chat-username {
    font-weight: 600;
    color: #e91e63;
}

.chat-text {
    color: #333;
}

.stream-chat-input {
    padding: 15px;
    border-top: 1px solid #e0e0e0;
    display: flex;
    gap: 10px;
}

.stream-chat-input input {
    flex: 1;
    padding: 10px 15px;
    border: 1px solid #e0e0e0;
    border-radius: 20px;
    outline: none;
}

/* Start Stream */
.start-stream-container {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 20px;
}

.stream-setup {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.setup-header {
    text-align: center;
    margin-bottom: 30px;
}

.setup-header h1 {
    color: #333;
    margin-bottom: 10px;
}

.setup-header i {
    color: #ff1744;
}

.camera-preview-small {
    position: relative;
    width: 100%;
    height: 250px;
    background: #1a1a1a;
    border-radius: 10px;
    overflow: hidden;
}

.camera-preview-small video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.preview-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.stream-tips {
    background: #f9f9f9;
    padding: 20px;
    border-radius: 10px;
    margin: 20px 0;
}

.stream-tips ul {
    margin-top: 10px;
    padding-left: 20px;
}

.stream-tips li {
    margin: 8px 0;
    color: #666;
}

.btn-lg {
    padding: 15px;
    font-size: 1.1em;
}

/* Live Streams Section */
.live-streams-section {
    background: white;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.live-streams-section h2 {
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.live-streams-grid {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.live-stream-card {
    border-radius: 10px;
    overflow: hidden;
    cursor: pointer;
    transition: transform 0.3s;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.live-stream-card:hover {
    transform: translateY(-3px);
}

.stream-thumbnail {
    position: relative;
    height: 150px;
    background: #1a1a1a;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stream-thumbnail img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    opacity: 0.8;
}

.live-label {
    position: absolute;
    top: 10px;
    left: 10px;
    background: #ff1744;
    color: white;
    padding: 3px 10px;
    border-radius: 5px;
    font-size: 0.8em;
    font-weight: 600;
}

.viewers-label {
    position: absolute;
    bottom: 10px;
    left: 10px;
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 3px 10px;
    border-radius: 5px;
    font-size: 0.8em;
}

.stream-card-info {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    background: white;
}

.streamer-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
}

.stream-details h4 {
    font-size: 0.9em;
    color: #333;
    margin-bottom: 3px;
}

.streamer-name {
    font-size: 0.85em;
    color: #666;
}

@media (max-width: 1024px) {
    .start-stream-container {
        grid-template-columns: 1fr;
    }
    
    .stream-preview {
        grid-template-columns: 1fr;
    }
    
    .stream-chat {
        display: none;
    }
}
</style>

<script>
let localStream;
let isCameraOn = true;
let isMicOn = true;

// Initialize camera preview
async function initCamera() {
    try {
        localStream = await navigator.mediaDevices.getUserMedia({ 
            video: true, 
            audio: true 
        });
        
        const previewVideo = document.getElementById('previewVideo');
        const localVideo = document.getElementById('localVideo');
        
        if (previewVideo) {
            previewVideo.srcObject = localStream;
        }
        
        if (localVideo) {
            localVideo.srcObject = localStream;
        }
    } catch (err) {
        console.error('Error accessing camera:', err);
        alert('Unable to access camera. Please make sure you have granted camera permissions.');
    }
}

// Toggle preview camera
function togglePreviewCamera() {
    if (localStream) {
        localStream.getTracks().forEach(track => track.stop());
        localStream = null;
        document.getElementById('previewVideo').srcObject = null;
    } else {
        initCamera();
    }
}

// Toggle camera during stream
document.getElementById('toggleCamera')?.addEventListener('click', function() {
    if (localStream) {
        const videoTrack = localStream.getVideoTracks()[0];
        if (videoTrack) {
            videoTrack.enabled = !videoTrack.enabled;
            isCameraOn = videoTrack.enabled;
            this.innerHTML = isCameraOn ? '<i class="fas fa-video"></i>' : '<i class="fas fa-video-slash"></i>';
            this.style.background = isCameraOn ? 'white' : '#ff1744';
            this.style.color = isCameraOn ? '#333' : 'white';
        }
    }
});

// Toggle microphone during stream
document.getElementById('toggleMic')?.addEventListener('click', function() {
    if (localStream) {
        const audioTrack = localStream.getAudioTracks()[0];
        if (audioTrack) {
            audioTrack.enabled = !audioTrack.enabled;
            isMicOn = audioTrack.enabled;
            this.innerHTML = isMicOn ? '<i class="fas fa-microphone"></i>' : '<i class="fas fa-microphone-slash"></i>';
            this.style.background = isMicOn ? 'white' : '#ff1744';
            this.style.color = isMicOn ? '#333' : 'white';
        }
    }
});

// Screen sharing
document.getElementById('shareScreen')?.addEventListener('click', async function() {
    try {
        const screenStream = await navigator.mediaDevices.getDisplayMedia({ video: true });
        const localVideo = document.getElementById('localVideo');
        localVideo.srcObject = screenStream;
        
        screenStream.getVideoTracks()[0].addEventListener('ended', () => {
            localVideo.srcObject = localStream;
        });
    } catch (err) {
        console.error('Error sharing screen:', err);
    }
});

// Stream chat
function sendStreamMessage() {
    const input = document.getElementById('streamChatInput');
    const message = input.value.trim();
    
    if (message) {
        const chatMessages = document.getElementById('streamChat');
        const messageDiv = document.createElement('div');
        messageDiv.className = 'chat-message';
        messageDiv.innerHTML = `
            <span class="chat-username">You</span>
            <span class="chat-text">${message}</span>
        `;
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        input.value = '';
        
        // In a real app, you would send this to your server via WebSocket
    }
}

// End stream
function endStream(streamId) {
    if (confirm('Are you sure you want to end your stream?')) {
        if (localStream) {
            localStream.getTracks().forEach(track => track.stop());
        }
        window.location.href = 'go_live.php?end=' + streamId;
    }
}

// Initialize on load
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!$active_stream): ?>
    initCamera();
    <?php else: ?>
    initCamera();
    <?php endif; ?>
});

// Update viewer count (simulated)
setInterval(function() {
    const viewerCountEl = document.getElementById('viewerCount');
    if (viewerCountEl) {
        const currentCount = parseInt(viewerCountEl.textContent);
        const change = Math.floor(Math.random() * 3) - 1; // Random -1, 0, or 1
        const newCount = Math.max(0, currentCount + change);
        viewerCountEl.textContent = newCount;
    }
}, 5000);
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>