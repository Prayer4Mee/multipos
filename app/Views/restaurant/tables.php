<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="tables-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-table"></i> Table Management</h1>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="refreshTables()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
             <?php if (!empty($deleted_tables)): ?>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#restoreModal">
                    <i class="fas fa-undo"></i> Restore Tables (<?= count($deleted_tables) ?>)
                </button>
            <?php endif; ?>
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
    <!-- Static Only, let's change it to dynamic! Let's rely on the database! -->
    <!-- Tables Grid -->
    <div class="row">
        <?php foreach ($restaurant_tables as $table): ?>
            <?php
                // Go lowercase
                $currentStatus = strtolower($table->status);
                $status = strtolower($table->status);
                // Safety Net: Ensure variables are always defined
                $cardClass = $status; 
                $badgeClass = 'bg-secondary';
                $color = 'success'; //default color
                $icon = 'fa-check-circle'; //default icon

                // Only for reservation
                
                // Apply same colors as the old static one
                switch ($status) {
                    case 'available':
                        $cardClass = 'available';
                        $badgeClass = 'bg-success';
                        $iconColor = "text-success";
                        break;
                    case 'occupied':
                        $cardClass = 'occupied';
                        $badgeClass = 'bg-warning';
                        $iconColor = "text-warning";
                        break;
                    case 'reserved':
                        $cardClass = 'reserved';
                        $badgeClass = 'bg-danger';
                        $iconColor = "text-danger";
                        break;
                    case 'cleaning':
                        $cardClass = 'cleaning';
                        $badgeClass = 'bg-secondary';
                        $iconColor = "text-secondary";
                        break;
                        // ADDED THIS CASE TO PREVENT THE ERROR
                    case 'unavailable':
                    default:
                        $cardClass = 'cleaning'; // or 'unavailable' if you have CSS for it
                        $badgeClass = 'bg-dark';
                        $iconColor = "text-muted";
                        break;
                }
            ?>
        <!-- Table Card HTML -->
        <div class="col-md-3 mb-3">
            <div class="card table-card <?= $cardClass ?>" 
                onclick="editTable('<?= $table->id ?>', 
                    '<?= $table->table_number ?>', 
                    '<?= $table->capacity ?>', 
                    '<?= $table->location ?>', 
                    '<?= $status ?>',
                    '<?= esc($table->customer_name ?? '') ?>', 
                    '<?= $table->reservation_time ?? '' ?>'
                )">

                <div class="card-body text-center">
                    <!-- The icon look -->
                    <i class="fas fa-table fa-3x <?= $iconColor ?>"></i>

                    <!-- Table 1, 2, 3 and so on -->
                    <h5 class="card-title">Table <?= $table->table_number ?></h5>

                    <!-- Status Badge: Available, Occupied, Reserved, Cleaning, Unavailable -->
                    <span class="badge <?= $badgeClass ?>"><?= ucfirst($status) ?></span>
                    
                    <!-- Other Details but smaller -->
                    <div class="mt-2">
                        <?php if ($status === 'occupied'): ?>
                            <small class="text-muted">Guests: <?= $table->capacity ?>/<?= $table->capacity ?></small>
                            <br>
                            <small class="text-muted">Order: #<?= $table->order_id ?? 'N/A' ?></small>

                        <?php elseif ($status === 'reserved'): ?>
                            <?php if (!empty($table->reservation_time)): ?>
                                <small class="text-muted">
                                    <i class="far fa-clock"></i> <?= date('M d, h:i A', strtotime($table->reservation_time)) ?>
                                </small>
                            <?php else: ?>
                                <small class="text-muted">Time: TBD</small>
                            <?php endif; ?>
                            <br>
                            <small class="text-muted">Name: <?= esc($table->customer_name ?? 'Guest') ?></small>

                        <?php elseif ($status === 'cleaning'): ?>
                            <small class="text-muted">ETA: 5 min</small>

                        <?php else: ?>
                            <small class="text-muted">Capacity: <?= $table->capacity ?></small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>  
    <?php endforeach; ?>
</div>

<!-- Add/Edit Table Modal -->
<div class="modal fade" id="addTableModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Table</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addTableForm">
                    <!-- CSRF Token - MUST BE HERE -->
                    <?= csrf_field() ?>
                    <!-- Added this -->
                    <input type="hidden" name="table_id" id="edit_table_id">
                    
                    <!-- Table Number Field -->
                    <div class="mb-3">
                        <label class="form-label">Table Number</label>
                        <input type="number" class="form-control" id="table_number_input" name="table_number" min="1" max="100" required>
                        <small class="text-muted">Next available: <strong><?= $next_table_number ?></strong></small>
                    </div>
                    <!-- Capacity Field -->
                    <div class="mb-3">
                        <label class="form-label">Capacity</label>
                        <input type="number" class="form-control" name="capacity" min="1" max="12" required>
                    </div>
                    <!-- Location Field -->
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <select class="form-select" name="location" required>
                            <option value="" disabled selected>Select Location</option>
                            <option value="indoor">Indoor</option>
                            <option value="outdoor">Outdoor</option>
                            <option value="vip">VIP Section</option>
                        </select>
                    </div>
                    <!-- Status Field -->
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" required>
                            <option value="available">Available</option>
                            <option value="occupied">Occupied</option>
                            <option value="reserved">Reserved</option>
                            <option value="cleaning">Cleaning</option>
                            <option value="unavailable">Unavailable (Maintenance)</option>
                        </select>
                    </div>
                    <!-- Reservation Details - Only shown when status is 'reserved' -->
                    <div id="reservationDetails" style="display: none;">
                        <!-- Reservation Date -->
                        <div class="mb-3">
                            <label class="form-label">Reservation Date</label>
                            <input type="date" class="form-control" id="reservationDate" name="reservation_date">
                            <small class="text-muted">Default: Today (<?= date('M d, Y', strtotime($current_date)) ?>)</small>
                        </div>
                        
                        <!-- Reservation Time -->
                        <div class="mb-3">
                            <label class="form-label">Reservation Time</label>
                            <input type="time" class="form-control" id="reservationTime" name="reservation_time">
                            <small class="text-muted">Default: Now (<?= date('H:i', strtotime($current_time)) ?>)</small>
                        </div>
                    </div>
                    <!-- Customer Name - Only shown when status is 'reserved' -->
                    <div class="mb-3" id="reservationName" style="display: none;">
                        <label class="form-label">Guest Name</label>
                        <input type="text" class="form-control" id="customerName" name="customer_name" placeholder="Enter guest name">
                    </div>
                    </form>
                </div>
                    <!-- Modal Footer -->
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-danger" id="deleteTableBtn" style="display:none;" onclick="confirmDelete()">
                    <i class="fas fa-trash"></i> Delete Table
                </button>
                <div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveTable()">Save Changes</button>
                </div>
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
                    <label class="form-label">Reservation Time (Optional)</label>
                    <input type="datetime-local" class="form-control" id="reservationTime" name="reservationTime">
                    <small class="text-muted">No Date or Time? Leave it be</small>
                </div>
                <div class="mb-3" id="reservationName" style="display: none;">
                    <label class="form-label">Customer Name</label>
                    <input type="text" class="form-control" id="customerName" name ="customerName" placeholder="Enter customer name">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updateTableStatus()">Update Status</button>
            </div>
        </div>
    </div>
</div>

<!-- Restore Modal -->
<div class="modal fade" id="restoreModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-undo"></i> Restore Deleted Tables</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php if (!empty($deleted_tables)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Table #</th>
                                    <th>Capacity</th>
                                    <th>Location</th>
                                    <th>Last Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($deleted_tables as $delTable): ?>
                                    <tr>
                                        <td>
                                            <strong>Table <?= $delTable->table_number ?></strong>
                                        </td>
                                        <td><?= $delTable->capacity ?> seats</td>
                                        <td>
                                            <span class="badge bg-secondary"><?= ucfirst($delTable->location) ?></span>
                                        </td>
                                        <td><?= ucfirst($delTable->status) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-success" onclick="restoreTable('<?= $delTable->id ?>', '<?= $delTable->table_number ?>')">
                                                <i class="fas fa-undo"></i> Restore
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No deleted tables to restore.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Store constants from PHP
const NEXT_TABLE_NUMBER = <?= $next_table_number ?>;
const CURRENT_DATE = '<?= $current_date ?>';
const CURRENT_TIME = '<?= $current_time ?>';

$(document).ready(function() {
    // 1. Reset Modal for NEW tables
    $('[data-bs-target="#addTableModal"]').on('click', function() {
        $('#addTableForm')[0].reset();
        $('#edit_table_id').val('');
        $('#table_number_input').val(NEXT_TABLE_NUMBER);
        $('#table_number_input').prop('disabled', false);  // Enable for new tables
        $('#reservationDate').val(CURRENT_DATE);
        $('#reservationTime').val(CURRENT_TIME);
        $('#deleteTableBtn').hide();
        $('#addTableModal .modal-title').text('Add New Table');
        $('#addTableModal .btn-primary').text('Add Table');
        
        // Hide reservation fields for new "Available" table
        $('#reservationDetails, #reservationName').hide();
    });

    // 2. Handle Status change
    $('select[name="status"]').change(function() {
        toggleReservationFields($(this).val());
    });
});

// 4. Edit table function
function editTable(id, number, capacity, location, status, custName = '', resTime = '') {
    $('#addTableModal .modal-title').text('Edit Table ' + number);
    $('#addTableModal .btn-primary').text('Save Changes');
    
    $('#edit_table_id').val(id);
    $('#table_number_input').val(number);
    $('#table_number_input').prop('disabled', true);  // DISABLE table number when editing
    $('input[name="capacity"]').val(capacity);
    $('select[name="location"]').val(location);
    $('select[name="status"]').val(status);
    
    $('#customerName').val(custName);
    
    // Parse reservation time into separate date and time
    if (resTime) {
        try {
            const dateTimeObj = new Date(resTime);
            const dateStr = dateTimeObj.toISOString().split('T')[0];
            const timeStr = dateTimeObj.toTimeString().slice(0, 5);
            $('#reservationDate').val(dateStr);
            $('#reservationTime').val(timeStr);
        } catch (e) {
            // Fallback to current values if parsing fails
            $('#reservationDate').val(CURRENT_DATE);
            $('#reservationTime').val(CURRENT_TIME);
        }
    } else {
        $('#reservationDate').val(CURRENT_DATE);
        $('#reservationTime').val(CURRENT_TIME);
    }
    
    toggleReservationFields(status);
    $('#deleteTableBtn').show();
    $('#addTableModal').modal('show');
}

// 5. Toggle reservation fields
function toggleReservationFields(status) {
    if (status === 'reserved') {
        $('#reservationDetails, #reservationName').slideDown();
        $('#customerName').attr('required', true);
        $('#reservationDate').attr('required', true);
        $('#reservationTime').attr('required', true);
    } else {
        $('#reservationDetails, #reservationName').slideUp();
        $('#customerName').attr('required', false);
        $('#reservationDate').attr('required', false);
        $('#reservationTime').attr('required', false);
    }
}

// 6. Save table
function saveTable() {
    const status = $('select[name="status"]').val();
    const tableNumber = $('#table_number_input').val();
    
    // Validate reserved table fields
    if (status === 'reserved') {
        if (!$('#customerName').val().trim()) {
            alert('Guest name is required for reserved tables');
            return;
        }
        if (!$('#reservationDate').val()) {
            alert('Reservation date is required');
            return;
        }
        if (!$('#reservationTime').val()) {
            alert('Reservation time is required');
            return;
        }
    }
    
    // Use FormData with the form that contains CSRF token
    const formData = new FormData($('#addTableForm')[0]);
    
    // Ensure table_number is always included
    formData.set('table_number', tableNumber);
    
    const saveBtn = $('#addTableModal .btn-primary');
    const originalText = saveBtn.html();

    saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

    $.ajax({
        url: '<?= base_url("restaurant/{$tenant_slug}/save-table") ?>',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                alert(response.message);
                $('#addTableModal').modal('hide');
                location.reload();
            } else {
                alert('Error: ' + (typeof response.error === 'object' ? Object.values(response.error).join('\n') : response.error));
            }
        },
        error: function (xhr) {
            console.error('Status:', xhr.status);
            console.error('Response:', xhr.responseText);
            
            if (xhr.status === 403) {
                alert('Security Error: CSRF token expired. Please refresh the page and try again.');
            } else {
                alert('❌ Error: ' + xhr.statusText || '. Something went wrong. Please try again.');
            }
        },
        complete: function() {
            saveBtn.prop('disabled', false).html(originalText);
        }
    });
}

// 7. Delete table
function confirmDelete() {
    const tableId = $('#edit_table_id').val();
    const tableNum = $('#table_number_input').val();

    if (confirm('Are you sure you want to delete Table #' + tableNum + '? This action cannot be undone.')) {
        // Create FormData to include CSRF token properly
        const deleteData = new FormData();
        deleteData.append('table_id', tableId);
        
        // Get CSRF token from the form
        const csrfName = $('input[name^="csrf_"]').attr('name');
        const csrfValue = $('input[name^="csrf_"]').val();
        if (csrfName && csrfValue) {
            deleteData.append(csrfName, csrfValue);
        }
        
        $.ajax({
            url: '<?= base_url("restaurant/{$tenant_slug}/delete-table") ?>',
            type: 'POST',
            data: deleteData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Delete failed: ' + (response.error || 'Unknown error'));
                }
            },
            error: function(xhr) {
                console.error('Status:', xhr.status);
                console.error('Response:', xhr.responseText);
                if (xhr.status === 403) {
                    alert('Security Error: Please refresh the page and try again.');
                } else {
                    alert('Error deleting table. Please try again.');
                }
            }
        });
    }
}
// NEW: Restore function
function restoreTable(tableId, tableNum) {
    if (confirm('Restore Table #' + tableNum + '?')) {
        const restoreData = new FormData();
        restoreData.append('table_id', tableId);
        
        const csrfName = $('input[name^="csrf_"]').attr('name');
        const csrfValue = $('input[name^="csrf_"]').val();
        if (csrfName && csrfValue) {
            restoreData.append(csrfName, csrfValue);
        }
        
        $.ajax({
            url: '<?= base_url("restaurant/{$tenant_slug}/restore-table") ?>',
            type: 'POST',
            data: restoreData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#restoreModal').modal('hide');
                    alert('✅ ' + response.message || 'Table restored successfully!')
                    setTimeout(function() {
                        location.reload(true);
                    }, 300);
                } else {
                    alert('Restore failed: ' + (response.error || 'Unknown error'));
                }
            },
            error: function(xhr) {
                alert('Error restoring table. Please try again.');
            }
        });
    }
}

function refreshTables() {
    location.reload();
}
</script>
<?= $this->endSection() ?>
