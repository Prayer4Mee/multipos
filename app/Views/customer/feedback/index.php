<?php
// =====================================
// app/Views/customer/feedback/index.php
// =====================================
?>
<?= $this->extend('layouts/kiosk') ?>

<?= $this->section('content') ?>
<div class="feedback-container">
    <div class="feedback-header">
        <h2>📝 Your Feedback Matters!</h2>
        <p>Help us improve your dining experience</p>
    </div>
    
    <!-- Rating Section -->
    <div class="rating-section">
        <div class="card">
            <div class="card-body text-center">
                <h4>How was your overall experience?</h4>
                <div class="star-rating" id="overall-rating">
                    <span class="star" data-rating="1">⭐</span>
                    <span class="star" data-rating="2">⭐</span>
                    <span class="star" data-rating="3">⭐</span>
                    <span class="star" data-rating="4">⭐</span>
                    <span class="star" data-rating="5">⭐</span>
                </div>
                <div class="rating-text" id="rating-text">Tap to rate</div>
            </div>
        </div>
    </div>
    
    <!-- Detailed Feedback -->
    <div class="detailed-feedback">
        <div class="row">
            <div class="col-md-6">
                <div class="feedback-card">
                    <h5>🍽️ Food Quality</h5>
                    <div class="rating-stars" data-category="food">
                        <span class="star" data-rating="1">⭐</span>
                        <span class="star" data-rating="2">⭐</span>
                        <span class="star" data-rating="3">⭐</span>
                        <span class="star" data-rating="4">⭐</span>
                        <span class="star" data-rating="5">⭐</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="feedback-card">
                    <h5>⚡ Service Speed</h5>
                    <div class="rating-stars" data-category="service">
                        <span class="star" data-rating="1">⭐</span>
                        <span class="star" data-rating="2">⭐</span>
                        <span class="star" data-rating="3">⭐</span>
                        <span class="star" data-rating="4">⭐</span>
                        <span class="star" data-rating="5">⭐</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="feedback-card">
                    <h5>🏪 Cleanliness</h5>
                    <div class="rating-stars" data-category="cleanliness">
                        <span class="star" data-rating="1">⭐</span>
                        <span class="star" data-rating="2">⭐</span>
                        <span class="star" data-rating="3">⭐</span>
                        <span class="star" data-rating="4">⭐</span>
                        <span class="star" data-rating="5">⭐</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="feedback-card">
                    <h5>💰 Value for Money</h5>
                    <div class="rating-stars" data-category="value">
                        <span class="star" data-rating="1">⭐</span>
                        <span class="star" data-rating="2">⭐</span>
                        <span class="star" data-rating="3">⭐</span>
                        <span class="star" data-rating="4">⭐</span>
                        <span class="star" data-rating="5">⭐</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Comments Section -->
    <div class="comments-section">
        <div class="card">
            <div class="card-body">
                <h5>💬 Additional Comments</h5>
                <textarea id="feedback-comments" placeholder="Tell us more about your experience..." rows="4"></textarea>
                
                <!-- Quick Feedback Buttons -->
                <div class="quick-feedback">
                    <h6>Quick Feedback:</h6>
                    <div class="feedback-tags">
                        <button class="tag-btn" data-tag="delicious">😋 Delicious</button>
                        <button class="tag-btn" data-tag="fast">⚡ Fast Service</button>
                        <button class="tag-btn" data-tag="clean">✨ Very Clean</button>
                        <button class="tag-btn" data-tag="friendly">😊 Friendly Staff</button>
                        <button class="tag-btn" data-tag="affordable">💰 Affordable</button>
                        <button class="tag-btn" data-tag="recommend">👍 Would Recommend</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Customer Info (Optional) -->
    <div class="customer-info-section">
        <div class="card">
            <div class="card-body">
                <h5>📞 Contact Info (Optional)</h5>
                <div class="row">
                    <div class="col-md-6">
                        <input type="text" id="customer-name" placeholder="Your Name" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <input type="email" id="customer-email" placeholder="Email Address" class="form-control">
                    </div>
                </div>
                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" id="newsletter-signup">
                    <label class="form-check-label" for="newsletter-signup">
                        Subscribe to our newsletter for special offers
                    </label>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Submit Button -->
    <div class="submit-section">
        <button class="submit-feedback-btn" onclick="submitFeedback()">
            <i class="fas fa-paper-plane"></i> Submit Feedback
        </button>
        <p class="privacy-note">
            Your feedback helps us improve. All reviews are confidential and used for quality improvement purposes only.
        </p>
    </div>
</div>

<!-- Thank You Modal -->
<div class="modal fade" id="thankYouModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="thank-you-animation">
                    <i class="fas fa-heart text-danger" style="font-size: 4rem;"></i>
                </div>
                <h3>Thank You!</h3>
                <p>Your feedback has been submitted successfully. We truly appreciate your time and input!</p>
                <div class="social-share">
                    <p>Share your experience:</p>
                    <button class="btn btn-primary btn-sm me-2">
                        <i class="fab fa-facebook"></i> Facebook
                    </button>
                    <button class="btn btn-info btn-sm me-2">
                        <i class="fab fa-twitter"></i> Twitter
                    </button>
                    <button class="btn btn-success btn-sm">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </button>
                </div>
                <button type="button" class="btn btn-secondary mt-3" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.feedback-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.feedback-header {
    text-align: center;
    margin-bottom: 30px;
}

.feedback-header h2 {
    color: var(--primary-color);
    margin-bottom: 10px;
}

.rating-section {
    margin-bottom: 30px;
}

.star-rating {
    font-size: 3rem;
    margin: 20px 0;
}

.star {
    cursor: pointer;
    transition: all 0.3s ease;
    margin: 0 5px;
    opacity: 0.3;
}

.star.active {
    opacity: 1;
    transform: scale(1.2);
}

.rating-text {
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--primary-color);
}

.feedback-card {
    background: white;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    text-align: center;
}

.rating-stars {
    font-size: 1.5rem;
    margin-top: 10px;
}

.rating-stars .star {
    font-size: 1.5rem;
    margin: 0 2px;
}

.comments-section, .customer-info-section {
    margin-bottom: 30px;
}

.comments-section textarea {
    width: 100%;
    border: 2px solid #e1e8ed;
    border-radius: 10px;
    padding: 15px;
    font-size: 1rem;
    resize: vertical;
    margin-top: 10px;
}

.quick-feedback {
    margin-top: 20px;
}

.feedback-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 10px;
}

.tag-btn {
    background: #f8f9fa;
    border: 2px solid #e1e8ed;
    padding: 8px 15px;
    border-radius: 20px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.9rem;
}

.tag-btn.active {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

.tag-btn:hover {
    border-color: var(--primary-color);
    background: rgba(102, 126, 234, 0.1);
}

.submit-section {
    text-align: center;
}

.submit-feedback-btn {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    border: none;
    padding: 15px 40px;
    border-radius: 25px;
    font-size: 1.2rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.submit-feedback-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(40, 167, 69, 0.3);
}

.privacy-note {
    font-size: 0.9rem;
    color: #666;
    margin-top: 15px;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

.thank-you-animation i {
    animation: heartbeat 1.5s ease-in-out infinite;
}

@keyframes heartbeat {
    0% { transform: scale(1); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

.social-share {
    margin: 20px 0;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .feedback-container {
        padding: 15px;
    }
    
    .star-rating {
        font-size: 2.5rem;
    }
    
    .feedback-tags {
        justify-content: center;
    }
    
    .tag-btn {
        font-size: 0.8rem;
        padding: 6px 12px;
    }
}
</style>

<script>
let ratings = {
    overall: 0,
    food: 0,
    service: 0,
    cleanliness: 0,
    value: 0
};

let selectedTags = [];

// Star rating functionality
$('.star-rating .star').click(function() {
    const rating = $(this).data('rating');
    ratings.overall = rating;
    
    $(this).parent().find('.star').removeClass('active');
    $(this).parent().find('.star').each(function(index) {
        if (index < rating) {
            $(this).addClass('active');
        }
    });
    
    updateRatingText(rating);
});

$('.rating-stars .star').click(function() {
    const rating = $(this).data('rating');
    const category = $(this).parent().data('category');
    ratings[category] = rating;
    
    $(this).parent().find('.star').removeClass('active');
    $(this).parent().find('.star').each(function(index) {
        if (index < rating) {
            $(this).addClass('active');
        }
    });
});

function updateRatingText(rating) {
    const texts = {
        1: "😞 Poor - We need to improve",
        2: "😐 Fair - Could be better", 
        3: "🙂 Good - We're on track",
        4: "😊 Very Good - Great experience",
        5: "🤩 Excellent - Outstanding!"
    };
    
    $('#rating-text').text(texts[rating] || 'Tap to rate');
}

// Tag selection
$('.tag-btn').click(function() {
    const tag = $(this).data('tag');
    
    if ($(this).hasClass('active')) {
        $(this).removeClass('active');
        selectedTags = selectedTags.filter(t => t !== tag);
    } else {
        $(this).addClass('active');
        selectedTags.push(tag);
    }
});

function submitFeedback() {
    // Validate minimum rating
    if (ratings.overall === 0) {
        alert('Please provide an overall rating before submitting.');
        return;
    }
    
    const feedbackData = {
        ratings: ratings,
        comments: $('#feedback-comments').val(),
        tags: selectedTags,
        customer_name: $('#customer-name').val(),
        customer_email: $('#customer-email').val(),
        newsletter_signup: $('#newsletter-signup').is(':checked'),
        order_id: localStorage.getItem('last_order_id'), // If available
        timestamp: new Date().toISOString()
    };
    
    // Submit feedback
    $.ajax({
        url: '<?= base_url('api/feedback/submit') ?>',
        method: 'POST',
        data: feedbackData,
        beforeSend: function() {
            $('.submit-feedback-btn').html('<i class="fas fa-spinner fa-spin"></i> Submitting...').prop('disabled', true);
        },
        success: function(response) {
            if (response.success) {
                $('#thankYouModal').modal('show');
                
                // Clear form after successful submission
                setTimeout(function() {
                    resetForm();
                }, 2000);
            } else {
                alert('Error submitting feedback: ' + response.message);
            }
        },
        error: function() {
            alert('Error submitting feedback. Please try again.');
        },
        complete: function() {
            $('.submit-feedback-btn').html('<i class="fas fa-paper-plane"></i> Submit Feedback').prop('disabled', false);
        }
    });
}

function resetForm() {
    // Reset ratings
    ratings = { overall: 0, food: 0, service: 0, cleanliness: 0, value: 0 };
    $('.star').removeClass('active');
    $('#rating-text').text('Tap to rate');
    
    // Reset form fields
    $('#feedback-comments').val('');
    $('#customer-name').val('');
    $('#customer-email').val('');
    $('#newsletter-signup').prop('checked', false);
    
    // Reset tags
    selectedTags = [];
    $('.tag-btn').removeClass('active');
}

// Auto-save draft feedback
setInterval(function() {
    if (ratings.overall > 0 || $('#feedback-comments').val()) {
        const draftData = {
            ratings: ratings,
            comments: $('#feedback-comments').val(),
            tags: selectedTags,
            timestamp: new Date().toISOString()
        };
        localStorage.setItem('feedback_draft', JSON.stringify(draftData));
    }
}, 30000); // Save every 30 seconds

// Load draft on page load
$(document).ready(function() {
    const draft = localStorage.getItem('feedback_draft');
    if (draft) {
        const draftData = JSON.parse(draft);
        // Only load if draft is less than 24 hours old
        const draftAge = new Date() - new Date(draftData.timestamp);
        if (draftAge < 24 * 60 * 60 * 1000) {
            if (confirm('You have a saved feedback draft. Would you like to continue where you left off?')) {
                loadDraft(draftData);
            }
        }
    }
});

function loadDraft(draftData) {
    ratings = draftData.ratings;
    $('#feedback-comments').val(draftData.comments);
    selectedTags = draftData.tags;
    
    // Restore star ratings
    Object.keys(ratings).forEach(category => {
        const rating = ratings[category];
        if (rating > 0) {
            const container = category === 'overall' ? $('.star-rating') : $(`.rating-stars[data-category="${category}"]`);
            container.find('.star').each(function(index) {
                if (index < rating) {
                    $(this).addClass('active');
                }
            });
        }
    });
    
    // Restore tags
    selectedTags.forEach(tag => {
        $(`.tag-btn[data-tag="${tag}"]`).addClass('active');
    });
    
    if (ratings.overall > 0) {
        updateRatingText(ratings.overall);
    }
}
</script>
<?= $this->endSection() ?>