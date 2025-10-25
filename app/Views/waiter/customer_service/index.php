<?php
// =============================================================================
// WAITER/CUSTOMER_SERVICE/ - Customer Service Views
// =============================================================================

// File: app/Views/waiter/customer_service/index.php
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Customer Service - <?= esc($tenant_config['restaurant_name']) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <!-- Active Requests -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">🔔 Active Requests</h4>
                    <div class="card-tools">
                        <button class="btn btn-sm btn-info" onclick="refreshRequests()">
                            <i class="fas fa-sync"></i> Refresh
                        </button>
                    </div>
                </div>
                
                <div class="card-body">
                    <div id="active_requests_container">
                        <?php if (isset($active_requests) && !empty($active_requests)): ?>
                            <?php foreach ($active_requests as $request): ?>
                            <div class="request-item priority-<?= $request['priority'] ?>" data-request-id="<?= $request['id'] ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="request-info">
                                        <h6 class="request-title">
                                            <i class="fas fa-<?= getRequestIcon($request['type']) ?>"></i>
                                            <?= esc($request['title']) ?>
                                        </h6>
                                        <p class="request-description"><?= esc($request['description']) ?></p>
                                        <small class="text-muted">
                                            Table <?= $request['table_number'] ?> • 
                                            <?= time_elapsed_string($request['created_at']) ?> ago
                                        </small>
                                    </div>
                                    <div class="request-actions">
                                        <span class="badge badge-<?= getPriorityBadgeColor($request['priority']) ?>">
                                            <?= ucfirst($request['priority']) ?>
                                        </span>
                                        <div class="btn-group-vertical mt-2">
                                            <button class="btn btn-sm btn-success" onclick="resolveRequest(<?= $request['id'] ?>)">
                                                Resolve
                                            </button>
                                            <button class="btn btn-sm btn-warning" onclick="escalateRequest(<?= $request['id'] ?>)">
                                                Escalate
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-muted">
                                <i class="fas fa-check-circle fa-3x mb-3"></i>
                                <p>No active requests at the moment</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Feedback -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">💬 Recent Feedback</h4>
                    <div class="card-tools">
                        <button class="btn btn-sm btn-primary" onclick="addFeedback()">
                            <i class="fas fa-plus"></i> Add Feedback
                        </button>
                    </div>
                </div>
                
                <div class="card-body">
                    <div id="feedback_container">
                        <?php if (isset($recent_feedback) && !empty($recent_feedback)): ?>
                            <?php foreach ($recent_feedback as $feedback): ?>
                            <div class="feedback-item">
                                <div class="d-flex justify-content-between">
                                    <div class="feedback-rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?= $i <= $feedback['rating'] ? 'text-warning' : 'text-muted' ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <small class="text-muted"><?= date('M j, H:i', strtotime($feedback['created_at'])) ?></small>
                                </div>
                                <p class="feedback-comment"><?= esc($feedback['comment']) ?></p>
                                <small class="text-info">Table <?= $feedback['table_number'] ?></small>
                                
                                <?php if ($feedback['status'] === 'pending'): ?>
                                <div class="mt-2">
                                    <button class="btn btn-sm btn-outline-primary" onclick="respondToFeedback(<?= $feedback['id'] ?>)">
                                        Respond
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-muted">
                                <i class="fas fa-comments fa-3x mb-3"></i>
                                <p>No recent feedback</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Statistics -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">📊 Service Statistics (Today)</h4>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-2">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-bell"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Requests</span>
                                    <span class="info-box-number"><?= $service_stats['total_requests'] ?? 0 ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Resolved</span>
                                    <span class="info-box-number"><?= $service_stats['resolved_requests'] ?? 0 ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Avg Response</span>
                                    <span class="info-box-number"><?= $service_stats['avg_response_time'] ?? 0 ?>m</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box bg-primary">
                                <span class="info-box-icon"><i class="fas fa-star"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Avg Rating</span>
                                    <span class="info-box-number"><?= number_format($service_stats['avg_rating'] ?? 0, 1) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box bg-secondary">
                                <span class="info-box-icon"><i class="fas fa-users"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Tables Served</span>
                                    <span class="info-box-number"><?= $service_stats['tables_served'] ?? 0 ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box bg-danger">
                                <span class="info-box-icon"><i class="fas fa-exclamation"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Complaints</span>
                                    <span class="info-box-number"><?= $service_stats['complaints'] ?? 0 ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Common Requests Quick Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">⚡ Quick Actions</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <button class="btn btn-outline-primary btn-block" onclick="quickRequest('water')">
                                <i class="fas fa-tint"></i><br>Request Water
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-warning btn-block" onclick="quickRequest('bill')">
                                <i class="fas fa-receipt"></i><br>Request Bill
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-info btn-block" onclick="quickRequest('assistance')">
                                <i class="fas fa-hand-paper"></i><br>Need Assistance
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-danger btn-block" onclick="quickRequest('complaint')">
                                <i class="fas fa-exclamation-triangle"></i><br>Handle Complaint
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Request Resolution Modal -->
<div class="modal fade" id="resolveRequestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Resolve Request</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="resolveRequestForm">
                    <input type="hidden" id="resolve_request_id">
                    
                    <div class="form-group">
                        <label for="resolution_notes">Resolution Notes</label>
                        <textarea id="resolution_notes" class="form-control" rows="4" placeholder="Describe how the request was resolved" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="resolution_rating">Service Quality</label>
                        <select id="resolution_rating" class="form-control">
                            <option value="5">Excellent</option>
                            <option value="4" selected>Good</option>
                            <option value="3">Satisfactory</option>
                            <option value="2">Needs Improvement</option>
                            <option value="1">Poor</option>
                        </select>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" id="follow_up_required" class="form-check-input">
                        <label for="follow_up_required" class="form-check-label">
                            Follow-up required
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="confirmResolveRequest()">
                    Mark as Resolved
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add Feedback Modal -->
<div class="modal fade" id="addFeedbackModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Customer Feedback</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addFeedbackForm">
                    <div class="form-group">
                        <label for="feedback_table">Table Number</label>
                        <select id="feedback_table" class="form-control" required>
                            <option value="">Select table</option>
                            <?php if (isset($occupied_tables)): ?>
                                <?php foreach ($occupied_tables as $table): ?>
                                <option value="<?= $table['id'] ?>"><?= $table['number'] ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="feedback_rating">Rating</label>
                        <div class="rating-input">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star rating-star" data-rating="<?= $i ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" id="feedback_rating" value="5">
                    </div>
                    
                    <div class="form-group">
                        <label for="feedback_comment">Customer Comment</label>
                        <textarea id="feedback_comment" class="form-control" rows="4" placeholder="Enter customer's feedback" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="feedback_category">Category</label>
                        <select id="feedback_category" class="form-control">
                            <option value="general">General</option>
                            <option value="food_quality">Food Quality</option>
                            <option value="service">Service</option>
                            <option value="cleanliness">Cleanliness</option>
                            <option value="atmosphere">Atmosphere</option>
                            <option value="complaint">Complaint</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitFeedback()">
                    Save Feedback
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.request-item {
    background: white;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-left: 4px solid;
}

.request-item.priority-low {
    border-left-color: #28a745;
}

.request-item.priority-medium {
    border-left-color: #ffc107;
}

.request-item.priority-high {
    border-left-color: #dc3545;
}

.request-item.priority-urgent {
    border-left-color: #dc3545;
    background: #ffe6e6;
}

.request-title {
    color: #333;
    margin-bottom: 5px;
}

.request-description {
    color: #666;
    margin-bottom: 10px;
}

.feedback-item {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    border: 1px solid #e9ecef;
}

.feedback-comment {
    margin: 10px 0;
    color: #333;
}

.info-box {
    display: block;
    min-height: 90px;
    background: #fff;
    width: 100%;
    box-shadow: 0 1px 1px rgba(0,0,0,0.1);
    border-radius: 2px;
    margin-bottom: 15px;
}

.info-box .info-box-icon {
    border-radius: 2px 0 0 2px;
    display: block;
    float: left;
    height: 90px;
    width: 90px;
    text-align: center;
    font-size: 45px;
    line-height: 90px;
    background: rgba(0,0,0,0.2);
}

.info-box .info-box-content {
    padding: 5px 10px;
    margin-left: 90px;
}

.info-box .info-box-text {
    text-transform: uppercase;
    font-weight: bold;
    font-size: 13px;
}

.info-box .info-box-number {
    display: block;
    font-weight: bold;
    font-size: 18px;
}

.rating-input {
    margin: 10px 0;
}

.rating-star {
    font-size: 1.5em;
    color: #ddd;
    cursor: pointer;
    margin-right: 5px;
    transition: color 0.2s;
}

.rating-star:hover,
.rating-star.active {
    color: #ffc107;
}

@media (max-width: 768px) {
    .request-actions {
        margin-top: 10px;
    }
    
    .btn-group-vertical {
        width: 100%;
    }
}
</style>

<script>
// Customer Service JavaScript
$(document).ready(function() {
    // Auto-refresh requests every 30 seconds
    setInterval(refreshRequests, 30000);
    
    // Rating stars interaction
    $('.rating-star').hover(function() {
        const rating = $(this).data('rating');
        highlightStars(rating);
    }).click(function() {
        const rating = $(this).data('rating');
        $('#feedback_rating').val(rating);
        setStarRating(rating);
    });
});

function refreshRequests() {
    $.ajax({
        url: '<?= base_url("restaurant/{$tenant_id}/waiter/customer-service/requests") ?>',
        method: 'GET',
        dataType: 'json',
        beforeSend: function() {
            $('.fa-sync').addClass('fa-spin');
        },
        success: function(response) {
            if (response.success) {
                updateRequestsDisplay(response.requests);
            }
        },
        error: function() {
            showNotification('Error refreshing requests', 'error');
        },
        complete: function() {
            $('.fa-sync').removeClass('fa-spin');
        }
    });
}

function updateRequestsDisplay(requests) {
    const container = $('#active_requests_container');
    
    if (requests.length === 0) {
        container.html(`
            <div class="text-center text-muted">
                <i class="fas fa-check-circle fa-3x mb-3"></i>
                <p>No active requests at the moment</p>
            </div>
        `);
    } else {
        let html = '';
        requests.forEach(request => {
            html += `
                <div class="request-item priority-${request.priority}" data-request-id="${request.id}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="request-info">
                            <h6 class="request-title">
                                <i class="fas fa-${getRequestIcon(request.type)}"></i>
                                ${request.title}
                            </h6>
                            <p class="request-description">${request.description}</p>
                            <small class="text-muted">
                                Table ${request.table_number} • ${request.time_ago} ago
                            </small>
                        </div>
                        <div class="request-actions">
                            <span class="badge badge-${getPriorityBadgeColor(request.priority)}">
                                ${request.priority.charAt(0).toUpperCase() + request.priority.slice(1)}
                            </span>
                            <div class="btn-group-vertical mt-2">
                                <button class="btn btn-sm btn-success" onclick="resolveRequest(${request.id})">
                                    Resolve
                                </button>
                                <button class="btn btn-sm btn-warning" onclick="escalateRequest(${request.id})">
                                    Escalate
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        container.html(html);
    }
}

function resolveRequest(requestId) {
    $('#resolve_request_id').val(requestId);
    $('#resolveRequestModal').modal('show');
}

function confirmResolveRequest() {
    const formData = {
        request_id: $('#resolve_request_id').val(),
        resolution_notes: $('#resolution_notes').val(),
        resolution_rating: $('#resolution_rating').val(),
        follow_up_required: $('#follow_up_required').is(':checked')
    };
    
    $.ajax({
        url: '<?= base_url("restaurant/{$tenant_id}/waiter/customer-service/resolve") ?>',
        method: 'POST',
        data: formData,
        dataType: 'json',
        beforeSend: function() {
            $('.modal .btn-success').prop('disabled', true).text('Resolving...');
        },
        success: function(response) {
            if (response.success) {
                $('#resolveRequestModal').modal('hide');
                showNotification('Request resolved successfully!', 'success');
                refreshRequests();
            } else {
                showNotification('Error resolving request: ' + response.message, 'error');
            }
        },
        error: function() {
            showNotification('Error resolving request. Please try again.', 'error');
        },
        complete: function() {
            $('.modal .btn-success').prop('disabled', false).text('Mark as Resolved');
        }
    });
}

function escalateRequest(requestId) {
    if (confirm('Escalate this request to management?')) {
        $.ajax({
            url: '<?= base_url("restaurant/{$tenant_id}/waiter/customer-service/escalate") ?>',
            method: 'POST',
            data: { request_id: requestId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showNotification('Request escalated to management', 'info');
                    refreshRequests();
                } else {
                    showNotification('Error escalating request: ' + response.message, 'error');
                }
            },
            error: function() {
                showNotification('Error escalating request. Please try again.', 'error');
            }
        });
    }
}

function addFeedback() {
    $('#addFeedbackModal').modal('show');
    setStarRating(5); // Default to 5 stars
}