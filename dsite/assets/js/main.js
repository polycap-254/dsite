// Mobile Menu Toggle
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const mainNav = document.querySelector('.main-nav');
    
    if (mobileMenuToggle && mainNav) {
        mobileMenuToggle.addEventListener('click', function() {
            mainNav.classList.toggle('active');
        });
    }
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
    
    // Real-time notification check (every 30 seconds)
    if (window.userLoggedIn) {
        setInterval(updateNotificationCount, 30000);
    }
});

function updateNotificationCount() {
    fetch('/api/get_notifications_count.php')
        .then(response => response.json())
        .then(data => {
            const badge = document.querySelector('.notification-badge');
            if (badge) {
                if (data.count > 0) {
                    badge.textContent = data.count;
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }
            }
        });
}

// Global functions for like and follow
document.addEventListener('DOMContentLoaded', function() {
    // Initialize any post likes/follows on page load
    initializeInteractions();
});

function initializeInteractions() {
    // Attach event listeners to all like buttons
    document.querySelectorAll('.like-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const postId = this.getAttribute('data-post-id') || this.closest('.post-card')?.id?.replace('post-', '');
            if (postId) {
                likePost(postId);
            }
        });
    });
    
    // Attach event listeners to all follow buttons
    document.querySelectorAll('.follow-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const userId = this.getAttribute('data-user-id') || this.id?.replace('follow-btn-', '');
            if (userId) {
                followUser(userId);
            }
        });
    });
}

// Like/Unlike Post
function likePost(postId) {
    console.log('Like/Unlike post:', postId);
    
    // Get the like button
    const likeBtn = document.getElementById('like-btn-' + postId);
    
    // Disable button while processing
    if (likeBtn) {
        likeBtn.disabled = true;
        likeBtn.style.opacity = '0.7';
    }
    
    // Create form data
    const formData = new URLSearchParams();
    formData.append('post_id', postId);
    
    fetch('../api/like_post.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData.toString()
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        console.log('Like response:', data);
        
        if (data.success) {
            // Update the button that was clicked
            if (likeBtn) {
                updateLikeButton(likeBtn, data.action, postId);
            }
            
            // Update any other like buttons for the same post
            const otherButtons = document.querySelectorAll(`[data-post-id="${postId}"]`);
            otherButtons.forEach(btn => {
                if (btn !== likeBtn) {
                    updateLikeButton(btn, data.action, postId);
                }
            });
            
            // Update likes count
            const likesCount = document.getElementById('likes-count-' + postId);
            if (likesCount) {
                likesCount.textContent = data.likes_count;
            }
            
            // Update stats bar if exists
            updatePostStats(postId, data.likes_count);
            
            // Show success message
            showToast(data.message || (data.action === 'liked' ? 'Post liked! ❤️' : 'Post unliked'), 'success');
        } else {
            showToast(data.error || 'Failed to process request', 'error');
        }
    })
    .catch(error => {
        console.error('Like error:', error);
        showToast('Network error. Please try again.', 'error');
    })
    .finally(() => {
        // Re-enable button
        if (likeBtn) {
            likeBtn.disabled = false;
            likeBtn.style.opacity = '1';
        }
    });
}

// Helper function to update like button appearance
function updateLikeButton(button, action, postId) {
    if (!button) return;
    
    if (action === 'liked') {
        // Liked state
        button.innerHTML = '<i class="fas fa-heart"></i> Liked';
        button.classList.add('liked');
        button.style.color = '#e91e63';
        button.style.fontWeight = '600';
        
        // Add animation
        button.style.transform = 'scale(1.1)';
        setTimeout(() => {
            button.style.transform = 'scale(1)';
        }, 200);
        
        // Add pulse animation to icon
        const icon = button.querySelector('i');
        if (icon) {
            icon.style.animation = 'heartPulse 0.3s ease';
            setTimeout(() => {
                icon.style.animation = '';
            }, 300);
        }
    } else {
        // Unliked state
        button.innerHTML = '<i class="far fa-heart"></i> Like';
        button.classList.remove('liked');
        button.style.color = '#666';
        button.style.fontWeight = '500';
        button.style.transform = 'scale(1)';
    }
}

// Update post stats display
function updatePostStats(postId, likesCount) {
    // Update in post stats bar
    const postCard = document.getElementById('post-' + postId);
    if (postCard) {
        const statsBar = postCard.querySelector('.post-stats-bar, .post-stats');
        if (statsBar) {
            const likesSpan = statsBar.querySelector('span:first-child');
            if (likesSpan) {
                likesSpan.innerHTML = '<span id="likes-count-' + postId + '">' + likesCount + '</span> likes';
            }
        }
    }
}

// Add heart pulse animation
const heartStyle = document.createElement('style');
heartStyle.textContent = `
    @keyframes heartPulse {
        0% { transform: scale(1); }
        25% { transform: scale(1.3); }
        50% { transform: scale(1); }
        75% { transform: scale(1.2); }
        100% { transform: scale(1); }
    }
    
    .like-btn {
        transition: all 0.2s ease;
    }
    
    .like-btn.liked {
        color: #e91e63 !important;
    }
    
    .like-btn.liked i {
        animation: heartPulse 0.3s ease;
    }
    
    .like-btn:hover {
        transform: scale(1.05);
    }
    
    .like-btn.liked:hover {
        color: #c2185b !important;
    }
`;
document.head.appendChild(heartStyle);

// Follow/Unfollow User
function followUser(userId) {
    console.log('Follow/Unfollow user:', userId);
    
    // Get the button
    const followBtn = document.getElementById('follow-btn-' + userId);
    
    // Disable button while processing
    if (followBtn) {
        followBtn.disabled = true;
        followBtn.style.opacity = '0.7';
    }
    
    // Create form data
    const formData = new URLSearchParams();
    formData.append('user_id', userId);
    
    fetch('../api/follow_user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData.toString()
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        console.log('Follow response:', data);
        
        if (data.success) {
            // Update button
            if (followBtn) {
                if (data.action === 'followed') {
                    followBtn.innerHTML = '<i class="fas fa-user-check"></i> Following';
                    followBtn.classList.add('following');
                    followBtn.style.backgroundColor = '#4caf50';
                    followBtn.style.color = 'white';
                    followBtn.style.borderColor = '#4caf50';
                } else {
                    followBtn.innerHTML = '<i class="fas fa-user-plus"></i> Follow';
                    followBtn.classList.remove('following');
                    followBtn.style.backgroundColor = '';
                    followBtn.style.color = '';
                    followBtn.style.borderColor = '';
                }
            }
            
            // Update followers count
            const followersCount = document.getElementById('followers-count');
            if (followersCount) {
                followersCount.textContent = data.followers_count;
            }
            
            // Update any other follow buttons for this user on the page
            const otherButtons = document.querySelectorAll(`[data-user-id="${userId}"]`);
            otherButtons.forEach(btn => {
                if (data.action === 'followed') {
                    btn.innerHTML = '<i class="fas fa-user-check"></i> Following';
                    btn.classList.add('following');
                    btn.style.backgroundColor = '#4caf50';
                    btn.style.color = 'white';
                } else {
                    btn.innerHTML = '<i class="fas fa-user-plus"></i> Follow';
                    btn.classList.remove('following');
                    btn.style.backgroundColor = '';
                    btn.style.color = '';
                }
            });
            
            // Show success message
            showToast(data.message || (data.action === 'followed' ? 'Followed successfully!' : 'Unfollowed successfully!'), 'success');
        } else {
            // Show error
            showToast(data.error || 'Failed to process request', 'error');
        }
    })
    .catch(error => {
        console.error('Follow error:', error);
        showToast('Network error. Please try again.', 'error');
        
        // Reset button
        if (followBtn) {
            followBtn.style.backgroundColor = '#ff4444';
            setTimeout(() => {
                followBtn.style.backgroundColor = '';
                followBtn.style.opacity = '1';
                followBtn.disabled = false;
            }, 1000);
        }
    })
    .finally(() => {
        // Re-enable button
        if (followBtn) {
            followBtn.disabled = false;
            followBtn.style.opacity = '1';
        }
    });
}

// Toast notification function
function showToast(message, type = 'info') {
    // Remove existing toast
    const existingToast = document.querySelector('.custom-toast');
    if (existingToast) {
        existingToast.remove();
    }
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `custom-toast toast-${type}`;
    toast.innerHTML = `
        <span>${message}</span>
        <button onclick="this.parentElement.remove()">&times;</button>
    `;
    
    // Add styles
    Object.assign(toast.style, {
        position: 'fixed',
        bottom: '20px',
        right: '20px',
        padding: '15px 20px',
        borderRadius: '10px',
        color: 'white',
        fontWeight: '500',
        zIndex: '9999',
        animation: 'slideIn 0.3s ease',
        display: 'flex',
        alignItems: 'center',
        gap: '15px',
        boxShadow: '0 5px 20px rgba(0,0,0,0.3)',
        maxWidth: '400px'
    });
    
    // Set background color based on type
    if (type === 'success') {
        toast.style.backgroundColor = '#4caf50';
    } else if (type === 'error') {
        toast.style.backgroundColor = '#f44336';
    } else {
        toast.style.backgroundColor = '#2196f3';
    }
    
    document.body.appendChild(toast);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (toast.parentElement) {
            toast.style.animation = 'slideOut 0.3s ease forwards';
            setTimeout(() => toast.remove(), 300);
        }
    }, 3000);
}

// Add toast animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
// Share Post Function
function sharePost(postId) {
    console.log('Sharing post:', postId);
    
    fetch('../api/share_post.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'post_id=' + postId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Post shared successfully!');
            location.reload();
        } else {
            alert('Failed to share post. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}
// Update Like Button State for Horizontal Layout
function updateLikeButtonState(button, action) {
    if (!button) return;
    
    const icon = button.querySelector('i');
    const textSpan = button.querySelector('span:first-of-type');
    const countSpan = button.querySelector('.count');
    
    if (action === 'liked') {
        if (icon) {
            icon.className = 'fas fa-heart';
        }
        if (textSpan) {
            textSpan.textContent = 'Liked';
        }
        button.classList.add('liked');
        button.style.color = '#e91e63';
        
        // Brief animation
        button.style.transform = 'scale(1.05)';
        setTimeout(() => {
            button.style.transform = 'scale(1)';
        }, 150);
    } else {
        if (icon) {
            icon.className = 'far fa-heart';
        }
        if (textSpan) {
            textSpan.textContent = 'Like';
        }
        button.classList.remove('liked');
        button.style.color = '#65676b';
        button.style.transform = 'scale(1)';
    }
}