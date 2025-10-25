<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="tables-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-table"></i> Table Management</h1>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="refreshTables()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTableModal">
                <i class="fas fa-plus"></i> Add Table
            </button>
        </div>
    </div>

    <!-- Table Status Summary -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?= $stats['available_tables'] ?></h4>
                            <p class="mb-0">Available</p>
                            <small>Capacity: <?= $stats['available_capacity'] ?></small>
                        </div>
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?= $stats['occupied_tables'] ?></h4>
                            <p class="mb-0">Occupied</p>
                            <small>Rate: <?= $stats['occupancy_rate'] ?>%</small>
                        </div>
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?= $stats['reserved_tables'] ?></h4>
                            <p class="mb-0">Reserved</p>
                            <small>Pending</small>
                        </div>
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?= $stats['cleaning_tables'] ?></h4>
                            <p class="mb-0">Cleaning</p>
                            <small>In Progress</small>
                        </div>
                        <i class="fas fa-broom fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Additional Stats -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Tables</h5>
                    <h3 class="text-primary"><?= $stats['total_tables'] ?></h3>
                    <p class="text-muted mb-0">Total Capacity: <?= $stats['total_capacity'] ?> seats</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Occupancy Rate</h5>
                    <h3 class="text-warning"><?= $stats['occupancy_rate'] ?>%</h3>
                    <p class="text-muted mb-0">Currently occupied tables</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tables Grid -->
    <div class="row">
        <!-- Table 1 -->
        <div class="col-md-3 col-lg-2 mb-3">
            <div class="card table-card available" data-table-id="1">
                <div class="card-body text-center">
                    <i class="fas fa-table fa-3x text-success mb-2"></i>
                    <h5>Table 1</h5>
                    <span class="badge bg-success">Available</span>
                    <div class="mt-2">
                        <small class="text-muted">Capacity: 4</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table 2 -->
        <div class="col-md-3 col-lg-2 mb-3">
            <div class="card table-card occupied" data-table-id="2">
                <div class="card-body text-center">
                    <i class="fas fa-table fa-3x text-warning mb-2"></i>
                    <h5>Table 2</h5>
                    <span class="badge bg-warning">Occupied</span>
                    <div class="mt-2">
                        <small class="text-muted">Guests: 2/4</small>
                        <br>
                        <small class="text-muted">Order: #1234</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table 3 -->
        <div class="col-md-3 col-lg-2 mb-3">
            <div class="card table-card available" data-table-id="3">
                <div class="card-body text-center">
                    <i class="fas fa-table fa-3x text-success mb-2"></i>
                    <h5>Table 3</h5>
                    <span class="badge bg-success">Available</span>
                    <div class="mt-2">
                        <small class="text-muted">Capacity: 6</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table 4 -->
        <div class="col-md-3 col-lg-2 mb-3">
            <div class="card table-card reserved" data-table-id="4">
                <div class="card-body text-center">
                    <i class="fas fa-table fa-3x text-danger mb-2"></i>
                    <h5>Table 4</h5>
                    <span class="badge bg-danger">Reserved</span>
                    <div class="mt-2">
                        <small class="text-muted">Time: 7:30 PM</small>
                        <br>
                        <small class="text-muted">Name: John Doe</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table 5 -->
        <div class="col-md-3 col-lg-2 mb-3">
            <div class="card table-card cleaning" data-table-id="5">
                <div class="card-body text-center">
                    <i class="fas fa-table fa-3x text-secondary mb-2"></i>
                    <h5>Table 5</h5>
                    <span class="badge bg-secondary">Cleaning</span>
                    <div class="mt-2">
                        <small class="text-muted">ETA: 5 min</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table 6 -->
        <div class="col-md-3 col-lg-2 mb-3">
            <div class="card table-card available" data-table-id="6">
                <div class="card-body text-center">
                    <i class="fas fa-table fa-3x text-success mb-2"></i>
                    <h5>Table 6</h5>
                    <span class="badge bg-success">Available</span>
                    <div class="mt-2">
                        <small class="text-muted">Capacity: 2</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table 7 -->
        <div class="col-md-3 col-lg-2 mb-3">
            <div class="card table-card occupied" data-table-id="7">
                <div class="card-body text-center">
                    <i class="fas fa-table fa-3x text-warning mb-2"></i>
                    <h5>Table 7</h5>
                    <span class="badge bg-warning">Occupied</span>
                    <div class="mt-2">
                        <small class="text-muted">Guests: 4/4</small>
                        <br>
                        <small class="text-muted">Order: #1235</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table 8 -->
        <div class="col-md-3 col-lg-2 mb-3">
            <div class="card table-card available" data-table-id="8">
                <div class="card-body text-center">
                    <i class="fas fa-table fa-3x text-success mb-2"></i>
                    <h5>Table 8</h5>
                    <span class="badge bg-success">Available</span>
                    <div class="mt-2">
                        <small class="text-muted">Capacity: 8</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Table Modal -->
<div class="modal fade" id="addTableModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Table</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addTableForm">
                    <div class="mb-3">
                        <label class="form-label">Table Number</label>
                        <input type="number" class="form-control" name="table_number" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Capacity</label>
                        <input type="number" class="form-control" name="capacity" min="1" max="12" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <select class="form-select" name="location" required>
                            <option value="">Select Location</option>
                            <option value="indoor">Indoor</option>
                            <option value="outdoor">Outdoor</option>
                            <option value="vip">VIP Section</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" required>
                            <option value="available">Available</option>
                            <option value="unavailable">Unavailable</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveTable()">Add Table</button>
            </div>
        </div>
    </div>
</div>

<!-- Table Status Modal -->
<div class="modal fade" id="tableStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Table Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Table</label>
                    <input type="text" class="form-control" id="modalTableNumber" readonly>
                    <input type="hidden" id="modalTableId">
                </div>
                <div class="mb-3">
                    <label class="form-label">Current Status</label>
                    <input type="text" class="form-control" id="modalCurrentStatus" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">New Status</label>
                    <select class="form-select" id="modalNewStatus">
                        <option value="available">Available</option>
                        <option value="occupied">Occupied</option>
                        <option value="reserved">Reserved</option>
                        <option value="cleaning">Cleaning</option>
                    </select>
                </div>
                <div class="mb-3" id="reservationDetails" style="display: none;">
                    <label class="form-label">Reservation Time</label>
                    <input type="datetime-local" class="form-control" id="reservationTime">
                </div>
                <div class="mb-3" id="reservationName" style="display: none;">
                    <label class="form-label">Customer Name</label>
                    <input type="text" class="form-control" id="customerName" placeholder="Enter customer name">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updateTableStatus()">Update Status</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Table click handlers
    $('.table-card').click(function() {
        const tableId = $(this).data('table-id');
        const status = $(this).hasClass('available') ? 'available' : 
                      $(this).hasClass('occupied') ? 'occupied' :
                      $(this).hasClass('reserved') ? 'reserved' : 'cleaning';
        
        showTableStatusModal(tableId, status);
    });
    
    // Status change handler for modal
    $('#modalNewStatus').change(function() {
        toggleReservationFields($(this).val());
    });
});

function showTableStatusModal(tableId, currentStatus) {
    // Get table number from the card
    const tableCard = $(`.table-card[data-table-id="${tableId}"]`);
    const tableNumber = tableCard.find('h5').text();
    
    // Set modal data
    $('#modalTableId').val(tableId);
    $('#modalTableNumber').val(tableNumber);
    $('#modalCurrentStatus').val(currentStatus.charAt(0).toUpperCase() + currentStatus.slice(1));
    $('#modalNewStatus').val(currentStatus);
    
    // Show/hide reservation fields
    toggleReservationFields(currentStatus);
    
    // Show modal
    $('#tableStatusModal').modal('show');
}

function toggleReservationFields(status) {
    if (status === 'reserved') {
        $('#reservationDetails, #reservationName').show();
    } else {
        $('#reservationDetails, #reservationName').hide();
    }
}

function updateTableStatus() {
    const tableId = $('#modalTableId').val();
    const newStatus = $('#modalNewStatus').val();
    const reservationTime = $('#reservationTime').val();
    const customerName = $('#customerName').val();
    
    console.log('Updating table:', tableId, 'to status:', newStatus);
    
    const updateData = {
        table_id: tableId,
        status: newStatus,
        csrf_test_name: '<?= csrf_hash() ?>'
    };
    
    if (newStatus === 'reserved') {
        updateData.reservation_time = reservationTime;
        updateData.customer_name = customerName;
    }
    
    console.log('Sending data:', updateData);
    
    // Show loading
    const updateBtn = $('#tableStatusModal .btn-primary');
    updateBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');
    
    // Send AJAX request
    $.ajax({
        url: '<?= base_url("restaurant/{$tenant->tenant_slug}/update-table-status") ?>',
        type: 'POST',
        data: updateData,
        dataType: 'json',
        beforeSend: function(xhr) {
            console.log('Sending AJAX request...');
        },
        success: function(response) {
            console.log('AJAX Success:', response);
            if (response.success) {
                // Update table card UI
                updateTableCard(tableId, newStatus);
                $('#tableStatusModal').modal('hide');
                alert('Table status updated successfully!');
            } else {
                alert('Error: ' + (response.error || 'Failed to update table status'));
            }
        },
        error: function(xhr, status, error) {
            console.log('AJAX Error:', xhr, status, error);
            console.log('Response Text:', xhr.responseText);
            const response = xhr.responseJSON;
            alert('Error: ' + (response?.error || 'Failed to update table status: ' + error));
        },
        complete: function() {
            updateBtn.prop('disabled', false).html('Update Status');
        }
    });
}

function updateTableCard(tableId, newStatus) {
    console.log('Updating table card:', tableId, 'to status:', newStatus);
    
    const tableCard = $(`.table-card[data-table-id="${tableId}"]`);
    console.log('Found table card:', tableCard.length);
    
    if (tableCard.length === 0) {
        console.error('Table card not found for ID:', tableId);
        return;
    }
    
    // Remove all status classes
    tableCard.removeClass('available occupied reserved cleaning');
    
    // Add new status class
    tableCard.addClass(newStatus);
    console.log('Added class:', newStatus);
    
    // Update badge
    const badge = tableCard.find('.badge');
    const statusText = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
    
    badge.removeClass('bg-success bg-warning bg-danger bg-secondary')
         .addClass(getStatusBadgeClass(newStatus))
         .text(statusText);
    
    console.log('Updated badge to:', statusText, 'with class:', getStatusBadgeClass(newStatus));
    
    // Update additional info based on status
    const infoDiv = tableCard.find('.mt-2');
    let infoHtml = '';
    
    if (newStatus === 'available') {
        infoHtml = '<small class="text-muted">Capacity: ' + getTableCapacity(tableId) + '</small>';
    } else if (newStatus === 'occupied') {
        infoHtml = '<small class="text-muted">Guests: 2/4</small><br><small class="text-muted">Order: #1234</small>';
    } else if (newStatus === 'reserved') {
        const reservationTime = $('#reservationTime').val();
        const customerName = $('#customerName').val();
        infoHtml = '<small class="text-muted">Time: ' + formatTime(reservationTime) + '</small><br><small class="text-muted">Name: ' + customerName + '</small>';
    } else if (newStatus === 'cleaning') {
        infoHtml = '<small class="text-muted">ETA: 5 min</small>';
    }
    
    infoDiv.html(infoHtml);
    console.log('Updated info div with:', infoHtml);
}

function getStatusBadgeClass(status) {
    switch(status) {
        case 'available': return 'bg-success';
        case 'occupied': return 'bg-warning';
        case 'reserved': return 'bg-danger';
        case 'cleaning': return 'bg-secondary';
        default: return 'bg-secondary';
    }
}

function getTableCapacity(tableId) {
    // This would ideally come from the database
    const capacities = {1: 4, 2: 4, 3: 6, 4: 4, 5: 4, 6: 2, 7: 4, 8: 8};
    return capacities[tableId] || 4;
}

function formatTime(datetime) {
    if (!datetime) return '';
    const date = new Date(datetime);
    return date.toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit'});
}

function saveTable() {
    const form = document.getElementById('addTableForm');
    const formData = new FormData(form);
    
    // Simulate save functionality
    console.log('Saving table:', Object.fromEntries(formData));
    alert('Table added successfully!');
    $('#addTableModal').modal('hide');
    form.reset();
}

function refreshTables() {
    location.reload();
}
</script>
<?= $this->endSection() ?>
