<?php
/**
 * TouchPoint POS - Waiter/Server Module Views
 * NTEKSYSTEMS Inc.
 * BIR-Certified Multi-Tenant Restaurant Management System
 */

// =============================================================================
// WAITER/TABLES/ - Table Management Views
// =============================================================================

// File: app/Views/waiter/tables/index.php
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Table Management - <?= esc($tenant_config['restaurant_name']) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">🪑 Table Management</h3>
                    <div class="btn-group">
                        <button class="btn btn-success" onclick="openNewOrderModal()">
                            <i class="fas fa-plus"></i> New Order
                        </button>
                        <button class="btn btn-info" onclick="refreshTables()">
                            <i class="fas fa-sync"></i> Refresh
                        </button>
                        <button class="btn btn-warning" onclick="showReservations()">
                            <i class="fas fa-calendar"></i> Reservations
                        </button>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Table Status Legend -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <div class="row text-center">
                                    <div class="col-md-2">
                                        <span class="badge badge-success badge-lg">Available</span>
                                        <p class="mt-2">Ready for seating</p>
                                    </div>
                                    <div class="col-md-2">
                                        <span class="badge badge-danger badge-lg">Occupied</span>
                                        <p class="mt-2">Currently dining</p>
                                    </div>
                                    <div class="col-md-2">
                                        <span class="badge badge-warning badge-lg">Cleaning</span>
                                        <p class="mt-2">Being cleaned</p>
                                    </div>
                                    <div class="col-md-2">
                                        <span class="badge badge-primary badge-lg">Reserved</span>
                                        <p class="mt-2">Reserved booking</p>
                                    </div>
                                    <div class="col-md-2">
                                        <span class="badge badge-secondary badge-lg">Out of Order</span>
                                        <p class="mt-2">Not available</p>
                                    </div>
                                    <div class="col-md-2">
                                        <span class="badge badge-info badge-lg">Waiting Bill</span>
                                        <p class="mt-2">Ready to pay</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table Layout Grid -->
                    <div class="row">
                        <div class="col-12">
                            <div class="table-layout-container" style="min-height: 600px; position: relative; background: #f8f9fa; border-radius: 10px; padding: 20px;">
                                <?php if (isset($tables) && !empty($tables)): ?>
                                    <?php foreach ($tables as $table): ?>
                                    <div class="table-item table-<?= $table['status'] ?>" 
                                         data-table-id="<?= $table['id'] ?>"
                                         data-table-number="<?= $table['number'] ?>"
                                         data-capacity="<?= $table['capacity'] ?>"
                                         data-status="<?= $table['status'] ?>"
                                         style="position: absolute; top: <?= $table['pos_y'] ?>px; left: <?= $table['pos_x'] ?>px;">
                                        
                                        <div class="table-content">
                                            <div class="table-number">T<?= $table['number'] ?></div>
                                            <div class="table-capacity"><?= $table['capacity'] ?> seats</div>
                                            
                                            <?php if ($table['status'] === 'occupied'): ?>
                                                <div class="table-info">
                                                    <small>Order #<?= $table['current_order_id'] ?></small>
                                                    <br>
                                                    <small><?= $table['occupied_duration'] ?></small>
                                                </div>
                                            <?php elseif ($table['status'] === 'reserved'): ?>
                                                <div class="table-info">
                                                    <small>Reserved for:</small>
                                                    <br>
                                                    <small><?= date('H:i', strtotime($table['reservation_time'])) ?></small>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="table-actions">
                                                <?php if ($table['status'] === 'available'): ?>
                                                    <button class="btn btn-sm btn-success" onclick="seatCustomers(<?= $table['id'] ?>)">
                                                        Seat Customers
                                                    </button>
                                                <?php elseif ($table['status'] === 'occupied'): ?>
                                                    <div class="btn-group-vertical">
                                                        <button class="btn btn-sm btn-primary" onclick="viewOrder(<?= $table['current_order_id'] ?>)">
                                                            View Order
                                                        </button>
                                                        <button class="btn btn-sm btn-info" onclick="addToOrder(<?= $table['current_order_id'] ?>)">
                                                            Add Items
                                                        </button>
                                                        <button class="btn btn-sm btn-warning" onclick="requestBill(<?= $table['id'] ?>)">
                                                            Request Bill
                                                        </button>
                                                    </div>
                                                <?php elseif ($table['status'] === 'cleaning'): ?>
                                                    <button class="btn btn-sm btn-success" onclick="markClean(<?= $table['id'] ?>)">
                                                        Mark Clean
                                                    </button>
                                                <?php elseif ($table['status'] === 'waiting_bill'): ?>
                                                    <div class="btn-group-vertical">
                                                        <button class="btn btn-sm btn-success" onclick="processPayment(<?= $table['current_order_id'] ?>)">
                                                            Process Payment
                                                        </button>
                                                        <button class="btn btn-sm btn-info" onclick="printBill(<?= $table['current_order_id'] ?>)">
                                                            Print Bill
                                                        </button>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-2">
                                            <h4 class="text-success"><?= $table_stats['available'] ?? 0 ?></h4>
                                            <p>Available Tables</p>
                                        </div>
                                        <div class="col-md-2">
                                            <h4 class="text-danger"><?= $table_stats['occupied'] ?? 0 ?></h4>
                                            <p>Occupied Tables</p>
                                        </div>
                                        <div class="col-md-2">
                                            <h4 class="text-primary"><?= $table_stats['reserved'] ?? 0 ?></h4>
                                            <p>Reserved Tables</p>
                                        </div>
                                        <div class="col-md-2">
                                            <h4 class="text-info"><?= $table_stats['avg_turnover'] ?? 0 ?></h4>
                                            <p>Avg Turnover (hrs)</p>
                                        </div>
                                        <div class="col-md-2">
                                            <h4 class="text-warning"><?= $table_stats['peak_capacity'] ?? 0 ?>%</h4>
                                            <p>Peak Capacity</p>
                                        </div>
                                        <div class="col-md-2">
                                            <h4 class="text-secondary"><?= $table_stats['total_revenue'] ?? 0 ?></h4>
                                            <p>Today's Revenue</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Seat Customers Modal -->
<div class="modal fade" id="seatCustomersModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Seat Customers</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="seatCustomersForm">
                    <input type="hidden" id="selected_table_id" name="table_id">
                    
                    <div class="form-group">
                        <label for="party_size">Party Size</label>
                        <select id="party_size" name="party_size" class="form-control" required>
                            <option value="">Select party size</option>
                            <option value="1">1 Person</option>
                            <option value="2">2 People</option>
                            <option value="3">3 People</option>
                            <option value="4">4 People</option>
                            <option value="5">5 People</option>
                            <option value="6">6 People</option>
                            <option value="7">7 People</option>
                            <option value="8">8 People</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="customer_name">Customer Name (Optional)</label>
                        <input type="text" id="customer_name" name="customer_name" class="form-control" placeholder="Enter customer name">
                    </div>
                    
                    <div class="form-group">
                        <label for="customer_phone">Phone Number (Optional)</label>
                        <input type="tel" id="customer_phone" name="customer_phone" class="form-control" placeholder="Enter phone number">
                    </div>
                    
                    <div class="form-group">
                        <label for="special_requests">Special Requests</label>
                        <textarea id="special_requests" name="special_requests" class="form-control" rows="3" placeholder="Any special requests or notes"></textarea>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" id="is_reservation" name="is_reservation" class="form-check-input">
                        <label for="is_reservation" class="form-check-label">This is for a reservation</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="confirmSeatCustomers()">
                    Seat Customers
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Reservations Modal -->
<div class="modal fade" id="reservationsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">📅 Today's Reservations</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Customer</th>
                                <th>Party Size</th>
                                <th>Table</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="reservations_table_body">
                            <!-- Reservations will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.table-item {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    background: white;
    border: 3px solid;
}

.table-available {
    border-color: #28a745;
    color: #28a745;
}

.table-occupied {
    border-color: #dc3545;
    color: #dc3545;
    background: #ffe6e6;
}

.table-cleaning {
    border-color: #ffc107;
    color: #856404;
    background: #fff8e1;
}

.table-reserved {
    border-color: #007bff;
    color: #007bff;
    background: #e6f3ff;
}

.table-out_of_order {
    border-color: #6c757d;
    color: #6c757d;
    background: #f8f9fa;
}

.table-waiting_bill {
    border-color: #17a2b8;
    color: #17a2b8;
    background: #e6f9fc;
}

.table-content {
    text-align: center;
    padding: 10px;
}

.table-number {
    font-size: 1.2em;
    font-weight: bold;
    margin-bottom: 5px;
}

.table-capacity {
    font-size: 0.8em;
    margin-bottom: 5px;
}

.table-info {
    font-size: 0.7em;
    margin-bottom: 10px;
}

.table-actions {
    margin-top: 10px;
}

.table-item:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 20px rgba(0,0,0,0.2);
}

.badge-lg {
    padding: 8px 12px;
    font-size: 0.9em;
}

@media (max-width: 768px) {
    .table-item {
        width: 100px;
        height: 100px;
    }
    
    .table-number {
        font-size: 1em;
    }
    
    .table-capacity, .table-info {
        font-size: 0.7em;
    }
}
</style>

<script>
// Table Management JavaScript
$(document).ready(function() {
    // Auto-refresh tables every 30 seconds
    setInterval(refreshTables, 30000);
    
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
});

function seatCustomers(tableId) {
    $('#selected_table_id').val(tableId);
    $('#seatCustomersModal').modal('show');
}

function confirmSeatCustomers() {
    const formData = $('#seatCustomersForm').serialize();
    
    $.ajax({
        url: '<?= base_url("restaurant/{$tenant_id}/waiter/tables/seat") ?>',
        method: 'POST',
        data: formData,
        dataType: 'json',
        beforeSend: function() {
            $('.modal .btn-success').prop('disabled', true).text('Seating...');
        },
        success: function(response) {
            if (response.success) {
                $('#seatCustomersModal').modal('hide');
                showNotification('Customers seated successfully!', 'success');
                refreshTables();
                
                // Optionally open order taking interface
                if (confirm('Would you like to start taking their order now?')) {
                    window.location.href = '<?= base_url("restaurant/{$tenant_id}/waiter/orders/new/") ?>' + response.order_id;
                }
            } else {
                showNotification('Error seating customers: ' + response.message, 'error');
            }
        },
        error: function() {
            showNotification('Error seating customers. Please try again.', 'error');
        },
        complete: function() {
            $('.modal .btn-success').prop('disabled', false).text('Seat Customers');
        }
    });
}

function viewOrder(orderId) {
    window.open('<?= base_url("restaurant/{$tenant_id}/waiter/orders/view/") ?>' + orderId, '_blank');
}

function addToOrder(orderId) {
    window.location.href = '<?= base_url("restaurant/{$tenant_id}/waiter/orders/edit/") ?>' + orderId;
}

function requestBill(tableId) {
    if (confirm('Request bill for this table? This will notify the cashier.')) {
        $.ajax({
            url: '<?= base_url("restaurant/{$tenant_id}/waiter/tables/request-bill") ?>',
            method: 'POST',
            data: { table_id: tableId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showNotification('Bill requested successfully!', 'success');
                    refreshTables();
                } else {
                    showNotification('Error requesting bill: ' + response.message, 'error');
                }
            },
            error: function() {
                showNotification('Error requesting bill. Please try again.', 'error');
            }
        });
    }
}

function markClean(tableId) {
    $.ajax({
        url: '<?= base_url("restaurant/{$tenant_id}/waiter/tables/mark-clean") ?>',
        method: 'POST',
        data: { table_id: tableId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showNotification('Table marked as clean!', 'success');
                refreshTables();
            } else {
                showNotification('Error updating table status: ' + response.message, 'error');
            }
        },
        error: function() {
            showNotification('Error updating table status. Please try again.', 'error');
        }
    });
}

function processPayment(orderId) {
    window.location.href = '<?= base_url("restaurant/{$tenant_id}/cashier/payment/") ?>' + orderId;
}

function printBill(orderId) {
    window.open('<?= base_url("restaurant/{$tenant_id}/orders/print-bill/") ?>' + orderId, '_blank');
}

function refreshTables() {
    $.ajax({
        url: '<?= base_url("restaurant/{$tenant_id}/waiter/tables/refresh") ?>',
        method: 'GET',
        dataType: 'json',
        beforeSend: function() {
            $('.btn-info i').addClass('fa-spin');
        },
        success: function(response) {
            if (response.success) {
                location.reload(); // Refresh the page to update table layout
            }
        },
        error: function() {
            showNotification('Error refreshing tables. Please try again.', 'error');
        },
        complete: function() {
            $('.btn-info i').removeClass('fa-spin');
        }
    });
}

function showReservations() {
    $.ajax({
        url: '<?= base_url("restaurant/{$tenant_id}/waiter/tables/reservations") ?>',
        method: 'GET',
        dataType: 'json',
        beforeSend: function() {
            $('#reservations_table_body').html('<tr><td colspan="6" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>');
            $('#reservationsModal').modal('show');
        },
        success: function(response) {
            if (response.success && response.reservations) {
                let tbody = '';
                response.reservations.forEach(function(reservation) {
                    tbody += `
                        <tr>
                            <td>${reservation.time}</td>
                            <td>${reservation.customer_name}</td>
                            <td>${reservation.party_size} people</td>
                            <td>Table ${reservation.table_number || 'TBA'}</td>
                            <td><span class="badge badge-${getReservationBadgeColor(reservation.status)}">${reservation.status}</span></td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-success" onclick="confirmReservation(${reservation.id})">Confirm</button>
                                    <button class="btn btn-sm btn-warning" onclick="modifyReservation(${reservation.id})">Modify</button>
                                    <button class="btn btn-sm btn-danger" onclick="cancelReservation(${reservation.id})">Cancel</button>
                                </div>
                            </td>
                        </tr>
                    `;
                });
                $('#reservations_table_body').html(tbody);
            } else {
                $('#reservations_table_body').html('<tr><td colspan="6" class="text-center">No reservations for today</td></tr>');
            }
        },
        error: function() {
            $('#reservations_table_body').html('<tr><td colspan="6" class="text-center text-danger">Error loading reservations</td></tr>');
        }
    });
}

function getReservationBadgeColor(status) {
    const colors = {
        'confirmed': 'success',
        'pending': 'warning',
        'seated': 'info',
        'cancelled': 'danger',
        'no_show': 'secondary'
    };
    return colors[status] || 'secondary';
}

function openNewOrderModal() {
    // Redirect to order taking page
    window.location.href = '<?= base_url("restaurant/{$tenant_id}/waiter/orders/new") ?>';
}

function showNotification(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const notification = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    `;
    
    $('.container-fluid').prepend(notification);
    
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
}

// Real-time updates via WebSocket (if implemented)
if (typeof io !== 'undefined') {
    const socket = io('<?= base_url() ?>');
    socket.on('table_status_update', function(data) {
        if (data.tenant_id === '<?= $tenant_id ?>') {
            refreshTables();
        }
    });
}
</script>
<?= $this->endSection() ?>