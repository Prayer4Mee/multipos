<?php
// =====================================
// app/Views/components/modals/order_details.php
// =====================================
?>
<!-- Order Details Modal -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-receipt"></i> Order Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="order-info">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6>Order Information</h6>
                            <p><strong>Order #:</strong> <span id="order-number"></span></p>
                            <p><strong>Date & Time:</strong> <span id="order-datetime"></span></p>
                            <p><strong>Status:</strong> <span id="order-status"></span></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Customer Information</h6>
                            <p><strong>Name:</strong> <span id="customer-name"></span></p>
                            <p><strong>Table:</strong> <span id="table-number"></span></p>
                            <p><strong>Type:</strong> <span id="order-type"></span></p>
                        </div>
                    </div>
                </div>
                
                <div class="order-items">
                    <h6>Order Items</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Qty</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                    <th>Special Instructions</th>
                                </tr>
                            </thead>
                            <tbody id="order-items-list">
                                <!-- Items will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="order-summary mt-3">
                    <div class="row">
                        <div class="col-md-6 offset-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <td>Subtotal:</td>
                                    <td class="text-end" id="order-subtotal"></td>
                                </tr>
                                <tr>
                                    <td>Service Charge:</td>
                                    <td class="text-end" id="order-service-charge"></td>
                                </tr>
                                <tr>
                                    <td>VAT (12%):</td>
                                    <td class="text-end" id="order-vat"></td>
                                </tr>
                                <tr class="table-primary">
                                    <td><strong>Total:</strong></td>
                                    <td class="text-end"><strong id="order-total"></strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="payment-info mt-3">
                    <h6>Payment Information</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Payment Method:</strong> <span id="payment-method"></span></p>
                            <p><strong>Payment Status:</strong> <span id="payment-status"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Reference #:</strong> <span id="payment-reference"></span></p>
                            <p><strong>Paid Amount:</strong> <span id="paid-amount"></span></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printOrderReceipt()">
                    <i class="fas fa-print"></i> Print Receipt
                </button>
                <button type="button" class="btn btn-success" onclick="updateOrderStatus()">
                    <i class="fas fa-edit"></i> Update Status
                </button>
            </div>
        </div>
    </div>
</div>